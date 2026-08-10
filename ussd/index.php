<?php

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

const USSD_PAGE_SIZE = 4;

/*
|--------------------------------------------------------------------------
| Mzumbe Electoral Portal - session-based USSD callback
|--------------------------------------------------------------------------
| Africa's Talking sends the complete input history in "text". This callback
| uses sessionId to keep navigation state, never stores the password, caches
| repeated callbacks, and automatically advances to the next ballot position.
|--------------------------------------------------------------------------
*/

try {
    require_once __DIR__ . '/../config/database.php';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('The database connection is unavailable.');
    }
} catch (Throwable $e) {
    error_log('USSD database error: ' . $e->getMessage());
    echo 'END The voting service is temporarily unavailable.';
    exit;
}

$sessionId   = trim((string) ($_POST['sessionId'] ?? ''));
$serviceCode = trim((string) ($_POST['serviceCode'] ?? ''));
$phoneNumber = trim((string) ($_POST['phoneNumber'] ?? ''));
$text        = trim((string) ($_POST['text'] ?? ''));

/*
|--------------------------------------------------------------------------
| Response and display helpers
|--------------------------------------------------------------------------
*/

function rawRespond(string $type, string $message): void
{
    echo $type . ' ' . $message;
    exit;
}

function mainMenuText(string $prefix = ''): string
{
    return $prefix .
        "Mzumbe Electoral Portal\n" .
        "1. Vote\n" .
        "2. Check application status\n" .
        "3. Help";
}

function shortLabel(string $value, int $limit = 24): string
{
    $value = trim((string) preg_replace('/\s+/', ' ', $value));

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($value, 0, $limit, '...', 'UTF-8');
    }

    if (strlen($value) <= $limit) {
        return $value;
    }

    return substr($value, 0, max(1, $limit - 3)) . '...';
}

function latestInput(string $text): string
{
    if ($text === '') {
        return '';
    }

    $parts = explode('*', $text);
    return trim((string) end($parts));
}

function encodeOptions(array $ids): string
{
    $json = json_encode(array_values(array_map('intval', $ids)));
    return $json === false ? '[]' : $json;
}

function decodeOptions(?string $json): array
{
    if ($json === null || $json === '') {
        return [];
    }

    $decoded = json_decode($json, true);

    if (!is_array($decoded)) {
        return [];
    }

    return array_values(array_map('intval', $decoded));
}

/*
|--------------------------------------------------------------------------
| USSD session storage
|--------------------------------------------------------------------------
| The table is created automatically the first time this callback runs.
|--------------------------------------------------------------------------
*/

function ensureSessionTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS ussd_ballot_sessions (
            session_id VARCHAR(128) NOT NULL,
            phone_number VARCHAR(30) NOT NULL DEFAULT '',
            state VARCHAR(40) NOT NULL DEFAULT 'main',
            user_id INT NULL,
            pending_student_no VARCHAR(50) NULL,
            election_id INT NULL,
            current_position_id INT NULL,
            selected_candidate_id INT NULL,
            menu_options TEXT NULL,
            menu_page SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            last_request_hash CHAR(64) NULL,
            last_response TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (session_id),
            KEY idx_ussd_last_activity (last_activity),
            KEY idx_ussd_user_election (user_id, election_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function getSession(PDO $pdo, string $sessionId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM ussd_ballot_sessions WHERE session_id = ? LIMIT 1'
    );
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch();

    return $session ?: null;
}

function createSession(
    PDO $pdo,
    string $sessionId,
    string $phoneNumber
): void {
    $stmt = $pdo->prepare(
        "INSERT INTO ussd_ballot_sessions (
            session_id,
            phone_number,
            state,
            created_at,
            last_activity
        ) VALUES (?, ?, 'main', NOW(), NOW())"
    );
    $stmt->execute([$sessionId, substr($phoneNumber, 0, 30)]);
}

function replyAndExit(
    PDO $pdo,
    string $sessionId,
    string $requestHash,
    string $type,
    string $message,
    array $changes = []
): void {
    $allowedColumns = [
        'state',
        'user_id',
        'pending_student_no',
        'election_id',
        'current_position_id',
        'selected_candidate_id',
        'menu_options',
        'menu_page',
    ];

    $sets = [
        'last_request_hash = ?',
        'last_response = ?',
        'last_activity = NOW()',
    ];

    $response = $type . ' ' . $message;
    $params = [$requestHash, $response];

    foreach ($changes as $column => $value) {
        if (!in_array($column, $allowedColumns, true)) {
            continue;
        }

        $sets[] = $column . ' = ?';
        $params[] = $value;
    }

    $params[] = $sessionId;

    $stmt = $pdo->prepare(
        'UPDATE ussd_ballot_sessions SET ' .
        implode(', ', $sets) .
        ' WHERE session_id = ?'
    );
    $stmt->execute($params);

    echo $response;
    exit;
}

function endCurrentSession(
    PDO $pdo,
    array $session,
    string $requestHash,
    string $message,
    string $state = 'ended'
): void {
    replyAndExit(
        $pdo,
        $session['session_id'],
        $requestHash,
        'END',
        $message,
        [
            'state' => $state,
            'pending_student_no' => null,
            'selected_candidate_id' => null,
            'menu_options' => null,
            'menu_page' => 0,
        ]
    );
}

function showMainMenu(
    PDO $pdo,
    array $session,
    string $requestHash,
    string $prefix = ''
): void {
    replyAndExit(
        $pdo,
        $session['session_id'],
        $requestHash,
        'CON',
        mainMenuText($prefix),
        [
            'state' => 'main',
            'user_id' => null,
            'pending_student_no' => null,
            'election_id' => null,
            'current_position_id' => null,
            'selected_candidate_id' => null,
            'menu_options' => null,
            'menu_page' => 0,
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Voter, election, position, and candidate queries
|--------------------------------------------------------------------------
*/

function authenticateVoter(
    PDO $pdo,
    string $studentNo,
    string $password
): ?array {
    $stmt = $pdo->prepare(
        "SELECT
            id,
            student_no,
            full_name,
            phone,
            faculty_code,
            programme_code,
            study_year,
            password_hash,
            must_change_password,
            role,
            is_active
         FROM users
         WHERE student_no = ?
         LIMIT 1"
    );
    $stmt->execute([$studentNo]);
    $user = $stmt->fetch();

    if (
        !$user ||
        $user['role'] !== 'voter' ||
        (int) $user['is_active'] !== 1 ||
        !password_verify($password, $user['password_hash'])
    ) {
        return null;
    }

    return $user;
}

function loadVoter(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT
            id,
            student_no,
            full_name,
            faculty_code,
            programme_code,
            study_year,
            role,
            is_active
         FROM users
         WHERE id = ?
         LIMIT 1"
    );
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (
        !$user ||
        $user['role'] !== 'voter' ||
        (int) $user['is_active'] !== 1
    ) {
        return null;
    }

    return $user;
}

function loadOpenElections(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT id, title
         FROM elections
         WHERE status = 'open'
           AND start_date <= NOW()
           AND end_date >= NOW()
         ORDER BY start_date ASC, id ASC"
    );

    return $stmt->fetchAll();
}

function loadOpenElection(PDO $pdo, int $electionId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT id, title
         FROM elections
         WHERE id = ?
           AND status = 'open'
           AND start_date <= NOW()
           AND end_date >= NOW()
         LIMIT 1"
    );
    $stmt->execute([$electionId]);
    $election = $stmt->fetch();

    return $election ?: null;
}

function findNextPosition(
    PDO $pdo,
    array $user,
    int $electionId
): ?array {
    $stmt = $pdo->prepare(
        "SELECT p.id, p.title
         FROM positions p
         WHERE p.election_id = ?
           AND p.is_active = 1
           AND (
                p.voter_faculty_code IS NULL
                OR p.voter_faculty_code = ''
                OR p.voter_faculty_code = ?
           )
           AND (
                p.voter_years IS NULL
                OR p.voter_years = ''
                OR FIND_IN_SET(?, REPLACE(p.voter_years, ' ', '')) > 0
           )
           AND NOT EXISTS (
                SELECT 1
                FROM votes v
                WHERE v.voter_id = ?
                  AND v.election_id = p.election_id
                  AND v.position_id = p.id
           )
           AND EXISTS (
                SELECT 1
                FROM candidate_applications ca
                INNER JOIN users cu ON cu.id = ca.user_id
                WHERE ca.election_id = p.election_id
                  AND ca.position_id = p.id
                  AND cu.is_active = 1
                  AND (
                        ca.application_status = 'approved'
                        OR ca.status = 'approved'
                  )
                  AND ca.application_status <> 'rejected'
                  AND ca.status <> 'rejected'
           )
         ORDER BY p.id ASC
         LIMIT 1"
    );
    $stmt->execute([
        $electionId,
        (string) ($user['faculty_code'] ?? ''),
        (string) $user['study_year'],
        (int) $user['id'],
    ]);
    $position = $stmt->fetch();

    return $position ?: null;
}

function ballotStats(
    PDO $pdo,
    array $user,
    int $electionId
): array {
    $stmt = $pdo->prepare(
        "SELECT
            COUNT(*) AS eligible_positions,
            SUM(
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM candidate_applications ca
                    INNER JOIN users cu ON cu.id = ca.user_id
                    WHERE ca.election_id = p.election_id
                      AND ca.position_id = p.id
                      AND cu.is_active = 1
                      AND (
                            ca.application_status = 'approved'
                            OR ca.status = 'approved'
                      )
                      AND ca.application_status <> 'rejected'
                      AND ca.status <> 'rejected'
                ) THEN 1 ELSE 0 END
            ) AS positions_with_candidates,
            SUM(
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM votes v
                    WHERE v.voter_id = ?
                      AND v.election_id = p.election_id
                      AND v.position_id = p.id
                ) THEN 1 ELSE 0 END
            ) AS voted_positions
         FROM positions p
         WHERE p.election_id = ?
           AND p.is_active = 1
           AND (
                p.voter_faculty_code IS NULL
                OR p.voter_faculty_code = ''
                OR p.voter_faculty_code = ?
           )
           AND (
                p.voter_years IS NULL
                OR p.voter_years = ''
                OR FIND_IN_SET(?, REPLACE(p.voter_years, ' ', '')) > 0
           )"
    );
    $stmt->execute([
        (int) $user['id'],
        $electionId,
        (string) ($user['faculty_code'] ?? ''),
        (string) $user['study_year'],
    ]);
    $stats = $stmt->fetch() ?: [];

    return [
        'eligible' => (int) ($stats['eligible_positions'] ?? 0),
        'with_candidates' => (int) ($stats['positions_with_candidates'] ?? 0),
        'voted' => (int) ($stats['voted_positions'] ?? 0),
    ];
}

function loadApprovedCandidates(
    PDO $pdo,
    int $electionId,
    int $positionId
): array {
    $stmt = $pdo->prepare(
        "SELECT
            ca.id,
            u.full_name
         FROM candidate_applications ca
         INNER JOIN users u ON u.id = ca.user_id
         WHERE ca.election_id = ?
           AND ca.position_id = ?
           AND u.is_active = 1
           AND (
                ca.application_status = 'approved'
                OR ca.status = 'approved'
           )
           AND ca.application_status <> 'rejected'
           AND ca.status <> 'rejected'
         ORDER BY u.full_name ASC, ca.id ASC"
    );
    $stmt->execute([$electionId, $positionId]);

    return $stmt->fetchAll();
}

function loadApprovedCandidate(
    PDO $pdo,
    int $candidateApplicationId,
    int $electionId,
    int $positionId
): ?array {
    $stmt = $pdo->prepare(
        "SELECT
            ca.id,
            ca.user_id,
            u.full_name,
            p.title AS position_title
         FROM candidate_applications ca
         INNER JOIN users u ON u.id = ca.user_id
         INNER JOIN positions p ON p.id = ca.position_id
         WHERE ca.id = ?
           AND ca.election_id = ?
           AND ca.position_id = ?
           AND p.is_active = 1
           AND u.is_active = 1
           AND (
                ca.application_status = 'approved'
                OR ca.status = 'approved'
           )
           AND ca.application_status <> 'rejected'
           AND ca.status <> 'rejected'
         LIMIT 1"
    );
    $stmt->execute([
        $candidateApplicationId,
        $electionId,
        $positionId,
    ]);
    $candidate = $stmt->fetch();

    return $candidate ?: null;
}

/*
|--------------------------------------------------------------------------
| Paged election and candidate menus
|--------------------------------------------------------------------------
*/

function showElectionMenu(
    PDO $pdo,
    array $session,
    string $requestHash,
    int $page = 0,
    string $prefix = '',
    array $baseChanges = []
): void {
    $elections = loadOpenElections($pdo);

    if (!$elections) {
        endCurrentSession(
            $pdo,
            $session,
            $requestHash,
            'Login successful, but there is no open election.'
        );
    }

    $pageCount = max(1, (int) ceil(count($elections) / USSD_PAGE_SIZE));
    $page = max(0, min($page, $pageCount - 1));
    $visible = array_slice(
        $elections,
        $page * USSD_PAGE_SIZE,
        USSD_PAGE_SIZE
    );

    $message = $prefix .
        'Select election (' . ($page + 1) . '/' . $pageCount . "):\n";
    $optionIds = [];

    foreach ($visible as $index => $election) {
        $message .= ($index + 1) . '. ' .
            shortLabel((string) $election['title']) . "\n";
        $optionIds[] = (int) $election['id'];
    }

    if ($page < $pageCount - 1) {
        $message .= "8. Next\n";
    }

    if ($page > 0) {
        $message .= "9. Previous\n";
    }

    $message .= '0. Cancel';

    $changes = array_merge(
        $baseChanges,
        [
            'state' => 'election_select',
            'menu_options' => encodeOptions($optionIds),
            'menu_page' => $page,
        ]
    );

    replyAndExit(
        $pdo,
        $session['session_id'],
        $requestHash,
        'CON',
        $message,
        $changes
    );
}

function showCandidateMenu(
    PDO $pdo,
    array $session,
    string $requestHash,
    int $page = 0,
    string $prefix = '',
    array $baseChanges = []
): void {
    $electionId = (int) ($session['election_id'] ?? 0);
    $positionId = (int) ($session['current_position_id'] ?? 0);

    $election = loadOpenElection($pdo, $electionId);

    if (!$election) {
        endCurrentSession(
            $pdo,
            $session,
            $requestHash,
            'This election is no longer open.'
        );
    }

    $stmt = $pdo->prepare(
        "SELECT id, title
         FROM positions
         WHERE id = ?
           AND election_id = ?
           AND is_active = 1
         LIMIT 1"
    );
    $stmt->execute([$positionId, $electionId]);
    $position = $stmt->fetch();

    if (!$position) {
        showNextPosition(
            $pdo,
            $session,
            $requestHash,
            "That position is unavailable. Moving on.\n",
            false,
            $baseChanges
        );
    }

    $candidates = loadApprovedCandidates($pdo, $electionId, $positionId);

    if (!$candidates) {
        showNextPosition(
            $pdo,
            $session,
            $requestHash,
            "No approved candidate for that position. Moving on.\n",
            false,
            array_merge(
                $baseChanges,
                ['current_position_id' => null]
            )
        );
    }

    $pageCount = max(1, (int) ceil(count($candidates) / USSD_PAGE_SIZE));
    $page = max(0, min($page, $pageCount - 1));
    $visible = array_slice(
        $candidates,
        $page * USSD_PAGE_SIZE,
        USSD_PAGE_SIZE
    );

    $message = $prefix .
        shortLabel((string) $position['title'], 28) . "\n" .
        'Choose candidate (' . ($page + 1) . '/' . $pageCount . "):\n";
    $optionIds = [];

    foreach ($visible as $index => $candidate) {
        $message .= ($index + 1) . '. ' .
            shortLabel((string) $candidate['full_name']) . "\n";
        $optionIds[] = (int) $candidate['id'];
    }

    if ($page < $pageCount - 1) {
        $message .= "8. Next\n";
    }

    if ($page > 0) {
        $message .= "9. Previous\n";
    }

    $message .= '0. Cancel ballot';

    $changes = array_merge(
        $baseChanges,
        [
            'state' => 'candidate_select',
            'current_position_id' => $positionId,
            'selected_candidate_id' => null,
            'menu_options' => encodeOptions($optionIds),
            'menu_page' => $page,
        ]
    );

    replyAndExit(
        $pdo,
        $session['session_id'],
        $requestHash,
        'CON',
        $message,
        $changes
    );
}

function showNextPosition(
    PDO $pdo,
    array $session,
    string $requestHash,
    string $prefix = '',
    bool $voteWasJustRecorded = false,
    array $baseChanges = []
): void {
    $userId = (int) ($session['user_id'] ?? 0);
    $electionId = (int) ($session['election_id'] ?? 0);

    $user = loadVoter($pdo, $userId);

    if (!$user) {
        endCurrentSession(
            $pdo,
            $session,
            $requestHash,
            'Your voter account is no longer active.'
        );
    }

    if (!loadOpenElection($pdo, $electionId)) {
        endCurrentSession(
            $pdo,
            $session,
            $requestHash,
            'This election is no longer open.'
        );
    }

    $position = findNextPosition($pdo, $user, $electionId);

    if (!$position) {
        $stats = ballotStats($pdo, $user, $electionId);
        $changes = array_merge(
            $baseChanges,
            [
                'state' => 'completed',
                'current_position_id' => null,
                'selected_candidate_id' => null,
                'menu_options' => null,
                'menu_page' => 0,
            ]
        );

        if ($stats['eligible'] === 0) {
            replyAndExit(
                $pdo,
                $session['session_id'],
                $requestHash,
                'END',
                'You are not eligible for any position in this election.',
                $changes
            );
        }

        if ($stats['with_candidates'] === 0) {
            replyAndExit(
                $pdo,
                $session['session_id'],
                $requestHash,
                'END',
                'No eligible position currently has an approved candidate.',
                $changes
            );
        }

        $message = $voteWasJustRecorded
            ? 'Ballot completed successfully. Your votes have been recorded.'
            : 'You have already completed this ballot.';

        if ($stats['with_candidates'] < $stats['eligible']) {
            $message .= ' Some positions had no approved candidates.';
        }

        replyAndExit(
            $pdo,
            $session['session_id'],
            $requestHash,
            'END',
            $message,
            $changes
        );
    }

    $nextSession = $session;
    $nextSession['current_position_id'] = (int) $position['id'];

    showCandidateMenu(
        $pdo,
        $nextSession,
        $requestHash,
        0,
        $prefix,
        array_merge(
            $baseChanges,
            [
                'current_position_id' => (int) $position['id'],
                'selected_candidate_id' => null,
            ]
        )
    );
}

/*
|--------------------------------------------------------------------------
| Transactional, retry-safe vote recording
|--------------------------------------------------------------------------
| Locking the voter row serializes simultaneous vote requests from one voter.
| A database unique key is still recommended as an additional safeguard.
|--------------------------------------------------------------------------
*/

function positionAllowsVoter(array $position, array $user): bool
{
    $requiredFaculty = trim((string) ($position['voter_faculty_code'] ?? ''));
    $userFaculty = trim((string) ($user['faculty_code'] ?? ''));

    if ($requiredFaculty !== '' && $requiredFaculty !== $userFaculty) {
        return false;
    }

    $allowedYears = trim((string) ($position['voter_years'] ?? ''));

    if ($allowedYears === '') {
        return true;
    }

    $years = array_filter(array_map('trim', explode(',', $allowedYears)));
    return in_array((string) $user['study_year'], $years, true);
}

function recordVote(
    PDO $pdo,
    int $userId,
    int $electionId,
    int $positionId,
    int $candidateApplicationId
): string {
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            "SELECT
                id,
                faculty_code,
                study_year,
                role,
                is_active
             FROM users
             WHERE id = ?
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (
            !$user ||
            $user['role'] !== 'voter' ||
            (int) $user['is_active'] !== 1
        ) {
            throw new RuntimeException('The voter account is unavailable.');
        }

        $stmt = $pdo->prepare(
            "SELECT id
             FROM elections
             WHERE id = ?
               AND status = 'open'
               AND start_date <= NOW()
               AND end_date >= NOW()
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([$electionId]);

        if (!$stmt->fetch()) {
            throw new RuntimeException('The election is no longer open.');
        }

        $stmt = $pdo->prepare(
            "SELECT
                id,
                voter_faculty_code,
                voter_years
             FROM positions
             WHERE id = ?
               AND election_id = ?
               AND is_active = 1
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([$positionId, $electionId]);
        $position = $stmt->fetch();

        if (!$position || !positionAllowsVoter($position, $user)) {
            throw new RuntimeException(
                'The voter is not eligible for this position.'
            );
        }

        $stmt = $pdo->prepare(
            "SELECT ca.id
             FROM candidate_applications ca
             INNER JOIN users u ON u.id = ca.user_id
             WHERE ca.id = ?
               AND ca.election_id = ?
               AND ca.position_id = ?
               AND u.is_active = 1
               AND (
                    ca.application_status = 'approved'
                    OR ca.status = 'approved'
               )
               AND ca.application_status <> 'rejected'
               AND ca.status <> 'rejected'
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([
            $candidateApplicationId,
            $electionId,
            $positionId,
        ]);

        if (!$stmt->fetch()) {
            throw new RuntimeException(
                'The selected candidate is no longer available.'
            );
        }

        $stmt = $pdo->prepare(
            "SELECT candidate_application_id
             FROM votes
             WHERE voter_id = ?
               AND election_id = ?
               AND position_id = ?
             ORDER BY id ASC
             LIMIT 1"
        );
        $stmt->execute([$userId, $electionId, $positionId]);
        $existingVote = $stmt->fetch();

        if ($existingVote) {
            $pdo->commit();

            return (int) $existingVote['candidate_application_id'] ===
                $candidateApplicationId
                ? 'already_recorded'
                : 'position_already_voted';
        }

        $stmt = $pdo->prepare(
            "INSERT INTO votes (
                voter_id,
                election_id,
                position_id,
                candidate_application_id,
                voted_at
             ) VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $userId,
            $electionId,
            $positionId,
            $candidateApplicationId,
        ]);

        $pdo->commit();
        return 'recorded';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

/*
|--------------------------------------------------------------------------
| Start or resume session
|--------------------------------------------------------------------------
*/

try {
    ensureSessionTable($pdo);

    /*
    | Browser health check: opening index.php without Africa's Talking fields
    | displays the main menu without creating a shared blank session.
    */
    if ($sessionId === '') {
        rawRespond('CON', mainMenuText());
    }

    $sessionKey = strlen($sessionId) <= 128
        ? $sessionId
        : hash('sha256', $sessionId);
    $phoneNumber = substr($phoneNumber, 0, 30);
    $requestHash = hash('sha256', $sessionKey . "\0" . $text);
    $session = getSession($pdo, $sessionKey);

    if (!$session) {
        if ($text !== '') {
            rawRespond(
                'END',
                'Your session expired. Please dial the service again.'
            );
        }

        createSession($pdo, $sessionKey, $phoneNumber);
        $session = getSession($pdo, $sessionKey);

        if (!$session) {
            throw new RuntimeException('Unable to create the USSD session.');
        }

        /*
        | Remove old sessions only when a new USSD session begins.
        */
        $pdo->exec(
            "DELETE FROM ussd_ballot_sessions
             WHERE last_activity < (NOW() - INTERVAL 1 DAY)
             LIMIT 100"
        );
    }

    if (
        !empty($session['last_request_hash']) &&
        !empty($session['last_response']) &&
        hash_equals((string) $session['last_request_hash'], $requestHash)
    ) {
        echo $session['last_response'];
        exit;
    }

    if ($text === '') {
        showMainMenu($pdo, $session, $requestHash);
    }

    $input = latestInput($text);
    $state = (string) ($session['state'] ?? 'main');

    /*
    |--------------------------------------------------------------------------
    | Main menu
    |--------------------------------------------------------------------------
    */
    if ($state === 'main') {
        if ($input === '1') {
            replyAndExit(
                $pdo,
                $sessionKey,
                $requestHash,
                'CON',
                "Voting Menu\n" .
                "1. Login with Student Number\n" .
                "2. Back",
                ['state' => 'vote_menu']
            );
        }

        if ($input === '2') {
            replyAndExit(
                $pdo,
                $sessionKey,
                $requestHash,
                'CON',
                'Enter your Student Number:',
                ['state' => 'status_student']
            );
        }

        if ($input === '3') {
            endCurrentSession(
                $pdo,
                $session,
                $requestHash,
                'For assistance, contact the university election office.'
            );
        }

        showMainMenu(
            $pdo,
            $session,
            $requestHash,
            "Invalid option.\n"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Vote login
    |--------------------------------------------------------------------------
    */
    if ($state === 'vote_menu') {
        if ($input === '1') {
            replyAndExit(
                $pdo,
                $sessionKey,
                $requestHash,
                'CON',
                'Enter your Student Number:',
                [
                    'state' => 'login_student',
                    'pending_student_no' => null,
                ]
            );
        }

        if ($input === '2') {
            showMainMenu($pdo, $session, $requestHash);
        }

        replyAndExit(
            $pdo,
            $sessionKey,
            $requestHash,
            'CON',
            "Invalid option.\nVoting Menu\n" .
            "1. Login with Student Number\n" .
            "2. Back",
            ['state' => 'vote_menu']
        );
    }

    if ($state === 'login_student') {
        if ($input === '' || strlen($input) > 50) {
            replyAndExit(
                $pdo,
                $sessionKey,
                $requestHash,
                'CON',
                "Invalid student number.\nEnter your Student Number:",
                ['state' => 'login_student']
            );
        }

        replyAndExit(
            $pdo,
            $sessionKey,
            $requestHash,
            'CON',
            'Enter your Password:',
            [
                'state' => 'login_password',
                'pending_student_no' => $input,
            ]
        );
    }

    if ($state === 'login_password') {
        $studentNo = trim((string) ($session['pending_student_no'] ?? ''));

        if ($studentNo === '' || $input === '') {
            endCurrentSession(
                $pdo,
                $session,
                $requestHash,
                'Student number and password are required.'
            );
        }

        $user = authenticateVoter($pdo, $studentNo, $input);

        if (!$user) {
            endCurrentSession(
                $pdo,
                $session,
                $requestHash,
                'Invalid student number or password.'
            );
        }

        if ((int) $user['must_change_password'] === 1) {
            endCurrentSession(
                $pdo,
                $session,
                $requestHash,
                'Change your temporary password through the web portal first.'
            );
        }

        $session['user_id'] = (int) $user['id'];

        showElectionMenu(
            $pdo,
            $session,
            $requestHash,
            0,
            'Welcome, ' . shortLabel((string) $user['full_name']) . "\n",
            [
                'user_id' => (int) $user['id'],
                'pending_student_no' => null,
                'election_id' => null,
                'current_position_id' => null,
                'selected_candidate_id' => null,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Election selection
    |--------------------------------------------------------------------------
    */
    if ($state === 'election_select') {
        $page = (int) ($session['menu_page'] ?? 0);

        if ($input === '0') {
            endCurrentSession(
                $pdo,
                $session,
                $requestHash,
                'Voting cancelled. No new vote was recorded.'
            );
        }

        if ($input === '8') {
            showElectionMenu(
                $pdo,
                $session,
                $requestHash,
                $page + 1
            );
        }

        if ($input === '9') {
            showElectionMenu(
                $pdo,
                $session,
                $requestHash,
                max(0, $page - 1)
            );
        }

        $choice = filter_var($input, FILTER_VALIDATE_INT);
        $options = decodeOptions($session['menu_options'] ?? null);
        $selectedIndex = $choice === false ? -1 : ((int) $choice - 1);

        if ($selectedIndex < 0 || !isset($options[$selectedIndex])) {
            showElectionMenu(
                $pdo,
                $session,
                $requestHash,
                $page,
                "Invalid option.\n"
            );
        }

        $electionId = (int) $options[$selectedIndex];

        if (!loadOpenElection($pdo, $electionId)) {
            showElectionMenu(
                $pdo,
                $session,
                $requestHash,
                0,
                "That election is no longer available.\n"
            );
        }

        $session['election_id'] = $electionId;
        $session['current_position_id'] = null;

        showNextPosition(
            $pdo,
            $session,
            $requestHash,
            '',
            false,
            [
                'election_id' => $electionId,
                'current_position_id' => null,
                'selected_candidate_id' => null,
                'menu_options' => null,
                'menu_page' => 0,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Candidate selection and confirmation
    |--------------------------------------------------------------------------
    */
    if ($state === 'candidate_select') {
        $page = (int) ($session['menu_page'] ?? 0);

        if ($input === '0') {
            endCurrentSession(
                $pdo,
                $session,
                $requestHash,
                'Ballot cancelled. Votes already confirmed remain recorded.'
            );
        }

        if ($input === '8') {
            showCandidateMenu(
                $pdo,
                $session,
                $requestHash,
                $page + 1
            );
        }

        if ($input === '9') {
            showCandidateMenu(
                $pdo,
                $session,
                $requestHash,
                max(0, $page - 1)
            );
        }

        $choice = filter_var($input, FILTER_VALIDATE_INT);
        $options = decodeOptions($session['menu_options'] ?? null);
        $selectedIndex = $choice === false ? -1 : ((int) $choice - 1);

        if ($selectedIndex < 0 || !isset($options[$selectedIndex])) {
            showCandidateMenu(
                $pdo,
                $session,
                $requestHash,
                $page,
                "Invalid option.\n"
            );
        }

        $candidateApplicationId = (int) $options[$selectedIndex];
        $candidate = loadApprovedCandidate(
            $pdo,
            $candidateApplicationId,
            (int) $session['election_id'],
            (int) $session['current_position_id']
        );

        if (!$candidate) {
            showCandidateMenu(
                $pdo,
                $session,
                $requestHash,
                $page,
                "That candidate is no longer available.\n"
            );
        }

        $message =
            'Confirm ' . shortLabel((string) $candidate['full_name']) . "\n" .
            'For: ' . shortLabel(
                (string) $candidate['position_title'],
                28
            ) . "\n" .
            "1. Confirm vote\n" .
            "2. Choose again\n" .
            '0. Cancel ballot';

        replyAndExit(
            $pdo,
            $sessionKey,
            $requestHash,
            'CON',
            $message,
            [
                'state' => 'confirm_vote',
                'selected_candidate_id' => $candidateApplicationId,
                'menu_options' => null,
            ]
        );
    }

    if ($state === 'confirm_vote') {
        if ($input === '0') {
            endCurrentSession(
                $pdo,
                $session,
                $requestHash,
                'Ballot cancelled. Votes already confirmed remain recorded.'
            );
        }

        if ($input === '2') {
            showCandidateMenu(
                $pdo,
                $session,
                $requestHash,
                0,
                "Choose again.\n",
                ['selected_candidate_id' => null]
            );
        }

        if ($input !== '1') {
            $candidate = loadApprovedCandidate(
                $pdo,
                (int) $session['selected_candidate_id'],
                (int) $session['election_id'],
                (int) $session['current_position_id']
            );

            if (!$candidate) {
                showCandidateMenu(
                    $pdo,
                    $session,
                    $requestHash,
                    0,
                    "Candidate unavailable. Choose again.\n",
                    ['selected_candidate_id' => null]
                );
            }

            replyAndExit(
                $pdo,
                $sessionKey,
                $requestHash,
                'CON',
                "Invalid option.\n" .
                'Confirm ' .
                shortLabel((string) $candidate['full_name']) . "\n" .
                "1. Confirm vote\n" .
                "2. Choose again\n" .
                '0. Cancel ballot',
                ['state' => 'confirm_vote']
            );
        }

        $result = recordVote(
            $pdo,
            (int) $session['user_id'],
            (int) $session['election_id'],
            (int) $session['current_position_id'],
            (int) $session['selected_candidate_id']
        );

        $prefix = $result === 'position_already_voted'
            ? "This position was already voted. Moving on.\n"
            : "Vote recorded.\n";

        $session['current_position_id'] = null;
        $session['selected_candidate_id'] = null;

        showNextPosition(
            $pdo,
            $session,
            $requestHash,
            $prefix,
            true,
            [
                'current_position_id' => null,
                'selected_candidate_id' => null,
                'menu_options' => null,
                'menu_page' => 0,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Candidate application status
    |--------------------------------------------------------------------------
    */
    if ($state === 'status_student') {
        if ($input === '' || strlen($input) > 50) {
            replyAndExit(
                $pdo,
                $sessionKey,
                $requestHash,
                'CON',
                "Invalid student number.\nEnter your Student Number:",
                ['state' => 'status_student']
            );
        }

        $stmt = $pdo->prepare(
            "SELECT
                ca.application_status,
                ca.status,
                ca.vetting_comment,
                p.title AS position_title,
                e.title AS election_title
             FROM candidate_applications ca
             INNER JOIN users u ON u.id = ca.user_id
             INNER JOIN positions p ON p.id = ca.position_id
             INNER JOIN elections e ON e.id = ca.election_id
             WHERE u.student_no = ?
             ORDER BY ca.created_at DESC
             LIMIT 1"
        );
        $stmt->execute([$input]);
        $application = $stmt->fetch();

        if (!$application) {
            endCurrentSession(
                $pdo,
                $session,
                $requestHash,
                'No candidate application was found.'
            );
        }

        $status = (string) $application['application_status'];

        if ($application['status'] !== 'pending') {
            $status = (string) $application['status'];
        }

        $message =
            'Election: ' .
            shortLabel((string) $application['election_title'], 28) . "\n" .
            'Position: ' .
            shortLabel((string) $application['position_title'], 28) . "\n" .
            'Status: ' . ucfirst($status);

        if (trim((string) ($application['vetting_comment'] ?? '')) !== '') {
            $message .= "\nComment: " .
                shortLabel((string) $application['vetting_comment'], 50);
        }

        endCurrentSession($pdo, $session, $requestHash, $message);
    }

    if ($state === 'completed') {
        endCurrentSession(
            $pdo,
            $session,
            $requestHash,
            'This ballot session is complete. Dial again to start a new session.',
            'completed'
        );
    }

    endCurrentSession(
        $pdo,
        $session,
        $requestHash,
        'This session has ended. Please dial the service again.'
    );
} catch (Throwable $e) {
    error_log(
        'USSD processing error: ' .
        get_class($e) . ': ' .
        $e->getMessage()
    );

    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    rawRespond(
        'END',
        'Unable to process your request right now. Please try again.'
    );
}
