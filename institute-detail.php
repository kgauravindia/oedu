<?php
// institute-detail.php — Dedicated Profile for Standalone Institute from AISHE (.php only)
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
$aishe = trim($_GET['aishe'] ?? '');
$id = (int)($_GET['id'] ?? 0);

if (!$slug && !$aishe && !$id) {
    header("Location: institute.php");
    exit;
}

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM institute WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
} elseif ($aishe) {
    $stmt = $pdo->prepare("SELECT * FROM institute WHERE aishe_code = :aishe LIMIT 1");
    $stmt->execute([':aishe' => $aishe]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM institute WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
}

$inst = $stmt->fetch();

if (!$inst) {
    $pageTitle = 'Institute Not Found | Online Education';
    require_once __DIR__ . '/includes/header.php';
    echo '<div style="background:#FFF; padding:48px 16px; text-align:center; border:1px solid #E5E7EB; border-radius:12px; margin:40px auto; max-width:600px;">';
    echo '<h2 style="color:#111827; font-weight:800;">Institute Not Found</h2>';
    echo '<p style="color:#6B7280; margin:8px 0 20px;">The requested standalone institute could not be located in the national directory.</p>';
    echo '<a href="institute.php" class="btn btn-primary">Browse All Institutes</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch other institutes under the same type or district
$relatedInstitutes = [];
if (!empty($inst['institute_type']) && !empty($inst['state'])) {
    $relStmt = $pdo->prepare("SELECT * FROM institute WHERE institute_type = :type AND state = :state AND id != :id ORDER BY name ASC LIMIT 6");
    $relStmt->execute([':type' => $inst['institute_type'], ':state' => $inst['state'], ':id' => $inst['id']]);
    $relatedInstitutes = $relStmt->fetchAll();
} elseif (!empty($inst['state'])) {
    $relStmt = $pdo->prepare("SELECT * FROM institute WHERE state = :state AND id != :id ORDER BY name ASC LIMIT 6");
    $relStmt->execute([':state' => $inst['state'], ':id' => $inst['id']]);
    $relatedInstitutes = $relStmt->fetchAll();
}

// Icon helper
function getInstIcon($type) {
    if (stripos($type, 'polytechnic') !== false || stripos($type, 'technical') !== false) return '🛠️';
    if (stripos($type, 'nursing') !== false) return '🩺';
    if (stripos($type, 'teacher') !== false) return '📖';
    if (stripos($type, 'paramedical') !== false) return '🧪';
    if (stripos($type, 'pgdm') !== false) return '💼';
    if (stripos($type, 'pharmacy') !== false) return '💊';
    return '🏢';
}

$icon = getInstIcon($inst['institute_type'] ?? '');
$isGovt = stripos($inst['management'] ?? '', 'Government') !== false;
$mgmtColor = $isGovt ? '#166534' : '#1E40AF';
$mgmtBg = $isGovt ? '#F0FDF4' : '#EFF6FF';
$mgmtBorder = $isGovt ? '#BBF7D0' : '#BFDBFE';

$pageTitle = htmlspecialchars($inst['name']) . " ({$inst['aishe_code']}) | Standalone Institute Portal — Online Education";
$pageDesc = htmlspecialchars($inst['name']) . " in {$inst['district']}, {$inst['state']}. AISHE Code: {$inst['aishe_code']}. Category: {$inst['institute_type']}.";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs -->
<div style="font-size: 0.8rem; color: #6B7280; margin-bottom: 16px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
    <a href="index.php" style="color: #111827; font-weight: 700;">Home</a>
    <span>/</span>
    <a href="institute.php" style="color: #111827; font-weight: 700;">Standalone Institutes</a>
    <span>/</span>
    <a href="institute.php?state=<?= urlencode($inst['state'] ?? '') ?>" style="color: var(--orange-dark); font-weight: 700;"><?= htmlspecialchars($inst['state'] ?: 'India') ?></a>
    <span>/</span>
    <span style="color: #111827;"><?= htmlspecialchars($inst['name']) ?></span>
</div>

<!-- Main Institute Profile Card -->
<div style="background: #FFFFFF; border: 2px solid #111827; border-radius: var(--radius-lg); padding: 28px 20px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
        <!-- Emblem Badge -->
        <div style="width: 56px; height: 56px; border-radius: 12px; background: #F8FAFC; border: 2px solid #CBD5E1; color: #0F172A; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; flex-shrink: 0;">
            <?= $icon ?>
        </div>

        <div style="flex: 1; min-width: 260px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 6px; align-items: center;">
                <span style="background: #111827; color: #FFFFFF; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                    AISHE: <?= htmlspecialchars($inst['aishe_code']) ?>
                </span>
                <?php if (!empty($inst['institute_type'])): ?>
                    <span style="background: var(--orange-light); color: var(--orange-dark); border: 1px solid var(--orange-border); font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        <?= htmlspecialchars($inst['institute_type']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($inst['management'])): ?>
                    <span style="background: <?= $mgmtBg ?>; color: <?= $mgmtColor ?>; border: 1px solid <?= $mgmtBorder ?>; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                        <?= htmlspecialchars($inst['management']) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($inst['year_of_establishment'])): ?>
                    <span style="background: #F1F5F9; color: #475569; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                        Est. <?= htmlspecialchars($inst['year_of_establishment']) ?>
                    </span>
                <?php endif; ?>
                <span style="background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                    ✓ AISHE Verified
                </span>
            </div>

            <h1 style="font-size: 1.75rem; font-weight: 800; color: #111827; line-height: 1.25; margin-bottom: 6px;">
                <?= htmlspecialchars($inst['name']) ?>
            </h1>

            <div style="font-size: 0.95rem; color: #4B5563; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 16px;">
                <span>📍 <?= htmlspecialchars($inst['district'] ?: 'District N/A') ?>, <strong><?= htmlspecialchars($inst['state'] ?: 'India') ?></strong>, India</span>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="#enquiry-section" class="btn btn-primary">
                    Apply & Counseling Guidance →
                </a>
                <a href="institute.php?type=<?= urlencode($inst['institute_type'] ?? '') ?>" class="btn btn-outline" style="border-color: #111827; color: #111827; font-weight: 700;">
                    More <?= htmlspecialchars($inst['institute_type'] ?? 'Institutes') ?>
                </a>
                <a href="institute.php?state=<?= urlencode($inst['state'] ?? '') ?>" class="btn btn-outline" style="border-color: #CBD5E1;">
                    More in <?= htmlspecialchars($inst['state'] ?: 'State') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 40px;" class="detail-two-col">
    <!-- Left Column: Classification & Technical Details -->
    <div>
        <!-- Institutional Profile Card -->
        <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 16px;">
                Standalone Institute Profile & Classification
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">Institute Category</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($inst['institute_type'] ?: 'Standalone') ?></div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">Management Pattern</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($inst['management'] ?: 'Standard') ?></div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">Year of Establishment</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($inst['year_of_establishment'] ?: 'N/A') ?></div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">Location Classification</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($inst['location'] ?: 'Urban / Rural') ?> Area</div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">District & State</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($inst['district']) ?>, <?= htmlspecialchars($inst['state']) ?></div>
                </div>
                <div style="border-left: 3px solid var(--orange); padding-left: 10px;">
                    <div style="font-size: 0.75rem; color: #6B7280; font-weight: 700;">AISHE Unique Code</div>
                    <div style="font-size: 0.92rem; font-weight: 800; color: #111827;"><?= htmlspecialchars($inst['aishe_code']) ?></div>
                </div>
            </div>
        </div>

        <!-- Related Institutes -->
        <?php if (!empty($relatedInstitutes)): ?>
            <div style="background: #FFFFFF; border: 1.5px solid #E5E7EB; border-radius: var(--radius-md); padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin-bottom: 14px;">
                    Other <?= htmlspecialchars($inst['institute_type'] ?? 'Institutes') ?> in <?= htmlspecialchars($inst['state'] ?? 'State') ?>
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px;">
                    <?php foreach ($relatedInstitutes as $ri): ?>
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <span style="font-size: 0.68rem; font-weight: 800; background: #111827; color: #FFF; padding: 1px 6px; border-radius: 3px;">
                                    <?= htmlspecialchars($ri['aishe_code']) ?>
                                </span>
                                <h4 style="font-size: 0.88rem; font-weight: 800; color: #111827; margin: 6px 0 3px; line-height: 1.3;">
                                    <a href="institute/<?= urlencode($ri['slug']) ?>"><?= htmlspecialchars($ri['name']) ?></a>
                                </h4>
                                <div style="font-size: 0.75rem; color: #64748B;">
                                    📍 <?= htmlspecialchars($ri['district']) ?>, <?= htmlspecialchars($ri['state']) ?>
                                </div>
                            </div>
                            <div style="margin-top: 10px;">
                                <a href="institute/<?= urlencode($ri['slug']) ?>" style="font-size: 0.75rem; font-weight: 700; color: var(--orange-dark);">
                                    View Institute Profile →
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
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827;">Admission & Diploma Guidance</h3>
            </div>
            <p style="font-size: 0.82rem; color: #6B7280; margin-bottom: 16px;">
                Get counseling on course admission, entrance requirements, diploma certificates, and dates for <?= htmlspecialchars($inst['name']) ?>.
            </p>

            <form action="enquiry-submit.php" method="POST" class="oedu-enquiry-form">
                <input type="hidden" name="target_type" value="institute">
                <input type="hidden" name="target_id" value="<?= (int)$inst['id'] ?>">
                <input type="hidden" name="redirect_url" value="institute/<?= !empty($inst['slug']) ? urlencode($inst['slug']) : (int)$inst['id'] ?>">

                <div style="margin-bottom: 10px;">
                    <label style="font-size: 0.74rem; font-weight: 700; color: #111827; display: block; margin-bottom: 3px;">Full Name *</label>
                    <input type="text" name="sender_name" required placeholder="e.g. Pooja Patel" style="width: 100%; height: 38px; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem;">
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="font-size: 0.74rem; font-weight: 700; color: #111827; display: block; margin-bottom: 3px;">Phone / WhatsApp *</label>
                    <input type="tel" name="sender_phone" required placeholder="e.g. 9876543210" style="width: 100%; height: 38px; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem;">
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="font-size: 0.74rem; font-weight: 700; color: #111827; display: block; margin-bottom: 3px;">Email (Optional)</label>
                    <input type="email" name="sender_email" placeholder="e.g. pooja@example.com" style="width: 100%; height: 38px; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.74rem; font-weight: 700; color: #111827; display: block; margin-bottom: 3px;">Course / Query Details *</label>
                    <textarea name="message" rows="3" required placeholder="e.g. Diploma in Engineering / GNM Nursing / B.Ed admission cutoffs and fees..." style="width: 100%; padding: 8px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem; font-family: inherit; resize: vertical;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px; font-size: 0.9rem; font-weight: 800; border-radius: 6px;">
                    Submit Institute Inquiry 🚀
                </button>
            </form>

            <div style="font-size: 0.72rem; color: #9CA3AF; text-align: center; margin-top: 12px;">
                🔒 100% Free & Confidential Official Counseling Support
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
