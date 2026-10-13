<?php
// includes/header.php - Modular Header for Oedu.in (.php only)
if (!isset($pageTitle)) {
    $pageTitle = 'National University & College Directory of India | Oedu.in — Higher Education Portal';
}
if (!isset($pageDesc)) {
    $pageDesc = "Discover and compare Central Universities, IITs, IIMs, AIIMS, State Universities, Autonomous Colleges, NIRF Rankings, and CUET/Entrance Cut-offs on Oedu.in.";
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
                <a href="index.php" class="brand-logo" title="Oedu.in — National Education Platform of India">
                    <img src="logo_full.png" alt="Oedu.in — विद्या ददाति विनयम्" class="brand-full-logo">
                </a>

                <!-- Quick Action Links -->
                <div class="header-actions">
                    <a href="institutions.php" class="btn btn-outline" style="font-size: 0.8rem;">
                        🏛️ All (<?= (int)$pdo->query("SELECT COUNT(*) FROM institutions WHERE type IN ('university', 'college')")->fetchColumn() ?>)
                    </a>
                    <a href="institutions.php?type=university" class="btn btn-primary" style="font-size: 0.8rem;">
                        🎓 Universities
                    </a>
                </div>
            </div>

            <!-- Omnibox Search -->
            <form action="search.php" method="GET" class="header-search-form">
                <input 
                    type="text" 
                    name="q" 
                    class="header-search-input" 
                    placeholder="Search universities, colleges, degrees (DU, IIT, MBBS, B.Tech, JNU)..." 
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
                <a href="institutions.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'institutions.php' && empty($_GET['subtype']) ? 'active' : '' ?>">
                    🏛️ University & College Directory <span class="ribbon-badge">Live Sector</span>
                </a>
                <a href="institutions.php?subtype=central_university" class="ribbon-item <?= ($_GET['subtype'] ?? '') == 'central_university' ? 'active' : '' ?>">
                    🏛️ Central Universities <span class="ribbon-badge">DU • BHU • JNU</span>
                </a>
                <a href="institutions.php?subtype=institute_of_national_importance" class="ribbon-item <?= ($_GET['subtype'] ?? '') == 'institute_of_national_importance' ? 'active' : '' ?>">
                    ⚡ National Institutes <span class="ribbon-badge">IITs • AIIMS • IISc</span>
                </a>
                <a href="institutions.php?subtype=state_university" class="ribbon-item <?= ($_GET['subtype'] ?? '') == 'state_university' ? 'active' : '' ?>">
                    🏢 State Universities <span class="ribbon-badge">Pune • Jadavpur</span>
                </a>
                <a href="institutions.php?type=college" class="ribbon-item <?= ($_GET['type'] ?? '') == 'college' ? 'active' : '' ?>">
                    🎓 Top Ranked Colleges <span class="ribbon-badge">NIRF #1</span>
                </a>
                <a href="exams.php" class="ribbon-item <?= basename($_SERVER['PHP_SELF']) == 'exams.php' ? 'active' : '' ?>">
                    🎯 Entrance Cutoffs <span class="ribbon-badge">CUET • JEE • NEET</span>
                </a>
                <span class="ribbon-item" style="opacity: 0.65; cursor: default;">
                    📚 School & Tutor Sectors <span class="ribbon-badge-soon">Phase 2 Soon</span>
                </span>
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
