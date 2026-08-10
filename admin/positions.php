<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

function faculty_options(): array
{
    return [
        'FST' => 'Faculty of Science and Technology',
        'SOB' => 'School of Business',
        'FOL' => 'Faculty of Law',
        'FSS' => 'Faculty of Social Science',
        'IDS' => 'Institute of Development Studies',
        'SOPAM' => 'School of Public Administration and Management',
    ];
}

function clean_years_array(array $years): ?string
{
    $clean = [];

    foreach ($years as $year) {
        $year = (int)$year;

        if ($year >= 1 && $year <= 5) {
            $clean[] = (string)$year;
        }
    }

    $clean = array_values(array_unique($clean));

    if (!$clean) {
        return null;
    }

    sort($clean, SORT_NUMERIC);

    return implode(',', $clean);
}

function clean_faculty_code(string $facultyCode, array $faculties): ?string
{
    $facultyCode = strtoupper(trim($facultyCode));

    if ($facultyCode === '' || !isset($faculties[$facultyCode])) {
        return null;
    }

    return $facultyCode;
}

function eligibility_year_label(?string $years): string
{
    $years = trim((string)$years);

    if ($years === '') {
        return 'All years';
    }

    $items = array_filter(array_map('trim', explode(',', $years)));

    if (!$items) {
        return 'All years';
    }

    return 'Year ' . implode(', Year ', $items);
}

function eligibility_faculty_label(?string $facultyCode, array $faculties): string
{
    $facultyCode = trim((string)$facultyCode);

    if ($facultyCode === '') {
        return 'All faculties';
    }

    return ($faculties[$facultyCode] ?? $facultyCode) . ' (' . $facultyCode . ')';
}

$faculties = faculty_options();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $electionId = (int)($_POST['election_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $voterFacultyCode = clean_faculty_code((string)($_POST['voter_faculty_code'] ?? ''), $faculties);
        $candidateFacultyCode = clean_faculty_code((string)($_POST['candidate_faculty_code'] ?? ''), $faculties);
        $voterYears = clean_years_array($_POST['voter_years'] ?? []);
        $candidateYears = clean_years_array($_POST['candidate_years'] ?? []);
        $maxWinners = max(1, (int)($_POST['max_winners'] ?? 1));

        if ($electionId <= 0 || $title === '') {
            set_flash('error', 'Election and position title are required.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO positions
                    (election_id, title, description, voter_faculty_code, candidate_faculty_code, voter_years, candidate_years, max_winners, is_active)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, 1)'
            );

            $stmt->execute([
                $electionId,
                $title,
                $description,
                $voterFacultyCode,
                $candidateFacultyCode,
                $voterYears,
                $candidateYears,
                $maxWinners,
            ]);

            log_activity($pdo, (int)$_SESSION['user_id'], 'create_position', 'positions', (int)$pdo->lastInsertId());
            set_flash('success', 'Position added successfully with eligibility rules.');
        }

        redirect('admin/positions.php');
    }

    if ($action === 'edit') {
        $positionId = (int)($_POST['position_id'] ?? 0);
        $electionId = (int)($_POST['election_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $voterFacultyCode = clean_faculty_code((string)($_POST['voter_faculty_code'] ?? ''), $faculties);
        $candidateFacultyCode = clean_faculty_code((string)($_POST['candidate_faculty_code'] ?? ''), $faculties);
        $voterYears = clean_years_array($_POST['voter_years'] ?? []);
        $candidateYears = clean_years_array($_POST['candidate_years'] ?? []);
        $maxWinners = max(1, (int)($_POST['max_winners'] ?? 1));

        if ($positionId <= 0 || $electionId <= 0 || $title === '') {
            set_flash('error', 'Position, election, and title are required.');
        } else {
            $stmt = $pdo->prepare(
                'UPDATE positions
                 SET election_id = ?,
                     title = ?,
                     description = ?,
                     voter_faculty_code = ?,
                     candidate_faculty_code = ?,
                     voter_years = ?,
                     candidate_years = ?,
                     max_winners = ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $electionId,
                $title,
                $description,
                $voterFacultyCode,
                $candidateFacultyCode,
                $voterYears,
                $candidateYears,
                $maxWinners,
                $positionId,
            ]);

            log_activity($pdo, (int)$_SESSION['user_id'], 'edit_position', 'positions', $positionId);
            set_flash('success', 'Position updated successfully.');
        }

        redirect('admin/positions.php');
    }

    if ($action === 'toggle_status') {
        $positionId = (int)($_POST['position_id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;

        if ($positionId > 0) {
            $stmt = $pdo->prepare('UPDATE positions SET is_active = ? WHERE id = ?');
            $stmt->execute([$isActive, $positionId]);

            log_activity($pdo, (int)$_SESSION['user_id'], $isActive ? 'activate_position' : 'deactivate_position', 'positions', $positionId);
            set_flash('success', 'Position status updated.');
        }

        redirect('admin/positions.php');
    }

    if ($action === 'delete') {
        $positionId = (int)($_POST['position_id'] ?? 0);

        if ($positionId > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM positions WHERE id = ?');
                $stmt->execute([$positionId]);

                log_activity($pdo, (int)$_SESSION['user_id'], 'delete_position', 'positions', $positionId);
                set_flash('success', 'Position deleted successfully.');
            } catch (PDOException $e) {
                set_flash('error', 'Could not delete this position because related records may exist.');
            }
        }

        redirect('admin/positions.php');
    }
}

$elections = $pdo->query('SELECT id, title FROM elections ORDER BY created_at DESC')->fetchAll();

$positions = $pdo->query(
    'SELECT p.*, e.title AS election_title
     FROM positions p
     JOIN elections e ON p.election_id = e.id
     ORDER BY e.created_at DESC, p.title ASC'
)->fetchAll();

$page_title = 'Manage Positions';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .positions-page {
        --primary: #2563eb;
        --ink: #0f172a;
        --muted: #64748b;
        --line: #e2e8f0;
        --danger: #dc2626;
        --success: #16a34a;
        color: var(--ink);
    }

    .positions-page label {
        display: block;
        margin: .85rem 0 .35rem;
        font-weight: 800;
        color: #334155;
        font-size: .88rem;
    }

    .positions-page input,
    .positions-page select,
    .positions-page textarea {
        width: 100%;
        min-height: 43px;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: .7rem .85rem;
        outline: none;
        background: #fff;
        color: var(--ink);
    }

    .positions-page textarea {
        min-height: 90px;
        resize: vertical;
    }

    .positions-page input:focus,
    .positions-page select:focus,
    .positions-page textarea:focus {
        border-color: rgba(37, 99, 235, .55);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .position-rule-box {
        margin-top: 1.1rem;
        padding: 1rem;
        border-radius: 18px;
        border: 1px dashed #93c5fd;
        background: linear-gradient(180deg, #eff6ff, #fff);
    }

    .position-rule-box h3 {
        margin: 0 0 .3rem;
        font-size: 1rem;
    }

    .position-rule-box p {
        margin: 0 0 .8rem;
        color: var(--muted);
        font-size: .88rem;
        line-height: 1.45;
    }

    .form-grid-pro {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .form-grid-pro .full {
        grid-column: 1 / -1;
    }

    .years-checks {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        padding: .7rem;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: #fff;
        min-height: 43px;
    }

    .year-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        padding: .4rem .65rem;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .82rem;
        font-weight: 800;
        cursor: pointer;
    }

    .year-pill input {
        width: auto;
        min-height: auto;
        padding: 0;
        box-shadow: none !important;
    }

    .hint {
        color: var(--muted);
        font-size: .82rem;
        line-height: 1.4;
        margin-top: .35rem;
    }

    .btn-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 40px;
        border: 0;
        border-radius: 999px;
        padding: .7rem 1rem;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, var(--primary), #38bdf8);
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

    .btn-success-modern {
        background: #dcfce7;
        color: #166534;
    }

    .btn-edit-modern {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-action-small {
        min-height: 28px !important;
        padding: .32rem .55rem !important;
        border-radius: 9px !important;
        font-size: .72rem !important;
        font-weight: 800 !important;
        gap: .25rem !important;
    }

    .position-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .32rem .6rem;
        font-size: .75rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .position-badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .position-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .eligibility-list {
        display: grid;
        gap: .25rem;
        color: #334155;
        font-size: .86rem;
        line-height: 1.4;
    }

    .eligibility-list strong {
        color: var(--ink);
    }

    .actions-inline {
        display: flex;
        align-items: center;
        gap: .28rem;
        flex-wrap: wrap;
    }

    .actions-inline form {
        margin: 0;
    }

    .empty-state {
        text-align: center;
        color: var(--muted);
        padding: 1.2rem;
    }

    .modal-backdrop[hidden] {
        display: none !important;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 6rem 1rem 2rem;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(8px);
        overflow-y: auto;
    }

    .edit-position-modal {
        width: min(850px, 100%);
        max-height: none;
        overflow: visible;
        margin-bottom: 2rem;
        position: relative;
    }

    .modal-titlebar {
        position: sticky;
        top: 0;
        z-index: 2;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.35rem 1rem;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 16px 16px 0 0;
    }

    .modal-titlebar h2 {
        margin: 0;
        font-size: 1.15rem;
    }

    .modal-titlebar p {
        margin: .35rem 0 0;
        color: var(--muted);
    }

    .modal-close {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 999px;
        background: #2563eb;
        color: #fff;
        cursor: pointer;
        font-weight: 900;
        flex: 0 0 auto;
    }

    .edit-position-form {
        padding: 1.35rem;
    }

    body.modal-open {
        overflow: hidden;
    }

    @media (max-width: 760px) {
        .form-grid-pro {
            grid-template-columns: 1fr;
        }

        .form-grid-pro .full {
            grid-column: auto;
        }

        .actions-inline {
            flex-direction: column;
            align-items: stretch;
        }

        .actions-inline .btn-modern {
            width: 100%;
        }

        .modal-backdrop {
            padding: 5rem .75rem 1.5rem;
        }
    }
</style>

<div class="positions-page">
    <div class="card">
        <h2>Add Position</h2>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="create">

            <div class="form-grid-pro">
                <div class="full">
                    <label>Election</label>
                    <select name="election_id" required>
                        <option value="">-- Select election --</option>
                        <?php foreach ($elections as $election): ?>
                            <option value="<?= (int)$election['id'] ?>"><?= e($election['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Position Title</label>
                    <input type="text" name="title" placeholder="e.g. President, Secretary, FST Representative" required>
                </div>

                <div>
                    <label>Number of Winners</label>
                    <input type="number" name="max_winners" value="1" min="1" required>
                </div>

                <div class="full">
                    <label>Description</label>
                    <textarea name="description" placeholder="Optional position description"></textarea>
                </div>
            </div>

            <div class="position-rule-box">
                <h3>Voting Eligibility</h3>
                <p>Choose who is allowed to vote for this position. Leave faculty and years empty to allow all students.</p>

                <div class="form-grid-pro">
                    <div>
                        <label>Allowed Voter Faculty</label>
                        <select name="voter_faculty_code">
                            <option value="">All faculties</option>
                            <?php foreach ($faculties as $code => $name): ?>
                                <option value="<?= e($code) ?>"><?= e($code . ' — ' . $name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Allowed Voter Years</label>
                        <div class="years-checks">
                            <?php for ($year = 1; $year <= 5; $year++): ?>
                                <label class="year-pill">
                                    <input type="checkbox" name="voter_years[]" value="<?= $year ?>">
                                    Year <?= $year ?>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <div class="hint">Leave unchecked to allow all years to vote.</div>
                    </div>
                </div>
            </div>

            <div class="position-rule-box">
                <h3>Candidate Eligibility</h3>
                <p>Choose who is allowed to become a candidate for this position. Leave faculty and years empty to allow all students.</p>

                <div class="form-grid-pro">
                    <div>
                        <label>Allowed Candidate Faculty</label>
                        <select name="candidate_faculty_code">
                            <option value="">All faculties</option>
                            <?php foreach ($faculties as $code => $name): ?>
                                <option value="<?= e($code) ?>"><?= e($code . ' — ' . $name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Allowed Candidate Years</label>
                        <div class="years-checks">
                            <?php for ($year = 1; $year <= 5; $year++): ?>
                                <label class="year-pill">
                                    <input type="checkbox" name="candidate_years[]" value="<?= $year ?>">
                                    Year <?= $year ?>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <div class="hint">Example: select Year 2, Year 3, Year 4, Year 5 for senior-only posts.</div>
                    </div>
                </div>
            </div>

            <br>

            <button type="submit" class="btn-modern btn-primary-modern">
                <i class="fas fa-plus"></i> Add Position
            </button>
        </form>
    </div>

    <div class="card">
        <h2>Positions</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Election</th>
                        <th>Position</th>
                        <th>Description</th>
                        <th>Eligibility</th>
                        <th>Winners</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($positions as $position): ?>
                    <tr>
                        <td><?= e($position['election_title']) ?></td>

                        <td>
                            <strong><?= e($position['title']) ?></strong>
                        </td>

                        <td><?= e($position['description']) ?></td>

                        <td>
                            <div class="eligibility-list">
                                <div>
                                    <strong>Voters:</strong>
                                    <?= e(eligibility_faculty_label($position['voter_faculty_code'] ?? null, $faculties)) ?> ·
                                    <?= e(eligibility_year_label($position['voter_years'] ?? null)) ?>
                                </div>

                                <div>
                                    <strong>Candidates:</strong>
                                    <?= e(eligibility_faculty_label($position['candidate_faculty_code'] ?? null, $faculties)) ?> ·
                                    <?= e(eligibility_year_label($position['candidate_years'] ?? null)) ?>
                                </div>
                            </div>
                        </td>

                        <td><?= (int)($position['max_winners'] ?? 1) ?></td>

                        <td>
                            <span class="position-badge <?= (int)($position['is_active'] ?? 1) === 1 ? 'active' : 'inactive' ?>">
                                <?= (int)($position['is_active'] ?? 1) === 1 ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>

                        <td>
                            <div class="actions-inline">
                                <button
                                    type="button"
                                    class="btn-modern btn-action-small btn-edit-modern"
                                    data-edit-position
                                    data-id="<?= (int)$position['id'] ?>"
                                    data-election-id="<?= (int)$position['election_id'] ?>"
                                    data-title="<?= e($position['title']) ?>"
                                    data-description="<?= e($position['description']) ?>"
                                    data-voter-faculty-code="<?= e($position['voter_faculty_code'] ?? '') ?>"
                                    data-candidate-faculty-code="<?= e($position['candidate_faculty_code'] ?? '') ?>"
                                    data-voter-years="<?= e($position['voter_years'] ?? '') ?>"
                                    data-candidate-years="<?= e($position['candidate_years'] ?? '') ?>"
                                    data-max-winners="<?= (int)($position['max_winners'] ?? 1) ?>"
                                >
                                    <i class="fas fa-edit"></i> Edit
                                </button>

                                <form method="POST">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="position_id" value="<?= (int)$position['id'] ?>">
                                    <input type="hidden" name="is_active" value="<?= (int)($position['is_active'] ?? 1) === 1 ? 0 : 1 ?>">

                                    <button type="submit" class="btn-modern btn-action-small <?= (int)($position['is_active'] ?? 1) === 1 ? 'btn-muted-modern' : 'btn-success-modern' ?>">
                                        <?= (int)($position['is_active'] ?? 1) === 1 ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>

                                <form method="POST" onsubmit="return confirm('Delete this position?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="position_id" value="<?= (int)$position['id'] ?>">

                                    <button type="submit" class="btn-modern btn-action-small btn-danger-modern">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$positions): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No positions added yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="editPositionModal" class="modal-backdrop" hidden>
        <div class="edit-position-modal card" role="dialog" aria-modal="true" aria-labelledby="editPositionTitle">
            <div class="modal-titlebar">
                <div>
                    <h2 id="editPositionTitle">Edit Position</h2>
                    <p>Update the position details and eligibility rules.</p>
                </div>

                <button type="button" class="modal-close" data-close-edit-position aria-label="Close edit form">
                    &times;
                </button>
            </div>

            <form method="POST" class="edit-position-form" id="editPositionForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="position_id" id="edit_position_id">

                <div class="form-grid-pro">
                    <div class="full">
                        <label>Election</label>
                        <select name="election_id" id="edit_election_id" required>
                            <option value="">-- Select election --</option>
                            <?php foreach ($elections as $election): ?>
                                <option value="<?= (int)$election['id'] ?>"><?= e($election['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Position Title</label>
                        <input type="text" name="title" id="edit_title" required>
                    </div>

                    <div>
                        <label>Number of Winners</label>
                        <input type="number" name="max_winners" id="edit_max_winners" min="1" required>
                    </div>

                    <div class="full">
                        <label>Description</label>
                        <textarea name="description" id="edit_description"></textarea>
                    </div>
                </div>

                <div class="position-rule-box">
                    <h3>Voting Eligibility</h3>
                    <p>Choose who is allowed to vote for this position. Leave faculty and years empty to allow all students.</p>

                    <div class="form-grid-pro">
                        <div>
                            <label>Allowed Voter Faculty</label>
                            <select name="voter_faculty_code" id="edit_voter_faculty_code">
                                <option value="">All faculties</option>
                                <?php foreach ($faculties as $code => $name): ?>
                                    <option value="<?= e($code) ?>"><?= e($code . ' — ' . $name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>Allowed Voter Years</label>
                            <div class="years-checks">
                                <?php for ($year = 1; $year <= 5; $year++): ?>
                                    <label class="year-pill">
                                        <input type="checkbox" name="voter_years[]" value="<?= $year ?>" data-edit-voter-year>
                                        Year <?= $year ?>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <div class="hint">Leave unchecked to allow all years to vote.</div>
                        </div>
                    </div>
                </div>

                <div class="position-rule-box">
                    <h3>Candidate Eligibility</h3>
                    <p>Choose who is allowed to become a candidate for this position. Leave faculty and years empty to allow all students.</p>

                    <div class="form-grid-pro">
                        <div>
                            <label>Allowed Candidate Faculty</label>
                            <select name="candidate_faculty_code" id="edit_candidate_faculty_code">
                                <option value="">All faculties</option>
                                <?php foreach ($faculties as $code => $name): ?>
                                    <option value="<?= e($code) ?>"><?= e($code . ' — ' . $name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>Allowed Candidate Years</label>
                            <div class="years-checks">
                                <?php for ($year = 1; $year <= 5; $year++): ?>
                                    <label class="year-pill">
                                        <input type="checkbox" name="candidate_years[]" value="<?= $year ?>" data-edit-candidate-year>
                                        Year <?= $year ?>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <div class="hint">Example: select Year 2, Year 3, Year 4, Year 5 for senior-only posts.</div>
                        </div>
                    </div>
                </div>

                <br>

                <button type="submit" class="btn-modern btn-primary-modern">
                    <i class="fas fa-save"></i> Save Changes
                </button>

                <button type="button" class="btn-modern btn-muted-modern" data-close-edit-position>
                    Cancel
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const editPositionModal = document.getElementById('editPositionModal');

function openEditPositionModal(button) {
    document.getElementById('edit_position_id').value = button.dataset.id || '';
    document.getElementById('edit_election_id').value = button.dataset.electionId || '';
    document.getElementById('edit_title').value = button.dataset.title || '';
    document.getElementById('edit_description').value = button.dataset.description || '';
    document.getElementById('edit_voter_faculty_code').value = button.dataset.voterFacultyCode || '';
    document.getElementById('edit_candidate_faculty_code').value = button.dataset.candidateFacultyCode || '';
    document.getElementById('edit_max_winners').value = button.dataset.maxWinners || '1';

    const voterYears = String(button.dataset.voterYears || '')
        .split(',')
        .map(item => item.trim())
        .filter(Boolean);

    const candidateYears = String(button.dataset.candidateYears || '')
        .split(',')
        .map(item => item.trim())
        .filter(Boolean);

    document.querySelectorAll('[data-edit-voter-year]').forEach(checkbox => {
        checkbox.checked = voterYears.includes(checkbox.value);
    });

    document.querySelectorAll('[data-edit-candidate-year]').forEach(checkbox => {
        checkbox.checked = candidateYears.includes(checkbox.value);
    });

    editPositionModal.hidden = false;
    document.body.classList.add('modal-open');
}

function closeEditPositionModal() {
    editPositionModal.hidden = true;
    document.body.classList.remove('modal-open');
}

document.querySelectorAll('[data-edit-position]').forEach(button => {
    button.addEventListener('click', function () {
        openEditPositionModal(button);
    });
});

document.querySelectorAll('[data-close-edit-position]').forEach(button => {
    button.addEventListener('click', closeEditPositionModal);
});

editPositionModal.addEventListener('click', function (event) {
    if (event.target === editPositionModal) {
        closeEditPositionModal();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && editPositionModal && !editPositionModal.hidden) {
        closeEditPositionModal();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>