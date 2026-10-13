<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Admin Staff & Access Control - Oedu Admin';
$msg = '';
$msgType = 'success';
$currentAdminId = (int)($_SESSION['admin_id'] ?? 0);

// Handle Actions (Add / Edit / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === $currentAdminId) {
            $msg = "You cannot delete your own logged-in account.";
            $msgType = 'danger';
        } elseif ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $msg = "Admin user #{$id} has been removed.";
        }
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? $username);
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'Editor');
        $status = trim($_POST['status'] ?? 'ACTIVE');
        $newPassword = $_POST['password'] ?? '';

        if ($username === '' || $email === '') {
            $msg = "Username and email are required.";
            $msgType = 'danger';
        } else {
            if ($id > 0) {
                // Update existing user
                if (!empty($newPassword)) {
                    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("
                        UPDATE admin_users 
                        SET username = :username, name = :name, email = :email, role = :role, status = :status, `password` = :hash, updated_at = NOW() 
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':username' => $username,
                        ':name' => $name,
                        ':email' => $email,
                        ':role' => $role,
                        ':status' => $status,
                        ':hash' => $hash,
                        ':id' => $id
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE admin_users 
                        SET username = :username, name = :name, email = :email, role = :role, status = :status, updated_at = NOW() 
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':username' => $username,
                        ':name' => $name,
                        ':email' => $email,
                        ':role' => $role,
                        ':status' => $status,
                        ':id' => $id
                    ]);
                }
                $msg = "User \"{$username}\" updated successfully.";
            } else {
                // Insert new admin user
                if (empty($newPassword)) {
                    $msg = "Password is required for new admin users.";
                    $msgType = 'danger';
                } else {
                    // Check duplicate
                    $check = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = :u OR email = :e");
                    $check->execute([':u' => $username, ':e' => $email]);
                    if ($check->fetchColumn() > 0) {
                        $msg = "A user with this username or email already exists.";
                        $msgType = 'danger';
                    } else {
                        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("
                            INSERT INTO admin_users (username, name, email, `password`, role, status, created_at, updated_at)
                            VALUES (:username, :name, :email, :hash, :role, :status, NOW(), NOW())
                        ");
                        $stmt->execute([
                            ':username' => $username,
                            ':name' => $name,
                            ':email' => $email,
                            ':hash' => $hash,
                            ':role' => $role,
                            ':status' => $status
                        ]);
                        $msg = "New administrator \"{$username}\" created.";
                    }
                }
            }
        }
    }
}

// Fetch all admin users
$usersStmt = $pdo->query("SELECT * FROM admin_users ORDER BY id ASC");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <i class="fas fa-users-cog text-primary me-2"></i> Admin Staff &amp; Access Control
                    </h4>
                    <p class="text-muted small mb-0">Manage portal administrative staff, roles, and security access</p>
                </div>
                <div>
                    <button class="btn btn-primary-custom btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAddModal()">
                        <i class="fas fa-user-plus me-1"></i> Add Administrator
                    </button>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?> alert-dismissible fade show alert-auto-dismiss" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Table Card -->
            <div class="data-table-card">
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>Admin User</th>
                                <th>Email Address</th>
                                <th>Assigned Role</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th style="width: 120px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="text-muted fw-bold">#<?= (int)$u['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="admin-avatar" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block"><?= htmlspecialchars($u['username']) ?></span>
                                                <?php if ((int)$u['id'] === $currentAdminId): ?>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">You</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark small fw-semibold"><?= htmlspecialchars($u['email']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-bold">
                                            <?= htmlspecialchars($u['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['status'] === 'active'): ?>
                                            <span class="badge bg-success rounded-pill px-2">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-2">Suspended</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars(date('d M Y', strtotime($u['created_at'] ?? 'now'))) ?></small>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-light border py-1 px-2" title="Edit User" onclick='openEditModal(<?= json_encode($u) ?>)'>
                                                <i class="fas fa-pencil-alt text-secondary"></i>
                                            </button>
                                            <?php if ((int)$u['id'] !== $currentAdminId): ?>
                                                <form method="POST" onsubmit="return confirm('Remove administrator <?= addslashes($u['username']) ?>?');" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="btn btn-light border py-1 px-2 text-danger" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="formId" value="0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Add Administrator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Username *</label>
                    <input type="text" name="username" id="formUsername" class="form-control" required placeholder="e.g. editor_admin">
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Email Address *</label>
                    <input type="email" name="email" id="formEmail" class="form-control" required placeholder="admin@oedu.in">
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold" id="labelPassword">Password *</label>
                    <input type="password" name="password" id="formPassword" class="form-control" placeholder="Leave blank to keep unchanged">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Role</label>
                    <select name="role" id="formRole" class="form-select">
                        <option value="Super Admin">Super Admin</option>
                        <option value="Editor">Editor</option>
                        <option value="Manager">Manager</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" id="formStatus" class="form-select">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary-custom btn-sm">Save Account</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Administrator';
    document.getElementById('formId').value = '0';
    document.getElementById('formUsername').value = '';
    document.getElementById('formEmail').value = '';
    document.getElementById('formPassword').value = '';
    document.getElementById('formPassword').required = true;
    document.getElementById('labelPassword').textContent = 'Password *';
    document.getElementById('formRole').value = 'Editor';
    document.getElementById('formStatus').value = 'active';
}

function openEditModal(u) {
    document.getElementById('modalTitle').textContent = 'Edit Administrator: ' + u.username;
    document.getElementById('formId').value = u.id;
    document.getElementById('formUsername').value = u.username || '';
    document.getElementById('formEmail').value = u.email || '';
    document.getElementById('formPassword').value = '';
    document.getElementById('formPassword').required = false;
    document.getElementById('labelPassword').textContent = 'New Password (leave empty to keep current)';
    document.getElementById('formRole').value = u.role || 'Editor';
    document.getElementById('formStatus').value = u.status || 'active';
    
    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}
</script>

<?php include __DIR__ . '/admin-footer.php'; ?>
