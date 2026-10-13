<?php
// api/districts.php — Fetch districts from op_sdb table (.php only)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

$state = trim($_GET['state'] ?? '');

if (!$state) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT DISTINCT district FROM op_sdb WHERE state = :state AND district IS NOT NULL AND district != '' ORDER BY district ASC");
    $stmt->execute([':state' => $state]);
    $districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($districts);
} catch (Exception $e) {
    echo json_encode([]);
}
