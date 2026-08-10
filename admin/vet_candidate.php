<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/candidate_applications.php');
}

$applicationId = (int)($_POST['application_id'] ?? 0);
$decision = $_POST['decision'] ?? '';
$comment = trim($_POST['vetting_comment'] ?? '');

if ($applicationId <= 0 || !in_array($decision, ['approved', 'rejected'], true)) {
    set_flash('error', 'Invalid vetting request.');
    redirect('admin/candidate_applications.php');
}

$stmt = $pdo->prepare('UPDATE candidate_applications SET status = ?, vetting_comment = ?, vetted_by = ?, vetted_at = NOW() WHERE id = ? AND status = "pending"');
$stmt->execute([$decision, $comment, (int)$_SESSION['user_id'], $applicationId]);

if ($stmt->rowCount() > 0) {
    log_activity($pdo, (int)$_SESSION['user_id'], 'candidate_' . $decision, 'candidate_applications', $applicationId);
    set_flash('success', 'Candidate application ' . $decision . '.');
} else {
    set_flash('error', 'Application was not updated. It may have already been vetted.');
}

redirect('admin/candidate_applications.php');
