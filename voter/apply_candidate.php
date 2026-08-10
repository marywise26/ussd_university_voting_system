<?php
require_once __DIR__ . '/../includes/init.php';

$smsConfigPath = __DIR__ . '/../config/sms.php';

if (is_file($smsConfigPath)) {
    require_once $smsConfigPath;
}

require_login();

if (($_SESSION['role'] ?? '') === 'admin') {
    redirect('admin/dashboard.php');
}

$user = current_user($pdo);

function faculty_options_for_candidate_page(): array
{
    return [
        'FST' => 'Faculty of Science and Technology',
        'SOB' => 'School of Business',
        'FOL' => 'Faculty of Law',
        'FSS' => 'Faculty of Social Science',
        'IDS' => 'Institute of Development Studies',
        'SOPAM' => 'School of Public Administration and Management',
    ];
}

function candidate_form_definitions(): array
{
    $attachments = [
        ['key' => 'cv', 'label' => 'Updated Curriculum Vitae'],
        ['key' => 'results', 'label' => 'Certified academic results'],
        ['key' => 'passport_photo', 'label' => 'Recent passport size photograph'],
        ['key' => 'registration_form', 'label' => 'Student registration form'],
        ['key' => 'subscription_fee', 'label' => 'Evidence of payment of MUSO subscription fees'],
        ['key' => 'form_fee', 'label' => 'Evidence of payment of application form fee'],
    ];

    $commonParticulars = [
        ['key' => 'age', 'label' => 'Age', 'type' => 'number', 'required' => true],
        [
            'key' => 'sex',
            'label' => 'Sex',
            'type' => 'select',
            'required' => true,
            'options' => [
                '' => '-- Select sex --',
                'Male' => 'Male',
                'Female' => 'Female',
            ],
        ],
        ['key' => 'nationality', 'label' => 'Nationality', 'type' => 'text', 'required' => true],
        ['key' => 'po_box', 'label' => 'P.O. Box', 'type' => 'text', 'required' => false],
    ];

    return [
        'president' => [
            'label' => 'Chairperson (President) Application Form',
            'subtitle' => 'Major office application. Fifty (50) approved nominators are required.',
            'nominator_count' => 50,
            'particulars' => $commonParticulars,
            'attachments' => $attachments,
            'questions' => [
                ['key' => 'role_meaning', 'label' => 'What is the primary role of the MUSO Chairperson, and what does the position mean to the entire student body?'],
                ['key' => 'why_president', 'label' => 'Why do you wish to be President of MUSO? Describe a specific leadership role or initiative you previously undertook.'],
                ['key' => 'inclusive_leadership', 'label' => 'How will you ensure all students, including minority groups, off-campus students, and postgraduate students, are heard and represented?'],
                ['key' => 'tuition_response', 'label' => 'If university administration proposes a 20% tuition fee increase, how would you respond from first notice to final outcome?'],
                ['key' => 'policy_priorities', 'label' => 'Describe your top three policy priorities. For each, state the problem, action, and measurement of success.'],
                ['key' => 'funds_misuse', 'label' => 'A senior MUSO official misuses student funds. What steps would you take, and what governance principles would you uphold?'],
                ['key' => 'management_relationship', 'label' => 'How will you maintain a productive relationship with university management while firmly advocating for student interests?'],
                ['key' => 'success_commitments', 'label' => 'At the end of your term, how will students know your presidency was successful? State measurable commitments.'],
                ['key' => 'financial_transparency', 'label' => 'How will you ensure financial transparency and accountability in MUSO funds management?'],
                ['key' => 'meeting_management', 'label' => 'How familiar are you with parliamentary procedure and formal meeting management? Give an example.'],
                ['key' => 'student_welfare', 'label' => 'Which student welfare area is most neglected, and what concrete plan do you have to address it?'],
                ['key' => 'technology_communication', 'label' => 'How will you use technology and social media to communicate with students and gather feedback?'],
                ['key' => 'collaboration', 'label' => 'How will you collaborate with academic departments, clubs, and external organizations to improve student experience?'],
                ['key' => 'personal_weakness', 'label' => 'What personal weakness could affect your performance as President, and how will you manage it?'],
                ['key' => 'not_elected', 'label' => 'If you are not elected President, how will you continue contributing to student welfare and MUSO mission?'],
            ],
        ],

        'vice_president' => [
            'label' => 'Vice Chairperson (Vice President) Application Form',
            'subtitle' => 'Major office application. Fifty (50) approved nominators are required.',
            'nominator_count' => 50,
            'particulars' => $commonParticulars,
            'attachments' => $attachments,
            'questions' => [
                ['key' => 'vice_functions', 'label' => 'Describe the primary functions of the MUSO Vice Chairperson and how the role supports overall leadership.'],
                ['key' => 'why_vice', 'label' => 'Why do you wish to serve as Vice President? Give a real example where you supported a team leader or acted in a deputy role.'],
                ['key' => 'crisis_management', 'label' => 'If you were Acting President during a sudden student crisis, how would you handle it?'],
                ['key' => 'committee_oversight', 'label' => 'How will you monitor, support, and correct the performance of representatives and internal MUSO committees?'],
                ['key' => 'student_welfare_focus', 'label' => 'Describe three areas of student welfare or campus life you would improve, including the problem and your action.'],
                ['key' => 'president_disagreement', 'label' => 'How would you handle a serious disagreement with the Chairperson while maintaining a united leadership front?'],
                ['key' => 'build_trust', 'label' => 'How will you build and maintain trust between MUSO leadership and the student body?'],
                ['key' => 'vice_success', 'label' => 'At the end of your term, what measurable achievements will show that you served students effectively?'],
            ],
        ],

        'senator' => [
            'label' => 'Senator Application Form',
            'subtitle' => 'Senator application. Twenty (20) approved nominators are required.',
            'nominator_count' => 20,
            'particulars' => array_merge($commonParticulars, [
                ['key' => 'campus', 'label' => 'Campus', 'type' => 'text', 'required' => true],
                ['key' => 'senate_constituency', 'label' => 'Senate Constituency', 'type' => 'text', 'required' => true],
            ]),
            'attachments' => $attachments,
            'questions' => [
                ['key' => 'senator_role', 'label' => 'What is the role of a Senator in MUSO, and how does the Senate contribute to student governance?'],
                ['key' => 'why_senator', 'label' => 'Why do you wish to serve as Senator? Describe one situation where you participated in debate or decision-making for a group.'],
                ['key' => 'first_motion', 'label' => 'Propose one motion you would bring before the Senate in your first term. State the problem, motion, and implementers.'],
                ['key' => 'budget_oversight', 'label' => 'How would you approach budget oversight as Senator, and what questions would you ask before approving expenditure?'],
                ['key' => 'conflicting_interests', 'label' => 'If a motion benefits one faculty but negatively affects another, how would you decide how to vote?'],
                ['key' => 'checking_executive', 'label' => 'If the President acts against the constitution or harms students, what steps would you take as Senator?'],
                ['key' => 'student_accountability', 'label' => 'How will you consult with and remain accountable to fellow students throughout your term?'],
                ['key' => 'time_management', 'label' => 'How will you manage academic responsibilities alongside Senate duties?'],
                ['key' => 'pressing_issues', 'label' => 'What are the three most pressing issues affecting students in your constituency or school, and how can Senate address them?'],
                ['key' => 'absent_senator', 'label' => 'A fellow Senator is consistently absent and not fulfilling duties. What would you do?'],
                ['key' => 'constitution_change', 'label' => 'If you could change or add one provision to the MUSO Constitution, what would it be and why?'],
                ['key' => 'senator_success', 'label' => 'By the end of your term, how will students know you performed your duties faithfully?'],
            ],
        ],

        'general' => [
            'label' => 'General Candidate Application Form',
            'subtitle' => 'Use this form for positions that are not President, Vice President, or Senator.',
            'nominator_count' => 0,
            'particulars' => $commonParticulars,
            'attachments' => $attachments,
            'questions' => [
                ['key' => 'manifesto', 'label' => 'Write your campaign manifesto.'],
                ['key' => 'reason', 'label' => 'Why do you want this position?'],
                ['key' => 'experience', 'label' => 'Mention your leadership or service experience.'],
            ],
        ],
    ];
}

function size_to_bytes(string $size): int
{
    $size = trim($size);

    if ($size === '') {
        return 0;
    }

    $unit = strtolower(substr($size, -1));
    $value = (float)$size;

    return match ($unit) {
        'g' => (int)($value * 1024 * 1024 * 1024),
        'm' => (int)($value * 1024 * 1024),
        'k' => (int)($value * 1024),
        default => (int)$value,
    };
}

function bytes_to_mb_label(int $bytes): string
{
    return number_format($bytes / (1024 * 1024), 1) . 'MB';
}

function candidate_form_type_from_title(string $title): string
{
    $title = strtoupper($title);

    if (
        strpos($title, 'VICE') !== false &&
        (
            strpos($title, 'PRESIDENT') !== false ||
            strpos($title, 'CHAIR') !== false
        )
    ) {
        return 'vice_president';
    }

    if (
        strpos($title, 'SENATOR') !== false ||
        strpos($title, 'SENATE') !== false
    ) {
        return 'senator';
    }

    if (
        strpos($title, 'PRESIDENT') !== false ||
        strpos($title, 'CHAIRPERSON') !== false ||
        strpos($title, 'CHAIR') !== false
    ) {
        return 'president';
    }

    return 'general';
}

function candidate_faculty_allowed(?string $allowedFacultyCode, string $studentFacultyCode): bool
{
    $allowedFacultyCode = strtoupper(trim((string)$allowedFacultyCode));
    $studentFacultyCode = strtoupper(trim($studentFacultyCode));

    if ($allowedFacultyCode === '') {
        return true;
    }

    return $studentFacultyCode !== '' && $studentFacultyCode === $allowedFacultyCode;
}

function candidate_year_allowed(?string $allowedYears, int $studentYear): bool
{
    $allowedYears = trim((string)$allowedYears);

    if ($allowedYears === '') {
        return true;
    }

    if ($studentYear <= 0) {
        return false;
    }

    $years = array_values(array_filter(array_map('trim', explode(',', $allowedYears))));

    return in_array((string)$studentYear, $years, true);
}

function candidate_position_allowed(array $position, array $user): bool
{
    $studentFacultyCode = strtoupper(trim((string)($user['faculty_code'] ?? '')));
    $studentYear = (int)($user['study_year'] ?? 0);

    return candidate_faculty_allowed($position['candidate_faculty_code'] ?? null, $studentFacultyCode)
        && candidate_year_allowed($position['candidate_years'] ?? null, $studentYear);
}

function eligibility_year_label_candidate(?string $years): string
{
    $years = trim((string)$years);

    if ($years === '') {
        return 'All years';
    }

    $items = array_filter(array_map('trim', explode(',', $years)));

    if (!$items) {
        return 'All years';
    }

    return 'Year ' . implode(', Year ', $items);
}

function eligibility_faculty_label_candidate(?string $facultyCode, array $faculties): string
{
    $facultyCode = strtoupper(trim((string)$facultyCode));

    if ($facultyCode === '') {
        return 'All faculties';
    }

    return ($faculties[$facultyCode] ?? $facultyCode) . ' (' . $facultyCode . ')';
}

function clean_form_answers($value)
{
    if (is_array($value)) {
        $clean = [];

        foreach ($value as $key => $item) {
            $clean[$key] = clean_form_answers($item);
        }

        return $clean;
    }

    return trim((string)$value);
}

function normalize_student_no(string $studentNo): string
{
    return strtoupper(preg_replace('/\s+/', '', trim($studentNo)));
}

function candidate_json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function candidate_csrf_token(): string
{
    if (empty($_SESSION['candidate_form_csrf'])) {
        $_SESSION['candidate_form_csrf'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['candidate_form_csrf'];
}

function candidate_require_csrf(): void
{
    $submitted = (string)($_POST['csrf_token'] ?? '');
    $stored = (string)($_SESSION['candidate_form_csrf'] ?? '');

    if ($submitted === '' || $stored === '' || !hash_equals($stored, $submitted)) {
        throw new RuntimeException('Your session token has expired. Refresh the page and try again.');
    }
}

function candidate_decode_answers(?string $json): array
{
    if (!$json) {
        return [];
    }

    $decoded = json_decode($json, true);

    return is_array($decoded) ? $decoded : [];
}

function candidate_load_available_position(PDO $pdo, int $positionId, int $electionId): array
{
    $stmt = $pdo->prepare(
        'SELECT
            p.*,
            e.title AS election_title,
            e.status AS election_status
         FROM positions p
         JOIN elections e ON e.id = p.election_id
         WHERE p.id = ?
         AND p.election_id = ?
         AND p.is_active = 1
         AND e.status IN ("draft", "open")
         LIMIT 1'
    );

    $stmt->execute([$positionId, $electionId]);
    $position = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$position) {
        throw new RuntimeException('Selected position is not available for candidate application.');
    }

    return $position;
}

function candidate_application_for_election(PDO $pdo, int $candidateUserId, int $electionId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT ca.*, p.title AS position_title
         FROM candidate_applications ca
         JOIN positions p ON p.id = ca.position_id
         WHERE ca.user_id = ?
         AND ca.election_id = ?
         LIMIT 1'
    );
    $stmt->execute([$candidateUserId, $electionId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    return $application ?: null;
}

function candidate_ensure_draft_application(
    PDO $pdo,
    int $candidateUserId,
    int $electionId,
    int $positionId,
    string $formType,
    array $formAnswers = []
): array {
    $existing = candidate_application_for_election($pdo, $candidateUserId, $electionId);

    if ($existing) {
        if (($existing['application_status'] ?? 'submitted') !== 'draft') {
            throw new RuntimeException(
                'You have already submitted an application for ' .
                ($existing['position_title'] ?? 'another position') .
                ' in this election.'
            );
        }

        if ((int)$existing['position_id'] !== $positionId) {
            throw new RuntimeException(
                'You already have a draft for ' .
                ($existing['position_title'] ?? 'another position') .
                '. Continue that draft or remove it before changing positions.'
            );
        }

        return $existing;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO candidate_applications
            (user_id, election_id, position_id, manifesto, reason, experience, photo, form_type, form_answers, application_status)
         VALUES
            (?, ?, ?, "", "", "", "", ?, ?, "draft")'
    );
    $stmt->execute([
        $candidateUserId,
        $electionId,
        $positionId,
        $formType,
        json_encode($formAnswers, JSON_UNESCAPED_UNICODE),
    ]);

    $applicationId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'SELECT ca.*, p.title AS position_title
         FROM candidate_applications ca
         JOIN positions p ON p.id = ca.position_id
         WHERE ca.id = ?
         LIMIT 1'
    );
    $stmt->execute([$applicationId]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function candidate_phone_column(PDO $pdo): ?string
{
    static $resolved = false;
    static $column = null;

    if ($resolved) {
        return $column;
    }

    $resolved = true;
    $available = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    $preferred = ['phone_number', 'phone', 'mobile_number', 'mobile', 'telephone'];

    foreach ($preferred as $candidate) {
        if (in_array($candidate, $available, true)) {
            $column = $candidate;
            break;
        }
    }

    return $column;
}

function candidate_find_active_nominator(PDO $pdo, string $studentNo): array
{
    $normalized = normalize_student_no($studentNo);
    $phoneColumn = candidate_phone_column($pdo);
    $phoneSelect = $phoneColumn ? ', `' . $phoneColumn . '` AS phone_number' : ', "" AS phone_number';

    $stmt = $pdo->prepare(
        "SELECT id, full_name, student_no {$phoneSelect}
         FROM users
         WHERE REPLACE(UPPER(student_no), ' ', '') = ?
         AND role = 'voter'
         AND is_active = 1
         LIMIT 1"
    );
    $stmt->execute([$normalized]);
    $nominator = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$nominator) {
        throw new RuntimeException('Student registration number was not found or the student account is inactive.');
    }

    if (trim((string)($nominator['phone_number'] ?? '')) === '') {
        throw new RuntimeException('This active student has no phone number in the system. Ask the administrator to update the student profile.');
    }

    return $nominator;
}

function candidate_list_nominators(PDO $pdo, int $applicationId): array
{
    $stmt = $pdo->prepare(
        'SELECT
            cn.id,
            cn.status,
            u.id AS user_id,
            u.full_name,
            u.student_no,
            u.is_active,
            u.role
         FROM candidate_nominators cn
         JOIN users u ON u.id = cn.nominator_user_id
         WHERE cn.application_id = ?
         ORDER BY cn.id ASC'
    );
    $stmt->execute([$applicationId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(
        static function (array $row): array {
            $row['valid'] = (int)$row['is_active'] === 1 && (string)$row['role'] === 'voter';
            return $row;
        },
        $rows
    );
}

function candidate_valid_nominator_count(PDO $pdo, int $applicationId): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(DISTINCT cn.nominator_user_id)
         FROM candidate_nominators cn
         JOIN users u ON u.id = cn.nominator_user_id
         WHERE cn.application_id = ?
         AND u.role = "voter"
         AND u.is_active = 1
         AND cn.status IN ("pending", "approved")'
    );
    $stmt->execute([$applicationId]);

    return (int)$stmt->fetchColumn();
}

function candidate_approved_nominator_count(PDO $pdo, int $applicationId): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(DISTINCT cn.nominator_user_id)
         FROM candidate_nominators cn
         JOIN users u ON u.id = cn.nominator_user_id
         WHERE cn.application_id = ?
         AND u.role = "voter"
         AND u.is_active = 1
         AND cn.status = "approved"'
    );
    $stmt->execute([$applicationId]);

    return (int)$stmt->fetchColumn();
}

function candidate_at_config_value(string $key, string $default = ''): string
{
    if (defined($key)) {
        return trim((string)constant($key));
    }

    $value = getenv($key);

    if ($value === false || trim((string)$value) === '') {
        return $default;
    }

    return trim((string)$value);
}

function candidate_africastalking_sms_endpoint(): string
{
    $environment = strtolower(candidate_at_config_value('AT_ENV', 'sandbox'));

    if ($environment === 'live' || $environment === 'production') {
        return 'https://api.africastalking.com/version1/messaging';
    }

    return 'https://api.sandbox.africastalking.com/version1/messaging';
}

function candidate_africastalking_ca_file(): string
{
    /*
    |--------------------------------------------------------------------------
    | WAMP SSL CA certificate
    |--------------------------------------------------------------------------
    | Make sure this file exists:
    | C:\wamp64\ssl\cacert.pem
    |
    | If your WAMP is installed in C:\wamp instead of C:\wamp64,
    | change this to: C:/wamp/ssl/cacert.pem
    */
    return 'C:/wamp64/ssl/cacert.pem';
}

function candidate_prepare_sms_environment(): void
{
    /*
    |--------------------------------------------------------------------------
    | Clear accidental proxy environment variables
    |--------------------------------------------------------------------------
    */
    putenv('HTTP_PROXY=');
    putenv('HTTPS_PROXY=');
    putenv('http_proxy=');
    putenv('https_proxy=');
}

function candidate_normalize_sms_phone(string $phone): ?string
{
    $phone = trim($phone);
    $phone = str_replace([' ', '-', '(', ')'], '', $phone);
    $phone = preg_replace('/[^0-9+]/', '', (string)$phone);

    if ($phone === '') {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Tanzania student numbers
    |--------------------------------------------------------------------------
    | Accept 712345678, 0712345678, 255712345678, or +255712345678.
    */
    if (substr($phone, 0, 4) === '+255') {
        $local = substr($phone, 4);

        if (preg_match('/^[67][0-9]{8}$/', $local)) {
            return '+255' . $local;
        }
    }

    if (substr($phone, 0, 3) === '255') {
        $local = substr($phone, 3);

        if (preg_match('/^[67][0-9]{8}$/', $local)) {
            return '+255' . $local;
        }
    }

    if (substr($phone, 0, 1) === '0') {
        $local = substr($phone, 1);

        if (preg_match('/^[67][0-9]{8}$/', $local)) {
            return '+255' . $local;
        }
    }

    if (preg_match('/^[67][0-9]{8}$/', $phone)) {
        return '+255' . $phone;
    }

    /*
    |--------------------------------------------------------------------------
    | International E.164 fallback
    |--------------------------------------------------------------------------
    | This helps if the Africa's Talking simulator is opened with +254...
    */
    if (preg_match('/^\+[1-9][0-9]{7,14}$/', $phone)) {
        return $phone;
    }

    return null;
}

function candidate_africastalking_response_success(array $decoded, int $statusCode): bool
{
    if ($statusCode < 200 || $statusCode >= 300) {
        return false;
    }

    $recipients = $decoded['SMSMessageData']['Recipients'] ?? null;

    if (!is_array($recipients) || count($recipients) === 0) {
        return true;
    }

    $firstRecipient = $recipients[0] ?? [];
    $recipientStatus = strtolower((string)($firstRecipient['status'] ?? ''));

    return in_array($recipientStatus, ['success', 'sent', 'submitted'], true);
}

function candidate_send_africastalking_sms(string $phone, string $message): array
{
    candidate_prepare_sms_environment();

    $username = candidate_at_config_value('AT_USERNAME', 'sandbox');
    $apiKey = candidate_at_config_value('AT_API_KEY');
    $senderId = candidate_at_config_value('AT_SENDER_ID');
    $caFile = candidate_africastalking_ca_file();
    $normalizedPhone = candidate_normalize_sms_phone($phone);

    if ($username === '' || $apiKey === '' || $apiKey === 'PASTE_YOUR_SANDBOX_API_KEY_HERE') {
        return [
            'ok' => false,
            'error' => 'Africa\'s Talking username or API key is not configured.',
        ];
    }

    if (!$normalizedPhone) {
        return [
            'ok' => false,
            'error' => 'Invalid SMS phone number: ' . $phone,
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'ok' => false,
            'error' => 'PHP cURL extension is not enabled in WAMP.',
        ];
    }

    if (!is_file($caFile)) {
        return [
            'ok' => false,
            'error' => 'CA certificate file not found: ' . $caFile,
        ];
    }

    $payload = [
        'username' => $username,
        'to' => $normalizedPhone,
        'message' => $message,
    ];

    /*
    |--------------------------------------------------------------------------
    | Sandbox sender ID
    |--------------------------------------------------------------------------
    | Leave AT_SENDER_ID empty unless Africa's Talking has configured one.
    */
    if ($senderId !== '') {
        $payload['from'] = $senderId;
    }

    $endpoint = candidate_africastalking_sms_endpoint();
    $debugFile = __DIR__ . '/../logs/candidate_sms_debug.log';
    $logsDir = dirname($debugFile);

    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0775, true);
    }

    $verbose = @fopen($debugFile, 'a');
    $ch = curl_init($endpoint);

    if (!$ch) {
        if (is_resource($verbose)) {
            fclose($verbose);
        }

        return [
            'ok' => false,
            'error' => 'Could not initialize cURL.',
        ];
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_HTTPHEADER => [
            'apiKey: ' . $apiKey,
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'Expect:',
            'Connection: close',
        ],

        CURLOPT_POSTFIELDS => http_build_query($payload),

        /*
        |--------------------------------------------------------------------------
        | Timeout
        |--------------------------------------------------------------------------
        | Keep this moderate so the AJAX add-nominator request does not hang
        | too long if the Sandbox endpoint delays.
        */
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,

        /*
        |--------------------------------------------------------------------------
        | WAMP SSL fix
        |--------------------------------------------------------------------------
        */
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CAINFO => $caFile,

        /*
        |--------------------------------------------------------------------------
        | Local Windows/WAMP stability options
        |--------------------------------------------------------------------------
        */
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_USERAGENT => 'MzumbeVotingSystem/1.0 PHP-cURL',
        CURLOPT_PROXY => '',
        CURLOPT_NOPROXY => '*',
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_FORBID_REUSE => true,
    ]);

    if (is_resource($verbose)) {
        fwrite($verbose, PHP_EOL . '===== Candidate SMS Request ' . date('Y-m-d H:i:s') . ' =====' . PHP_EOL);
        fwrite($verbose, 'To: ' . $normalizedPhone . PHP_EOL);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
    }

    $responseBody = curl_exec($ch);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $primaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $connectTime = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
    $sslTime = curl_getinfo($ch, CURLINFO_APPCONNECT_TIME);

    curl_close($ch);

    if (is_resource($verbose)) {
        fclose($verbose);
    }

    if ($responseBody === false) {
        return [
            'ok' => false,
            'error' => $curlError ?: 'Africa\'s Talking SMS request failed.',
            'curl_errno' => $curlErrno,
            'status' => $statusCode,
            'endpoint' => $endpoint,
            'to' => $normalizedPhone,
            'primary_ip' => $primaryIp,
            'connect_time' => $connectTime,
            'ssl_handshake_time' => $sslTime,
            'total_time' => $totalTime,
        ];
    }

    $decoded = json_decode((string)$responseBody, true);

    if (!is_array($decoded)) {
        $decoded = [];
    }

    return [
        'ok' => candidate_africastalking_response_success($decoded, $statusCode),
        'status' => $statusCode,
        'response' => $responseBody,
        'decoded' => $decoded,
        'endpoint' => $endpoint,
        'to' => $normalizedPhone,
        'primary_ip' => $primaryIp,
        'connect_time' => $connectTime,
        'ssl_handshake_time' => $sslTime,
        'total_time' => $totalTime,
    ];
}

function candidate_queue_sms(
    PDO $pdo,
    int $userId,
    string $phone,
    string $message
): int {
    $stmt = $pdo->prepare(
        'INSERT INTO sms_outbox
            (user_id, phone, message, status, attempts, created_at)
         VALUES
            (?, ?, ?, "pending", 0, NOW())'
    );
    $stmt->execute([$userId, $phone, $message]);

    return (int)$pdo->lastInsertId();
}

function candidate_dispatch_queued_sms(
    PDO $pdo,
    int $outboxId,
    string $phone,
    string $message
): string {
    try {
        $result = candidate_send_africastalking_sms($phone, $message);

        if (empty($result['ok'])) {
            $error = $result['error']
                ?? $result['response']
                ?? ('HTTP status: ' . ($result['status'] ?? 'unknown'));

            throw new RuntimeException((string)$error);
        }

        $stmt = $pdo->prepare(
            'UPDATE sms_outbox
             SET status = "sent", attempts = attempts + 1, sent_at = NOW(), last_error = NULL
             WHERE id = ?'
        );
        $stmt->execute([$outboxId]);

        return 'sent';
    } catch (Throwable $e) {
        $stmt = $pdo->prepare(
            'UPDATE sms_outbox
             SET attempts = attempts + 1, last_error = ?
             WHERE id = ?'
        );
        $stmt->execute([substr($e->getMessage(), 0, 1000), $outboxId]);

        error_log('Candidate nominator SMS failed for outbox #' . $outboxId . ': ' . $e->getMessage());

        return 'queued';
    }
}

function candidate_nominators_for_archive(PDO $pdo, int $applicationId): array
{
    return array_map(
        static fn (array $row): array => [
            'full_name' => (string)$row['full_name'],
            'student_no' => (string)$row['student_no'],
            'status' => (string)$row['status'],
            'valid' => (bool)$row['valid'],
        ],
        candidate_list_nominators($pdo, $applicationId)
    );
}

function candidate_attachment_file_from_files(array $files, string $key): array
{
    return [
        'name' => $files['name'][$key] ?? '',
        'type' => $files['type'][$key] ?? '',
        'tmp_name' => $files['tmp_name'][$key] ?? '',
        'error' => $files['error'][$key] ?? UPLOAD_ERR_NO_FILE,
        'size' => $files['size'][$key] ?? 0,
    ];
}

function upload_candidate_required_attachment(array $file, string $key, string $label): string
{
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException($label . ' exceeds the allowed upload size.');
        }

        throw new RuntimeException($label . ' upload is required.');
    }

    $maxSize = $GLOBALS['pageSingleFileLimitBytes'] ?? (5 * 1024 * 1024);

    if ((int)($file['size'] ?? 0) > $maxSize) {
        throw new RuntimeException($label . ' must not exceed ' . bytes_to_mb_label((int)$maxSize) . '.');
    }

    $originalName = (string)($file['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException($label . ' must be PDF, DOC, DOCX, JPG, PNG, or WebP.');
    }

    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException($label . ' file type is not allowed.');
        }
    }

    $uploadDir = __DIR__ . '/../uploads/candidate_attachments';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $safeKey = preg_replace('/[^a-z0-9_]+/i', '_', $key);

    $filename =
        'attachment_' .
        (int)($_SESSION['user_id'] ?? 0) . '_' .
        time() . '_' .
        bin2hex(random_bytes(4)) . '_' .
        $safeKey . '.' .
        $extension;

    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not upload ' . $label . '.');
    }

    return 'uploads/candidate_attachments/' . $filename;
}

function upload_candidate_required_attachments(array $files, array $definition): array
{
    $uploaded = [];

    foreach ($definition['attachments'] as $attachment) {
        $key = $attachment['key'];
        $label = $attachment['label'];

        $file = candidate_attachment_file_from_files($files, $key);
        $uploaded[$key] = upload_candidate_required_attachment($file, $key, $label);
    }

    return $uploaded;
}

function upload_candidate_attachments_keeping_existing(
    array $files,
    array $definition,
    array $existingAttachments
): array {
    $uploaded = $existingAttachments;

    foreach ($definition['attachments'] as $attachment) {
        $key = $attachment['key'];
        $label = $attachment['label'];
        $file = candidate_attachment_file_from_files($files, $key);
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $uploaded[$key] = upload_candidate_required_attachment($file, $key, $label);
    }

    return $uploaded;
}

function validate_dynamic_candidate_form(array $answers, array $definition): void
{
    foreach ($definition['particulars'] as $field) {
        if (!empty($field['required'])) {
            $key = $field['key'];

            if (trim((string)($answers['particulars'][$key] ?? '')) === '') {
                throw new RuntimeException($field['label'] . ' is required.');
            }
        }
    }

    foreach ($definition['questions'] as $question) {
        $key = $question['key'];

        if (trim((string)($answers['questions'][$key] ?? '')) === '') {
            throw new RuntimeException('Please answer: ' . $question['label']);
        }
    }

    foreach ($definition['attachments'] as $attachment) {
        $key = $attachment['key'];

        if (trim((string)($answers['attachments'][$key] ?? '')) === '') {
            throw new RuntimeException('Please upload: ' . $attachment['label']);
        }
    }
}

function get_question_answer(array $answers, array $keys): string
{
    foreach ($keys as $key) {
        $value = trim((string)($answers['questions'][$key] ?? ''));

        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function build_manifesto_summary(array $answers, array $definition): string
{
    $lines = [];
    $lines[] = $definition['label'];

    foreach ($definition['questions'] as $question) {
        $key = $question['key'];
        $answer = trim((string)($answers['questions'][$key] ?? ''));

        if ($answer !== '') {
            $lines[] = $question['label'];
            $lines[] = $answer;
            $lines[] = '';
        }
    }

    return trim(implode("\n", $lines));
}

$faculties = faculty_options_for_candidate_page();
$formDefinitions = candidate_form_definitions();

$postMaxBytes = size_to_bytes((string)ini_get('post_max_size'));
$uploadMaxBytes = size_to_bytes((string)ini_get('upload_max_filesize'));

if ($postMaxBytes <= 0) {
    $postMaxBytes = 8 * 1024 * 1024;
}

if ($uploadMaxBytes <= 0) {
    $uploadMaxBytes = 2 * 1024 * 1024;
}

$pageTotalUploadLimitBytes = max(1024 * 1024, $postMaxBytes - (512 * 1024));
$pageSingleFileLimitBytes = min($uploadMaxBytes, 5 * 1024 * 1024);

$userFacultyCode = strtoupper(trim((string)($user['faculty_code'] ?? '')));
$userFacultyName = $faculties[$userFacultyCode] ?? ($userFacultyCode !== '' ? $userFacultyCode : 'Not assigned');
$userStudyYear = (int)($user['study_year'] ?? 0);

$candidateCsrfToken = candidate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = (string)($_POST['form_action'] ?? 'submit_application');
    $isAjaxAction = str_starts_with($formAction, 'ajax_');

    if (
        !$isAjaxAction &&
        empty($_POST) &&
        empty($_FILES) &&
        isset($_SERVER['CONTENT_LENGTH']) &&
        (int)$_SERVER['CONTENT_LENGTH'] > 0
    ) {
        set_flash(
            'error',
            'Your uploaded files are too large. Maximum total upload size allowed is about ' .
            bytes_to_mb_label($pageTotalUploadLimitBytes) .
            '. Please reduce file sizes and submit again.'
        );
    } else {
        $electionId = (int)($_POST['election_id'] ?? 0);
        $positionId = (int)($_POST['position_id'] ?? 0);
        $rawFormAnswers = $_POST['form_answers'] ?? [];
        $formAnswers = clean_form_answers(is_array($rawFormAnswers) ? $rawFormAnswers : []);

        try {
            candidate_require_csrf();

            if ($electionId <= 0 || $positionId <= 0) {
                throw new RuntimeException('Election and position are required.');
            }

            $selectedPosition = candidate_load_available_position($pdo, $positionId, $electionId);

            if (!candidate_position_allowed($selectedPosition, $user)) {
                throw new RuntimeException('You are not eligible to apply for this position based on faculty or study year rules.');
            }

            $formType = candidate_form_type_from_title((string)$selectedPosition['title']);
            $definition = $formDefinitions[$formType] ?? $formDefinitions['general'];
            $requiredNominators = (int)($definition['nominator_count'] ?? 0);
            $candidateUserId = (int)$_SESSION['user_id'];

            if ($isAjaxAction) {
                if ($formAction === 'ajax_list_nominators') {
                    $application = candidate_application_for_election($pdo, $candidateUserId, $electionId);

                    if (!$application || (int)$application['position_id'] !== $positionId) {
                        candidate_json_response([
                            'success' => true,
                            'required' => $requiredNominators,
                            'count' => 0,
                            'approved_count' => 0,
                            'nominators' => [],
                        ]);
                    }

                    $nominators = candidate_list_nominators($pdo, (int)$application['id']);

                    candidate_json_response([
                        'success' => true,
                        'required' => $requiredNominators,
                        'count' => candidate_valid_nominator_count($pdo, (int)$application['id']),
                        'approved_count' => candidate_approved_nominator_count($pdo, (int)$application['id']),
                        'nominators' => $nominators,
                    ]);
                }

                if ($formAction === 'ajax_add_nominator') {
                    $studentNo = trim((string)($_POST['nominator_student_no'] ?? ''));

                    if ($studentNo === '') {
                        throw new RuntimeException('Enter the nominator registration number.');
                    }

                    $pdo->beginTransaction();

                    $application = candidate_ensure_draft_application(
                        $pdo,
                        $candidateUserId,
                        $electionId,
                        $positionId,
                        $formType,
                        $formAnswers
                    );

                    $existingAnswers = candidate_decode_answers($application['form_answers'] ?? null);
                    $formAnswers['attachments'] = $existingAnswers['attachments'] ?? [];
                    $formAnswers['draft_saved_at'] = date('c');

                    $stmt = $pdo->prepare(
                        'UPDATE candidate_applications
                         SET form_answers = ?, form_type = ?, updated_at = NOW()
                         WHERE id = ? AND application_status = "draft"'
                    );
                    $stmt->execute([
                        json_encode($formAnswers, JSON_UNESCAPED_UNICODE),
                        $formType,
                        (int)$application['id'],
                    ]);

                    if (candidate_valid_nominator_count($pdo, (int)$application['id']) >= $requiredNominators) {
                        throw new RuntimeException('The required number of nominators has already been reached.');
                    }

                    $nominator = candidate_find_active_nominator($pdo, $studentNo);
                    $nominatorId = (int)$nominator['id'];

                    if ($nominatorId === $candidateUserId) {
                        throw new RuntimeException('You cannot nominate yourself.');
                    }

                    $stmt = $pdo->prepare(
                        'SELECT id
                         FROM candidate_nominators
                         WHERE application_id = ?
                         AND nominator_user_id = ?
                         LIMIT 1'
                    );
                    $stmt->execute([(int)$application['id'], $nominatorId]);

                    if ($stmt->fetchColumn()) {
                        throw new RuntimeException('This student has already been added as a nominator.');
                    }

                    $stmt = $pdo->prepare(
                        'INSERT INTO candidate_nominators
                            (application_id, candidate_user_id, nominator_user_id, status)
                         VALUES
                            (?, ?, ?, "pending")'
                    );
                    $stmt->execute([
                        (int)$application['id'],
                        $candidateUserId,
                        $nominatorId,
                    ]);

                    $message =
                        'Hello ' . (string)$nominator['full_name'] . ', ' .
                        (string)($user['full_name'] ?? 'a candidate') .
                        ' has listed you as a nominator for ' .
                        (string)$selectedPosition['title'] .
                        '. Please log in to the MUSO system to approve or reject the request.';

                    $outboxId = candidate_queue_sms(
                        $pdo,
                        $nominatorId,
                        trim((string)$nominator['phone_number']),
                        $message
                    );

                    $pdo->commit();

                    $delivery = candidate_dispatch_queued_sms(
                        $pdo,
                        $outboxId,
                        trim((string)$nominator['phone_number']),
                        $message
                    );

                    $nominators = candidate_list_nominators($pdo, (int)$application['id']);

                    candidate_json_response([
                        'success' => true,
                        'message' => $delivery === 'sent'
                            ? 'Nominator verified, added, and SMS sent.'
                            : 'Nominator verified and added. SMS is queued for delivery.',
                        'required' => $requiredNominators,
                        'count' => candidate_valid_nominator_count($pdo, (int)$application['id']),
                        'approved_count' => candidate_approved_nominator_count($pdo, (int)$application['id']),
                        'nominators' => $nominators,
                    ]);
                }

                if ($formAction === 'ajax_remove_nominator') {
                    $nominationId = (int)($_POST['nomination_id'] ?? 0);

                    if ($nominationId <= 0) {
                        throw new RuntimeException('Invalid nomination request.');
                    }

                    $application = candidate_application_for_election($pdo, $candidateUserId, $electionId);

                    if (
                        !$application ||
                        (int)$application['position_id'] !== $positionId ||
                        ($application['application_status'] ?? '') !== 'draft'
                    ) {
                        throw new RuntimeException('The draft application was not found.');
                    }

                    $stmt = $pdo->prepare(
                        'DELETE FROM candidate_nominators
                         WHERE id = ?
                         AND application_id = ?
                         AND candidate_user_id = ?'
                    );
                    $stmt->execute([
                        $nominationId,
                        (int)$application['id'],
                        $candidateUserId,
                    ]);

                    $nominators = candidate_list_nominators($pdo, (int)$application['id']);

                    candidate_json_response([
                        'success' => true,
                        'message' => 'Nominator removed from the draft.',
                        'required' => $requiredNominators,
                        'count' => candidate_valid_nominator_count($pdo, (int)$application['id']),
                        'approved_count' => candidate_approved_nominator_count($pdo, (int)$application['id']),
                        'nominators' => $nominators,
                    ]);
                }

                throw new RuntimeException('Unsupported request.');
            }

            $application = candidate_ensure_draft_application(
                $pdo,
                $candidateUserId,
                $electionId,
                $positionId,
                $formType,
                $formAnswers
            );

            $existingAnswers = candidate_decode_answers($application['form_answers'] ?? null);
            $existingAttachments = is_array($existingAnswers['attachments'] ?? null)
                ? $existingAnswers['attachments']
                : [];

            $formAnswers['attachments'] = upload_candidate_attachments_keeping_existing(
                $_FILES['form_attachments'] ?? [],
                $definition,
                $existingAttachments
            );

            $photoPath = trim((string)($application['photo'] ?? ''));

            if (
                isset($_FILES['photo']) &&
                (int)($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
            ) {
                $photoPath = upload_candidate_photo($_FILES['photo']);
            }

            $manifesto = build_manifesto_summary($formAnswers, $definition);
            $reason = get_question_answer($formAnswers, [
                'why_president',
                'why_vice',
                'why_senator',
                'reason',
            ]);
            $experience = get_question_answer($formAnswers, [
                'why_president',
                'why_vice',
                'why_senator',
                'experience',
            ]);

            if ($formAction === 'save_draft') {
                $formAnswers['draft_saved_at'] = date('c');

                $stmt = $pdo->prepare(
                    'UPDATE candidate_applications
                     SET
                        position_id = ?,
                        manifesto = ?,
                        reason = ?,
                        experience = ?,
                        photo = ?,
                        form_type = ?,
                        form_answers = ?,
                        application_status = "draft",
                        updated_at = NOW()
                     WHERE id = ?'
                );
                $stmt->execute([
                    $positionId,
                    $manifesto,
                    $reason,
                    $experience,
                    $photoPath,
                    $formType,
                    json_encode($formAnswers, JSON_UNESCAPED_UNICODE),
                    (int)$application['id'],
                ]);

                log_activity(
                    $pdo,
                    $candidateUserId,
                    'save_candidate_draft',
                    'candidate_applications',
                    (int)$application['id']
                );

                set_flash('success', 'Draft saved. You can continue the application and add nominators later.');

                $path = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '/';
                header('Location: ' . $path);
                exit;
            }

            if ($formAction !== 'submit_application') {
                throw new RuntimeException('Unsupported form action.');
            }

            // Persist the latest work before final validation so a validation
            // error does not discard newly uploaded files or typed answers.
            $formAnswers['draft_saved_at'] = date('c');

            $stmt = $pdo->prepare(
                'UPDATE candidate_applications
                 SET
                    manifesto = ?,
                    reason = ?,
                    experience = ?,
                    photo = ?,
                    form_type = ?,
                    form_answers = ?,
                    application_status = "draft",
                    updated_at = NOW()
                 WHERE id = ?'
            );
            $stmt->execute([
                $manifesto,
                $reason,
                $experience,
                $photoPath,
                $formType,
                json_encode($formAnswers, JSON_UNESCAPED_UNICODE),
                (int)$application['id'],
            ]);

            if ($photoPath === '') {
                throw new RuntimeException('Candidate photo is required.');
            }

            validate_dynamic_candidate_form($formAnswers, $definition);

            $validNominatorCount = candidate_valid_nominator_count($pdo, (int)$application['id']);
            $approvedNominatorCount = candidate_approved_nominator_count($pdo, (int)$application['id']);

            if ($approvedNominatorCount < $requiredNominators) {
                throw new RuntimeException(
                    'Only ' . $approvedNominatorCount . ' of ' . $requiredNominators .
                    ' required nominators have approved. You currently have ' .
                    $validNominatorCount . ' active pending/approved requests.'
                );
            }

            $formAnswers['nominators_resolved'] = candidate_nominators_for_archive(
                $pdo,
                (int)$application['id']
            );
            $formAnswers['submitted_at'] = date('c');

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'UPDATE candidate_applications
                 SET
                    manifesto = ?,
                    reason = ?,
                    experience = ?,
                    photo = ?,
                    form_type = ?,
                    form_answers = ?,
                    application_status = "submitted",
                    submitted_at = NOW(),
                    updated_at = NOW()
                 WHERE id = ?
                 AND application_status = "draft"'
            );
            $stmt->execute([
                $manifesto,
                $reason,
                $experience,
                $photoPath,
                $formType,
                json_encode($formAnswers, JSON_UNESCAPED_UNICODE),
                (int)$application['id'],
            ]);

            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException('The application could not be submitted because its status changed.');
            }

            log_activity(
                $pdo,
                $candidateUserId,
                'apply_candidate',
                'candidate_applications',
                (int)$application['id']
            );

            $pdo->commit();

            set_flash('success', 'Application submitted. Nominators can continue approving their nomination requests.');
            redirect('voter/application_status.php');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($isAjaxAction) {
                candidate_json_response([
                    'success' => false,
                    'message' => 'The request could not be completed. Confirm that the database migration has been applied.',
                ], 422);
            }

            set_flash('error', 'Application could not be saved. Confirm that the database migration has been applied.');
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($isAjaxAction) {
                candidate_json_response([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            set_flash('error', $e->getMessage());
        }
    }
}

$elections = $pdo->query(
    "SELECT *
     FROM elections
     WHERE status IN ('draft', 'open')
     ORDER BY created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$positionsStmt = $pdo->query(
    'SELECT
        p.*,
        e.title AS election_title,
        e.status AS election_status
     FROM positions p
     JOIN elections e ON p.election_id = e.id
     WHERE p.is_active = 1
     AND e.status IN ("draft", "open")
     ORDER BY e.title ASC, p.title ASC'
);

$allPositions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

$eligiblePositions = [];

foreach ($allPositions as $position) {
    if (candidate_position_allowed($position, $user)) {
        $eligiblePositions[] = $position;
    }
}

$applicationStmt = $pdo->prepare(
    'SELECT
        ca.id,
        ca.election_id,
        ca.position_id,
        ca.application_status,
        ca.form_answers,
        ca.photo,
        ca.updated_at,
        p.title AS position_title
     FROM candidate_applications ca
     JOIN positions p ON p.id = ca.position_id
     WHERE ca.user_id = ?
     ORDER BY ca.updated_at DESC, ca.id DESC'
);
$applicationStmt->execute([(int)$_SESSION['user_id']]);
$existingApplications = $applicationStmt->fetchAll(PDO::FETCH_ASSOC);

$submittedElectionKeys = [];
$submittedElectionTitles = [];
$draftByElection = [];
$latestDraft = null;

foreach ($existingApplications as $application) {
    $electionKey = (int)$application['election_id'];
    $status = (string)($application['application_status'] ?? 'submitted');

    if ($status === 'draft') {
        $draftByElection[$electionKey] = $application;

        if ($latestDraft === null) {
            $latestDraft = $application;
        }

        continue;
    }

    $submittedElectionKeys[$electionKey] = true;
    $submittedElectionTitles[$electionKey] = (string)($application['position_title'] ?? 'another position');
}

$positionsForJs = [];

foreach ($eligiblePositions as $position) {
    $electionKey = (int)$position['election_id'];
    $formType = candidate_form_type_from_title((string)$position['title']);
    $definition = $formDefinitions[$formType] ?? $formDefinitions['general'];
    $draft = $draftByElection[$electionKey] ?? null;
    $isCurrentDraftPosition = $draft && (int)$draft['position_id'] === (int)$position['id'];
    $hasDifferentDraft = $draft && !$isCurrentDraftPosition;

    $positionsForJs[] = [
        'id' => (int)$position['id'],
        'election_id' => (int)$position['election_id'],
        'election_title' => (string)$position['election_title'],
        'title' => (string)$position['title'],
        'description' => (string)($position['description'] ?? ''),
        'candidate_faculty' => eligibility_faculty_label_candidate($position['candidate_faculty_code'] ?? null, $faculties),
        'candidate_years' => eligibility_year_label_candidate($position['candidate_years'] ?? null),
        'already_applied' => isset($submittedElectionKeys[$electionKey]),
        'already_applied_position' => $submittedElectionTitles[$electionKey] ?? '',
        'has_draft' => (bool)$isCurrentDraftPosition,
        'blocked_by_draft' => (bool)$hasDifferentDraft,
        'draft_position_title' => $draft['position_title'] ?? '',
        'form_type' => $formType,
        'form_label' => $definition['label'],
    ];
}

$postHasSelection = isset($_POST['election_id']) || isset($_POST['position_id']);

if ($postHasSelection) {
    $oldElectionId = (int)($_POST['election_id'] ?? 0);
    $oldPositionId = (int)($_POST['position_id'] ?? 0);
    $oldFormAnswers = clean_form_answers($_POST['form_answers'] ?? []);
    $oldPhotoPath = '';

    $matchingDraft = $draftByElection[$oldElectionId] ?? null;

    if ($matchingDraft && (int)$matchingDraft['position_id'] === $oldPositionId) {
        $savedDraftAnswers = candidate_decode_answers($matchingDraft['form_answers'] ?? null);

        if (is_array($savedDraftAnswers['attachments'] ?? null)) {
            $oldFormAnswers['attachments'] = $savedDraftAnswers['attachments'];
        }

        $oldPhotoPath = (string)($matchingDraft['photo'] ?? '');
    }
} elseif ($latestDraft) {
    $oldElectionId = (int)$latestDraft['election_id'];
    $oldPositionId = (int)$latestDraft['position_id'];
    $oldFormAnswers = candidate_decode_answers($latestDraft['form_answers'] ?? null);
    $oldPhotoPath = (string)($latestDraft['photo'] ?? '');
} else {
    $oldElectionId = 0;
    $oldPositionId = 0;
    $oldFormAnswers = [];
    $oldPhotoPath = '';
}

$oldInputForJs = [
    'election_id' => $oldElectionId,
    'position_id' => $oldPositionId,
    'form_answers' => $oldFormAnswers,
    'photo' => $oldPhotoPath,
];

$page_title = 'Apply as Candidate';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .candidate-apply-page {
        --primary: #2563eb;
        --ink: #0f172a;
        --muted: #64748b;
        --line: #e2e8f0;
        --soft: #f8fafc;
        color: var(--ink);
    }

    .candidate-hero {
        padding: 1.6rem;
        border-radius: 22px;
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 58%, #38bdf8 100%);
        color: #fff;
        margin-bottom: 1.2rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .14);
    }

    .candidate-hero h1 {
        margin: 0;
        font-size: clamp(1.5rem, 4vw, 2.4rem);
        letter-spacing: -.03em;
    }

    .candidate-hero p {
        margin: .55rem 0 0;
        color: rgba(255, 255, 255, .82);
        line-height: 1.55;
    }

    .candidate-grid {
        display: grid;
        grid-template-columns: minmax(0, .72fr) minmax(0, 1.28fr);
        gap: 1rem;
        align-items: start;
    }

    .candidate-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 20px;
        padding: 1.35rem;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .06);
    }

    .candidate-card h2 {
        margin: 0 0 .75rem;
        font-size: 1.15rem;
    }

    .profile-list {
        display: grid;
        gap: .7rem;
    }

    .profile-item {
        padding: .75rem;
        border-radius: 14px;
        background: var(--soft);
        border: 1px solid var(--line);
    }

    .profile-item span {
        display: block;
        color: var(--muted);
        font-size: .8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .2rem;
    }

    .profile-item strong {
        display: block;
        color: var(--ink);
        overflow-wrap: anywhere;
    }

    .candidate-apply-page label {
        display: block;
        margin: .9rem 0 .35rem;
        font-size: .88rem;
        font-weight: 900;
        color: #334155;
    }

    .candidate-apply-page input,
    .candidate-apply-page select,
    .candidate-apply-page textarea {
        width: 100%;
        min-height: 46px;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: .75rem .9rem;
        outline: none;
        color: var(--ink);
        background: #fff;
    }

    .candidate-apply-page textarea {
        min-height: 125px;
        resize: vertical;
    }

    .candidate-apply-page input:focus,
    .candidate-apply-page select:focus,
    .candidate-apply-page textarea:focus {
        border-color: rgba(37, 99, 235, .55);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .position-info-box {
        display: none;
        margin-top: .8rem;
        padding: .9rem;
        border-radius: 16px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #334155;
        line-height: 1.5;
        font-size: .9rem;
    }

    .position-info-box.is-visible,
    .dynamic-form-area.is-visible {
        display: block;
    }

    .position-info-box strong {
        color: #1e40af;
    }

    .dynamic-form-area {
        display: none;
        margin-top: 1rem;
    }

    .form-section-box {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 18px;
        border: 1px dashed #93c5fd;
        background: linear-gradient(180deg, #eff6ff, #fff);
    }

    .form-section-box h3 {
        margin: 0 0 .35rem;
        font-size: 1.05rem;
    }

    .form-section-box p {
        margin: 0;
        color: var(--muted);
        font-size: .9rem;
        line-height: 1.45;
    }

    .form-grid-two,
    .attachment-upload-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .85rem;
    }

    .question-card {
        margin-top: .85rem;
    }

    .question-card label {
        line-height: 1.45;
    }

    .attachment-upload-grid {
        gap: .75rem;
        margin-top: .8rem;
    }

    .attachment-upload-card {
        padding: .8rem;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #fff;
    }

    .attachment-upload-card label {
        margin-top: 0 !important;
    }

    .attachment-upload-card span {
        display: block;
        margin-top: .25rem;
        color: var(--muted);
        font-size: .78rem;
        line-height: 1.35;
    }

    .attachment-upload-card input[type="file"] {
        margin-top: .55rem;
        padding: .65rem;
        min-height: auto;
    }

    .photo-upload-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 180px;
        gap: 1rem;
        align-items: start;
    }

    .photo-preview-box {
        min-height: 190px;
        border: 2px dashed #93c5fd;
        border-radius: 18px;
        background: linear-gradient(180deg, #eff6ff, #fff);
        display: grid;
        place-items: center;
        overflow: hidden;
        padding: .6rem;
    }

    .photo-preview-placeholder {
        text-align: center;
        color: #64748b;
        font-size: .85rem;
        line-height: 1.4;
    }

    .photo-preview-placeholder i {
        display: block;
        font-size: 2rem;
        color: #2563eb;
        margin-bottom: .45rem;
    }

    #candidatePhotoPreview {
        width: 100%;
        height: 170px;
        object-fit: cover;
        border-radius: 14px;
        display: none;
    }

    #candidatePhotoPreview.is-visible {
        display: block;
    }

    .nominator-grid {
        display: grid;
        grid-template-columns: 60px 1fr;
        gap: .5rem;
        align-items: center;
        margin-top: .6rem;
    }

    .nominator-head {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 900;
        text-transform: uppercase;
    }

    details.nominator-details {
        margin-top: 1rem;
        border-radius: 16px;
        border: 1px solid var(--line);
        background: #fff;
        padding: .85rem;
    }

    details.nominator-details summary {
        cursor: pointer;
        font-weight: 900;
        color: #1e40af;
    }

    .nominator-entry-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .7rem;
        align-items: end;
        margin-top: .9rem;
    }

    .nominator-entry-row label {
        margin-top: 0;
    }

    .nominator-entry-row button,
    .draft-btn,
    .remove-nominator-btn {
        border: 0;
        border-radius: 999px;
        font-weight: 900;
        cursor: pointer;
    }

    .nominator-entry-row button {
        min-height: 46px;
        padding: .75rem 1rem;
        background: #1d4ed8;
        color: #fff;
        white-space: nowrap;
    }

    .nominator-progress {
        margin-top: .85rem;
        padding: .75rem .85rem;
        border-radius: 14px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        font-weight: 800;
        color: #1e3a8a;
    }

    .nominator-feedback {
        display: none;
        margin-top: .75rem;
        padding: .7rem .8rem;
        border-radius: 12px;
        font-size: .88rem;
        line-height: 1.4;
    }

    .nominator-feedback.is-visible {
        display: block;
    }

    .nominator-feedback.is-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .nominator-feedback.is-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .nominator-list {
        display: grid;
        gap: .65rem;
        margin-top: .85rem;
    }

    .nominator-list-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .75rem;
        align-items: center;
        padding: .8rem;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #fff;
    }

    .nominator-list-item strong,
    .nominator-list-item span {
        display: block;
    }

    .nominator-list-item span {
        margin-top: .15rem;
        color: var(--muted);
        font-size: .82rem;
    }

    .nominator-status {
        display: inline-flex !important;
        width: fit-content;
        margin-top: .35rem !important;
        padding: .22rem .5rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155 !important;
        font-weight: 800;
        text-transform: capitalize;
    }

    .remove-nominator-btn {
        padding: .55rem .75rem;
        background: #fee2e2;
        color: #991b1b;
    }

    .form-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
        margin-top: 1rem;
    }

    .form-action-row .submit-btn {
        margin-top: 0;
    }

    .draft-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 44px;
        padding: .8rem 1.15rem;
        background: #e2e8f0;
        color: #0f172a;
    }

    .small-note {
        margin: .45rem 0 0;
        color: var(--muted);
        font-size: .86rem;
        line-height: 1.45;
    }

    .warning-note {
        margin-top: .85rem;
        padding: .8rem;
        border-radius: 14px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: .88rem;
        line-height: 1.45;
    }

    .submit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 44px;
        margin-top: 1rem;
        border: 0;
        border-radius: 999px;
        padding: .8rem 1.15rem;
        background: linear-gradient(135deg, var(--primary), #38bdf8);
        color: #fff;
        font-weight: 900;
        cursor: pointer;
    }

    .submit-btn:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    @media (max-width: 900px) {
        .candidate-grid,
        .form-grid-two,
        .attachment-upload-grid,
        .photo-upload-grid,
        .nominator-grid,
        .nominator-entry-row,
        .nominator-list-item {
            grid-template-columns: 1fr;
        }

        .nominator-head {
            display: none;
        }
    }
</style>

<div class="candidate-apply-page">
    <section class="candidate-hero">
        <h1>Apply as Candidate</h1>
        <p>Select an election and position. The correct MUSO application form will open automatically.</p>
    </section>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="candidate-grid">
        <aside class="candidate-card">
            <h2>Applicant Details</h2>

            <div class="profile-list">
                <div class="profile-item">
                    <span>Full Name</span>
                    <strong><?= e($user['full_name'] ?? '') ?></strong>
                </div>

                <div class="profile-item">
                    <span>Student Number</span>
                    <strong><?= e($user['student_no'] ?? '') ?></strong>
                </div>

                <div class="profile-item">
                    <span>Faculty</span>
                    <strong><?= e($userFacultyName) ?></strong>
                </div>

                <div class="profile-item">
                    <span>Study Year</span>
                    <strong><?= $userStudyYear > 0 ? 'Year ' . $userStudyYear : 'Not assigned' ?></strong>
                </div>
            </div>

            <?php if ($userFacultyCode === '' || $userStudyYear <= 0): ?>
                <div class="warning-note">
                    Your faculty or study year is missing. Some positions may not appear until admin updates your student profile.
                </div>
            <?php endif; ?>

            <?php if (!$eligiblePositions): ?>
                <div class="warning-note">
                    No eligible candidate positions are currently available for your faculty and study year.
                </div>
            <?php endif; ?>
        </aside>

        <main class="candidate-card">
            <h2>Candidate Application Form</h2>

            <form method="POST" enctype="multipart/form-data" id="candidateApplicationForm">
                <input type="hidden" name="csrf_token" value="<?= e($candidateCsrfToken) ?>">
                <label for="election_id">Election</label>
                <select id="election_id" name="election_id" required>
                    <option value="">-- Select election --</option>
                    <?php foreach ($elections as $election): ?>
                        <option value="<?= (int)$election['id'] ?>" <?= $oldElectionId === (int)$election['id'] ? 'selected' : '' ?>>
                            <?= e($election['title']) ?> (<?= e($election['status']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="position_id">Position</label>
                <select id="position_id" name="position_id" required disabled>
                    <option value="">-- Select election first --</option>
                </select>

                <p class="small-note" id="positionHelpText">
                    Only positions you are eligible to contest will appear.
                </p>

                <div id="positionInfoBox" class="position-info-box"></div>

                <div id="dynamicFormArea" class="dynamic-form-area"></div>

                <div class="form-section-box">
                    <h3>Candidate Photo</h3>
                    <p>Upload a clear passport-size candidate photo. A preview will appear before submission.</p>

                    <div class="photo-upload-grid">
                        <div>
                            <label for="photo">Candidate Photo</label>
                            <input
                                id="photo"
                                type="file"
                                name="photo"
                                accept="image/jpeg,image/png,image/webp"
                                <?= $oldPhotoPath === '' ? 'required' : '' ?>
                            >

                            <p class="small-note">
                                Accepted photo formats: JPEG, PNG, or WebP. You must reselect the photo if the form returns with an error.
                            </p>
                        </div>

                        <div class="photo-preview-box">
                            <div id="photoPreviewPlaceholder" class="photo-preview-placeholder">
                                <i class="fas fa-image"></i>
                                Photo preview will appear here.
                            </div>

                            <img id="candidatePhotoPreview" src="" alt="Candidate photo preview">
                        </div>
                    </div>
                </div>

                <div class="form-action-row">
                    <button
                        type="submit"
                        name="form_action"
                        value="save_draft"
                        class="draft-btn"
                        formnovalidate
                        <?= !$eligiblePositions ? 'disabled' : '' ?>
                    >
                        <i class="fas fa-save"></i>
                        Save Draft
                    </button>

                    <button
                        type="submit"
                        name="form_action"
                        value="submit_application"
                        class="submit-btn"
                        <?= !$eligiblePositions ? 'disabled' : '' ?>
                    >
                        <i class="fas fa-paper-plane"></i>
                        Submit Application
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
const ELIGIBLE_POSITIONS = <?= json_encode($positionsForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const FORM_DEFINITIONS = <?= json_encode($formDefinitions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const OLD_INPUT = <?= json_encode($oldInputForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const PAGE_TOTAL_UPLOAD_LIMIT_BYTES = <?= (int)$pageTotalUploadLimitBytes ?>;
const PAGE_SINGLE_FILE_LIMIT_BYTES = <?= (int)$pageSingleFileLimitBytes ?>;

const electionSelect = document.getElementById('election_id');
const positionSelect = document.getElementById('position_id');
const positionHelpText = document.getElementById('positionHelpText');
const positionInfoBox = document.getElementById('positionInfoBox');
const dynamicFormArea = document.getElementById('dynamicFormArea');

const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('candidatePhotoPreview');
const photoPreviewPlaceholder = document.getElementById('photoPreviewPlaceholder');

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function bytesToMbLabel(bytes) {
    return (bytes / (1024 * 1024)).toFixed(1) + 'MB';
}

function oldValue(section, key, fallback = '') {
    if (
        OLD_INPUT &&
        OLD_INPUT.form_answers &&
        OLD_INPUT.form_answers[section] &&
        Object.prototype.hasOwnProperty.call(OLD_INPUT.form_answers[section], key)
    ) {
        return OLD_INPUT.form_answers[section][key];
    }

    return fallback;
}

function oldParticularValue(key) {
    return oldValue('particulars', key, '');
}

function oldQuestionValue(key) {
    return oldValue('questions', key, '');
}

function oldAttachmentValue(key) {
    return oldValue('attachments', key, '');
}

function hasExistingPhoto() {
    return Boolean(OLD_INPUT && OLD_INPUT.photo);
}

function resetPositionInfo() {
    positionInfoBox.classList.remove('is-visible');
    positionInfoBox.innerHTML = '';
}

function resetDynamicForm() {
    dynamicFormArea.classList.remove('is-visible');
    dynamicFormArea.innerHTML = '';
}

function getSelectedPosition() {
    const selectedPositionId = positionSelect.value;
    const electionId = electionSelect.value;

    if (!selectedPositionId || !electionId) {
        return null;
    }

    return ELIGIBLE_POSITIONS.find(item =>
        String(item.id) === String(selectedPositionId) &&
        String(item.election_id) === String(electionId)
    ) || null;
}

function renderPositionOptions() {
    const electionId = electionSelect.value;

    positionSelect.innerHTML = '';
    resetPositionInfo();
    resetDynamicForm();

    if (!electionId) {
        positionSelect.disabled = true;

        const option = document.createElement('option');
        option.value = '';
        option.textContent = '-- Select election first --';
        positionSelect.appendChild(option);

        positionHelpText.textContent = 'Only positions you are eligible to contest will appear.';
        return;
    }

    const positions = ELIGIBLE_POSITIONS.filter(
        position => String(position.election_id) === String(electionId)
    );

    if (!positions.length) {
        positionSelect.disabled = true;

        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No eligible positions for this election';
        positionSelect.appendChild(option);

        positionHelpText.textContent = 'You are not eligible for any position in the selected election.';
        return;
    }

    positionSelect.disabled = false;

    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = '-- Select position --';
    positionSelect.appendChild(defaultOption);

    positions.forEach(position => {
        const option = document.createElement('option');
        option.value = position.id;

        let suffix = '';

        if (position.already_applied) {
            suffix = ' — Already submitted for ' + (position.already_applied_position || 'another position');
        } else if (position.has_draft) {
            suffix = ' — Draft in progress';
        } else if (position.blocked_by_draft) {
            suffix = ' — Continue draft for ' + (position.draft_position_title || 'another position');
        }

        option.textContent = position.title + ' — ' + position.form_label + suffix;
        option.disabled = Boolean(position.already_applied || position.blocked_by_draft);
        positionSelect.appendChild(option);
    });

    const hasSubmitted = positions.some(position => Boolean(position.already_applied));
    const hasDraft = positions.some(position => Boolean(position.has_draft));

    if (hasSubmitted) {
        positionHelpText.textContent =
            'You have already submitted one application for this election.';
    } else if (hasDraft) {
        positionHelpText.textContent =
            'A saved draft was found. Continue filling it and add nominators one at a time.';
    } else {
        positionHelpText.textContent =
            'Select one eligible position. The correct application form will open automatically.';
    }
}

function renderSelectedPositionInfo() {
    const position = getSelectedPosition();

    resetPositionInfo();
    resetDynamicForm();

    if (!position) {
        return;
    }

    positionInfoBox.innerHTML =
        '<strong>' + escapeHtml(position.title) + '</strong><br>' +
        '<span>Form: ' + escapeHtml(position.form_label) + '</span><br>' +
        '<span>Candidate faculty: ' + escapeHtml(position.candidate_faculty) + '</span><br>' +
        '<span>Candidate years: ' + escapeHtml(position.candidate_years) + '</span>' +
        (position.description ? '<br><br><span>' + escapeHtml(position.description) + '</span>' : '');

    positionInfoBox.classList.add('is-visible');

    renderDynamicForm(position);
}

function renderField(field) {
    const safeKey = escapeHtml(field.key);
    const rawKey = String(field.key);
    const label = escapeHtml(field.label);
    const required = field.required ? 'required' : '';
    const type = field.type || 'text';
    const oldVal = oldParticularValue(rawKey);

    if (type === 'select') {
        let html = '<div><label for="particular_' + safeKey + '">' + label + '</label>';
        html += '<select id="particular_' + safeKey + '" name="form_answers[particulars][' + safeKey + ']" ' + required + '>';

        Object.entries(field.options || {}).forEach(([value, text]) => {
            const selected = String(value) === String(oldVal) ? 'selected' : '';
            html += '<option value="' + escapeHtml(value) + '" ' + selected + '>' + escapeHtml(text) + '</option>';
        });

        html += '</select></div>';

        return html;
    }

    return '<div>' +
        '<label for="particular_' + safeKey + '">' + label + '</label>' +
        '<input id="particular_' + safeKey + '" type="' + escapeHtml(type) + '" name="form_answers[particulars][' + safeKey + ']" value="' + escapeHtml(oldVal) + '" ' + required + '>' +
        '</div>';
}

function renderDynamicForm(position) {
    const definition = FORM_DEFINITIONS[position.form_type] || FORM_DEFINITIONS.general;

    let html = '';

    html += '<div class="form-section-box">';
    html += '<h3>' + escapeHtml(definition.label) + '</h3>';
    html += '<p>' + escapeHtml(definition.subtitle) + '</p>';
    html += '</div>';

    html += '<div class="form-section-box">';
    html += '<h3>A. Particulars of the Applicant</h3>';
    html += '<div class="form-grid-two">';

    (definition.particulars || []).forEach(field => {
        html += renderField(field);
    });

    html += '</div>';
    html += '</div>';

    html += '<div class="form-section-box">';
    html += '<h3>B. Leadership Questions & Vision Assessment</h3>';

    (definition.questions || []).forEach((question, index) => {
        const safeKey = escapeHtml(question.key);
        const oldAnswer = oldQuestionValue(question.key);

        html += '<div class="question-card">';
        html += '<label for="question_' + safeKey + '">' + (index + 1) + '. ' + escapeHtml(question.label) + '</label>';
        html += '<textarea id="question_' + safeKey + '" name="form_answers[questions][' + safeKey + ']" required>' + escapeHtml(oldAnswer) + '</textarea>';
        html += '</div>';
    });

    html += '</div>';

    html += '<div class="form-section-box">';
    html += '<h3>C. Required Attachments Checklist</h3>';
    html += '<p>Upload each required document before submitting your application.</p>';
    html += '<div class="attachment-upload-grid">';

    (definition.attachments || []).forEach(attachment => {
        const safeKey = escapeHtml(attachment.key);
        const label = escapeHtml(attachment.label);
        const existingFile = oldAttachmentValue(attachment.key);
        const requiredAttribute = existingFile ? '' : 'required';
        const note = existingFile
            ? 'Already uploaded in the draft. Select another file only to replace it.'
            : 'Required before final submission. You may leave it empty when saving a draft.';

        html += '<div class="attachment-upload-card">';
        html += '<label for="attachment_' + safeKey + '">' + label + '</label>';
        html += '<span>' + escapeHtml(note) + ' Max ' + bytesToMbLabel(PAGE_SINGLE_FILE_LIMIT_BYTES) + ' per file.</span>';
        html += '<input id="attachment_' + safeKey + '" type="file" name="form_attachments[' + safeKey + ']" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,image/webp" ' + requiredAttribute + '>';
        html += '</div>';
    });

    html += '</div>';
    html += '</div>';

    if ((definition.nominator_count || 0) > 0) {
        html += '<div class="form-section-box">';
        html += '<h3>D. Nominators</h3>';
        html += '<p>Add one student at a time. The system checks that the account is an active voter, prevents duplicates, saves the draft, and sends or queues an SMS notification.</p>';

        html += '<div class="nominator-entry-row">';
        html += '<div>';
        html += '<label for="nominatorStudentNo">Student Registration Number</label>';
        html += '<input id="nominatorStudentNo" type="text" placeholder="Enter one registration number">';
        html += '</div>';
        html += '<button type="button" id="addNominatorBtn"><i class="fas fa-user-check"></i> Verify & Add</button>';
        html += '</div>';

        html += '<div id="nominatorFeedback" class="nominator-feedback"></div>';
        html += '<div class="nominator-progress">';
        html += '<span id="nominatorCount">0</span> added; <span id="nominatorApprovedCount">0</span> of ' + Number(definition.nominator_count || 0) + ' approved';
        html += '</div>';
        html += '<div id="nominatorList" class="nominator-list">';
        html += '<div class="small-note">Loading saved nominators...</div>';
        html += '</div>';
        html += '</div>';
    }

    dynamicFormArea.innerHTML = html;
    dynamicFormArea.classList.add('is-visible');

    if ((definition.nominator_count || 0) > 0) {
        bindNominatorControls();
        loadNominators();
    }
}

function candidateTextFormData(action) {
    const form = document.getElementById('candidateApplicationForm');
    const source = new FormData(form);
    const payload = new FormData();

    source.forEach((value, key) => {
        if (value instanceof File) {
            return;
        }

        payload.append(key, value);
    });

    payload.set('form_action', action);

    return payload;
}

async function postCandidateAction(action, extra = {}) {
    const payload = candidateTextFormData(action);

    Object.entries(extra).forEach(([key, value]) => {
        payload.set(key, String(value));
    });

    const response = await fetch(window.location.href, {
        method: 'POST',
        body: payload,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    let data;

    try {
        data = await response.json();
    } catch (error) {
        throw new Error('The server returned an invalid response.');
    }

    if (!response.ok || !data.success) {
        throw new Error(data.message || 'The request could not be completed.');
    }

    return data;
}

function showNominatorFeedback(message, type = 'success') {
    const feedback = document.getElementById('nominatorFeedback');

    if (!feedback) {
        return;
    }

    feedback.textContent = message;
    feedback.className =
        'nominator-feedback is-visible ' +
        (type === 'error' ? 'is-error' : 'is-success');
}

function renderNominatorList(data) {
    const list = document.getElementById('nominatorList');
    const count = document.getElementById('nominatorCount');
    const approvedCountElement = document.getElementById('nominatorApprovedCount');
    const addButton = document.getElementById('addNominatorBtn');
    const input = document.getElementById('nominatorStudentNo');

    if (!list || !count) {
        return;
    }

    const nominators = Array.isArray(data.nominators) ? data.nominators : [];
    const validCount = Number(data.count || 0);
    const approvedCount = Number(data.approved_count || 0);
    const requiredCount = Number(data.required || 0);

    count.textContent = String(validCount);

    if (approvedCountElement) {
        approvedCountElement.textContent = String(approvedCount);
    }

    if (addButton) {
        addButton.disabled = requiredCount > 0 && validCount >= requiredCount;
    }

    if (input) {
        input.disabled = requiredCount > 0 && validCount >= requiredCount;
    }

    if (!nominators.length) {
        list.innerHTML =
            '<div class="small-note">No nominators have been added to this draft.</div>';
        return;
    }

    list.innerHTML = nominators.map((nominator, index) => {
        const valid = Boolean(nominator.valid);
        const statusText = valid
            ? String(nominator.status || 'pending')
            : 'inactive';

        return '<div class="nominator-list-item">' +
            '<div>' +
                '<strong>' + (index + 1) + '. ' + escapeHtml(nominator.full_name) + '</strong>' +
                '<span>' + escapeHtml(nominator.student_no) + '</span>' +
                '<span class="nominator-status">' + escapeHtml(statusText) + '</span>' +
            '</div>' +
            '<button type="button" class="remove-nominator-btn" data-nomination-id="' +
                escapeHtml(nominator.id) +
            '">Remove</button>' +
        '</div>';
    }).join('');

    list.querySelectorAll('.remove-nominator-btn').forEach(button => {
        button.addEventListener('click', async function () {
            button.disabled = true;

            try {
                const data = await postCandidateAction('ajax_remove_nominator', {
                    nomination_id: button.dataset.nominationId
                });

                renderNominatorList(data);
                showNominatorFeedback(data.message || 'Nominator removed.');
            } catch (error) {
                showNominatorFeedback(error.message, 'error');
            } finally {
                button.disabled = false;
            }
        });
    });
}

async function loadNominators() {
    if (!electionSelect.value || !positionSelect.value) {
        return;
    }

    try {
        const data = await postCandidateAction('ajax_list_nominators');
        renderNominatorList(data);
    } catch (error) {
        showNominatorFeedback(error.message, 'error');

        const list = document.getElementById('nominatorList');

        if (list) {
            list.innerHTML =
                '<div class="small-note">Saved nominators could not be loaded.</div>';
        }
    }
}

function bindNominatorControls() {
    const button = document.getElementById('addNominatorBtn');
    const input = document.getElementById('nominatorStudentNo');

    if (!button || !input) {
        return;
    }

    const addNominator = async () => {
        const studentNo = input.value.trim();

        if (!studentNo) {
            showNominatorFeedback('Enter the student registration number.', 'error');
            input.focus();
            return;
        }

        button.disabled = true;
        input.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';

        try {
            const data = await postCandidateAction('ajax_add_nominator', {
                nominator_student_no: studentNo
            });

            input.value = '';
            renderNominatorList(data);
            showNominatorFeedback(data.message);
        } catch (error) {
            showNominatorFeedback(error.message, 'error');
        } finally {
            const count = Number(
                document.getElementById('nominatorCount')?.textContent || 0
            );
            const position = getSelectedPosition();
            const definition = position
                ? (FORM_DEFINITIONS[position.form_type] || FORM_DEFINITIONS.general)
                : null;
            const required = Number(definition?.nominator_count || 0);
            const complete = required > 0 && count >= required;

            button.disabled = complete;
            input.disabled = complete;
            button.innerHTML = '<i class="fas fa-user-check"></i> Verify & Add';
        }
    };

    button.addEventListener('click', addNominator);

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addNominator();
        }
    });
}

function restoreOldFormAfterError() {
    if (OLD_INPUT && OLD_INPUT.photo && photoPreview && photoPreviewPlaceholder) {
        photoPreview.src = OLD_INPUT.photo;
        photoPreviewPlaceholder.style.display = 'none';
        photoPreview.classList.add('is-visible');
    }

    if (!OLD_INPUT || !OLD_INPUT.election_id) {
        return;
    }

    electionSelect.value = String(OLD_INPUT.election_id);
    renderPositionOptions();

    if (OLD_INPUT.position_id) {
        positionSelect.value = String(OLD_INPUT.position_id);
        renderSelectedPositionInfo();
    }
}

function collectApplicationFiles() {
    const files = [];

    if (photoInput && photoInput.files && photoInput.files[0]) {
        files.push(photoInput.files[0]);
    }

    document.querySelectorAll('input[type="file"][name^="form_attachments"]').forEach(input => {
        if (input.files && input.files[0]) {
            files.push(input.files[0]);
        }
    });

    return files;
}

electionSelect.addEventListener('change', renderPositionOptions);
positionSelect.addEventListener('change', renderSelectedPositionInfo);

if (photoInput && photoPreview && photoPreviewPlaceholder) {
    photoInput.addEventListener('change', function () {
        const file = photoInput.files && photoInput.files[0] ? photoInput.files[0] : null;

        photoPreview.classList.remove('is-visible');
        photoPreview.src = '';
        photoPreviewPlaceholder.style.display = 'block';

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            alert('Please upload a valid image file.');
            photoInput.value = '';
            return;
        }

        if (file.size > PAGE_SINGLE_FILE_LIMIT_BYTES) {
            alert(file.name + ' is too large. Maximum allowed per file is ' + bytesToMbLabel(PAGE_SINGLE_FILE_LIMIT_BYTES) + '.');
            photoInput.value = '';
            return;
        }

        const previewUrl = URL.createObjectURL(file);

        photoPreview.src = previewUrl;

        photoPreview.onload = function () {
            URL.revokeObjectURL(previewUrl);
        };

        photoPreviewPlaceholder.style.display = 'none';
        photoPreview.classList.add('is-visible');
    });
}

document.getElementById('candidateApplicationForm').addEventListener('submit', function (event) {
    const action = event.submitter?.value || 'submit_application';
    const isDraftSave = action === 'save_draft';

    if (!electionSelect.value || !positionSelect.value) {
        event.preventDefault();
        alert('Please select an election and an eligible position.');
        return;
    }

    if (!dynamicFormArea.classList.contains('is-visible')) {
        event.preventDefault();
        alert('Please select a position so that the correct application form can load.');
        return;
    }

    if (
        !isDraftSave &&
        (!photoInput.files || !photoInput.files[0]) &&
        !hasExistingPhoto()
    ) {
        event.preventDefault();
        alert('Please upload a candidate photo.');
        return;
    }

    const files = collectApplicationFiles();
    let totalSize = 0;

    for (const file of files) {
        totalSize += file.size;

        if (file.size > PAGE_SINGLE_FILE_LIMIT_BYTES) {
            event.preventDefault();
            alert(
                file.name +
                ' is too large. Maximum allowed per file is ' +
                bytesToMbLabel(PAGE_SINGLE_FILE_LIMIT_BYTES) +
                '.'
            );
            return;
        }
    }

    if (totalSize > PAGE_TOTAL_UPLOAD_LIMIT_BYTES) {
        event.preventDefault();
        alert(
            'Your selected files are too large together. Maximum total upload size is ' +
            bytesToMbLabel(PAGE_TOTAL_UPLOAD_LIMIT_BYTES) +
            '. Current selected total is ' +
            bytesToMbLabel(totalSize) +
            '. Please reduce file sizes.'
        );
    }
});

restoreOldFormAfterError();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>