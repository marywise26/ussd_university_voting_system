<?php
require_once __DIR__ . '/../includes/init.php';

require_login();

function voter_redirect(string $file): void
{
    $file = ltrim($file, '/');

    if (strpos($file, 'voter/') === 0) {
        $file = substr($file, 6);
    }

    header('Location: ' . $file);
    exit;
}

if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user = current_user($pdo);
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$applicationId = (int)($_GET['id'] ?? $_POST['application_id'] ?? 0);

if ($applicationId <= 0) {
    set_flash('error', 'Invalid application.');
    voter_redirect('application_status.php');
}

function edit_faculty_options(): array
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

function edit_candidate_form_definitions(): array
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
                ['key' => 'manifesto', 'label' => 'Campaign manifesto'],
                ['key' => 'reason', 'label' => 'Why do you want this position?'],
                ['key' => 'experience', 'label' => 'Leadership or service experience'],
            ],
        ],
    ];
}

function edit_form_type_from_title(string $title): string
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

    if (strpos($title, 'SENATOR') !== false || strpos($title, 'SENATE') !== false) {
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

function safe_json_array_edit(?string $json): array
{
    if (!$json) {
        return [];
    }

    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

function clean_form_answers_edit($value)
{
    if (is_array($value)) {
        $clean = [];

        foreach ($value as $key => $item) {
            $clean[$key] = clean_form_answers_edit($item);
        }

        return $clean;
    }

    return trim((string)$value);
}

function edit_candidate_faculty_allowed(?string $allowedFacultyCode, string $studentFacultyCode): bool
{
    $allowedFacultyCode = strtoupper(trim((string)$allowedFacultyCode));
    $studentFacultyCode = strtoupper(trim($studentFacultyCode));

    if ($allowedFacultyCode === '') {
        return true;
    }

    return $studentFacultyCode !== '' && $studentFacultyCode === $allowedFacultyCode;
}

function edit_candidate_year_allowed(?string $allowedYears, int $studentYear): bool
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

function edit_candidate_position_allowed(array $position, array $user): bool
{
    $studentFacultyCode = strtoupper(trim((string)($user['faculty_code'] ?? '')));
    $studentYear = (int)($user['study_year'] ?? 0);

    return edit_candidate_faculty_allowed($position['candidate_faculty_code'] ?? null, $studentFacultyCode)
        && edit_candidate_year_allowed($position['candidate_years'] ?? null, $studentYear);
}

function normalize_student_no_edit(string $studentNo): string
{
    return strtoupper(preg_replace('/\s+/', '', trim($studentNo)));
}

function extract_nominator_student_numbers_edit(array $answers): array
{
    $rows = $answers['nominators'] ?? [];
    $numbers = [];

    if (!is_array($rows)) {
        return [];
    }

    foreach ($rows as $row) {
        $studentNo = '';

        if (is_array($row)) {
            $studentNo = trim((string)($row['student_no'] ?? ''));

            if ($studentNo === '') {
                $studentNo = trim((string)($row['registration_no'] ?? ''));
            }
        }

        if ($studentNo === '') {
            continue;
        }

        $numbers[normalize_student_no_edit($studentNo)] = $studentNo;
    }

    return array_values($numbers);
}

function resolve_nominators_edit(PDO $pdo, array $answers, int $candidateUserId, int $requiredCount): array
{
    if ($requiredCount <= 0) {
        return [];
    }

    $studentNumbers = extract_nominator_student_numbers_edit($answers);

    if (count($studentNumbers) < $requiredCount) {
        throw new RuntimeException('You must provide at least ' . $requiredCount . ' unique nominator registration numbers.');
    }

    $stmt = $pdo->prepare(
        "SELECT id, full_name, student_no
         FROM users
         WHERE REPLACE(UPPER(student_no), ' ', '') = ?
         AND role = 'voter'
         AND is_active = 1
         LIMIT 1"
    );

    $resolved = [];

    foreach ($studentNumbers as $studentNo) {
        $normalized = normalize_student_no_edit($studentNo);
        $stmt->execute([$normalized]);
        $nominator = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$nominator) {
            throw new RuntimeException('Nominator registration number not found or inactive: ' . $studentNo);
        }

        $nominatorId = (int)$nominator['id'];

        if ($nominatorId === $candidateUserId) {
            throw new RuntimeException('You cannot nominate yourself: ' . $studentNo);
        }

        $resolved[$nominatorId] = [
            'id' => $nominatorId,
            'full_name' => (string)$nominator['full_name'],
            'student_no' => (string)$nominator['student_no'],
        ];
    }

    if (count($resolved) < $requiredCount) {
        throw new RuntimeException('You must provide at least ' . $requiredCount . ' different valid nominators.');
    }

    return array_slice(array_values($resolved), 0, $requiredCount);
}

function create_nomination_requests_edit(PDO $pdo, int $applicationId, int $candidateUserId, array $nominators): void
{
    $delete = $pdo->prepare('DELETE FROM candidate_nominators WHERE application_id = ?');
    $delete->execute([$applicationId]);

    if (!$nominators) {
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO candidate_nominators
            (application_id, candidate_user_id, nominator_user_id, status)
         VALUES
            (?, ?, ?, "pending")'
    );

    foreach ($nominators as $nominator) {
        $stmt->execute([
            $applicationId,
            $candidateUserId,
            (int)$nominator['id'],
        ]);
    }
}

function candidate_attachment_file_edit(array $files, string $key): array
{
    return [
        'name' => $files['name'][$key] ?? '',
        'type' => $files['type'][$key] ?? '',
        'tmp_name' => $files['tmp_name'][$key] ?? '',
        'error' => $files['error'][$key] ?? UPLOAD_ERR_NO_FILE,
        'size' => $files['size'][$key] ?? 0,
    ];
}

function upload_required_attachment_edit(array $file, string $key, string $label): string
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
        throw new RuntimeException($label . ' must not exceed 5MB.');
    }

    $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException($label . ' must be PDF, DOC, DOCX, JPG, PNG, or WebP.');
    }

    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
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
        'attachment_edit_' .
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

function update_attachments_edit(array $files, array $definition, array $existingAttachments): array
{
    $attachments = $existingAttachments;

    foreach ($definition['attachments'] as $attachment) {
        $key = $attachment['key'];
        $label = $attachment['label'];

        $file = candidate_attachment_file_edit($files, $key);
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            if (trim((string)($attachments[$key] ?? '')) === '') {
                throw new RuntimeException($label . ' upload is required.');
            }

            continue;
        }

        $attachments[$key] = upload_required_attachment_edit($file, $key, $label);
    }

    return $attachments;
}

function validate_answers_edit(array $answers, array $definition): void
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

function get_question_answer_edit(array $answers, array $keys): string
{
    foreach ($keys as $key) {
        $value = trim((string)($answers['questions'][$key] ?? ''));

        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function build_manifesto_summary_edit(array $answers, array $definition): string
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

function size_to_bytes_edit(string $size): int
{
    $size = trim($size);

    if ($size === '') {
        return 0;
    }

    $unit = strtolower(substr($size, -1));
    $value = (float)$size;

    switch ($unit) {
        case 'g':
            return (int)($value * 1024 * 1024 * 1024);
        case 'm':
            return (int)($value * 1024 * 1024);
        case 'k':
            return (int)($value * 1024);
        default:
            return (int)$value;
    }
}

function file_url_edit(?string $path): string
{
    $path = trim((string)$path);

    if ($path === '') {
        return '#';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return '../' . ltrim($path, '/');
}

$faculties = edit_faculty_options();
$formDefinitions = edit_candidate_form_definitions();

$postMaxBytes = size_to_bytes_edit((string)ini_get('post_max_size'));
$uploadMaxBytes = size_to_bytes_edit((string)ini_get('upload_max_filesize'));

if ($postMaxBytes <= 0) {
    $postMaxBytes = 8 * 1024 * 1024;
}

if ($uploadMaxBytes <= 0) {
    $uploadMaxBytes = 2 * 1024 * 1024;
}

$pageTotalUploadLimitBytes = max(1024 * 1024, $postMaxBytes - (512 * 1024));
$pageSingleFileLimitBytes = min($uploadMaxBytes, 5 * 1024 * 1024);

$appStmt = $pdo->prepare(
    'SELECT
        ca.*,
        e.title AS election_title,
        e.status AS election_status,
        p.title AS position_title
     FROM candidate_applications ca
     JOIN elections e ON e.id = ca.election_id
     JOIN positions p ON p.id = ca.position_id
     WHERE ca.id = ?
     AND ca.user_id = ?
     LIMIT 1'
);
$appStmt->execute([$applicationId, $currentUserId]);
$application = $appStmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    set_flash('error', 'Application not found.');
    voter_redirect('application_status.php');
}

if ((string)$application['election_status'] !== 'draft') {
    set_flash('error', 'This application can no longer be edited because the election is ' . $application['election_status'] . '.');
    voter_redirect('application_status.php');
}

$currentAnswers = safe_json_array_edit($application['form_answers'] ?? '');
$currentFormType = trim((string)($application['form_type'] ?? ''));

if ($currentFormType === '') {
    $currentFormType = edit_form_type_from_title((string)$application['position_title']);
}

$positionsStmt = $pdo->prepare(
    'SELECT *
     FROM positions
     WHERE election_id = ?
     AND is_active = 1
     ORDER BY title ASC'
);
$positionsStmt->execute([(int)$application['election_id']]);
$allPositions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

$positions = [];

foreach ($allPositions as $position) {
    if (edit_candidate_position_allowed($position, $user) || (int)$position['id'] === (int)$application['position_id']) {
        $positions[] = $position;
    }
}

if (!$positions) {
    set_flash('error', 'No editable positions are available for this application.');
    voter_redirect('application_status.php');
}

$positionsForJs = [];

foreach ($positions as $position) {
    $formType = edit_form_type_from_title((string)$position['title']);
    $definition = $formDefinitions[$formType] ?? $formDefinitions['general'];

    $positionsForJs[] = [
        'id' => (int)$position['id'],
        'title' => (string)$position['title'],
        'description' => (string)($position['description'] ?? ''),
        'form_type' => $formType,
        'form_label' => $definition['label'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST) &&
        empty($_FILES) &&
        isset($_SERVER['CONTENT_LENGTH']) &&
        (int)$_SERVER['CONTENT_LENGTH'] > 0
    ) {
        set_flash('error', 'Your uploaded files are too large. Please reduce file sizes and submit again.');
        voter_redirect('edit_application.php?id=' . $applicationId);
    }

    $positionId = (int)($_POST['position_id'] ?? 0);
    $formAnswers = clean_form_answers_edit($_POST['form_answers'] ?? []);

    try {
        if ($positionId <= 0) {
            throw new RuntimeException('Position is required.');
        }

        $posStmt = $pdo->prepare(
            'SELECT *
             FROM positions
             WHERE id = ?
             AND election_id = ?
             AND is_active = 1
             LIMIT 1'
        );
        $posStmt->execute([$positionId, (int)$application['election_id']]);
        $selectedPosition = $posStmt->fetch(PDO::FETCH_ASSOC);

        if (!$selectedPosition) {
            throw new RuntimeException('Selected position is not available.');
        }

        if (!edit_candidate_position_allowed($selectedPosition, $user)) {
            throw new RuntimeException('You are not eligible to apply for the selected position.');
        }

        $duplicateStmt = $pdo->prepare(
            'SELECT id
             FROM candidate_applications
             WHERE user_id = ?
             AND election_id = ?
             AND position_id = ?
             AND id <> ?
             LIMIT 1'
        );
        $duplicateStmt->execute([
            $currentUserId,
            (int)$application['election_id'],
            $positionId,
            $applicationId,
        ]);

        if ($duplicateStmt->fetch()) {
            throw new RuntimeException('You already have another application for this position.');
        }

        $formType = edit_form_type_from_title((string)$selectedPosition['title']);
        $definition = $formDefinitions[$formType] ?? $formDefinitions['general'];
        $requiredNominators = (int)($definition['nominator_count'] ?? 0);

        $existingAttachments = $currentAnswers['attachments'] ?? [];

        if (!is_array($existingAttachments)) {
            $existingAttachments = [];
        }

        $formAnswers['attachments'] = update_attachments_edit(
            $_FILES['form_attachments'] ?? [],
            $definition,
            $existingAttachments
        );

        validate_answers_edit($formAnswers, $definition);

        $resolvedNominators = resolve_nominators_edit(
            $pdo,
            $formAnswers,
            $currentUserId,
            $requiredNominators
        );

        $formAnswers['nominators_resolved'] = array_map(
            static function ($nominator) {
                return [
                    'full_name' => $nominator['full_name'],
                    'student_no' => $nominator['student_no'],
                ];
            },
            $resolvedNominators
        );

        $photoPath = (string)($application['photo'] ?? '');

        if (!empty($_FILES['photo']['tmp_name']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $photoPath = upload_candidate_photo($_FILES['photo'] ?? []);
        }

        if ($photoPath === '') {
            throw new RuntimeException('Candidate photo is required.');
        }

        $manifesto = build_manifesto_summary_edit($formAnswers, $definition);

        $reason = get_question_answer_edit($formAnswers, [
            'why_president',
            'why_vice',
            'why_senator',
            'reason',
        ]);

        $experience = get_question_answer_edit($formAnswers, [
            'why_president',
            'why_vice',
            'why_senator',
            'experience',
        ]);

        $pdo->beginTransaction();

        $update = $pdo->prepare(
            'UPDATE candidate_applications
             SET position_id = ?,
                 manifesto = ?,
                 reason = ?,
                 experience = ?,
                 photo = ?,
                 form_type = ?,
                 form_answers = ?,
                 status = "pending",
                 vetting_comment = NULL
             WHERE id = ?
             AND user_id = ?'
        );

        $update->execute([
            $positionId,
            $manifesto,
            $reason,
            $experience,
            $photoPath,
            $formType,
            json_encode($formAnswers, JSON_UNESCAPED_UNICODE),
            $applicationId,
            $currentUserId,
        ]);

        create_nomination_requests_edit(
            $pdo,
            $applicationId,
            $currentUserId,
            $resolvedNominators
        );

        log_activity($pdo, $currentUserId, 'edit_candidate_application', 'candidate_applications', $applicationId);

        $pdo->commit();

        set_flash('success', 'Application updated. Nominator approvals have been reset and must be approved again.');
        voter_redirect('application_status.php');
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        set_flash('error', 'Application could not be updated.');
    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        set_flash('error', $e->getMessage());
    }
}

$oldAnswers = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? clean_form_answers_edit($_POST['form_answers'] ?? [])
    : $currentAnswers;

$oldPositionId = (int)($_POST['position_id'] ?? $application['position_id']);

$oldInputForJs = [
    'position_id' => $oldPositionId,
    'form_answers' => $oldAnswers,
];

$existingAttachmentsForJs = $currentAnswers['attachments'] ?? [];

if (!is_array($existingAttachmentsForJs)) {
    $existingAttachmentsForJs = [];
}

$existingAttachmentUrls = [];

foreach ($existingAttachmentsForJs as $key => $path) {
    $existingAttachmentUrls[$key] = file_url_edit((string)$path);
}

$page_title = 'Edit Application';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.edit-page {
    --primary: #2563eb;
    --ink: #0f172a;
    --muted: #64748b;
    --line: #e2e8f0;
    --soft: #f8fafc;
    color: var(--ink);
}

.edit-hero {
    padding: 1.4rem;
    border-radius: 20px;
    color: #fff;
    background: linear-gradient(135deg, #0f172a, #2563eb);
    margin-bottom: 1rem;
    box-shadow: 0 16px 38px rgba(15, 23, 42, .12);
}

.edit-hero h1 {
    margin: 0;
    font-size: clamp(1.4rem, 4vw, 2.2rem);
}

.edit-hero p {
    margin: .45rem 0 0;
    color: rgba(255, 255, 255, .85);
}

.edit-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 20px;
    padding: 1.2rem;
    box-shadow: 0 14px 34px rgba(15, 23, 42, .05);
}

.edit-page label {
    display: block;
    margin: .9rem 0 .35rem;
    font-size: .88rem;
    font-weight: 900;
    color: #334155;
}

.edit-page input,
.edit-page select,
.edit-page textarea {
    width: 100%;
    min-height: 46px;
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: .75rem .9rem;
    outline: none;
    color: var(--ink);
    background: #fff;
}

.edit-page textarea {
    min-height: 125px;
    resize: vertical;
}

.edit-page input:focus,
.edit-page select:focus,
.edit-page textarea:focus {
    border-color: rgba(37, 99, 235, .55);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
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
    margin-top: .4rem;
    color: var(--muted);
    font-size: .78rem;
    line-height: 1.35;
}

.attachment-upload-card input[type="file"] {
    margin-top: .6rem;
    min-height: auto;
    padding: .65rem;
}

.file-link {
    display: inline-flex;
    margin-top: .45rem;
    border-radius: 999px;
    padding: .45rem .7rem;
    background: #2563eb;
    color: #fff;
    font-weight: 900;
    text-decoration: none;
    font-size: .82rem;
}

.photo-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 180px;
    gap: 1rem;
    align-items: start;
}

.photo-preview-box {
    min-height: 190px;
    border: 2px dashed #93c5fd;
    border-radius: 18px;
    display: grid;
    place-items: center;
    overflow: hidden;
    padding: .6rem;
    background: linear-gradient(180deg, #eff6ff, #fff);
}

#candidatePhotoPreview {
    width: 100%;
    height: 170px;
    object-fit: cover;
    border-radius: 14px;
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

.submit-row {
    display: flex;
    gap: .7rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.submit-btn,
.cancel-btn {
    border: 0;
    border-radius: 999px;
    padding: .8rem 1.1rem;
    font-weight: 900;
    cursor: pointer;
    text-decoration: none;
}

.submit-btn {
    background: linear-gradient(135deg, #2563eb, #38bdf8);
    color: #fff;
}

.cancel-btn {
    background: #e5e7eb;
    color: #374151;
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

@media (max-width: 760px) {
    .form-grid-two,
    .attachment-upload-grid,
    .photo-grid,
    .nominator-grid {
        grid-template-columns: 1fr;
    }

    .nominator-head {
        display: none;
    }
}
</style>

<div class="edit-page">
    <section class="edit-hero">
        <h1>Edit Candidate Application</h1>
        <p>
            Election: <?= e($application['election_title']) ?>.
            Editing is allowed because this election is still in draft status.
        </p>
    </section>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="edit-card">
        <form method="POST" enctype="multipart/form-data" id="editApplicationForm">
            <input type="hidden" name="application_id" value="<?= (int)$applicationId ?>">

            <label>Election</label>
            <input type="text" value="<?= e($application['election_title']) ?>" disabled>

            <label for="position_id">Position</label>
            <select id="position_id" name="position_id" required>
                <?php foreach ($positions as $position): ?>
                    <option value="<?= (int)$position['id'] ?>" <?= $oldPositionId === (int)$position['id'] ? 'selected' : '' ?>>
                        <?= e($position['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <p class="small-note">
                You can edit the position and all form details while this election remains in draft.
            </p>

            <div id="dynamicFormArea"></div>

            <div class="form-section-box">
                <h3>Candidate Photo</h3>

                <div class="photo-grid">
                    <div>
                        <label for="photo">Upload new photo to replace existing</label>
                        <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp">

                        <p class="small-note">
                            Leave blank to keep the current photo.
                        </p>
                    </div>

                    <div class="photo-preview-box">
                        <?php if (!empty($application['photo'])): ?>
                            <img id="candidatePhotoPreview" src="<?= e(file_url_edit($application['photo'])) ?>" alt="Candidate photo preview">
                        <?php else: ?>
                            <img id="candidatePhotoPreview" src="" alt="Candidate photo preview">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="warning-note">
                Saving changes resets nominator approvals. Your nominators must approve the updated application again.
            </div>

            <div class="submit-row">
                <button type="submit" class="submit-btn">
                    Save Changes
                </button>

                <a href="application_status.php" class="cancel-btn">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const POSITIONS = <?= json_encode($positionsForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const FORM_DEFINITIONS = <?= json_encode($formDefinitions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const OLD_INPUT = <?= json_encode($oldInputForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const EXISTING_ATTACHMENTS = <?= json_encode($existingAttachmentsForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const EXISTING_ATTACHMENT_URLS = <?= json_encode($existingAttachmentUrls, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const PAGE_TOTAL_UPLOAD_LIMIT_BYTES = <?= (int)$pageTotalUploadLimitBytes ?>;
const PAGE_SINGLE_FILE_LIMIT_BYTES = <?= (int)$pageSingleFileLimitBytes ?>;

const positionSelect = document.getElementById('position_id');
const dynamicFormArea = document.getElementById('dynamicFormArea');
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('candidatePhotoPreview');

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

function oldNominatorValue(number, key) {
    const numberKey = String(number);

    if (
        OLD_INPUT &&
        OLD_INPUT.form_answers &&
        OLD_INPUT.form_answers.nominators &&
        OLD_INPUT.form_answers.nominators[numberKey]
    ) {
        if (Object.prototype.hasOwnProperty.call(OLD_INPUT.form_answers.nominators[numberKey], key)) {
            return OLD_INPUT.form_answers.nominators[numberKey][key];
        }

        if (key === 'student_no' && Object.prototype.hasOwnProperty.call(OLD_INPUT.form_answers.nominators[numberKey], 'registration_no')) {
            return OLD_INPUT.form_answers.nominators[numberKey].registration_no;
        }
    }

    return '';
}

function existingAttachmentValue(key) {
    return EXISTING_ATTACHMENTS && EXISTING_ATTACHMENTS[key] ? EXISTING_ATTACHMENTS[key] : '';
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

function currentPosition() {
    return POSITIONS.find(position => String(position.id) === String(positionSelect.value)) || POSITIONS[0] || null;
}

function renderDynamicForm() {
    const position = currentPosition();

    if (!position) {
        dynamicFormArea.innerHTML = '<div class="form-section-box">No editable position found.</div>';
        return;
    }

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
        html += '<div class="question-card">';
        html += '<label for="question_' + safeKey + '">' + (index + 1) + '. ' + escapeHtml(question.label) + '</label>';
        html += '<textarea id="question_' + safeKey + '" name="form_answers[questions][' + safeKey + ']" required>' + escapeHtml(oldQuestionValue(question.key)) + '</textarea>';
        html += '</div>';
    });

    html += '</div>';

    html += '<div class="form-section-box">';
    html += '<h3>C. Required Attachments</h3>';
    html += '<p>Leave a file blank to keep the current uploaded file. Upload a new file only when you want to replace it.</p>';
    html += '<div class="attachment-upload-grid">';

    (definition.attachments || []).forEach(attachment => {
        const safeKey = escapeHtml(attachment.key);
        const label = escapeHtml(attachment.label);
        const hasExisting = existingAttachmentValue(attachment.key) !== '';
        const required = hasExisting ? '' : 'required';

        html += '<div class="attachment-upload-card">';
        html += '<label for="attachment_' + safeKey + '">' + label + '</label>';

        if (hasExisting) {
            html += '<a class="file-link" href="' + escapeHtml(EXISTING_ATTACHMENT_URLS[attachment.key] || '#') + '" target="_blank" rel="noopener">View current file</a>';
            html += '<span>Upload a new file only if you want to replace the current one.</span>';
        } else {
            html += '<span>No existing file found. Upload is required.</span>';
        }

        html += '<input id="attachment_' + safeKey + '" type="file" name="form_attachments[' + safeKey + ']" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,image/webp" ' + required + '>';
        html += '</div>';
    });

    html += '</div>';
    html += '</div>';

    if ((definition.nominator_count || 0) > 0) {
        html += '<div class="form-section-box">';
        html += '<h3>D. Nominators</h3>';
        html += '<p>Enter ' + escapeHtml(definition.nominator_count) + ' valid student registration numbers. Editing this list resets approval requests.</p>';
        html += '<div class="nominator-grid">';
        html += '<div class="nominator-head">No.</div>';
        html += '<div class="nominator-head">Registration Number</div>';

        for (let i = 1; i <= Number(definition.nominator_count || 0); i++) {
            html += '<div><strong>' + i + '</strong></div>';
            html += '<div><input type="text" name="form_answers[nominators][' + i + '][student_no]" value="' + escapeHtml(oldNominatorValue(i, 'student_no')) + '" placeholder="Nominator registration number" required></div>';
        }

        html += '</div>';
        html += '</div>';
    }

    dynamicFormArea.innerHTML = html;
}

positionSelect.addEventListener('change', renderDynamicForm);

if (photoInput && photoPreview) {
    photoInput.addEventListener('change', function () {
        const file = photoInput.files && photoInput.files[0] ? photoInput.files[0] : null;

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
    });
}

document.getElementById('editApplicationForm').addEventListener('submit', function (event) {
    const files = [];

    if (photoInput && photoInput.files && photoInput.files[0]) {
        files.push(photoInput.files[0]);
    }

    document.querySelectorAll('input[type="file"][name^="form_attachments"]').forEach(input => {
        if (input.files && input.files[0]) {
            files.push(input.files[0]);
        }
    });

    let totalSize = 0;

    for (const file of files) {
        totalSize += file.size;

        if (file.size > PAGE_SINGLE_FILE_LIMIT_BYTES) {
            event.preventDefault();
            alert(file.name + ' is too large. Maximum allowed per file is ' + bytesToMbLabel(PAGE_SINGLE_FILE_LIMIT_BYTES) + '.');
            return;
        }
    }

    if (totalSize > PAGE_TOTAL_UPLOAD_LIMIT_BYTES) {
        event.preventDefault();
        alert(
            'Selected files are too large together. Maximum total upload size is ' +
            bytesToMbLabel(PAGE_TOTAL_UPLOAD_LIMIT_BYTES) +
            '. Current selected total is ' +
            bytesToMbLabel(totalSize) +
            '.'
        );
    }
});

renderDynamicForm();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>