<?php
// search.php — Online Education Unified Omnibox Search Engine
require_once __DIR__ . '/includes/db.php';

$query = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'all';
$state = trim($_GET['state'] ?? '');

// Preload state list for in-page state filtering
$allStates = [];
try {
    $statesStmt = $pdo->query("SELECT DISTINCT state FROM op_sdb WHERE state IS NOT NULL AND state != '' ORDER BY state ASC");
    $allStates = $statesStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $allStates = [];
}

// Initialize all result lists to prevent undefined variable errors
$resources = [];
$educators = [];
$universitiesList = [];
$collegesList = [];
$institutesList = [];
$examinations = [];
$scholarships = [];

if ($query !== '' || $state !== '' || $type !== 'all') {
    $searchWildcard = "%{$query}%";
    $limit = ($type === 'all') ? 16 : 40;

    // 1. Universities (AISHE Directory)
    if (in_array($type, ['all', 'universities', 'institutions'])) {
        $sql = "SELECT * FROM university WHERE status = 'ACTIVE'";
        $params = [];
        if ($query !== '') {
            $sql .= " AND (name LIKE ? OR aishe_code LIKE ? OR district LIKE ? OR state LIKE ?)";
            $params = array_merge($params, [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
        }
        if ($state !== '') {
            $sql .= " AND state = ?";
            $params[] = $state;
        }
        $sql .= " ORDER BY (CASE WHEN name LIKE ? THEN 1 ELSE 2 END), name ASC LIMIT {$limit}";
        $params[] = $query !== '' ? "{$query}%" : '%';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $universitiesList = $stmt->fetchAll();
    }

    // 2. Colleges (AISHE Directory)
    if (in_array($type, ['all', 'colleges', 'institutions'])) {
        $sql = "SELECT * FROM college WHERE 1=1";
        $params = [];
        if ($query !== '') {
            $sql .= " AND (name LIKE ? OR aishe_code LIKE ? OR university_name LIKE ? OR district LIKE ? OR state LIKE ?)";
            $params = array_merge($params, [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
        }
        if ($state !== '') {
            $sql .= " AND state = ?";
            $params[] = $state;
        }
        $sql .= " ORDER BY (CASE WHEN name LIKE ? THEN 1 ELSE 2 END), name ASC LIMIT {$limit}";
        $params[] = $query !== '' ? "{$query}%" : '%';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $collegesList = $stmt->fetchAll();
    }

    // 3. Standalone Institutes (AISHE Directory)
    if (in_array($type, ['all', 'institutes', 'institutions'])) {
        $sql = "SELECT * FROM institute WHERE 1=1";
        $params = [];
        if ($query !== '') {
            $sql .= " AND (name LIKE ? OR aishe_code LIKE ? OR institute_type LIKE ? OR district LIKE ? OR state LIKE ?)";
            $params = array_merge($params, [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
        }
        if ($state !== '') {
            $sql .= " AND state = ?";
            $params[] = $state;
        }
        $sql .= " ORDER BY (CASE WHEN name LIKE ? THEN 1 ELSE 2 END), name ASC LIMIT {$limit}";
        $params[] = $query !== '' ? "{$query}%" : '%';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $institutesList = $stmt->fetchAll();
    }

    // 4. Entrance Examinations
    if (in_array($type, ['all', 'exams'])) {
        $sql = "SELECT * FROM examination WHERE 1=1";
        $params = [];
        if ($query !== '') {
            $sql .= " AND (title LIKE ? OR code LIKE ? OR category LIKE ? OR conducting_body LIKE ?)";
            $params = array_merge($params, [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
        }
        $sql .= " ORDER BY is_featured DESC, id ASC LIMIT {$limit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $examinations = $stmt->fetchAll();
    }

    // 5. Scholarships
    if (in_array($type, ['all', 'scholarships'])) {
        $sql = "SELECT * FROM scholarship WHERE is_active = 1";
        $params = [];
        if ($query !== '') {
            $sql .= " AND (title LIKE ? OR provider_type LIKE ? OR ministry_or_body LIKE ? OR eligibility LIKE ?)";
            $params = array_merge($params, [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
        }
        if ($state !== '') {
            $sql .= " AND (state = ? OR state IS NULL OR state = '' OR state = 'All India')";
            $params[] = $state;
        }
        $sql .= " ORDER BY id ASC LIMIT {$limit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $scholarships = $stmt->fetchAll();
    }

    // 6. Learning Library & Textbooks
    if (in_array($type, ['all', 'learn'])) {
        $sql = "
            SELECT lr.*, b.code as board_code, s.name as subject_name 
            FROM learning_resource lr 
            LEFT JOIN board b ON lr.board_id = b.id 
            LEFT JOIN subject s ON lr.subject_id = s.id 
            WHERE 1=1
        ";
        $params = [];
        if ($query !== '') {
            $sql .= " AND (lr.title LIKE ? OR lr.title_hi LIKE ? OR lr.class_number LIKE ? OR lr.content_body LIKE ?)";
            $params = array_merge($params, [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
        }
        $sql .= " LIMIT {$limit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resources = $stmt->fetchAll();
    }

    // 7. Verified Tutors & Educators
    if (in_array($type, ['all', 'tutors'])) {
        $sql = "SELECT * FROM educator WHERE 1=1";
        $params = [];
        if ($query !== '') {
            $sql .= " AND (name LIKE ? OR headline LIKE ? OR city LIKE ? OR district LIKE ? OR bio LIKE ?)";
            $params = array_merge($params, [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
        }
        if ($state !== '') {
            $sql .= " AND state = ?";
            $params[] = $state;
        }
        $sql .= " LIMIT {$limit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $educators = $stmt->fetchAll();
    }
}

$totalResults = count($resources) + count($educators) + count($universitiesList) + count($collegesList) + count($institutesList) + count($examinations) + count($scholarships);

$pageTitle = 'Search: ' . ($query ?: ($state ? "State: {$state}" : 'National Higher Education Directory')) . ' — Online Education';
$pageDesc = 'Search and compare universities, affiliated colleges, standalone institutes, entrance exams, and scholarships across all 36 States & UTs of India.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Search Bar & Filters Section -->
<div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
    <form action="search.php" method="GET" style="display: flex; flex-direction: column; gap: 14px;">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 260px; position: relative;">
                <input 
                    type="text" 
                    name="q" 
                    class="form-control" 
                    placeholder="Search universities, colleges, polytechnics, AISHE codes, exams..." 
                    value="<?= htmlspecialchars($query) ?>"
                    style="width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95rem; font-weight: 500;"
                    autofocus
                >
            </div>
            <div style="width: 220px; min-width: 180px;">
                <select name="state" class="form-select" style="width: 100%; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; background: #FFF;">
                    <option value="">All States & UTs (36)</option>
                    <?php foreach ($allStates as $st): ?>
                        <option value="<?= htmlspecialchars($st) ?>" <?= $state === $st ? 'selected' : '' ?>>
                            <?= htmlspecialchars($st) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 0.95rem; font-weight: 700; height: 100%;">
                    🔍 Search
                </button>
            </div>
            <?php if ($query !== '' || $state !== '' || $type !== 'all'): ?>
                <div>
                    <a href="search.php" class="btn btn-outline" style="padding: 12px 16px; font-size: 0.9rem;" title="Reset Search">
                        ✕ Clear
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <div style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-top: 1px solid #F1F5F9; padding-top: 14px;">
        <div style="font-size: 0.9rem; color: var(--slate-body);">
            <?php if ($query !== '' || $state !== ''): ?>
                Found <strong><?= $totalResults ?></strong> result<?= $totalResults === 1 ? '' : 's' ?> for 
                <?php if ($query !== ''): ?><span style="color: var(--orange); font-weight: 700;">"<?= htmlspecialchars($query) ?>"</span><?php endif; ?>
                <?php if ($state !== ''): ?> in <strong style="color: var(--charcoal);"><?= htmlspecialchars($state) ?></strong><?php endif; ?>
            <?php else: ?>
                Showing results across India's premier educational institutions, exams, and scholarship registries.
            <?php endif; ?>
        </div>
        <div style="display: flex; gap: 6px; font-size: 0.78rem; color: var(--slate-muted); flex-wrap: wrap; align-items: center;">
            <span>Popular:</span>
            <a href="search.php?q=Delhi+University" style="color: var(--charcoal); text-decoration: underline;">Delhi University</a> •
            <a href="search.php?q=IIT" style="color: var(--charcoal); text-decoration: underline;">IITs</a> •
            <a href="search.php?q=NEET" style="color: var(--charcoal); text-decoration: underline;">NEET</a> •
            <a href="search.php?q=JEE" style="color: var(--charcoal); text-decoration: underline;">JEE</a> •
            <a href="search.php?q=Polytechnic" style="color: var(--charcoal); text-decoration: underline;">Polytechnic</a>
        </div>
    </div>
</div>

<!-- Type Filter Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 6px; border-bottom: 1px solid var(--border-color);">
    <a href="search.php?q=<?= urlencode($query) ?>&state=<?= urlencode($state) ?>&type=all" class="btn <?= $type === 'all' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
        All (<?= $totalResults ?>)
    </a>
    <a href="search.php?q=<?= urlencode($query) ?>&state=<?= urlencode($state) ?>&type=universities" class="btn <?= $type === 'universities' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
        🏛️ Universities (<?= count($universitiesList) ?>)
    </a>
    <a href="search.php?q=<?= urlencode($query) ?>&state=<?= urlencode($state) ?>&type=colleges" class="btn <?= $type === 'colleges' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
        🎓 Colleges (<?= count($collegesList) ?>)
    </a>
    <a href="search.php?q=<?= urlencode($query) ?>&state=<?= urlencode($state) ?>&type=institutes" class="btn <?= $type === 'institutes' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
        🏢 Standalone Institutes (<?= count($institutesList) ?>)
    </a>
    <a href="search.php?q=<?= urlencode($query) ?>&state=<?= urlencode($state) ?>&type=exams" class="btn <?= $type === 'exams' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
        🎯 Entrance Exams (<?= count($examinations) ?>)
    </a>
    <a href="search.php?q=<?= urlencode($query) ?>&state=<?= urlencode($state) ?>&type=scholarships" class="btn <?= $type === 'scholarships' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
        💰 Scholarships (<?= count($scholarships) ?>)
    </a>
    <?php if (!empty($resources) || $type === 'learn'): ?>
        <a href="search.php?q=<?= urlencode($query) ?>&state=<?= urlencode($state) ?>&type=learn" class="btn <?= $type === 'learn' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
            📚 Library (<?= count($resources) ?>)
        </a>
    <?php endif; ?>
    <?php if (!empty($educators) || $type === 'tutors'): ?>
        <a href="search.php?q=<?= urlencode($query) ?>&state=<?= urlencode($state) ?>&type=tutors" class="btn <?= $type === 'tutors' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
            👨‍🏫 Educators (<?= count($educators) ?>)
        </a>
    <?php endif; ?>
</div>

<?php if ($totalResults === 0): ?>
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 48px; text-align: center; margin-bottom: 36px;">
        <div style="font-size: 3rem; margin-bottom: 12px;">🔍</div>
        <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--charcoal); margin-bottom: 8px;">No matching results found</h3>
        <p style="color: var(--slate-muted); font-size: 0.9rem; max-width: 520px; margin: 0 auto 24px;">
            We couldn't find any match for your query. Try searching by institution name, city, district, exam code, or AISHE code.
        </p>
        <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 24px;">
            <a href="search.php?q=University" class="btn btn-outline" style="font-size: 0.85rem;">🏛️ Explore Universities</a>
            <a href="search.php?q=Medical" class="btn btn-outline" style="font-size: 0.85rem;">🩺 Medical Colleges</a>
            <a href="search.php?q=Engineering" class="btn btn-outline" style="font-size: 0.85rem;">⚙️ Engineering Colleges</a>
            <a href="exam.php" class="btn btn-outline" style="font-size: 0.85rem;">🎯 All Exams</a>
            <a href="scholarship.php" class="btn btn-outline" style="font-size: 0.85rem;">💰 Scholarships</a>
        </div>
        <a href="index.php" class="btn btn-primary">Return to Homepage</a>
    </div>
<?php endif; ?>

<!-- 1. Universities (National UGC / AISHE Directory) -->
<?php if (!empty($universitiesList)): ?>
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0;">
                🏛️ Universities (<?= count($universitiesList) ?>)
            </h3>
            <a href="institution.php" style="font-size: 0.85rem; font-weight: 700; color: var(--orange);">View National Directory (1,505) →</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php foreach ($universitiesList as $univ): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm); transition: transform 0.2s ease;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.72rem; font-weight: 800; background: #FFF7ED; color: var(--orange); border: 1px solid #FFEDD5; padding: 2px 8px; border-radius: 4px;">
                                AISHE: <?= htmlspecialchars($univ['aishe_code']) ?>
                            </span>
                            <?php if (!empty($univ['year_of_establishment'])): ?>
                                <span style="font-size: 0.72rem; color: var(--slate-muted); font-weight: 600;">
                                    Est. <?= htmlspecialchars($univ['year_of_establishment']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h4 style="font-size: 1rem; font-weight: 700; color: var(--charcoal); line-height: 1.35; margin-bottom: 6px;">
                            <a href="university/<?= urlencode($univ['slug']) ?>" style="color: var(--charcoal); text-decoration: none;">
                                <?= htmlspecialchars($univ['name']) ?>
                            </a>
                        </h4>
                        <div style="font-size: 0.82rem; color: var(--slate-muted); margin-bottom: 12px; display: flex; gap: 8px; align-items: center;">
                            <span>📍 <?= htmlspecialchars($univ['district'] ?: 'Headquarters') ?>, <?= htmlspecialchars($univ['state']) ?></span>
                            <?php if (!empty($univ['location'])): ?>
                                <span>• <?= htmlspecialchars($univ['location']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; border-top: 1px solid #F3F4F6; padding-top: 12px;">
                        <a href="university/<?= urlencode($univ['slug']) ?>" class="btn btn-primary" style="flex: 1; text-align: center; font-size: 0.82rem; padding: 7px 12px;">
                            University Profile →
                        </a>
                        <?php if (!empty($univ['website']) && $univ['website'] !== '-'): ?>
                            <?php 
                                $webUrl = $univ['website'];
                                if (!preg_match("~^(?:f|ht)tps?://~i", $webUrl)) $webUrl = "http://" . $webUrl;
                            ?>
                            <a href="<?= htmlspecialchars($webUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.82rem; padding: 7px 12px;" title="Official Website">
                                🌐 Web
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 2. Colleges (AISHE Directory) -->
<?php if (!empty($collegesList)): ?>
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0;">
                🎓 Affiliated Colleges (<?= count($collegesList) ?>)
            </h3>
            <a href="college.php" style="font-size: 0.85rem; font-weight: 700; color: var(--orange);">View All 54,351 Colleges →</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php foreach ($collegesList as $colItem): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.72rem; font-weight: 800; background: #111827; color: #FFF; padding: 2px 7px; border-radius: 4px;">
                                AISHE: <?= htmlspecialchars($colItem['aishe_code']) ?>
                            </span>
                            <?php if (!empty($colItem['management'])): ?>
                                <span style="font-size: 0.72rem; color: #1E40AF; background: #EFF6FF; border: 1px solid #BFDBFE; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                    <?= htmlspecialchars($colItem['management']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h4 style="font-size: 1rem; font-weight: 700; color: var(--charcoal); line-height: 1.35; margin-bottom: 6px;">
                            <a href="college/<?= urlencode($colItem['slug']) ?>" style="color: var(--charcoal); text-decoration: none;">
                                <?= htmlspecialchars($colItem['name']) ?>
                            </a>
                        </h4>
                        <div style="font-size: 0.82rem; color: var(--slate-muted); margin-bottom: 8px;">
                            📍 <?= htmlspecialchars($colItem['district'] ?: 'District N/A') ?>, <?= htmlspecialchars($colItem['state']) ?>
                        </div>
                        <?php if (!empty($colItem['university_name'])): ?>
                            <div style="font-size: 0.74rem; color: #64748B; background: #F8FAFC; padding: 4px 8px; border-radius: 4px; margin-bottom: 12px; border: 1px solid #E2E8F0;">
                                Affiliated to: <strong><?= htmlspecialchars($colItem['university_name']) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; border-top: 1px solid #F3F4F6; padding-top: 12px;">
                        <a href="college/<?= urlencode($colItem['slug']) ?>" class="btn btn-primary" style="flex: 1; text-align: center; font-size: 0.82rem; padding: 7px 12px;">
                            College Profile →
                        </a>
                        <?php if (!empty($colItem['website'])): 
                            $cWeb = $colItem['website'];
                            if (!preg_match("~^(?:f|ht)tps?://~i", $cWeb)) $cWeb = "http://" . $cWeb;
                        ?>
                            <a href="<?= htmlspecialchars($cWeb) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.82rem; padding: 7px 12px;" title="Official Website">
                                🌐 Web
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 3. Standalone Institutes (AISHE Directory) -->
<?php if (!empty($institutesList)): ?>
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0;">
                🏢 Standalone Institutes (<?= count($institutesList) ?>)
            </h3>
            <a href="institute.php" style="font-size: 0.85rem; font-weight: 700; color: var(--orange);">View All 17,010 Institutes →</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php foreach ($institutesList as $insItem): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.72rem; font-weight: 800; background: #111827; color: #FFF; padding: 2px 7px; border-radius: 4px;">
                                AISHE: <?= htmlspecialchars($insItem['aishe_code']) ?>
                            </span>
                            <?php if (!empty($insItem['institute_type'])): ?>
                                <span style="font-size: 0.72rem; color: var(--orange-dark); background: var(--orange-light); border: 1px solid var(--orange-border); font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                    <?= htmlspecialchars($insItem['institute_type']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h4 style="font-size: 1rem; font-weight: 700; color: var(--charcoal); line-height: 1.35; margin-bottom: 6px;">
                            <a href="institute/<?= urlencode($insItem['slug']) ?>" style="color: var(--charcoal); text-decoration: none;">
                                <?= htmlspecialchars($insItem['name']) ?>
                            </a>
                        </h4>
                        <div style="font-size: 0.82rem; color: var(--slate-muted); margin-bottom: 12px;">
                            📍 <?= htmlspecialchars($insItem['district'] ?: 'District N/A') ?>, <?= htmlspecialchars($insItem['state']) ?>
                            <?php if (!empty($insItem['management'])): ?>
                                • <?= htmlspecialchars($insItem['management']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; border-top: 1px solid #F3F4F6; padding-top: 12px;">
                        <a href="institute/<?= urlencode($insItem['slug']) ?>" class="btn btn-primary" style="flex: 1; text-align: center; font-size: 0.82rem; padding: 7px 12px;">
                            Institute Profile →
                        </a>
                        <a href="institute.php?type=<?= urlencode($insItem['institute_type'] ?? '') ?>" class="btn btn-outline" style="font-size: 0.82rem; padding: 7px 12px;">
                            Similar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 4. Entrance Examinations -->
<?php if (!empty($examinations)): ?>
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0;">
                🎯 Entrance Examinations (<?= count($examinations) ?>)
            </h3>
            <a href="exam.php" style="font-size: 0.85rem; font-weight: 700; color: var(--orange);">View Exam Calendar →</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php foreach ($examinations as $exam): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.72rem; font-weight: 700; background: #EEF2FF; color: #4338CA; border: 1px solid #C7D2FE; padding: 2px 7px; border-radius: 4px;">
                                <?= htmlspecialchars($exam['category']) ?>
                            </span>
                            <?php if (!empty($exam['conducting_body'])): ?>
                                <span style="font-size: 0.72rem; font-weight: 700; background: #F3F4F6; color: #374151; padding: 2px 7px; border-radius: 4px;">
                                    <?= htmlspecialchars($exam['conducting_body']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--charcoal); line-height: 1.35; margin-bottom: 6px;">
                            <?= htmlspecialchars($exam['title']) ?>
                            <?php if (!empty($exam['code'])): ?>
                                <span style="font-size: 0.85rem; color: var(--orange); font-weight: 700;">(<?= htmlspecialchars($exam['code']) ?>)</span>
                            <?php endif; ?>
                        </h4>
                        <?php if (!empty($exam['exam_date'])): ?>
                            <div style="font-size: 0.82rem; color: #166534; font-weight: 600; margin-bottom: 8px;">
                                📅 Exam Schedule: <?= htmlspecialchars($exam['exam_date']) ?>
                            </div>
                        <?php endif; ?>
                        <p style="font-size: 0.82rem; color: var(--slate-muted); line-height: 1.5; margin-bottom: 12px;">
                            <?= htmlspecialchars(mb_substr($exam['syllabus_summary'] ?? $exam['eligibility'] ?? '', 0, 110)) ?>...
                        </p>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; border-top: 1px solid #F3F4F6; padding-top: 12px;">
                        <a href="exam.php?category=<?= urlencode($exam['category']) ?>" class="btn btn-primary" style="flex: 1; text-align: center; font-size: 0.82rem; padding: 7px 12px;">
                            Exam Details →
                        </a>
                        <?php if (!empty($exam['official_url'])): ?>
                            <a href="<?= htmlspecialchars($exam['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.82rem; padding: 7px 12px;" title="Official Exam Portal">
                                🌐 Official
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 5. Scholarships -->
<?php if (!empty($scholarships)): ?>
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0;">
                💰 National & State Scholarships (<?= count($scholarships) ?>)
            </h3>
            <a href="scholarship.php" style="font-size: 0.85rem; font-weight: 700; color: var(--orange);">View All Scholarships →</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php foreach ($scholarships as $sch): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.72rem; font-weight: 700; background: #DEF7EC; color: #03543F; padding: 2px 7px; border-radius: 4px;">
                                <?= htmlspecialchars($sch['provider_type']) ?>
                            </span>
                            <span style="font-size: 0.72rem; color: #B45309; font-weight: 700;">
                                Deadline: <?= htmlspecialchars($sch['application_deadline'] ?? 'Annual') ?>
                            </span>
                        </div>
                        <h4 style="font-size: 1rem; font-weight: 800; color: var(--charcoal); line-height: 1.35; margin-bottom: 6px;">
                            <?= htmlspecialchars($sch['title']) ?>
                        </h4>
                        <?php if (!empty($sch['ministry_or_body'])): ?>
                            <div style="font-size: 0.8rem; color: #4B5563; margin-bottom: 6px;">
                                🏛️ <?= htmlspecialchars($sch['ministry_or_body']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($sch['benefit_amount'])): ?>
                            <div style="font-size: 0.82rem; color: #047857; font-weight: 700; margin-bottom: 8px;">
                                💵 Benefit: <?= htmlspecialchars($sch['benefit_amount']) ?>
                            </div>
                        <?php endif; ?>
                        <p style="font-size: 0.82rem; color: var(--slate-muted); line-height: 1.45; margin-bottom: 12px;">
                            <?= htmlspecialchars(mb_substr($sch['eligibility'] ?? '', 0, 110)) ?>...
                        </p>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; border-top: 1px solid #F3F4F6; padding-top: 12px;">
                        <a href="scholarship.php" class="btn btn-primary" style="flex: 1; text-align: center; font-size: 0.82rem; padding: 7px 12px;">
                            Eligibility & Apply →
                        </a>
                        <?php if (!empty($sch['official_portal_url'])): ?>
                            <a href="<?= htmlspecialchars($sch['official_portal_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.82rem; padding: 7px 12px;" title="Official Government Portal">
                                🌐 Portal
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 6. Learning Resources & Library -->
<?php if (!empty($resources)): ?>
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0;">
                📚 Learning Library & Books (<?= count($resources) ?>)
            </h3>
            <a href="learn.php" style="font-size: 0.85rem; font-weight: 700; color: var(--orange);">View National Library →</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
            <?php foreach ($resources as $res): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                    <div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:0.75rem; background:var(--primary-light); color:var(--primary); font-weight:700; padding:2px 6px; border-radius:4px;">
                                <?= htmlspecialchars($res['class_number']) ?> • <?= htmlspecialchars($res['subject_name'] ?? 'Core') ?>
                            </span>
                            <?php if ($res['is_official']): ?>
                                <span class="official-badge" style="font-size: 0.72rem;">🏛️ NCERT</span>
                            <?php endif; ?>
                        </div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 6px;">
                            <a href="resource-detail.php?slug=<?= urlencode($res['slug']) ?>" style="color: var(--charcoal); text-decoration: none;">
                                <?= htmlspecialchars($res['title']) ?>
                            </a>
                        </h4>
                        <p style="font-size: 0.8rem; color: var(--slate-muted); line-height: 1.4; margin-bottom: 12px;">
                            <?= htmlspecialchars(mb_substr($res['content_body'] ?? '', 0, 95)) ?>...
                        </p>
                    </div>
                    <a href="resource-detail.php?slug=<?= urlencode($res['slug']) ?>" class="btn btn-outline" style="font-size: 0.82rem; text-align: center; padding: 7px 12px;">
                        View Notes & Textbook →
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 7. Verified Educators -->
<?php if (!empty($educators)): ?>
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0;">
                👨‍🏫 Verified Tutors & Educators (<?= count($educators) ?>)
            </h3>
            <a href="tutor.php" style="font-size: 0.85rem; font-weight: 700; color: var(--orange);">Find More Educators →</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php foreach ($educators as $tutor): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; gap: 12px; box-shadow: var(--shadow-sm);">
                    <div style="width: 48px; height: 48px; border-radius: 10px; background: #FEF3C7; color: #92400E; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; flex-shrink: 0;">
                        👨‍🏫
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: var(--charcoal); font-size: 0.95rem;">
                            <a href="tutor-detail.php?slug=<?= urlencode($tutor['slug']) ?>" style="color: var(--charcoal); text-decoration: none;">
                                <?= htmlspecialchars($tutor['name']) ?>
                            </a>
                            <span class="verified-badge" style="font-size: 0.72rem;">✓ Verified</span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--slate-muted); margin: 3px 0;">
                            📍 <?= htmlspecialchars($tutor['city'] ?: $tutor['district']) ?>, <?= htmlspecialchars($tutor['state']) ?> • <?= htmlspecialchars($tutor['qualification']) ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--slate-body); margin-bottom: 8px;">
                            <?= htmlspecialchars(mb_substr($tutor['headline'] ?? '', 0, 75)) ?>
                        </div>
                        <a href="tutor-detail.php?slug=<?= urlencode($tutor['slug']) ?>" class="btn btn-outline" style="padding: 4px 10px; font-size: 0.78rem;">
                            Contact Educator →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
