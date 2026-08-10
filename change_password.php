<?php
require_once __DIR__ . '/includes/init.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$userId = (int)($_SESSION['user_id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT id, role, full_name, password_hash, must_change_password
     FROM users
     WHERE id = ?
     LIMIT 1'
);
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    session_destroy();
    redirect('login.php');
}

if ($currentUser['role'] !== 'voter') {
    redirect('dashboard.php');
}

if ((int)$currentUser['must_change_password'] !== 1) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        set_flash('error', 'Please fill in all password fields.');
    } elseif (!password_verify($currentPassword, $currentUser['password_hash'])) {
        set_flash('error', 'Current temporary password is incorrect.');
    } elseif (strlen($newPassword) < 8) {
        set_flash('error', 'New password must be at least 8 characters.');
    } elseif ($newPassword !== $confirmPassword) {
        set_flash('error', 'New password and confirmation do not match.');
    } elseif (password_verify($newPassword, $currentUser['password_hash'])) {
        set_flash('error', 'New password cannot be the same as the temporary password.');
    } else {
        $stmt = $pdo->prepare(
            'UPDATE users
             SET password_hash = ?, must_change_password = 0
             WHERE id = ? AND role = "voter"'
        );

        $stmt->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            $userId,
        ]);

        $_SESSION['must_change_password'] = 0;

        log_activity($pdo, $userId, 'change_initial_password', 'users', $userId);

        set_flash('success', 'Password changed successfully. You can now use the portal.');
        redirect('dashboard.php');
    }
}

$page_title = 'Change Password';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .change-password-wrap {
        min-height: 70vh;
        display: grid;
        place-items: center;
        padding: 2rem 1rem;
    }

    .change-password-card {
        width: min(480px, 100%);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 1.6rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
    }

    .change-password-card h2 {
        margin: 0 0 .4rem;
        color: #0f172a;
    }

    .change-password-card p {
        margin: 0 0 1.2rem;
        color: #64748b;
        line-height: 1.5;
    }

    .password-field {
        margin-bottom: 1rem;
    }

    .password-field label {
        display: block;
        margin-bottom: .35rem;
        font-weight: 800;
        color: #334155;
        font-size: .9rem;
    }

    .password-field input {
        width: 100%;
        min-height: 46px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: .75rem .9rem;
        outline: none;
    }

    .password-field input:focus {
        border-color: rgba(37, 99, 235, .55);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .change-password-btn {
        width: 100%;
        border: 0;
        border-radius: 999px;
        padding: .85rem 1rem;
        font-weight: 900;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        cursor: pointer;
    }

    .security-note {
        margin-top: 1rem;
        font-size: .86rem;
        color: #64748b;
    }
</style>

<div class="change-password-wrap">
    <div class="change-password-card">
        <h2>Change Your Password</h2>
        <p>
            You are using a temporary password provided by the election administrator.
            Please create your own password before continuing.
        </p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(app_url('change_password.php')) ?>">
            <div class="password-field">
                <label for="current_password">Temporary Password</label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <div class="password-field">
                <label for="new_password">New Password</label>
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </div>

            <div class="password-field">
                <label for="confirm_password">Confirm New Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </div>

            <button type="submit" class="change-password-btn">
                Change Password and Continue
            </button>

            <div class="security-note">
                For security, do not reuse the temporary password.
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>