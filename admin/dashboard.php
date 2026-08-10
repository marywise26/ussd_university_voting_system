<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$queries = [
    'users' => "SELECT COUNT(*) AS total FROM users WHERE role = 'voter'",
    'active_users' => "SELECT COUNT(*) AS total FROM users WHERE role = 'voter' AND is_active = 1",
    'inactive_users' => "SELECT COUNT(*) AS total FROM users WHERE role = 'voter' AND is_active = 0",
    'elections' => "SELECT COUNT(*) AS total FROM elections",
    'pending' => "SELECT COUNT(*) AS total FROM candidate_applications WHERE status = 'pending'",
    'approved' => "SELECT COUNT(*) AS total FROM candidate_applications WHERE status = 'approved'",
    'rejected' => "SELECT COUNT(*) AS total FROM candidate_applications WHERE status = 'rejected'",
    'votes' => "SELECT COUNT(*) AS total FROM votes",
];

$counts = [];

foreach ($queries as $key => $sql) {
    $counts[$key] = (int)$pdo->query($sql)->fetch()['total'];
}

$studentActivationRate = $counts['users'] > 0
    ? round(($counts['active_users'] / $counts['users']) * 100)
    : 0;

$candidateTotal = $counts['pending'] + $counts['approved'] + $counts['rejected'];

$approvedRate = $candidateTotal > 0
    ? round(($counts['approved'] / $candidateTotal) * 100)
    : 0;

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-dashboard">

    <div class="admin-hero">
        <div>
            <p class="eyebrow mb-1">Election Administration</p>
            <h2>Control Center</h2>
        </div>

        <div class="admin-hero-badge">
            <i class="fas fa-shield-halved"></i>
            <span>Admin Console</span>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-icon blue">
                <i class="fas fa-user-graduate"></i>
            </div>

            <div class="admin-stat-content">
                <span>Registered Students</span>
                <strong><?= number_format($counts['users']) ?></strong>
                <small><?= number_format($counts['active_users']) ?> active accounts</small>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon purple">
                <i class="fas fa-calendar-check"></i>
            </div>

            <div class="admin-stat-content">
                <span>Total Elections</span>
                <strong><?= number_format($counts['elections']) ?></strong>
                <small>Election records created</small>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon amber">
                <i class="fas fa-user-check"></i>
            </div>

            <div class="admin-stat-content">
                <span>Pending Vetting</span>
                <strong><?= number_format($counts['pending']) ?></strong>
                <small>Applications awaiting review</small>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon green">
                <i class="fas fa-square-poll-vertical"></i>
            </div>

            <div class="admin-stat-content">
                <span>Votes Cast</span>
                <strong><?= number_format($counts['votes']) ?></strong>
                <small>Recorded vote entries</small>
            </div>
        </div>
    </div>

    <div class="admin-dashboard-grid">
        <div class="card admin-card">
            <div class="admin-card-header">
                <div>
                    <p class="eyebrow mb-1">Quick Actions</p>
                    <h3>Election Operations</h3>
                </div>

                <i class="fas fa-bolt"></i>
            </div>

            <div class="admin-action-grid">
                <a class="admin-action-card" href="<?= e(app_url('admin/elections.php')) ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <div>
                        <strong>Create Election</strong>
                        <span>Set up election records</span>
                    </div>
                </a>

                <a class="admin-action-card" href="<?= e(app_url('admin/positions.php')) ?>">
                    <i class="fas fa-layer-group"></i>
                    <div>
                        <strong>Add Positions</strong>
                        <span>Manage ballot positions</span>
                    </div>
                </a>

                <a class="admin-action-card" href="<?= e(app_url('admin/candidate_applications.php')) ?>">
                    <i class="fas fa-user-check"></i>
                    <div>
                        <strong>Vet Candidates</strong>
                        <span>Review applications</span>
                    </div>
                </a>

                <a class="admin-action-card" href="<?= e(app_url('admin/results.php')) ?>">
                    <i class="fas fa-chart-column"></i>
                    <div>
                        <strong>View Results</strong>
                        <span>Inspect election outcomes</span>
                    </div>
                </a>

                <a class="admin-action-card" href="<?= e(app_url('admin/students.php')) ?>">
                    <i class="fas fa-user-graduate"></i>
                    <div>
                        <strong>Manage Students</strong>
                        <span>Control voter access</span>
                    </div>
                </a>

                <a class="admin-action-card" href="<?= e(app_url('admin/audit_logs.php')) ?>">
                    <i class="fas fa-shield-halved"></i>
                    <div>
                        <strong>Audit Logs</strong>
                        <span>Track system activity</span>
                    </div>
                </a>
            </div>
        </div>

        <div class="card admin-card">
            <div class="admin-card-header">
                <div>
                    <p class="eyebrow mb-1">System Snapshot</p>
                    <h3>Portal Health</h3>
                </div>

                <i class="fas fa-chart-simple"></i>
            </div>

            <div class="admin-meter-list">
                <div class="admin-meter-item">
                    <div class="admin-meter-top">
                        <span>Student Activation</span>
                        <strong><?= $studentActivationRate ?>%</strong>
                    </div>

                    <div class="admin-meter">
                        <span style="width: <?= $studentActivationRate ?>%;"></span>
                    </div>

                    <small>
                        <?= number_format($counts['active_users']) ?> active /
                        <?= number_format($counts['users']) ?> registered
                    </small>
                </div>

                <div class="admin-meter-item">
                    <div class="admin-meter-top">
                        <span>Approved Candidates</span>
                        <strong><?= $approvedRate ?>%</strong>
                    </div>

                    <div class="admin-meter">
                        <span style="width: <?= $approvedRate ?>%;"></span>
                    </div>

                    <small>
                        <?= number_format($counts['approved']) ?> approved /
                        <?= number_format($candidateTotal) ?> total applications
                    </small>
                </div>
            </div>

            <div class="admin-mini-grid">
                <div>
                    <span>Inactive Students</span>
                    <strong><?= number_format($counts['inactive_users']) ?></strong>
                </div>

                <div>
                    <span>Approved Candidates</span>
                    <strong><?= number_format($counts['approved']) ?></strong>
                </div>

                <div>
                    <span>Rejected Candidates</span>
                    <strong><?= number_format($counts['rejected']) ?></strong>
                </div>

                <div>
                    <span>Total Applications</span>
                    <strong><?= number_format($candidateTotal) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <?php if ($counts['pending'] > 0): ?>
        <div class="admin-notice">
            <div>
                <i class="fas fa-circle-exclamation"></i>
            </div>

            <div>
                <strong><?= number_format($counts['pending']) ?> candidate application<?= $counts['pending'] === 1 ? '' : 's' ?> pending review.</strong>
                <p>Review pending applications before publishing or finalizing ballot candidates.</p>
            </div>

            <a class="btn" href="<?= e(app_url('admin/candidate_applications.php')) ?>">
                Review Now
            </a>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>