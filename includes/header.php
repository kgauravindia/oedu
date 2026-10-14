<?php
// includes/header.php - Modular Header for Online Education (.php only)
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
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 0.72rem; background: rgba(255,107,0,0.18); border: 1px solid rgba(255,107,0,0.4); color: #FFA35B; padding: 2px 7px; border-radius: 4px; font-weight: 700;">🇮🇳 Higher Education</span>
                <span style="font-family: var(--font-hindi); font-weight: 600; color: #E5E7EB;">विश्वविद्यालय एवं महाविद्यालय मंच — One Education Platform for Every People</span>
            </div>
            <div class="utility-links">
                <a href="government.php">🏛️ UGC & MoE Portals</a>
                <a href="global.php">🌏 NRI Admissions</a>
                <a href="https://www.facebook.com/oeduin" target="_blank" rel="noopener noreferrer" style="display:inline-flex; align-items:center; gap:4px;"><svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg> Facebook</a>
                <a href="#child-safety">🛡️ Trust & Safety</a>
                <span style="color: #4B5563;">|</span>
                <span id="lang-en-btn" style="cursor: pointer; font-weight: 700; color: #FFFFFF;">EN</span>
                <span style="color: #4B5563;">/</span>
                <span id="lang-hi-btn" style="cursor: pointer; font-family: var(--font-hindi); color: #FFA35B;">हिन्दी</span>
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

                <!-- Quick Action Links -->
                <div class="header-actions">
                    <a href="institution.php" class="btn btn-outline" style="font-size: 0.8rem;">
                        🏛️ Universities (1,505)
                    </a>
                    <a href="college.php" class="btn btn-primary" style="font-size: 0.8rem;">
                        🎓 Colleges (54K+)
                    </a>
                    <a href="institute.php" class="btn btn-outline" style="font-size: 0.8rem;">
                        🏢 Institutes (17K+)
                    </a>
                </div>
            </div>

            <!-- Omnibox Search -->
            <form action="search.php" method="GET" class="header-search-form">
                <input 
                    type="text" 
                    name="q" 
                    class="header-search-input" 
                    placeholder="Search universities, colleges, polytechnics, AISHE codes (DU, IIT, C-59634, S-1802)..." 
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

        <!-- Selected Sector Navigation Ribbon: Universities & Colleges -->
        <nav class="nav-ribbon" aria-label="Sector Navigation">
            <div class="ribbon-container">
                <a href="index.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['sub']) ? 'active' : '' ?>">
                    🏠 Home
                </a>
                <a href="institution.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'institution.php' ? 'active' : '' ?>">
                    🏛️ Universities <span class="ribbon-badge">1,505 Directory</span>
                </a>
                <a href="college.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'college.php' ? 'active' : '' ?>">
                    🎓 Affiliated Colleges <span class="ribbon-badge">54,351 Directory</span>
                </a>
                <a href="institute.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'institute.php' ? 'active' : '' ?>">
                    🏢 Standalone Institutes <span class="ribbon-badge">17,010 Directory</span>
                </a>
                <a href="exam.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'exam.php' ? 'active' : '' ?>">
                    🎯 Entrance Cutoffs <span class="ribbon-badge">CUET • JEE • NEET</span>
                </a>
                <a href="government.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'government.php' ? 'active' : '' ?>">
                    🏛️ MoE Portals
                </a>
            </div>
        </nav>
    </header>

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
