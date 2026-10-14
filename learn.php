<?php
// learn.php — Oedu Learn Digital Library & NCERT Books (.php only)
require_once __DIR__ . '/includes/db.php';

$selectedLevel = trim($_GET['level'] ?? '');
$selectedBoard = trim($_GET['board'] ?? '');
$selectedSubject = trim($_GET['subject'] ?? '');
$selectedType = trim($_GET['type'] ?? '');

$sql = "
    SELECT lr.*, b.code as board_code, b.name as board_name, s.name as subject_name, el.name as level_name
    FROM learning_resource lr
    LEFT JOIN board b ON lr.board_id = b.id
    LEFT JOIN subject s ON lr.subject_id = s.id
    LEFT JOIN education_level el ON lr.education_level_id = el.id
    WHERE 1=1
";
$params = [];

if ($selectedLevel) {
    $sql .= " AND el.slug = :level";
    $params[':level'] = $selectedLevel;
}
if ($selectedBoard) {
    $sql .= " AND b.code = :board";
    $params[':board'] = $selectedBoard;
}
if ($selectedSubject) {
    $sql .= " AND s.slug = :subject";
    $params[':subject'] = $selectedSubject;
}
if ($selectedType) {
    $sql .= " AND lr.resource_type = :type";
    $params[':type'] = $selectedType;
}

$sql .= " ORDER BY lr.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resources = $stmt->fetchAll();

$levels = $pdo->query("SELECT * FROM education_level WHERE is_active = 1 ORDER BY order_index ASC")->fetchAll();
$boards = $pdo->query("SELECT * FROM board ORDER BY id ASC")->fetchAll();
$subjects = $pdo->query("SELECT * FROM subject ORDER BY id ASC")->fetchAll();

$pageTitle = 'Oedu Learn — NCERT Books, Chapter Notes & Practice Tests | Oedu.in';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 32px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px;">
        <div>
            <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
                📚 Oedu Learn: Digital Learning Library
            </h2>
            <p style="color: var(--slate-muted); font-size: 0.95rem; max-width: 720px;">
                Access official NCERT textbook links, original class-wise notes, chapter solutions, and practice question banks for CBSE and State Boards.
            </p>
        </div>
        <div style="font-size:0.82rem; background:#DEF7EC; color:#03543F; padding:6px 12px; border-radius:999px; font-weight:700; border:1px solid #BCF0DA;">
            🏛️ 100% Free Public Learning Access
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; margin-bottom: 28px; box-shadow: var(--shadow-sm);">
    <form action="learn.php" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 120px; gap: 12px; align-items: flex-end;">
        <div>
            <label style="font-size: 0.78rem; font-weight: 700; color: var(--slate-body); display: block; margin-bottom: 4px;">Education Level</label>
            <select name="level" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                <option value="">All Levels</option>
                <?php foreach($levels as $lvl): ?>
                    <option value="<?= htmlspecialchars($lvl['slug']) ?>" <?= $selectedLevel == $lvl['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($lvl['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.78rem; font-weight: 700; color: var(--slate-body); display: block; margin-bottom: 4px;">Curriculum Board</label>
            <select name="board" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                <option value="">All Boards</option>
                <?php foreach($boards as $brd): ?>
                    <option value="<?= htmlspecialchars($brd['code']) ?>" <?= $selectedBoard == $brd['code'] ? 'selected' : '' ?>><?= htmlspecialchars($brd['code']) ?> — <?= htmlspecialchars($brd['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.78rem; font-weight: 700; color: var(--slate-body); display: block; margin-bottom: 4px;">Subject</label>
            <select name="subject" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                <option value="">All Subjects</option>
                <?php foreach($subjects as $sbj): ?>
                    <option value="<?= htmlspecialchars($sbj['slug']) ?>" <?= $selectedSubject == $sbj['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($sbj['name']) ?> (<?= htmlspecialchars($sbj['name_hi']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-size: 0.78rem; font-weight: 700; color: var(--slate-body); display: block; margin-bottom: 4px;">Resource Type</label>
            <select name="type" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                <option value="">All Types</option>
                <option value="ncert_book" <?= $selectedType == 'ncert_book' ? 'selected' : '' ?>>Official Textbook</option>
                <option value="notes" <?= $selectedType == 'notes' ? 'selected' : '' ?>>Revision Notes</option>
                <option value="solution" <?= $selectedType == 'solution' ? 'selected' : '' ?>>Chapter Solutions</option>
                <option value="mcq_quiz" <?= $selectedType == 'mcq_quiz' ? 'selected' : '' ?>>MCQ Practice</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%; height: 38px;">Filter</button>
        </div>
    </form>
</div>

<!-- Resources Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 36px;">
    <?php if (empty($resources)): ?>
        <div style="grid-column: 1 / -1; background: var(--bg-card); padding: 48px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
            <p style="font-size: 1.1rem; color: var(--slate-muted); margin-bottom: 12px;">No resources found for the selected criteria.</p>
            <a href="learn.php" class="btn btn-outline">Clear Filters</a>
        </div>
    <?php else: ?>
        <?php foreach($resources as $res): ?>
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm); transition: transform 0.2s;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.75rem; font-weight: 700; background: var(--primary-light); color: var(--primary); padding: 3px 8px; border-radius: 4px;">
                            <?= htmlspecialchars($res['class_number']) ?> • <?= htmlspecialchars($res['subject_name'] ?? 'General') ?>
                        </span>
                        <?php if ($res['is_official']): ?>
                            <span class="official-badge">🏛️ Official NCERT</span>
                        <?php endif; ?>
                    </div>

                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal); margin-bottom: 8px; line-height: 1.4;">
                        <a href="resource-detail.php?slug=<?= urlencode($res['slug']) ?>"><?= htmlspecialchars($res['title']) ?></a>
                    </h3>

                    <?php if (!empty($res['title_hi'])): ?>
                        <div style="font-family: var(--font-hindi); font-size: 0.85rem; color: var(--slate-muted); margin-bottom: 10px;">
                            <?= htmlspecialchars($res['title_hi']) ?>
                        </div>
                    <?php endif; ?>

                    <p style="font-size: 0.82rem; color: var(--slate-body); line-height: 1.5; margin-bottom: 16px;">
                        <?= htmlspecialchars(mb_substr($res['content_body'] ?? '', 0, 120)) ?>...
                    </p>
                </div>

                <div style="border-top: 1px solid #F1F5F9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.78rem; color: var(--slate-muted);">
                        <?= strtoupper($res['language']) ?> • 👁️ <?= number_format((int) $res['views_count']) ?>
                    </span>
                    <a href="resource-detail.php?slug=<?= urlencode($res['slug']) ?>" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.8rem;">
                        Read Now →
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
