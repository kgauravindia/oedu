<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Change Password - Online Education Admin';
$msg = '';
$msgType = 'success';
$adminId = (int)($_SESSION['admin_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $msg = "Please fill in all password fields.";
        $msgType = 'danger';
    } elseif ($newPassword !== $confirmPassword) {
        $msg = "New password and confirmation do not match.";
        $msgType = 'danger';
    } elseif (strlen($newPassword) < 6) {
        $msg = "New password must be at least 6 characters long.";
        $msgType = 'danger';
    } else {
        // Fetch current hash
        $stmt = $pdo->prepare("SELECT `password` FROM admin_users WHERE id = :id");
        $stmt->execute([':id' => $adminId]);
        $currentHash = $stmt->fetchColumn();

        if ($currentHash && password_verify($currentPassword, $currentHash)) {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE admin_users SET `password` = :hash, updated_at = NOW() WHERE id = :id");
            $upd->execute([':hash' => $newHash, ':id' => $adminId]);
            $msg = "Your password has been changed successfully!";
        } else {
            $msg = "Your current password was incorrect. Please try again.";
            $msgType = 'danger';
        }
    }
}
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
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #0F172A;">
                        <i class="fas fa-key text-warning me-2"></i> Change Account Password
                    </h4>
                    <p class="text-muted small mb-0">Update your administrative credentials securely</p>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?> alert-dismissible fade show alert-auto-dismiss" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-7 col-lg-5">
                    <div class="data-table-card p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Current Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="current_password" class="form-control border-start-0" required placeholder="Enter current password">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-shield-alt text-muted"></i></span>
                                    <input type="password" name="new_password" class="form-control border-start-0" required placeholder="At least 6 characters">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-check-circle text-muted"></i></span>
                                    <input type="password" name="confirm_password" class="form-control border-start-0" required placeholder="Repeat new password">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100 py-2">
                                <i class="fas fa-save me-1"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

<?php include __DIR__ . '/admin-footer.php'; ?>
