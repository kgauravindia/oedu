<?php
// institution-detail.php — Institution Campus Profile (.php only)
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    header("Location: institutions.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM institutions WHERE slug = :slug LIMIT 1");
$stmt->execute([':slug' => $slug]);
$institution = $stmt->fetch();

if (!$institution) {
    die("Institution not found. <a href='institutions.php'>Return to Directory</a>");
}

$facilities = json_decode($institution['facilities'] ?? '[]', true) ?: [];

$pageTitle = $institution['name'] . ' — National Education Directory | Oedu.in';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="institutions.php" style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">
        ← Back to Institutions Directory
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)) 340px; gap: 32px; align-items: start;">
    <!-- Main Detail -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px;">
            <div style="width: 64px; height: 64px; border-radius: 12px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; flex-shrink: 0;">
                🏫
            </div>
            <div>
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <span style="font-size: 0.75rem; font-weight: 700; background: #E2E8F0; color: #1E293B; padding: 2px 8px; border-radius: 4px; text-transform: capitalize;">
                        <?= htmlspecialchars(str_replace('_', ' ', $institution['type'])) ?>
                    </span>
                    <?php if ($institution['is_verified']): ?>
                        <span class="verified-badge">✓ Verified Institution</span>
                    <?php endif; ?>
                    <?php if ($institution['is_government']): ?>
                        <span class="official-badge">🏛️ Government Institution</span>
                    <?php endif; ?>
                </div>
                <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--charcoal); margin: 6px 0;">
                    <?= htmlspecialchars($institution['name']) ?>
                </h1>
                <div style="font-size: 0.88rem; color: var(--slate-muted);">
                    📍 <?= htmlspecialchars($institution['address'] ?: $institution['city']) ?>, <?= htmlspecialchars($institution['state']) ?> — PIN: <?= htmlspecialchars($institution['pincode'] ?: 'N/A') ?>
                </div>
            </div>
        </div>

        <div style="background: #F8FAF9; border-radius: var(--radius-md); padding: 16px; margin-bottom: 28px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; text-align: center;">
            <div>
                <span style="font-size: 0.75rem; color: var(--slate-muted); display: block;">Affiliation / Recognition</span>
                <strong style="color: var(--charcoal); font-size: 0.95rem;"><?= htmlspecialchars($institution['affiliation'] ?? 'State Authorized') ?></strong>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: var(--slate-muted); display: block;">Annual Fee Range</span>
                <strong style="color: #03543F; font-size: 0.95rem;"><?= htmlspecialchars($institution['fee_range'] ?? 'Government Funded') ?></strong>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: var(--slate-muted); display: block;">Public Rating</span>
                <strong style="color: #B45309; font-size: 0.95rem;">★ <?= htmlspecialchars($institution['rating']) ?> (<?= htmlspecialchars($institution['reviews_count']) ?> Reviews)</strong>
            </div>
        </div>

        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin-bottom: 10px;">About the Campus & Academic Profile</h3>
        <p style="font-size: 0.95rem; line-height: 1.8; color: var(--slate-body); margin-bottom: 28px;">
            <?= nl2br(htmlspecialchars($institution['overview'])) ?>
        </p>

        <?php if (!empty($facilities)): ?>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin-bottom: 12px;">Campus Facilities & Infrastructure</h3>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 28px;">
                <?php foreach($facilities as $fac): ?>
                    <span style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #1E293B; padding: 6px 14px; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 600;">
                        ✓ <?= htmlspecialchars($fac) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($institution['website'])): ?>
            <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="<?= htmlspecialchars($institution['website']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.85rem;">
                    🌐 Visit Official Website ↗
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar: Admission Enquiry & Verification -->
    <aside style="display: flex; flex-direction: column; gap: 24px;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
            <h4 style="font-size: 1.15rem; font-weight: 700; color: var(--charcoal); margin-bottom: 4px;">Admissions & Course Enquiry</h4>
            <p style="font-size: 0.82rem; color: var(--slate-muted); margin-bottom: 16px;">
                Get authentic admission details, syllabus updates, and prospectus directly from this institution.
            </p>

            <form action="enquiry-submit.php" method="POST" class="oedu-enquiry-form">
                <input type="hidden" name="target_type" value="institution">
                <input type="hidden" name="target_id" value="<?= htmlspecialchars($institution['id']) ?>">
                <input type="hidden" name="redirect_url" value="institution-detail.php?slug=<?= urlencode($institution['slug']) ?>">

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Your Full Name</label>
                    <input type="text" name="sender_name" required style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Mobile Number</label>
                    <input type="tel" name="sender_phone" required placeholder="+91" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">I am a</label>
                    <select name="sender_type" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                        <option value="parent">Parent</option>
                        <option value="student">Student</option>
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Query / Course Interested</label>
                    <textarea name="message" rows="3" required placeholder="Interested in admission, fee structure, or batch timings..." style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px;">
                    Submit Enquiry →
                </button>
            </form>
        </div>

        <div style="background: #F8FAF9; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; font-size: 0.8rem; color: var(--slate-muted); line-height: 1.5;">
            🛡️ <strong>Safety & Quality Guarantee:</strong> Oedu.in moderates institution listings. We never promise guaranteed admissions and encourage applicants to verify formal prospectus directly.
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
