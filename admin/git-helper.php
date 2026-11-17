<?php
// admin/git-helper.php — Cross-Platform Git Version Control & Update Helper for Admin (Linux & Windows)

if (!defined('REPO_DIR')) {
    define('REPO_DIR', dirname(__DIR__));
}

/**
 * Check if a specific PHP execution function is allowed.
 */
function isFunctionAllowed(string $func): bool {
    if (!function_exists($func)) {
        return false;
    }
    $disabled = explode(',', (string)ini_get('disable_functions'));
    $disabled = array_map('trim', $disabled);
    return !in_array($func, $disabled, true);
}

/**
 * Check if any CLI shell execution method is available.
 */
function isExecEnabled(): bool {
    return isFunctionAllowed('proc_open') 
        || isFunctionAllowed('exec') 
        || isFunctionAllowed('shell_exec') 
        || isFunctionAllowed('passthru') 
        || isFunctionAllowed('popen');
}

/**
 * Execute command through whichever function is enabled on the hosting server.
 */
function executeShellCommand(string $cmd): array {
    // 1. Try proc_open (cleanest, captures stdout & stderr)
    if (isFunctionAllowed('proc_open')) {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $process = @proc_open($cmd, $descriptors, $pipes);
        if (is_resource($process)) {
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $returnCode = proc_close($process);
            $output = trim($stdout . ($stderr ? "\n" . $stderr : ''));
            return ['code' => $returnCode, 'output' => $output];
        }
    }

    // 2. Try exec
    if (isFunctionAllowed('exec')) {
        $lines = [];
        $code = 0;
        @exec($cmd, $lines, $code);
        return ['code' => $code, 'output' => implode("\n", $lines)];
    }

    // 3. Try shell_exec
    if (isFunctionAllowed('shell_exec')) {
        $out = @shell_exec($cmd);
        return ['code' => ($out !== null ? 0 : 1), 'output' => (string)$out];
    }

    // 4. Try passthru
    if (isFunctionAllowed('passthru')) {
        ob_start();
        $code = 0;
        @passthru($cmd, $code);
        $out = ob_get_clean();
        return ['code' => $code, 'output' => (string)$out];
    }

    // 5. Try popen
    if (isFunctionAllowed('popen')) {
        $handle = @popen($cmd, 'r');
        if ($handle) {
            $out = '';
            while (!feof($handle)) {
                $out .= fread($handle, 4096);
            }
            $code = pclose($handle);
            return ['code' => $code, 'output' => trim($out)];
        }
    }

    return [
        'code' => 127,
        'output' => "Notice: Shell execution functions (proc_open, exec, shell_exec, passthru, popen) are disabled in php.ini on this server.\nPlease remove 'exec' from 'disable_functions' in cPanel > Select PHP Version > Options."
    ];
}

/**
 * Locate the Git executable across Linux, cPanel, Windows, and Laragon environments.
 */
function getGitExecutable(): string {
    static $gitBin = null;
    if ($gitBin !== null) {
        return $gitBin;
    }
    
    $candidates = [
        'git',
        '/usr/bin/git',
        '/usr/local/bin/git',
        '/bin/git',
        '/opt/cpanel/ea-git/usr/bin/git',
        'C:\Program Files\Git\cmd\git.exe',
        'C:\Program Files\Git\bin\git.exe',
        'C:\Program Files (x86)\Git\cmd\git.exe',
        'D:\laragon\bin\git\bin\git.exe',
        'D:\laragon\bin\git\cmd\git.exe',
        'C:\laragon\bin\git\bin\git.exe',
        'C:\laragon\bin\git\cmd\git.exe',
    ];
    
    foreach ($candidates as $cand) {
        if ($cand === 'git') {
            if (isExecEnabled()) {
                $test = executeShellCommand("git --version 2>&1");
                if ($test['code'] === 0) {
                    $gitBin = 'git';
                    return $gitBin;
                }
            }
        } elseif (@file_exists($cand)) {
            $gitBin = (DIRECTORY_SEPARATOR === '\\') ? ('"' . $cand . '"') : escapeshellcmd($cand);
            return $gitBin;
        }
    }
    
    $gitBin = 'git';
    return $gitBin;
}

/**
 * Fetch saved GitHub Personal Access Token from admin_settings.
 * Auto-creates the table if it does not already exist.
 */
function getSavedGithubToken(?PDO $pdo = null): string {
    if (!$pdo) {
        $pdo = $GLOBALS['pdo'] ?? null;
    }
    if (!$pdo instanceof PDO) {
        return '';
    }
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `admin_settings` (
                `setting_key` VARCHAR(100) PRIMARY KEY,
                `setting_value` TEXT NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        $stmt = $pdo->query("SELECT setting_value FROM admin_settings WHERE setting_key = 'github_token' LIMIT 1");
        return (string)($stmt->fetchColumn() ?: '');
    } catch (Throwable $e) {
        return '';
    }
}

/**
 * Execute a Git command safely with OS-specific shell handling and token injection.
 */
function runGitCommand(string $cmd, ?string $repoDir = null, ?string $token = null): array {
    $repoDir = $repoDir ?: REPO_DIR;
    
    if (!isExecEnabled()) {
        return [
            'code' => 127,
            'output' => "Notice: Shell execution functions are disabled in php.ini on this server.\nPlease enable 'exec' in cPanel > Select PHP Version > Options (disable_functions), or use cPanel Git Version Control.",
            'cmd' => $cmd
        ];
    }

    $gitExe = getGitExecutable();
    $isWindows = (DIRECTORY_SEPARATOR === '\\' || PHP_OS_FAMILY === 'Windows');
    
    // Normalize command with executable and safe.directory configuration
    $safeCmd = preg_replace('/^git\b/', "{$gitExe} -c safe.directory=* ", trim($cmd));
    
    // If GitHub token is provided, inject it for authenticated GitHub remote operations
    if (!empty($token) && (str_contains($cmd, 'push') || str_contains($cmd, 'pull') || str_contains($cmd, 'fetch'))) {
        $authRemote = "https://{$token}@github.com/kgauravindia/oedu.git";
        $safeCmd = preg_replace('/\borigin\b/', "\"{$authRemote}\"", $safeCmd, 1);
    }
    
    // Build cross-platform command
    if ($isWindows) {
        $repoDirNorm = str_replace('/', DIRECTORY_SEPARATOR, $repoDir);
        $fullCommand = "set \"GIT_TERMINAL_PROMPT=0\" && cd /d \"{$repoDirNorm}\" && {$safeCmd} 2>&1";
    } else {
        $safeRepoDir = escapeshellarg($repoDir);
        $fullCommand = "export GIT_TERMINAL_PROMPT=0 && cd {$safeRepoDir} && {$safeCmd} 2>&1";
    }
    
    $res = executeShellCommand($fullCommand);
    
    $outText = $res['output'];
    if (!empty($token)) {
        $outText = str_replace($token, '***TOKEN***', $outText);
    }
    
    return [
        'code' => $res['code'],
        'output' => $outText,
        'cmd' => $cmd
    ];
}

/**
 * Get comprehensive Git status, branch, version, and sync metrics with safety fallbacks.
 */
function getGitSummary(?string $repoDir = null, ?string $token = null): array {
    $repoDir = $repoDir ?: REPO_DIR;
    
    $hasDotGit = is_dir($repoDir . '/.git');
    $execEnabled = isExecEnabled();
    
    if (!$execEnabled || !$hasDotGit) {
        return [
            'branch' => 'main',
            'version' => 'v1.0.0',
            'remote_url' => 'https://github.com/kgauravindia/oedu.git',
            'github_base' => 'https://github.com/kgauravindia/oedu',
            'ahead' => 0,
            'behind' => 0,
            'last_commit' => [
                'hash' => '',
                'author' => '',
                'ago' => '',
                'msg' => $hasDotGit ? 'Shell execution disabled in php.ini' : 'No .git directory found on server',
                'date' => ''
            ],
            'changed_files' => [],
            'is_dirty' => false,
            'has_dot_git' => $hasDotGit,
            'exec_enabled' => $execEnabled
        ];
    }

    // Active Branch
    $branchRes = runGitCommand('git rev-parse --abbrev-ref HEAD', $repoDir);
    $currentBranch = trim($branchRes['output'] ?: 'main');
    if (str_contains($currentBranch, "\n") || str_contains($currentBranch, 'fatal:') || empty($currentBranch)) {
        $currentBranch = 'main';
    }
    
    // Version / Tag (e.g. v1.0.0, or commit hash)
    $versionRes = runGitCommand('git describe --tags --always', $repoDir);
    $currentVersion = trim($versionRes['output'] ?: 'v1.0.0');
    if (str_contains($currentVersion, "\n") || str_contains($currentVersion, 'fatal:') || empty($currentVersion)) {
        $currentVersion = 'v1.0.0';
    }
    
    // Remote URL
    $remoteRes = runGitCommand('git remote get-url origin', $repoDir);
    $remoteUrl = trim($remoteRes['output'] ?: '');
    if (str_contains($remoteUrl, 'fatal:') || empty($remoteUrl)) {
        $remoteUrl = 'https://github.com/kgauravindia/oedu.git';
    }
    
    // Ahead / Behind commit counts vs origin
    $aheadCount = 0;
    $behindCount = 0;
    try {
        $aheadRes = runGitCommand("git rev-list --count origin/{$currentBranch}..HEAD", $repoDir);
        if ($aheadRes['code'] === 0) {
            $aheadCount = (int)trim($aheadRes['output']);
        }
        $behindRes = runGitCommand("git rev-list --count HEAD..origin/{$currentBranch}", $repoDir);
        if ($behindRes['code'] === 0) {
            $behindCount = (int)trim($behindRes['output']);
        }
    } catch (Throwable $e) {}
    
    // Latest Commit
    $lastCommitRes = runGitCommand('git log -1 --format="%h|%an|%ar|%s|%cd"', $repoDir);
    $lastCommitParts = explode('|', trim($lastCommitRes['output'] ?: ''));
    $lastCommit = [
        'hash' => $lastCommitParts[0] ?? '',
        'author' => $lastCommitParts[1] ?? '',
        'ago' => $lastCommitParts[2] ?? '',
        'msg' => $lastCommitParts[3] ?? '',
        'date' => $lastCommitParts[4] ?? ''
    ];
    
    // Changed Files in Working Tree
    $statusRes = runGitCommand('git status -s', $repoDir);
    $statusLines = array_filter(explode("\n", trim($statusRes['output'] ?: '')));
    $changedFiles = [];
    foreach ($statusLines as $line) {
        $line = trim($line);
        if (!$line || str_contains($line, 'fatal:')) continue;
        $statusType = substr($line, 0, 2);
        $fileName = trim(substr($line, 2));
        $changedFiles[] = [
            'raw_status' => $statusType,
            'file' => $fileName
        ];
    }
    
    // GitHub Base URL
    $githubBase = '';
    if (preg_match('/github\.com[:\/]([^\/]+)\/([^\/\.]+)/', $remoteUrl, $m)) {
        $githubBase = "https://github.com/{$m[1]}/{$m[2]}";
    }
    
    return [
        'branch' => $currentBranch,
        'version' => $currentVersion,
        'remote_url' => $remoteUrl,
        'github_base' => $githubBase,
        'ahead' => $aheadCount,
        'behind' => $behindCount,
        'last_commit' => $lastCommit,
        'changed_files' => $changedFiles,
        'is_dirty' => count($changedFiles) > 0,
        'has_dot_git' => $hasDotGit,
        'exec_enabled' => $execEnabled
    ];
}

/**
 * Retrieve recent commit log history.
 */
function getGitCommitHistory(?string $repoDir = null, int $limit = 15): array {
    $repoDir = $repoDir ?: REPO_DIR;
    if (!isExecEnabled() || !is_dir($repoDir . '/.git')) {
        return [];
    }
    $logRes = runGitCommand("git log -n {$limit} --format=\"%h|%an|%ar|%s|%cd\"", $repoDir);
    $logLines = array_filter(explode("\n", trim($logRes['output'] ?: '')));
    $history = [];
    foreach ($logLines as $line) {
        if (str_contains($line, 'fatal:')) continue;
        $p = explode('|', $line, 5);
        if (count($p) >= 4) {
            $history[] = [
                'hash' => $p[0],
                'author' => $p[1],
                'ago' => $p[2],
                'subject' => $p[3],
                'date' => $p[4] ?? ''
            ];
        }
    }
    return $history;
}

/**
 * List all Git tags / release versions.
 */
function getGitTagsList(?string $repoDir = null): array {
    $repoDir = $repoDir ?: REPO_DIR;
    if (!isExecEnabled() || !is_dir($repoDir . '/.git')) {
        return [];
    }
    $res = runGitCommand('git tag -l --sort=-creatordate --format="%(refname:short)|%(creatordate:relative)|%(subject)"', $repoDir);
    $lines = array_filter(explode("\n", trim($res['output'] ?: '')));
    $tags = [];
    foreach ($lines as $line) {
        if (str_contains($line, 'fatal:')) continue;
        $p = explode('|', $line, 3);
        if (!empty($p[0])) {
            $tags[] = [
                'name' => trim($p[0]),
                'date' => trim($p[1] ?? ''),
                'subject' => trim($p[2] ?? '')
            ];
        }
    }
    return $tags;
}

/**
 * List all Git branches.
 */
function getGitBranchesList(?string $repoDir = null): array {
    $repoDir = $repoDir ?: REPO_DIR;
    if (!isExecEnabled() || !is_dir($repoDir . '/.git')) {
        return [['name' => 'main', 'is_current' => true]];
    }
    $res = runGitCommand('git branch -a', $repoDir);
    $lines = array_filter(explode("\n", trim($res['output'] ?: '')));
    $branches = [];
    foreach ($lines as $line) {
        if (str_contains($line, 'fatal:')) continue;
        $isCurrent = str_starts_with($line, '*');
        $name = trim(ltrim($line, '* '));
        if ($name && !str_contains($name, '->')) {
            $branches[] = [
                'name' => $name,
                'is_current' => $isCurrent
            ];
        }
    }
    if (empty($branches)) {
        $branches[] = ['name' => 'main', 'is_current' => true];
    }
    return $branches;
}

/**
 * Get unified diff of a specific file or all files.
 */
function getGitDiff(?string $file = null, ?string $repoDir = null): string {
    $repoDir = $repoDir ?: REPO_DIR;
    if (!isExecEnabled() || !is_dir($repoDir . '/.git')) {
        return 'Shell execution or .git directory unavailable on server.';
    }
    if ($file) {
        $safeFile = escapeshellarg($file);
        $res = runGitCommand("git diff {$safeFile}", $repoDir);
    } else {
        $res = runGitCommand("git diff", $repoDir);
    }
    return $res['output'] ?: 'No difference detected or untracked file.';
}

/**
 * Perform a safe, complete System Update (Git Pull).
 */
function performGitUpdate(string $branch = 'main', ?string $token = null, bool $autoStash = true, ?string $repoDir = null): array {
    $repoDir = $repoDir ?: REPO_DIR;
    
    if (!isExecEnabled()) {
        return [
            'success' => false,
            'code' => 127,
            'log' => "Shell execution is disabled on this server. Enable 'exec' in cPanel > Select PHP Version > Options, or pull via cPanel Git Version Control.",
            'message' => "Update failed: Shell execution is disabled on this server."
        ];
    }
    
    $logs = [];
    
    // 1. Fetch remote changes
    $fetchRes = runGitCommand('git fetch origin', $repoDir, $token);
    $logs[] = "$ git fetch origin\n" . ($fetchRes['output'] ?: 'Fetch complete.');
    
    // Check if working tree has changes
    $statusCheck = runGitCommand('git status -s', $repoDir);
    $hasChanges = !empty(trim($statusCheck['output'] ?? ''));
    $stashed = false;
    
    // 2. Auto-stash if dirty
    if ($hasChanges && $autoStash) {
        $stashRes = runGitCommand('git stash save "Auto-stash before system update"', $repoDir);
        $logs[] = "$ git stash\n" . ($stashRes['output'] ?: 'Local changes temporarily stashed.');
        $stashed = true;
    }
    
    // 3. Pull latest updates from origin
    $pullRes = runGitCommand("git pull origin {$branch}", $repoDir, $token);
    $logs[] = "$ git pull origin {$branch}\n" . ($pullRes['output'] ?: 'Already up to date.');
    
    // 4. Pop stash if we stashed
    if ($stashed) {
        $popRes = runGitCommand('git stash pop', $repoDir);
        $logs[] = "$ git stash pop\n" . ($popRes['output'] ?: 'Restored local changes.');
    }
    
    $fullLog = implode("\n\n", $logs);
    $isSuccess = ($pullRes['code'] === 0);
    
    return [
        'success' => $isSuccess,
        'code' => $pullRes['code'],
        'log' => $fullLog,
        'message' => $isSuccess 
            ? "System updated successfully from origin/{$branch}!" 
            : "System update encountered an issue. See logs below."
    ];
}
