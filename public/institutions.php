<?php
// institutions.php — National University & College Directory of India (.php only)
// Powered by op_sdb table (36 States & 780 Districts) and university table (1,438 Universities)
require_once __DIR__ . '/includes/db.php';

// 1. Fetch States from op_sdb
$statesStmt = $pdo->query("SELECT DISTINCT state FROM op_sdb WHERE state IS NOT NULL AND state != '' ORDER BY state ASC");
$allStates = $statesStmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Filter Inputs
$selectedState = trim($_GET['state'] ?? '');
$selectedDistrict = trim($_GET['district'] ?? '');
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;
$offset = ($page - 1) * $perPage;

// Preload districts from op_sdb if state is selected
$stateDistricts = [];
if ($selectedState) {
    $dstStmt = $pdo->prepare("SELECT DISTINCT district FROM op_sdb WHERE state = :state AND district IS NOT NULL AND district != '' ORDER BY district ASC");
    $dstStmt->execute([':state' => $selectedState]);
    $stateDistricts = $dstStmt->fetchAll(PDO::FETCH_COLUMN);
}

// 3. Build Query on `university` table
$where = ["status = 'ACTIVE'"];
$params = [];

if ($selectedState) {
    $where[] = "state = :state";
    $params[':state'] = $selectedState;
}

if ($selectedDistrict) {
    $where[] = "district LIKE :district";
    $params[':district'] = "%{$selectedDistrict}%";
}

if ($search) {
    $where[] = "(name LIKE :q OR aishe_code LIKE :q OR district LIKE :q OR state LIKE :q)";
    $params[':q'] = "%{$search}%";
}

$whereSql = implode(' AND ', $where);

// Count Total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM `university` WHERE {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Fetch Paginated Records
$sql = "SELECT * FROM `university` WHERE {$whereSql} ORDER BY state ASC, name ASC LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$universities = $stmt->fetchAll();

// Total count from op_sdb
$totalStatesOpSdb = (int) $pdo->query("SELECT COUNT(DISTINCT state) FROM op_sdb")->fetchColumn();
$totalDistrictsOpSdb = (int) $pdo->query("SELECT COUNT(DISTINCT district) FROM op_sdb")->fetchColumn();

$pageTitle = 'All Universities in India — National University Directory | Oedu.in';
$pageDesc = 'Comprehensive national directory of 1,438+ Universities across all 36 States and 780 Districts of India. Powered by op_sdb and official AISHE data.';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 800; color: var(--orange-dark); background: var(--orange-light); border: 1px solid var(--orange-border); padding: 3px 10px; border-radius: 999px; margin-bottom: 6px;">
                🇮🇳 Official AISHE & op_sdb National Directory
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: #111827; margin-bottom: 4px; line-height: 1.25;">
                National University Directory of India
            </h1>
            <p style="color: #6B7280; font-size: 0.92rem;">
                Explore all 1,438 Universities recognized across <?= $totalStatesOpSdb ?> States & UTs and <?= $totalDistrictsOpSdb ?> Districts of India.
            </p>
        </div>
        <div>
            <a href="index.php#counseling-box" class="btn btn-primary" style="font-size: 0.85rem; padding: 9px 18px;">
                Free Admission Counseling →
            </a>
        </div>
    </div>
</div>

<!-- Simple Filter Form (Orange, Black, White - Mobile Friendly) -->
<div style="background: #FFFFFF; border: 1.5px solid #111827; border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <form action="institutions.php" method="GET" class="filter-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 110px; gap: 12px; align-items: flex-end;">
        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">1. State (op_sdb)</label>
            <select name="state" class="state-select-input" data-district-target="inst-district-select" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All 36 States & UTs</option>
                <?php foreach ($allStates as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>" <?= $selectedState === $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">2. District (op_sdb)</label>
            <select name="district" id="inst-district-select" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All Districts</option>
                <?php foreach ($stateDistricts as $dst): ?>
                    <option value="<?= htmlspecialchars($dst) ?>" <?= $selectedDistrict === $dst ? 'selected' : '' ?>><?= htmlspecialchars($dst) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Keyword / AISHE Code</label>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. U-1159, Delhi, Technical..." style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF;">
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px; font-size: 0.92rem; border-radius: 6px;">Filter 🔍</button>
        </div>
    </form>
</div>

<!-- Active Filter Status -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
    <div style="font-size: 0.9rem; font-weight: 700; color: #111827;">
        Found <strong><?= number_format($totalRecords) ?></strong> Universities
        <?= $selectedState ? "in " . htmlspecialchars($selectedState) : 'across India' ?>
        <?= $selectedDistrict ? "• District: " . htmlspecialchars($selectedDistrict) : '' ?>
        (Page <?= $page ?> of <?= max(1, $totalPages) ?>)
    </div>
    <?php if ($selectedState || $selectedDistrict || $search): ?>
        <a href="institutions.php" class="btn btn-outline" style="font-size: 0.78rem; padding: 5px 12px; border-color: #CBD5E1;">
            Clear All Filters ✕
        </a>
    <?php endif; ?>
</div>

<!-- Grid (100% Mobile Responsive) -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 18px; margin-bottom: 36px;">
    <?php if (empty($universities)): ?>
        <div style="grid-column: 1 / -1; background: #FFFFFF; border: 1.5px solid var(--border-color); padding: 48px 16px; border-radius: var(--radius-md); text-align: center;">
            <div style="font-size: 2.4rem; margin-bottom: 8px;">🏛️</div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 4px;">No Universities Found</h3>
            <p style="font-size: 0.88rem; color: #6B7280; margin-bottom: 16px;">Try adjusting your keyword or reset filters to view all universities in India.</p>
            <a href="institutions.php" class="btn btn-primary">Reset Filters</a>
        </div>
    <?php else: ?>
        <?php foreach ($universities as $u): ?>
            <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: border-color 0.2s, transform 0.15s;">
                <div>
                    <!-- Header -->
                    <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 10px;">
                        <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--orange-light); border: 1px solid var(--orange-border); color: var(--orange-dark); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; font-weight: 800;">
                            🏛️
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 1.02rem; font-weight: 800; color: #111827; line-height: 1.3; margin-bottom: 2px;">
                                <a href="university-detail.php?slug=<?= urlencode($u['slug']) ?>"><?= htmlspecialchars($u['name']) ?></a>
                            </h3>
                            <div style="font-size: 0.8rem; color: #6B7280;">
                                📍 <?= htmlspecialchars($u['district']) ?>, <strong><?= htmlspecialchars($u['state']) ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px;">
                        <span style="font-size: 0.72rem; font-weight: 800; background: #111827; color: #FFFFFF; padding: 2px 7px; border-radius: 4px;">
                            AISHE: <?= htmlspecialchars($u['aishe_code']) ?>
                        </span>
                        <?php if (!empty($u['year_of_establishment'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: var(--orange-light); color: var(--orange-dark); padding: 2px 7px; border-radius: 4px; border: 1px solid var(--orange-border);">
                                Est. <?= htmlspecialchars($u['year_of_establishment']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($u['location'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 600; background: #F1F5F9; color: #475569; padding: 2px 7px; border-radius: 4px;">
                                <?= htmlspecialchars($u['location']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer / Actions -->
                <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <div>
                        <?php if (!empty($u['website'])): 
                            $web = $u['website'];
                            if (!preg_match("~^(?:f|ht)tps?://~i", $web)) {
                                $web = "http://" . $web;
                            }
                        ?>
                            <a href="<?= htmlspecialchars($web) ?>" target="_blank" rel="noopener noreferrer" style="font-size: 0.78rem; font-weight: 700; color: var(--orange);">
                                Visit Portal ↗
                            </a>
                        <?php else: ?>
                            <span style="font-size: 0.75rem; color: #9CA3AF;">Official AISHE</span>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <a href="university-detail.php?slug=<?= urlencode($u['slug']) ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.78rem; border-color: #111827; color: #111827;">
                            Details →
                        </a>
                        <a href="index.php#counseling-box" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.78rem;">
                            Enquire
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
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-outline" style="padding: 6px 14px; font-size: 0.85rem;">← Prev</a>
        <?php endif; ?>

        <span style="font-size: 0.88rem; font-weight: 700; color: #111827; padding: 6px 12px;">
            Page <?= $page ?> of <?= $totalPages ?>
        </span>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.85rem;">Next →</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
