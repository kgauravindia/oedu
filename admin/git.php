<?php
// admin/git.php — Git Version Control, Remote Sync & Commit Manager
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Git Version & Sync — Online Education Admin';
$msg = '';
$msgType = 'success';
$cmdOutput = '';

$repoDir = dirname(__DIR__);

// Helper to run git safely in repository root
function runGitCommand(string $cmd, string $repoDir): array {
    $command = "cd /d \"{$repoDir}\" && {$cmd} 2>&1";
    $output = [];
    $returnCode = 0;
    exec($command, $output, $returnCode);
    return [
        'code' => $returnCode,
        'output' => implode("\n", $output),
        'cmd' => $cmd
    ];
}

// Handle Git Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'fetch') {
        $res = runGitCommand('git fetch origin', $repoDir);
        $cmdOutput = "$ git fetch origin\n" . ($res['output'] ?: 'Repository fetched successfully. No new changes.');
        $msg = "Git fetch completed.";
        $msgType = ($res['code'] === 0) ? 'success' : 'danger';
    } elseif ($action === 'pull') {
        $branch = trim($_POST['branch'] ?? 'main');
        $res = runGitCommand("git pull origin {$branch}", $repoDir);
        $cmdOutput = "$ git pull origin {$branch}\n" . $res['output'];
        if ($res['code'] === 0) {
            $msg = "Pulled successfully from origin/{$branch}.";
            $msgType = 'success';
        } else {
            $msg = "Git pull failed. Check console output.";
            $msgType = 'danger';
        }
    } elseif ($action === 'commit_push') {
        $commitMsg = trim($_POST['commit_message'] ?? '');
        $branch = trim($_POST['branch'] ?? 'main');
        
        if (empty($commitMsg)) {
            $msg = "Commit message cannot be empty.";
            $msgType = 'danger';
        } else {
            // Escape double quotes in commit message
            $safeMsg = addslashes($commitMsg);
            
            // 1. git add .
            $addRes = runGitCommand('git add .', $repoDir);
            
            // 2. git commit -m "..."
            $commitRes = runGitCommand("git commit -m \"{$safeMsg}\"", $repoDir);
            
            // 3. git push origin branch
            $pushRes = runGitCommand("git push origin {$branch}", $repoDir);
            
            $cmdOutput = "$ git add .\n" . $addRes['output'] . "\n\n"
                       . "$ git commit -m \"{$safeMsg}\"\n" . $commitRes['output'] . "\n\n"
                       . "$ git push origin {$branch}\n" . $pushRes['output'];

            if ($pushRes['code'] === 0) {
                $msg = "Changes committed & pushed to origin/{$branch} successfully!";
                $msgType = 'success';
            } elseif ($commitRes['code'] === 0 && $pushRes['code'] !== 0) {
                $msg = "Changes committed locally, but push failed. Check console output.";
                $msgType = 'warning';
            } else {
                $msg = "Git commit / push encountered an issue. See console below.";
                $msgType = 'danger';
            }
        }
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
    }
}

// Query Current Git State
$branchRes = runGitCommand('git rev-parse --abbrev-ref HEAD', $repoDir);
$currentBranch = trim($branchRes['output'] ?: 'main');

$remoteRes = runGitCommand('git remote get-url origin', $repoDir);
$remoteUrl = trim($remoteRes['output'] ?: '');

$lastCommitRes = runGitCommand('git log -1 --format="%h|%an|%ar|%s|%cd"', $repoDir);
$lastCommitParts = explode('|', trim($lastCommitRes['output'] ?: ''));
$lastCommit = [
    'hash' => $lastCommitParts[0] ?? '',
    'author' => $lastCommitParts[1] ?? '',
    'ago' => $lastCommitParts[2] ?? '',
    'msg' => $lastCommitParts[3] ?? '',
    'date' => $lastCommitParts[4] ?? ''
];

// Working tree changes
$statusRes = runGitCommand('git status -s', $repoDir);
$statusLines = array_filter(explode("\n", trim($statusRes['output'] ?: '')));
$changedFiles = [];
foreach ($statusLines as $line) {
    $line = trim($line);
    if (!$line) continue;
    $statusType = substr($line, 0, 2);
    $fileName = trim(substr($line, 2));
    $changedFiles[] = [
        'raw_status' => $statusType,
        'file' => $fileName
    ];
}

// Recent Commit History (Last 12 commits)
$logRes = runGitCommand('git log -n 12 --format="%h|%an|%ar|%s"', $repoDir);
$logLines = array_filter(explode("\n", trim($logRes['output'] ?: '')));
$commitHistory = [];
foreach ($logLines as $line) {
    $p = explode('|', $line, 4);
    if (count($p) >= 4) {
        $commitHistory[] = [
            'hash' => $p[0],
            'author' => $p[1],
            'ago' => $p[2],
            'subject' => $p[3]
        ];
    }
}

// GitHub web commit prefix if remote is github
$githubBase = '';
if (preg_match('/github\.com[:\/]([^\/]+)\/([^\/\.]+)/', $remoteUrl, $m)) {
    $githubBase = "https://github.com/{$m[1]}/{$m[2]}";
}
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
    </style>
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
                        <i class="fab fa-git-alt text-danger me-2"></i> Git Version Control &amp; Remote Sync
                    </h4>
                    <p class="text-muted small mb-0">Manage repository branch, inspect pending modifications, pull updates, and sync to remote repository.</p>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="fetch">
                        <button type="submit" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                            <i class="fas fa-sync-alt me-1"></i> Git Fetch
                        </button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Pull latest updates from origin/<?= htmlspecialchars($currentBranch) ?>?');" style="display: inline;">
                        <input type="hidden" name="action" value="pull">
                        <input type="hidden" name="branch" value="<?= htmlspecialchars($currentBranch) ?>">
                        <button type="submit" class="btn btn-outline-primary btn-sm px-3 shadow-sm">
                            <i class="fas fa-cloud-download-alt me-1"></i> Git Pull (Sync Down)
                        </button>
                    </form>
                    <button type="button" class="btn btn-primary-custom btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#commitModal">
                        <i class="fas fa-cloud-upload-alt me-1"></i> Commit &amp; Push
                    </button>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType ?> alert-dismissible fade show alert-auto-dismiss" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Metrics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(240, 80, 50, 0.1); color: #F05032;">
                            <i class="fas fa-code-branch"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Active Branch</span>
                            <h4 class="stat-value text-danger mb-0"><?= htmlspecialchars($currentBranch) ?></h4>
                            <small class="text-muted" style="font-size: 0.72rem;">Remote: origin/<?= htmlspecialchars($currentBranch) ?></small>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(56, 189, 248, 0.1); color: #0284C7;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Latest Commit</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="git-hash-badge"><?= htmlspecialchars($lastCommit['hash'] ?: 'None') ?></span>
                                <small class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($lastCommit['ago'] ?: '') ?></small>
                            </div>
                            <small class="text-dark fw-semibold text-truncate d-block" style="font-size: 0.72rem;" title="<?= htmlspecialchars($lastCommit['msg']) ?>">
                                <?= htmlspecialchars(substr($lastCommit['msg'], 0, 32)) ?>...
                            </small>
                        </div>
                    </div>
                </div>

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

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #4F46E5;">
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
                        <span class="small fw-bold"><i class="fas fa-terminal me-2 text-warning"></i>Console Output</span>
                        <span class="badge bg-secondary">exit code: <?= $res['code'] ?? 0 ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="console-output"><?= htmlspecialchars($cmdOutput) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Left: Uncommitted Changed Files -->
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-edit me-2 text-primary"></i>Working Tree Changes (<?= count($changedFiles) ?>)
                                </h6>
                                <small class="text-muted">Modified, added, or deleted files since last commit</small>
                            </div>
                            <?php if (count($changedFiles) > 0): ?>
                                <button type="button" class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#commitModal">
                                    <i class="fas fa-check me-1"></i>Sync All
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-0">
                            <?php if (empty($changedFiles)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-2" style="font-size: 2.2rem; color: #10B981;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Working Tree is Clean</h6>
                                    <p class="text-muted small mb-0">All changes have been committed and synced to git.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="bg-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                                            <tr>
                                                <th class="ps-3">Status</th>
                                                <th>File Path</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($changedFiles as $cf): 
                                                $stat = trim($cf['raw_status']);
                                                $badgeClass = 'git-status-m';
                                                $label = 'Modified';
                                                if (strpos($stat, '?') !== false) {
                                                    $badgeClass = 'git-status-q';
                                                    $label = 'Untracked / New';
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
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Recent Commit History -->
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-history me-2 text-info"></i>Recent Commit History
                                </h6>
                                <small class="text-muted">Last <?= count($commitHistory) ?> commits on branch <?= htmlspecialchars($currentBranch) ?></small>
                            </div>
                            <?php if ($githubBase): ?>
                                <a href="<?= htmlspecialchars($githubBase) ?>/commits/<?= htmlspecialchars($currentBranch) ?>" target="_blank" class="btn btn-outline-secondary btn-sm" style="font-size: 0.78rem;">
                                    GitHub Commits ↗
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead class="bg-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                                        <tr>
                                            <th class="ps-3">Commit</th>
                                            <th>Description</th>
                                            <th>Author</th>
                                            <th class="text-end pe-3">Time</th>
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
                                                        <div class="fw-bold text-dark text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($c['subject']) ?>">
                                                            <?= htmlspecialchars($c['subject']) ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted small"><?= htmlspecialchars($c['author']) ?></td>
                                                    <td class="text-end pe-3 text-muted small" style="white-space: nowrap;"><?= htmlspecialchars($c['ago']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
                    <i class="fas fa-info-circle me-1"></i> This will run <code>git add .</code>, create a commit, and execute <code>git push origin <?= htmlspecialchars($currentBranch) ?></code>.
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Commit Message *</label>
                    <input type="text" name="commit_message" required class="form-control" placeholder="e.g. Add BCI law colleges directory and XML sitemaps" value="Update portal features and verified institution directories">
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

<?php include __DIR__ . '/admin-footer.php'; ?>
