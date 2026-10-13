<?php
// university-detail.php — Dedicated Profile for University from op_sdb & AISHE (.php only)
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
$aishe = trim($_GET['aishe'] ?? '');
$id = (int)($_GET['id'] ?? 0);

if (!$slug && !$aishe && !$id) {
    header("Location: institutions.php");
    exit;
}

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM university WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
} elseif ($aishe) {
    $stmt = $pdo->prepare("SELECT * FROM university WHERE aishe_code = :aishe LIMIT 1");
    $stmt->execute([':aishe' => $aishe]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM university WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
}

$univ = $stmt->fetch();

if (!$univ) {
    // Check college table as fallback
    $stmtCol = $pdo->prepare("SELECT slug FROM college WHERE slug = :slug LIMIT 1");
    $stmtCol->execute([':slug' => $slug]);
    $colSlug = $stmtCol->fetchColumn();
    if ($colSlug) {
        header("Location: college-detail.php?slug=" . urlencode($colSlug));
        exit;
    }

    // Check institute table as fallback
    $stmtInst = $pdo->prepare("SELECT slug FROM institute WHERE slug = :slug LIMIT 1");
    $stmtInst->execute([':slug' => $slug]);
    $instSlug = $stmtInst->fetchColumn();
    if ($instSlug) {
        header("Location: institute-detail.php?slug=" . urlencode($instSlug));
        exit;
    }

    $pageTitle = 'University Not Found | Oedu.in';
    require_once __DIR__ . '/includes/header.php';
    echo '<div style="background:#FFF; padding:48px 16px; text-align:center; border:1px solid #E5E7EB; border-radius:12px; margin:40px auto; max-width:600px;">';
    echo '<h2 style="color:#111827; font-weight:800;">University Not Found</h2>';
    echo '<p style="color:#6B7280; margin:8px 0 20px;">The requested university profile could not be located in the national directory.</p>';
    echo '<a href="institutions.php" class="btn btn-primary">Browse All Universities</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = htmlspecialchars($univ['name']) . " ({$univ['aishe_code']}) | National University Portal — Oedu.in";
$pageDesc = htmlspecialchars($univ['name']) . " in {$univ['district']}, {$univ['state']}. AISHE Code: {$univ['aishe_code']}. Established in {$univ['year_of_establishment']}.";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs -->
<div style="font-size: 0.8rem; color: #6B7280; margin-bottom: 16px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
    <a href="index.php" style="color: #111827; font-weight: 700;">Home</a>
    <span>/</span>
    <a href="institutions.php?state=<?= urlencode($univ['state']) ?>" style="color: var(--orange-dark); font-weight: 700;"><?= htmlspecialchars($univ['state']) ?></a>
    <span>/</span>
    <span style="color: #111827;"><?= htmlspecialchars($univ['name']) ?></span>
</div>

<!-- Main University Profile Card (Simple, White, Black, Orange) -->
<div style="background: #FFFFFF; border: 2px solid #111827; border-radius: var(--radius-lg); padding: 28px 20px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
        <!-- Emblem Badge -->
        <div style="width: 56px; height: 56px; border-radius: 12px; background: var(--orange-light); border: 2px solid var(--orange-border); color: var(--orange-dark); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; flex-shrink: 0;">
            🏛️
        </div>

        <div style="flex: 1; min-width: 260px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 6px; align-items: center;">
                <span style="background: #111827; color: #FFFFFF; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                    AISHE: <?= htmlspecialchars($univ['aishe_code']) ?>
                </span>
                <?php if (!empty($univ['year_of_establishment'])): ?>
                    <span style="background: var(--orange-light); color: var(--orange-dark); border: 1px solid var(--orange-border); font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        Est. <?= htmlspecialchars($univ['year_of_establishment']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($univ['location'])): ?>
                    <span style="background: #F1F5F9; color: #475569; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                        <?= htmlspecialchars($univ['location']) ?> Area
                    </span>
                <?php endif; ?>
                <span style="background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                    ✓ Government AISHE Verified
                </span>
            </div>

            <h1 style="font-size: 1.75rem; font-weight: 800; color: #111827; line-height: 1.25; margin-bottom: 6px;">
                <?= htmlspecialchars($univ['name']) ?>
            </h1>

            <div style="font-size: 0.95rem; color: #4B5563; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 16px;">
                <span>📍 <?= htmlspecialchars($univ['district']) ?>, <strong><?= htmlspecialchars($univ['state']) ?></strong>, India</span>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?php if (!empty($univ['website'])): 
                    $webUrl = $univ['website'];
                    if (!preg_match("~^(?:f|ht)tps?://~i", $webUrl)) {
                        $webUrl = "http://" . $webUrl;
                    }
                ?>
                    <a href="<?= htmlspecialchars($webUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="padding: 10px 20px; font-size: 0.9rem;">
                        Official Website (<?= htmlspecialchars(parse_url($webUrl, PHP_URL_HOST) ?: $univ['website']) ?>) ↗
                    </a>
                <?php endif; ?>
                <a href="#enquiry-box" class="btn btn-dark" style="padding: 10px 20px; font-size: 0.9rem;">
                    Admission Enquiry / Apply
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Details Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 32px;">
    <!-- Key Details Box -->
    <div style="background: #FFFFFF; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); padding: 20px;">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 14px; border-bottom: 2px solid var(--orange); padding-bottom: 6px; display: inline-block;">
            📋 University Directory Data
        </h3>

        <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <tr style="border-bottom: 1px solid #F1F5F9;">
                <td style="padding: 10px 0; color: #6B7280; font-weight: 700; width: 40%;">AISHE Code</td>
                <td style="padding: 10px 0; color: #111827; font-weight: 800;"><?= htmlspecialchars($univ['aishe_code']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #F1F5F9;">
                <td style="padding: 10px 0; color: #6B7280; font-weight: 700;">State</td>
                <td style="padding: 10px 0; color: #111827; font-weight: 800;"><?= htmlspecialchars($univ['state']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #F1F5F9;">
                <td style="padding: 10px 0; color: #6B7280; font-weight: 700;">District</td>
                <td style="padding: 10px 0; color: #111827; font-weight: 800;"><?= htmlspecialchars($univ['district']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #F1F5F9;">
                <td style="padding: 10px 0; color: #6B7280; font-weight: 700;">Establishment Year</td>
                <td style="padding: 10px 0; color: #111827; font-weight: 800;"><?= htmlspecialchars($univ['year_of_establishment'] ?: 'Official record') ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #F1F5F9;">
                <td style="padding: 10px 0; color: #6B7280; font-weight: 700;">Campus Setting</td>
                <td style="padding: 10px 0; color: #111827; font-weight: 800;"><?= htmlspecialchars($univ['location'] ?: 'Recognized Campus') ?></td>
            </tr>
            <tr>
                <td style="padding: 10px 0; color: #6B7280; font-weight: 700;">Directory Status</td>
                <td style="padding: 10px 0; color: #166534; font-weight: 800;">Active National Record</td>
            </tr>
        </table>
    </div>

    <!-- Admission & Information Enquiry Form -->
    <div id="enquiry-box" style="background: #FFFFFF; border: 2px solid var(--orange); border-radius: var(--radius-md); padding: 20px;">
        <span style="font-size: 0.72rem; font-weight: 800; color: var(--orange-dark); background: var(--orange-light); padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
            Direct Counseling Assistance
        </span>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: #111827; margin: 6px 0 12px;">
            Enquire for Admissions & Courses
        </h3>
        <p style="font-size: 0.82rem; color: #6B7280; margin-bottom: 16px;">
            Submit your inquiry regarding admissions, eligibility, cutoff ranks, or campus visits for <strong><?= htmlspecialchars($univ['name']) ?></strong>.
        </p>

        <form action="enquiry-submit.php" method="POST" class="oedu-enquiry-form" style="display: flex; flex-direction: column; gap: 12px;">
            <input type="hidden" name="institution_id" value="<?= $univ['id'] ?>">
            <input type="hidden" name="institution_name" value="<?= htmlspecialchars($univ['name']) ?>">
            <input type="hidden" name="source" value="university_detail_page">

            <div>
                <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Student Name *</label>
                <input type="text" name="student_name" placeholder="Your Full Name" required style="width: 100%; height: 40px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
            </div>

            <div>
                <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Mobile Number (for DLT SMS OTP) *</label>
                <input type="tel" name="phone" placeholder="10-digit Mobile" pattern="[0-9]{10}" required style="width: 100%; height: 40px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
            </div>

            <div>
                <label style="font-size: 0.76rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Degree of Interest *</label>
                <select name="interested_stream" style="width: 100%; height: 40px; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                    <option value="Undergraduate (UG Degree)">Undergraduate (UG Degree)</option>
                    <option value="Postgraduate (PG Degree)">Postgraduate (PG Degree)</option>
                    <option value="PhD / Doctoral Research">PhD / Doctoral Research</option>
                    <option value="Diploma / Certificate">Diploma / Certificate</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="height: 44px; font-size: 0.92rem; border-radius: 6px; width: 100%;">
                Submit Free Enquiry & Receive OTP →
            </button>
            <div style="font-size: 0.7rem; color: #9CA3AF; text-align: center;">
                SMS verified via DLT Sender ID: <strong>OEDUIN</strong>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
