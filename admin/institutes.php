<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Standalone Institutes (17K+) - Online Education Admin';
$msg = '';
$msgType = 'success';

// Handle Actions (Save / Status Toggle / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM institute WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $msg = "Institute ID #{$id} was permanently deleted.";
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $currentStatus = $_POST['current_status'] ?? 'ACTIVE';
        $newStatus = ($currentStatus === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
        $stmt = $pdo->prepare("UPDATE institute SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $id]);
        $msg = "Institute ID #{$id} status updated to {$newStatus}.";
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['institute_name'] ?? '');
        $aishe = trim($_POST['aishe_code'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $type = trim($_POST['institute_type'] ?? '');
        $management = trim($_POST['management'] ?? '');
        $surveyYear = trim($_POST['survey_year'] ?? '');
        $status = trim($_POST['status'] ?? 'ACTIVE');

        if ($name === '' || $aishe === '') {
            $msg = "Institute name and AISHE code are required.";
            $msgType = 'danger';
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name . '-' . $aishe), '-'));

            if ($id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE institute 
                    SET institute_name = :name, aishe_code = :aishe, state = :state, district = :district,
                        institute_type = :type, management = :mgmt, survey_year = :year, status = :status, slug = :slug
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':aishe' => $aishe,
                    ':state' => $state,
                    ':district' => $district,
                    ':type' => $type,
                    ':mgmt' => $management,
                    ':year' => $surveyYear,
                    ':status' => $status,
                    ':slug' => $slug,
                    ':id' => $id
                ]);
                $msg = "Institute \"{$name}\" updated successfully.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO institute (institute_name, aishe_code, state, district, institute_type, management, survey_year, status, slug)
                    VALUES (:name, :aishe, :state, :district, :type, :mgmt, :year, :status, :slug)
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':aishe' => $aishe,
                    ':state' => $state,
                    ':district' => $district,
                    ':type' => $type,
                    ':mgmt' => $management,
                    ':year' => $surveyYear,
                    ':status' => $status,
                    ':slug' => $slug
                ]);
                $msg = "Institute \"{$name}\" added successfully.";
            }
        }
    }
}

// Search & Filters
$search = trim($_GET['q'] ?? '');
$filterState = trim($_GET['state'] ?? '');
$filterType = trim($_GET['type'] ?? '');
$filterMgmt = trim($_GET['management'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(institute_name LIKE :q OR aishe_code LIKE :q OR district LIKE :q)";
    $params[':q'] = "%{$search}%";
}
if ($filterState !== '') {
    $where[] = "state = :state";
    $params[':state'] = $filterState;
}
if ($filterType !== '') {
    $where[] = "institute_type = :type";
    $params[':type'] = $filterType;
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
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM institute WHERE {$whereClause}");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

// Fetch Institutes
$dataStmt = $pdo->prepare("
    SELECT * FROM institute 
    WHERE {$whereClause} 
    ORDER BY id ASC 
    LIMIT {$perPage} OFFSET {$offset}
");
$dataStmt->execute($params);
$institutes = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

// Distinct states, types, managements
$states = $pdo->query("SELECT DISTINCT state FROM institute WHERE state IS NOT NULL AND state != '' ORDER BY state ASC LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
$types = $pdo->query("SELECT DISTINCT institute_type FROM institute WHERE institute_type IS NOT NULL AND institute_type != '' ORDER BY institute_type ASC LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
$managements = $pdo->query("SELECT DISTINCT management FROM institute WHERE management IS NOT NULL AND management != '' ORDER BY management ASC LIMIT 30")->fetchAll(PDO::FETCH_COLUMN);
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
                        <i class="fas fa-building-columns text-info me-2"></i> Standalone Institutes Directory
                    </h4>
                    <p class="text-muted small mb-0">Total <?= number_format($totalRows) ?> records found matching current criteria</p>
                </div>
                <div>
                    <button class="btn btn-primary-custom btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#editModal" onclick="openAddModal()">
                        <i class="fas fa-plus me-1"></i> Add Institute
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
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search institute name, AISHE..." value="<?= htmlspecialchars($search) ?>">
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
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Institute Types</option>
                            <?php foreach ($types as $tp): ?>
                                <option value="<?= htmlspecialchars($tp) ?>" <?= $filterType === $tp ? 'selected' : '' ?>><?= htmlspecialchars($tp) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="management" class="form-select form-select-sm">
                            <option value="">All Managements</option>
                            <?php foreach ($managements as $mg): ?>
                                <option value="<?= htmlspecialchars($mg) ?>" <?= $filterMgmt === $mg ? 'selected' : '' ?>><?= htmlspecialchars($mg) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-primary-custom btn-sm w-100">Filter</button>
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
                                <th>Institute Name &amp; Code</th>
                                <th>Institute Type</th>
                                <th>State &amp; District</th>
                                <th>Management</th>
                                <th>Status</th>
                                <th style="width: 130px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($institutes)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-2x mb-2 d-block opacity-50"></i>
                                        No standalone institutes found matching your criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($institutes as $inst): ?>
                                    <tr>
                                        <td class="text-muted fw-bold">#<?= (int)$inst['id'] ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($inst['institute_name']) ?></span>
                                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;">AISHE: <?= htmlspecialchars($inst['aishe_code']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.72rem;">
                                                <?= htmlspecialchars($inst['institute_type'] ?: 'Standalone') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($inst['district'] ?: '-') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($inst['state']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">
                                                <?= htmlspecialchars($inst['management'] ?: 'Unspecified') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?= $inst['id'] ?>">
                                                <input type="hidden" name="current_status" value="<?= htmlspecialchars($inst['status']) ?>">
                                                <button type="submit" class="btn btn-sm border-0 p-0" title="Click to toggle status">
                                                    <?php if ($inst['status'] === 'ACTIVE'): ?>
                                                        <span class="badge bg-success rounded-pill px-2">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger rounded-pill px-2">Inactive</span>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="btn-group btn-group-sm">
                                                <a href="../institute/<?= urlencode($inst['slug'] ?? '') ?>" target="_blank" class="btn btn-light border py-1 px-2" title="View Public Page">
                                                    <i class="fas fa-external-link-alt text-primary"></i>
                                                </a>
                                                <button class="btn btn-light border py-1 px-2" title="Edit" onclick='openEditModal(<?= json_encode($inst) ?>)'>
                                                    <i class="fas fa-pencil-alt text-secondary"></i>
                                                </button>
                                                <form method="POST" onsubmit="return confirm('Delete institute <?= addslashes($inst['institute_name']) ?>?');" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $inst['id'] ?>">
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
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&type=<?= urlencode($filterType) ?>&management=<?= urlencode($filterMgmt) ?>&page=<?= $page - 1 ?>">&laquo;</a>
                                </li>
                                <?php
                                $startP = max(1, $page - 2);
                                $endP = min($totalPages, $page + 2);
                                for ($i = $startP; $i <= $endP; $i++):
                                ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&type=<?= urlencode($filterType) ?>&management=<?= urlencode($filterMgmt) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&type=<?= urlencode($filterType) ?>&management=<?= urlencode($filterMgmt) ?>&page=<?= $page + 1 ?>">&raquo;</a>
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
                <h5 class="modal-title fw-bold" id="modalTitle">Add Standalone Institute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-bold">Institute Name *</label>
                    <input type="text" name="institute_name" id="formName" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">AISHE Code *</label>
                    <input type="text" name="aishe_code" id="formAishe" class="form-control" required placeholder="e.g. S-12345">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Institute Type</label>
                    <input type="text" name="institute_type" id="formType" class="form-control" placeholder="e.g. Technical / Polytechnic / Nursing">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Management</label>
                    <input type="text" name="management" id="formMgmt" class="form-control" placeholder="e.g. Government / Private">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">State *</label>
                    <input type="text" name="state" id="formState" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">District</label>
                    <input type="text" name="district" id="formDistrict" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">AISHE Survey Year</label>
                    <input type="text" name="survey_year" id="formYear" class="form-control" placeholder="e.g. 2021-2022">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" id="formStatus" class="form-select">
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="INACTIVE">INACTIVE</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary-custom btn-sm">Save Institute</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Standalone Institute';
    document.getElementById('formId').value = '0';
    document.getElementById('formName').value = '';
    document.getElementById('formAishe').value = '';
    document.getElementById('formType').value = '';
    document.getElementById('formMgmt').value = '';
    document.getElementById('formState').value = '';
    document.getElementById('formDistrict').value = '';
    document.getElementById('formYear').value = '';
    document.getElementById('formStatus').value = 'ACTIVE';
}

function openEditModal(i) {
    document.getElementById('modalTitle').textContent = 'Edit Institute: ' + i.institute_name;
    document.getElementById('formId').value = i.id;
    document.getElementById('formName').value = i.institute_name || '';
    document.getElementById('formAishe').value = i.aishe_code || '';
    document.getElementById('formType').value = i.institute_type || '';
    document.getElementById('formMgmt').value = i.management || '';
    document.getElementById('formState').value = i.state || '';
    document.getElementById('formDistrict').value = i.district || '';
    document.getElementById('formYear').value = i.survey_year || '';
    document.getElementById('formStatus').value = i.status || 'ACTIVE';
    
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>

<?php include __DIR__ . '/admin-footer.php'; ?>
