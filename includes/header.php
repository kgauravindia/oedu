<?php
// includes/header.php - Modular Header for Online Education (.php only)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pageTitle)) {
    $pageTitle = 'National University & College Directory of India | Online Education — Higher Education Portal';
}
if (!isset($pageDesc)) {
    $pageDesc = "Discover and compare Central Universities, IITs, IIMs, AIIMS, State Universities, Autonomous Colleges, NIRF Rankings, and CUET/Entrance Cut-offs on Online Education.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="theme-color" content="#0B0F19">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">

    <!-- Base URL for Clean Routing (/university/, /college/, /institute/) -->
    <?php
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    $scriptDir = str_replace('\\', '/', $scriptDir);
    $appBasePath = rtrim($scriptDir, '/') . '/';
    if ($appBasePath === '//') {
        $appBasePath = '/';
    }
    ?>
    <base href="<?= htmlspecialchars($appBasePath) ?>">

    <!-- Brand Favicons -->
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="apple-touch-icon" href="logo.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Pure CSS Style -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- National Indian Knowledge Header Ribbon -->
    <div class="national-stripe"></div>

    <!-- Top Utility Bar (Black & Orange) -->
    <div class="top-utility-bar">
        <div class="utility-container">
            <div class="utility-left">
                <span class="utility-flag-badge">🇮🇳 Higher &amp; School</span>
                <span class="utility-tagline-desktop" style="font-family: var(--font-hindi); font-weight: 600; color: #E5E7EB;">विश्वविद्यालय, महाविद्यालय एवं विद्यालय मंच — One Education Platform for Every People</span>
                <span class="utility-tagline-mobile" style="font-family: var(--font-hindi); font-weight: 600; color: #E5E7EB;">उच्च एवं विद्यालय मंच</span>
            </div>
            
            <!-- Desktop Utility Links -->
            <div class="utility-links desktop-utility-links">
                <a href="school.php">🏫 CBSE Schools (33K+)</a>
                <a href="syllabus.php">📖 Syllabus</a>
                <a href="government.php">🏛️ UGC &amp; MoE Portals</a>
                <a href="global.php">🌏 NRI Admissions</a>
                <?php if (isset($_SESSION['student_user_id'])): ?>
                    <a href="student-dashboard.php" style="color: #FFA35B; font-weight: 700;">👤 <?= htmlspecialchars(explode(' ', $_SESSION['student_name'])[0]) ?>'s Dashboard</a>
                    <a href="logout.php" style="color: #EF4444; font-weight: 700;">Sign Out</a>
                <?php else: ?>
                    <a href="login.php" style="color: #FFA35B; font-weight: 700;">📱 Student Sign In</a>
                    <a href="register.php" style="color: #60A5FA; font-weight: 700;">Register</a>
                <?php endif; ?>
                <span style="color: #4B5563;">|</span>
                <span id="lang-en-btn" class="lang-toggle-en" style="cursor: pointer; font-weight: 700; color: #FFFFFF;">EN</span>
                <span style="color: #4B5563;">/</span>
                <span id="lang-hi-btn" class="lang-toggle-hi" style="cursor: pointer; font-family: var(--font-hindi); color: #FFA35B;">हिन्दी</span>
            </div>

            <!-- Mobile Utility Quick Bar -->
            <div class="mobile-utility-quick">
                <div class="mobile-lang-switch">
                    <span class="lang-toggle-en" style="cursor: pointer; font-weight: 700; color: #FFFFFF;">EN</span>
                    <span style="color: #6B7280;">/</span>
                    <span class="lang-toggle-hi" style="cursor: pointer; font-family: var(--font-hindi); color: #FFA35B; font-weight: 600;">हिन्दी</span>
                </div>
                <?php if (isset($_SESSION['student_user_id'])): ?>
                    <a href="student-dashboard.php" style="color: #FFA35B; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 4px;">👤 Dashboard</a>
                <?php else: ?>
                    <a href="login.php" style="color: #FFA35B; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 4px;">📱 Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="header-inner">
            <div class="header-top-row">
                <!-- Brand with High-Res Official Logo -->
                <a href="index.php" class="brand-logo" title="Online Education — National Education Platform of India">
                    <img src="logo_full.png" alt="Online Education — विद्या ददाति विनयम्" class="brand-full-logo">
                </a>

                <!-- Desktop Action Links (Visible on >= 992px) -->
                <div class="header-desktop-actions">
                    <a href="school.php" class="btn btn-outline" style="font-size: 0.8rem;">
                        🏫 Schools (33K+)
                    </a>
                    <a href="institution.php" class="btn btn-outline" style="font-size: 0.8rem;">
                        🏛️ Universities (1,505)
                    </a>
                    <a href="college.php" class="btn btn-primary" style="font-size: 0.8rem;">
                        🎓 Colleges (54K+)
                    </a>
                    <a href="institute.php" class="btn btn-outline" style="font-size: 0.8rem;">
                        🏢 Institutes (17K+)
                    </a>
                    <?php if (isset($_SESSION['student_user_id'])): ?>
                        <a href="student-dashboard.php" class="btn btn-outline" style="font-size: 0.8rem; border-color: var(--orange); color: var(--orange);">
                            👤 Account
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline" style="font-size: 0.8rem; border-color: var(--orange); color: var(--orange);">
                            📱 Login / OTP
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Header Actions (Visible on < 992px) -->
                <div class="header-mobile-actions">
                    <?php if (isset($_SESSION['student_user_id'])): ?>
                        <a href="student-dashboard.php" class="mobile-user-pill" title="Student Dashboard">
                            <span class="user-avatar-circle"><?= strtoupper(substr($_SESSION['student_name'], 0, 1)) ?></span>
                            <span class="user-name-abbr"><?= htmlspecialchars(substr(explode(' ', $_SESSION['student_name'])[0], 0, 8)) ?></span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="mobile-signin-btn" title="Sign In">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Sign In</span>
                        </a>
                    <?php endif; ?>

                    <!-- Hamburger Menu Button -->
                    <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Open Navigation Menu" aria-expanded="false" aria-controls="mobileNavDrawer">
                        <svg class="hamburger-icon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Omnibox Search -->
            <form action="search.php" method="GET" class="header-search-form">
                <input 
                    type="text" 
                    name="q" 
                    class="header-search-input" 
                    placeholder="Search schools, universities, colleges, AISHE..." 
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                    autocomplete="off"
                    required
                >
                <button type="submit" class="header-search-button" aria-label="Search">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Selected Sector Navigation Ribbon: Universities & Colleges & Schools -->
        <nav class="nav-ribbon" aria-label="Sector Navigation">
            <div class="ribbon-container">
                <a href="index.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['sub']) ? 'active' : '' ?>">
                    <span>🏠</span> Home
                </a>
                <a href="school.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'school.php' || basename($_SERVER['PHP_SELF']) == 'school-detail.php' ? 'active' : '' ?>">
                    <span>🏫</span> CBSE Schools <span class="ribbon-badge">33K+</span>
                </a>
                <a href="institution.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'institution.php' ? 'active' : '' ?>">
                    <span>🏛️</span> Universities <span class="ribbon-badge">1,505</span>
                </a>
                <a href="college.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'college.php' ? 'active' : '' ?>">
                    <span>🎓</span> Colleges <span class="ribbon-badge">54K+</span>
                </a>
                <a href="institute.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'institute.php' ? 'active' : '' ?>">
                    <span>🏢</span> Institutes <span class="ribbon-badge">17K+</span>
                </a>
                <a href="syllabus.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'syllabus.php' ? 'active' : '' ?>">
                    <span>📖</span> Syllabus <span class="ribbon-badge">CBSE • CUET</span>
                </a>
                <a href="exam.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'exam.php' ? 'active' : '' ?>">
                    <span>🎯</span> Entrance Cutoffs <span class="ribbon-badge">JEE • NEET</span>
                </a>
                <a href="government.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'government.php' ? 'active' : '' ?>">
                    <span>🏛️</span> MoE Portals
                </a>
            </div>
        </nav>
    </header>

    <!-- Mobile Slide-Out Navigation Drawer -->
    <div class="mobile-drawer-overlay" id="mobileDrawerOverlay" aria-hidden="true"></div>
    <aside class="mobile-drawer-panel" id="mobileNavDrawer" role="dialog" aria-modal="true" aria-label="Navigation Menu" aria-hidden="true">
        <div class="drawer-header">
            <a href="index.php" class="drawer-logo" onclick="if(window.closeMobileDrawer) window.closeMobileDrawer();">
                <img src="logo_full.png" alt="Online Education" style="height: 36px; width: auto; object-fit: contain;">
            </a>
            <button type="button" class="drawer-close-btn" id="mobileDrawerClose" aria-label="Close navigation menu">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="drawer-body">
            <!-- Student Profile / Sign-in Box -->
            <div class="drawer-user-card">
                <?php if (isset($_SESSION['student_user_id'])): ?>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--orange-light); border: 1.5px solid var(--orange); color: var(--orange); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 800;">
                            <?= strtoupper(substr($_SESSION['student_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight: 800; color: var(--black-soft); font-size: 0.96rem;"><?= htmlspecialchars($_SESSION['student_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #16A34A; font-weight: 700;">● Verified Student</div>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <a href="student-dashboard.php" class="btn btn-outline" style="font-size: 0.8rem; padding: 8px 10px; justify-content: center; border-color: var(--orange); color: var(--orange);">Dashboard</a>
                        <a href="logout.php" class="btn btn-outline" style="font-size: 0.8rem; padding: 8px 10px; justify-content: center; border-color: #EF4444; color: #EF4444;">Sign Out</a>
                    </div>
                <?php else: ?>
                    <div style="font-size: 0.88rem; font-weight: 800; color: var(--black-soft); margin-bottom: 4px;">Student &amp; Parent Access</div>
                    <p style="font-size: 0.78rem; color: var(--slate-muted); margin-bottom: 12px; line-height: 1.4;">Sign in to track entrance cutoffs, bookmark institutions, and request admission counseling.</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <a href="login.php" class="btn btn-primary" style="font-size: 0.82rem; padding: 8px 10px; justify-content: center;">📱 Sign In</a>
                        <a href="register.php" class="btn btn-outline" style="font-size: 0.82rem; padding: 8px 10px; justify-content: center; border-color: var(--black-soft);">Register</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Major Directories Nav Group -->
            <div class="drawer-nav-group">
                <div class="drawer-nav-heading">National Directories</div>
                <a href="index.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['sub']) ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">🏠</span>
                    <span class="drawer-nav-text">Home Portal</span>
                </a>
                <a href="school.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'school.php' || basename($_SERVER['PHP_SELF']) == 'school-detail.php' ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">🏫</span>
                    <span class="drawer-nav-text">CBSE Schools Directory</span>
                    <span class="drawer-badge">33,233</span>
                </a>
                <a href="institution.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'institution.php' ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">🏛️</span>
                    <span class="drawer-nav-text">Universities (Central / State / IIT)</span>
                    <span class="drawer-badge">1,505</span>
                </a>
                <a href="college.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'college.php' ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">🎓</span>
                    <span class="drawer-nav-text">Affiliated Colleges</span>
                    <span class="drawer-badge">54,351</span>
                </a>
                <a href="institute.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'institute.php' ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">🏢</span>
                    <span class="drawer-nav-text">Standalone Institutes</span>
                    <span class="drawer-badge">17,010</span>
                </a>
            </div>

            <!-- Academics & Admissions Group -->
            <div class="drawer-nav-group">
                <div class="drawer-nav-heading">Syllabus &amp; Entrances</div>
                <a href="syllabus.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'syllabus.php' ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">📖</span>
                    <span class="drawer-nav-text">Official Syllabus (CBSE • CUET • JEE)</span>
                </a>
                <a href="exam.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'exam.php' ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">🎯</span>
                    <span class="drawer-nav-text">Entrance Cutoffs (CUET • JEE • NEET)</span>
                </a>
                <a href="scholarship.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'scholarship.php' ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">📜</span>
                    <span class="drawer-nav-text">National Scholarships (NSP)</span>
                </a>
                <a href="government.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'government.php' ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">🏛️</span>
                    <span class="drawer-nav-text">UGC &amp; MoE Official Portals</span>
                </a>
                <a href="global.php" class="drawer-nav-link <?= basename($_SERVER['PHP_SELF']) == 'global.php' ? 'active' : '' ?>">
                    <span class="drawer-nav-icon">🌏</span>
                    <span class="drawer-nav-text">NRI &amp; Diaspora Admissions</span>
                </a>
            </div>

            <!-- Language & Support -->
            <div class="drawer-nav-group" style="border-bottom: none;">
                <div class="drawer-nav-heading">Preferences &amp; Help</div>
                <div class="drawer-lang-row">
                    <span style="font-size: 0.82rem; font-weight: 600; color: var(--slate-body);">Language / भाषा:</span>
                    <div style="display: inline-flex; gap: 4px; background: #FFFFFF; border: 1.5px solid var(--border-color); padding: 3px 6px; border-radius: 6px;">
                        <button type="button" class="lang-toggle-en" style="border:none; background:none; font-weight:700; font-size:0.8rem; cursor:pointer; color:var(--orange);">English</button>
                        <span style="color:#CBD5E1;">|</span>
                        <button type="button" class="lang-toggle-hi" style="border:none; background:none; font-family:var(--font-hindi); font-weight:700; font-size:0.8rem; cursor:pointer; color:var(--slate-muted);">हिन्दी</button>
                    </div>
                </div>
                <a href="contact.php" class="drawer-nav-link">
                    <span class="drawer-nav-icon">📞</span>
                    <span class="drawer-nav-text">Contact &amp; Student Helpdesk</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Mobile Drawer & Header Inline Responsive CSS (Ensures flawless display across all views) -->
    <style>
    /* Utility Bar Responsive */
    .utility-left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .utility-flag-badge {
        font-size: 0.72rem;
        background: rgba(255,107,0,0.18);
        border: 1px solid rgba(255,107,0,0.4);
        color: #FFA35B;
        padding: 2px 7px;
        border-radius: 4px;
        font-weight: 700;
        white-space: nowrap;
    }
    .utility-tagline-mobile {
        display: none;
    }
    .mobile-utility-quick {
        display: none;
        align-items: center;
        gap: 10px;
    }
    .mobile-lang-switch {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(255,255,255,0.08);
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.72rem;
    }

    /* Header Action Layouts */
    .header-desktop-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .header-mobile-actions {
        display: none;
        align-items: center;
        gap: 8px;
    }

    /* Mobile Buttons */
    .mobile-signin-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.78rem;
        font-weight: 700;
        background: var(--orange-light);
        color: var(--orange-dark);
        border: 1.5px solid var(--orange-border);
        padding: 6px 11px;
        border-radius: 999px;
        text-decoration: none;
        transition: all 0.2s ease;
        touch-action: manipulation;
    }
    .mobile-signin-btn:hover, .mobile-signin-btn:active {
        background: var(--orange);
        color: #FFFFFF;
        border-color: var(--orange);
    }
    .mobile-user-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--orange-light);
        border: 1.5px solid var(--orange);
        padding: 4px 10px 4px 5px;
        border-radius: 999px;
        color: var(--orange-dark);
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
    }
    .user-avatar-circle {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--orange);
        color: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 800;
    }
    .mobile-menu-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: #F8FAFC;
        border: 1.5px solid var(--border-color);
        border-radius: 9px;
        color: var(--black-soft);
        cursor: pointer;
        padding: 0;
        transition: all 0.2s ease;
        touch-action: manipulation;
    }
    .mobile-menu-toggle:hover, .mobile-menu-toggle:active {
        background: var(--orange-light);
        border-color: var(--orange);
        color: var(--orange);
    }

    /* Mobile Drawer */
    .mobile-drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(11, 15, 25, 0.6);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 2000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }
    .mobile-drawer-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .mobile-drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 86%;
        max-width: 350px;
        background: #FFFFFF;
        z-index: 2001;
        box-shadow: -8px 0 28px rgba(0, 0, 0, 0.22);
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    .mobile-drawer-panel.active {
        transform: translateX(0);
    }
    body.mobile-drawer-open {
        overflow: hidden !important;
    }
    .drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        background: #FAFAFA;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .drawer-close-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: #FFFFFF;
        color: var(--slate-body);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        transition: all 0.2s ease;
    }
    .drawer-close-btn:hover {
        background: #FEE2E2;
        color: #DC2626;
        border-color: #FCA5A5;
    }
    .drawer-body {
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding-bottom: 60px;
    }
    .drawer-user-card {
        background: linear-gradient(135deg, #FFF7ED 0%, #FFFFFF 100%);
        border: 1.5px solid var(--orange-border);
        border-radius: var(--radius-md);
        padding: 14px;
        box-shadow: var(--shadow-sm);
    }
    .drawer-nav-group {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 14px;
    }
    .drawer-nav-heading {
        font-size: 0.68rem;
        font-weight: 800;
        color: var(--slate-muted);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 8px;
        padding: 0 4px;
    }
    .drawer-nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 10px;
        border-radius: 8px;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--black-soft);
        text-decoration: none;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .drawer-nav-link:hover, .drawer-nav-link:active, .drawer-nav-link.active {
        background: var(--orange-light);
        color: var(--orange);
    }
    .drawer-nav-icon {
        font-size: 1.15rem;
        line-height: 1;
        flex-shrink: 0;
    }
    .drawer-nav-text {
        flex: 1;
    }
    .drawer-badge {
        font-size: 0.7rem;
        font-weight: 700;
        background: #F1F5F9;
        color: #475569;
        padding: 2px 7px;
        border-radius: 999px;
    }
    .drawer-nav-link.active .drawer-badge, .drawer-nav-link:hover .drawer-badge {
        background: var(--orange);
        color: #FFFFFF;
    }
    .drawer-lang-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        background: #F8FAFC;
        border-radius: 8px;
        margin-bottom: 8px;
    }

    /* Screen Breakpoints */
    @media (max-width: 991px) {
        .header-desktop-actions {
            display: none !important;
        }
        .header-mobile-actions {
            display: flex !important;
        }
        .header-inner {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
            padding: 10px 12px;
        }
        .header-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        .brand-full-logo {
            height: 38px;
            max-width: 175px;
        }
        .header-search-form {
            order: 3;
            max-width: 100%;
            width: 100%;
        }
        .header-search-input {
            font-size: 16px !important; /* Prevents auto zoom in iOS Safari */
            padding: 10px 44px 10px 16px;
            height: 42px;
        }
    }

    @media (max-width: 640px) {
        .desktop-utility-links {
            display: none !important;
        }
        .mobile-utility-quick {
            display: flex !important;
        }
        .utility-tagline-desktop {
            display: none !important;
        }
        .utility-tagline-mobile {
            display: inline !important;
        }
        .top-utility-bar {
            padding: 5px 12px;
            font-size: 0.74rem;
        }
        .utility-container {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            flex-wrap: nowrap;
        }
        .ribbon-container {
            padding: 0 10px;
            gap: 10px;
        }
        .ribbon-item {
            padding: 9px 3px;
            font-size: 0.8rem;
        }
        .ribbon-badge {
            font-size: 0.65rem;
            padding: 1px 5px;
        }
    }
    </style>

    <!-- Mobile Drawer & Language Interactive Logic -->
    <script>
    (function () {
        function initMobileNav() {
            var toggleBtn = document.getElementById('mobileMenuToggle');
            var drawer = document.getElementById('mobileNavDrawer');
            var overlay = document.getElementById('mobileDrawerOverlay');
            var closeBtn = document.getElementById('mobileDrawerClose');

            if (!toggleBtn || !drawer || !overlay) return;

            function openDrawer() {
                drawer.classList.add('active');
                overlay.classList.add('active');
                document.body.classList.add('mobile-drawer-open');
                toggleBtn.setAttribute('aria-expanded', 'true');
                drawer.setAttribute('aria-hidden', 'false');
                overlay.setAttribute('aria-hidden', 'false');
            }

            function closeDrawer() {
                drawer.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('mobile-drawer-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
                drawer.setAttribute('aria-hidden', 'true');
                overlay.setAttribute('aria-hidden', 'true');
            }

            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (drawer.classList.contains('active')) {
                    closeDrawer();
                } else {
                    openDrawer();
                }
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeDrawer();
                });
            }

            overlay.addEventListener('click', function () {
                closeDrawer();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && drawer.classList.contains('active')) {
                    closeDrawer();
                }
            });

            window.closeMobileDrawer = closeDrawer;

            // Language Switcher (English <-> हिन्दी) across all instances
            var enBtns = document.querySelectorAll('.lang-toggle-en, #lang-en-btn');
            var hiBtns = document.querySelectorAll('.lang-toggle-hi, #lang-hi-btn');

            function applyLanguage(lang) {
                localStorage.setItem('oedu_lang', lang);
                if (lang === 'hi') {
                    document.body.classList.add('lang-hindi-mode');
                    enBtns.forEach(function (b) { b.style.color = '#9CA3AF'; b.style.fontWeight = '600'; });
                    hiBtns.forEach(function (b) { b.style.color = '#FFA35B'; b.style.fontWeight = '700'; });
                } else {
                    document.body.classList.remove('lang-hindi-mode');
                    enBtns.forEach(function (b) { b.style.color = '#FFFFFF'; b.style.fontWeight = '700'; });
                    hiBtns.forEach(function (b) { b.style.color = '#FFA35B'; b.style.fontWeight = '600'; });
                }
            }

            enBtns.forEach(function (btn) {
                btn.addEventListener('click', function () { applyLanguage('en'); });
            });
            hiBtns.forEach(function (btn) {
                btn.addEventListener('click', function () { applyLanguage('hi'); });
            });

            var initialLang = localStorage.getItem('oedu_lang') || 'en';
            applyLanguage(initialLang);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMobileNav);
        } else {
            initMobileNav();
        }
    })();
    </script>

    <main class="main-wrapper">
        <?php if (!empty($_GET['success'])): ?>
            <div class="flash-alert">
                <span>✅ <?= htmlspecialchars($_GET['success']) ?></span>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:var(--orange-dark);">×</button>
            </div>
        <?php endif; ?>
        <?php if (!empty($_GET['error'])): ?>
            <div class="flash-alert" style="background:#FEE2E2; border:1px solid #FCA5A5; color:#991B1B;">
                <span>⚠️ <?= htmlspecialchars($_GET['error']) ?></span>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:#991B1B;">×</button>
            </div>
        <?php endif; ?>
