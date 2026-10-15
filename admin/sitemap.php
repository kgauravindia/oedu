<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'XML Sitemap Manager — Online Education Admin';
$msg = '';
$msgType = 'success';

$rootDir = dirname(__DIR__);
$baseUrl = 'https://oedu.in';

// Quick Counts
$uniCount = (int)$pdo->query("SELECT COUNT(*) FROM university WHERE slug IS NOT NULL AND slug != ''")->fetchColumn();
$collegeCount = (int)$pdo->query("SELECT COUNT(*) FROM college WHERE slug IS NOT NULL AND slug != ''")->fetchColumn();
$instituteCount = (int)$pdo->query("SELECT COUNT(*) FROM institute WHERE slug IS NOT NULL AND slug != ''")->fetchColumn();
$schoolCount = (int)$pdo->query("SELECT COUNT(*) FROM `school`")->fetchColumn();
$totalUrls = $uniCount + $collegeCount + $instituteCount + $schoolCount + 14; // 14 core portal pages

// File definitions
$sitemapFiles = [
    'sitemap.xml' => ['title' => 'Master Sitemap Index', 'type' => 'index', 'urls' => 7],
    'sitemap-main.xml' => ['title' => 'Core Portal Pages', 'type' => 'main', 'urls' => 14],
    'sitemap-universities.xml' => ['title' => 'All Universities (/university/)', 'type' => 'universities', 'urls' => $uniCount],
    'sitemap-colleges-1.xml' => ['title' => 'Colleges Part 1 (1–25,000)', 'type' => 'colleges-1', 'urls' => min(25000, $collegeCount)],
    'sitemap-colleges-2.xml' => ['title' => 'Colleges Part 2 (25,001–50,000)', 'type' => 'colleges-2', 'urls' => max(0, min(25000, $collegeCount - 25000))],
    'sitemap-colleges-3.xml' => ['title' => 'Colleges Part 3 (50,001+)', 'type' => 'colleges-3', 'urls' => max(0, $collegeCount - 50000)],
    'sitemap-institutes.xml' => ['title' => 'Standalone Institutes (/institute/)', 'type' => 'institutes', 'urls' => $instituteCount],
    'sitemap-schools.xml' => ['title' => 'CBSE Schools (/school-detail.php?id=)', 'type' => 'schools', 'urls' => $schoolCount]
];

define('SITEMAP_INTERNAL_CALL', true);
require_once $rootDir . '/sitemap.php';

// Handle File Generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_static') {
    $startTime = microtime(true);
    $generatedCount = 0;
    $errors = [];

    foreach ($sitemapFiles as $fileName => $meta) {
        $filePath = $rootDir . DIRECTORY_SEPARATOR . $fileName;
        try {
            $xmlContent = generateSitemapXmlContent($meta['type'], $baseUrl, $pdo);
            if ($xmlContent && file_put_contents($filePath, $xmlContent)) {
                $generatedCount++;
            } else {
                $errors[] = "Failed to write {$fileName}";
            }
        } catch (Exception $e) {
            $errors[] = "Error generating {$fileName}: " . $e->getMessage();
        }
    }

    $duration = round(microtime(true) - $startTime, 2);
    if (empty($errors)) {
        $msg = "All {$generatedCount} Sitemap XML files successfully generated & updated in {$duration}s!";
        $msgType = 'success';
    } else {
        $msg = "Generated with issues: " . implode(', ', $errors);
        $msgType = 'warning';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Admin Custom CSS -->
    <link href="admin.css" rel="stylesheet">
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/admin-menu.php'; ?>

    <div style="display: flex; flex-direction: column; min-width: 0; width: 100%;">
        <?php include __DIR__ . '/admin-header.php'; ?>

        <main class="admin-main">
            <!-- Header bar -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #0F172A;">
                        <i class="fas fa-sitemap text-warning me-2"></i> XML Sitemap &amp; SEO Engine
                    </h4>
                    <p class="text-muted small mb-0">
                        Manage search engine sitemap indexes covering <?= number_format($totalUrls) ?> clean URLs across India.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="generate_static">
                        <button type="submit" class="btn btn-primary-custom btn-sm px-3 shadow-sm">
                            <i class="fas fa-sync-alt me-1"></i> Rebuild All Sitemap XML Files
                        </button>
                    </form>
                    <a href="../sitemap.xml" target="_blank" class="btn btn-outline-dark btn-sm px-3">
                        <i class="fas fa-external-link-alt me-1"></i> Open Live sitemap.xml
                    </a>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?> alert-auto-dismiss d-flex align-items-center shadow-sm mb-4" role="alert">
                    <i class="fas <?= $msgType === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2 fs-5"></i>
                    <div><?= htmlspecialchars($msg) ?></div>
                </div>
            <?php endif; ?>

            <!-- Metrics -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="stat-card">
                        <div>
                            <div class="stat-title">Total URLs Indexed</div>
                            <div class="stat-value"><?= number_format($totalUrls) ?></div>
                            <span class="stat-badge" style="background: #FFF7ED; color: #EA580C;">
                                100% Clean Routing
                            </span>
                        </div>
                        <div class="stat-icon" style="background: #FFF7ED; color: #FF6B00;">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="stat-card">
                        <div>
                            <div class="stat-title">Universities in Sitemap</div>
                            <div class="stat-value"><?= number_format($uniCount) ?></div>
                            <span class="stat-badge" style="background: #EFF6FF; color: #2563EB;">
                                /university/{slug}
                            </span>
                        </div>
                        <div class="stat-icon" style="background: #EFF6FF; color: #3B82F6;">
                            <i class="fas fa-university"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="stat-card">
                        <div>
                            <div class="stat-title">Colleges in Sitemap</div>
                            <div class="stat-value"><?= number_format($collegeCount) ?></div>
                            <span class="stat-badge" style="background: #FEF3C7; color: #D97706;">
                                /college/{slug}
                            </span>
                        </div>
                        <div class="stat-icon" style="background: #FEF3C7; color: #F59E0B;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="stat-card">
                        <div>
                            <div class="stat-title">Institutes in Sitemap</div>
                            <div class="stat-value"><?= number_format($instituteCount) ?></div>
                            <span class="stat-badge" style="background: #F0FDF4; color: #16A34A;">
                                /institute/{slug}
                            </span>
                        </div>
                        <div class="stat-icon" style="background: #F0FDF4; color: #10B981;">
                            <i class="fas fa-building-columns"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="stat-card">
                        <div>
                            <div class="stat-title">CBSE Schools in Sitemap</div>
                            <div class="stat-value"><?= number_format($schoolCount) ?></div>
                            <span class="stat-badge" style="background: #FAF5FF; color: #7E22CE;">
                                /school-detail.php?id=
                            </span>
                        </div>
                        <div class="stat-icon" style="background: #FAF5FF; color: #9333EA;">
                            <i class="fas fa-school"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table of Sitemap XML Files -->
            <div class="data-table-card mb-4">
                <div class="table-card-header">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-list-check text-primary me-2"></i> Official Google XML Sitemap Files
                        </h6>
                        <small class="text-muted">Chunked according to standard Google 50,000 URL limit specifications.</small>
                    </div>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="generate_static">
                        <button type="submit" class="btn btn-sm btn-outline-primary fw-bold">
                            <i class="fas fa-sync-alt me-1"></i> Update All Files
                        </button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Sitemap File</th>
                                <th>Target Content</th>
                                <th>Estimated URLs</th>
                                <th>Physical Status</th>
                                <th>File Size</th>
                                <th>Last Modified</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sitemapFiles as $fileName => $meta): 
                                $filePath = $rootDir . DIRECTORY_SEPARATOR . $fileName;
                                $exists = file_exists($filePath);
                                $fileSize = $exists ? round(filesize($filePath) / 1024, 1) . ' KB' : 'Dynamic Route';
                                $mtime = $exists ? date('Y-m-d H:i', filemtime($filePath)) : 'On the fly';
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <i class="fas fa-file-code text-warning me-2"></i><?= htmlspecialchars($fileName) ?>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars("{$baseUrl}/{$fileName}") ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-bold"><?= htmlspecialchars($meta['title']) ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark"><?= number_format($meta['urls']) ?> URLs</span>
                                    </td>
                                    <td>
                                        <?php if ($exists): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">
                                                <i class="fas fa-check-circle me-1"></i> Cached Static
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2">
                                                <i class="fas fa-bolt me-1"></i> Live Streamed
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small class="text-muted fw-bold"><?= $fileSize ?></small></td>
                                    <td><small class="text-muted"><?= $mtime ?></small></td>
                                    <td style="text-align: right;">
                                        <div class="btn-group btn-group-sm">
                                            <a href="../<?= htmlspecialchars($fileName) ?>" target="_blank" class="btn btn-light border py-1 px-2" title="Inspect XML">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                            <a href="../<?= htmlspecialchars($fileName) ?>" download="<?= htmlspecialchars($fileName) ?>" class="btn btn-light border py-1 px-2" title="Download XML">
                                                <i class="fas fa-download text-secondary"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SEO & Search Engine Ping Guide -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="stat-card h-100 flex-column align-items-stretch">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="stat-icon" style="background: #EFF6FF; color: #2563EB; width: 40px; height: 40px; font-size: 1.1rem;">
                                <i class="fab fa-google"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Google Search Console Submission</h6>
                                <small class="text-muted">Fast indexing for all 72,000+ institution pages</small>
                            </div>
                        </div>
                        <p class="small text-muted mb-3">
                            Submit your master index URL to Google Search Console to index all child sitemaps automatically:
                        </p>
                        <div class="input-group input-group-sm mb-3">
                            <input type="text" class="form-control font-monospace" id="gscUrl" value="https://oedu.in/sitemap.xml" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('gscUrl').value); alert('Copied to clipboard!');">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <div class="d-flex gap-2 mt-auto">
                            <a href="https://search.google.com/search-console/sitemaps" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary fw-bold">
                                Open Google Search Console ↗
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="stat-card h-100 flex-column align-items-stretch">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="stat-icon" style="background: #F0FDF4; color: #16A34A; width: 40px; height: 40px; font-size: 1.1rem;">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Robots.txt Integration</h6>
                                <small class="text-muted">Verified directive in robots.txt</small>
                            </div>
                        </div>
                        <p class="small text-muted mb-3">
                            Your <a href="../robots.txt" target="_blank" class="fw-bold text-decoration-none">robots.txt</a> file instructs all crawlers to inspect your master sitemap:
                        </p>
                        <pre class="bg-light p-2 rounded small text-dark border mb-0" style="font-family: monospace;">User-agent: *
Disallow: /admin/
Disallow: /api/

Sitemap: https://oedu.in/sitemap.xml</pre>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include __DIR__ . '/admin-footer.php'; ?>
