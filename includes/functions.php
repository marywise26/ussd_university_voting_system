<?php
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . app_url($path));
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_message(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in first.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        die('Access denied. Admins only.');
    }
}

function current_user(PDO $pdo): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function log_activity(PDO $pdo, ?int $userId, string $action, string $entity = '', ?int $entityId = null): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $action, $entity, $entityId, $ip]);
}

function is_election_open(array $election): bool
{
    $now = new DateTimeImmutable('now');
    $start = new DateTimeImmutable($election['start_date']);
    $end = new DateTimeImmutable($election['end_date']);
    return $election['status'] === 'open' && $now >= $start && $now <= $end;
}

function has_voted(PDO $pdo, int $voterId, int $electionId, int $positionId): bool
{
    $stmt = $pdo->prepare('SELECT id FROM votes WHERE voter_id = ? AND election_id = ? AND position_id = ? LIMIT 1');
    $stmt->execute([$voterId, $electionId, $positionId]);
    return (bool) $stmt->fetch();
}

function is_approved_candidate(PDO $pdo, int $userId): bool
{
    $stmt = $pdo->prepare("SELECT id FROM candidate_applications WHERE user_id = ? AND status = 'approved' LIMIT 1");
    $stmt->execute([$userId]);
    return (bool) $stmt->fetch();
}

function upload_candidate_photo(array $file): ?string
{
    if (empty($file['name'])) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Photo upload failed.');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Photo must be 2MB or smaller.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, and WEBP photos are allowed.');
    }

    $filename = 'candidate_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    $destinationDir = __DIR__ . '/../assets/uploads/candidates';

    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0775, true);
    }

    $destination = $destinationDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save uploaded photo.');
    }

    return 'assets/uploads/candidates/' . $filename;
}
