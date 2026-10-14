<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Student Admission Inquiries - Online Education Admin';
$msg = '';
$msgType = 'success';

// Handle Actions (Status Update / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM enquiry WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $msg = "Inquiry #{$id} was successfully removed.";
        }
    } elseif ($action === 'update_status') {
        $id = (int)($_POST['id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? 'new');
        if ($id > 0 && in_array($newStatus, ['new', 'contacted', 'closed'])) {
            $stmt = $pdo->prepare("UPDATE enquiry SET status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':status' => $newStatus, ':id' => $id]);
            $msg = "Inquiry #{$id} marked as " . strtoupper($newStatus) . ".";
        }
    }
}

// Search & Filter Inputs
$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(sender_name LIKE :q OR sender_phone LIKE :q OR sender_email LIKE :q OR message LIKE :q)";
    $params[':q'] = "%{$search}%";
}
if ($statusFilter !== '' && $statusFilter !== 'all') {
    $where[] = "status = :status";
    $params[':status'] = $statusFilter;
}

$whereClause = implode(" AND ", $where);

// Total Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM enquiry WHERE {$whereClause}");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

// Summary status counts
$countNew = (int)$pdo->query("SELECT COUNT(*) FROM enquiry WHERE status = 'new'")->fetchColumn();
$countContacted = (int)$pdo->query("SELECT COUNT(*) FROM enquiry WHERE status = 'contacted'")->fetchColumn();
$countClosed = (int)$pdo->query("SELECT COUNT(*) FROM enquiry WHERE status = 'closed'")->fetchColumn();
$countAll = $countNew + $countContacted + $countClosed;

// Fetch inquiries
$dataStmt = $pdo->prepare("
    SELECT * FROM enquiry 
    WHERE {$whereClause} 
    ORDER BY id DESC 
    LIMIT {$perPage} OFFSET {$offset}
");
$dataStmt->execute($params);
$enquiries = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <i class="fas fa-envelope-open-text text-danger me-2"></i> Student Admission Leads
                    </h4>
                    <p class="text-muted small mb-0">Direct student &amp; parent inquiries submitted from public portal</p>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?> alert-dismissible fade show alert-auto-dismiss" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Status Tabs -->
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="enquiries.php" class="btn btn-sm <?= ($statusFilter === '' || $statusFilter === 'all') ? 'btn-dark' : 'btn-light border' ?> fw-semibold">
                    All Leads (<?= $countAll ?>)
                </a>
                <a href="enquiries.php?status=new" class="btn btn-sm <?= $statusFilter === 'new' ? 'btn-danger' : 'btn-light border' ?> fw-semibold">
                    <i class="fas fa-exclamation-circle me-1"></i> New (<?= $countNew ?>)
                </a>
                <a href="enquiries.php?status=contacted" class="btn btn-sm <?= $statusFilter === 'contacted' ? 'btn-warning text-dark' : 'btn-light border' ?> fw-semibold">
                    <i class="fas fa-phone-alt me-1"></i> Contacted (<?= $countContacted ?>)
                </a>
                <a href="enquiries.php?status=closed" class="btn btn-sm <?= $statusFilter === 'closed' ? 'btn-success' : 'btn-light border' ?> fw-semibold">
                    <i class="fas fa-check me-1"></i> Closed (<?= $countClosed ?>)
                </a>
            </div>

            <!-- Search Filter Bar -->
            <div class="data-table-card mb-4 p-3">
                <form method="GET" class="row g-2 align-items-center">
                    <?php if ($statusFilter): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                    <?php endif; ?>
                    <div class="col-md-9">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by student name, phone number, email, or message keyword..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button type="submit" class="btn btn-primary-custom btn-sm w-100">Search</button>
                        <?php if ($search): ?>
                            <a href="enquiries.php<?= $statusFilter ? '?status=' . urlencode($statusFilter) : '' ?>" class="btn btn-light btn-sm border" title="Clear Search"><i class="fas fa-times"></i></a>
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
                                <th style="width: 70px;">Lead #</th>
                                <th>Student / Applicant</th>
                                <th>Contact Information</th>
                                <th>Target Institution</th>
                                <th>Message / Note</th>
                                <th>Lead Status</th>
                                <th style="width: 130px; text-align: right;">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($enquiries)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-2 d-block opacity-50"></i>
                                        No student admission inquiries found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($enquiries as $enq): 
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $enq['sender_phone']);
                                    if (strlen($cleanPhone) === 10) {
                                        $waNumber = '91' . $cleanPhone;
                                    } else {
                                        $waNumber = $cleanPhone;
                                    }
                                    $waText = urlencode("Hello " . $enq['sender_name'] . ", this is regarding your inquiry on Online Education.");
                                ?>
                                    <tr>
                                        <td class="text-muted fw-bold">#<?= (int)$enq['id'] ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($enq['sender_name']) ?></span>
                                            <small class="badge bg-light text-secondary border text-capitalize"><?= htmlspecialchars($enq['sender_type'] ?? 'student') ?></small>
                                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                                <?= htmlspecialchars(date('d M Y, h:i A', strtotime($enq['created_at'] ?? 'now'))) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                <a href="tel:<?= htmlspecialchars($enq['sender_phone']) ?>" class="text-dark text-decoration-none">
                                                    <i class="fas fa-phone-alt me-1 text-primary small"></i><?= htmlspecialchars($enq['sender_phone']) ?>
                                                </a>
                                            </div>
                                            <?php if (!empty($enq['sender_email'])): ?>
                                                <small class="text-muted d-block">
                                                    <a href="mailto:<?= htmlspecialchars($enq['sender_email']) ?>" class="text-muted text-decoration-none">
                                                        <i class="fas fa-envelope me-1 small"></i><?= htmlspecialchars($enq['sender_email']) ?>
                                                    </a>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (!empty($enq['city_or_pincode'])): ?>
                                                <small class="text-muted"><i class="fas fa-map-marker-alt me-1 small"></i><?= htmlspecialchars($enq['city_or_pincode']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-uppercase" style="font-size: 0.7rem;">
                                                <?= htmlspecialchars($enq['target_type'] ?? 'General') ?> #<?= (int)$enq['target_id'] ?>
                                            </span>
                                        </td>
                                        <td style="max-width: 250px;">
                                            <div class="text-truncate small text-dark" title="<?= htmlspecialchars($enq['message']) ?>">
                                                <?= htmlspecialchars($enq['message']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="id" value="<?= $enq['id'] ?>">
                                                <select name="status" class="form-select form-select-sm py-0 ps-2 pe-4" onchange="this.form.submit()" style="font-size: 0.78rem; font-weight: 700; width: 115px; border-radius: 999px; <?= $enq['status'] === 'new' ? 'border-color: #EF4444; color: #EF4444;' : ($enq['status'] === 'contacted' ? 'border-color: #F59E0B; color: #D97706;' : 'border-color: #10B981; color: #10B981;') ?>">
                                                    <option value="new" <?= $enq['status'] === 'new' ? 'selected' : '' ?>>● New</option>
                                                    <option value="contacted" <?= $enq['status'] === 'contacted' ? 'selected' : '' ?>>● Contacted</option>
                                                    <option value="closed" <?= $enq['status'] === 'closed' ? 'selected' : '' ?>>● Closed</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($waNumber): ?>
                                                    <a href="https://wa.me/<?= $waNumber ?>?text=<?= $waText ?>" target="_blank" class="btn btn-light border py-1 px-2 text-success" title="Chat on WhatsApp">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button class="btn btn-light border py-1 px-2" title="View Full Message" onclick='viewMessage(<?= json_encode($enq) ?>)'>
                                                    <i class="fas fa-eye text-primary"></i>
                                                </button>
                                                <form method="POST" onsubmit="return confirm('Delete inquiry from <?= addslashes($enq['sender_name']) ?>?');" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $enq['id'] ?>">
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
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&page=<?= $page - 1 ?>">&laquo;</a>
                                </li>
                                <?php
                                $startP = max(1, $page - 2);
                                $endP = min($totalPages, $page + 2);
                                for ($i = $startP; $i <= $endP; $i++):
                                ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?q=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?q=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&page=<?= $page + 1 ?>">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

<!-- Message Detail Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalStudentName">Inquiry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span class="small text-muted d-block">Sender</span>
                    <strong id="modalSenderInfo"></strong>
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Phone &amp; Email</span>
                    <div id="modalContactInfo"></div>
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Student Message</span>
                    <div class="p-3 bg-light rounded-3 border small" id="modalMessageText" style="white-space: pre-wrap;"></div>
                </div>
                <div>
                    <span class="small text-muted d-block">Submitted On</span>
                    <small id="modalTimestamp" class="text-muted"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewMessage(enq) {
    document.getElementById('modalStudentName').textContent = 'Inquiry from ' + enq.sender_name;
    document.getElementById('modalSenderInfo').textContent = enq.sender_name + ' (' + (enq.sender_type || 'student') + ')';
    document.getElementById('modalContactInfo').innerHTML = '<strong>' + enq.sender_phone + '</strong> &bull; ' + (enq.sender_email || 'No email provided');
    document.getElementById('modalMessageText').textContent = enq.message || 'No message content provided.';
    document.getElementById('modalTimestamp').textContent = enq.created_at || 'Just now';
    
    const modal = new bootstrap.Modal(document.getElementById('messageModal'));
    modal.show();
}
</script>

<?php include __DIR__ . '/admin-footer.php'; ?>
