<?php
// admin/students.php — Admin Panel Registered Students Management
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Registered Students — Online Education Admin';
$msg = '';
$msgType = 'success';

// Handle Actions (Delete / Toggle Verification)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM `user` WHERE id = :id AND role = 'student'");
        $stmt->execute([':id' => $id]);
        $msg = "Student record #{$id} has been removed.";
    } elseif ($action === 'toggle_verify' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE `user` SET is_verified = IF(is_verified=1, 0, 1), updated_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $msg = "Verification status updated for Student #{$id}.";
    }
}

// Search & Filter Inputs
$search = trim($_GET['q'] ?? '');
$stateFilter = trim($_GET['state'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = ["role = 'student'"];
$params = [];

if ($search !== '') {
    $where[] = "(name LIKE :q OR email LIKE :q OR phone LIKE :q OR district LIKE :q OR target_course LIKE :q)";
    $params[':q'] = "%{$search}%";
}
if ($stateFilter !== '' && $stateFilter !== 'all') {
    $where[] = "state = :state";
    $params[':state'] = $stateFilter;
}

$whereClause = implode(" AND ", $where);

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM `user` WHERE {$whereClause}");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Summary counts
$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM `user` WHERE role = 'student'")->fetchColumn();
$verifiedStudents = (int)$pdo->query("SELECT COUNT(*) FROM `user` WHERE role = 'student' AND is_verified = 1")->fetchColumn();
$todayStudents = (int)$pdo->query("SELECT COUNT(*) FROM `user` WHERE role = 'student' AND DATE(created_at) = CURDATE()")->fetchColumn();

// Fetch students
$dataStmt = $pdo->prepare("
    SELECT * FROM `user` 
    WHERE {$whereClause} 
    ORDER BY id DESC 
    LIMIT {$perPage} OFFSET {$offset}
");
$dataStmt->execute($params);
$students = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

// Distinct states for filter
$states = $pdo->query("SELECT DISTINCT state FROM `user` WHERE role = 'student' AND state IS NOT NULL AND state != '' ORDER BY state ASC")->fetchAll(PDO::FETCH_COLUMN);
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
                        <i class="fas fa-user-graduate text-warning me-2"></i> Registered Students
                    </h4>
                    <p class="text-muted small mb-0">Manage registered students, authentication, and counseling profiles</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="../register.php" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Public Registration Page
                    </a>
                </div>
            </div>

            <?php if (!empty($msg)): ?>
                <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-bold text-uppercase">Total Students</div>
                                <div class="h3 fw-bold mb-0 mt-1"><?= number_format($totalStudents) ?></div>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-bold text-uppercase">Verified Students</div>
                                <div class="h3 fw-bold mb-0 mt-1 text-success"><?= number_format($verifiedStudents) ?></div>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-bold text-uppercase">Registered Today</div>
                                <div class="h3 fw-bold mb-0 mt-1 text-warning"><?= number_format($todayStudents) ?></div>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                                <i class="fas fa-calendar-day fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Form -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="students.php" class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Search by name, email, phone, city, course..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="state" class="form-select form-select-sm">
                                <option value="all">All States</option>
                                <?php foreach ($states as $st): ?>
                                    <option value="<?= htmlspecialchars($st) ?>" <?= $stateFilter === $st ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-dark btn-sm flex-grow-1">Filter</button>
                            <?php if ($search || ($stateFilter && $stateFilter !== 'all')): ?>
                                <a href="students.php" class="btn btn-outline-secondary btn-sm">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table of Students -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <th class="ps-3" style="width: 60px;">ID</th>
                                    <th>Student Name</th>
                                    <th>Contact Info</th>
                                    <th>Location</th>
                                    <th>Academic Level &amp; Target</th>
                                    <th>Registered</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-user-graduate fa-3x mb-3 text-secondary opacity-50"></i>
                                            <p class="mb-0">No registered students found matching your criteria.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $stu): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-muted">#<?= $stu['id'] ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($stu['name']) ?></div>
                                                <?php if (!empty($stu['gender'])): ?>
                                                    <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">
                                                        <?= htmlspecialchars($stu['gender']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="font-size: 0.85rem;"><i class="fas fa-envelope text-muted me-1"></i> <?= htmlspecialchars($stu['email']) ?></div>
                                                <?php if (!empty($stu['phone'])): ?>
                                                    <div style="font-size: 0.85rem;"><i class="fas fa-phone text-muted me-1"></i> +91 <?= htmlspecialchars($stu['phone']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($stu['state'] ?: 'India') ?></div>
                                                <?php if (!empty($stu['district'])): ?>
                                                    <div class="text-muted" style="font-size: 0.78rem;"><?= htmlspecialchars($stu['district']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($stu['education_level'])): ?>
                                                    <div style="font-size: 0.82rem; font-weight: 600; color: #1E293B;">
                                                        <?= htmlspecialchars($stu['education_level']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($stu['target_course'])): ?>
                                                    <span class="badge bg-warning bg-opacity-25 text-dark border" style="font-size: 0.72rem;">
                                                        <?= htmlspecialchars($stu['target_course']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-size: 0.8rem; color: #64748B;">
                                                <?= date('M d, Y', strtotime($stu['created_at'])) ?>
                                                <?php if (!empty($stu['last_login'])): ?>
                                                    <div style="font-size: 0.72rem; color: #10B981;">
                                                        Login: <?= date('M d, H:i', strtotime($stu['last_login'])) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="students.php" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle_verify">
                                                    <input type="hidden" name="id" value="<?= $stu['id'] ?>">
                                                    <?php if ($stu['is_verified']): ?>
                                                        <button type="submit" class="badge bg-success border-0" title="Click to unverify">
                                                            Verified
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" class="badge bg-secondary border-0" title="Click to verify">
                                                            Unverified
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                            <td class="text-end pe-3">
                                                <form method="POST" action="students.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $stu['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white border-0 py-3">
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Prev</a>
                                </li>
                            <?php endif; ?>
                            <li class="page-item disabled">
                                <span class="page-link">Page <?= $page ?> of <?= $totalPages ?> (<?= number_format($totalRows) ?> total)</span>
                            </li>
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        <?php include __DIR__ . '/admin-footer.php'; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
