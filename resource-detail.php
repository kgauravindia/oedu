<?php
// resource-detail.php — Oedu Learn Chapter & Textbook Reader (.php only)
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    header("Location: learn.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT lr.*, b.code as board_code, b.name as board_name, s.name as subject_name
    FROM learning_resource lr
    LEFT JOIN board b ON lr.board_id = b.id
    LEFT JOIN subject s ON lr.subject_id = s.id
    WHERE lr.slug = :slug
    LIMIT 1
");
$stmt->execute([':slug' => $slug]);
$resource = $stmt->fetch();

if (!$resource) {
    die("Resource not found. <a href='learn.php'>Return to Library</a>");
}

// Increment view count
$pdo->prepare("UPDATE learning_resource SET views_count = views_count + 1 WHERE id = :id")->execute([':id' => $resource['id']]);

// Related resources
$relStmt = $pdo->prepare("
    SELECT * FROM learning_resource 
    WHERE subject_id = :sid AND id != :id 
    LIMIT 3
");
$relStmt->execute([':sid' => $resource['subject_id'], ':id' => $resource['id']]);
$relatedResources = $relStmt->fetchAll();

$pageTitle = $resource['title'] . ' — Oedu Learn | Oedu.in';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="learn.php" style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">
        ← Back to Oedu Learn Library
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)) 340px; gap: 32px; align-items: start;">
    <!-- Main Content -->
    <article style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 12px; flex-wrap: wrap;">
            <span style="font-size: 0.78rem; font-weight: 700; background: var(--primary-light); color: var(--primary); padding: 3px 10px; border-radius: 4px;">
                <?= htmlspecialchars($resource['class_number']) ?> • <?= htmlspecialchars($resource['subject_name'] ?? 'Core') ?>
            </span>
            <span style="font-size: 0.78rem; background: #E2E8F0; color: #334155; padding: 3px 10px; border-radius: 4px; font-weight: 600;">
                <?= htmlspecialchars($resource['board_code'] ?? 'All Boards') ?>
            </span>
            <?php if ($resource['is_official']): ?>
                <span class="official-badge">🏛️ Official NCERT Portal Resource</span>
            <?php endif; ?>
        </div>

        <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--charcoal); line-height: 1.3; margin-bottom: 8px;">
            <?= htmlspecialchars($resource['title']) ?>
        </h1>

        <?php if (!empty($resource['title_hi'])): ?>
            <div style="font-family: var(--font-hindi); font-size: 1.15rem; color: var(--slate-muted); margin-bottom: 20px;">
                <?= htmlspecialchars($resource['title_hi']) ?>
            </div>
        <?php endif; ?>

        <!-- Source & Compliance Banner -->
        <?php if (!empty($resource['official_source_url'])): ?>
            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: var(--radius-md); padding: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                <div>
                    <strong style="color: #166534; font-size: 0.9rem; display: block;">Authorized Educational Source Link</strong>
                    <span style="font-size: 0.82rem; color: #15803D;">Direct official textbook portal provided by NCERT / Ministry of Education.</span>
                </div>
                <a href="<?= htmlspecialchars($resource['official_source_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="padding: 8px 18px; font-size: 0.85rem;">
                    Open Official Textbook ↗
                </a>
            </div>
        <?php endif; ?>

        <!-- Body / Chapter Explanation -->
        <div style="font-size: 0.95rem; line-height: 1.8; color: var(--slate-body); margin-bottom: 32px;">
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin-bottom: 12px; border-bottom: 2px solid var(--primary-light); padding-bottom: 6px;">
                Chapter Overview & Key Study Notes
            </h3>
            <p style="white-space: pre-line; margin-bottom: 20px;">
                <?= nl2br(htmlspecialchars($resource['content_body'])) ?>
            </p>

            <div style="background: #F8FAFC; border-left: 4px solid var(--secondary); padding: 16px; border-radius: 0 var(--radius-md) var(--radius-md) 0; margin-top: 24px;">
                <strong style="color: var(--charcoal); font-size: 0.9rem;">💡 Revision Tip for Board & Competitive Exams:</strong>
                <p style="font-size: 0.85rem; color: var(--slate-body); margin-top: 4px;">
                    Make sure to thoroughly solve in-text exercise problems and NCERT Exemplar questions after reading this chapter summary. Consistent conceptual practice guarantees 90%+ scores in CBSE and state board exams.
                </p>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 16px; font-size: 0.85rem; color: var(--slate-muted);">
            <span>👁️ <?= number_format((int) $resource['views_count']) ?> Total Reads</span>
            <span>Language: <strong><?= strtoupper($resource['language']) ?></strong></span>
        </div>
    </article>

    <!-- Sidebar -->
    <aside style="display: flex; flex-direction: column; gap: 24px;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
            <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal); margin-bottom: 6px;">Need Help with <?= htmlspecialchars($resource['subject_name'] ?? 'Studies') ?>?</h4>
            <p style="font-size: 0.82rem; color: var(--slate-muted); margin-bottom: 16px;">
                Connect with verified local home tutors or 1-on-1 online mentors specializing in <?= htmlspecialchars($resource['class_number']) ?>.
            </p>
            <a href="tutor.php" class="btn btn-primary" style="width: 100%; font-size: 0.85rem;">
                Find <?= htmlspecialchars($resource['subject_name'] ?? '') ?> Tutors →
            </a>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
            <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal); margin-bottom: 12px;">Related Study Notes</h4>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach($relatedResources as $rel): ?>
                    <div style="border-bottom: 1px solid #F1F5F9; padding-bottom: 10px;">
                        <a href="resource-detail.php?slug=<?= urlencode($rel['slug']) ?>" style="font-size: 0.88rem; font-weight: 600; color: var(--charcoal); display: block; line-height: 1.4;">
                            <?= htmlspecialchars($rel['title']) ?>
                        </a>
                        <span style="font-size: 0.75rem; color: var(--slate-muted);"><?= htmlspecialchars($rel['class_number']) ?> • 👁️ <?= number_format((int) $rel['views_count']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
