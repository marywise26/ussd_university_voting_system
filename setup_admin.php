<?php
require_once __DIR__ . '/includes/init.php';

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
$adminExists = (int)$stmt->fetch()['total'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$adminExists) {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($fullName === '' || $email === '' || $password === '') {
        set_flash('error', 'All fields are required.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Enter a valid email address.');
    } elseif (strlen($password) < 6) {
        set_flash('error', 'Password must be at least 6 characters.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (student_no, full_name, email, password_hash, role) VALUES (NULL, ?, ?, ?, "admin")');
        $stmt->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT)]);
        set_flash('success', 'Admin account created. Delete setup_admin.php after this step.');
        redirect('login.php');
    }
}

$page_title = 'Setup Admin';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width: 560px; margin: 0 auto;">
    <h1>Setup Admin</h1>
    <?php if ($adminExists): ?>
        <div class="alert alert-info">An admin account already exists. For safety, this page cannot create another admin.</div>
        <p><a class="btn" href="<?= e(app_url('login.php')) ?>">Go to Login</a></p>
    <?php else: ?>
        <form method="POST">
            <label>Admin Full Name</label>
            <input type="text" name="full_name" required>

            <label>Admin Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Create Admin</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
