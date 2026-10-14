<?php
// scholarship.php — National & State Scholarships (.php only)
require_once __DIR__ . '/includes/db.php';

$provider = trim($_GET['provider'] ?? '');

$sql = "SELECT * FROM scholarship WHERE is_active = 1";
$params = [];

if ($provider) {
    $sql .= " AND provider_type = :provider";
    $params[':provider'] = $provider;
}

$sql .= " ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$scholarships = $stmt->fetchAll();

$pageTitle = 'National & State Education Scholarships 2026-27 | Oedu.in';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 32px;">
    <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
        💰 National & State Scholarship Tracker
    </h2>
    <p style="color: var(--slate-muted); font-size: 0.95rem;">
        Complete discovery of Indian government welfare scholarships, school merit awards, and doctoral fellowships (PMRF, NMMSS, NSP).
    </p>
</div>

<!-- Provider Filter Pills -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px;">
    <a href="scholarship.php" class="btn <?= empty($provider) ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
        All Scholarships
    </a>
    <a href="scholarship.php?provider=Central+Government" class="btn <?= $provider == 'Central Government' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
        Central Government (MoE)
    </a>
    <a href="scholarship.php?provider=State+Government" class="btn <?= $provider == 'State Government' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
        State Government Schemes
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; margin-bottom: 36px;">
    <?php if (empty($scholarships)): ?>
        <div style="grid-column: 1 / -1; background: var(--bg-card); padding: 48px; border-radius: var(--radius-md); text-align: center;">
            <p style="color: var(--slate-muted);">No scholarships found for this category.</p>
        </div>
    <?php else: ?>
        <?php foreach($scholarships as $sch): ?>
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.75rem; font-weight: 700; background: #DEF7EC; color: #03543F; padding: 3px 8px; border-radius: 4px;">
                            <?= htmlspecialchars($sch['provider_type']) ?>
                        </span>
                        <span style="font-size: 0.75rem; color: #B45309; font-weight: 700;">
                            Deadline: <?= htmlspecialchars($sch['application_deadline'] ?? 'Annual') ?>
                        </span>
                    </div>

                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px; line-height: 1.3;">
                        <?= htmlspecialchars($sch['title']) ?>
                    </h3>

                    <div style="font-size: 0.82rem; color: var(--slate-muted); margin-bottom: 12px;">
                        Department: <strong><?= htmlspecialchars($sch['ministry_or_body'] ?? 'Ministry of Education') ?></strong>
                    </div>

                    <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 12px; margin-bottom: 14px;">
                        <span style="font-size: 0.75rem; color: #166534; font-weight: 700; display: block;">Financial Grant:</span>
                        <strong style="color: #15803D; font-size: 1.05rem;"><?= htmlspecialchars($sch['benefit_amount']) ?></strong>
                    </div>

                    <p style="font-size: 0.82rem; color: var(--slate-body); line-height: 1.5; margin-bottom: 16px;">
                        <strong>Eligibility:</strong> <?= htmlspecialchars($sch['eligibility']) ?>
                    </p>
                </div>

                <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.78rem; color: var(--slate-muted);">Official Scheme Verified</span>
                    <a href="<?= htmlspecialchars($sch['official_portal_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.8rem;">
                        Apply on NSP Portal ↗
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
