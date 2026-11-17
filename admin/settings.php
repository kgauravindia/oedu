<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();
require_once __DIR__ . '/git-helper.php';

$pageTitle = 'Portal Settings - Online Education Admin';
$msg = '';
$msgType = 'success';
$gitLogOutput = '';

$repoDir = dirname(__DIR__);
$githubToken = getSavedGithubToken($pdo);

// Ensure settings table exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS admin_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Defaults
$defaultSettings = [
    'site_name' => 'Online Education',
    'site_tagline' => 'National Higher Education & Institutional Directory',
    'site_url' => 'https://oedu.in',
    'contact_email' => 'contact@oedu.in',
    'contact_phone' => '+91 98765 43210',
    'whatsapp_number' => '+919876543210',
    'facebook_url' => 'https://www.facebook.com/oeduin',
    'enable_inquiries' => '1',
    'meta_description' => 'Comprehensive national database of Universities, Colleges, Standalone Institutions, CBSE Schools, and BCI Law Colleges of India.',
    'seo_keywords' => 'Universities in India, AISHE Colleges, CBSE Schools, BCI Law Colleges, Standalone Institutes, Entrance Exams, CUET Cutoff, NIRF Ranking, Higher Education India',
    'google_site_verification' => '',
    'bing_site_verification' => '',
    'google_analytics_id' => 'G-HN2E45GF5T',
];

// Handle Actions (Portal Settings or Git System Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['git_action'])) {
        $gitAct = $_POST['git_action'];
        if ($gitAct === 'check_updates') {
            $fetchRes = runGitCommand('git fetch origin', $repoDir, $githubToken);
            $gitLogOutput = $fetchRes['output'] ?: 'Remote references successfully refreshed.';
            $msg = ($fetchRes['code'] === 0) 
                ? "Update check completed. Remote repository checked for updates." 
                : "Failed to check remote updates. Check internet or credentials.";
            $msgType = ($fetchRes['code'] === 0) ? 'success' : 'danger';
        } elseif ($gitAct === 'update_now') {
            $summary = getGitSummary($repoDir, $githubToken);
            $branch = $summary['branch'] ?? 'main';
            $updateRes = performGitUpdate($branch, $githubToken, true, $repoDir);
            $gitLogOutput = $updateRes['log'];
            $msg = $updateRes['message'];
            $msgType = $updateRes['success'] ? 'success' : 'danger';
        }
    } else {
        foreach ($defaultSettings as $k => $v) {
            $val = trim($_POST[$k] ?? '');
            $stmt = $pdo->prepare("
                INSERT INTO admin_settings (setting_key, setting_value) 
                VALUES (:k, :v) 
                ON DUPLICATE KEY UPDATE setting_value = :v2
            ");
            $stmt->execute([':k' => $k, ':v' => $val, ':v2' => $val]);
        }
        $msg = "Portal settings have been updated successfully.";
    }
}

// Load current settings from database
$dbSettings = $pdo->query("SELECT setting_key, setting_value FROM admin_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$settings = array_merge($defaultSettings, $dbSettings);

// Load Git Summary for Version Control Card
$gitSummary = getGitSummary($repoDir, $githubToken);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/admin-menu.php'; ?>

    <div style="display: flex; flex-direction: column; min-width: 0; width: 100%;">
        <?php include __DIR__ . '/admin-header.php'; ?>

        <main class="admin-main">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #0F172A;">
                        <i class="fas fa-sliders-h text-info me-2"></i> Portal Settings &amp; Configuration
                    </h4>
                    <p class="text-muted small mb-0">Global website parameters and contact gateway configuration</p>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?> alert-dismissible fade show alert-auto-dismiss" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="data-table-card p-4">
                        <form method="POST">
                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                                <i class="fas fa-globe text-primary me-2"></i> General Portal Information
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Portal Title / Brand Name</label>
                                    <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Tagline</label>
                                    <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars($settings['site_tagline']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($settings['meta_description']) ?></textarea>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                                <i class="fas fa-address-book text-warning me-2"></i> Contact &amp; Support Channels
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Support Email Address</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Support Phone Number</label>
                                    <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($settings['contact_phone']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">WhatsApp Helpline Number</label>
                                    <input type="text" name="whatsapp_number" class="form-control" value="<?= htmlspecialchars($settings['whatsapp_number']) ?>" placeholder="+919876543210">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Official Facebook Page</label>
                                    <input type="url" name="facebook_url" class="form-control" value="<?= htmlspecialchars($settings['facebook_url']) ?>" placeholder="https://www.facebook.com/oeduin">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Student Admission Leads</label>
                                    <select name="enable_inquiries" class="form-select">
                                        <option value="1" <?= $settings['enable_inquiries'] === '1' ? 'selected' : '' ?>>Enabled (Accept Public Leads)</option>
                                        <option value="0" <?= $settings['enable_inquiries'] === '0' ? 'selected' : '' ?>>Disabled</option>
                                    </select>
                                </div>
                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                                <i class="fas fa-search-location text-success me-2"></i> SEO, Google Search Console &amp; Verification
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-dark">Canonical Domain URL</label>
                                    <input type="url" name="site_url" class="form-control font-monospace" value="<?= htmlspecialchars($settings['site_url'] ?? 'https://oedu.in') ?>" placeholder="https://oedu.in" required>
                                    <small class="text-muted">Master canonical domain used for XML sitemaps, canonical links, and search indexing.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">
                                        Google Search Console Verification Token
                                        <a href="https://search.google.com/search-console" target="_blank" class="small ms-1 text-decoration-none">Open GSC ↗</a>
                                    </label>
                                    <input type="text" name="google_site_verification" class="form-control font-monospace" value="<?= htmlspecialchars($settings['google_site_verification'] ?? '') ?>" placeholder="e.g. AbCdEfGhIjKlMnOpQrStUvWxYz0123456789">
                                    <small class="text-muted">Value from <code>&lt;meta name="google-site-verification" content="..."&gt;</code>.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">
                                        Bing Webmaster Verification Code
                                        <a href="https://www.bing.com/webmasters" target="_blank" class="small ms-1 text-decoration-none">Open Bing ↗</a>
                                    </label>
                                    <input type="text" name="bing_site_verification" class="form-control font-monospace" value="<?= htmlspecialchars($settings['bing_site_verification'] ?? '') ?>" placeholder="e.g. 1234567890ABCDEF1234567890ABCDEF">
                                    <small class="text-muted">Value from <code>&lt;meta name="msvalidate.01" content="..."&gt;</code>.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Google Analytics 4 Measurement ID</label>
                                    <input type="text" name="google_analytics_id" class="form-control font-monospace" value="<?= htmlspecialchars($settings['google_analytics_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX">
                                    <small class="text-muted">Optional: GA4 measurement tag to track live organic search traffic.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Default Educational SEO Keywords</label>
                                    <input type="text" name="seo_keywords" class="form-control" value="<?= htmlspecialchars($settings['seo_keywords'] ?? '') ?>" placeholder="colleges, universities, schools, etc.">
                                    <small class="text-muted">Comma-separated keywords for meta tags.</small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary-custom px-4 py-2">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- System Update & Version Control Card -->
                    <div class="data-table-card p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fab fa-git-alt text-danger me-2"></i> System &amp; Git Updates
                            </h6>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                <?= htmlspecialchars($gitSummary['version']) ?>
                            </span>
                        </div>

                        <ul class="list-unstyled small mb-3">
                            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span class="text-muted">Active Branch:</span>
                                <code><?= htmlspecialchars($gitSummary['branch']) ?></code>
                            </li>
                            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span class="text-muted">Latest Commit:</span>
                                <span class="font-monospace small text-primary fw-bold">
                                    <?= htmlspecialchars($gitSummary['last_commit']['hash'] ?: 'None') ?>
                                </span>
                            </li>
                            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span class="text-muted">Sync Status:</span>
                                <?php if ($gitSummary['behind'] > 0): ?>
                                    <span class="badge bg-danger rounded-pill px-2">
                                        <?= $gitSummary['behind'] ?> commits behind
                                    </span>
                                <?php elseif ($gitSummary['ahead'] > 0): ?>
                                    <span class="badge bg-info rounded-pill px-2">
                                        <?= $gitSummary['ahead'] ?> unpushed
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="fas fa-check-circle me-1"></i> Up to date
                                    </span>
                                <?php endif; ?>
                            </li>
                            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
                                <span class="text-muted">Working Tree:</span>
                                <span>
                                    <?= count($gitSummary['changed_files']) ?> <?= count($gitSummary['changed_files']) === 1 ? 'file changed' : 'files changed' ?>
                                </span>
                            </li>
                        </ul>

                        <?php if ($gitLogOutput): ?>
                            <div class="mb-3 p-2 bg-dark text-light rounded small font-monospace" style="font-size: 0.76rem; max-height: 140px; overflow-y: auto; white-space: pre-wrap;">
                                <?= htmlspecialchars($gitLogOutput) ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <form method="POST">
                                <input type="hidden" name="git_action" value="check_updates">
                                <button type="submit" class="btn btn-outline-secondary btn-sm w-100 fw-bold">
                                    <i class="fas fa-sync-alt me-1"></i> Check for Updates
                                </button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Update system from origin/<?= htmlspecialchars($gitSummary['branch']) ?>? Any local changes will be safely auto-stashed.');">
                                <input type="hidden" name="git_action" value="update_now">
                                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                                    <i class="fas fa-cloud-download-alt me-1"></i> Update System Now
                                    <?php if ($gitSummary['behind'] > 0): ?>
                                        <span class="badge bg-danger ms-1"><?= $gitSummary['behind'] ?></span>
                                    <?php endif; ?>
                                </button>
                            </form>
                            <a href="git.php" class="btn btn-light border btn-sm text-dark fw-bold">
                                <i class="fab fa-git-alt text-danger me-1"></i> Open Version Control Center →
                            </a>
                        </div>
                    </div>

                    <div class="data-table-card p-4">
                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                            <i class="fas fa-server text-secondary me-2"></i> Environment Health
                        </h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="py-2 border-bottom d-flex justify-content-between">
                                <span class="text-muted">PHP Version:</span>
                                <strong><?= PHP_VERSION ?></strong>
                            </li>
                            <li class="py-2 border-bottom d-flex justify-content-between">
                                <span class="text-muted">Server Software:</span>
                                <strong><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Apache/PHP') ?></strong>
                            </li>
                            <li class="py-2 border-bottom d-flex justify-content-between">
                                <span class="text-muted">Local Time:</span>
                                <strong><?= date('d M Y, H:i') ?></strong>
                            </li>
                            <li class="py-2 d-flex justify-content-between">
                                <span class="text-muted">Session Status:</span>
                                <span class="text-success fw-bold"><i class="fas fa-check-circle"></i> Active</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

<?php include __DIR__ . '/admin-footer.php'; ?>
