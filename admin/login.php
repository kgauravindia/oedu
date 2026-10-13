<?php
// admin/login.php — Admin Portal Sign In
require_once __DIR__ . '/auth_check.php';

if (checkAdminAuth()) {
    header("Location: index.php");
    exit;
}

$error = '';
$ref = $_GET['ref'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `admin_users` WHERE (`username` = :u OR `email` = :u) AND `status` = 'ACTIVE' LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Update last_login
            $upd = $pdo->prepare("UPDATE `admin_users` SET `last_login` = NOW() WHERE `id` = :id");
            $upd->execute([':id' => $user['id']]);

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_user'] = $user['username'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_role'] = $user['role'];

            $dest = !empty($_POST['ref']) ? urldecode($_POST['ref']) : 'index.php';
            header("Location: " . $dest);
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Sign In — Oedu.in Control Hub</title>
    <link rel="icon" type="image/png" href="../favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0B0F19 0%, #111827 50%, #1E293B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 1.5rem;
            color: #F8FAFC;
        }
        .login-card {
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 420px;
            padding: 2.25rem;
            color: #1E293B;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .brand-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }
        .brand-img {
            max-height: 48px;
            margin-bottom: 0.75rem;
        }
        .form-control:focus {
            border-color: #FF6B00;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 0, 0.15);
        }
        .btn-orange {
            background: #FF6B00;
            border: none;
            color: #FFF;
            font-weight: 700;
            padding: 0.7rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn-orange:hover {
            background: #E65A00;
            color: #FFF;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-header">
        <a href="../index.php">
            <img src="../logo_full.png" alt="Oedu.in" class="brand-img">
        </a>
        <h4 class="fw-bold mb-1" style="color: #0F172A;">Admin Control Hub</h4>
        <p class="text-muted small mb-0">National Higher Education Management</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-3">
            <i class="fas fa-circle-exclamation flex-shrink-0"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="hidden" name="ref" value="<?= htmlspecialchars($ref) ?>">

        <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Username or Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-user"></i></span>
                <input type="text" name="username" class="form-control border-start-0" required placeholder="admin" autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control border-start-0" required placeholder="••••••••">
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe">
                <label class="form-check-label small text-muted" for="rememberMe">Remember session</label>
            </div>
            <a href="../index.php" class="small text-muted text-decoration-none">← Public Site</a>
        </div>

        <button type="submit" class="btn btn-orange w-100">
            Sign In to Hub <i class="fas fa-arrow-right ms-1"></i>
        </button>
    </form>

    <div class="mt-4 pt-3 border-top text-center">
        <small class="text-muted" style="font-size: 0.76rem;">
            Default Superadmin: <strong>admin</strong> / <strong>Admin@Oedu2026!</strong>
        </small>
    </div>
</div>

</body>
</html>
