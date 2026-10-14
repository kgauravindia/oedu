<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Portal Settings - Online Education Admin';
$msg = '';
$msgType = 'success';

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
    'contact_email' => 'contact@oedu.in',
    'contact_phone' => '+91 98765 43210',
    'whatsapp_number' => '+919876543210',
    'facebook_url' => 'https://www.facebook.com/oeduin',
    'enable_inquiries' => '1',
    'meta_description' => 'Comprehensive national database of Universities, Colleges, and Standalone Institutions of India.'
];

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

// Load current settings from database
$dbSettings = $pdo->query("SELECT setting_key, setting_value FROM admin_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$settings = array_merge($defaultSettings, $dbSettings);
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
                            </div>

                            <button type="submit" class="btn btn-primary-custom px-4 py-2">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
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
