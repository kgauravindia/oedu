<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Affiliated Colleges (54K+) - Online Education Admin';
$msg = '';
$msgType = 'success';

// Handle Actions (Save / Status Toggle / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM college WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $msg = "College ID #{$id} was permanently deleted.";
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $currentStatus = $_POST['current_status'] ?? 'ACTIVE';
        $newStatus = ($currentStatus === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
        $stmt = $pdo->prepare("UPDATE college SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $id]);
        $msg = "College ID #{$id} status updated to {$newStatus}.";
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['college_name'] ?? '');
        $aishe = trim($_POST['aishe_code'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $univName = trim($_POST['university_name'] ?? '');
        $univAishe = trim($_POST['university_aishe_code'] ?? '');
        $management = trim($_POST['management'] ?? '');
        $specialised = trim($_POST['specialised'] ?? '');
        $status = trim($_POST['status'] ?? 'ACTIVE');

        if ($name === '' || $aishe === '') {
            $msg = "College name and AISHE code are required.";
            $msgType = 'danger';
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name . '-' . $aishe), '-'));

            if ($id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE college 
                    SET college_name = :name, aishe_code = :aishe, state = :state, district = :district,
                        university_name = :univ_name, university_aishe_code = :univ_aishe,
                        management = :mgmt, specialised = :spec, status = :status, slug = :slug
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':aishe' => $aishe,
                    ':state' => $state,
                    ':district' => $district,
                    ':univ_name' => $univName,
                    ':univ_aishe' => $univAishe,
                    ':mgmt' => $management,
                    ':spec' => $specialised,
                    ':status' => $status,
                    ':slug' => $slug,
                    ':id' => $id
                ]);
                $msg = "College \"{$name}\" updated successfully.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO college (college_name, aishe_code, state, district, university_name, university_aishe_code, management, specialised, status, slug)
                    VALUES (:name, :aishe, :state, :district, :univ_name, :univ_aishe, :mgmt, :spec, :status, :slug)
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':aishe' => $aishe,
                    ':state' => $state,
                    ':district' => $district,
                    ':univ_name' => $univName,
                    ':univ_aishe' => $univAishe,
                    ':mgmt' => $management,
                    ':spec' => $specialised,
                    ':status' => $status,
                    ':slug' => $slug
                ]);
                $msg = "College \"{$name}\" added successfully.";
            }
        }
    }
}

// Search & Filters
$search = trim($_GET['q'] ?? '');
$filterState = trim($_GET['state'] ?? '');
$filterMgmt = trim($_GET['management'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(college_name LIKE :q OR aishe_code LIKE :q OR district LIKE :q OR university_name LIKE :q)";
    $params[':q'] = "%{$search}%";
}
if ($filterState !== '') {
    $where[] = "state = :state";
    $params[':state'] = $filterState;
}
if ($filterMgmt !== '') {
    $where[] = "management = :mgmt";
    $params[':mgmt'] = $filterMgmt;
}
if ($filterStatus !== '') {
    $where[] = "status = :status";
    $params[':status'] = $filterStatus;
}

$whereClause = implode(" AND ", $where);

// Total Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM college WHERE {$whereClause}");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

// Fetch Colleges
$dataStmt = $pdo->prepare("
    SELECT * FROM college 
    WHERE {$whereClause} 
    ORDER BY id ASC 
    LIMIT {$perPage} OFFSET {$offset}
");
$dataStmt->execute($params);
$colleges = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

// Distinct states & managements
$states = $pdo->query("SELECT DISTINCT state FROM college WHERE state IS NOT NULL AND state != '' ORDER BY state ASC LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
$managements = $pdo->query("SELECT DISTINCT management FROM college WHERE management IS NOT NULL AND management != '' ORDER BY management ASC LIMIT 30")->fetchAll(PDO::FETCH_COLUMN);
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
            <!-- Header bar -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #0F172A;">
                        <i class="fas fa-graduation-cap text-warning me-2"></i> Affiliated Colleges Directory
                    </h4>
                    <p class="text-muted small mb-0">Total <?= number_format($totalRows) ?> records found matching current criteria</p>
                </div>
                <div>
                    <button class="btn btn-primary-custom btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#editModal" onclick="openAddModal()">
                        <i class="fas fa-plus me-1"></i> Add College
                    </button>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?> alert-dismissible fade show alert-auto-dismiss" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Filter Card -->
            <div class="data-table-card mb-4 p-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search college name, AISHE, district..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="state" class="form-select form-select-sm">
                            <option value="">All States</option>
                            <?php foreach ($states as $st): ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= $filterState === $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="management" class="form-select form-select-sm">
                            <option value="">All Managements</option>
                            <?php foreach ($managements as $mg): ?>
                                <option value="<?= htmlspecialchars($mg) ?>" <?= $filterMgmt === $mg ? 'selected' : '' ?>><?= htmlspecialchars($mg) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary-custom btn-sm w-100">Filter</button>
                        <?php if ($search || $filterState || $filterMgmt || $filterStatus): ?>
                            <a href="colleges.php" class="btn btn-light btn-sm border" title="Clear Filters"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="data-table-card">
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>College Name &amp; Code</th>
                                <th>Affiliated University</th>
                                <th>State &amp; District</th>
                                <th>Management</th>
                                <th>Status</th>
                                <th style="width: 130px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($colleges)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-2x mb-2 d-block opacity-50"></i>
                                        No colleges found matching your criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($colleges as $c): ?>
                                    <tr>
                                        <td class="text-muted fw-bold">#<?= (int)$c['id'] ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($c['college_name']) ?></span>
                                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;">AISHE: <?= htmlspecialchars($c['aishe_code']) ?></span>
                                            <?php if (!empty($c['specialised'])): ?>
                                                <small class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1"><?= htmlspecialchars($c['specialised']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($c['university_name']) ?>">
                                                <?= htmlspecialchars($c['university_name'] ?: '-') ?>
                                            </div>
                                            <small class="text-muted"><?= htmlspecialchars($c['university_aishe_code']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($c['district'] ?: '-') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($c['state']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">
                                                <?= htmlspecialchars($c['management'] ?: 'Unspecified') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="current_status" value="<?= htmlspecialchars($c['status']) ?>">
                                                <button type="submit" class="btn btn-sm border-0 p-0" title="Click to toggle status">
                                                    <?php if ($c['status'] === 'ACTIVE'): ?>
                                                        <span class="badge bg-success rounded-pill px-2">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger rounded-pill px-2">Inactive</span>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="btn-group btn-group-sm">
                                                <a href="../college/<?= urlencode($c['slug'] ?? '') ?>" target="_blank" class="btn btn-light border py-1 px-2" title="View Public Page">
                                                    <i class="fas fa-external-link-alt text-primary"></i>
                                                </a>
                                                <button class="btn btn-light border py-1 px-2" title="Edit" onclick='openEditModal(<?= json_encode($c) ?>)'>
                                                    <i class="fas fa-pencil-alt text-secondary"></i>
                                                </button>
                                                <form method="POST" onsubmit="return confirm('Delete college <?= addslashes($c['college_name']) ?>?');" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                    <button type="submit" class="btn btn-light border py-1 px-2 text-danger" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
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

                <!-- Pagination footer -->
                <?php if ($totalPages > 1): ?>
                    <div class="d-flex flex-wrap justify-content-between align-items-center p-3 border-top">
                        <small class="text-muted">
                            Showing <?= number_format($offset + 1) ?> to <?= number_format(min($totalRows, $offset + $perPage)) ?> of <?= number_format($totalRows) ?>
                        </small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&management=<?= urlencode($filterMgmt) ?>&page=<?= $page - 1 ?>">&laquo;</a>
                                </li>
                                <?php
                                $startP = max(1, $page - 2);
                                $endP = min($totalPages, $page + 2);
                                for ($i = $startP; $i <= $endP; $i++):
                                ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&management=<?= urlencode($filterMgmt) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&management=<?= urlencode($filterMgmt) ?>&page=<?= $page + 1 ?>">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="formId" value="0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Add College</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-bold">College Name *</label>
                    <input type="text" name="college_name" id="formName" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">AISHE Code *</label>
                    <input type="text" name="aishe_code" id="formAishe" class="form-control" required placeholder="e.g. C-12345">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Affiliated University Name</label>
                    <input type="text" name="university_name" id="formUnivName" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">University AISHE Code</label>
                    <input type="text" name="university_aishe_code" id="formUnivAishe" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">State *</label>
                    <input type="text" name="state" id="formState" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">District</label>
                    <input type="text" name="district" id="formDistrict" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Management</label>
                    <input type="text" name="management" id="formMgmt" class="form-control" placeholder="e.g. Government / Private">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Specialisation</label>
                    <input type="text" name="specialised" id="formSpec" class="form-control" placeholder="e.g. Engineering, Medical">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" id="formStatus" class="form-select">
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="INACTIVE">INACTIVE</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary-custom btn-sm">Save College</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add College';
    document.getElementById('formId').value = '0';
    document.getElementById('formName').value = '';
    document.getElementById('formAishe').value = '';
    document.getElementById('formUnivName').value = '';
    document.getElementById('formUnivAishe').value = '';
    document.getElementById('formState').value = '';
    document.getElementById('formDistrict').value = '';
    document.getElementById('formMgmt').value = '';
    document.getElementById('formSpec').value = '';
    document.getElementById('formStatus').value = 'ACTIVE';
}

function openEditModal(c) {
    document.getElementById('modalTitle').textContent = 'Edit College: ' + c.college_name;
    document.getElementById('formId').value = c.id;
    document.getElementById('formName').value = c.college_name || '';
    document.getElementById('formAishe').value = c.aishe_code || '';
    document.getElementById('formUnivName').value = c.university_name || '';
    document.getElementById('formUnivAishe').value = c.university_aishe_code || '';
    document.getElementById('formState').value = c.state || '';
    document.getElementById('formDistrict').value = c.district || '';
    document.getElementById('formMgmt').value = c.management || '';
    document.getElementById('formSpec').value = c.specialised || '';
    document.getElementById('formStatus').value = c.status || 'ACTIVE';
    
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>

<?php include __DIR__ . '/admin-footer.php'; ?>
