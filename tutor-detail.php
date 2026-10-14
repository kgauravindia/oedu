<?php
// tutor-detail.php — Detailed Educator Profile & Safe Booking (.php only)
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    header("Location: tutor.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM educator WHERE slug = :slug LIMIT 1");
$stmt->execute([':slug' => $slug]);
$educator = $stmt->fetch();

if (!$educator) {
    die("Educator not found. <a href='tutor.php'>Return to Directory</a>");
}

$classes = json_decode($educator['classes_taught'] ?? '[]', true) ?: [];
$subjects = json_decode($educator['subjects_taught'] ?? '[]', true) ?: [];
$modes = json_decode($educator['teaching_modes'] ?? '[]', true) ?: [];
$languages = json_decode($educator['languages'] ?? '[]', true) ?: ['Hindi', 'English'];

$pageTitle = $educator['name'] . ' — ' . $educator['headline'] . ' | Online Education';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="tutor.php" style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">
        ← Back to Teacher & Tutor Network
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)) 340px; gap: 32px; align-items: start;">
    <!-- Main Profile Card -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; gap: 20px; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap;">
            <div style="width: 76px; height: 76px; border-radius: 16px; background: #FEF3C7; color: #92400E; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; flex-shrink: 0; box-shadow: var(--shadow-sm);">
                👨‍🏫
            </div>
            <div>
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 4px;">
                    <span class="verified-badge">✓ <?= htmlspecialchars($educator['badge_label']) ?></span>
                    <span style="font-size: 0.75rem; background: var(--primary-light); color: var(--primary); font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                        ★ <?= htmlspecialchars($educator['rating']) ?> (<?= htmlspecialchars($educator['reviews_count']) ?> Verified Reviews)
                    </span>
                </div>
                <h1 style="font-size: 2rem; font-weight: 800; color: var(--charcoal); margin: 4px 0;">
                    <?= htmlspecialchars($educator['name']) ?>
                </h1>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--slate-body); margin-bottom: 4px;">
                    <?= htmlspecialchars($educator['headline']) ?>
                </div>
                <div style="font-size: 0.88rem; color: var(--slate-muted);">
                    📍 <?= htmlspecialchars($educator['city'] ?: $educator['district']) ?>, <?= htmlspecialchars($educator['state']) ?> • PIN: <?= htmlspecialchars($educator['pincode'] ?: '841301') ?>
                </div>
            </div>
        </div>

        <!-- Highlights Grid (Example Profile Specifications from user document) -->
        <div style="background: #F8FAF9; border-radius: var(--radius-md); padding: 20px; margin-bottom: 28px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; text-align: center; border: 1px solid var(--border-color);">
            <div>
                <span style="font-size: 0.75rem; color: var(--slate-muted); display: block; font-weight: 600;">Teaching Modes</span>
                <strong style="color: var(--charcoal); font-size: 0.9rem; text-transform: capitalize;">
                    <?= htmlspecialchars(implode(' • ', array_map(fn($m) => str_replace('_', ' ', $m), $modes))) ?>
                </strong>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: var(--slate-muted); display: block; font-weight: 600;">Fee Structure</span>
                <strong style="color: #03543F; font-size: 0.95rem;">
                    <?php if ($educator['hourly_fee']): ?>
                        ₹<?= number_format((float) $educator['hourly_fee']) ?>/hr
                    <?php endif; ?>
                    <?php if ($educator['monthly_fee']): ?>
                        • ₹<?= number_format((float) $educator['monthly_fee']) ?>/month
                    <?php endif; ?>
                </strong>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: var(--slate-muted); display: block; font-weight: 600;">Languages</span>
                <strong style="color: var(--charcoal); font-size: 0.9rem;">
                    <?= htmlspecialchars(implode(' / ', $languages)) ?>
                </strong>
            </div>
        </div>

        <!-- Academic & Subject Details -->
        <div style="margin-bottom: 28px;">
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--charcoal); margin-bottom: 12px; border-bottom: 2px solid var(--primary-light); padding-bottom: 6px;">
                Subjects & Classes Taught
            </h3>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                <?php foreach($subjects as $sub): ?>
                    <span style="background: var(--primary-light); color: var(--primary); padding: 6px 14px; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 700;">
                        📘 <?= htmlspecialchars($sub) ?>
                    </span>
                <?php endforeach; ?>
                <?php foreach($classes as $cls): ?>
                    <span style="background: #E2E8F0; color: #1E293B; padding: 6px 14px; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 600;">
                        🎓 <?= htmlspecialchars($cls) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Availability & Timings -->
        <div style="margin-bottom: 28px;">
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--charcoal); margin-bottom: 8px;">
                Availability & Batch Timings
            </h3>
            <p style="font-size: 0.92rem; color: var(--slate-body); background: #FFFBEB; border-left: 4px solid var(--secondary); padding: 12px 16px; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                🕒 <strong>Current Schedule:</strong> <?= htmlspecialchars($educator['availability'] ?: 'Mon–Sat (4:00 PM – 8:30 PM), Sunday Morning batches') ?>
            </p>
        </div>

        <!-- Bio / Methodology -->
        <div style="margin-bottom: 28px;">
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--charcoal); margin-bottom: 8px;">
                Educator Background & Teaching Methodology
            </h3>
            <p style="font-size: 0.92rem; line-height: 1.8; color: var(--slate-body);">
                <?= nl2br(htmlspecialchars($educator['bio'])) ?>
            </p>
        </div>

        <!-- Verification Checklist -->
        <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: var(--radius-md); padding: 16px;">
            <strong style="color: #166534; font-size: 0.88rem; display: block; margin-bottom: 6px;">✓ Verified Educator Credentials Checklist</strong>
            <div style="font-size: 0.82rem; color: #15803D; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <div>✓ Aadhaar / Government ID Verified</div>
                <div>✓ Educational Degree (<?= htmlspecialchars($educator['qualification']) ?>) Verified</div>
                <div>✓ Phone & Email OTP Authenticated</div>
                <div>✓ Background & Child Safety Check Acknowledged</div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Safe Enquiry Form & Child Safety Guarantee -->
    <aside style="display: flex; flex-direction: column; gap: 24px;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
            <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); margin-bottom: 4px;">Book Home Tuition / Online Batch</h4>
            <p style="font-size: 0.82rem; color: var(--slate-muted); margin-bottom: 16px;">
                Send your class requirement directly to <?= htmlspecialchars($educator['name']) ?>. You will receive a verification OTP and callback.
            </p>

            <form action="enquiry-submit.php" method="POST" class="oedu-enquiry-form">
                <input type="hidden" name="target_type" value="educator">
                <input type="hidden" name="target_id" value="<?= htmlspecialchars($educator['id']) ?>">
                <input type="hidden" name="redirect_url" value="tutor-detail.php?slug=<?= urlencode($educator['slug']) ?>">

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Parent / Student Name</label>
                    <input type="text" name="sender_name" required placeholder="Your full name" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Mobile Number</label>
                    <input type="tel" name="sender_phone" required placeholder="+91" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">I am a</label>
                    <select name="sender_type" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                        <option value="parent">Parent (Recommended for Minors)</option>
                        <option value="student">Student (18+)</option>
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Your City / Locality / PIN</label>
                    <input type="text" name="city_or_pincode" placeholder="e.g. Chapra, Dahiyawan (841301)" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Message / Class & Subject Needed</label>
                    <textarea name="message" rows="3" required placeholder="Looking for Class 9 Maths home tuition 3 days/week..." style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px;">
                    Send Tuition Enquiry →
                </button>
            </form>
        </div>

        <!-- Child Safety Guarantee Box -->
        <div style="background: #F8FAF9; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; font-size: 0.8rem; color: var(--slate-body); line-height: 1.5;">
            🛡️ <strong>Child Safety & Minor Privacy:</strong>
            Online Education never reveals personal contact numbers of student minors publicly. Tutors are contacted via parent accounts, and all initial communications are logged for trust, safety, and moderation.
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
