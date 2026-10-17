<?php
// update_schools_nvs.php — Synchronize and update all 675 Jawahar Navodaya Vidyalayas from nvs.xlsx
// Updates existing records with UDISE code, address, PIN code, and inserts newly sanctioned JNVs
require_once __DIR__ . '/includes/db.php';

echo "=== Online Education: NVS (Navodaya Vidyalaya Samiti) School Synchronizer ===\n";

// 1. Ensure udise_code column exists
$colCheck = $pdo->query("SHOW COLUMNS FROM `school` LIKE 'udise_code'");
if ($colCheck->rowCount() === 0) {
    echo "Creating udise_code column in `school` table...\n";
    $pdo->exec("ALTER TABLE `school` ADD COLUMN `udise_code` VARCHAR(30) NULL AFTER `school_code`, ADD INDEX `idx_school_udise` (`udise_code`)");
}

function normalizeStateName($st) {
    $st = strtoupper(trim($st));
    $st = str_replace(['&', 'AND', 'ISLANDS', 'THE', 'OF', 'NCT', 'UT', 'STATE'], ' ', $st);
    $st = preg_replace('/\s+/', ' ', $st);
    $st = trim($st);
    if (strpos($st, 'CHATTISGARH') !== false || strpos($st, 'CHHATTISGARH') !== false) return 'CHHATTISGARH';
    if (strpos($st, 'TAMIL') !== false) return 'TAMIL NADU';
    if (strpos($st, 'ODISHA') !== false || strpos($st, 'ORISSA') !== false) return 'ODISHA';
    if (strpos($st, 'PUDUCHERRY') !== false || strpos($st, 'PONDICHERRY') !== false) return 'PUDUCHERRY';
    if (strpos($st, 'DADRA') !== false || strpos($st, 'DAMAN') !== false) return 'DADRA DAMAN';
    if (strpos($st, 'ANDAMAN') !== false || strpos($st, 'NICOBAR') !== false) return 'ANDAMAN NICOBAR';
    if (strpos($st, 'JAMMU') !== false || strpos($st, 'KASHMIR') !== false) return 'JAMMU KASHMIR';
    return $st;
}

function cleanTokensList($str) {
    $str = strtoupper(trim($str));
    $str = preg_replace('/[^A-Z0-9\s]/', ' ', $str);
    $tokens = array_filter(explode(' ', $str), function($t) {
        return strlen($t) >= 3 && !in_array($t, ['JAWAHAR', 'NAVODAYA', 'VIDYALAYA', 'SAMITI', 'DIST', 'DISTT', 'DISTRICT', 'POST', 'VILL', 'VILLAGE', 'TALUK', 'TALUKA', 'TEHSIL', 'NEAR', 'ROAD', 'ROADNEAR', 'GOVT', 'COLLEGE', 'SCHOOL', 'SHRI', 'CENTRAL', 'PO']);
    });
    return array_values($tokens);
}

function slugifyString($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'nvs-school-' . substr(md5(uniqid()), 0, 6) : $text;
}

// 2. Load state mapping from op_sdb
$stStmt = $pdo->query('SELECT DISTINCT state_code, state FROM op_sdb WHERE state IS NOT NULL');
$stateCodeMap = [];
while ($row = $stStmt->fetch(PDO::FETCH_ASSOC)) {
    $code = str_pad(trim($row['state_code']), 2, '0', STR_PAD_LEFT);
    $stateCodeMap[$code] = strtoupper(trim($row['state']));
}

// 3. Open nvs.xlsx
$xlsxFile = __DIR__ . '/nvs.xlsx';
if (!file_exists($xlsxFile)) {
    die("Error: nvs.xlsx not found at {$xlsxFile}\n");
}

$zip = new ZipArchive();
if ($zip->open($xlsxFile) !== true) {
    die("Error: Failed to open nvs.xlsx as a ZIP archive\n");
}

$sharedStrings = [];
$ssXml = $zip->getFromName('xl/sharedStrings.xml');
if ($ssXml) {
    $xml = simplexml_load_string($ssXml);
    foreach ($xml->si as $si) {
        if (isset($si->t)) $sharedStrings[] = (string)$si->t;
        elseif (isset($si->r)) {
            $t = '';
            foreach ($si->r as $r) $t .= (string)$r->t;
            $sharedStrings[] = $t;
        } else $sharedStrings[] = '';
    }
}

$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
$xml = simplexml_load_string($sheetXml);

$schoolsRaw = [];
$curr = null;
foreach ($xml->sheetData->row as $r) {
    $val = '';
    foreach ($r->c as $c) {
        $type = (string)$c['t'];
        $v = (string)$c->v;
        if ($type === 's') $v = $sharedStrings[(int)$v] ?? '';
        $val = trim($v);
        break;
    }
    if ($val === '') continue;

    if (preg_match('/^UDISE Code\s*:\s*(\d+)/i', $val, $m)) {
        if ($curr) $schoolsRaw[] = $curr;
        $curr = [$val];
    } else {
        if ($curr) $curr[] = $val;
    }
}
if ($curr) $schoolsRaw[] = $curr;
$zip->close();

echo "Parsed " . count($schoolsRaw) . " school entries from nvs.xlsx.\n";

// 4. Extract structured NVS school items
$nvsList = [];
foreach ($schoolsRaw as $block) {
    $item = [
        'udise_code' => '',
        'operational' => 'Operational',
        'name' => '',
        'state' => '',
        'district' => '',
        'address' => '',
        'pincode' => '',
        'block' => '',
        'village' => '',
        'category' => 'Up. Pr. Secondary and Higher Sec',
        'class_range' => 'Class: 6 To 12',
        'type' => 'Co-educational',
        'location' => 'Rural',
    ];
    foreach ($block as $line) {
        if (preg_match('/^UDISE Code\s*:\s*(\d+)\s*(Operational|Non-Operational)?/i', $line, $m)) {
            $item['udise_code'] = $m[1];
            if (!empty($m[2])) $item['operational'] = $m[2];
            $stPrefix = substr($m[1], 0, 2);
            $item['state'] = $stateCodeMap[$stPrefix] ?? '';
        } elseif (preg_match('/^(JAWAHAR NAVODAYA VIDYALAYA|NAVODAYA VIDYALAYA|PM SHRI JAWAHAR NAVODAYA|PM SHRI SCHOOL JAWAHAR NAVODAYA|PM SHRI JNV|JNV)\b/i', $line)) {
            if (empty($item['name']) && stripos($line, 'Navodaya Vidyalaya Samiti') === false) {
                $item['name'] = trim($line);
            }
        } elseif (preg_match('/^Address\s*:\s*(.+)$/i', $line, $m)) {
            $item['address'] = trim($m[1]);
        } elseif (preg_match('/^PIN Code\s*:\s*(\d{6})/i', $line, $m)) {
            $item['pincode'] = trim($m[1]);
        } elseif (preg_match('/^LGD Block\s*:\s*(.+)$/i', $line, $m)) {
            $item['block'] = trim($m[1]);
        } elseif (preg_match('/^LGD Village\s*:\s*(.+)$/i', $line, $m)) {
            $item['village'] = trim($m[1]);
        } elseif (preg_match('/^School Category\s*:\s*(.+)$/i', $line, $m)) {
            $item['category'] = trim($m[1]);
        } elseif (preg_match('/^Class\s*:\s*(.+)$/i', $line, $m)) {
            $item['class_range'] = trim($m[1]);
        } elseif (preg_match('/^School Type\s*:\s*(.+)$/i', $line, $m)) {
            $item['type'] = trim($m[1]);
        } elseif (preg_match('/^School Location\s*:\s*(.+)$/i', $line, $m)) {
            $item['location'] = trim($m[1]);
        }
    }
    if (empty($item['name']) && isset($block[4])) $item['name'] = trim($block[4]);

    // Extract district from name or block
    if (preg_match('/^(?:PM\s+SHRI\s+(?:SCHOOL\s+)?)?(?:JAWAHAR\s+NAVODAYA\s+VIDYALAYA|NAVODAYA\s+VIDYALAYA|JNV)\s+([^,]+)(?:,\s*(.+))?$/i', $item['name'], $m)) {
        $item['district'] = strtoupper(trim($m[1]));
    }
    if (empty($item['district']) && !empty($item['block']) && $item['block'] !== 'NA') {
        $item['district'] = strtoupper(trim($item['block']));
    }

    $nvsList[] = $item;
}

// 5. Load all existing JNVs from database
$dbJNVs = $pdo->query("SELECT id, affiliation_no, school_code, udise_code, name, slug, state, district, pincode, address, website FROM `school` WHERE name LIKE '%NAVODAYA%' OR name LIKE '%JNV%'")->fetchAll(PDO::FETCH_ASSOC);

$dbMatchedIds = [];

// Pass 1: Exact UDISE code match (if previously stored)
foreach ($nvsList as &$nvs) {
    if (!empty($nvs['db_id'])) continue;
    foreach ($dbJNVs as $db) {
        if (isset($dbMatchedIds[$db['id']])) continue;
        if (!empty($db['udise_code']) && $db['udise_code'] === $nvs['udise_code']) {
            $nvs['db_id'] = $db['id'];
            $dbMatchedIds[$db['id']] = true;
            break;
        }
    }
}

// Pass 2: Pincode + State exact match
foreach ($nvsList as &$nvs) {
    if (!empty($nvs['db_id'])) continue;
    if (empty($nvs['pincode'])) continue;

    $nvsStateNorm = normalizeStateName($nvs['state']);
    foreach ($dbJNVs as $db) {
        if (isset($dbMatchedIds[$db['id']])) continue;
        $dbStateNorm = normalizeStateName($db['state']);
        if ($nvsStateNorm !== $dbStateNorm) continue;

        if ($db['pincode'] === $nvs['pincode'] || strpos($db['address'], $nvs['pincode']) !== false) {
            $nvs['db_id'] = $db['id'];
            $dbMatchedIds[$db['id']] = true;
            break;
        }
    }
}

// Pass 3: District + State exact match
foreach ($nvsList as &$nvs) {
    if (!empty($nvs['db_id'])) continue;
    $nvsStateNorm = normalizeStateName($nvs['state']);
    $nvsDist = strtoupper(trim($nvs['district']));
    if (empty($nvsDist)) continue;

    foreach ($dbJNVs as $db) {
        if (isset($dbMatchedIds[$db['id']])) continue;
        $dbStateNorm = normalizeStateName($db['state']);
        if ($nvsStateNorm !== $dbStateNorm) continue;

        $dbDist = strtoupper(trim($db['district']));
        if ($dbDist === $nvsDist || stripos($nvs['name'], $dbDist) !== false || stripos($db['name'], $nvsDist) !== false) {
            $nvs['db_id'] = $db['id'];
            $dbMatchedIds[$db['id']] = true;
            break;
        }
    }
}

// Pass 4: Token scoring match within same state
foreach ($nvsList as &$nvs) {
    if (!empty($nvs['db_id'])) continue;
    $nvsStateNorm = normalizeStateName($nvs['state']);
    $nvsTokens = cleanTokensList($nvs['name'] . ' ' . $nvs['district'] . ' ' . $nvs['address'] . ' ' . $nvs['block']);

    $bestMatch = null;
    $bestScore = 0;

    foreach ($dbJNVs as $db) {
        if (isset($dbMatchedIds[$db['id']])) continue;
        $dbStateNorm = normalizeStateName($db['state']);
        if ($nvsStateNorm !== $dbStateNorm) continue;

        $dbTokens = cleanTokensList($db['name'] . ' ' . $db['district'] . ' ' . $db['address']);
        $intersect = array_intersect($nvsTokens, $dbTokens);
        $score = count($intersect);

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestMatch = $db;
        }
    }

    if ($bestMatch && $bestScore >= 1) {
        $nvs['db_id'] = $bestMatch['id'];
        $dbMatchedIds[$bestMatch['id']] = true;
    }
}

// 6. Execute Updates and Inserts in Transaction
$pdo->beginTransaction();

$updateStmt = $pdo->prepare("
    UPDATE `school`
    SET 
        `udise_code` = :udise_code,
        `pincode` = COALESCE(NULLIF(:pincode, ''), `pincode`),
        `address` = COALESCE(NULLIF(:address, ''), `address`),
        `status` = 'Senior Secondary Level',
        `board` = 'CBSE',
        `updated_at` = NOW()
    WHERE `id` = :id
");

$insertStmt = $pdo->prepare("
    INSERT INTO `school` (
        `affiliation_no`,
        `school_code`,
        `udise_code`,
        `name`,
        `slug`,
        `board`,
        `status`,
        `head_name`,
        `state`,
        `district`,
        `address`,
        `website`,
        `pincode`,
        `created_at`,
        `updated_at`
    ) VALUES (
        :affiliation_no,
        :school_code,
        :udise_code,
        :name,
        :slug,
        'CBSE',
        'Senior Secondary Level',
        'Principal, JNV',
        :state,
        :district,
        :address,
        'https://navodaya.gov.in',
        :pincode,
        NOW(),
        NOW()
    )
");

$updatedCount = 0;
$insertedCount = 0;

foreach ($nvsList as $nvs) {
    if (!empty($nvs['db_id'])) {
        // UPDATE existing school
        $updateStmt->execute([
            ':udise_code' => $nvs['udise_code'],
            ':pincode' => $nvs['pincode'],
            ':address' => $nvs['address'],
            ':id' => $nvs['db_id'],
        ]);
        $updatedCount++;
    } else {
        // INSERT new school
        $cleanDistrict = $nvs['district'] ?: 'Navodaya';
        $slugBase = 'cbse-' . slugifyString($nvs['name'] ?: ('jnv-' . $cleanDistrict));
        
        // Ensure slug is unique
        $slug = $slugBase;
        $slugCheck = $pdo->prepare("SELECT id FROM `school` WHERE slug = ? LIMIT 1");
        $slugCheck->execute([$slug]);
        if ($slugCheck->fetch()) {
            $slug = $slugBase . '-' . substr($nvs['udise_code'], -4);
        }

        $affNo = 'NVS-' . $nvs['udise_code'];
        $schCode = substr($nvs['udise_code'], -5);

        $insertStmt->execute([
            ':affiliation_no' => $affNo,
            ':school_code' => $schCode,
            ':udise_code' => $nvs['udise_code'],
            ':name' => $nvs['name'] ?: ('JAWAHAR NAVODAYA VIDYALAYA ' . $cleanDistrict),
            ':slug' => $slug,
            ':state' => $nvs['state'] ?: 'All India',
            ':district' => $cleanDistrict,
            ':address' => $nvs['address'] ?: 'Jawahar Navodaya Vidyalaya Campus',
            ':pincode' => $nvs['pincode'] ?: '',
        ]);
        $insertedCount++;
    }
}

$pdo->commit();

echo "Database update completed successfully!\n";
echo "Schools Updated with UDISE / Address / PIN: {$updatedCount}\n";
echo "New JNVs Inserted: {$insertedCount}\n";
$newTotal = $pdo->query("SELECT COUNT(*) FROM `school`")->fetchColumn();
echo "New Total Schools in Database: {$newTotal}\n";
