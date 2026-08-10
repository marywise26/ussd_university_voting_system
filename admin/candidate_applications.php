<?php
require_once __DIR__ . '/../includes/init.php';

require_admin();

$status = $_GET['status'] ?? 'pending';

if (!in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
    $status = 'pending';
}

function admin_candidate_form_definitions(): array
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
        ['key' => 'age', 'label' => 'Age'],
        ['key' => 'sex', 'label' => 'Sex'],
        ['key' => 'nationality', 'label' => 'Nationality'],
        ['key' => 'po_box', 'label' => 'P.O. Box'],
    ];

    return [
        'president' => [
            'label' => 'Chairperson (President) Application Form',
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
            'nominator_count' => 20,
            'particulars' => array_merge($commonParticulars, [
                ['key' => 'campus', 'label' => 'Campus'],
                ['key' => 'senate_constituency', 'label' => 'Senate Constituency'],
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

function admin_form_type_from_title(string $title): string
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

function safe_json_array_admin(?string $json): array
{
    if (!$json) {
        return [];
    }

    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

function answer_value_admin(array $answers, string $section, string $key): string
{
    return trim((string)($answers[$section][$key] ?? ''));
}

function required_nominator_count_admin(array $application): int
{
    $formType = trim((string)($application['form_type'] ?? ''));

    if ($formType === '') {
        $formType = admin_form_type_from_title((string)($application['position_title'] ?? ''));
    }

    return match ($formType) {
        'president', 'vice_president' => 50,
        'senator' => 20,
        default => 0,
    };
}

function percentage_value_admin(int $part, int $whole): int
{
    if ($whole <= 0) {
        return 100;
    }

    return max(0, min(100, (int)round(($part / $whole) * 100)));
}

function admin_file_url(?string $path): string
{
    $path = trim((string)$path);

    if ($path === '') {
        return '#';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    if (function_exists('app_url')) {
        return app_url(ltrim($path, '/'));
    }

    return '../' . ltrim($path, '/');
}

$formDefinitions = admin_candidate_form_definitions();

$sql = '
    SELECT
        ca.*,
        u.full_name,
        u.student_no,
        u.email,
        u.phone,
        u.faculty_code,
        u.programme_code,
        u.study_year,
        e.title AS election_title,
        e.status AS election_status,
        p.title AS position_title,

        (SELECT COUNT(*) FROM candidate_nominators cn WHERE cn.application_id = ca.id) AS total_nominators,
        (SELECT COUNT(*) FROM candidate_nominators cn WHERE cn.application_id = ca.id AND cn.status = "approved") AS approved_nominators,
        (SELECT COUNT(*) FROM candidate_nominators cn WHERE cn.application_id = ca.id AND cn.status = "pending") AS pending_nominators,
        (SELECT COUNT(*) FROM candidate_nominators cn WHERE cn.application_id = ca.id AND cn.status = "rejected") AS rejected_nominators

    FROM candidate_applications ca
    JOIN users u ON ca.user_id = u.id
    JOIN elections e ON ca.election_id = e.id
    JOIN positions p ON ca.position_id = p.id
';

$params = [];

if ($status !== 'all') {
    $sql .= ' WHERE ca.status = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY ca.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$appIds = array_map(static fn ($app) => (int)$app['id'], $applications);
$nominatorsByApplication = [];

if ($appIds) {
    $placeholders = implode(',', array_fill(0, count($appIds), '?'));

    $nomStmt = $pdo->prepare(
        "SELECT
            cn.*,
            u.full_name,
            u.student_no,
            u.email,
            u.phone
         FROM candidate_nominators cn
         JOIN users u ON u.id = cn.nominator_user_id
         WHERE cn.application_id IN ($placeholders)
         ORDER BY
            cn.application_id ASC,
            CASE cn.status
                WHEN 'approved' THEN 1
                WHEN 'pending' THEN 2
                WHEN 'rejected' THEN 3
                ELSE 4
            END,
            u.full_name ASC"
    );

    $nomStmt->execute($appIds);

    foreach ($nomStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $nominatorsByApplication[(int)$row['application_id']][] = $row;
    }
}

$page_title = 'Candidate Vetting';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .vetting-page {
        --primary: #2563eb;
        --ink: #0f172a;
        --muted: #64748b;
        --line: #e2e8f0;
        --soft: #f8fafc;
        color: var(--ink);
    }

    .vetting-hero {
        padding: 1.4rem;
        border-radius: 20px;
        color: #fff;
        background: linear-gradient(135deg, #0f172a, #2563eb);
        margin-bottom: 1rem;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .12);
    }

    .vetting-hero h1 {
        margin: 0;
        font-size: clamp(1.4rem, 4vw, 2.2rem);
    }

    .vetting-hero p {
        margin: .45rem 0 0;
        color: rgba(255, 255, 255, .85);
    }

    .filter-card,
    .application-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 20px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .05);
    }

    .actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .vet-btn,
    .file-link,
    .small-action-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 999px;
        padding: .6rem .85rem;
        background: var(--primary);
        color: #fff;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        font-size: .86rem;
    }

    .btn-muted {
        background: #e5e7eb !important;
        color: #374151 !important;
    }

    .btn-danger {
        background: #dc2626 !important;
        color: #fff !important;
    }

    .btn-success {
        background: #16a34a !important;
        color: #fff !important;
    }

    .vet-btn:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .application-grid {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }

    .candidate-photo {
        width: 150px;
        height: 150px;
        border-radius: 18px;
        object-fit: cover;
        border: 1px solid var(--line);
        background: #f8fafc;
    }

    .candidate-name {
        margin: .75rem 0 .3rem;
        font-size: 1.25rem;
    }

    .small {
        color: var(--muted);
        line-height: 1.5;
        font-size: .9rem;
    }

    .badge {
        display: inline-flex;
        border-radius: 999px;
        padding: .35rem .65rem;
        font-size: .78rem;
        font-weight: 900;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .badge-pending,
    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-approved,
    .badge-success,
    .badge-open {
        background: #dcfce7;
        color: #166534;
    }

    .badge-rejected,
    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-draft {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-closed {
        background: #e5e7eb;
        color: #374151;
    }

    .position-title {
        margin: 0 0 .4rem;
        font-size: 1.2rem;
    }

    .meta-line {
        color: var(--muted);
        line-height: 1.55;
        font-size: .92rem;
    }

    .progress-wrap {
        margin-top: 1rem;
        padding: .85rem;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        border-radius: 16px;
    }

    .progress-top {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        font-size: .9rem;
        color: #1e3a8a;
        font-weight: 900;
    }

    .progress-bar {
        height: 11px;
        border-radius: 999px;
        background: #dbeafe;
        overflow: hidden;
        margin-top: .55rem;
    }

    .progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        width: var(--progress-width);
    }

    .progress-stats {
        display: flex;
        gap: .65rem;
        flex-wrap: wrap;
        margin-top: .65rem;
        font-size: .85rem;
        color: #334155;
    }

    .warning-box {
        margin-top: .75rem;
        padding: .75rem;
        border-radius: 14px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        line-height: 1.45;
        font-size: .9rem;
    }

    .success-box {
        margin-top: .75rem;
        padding: .75rem;
        border-radius: 14px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        line-height: 1.45;
        font-size: .9rem;
    }

    .full-form {
        margin-top: 1rem;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }

    .full-form summary {
        cursor: pointer;
        padding: .85rem 1rem;
        font-weight: 900;
        color: #1e40af;
        background: #eff6ff;
    }

    .full-form-body {
        padding: 1rem;
    }

    .form-block {
        margin-top: 1rem;
        padding: .9rem;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #f8fafc;
    }

    .form-block:first-child {
        margin-top: 0;
    }

    .form-block h4 {
        margin: 0 0 .65rem;
    }

    .kv-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem;
    }

    .kv-item {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: .7rem;
    }

    .kv-item span {
        display: block;
        color: var(--muted);
        font-size: .78rem;
        font-weight: 900;
        margin-bottom: .25rem;
    }

    .answer-box {
        white-space: pre-wrap;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: .75rem;
        margin-top: .5rem;
        line-height: 1.55;
    }

    .attachment-list,
    .nominator-list {
        display: grid;
        gap: .55rem;
    }

    .attachment-row,
    .nominator-row {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        align-items: center;
        flex-wrap: wrap;
        padding: .7rem;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: #fff;
    }

    .legacy-section {
        margin-top: 1rem;
        padding: .9rem;
        border-radius: 14px;
        border: 1px solid var(--line);
        background: #fff;
    }

    .legacy-section p {
        margin: .5rem 0;
        line-height: 1.55;
    }

    .vetting-form {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 16px;
        border: 1px solid var(--line);
        background: #f8fafc;
    }

    .vetting-form label {
        display: block;
        margin-bottom: .35rem;
        font-weight: 900;
        color: #334155;
    }

    .vetting-form textarea {
        width: 100%;
        min-height: 105px;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: .75rem .9rem;
        resize: vertical;
        outline: none;
    }

    .vetting-form textarea:focus {
        border-color: rgba(37, 99, 235, .55);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .empty-card {
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 18px;
        padding: 1rem;
        color: #64748b;
    }

    @media (max-width: 900px) {
        .application-grid,
        .kv-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="vetting-page">
    <section class="vetting-hero">
        <h1>Candidate Vetting</h1>
        <p>Review candidate details, full application forms, attachments, and verified nominator approvals before vetting.</p>
    </section>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="filter-card">
        <div class="actions">
            <?php foreach (['pending', 'approved', 'rejected', 'all'] as $item): ?>
                <a
                    class="vet-btn <?= $status === $item ? '' : 'btn-muted' ?>"
                    href="<?= e(app_url('admin/candidate_applications.php?status=' . $item)) ?>"
                >
                    <?= e(ucfirst($item)) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php foreach ($applications as $app): ?>
        <?php
        $answers = safe_json_array_admin($app['form_answers'] ?? '');
        $formType = trim((string)($app['form_type'] ?? ''));

        if ($formType === '') {
            $formType = admin_form_type_from_title((string)$app['position_title']);
        }

        $definition = $formDefinitions[$formType] ?? $formDefinitions['general'];

        $requiredNominators = required_nominator_count_admin($app);
        $approvedNominators = (int)($app['approved_nominators'] ?? 0);
        $pendingNominators = (int)($app['pending_nominators'] ?? 0);
        $rejectedNominators = (int)($app['rejected_nominators'] ?? 0);
        $totalNominators = (int)($app['total_nominators'] ?? 0);
        $progress = percentage_value_admin($approvedNominators, $requiredNominators);
        $nominationReady = $requiredNominators === 0 || $approvedNominators >= $requiredNominators;
        $appNominators = $nominatorsByApplication[(int)$app['id']] ?? [];
        ?>

        <div class="application-card">
            <div class="application-grid">
                <div>
                    <?php if (!empty($app['photo'])): ?>
                        <img class="candidate-photo" src="<?= e(admin_file_url($app['photo'])) ?>" alt="Candidate photo">
                    <?php endif; ?>

                    <h2 class="candidate-name"><?= e($app['full_name']) ?></h2>

                    <p class="small">
                        <strong>Student No:</strong> <?= e($app['student_no']) ?><br>
                        <strong>Email:</strong> <?= e($app['email']) ?><br>
                        <strong>Phone:</strong> <?= e($app['phone']) ?><br>
                        <strong>Faculty:</strong> <?= e($app['faculty_code'] ?? '-') ?><br>
                        <strong>Programme:</strong> <?= e($app['programme_code'] ?? '-') ?><br>
                        <strong>Study Year:</strong> <?= e($app['study_year'] ?? '-') ?>
                    </p>

                    <p>
                        <span class="badge badge-<?= e($app['status']) ?>">
                            <?= e($app['status']) ?>
                        </span>
                    </p>
                </div>

                <div>
                    <h3 class="position-title"><?= e($app['position_title']) ?></h3>

                    <div class="meta-line">
                        <strong>Election:</strong> <?= e($app['election_title']) ?>
                        <span class="badge badge-<?= e($app['election_status']) ?>">
                            <?= e($app['election_status']) ?>
                        </span><br>

                        <strong>Application Form:</strong> <?= e($definition['label']) ?><br>
                        <strong>Applied At:</strong> <?= e($app['created_at']) ?>
                    </div>

                    <?php if ($requiredNominators > 0): ?>
                        <div class="progress-wrap">
                            <div class="progress-top">
                                <span>Nominator approval progress</span>
                                <span><?= $approvedNominators ?> / <?= $requiredNominators ?> approved</span>
                            </div>

                            <div class="progress-bar">
                                <div class="progress-fill" style="--progress-width: <?= $progress ?>%;"></div>
                            </div>

                            <div class="progress-stats">
                                <span>Total requests: <?= $totalNominators ?></span>
                                <span>Approved: <?= $approvedNominators ?></span>
                                <span>Pending: <?= $pendingNominators ?></span>
                                <span>Rejected: <?= $rejectedNominators ?></span>
                            </div>

                            <?php if ($nominationReady): ?>
                                <div class="success-box">
                                    Nominator requirement is complete. This application is ready for admin vetting.
                                </div>
                            <?php else: ?>
                                <div class="warning-box">
                                    This application is not ready for approval. Required approved nominators:
                                    <?= $requiredNominators ?>. Current approved: <?= $approvedNominators ?>.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="success-box">
                            This position does not require nominator approvals.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($app['vetting_comment'])): ?>
                        <div class="legacy-section">
                            <strong>Vetting Comment:</strong><br>
                            <?= nl2br(e($app['vetting_comment'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <details class="full-form">
                <summary>View Full Application Form</summary>

                <div class="full-form-body">
                    <div class="form-block">
                        <h4>A. Particulars of the Applicant</h4>

                        <div class="kv-grid">
                            <?php foreach ($definition['particulars'] as $field): ?>
                                <div class="kv-item">
                                    <span><?= e($field['label']) ?></span>
                                    <?= e(answer_value_admin($answers, 'particulars', $field['key']) ?: '-') ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-block">
                        <h4>B. Leadership Questions & Vision Assessment</h4>

                        <?php foreach ($definition['questions'] as $index => $question): ?>
                            <div style="margin-top:.9rem;">
                                <strong><?= ($index + 1) ?>. <?= e($question['label']) ?></strong>

                                <div class="answer-box">
                                    <?= e(answer_value_admin($answers, 'questions', $question['key']) ?: '-') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-block">
                        <h4>C. Required Attachments</h4>

                        <div class="attachment-list">
                            <?php foreach ($definition['attachments'] as $attachment): ?>
                                <?php $path = trim((string)($answers['attachments'][$attachment['key']] ?? '')); ?>

                                <div class="attachment-row">
                                    <strong><?= e($attachment['label']) ?></strong>

                                    <?php if ($path !== ''): ?>
                                        <a class="file-link" href="<?= e(admin_file_url($path)) ?>" target="_blank" rel="noopener">
                                            View File
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Missing</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($requiredNominators > 0): ?>
                        <div class="form-block">
                            <h4>D. Verified Nominators</h4>

                            <div class="nominator-list">
                                <?php foreach ($appNominators as $nominator): ?>
                                    <div class="nominator-row">
                                        <div>
                                            <strong><?= e($nominator['full_name']) ?></strong><br>
                                            <span class="small">
                                                Student No: <?= e($nominator['student_no']) ?><br>
                                                Email: <?= e($nominator['email'] ?? '-') ?><br>
                                                Phone: <?= e($nominator['phone'] ?? '-') ?>
                                            </span>
                                        </div>

                                        <span class="badge badge-<?= e($nominator['status']) ?>">
                                            <?= e($nominator['status']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (!$appNominators): ?>
                                    <div class="empty-card">
                                        No nominator requests found for this application.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="legacy-section">
                        <h4>Legacy Summary Fields</h4>

                        <p>
                            <strong>Manifesto:</strong><br>
                            <?= nl2br(e($app['manifesto'] ?? '-')) ?>
                        </p>

                        <p>
                            <strong>Reason:</strong><br>
                            <?= nl2br(e($app['reason'] ?? '-')) ?>
                        </p>

                        <p>
                            <strong>Experience:</strong><br>
                            <?= nl2br(e($app['experience'] ?? '-')) ?>
                        </p>
                    </div>
                </div>
            </details>

            <?php if ($app['status'] === 'pending'): ?>
                <form method="POST" action="<?= e(app_url('admin/vet_candidate.php')) ?>" class="vetting-form">
                    <input type="hidden" name="application_id" value="<?= (int)$app['id'] ?>">

                    <label>Vetting Comment</label>
                    <textarea name="vetting_comment" placeholder="Optional comment for approval or rejection"></textarea>

                    <?php if (!$nominationReady): ?>
                        <div class="warning-box">
                            Approval is disabled until the candidate reaches the required number of approved nominators.
                            You may still reject the application.
                        </div>
                    <?php endif; ?>

                    <div class="actions" style="margin-top:.8rem;">
                        <button
                            class="vet-btn btn-success"
                            type="submit"
                            name="decision"
                            value="approved"
                            <?= $nominationReady ? '' : 'disabled' ?>
                        >
                            Approve
                        </button>

                        <button class="vet-btn btn-danger" type="submit" name="decision" value="rejected">
                            Reject
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (!$applications): ?>
        <div class="empty-card">
            No candidate applications found.
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>