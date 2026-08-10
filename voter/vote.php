<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
if (($_SESSION['role'] ?? '') === 'admin') {
    redirect('admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('voter/ballot.php');
}

$voterId = (int)$_SESSION['user_id'];
$electionId = (int)($_POST['election_id'] ?? 0);
$positionId = (int)($_POST['position_id'] ?? 0);
$candidateApplicationId = (int)($_POST['candidate_application_id'] ?? 0);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT * FROM elections WHERE id = ? LIMIT 1 FOR UPDATE');
    $stmt->execute([$electionId]);
    $election = $stmt->fetch();

    if (!$election || !is_election_open($election)) {
        throw new RuntimeException('This election is not open for voting.');
    }

    $stmt = $pdo->prepare('SELECT id FROM positions WHERE id = ? AND election_id = ? LIMIT 1');
    $stmt->execute([$positionId, $electionId]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('Invalid position for this election.');
    }

    $stmt = $pdo->prepare("SELECT id FROM candidate_applications WHERE id = ? AND election_id = ? AND position_id = ? AND status = 'approved' LIMIT 1");
    $stmt->execute([$candidateApplicationId, $electionId, $positionId]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('Invalid candidate selection.');
    }

    $stmt = $pdo->prepare('INSERT INTO votes (voter_id, election_id, position_id, candidate_application_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$voterId, $electionId, $positionId, $candidateApplicationId]);

    log_activity($pdo, $voterId, 'cast_vote', 'votes', (int)$pdo->lastInsertId());
    $pdo->commit();
    set_flash('success', 'Vote submitted successfully.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', 'You have already voted for this position.');
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', $e->getMessage());
}

redirect('voter/ballot.php?election_id=' . $electionId);
