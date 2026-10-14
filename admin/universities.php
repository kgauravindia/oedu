<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Manage Universities - Online Education Admin';
$msg = '';
$msgType = 'success';

// Handle Add / Edit / Delete POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM university WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $msg = "University ID #{$id} was successfully removed.";
        }
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $aishe = trim($_POST['aishe_code'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $year = trim($_POST['year_of_establishment'] ?? '');
        $website = trim($_POST['website'] ?? '');

        if ($name === '' || $aishe === '') {
            $msg = "Name and AISHE code are required.";
            $msgType = 'danger';
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name . '-' . $aishe), '-'));

            if ($id > 0) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE university 
                    SET name = :name, aishe_code = :aishe, state = :state, district = :district, 
                        type = :type, year_of_establishment = :year, website = :website, slug = :slug
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':aishe' => $aishe,
                    ':state' => $state,
                    ':district' => $district,
                    ':type' => $type,
                    ':year' => $year,
                    ':website' => $website,
                    ':slug' => $slug,
                    ':id' => $id
                ]);
                $msg = "University \"{$name}\" updated successfully.";
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO university (name, aishe_code, state, district, type, year_of_establishment, website, slug)
                    VALUES (:name, :aishe, :state, :district, :type, :year, :website, :slug)
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':aishe' => $aishe,
                    ':state' => $state,
                    ':district' => $district,
                    ':type' => $type,
                    ':year' => $year,
                    ':website' => $website,
                    ':slug' => $slug
                ]);
                $msg = "New University \"{$name}\" created successfully.";
            }
        }
    }
}

// Filters & Pagination
$search = trim($_GET['q'] ?? '');
$filterState = trim($_GET['state'] ?? '');
$filterType = trim($_GET['type'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(name LIKE :q OR aishe_code LIKE :q OR district LIKE :q)";
    $params[':q'] = "%{$search}%";
}
if ($filterState !== '') {
    $where[] = "state = :state";
    $params[':state'] = $filterState;
}
if ($filterType !== '') {
    $where[] = "type = :type";
    $params[':type'] = $filterType;
}

$whereClause = implode(" AND ", $where);

// Total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM university WHERE {$whereClause}");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

// Fetch Records
$dataStmt = $pdo->prepare("
    SELECT * FROM university 
    WHERE {$whereClause} 
    ORDER BY id ASC 
    LIMIT {$perPage} OFFSET {$offset}
");
$dataStmt->execute($params);
$universities = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

// Distinct states for dropdown
$states = $pdo->query("SELECT DISTINCT state FROM university WHERE state IS NOT NULL AND state != '' ORDER BY state ASC")->fetchAll(PDO::FETCH_COLUMN);
$types = $pdo->query("SELECT DISTINCT type FROM university WHERE type IS NOT NULL AND type != '' ORDER BY type ASC")->fetchAll(PDO::FETCH_COLUMN);
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
                        <i class="fas fa-university text-primary me-2"></i> Universities Management
                    </h4>
                    <p class="text-muted small mb-0">Total <?= number_format($totalRows) ?> records found matching current criteria</p>
                </div>
                <div>
                    <button class="btn btn-primary-custom btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#editModal" onclick="openAddModal()">
                        <i class="fas fa-plus me-1"></i> Add University
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
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by University name, AISHE code, or district..." value="<?= htmlspecialchars($search) ?>">
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
                    <div class="col-md-2">
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <?php foreach ($types as $tp): ?>
                                <option value="<?= htmlspecialchars($tp) ?>" <?= $filterType === $tp ? 'selected' : '' ?>><?= htmlspecialchars($tp) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary-custom btn-sm w-100">Filter</button>
                        <?php if ($search || $filterState || $filterType): ?>
                            <a href="universities.php" class="btn btn-light btn-sm border" title="Clear Filters"><i class="fas fa-times"></i></a>
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
                                <th>University Name &amp; Code</th>
                                <th>State &amp; District</th>
                                <th>Category / Type</th>
                                <th>Website</th>
                                <th style="width: 130px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($universities)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-2x mb-2 d-block opacity-50"></i>
                                        No universities found matching your criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($universities as $u): ?>
                                    <tr>
                                        <td class="text-muted fw-bold">#<?= (int)$u['id'] ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($u['name']) ?></span>
                                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;">AISHE: <?= htmlspecialchars($u['aishe_code']) ?></span>
                                            <?php if (!empty($u['year_of_establishment'])): ?>
                                                <small class="text-muted ms-2">Est. <?= htmlspecialchars($u['year_of_establishment']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($u['district'] ?: '-') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($u['state']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <?= htmlspecialchars($u['type'] ?: 'General') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($u['website'])): ?>
                                                <a href="<?= htmlspecialchars(strpos($u['website'], 'http') === 0 ? $u['website'] : 'http://' . $u['website']) ?>" target="_blank" class="small text-decoration-none text-primary" style="word-break: break-all;">
                                                    <i class="fas fa-link me-1"></i>Visit
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="btn-group btn-group-sm">
                                                <a href="../university/<?= urlencode($u['slug'] ?? '') ?>" target="_blank" class="btn btn-light border py-1 px-2" title="View Public Page">
                                                    <i class="fas fa-external-link-alt text-primary"></i>
                                                </a>
                                                <button class="btn btn-light border py-1 px-2" title="Edit" onclick='openEditModal(<?= json_encode($u) ?>)'>
                                                    <i class="fas fa-pencil-alt text-secondary"></i>
                                                </button>
                                                <form method="POST" onsubmit="return confirm('Delete university <?= addslashes($u['name']) ?>?');" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
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
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&type=<?= urlencode($filterType) ?>&page=<?= $page - 1 ?>">&laquo;</a>
                                </li>
                                <?php
                                $startP = max(1, $page - 2);
                                $endP = min($totalPages, $page + 2);
                                for ($i = $startP; $i <= $endP; $i++):
                                ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&type=<?= urlencode($filterType) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&type=<?= urlencode($filterType) ?>&page=<?= $page + 1 ?>">&raquo;</a>
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
                <h5 class="modal-title fw-bold" id="modalTitle">Add University</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-bold">University Name *</label>
                    <input type="text" name="name" id="formName" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">AISHE Code *</label>
                    <input type="text" name="aishe_code" id="formAishe" class="form-control" required placeholder="e.g. U-0001">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">State *</label>
                    <input type="text" name="state" id="formState" class="form-control" required list="stateOptions">
                    <datalist id="stateOptions">
                        <?php foreach ($states as $st): ?>
                            <option value="<?= htmlspecialchars($st) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">District</label>
                    <input type="text" name="district" id="formDistrict" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">University Type</label>
                    <input type="text" name="type" id="formType" class="form-control" placeholder="e.g. State Public University">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Year of Establishment</label>
                    <input type="text" name="year_of_establishment" id="formYear" class="form-control" placeholder="e.g. 1960">
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Official Website URL</label>
                    <input type="text" name="website" id="formWebsite" class="form-control" placeholder="https://example.edu.in">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary-custom btn-sm">Save University</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add University';
    document.getElementById('formId').value = '0';
    document.getElementById('formName').value = '';
    document.getElementById('formAishe').value = '';
    document.getElementById('formState').value = '';
    document.getElementById('formDistrict').value = '';
    document.getElementById('formType').value = '';
    document.getElementById('formYear').value = '';
    document.getElementById('formWebsite').value = '';
}

function openEditModal(univ) {
    document.getElementById('modalTitle').textContent = 'Edit University: ' + univ.name;
    document.getElementById('formId').value = univ.id;
    document.getElementById('formName').value = univ.name || '';
    document.getElementById('formAishe').value = univ.aishe_code || '';
    document.getElementById('formState').value = univ.state || '';
    document.getElementById('formDistrict').value = univ.district || '';
    document.getElementById('formType').value = univ.type || '';
    document.getElementById('formYear').value = univ.year_of_establishment || '';
    document.getElementById('formWebsite').value = univ.website || '';
    
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>

<?php include __DIR__ . '/admin-footer.php'; ?>
