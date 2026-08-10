<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

/**
 * Accepts Tanzanian numbers in these formats:
 * 0712345678, 712345678, 255712345678, +255712345678.
 */
function normalize_tanzania_phone(string $phone): ?string
{
    $phone = trim($phone);
    $phone = str_replace([' ', '-', '(', ')'], '', $phone);
    $phone = preg_replace('/[^0-9+]/', '', (string)$phone);

    if (substr($phone, 0, 4) === '+255') {
        $local = substr($phone, 4);
    } elseif (substr($phone, 0, 3) === '255') {
        $local = substr($phone, 3);
    } elseif (substr($phone, 0, 1) === '0') {
        $local = substr($phone, 1);
    } else {
        $local = $phone;
    }

    if (preg_match('/^[67][0-9]{8}$/', $local)) {
        return '+255' . $local;
    }

    return null;
}

function nextsms_config_value(string $key, string $default = ''): string
{
    if (defined($key)) {
        return trim((string)constant($key));
    }

    $value = getenv($key);
    if ($value === false || trim((string)$value) === '') {
        return $default;
    }

    return trim((string)$value);
}

function nextsms_recipient_number(string $phone): string
{
    return ltrim(trim($phone), '+');
}

/**
 * Configure these in your config/init file or server environment:
 * NEXTSMS_USERNAME
 * NEXTSMS_PASSWORD
 * NEXTSMS_SENDER_ID
 *
 * Example:
 * define('NEXTSMS_USERNAME', 'your_username');
 * define('NEXTSMS_PASSWORD', 'your_password');
 * define('NEXTSMS_SENDER_ID', 'UniMessage');
 */
function send_nextsms(string $phone, string $message): array
{
    $username = nextsms_config_value('NEXTSMS_USERNAME', nextsms_config_value('NEXTSMS_USER', nextsms_config_value('SMS_USERNAME')));
    $password = nextsms_config_value('NEXTSMS_PASSWORD', nextsms_config_value('NEXTSMS_PASS', nextsms_config_value('SMS_PASSWORD')));
    $senderId = nextsms_config_value('NEXTSMS_SENDER_ID', nextsms_config_value('SMS_SENDER_ID', 'UniMessage'));

    if ($username === '' || $password === '' || $senderId === '') {
        return ['ok' => false, 'error' => 'NextSMS credentials are not configured.'];
    }

    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'PHP cURL extension is not enabled.'];
    }

    $payload = [
        'from' => $senderId,
        'to' => nextsms_recipient_number($phone),
        'text' => $message,
    ];

    $ch = curl_init('https://messaging-service.co.tz/api/sms/v1/text/single');
    if (!$ch) {
        return ['ok' => false, 'error' => 'Could not initialize cURL.'];
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode($username . ':' . $password),
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 20,
    ]);

    $responseBody = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($responseBody === false) {
        return ['ok' => false, 'error' => $curlError ?: 'NextSMS request failed.'];
    }

    return [
        'ok' => $statusCode >= 200 && $statusCode < 300,
        'status' => $statusCode,
        'response' => $responseBody,
    ];
}

function reset_session_is_valid(): bool
{
    if (empty($_SESSION['password_reset']) || !is_array($_SESSION['password_reset'])) {
        return false;
    }

    $reset = $_SESSION['password_reset'];

    if (empty($reset['user_id']) || empty($reset['otp_hash']) || empty($reset['expires_at'])) {
        return false;
    }

    return (int)$reset['expires_at'] >= time();
}

function clear_password_reset_session(): void
{
    unset($_SESSION['password_reset']);
}

function build_reset_sms(string $fullName, string $otp): string
{
    return "Dear {$fullName}, your password reset code is {$otp}. It expires in 10 minutes. If you did not request this, ignore this SMS.";
}

$step = reset_session_is_valid() ? 'verify' : 'request';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'request_code') {
        $identity = trim((string)($_POST['identity'] ?? ''));
        $phone = normalize_tanzania_phone((string)($_POST['phone'] ?? ''));

        if ($identity === '' || !$phone) {
            set_flash('error', 'Enter your email/student number and a valid Tanzania phone number.');
            redirect('forgot_password.php');
        }

        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, student_no, phone, is_active
             FROM users
             WHERE email = ? OR student_no = ?
             LIMIT 1'
        );
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();

        $storedPhone = $user ? normalize_tanzania_phone((string)($user['phone'] ?? '')) : null;

        if (!$user || (int)$user['is_active'] !== 1 || !$storedPhone || $storedPhone !== $phone) {
            clear_password_reset_session();
            set_flash('error', 'We could not verify that account. Check your details or contact the administrator.');
            redirect('forgot_password.php');
        }

        $otp = (string)random_int(100000, 999999);

        $_SESSION['password_reset'] = [
            'user_id' => (int)$user['id'],
            'full_name' => (string)$user['full_name'],
            'otp_hash' => password_hash($otp, PASSWORD_DEFAULT),
            'expires_at' => time() + 600,
            'attempts' => 0,
        ];

        $sms = send_nextsms($phone, build_reset_sms((string)$user['full_name'], $otp));

        if (!$sms['ok']) {
            error_log('Password reset SMS failed for user ' . (int)$user['id'] . ': ' . json_encode($sms));
            clear_password_reset_session();
            set_flash('error', 'Could not send reset code by SMS. Contact the administrator or check SMS settings.');
            redirect('forgot_password.php');
        }

        set_flash('success', 'A reset code has been sent to your registered phone number.');
        redirect('forgot_password.php?step=verify');
    }

    if ($action === 'reset_password') {
        if (!reset_session_is_valid()) {
            clear_password_reset_session();
            set_flash('error', 'Your reset session has expired. Request a new code.');
            redirect('forgot_password.php');
        }

        $reset = $_SESSION['password_reset'];
        $otp = trim((string)($_POST['otp'] ?? ''));
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ((int)($reset['attempts'] ?? 0) >= 5) {
            clear_password_reset_session();
            set_flash('error', 'Too many invalid attempts. Request a new reset code.');
            redirect('forgot_password.php');
        }

        if ($otp === '' || $newPassword === '' || $confirmPassword === '') {
            set_flash('error', 'Enter the reset code and your new password.');
            redirect('forgot_password.php?step=verify');
        }

        if ($newPassword !== $confirmPassword) {
            set_flash('error', 'The new passwords do not match.');
            redirect('forgot_password.php?step=verify');
        }

        if (strlen($newPassword) < 6) {
            set_flash('error', 'Password must be at least 6 characters.');
            redirect('forgot_password.php?step=verify');
        }

        if (!password_verify($otp, (string)$reset['otp_hash'])) {
            $_SESSION['password_reset']['attempts'] = (int)($reset['attempts'] ?? 0) + 1;
            set_flash('error', 'Invalid reset code.');
            redirect('forgot_password.php?step=verify');
        }

        $stmt = $pdo->prepare(
            'UPDATE users
             SET password_hash = ?
             WHERE id = ? AND is_active = 1'
        );
        $stmt->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            (int)$reset['user_id'],
        ]);

        log_activity($pdo, (int)$reset['user_id'], 'reset_password', 'users', (int)$reset['user_id']);

        clear_password_reset_session();

        set_flash('success', 'Password reset successfully. You can now login with your new password.');
        redirect('login.php');
    }
}

if (($_GET['step'] ?? '') === 'verify' && reset_session_is_valid()) {
    $step = 'verify';
}

$page_title = 'Forgot Password';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .forgot-page {
        width: 100%;
        min-height: calc(100vh - 120px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        box-sizing: border-box;
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, .14), transparent 32%),
            radial-gradient(circle at bottom right, rgba(56, 189, 248, .12), transparent 30%);
    }

    .forgot-card {
        width: min(100%, 520px);
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 1.6rem;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .12);
    }

    .forgot-form {
        width: 100%;
    }

    .forgot-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        border-radius: 18px;
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        font-size: 1.4rem;
        margin-bottom: 1rem;
    }

    .forgot-card h1 {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.6rem, 4vw, 2.1rem);
        letter-spacing: -.04em;
    }

    .forgot-card p {
        color: #64748b;
        line-height: 1.55;
        margin: .6rem 0 1.2rem;
    }

    .forgot-form label {
        display: block;
        margin: .95rem 0 .4rem;
        color: #334155;
        font-size: .88rem;
        font-weight: 800;
    }

    .forgot-form input {
        width: 100%;
        min-height: 46px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: .75rem .9rem;
        color: #0f172a;
        background: #fff;
        outline: none;
    }

    .forgot-form input:focus {
        border-color: rgba(37, 99, 235, .55);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .phone-control-reset {
        display: flex;
        align-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .phone-control-reset:focus-within {
        border-color: rgba(37, 99, 235, .55);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .phone-control-reset span {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        padding: 0 .9rem;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 900;
        border-right: 1px solid #e2e8f0;
    }

    .phone-control-reset input {
        border: 0;
        border-radius: 0;
        box-shadow: none !important;
    }

    .forgot-actions {
        display: grid;
        gap: .8rem;
        margin-top: 1.2rem;
    }

    .forgot-btn {
        min-height: 46px;
        border: 0;
        border-radius: 999px;
        padding: .8rem 1rem;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        color: #ffffff;
        font-weight: 900;
        cursor: pointer;
    }

    .forgot-back {
        text-align: center;
        color: #2563eb;
        font-weight: 800;
        text-decoration: none;
    }

    .forgot-back:hover {
        text-decoration: underline;
    }

    .forgot-note {
        margin-top: 1rem;
        padding: .85rem;
        border-radius: 14px;
        background: #f8fafc;
        color: #64748b;
        font-size: .88rem;
        line-height: 1.45;
    }

    .forgot-alert {
        margin: 1rem 0;
    }
</style>

<div class="forgot-page">
    <div class="forgot-card">
        <div class="forgot-icon">
            <i class="fas fa-key"></i>
        </div>

        <?php if ($step === 'verify'): ?>
            <h1>Set a new password</h1>
            <p>Enter the 6-digit code sent to your registered phone number, then create your new password.</p>
        <?php else: ?>
            <h1>Forgot password?</h1>
            <p>Enter your email or student number and your registered phone number. We will send a reset code by SMS.</p>
        <?php endif; ?>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> forgot-alert">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 'verify'): ?>
            <form method="POST" action="<?= e(app_url('forgot_password.php?step=verify')) ?>" class="forgot-form">
                <input type="hidden" name="action" value="reset_password">

                <label for="otp">Reset Code</label>
                <input
                    type="text"
                    id="otp"
                    name="otp"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    placeholder="Enter 6-digit code"
                    required
                >

                <label for="new_password">New Password</label>
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    autocomplete="new-password"
                    placeholder="Minimum 6 characters"
                    required
                >

                <label for="confirm_password">Confirm New Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    autocomplete="new-password"
                    placeholder="Repeat new password"
                    required
                >

                <div class="forgot-actions">
                    <button type="submit" class="forgot-btn">
                        <i class="fas fa-shield-check"></i> Reset Password
                    </button>

                    <a class="forgot-back" href="<?= e(app_url('forgot_password.php')) ?>">
                        Request another code
                    </a>
                </div>
            </form>
        <?php else: ?>
            <form method="POST" action="<?= e(app_url('forgot_password.php')) ?>" class="forgot-form">
                <input type="hidden" name="action" value="request_code">

                <label for="identity">Email or Student Number</label>
                <input
                    type="text"
                    id="identity"
                    name="identity"
                    autocomplete="username"
                    placeholder="e.g. BCS/2026/001 or student@mzumbe.ac.tz"
                    required
                >

                <label for="phone">Registered Phone Number</label>
                <div class="phone-control-reset">
                    <span>+255</span>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        inputmode="numeric"
                        pattern="[67][0-9]{8}"
                        maxlength="9"
                        placeholder="712345678"
                        required
                    >
                </div>

                <div class="forgot-actions">
                    <button type="submit" class="forgot-btn">
                        <i class="fas fa-paper-plane"></i> Send Reset Code
                    </button>

                    <a class="forgot-back" href="<?= e(app_url('login.php')) ?>">
                        Back to login
                    </a>
                </div>

                <div class="forgot-note">
                    <i class="fas fa-circle-info"></i>
                    The phone number must match the number registered by the administrator.
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.getElementById('phone');
        const otpInput = document.getElementById('otp');

        if (phoneInput) {
            phoneInput.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 9);
            });
        }

        if (otpInput) {
            otpInput.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
            });
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
