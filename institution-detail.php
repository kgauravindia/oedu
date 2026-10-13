<?php
// institution-detail.php — Redirect legacy URLs to university/colleges directory
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');

if ($slug) {
    $stmt = $pdo->prepare("SELECT slug FROM university WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    $uSlug = $stmt->fetchColumn();
    if ($uSlug) {
        header("Location: university-detail.php?slug=" . urlencode($uSlug), true, 301);
        exit;
    }

    $stmtCol = $pdo->prepare("SELECT slug FROM college WHERE slug = :slug LIMIT 1");
    $stmtCol->execute([':slug' => $slug]);
    $cSlug = $stmtCol->fetchColumn();
    if ($cSlug) {
        header("Location: college-detail.php?slug=" . urlencode($cSlug), true, 301);
        exit;
    }
}

header("Location: institutions.php", true, 301);
exit;
