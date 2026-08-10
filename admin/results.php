<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$elections = $pdo->query('SELECT * FROM elections ORDER BY created_at DESC')->fetchAll();
$selectedElectionId = (int)($_GET['election_id'] ?? ($elections[0]['id'] ?? 0));
$selectedElection = null;
foreach ($elections as $election) {
    if ((int)$election['id'] === $selectedElectionId) {
        $selectedElection = $election;
        break;
    }
}

$results = [];
if ($selectedElectionId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM positions WHERE election_id = ? ORDER BY title ASC');
    $stmt->execute([$selectedElectionId]);
    $positions = $stmt->fetchAll();

    foreach ($positions as $position) {
        $stmt = $pdo->prepare("SELECT ca.id AS application_id, u.full_name, u.student_no, COUNT(v.id) AS vote_count
            FROM candidate_applications ca
            JOIN users u ON ca.user_id = u.id
            LEFT JOIN votes v ON v.candidate_application_id = ca.id
            WHERE ca.election_id = ? AND ca.position_id = ? AND ca.status = 'approved'
            GROUP BY ca.id, u.full_name, u.student_no
            ORDER BY vote_count DESC, u.full_name ASC");
        $stmt->execute([$selectedElectionId, (int)$position['id']]);
        $results[] = ['position' => $position, 'candidates' => $stmt->fetchAll()];
    }
}

$page_title = 'Results';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <form method="GET">
        <label>Select Election</label>
        <select name="election_id" onchange="this.form.submit()">
            <?php foreach ($elections as $election): ?>
                <option value="<?= (int)$election['id'] ?>" <?= (int)$election['id'] === $selectedElectionId ? 'selected' : '' ?>><?= e($election['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<?php if ($selectedElection): ?>
    <div class="card">
        <h2><?= e($selectedElection['title']) ?></h2>
        <p>Status: <span class="badge badge-<?= e($selectedElection['status']) ?>"><?= e($selectedElection['status']) ?></span></p>
    </div>
<?php endif; ?>
<?php foreach ($results as $group): ?>
    <div class="card">
        <h2><?= e($group['position']['title']) ?></h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Candidate</th><th>Student No</th><th>Votes</th></tr></thead>
                <tbody>
                <?php foreach ($group['candidates'] as $candidate): ?>
                    <tr>
                        <td><?= e($candidate['full_name']) ?></td>
                        <td><?= e($candidate['student_no']) ?></td>
                        <td><strong><?= (int)$candidate['vote_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$group['candidates']): ?>
                    <tr><td colspan="3">No approved candidates for this position.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
