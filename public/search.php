<?php
// search.php — Oedu.in Unified Omnibox Search Engine (.php only)
require_once __DIR__ . '/includes/db.php';

$query = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'all';
$state = trim($_GET['state'] ?? '');

$resources = [];
$educators = [];
$institutions = [];
$examinations = [];
$scholarships = [];

if ($query !== '' || $state !== '' || $type !== 'all') {
    $searchWildcard = "%{$query}%";

    // 1. Resources
    if (in_array($type, ['all', 'learn'])) {
        $stmt = $pdo->prepare("
            SELECT lr.*, b.code as board_code, s.name as subject_name 
            FROM learning_resources lr 
            LEFT JOIN boards b ON lr.board_id = b.id 
            LEFT JOIN subjects s ON lr.subject_id = s.id 
            WHERE lr.title LIKE :q OR lr.title_hi LIKE :q OR lr.class_number LIKE :q OR lr.content_body LIKE :q
            LIMIT 10
        ");
        $stmt->execute([':q' => $searchWildcard]);
        $resources = $stmt->fetchAll();
    }

    // 2. Educators
    if (in_array($type, ['all', 'tutors'])) {
        $stmt = $pdo->prepare("
            SELECT * FROM educators 
            WHERE (name LIKE :q OR headline LIKE :q OR city LIKE :q OR district LIKE :q OR bio LIKE :q)
            " . ($state ? "AND state LIKE :st" : "") . "
            LIMIT 10
        ");
        $params = [':q' => $searchWildcard];
        if ($state) $params[':st'] = "%{$state}%";
        $stmt->execute($params);
        $educators = $stmt->fetchAll();
    }

    // 3. Universities (AISHE table)
    $universitiesList = [];
    if (in_array($type, ['all', 'institutions', 'universities'])) {
        $stmtUni = $pdo->prepare("
            SELECT * FROM university 
            WHERE (name LIKE :q OR aishe_code LIKE :q OR district LIKE :q OR state LIKE :q)
            " . ($state ? "AND state LIKE :st" : "") . "
            LIMIT 20
        ");
        $paramsUni = [':q' => $searchWildcard];
        if ($state) $paramsUni[':st'] = "%{$state}%";
        $stmtUni->execute($paramsUni);
        $universitiesList = $stmtUni->fetchAll();
    }

    // 4. Institutions
    if (in_array($type, ['all', 'institutions'])) {
        $stmt = $pdo->prepare("
            SELECT * FROM institutions 
            WHERE (name LIKE :q OR type LIKE :q OR city LIKE :q OR district LIKE :q)
            " . ($state ? "AND state LIKE :st" : "") . "
            LIMIT 10
        ");
        $params = [':q' => $searchWildcard];
        if ($state) $params[':st'] = "%{$state}%";
        $stmt->execute($params);
        $institutions = $stmt->fetchAll();
    }

    // 4. Exams
    if (in_array($type, ['all', 'exams'])) {
        $stmt = $pdo->prepare("
            SELECT * FROM examinations 
            WHERE title LIKE :q OR code LIKE :q OR category LIKE :q
            LIMIT 10
        ");
        $stmt->execute([':q' => $searchWildcard]);
        $examinations = $stmt->fetchAll();
    }

    // 5. Scholarships
    if (in_array($type, ['all', 'scholarships'])) {
        $stmt = $pdo->prepare("
            SELECT * FROM scholarships 
            WHERE title LIKE :q OR provider_type LIKE :q OR ministry_or_body LIKE :q
            LIMIT 10
        ");
        $stmt->execute([':q' => $searchWildcard]);
        $scholarships = $stmt->fetchAll();
    }
}

$totalResults = count($resources) + count($educators) + count($universitiesList) + count($institutions) + count($examinations) + count($scholarships);

$pageTitle = 'Search Results: ' . ($query ?: 'All Categories') . ' — Oedu.in';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 24px;">
    <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
        Search Results for <span style="color: var(--orange);">"<?= htmlspecialchars($query ?: 'All Categories') ?>"</span>
    </h2>
    <p style="color: var(--slate-muted); font-size: 0.9rem;">
        Found <strong><?= $totalResults ?></strong> matching results across India's educational directory, library, and exams.
    </p>
</div>

<!-- Type Filter Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px; border-bottom: 1px solid var(--border-color);">
    <a href="search.php?q=<?= urlencode($query) ?>&type=all" class="btn <?= $type == 'all' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
        All (<?= $totalResults ?>)
    </a>
    <a href="search.php?q=<?= urlencode($query) ?>&type=universities" class="btn <?= $type == 'universities' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
        🏛️ Universities (<?= count($universitiesList) ?>)
    </a>
    <a href="search.php?q=<?= urlencode($query) ?>&type=institutions" class="btn <?= $type == 'institutions' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
        🏫 Other Institutions (<?= count($institutions) ?>)
    </a>
    <a href="search.php?q=<?= urlencode($query) ?>&type=exams" class="btn <?= $type == 'exams' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
        🎯 Exams (<?= count($examinations) ?>)
    </a>
    <a href="search.php?q=<?= urlencode($query) ?>&type=scholarships" class="btn <?= $type == 'scholarships' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem;">
        💰 Scholarships (<?= count($scholarships) ?>)
    </a>
</div>

<?php if ($totalResults === 0): ?>
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 48px; text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 12px;">🔍</div>
        <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--charcoal); margin-bottom: 8px;">No matching results found</h3>
        <p style="color: var(--slate-muted); font-size: 0.9rem; max-width: 500px; margin: 0 auto 20px;">
            Try checking your spelling or search by general keywords such as <em>"Maths"</em>, <em>"Science"</em>, <em>"Patna"</em>, <em>"CBSE"</em>, or <em>"JEE"</em>.
        </p>
        <a href="index.php" class="btn btn-primary">Return to Homepage</a>
    </div>
<?php endif; ?>

<!-- 0. Universities (National UGC / AISHE Directory) -->
<?php if (!empty($universitiesList)): ?>
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0;">
                🏛️ Universities (<?= count($universitiesList) ?>)
            </h3>
            <a href="institutions.php?type=University" style="font-size: 0.85rem; font-weight: 700; color: var(--orange);">View National Directory →</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php foreach ($universitiesList as $univ): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm); transition: transform 0.2s ease;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.72rem; font-weight: 800; background: #FFF7ED; color: var(--orange); border: 1px solid #FFEDD5; padding: 2px 8px; border-radius: 4px;">
                                AISHE: <?= htmlspecialchars($univ['aishe_code']) ?>
                            </span>
                            <?php if (!empty($univ['year_of_establishment'])): ?>
                                <span style="font-size: 0.72rem; color: var(--slate-muted); font-weight: 600;">
                                    Est. <?= htmlspecialchars($univ['year_of_establishment']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h4 style="font-size: 1rem; font-weight: 700; color: var(--charcoal); line-height: 1.35; margin-bottom: 6px;">
                            <a href="university-detail.php?id=<?= $univ['id'] ?>" style="color: var(--charcoal); text-decoration: none;">
                                <?= htmlspecialchars($univ['name']) ?>
                            </a>
                        </h4>
                        <div style="font-size: 0.82rem; color: var(--slate-muted); margin-bottom: 12px; display: flex; gap: 8px; align-items: center;">
                            <span>📍 <?= htmlspecialchars($univ['district'] ?: 'Headquarters') ?>, <?= htmlspecialchars($univ['state']) ?></span>
                            <?php if (!empty($univ['location'])): ?>
                                <span>• <?= htmlspecialchars($univ['location']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; border-top: 1px solid #F3F4F6; padding-top: 12px;">
                        <a href="university-detail.php?id=<?= $univ['id'] ?>" class="btn btn-primary" style="flex: 1; text-align: center; font-size: 0.82rem; padding: 7px 12px;">
                            View Details
                        </a>
                        <?php if (!empty($univ['website']) && $univ['website'] !== '-'): ?>
                            <?php 
                                $webUrl = $univ['website'];
                                if (!preg_match("~^(?:f|ht)tps?://~i", $webUrl)) $webUrl = "http://" . $webUrl;
                            ?>
                            <a href="<?= htmlspecialchars($webUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.82rem; padding: 7px 12px;" title="Official Website">
                                🌐 Web
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 1. Resources -->
<?php if (!empty($resources)): ?>
    <div style="margin-bottom: 36px;">
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 14px;">
            📚 Learning Library & Books (<?= count($resources) ?>)
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
            <?php foreach ($resources as $res): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:0.75rem; background:var(--primary-light); color:var(--primary); font-weight:700; padding:2px 6px; border-radius:4px;">
                                <?= htmlspecialchars($res['class_number']) ?> • <?= htmlspecialchars($res['subject_name'] ?? 'Core') ?>
                            </span>
                            <?php if ($res['is_official']): ?>
                                <span class="official-badge">🏛️ Official NCERT</span>
                            <?php endif; ?>
                        </div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 6px;">
                            <a href="resource-detail.php?slug=<?= urlencode($res['slug']) ?>" style="color: var(--charcoal);"><?= htmlspecialchars($res['title']) ?></a>
                        </h4>
                        <p style="font-size: 0.8rem; color: var(--slate-muted); line-height: 1.4; margin-bottom: 12px;">
                            <?= htmlspecialchars(mb_substr($res['content_body'] ?? '', 0, 85)) ?>...
                        </p>
                    </div>
                    <a href="resource-detail.php?slug=<?= urlencode($res['slug']) ?>" style="font-size: 0.82rem; font-weight: 700; color: var(--primary);">View Notes & Textbook →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 2. Educators -->
<?php if (!empty($educators)): ?>
    <div style="margin-bottom: 36px;">
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 14px;">
            👨‍🏫 Verified Tutors & Educators (<?= count($educators) ?>)
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php foreach ($educators as $tutor): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; display: flex; gap: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 10px; background: #FEF3C7; color: #92400E; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; flex-shrink: 0;">
                        👨‍🏫
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: var(--charcoal); font-size: 0.95rem;">
                            <a href="tutor-detail.php?slug=<?= urlencode($tutor['slug']) ?>"><?= htmlspecialchars($tutor['name']) ?></a>
                            <span class="verified-badge">✓ Verified</span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--slate-muted); margin: 3px 0;">
                            📍 <?= htmlspecialchars($tutor['city'] ?: $tutor['district']) ?>, <?= htmlspecialchars($tutor['state']) ?> • <?= htmlspecialchars($tutor['qualification']) ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--slate-body); margin-bottom: 8px;">
                            <?= htmlspecialchars(mb_substr($tutor['headline'] ?? '', 0, 70)) ?>
                        </div>
                        <a href="tutor-detail.php?slug=<?= urlencode($tutor['slug']) ?>" class="btn btn-outline" style="padding: 4px 10px; font-size: 0.78rem;">
                            Contact Educator →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 3. Institutions -->
<?php if (!empty($institutions)): ?>
    <div style="margin-bottom: 36px;">
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 14px;">
            🏫 Educational Institutions (<?= count($institutions) ?>)
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php foreach ($institutions as $inst): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; display: flex; gap: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 10px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; flex-shrink: 0;">
                        🏫
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: var(--charcoal); font-size: 0.95rem;">
                            <a href="institution-detail.php?slug=<?= urlencode($inst['slug']) ?>"><?= htmlspecialchars($inst['name']) ?></a>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--slate-muted); margin: 3px 0;">
                            📍 <?= htmlspecialchars($inst['city'] ?: $inst['district']) ?>, <?= htmlspecialchars($inst['state']) ?> • <?= htmlspecialchars($inst['affiliation'] ?? 'Recognized') ?>
                        </div>
                        <a href="institution-detail.php?slug=<?= urlencode($inst['slug']) ?>" class="btn btn-outline" style="padding: 4px 10px; font-size: 0.78rem;">
                            View Campus & Courses →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
