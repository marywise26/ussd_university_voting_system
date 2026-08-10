<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
if (($_SESSION['role'] ?? '') === 'admin') {
    redirect('admin/dashboard.php');
}

$elections = $pdo->query("SELECT * FROM elections WHERE status = 'open' AND NOW() BETWEEN start_date AND end_date ORDER BY end_date ASC")->fetchAll();
$selectedElectionId = (int)($_GET['election_id'] ?? ($elections[0]['id'] ?? 0));
$selectedElection = null;
foreach ($elections as $election) {
    if ((int)$election['id'] === $selectedElectionId) {
        $selectedElection = $election;
        break;
    }
}

$ballot = [];
if ($selectedElectionId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM positions WHERE election_id = ? ORDER BY title ASC');
    $stmt->execute([$selectedElectionId]);
    $positions = $stmt->fetchAll();

    foreach ($positions as $position) {
        $stmt = $pdo->prepare("SELECT ca.*, u.full_name, u.student_no
            FROM candidate_applications ca
            JOIN users u ON ca.user_id = u.id
            WHERE ca.election_id = ? AND ca.position_id = ? AND ca.status = 'approved'
            ORDER BY u.full_name ASC");
        $stmt->execute([$selectedElectionId, (int)$position['id']]);
        $ballot[] = [
            'position' => $position,
            'candidates' => $stmt->fetchAll(),
            'has_voted' => has_voted($pdo, (int)$_SESSION['user_id'], $selectedElectionId, (int)$position['id'])
        ];
    }
}

$page_title = 'Ballot';
require_once __DIR__ . '/../includes/header.php';
?>
<?php if (!$elections): ?>
    <div class="card">No election is open for voting right now.</div>
<?php else: ?>
    <div class="card">
        <form method="GET">
            <label>Select Open Election</label>
            <select name="election_id" onchange="this.form.submit()">
                <?php foreach ($elections as $election): ?>
                    <option value="<?= (int)$election['id'] ?>" <?= (int)$election['id'] === $selectedElectionId ? 'selected' : '' ?>><?= e($election['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
<?php endif; ?>

<?php if ($selectedElection): ?>
    <div class="card">
        <h2><?= e($selectedElection['title']) ?></h2>
        <p><?= e($selectedElection['description']) ?></p>
        <p class="small">Voting closes: <?= e($selectedElection['end_date']) ?></p>
    </div>
<?php endif; ?>

<?php foreach ($ballot as $group): ?>
    <div class="card">
        <h2><?= e($group['position']['title']) ?></h2>
        <?php if ($group['has_voted']): ?>
            <div class="alert alert-info">You have already voted for this position.</div>
        <?php elseif (!$group['candidates']): ?>
            <p>No approved candidates for this position.</p>
        <?php else: ?>
            <form method="POST" action="<?= e(app_url('voter/vote.php')) ?>">
                <input type="hidden" name="election_id" value="<?= (int)$selectedElectionId ?>">
                <input type="hidden" name="position_id" value="<?= (int)$group['position']['id'] ?>">
                <?php foreach ($group['candidates'] as $candidate): ?>
                    <label class="card candidate-option">
                        <input type="radio" name="candidate_application_id" value="<?= (int)$candidate['id'] ?>" required style="width:auto; margin-right:8px;">
                        <?php if ($candidate['photo']): ?>
                            <img class="candidate-photo" src="<?= e(app_url($candidate['photo'])) ?>" alt="Candidate photo" style="vertical-align:middle; margin-right:10px;">
                        <?php endif; ?>
                        <strong><?= e($candidate['full_name']) ?></strong>
                        <span class="small">(<?= e($candidate['student_no']) ?>)</span>
                        <p><?= nl2br(e($candidate['manifesto'])) ?></p>
                    </label>
                <?php endforeach; ?>
                <button type="submit"><i class="fas fa-check-to-slot mr-1"></i> Submit Vote for <?= e($group['position']['title']) ?></button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
