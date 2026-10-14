<?php
// tutor.php — Verified Teachers & Home Tutors Network (.php only)
require_once __DIR__ . '/includes/db.php';

$selectedMode = trim($_GET['mode'] ?? '');
$selectedState = trim($_GET['state'] ?? '');
$search = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM educator WHERE 1=1";
$params = [];

if ($selectedState) {
    $sql .= " AND state LIKE :state";
    $params[':state'] = "%{$selectedState}%";
}
if ($search) {
    $sql .= " AND (name LIKE :q OR headline LIKE :q OR city LIKE :q OR district LIKE :q)";
    $params[':q'] = "%{$search}%";
}

$sql .= " ORDER BY verification_status = 'verified' DESC, rating DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$educators = $stmt->fetchAll();

$states = $pdo->query("SELECT DISTINCT state FROM educator ORDER BY state ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Verified Teachers & Home Tutors Network — Oedu.in';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 28px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px;">
        <div>
            <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
                👨‍🏫 Teacher & Tutor Network of India
            </h2>
            <p style="color: var(--slate-muted); font-size: 0.95rem;">
                Connect with verified home tuition teachers, subject specialists, and online mentors across India.
            </p>
        </div>
        <a href="#register-teacher" class="btn btn-secondary" style="font-size: 0.85rem;">
            + Register as a Teacher / Tutor
        </a>
    </div>
</div>

<!-- Search & Filter Bar -->
<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; margin-bottom: 28px; box-shadow: var(--shadow-sm);">
    <form action="tutor.php" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 100px; gap: 12px; align-items: flex-end;">
        <div>
            <label style="font-size: 0.78rem; font-weight: 700; color: var(--slate-body); display: block; margin-bottom: 4px;">Search Teacher / Subject / City</label>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. Mathematics, Chapra, Priya Sharma, NEET Biology..." style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
        </div>

        <div>
            <label style="font-size: 0.78rem; font-weight: 700; color: var(--slate-body); display: block; margin-bottom: 4px;">Teaching Mode</label>
            <select name="mode" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                <option value="">All Modes</option>
                <option value="home_tuition" <?= $selectedMode == 'home_tuition' ? 'selected' : '' ?>>Home Tuition</option>
                <option value="online" <?= $selectedMode == 'online' ? 'selected' : '' ?>>Online Live Classes</option>
                <option value="centre" <?= $selectedMode == 'centre' ? 'selected' : '' ?>>At Coaching Centre</option>
            </select>
        </div>

        <div>
            <label style="font-size: 0.78rem; font-weight: 700; color: var(--slate-body); display: block; margin-bottom: 4px;">State</label>
            <select name="state" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                <option value="">All States</option>
                <?php foreach($states as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>" <?= $selectedState == $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%; height: 38px;">Filter</button>
        </div>
    </form>
</div>

<!-- Educators Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 36px;">
    <?php if (empty($educators)): ?>
        <div style="grid-column: 1 / -1; background: var(--bg-card); padding: 48px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
            <p style="font-size: 1.1rem; color: var(--slate-muted); margin-bottom: 12px;">No tutors found matching your criteria.</p>
            <a href="tutor.php" class="btn btn-outline">Reset Filter</a>
        </div>
    <?php else: ?>
        <?php foreach($educators as $tutor): 
            $classes = json_decode($tutor['classes_taught'] ?? '[]', true) ?: [];
            $subjects = json_decode($tutor['subjects_taught'] ?? '[]', true) ?: [];
            $langs = json_decode($tutor['languages'] ?? '[]', true) ?: ['Hindi', 'English'];
        ?>
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 22px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm); transition: transform 0.2s;">
                <div>
                    <div style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 14px;">
                        <div style="width: 52px; height: 52px; border-radius: 12px; background: #FEF3C7; color: #92400E; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                            👨‍🏫
                        </div>
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); line-height: 1.2;">
                                <a href="tutor-detail.php?slug=<?= urlencode($tutor['slug']) ?>"><?= htmlspecialchars($tutor['name']) ?></a>
                            </h3>
                            <div style="font-size: 0.82rem; color: var(--slate-muted); margin: 3px 0;">
                                📍 <?= htmlspecialchars($tutor['city'] ?: $tutor['district']) ?>, <?= htmlspecialchars($tutor['state']) ?>
                            </div>
                            <span class="verified-badge">✓ <?= htmlspecialchars($tutor['badge_label']) ?></span>
                        </div>
                    </div>

                    <div style="font-size: 0.88rem; font-weight: 600; color: var(--charcoal); margin-bottom: 8px;">
                        <?= htmlspecialchars($tutor['headline']) ?>
                    </div>

                    <div style="background: #F8FAF9; padding: 10px; border-radius: 6px; margin-bottom: 12px; font-size: 0.78rem;">
                        <div><strong>Subjects:</strong> <?= htmlspecialchars(implode(', ', $subjects)) ?></div>
                        <div style="margin-top: 3px;"><strong>Classes:</strong> <?= htmlspecialchars(implode(', ', $classes)) ?></div>
                        <div style="margin-top: 3px;"><strong>Languages:</strong> <?= htmlspecialchars(implode(' / ', $langs)) ?></div>
                    </div>

                    <p style="font-size: 0.82rem; color: var(--slate-body); line-height: 1.5; margin-bottom: 14px;">
                        <?= htmlspecialchars(mb_substr($tutor['bio'] ?? '', 0, 110)) ?>...
                    </p>
                </div>

                <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <?php if ($tutor['hourly_fee']): ?>
                            <span style="font-size: 0.92rem; font-weight: 800; color: #03543F;">₹<?= number_format((float) $tutor['hourly_fee']) ?>/hr</span>
                        <?php elseif ($tutor['monthly_fee']): ?>
                            <span style="font-size: 0.92rem; font-weight: 800; color: #03543F;">₹<?= number_format((float) $tutor['monthly_fee']) ?>/mo</span>
                        <?php endif; ?>
                    </div>
                    <a href="tutor-detail.php?slug=<?= urlencode($tutor['slug']) ?>" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.8rem;">
                        View Profile & Timings →
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
