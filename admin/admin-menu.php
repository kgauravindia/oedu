<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Quick menu badge counts (cached or lightweight query)
$menuBadgeCounts = [
    'universities' => '1.5k',
    'colleges' => '54k',
    'institutes' => '17k',
    'enquiries' => 0
];

if (isset($pdo)) {
    try {
        $enqCount = $pdo->query("SELECT COUNT(*) FROM enquiry WHERE status = 'new'")->fetchColumn();
        $menuBadgeCounts['enquiries'] = (int)$enqCount;
    } catch (Exception $e) {
        // Fallback silently
    }
}
?>
<!-- Mobile backdrop overlay -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleMobileSidebar()"></div>

<!-- Modern Sticky Sidebar -->
<aside class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 9px; background: linear-gradient(135deg, #FF6B00 0%, #FF8533 100%); color: #fff; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(255, 107, 0, 0.35);">
                O
            </span>
            <span>Online <span style="color: #FFFFFF;">Education</span></span>
        </a>
        <button type="button" class="sidebar-toggle-btn d-none d-lg-flex" onclick="toggleSidebarCollapse()" title="Toggle Sidebar Width">
            <i class="fas fa-chevron-left" id="sidebarCollapseIcon"></i>
        </button>
    </div>

    <div class="flex-grow-1 overflow-auto" style="padding-bottom: 2rem;">
        <div class="nav-section-title">Overview</div>
        <a href="index.php" class="nav-link-custom <?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-th-large text-primary"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-section-title">Directory Management</div>
        <a href="universities.php" class="nav-link-custom <?= $currentPage === 'universities.php' ? 'active' : '' ?>">
            <i class="fas fa-university text-info"></i>
            <span>Universities</span>
            <span class="badge bg-secondary rounded-pill"><?= $menuBadgeCounts['universities'] ?></span>
        </a>

        <a href="colleges.php" class="nav-link-custom <?= $currentPage === 'colleges.php' ? 'active' : '' ?>">
            <i class="fas fa-graduation-cap text-warning"></i>
            <span>Affiliated Colleges</span>
            <span class="badge bg-secondary rounded-pill"><?= $menuBadgeCounts['colleges'] ?></span>
        </a>

        <a href="law-colleges.php" class="nav-link-custom <?= $currentPage === 'law-colleges.php' ? 'active' : '' ?>">
            <i class="fas fa-balance-scale" style="color: #60A5FA;"></i>
            <span>BCI Law Colleges</span>
            <span class="badge bg-secondary rounded-pill">3.1k</span>
        </a>

        <a href="institutes.php" class="nav-link-custom <?= $currentPage === 'institutes.php' ? 'active' : '' ?>">
            <i class="fas fa-building-columns" style="color: #38BDF8;"></i>
            <span>Standalone Institutes</span>
            <span class="badge bg-secondary rounded-pill"><?= $menuBadgeCounts['institutes'] ?></span>
        </a>

        <a href="op-sdb.php" class="nav-link-custom <?= $currentPage === 'op-sdb.php' ? 'active' : '' ?>">
            <i class="fas fa-school text-success"></i>
            <span>School Education</span>
        </a>

        <a href="../school.php" target="_blank" class="nav-link-custom">
            <i class="fas fa-chalkboard-teacher" style="color: #F59E0B;"></i>
            <span>CBSE Schools</span>
            <span class="badge bg-secondary rounded-pill">33k</span>
        </a>

        <a href="../syllabus.php" target="_blank" class="nav-link-custom">
            <i class="fas fa-book-open" style="color: #6366F1;"></i>
            <span>Curriculum &amp; Syllabus</span>
        </a>

        <div class="nav-section-title">Admissions & Leads</div>
        <a href="students.php" class="nav-link-custom <?= $currentPage === 'students.php' ? 'active' : '' ?>">
            <i class="fas fa-user-graduate text-warning"></i>
            <span>Registered Students</span>
        </a>

        <a href="enquiries.php" class="nav-link-custom <?= $currentPage === 'enquiries.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope-open-text" style="color: #F43F5E;"></i>
            <span>Student Inquiries</span>
            <?php if ($menuBadgeCounts['enquiries'] > 0): ?>
                <span class="badge bg-danger rounded-pill"><?= $menuBadgeCounts['enquiries'] ?></span>
            <?php else: ?>
                <span class="badge bg-secondary rounded-pill">0</span>
            <?php endif; ?>
        </a>

        <div class="nav-section-title">System & Security</div>
        <a href="users.php" class="nav-link-custom <?= $currentPage === 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users-cog text-light"></i>
            <span>Admin Staff</span>
        </a>

        <a href="change-password.php" class="nav-link-custom <?= $currentPage === 'change-password.php' ? 'active' : '' ?>">
            <i class="fas fa-key text-warning"></i>
            <span>Change Password</span>
        </a>

        <a href="git.php" class="nav-link-custom <?= $currentPage === 'git.php' ? 'active' : '' ?>">
            <i class="fab fa-git-alt" style="color: #F05032;"></i>
            <span>Git Version &amp; Sync</span>
        </a>

        <a href="sitemap.php" class="nav-link-custom <?= $currentPage === 'sitemap.php' ? 'active' : '' ?>">
            <i class="fas fa-sitemap text-warning"></i>
            <span>XML Sitemaps</span>
        </a>

        <a href="settings.php" class="nav-link-custom <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
            <i class="fas fa-sliders-h text-info"></i>
            <span>Portal Settings</span>
        </a>

        <div class="nav-section-title">Live Portals</div>
        <a href="../index.php" target="_blank" class="nav-link-custom">
            <i class="fas fa-external-link-alt text-muted"></i>
            <span>Public Website</span>
        </a>
    </div>

    <!-- Sidebar footer profile quick info -->
    <div style="padding: 1rem 1.25rem; border-top: 1px solid rgba(255, 255, 255, 0.08); background: rgba(0, 0, 0, 0.2);">
        <a href="logout.php" class="text-danger d-flex align-items-center text-decoration-none fw-bold" style="font-size: 0.85rem; gap: 0.75rem;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Out</span>
        </a>
    </div>
</aside>
