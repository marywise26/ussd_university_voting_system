<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    if (
        ($_SESSION['role'] ?? '') === 'voter' &&
        (int)($_SESSION['must_change_password'] ?? 0) === 1
    ) {
        redirect('change_password.php');
    }

    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim((string)($_POST['identity'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($identity === '' || $password === '') {
        set_flash('error', 'Please enter your email/registration number and password.');
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, role, full_name, password_hash, is_active, must_change_password
             FROM users
             WHERE email = ? OR student_no = ?
             LIMIT 1'
        );

        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();

        if (
            $user &&
            (int)$user['is_active'] === 1 &&
            password_verify($password, $user['password_hash'])
        ) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['must_change_password'] = (int)$user['must_change_password'];

            log_activity($pdo, (int)$user['id'], 'login', 'users', (int)$user['id']);

            if (
                $user['role'] === 'voter' &&
                (int)$user['must_change_password'] === 1
            ) {
                redirect('change_password.php');
            }

            redirect('dashboard.php');
        }

        set_flash('error', 'Invalid login credentials.');
    }
}

$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .forgot-password-row {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: .95rem;
        font-size: .92rem;
    }

    .forgot-password-row a {
        color: #2563eb;
        font-weight: 800;
        text-decoration: none;
    }

    .forgot-password-row a:hover {
        text-decoration: underline;
    }
</style>

<div class="auth-grid auth-grid-pro">
    <div class="auth-copy auth-copy-pro">
        <div class="portal-mark">
            <div class="portal-mark-icon">
                <i class="fas fa-check-to-slot"></i>
            </div>

            <div>
                <p class="eyebrow mb-1">Mzumbe Electoral Portal</p>
                <strong>Secure Muso Election System</strong>
            </div>
        </div>

        <h1>MUSO USSD-Based Voting System</h1>

        <p class="auth-lead">
            A controlled digital and USSD election platform for student registration, candidate vetting,
            ballot access, and transparent vote management.
        </p>

        <div class="auth-feature-grid">
            <div class="auth-feature-card">
                <i class="fas fa-user-shield"></i>

                <div>
                    <strong>Admin-controlled students</strong>
                    <span>Only verified students can access the portal.</span>
                </div>
            </div>

            <div class="auth-feature-card">
                <i class="fas fa-file-signature"></i>

                <div>
                    <strong>Candidate applications</strong>
                    <span>Students apply and wait for official approval.</span>
                </div>
            </div>

            <div class="auth-feature-card">
                <i class="fas fa-square-poll-vertical"></i>

                <div>
                    <strong>One vote per position</strong>
                    <span>Controlled voting prevents duplicate submissions.</span>
                </div>
            </div>

            <div class="auth-feature-card">
                <i class="fas fa-shield-halved"></i>

                <div>
                    <strong>Audit-ready activity</strong>
                    <span>Important actions are logged for accountability.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="login-panel-wrap">
        <div class="login-glow"></div>

        <div class="card login-card login-card-pro">
            <div class="login-card-header">
                <div class="login-logo-ring">
                    <img
                        src="<?= e(app_url('assets/images/Mzumbe_University_Logo.png')) ?>"
                        alt="Mzumbe University Logo"
                        class="login-logo"
                    >
                </div>

                <h2>Welcome back</h2>
                <p>Sign in with your email or student number.</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> login-alert">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= e(app_url('login.php')) ?>" class="login-form-pro">
                <label for="identity">Email or Student Number</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-user"></i>
                    <input
                        type="text"
                        id="identity"
                        name="identity"
                        autocomplete="username"
                        placeholder="e.g. student@example.com"
                        required
                    >
                </div>

                <label for="password">Password</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-lock"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-block login-btn-pro">
                    <i class="fas fa-arrow-right-to-bracket mr-1"></i>
                    Login to Portal
                </button>

                <div class="forgot-password-row">
                    <a href="<?= e(app_url('forgot_password.php')) ?>">
                        <i class="fas fa-key"></i> Forgot password?
                    </a>
                </div>

                <div class="login-help-text">
                    <i class="fas fa-circle-info"></i>
                    Use the credentials provided by the election administrator.
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
