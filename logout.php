<?php
require_once __DIR__ . '/includes/init.php';
if (is_logged_in()) {
    log_activity($pdo, (int)$_SESSION['user_id'], 'logout', 'users', (int)$_SESSION['user_id']);
}
$_SESSION = [];
session_destroy();
session_start();
set_flash('success', 'Logged out successfully.');
redirect('login.php');
