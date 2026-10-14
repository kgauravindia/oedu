<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$admin_name = $_SESSION['admin_name'] ?? $_SESSION['admin_user'] ?? 'Administrator';
$admin_role = $_SESSION['admin_role'] ?? 'Super Admin';
$admin_initial = strtoupper(substr($admin_name, 0, 1));
?>
<header class="top-bar">
    <div class="top-bar-left">
        <button class="btn btn-light d-lg-none rounded-3 px-2 border-0 shadow-sm" id="mobileHamburger" onclick="toggleMobileSidebar()" style="width: 40px; height: 40px; background: white;">
            <i class="fas fa-bars" style="color: var(--primary);"></i>
        </button>
        <div class="d-none d-lg-block">
            <h5 class="mb-0 fw-bold" style="font-family: 'Plus Jakarta Sans', sans-serif; color: #0F172A; font-size: 1.05rem;">
                Online Education <span class="badge" style="background: var(--primary); font-size: 0.7rem; vertical-align: middle;">Control Hub</span>
            </h5>
        </div>
    </div>
    
    <div class="top-bar-right">
        <a href="../index.php" class="top-bar-link shadow-sm" title="Visit Public Portal" target="_blank">
            <i class="fas fa-globe"></i>
        </a>
        
        <div class="dropdown">
            <a class="admin-profile-dropdown shadow-sm" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="admin-avatar">
                    <?= htmlspecialchars($admin_initial) ?>
                </div>
                <div class="admin-info d-none d-md-block">
                    <span class="admin-name"><?= htmlspecialchars($admin_name) ?></span>
                    <span class="admin-role"><?= htmlspecialchars($admin_role) ?></span>
                </div>
                <i class="fas fa-chevron-down text-muted ms-1" style="font-size: 0.72rem;"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" style="min-width: 210px;">
                <li class="px-3 py-2 border-bottom">
                    <p class="mb-0 fw-bold small text-dark"><?= htmlspecialchars($admin_name) ?></p>
                    <small class="text-muted"><?= htmlspecialchars($_SESSION['admin_email'] ?? 'admin@oedu.in') ?></small>
                </li>
                <li><a class="dropdown-item py-2" href="change-password.php"><i class="fas fa-key me-2 text-warning"></i> Change Password</a></li>
                <li><a class="dropdown-item py-2" href="users.php"><i class="fas fa-user-shield me-2 text-primary"></i> Admin Users</a></li>
                <li><a class="dropdown-item py-2" href="settings.php"><i class="fas fa-cog me-2 text-secondary"></i> System Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger fw-bold" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a></li>
            </ul>
        </div>
    </div>
</header>
