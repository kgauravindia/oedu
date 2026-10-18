<?php
// admin/migrate-law.php — Dedicated Web Database Migrator & Synchronizer for law_college
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Law College Database Synchronizer — Online Education Admin';
$statusMsg = '';
$statusType = 'info';
$log = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_sync'])) {
    $startTime = microtime(true);
    $log[] = "Starting `law_college` table synchronization at " . date('Y-m-d H:i:s') . "...";

    $sqlFile = dirname(__DIR__) . '/law_college.sql';
    if (!file_exists($sqlFile)) {
        $statusMsg = "Error: law_college.sql dump file not found on server at {$sqlFile}";
        $statusType = 'danger';
    } else {
        try {
            // Check if table exists
            $tblCheck = $pdo->query("SHOW TABLES LIKE 'law_college'");
            $tableExists = ($tblCheck->rowCount() > 0);

            if (!$tableExists) {
                $log[] = "Table `law_college` not found. Creating table and importing data from law_college.sql...";
                $sqlDump = file_get_contents($sqlFile);
                $pdo->exec($sqlDump);
                $log[] = "SQL dump imported successfully.";
            } else {
                $log[] = "Table `law_college` exists. Checking schema columns...";

                // Ensure columns are expanded and slug exists
                $colCheck = $pdo->query("SHOW COLUMNS FROM `law_college` LIKE 'slug'");
                if ($colCheck->rowCount() === 0) {
                    $log[] = "Adding `slug` column and updating schema field sizes...";
                    $pdo->exec("ALTER TABLE `law_college` 
                        MODIFY `state` VARCHAR(100) NULL,
                        MODIFY `affiliating_university` VARCHAR(500) NULL,
                        MODIFY `name` VARCHAR(500) NULL,
                        MODIFY `course_name` VARCHAR(255) NULL,
                        MODIFY `approval_till` VARCHAR(150) NULL,
                        MODIFY `e_year` VARCHAR(50) NULL,
                        MODIFY `remarks` TEXT NULL,
                        ADD COLUMN `slug` VARCHAR(255) NULL AFTER `name`,
                        ADD INDEX `idx_law_college_slug` (`slug`),
                        ADD INDEX `idx_law_college_state` (`state`),
                        ADD INDEX `idx_law_college_status` (`status`)
                    ");
                    $log[] = "Schema columns updated.";
                }

                // Clean data
                $del = $pdo->exec("DELETE FROM `law_college` WHERE state = 'State' OR name LIKE '%College Name & Place%'");
                if ($del > 0) $log[] = "Removed {$del} header row(s).";

                // Clean whitespace and newlines
                $pdo->exec("UPDATE `law_college` SET approval_till = REPLACE(REPLACE(approval_till, '-\r\n', '-'), '-\n', '-') WHERE approval_till LIKE '%\n%' OR approval_till LIKE '%\r%'");
                $pdo->exec("UPDATE `law_college` SET approval_till = REPLACE(REPLACE(approval_till, '\r', ' '), '\n', ' ') WHERE approval_till LIKE '%\n%' OR approval_till LIKE '%\r%'");
                $pdo->exec("UPDATE `law_college` SET course_name = REPLACE(REPLACE(course_name, '\r', ' '), '\n', ' ') WHERE course_name LIKE '%\n%' OR course_name LIKE '%\r%'");
                $pdo->exec("UPDATE `law_college` SET affiliating_university = REPLACE(REPLACE(affiliating_university, '\r', ' '), '\n', ' ') WHERE affiliating_university LIKE '%\n%' OR affiliating_university LIKE '%\r%'");
                $pdo->exec("UPDATE `law_college` SET name = REPLACE(REPLACE(name, '\r', ' '), '\n', ' ') WHERE name LIKE '%\n%' OR name LIKE '%\r%'");

                // Normalize states
                $pdo->exec("UPDATE `law_college` SET state = 'Jammu and Kashmir' WHERE state = 'Jammu and Kashm'");
                $pdo->exec("UPDATE `law_college` SET state = 'West Bengal' WHERE state = 'Kolkata'");
                $pdo->exec("UPDATE `law_college` SET state = 'Meghalaya' WHERE state = 'Meghalya'");
                $pdo->exec("UPDATE `law_college` SET state = 'Odisha' WHERE state = 'Orissa/Odisha'");
                $pdo->exec("UPDATE `law_college` SET state = 'Puducherry' WHERE state = 'Pudduchery'");
                $pdo->exec("UPDATE `law_college` SET state = 'Uttarakhand' WHERE state = 'Uttarkhand'");
                $pdo->exec("UPDATE `law_college` SET state = 'Delhi' WHERE state = 'New Delhi'");

                // Populate slugs
                $emptySlugs = (int)$pdo->query("SELECT COUNT(*) FROM `law_college` WHERE slug IS NULL OR slug = ''")->fetchColumn();
                if ($emptySlugs > 0) {
                    $log[] = "Generating slugs for {$emptySlugs} records...";
                    $distinct = $pdo->query("SELECT name, state, affiliating_university FROM `law_college` GROUP BY name, state, affiliating_university")->fetchAll();
                    $upd = $pdo->prepare("UPDATE `law_college` SET slug = :slug WHERE name = :name AND state = :state AND affiliating_university = :univ");
                    $usedSlugs = [];

                    foreach ($distinct as $d) {
                        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', ($d['name'] ?: 'law-college') . ' ' . $d['state']), '-'));
                        $s = $base ?: 'law-college';
                        $cnt = 2;
                        while (isset($usedSlugs[$s])) {
                            $s = $base . '-' . $cnt;
                            $cnt++;
                        }
                        $usedSlugs[$s] = true;
                        $upd->execute([':slug' => $s, ':name' => $d['name'], ':state' => $d['state'], ':univ' => $d['affiliating_university']]);
                    }
                    $log[] = "Slugs generated and assigned.";
                }
            }

            // Stats
            $totalCount = (int)$pdo->query("SELECT COUNT(*) FROM `law_college`")->fetchColumn();
            $slugCount = (int)$pdo->query("SELECT COUNT(DISTINCT slug) FROM `law_college` WHERE slug IS NOT NULL AND slug != ''")->fetchColumn();
            $stateCount = (int)$pdo->query("SELECT COUNT(DISTINCT state) FROM `law_college` WHERE state IS NOT NULL AND state != ''")->fetchColumn();

            $elapsed = round(microtime(true) - $startTime, 2);
            $log[] = "Synchronization completed in {$elapsed}s!";
            $log[] = "Total rows: " . number_format($totalCount);
            $log[] = "Distinct college profiles: " . number_format($slugCount);
            $log[] = "States & UTs covered: " . $stateCount;

            $statusMsg = "Synchronization Successful! Database table `law_college` is ready with " . number_format($totalCount) . " records and " . number_format($slugCount) . " unique college profiles.";
            $statusType = 'success';
        } catch (Throwable $e) {
            $log[] = "FATAL ERROR: " . $e->getMessage();
            $statusMsg = "Migration failed: " . $e->getMessage();
            $statusType = 'danger';
        }
    }
}

// Current status check
$currentTableExists = false;
$currentSlugExists = false;
$currentRowCount = 0;
$currentSlugCount = 0;
try {
    $chk = $pdo->query("SHOW TABLES LIKE 'law_college'");
    $currentTableExists = ($chk->rowCount() > 0);
    if ($currentTableExists) {
        $currentRowCount = (int)$pdo->query("SELECT COUNT(*) FROM `law_college`")->fetchColumn();
        $sc = $pdo->query("SHOW COLUMNS FROM `law_college` LIKE 'slug'");
        $currentSlugExists = ($sc->rowCount() > 0);
        if ($currentSlugExists) {
            $currentSlugCount = (int)$pdo->query("SELECT COUNT(DISTINCT slug) FROM `law_college` WHERE slug IS NOT NULL AND slug != ''")->fetchColumn();
        }
    }
} catch (Throwable $e) {}

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
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #0F172A;">
                        <i class="fas fa-database text-primary me-2"></i> Law College Database Synchronizer
                    </h4>
                    <p class="text-muted small mb-0">Ensure table `law_college` schema, SEO slugs, and 5,470+ BCI records are fully synchronized.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="law-colleges.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Law Colleges
                    </a>
                </div>
            </div>

            <?php if ($statusMsg): ?>
                <div class="alert alert-<?= $statusType ?> alert-dismissible fade show mb-4" role="alert">
                    <h6 class="fw-bold mb-1"><i class="fas <?= $statusType === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i><?= $statusType === 'success' ? 'Success' : 'Notice' ?></h6>
                    <?= htmlspecialchars($statusMsg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Database Current Status</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush mb-3" style="font-size: 0.9rem;">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Table <code>law_college</code> Exists</span>
                                    <span class="badge <?= $currentTableExists ? 'bg-success' : 'bg-danger' ?>"><?= $currentTableExists ? 'YES' : 'NO' ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Column <code>slug</code> Exists</span>
                                    <span class="badge <?= $currentSlugExists ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $currentSlugExists ? 'YES' : 'MISSING' ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Total Rows in Table</span>
                                    <span class="fw-bold text-dark"><?= number_format($currentRowCount) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Unique College Slugs</span>
                                    <span class="fw-bold text-primary"><?= number_format($currentSlugCount) ?></span>
                                </li>
                            </ul>

                            <form method="POST">
                                <input type="hidden" name="run_sync" value="1">
                                <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-bold" onclick="return confirm('Run database migration and synchronization now?');">
                                    <i class="fas fa-play-circle me-2"></i> Run Synchronization Now
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-dark text-white py-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-terminal text-warning me-2"></i>Migration Execution Log</h6>
                        </div>
                        <div class="card-body p-0">
                            <div style="background: #0F172A; color: #38BDF8; font-family: monospace; font-size: 0.85rem; padding: 18px; min-height: 240px; max-height: 380px; overflow-y: auto; white-space: pre-wrap;">
<?php if (empty($log)): ?>
Ready to run. Click "Run Synchronization Now" to execute.
<?php else: ?>
<?= htmlspecialchars(implode("\n", $log)) ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php include __DIR__ . '/admin-footer.php'; ?>
    </div>
</div>

</body>
</html>
