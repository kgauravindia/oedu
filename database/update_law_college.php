<?php
// update_law_college.php — Clean, normalize, alter schema, and generate slugs for law_college table
require_once __DIR__ . '/includes/db.php';

echo "=== Online Education: law_college Table Synchronizer & Updater ===\n\n";

// 1. Expand columns & Add `slug` column if missing
echo "1. Checking and updating schema for `law_college`...\n";
$pdo->exec("ALTER TABLE `law_college` 
    MODIFY `state` VARCHAR(100) NULL,
    MODIFY `affiliating_university` VARCHAR(500) NULL,
    MODIFY `name` VARCHAR(500) NULL,
    MODIFY `course_name` VARCHAR(255) NULL,
    MODIFY `approval_till` VARCHAR(150) NULL,
    MODIFY `e_year` VARCHAR(50) NULL,
    MODIFY `remarks` TEXT NULL
");

$colCheck = $pdo->query("SHOW COLUMNS FROM `law_college` LIKE 'slug'");
if ($colCheck->rowCount() === 0) {
    echo "Adding `slug` column to `law_college`...\n";
    $pdo->exec("ALTER TABLE `law_college` ADD COLUMN `slug` VARCHAR(255) NULL AFTER `name`, ADD INDEX `idx_law_college_slug` (`slug`)");
}

// Add state & status indexes if not exists
try {
    $pdo->exec("ALTER TABLE `law_college` ADD INDEX `idx_law_college_state` (`state`)");
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE `law_college` ADD INDEX `idx_law_college_status` (`status`)");
} catch (Exception $e) {}

echo "Schema updated successfully.\n\n";

// 2. Remove header row(s)
echo "2. Removing invalid table header rows...\n";
$deleted = $pdo->exec("DELETE FROM `law_college` WHERE state = 'State' OR name LIKE '%College Name & Place%'");
echo "Deleted {$deleted} header row(s).\n\n";

// 3. Fix missing names in known orphaned rows
echo "3. Repairing missing college names...\n";
$pdo->exec("UPDATE `law_college` SET 
    name = 'CVM College of Law, Charutar Vidya Mandal University, Vallabh Vidyanagar',
    affiliating_university = 'THE CHARUTAR VIDYA MANDAL UNIVERSITY, MOTABAZAR, VALLABH VIDYANAGAR, GUJARAT'
    WHERE id IN (659, 660, 661) AND (name IS NULL OR TRIM(name) = '')");

$pdo->exec("UPDATE `law_college` SET 
    name = 'University Institute of Law, Sant Baba Bhag Singh University, Khiala, Jalandhar',
    affiliating_university = 'UNIVERSITY INSTITUTE OF LAW, SANT BABA BHAG SINGH UNIVERSITY, KHIALA, JALANDHAR'
    WHERE id = 2863 AND (name IS NULL OR TRIM(name) = '')");

$pdo->exec("UPDATE `law_college` SET 
    name = 'Apex School of Law, Apex University, Jaipur',
    affiliating_university = 'APEX UNIVERSITY, JAIPUR'
    WHERE id = 2891 AND (name IS NULL OR TRIM(name) = '')");

$pdo->exec("UPDATE `law_college` SET 
    name = 'Crescent School of Law, B.S. Abdur Rahman Crescent Institute of Science and Technology, Chennai',
    affiliating_university = 'B.S. ABDUR RAHMAN CRESCENT INSTITUTE OF SCIENCE AND TECHNOLOGY, CHENNAI'
    WHERE id = 3375 AND (name IS NULL OR TRIM(name) = '')");

// Any other remaining empty names
$remEmpty = $pdo->query("SELECT id, affiliating_university FROM `law_college` WHERE name IS NULL OR TRIM(name) = ''")->fetchAll();
foreach ($remEmpty as $row) {
    $fallbackName = !empty($row['affiliating_university']) ? ucwords(strtolower(trim($row['affiliating_university']))) : 'Law College';
    $pdo->prepare("UPDATE `law_college` SET name = ? WHERE id = ?")->execute([$fallbackName, $row['id']]);
}
echo "Missing names repaired.\n\n";

// 4. Normalize state names
echo "4. Normalizing state names...\n";
$stateFixes = [
    'Jammu and Kashm' => 'Jammu and Kashmir',
    'Kolkata' => 'West Bengal',
    'Meghalya' => 'Meghalaya',
    'Orissa/Odisha' => 'Odisha',
    'Pudduchery' => 'Puducherry',
    'Uttarkhand' => 'Uttarakhand',
    'New Delhi' => 'Delhi'
];
foreach ($stateFixes as $oldSt => $newSt) {
    $stmt = $pdo->prepare("UPDATE `law_college` SET state = ? WHERE state = ?");
    $stmt->execute([$newSt, $oldSt]);
    if ($stmt->rowCount() > 0) {
        echo "Updated state '{$oldSt}' -> '{$newSt}' ({$stmt->rowCount()} rows)\n";
    }
}
echo "State normalization complete.\n\n";

// 5. Clean up newlines, whitespace, and formatting in all text columns
echo "5. Cleaning up whitespace and newlines across records...\n";
// Approval till cleanup: replace "-\n" or "-\r\n" with "-", then clean spaces
$pdo->exec("UPDATE `law_college` SET approval_till = REPLACE(REPLACE(approval_till, '-\r\n', '-'), '-\n', '-') WHERE approval_till LIKE '%\n%' OR approval_till LIKE '%\r%'");
$pdo->exec("UPDATE `law_college` SET approval_till = REPLACE(REPLACE(approval_till, '\r', ' '), '\n', ' ') WHERE approval_till LIKE '%\n%' OR approval_till LIKE '%\r%'");
$pdo->exec("UPDATE `law_college` SET approval_till = TRIM(REGEXP_REPLACE(approval_till, '[[:space:]]+', ' ')) WHERE approval_till IS NOT NULL");

// Course name cleanup
$pdo->exec("UPDATE `law_college` SET course_name = REPLACE(REPLACE(course_name, '\r', ' '), '\n', ' ') WHERE course_name LIKE '%\n%' OR course_name LIKE '%\r%'");
$pdo->exec("UPDATE `law_college` SET course_name = TRIM(REGEXP_REPLACE(course_name, '[[:space:]]+', ' ')) WHERE course_name IS NOT NULL");

// Affiliating university cleanup
$pdo->exec("UPDATE `law_college` SET affiliating_university = REPLACE(REPLACE(affiliating_university, '\r', ' '), '\n', ' ') WHERE affiliating_university LIKE '%\n%' OR affiliating_university LIKE '%\r%'");
$pdo->exec("UPDATE `law_college` SET affiliating_university = TRIM(REGEXP_REPLACE(affiliating_university, '[[:space:]]+', ' ')) WHERE affiliating_university IS NOT NULL");

// Name cleanup: remove trailing commas, newlines, multiple spaces
$pdo->exec("UPDATE `law_college` SET name = REPLACE(REPLACE(name, '\r', ' '), '\n', ' ') WHERE name LIKE '%\n%' OR name LIKE '%\r%'");
$pdo->exec("UPDATE `law_college` SET name = TRIM(REGEXP_REPLACE(name, '[[:space:]]+', ' ')) WHERE name IS NOT NULL");
$pdo->exec("UPDATE `law_college` SET name = TRIM(TRAILING ',' FROM name) WHERE name LIKE '%,'");
$pdo->exec("UPDATE `law_college` SET name = TRIM(name) WHERE name IS NOT NULL");

// Remarks cleanup: replace '?' with '-' where it is year hyphen like 2026?27
$pdo->exec("UPDATE `law_college` SET remarks = REPLACE(remarks, '?', '-') WHERE remarks LIKE '%?%'");
$pdo->exec("UPDATE `law_college` SET remarks = TRIM(REGEXP_REPLACE(REPLACE(REPLACE(remarks, '\r', ' '), '\n', ' '), '[[:space:]]+', ' ')) WHERE remarks IS NOT NULL AND TRIM(remarks) != ''");

echo "Data formatting cleaned up successfully.\n\n";

// 6. Generate Clean Slugs
echo "6. Generating SEO Slugs for all Law Colleges...\n";

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'law-college' : $text;
}

// Group distinct colleges by (name, state, affiliating_university) to give same slug to all courses of a college
$distinctColleges = $pdo->query("SELECT name, state, affiliating_university, MIN(id) as first_id, COUNT(*) as cnt 
    FROM `law_college` 
    GROUP BY name, state, affiliating_university 
    ORDER BY first_id ASC")->fetchAll();

echo "Found " . count($distinctColleges) . " distinct college groupings across 5,400+ course records.\n";

$usedSlugs = [];
$updateStmt = $pdo->prepare("UPDATE `law_college` SET slug = :slug WHERE name = :name AND state = :state AND affiliating_university = :univ");

$slugCount = 0;
foreach ($distinctColleges as $col) {
    $baseSlug = slugify($col['name']);
    
    // If slug is too short or generic, append state
    if (strlen($baseSlug) < 8 || $baseSlug === 'law-college' || $baseSlug === 'government-law-college') {
        $baseSlug = slugify($col['name'] . ' ' . $col['state']);
    }

    $finalSlug = $baseSlug;
    $counter = 2;
    while (isset($usedSlugs[$finalSlug])) {
        // Try appending state first
        if ($counter === 2 && stripos($finalSlug, slugify($col['state'])) === false) {
            $finalSlug = slugify($col['name'] . ' ' . $col['state']);
        } else {
            $finalSlug = $baseSlug . '-' . $counter;
        }
        $counter++;
    }
    $usedSlugs[$finalSlug] = true;

    $updateStmt->execute([
        ':slug' => $finalSlug,
        ':name' => $col['name'],
        ':state' => $col['state'],
        ':univ' => $col['affiliating_university']
    ]);
    $slugCount++;
}

echo "Successfully assigned {$slugCount} unique college slugs to all law college records.\n\n";

// 7. Verification stats
$totalRows = $pdo->query("SELECT COUNT(*) FROM `law_college`")->fetchColumn();
$totalSlugs = $pdo->query("SELECT COUNT(DISTINCT slug) FROM `law_college` WHERE slug IS NOT NULL AND slug != ''")->fetchColumn();
$totalStates = $pdo->query("SELECT COUNT(DISTINCT state) FROM `law_college` WHERE state IS NOT NULL AND state != ''")->fetchColumn();
$emptySlugs = $pdo->query("SELECT COUNT(*) FROM `law_college` WHERE slug IS NULL OR slug = ''")->fetchColumn();

echo "=== Final Verification Summary ===\n";
echo "Total Rows: {$totalRows}\n";
echo "Distinct College Slugs: {$totalSlugs}\n";
echo "Covered States / UTs: {$totalStates}\n";
echo "Unassigned Slugs: {$emptySlugs}\n";
echo "Sample Slugs:\n";
$samples = $pdo->query("SELECT DISTINCT name, state, slug FROM `law_college` LIMIT 6")->fetchAll();
foreach ($samples as $s) {
    echo "- {$s['name']} ({$s['state']}) -> {$s['slug']}\n";
}
echo "\nUpdate completed successfully!\n";
