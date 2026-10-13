<?php
// import_data.php — Creates 'college' and 'institute' tables and imports Excel data
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 600);
require_once __DIR__ . '/includes/db.php';

function slugify($text, $extra = '') {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    if (empty($text)) {
        $text = 'item';
    }
    if ($extra) {
        $cleanExtra = strtolower(preg_replace('~[^-\w]+~', '', $extra));
        $text .= '-' . $cleanExtra;
    }
    return substr($text, 0, 350);
}

echo "=== 1. CREATING TABLES ===\n";

// Table: institute
$pdo->exec("DROP TABLE IF EXISTS `institute`");
$pdo->exec("
CREATE TABLE `institute` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aishe_code` varchar(50) NOT NULL,
  `name` varchar(500) NOT NULL,
  `state` varchar(150) DEFAULT NULL,
  `district` varchar(150) DEFAULT NULL,
  `year_of_establishment` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `institute_type` varchar(200) DEFAULT NULL,
  `management` varchar(255) DEFAULT NULL,
  `slug` varchar(400) NOT NULL,
  `status` varchar(25) NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `institute_aishe_unique` (`aishe_code`),
  KEY `institute_name_idx` (`name`(191)),
  KEY `institute_state_idx` (`state`),
  KEY `institute_district_idx` (`district`),
  KEY `institute_type_idx` (`institute_type`(100)),
  KEY `institute_management_idx` (`management`(100)),
  KEY `institute_slug_idx` (`slug`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "Table `institute` created successfully.\n";

// Table: college
$pdo->exec("DROP TABLE IF EXISTS `college`");
$pdo->exec("
CREATE TABLE `college` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aishe_code` varchar(50) NOT NULL,
  `name` varchar(500) NOT NULL,
  `state` varchar(150) DEFAULT NULL,
  `district` varchar(150) DEFAULT NULL,
  `website` varchar(350) DEFAULT NULL,
  `year_of_establishment` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `college_type` varchar(150) DEFAULT NULL,
  `management` varchar(255) DEFAULT NULL,
  `university_aishe_code` varchar(50) DEFAULT NULL,
  `university_name` varchar(500) DEFAULT NULL,
  `university_type` varchar(150) DEFAULT NULL,
  `slug` varchar(400) NOT NULL,
  `status` varchar(25) NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `college_aishe_unique` (`aishe_code`),
  KEY `college_name_idx` (`name`(191)),
  KEY `college_state_idx` (`state`),
  KEY `college_district_idx` (`district`),
  KEY `college_univ_aishe_idx` (`university_aishe_code`),
  KEY `college_type_idx` (`college_type`(100)),
  KEY `college_management_idx` (`management`(100)),
  KEY `college_slug_idx` (`slug`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "Table `college` created successfully.\n\n";

// 2. Import Standalone Institutes
echo "=== 2. IMPORTING STANDALONE INSTITUTES ===\n";
$instituteFile = __DIR__ . '/Standalone-ALL STANDALONE (1).xlsx';
$startInst = microtime(true);

$reader = new XMLReader();
$reader->open('zip://' . realpath($instituteFile) . '#xl/worksheets/sheet1.xml');

$pdo->beginTransaction();
$sqlInst = "INSERT INTO `institute` (`aishe_code`, `name`, `state`, `district`, `year_of_establishment`, `location`, `institute_type`, `management`, `slug`, `status`) VALUES ";
$batch = [];
$batchSize = 1000;
$rowNum = 0;
$totalInstImported = 0;

while ($reader->read()) {
    if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'row') {
        $rowNum++;
        if ($rowNum <= 3) continue; // Skip title & headers

        $xml = new SimpleXMLElement($reader->readOuterXml());
        $cols = [];
        foreach ($xml->c as $c) {
            $colLetter = preg_replace('/[0-9]/', '', (string)$c['r']);
            $cols[$colLetter] = trim((string)$c->v);
        }

        $aishe = $cols['A'] ?? '';
        $name = $cols['B'] ?? '';
        if (!$aishe || !$name) continue;

        $state = $cols['C'] ?? null;
        $district = $cols['D'] ?? null;
        $year = $cols['E'] ?? null;
        $location = $cols['F'] ?? null;
        $instType = $cols['G'] ?? null;
        $mgmt = $cols['H'] ?? null;
        $slug = slugify($name, $aishe);

        $batch[] = [
            $aishe,
            $name,
            $state ?: null,
            $district ?: null,
            $year ?: null,
            $location ?: null,
            $instType ?: null,
            $mgmt ?: null,
            $slug,
            'ACTIVE'
        ];

        if (count($batch) >= $batchSize) {
            insertBatch($pdo, 'institute', [
                'aishe_code', 'name', 'state', 'district', 'year_of_establishment', 
                'location', 'institute_type', 'management', 'slug', 'status'
            ], $batch);
            $totalInstImported += count($batch);
            $batch = [];
            echo "Imported $totalInstImported institutes...\n";
        }
    }
}
$reader->close();

if (!empty($batch)) {
    insertBatch($pdo, 'institute', [
        'aishe_code', 'name', 'state', 'district', 'year_of_establishment', 
        'location', 'institute_type', 'management', 'slug', 'status'
    ], $batch);
    $totalInstImported += count($batch);
}
$pdo->commit();
$timeInst = round(microtime(true) - $startInst, 2);
echo "Completed Standalone Institutes: $totalInstImported imported in {$timeInst}s.\n\n";

// 3. Import Affiliated Colleges
echo "=== 3. IMPORTING AFFILIATED COLLEGES ===\n";
$collegeFile = __DIR__ . '/College-Affiliated College.xlsx';
$startCol = microtime(true);

$reader = new XMLReader();
$reader->open('zip://' . realpath($collegeFile) . '#xl/worksheets/sheet1.xml');

$pdo->beginTransaction();
$batch = [];
$rowNum = 0;
$totalColImported = 0;

while ($reader->read()) {
    if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'row') {
        $rowNum++;
        if ($rowNum <= 3) continue; // Skip title & headers

        $xml = new SimpleXMLElement($reader->readOuterXml());
        $cols = [];
        foreach ($xml->c as $c) {
            $colLetter = preg_replace('/[0-9]/', '', (string)$c['r']);
            $cols[$colLetter] = trim((string)$c->v);
        }

        $aishe = $cols['A'] ?? '';
        $name = $cols['B'] ?? '';
        if (!$aishe || !$name) continue;

        $state = $cols['C'] ?? null;
        $district = $cols['D'] ?? null;
        $website = $cols['E'] ?? null;
        $year = $cols['F'] ?? null;
        $location = $cols['G'] ?? null;
        $collegeType = $cols['H'] ?? null;
        $mgmt = $cols['I'] ?? null;
        $univAishe = $cols['J'] ?? null;
        $univName = $cols['K'] ?? null;
        $univType = $cols['L'] ?? null;
        $slug = slugify($name, $aishe);

        $batch[] = [
            $aishe,
            $name,
            $state ?: null,
            $district ?: null,
            $website ?: null,
            $year ?: null,
            $location ?: null,
            $collegeType ?: null,
            $mgmt ?: null,
            $univAishe ?: null,
            $univName ?: null,
            $univType ?: null,
            $slug,
            'ACTIVE'
        ];

        if (count($batch) >= $batchSize) {
            insertBatch($pdo, 'college', [
                'aishe_code', 'name', 'state', 'district', 'website',
                'year_of_establishment', 'location', 'college_type', 'management',
                'university_aishe_code', 'university_name', 'university_type',
                'slug', 'status'
            ], $batch);
            $totalColImported += count($batch);
            $batch = [];
            if ($totalColImported % 5000 === 0) {
                echo "Imported $totalColImported colleges...\n";
            }
        }
    }
}
$reader->close();

if (!empty($batch)) {
    insertBatch($pdo, 'college', [
        'aishe_code', 'name', 'state', 'district', 'website',
        'year_of_establishment', 'location', 'college_type', 'management',
        'university_aishe_code', 'university_name', 'university_type',
        'slug', 'status'
    ], $batch);
    $totalColImported += count($batch);
}
$pdo->commit();
$timeCol = round(microtime(true) - $startCol, 2);
echo "Completed Affiliated Colleges: $totalColImported imported in {$timeCol}s.\n\n";

function insertBatch(PDO $pdo, $table, array $columns, array $batch) {
    if (empty($batch)) return;
    $colList = '`' . implode('`, `', $columns) . '`';
    $rowPlaceholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
    $allPlaceholders = implode(', ', array_fill(0, count($batch), $rowPlaceholders));
    
    // ON DUPLICATE KEY UPDATE to avoid fatal crashes if AISHE code repeats
    $updates = [];
    foreach ($columns as $c) {
        if ($c !== 'aishe_code') {
            $updates[] = "`$c` = VALUES(`$c`)";
        }
    }
    $updateSql = implode(', ', $updates);

    $sql = "INSERT INTO `$table` ($colList) VALUES $allPlaceholders ON DUPLICATE KEY UPDATE $updateSql";
    $stmt = $pdo->prepare($sql);
    
    $flatValues = [];
    foreach ($batch as $row) {
        foreach ($row as $val) {
            $flatValues[] = $val;
        }
    }
    $stmt->execute($flatValues);
}

echo "=== SUMMARY ===\n";
echo "Total Institutes in DB: " . $pdo->query("SELECT COUNT(*) FROM `institute`")->fetchColumn() . "\n";
echo "Total Colleges in DB: " . $pdo->query("SELECT COUNT(*) FROM `college`")->fetchColumn() . "\n";
