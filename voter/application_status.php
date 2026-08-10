<?php
require_once __DIR__ . '/../includes/init.php';

require_login();

function voter_redirect(string $file): void
{
    $file = ltrim($file, '/');

    if (function_exists('app_url')) {
        header('Location: ' . app_url('voter/' . $file));
        exit;
    }

    header('Location: ' . $file);
    exit;
}

if (($_SESSION['role'] ?? '') === 'admin') {
    redirect('admin/dashboard.php');
}

function status_candidate_form_definitions(): array
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

function status_form_type_from_title(string $title): string
{
    $title = strtoupper($title);

    if (strpos($title, 'VICE') !== false && (strpos($title, 'PRESIDENT') !== false || strpos($title, 'CHAIR') !== false)) {
        return 'vice_president';
    }

    if (strpos($title, 'SENATOR') !== false || strpos($title, 'SENATE') !== false) {
        return 'senator';
    }

    if (strpos($title, 'PRESIDENT') !== false || strpos($title, 'CHAIRPERSON') !== false || strpos($title, 'CHAIR') !== false) {
        return 'president';
    }

    return 'general';
}

function safe_json_array(?string $json): array
{
    if (!$json) {
        return [];
    }

    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

function required_nominator_count_for_application(array $application): int
{
    $formType = trim((string)($application['form_type'] ?? ''));

    if ($formType === '') {
        $formType = status_form_type_from_title((string)($application['position_title'] ?? ''));
    }

    return match ($formType) {
        'president', 'vice_president' => 50,
        'senator' => 20,
        default => 0,
    };
}

function percentage_value(int $part, int $whole): int
{
    if ($whole <= 0) {
        return 100;
    }

    return max(0, min(100, (int)round(($part / $whole) * 100)));
}

function file_url(?string $path): string
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

function answer_value(array $answers, string $section, string $key): string
{
    return trim((string)($answers[$section][$key] ?? ''));
}

$currentUserId = (int)$_SESSION['user_id'];
$formDefinitions = status_candidate_form_definitions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $requestId = (int)($_POST['request_id'] ?? 0);

    try {
        if ($requestId <= 0) {
            throw new RuntimeException('Invalid nomination request.');
        }

        if ($action === 'approve_nomination') {
            $stmt = $pdo->prepare(
                'UPDATE candidate_nominators
                 SET status = "approved",
                     approved_at = NOW(),
                     rejected_at = NULL
                 WHERE id = ?
                 AND nominator_user_id = ?
                 AND status = "pending"'
            );

            $stmt->execute([$requestId, $currentUserId]);

            if ($stmt->rowCount() < 1) {
                throw new RuntimeException('Nomination request could not be approved.');
            }

            set_flash('success', 'Nomination request approved.');
            voter_redirect('application_status.php');
        }

        if ($action === 'reject_nomination') {
            $stmt = $pdo->prepare(
                'UPDATE candidate_nominators
                 SET status = "rejected",
                     rejected_at = NOW()
                 WHERE id = ?
                 AND nominator_user_id = ?
                 AND status = "pending"'
            );

            $stmt->execute([$requestId, $currentUserId]);

            if ($stmt->rowCount() < 1) {
                throw new RuntimeException('Nomination request could not be rejected.');
            }

            set_flash('success', 'Nomination request rejected.');
            voter_redirect('application_status.php');
        }

        throw new RuntimeException('Unknown action.');
    } catch (RuntimeException $e) {
        set_flash('error', $e->getMessage());
        voter_redirect('application_status.php');
    }
}

$stmt = $pdo->prepare(
    'SELECT
        ca.*,
        e.title AS election_title,
        e.status AS election_status,
        p.title AS position_title,

        (SELECT COUNT(*) FROM candidate_nominators cn WHERE cn.application_id = ca.id) AS total_nominators,
        (SELECT COUNT(*) FROM candidate_nominators cn WHERE cn.application_id = ca.id AND cn.status = "approved") AS approved_nominators,
        (SELECT COUNT(*) FROM candidate_nominators cn WHERE cn.application_id = ca.id AND cn.status = "pending") AS pending_nominators,
        (SELECT COUNT(*) FROM candidate_nominators cn WHERE cn.application_id = ca.id AND cn.status = "rejected") AS rejected_nominators

     FROM candidate_applications ca
     JOIN elections e ON ca.election_id = e.id
     JOIN positions p ON ca.position_id = p.id
     WHERE ca.user_id = ?
     ORDER BY ca.created_at DESC'
);
$stmt->execute([$currentUserId]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$appIds = array_map(static fn ($app) => (int)$app['id'], $applications);
$nominatorsByApplication = [];

if ($appIds) {
    $placeholders = implode(',', array_fill(0, count($appIds), '?'));

    $nomStmt = $pdo->prepare(
        "SELECT
            cn.*,
            u.full_name,
            u.student_no
         FROM candidate_nominators cn
         JOIN users u ON u.id = cn.nominator_user_id
         WHERE cn.application_id IN ($placeholders)
         ORDER BY cn.application_id ASC, cn.status ASC, u.full_name ASC"
    );

    $nomStmt->execute($appIds);

    foreach ($nomStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $nominatorsByApplication[(int)$row['application_id']][] = $row;
    }
}

$requestStmt = $pdo->prepare(
    'SELECT
        cn.*,
        ca.status AS application_status,
        ca.form_type,
        ca.created_at AS application_created_at,
        e.title AS election_title,
        p.title AS position_title,
        candidate.full_name AS candidate_name,
        candidate.student_no AS candidate_student_no
     FROM candidate_nominators cn
     JOIN candidate_applications ca ON ca.id = cn.application_id
     JOIN elections e ON e.id = ca.election_id
     JOIN positions p ON p.id = ca.position_id
     JOIN users candidate ON candidate.id = cn.candidate_user_id
     WHERE cn.nominator_user_id = ?
     ORDER BY
        CASE cn.status
            WHEN "pending" THEN 1
            WHEN "approved" THEN 2
            WHEN "rejected" THEN 3
            ELSE 4
        END,
        cn.created_at DESC'
);
$requestStmt->execute([$currentUserId]);
$nominationRequests = $requestStmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Application Status';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.status-page {
    --primary: #2563eb;
    --ink: #0f172a;
    --muted: #64748b;
    --line: #e2e8f0;
    --soft: #f8fafc;
    color: var(--ink);
}
.status-hero {
    padding: 1.4rem;
    border-radius: 20px;
    color: #fff;
    background: linear-gradient(135deg, #0f172a, #2563eb);
    margin-bottom: 1rem;
}
.status-hero h1 { margin: 0; font-size: clamp(1.4rem, 4vw, 2.2rem); }
.status-hero p { margin: .45rem 0 0; color: rgba(255,255,255,.82); }
.status-section {
    margin-top: 1rem;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 20px;
    padding: 1rem;
}
.application-list, .request-list { display: grid; gap: .9rem; }
.application-card, .request-card {
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 1rem;
    background: linear-gradient(180deg, #f8fafc, #fff);
}
.application-header, .request-header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
    flex-wrap: wrap;
}
.application-title, .request-title { margin: 0; font-size: 1rem; }
.meta { margin-top: .25rem; color: var(--muted); font-size: .86rem; line-height: 1.45; }
.badge {
    display: inline-flex;
    border-radius: 999px;
    padding: .35rem .65rem;
    font-size: .78rem;
    font-weight: 900;
    text-transform: capitalize;
    white-space: nowrap;
}
.badge-pending, .badge-warning { background: #fef3c7; color: #92400e; }
.badge-approved, .badge-success, .badge-vetted, .badge-accepted { background: #dcfce7; color: #166534; }
.badge-rejected, .badge-danger, .badge-declined { background: #fee2e2; color: #991b1b; }
.badge-draft { background: #dbeafe; color: #1e40af; }
.badge-open { background: #dcfce7; color: #166534; }
.badge-closed { background: #e5e7eb; color: #374151; }
.progress-wrap {
    margin-top: .9rem;
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
.full-form-body { padding: 1rem; }
.form-block {
    margin-top: 1rem;
    padding: .9rem;
    border: 1px solid var(--line);
    border-radius: 14px;
    background: #f8fafc;
}
.form-block h4 { margin: 0 0 .6rem; }
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
.attachment-list, .nominator-list { display: grid; gap: .5rem; }
.attachment-row, .nominator-row {
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
.file-link, .edit-link {
    display: inline-flex;
    border-radius: 999px;
    padding: .55rem .8rem;
    background: #2563eb;
    color: #fff;
    font-weight: 900;
    text-decoration: none;
    font-size: .84rem;
}
.locked-note {
    margin-top: .75rem;
    padding: .75rem;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    color: #64748b;
}
.candidate-photo {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 14px;
    border: 1px solid var(--line);
}
.comment-box {
    margin-top: .75rem;
    padding: .75rem;
    border-radius: 14px;
    background: #fff;
    border: 1px solid var(--line);
    color: #334155;
}
.request-actions {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    margin-top: .8rem;
}
.action-btn {
    border: 0;
    border-radius: 999px;
    padding: .65rem .9rem;
    font-weight: 900;
    cursor: pointer;
}
.approve-btn { background: #16a34a; color: #fff; }
.reject-btn { background: #dc2626; color: #fff; }
.empty-box {
    padding: 1rem;
    border-radius: 16px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    color: #64748b;
}
@media (max-width: 760px) {
    .kv-grid { grid-template-columns: 1fr; }
}
</style>

<div class="status-page">
    <section class="status-hero">
        <h1>Application Status</h1>
        <p>Track your candidate applications, view your full submitted form, and respond to nomination requests.</p>
    </section>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <section class="status-section">
        <h2>My Candidate Applications</h2>

        <?php if (!$applications): ?>
            <div class="empty-box">You have not submitted any candidate application.</div>
        <?php else: ?>
            <div class="application-list">
                <?php foreach ($applications as $app): ?>
                    <?php
                    $answers = safe_json_array($app['form_answers'] ?? '');
                    $formType = trim((string)($app['form_type'] ?? ''));

                    if ($formType === '') {
                        $formType = status_form_type_from_title((string)$app['position_title']);
                    }

                    $definition = $formDefinitions[$formType] ?? $formDefinitions['general'];
                    $requiredNominators = required_nominator_count_for_application($app);
                    $approvedNominators = (int)($app['approved_nominators'] ?? 0);
                    $pendingNominators = (int)($app['pending_nominators'] ?? 0);
                    $rejectedNominators = (int)($app['rejected_nominators'] ?? 0);
                    $totalNominators = (int)($app['total_nominators'] ?? 0);
                    $progress = percentage_value($approvedNominators, $requiredNominators);
                    $readyForVetting = $requiredNominators === 0 || $approvedNominators >= $requiredNominators;
                    $canEdit = ((string)$app['election_status'] === 'draft');
                    $appNominators = $nominatorsByApplication[(int)$app['id']] ?? [];
                    ?>
                    <article class="application-card">
                        <div class="application-header">
                            <div>
                                <h3 class="application-title"><?= e($app['position_title']) ?></h3>
                                <div class="meta">
                                    Election: <?= e($app['election_title']) ?>
                                    <span class="badge badge-<?= e($app['election_status']) ?>"><?= e($app['election_status']) ?></span><br>
                                    Applied at: <?= e($app['created_at']) ?>
                                </div>
                            </div>

                            <span class="badge badge-<?= e($app['status']) ?>">
                                <?= e($app['status']) ?>
                            </span>
                        </div>

                        <?php if ($canEdit): ?>
                            <div style="margin-top:.8rem;">
                                <a class="edit-link" href="edit_application.php?id=<?= (int)$app['id'] ?>">
                                    Edit Application
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="locked-note">
                                Editing is locked because this election is <?= e($app['election_status']) ?>.
                            </div>
                        <?php endif; ?>

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

                                <div style="margin-top:.65rem;">
                                    <?php if ($readyForVetting): ?>
                                        <span class="badge badge-success">Ready for admin vetting</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Waiting for more nominator approvals</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <details class="full-form">
                            <summary>View Full Submitted Form</summary>

                            <div class="full-form-body">
                                <div class="form-block">
                                    <h4><?= e($definition['label']) ?></h4>

                                    <?php if (!empty($app['photo'])): ?>
                                        <img class="candidate-photo" src="<?= e(file_url($app['photo'])) ?>" alt="Candidate photo">
                                    <?php endif; ?>
                                </div>

                                <div class="form-block">
                                    <h4>A. Particulars of the Applicant</h4>
                                    <div class="kv-grid">
                                        <?php foreach ($definition['particulars'] as $field): ?>
                                            <div class="kv-item">
                                                <span><?= e($field['label']) ?></span>
                                                <?= e(answer_value($answers, 'particulars', $field['key']) ?: '-') ?>
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
                                                <?= e(answer_value($answers, 'questions', $question['key']) ?: '-') ?>
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
                                                    <a class="file-link" href="<?= e(file_url($path)) ?>" target="_blank" rel="noopener">
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
                                        <h4>D. Nominators</h4>

                                        <div class="nominator-list">
                                            <?php foreach ($appNominators as $nominator): ?>
                                                <div class="nominator-row">
                                                    <div>
                                                        <strong><?= e($nominator['full_name']) ?></strong><br>
                                                        <span class="meta"><?= e($nominator['student_no']) ?></span>
                                                    </div>

                                                    <span class="badge badge-<?= e($nominator['status']) ?>">
                                                        <?= e($nominator['status']) ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>

                                            <?php if (!$appNominators): ?>
                                                <div class="empty-box">No nominator requests found for this application.</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </details>

                        <?php if (trim((string)($app['vetting_comment'] ?? '')) !== ''): ?>
                            <div class="comment-box">
                                <strong>Admin comment:</strong><br>
                                <?= nl2br(e($app['vetting_comment'])) ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="status-section">
        <h2>Nomination Requests for Me</h2>

        <?php if (!$nominationRequests): ?>
            <div class="empty-box">No candidate has requested your nomination yet.</div>
        <?php else: ?>
            <div class="request-list">
                <?php foreach ($nominationRequests as $request): ?>
                    <article class="request-card">
                        <div class="request-header">
                            <div>
                                <h3 class="request-title">
                                    <?= e($request['candidate_name']) ?> wants your nomination
                                </h3>
                                <div class="meta">
                                    Candidate Reg. No: <?= e($request['candidate_student_no']) ?><br>
                                    Election: <?= e($request['election_title']) ?><br>
                                    Position: <?= e($request['position_title']) ?><br>
                                    Requested at: <?= e($request['created_at']) ?>
                                </div>
                            </div>

                            <span class="badge badge-<?= e($request['status']) ?>">
                                <?= e($request['status']) ?>
                            </span>
                        </div>

                        <?php if ($request['status'] === 'pending'): ?>
                            <div class="request-actions">
                                <form method="POST" onsubmit="return confirm('Approve this nomination request?');">
                                    <input type="hidden" name="action" value="approve_nomination">
                                    <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                                    <button type="submit" class="action-btn approve-btn">Approve</button>
                                </form>

                                <form method="POST" onsubmit="return confirm('Reject this nomination request?');">
                                    <input type="hidden" name="action" value="reject_nomination">
                                    <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                                    <button type="submit" class="action-btn reject-btn">Reject</button>
                                </form>
                            </div>
                        <?php elseif ($request['status'] === 'approved'): ?>
                            <div class="comment-box">
                                You approved this nomination on <?= e($request['approved_at']) ?>.
                            </div>
                        <?php elseif ($request['status'] === 'rejected'): ?>
                            <div class="comment-box">
                                You rejected this nomination on <?= e($request['rejected_at']) ?>.
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>