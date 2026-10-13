<?php
// institutes.php — National Standalone Institutions Directory of India (.php only)
// Powered by op_sdb table (36 States & 780 Districts) and institute table (17,010 Standalone Institutes)
require_once __DIR__ . '/includes/db.php';

// 1. Fetch States from op_sdb
$statesStmt = $pdo->query("SELECT DISTINCT state FROM op_sdb WHERE state IS NOT NULL AND state != '' ORDER BY state ASC");
$allStates = $statesStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct institute types
$typesStmt = $pdo->query("SELECT DISTINCT institute_type FROM institute WHERE institute_type IS NOT NULL AND institute_type != '' ORDER BY institute_type ASC");
$allTypes = $typesStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct managements
$mgmtStmt = $pdo->query("SELECT DISTINCT management FROM institute WHERE management IS NOT NULL AND management != '' ORDER BY management ASC");
$allManagements = $mgmtStmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Filter Inputs
$selectedState = trim($_GET['state'] ?? '');
$selectedDistrict = trim($_GET['district'] ?? '');
$selectedType = trim($_GET['type'] ?? '');
$selectedMgmt = trim($_GET['management'] ?? '');
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

// 3. Build Query on `institute` table
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

if ($selectedType) {
    $where[] = "institute_type = :type";
    $params[':type'] = $selectedType;
}

if ($selectedMgmt) {
    $where[] = "management = :management";
    $params[':management'] = $selectedMgmt;
}

if ($search) {
    $where[] = "(name LIKE :q OR aishe_code LIKE :q OR district LIKE :q OR state LIKE :q OR institute_type LIKE :q)";
    $params[':q'] = "%{$search}%";
}

$whereSql = implode(' AND ', $where);

// Count Total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM `institute` WHERE {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Fetch Paginated Records
$sql = "SELECT * FROM `institute` WHERE {$whereSql} ORDER BY state ASC, name ASC LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$institutes = $stmt->fetchAll();

// Total count from institute table
$totalInstitutesOverall = (int) $pdo->query("SELECT COUNT(*) FROM institute")->fetchColumn();
$totalStatesOpSdb = (int) $pdo->query("SELECT COUNT(DISTINCT state) FROM op_sdb")->fetchColumn();
$totalDistrictsOpSdb = (int) $pdo->query("SELECT COUNT(DISTINCT district) FROM op_sdb")->fetchColumn();

// Icon mapping helper
function getInstituteIcon($type) {
    if (stripos($type, 'polytechnic') !== false || stripos($type, 'technical') !== false) return '🛠️';
    if (stripos($type, 'nursing') !== false) return '🩺';
    if (stripos($type, 'teacher') !== false) return '📖';
    if (stripos($type, 'paramedical') !== false) return '🧪';
    if (stripos($type, 'pgdm') !== false) return '💼';
    if (stripos($type, 'pharmacy') !== false) return '💊';
    if (stripos($type, 'hotel') !== false) return '🏨';
    return '🏢';
}

$pageTitle = 'All Standalone Institutes in India — Polytechnics, Nursing & Teacher Training (17,010+) | Oedu.in';
$pageDesc = "Official AISHE directory of {$totalInstitutesOverall} Standalone Institutions across India. Explore Polytechnic colleges, Nursing diploma institutes, Teacher Training, Paramedical and PGDM institutions.";
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 800; color: var(--orange-dark); background: var(--orange-light); border: 1px solid var(--orange-border); padding: 3px 10px; border-radius: 999px; margin-bottom: 6px;">
                🇮🇳 Official AISHE Standalone Institutions Directory • <?= number_format($totalInstitutesOverall) ?> Institutes
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: #111827; margin-bottom: 4px; line-height: 1.25;">
                Standalone Institutes Directory of India
            </h1>
            <p style="color: #6B7280; font-size: 0.92rem;">
                Explore all <?= number_format($totalInstitutesOverall) ?> Standalone Institutions (Polytechnics, Nursing Institutes, Teacher Training, Paramedicals, PGDM) across <?= $totalStatesOpSdb ?> States & UTs.
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="institutions.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 9px 16px;">
                🏛️ Universities (1,438)
            </a>
            <a href="colleges.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 9px 16px;">
                🎓 Colleges (54K+)
            </a>
            <a href="index.php#counseling-box" class="btn btn-primary" style="font-size: 0.85rem; padding: 9px 18px;">
                Free Admission Counseling →
            </a>
        </div>
    </div>
</div>

<!-- Quick Category Pills -->
<div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 16px; -webkit-overflow-scrolling: touch;">
    <a href="institutes.php" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= empty($selectedType) ? '#111827' : '#E5E7EB' ?>; background: <?= empty($selectedType) ? '#111827' : '#FFFFFF' ?>; color: <?= empty($selectedType) ? '#FFFFFF' : '#374151' ?>;">
        All Categories (<?= number_format($totalInstitutesOverall) ?>)
    </a>
    <a href="institutes.php?type=Technical%2FPolytechnic" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedType === 'Technical/Polytechnic' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedType === 'Technical/Polytechnic' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedType === 'Technical/Polytechnic' ? 'var(--orange-dark)' : '#374151' ?>;">
        🛠️ Polytechnics (5,361)
    </a>
    <a href="institutes.php?type=Nursing+(Diploma)+Institute" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedType === 'Nursing (Diploma) Institute' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedType === 'Nursing (Diploma) Institute' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedType === 'Nursing (Diploma) Institute' ? 'var(--orange-dark)' : '#374151' ?>;">
        🩺 Nursing Institutes (4,877)
    </a>
    <a href="institutes.php?type=Teacher+Training+Institute" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedType === 'Teacher Training Institute' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedType === 'Teacher Training Institute' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedType === 'Teacher Training Institute' ? 'var(--orange-dark)' : '#374151' ?>;">
        📖 Teacher Training (3,927)
    </a>
    <a href="institutes.php?type=Paramedical+Institute" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedType === 'Paramedical Institute' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedType === 'Paramedical Institute' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedType === 'Paramedical Institute' ? 'var(--orange-dark)' : '#374151' ?>;">
        🧪 Paramedical (1,750)
    </a>
    <a href="institutes.php?type=PGDM+Institute" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedType === 'PGDM Institute' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedType === 'PGDM Institute' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedType === 'PGDM Institute' ? 'var(--orange-dark)' : '#374151' ?>;">
        💼 PGDM Business (354)
    </a>
    <a href="institutes.php?type=Pharmacy+Institution" style="white-space: nowrap; font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 999px; border: 1.5px solid <?= $selectedType === 'Pharmacy Institution' ? 'var(--orange)' : '#E5E7EB' ?>; background: <?= $selectedType === 'Pharmacy Institution' ? 'var(--orange-light)' : '#FFFFFF' ?>; color: <?= $selectedType === 'Pharmacy Institution' ? 'var(--orange-dark)' : '#374151' ?>;">
        💊 Pharmacy (306)
    </a>
</div>

<!-- Filter Form -->
<div style="background: #FFFFFF; border: 1.5px solid #111827; border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <form action="institutes.php" method="GET" class="filter-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)) 110px; gap: 12px; align-items: flex-end;">
        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">1. State</label>
            <select name="state" class="state-select-input" data-district-target="inst-district-select-box" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All 36 States & UTs</option>
                <?php foreach ($allStates as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>" <?= $selectedState === $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">2. District</label>
            <select name="district" id="inst-district-select-box" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All Districts</option>
                <?php foreach ($stateDistricts as $dst): ?>
                    <option value="<?= htmlspecialchars($dst) ?>" <?= $selectedDistrict === $dst ? 'selected' : '' ?>><?= htmlspecialchars($dst) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">3. Specialization / Type</label>
            <select name="type" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All Standalone Types</option>
                <?php foreach ($allTypes as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= $selectedType === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Search Keyword / AISHE</label>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. S-1802, Polytechnic, Pune..." style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF;">
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px; font-size: 0.92rem; border-radius: 6px;">Filter 🔍</button>
        </div>
    </form>
</div>

<!-- Active Filter Status -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
    <div style="font-size: 0.9rem; font-weight: 700; color: #111827;">
        Found <strong><?= number_format($totalRecords) ?></strong> Standalone Institutes
        <?= $selectedState ? "in " . htmlspecialchars($selectedState) : 'across India' ?>
        <?= $selectedDistrict ? "• District: " . htmlspecialchars($selectedDistrict) : '' ?>
        <?= $selectedType ? "• " . htmlspecialchars($selectedType) : '' ?>
        (Page <?= $page ?> of <?= max(1, $totalPages) ?>)
    </div>
    <?php if ($selectedState || $selectedDistrict || $selectedType || $selectedMgmt || $search): ?>
        <a href="institutes.php" class="btn btn-outline" style="font-size: 0.78rem; padding: 5px 12px; border-color: #CBD5E1;">
            Clear All Filters ✕
        </a>
    <?php endif; ?>
</div>

<!-- Grid (100% Mobile Responsive) -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; margin-bottom: 36px;">
    <?php if (empty($institutes)): ?>
        <div style="grid-column: 1 / -1; background: #FFFFFF; border: 1.5px solid var(--border-color); padding: 48px 16px; border-radius: var(--radius-md); text-align: center;">
            <div style="font-size: 2.4rem; margin-bottom: 8px;">🏢</div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 4px;">No Institutes Found</h3>
            <p style="font-size: 0.88rem; color: #6B7280; margin-bottom: 16px;">Try adjusting your keyword or reset filters to view all 17,010 standalone institutes in India.</p>
            <a href="institutes.php" class="btn btn-primary">Reset Filters</a>
        </div>
    <?php else: ?>
        <?php foreach ($institutes as $inst): 
            $icon = getInstituteIcon($inst['institute_type'] ?? '');
            $isGovt = stripos($inst['management'] ?? '', 'Government') !== false;
            $mgmtColor = $isGovt ? '#166534' : '#1E40AF';
            $mgmtBg = $isGovt ? '#F0FDF4' : '#EFF6FF';
            $mgmtBorder = $isGovt ? '#BBF7D0' : '#BFDBFE';
        ?>
            <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: border-color 0.2s, transform 0.15s;">
                <div>
                    <!-- Header -->
                    <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 10px;">
                        <div style="width: 44px; height: 44px; border-radius: 8px; background: #F8FAFC; border: 1px solid #CBD5E1; color: #0F172A; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; font-weight: 800;">
                            <?= $icon ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 1.02rem; font-weight: 800; color: #111827; line-height: 1.3; margin-bottom: 2px;">
                                <a href="institute-detail.php?slug=<?= urlencode($inst['slug']) ?>"><?= htmlspecialchars($inst['name']) ?></a>
                            </h3>
                            <div style="font-size: 0.8rem; color: #6B7280;">
                                📍 <?= htmlspecialchars($inst['district'] ?: 'District N/A') ?>, <strong><?= htmlspecialchars($inst['state'] ?: 'India') ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px;">
                        <span style="font-size: 0.72rem; font-weight: 800; background: #111827; color: #FFFFFF; padding: 2px 7px; border-radius: 4px;">
                            AISHE: <?= htmlspecialchars($inst['aishe_code']) ?>
                        </span>
                        <?php if (!empty($inst['institute_type'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 800; background: var(--orange-light); color: var(--orange-dark); border: 1px solid var(--orange-border); padding: 2px 7px; border-radius: 4px;">
                                <?= htmlspecialchars($inst['institute_type']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($inst['management'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: <?= $mgmtBg ?>; color: <?= $mgmtColor ?>; border: 1px solid <?= $mgmtBorder ?>; padding: 2px 7px; border-radius: 4px;">
                                <?= htmlspecialchars($inst['management']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($inst['year_of_establishment'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #F1F5F9; color: #475569; padding: 2px 7px; border-radius: 4px;">
                                Est. <?= htmlspecialchars($inst['year_of_establishment']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($inst['location'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 600; background: #F8FAFC; color: #64748B; padding: 2px 7px; border-radius: 4px;">
                                <?= htmlspecialchars($inst['location']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer / Actions -->
                <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <div>
                        <span style="font-size: 0.75rem; color: #166534; font-weight: 700;">
                            ✓ AISHE Registered
                        </span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <a href="institute-detail.php?slug=<?= urlencode($inst['slug']) ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.78rem; border-color: #111827; color: #111827;">
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
