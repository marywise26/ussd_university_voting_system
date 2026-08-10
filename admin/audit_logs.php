<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$logs = $pdo->query('SELECT a.*, u.full_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 200')->fetchAll();

$page_title = 'Audit Logs';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card table-wrap">
    <table>
        <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= e($log['created_at']) ?></td>
                <td><?= e($log['full_name'] ?? 'System') ?></td>
                <td><?= e($log['action']) ?></td>
                <td><?= e($log['entity']) ?> #<?= e((string)$log['entity_id']) ?></td>
                <td><?= e($log['ip_address']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
