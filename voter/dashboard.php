<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
if (($_SESSION['role'] ?? '') === 'admin') {
    redirect('admin/dashboard.php');
}

$user = current_user($pdo);
$isCandidate = is_approved_candidate($pdo, (int)$_SESSION['user_id']);

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM candidate_applications WHERE user_id = ?");
$stmt->execute([(int)$_SESSION['user_id']]);
$appCount = (int)$stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM votes WHERE voter_id = ?");
$stmt->execute([(int)$_SESSION['user_id']]);
$voteCount = (int)$stmt->fetch()['total'];

$openElections = $pdo->query("SELECT * FROM elections WHERE status = 'open' AND NOW() BETWEEN start_date AND end_date ORDER BY end_date ASC")->fetchAll();

$page_title = 'Voter Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="grid">
    <div class="card stat-card"><h3>Candidate Applications</h3><div class="stat"><?= $appCount ?></div></div>
    <div class="card stat-card"><h3>Votes Cast</h3><div class="stat"><?= $voteCount ?></div></div>
    <div class="card stat-card"><h3>Approved Candidate</h3><div class="stat"><?= $isCandidate ? 'Yes' : 'No' ?></div></div>
</div>
<div class="card">
    <h2>Welcome, <?= e($user['full_name'] ?? 'Student') ?></h2><p class="small">Use this portal to apply for candidacy, track vetting status, and cast votes during open elections.</p>
    <div class="actions">
        <a class="btn" href="<?= e(app_url('voter/apply_candidate.php')) ?>"><i class="fas fa-file-signature mr-1"></i> Apply as Candidate</a>
        <a class="btn" href="<?= e(app_url('voter/application_status.php')) ?>"><i class="fas fa-list-check mr-1"></i> Application Status</a>
        <a class="btn" href="<?= e(app_url('voter/ballot.php')) ?>"><i class="fas fa-square-poll-vertical mr-1"></i> Go to Ballot</a>
    </div>
</div>
<div class="card">
    <h2>Open Elections</h2>
    <?php if ($openElections): ?>
        <ul>
            <?php foreach ($openElections as $election): ?>
                <li><strong><?= e($election['title']) ?></strong> closes on <?= e($election['end_date']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No election is open right now.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
