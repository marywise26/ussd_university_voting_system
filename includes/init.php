<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$currentScript = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));

$allowedBeforePasswordChange = [
    'change_password.php',
    'logout.php',
];

if (
    is_logged_in() &&
    ($_SESSION['role'] ?? '') === 'voter' &&
    !in_array($currentScript, $allowedBeforePasswordChange, true)
) {
    if (!array_key_exists('must_change_password', $_SESSION)) {
        $stmt = $pdo->prepare('SELECT must_change_password FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int)($_SESSION['user_id'] ?? 0)]);
        $_SESSION['must_change_password'] = (int)$stmt->fetchColumn();
    }

    if ((int)($_SESSION['must_change_password'] ?? 0) === 1) {
        redirect('change_password.php');
    }
}