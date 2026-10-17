<?php
// school-detail.php — Dedicated Profile for CBSE School (.php only)
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);
$affNo = trim($_GET['aff'] ?? '');
$schCode = trim($_GET['code'] ?? '');
$udise = trim($_GET['udise'] ?? '');

if (!$slug && !$id && !$affNo && !$schCode && !$udise) {
    header("Location: school.php");
    exit;
}

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM `school` WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fallback: If searched without 'cbse-' prefix, retry with 'cbse-'
    if (!$school && !str_starts_with($slug, 'cbse-')) {
        $stmt = $pdo->prepare("SELECT * FROM `school` WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => 'cbse-' . $slug]);
        $school = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} elseif ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM `school` WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($school && !empty($school['slug'])) {
        header("Location: school/" . urlencode($school['slug']), true, 301);
        exit;
    }
} elseif ($affNo) {
    $stmt = $pdo->prepare("SELECT * FROM `school` WHERE affiliation_no = :aff LIMIT 1");
    $stmt->execute([':aff' => $affNo]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($schCode) {
    $stmt = $pdo->prepare("SELECT * FROM `school` WHERE school_code = :code LIMIT 1");
    $stmt->execute([':code' => $schCode]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM `school` WHERE udise_code = :udise LIMIT 1");
    $stmt->execute([':udise' => $udise]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$school) {
    $pageTitle = 'CBSE School Not Found | Online Education';
    require_once __DIR__ . '/includes/header.php';
    echo '<div style="background:#FFF; padding:48px 16px; text-align:center; border:1px solid #E5E7EB; border-radius:12px; margin:40px auto; max-width:600px;">';
    echo '<div style="font-size: 3rem; margin-bottom: 12px;">🏫</div>';
    echo '<h2 style="color:#111827; font-weight:800;">CBSE School Not Found</h2>';
    echo '<p style="color:#6B7280; margin:8px 0 20px;">The requested CBSE school profile could not be located in the national directory.</p>';
    echo '<a href="school.php" class="btn btn-primary">Browse All CBSE Schools</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch nearby / other schools in same district
$nearbyStmt = $pdo->prepare("
    SELECT id, name, affiliation_no, school_code, status, district, state, slug 
    FROM `school` 
    WHERE state = :state AND district = :district AND id != :id 
    ORDER BY name ASC 
    LIMIT 4
");
$nearbyStmt->execute([
    ':state' => $school['state'],
    ':district' => $school['district'],
    ':id' => $school['id']
]);
$nearbySchools = $nearbyStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = htmlspecialchars($school['name']) . " (Aff: " . htmlspecialchars($school['affiliation_no']) . ") — CBSE School Profile | Online Education";
$pageDesc = htmlspecialchars($school['name']) . " in " . htmlspecialchars($school['district']) . ", " . htmlspecialchars($school['state']) . ". Affiliation No: " . htmlspecialchars($school['affiliation_no']) . ", School Code: " . htmlspecialchars($school['school_code']) . ".";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs -->
<div style="font-size: 0.82rem; color: #6B7280; margin-bottom: 18px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
    <a href="index.php" style="color: #111827; font-weight: 700;">Home</a>
    <span>/</span>
    <a href="school.php" style="color: #111827; font-weight: 700;">CBSE Schools</a>
    <span>/</span>
    <a href="school.php?state=<?= urlencode($school['state']) ?>" style="color: var(--orange-dark); font-weight: 700;"><?= htmlspecialchars($school['state']) ?></a>
    <span>/</span>
    <span style="color: #111827;"><?= htmlspecialchars($school['name']) ?></span>
</div>

<!-- Main School Profile Card -->
<div style="background: #FFFFFF; border: 2px solid #111827; border-radius: var(--radius-lg); padding: 28px 22px; margin-bottom: 28px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
        <!-- Emblem Badge -->
        <div style="width: 58px; height: 58px; border-radius: 14px; background: var(--orange-light); border: 2px solid var(--orange-border); color: var(--orange-dark); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; flex-shrink: 0;">
            🏫
        </div>

        <div style="flex: 1; min-width: 260px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; align-items: center;">
                <?php if (!empty($school['affiliation_no'])): ?>
                    <span style="background: #111827; color: #FFFFFF; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        CBSE Affiliation: <?= htmlspecialchars($school['affiliation_no']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($school['school_code'])): ?>
                    <span style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        School Code: <?= htmlspecialchars($school['school_code']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($school['status'])): ?>
                    <span style="background: var(--orange-light); color: var(--orange-dark); border: 1px solid var(--orange-border); font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        <?= htmlspecialchars($school['status']) ?>
                    </span>
                <?php endif; ?>
                <span style="background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                    ✓ CBSE Recognized
                </span>
            </div>

            <h1 style="font-size: 1.75rem; font-weight: 800; color: #111827; line-height: 1.25; margin-bottom: 6px;">
                <?= htmlspecialchars($school['name']) ?>
            </h1>

            <p style="font-size: 0.92rem; color: #4B5563; margin-bottom: 14px;">
                📍 <?= htmlspecialchars($school['district']) ?>, <strong><?= htmlspecialchars($school['state']) ?></strong>, India
            </p>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?php if (!empty($school['website'])): 
                    $web = $school['website'];
                    if (!preg_match("~^(?:f|ht)tps?://~i", $web)) {
                        $web = "http://" . $web;
                    }
                ?>
                    <a href="<?= htmlspecialchars($web) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="padding: 8px 18px; font-size: 0.85rem;">
                        Official Website ↗
                    </a>
                <?php endif; ?>
                <a href="#enquire" class="btn btn-outline" style="border-color: #111827; color: #111827; padding: 8px 18px; font-size: 0.85rem;">
                    Admissions Inquiry ↓
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Layout: Two Columns (Left Details, Right Inquiry Form) -->
<div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 24px; margin-bottom: 48px;">
    <!-- Left Column: Key Facts & Address -->
    <div>
        <!-- Facts Grid -->
        <div style="background: #FFFFFF; border: 1.5px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 16px; border-bottom: 2px solid var(--primary-subtle); padding-bottom: 8px;">
                🏫 Official CBSE Affiliation Data
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div>
                    <div style="font-size: 0.76rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">Affiliation Number</div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: #111827;">
                        <?= htmlspecialchars($school['affiliation_no'] ?: 'N/A') ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 0.76rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">School Code</div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: #111827;">
                        <?= htmlspecialchars($school['school_code'] ?: 'N/A') ?>
                    </div>
                </div>

                <?php if (!empty($school['udise_code'])): ?>
                    <div>
                        <div style="font-size: 0.76rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">National UDISE Code</div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #1D4ED8;">
                            <?= htmlspecialchars($school['udise_code']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($school['pincode'])): ?>
                    <div>
                        <div style="font-size: 0.76rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">Postal PIN Code</div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #111827;">
                            <?= htmlspecialchars($school['pincode']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <div style="font-size: 0.76rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">School Level</div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: var(--orange);">
                        <?= htmlspecialchars($school['status'] ?: 'CBSE Curriculum') ?>
                    </div>
                </div>

                <div>
                    <div style="font-size: 0.76rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">Education Board</div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: #111827;">
                        CBSE (New Delhi)
                    </div>
                </div>

                <?php if (!empty($school['head_name'])): ?>
                    <div style="grid-column: 1 / -1;">
                        <div style="font-size: 0.76rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">Principal / Head of Institution</div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #111827;">
                            <?= htmlspecialchars($school['head_name']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($school['address'])): ?>
                    <div style="grid-column: 1 / -1; border-top: 1px solid #F1F5F9; padding-top: 12px;">
                        <div style="font-size: 0.76rem; font-weight: 700; color: #6B7280; text-transform: uppercase; margin-bottom: 4px;">Campus Address</div>
                        <div style="font-size: 0.92rem; color: #374151; line-height: 1.5;">
                            <?= nl2br(htmlspecialchars($school['address'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nearby CBSE Schools in same district -->
        <?php if (!empty($nearbySchools)): ?>
            <div style="background: #FFFFFF; border: 1.5px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 16px;">
                    Other CBSE Schools in <?= htmlspecialchars($school['district']) ?>
                </h3>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($nearbySchools as $ns): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; gap: 10px;">
                            <div>
                                <h4 style="font-size: 0.92rem; font-weight: 700; color: #111827; margin: 0 0 2px;">
                                    <?= htmlspecialchars($ns['name']) ?>
                                </h4>
                                <span style="font-size: 0.75rem; color: #64748B;">
                                    Aff. No: <?= htmlspecialchars($ns['affiliation_no']) ?> • <?= htmlspecialchars($ns['status'] ?: 'CBSE') ?>
                                </span>
                            </div>
                            <a href="school/<?= urlencode($ns['slug']) ?>" class="btn btn-outline" style="font-size: 0.76rem; padding: 4px 10px; white-space: nowrap;">
                                View →
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Direct Admission & Information Inquiry Form -->
    <div id="enquire">
        <div style="background: #FFFFFF; border: 2px solid #111827; border-radius: var(--radius-lg); padding: 26px 20px; box-shadow: var(--shadow-md); position: sticky; top: 90px;">
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.72rem; font-weight: 800; color: #FFFFFF; background: var(--orange); padding: 2px 8px; border-radius: 4px; margin-bottom: 8px;">
                Admissions &amp; Information
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 4px;">
                Inquire for School Admission
            </h3>
            <p style="font-size: 0.82rem; color: #6B7280; margin-bottom: 16px;">
                Submit inquiry regarding admission procedure, fees, and eligibility for <strong><?= htmlspecialchars($school['name']) ?></strong>.
            </p>

            <form action="enquiry-submit.php" method="POST" class="oedu-enquiry-form" style="display: flex; flex-direction: column; gap: 12px;">
                <input type="hidden" name="target_type" value="school">
                <input type="hidden" name="target_id" value="<?= (int)$school['id'] ?>">
                <input type="hidden" name="institution_name" value="<?= htmlspecialchars($school['name']) ?>">
                <input type="hidden" name="redirect_url" value="school/<?= urlencode($school['slug']) ?>">

                <div>
                    <label style="font-size: 0.76rem; font-weight: 700; display: block; margin-bottom: 4px;">Parent / Student Name *</label>
                    <input type="text" name="sender_name" required placeholder="e.g. Ramesh Kumar" value="<?= htmlspecialchars($_SESSION['student_name'] ?? '') ?>" style="width: 100%; padding: 9px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                </div>

                <div>
                    <label style="font-size: 0.76rem; font-weight: 700; display: block; margin-bottom: 4px;">10-Digit Mobile Number *</label>
                    <input type="tel" name="sender_phone" required placeholder="9876543210" pattern="[6-9][0-9]{9}" value="<?= htmlspecialchars($_SESSION['student_phone'] ?? '') ?>" style="width: 100%; padding: 9px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                </div>

                <div>
                    <label style="font-size: 0.76rem; font-weight: 700; display: block; margin-bottom: 4px;">Email Address (Optional)</label>
                    <input type="email" name="sender_email" placeholder="name@example.com" value="<?= htmlspecialchars($_SESSION['student_email'] ?? '') ?>" style="width: 100%; padding: 9px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                </div>

                <div>
                    <label style="font-size: 0.76rem; font-weight: 700; display: block; margin-bottom: 4px;">Applying For Class / Grade</label>
                    <select name="interested_stream" style="width: 100%; padding: 9px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                        <option value="Pre-Primary / Nursery / KG">Pre-Primary / Nursery / KG</option>
                        <option value="Primary (Classes 1–5)">Primary (Classes 1–5)</option>
                        <option value="Middle (Classes 6–8)">Middle (Classes 6–8)</option>
                        <option value="Secondary (Classes 9–10)">Secondary (Classes 9–10)</option>
                        <option value="Senior Secondary (Class 11–12)">Senior Secondary (Class 11–12)</option>
                        <option value="General Admission Inquiry" selected>General Admission Inquiry</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 0.76rem; font-weight: 700; display: block; margin-bottom: 4px;">Specific Questions (Optional)</label>
                    <textarea name="message" rows="2" placeholder="Fee structure, bus transport, admission date..." style="width: 100%; padding: 9px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 11px; font-size: 0.92rem; width: 100%; margin-top: 4px;">
                    Submit Inquiry via SMS →
                </button>

                <p style="font-size: 0.72rem; color: #9CA3AF; text-align: center; margin: 4px 0 0;">
                    🔒 Instant notification sent via verified DLT SMS (OEDUIN).
                </p>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
