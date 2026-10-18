<?php
// law-college.php — National Law Colleges & Legal Institutions Directory of India (.php only)
// Sourced from Bar Council of India (BCI) Statutory Law Directory (3,170+ Law Colleges & 5,470+ Approved Programs)
require_once __DIR__ . '/includes/db.php';

$allStates = [];
$allUniversities = [];
$lawColleges = [];
$totalRecords = 0;
$totalPages = 1;
$totalCollegesOverall = 3172;
$totalProgramsOverall = 5470;
$totalStatesOverall = 31;

// 1. Safe Schema Auto-Sync
try {
    $colCheck = $pdo->query("SHOW COLUMNS FROM `law_college` LIKE 'slug'");
    if ($colCheck->rowCount() === 0) {
        $sqlFile = __DIR__ . '/law_college.sql';
        if (file_exists($sqlFile)) {
            $pdo->exec(file_get_contents($sqlFile));
        }
    }
} catch (Throwable $e) {}

// 2. Fetch States & Universities
try {
    $statesStmt = $pdo->query("SELECT DISTINCT state FROM `law_college` WHERE state IS NOT NULL AND state != '' AND status = 'ACTIVE' ORDER BY state ASC");
    $allStates = $statesStmt->fetchAll(PDO::FETCH_COLUMN);

    $univStmt = $pdo->query("SELECT DISTINCT affiliating_university FROM `law_college` WHERE affiliating_university IS NOT NULL AND affiliating_university != '' AND status = 'ACTIVE' ORDER BY affiliating_university ASC LIMIT 250");
    $allUniversities = $univStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {}

// 3. Filter Inputs
$selectedState = trim($_GET['state'] ?? '');
$selectedUniv = trim($_GET['university'] ?? '');
$selectedCourse = trim($_GET['course'] ?? '');
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;
$offset = ($page - 1) * $perPage;

// 4. Build Query Conditions
try {
    $where = ["status = 'ACTIVE'"];
    $params = [];

    if ($selectedState) {
        $where[] = "state = :state";
        $params[':state'] = $selectedState;
    }

    if ($selectedUniv) {
        $where[] = "affiliating_university = :univ";
        $params[':univ'] = $selectedUniv;
    }

    if ($selectedCourse) {
        if ($selectedCourse === '3-llb') {
            $where[] = "(course_name LIKE '%3%year%' OR course_name LIKE '%3%yr%' OR course_name LIKE '%three%year%')";
        } elseif ($selectedCourse === '5-ballb') {
            $where[] = "(course_name LIKE '%BA%LL%' OR course_name LIKE '%B.A%LL%')";
        } elseif ($selectedCourse === '5-bballb') {
            $where[] = "(course_name LIKE '%BBA%LL%' OR course_name LIKE '%B.B.A%LL%')";
        } elseif ($selectedCourse === '5-bcomllb') {
            $where[] = "(course_name LIKE '%B.Com%LL%' OR course_name LIKE '%BCom%LL%')";
        } elseif ($selectedCourse === 'llm') {
            $where[] = "(course_name LIKE '%LL.M%' OR course_name LIKE '%LLM%')";
        } else {
            $where[] = "course_name LIKE :course_kw";
            $params[':course_kw'] = "%{$selectedCourse}%";
        }
    }

    if ($search) {
        $where[] = "(name LIKE :q OR affiliating_university LIKE :q OR state LIKE :q OR course_name LIKE :q)";
        $params[':q'] = "%{$search}%";
    }

    $whereSql = implode(' AND ', $where);

    // Count Total Distinct Colleges
    $countSql = "SELECT COUNT(DISTINCT slug) FROM `law_college` WHERE {$whereSql}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($totalRecords / $perPage));

    // Fetch Paginated Distinct Colleges
    $sql = "SELECT 
        slug,
        name,
        state,
        affiliating_university,
        MIN(NULLIF(e_year, '')) as est_year,
        COUNT(*) as total_courses,
        GROUP_CONCAT(course_name SEPARATOR ' || ') as courses_list,
        GROUP_CONCAT(approval_till SEPARATOR ' || ') as approvals_list,
        MAX(approval_till) as latest_approval
    FROM `law_college` 
    WHERE {$whereSql} 
    GROUP BY slug, name, state, affiliating_university 
    ORDER BY state ASC, name ASC 
    LIMIT {$perPage} OFFSET {$offset}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $lawColleges = $stmt->fetchAll();

    // Overall Stats for banners
    $totalCollegesOverall = (int)$pdo->query("SELECT COUNT(DISTINCT slug) FROM `law_college` WHERE status = 'ACTIVE'")->fetchColumn();
    $totalProgramsOverall = (int)$pdo->query("SELECT COUNT(*) FROM `law_college` WHERE status = 'ACTIVE'")->fetchColumn();
    $totalStatesOverall = (int)$pdo->query("SELECT COUNT(DISTINCT state) FROM `law_college` WHERE state IS NOT NULL AND state != '' AND status = 'ACTIVE'")->fetchColumn();
} catch (Throwable $e) {}

$pageTitle = 'Law Colleges in India — BCI Approved Law College Directory (3,170+) | Online Education';
$pageDesc = "Explore {$totalCollegesOverall}+ Bar Council of India (BCI) recognized Law Colleges across {$totalStatesOverall} States & UTs. Compare 3-Year LL.B, 5-Year Integrated BA LL.B, BBA LL.B courses, intake seats, and affiliation.";
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 800; color: #1E3A8A; background: #DBEAFE; border: 1px solid #BFDBFE; padding: 3px 10px; border-radius: 999px; margin-bottom: 6px;">
                ⚖️ Bar Council of India (BCI) Recognized Registry • <?= number_format($totalCollegesOverall) ?> Law Colleges • <?= number_format($totalProgramsOverall) ?> Approved Programs
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: #111827; margin-bottom: 4px; line-height: 1.25;">
                Law Colleges Directory of India
            </h1>
            <p style="color: #6B7280; font-size: 0.92rem;">
                Browse <?= number_format($totalCollegesOverall) ?> Bar Council of India (BCI) accredited Law Colleges, University Law Faculties, and National Law Institutes across <?= $totalStatesOverall ?> States &amp; UTs offering 3-Year LL.B and 5-Year Integrated Law Programs.
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="exam.php?category=law" class="btn btn-outline" style="font-size: 0.85rem; padding: 9px 16px;">
                🎯 CLAT / AILET Cutoffs
            </a>
            <a href="institution.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 9px 16px;">
                🏛️ Universities (1,505)
            </a>
            <a href="index.php#counseling-box" class="btn btn-primary" style="font-size: 0.85rem; padding: 9px 18px;">
                Legal Career Counseling →
            </a>
        </div>
    </div>
</div>

<!-- Quick Course Category Pills -->
<div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 16px; -webkit-overflow-scrolling: touch;">
    <a href="law-college.php" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= empty($selectedCourse) ? '#111827' : '#E5E7EB' ?>; background: <?= empty($selectedCourse) ? '#111827' : '#FFFFFF' ?>; color: <?= empty($selectedCourse) ? '#FFFFFF' : '#374151' ?>;">
        All Law Colleges (<?= number_format($totalCollegesOverall) ?>)
    </a>
    <a href="law-college.php?course=3-llb" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedCourse === '3-llb' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedCourse === '3-llb' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedCourse === '3-llb' ? 'var(--orange-dark)' : '#374151' ?>;">
        ⚖️ 3-Year LL.B Colleges (2,750+)
    </a>
    <a href="law-college.php?course=5-ballb" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedCourse === '5-ballb' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedCourse === '5-ballb' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedCourse === '5-ballb' ? 'var(--orange-dark)' : '#374151' ?>;">
        🎓 5-Year Integrated BA LL.B (2,370+)
    </a>
    <a href="law-college.php?course=5-bballb" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedCourse === '5-bballb' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedCourse === '5-bballb' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedCourse === '5-bballb' ? 'var(--orange-dark)' : '#374151' ?>;">
        💼 5-Year BBA LL.B Honors
    </a>
    <a href="law-college.php?course=5-bcomllb" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedCourse === '5-bcomllb' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedCourse === '5-bcomllb' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedCourse === '5-bcomllb' ? 'var(--orange-dark)' : '#374151' ?>;">
        📊 5-Year B.Com LL.B (230+)
    </a>
</div>

<!-- Filter Form -->
<div style="background: #FFFFFF; border: 1.5px solid #111827; border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <form action="law-college.php" method="GET" class="filter-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)) 110px; gap: 12px; align-items: flex-end;">
        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">1. State / UT</label>
            <select name="state" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All <?= $totalStatesOverall ?> States &amp; UTs</option>
                <?php foreach ($allStates as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>" <?= $selectedState === $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">2. Law Program Type</label>
            <select name="course" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All Law Programs</option>
                <option value="3-llb" <?= $selectedCourse === '3-llb' ? 'selected' : '' ?>>3-Year LL.B Course</option>
                <option value="5-ballb" <?= $selectedCourse === '5-ballb' ? 'selected' : '' ?>>5-Year Integrated BA LL.B</option>
                <option value="5-bballb" <?= $selectedCourse === '5-bballb' ? 'selected' : '' ?>>5-Year Integrated BBA LL.B</option>
                <option value="5-bcomllb" <?= $selectedCourse === '5-bcomllb' ? 'selected' : '' ?>>5-Year Integrated B.Com LL.B</option>
                <option value="llm" <?= $selectedCourse === 'llm' ? 'selected' : '' ?>>LL.M (Master of Laws)</option>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">3. Affiliating University</label>
            <select name="university" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All Universities</option>
                <?php foreach ($allUniversities as $u): ?>
                    <option value="<?= htmlspecialchars($u) ?>" <?= $selectedUniv === $u ? 'selected' : '' ?>><?= htmlspecialchars(substr($u, 0, 48)) ?><?= strlen($u) > 48 ? '...' : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Search Law College / City</label>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. Government Law, Amity, Lucknow, NLSIU..." style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF;">
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px; font-size: 0.92rem; border-radius: 6px;">Filter 🔍</button>
        </div>
    </form>
</div>

<!-- Active Filter Status -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
    <div style="font-size: 0.9rem; font-weight: 700; color: #111827;">
        Found <strong><?= number_format($totalRecords) ?></strong> Law Colleges
        <?= $selectedState ? "in " . htmlspecialchars($selectedState) : 'across India' ?>
        <?= $selectedCourse ? "• Program: " . htmlspecialchars($selectedCourse) : '' ?>
        <?= $selectedUniv ? "• University: " . htmlspecialchars(substr($selectedUniv, 0, 35)) . '...' : '' ?>
        (Page <?= $page ?> of <?= max(1, $totalPages) ?>)
    </div>
    <?php if ($selectedState || $selectedCourse || $selectedUniv || $search): ?>
        <a href="law-college.php" class="btn btn-outline" style="font-size: 0.78rem; padding: 5px 12px; border-color: #CBD5E1;">
            Clear All Filters ✕
        </a>
    <?php endif; ?>
</div>

<!-- Grid (100% Mobile Responsive) -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; margin-bottom: 36px;">
    <?php if (empty($lawColleges)): ?>
        <div style="grid-column: 1 / -1; background: #FFFFFF; border: 1.5px solid var(--border-color); padding: 48px 16px; border-radius: var(--radius-md); text-align: center;">
            <div style="font-size: 2.4rem; margin-bottom: 8px;">⚖️</div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 4px;">No Law Colleges Found</h3>
            <p style="font-size: 0.88rem; color: #6B7280; margin-bottom: 16px;">Try adjusting your keyword or reset filters to explore all <?= number_format($totalCollegesOverall) ?> Bar Council recognized law colleges.</p>
            <a href="law-college.php" class="btn btn-primary">Reset Filters</a>
        </div>
    <?php else: ?>
        <?php foreach ($lawColleges as $c): 
            $courses = array_filter(explode(' || ', $c['courses_list'] ?? ''));
        ?>
            <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: border-color 0.2s, transform 0.15s;">
                <div>
                    <!-- Header -->
                    <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 10px;">
                        <div style="width: 44px; height: 44px; border-radius: 8px; background: #EFF6FF; border: 1px solid #BFDBFE; color: #1D4ED8; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; font-weight: 800;">
                            ⚖️
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 1.02rem; font-weight: 800; color: #111827; line-height: 1.3; margin-bottom: 2px;">
                                <a href="law-college/<?= urlencode($c['slug']) ?>" style="color: inherit; text-decoration: none;"><?= htmlspecialchars($c['name']) ?></a>
                            </h3>
                            <div style="font-size: 0.8rem; color: #6B7280;">
                                📍 <strong><?= htmlspecialchars($c['state'] ?: 'India') ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px;">
                        <span style="font-size: 0.72rem; font-weight: 800; background: #1E3A8A; color: #FFFFFF; padding: 2px 7px; border-radius: 4px;">
                            BCI Approved
                        </span>
                        <span style="font-size: 0.72rem; font-weight: 700; background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; padding: 2px 7px; border-radius: 4px;">
                            <?= $c['total_courses'] ?> Approved Program<?= $c['total_courses'] > 1 ? 's' : '' ?>
                        </span>
                        <?php if (!empty($c['est_year'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: var(--orange-light); color: var(--orange-dark); padding: 2px 7px; border-radius: 4px; border: 1px solid var(--orange-border);">
                                Est. <?= htmlspecialchars($c['est_year']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($c['latest_approval'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 600; background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0; padding: 2px 7px; border-radius: 4px;">
                                Validity: <?= htmlspecialchars($c['latest_approval']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Affiliating University Info -->
                    <?php if (!empty($c['affiliating_university'])): ?>
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 10px; margin-bottom: 12px; font-size: 0.78rem;">
                            <div style="color: #64748B; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">Affiliating University:</div>
                            <div style="color: #0F172A; font-weight: 700; margin-top: 1px; line-height: 1.25;">
                                <?= htmlspecialchars($c['affiliating_university']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Approved Programs Preview -->
                    <div style="margin-bottom: 14px;">
                        <div style="font-size: 0.72rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 5px;">Approved Law Courses:</div>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <?php 
                            $displayLimit = 3;
                            $shown = 0;
                            foreach ($courses as $crs): 
                                if ($shown >= $displayLimit) break;
                                $shown++;
                            ?>
                                <div style="font-size: 0.78rem; color: #334155; background: #F1F5F9; padding: 4px 8px; border-radius: 4px; display: flex; align-items: center; gap: 6px;">
                                    <span style="color: var(--orange); font-weight: 800;">•</span>
                                    <span style="font-weight: 600;"><?= htmlspecialchars($crs) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($courses) > $displayLimit): ?>
                                <div style="font-size: 0.74rem; color: #64748B; font-weight: 700; padding-left: 6px;">
                                    +<?= count($courses) - $displayLimit ?> more approved law course(s)...
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer / Actions -->
                <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <div>
                        <span style="font-size: 0.74rem; color: #64748B; font-weight: 600;">BCI Code Recognized</span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <a href="law-college/<?= urlencode($c['slug']) ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.78rem; border-color: #111827; color: #111827;">
                            View Courses →
                        </a>
                        <a href="index.php#counseling-box" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.78rem;">
                            Counseling
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination (Mobile Friendly) -->
<?php if ($totalPages > 1): ?>
    <div style="display: flex; justify-content: center; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 40px;">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" class="btn btn-outline" style="padding: 6px 10px; font-size: 0.82rem;">« First</a>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">← Prev</a>
        <?php endif; ?>

        <span style="font-size: 0.88rem; font-weight: 700; color: #111827; padding: 6px 12px; background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 6px;">
            Page <?= number_format($page) ?> of <?= number_format($totalPages) ?>
        </span>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;">Next →</a>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>" class="btn btn-outline" style="padding: 6px 10px; font-size: 0.82rem;">Last »</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
