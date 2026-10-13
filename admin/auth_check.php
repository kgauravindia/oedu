<?php
// admin/auth_check.php — Oedu.in Admin Authentication & Database Helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../includes/db.php';

/**
 * Initialize admin_users table and default superadmin if not exists
 */
function initAdminTables(PDO $pdo): void {
    static $initialized = false;
    if ($initialized) return;

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admin_users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `name` VARCHAR(150) NOT NULL,
            `email` VARCHAR(150) DEFAULT NULL,
            `role` VARCHAR(50) DEFAULT 'admin',
            `status` VARCHAR(20) DEFAULT 'ACTIVE',
            `last_login` DATETIME DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Check if default admin exists
    $chk = $pdo->prepare("SELECT id FROM `admin_users` WHERE `username` = 'admin' LIMIT 1");
    $chk->execute();
    if (!$chk->fetch()) {
        $defaultPassword = password_hash('Admin@Oedu2026!', PASSWORD_DEFAULT);
        $ins = $pdo->prepare("
            INSERT INTO `admin_users` (`username`, `password`, `name`, `email`, `role`, `status`)
            VALUES ('admin', :pwd, 'Oedu Platform Admin', 'admin@oedu.in', 'superadmin', 'ACTIVE')
        ");
        $ins->execute([':pwd' => $defaultPassword]);
    }

    $initialized = true;
}

initAdminTables($pdo);

/**
 * Check if current user is logged in as an administrator
 */
function checkAdminAuth(): bool {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Guard function to require admin authentication
 */
function requireAdmin(): void {
    if (!checkAdminAuth()) {
        $returnUrl = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
        header("Location: login.php?ref={$returnUrl}");
        exit;
    }
}
