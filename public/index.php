<?php
// index.php — Oedu.in National University & College Portal of India (.php only)
// Simple, Easy to Understand, High Contrast (White, Black, Orange)
// Powered by op_sdb table (36 States & 780 Districts of India)
require_once __DIR__ . '/includes/db.php';

// 1. Fetch States from op_sdb table
$statesStmt = $pdo->query("SELECT DISTINCT state FROM op_sdb WHERE state IS NOT NULL AND state != '' ORDER BY state ASC");
$allStates = $statesStmt->fetchAll(PDO::FETCH_COLUMN);

// 2. National Stats
$totalStatesCount = (int) $pdo->query("SELECT COUNT(DISTINCT state) FROM op_sdb")->fetchColumn();
$totalDistrictsCount = (int) $pdo->query("SELECT COUNT(DISTINCT district) FROM op_sdb")->fetchColumn();
$totalUniversities = (int) $pdo->query("SELECT COUNT(*) FROM institutions WHERE type = 'university'")->fetchColumn();
$totalColleges = (int) $pdo->query("SELECT COUNT(*) FROM institutions WHERE type = 'college'")->fetchColumn();

// 3. User Filter Inputs
$selectedState = trim($_GET['state'] ?? '');
$selectedDistrict = trim($_GET['district'] ?? '');
$selectedCategory = trim($_GET['category'] ?? '');
$searchKeyword = trim($_GET['q'] ?? '');

// Preload districts from op_sdb if a state is selected
$stateDistricts = [];
if ($selectedState) {
    $distStmt = $pdo->prepare("SELECT DISTINCT district FROM op_sdb WHERE state = :state AND district IS NOT NULL AND district != '' ORDER BY district ASC");
    $distStmt->execute([':state' => $selectedState]);
    $stateDistricts = $distStmt->fetchAll(PDO::FETCH_COLUMN);
}

// 4. Query Institutions
$sql = "SELECT * FROM institutions WHERE 1=1";
$params = [];

if ($selectedState) {
    $sql .= " AND state = :state";
    $params[':state'] = $selectedState;
}

if ($selectedDistrict) {
    $sql .= " AND (district LIKE :district OR city LIKE :district)";
    $params[':district'] = "%{$selectedDistrict}%";
}

if ($selectedCategory === 'central') {
    $sql .= " AND subtype LIKE '%Central%'";
} elseif ($selectedCategory === 'national') {
    $sql .= " AND subtype LIKE '%National Importance%'";
} elseif ($selectedCategory === 'state') {
    $sql .= " AND subtype LIKE '%State%'";
} elseif ($selectedCategory === 'college') {
    $sql .= " AND type = 'college'";
} elseif ($selectedCategory === 'deemed') {
    $sql .= " AND (subtype LIKE '%Deemed%' OR affiliation LIKE '%Deemed%')";
} else {
    $sql .= " AND type IN ('university', 'college')";
}

if ($searchKeyword) {
    $sql .= " AND (name LIKE :q OR city LIKE :q OR state LIKE :q OR affiliation LIKE :q OR subtype LIKE :q)";
    $params[':q'] = "%{$searchKeyword}%";
}

$sql .= " ORDER BY is_verified DESC, rating DESC, reviews_count DESC LIMIT 12";
$instStmt = $pdo->prepare($sql);
$instStmt->execute($params);
$institutions = $instStmt->fetchAll();

// 5. State-wise University Counts for Quick Directory
$stateCountStmt = $pdo->query("SELECT state, COUNT(*) as c FROM institutions WHERE type IN ('university', 'college') GROUP BY state");
$stateCounts = $stateCountStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Oedu.in — National University & College Portal of India | One Education Platform for Every People';
$pageDesc = 'Simple & official national directory of Central Universities, IITs, AIIMS, State Universities, and Colleges across all 36 States & 780 Districts of India.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Simple, High-Contrast National Portal Hero (White, Black, Orange) -->
<section style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px 20px; margin-bottom: 28px; box-shadow: var(--shadow-sm); text-align: center;">
    <div style="display: inline-flex; align-items: center; gap: 8px; background: var(--orange-light); border: 1.5px solid var(--orange-border); color: var(--orange-dark); font-size: 0.8rem; font-weight: 800; padding: 4px 14px; border-radius: 999px; margin-bottom: 14px;">
        <span>🇮🇳 राष्ट्रीय विश्वविद्यालय एवं महाविद्यालय मंच</span>
        <span>•</span>
        <span>National Portal of India</span>
    </div>

    <h1 style="font-size: 2.3rem; font-weight: 800; color: #111827; letter-spacing: -0.6px; line-height: 1.2; margin-bottom: 8px;">
        National University & College Portal
    </h1>
    <div style="font-family: var(--font-hindi); font-size: 1.25rem; font-weight: 700; color: var(--orange); margin-bottom: 12px;">
        भारत के सभी राज्यों एवं ज़िलों के विश्वविद्यालय — One Education Platform for Every People
    </div>
    <p style="font-size: 0.96rem; color: #4B5563; max-width: 760px; margin: 0 auto 24px; line-height: 1.55;">
        Easy directory for students and parents. Find Central Universities, IITs, AIIMS, State Universities, and Colleges with verified fees, NIRF rank, NAAC grades, and direct campus links.
    </p>

    <!-- National Statistics Bar (Powered by op_sdb) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; max-width: 820px; margin: 0 auto 28px; padding: 12px; background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
        <div style="padding: 6px;">
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--orange);"><?= $totalStatesCount ?></div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #111827;">States & UTs</div>
            <div style="font-size: 0.68rem; color: #6B7280;">(op_sdb data)</div>
        </div>
        <div style="padding: 6px;">
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--orange);"><?= $totalDistrictsCount ?></div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #111827;">Districts</div>
            <div style="font-size: 0.68rem; color: #6B7280;">(All India Covered)</div>
        </div>
        <div style="padding: 6px;">
            <div style="font-size: 1.5rem; font-weight: 800; color: #111827;"><?= $totalUniversities ?></div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #111827;">Universities</div>
            <div style="font-size: 0.68rem; color: #6B7280;">Central & State</div>
        </div>
        <div style="padding: 6px;">
            <div style="font-size: 1.5rem; font-weight: 800; color: #111827;"><?= $totalColleges ?></div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #111827;">Top Colleges</div>
            <div style="font-size: 0.68rem; color: #6B7280;">Autonomous & Deemed</div>
        </div>
    </div>

    <!-- 3-STEP EASY FINDER FORM (Using op_sdb Table) -->
    <div style="background: #FFFFFF; border: 2px solid #111827; border-radius: var(--radius-lg); padding: 20px; max-width: 960px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.06); text-align: left;">
        <div style="font-size: 0.85rem; font-weight: 800; color: #111827; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <span>🔍 3-Step Simple Finder (Select State & District from official directory):</span>
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--orange-dark); background: var(--orange-light); padding: 2px 8px; border-radius: 4px;">Powered by op_sdb</span>
        </div>

        <form action="index.php" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 120px; gap: 12px; align-items: flex-end;" class="filter-form-grid">
            <!-- Step 1: State from op_sdb -->
            <div>
                <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">1. Select State (op_sdb)</label>
                <select name="state" class="state-select-input" data-district-target="district-select" style="width: 100%; height: 44px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                    <option value="">All 36 States & UTs</option>
                    <?php foreach ($allStates as $st): ?>
                        <option value="<?= htmlspecialchars($st) ?>" <?= $selectedState === $st ? 'selected' : '' ?>>
                            <?= htmlspecialchars($st) ?> <?= isset($stateCounts[$st]) ? "({$stateCounts[$st]})" : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Step 2: District from op_sdb -->
            <div>
                <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">2. Select District (op_sdb)</label>
                <select name="district" id="district-select" style="width: 100%; height: 44px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                    <option value="">All Districts</option>
                    <?php foreach ($stateDistricts as $dst): ?>
                        <option value="<?= htmlspecialchars($dst) ?>" <?= $selectedDistrict === $dst ? 'selected' : '' ?>><?= htmlspecialchars($dst) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Step 3: University Category -->
            <div>
                <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">3. Category</label>
                <select name="category" style="width: 100%; height: 44px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                    <option value="">All Categories</option>
                    <option value="central" <?= $selectedCategory === 'central' ? 'selected' : '' ?>>Central Universities (DU, BHU, JNU)</option>
                    <option value="national" <?= $selectedCategory === 'national' ? 'selected' : '' ?>>National Importance (IITs, AIIMS, IISc)</option>
                    <option value="state" <?= $selectedCategory === 'state' ? 'selected' : '' ?>>State Universities (Pune, Jadavpur)</option>
                    <option value="college" <?= $selectedCategory === 'college' ? 'selected' : '' ?>>Top Autonomous Colleges (Miranda House)</option>
                    <option value="deemed" <?= $selectedCategory === 'deemed' ? 'selected' : '' ?>>Deemed Universities (BITS Pilani)</option>
                </select>
            </div>

            <!-- Search Keyword (Optional) -->
            <div>
                <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Keyword</label>
                <input type="text" name="q" value="<?= htmlspecialchars($searchKeyword) ?>" placeholder="e.g. IIT, Medical, B.Tech..." style="width: 100%; height: 44px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%; height: 44px; font-size: 0.92rem; border-radius: 6px; white-space: nowrap;">
                    Search 🔍
                </button>
            </div>
        </form>
    </div>
</section>

<!-- QUICK STATE DIRECTORY BROWSER (Powered by op_sdb) -->
<section style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin-bottom: 32px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
        <h2 style="font-size: 1.15rem; font-weight: 800; color: #111827; display: flex; align-items: center; gap: 6px;">
            📍 Browse by State & Union Territory <span style="font-size: 0.75rem; font-weight: 700; color: var(--orange); background: var(--orange-light); padding: 2px 8px; border-radius: 999px;">op_sdb</span>
        </h2>
        <a href="index.php" style="font-size: 0.8rem; font-weight: 700; color: var(--orange);">Reset Selection ↺</a>
    </div>

    <!-- State Pills (Tap to Filter) -->
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        <?php foreach ($allStates as $st): 
            $count = $stateCounts[$st] ?? 0;
            $isActive = ($selectedState === $st);
        ?>
            <a href="index.php?state=<?= urlencode($st) ?>" 
               style="font-size: 0.82rem; font-weight: 700; padding: 6px 12px; border-radius: 999px; transition: all 0.15s; display: inline-flex; align-items: center; gap: 5px; <?= $isActive ? 'background: #111827; color: #FFFFFF; border: 1.5px solid #111827;' : ($count > 0 ? 'background: var(--orange-light); color: var(--orange-dark); border: 1px solid var(--orange-border);' : 'background: #F8FAFC; color: #4B5563; border: 1px solid #E2E8F0;') ?>">
                <span><?= htmlspecialchars($st) ?></span>
                <?php if ($count > 0): ?>
                    <span style="background: <?= $isActive ? 'var(--orange)' : '#FFFFFF' ?>; color: <?= $isActive ? '#FFFFFF' : 'var(--orange-dark)' ?>; font-size: 0.68rem; padding: 1px 6px; border-radius: 999px; font-weight: 800;">
                        <?= $count ?>
                    </span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- DIRECTORY RESULTS: Universities & Colleges -->
<section style="margin-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
        <div>
            <h2 style="font-size: 1.45rem; font-weight: 800; color: #111827;">
                Universities & Colleges in India <?= $selectedState ? "— " . htmlspecialchars($selectedState) : '' ?>
            </h2>
            <p style="font-size: 0.85rem; color: #6B7280;">
                Showing <?= count($institutions) ?> verified higher education institutions.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="institutions.php" class="btn btn-outline" style="font-size: 0.8rem; padding: 6px 14px;">
                Full Directory Table →
            </a>
        </div>
    </div>

    <?php if (empty($institutions)): ?>
        <div style="background: #FFFFFF; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); padding: 40px 16px; text-align: center;">
            <div style="font-size: 2.2rem; margin-bottom: 8px;">🏛️</div>
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #111827; margin-bottom: 4px;">No Institutions Found</h3>
            <p style="font-size: 0.85rem; color: #6B7280; margin-bottom: 16px;">
                We are actively onboarding more colleges in <?= htmlspecialchars($selectedState ?: 'this area') ?> from the official database.
            </p>
            <a href="index.php" class="btn btn-primary" style="font-size: 0.85rem;">View All Premier Universities</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 18px;">
            <?php foreach ($institutions as $inst): 
                $facilities = json_decode($inst['facilities'] ?? '[]', true) ?: [];
            ?>
                <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: border-color 0.2s, transform 0.15s;">
                    <div>
                        <!-- Header: Name & Type -->
                        <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 10px;">
                            <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--orange-light); border: 1px solid var(--orange-border); color: var(--orange-dark); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; font-weight: 800;">
                                <?= $inst['type'] === 'college' ? '🎓' : '🏛️' ?>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h3 style="font-size: 1.05rem; font-weight: 800; color: #111827; line-height: 1.3; margin-bottom: 2px;">
                                    <a href="institution-detail.php?slug=<?= urlencode($inst['slug']) ?>"><?= htmlspecialchars($inst['name']) ?></a>
                                </h3>
                                <div style="font-size: 0.8rem; color: #6B7280;">
                                    📍 <?= htmlspecialchars($inst['city'] ?: $inst['district']) ?>, <strong><?= htmlspecialchars($inst['state']) ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Badges: Category & Accreditation -->
                        <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px;">
                            <?php if (!empty($inst['subtype'])): ?>
                                <span style="font-size: 0.72rem; font-weight: 800; background: #111827; color: #FFFFFF; padding: 2px 7px; border-radius: 4px;">
                                    <?= htmlspecialchars($inst['subtype']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($inst['affiliation'])): ?>
                                <span style="font-size: 0.72rem; font-weight: 700; background: #EFF6FF; color: #1E40AF; padding: 2px 7px; border-radius: 4px; border: 1px solid #BFDBFE;">
                                    <?= htmlspecialchars($inst['affiliation']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($inst['is_verified']): ?>
                                <span style="font-size: 0.72rem; font-weight: 800; background: var(--orange-light); color: var(--orange-dark); padding: 2px 7px; border-radius: 4px; border: 1px solid var(--orange-border);">
                                    ✓ Verified
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <p style="font-size: 0.82rem; color: #4B5563; line-height: 1.5; margin-bottom: 12px;">
                            <?= htmlspecialchars(mb_substr($inst['overview'] ?? '', 0, 110)) ?>...
                        </p>

                        <!-- Facilities -->
                        <?php if (!empty($facilities)): ?>
                            <div style="font-size: 0.74rem; color: #6B7280; margin-bottom: 12px;">
                                Campus: <strong style="color: #111827;"><?= htmlspecialchars(implode(', ', array_slice($facilities, 0, 3))) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Bottom Bar: Fee & Action -->
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                        <div>
                            <div style="font-size: 0.68rem; color: #6B7280; text-transform: uppercase; font-weight: 700;">Tuition Fee</div>
                            <div style="font-size: 0.85rem; font-weight: 800; color: var(--orange-dark);">
                                <?= htmlspecialchars($inst['fee_range'] ?: 'Details on request') ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <a href="institution-detail.php?slug=<?= urlencode($inst['slug']) ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.78rem; border-color: #111827; color: #111827;">
                                Details →
                            </a>
                            <a href="#counseling-box" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.78rem;">
                                Enquire
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ADMISSION COUNSELING & ENQUIRY BOX (Orange, Black, White) -->
<section id="counseling-box" style="background: #FFFFFF; border: 2px solid var(--orange); border-radius: var(--radius-lg); padding: 32px 20px; margin-bottom: 36px; box-shadow: var(--shadow-md);">
    <div style="max-width: 740px; margin: 0 auto; text-align: center;">
        <span style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 1px; color: var(--orange-dark); font-weight: 800; background: var(--orange-light); padding: 3px 10px; border-radius: 999px;">
            🎓 Free National Student Helpline
        </span>
        <h2 style="font-size: 1.8rem; font-weight: 800; color: #111827; margin: 10px 0 6px;">
            Direct University & College Admission Counseling
        </h2>
        <p style="color: #4B5563; font-size: 0.92rem; margin-bottom: 20px;">
            Get instant guidance on cut-offs (CUET, JEE, NEET), seat allotments, and scholarships. Instant mobile confirmation with DLT SMS OTP Sender ID <strong>OEDUIN</strong>.
        </p>

        <form action="enquiry-submit.php" method="POST" style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; text-align: left; display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px;" class="oedu-enquiry-form">
            <input type="hidden" name="institution_id" value="1">
            <input type="hidden" name="source" value="national_portal_finder">

            <div>
                <label style="font-size: 0.78rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Student Name *</label>
                <input type="text" name="student_name" placeholder="Your Full Name" required style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF;">
            </div>

            <div>
                <label style="font-size: 0.78rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Mobile Number (for DLT SMS OTP) *</label>
                <input type="tel" name="phone" placeholder="10-digit Mobile" pattern="[0-9]{10}" required style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF;">
            </div>

            <div>
                <label style="font-size: 0.78rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Select State (from op_sdb)</label>
                <select name="state" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                    <option value="">Select State</option>
                    <?php foreach ($allStates as $st): ?>
                        <option value="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="font-size: 0.78rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Degree / Course</label>
                <select name="interested_stream" style="width: 100%; height: 42px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem; background: #FFF; font-weight: 600;">
                    <option value="B.Tech / Engineering">B.Tech / Engineering</option>
                    <option value="MBBS / Medical">MBBS / Medical</option>
                    <option value="B.Sc / Pure Sciences">B.Sc / Pure Sciences</option>
                    <option value="B.Com / Finance">B.Com / Finance</option>
                    <option value="BA / Arts & Humanities">BA / Arts & Humanities</option>
                    <option value="LLB / National Law">LLB / National Law</option>
                    <option value="MBA / Management">MBA / Management</option>
                    <option value="PhD / Research">PhD / Research</option>
                </select>
            </div>

            <div style="grid-column: 1 / -1; margin-top: 6px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; height: 46px; font-size: 0.95rem; border-radius: 6px;">
                    Submit for Instant Counseling & DLT OTP →
                </button>
                <div style="font-size: 0.72rem; color: #6B7280; text-align: center; margin-top: 8px;">
                    🔒 Free Student Welfare Initiative • Verified DLT Sender: <strong>OEDUIN</strong> • Template: 1277179178303893981
                </div>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
