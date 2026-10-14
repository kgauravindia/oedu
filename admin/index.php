<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Executive Dashboard - Oedu Admin';

// Fetch Core Metrics
try {
    $uniCount = (int)$pdo->query("SELECT COUNT(*) FROM university")->fetchColumn();
    $collegeCount = (int)$pdo->query("SELECT COUNT(*) FROM college")->fetchColumn();
    $instituteCount = (int)$pdo->query("SELECT COUNT(*) FROM institute")->fetchColumn();
    $sdbCount = (int)$pdo->query("SELECT COUNT(*) FROM op_sdb")->fetchColumn();
    
    $totalHigherEd = $uniCount + $collegeCount + $instituteCount;

    $totalEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiry")->fetchColumn();
    $newEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiry WHERE status = 'new'")->fetchColumn();

    // Recent 5 Inquiries
    $recentEnquiriesStmt = $pdo->query("SELECT * FROM enquiry ORDER BY id DESC LIMIT 5");
    $recentEnquiries = $recentEnquiriesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Top 5 States in Colleges
    $topStatesStmt = $pdo->query("
        SELECT state_name, COUNT(*) as cnt 
        FROM college 
        WHERE state_name IS NOT NULL AND state_name != '' 
        GROUP BY state_name 
        ORDER BY cnt DESC 
        LIMIT 5
    ");
    $topStates = $topStatesStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Admin Custom CSS -->
    <link href="admin.css" rel="stylesheet">
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/admin-menu.php'; ?>

    <div style="display: flex; flex-direction: column; min-width: 0; width: 100%;">
        <?php include __DIR__ . '/admin-header.php'; ?>

        <main class="admin-main">
            <!-- Welcome Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #0F172A; letter-spacing: -0.3px;">
                        Dashboard Overview
                    </h4>
                    <p class="text-muted small mb-0">
                        Higher education metrics &amp; student admissions database monitoring.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="enquiries.php" class="btn btn-outline-danger btn-sm px-3 fw-bold rounded-3">
                        <i class="fas fa-bell me-1"></i> New Leads (<?= $newEnquiries ?>)
                    </a>
                    <a href="colleges.php" class="btn btn-primary-custom btn-sm px-3 shadow-sm">
                        <i class="fas fa-search me-1"></i> Search Colleges
                    </a>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="row g-3 mb-4">
                <!-- Total Higher Ed Institutions -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div>
                            <div class="stat-title">Total Institutions</div>
                            <div class="stat-value"><?= number_format($totalHigherEd) ?></div>
                            <span class="stat-badge" style="background: #FFF7ED; color: #EA580C;">
                                <i class="fas fa-database me-1"></i> Complete Registry
                            </span>
                        </div>
                        <div class="stat-icon" style="background: #FFF7ED; color: #FF6B00;">
                            <i class="fas fa-school-flag"></i>
                        </div>
                    </div>
                </div>

                <!-- Universities -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div>
                            <div class="stat-title">Universities</div>
                            <div class="stat-value"><?= number_format($uniCount) ?></div>
                            <a href="universities.php" class="stat-badge text-decoration-none" style="background: #EFF6FF; color: #2563EB;">
                                View All &rarr;
                            </a>
                        </div>
                        <div class="stat-icon" style="background: #EFF6FF; color: #3B82F6;">
                            <i class="fas fa-university"></i>
                        </div>
                    </div>
                </div>

                <!-- Affiliated Colleges -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div>
                            <div class="stat-title">Affiliated Colleges</div>
                            <div class="stat-value"><?= number_format($collegeCount) ?></div>
                            <a href="colleges.php" class="stat-badge text-decoration-none" style="background: #FEF3C7; color: #D97706;">
                                AISHE Directory &rarr;
                            </a>
                        </div>
                        <div class="stat-icon" style="background: #FEF3C7; color: #F59E0B;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>

                <!-- Standalone Institutes -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div>
                            <div class="stat-title">Standalone Institutes</div>
                            <div class="stat-value"><?= number_format($instituteCount) ?></div>
                            <a href="institutes.php" class="stat-badge text-decoration-none" style="background: #F0FDF4; color: #16A34A;">
                                Polytechnic &amp; PG &rarr;
                            </a>
                        </div>
                        <div class="stat-icon" style="background: #F0FDF4; color: #10B981;">
                            <i class="fas fa-building-columns"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid: Left 8 cols, Right 4 cols -->
            <div class="row g-4 mb-4">
                <!-- Recent Student Enquiries -->
                <div class="col-lg-8">
                    <div class="data-table-card">
                        <div class="table-card-header">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-envelope-open-text text-danger me-2"></i> Recent Student Inquiries
                                </h6>
                                <small class="text-muted">Direct admission leads submitted from public portal</small>
                            </div>
                            <a href="enquiries.php" class="btn btn-sm btn-light border fw-semibold rounded-3">
                                View All Inquiries
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th>Student / Sender</th>
                                        <th>Contact Details</th>
                                        <th>Target Institution</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentEnquiries)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-50"></i>
                                                No inquiries received yet.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentEnquiries as $enq): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($enq['sender_name']) ?></span>
                                                    <small class="badge bg-light text-secondary border text-capitalize"><?= htmlspecialchars($enq['sender_type'] ?? 'student') ?></small>
                                                </td>
                                                <td>
                                                    <div class="small fw-semibold"><?= htmlspecialchars($enq['sender_phone']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($enq['sender_email'] ?? 'No email') ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle text-uppercase" style="font-size: 0.7rem;">
                                                        <?= htmlspecialchars($enq['target_type']) ?> #<?= (int)$enq['target_id'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($enq['status'] === 'new'): ?>
                                                        <span class="badge bg-danger rounded-pill px-2">New</span>
                                                    <?php elseif ($enq['status'] === 'contacted'): ?>
                                                        <span class="badge bg-warning text-dark rounded-pill px-2">Contacted</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success rounded-pill px-2">Closed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="enquiries.php?id=<?= $enq['id'] ?>" class="btn btn-sm btn-light border py-1 px-2 rounded-2" title="Manage">
                                                        <i class="fas fa-chevron-right text-muted small"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top States Breakdown & Quick Links -->
                <div class="col-lg-4">
                    <!-- Top States -->
                    <div class="data-table-card mb-4">
                        <div class="table-card-header">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i> Top States by Colleges
                            </h6>
                        </div>
                        <div class="p-3">
                            <?php if (!empty($topStates)): ?>
                                <?php foreach ($topStates as $st): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small fw-bold mb-1">
                                            <span><?= htmlspecialchars($st['state_name']) ?></span>
                                            <span class="text-muted"><?= number_format($st['cnt']) ?> colleges</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, round(($st['cnt'] / max(1, $topStates[0]['cnt'])) * 100)) ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- System Status Card -->
                    <div class="data-table-card">
                        <div class="table-card-header">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-shield-alt text-success me-2"></i> System Security
                            </h6>
                        </div>
                        <div class="p-3">
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="small text-muted">PHP Version</span>
                                <span class="badge bg-light text-dark border"><?= PHP_VERSION ?></span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="small text-muted">Active Database</span>
                                <span class="small fw-bold text-success"><i class="fas fa-check-circle me-1"></i> Connected</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="small text-muted">School Directory</span>
                                <span class="small fw-bold"><?= number_format($sdbCount) ?> records</span>
                            </div>
                            <div class="pt-3">
                                <a href="change-password.php" class="btn btn-light border w-100 btn-sm fw-bold">
                                    <i class="fas fa-key me-1 text-warning"></i> Update Admin Password
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<?php include __DIR__ . '/admin-footer.php'; ?>
