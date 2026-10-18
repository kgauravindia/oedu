<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'BCI Law Colleges (3.1K+) - Online Education Admin';
$msg = '';
$msgType = 'success';

// Handle Actions (Save / Status Toggle / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM `law_college` WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $msg = "Law College record ID #{$id} was permanently deleted.";
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $currentStatus = $_POST['current_status'] ?? 'ACTIVE';
        $newStatus = ($currentStatus === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
        $stmt = $pdo->prepare("UPDATE `law_college` SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $id]);
        $msg = "Law College ID #{$id} status updated to {$newStatus}.";
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $affiliatingUniv = trim($_POST['affiliating_university'] ?? '');
        $courseName = trim($_POST['course_name'] ?? '');
        $approvalTill = trim($_POST['approval_till'] ?? '');
        $eYear = trim($_POST['e_year'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');
        $status = trim($_POST['status'] ?? 'ACTIVE');

        if ($name === '') {
            $msg = "College name is required.";
            $msgType = 'danger';
        } else {
            // Generate slug if empty or new
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name . '-' . $state), '-'));

            if ($id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE `law_college` 
                    SET name = :name, state = :state, affiliating_university = :univ,
                        course_name = :course, approval_till = :approval, e_year = :eyear,
                        remarks = :remarks, status = :status
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':state' => $state,
                    ':univ' => $affiliatingUniv,
                    ':course' => $courseName,
                    ':approval' => $approvalTill,
                    ':eyear' => $eYear,
                    ':remarks' => $remarks,
                    ':status' => $status,
                    ':id' => $id
                ]);
                $msg = "Law College record #{$id} updated successfully.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO `law_college` (name, slug, state, affiliating_university, course_name, approval_till, e_year, remarks, status)
                    VALUES (:name, :slug, :state, :univ, :course, :approval, :eyear, :remarks, :status)
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                    ':state' => $state,
                    ':univ' => $affiliatingUniv,
                    ':course' => $courseName,
                    ':approval' => $approvalTill,
                    ':eyear' => $eYear,
                    ':remarks' => $remarks,
                    ':status' => $status
                ]);
                $msg = "New Law College program inserted successfully.";
            }
        }
    }
}

// Filters & Pagination
$q = trim($_GET['q'] ?? '');
$filterState = trim($_GET['state'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if ($q !== '') {
    $where[] = "(name LIKE :q OR affiliating_university LIKE :q OR course_name LIKE :q OR remarks LIKE :q OR slug LIKE :q)";
    $params[':q'] = "%{$q}%";
}
if ($filterState !== '') {
    $where[] = "state = :state";
    $params[':state'] = $filterState;
}
if ($filterStatus !== '') {
    $where[] = "status = :status";
    $params[':status'] = $filterStatus;
}

$whereSql = implode(' AND ', $where);

// Count Total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM `law_college` WHERE {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Fetch Records
$sql = "SELECT * FROM `law_college` WHERE {$whereSql} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

// Overall Stats
$totalCourses = (int)$pdo->query("SELECT COUNT(*) FROM `law_college`")->fetchColumn();
$totalColleges = (int)$pdo->query("SELECT COUNT(DISTINCT slug) FROM `law_college`")->fetchColumn();
$totalStates = (int)$pdo->query("SELECT COUNT(DISTINCT state) FROM `law_college` WHERE state IS NOT NULL AND state != ''")->fetchColumn();

// States list for filter
$statesList = $pdo->query("SELECT DISTINCT state FROM `law_college` WHERE state IS NOT NULL AND state != '' ORDER BY state ASC")->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/admin-menu.php'; ?>

    <div style="display: flex; flex-direction: column; min-width: 0; width: 100%;">
        <?php include __DIR__ . '/admin-header.php'; ?>

        <main class="admin-main">
            <!-- Top bar / Title -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #0F172A;">
                        <i class="fas fa-balance-scale text-primary me-2"></i> BCI Law Colleges &amp; Programs
                    </h4>
                    <p class="text-muted small mb-0">Manage Bar Council of India approved legal education colleges, courses, and intake.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="../law-college.php" target="_blank" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>View Public Directory
                    </a>
                    <button type="button" class="btn btn-primary-custom btn-sm" onclick="openLawModal()">
                        <i class="fas fa-plus me-1"></i>Add Law College
                    </button>
                </div>
            </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card bg-dark border-secondary h-100 p-3">
                    <div class="text-muted small fw-bold text-uppercase">Total Programs</div>
                    <div class="h3 fw-bold text-white my-1"><?= number_format($totalCourses) ?></div>
                    <div class="text-success small"><i class="fas fa-check-circle me-1"></i>BCI Approved Course Entries</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-dark border-secondary h-100 p-3">
                    <div class="text-muted small fw-bold text-uppercase">Distinct Colleges</div>
                    <div class="h3 fw-bold text-info my-1"><?= number_format($totalColleges) ?></div>
                    <div class="text-muted small">Unique Law Colleges</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-dark border-secondary h-100 p-3">
                    <div class="text-muted small fw-bold text-uppercase">States &amp; UTs</div>
                    <div class="h3 fw-bold text-warning my-1"><?= $totalStates ?></div>
                    <div class="text-muted small">Pan-India Geographic Coverage</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-dark border-secondary h-100 p-3">
                    <div class="text-muted small fw-bold text-uppercase">Filtered Count</div>
                    <div class="h3 fw-bold text-light my-1"><?= number_format($totalRecords) ?></div>
                    <div class="text-muted small">Matches Current Filters</div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card bg-dark border-secondary mb-4">
            <div class="card-body">
                <form action="law-colleges.php" method="GET" class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted">Search Query</label>
                        <input type="text" name="q" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="College name, university, course..." value="<?= htmlspecialchars($q) ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted">State</label>
                        <select name="state" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="">All States (<?= count($statesList) ?>)</option>
                            <?php foreach ($statesList as $st): ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= $filterState === $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="">All Status</option>
                            <option value="ACTIVE" <?= $filterStatus === 'ACTIVE' ? 'selected' : '' ?>>ACTIVE</option>
                            <option value="INACTIVE" <?= $filterStatus === 'INACTIVE' ? 'selected' : '' ?>>INACTIVE</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-filter me-1"></i>Apply Filters
                        </button>
                        <?php if ($q || $filterState || $filterStatus): ?>
                            <a href="law-colleges.php" class="btn btn-outline-secondary btn-sm" title="Reset">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Records Table -->
        <div class="card bg-dark border-secondary">
            <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
                <span class="text-white fw-bold">Records (<?= number_format($totalRecords) ?>)</span>
                <span class="text-muted small">Page <?= $page ?> of <?= $totalPages ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped align-middle mb-0" style="font-size: 0.85rem;">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size: 0.75rem;">
                            <th>ID</th>
                            <th>College Name &amp; University</th>
                            <th>State</th>
                            <th>Course Name &amp; Strength</th>
                            <th>BCI Approval</th>
                            <th>Est. Year</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No law college records found matching your query.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="text-muted">#<?= $r['id'] ?></td>
                                    <td>
                                        <div class="fw-bold text-white"><?= htmlspecialchars($r['name']) ?></div>
                                        <?php if (!empty($r['affiliating_university'])): ?>
                                            <div class="text-muted small"><?= htmlspecialchars(substr($r['affiliating_university'], 0, 50)) ?><?= strlen($r['affiliating_university']) > 50 ? '...' : '' ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($r['slug'])): ?>
                                            <a href="../law-college/<?= urlencode($r['slug']) ?>" target="_blank" class="small text-info text-decoration-none">
                                                <i class="fas fa-link me-1"></i>/law-college/<?= htmlspecialchars($r['slug']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($r['state']) ?></span></td>
                                    <td>
                                        <span class="text-light fw-bold"><?= htmlspecialchars($r['course_name']) ?></span>
                                        <?php if (!empty($r['remarks'])): ?>
                                            <div class="small text-warning"><?= htmlspecialchars(substr($r['remarks'], 0, 45)) ?>...</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($r['approval_till'] ?: 'Pending') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($r['e_year'] ?: '—') ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $r['status'] ?>">
                                            <button type="submit" class="btn btn-sm badge <?= $r['status'] === 'ACTIVE' ? 'bg-success' : 'bg-danger' ?> border-0">
                                                <?= htmlspecialchars($r['status']) ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick='editLawRecord(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" onsubmit="return confirm('Delete this law college entry permanently?');" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">Showing <?= $offset + 1 ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= number_format($totalRecords) ?> entries</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link bg-dark text-white border-secondary" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">«</a></li>
                                <li class="page-item"><a class="page-link bg-dark text-white border-secondary" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">‹</a></li>
                            <?php endif; ?>
                            <li class="page-item active"><span class="page-link bg-primary border-primary"><?= $page ?></span></li>
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item"><a class="page-link bg-dark text-white border-secondary" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">›</a></li>
                                <li class="page-item"><a class="page-link bg-dark text-white border-secondary" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">»</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="lawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content bg-dark text-white border-secondary">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="modal_id" value="0">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="modalTitle">Add Law College Program</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <label class="form-label small text-muted">College Name *</label>
                        <input type="text" name="name" id="modal_name" required class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted">State *</label>
                        <input type="text" name="state" id="modal_state" required class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-muted">Affiliating University</label>
                        <input type="text" name="affiliating_university" id="modal_univ" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-muted">Course Name &amp; Strength *</label>
                        <input type="text" name="course_name" id="modal_course" required class="form-control bg-dark text-white border-secondary" placeholder="e.g. 3 year LLB Course (120)">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted">Approval Validity</label>
                        <input type="text" name="approval_till" id="modal_approval" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Upto 2026-27">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted">Est. Year</label>
                        <input type="text" name="e_year" id="modal_eyear" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 1995">
                    </div>
                    <div class="col-12 col-md-9">
                        <label class="form-label small text-muted">Special Remarks / Notifications</label>
                        <input type="text" name="remarks" id="modal_remarks" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" id="modal_status" class="form-select bg-dark text-white border-secondary">
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openLawModal() {
    document.getElementById('modalTitle').innerText = 'Add Law College Program';
    document.getElementById('modal_id').value = '0';
    document.getElementById('modal_name').value = '';
    document.getElementById('modal_state').value = '';
    document.getElementById('modal_univ').value = '';
    document.getElementById('modal_course').value = '';
    document.getElementById('modal_approval').value = '';
    document.getElementById('modal_eyear').value = '';
    document.getElementById('modal_remarks').value = '';
    document.getElementById('modal_status').value = 'ACTIVE';
    new bootstrap.Modal(document.getElementById('lawModal')).show();
}

function editLawRecord(r) {
    document.getElementById('modalTitle').innerText = 'Edit Law College Program #' + r.id;
    document.getElementById('modal_id').value = r.id;
    document.getElementById('modal_name').value = r.name || '';
    document.getElementById('modal_state').value = r.state || '';
    document.getElementById('modal_univ').value = r.affiliating_university || '';
    document.getElementById('modal_course').value = r.course_name || '';
    document.getElementById('modal_approval').value = r.approval_till || '';
    document.getElementById('modal_eyear').value = r.e_year || '';
    document.getElementById('modal_remarks').value = r.remarks || '';
    document.getElementById('modal_status').value = r.status || 'ACTIVE';
    new bootstrap.Modal(document.getElementById('lawModal')).show();
}
</script>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
