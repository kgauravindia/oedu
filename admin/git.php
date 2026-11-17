<?php
// admin/git.php — Git Version Control, Remote Sync, Releases & System Update Manager
require_once __DIR__ . '/auth_check.php';
requireAdmin();
require_once __DIR__ . '/git-helper.php';

$pageTitle = 'Git Version Control & System Update — Online Education Admin';
$msg = '';
$msgType = 'success';
$cmdOutput = '';

$repoDir = dirname(__DIR__);
$githubToken = getSavedGithubToken($pdo);

// Handle AJAX Diff Request
if (isset($_GET['ajax']) && $_GET['ajax'] === 'diff') {
    header('Content-Type: text/plain; charset=utf-8');
    $file = trim($_GET['file'] ?? '');
    echo getGitDiff($file, $repoDir);
    exit;
}

// Handle Git Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = trim($_POST['token'] ?? $githubToken);

    if ($action === 'save_token') {
        $tokenVal = trim($_POST['github_token'] ?? '');
        $pdo->prepare("INSERT INTO admin_settings (setting_key, setting_value) VALUES ('github_token', :v) ON DUPLICATE KEY UPDATE setting_value = :v2")
            ->execute([':v' => $tokenVal, ':v2' => $tokenVal]);
        $githubToken = $tokenVal;
        $msg = "GitHub Personal Access Token saved successfully.";
        $msgType = 'success';

    } elseif ($action === 'fetch') {
        $res = runGitCommand('git fetch origin', $repoDir, $token);
        $cmdOutput = "$ git fetch origin\n" . ($res['output'] ?: 'Repository fetched successfully. No new updates found.');
        $msg = ($res['code'] === 0) ? "Git fetch completed. Remote references are up to date." : "Git fetch failed. Check credentials or console output.";
        $msgType = ($res['code'] === 0) ? 'success' : 'danger';

    } elseif ($action === 'update' || $action === 'pull') {
        $branch = trim($_POST['branch'] ?? 'main');
        $autoStash = !empty($_POST['auto_stash']);
        $updateRes = performGitUpdate($branch, $token, $autoStash, $repoDir);
        $cmdOutput = $updateRes['log'];
        $msg = $updateRes['message'];
        $msgType = $updateRes['success'] ? 'success' : 'danger';

    } elseif ($action === 'push') {
        $branch = trim($_POST['branch'] ?? 'main');
        $res = runGitCommand("git push origin {$branch}", $repoDir, $token);
        $cmdOutput = "$ git push origin {$branch}\n" . $res['output'];
        if ($res['code'] === 0) {
            $msg = "Pushed successfully to origin/{$branch}!";
            $msgType = 'success';
        } else {
            $msg = "Git push failed. If GitHub requires authentication, save your Personal Access Token below.";
            $msgType = 'danger';
        }

    } elseif ($action === 'commit_push') {
        $commitMsg = trim($_POST['commit_message'] ?? '');
        $branch = trim($_POST['branch'] ?? 'main');
        
        $statusCheck = runGitCommand('git status -s', $repoDir);
        $hasChanges = !empty(trim($statusCheck['output'] ?? ''));
        $fullLogs = [];
        
        if ($hasChanges) {
            if (empty($commitMsg)) {
                $commitMsg = "System update: " . date('Y-m-d H:i');
            }
            $safeMsg = addslashes($commitMsg);
            
            // 1. git add .
            $addRes = runGitCommand('git add .', $repoDir);
            $fullLogs[] = "$ git add .\n" . ($addRes['output'] ?: 'Changes staged.');
            
            // 2. git commit -m "..."
            $commitRes = runGitCommand("git commit -m \"{$safeMsg}\"", $repoDir);
            $fullLogs[] = "$ git commit -m \"{$safeMsg}\"\n" . $commitRes['output'];
        } else {
            $fullLogs[] = "# Working tree is clean. Proceeding to push pending commits...";
        }
        
        // 3. git push origin branch
        $pushRes = runGitCommand("git push origin {$branch}", $repoDir, $token);
        $fullLogs[] = "$ git push origin {$branch}\n" . $pushRes['output'];
        
        $cmdOutput = implode("\n\n", $fullLogs);

        if ($pushRes['code'] === 0) {
            $msg = $hasChanges 
                ? "Changes committed & pushed to origin/{$branch} successfully!" 
                : "Pending commits pushed to origin/{$branch} successfully!";
            $msgType = 'success';
        } else {
            $msg = "Git push encountered an issue. See console output below.";
            $msgType = 'danger';
        }

    } elseif ($action === 'create_tag') {
        $tagName = trim($_POST['tag_name'] ?? '');
        $tagMsg = trim($_POST['tag_message'] ?? '');
        $pushTag = !empty($_POST['push_tag']);
        
        // Sanitize tag name
        $tagName = preg_replace('/[^a-zA-Z0-9\.\-\_]/', '', $tagName);
        if (empty($tagName)) {
            $msg = "Invalid tag name. Please use a format like v1.0.1";
            $msgType = 'danger';
        } else {
            $safeTagMsg = escapeshellarg($tagMsg ?: "Release {$tagName}");
            $res = runGitCommand("git tag -a {$tagName} -m {$safeTagMsg}", $repoDir);
            $log = "$ git tag -a {$tagName} -m {$safeTagMsg}\n" . ($res['output'] ?: 'Tag created.');
            
            if ($res['code'] === 0) {
                if ($pushTag) {
                    $pushRes = runGitCommand("git push origin {$tagName}", $repoDir, $token);
                    $log .= "\n\n$ git push origin {$tagName}\n" . $pushRes['output'];
                }
                $cmdOutput = $log;
                $msg = "Version release tag '{$tagName}' created successfully" . ($pushTag ? " and pushed to remote!" : "!");
                $msgType = 'success';
            } else {
                $cmdOutput = $log;
                $msg = "Failed to create tag '{$tagName}'. Check console output.";
                $msgType = 'danger';
            }
        }

    } elseif ($action === 'push_tags') {
        $res = runGitCommand("git push origin --tags", $repoDir, $token);
        $cmdOutput = "$ git push origin --tags\n" . $res['output'];
        $msg = ($res['code'] === 0) ? "All version tags pushed to remote." : "Failed to push tags. Check credentials.";
        $msgType = ($res['code'] === 0) ? 'success' : 'warning';

    } elseif ($action === 'checkout') {
        $target = trim($_POST['target'] ?? '');
        $targetClean = preg_replace('/[^a-zA-Z0-9\.\-\_\/]/', '', $target);
        if ($targetClean) {
            $res = runGitCommand("git checkout {$targetClean}", $repoDir);
            $cmdOutput = "$ git checkout {$targetClean}\n" . $res['output'];
            $msg = ($res['code'] === 0) ? "Switched to '{$targetClean}' successfully." : "Checkout failed. Commit or stash your changes first.";
            $msgType = ($res['code'] === 0) ? 'success' : 'danger';
        }

    } elseif ($action === 'create_branch') {
        $newBranch = preg_replace('/[^a-zA-Z0-9\.\-\_]/', '', trim($_POST['branch_name'] ?? ''));
        if ($newBranch) {
            $res = runGitCommand("git checkout -b {$newBranch}", $repoDir);
            $cmdOutput = "$ git checkout -b {$newBranch}\n" . $res['output'];
            $msg = ($res['code'] === 0) ? "Branch '{$newBranch}' created and active." : "Failed to create branch.";
            $msgType = ($res['code'] === 0) ? 'success' : 'danger';
        }

    } elseif ($action === 'discard_file') {
        $file = trim($_POST['file'] ?? '');
        if ($file) {
            $safeFile = escapeshellarg($file);
            $res = runGitCommand("git checkout -- {$safeFile}", $repoDir);
            if ($res['code'] !== 0) {
                $res = runGitCommand("git clean -f -- {$safeFile}", $repoDir);
            }
            $cmdOutput = "Discarded changes in: {$file}\n" . $res['output'];
            $msg = "Changes in '{$file}' discarded.";
            $msgType = 'info';
        }

    } elseif ($action === 'discard_all') {
        $res = runGitCommand('git checkout -- . && git clean -fd', $repoDir);
        $cmdOutput = "$ git checkout -- . && git clean -fd\n" . $res['output'];
        $msg = "All uncommitted changes in working tree discarded.";
        $msgType = 'warning';

    } elseif ($action === 'stash') {
        $res = runGitCommand('git stash', $repoDir);
        $cmdOutput = "$ git stash\n" . $res['output'];
        $msg = "Uncommitted changes stashed.";
        $msgType = 'info';

    } elseif ($action === 'stash_pop') {
        $res = runGitCommand('git stash pop', $repoDir);
        $cmdOutput = "$ git stash pop\n" . $res['output'];
        $msg = "Stashed changes restored.";
        $msgType = 'info';

    } elseif ($action === 'soft_reset') {
        $res = runGitCommand('git reset --soft HEAD~1', $repoDir);
        $cmdOutput = "$ git reset --soft HEAD~1\n" . $res['output'];
        $msg = "Undid last commit. Changes are preserved and staged.";
        $msgType = 'info';
    }
}

// Fetch Full Git State
$summary = getGitSummary($repoDir, $githubToken);
$currentBranch = $summary['branch'];
$currentVersion = $summary['version'];
$remoteUrl = $summary['remote_url'];
$githubBase = $summary['github_base'];
$aheadCount = $summary['ahead'];
$behindCount = $summary['behind'];
$lastCommit = $summary['last_commit'];
$changedFiles = $summary['changed_files'];

$commitHistory = getGitCommitHistory($repoDir, 15);
$tagsList = getGitTagsList($repoDir);
$branchesList = getGitBranchesList($repoDir);
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
    <style>
        .git-hash-badge {
            font-family: monospace;
            font-size: 0.8rem;
            background: #1E293B;
            color: #38BDF8;
            padding: 3px 8px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 700;
        }
        .git-tag-badge {
            font-family: monospace;
            font-size: 0.85rem;
            background: #EEF2FF;
            color: #4F46E5;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid #C7D2FE;
            font-weight: 700;
        }
        .git-status-m { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
        .git-status-q { background: #E0F2FE; color: #075985; border: 1px solid #BAE6FD; }
        .git-status-d { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
        .console-output {
            background: #0B0F19;
            color: #E2E8F0;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.85rem;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #1E293B;
            max-height: 280px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .diff-container {
            background: #0B0F19;
            color: #F8FAFC;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.85rem;
            padding: 1rem;
            border-radius: 8px;
            max-height: 480px;
            overflow: auto;
            white-space: pre-wrap;
            line-height: 1.45;
        }
        .diff-add { color: #4ADE80; background: rgba(74, 222, 128, 0.1); display: block; }
        .diff-del { color: #F87171; background: rgba(248, 113, 113, 0.1); display: block; }
        .diff-hunk { color: #38BDF8; background: rgba(56, 189, 248, 0.1); display: block; font-weight: bold; }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/admin-menu.php'; ?>

    <div style="display: flex; flex-direction: column; min-width: 0; width: 100%;">
        <?php include __DIR__ . '/admin-header.php'; ?>

        <main class="admin-main">
            <!-- Header bar with primary action controls -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #0F172A;">
                        <i class="fab fa-git-alt text-danger me-2"></i> System Updates &amp; Git Version Control
                    </h4>
                    <p class="text-muted small mb-0">Track release versions, inspect modifications, apply single-click system updates, and manage branches.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <!-- Check for updates (Fetch) -->
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="fetch">
                        <button type="submit" class="btn btn-outline-secondary btn-sm px-3 shadow-sm" title="Check for remote commits">
                            <i class="fas fa-sync-alt me-1"></i> Check Updates
                        </button>
                    </form>

                    <!-- One-Click System Update (Pull) -->
                    <form method="POST" onsubmit="return confirm('Update system from origin/<?= htmlspecialchars($currentBranch) ?>? Any uncommitted changes will be safely auto-stashed.');" style="display: inline;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="branch" value="<?= htmlspecialchars($currentBranch) ?>">
                        <input type="hidden" name="auto_stash" value="1">
                        <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm fw-bold">
                            <i class="fas fa-cloud-download-alt me-1"></i> Update System
                            <?php if ($behindCount > 0): ?>
                                <span class="badge bg-danger ms-1"><?= $behindCount ?> New</span>
                            <?php endif; ?>
                        </button>
                    </form>

                    <!-- Git Push -->
                    <form method="POST" onsubmit="return confirm('Push all committed changes to origin/<?= htmlspecialchars($currentBranch) ?>?');" style="display: inline;">
                        <input type="hidden" name="action" value="push">
                        <input type="hidden" name="branch" value="<?= htmlspecialchars($currentBranch) ?>">
                        <button type="submit" class="btn btn-success btn-sm px-3 shadow-sm">
                            <i class="fas fa-cloud-upload-alt me-1"></i> Push
                            <?php if ($aheadCount > 0): ?>
                                <span class="badge bg-white text-success ms-1"><?= $aheadCount ?></span>
                            <?php endif; ?>
                        </button>
                    </form>

                    <!-- Stage, Commit & Push Modal -->
                    <button type="button" class="btn btn-primary-custom btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#commitModal">
                        <i class="fas fa-plus-circle me-1"></i> Commit &amp; Push
                    </button>

                    <!-- Version Tag / Release Modal -->
                    <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createTagModal">
                        <i class="fas fa-tag me-1"></i> Create Release
                    </button>

                    <!-- GitHub Authentication Token -->
                    <button type="button" class="btn btn-outline-dark btn-sm px-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#tokenModal" title="Configure GitHub Authentication Token">
                        <i class="fab fa-github me-1"></i> Token <?= $githubToken ? '✓' : '⚙' ?>
                    </button>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?> alert-dismissible fade show alert-auto-dismiss" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- System Update Notification Banner -->
            <?php if ($behindCount > 0): ?>
                <div class="alert alert-warning py-3 px-3 d-flex flex-wrap align-items-center justify-content-between mb-4 shadow-sm border-warning rounded-3" role="alert">
                    <div class="d-flex align-items-center gap-3">
                        <div style="font-size: 1.8rem; color: #D97706;">
                            <i class="fas fa-arrow-circle-down"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">System Update Available!</h6>
                            <p class="mb-0 small text-muted">
                                Remote repository has <strong><?= $behindCount ?> new commit(s)</strong> ready to be pulled into your installation.
                            </p>
                        </div>
                    </div>
                    <form method="POST" onsubmit="return confirm('Update system to latest version now?');" class="mt-2 mt-md-0">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="branch" value="<?= htmlspecialchars($currentBranch) ?>">
                        <input type="hidden" name="auto_stash" value="1">
                        <button type="submit" class="btn btn-warning btn-sm px-3 fw-bold">
                            <i class="fas fa-download me-1"></i> Apply Update Now (<?= $behindCount ?> commits)
                        </button>
                    </form>
                </div>
            <?php elseif ($aheadCount > 0): ?>
                <div class="alert alert-info py-2 px-3 small d-flex align-items-center justify-content-between mb-4 shadow-sm rounded-3" role="alert">
                    <div>
                        <i class="fas fa-cloud-upload-alt me-2 text-primary"></i>
                        Your local branch has <strong><?= $aheadCount ?> unpushed commit(s)</strong> ready to sync with GitHub remote.
                    </div>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="push">
                        <input type="hidden" name="branch" value="<?= htmlspecialchars($currentBranch) ?>">
                        <button type="submit" class="btn btn-primary btn-sm py-1 px-3 fw-bold">Push <?= $aheadCount ?> commit(s)</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-success py-2 px-3 small d-flex align-items-center justify-content-between mb-4 shadow-sm rounded-3" role="alert">
                    <div>
                        <i class="fas fa-check-circle me-2 text-success"></i>
                        System is up to date on <strong>origin/<?= htmlspecialchars($currentBranch) ?></strong>. (Version: <strong><?= htmlspecialchars($currentVersion) ?></strong>).
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">All Synchronized</span>
                </div>
            <?php endif; ?>

            <!-- Metrics Cards -->
            <div class="row g-3 mb-4">
                <!-- Release Version -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #4F46E5;">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">System Version</span>
                            <h4 class="stat-value text-indigo mb-0 font-monospace" style="color: #4F46E5;"><?= htmlspecialchars($currentVersion) ?></h4>
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;">
                                    <?= count($tagsList) ?> <?= count($tagsList) === 1 ? 'Release Tag' : 'Release Tags' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Branch -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(240, 80, 50, 0.1); color: #F05032;">
                            <i class="fas fa-code-branch"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Active Branch</span>
                            <h4 class="stat-value text-danger mb-0"><?= htmlspecialchars($currentBranch) ?></h4>
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <?php if ($aheadCount === 0 && $behindCount === 0): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">✓ In sync</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.68rem;"><?= $aheadCount ?> ahead, <?= $behindCount ?> behind</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Working Tree Status -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: <?= count($changedFiles) > 0 ? 'rgba(245, 158, 11, 0.1)' : 'rgba(16, 185, 129, 0.1)' ?>; color: <?= count($changedFiles) > 0 ? '#D97706' : '#059669' ?>;">
                            <i class="fas <?= count($changedFiles) > 0 ? 'fa-file-signature' : 'fa-check-double' ?>"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Working Tree Status</span>
                            <h4 class="stat-value <?= count($changedFiles) > 0 ? 'text-warning' : 'text-success' ?> mb-0">
                                <?= count($changedFiles) ?> <?= count($changedFiles) === 1 ? 'Change' : 'Changes' ?>
                            </h4>
                            <small class="text-muted" style="font-size: 0.72rem;">
                                <?= count($changedFiles) > 0 ? 'Uncommitted files detected' : 'Clean working directory' ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Remote Target -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(14, 165, 233, 0.1); color: #0284C7;">
                            <i class="fas fa-server"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Remote Target</span>
                            <h6 class="text-truncate fw-bold mb-0 mt-1" style="font-size: 0.85rem; color: #1E293B;" title="<?= htmlspecialchars($remoteUrl) ?>">
                                <?= htmlspecialchars(basename($remoteUrl, '.git') ?: 'origin') ?>
                            </h6>
                            <?php if ($githubBase): ?>
                                <a href="<?= htmlspecialchars($githubBase) ?>" target="_blank" class="small text-primary text-decoration-none" style="font-size: 0.72rem;">
                                    View on GitHub ↗
                                </a>
                            <?php else: ?>
                                <small class="text-muted" style="font-size: 0.72rem;">Local / Remote</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Command Output Terminal (If an action was run) -->
            <?php if ($cmdOutput): ?>
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="small fw-bold"><i class="fas fa-terminal me-2 text-warning"></i>Execution Console</span>
                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" onclick="this.closest('.card').remove();">&times; Close</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="console-output"><?= htmlspecialchars($cmdOutput) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs -->
            <ul class="nav nav-pills mb-4 gap-2" id="gitTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active px-3 py-2 fw-semibold" id="changes-tab" data-bs-toggle="pill" data-bs-target="#tab-changes" type="button">
                        <i class="fas fa-edit me-1"></i> Working Tree (<?= count($changedFiles) ?>)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-3 py-2 fw-semibold" id="history-tab" data-bs-toggle="pill" data-bs-target="#tab-history" type="button">
                        <i class="fas fa-history me-1"></i> Commit History
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-3 py-2 fw-semibold" id="tags-tab" data-bs-toggle="pill" data-bs-target="#tab-tags" type="button">
                        <i class="fas fa-tags me-1"></i> Release Versions (<?= count($tagsList) ?>)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-3 py-2 fw-semibold" id="branches-tab" data-bs-toggle="pill" data-bs-target="#tab-branches" type="button">
                        <i class="fas fa-code-branch me-1"></i> Branches
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="gitTabsContent">
                <!-- TAB 1: Working Tree Changes -->
                <div class="tab-pane fade show active" id="tab-changes" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-file-code me-2 text-primary"></i>Working Tree Changes (<?= count($changedFiles) ?>)
                                </h6>
                                <small class="text-muted">Modified, untracked, or deleted files since last commit</small>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if (count($changedFiles) > 0): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="stash">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-archive me-1"></i> Stash
                                        </button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Discard ALL uncommitted changes permanently?');" style="display:inline;">
                                        <input type="hidden" name="action" value="discard_all">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-undo me-1"></i> Discard All
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#commitModal">
                                        <i class="fas fa-check me-1"></i> Commit &amp; Push All
                                    </button>
                                <?php else: ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="stash_pop">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Restore stashed changes">
                                            <i class="fas fa-box-open me-1"></i> Pop Stash
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <?php if (empty($changedFiles)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-2" style="font-size: 2.2rem; color: #10B981;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Working Tree is Clean</h6>
                                    <p class="text-muted small mb-0">All files have been committed and synced to git.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="bg-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                                            <tr>
                                                <th class="ps-3">Status</th>
                                                <th>File Path</th>
                                                <th class="text-end pe-3">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($changedFiles as $cf): 
                                                $stat = trim($cf['raw_status']);
                                                $badgeClass = 'git-status-m';
                                                $label = 'Modified';
                                                $canDiff = true;
                                                if (strpos($stat, '?') !== false) {
                                                    $badgeClass = 'git-status-q';
                                                    $label = 'Untracked / New';
                                                    $canDiff = false;
                                                } elseif (strpos($stat, 'D') !== false) {
                                                    $badgeClass = 'git-status-d';
                                                    $label = 'Deleted';
                                                }
                                            ?>
                                                <tr>
                                                    <td class="ps-3" style="width: 140px;">
                                                        <span class="badge <?= $badgeClass ?> px-2 py-1" style="font-size: 0.72rem;">
                                                            <?= $label ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <code class="text-dark fw-bold" style="font-size: 0.82rem;"><?= htmlspecialchars($cf['file']) ?></code>
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <?php if ($canDiff): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-info py-0 px-2 me-1" onclick="viewDiff('<?= htmlspecialchars(addslashes($cf['file'])) ?>')">
                                                                <i class="fas fa-file-diff me-1"></i> Diff
                                                            </button>
                                                        <?php endif; ?>
                                                        <form method="POST" onsubmit="return confirm('Discard changes in <?= htmlspecialchars(addslashes($cf['file'])) ?>?');" style="display:inline;">
                                                            <input type="hidden" name="action" value="discard_file">
                                                            <input type="hidden" name="file" value="<?= htmlspecialchars($cf['file']) ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Revert changes">
                                                                <i class="fas fa-undo"></i> Discard
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Commit History -->
                <div class="tab-pane fade" id="tab-history" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-history me-2 text-info"></i>Commit History &amp; Log
                                </h6>
                                <small class="text-muted">Last <?= count($commitHistory) ?> commits on branch <?= htmlspecialchars($currentBranch) ?></small>
                            </div>
                            <div class="d-flex gap-2">
                                <form method="POST" onsubmit="return confirm('Soft-undo the last commit? (Changes will remain staged in your working directory)');" style="display:inline;">
                                    <input type="hidden" name="action" value="soft_reset">
                                    <button type="submit" class="btn btn-outline-warning btn-sm" title="Undo HEAD commit but keep changes">
                                        <i class="fas fa-undo me-1"></i> Undo Last Commit
                                    </button>
                                </form>
                                <?php if ($githubBase): ?>
                                    <a href="<?= htmlspecialchars($githubBase) ?>/commits/<?= htmlspecialchars($currentBranch) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                        GitHub Commits ↗
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead class="bg-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                                        <tr>
                                            <th class="ps-3">Commit</th>
                                            <th>Description</th>
                                            <th>Author</th>
                                            <th class="text-end pe-3">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($commitHistory)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No commit history found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($commitHistory as $c): ?>
                                                <tr>
                                                    <td class="ps-3" style="width: 90px;">
                                                        <?php if ($githubBase): ?>
                                                            <a href="<?= htmlspecialchars($githubBase) ?>/commit/<?= htmlspecialchars($c['hash']) ?>" target="_blank" class="git-hash-badge">
                                                                <?= htmlspecialchars($c['hash']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="git-hash-badge"><?= htmlspecialchars($c['hash']) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold text-dark text-truncate" style="max-width: 480px;" title="<?= htmlspecialchars($c['subject']) ?>">
                                                            <?= htmlspecialchars($c['subject']) ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted small"><?= htmlspecialchars($c['author']) ?></td>
                                                    <td class="text-end pe-3 text-muted small" style="white-space: nowrap;">
                                                        <span title="<?= htmlspecialchars($c['date'] ?? '') ?>"><?= htmlspecialchars($c['ago']) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Release Versions & Tags -->
                <div class="tab-pane fade" id="tab-tags" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-tags me-2 text-primary"></i>Release Versions &amp; Milestones
                                </h6>
                                <small class="text-muted">Manage system releases, version tags, and rollback targets</small>
                            </div>
                            <div class="d-flex gap-2">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="push_tags">
                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-cloud-upload-alt me-1"></i> Push Tags to Remote
                                    </button>
                                </form>
                                <button type="button" class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#createTagModal">
                                    <i class="fas fa-plus-circle me-1"></i> New Version Tag
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <?php if (empty($tagsList)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-2" style="font-size: 2.2rem; color: #6366F1;">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">No Version Tags Yet</h6>
                                    <p class="text-muted small mb-3">Tag your first stable system version (e.g., v1.0.0).</p>
                                    <button type="button" class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#createTagModal">
                                        Create Version v1.0.0
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="bg-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                                            <tr>
                                                <th class="ps-3">Release Tag</th>
                                                <th>Release Description</th>
                                                <th>Created</th>
                                                <th class="text-end pe-3">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tagsList as $tg): ?>
                                                <tr>
                                                    <td class="ps-3" style="width: 130px;">
                                                        <span class="git-tag-badge">
                                                            <i class="fas fa-tag me-1 small"></i><?= htmlspecialchars($tg['name']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($tg['subject'] ?: 'Release ' . $tg['name']) ?></div>
                                                    </td>
                                                    <td class="text-muted small"><?= htmlspecialchars($tg['date']) ?></td>
                                                    <td class="text-end pe-3">
                                                        <form method="POST" onsubmit="return confirm('Rollback/Switch repository to tag <?= htmlspecialchars($tg['name']) ?>?');" style="display:inline;">
                                                            <input type="hidden" name="action" value="checkout">
                                                            <input type="hidden" name="target" value="<?= htmlspecialchars($tg['name']) ?>">
                                                            <button type="submit" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Switch to this version">
                                                                <i class="fas fa-history me-1"></i> Checkout
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: Branch Management -->
                <div class="tab-pane fade" id="tab-branches" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-code-branch me-2 text-warning"></i>Repository Branches
                                </h6>
                                <small class="text-muted">Switch branches or spawn new feature branches</small>
                            </div>
                            <button type="button" class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#createBranchModal">
                                <i class="fas fa-plus me-1"></i> New Branch
                            </button>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead class="bg-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                                        <tr>
                                            <th class="ps-3">Branch Name</th>
                                            <th>Status</th>
                                            <th class="text-end pe-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($branchesList as $br): ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <code class="fw-bold text-dark" style="font-size: 0.85rem;">
                                                        <i class="fas fa-code-branch me-1 text-muted"></i><?= htmlspecialchars($br['name']) ?>
                                                    </code>
                                                </td>
                                                <td>
                                                    <?php if ($br['is_current']): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                            <i class="fas fa-check-circle me-1"></i> Active
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <?php if (!$br['is_current'] && !str_starts_with($br['name'], 'remotes/')): ?>
                                                        <form method="POST" onsubmit="return confirm('Switch active branch to <?= htmlspecialchars($br['name']) ?>?');" style="display:inline;">
                                                            <input type="hidden" name="action" value="checkout">
                                                            <input type="hidden" name="target" value="<?= htmlspecialchars($br['name']) ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                                Switch
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Commit & Push -->
            <div class="modal fade" id="commitModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" class="modal-content border-0 shadow">
                        <input type="hidden" name="action" value="commit_push">
                        <input type="hidden" name="branch" value="<?= htmlspecialchars($currentBranch) ?>">

                        <div class="modal-header bg-light">
                            <h6 class="modal-title fw-bold text-dark">
                                <i class="fab fa-git-alt text-danger me-2"></i>Commit &amp; Push to origin/<?= htmlspecialchars($currentBranch) ?>
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="fas fa-info-circle me-1"></i> This will stage all files (<code>git add .</code>), record a commit, and execute <code>git push origin <?= htmlspecialchars($currentBranch) ?></code>.
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Commit Message *</label>
                                <input type="text" name="commit_message" required class="form-control" placeholder="e.g. Update portal features and institute directory" value="Update portal features and verified institution directories">
                            </div>

                            <div class="text-muted small">
                                <strong>Pending Files to Commit:</strong> <?= count($changedFiles) ?> change(s)
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom btn-sm px-3">
                                <i class="fas fa-check me-1"></i> Stage, Commit &amp; Push
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal for Creating Version Release Tag -->
            <div class="modal fade" id="createTagModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" class="modal-content border-0 shadow">
                        <input type="hidden" name="action" value="create_tag">

                        <div class="modal-header bg-light">
                            <h6 class="modal-title fw-bold text-dark">
                                <i class="fas fa-tag text-primary me-2"></i>Create Version Release Tag
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="fas fa-info-circle me-1"></i> Version tags bookmark milestones (e.g. <code>v1.0.1</code>) so you can track updates and rollback anytime.
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Version Tag Name *</label>
                                <input type="text" name="tag_name" required class="form-control font-monospace" placeholder="e.g. v1.0.1" value="v1.0.1">
                                <small class="text-muted">Use semantic versioning (e.g. v1.0.1, v1.1.0, v2.0.0).</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Release Title / Notes</label>
                                <input type="text" name="tag_message" class="form-control" placeholder="e.g. Stable release with law colleges and CBSE schools" value="Stable portal release">
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="push_tag" value="1" id="pushTagCheck" checked>
                                <label class="form-check-label small" for="pushTagCheck">
                                    Push tag to remote GitHub repository immediately
                                </label>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom btn-sm px-3">
                                <i class="fas fa-save me-1"></i> Create Release Tag
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal for Creating Branch -->
            <div class="modal fade" id="createBranchModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" class="modal-content border-0 shadow">
                        <input type="hidden" name="action" value="create_branch">

                        <div class="modal-header bg-light">
                            <h6 class="modal-title fw-bold text-dark">
                                <i class="fas fa-code-branch text-warning me-2"></i>Create New Git Branch
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Branch Name *</label>
                                <input type="text" name="branch_name" required class="form-control font-monospace" placeholder="e.g. feature-admissions">
                                <small class="text-muted">Alphanumeric, dashes, underscores only.</small>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom btn-sm px-3">
                                <i class="fas fa-check me-1"></i> Create &amp; Switch
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal for Diff Viewer -->
            <div class="modal fade" id="diffModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-dark text-white">
                            <h6 class="modal-title fw-bold" id="diffModalTitle">
                                <i class="fas fa-file-diff text-warning me-2"></i>File Diff
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div id="diffContent" class="diff-container">Loading diff...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for GitHub Personal Access Token (PAT) -->
            <div class="modal fade" id="tokenModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" class="modal-content border-0 shadow">
                        <input type="hidden" name="action" value="save_token">

                        <div class="modal-header bg-light">
                            <h6 class="modal-title fw-bold text-dark">
                                <i class="fab fa-github me-2 text-dark"></i>GitHub Authentication &amp; PAT
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="fas fa-key me-1"></i> A <strong>Personal Access Token (classic or fine-grained)</strong> allows the web server to push and pull to your GitHub repository without interactive desktop logins.
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">GitHub Personal Access Token (PAT)</label>
                                <input type="password" name="github_token" class="form-control font-monospace" placeholder="ghp_xxxxxxxxxxxxxxxxxxxx" value="<?= htmlspecialchars($githubToken) ?>">
                                <small class="text-muted">
                                    Need a token? <a href="https://github.com/settings/tokens/new" target="_blank" class="text-decoration-none">Generate token on GitHub ↗</a> (Scope: <code>repo</code>).
                                </small>
                            </div>

                            <?php if ($githubToken): ?>
                                <div class="alert alert-success py-2 small mb-0">
                                    <i class="fas fa-check-circle me-1"></i> A token is currently saved and active.
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">
                                    Leave blank if your system already has local SSH or system Git credentials configured.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom btn-sm px-3">
                                <i class="fas fa-save me-1"></i> Save Token
                            </button>
                        </div>
                    </form>
                </div>
            </div>

<script>
function viewDiff(filePath) {
    document.getElementById('diffModalTitle').innerHTML = '<i class="fas fa-file-diff text-warning me-2"></i>Diff: ' + filePath;
    var container = document.getElementById('diffContent');
    container.textContent = 'Loading diff...';
    
    var modal = new bootstrap.Modal(document.getElementById('diffModal'));
    modal.show();

    fetch('git.php?ajax=diff&file=' + encodeURIComponent(filePath))
        .then(function(res) { return res.text(); })
        .then(function(text) {
            if (!text.trim()) {
                container.textContent = 'No modifications detected in this file.';
                return;
            }
            var lines = text.split('\n');
            var html = '';
            for (var i = 0; i < lines.length; i++) {
                var l = lines[i];
                var escaped = l.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                if (l.startsWith('+') && !l.startsWith('+++')) {
                    html += '<span class="diff-add">' + escaped + '</span>';
                } else if (l.startsWith('-') && !l.startsWith('---')) {
                    html += '<span class="diff-del">' + escaped + '</span>';
                } else if (l.startsWith('@@')) {
                    html += '<span class="diff-hunk">' + escaped + '</span>';
                } else {
                    html += escaped + '\n';
                }
            }
            container.innerHTML = html;
        })
        .catch(function(err) {
            container.textContent = 'Failed to load diff: ' + err;
        });
}
</script>

<?php include __DIR__ . '/admin-footer.php'; ?>
