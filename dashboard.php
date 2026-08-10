<?php
require_once __DIR__ . '/includes/init.php';
require_login();

if (($_SESSION['role'] ?? '') === 'admin') {
    redirect('admin/dashboard.php');
}
redirect('voter/dashboard.php');
