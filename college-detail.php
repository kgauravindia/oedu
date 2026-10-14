<?php
// college-detail.php — Dedicated Profile for Affiliated College from AISHE (.php only)
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
$aishe = trim($_GET['aishe'] ?? '');
$id = (int)($_GET['id'] ?? 0);

if (!$slug && !$aishe && !$id) {
    header("Location: college.php");
    exit;
}

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM college WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
} elseif ($aishe) {
    $stmt = $pdo->prepare("SELECT * FROM college WHERE aishe_code = :aishe LIMIT 1");
    $stmt->execute([':aishe' => $aishe]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM college WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
}

$college = $stmt->fetch();

if (!$college) {
    $pageTitle = 'College Not Found | Online Education';
    require_once __DIR__ . '/includes/header.php';
    echo '<div style="background:#FFF; padding:48px 16px; text-align:center; border:1px solid #E5E7EB; border-radius:12px; margin:40px auto; max-width:600px;">';
    echo '<h2 style="color:#111827; font-weight:800;">College Not Found</h2>';
    echo '<p style="color:#6B7280; margin:8px 0 20px;">The requested college profile could not be located in the national directory.</p>';
    echo '<a href="college.php" class="btn btn-primary">Browse All Colleges</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch other colleges under the same university or district
$relatedColleges = [];
if (!empty($college['university_aishe_code'])) {
    $relStmt = $pdo->prepare("SELECT * FROM college WHERE university_aishe_code = :u_aishe AND id != :id ORDER BY name ASC LIMIT 6");
    $relStmt->execute([':u_aishe' => $college['university_aishe_code'], ':id' => $college['id']]);
    $relatedColleges = $relStmt->fetchAll();
} elseif (!empty($college['district'])) {
    $relStmt = $pdo->prepare("SELECT * FROM college WHERE district = :district AND id != :id ORDER BY name ASC LIMIT 6");
    $relStmt->execute([':district' => $college['district'], ':id' => $college['id']]);
    $relatedColleges = $relStmt->fetchAll();
}

$isGovt = stripos($college['management'] ?? '', 'Government') !== false;
$mgmtColor = $isGovt ? '#166534' : '#1E40AF';
$mgmtBg = $isGovt ? '#F0FDF4' : '#EFF6FF';
$mgmtBorder = $isGovt ? '#BBF7D0' : '#BFDBFE';

$pageTitle = htmlspecialchars($college['name']) . " ({$college['aishe_code']}) | National College Portal — Online Education";
$pageDesc = htmlspecialchars($college['name']) . " in {$college['district']}, {$college['state']}. AISHE Code: {$college['aishe_code']}. Affiliated to {$college['university_name']}.";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs -->
<div style="font-size: 0.8rem; color: #6B7280; margin-bottom: 16px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
    <a href="index.php" style="color: #111827; font-weight: 700;">Home</a>
    <span>/</span>
    <a href="college.php" style="color: #111827; font-weight: 700;">Colleges</a>
    <span>/</span>
    <a href="college.php?state=<?= urlencode($college['state'] ?? '') ?>" style="color: var(--orange-dark); font-weight: 700;"><?= htmlspecialchars($college['state'] ?: 'India') ?></a>
    <span>/</span>
    <span style="color: #111827;"><?= htmlspecialchars($college['name']) ?></span>
</div>

<!-- Main College Profile Card -->
<div style="background: #FFFFFF; border: 2px solid #111827; border-radius: var(--radius-lg); padding: 28px 20px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
        <!-- Emblem Badge -->
        <div style="width: 56px; height: 56px; border-radius: 12px; background: var(--orange-light); border: 2px solid var(--orange-border); color: var(--orange-dark); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; flex-shrink: 0;">
            🎓
        </div>

        <div style="flex: 1; min-width: 260px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 6px; align-items: center;">
                <span style="background: #111827; color: #FFFFFF; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                    AISHE: <?= htmlspecialchars($college['aishe_code']) ?>
                </span>
                <?php if (!empty($college['management'])): ?>
                    <span style="background: <?= $mgmtBg ?>; color: <?= $mgmtColor ?>; border: 1px solid <?= $mgmtBorder ?>; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        <?= htmlspecialchars($college['management']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($college['year_of_establishment'])): ?>
                    <span style="background: var(--orange-light); color: var(--orange-dark); border: 1px solid var(--orange-border); font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        Est. <?= htmlspecialchars($college['year_of_establishment']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($college['location'])): ?>
                    <span style="background: #F1F5F9; color: #475569; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                        <?= htmlspecialchars($college['location']) ?> Area
                    </span>
                <?php endif; ?>
                <span style="background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                    ✓ Official AISHE Record
                </span>
            </div>

            <h1 style="font-size: 1.75rem; font-weight: 800; color: #111827; line-height: 1.25; margin-bottom: 6px;">
                <?= htmlspecialchars($college['name']) ?>
            </h1>

            <div style="font-size: 0.95rem; color: #4B5563; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 16px;">
                <span>📍 <?= htmlspecialchars($college['district'] ?: 'District N/A') ?>, <strong><?= htmlspecialchars($college['state'] ?: 'India') ?></strong>, India</span>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?php if (!empty($college['website'])): 
                    $web = $college['website'];
                    if (!preg_match("~^(?:f|ht)tps?://~i", $web)) {
                        $web = "http://" . $web;
                    }
                ?>
                    <a href="<?= htmlspecialchars($web) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="border-color: #111827; color: #111827; font-weight: 700;">
                        Official Portal ↗
                    </a>
                <?php endif; ?>
                <a href="#enquiry-section" class="btn btn-primary">
                    Apply & Counseling Support →
                </a>
                <a href="college.php?state=<?= urlencode($college['state'] ?? '') ?>" class="btn btn-outline" style="border-color: #CBD5E1;">
                    More in <?= htmlspecialchars($college['state'] ?: 'State') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 40px;" class="detail-two-col">
    <!-- Left Column: Affiliation & Technical Details -->
    <div>
        <!-- Affiliating University Banner -->
        <?php if (!empty($college['university_name'])): ?>
            <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <span style="font-size: 1.3rem;">🏛️</span>
                    <h2 style="font-size: 1.15rem; font-weight: 800; color: #111827;">Affiliating University Information</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px;">
                        <div style="font-size: 0.72rem; color: #64748B; font-weight: 700; text-transform: uppercase;">University Name</div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin-top: 3px;">
                            <?= htmlspecialchars($college['university_name']) ?>
                        </div>
                    </div>
                    <?php if (!empty($college['university_aishe_code'])): ?>
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px;">
                            <div style="font-size: 0.72rem; color: #64748B; font-weight: 700; text-transform: uppercase;">University AISHE Code</div>
                            <div style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin-top: 3px;">
                                <?= htmlspecialchars($college['university_aishe_code']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($college['university_type'])): ?>
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px;">
                            <div style="font-size: 0.72rem; color: #64748B; font-weight: 700; text-transform: uppercase;">University Type</div>
                            <div style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin-top: 3px;">
                                <?= htmlspecialchars($college['university_type']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($college['university_aishe_code'])): ?>
                    <a href="college.php?univ_aishe=<?= urlencode($college['university_aishe_code']) ?>" class="btn btn-outline" style="font-size: 0.8rem; padding: 6px 14px; border-color: var(--orange-dark); color: var(--orange-dark);">
                        Explore all affiliated colleges under this university →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Institution Attributes Table -->
        <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 16px;">
                Institutional Profile & Accreditation
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">College Type</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($college['college_type'] ?: 'Affiliated College') ?></div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">Management Pattern</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($college['management'] ?: 'Standard') ?></div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">Year of Establishment</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($college['year_of_establishment'] ?: 'N/A') ?></div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">Location Classification</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($college['location'] ?: 'Urban / Rural') ?> Area</div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">District & State</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($college['district']) ?>, <?= htmlspecialchars($college['state']) ?></div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">AISHE Unique Code</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($college['aishe_code']) ?></div>
                </div>
            </div>
        </div>

        <!-- Related Colleges -->
        <?php if (!empty($relatedColleges)): ?>
            <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 14px;">
                    Other Affiliated Colleges Under Similar Jurisdiction
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px;">
                    <?php foreach ($relatedColleges as $rc): ?>
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <span style="font-size: 0.68rem; font-weight: 800; background: #111827; color: #FFF; padding: 1px 6px; border-radius: 3px;">
                                    <?= htmlspecialchars($rc['aishe_code']) ?>
                                </span>
                                <h4 style="font-size: 0.88rem; font-weight: 800; color: #111827; margin: 6px 0 3px; line-height: 1.3;">
                                    <a href="college/<?= urlencode($rc['slug']) ?>"><?= htmlspecialchars($rc['name']) ?></a>
                                </h4>
                                <div style="font-size: 0.75rem; color: #64748B;">
                                    📍 <?= htmlspecialchars($rc['district']) ?>, <?= htmlspecialchars($rc['state']) ?>
                                </div>
                            </div>
                            <div style="margin-top: 10px;">
                                <a href="college/<?= urlencode($rc['slug']) ?>" style="font-size: 0.75rem; font-weight: 700; color: var(--orange-dark);">
                                    View College Details →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Admission & Support Box -->
    <div id="enquiry-section">
        <div style="background: #FFFFFF; border: 2px solid var(--orange); border-radius: var(--radius-md); padding: 22px; box-shadow: 0 4px 14px rgba(255, 107, 0, 0.12); position: sticky; top: 90px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                <span style="font-size: 1.4rem;">🎯</span>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827;">College Admission Inquiry</h3>
            </div>
            <p style="font-size: 0.82rem; color: #6B7280; margin-bottom: 16px;">
                Get direct guidance on cut-offs, eligibility, seat availability, fees, and application dates for <?= htmlspecialchars($college['name']) ?>.
            </p>

            <form action="enquiry-submit.php" method="POST" class="oedu-enquiry-form">
                <input type="hidden" name="target_type" value="college">
                <input type="hidden" name="target_id" value="<?= (int)$college['id'] ?>">
                <input type="hidden" name="redirect_url" value="college/<?= !empty($college['slug']) ? urlencode($college['slug']) : (int)$college['id'] ?>">

                <div style="margin-bottom: 10px;">
                    <label style="font-size: 0.74rem; font-weight: 700; color: #111827; display: block; margin-bottom: 3px;">Full Name *</label>
                    <input type="text" name="sender_name" required placeholder="e.g. Rahul Sharma" style="width: 100%; height: 38px; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem;">
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="font-size: 0.74rem; font-weight: 700; color: #111827; display: block; margin-bottom: 3px;">Phone / WhatsApp *</label>
                    <input type="tel" name="sender_phone" required placeholder="e.g. 9876543210" style="width: 100%; height: 38px; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem;">
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="font-size: 0.74rem; font-weight: 700; color: #111827; display: block; margin-bottom: 3px;">Email (Optional)</label>
                    <input type="email" name="sender_email" placeholder="e.g. rahul@example.com" style="width: 100%; height: 38px; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.74rem; font-weight: 700; color: #111827; display: block; margin-bottom: 3px;">Program / Degree Interested In *</label>
                    <textarea name="message" rows="3" required placeholder="e.g. BA, BSc, BCom, BTech, B.Ed admission cutoffs and scholarship info..." style="width: 100%; padding: 8px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem; font-family: inherit; resize: vertical;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px; font-size: 0.9rem; font-weight: 800; border-radius: 6px;">
                    Submit College Inquiry 🚀
                </button>
            </form>

            <div style="font-size: 0.72rem; color: #9CA3AF; text-align: center; margin-top: 12px;">
                🔒 100% Free & Confidential Official Counseling Support
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
