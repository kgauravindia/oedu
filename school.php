<?php
// school.php — National CBSE School Directory of India (.php only)
// Data sourced from official CBSE Affiliation Registry (33,180+ CBSE Schools across India)
require_once __DIR__ . '/includes/db.php';

// 1. Fetch distinct States from `school` table
$statesStmt = $pdo->query("SELECT DISTINCT state FROM `school` WHERE state IS NOT NULL AND state != '' ORDER BY state ASC");
$allStates = $statesStmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Filter Inputs
$selectedState = trim($_GET['state'] ?? '');
$selectedDistrict = trim($_GET['district'] ?? '');
$selectedStatus = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;
$offset = ($page - 1) * $perPage;

// Preload districts if state is selected
$stateDistricts = [];
if ($selectedState) {
    $dstStmt = $pdo->prepare("SELECT DISTINCT district FROM `school` WHERE state = :state AND district IS NOT NULL AND district != '' ORDER BY district ASC");
    $dstStmt->execute([':state' => $selectedState]);
    $stateDistricts = $dstStmt->fetchAll(PDO::FETCH_COLUMN);
}

// 3. Build Query on `school` table
$where = ["1=1"];
$params = [];

if ($selectedState) {
    $where[] = "state = :state";
    $params[':state'] = $selectedState;
}

if ($selectedDistrict) {
    $where[] = "district = :district";
    $params[':district'] = $selectedDistrict;
}

if ($selectedStatus) {
    $where[] = "status = :status";
    $params[':status'] = $selectedStatus;
}

if ($search) {
    $where[] = "(name LIKE :q OR affiliation_no LIKE :q OR school_code LIKE :q OR udise_code LIKE :q OR district LIKE :q OR head_name LIKE :q)";
    $params[':q'] = "%{$search}%";
}

$whereSql = implode(' AND ', $where);

// Count Total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM `school` WHERE {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Fetch Paginated Records
$sql = "SELECT * FROM `school` WHERE {$whereSql} ORDER BY state ASC, district ASC, name ASC LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Distinct statuses for filter
$statuses = ['Senior Secondary', 'Secondary', 'Middle Class'];

$totalSchoolsInIndia = (int)$pdo->query("SELECT COUNT(*) FROM `school`")->fetchColumn();
$totalStatesInIndia = count($allStates);

$pageTitle = 'All CBSE Schools in India — National School Directory | Online Education';
$pageDesc = 'Comprehensive national directory of 33,000+ CBSE affiliated schools across all 36 States & UTs of India. Search by Affiliation No, School Code, District & State.';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 800; color: var(--orange-dark); background: var(--orange-light); border: 1px solid var(--orange-border); padding: 3px 10px; border-radius: 999px; margin-bottom: 6px;">
                🇮🇳 Official CBSE National Directory
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 800; color: #111827; margin-bottom: 4px; line-height: 1.25;">
                National CBSE School Directory of India
            </h1>
            <p style="color: #6B7280; font-size: 0.92rem;">
                Explore all <?= number_format($totalSchoolsInIndia) ?> Central Board of Secondary Education (CBSE) schools across <?= $totalStatesInIndia ?> States &amp; UTs of India.
            </p>
        </div>
        <div>
            <a href="index.php#counseling" class="btn btn-primary" style="font-size: 0.85rem; padding: 9px 18px;">
                School Admission Guidance →
            </a>
        </div>
    </div>
</div>

<!-- Search & Filter Form (Orange, Black, White - Mobile Friendly) -->
<div style="background: #FFFFFF; border: 1.5px solid #111827; border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <form action="school.php" method="GET" class="filter-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 110px; gap: 12px; align-items: flex-end;">
        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">1. State / UT</label>
            <select name="state" onchange="this.form.submit()" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All States &amp; UTs (<?= $totalStatesInIndia ?>)</option>
                <?php foreach ($allStates as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>" <?= $selectedState === $st ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">2. District</label>
            <select name="district" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All Districts <?= $selectedState ? '(' . count($stateDistricts) . ')' : '' ?></option>
                <?php foreach ($stateDistricts as $dst): ?>
                    <option value="<?= htmlspecialchars($dst) ?>" <?= $selectedDistrict === $dst ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dst) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">School Level / Status</label>
            <select name="status" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                <option value="">All Levels</option>
                <?php foreach ($statuses as $sta): ?>
                    <option value="<?= htmlspecialchars($sta) ?>" <?= $selectedStatus === $sta ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sta) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Keyword / Aff. / Code</label>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. 1030404, Delhi, DAV..." style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF;">
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px; font-size: 0.92rem; border-radius: 6px;">Filter 🔍</button>
        </div>
    </form>
</div>

<!-- Active Filter Status -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
    <div style="font-size: 0.9rem; font-weight: 700; color: #111827;">
        Found <strong><?= number_format($totalRecords) ?></strong> CBSE Schools
        <?= $selectedState ? "in " . htmlspecialchars($selectedState) : 'across India' ?>
        <?= $selectedDistrict ? "• District: " . htmlspecialchars($selectedDistrict) : '' ?>
        <?= $selectedStatus ? "• " . htmlspecialchars($selectedStatus) : '' ?>
        (Page <?= $page ?> of <?= max(1, $totalPages) ?>)
    </div>
    <?php if ($selectedState || $selectedDistrict || $selectedStatus || $search): ?>
        <a href="school.php" class="btn btn-outline" style="font-size: 0.78rem; padding: 5px 12px; border-color: #CBD5E1;">
            Clear All Filters ✕
        </a>
    <?php endif; ?>
</div>

<!-- Grid (100% Mobile Responsive) -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; margin-bottom: 36px;">
    <?php if (empty($schools)): ?>
        <div style="grid-column: 1 / -1; background: #FFFFFF; border: 1.5px solid var(--border-color); padding: 48px 16px; border-radius: var(--radius-md); text-align: center;">
            <div style="font-size: 2.4rem; margin-bottom: 8px;">🏫</div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 4px;">No CBSE Schools Found</h3>
            <p style="font-size: 0.88rem; color: #6B7280; margin-bottom: 16px;">Try adjusting your keyword or reset filters to browse all CBSE schools.</p>
            <a href="school.php" class="btn btn-primary">Reset Filters</a>
        </div>
    <?php else: ?>
        <?php foreach ($schools as $s): ?>
            <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: border-color 0.2s, transform 0.15s;">
                <div>
                    <!-- Header -->
                    <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 10px;">
                        <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--orange-light); border: 1px solid var(--orange-border); color: var(--orange-dark); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; font-weight: 800;">
                            🏫
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="font-size: 1.02rem; font-weight: 800; color: #111827; line-height: 1.3; margin-bottom: 2px;">
                                <a href="school/<?= urlencode($s['slug']) ?>"><?= htmlspecialchars($s['name']) ?></a>
                            </h3>
                            <div style="font-size: 0.8rem; color: #6B7280;">
                                📍 <?= htmlspecialchars($s['district']) ?>, <strong><?= htmlspecialchars($s['state']) ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px;">
                        <?php if (!empty($s['affiliation_no'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 800; background: #111827; color: #FFFFFF; padding: 2px 7px; border-radius: 4px;">
                                Aff. No: <?= htmlspecialchars($s['affiliation_no']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($s['school_code'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #F1F5F9; color: #334155; padding: 2px 7px; border-radius: 4px; border: 1px solid #CBD5E1;">
                                Code: <?= htmlspecialchars($s['school_code']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($s['udise_code'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #EFF6FF; color: #1D4ED8; padding: 2px 7px; border-radius: 4px; border: 1px solid #BFDBFE;">
                                UDISE: <?= htmlspecialchars($s['udise_code']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($s['status'])): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: var(--orange-light); color: var(--orange-dark); padding: 2px 7px; border-radius: 4px; border: 1px solid var(--orange-border);">
                                <?= htmlspecialchars($s['status']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($s['head_name'])): ?>
                        <div style="font-size: 0.78rem; color: #475569; margin-bottom: 6px;">
                            <strong>Principal:</strong> <?= htmlspecialchars($s['head_name']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($s['address'])): ?>
                        <div style="font-size: 0.75rem; color: #64748B; line-height: 1.4; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?= htmlspecialchars($s['address']) ?>">
                            <?= htmlspecialchars($s['address']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer / Actions -->
                <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <div>
                        <?php if (!empty($s['website'])): 
                            $web = $s['website'];
                            if (!preg_match("~^(?:f|ht)tps?://~i", $web)) {
                                $web = "http://" . $web;
                            }
                        ?>
                            <a href="<?= htmlspecialchars($web) ?>" target="_blank" rel="noopener noreferrer" style="font-size: 0.78rem; font-weight: 700; color: var(--orange);">
                                Visit Portal ↗
                            </a>
                        <?php else: ?>
                            <span style="font-size: 0.75rem; color: #9CA3AF;">CBSE Affiliated</span>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <a href="school/<?= urlencode($s['slug']) ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.78rem; border-color: #111827; color: #111827;">
                            Details →
                        </a>
                        <a href="school/<?= urlencode($s['slug']) ?>#enquire" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.78rem;">
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
