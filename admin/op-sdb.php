<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'School Education Directory - Oedu Admin';

// Search & Filter Inputs
$search = trim($_GET['q'] ?? '');
$filterState = trim($_GET['state'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(state LIKE :q OR district LIKE :q OR block LIKE :q)";
    $params[':q'] = "%{$search}%";
}
if ($filterState !== '') {
    $where[] = "state = :state";
    $params[':state'] = $filterState;
}

$whereClause = implode(" AND ", $where);

// Total Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM op_sdb WHERE {$whereClause}");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

// Fetch Records
$dataStmt = $pdo->prepare("
    SELECT * FROM op_sdb 
    WHERE {$whereClause} 
    ORDER BY state ASC, district ASC, block ASC 
    LIMIT {$perPage} OFFSET {$offset}
");
$dataStmt->execute($params);
$records = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

$states = $pdo->query("SELECT DISTINCT state FROM op_sdb WHERE state IS NOT NULL AND state != '' ORDER BY state ASC")->fetchAll(PDO::FETCH_COLUMN);
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
                        <i class="fas fa-school text-success me-2"></i> School Education Directory (State &amp; Block Hierarchy)
                    </h4>
                    <p class="text-muted small mb-0">Total <?= number_format($totalRows) ?> block mapping records</p>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="data-table-card mb-4 p-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search state, district, or educational block..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="state" class="form-select form-select-sm">
                            <option value="">All States</option>
                            <?php foreach ($states as $st): ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= $filterState === $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary-custom btn-sm w-100">Filter</button>
                        <?php if ($search || $filterState): ?>
                            <a href="op-sdb.php" class="btn btn-light btn-sm border" title="Clear Filters"><i class="fas fa-times"></i></a>
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
                                <th>State Name &amp; Code</th>
                                <th>District Name &amp; Code</th>
                                <th>Educational Block</th>
                                <th>Census 2001</th>
                                <th>Census 2011</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-2x mb-2 d-block opacity-50"></i>
                                        No educational block records found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($records as $r): ?>
                                    <tr>
                                        <td class="text-muted fw-bold">#<?= (int)$r['id'] ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($r['state']) ?></span>
                                            <small class="badge bg-light text-muted border">Code: <?= htmlspecialchars($r['state_code'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($r['district']) ?></span>
                                            <small class="badge bg-light text-muted border">Code: <?= htmlspecialchars($r['district_code'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-primary"><?= htmlspecialchars($r['block'] ?? '-') ?></span>
                                            <small class="text-muted ms-1">(<?= htmlspecialchars($r['block_code'] ?? '-') ?>)</small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= htmlspecialchars($r['c_2001'] ?: '-') ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= htmlspecialchars($r['c_2011'] ?: '-') ?></small>
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
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&page=<?= $page - 1 ?>">&laquo;</a>
                                </li>
                                <?php
                                $startP = max(1, $page - 2);
                                $endP = min($totalPages, $page + 2);
                                for ($i = $startP; $i <= $endP; $i++):
                                ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&state=<?= urlencode($filterState) ?>&page=<?= $page + 1 ?>">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

<?php include __DIR__ . '/admin-footer.php'; ?>
