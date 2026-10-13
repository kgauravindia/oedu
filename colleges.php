<?php
// colleges.php — National Affiliated Colleges Directory of India (.php only)
// Powered by op_sdb table (36 States & 780 Districts) and college table (54,351 Colleges)
require_once __DIR__ . '/includes/db.php';

// 1. Fetch States from op_sdb
$statesStmt = $pdo->query("SELECT DISTINCT state FROM op_sdb WHERE state IS NOT NULL AND state != '' ORDER BY state ASC");
$allStates = $statesStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct managements for filter
$mgmtStmt = $pdo->query("SELECT DISTINCT management FROM college WHERE management IS NOT NULL AND management != '' AND management != '-' ORDER BY management ASC");
$allManagements = $mgmtStmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Filter Inputs
$selectedState = trim($_GET['state'] ?? '');
$selectedDistrict = trim($_GET['district'] ?? '');
$selectedMgmt = trim($_GET['management'] ?? '');
$selectedUnivAishe = trim($_GET['univ_aishe'] ?? '');
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

// 3. Build Query on `college` table
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

if ($selectedMgmt) {
    $where[] = "management = :management";
    $params[':management'] = $selectedMgmt;
}

if ($selectedUnivAishe) {
    $where[] = "university_aishe_code = :univ_aishe";
    $params[':univ_aishe'] = $selectedUnivAishe;
}

if ($search) {
    $where[] = "(name LIKE :q OR aishe_code LIKE :q OR university_name LIKE :q OR district LIKE :q OR state LIKE :q)";
    $params[':q'] = "%{$search}%";
}

$whereSql = implode(' AND ', $where);

// Count Total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM `college` WHERE {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Fetch Paginated Records
$sql = "SELECT * FROM `college` WHERE {$whereSql} ORDER BY state ASC, name ASC LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$colleges = $stmt->fetchAll();

// Total count from college table
$totalCollegesOverall = (int) $pdo->query("SELECT COUNT(*) FROM college")->fetchColumn();
$totalStatesOpSdb = (int) $pdo->query("SELECT COUNT(DISTINCT state) FROM op_sdb")->fetchColumn();
$totalDistrictsOpSdb = (int) $pdo->query("SELECT COUNT(DISTINCT district) FROM op_sdb")->fetchColumn();

$pageTitle = 'All Affiliated Colleges in India — National College Directory (54,350+) | Oedu.in';
$pageDesc = "Browse official AISHE directory of {$totalCollegesOverall} Affiliated Colleges across all {$totalStatesOpSdb} States & UTs. Filter by Affiliating University, District, Management, and Location.";
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 800; color: var(--orange-dark); background: var(--orange-light); border: 1px solid var(--orange-border); padding: 3px 10px; border-radius: 999px; margin-bottom: 6px;">
                🇮🇳 Official AISHE National College Directory • <?= number_format($totalCollegesOverall) ?> Colleges
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: #111827; margin-bottom: 4px; line-height: 1.25;">
                Affiliated Colleges Directory of India
            </h1>
            <p style="color: #6B7280; font-size: 0.92rem;">
                Explore all <?= number_format($totalCollegesOverall) ?> affiliated & recognized colleges across <?= $totalStatesOpSdb ?> States & UTs and <?= $totalDistrictsOpSdb ?> Districts with affiliating university details.
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="institutions.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 9px 16px;">
                🏛️ Universities (1,438)
            </a>
            <a href="institutes.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 9px 16px;">
                🏢 Standalone Institutes (17K+)
            </a>
            <a href="index.php#counseling-box" class="btn btn-primary" style="font-size: 0.85rem; padding: 9px 18px;">
                Free Admission Counseling →
            </a>
        </div>
    </div>
</div>

<!-- Quick Category Pills -->
<div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 16px; -webkit-overflow-scrolling: touch;">
    <a href="colleges.php" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= empty($selectedMgmt) && empty($selectedUnivAishe) ? '#111827' : '#E5E7EB' ?>; background: <?= empty($selectedMgmt) && empty($selectedUnivAishe) ? '#111827' : '#FFFFFF' ?>; color: <?= empty($selectedMgmt) && empty($selectedUnivAishe) ? '#FFFFFF' : '#374151' ?>;">
        All Colleges (<?= number_format($totalCollegesOverall) ?>)
    </a>
    <a href="colleges.php?management=State+Government" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedMgmt === 'State Government' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedMgmt === 'State Government' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedMgmt === 'State Government' ? 'var(--orange-dark)' : '#374151' ?>;">
        🏛️ State Government Colleges (6,900+)
    </a>
    <a href="colleges.php?management=Private+Aided+(Government+Aided)" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedMgmt === 'Private Aided (Government Aided)' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedMgmt === 'Private Aided (Government Aided)' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedMgmt === 'Private Aided (Government Aided)' ? 'var(--orange-dark)' : '#374151' ?>;">
        ⭐ Government-Aided Colleges (5,900+)
    </a>
    <a href="colleges.php?management=Private+Un-Aided" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedMgmt === 'Private Un-Aided' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedMgmt === 'Private Un-Aided' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedMgmt === 'Private Un-Aided' ? 'var(--orange-dark)' : '#374151' ?>;">
        🏫 Private Un-Aided (37,500+)
    </a>
    <a href="colleges.php?management=Central+Government" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedMgmt === 'Central Government' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedMgmt === 'Central Government' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedMgmt === 'Central Government' ? 'var(--orange-dark)' : '#374151' ?>;">
        🇮🇳 Central Govt Colleges (400+)
    </a>
</div>

<!-- Filter Form -->
<div style="background: #FFFFFF; border: 1.5px solid #111827; border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <form action="colleges.php" method="GET" class="filter-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)) 110px; gap: 12px; align-items: flex-end;">
        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">1. State</label>
            <select name="state" class="state-select-input" data-district-target="col-district-select" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All 36 States & UTs</option>
                <?php foreach ($allStates as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>" <?= $selectedState === $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">2. District</label>
            <select name="district" id="col-district-select" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All Districts</option>
                <?php foreach ($stateDistricts as $dst): ?>
                    <option value="<?= htmlspecialchars($dst) ?>" <?= $selectedDistrict === $dst ? 'selected' : '' ?>><?= htmlspecialchars($dst) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">3. Management</label>
            <select name="management" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All Managements</option>
                <?php foreach ($allManagements as $m): ?>
                    <option value="<?= htmlspecialchars($m) ?>" <?= $selectedMgmt === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Search / AISHE / University</label>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. C-59634, Ambedkar, Science..." style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF;">
        </div>

        <?php if ($selectedUnivAishe): ?>
            <input type="hidden" name="univ_aishe" value="<?= htmlspecialchars($selectedUnivAishe) ?>">
        <?php endif; ?>

        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px; font-size: 0.92rem; border-radius: 6px;">Filter 🔍</button>
        </div>
    </form>
</div>

<!-- Active Filter Status -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
    <div style="font-size: 0.9rem; font-weight: 700; color: #111827;">
        Found <strong><?= number_format($totalRecords) ?></strong> Affiliated Colleges
        <?= $selectedState ? "in " . htmlspecialchars($selectedState) : 'across India' ?>
        <?= $selectedDistrict ? "• District: " . htmlspecialchars($selectedDistrict) : '' ?>
        <?= $selectedMgmt ? "• " . htmlspecialchars($selectedMgmt) : '' ?>
        <?= $selectedUnivAishe ? "• University AISHE: " . htmlspecialchars($selectedUnivAishe) : '' ?>
        (Page <?= $page ?> of <?= max(1, $totalPages) ?>)
    </div>
    <?php if ($selectedState || $selectedDistrict || $selectedMgmt || $selectedUnivAishe || $search): ?>
        <a href="colleges.php" class="btn btn-outline" style="font-size: 0.78rem; padding: 5px 12px; border-color: #CBD5E1;">
            Clear All Filters ✕
        </a>
    <?php endif; ?>
</div>

<!-- Grid (100% Mobile Responsive) -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; margin-bottom: 36px;">
    <?php if (empty($colleges)): ?>
        <div style="grid-column: 1 / -1; background: #FFFFFF; border: 1.5px solid var(--border-color); padding: 48px 16px; border-radius: var(--radius-md); text-align: center;">
            <div style="font-size: 2.4rem; margin-bottom: 8px;">🎓</div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 4px;">No Colleges Found</h3>
            <p style="font-size: 0.88rem; color: #6B7280; margin-bottom: 16px;">Try adjusting your keyword or reset filters to explore all 54,351 colleges in India.</p>
            <a href="colleges.php" class="btn btn-primary">Reset Filters</a>
        </div>
    <?php else: ?>
        <?php foreach ($colleges as $c): 
            $isGovt = stripos($c['management'] ?? '', 'Government') !== false;
            $mgmtColor = $isGovt ? '#166534' : '#1E40AF';
            $mgmtBg = $isGovt ? '#F0FDF4' : '#EFF6FF';
            $mgmtBorder = $isGovt ? '#BBF7D0' : '#BFDBFE';
        ?>
            <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: border-color 0.2s, transform 0.15s;">
                <div>
                    <!-- Header -->
                    <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 10px;">
                        <div style="width: 44px; height: 44px; border-radius: 8px; background: #FFF7ED; border: 1px solid #FED7AA; color: #C2410C; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; font-weight: 800;">
                            🎓
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 1.02rem; font-weight: 800; color: #111827; line-height: 1.3; margin-bottom: 2px;">
                                <a href="college-detail.php?slug=<?= urlencode($c['slug']) ?>"><?= htmlspecialchars($c['name']) ?></a>
                            </h3>
                            <div style="font-size: 0.8rem; color: #6B7280;">
                                📍 <?= htmlspecialchars($c['district'] ?: 'District N/A') ?>, <strong><?= htmlspecialchars($c['state'] ?: 'India') ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px;">
                        <span style="font-size: 0.72rem; font-weight: 800; background: #111827; color: #FFFFFF; padding: 2px 7px; border-radius: 4px;">
                            AISHE: <?= htmlspecialchars($c['aishe_code']) ?>
                        </span>
                        <?php if (!empty($c['management'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: <?= $mgmtBg ?>; color: <?= $mgmtColor ?>; border: 1px solid <?= $mgmtBorder ?>; padding: 2px 7px; border-radius: 4px;">
                                <?= htmlspecialchars($c['management']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($c['year_of_establishment'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: var(--orange-light); color: var(--orange-dark); padding: 2px 7px; border-radius: 4px; border: 1px solid var(--orange-border);">
                                Est. <?= htmlspecialchars($c['year_of_establishment']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($c['location'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 600; background: #F1F5F9; color: #475569; padding: 2px 7px; border-radius: 4px;">
                                <?= htmlspecialchars($c['location']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Affiliating University Info -->
                    <?php if (!empty($c['university_name'])): ?>
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 10px; margin-bottom: 12px; font-size: 0.78rem;">
                            <div style="color: #64748B; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">Affiliated To University:</div>
                            <div style="color: #0F172A; font-weight: 700; margin-top: 1px; line-height: 1.25;">
                                <?= htmlspecialchars($c['university_name']) ?>
                            </div>
                            <?php if (!empty($c['university_aishe_code'])): ?>
                                <a href="colleges.php?univ_aishe=<?= urlencode($c['university_aishe_code']) ?>" style="display: inline-block; margin-top: 4px; font-size: 0.7rem; color: var(--orange-dark); font-weight: 700;">
                                    View all colleges under (<?= htmlspecialchars($c['university_aishe_code']) ?>) →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer / Actions -->
                <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <div>
                        <?php if (!empty($c['website'])): 
                            $web = $c['website'];
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
                        <a href="college-detail.php?slug=<?= urlencode($c['slug']) ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.78rem; border-color: #111827; color: #111827;">
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
