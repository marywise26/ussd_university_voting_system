<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

function db_table_exists(PDO $pdo, string $table): bool
{
    static $cache = [];

    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.tables
         WHERE table_schema = DATABASE()
           AND table_name = ?'
    );
    $stmt->execute([$table]);

    return $cache[$table] = ((int)$stmt->fetchColumn() > 0);
}

function db_column_exists(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];

    $key = $table . '.' . $column;

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = ?
           AND column_name = ?'
    );
    $stmt->execute([$table, $column]);

    return $cache[$key] = ((int)$stmt->fetchColumn() > 0);
}

function count_related_table_for_election(PDO $pdo, string $table, int $electionId): int
{
    if (!db_table_exists($pdo, $table)) {
        return 0;
    }

    if (db_column_exists($pdo, $table, 'election_id')) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE election_id = ?");
        $stmt->execute([$electionId]);

        return (int)$stmt->fetchColumn();
    }

    if (
        db_column_exists($pdo, $table, 'position_id')
        && db_table_exists($pdo, 'positions')
        && db_column_exists($pdo, 'positions', 'id')
        && db_column_exists($pdo, 'positions', 'election_id')
    ) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM {$table} related
             INNER JOIN positions p ON p.id = related.position_id
             WHERE p.election_id = ?"
        );
        $stmt->execute([$electionId]);

        return (int)$stmt->fetchColumn();
    }

    return 0;
}

function election_related_counts(PDO $pdo, int $electionId): array
{
    $positions = 0;

    if (db_table_exists($pdo, 'positions') && db_column_exists($pdo, 'positions', 'election_id')) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM positions WHERE election_id = ?');
        $stmt->execute([$electionId]);
        $positions = (int)$stmt->fetchColumn();
    }

    $memberTables = ['candidate_applications', 'candidates', 'members'];
    $voteTables = ['votes'];

    $members = 0;
    foreach ($memberTables as $table) {
        $members += count_related_table_for_election($pdo, $table, $electionId);
    }

    $votes = 0;
    foreach ($voteTables as $table) {
        $votes += count_related_table_for_election($pdo, $table, $electionId);
    }

    return [
        'positions' => $positions,
        'members' => $members,
        'votes' => $votes,
    ];
}

function election_is_locked(array $counts): bool
{
    return ((int)($counts['members'] ?? 0) > 0) || ((int)($counts['votes'] ?? 0) > 0);
}

function normalize_datetime_local(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);

    if (!$timestamp) {
        return '';
    }

    return date('Y-m-d H:i:s', $timestamp);
}

function datetime_local_value(?string $value): string
{
    $timestamp = strtotime((string)$value);

    if (!$timestamp) {
        return '';
    }

    return date('Y-m-d\TH:i', $timestamp);
}

function valid_election_status(string $status): bool
{
    return in_array($status, ['draft', 'open', 'closed'], true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDate = normalize_datetime_local((string)($_POST['start_date'] ?? ''));
        $endDate = normalize_datetime_local((string)($_POST['end_date'] ?? ''));
        $status = $_POST['status'] ?? 'draft';

        if ($title === '' || $startDate === '' || $endDate === '') {
            set_flash('error', 'Title, start date, and end date are required.');
        } elseif (strtotime($endDate) <= strtotime($startDate)) {
            set_flash('error', 'End date must be after start date.');
        } elseif (!valid_election_status($status)) {
            set_flash('error', 'Invalid election status.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO elections (title, description, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$title, $description, $startDate, $endDate, $status]);

            log_activity($pdo, (int)$_SESSION['user_id'], 'create_election', 'elections', (int)$pdo->lastInsertId());
            set_flash('success', 'Election created successfully.');
        }
    }

    if ($action === 'edit') {
        $electionId = (int)($_POST['election_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDate = normalize_datetime_local((string)($_POST['start_date'] ?? ''));
        $endDate = normalize_datetime_local((string)($_POST['end_date'] ?? ''));
        $status = $_POST['status'] ?? 'draft';
        $counts = election_related_counts($pdo, $electionId);

        if ($electionId <= 0 || $title === '' || $startDate === '' || $endDate === '') {
            set_flash('error', 'Election, title, start date, and end date are required.');
        } elseif (election_is_locked($counts)) {
            set_flash('error', 'This election cannot be edited because it already has members/candidates or votes.');
        } elseif (strtotime($endDate) <= strtotime($startDate)) {
            set_flash('error', 'End date must be after start date.');
        } elseif (!valid_election_status($status)) {
            set_flash('error', 'Invalid election status.');
        } else {
            $stmt = $pdo->prepare(
                'UPDATE elections
                 SET title = ?, description = ?, start_date = ?, end_date = ?, status = ?
                 WHERE id = ?'
            );
            $stmt->execute([$title, $description, $startDate, $endDate, $status, $electionId]);

            log_activity($pdo, (int)$_SESSION['user_id'], 'edit_election', 'elections', $electionId);
            set_flash('success', 'Election updated successfully.');
        }
    }

    if ($action === 'status') {
        $electionId = (int)($_POST['election_id'] ?? 0);
        $status = $_POST['status'] ?? 'draft';

        if ($electionId > 0 && valid_election_status($status)) {
            $stmt = $pdo->prepare('UPDATE elections SET status = ? WHERE id = ?');
            $stmt->execute([$status, $electionId]);

            log_activity($pdo, (int)$_SESSION['user_id'], 'update_election_status', 'elections', $electionId);
            set_flash('success', 'Election status updated.');
        }
    }

    if ($action === 'delete') {
        $electionId = (int)($_POST['election_id'] ?? 0);
        $counts = election_related_counts($pdo, $electionId);

        if ($electionId <= 0) {
            set_flash('error', 'Invalid election selected.');
        } elseif (election_is_locked($counts)) {
            set_flash('error', 'This election cannot be deleted because it already has members/candidates or votes.');
        } else {
            try {
                $pdo->beginTransaction();

                if (db_table_exists($pdo, 'positions') && db_column_exists($pdo, 'positions', 'election_id')) {
                    $stmt = $pdo->prepare('DELETE FROM positions WHERE election_id = ?');
                    $stmt->execute([$electionId]);
                }

                $stmt = $pdo->prepare('DELETE FROM elections WHERE id = ?');
                $stmt->execute([$electionId]);

                if ($stmt->rowCount() > 0) {
                    log_activity($pdo, (int)$_SESSION['user_id'], 'delete_election', 'elections', $electionId);
                    $pdo->commit();
                    set_flash('success', 'Election deleted successfully.');
                } else {
                    $pdo->rollBack();
                    set_flash('error', 'Election was not found.');
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                set_flash('error', 'Could not delete this election because related records still exist.');
            }
        }
    }

    redirect('admin/elections.php');
}

$elections = $pdo->query('SELECT * FROM elections ORDER BY created_at DESC')->fetchAll();

$page_title = 'Manage Elections';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .elections-page {
        --primary: #2563eb;
        --ink: #0f172a;
        --muted: #64748b;
        --line: #e2e8f0;
        --danger: #dc2626;
        --success: #16a34a;
        color: var(--ink);
    }

    .elections-page label {
        display: block;
        margin: .75rem 0 .35rem;
        font-weight: 800;
        color: #334155;
        font-size: .88rem;
    }

    .elections-page input,
    .elections-page select,
    .elections-page textarea {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: .68rem .8rem;
        background: #fff;
        color: var(--ink);
        outline: none;
    }

    .elections-page textarea {
        min-height: 88px;
        resize: vertical;
    }

    .elections-page input:focus,
    .elections-page select:focus,
    .elections-page textarea:focus {
        border-color: rgba(37, 99, 235, .55);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .btn-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 38px;
        border: 0;
        border-radius: 999px;
        padding: .62rem .9rem;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        color: #fff;
    }

    .btn-muted-modern {
        background: #eef2ff;
        color: #1d4ed8;
    }

    .btn-danger-modern {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-disabled-modern {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .election-actions {
        display: flex;
        gap: .45rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .election-actions form {
        margin: 0;
    }

    .status-line {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-top: .45rem;
    }

    .mini-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .28rem .55rem;
        background: #f8fafc;
        color: #475569;
        font-size: .76rem;
        font-weight: 800;
    }

    .lock-pill {
        background: #fee2e2;
        color: #991b1b;
    }

    .open-pill {
        background: #dcfce7;
        color: #166534;
    }

    .modal-backdrop[hidden] {
        display: none;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: grid;
        place-items: center;
        padding: 1rem;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(8px);
    }

    .edit-modal {
        width: min(760px, 100%);
        max-height: 92vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 20px;
        border: 1px solid var(--line);
        box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
    }

    .modal-titlebar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.35rem 0;
    }

    .modal-titlebar h2 {
        margin: 0;
        font-size: 1.2rem;
    }

    .modal-titlebar p {
        margin: .35rem 0 0;
        color: var(--muted);
    }

    .modal-body {
        padding: 1.35rem;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 999px;
        background: #f1f5f9;
        color: #0f172a;
        cursor: pointer;
        font-weight: 900;
    }

    body.modal-open {
        overflow: hidden;
    }

    @media (max-width: 720px) {
        .election-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .election-actions .btn-modern,
        .election-actions form,
        .election-actions select {
            width: 100%;
        }
    }
</style>

<div class="elections-page">
    <div class="card">
        <h2>Create Election</h2>

        <form method="POST">
            <input type="hidden" name="action" value="create">

            <label>Title</label>
            <input type="text" name="title" placeholder="e.g. General Election 2026" required>

            <label>Description</label>
            <textarea name="description"></textarea>

            <div class="grid">
                <div>
                    <label>Start Date and Time</label>
                    <input type="datetime-local" name="start_date" required>
                </div>

                <div>
                    <label>End Date and Time</label>
                    <input type="datetime-local" name="end_date" required>
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="draft">Draft</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>

            <br>
            <button type="submit" class="btn-modern btn-primary-modern">
                <i class="fas fa-save"></i> Save Election
            </button>
        </form>
    </div>

    <div class="card">
        <h2>Existing Elections</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Rules</th>
                        <th>Change Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($elections as $election): ?>
                    <?php
                        $counts = election_related_counts($pdo, (int)$election['id']);
                        $isLocked = election_is_locked($counts);
                    ?>

                    <tr>
                        <td>
                            <strong><?= e($election['title']) ?></strong><br>
                            <span class="small"><?= e($election['description']) ?></span>

                            <div class="status-line">
                                <span class="mini-pill"><?= (int)$counts['positions'] ?> position<?= (int)$counts['positions'] === 1 ? '' : 's' ?></span>
                                <span class="mini-pill"><?= (int)$counts['members'] ?> member/candidate<?= (int)$counts['members'] === 1 ? '' : 's' ?></span>
                                <span class="mini-pill"><?= (int)$counts['votes'] ?> vote<?= (int)$counts['votes'] === 1 ? '' : 's' ?></span>
                            </div>
                        </td>

                        <td>
                            <?= e($election['start_date']) ?><br>
                            <?= e($election['end_date']) ?>
                        </td>

                        <td>
                            <span class="badge badge-<?= e($election['status']) ?>">
                                <?= e($election['status']) ?>
                            </span>
                        </td>

                        <td>
                            <?php if ($isLocked): ?>
                                <span class="mini-pill lock-pill">
                                    <i class="fas fa-lock"></i>&nbsp; Edit/Delete locked
                                </span>
                            <?php else: ?>
                                <span class="mini-pill open-pill">
                                    <i class="fas fa-unlock"></i>&nbsp; Editable
                                </span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <form method="POST" class="actions">
                                <input type="hidden" name="action" value="status">
                                <input type="hidden" name="election_id" value="<?= (int)$election['id'] ?>">

                                <select name="status">
                                    <?php foreach (['draft', 'open', 'closed'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $election['status'] === $status ? 'selected' : '' ?>>
                                            <?= e(ucfirst($status)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit">Update</button>
                            </form>
                        </td>

                        <td>
                            <div class="election-actions">
                                <?php if (!$isLocked): ?>
                                    <button
                                        type="button"
                                        class="btn-modern btn-muted-modern"
                                        data-edit-election
                                        data-id="<?= (int)$election['id'] ?>"
                                        data-title="<?= e($election['title']) ?>"
                                        data-description="<?= e($election['description']) ?>"
                                        data-start="<?= e(datetime_local_value($election['start_date'])) ?>"
                                        data-end="<?= e(datetime_local_value($election['end_date'])) ?>"
                                        data-status="<?= e($election['status']) ?>"
                                    >
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <form method="POST" onsubmit="return confirm('Delete this election? This will also delete its empty positions.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="election_id" value="<?= (int)$election['id'] ?>">
                                        <button type="submit" class="btn-modern btn-danger-modern">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn-modern btn-disabled-modern" disabled>
                                        <i class="fas fa-lock"></i> Locked
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$elections): ?>
                    <tr>
                        <td colspan="6" class="empty-state">No elections created yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="editElectionModal" hidden>
    <div class="edit-modal" role="dialog" aria-modal="true" aria-labelledby="editElectionTitle">
        <div class="modal-titlebar">
            <div>
                <h2 id="editElectionTitle">Edit Election</h2>
                <p>You can edit this election because it has no members/candidates or votes.</p>
            </div>

            <button type="button" class="modal-close" data-close-edit-election aria-label="Close edit election form">
                ×
            </button>
        </div>

        <form method="POST" class="modal-body">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="election_id" id="edit_election_id">

            <label for="edit_title">Title</label>
            <input type="text" name="title" id="edit_title" required>

            <label for="edit_description">Description</label>
            <textarea name="description" id="edit_description"></textarea>

            <div class="grid">
                <div>
                    <label for="edit_start_date">Start Date and Time</label>
                    <input type="datetime-local" name="start_date" id="edit_start_date" required>
                </div>

                <div>
                    <label for="edit_end_date">End Date and Time</label>
                    <input type="datetime-local" name="end_date" id="edit_end_date" required>
                </div>

                <div>
                    <label for="edit_status">Status</label>
                    <select name="status" id="edit_status" required>
                        <option value="draft">Draft</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>

            <br>
            <button type="submit" class="btn-modern btn-primary-modern">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('editElectionModal');
        const closeButton = document.querySelector('[data-close-edit-election]');

        const idInput = document.getElementById('edit_election_id');
        const titleInput = document.getElementById('edit_title');
        const descriptionInput = document.getElementById('edit_description');
        const startInput = document.getElementById('edit_start_date');
        const endInput = document.getElementById('edit_end_date');
        const statusInput = document.getElementById('edit_status');

        function openModal(button) {
            if (!modal) return;

            idInput.value = button.dataset.id || '';
            titleInput.value = button.dataset.title || '';
            descriptionInput.value = button.dataset.description || '';
            startInput.value = button.dataset.start || '';
            endInput.value = button.dataset.end || '';
            statusInput.value = button.dataset.status || 'draft';

            modal.hidden = false;
            document.body.classList.add('modal-open');
            titleInput.focus();
        }

        function closeModal() {
            if (!modal) return;

            modal.hidden = true;
            document.body.classList.remove('modal-open');
        }

        document.querySelectorAll('[data-edit-election]').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button);
            });
        });

        if (closeButton) {
            closeButton.addEventListener('click', closeModal);
        }

        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal && !modal.hidden) {
                closeModal();
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
