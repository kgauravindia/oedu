<?php
// exams.php — National Entrance Exams Calendar (.php only)
require_once __DIR__ . '/includes/db.php';

$category = trim($_GET['category'] ?? '');

$sql = "SELECT * FROM examinations WHERE 1=1";
$params = [];

if ($category) {
    $sql .= " AND category = :category";
    $params[':category'] = $category;
}

$sql .= " ORDER BY is_featured DESC, id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$exams = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM examinations ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'National Entrance Exams Calendar 2027 — JEE, NEET, CUET, UPSC | Oedu.in';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 32px;">
    <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
        🎯 National Entrance Examination Calendar
    </h2>
    <p style="color: var(--slate-muted); font-size: 0.95rem;">
        Verified schedules, application deadlines, syllabus summaries, and direct links to official examination conducting bodies (NTA, UPSC, CBSE).
    </p>
</div>

<!-- Category Filter Pills -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px;">
    <a href="exams.php" class="btn <?= empty($category) ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
        All Exams
    </a>
    <?php foreach($categories as $cat): ?>
        <a href="exams.php?category=<?= urlencode($cat) ?>" class="btn <?= $category == $cat ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
            <?= htmlspecialchars($cat) ?>
        </a>
    <?php endforeach; ?>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; margin-bottom: 36px;">
    <?php if (empty($exams)): ?>
        <div style="grid-column: 1 / -1; background: var(--bg-card); padding: 48px; border-radius: var(--radius-md); text-align: center;">
            <p style="color: var(--slate-muted);">No examinations found for this category.</p>
        </div>
    <?php else: ?>
        <?php foreach($exams as $exam): ?>
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.75rem; font-weight: 700; background: #E0E7FF; color: #3730A3; padding: 3px 8px; border-radius: 4px;">
                            <?= htmlspecialchars($exam['category']) ?>
                        </span>
                        <span style="font-size: 0.75rem; font-weight: 700; background: var(--primary-light); color: var(--primary); padding: 3px 8px; border-radius: 4px;">
                            <?= htmlspecialchars($exam['code']) ?>
                        </span>
                    </div>

                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px; line-height: 1.3;">
                        <?= htmlspecialchars($exam['title']) ?>
                    </h3>

                    <div style="font-size: 0.82rem; color: var(--slate-muted); margin-bottom: 12px;">
                        Conducting Authority: <strong><?= htmlspecialchars($exam['conducting_body']) ?></strong>
                    </div>

                    <div style="background: #F8FAF9; padding: 12px; border-radius: 8px; margin-bottom: 14px; font-size: 0.8rem; display: flex; flex-direction: column; gap: 4px;">
                        <div><strong>Eligibility:</strong> <?= htmlspecialchars($exam['eligibility']) ?></div>
                        <div><strong>Frequency:</strong> <?= htmlspecialchars($exam['frequency']) ?></div>
                        <div style="color: #B45309;"><strong>Exam Date:</strong> <?= htmlspecialchars($exam['exam_date']) ?></div>
                    </div>

                    <p style="font-size: 0.82rem; color: var(--slate-body); line-height: 1.5; margin-bottom: 16px;">
                        <strong>Syllabus Scope:</strong> <?= htmlspecialchars($exam['syllabus_summary']) ?>
                    </p>
                </div>

                <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <a href="learn.php" style="font-size: 0.82rem; font-weight: 700; color: var(--slate-body);">
                        Browse Prep Notes →
                    </a>
                    <a href="<?= htmlspecialchars($exam['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.8rem;">
                        Official Portal ↗
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
