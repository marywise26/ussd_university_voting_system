<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

set_flash('info', 'Public student registration is disabled. Students must be created or imported by the administrator.');
redirect('login.php');
