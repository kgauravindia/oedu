<?php
/**
 * api.php — Online Education National Education Platform Unified RESTful JSON API
 * 
 * Provides unified API endpoints for:
 *  - Live Omnibox Search & Autocomplete across 106,000+ educational institutions
 *  - Cascading State & District selectors (powered by op_sdb)
 *  - Universities Directory (AISHE)
 *  - Affiliated Colleges Directory (AISHE)
 *  - Standalone Institutes (Polytechnics, Nursing, PGDM)
 *  - CBSE Schools Directory (33,180+ schools)
 *  - BCI Approved Law Colleges Directory
 *  - National Entrance Examinations Calendar
 *  - Government & Welfare Scholarships Tracker
 *  - Verified Educators & Tutors Network
 *  - Admission Counseling Lead & Enquiry Submission (with DLT SMS OTP)
 *  - Platform Statistics & Directory Metrics
 */

// 1. CORS & Response Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');

// Handle CORS preflight early
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 2. Response Helpers
function jsonResponse(mixed $data = null, int $code = 200, string $status = 'success', string $message = '', array $meta = []): void {
    http_response_code($code);
    $payload = [
        'status' => $status,
        'code'   => $code,
    ];
    if ($message !== '') {
        $payload['message'] = $message;
    }
    if (!empty($meta)) {
        $payload['meta'] = $meta;
    }
    $payload['data'] = $data;

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function jsonError(string $message, int $code = 400, mixed $data = null, array $meta = []): void {
    jsonResponse($data, $code, 'error', $message, $meta);
}

// 3. Database Connection
$dbFile = __DIR__ . '/includes/db.php';
if (!file_exists($dbFile)) {
    jsonError('Database configuration file not found.', 500);
}

try {
    require_once $dbFile;
} catch (Throwable $e) {
    jsonError('Database connection failed: ' . $e->getMessage(), 500);
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    jsonError('Database connection handle not initialized.', 500);
}

// 4. Request Method & Parameters Extraction
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Parse JSON payload if sent via POST/PUT
$requestBody = [];
if (in_array($requestMethod, ['POST', 'PUT', 'PATCH'])) {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $decoded = json_decode($rawInput, true);
        if (is_array($decoded)) {
            $requestBody = $decoded;
        }
    }
}
$params = array_merge($_GET, $_POST, $requestBody);

// Determine requested action / endpoint
$action = trim((string)($params['action'] ?? $params['endpoint'] ?? ''));

// Fallback: Check PATH_INFO or URL segments (e.g. /api.php/universities or /api/universities)
if ($action === '') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (!empty($uriPath) && preg_match('#/(?:api\.php|api)/([a-zA-Z0-9_\-]+)#i', $uriPath, $matches)) {
        $action = $matches[1];
    }
}

// Secondary fallback: if 'type' is set without an explicit action, treat as search or listing
if ($action === '' && !empty($params['type'])) {
    $action = $params['type'];
}

$action = strtolower($action);

// Helper for integer sanitization
function cleanInt(mixed $val, int $default = 0, ?int $min = null, ?int $max = null): int {
    $int = is_numeric($val) ? (int)$val : $default;
    if ($min !== null && $int < $min) $int = $min;
    if ($max !== null && $int > $max) $int = $max;
    return $int;
}

// Helper to construct institution canonical URL
function makeInstUrl(string $type, ?string $slug, int|string $id = 0): string {
    if (!empty($slug)) {
        return match ($type) {
            'university' => 'university/' . rawurlencode($slug),
            'college'    => 'college/' . rawurlencode($slug),
            'institute'  => 'institute/' . rawurlencode($slug),
            'school'     => 'school/' . rawurlencode($slug),
            'law_college'=> 'law-college/' . rawurlencode($slug),
            'exam'       => 'exam.php?id=' . rawurlencode((string)$id),
            'tutor'      => 'tutor-detail.php?id=' . rawurlencode((string)$id),
            default      => 'search.php?q=' . rawurlencode($slug),
        };
    }
    return match ($type) {
        'university' => 'university-detail.php?id=' . (int)$id,
        'college'    => 'college-detail.php?id=' . (int)$id,
        'institute'  => 'institute-detail.php?id=' . (int)$id,
        'school'     => 'school-detail.php?id=' . (int)$id,
        'law_college'=> 'law-college-detail.php?id=' . (int)$id,
        default      => 'index.php',
    };
}

// 5. Route Handling
switch ($action) {

    // -------------------------------------------------------------
    // PING / HEALTH
    // -------------------------------------------------------------
    case 'ping':
    case 'health':
        try {
            $pdo->query("SELECT 1");
            jsonResponse([
                'status'    => 'ok',
                'database'  => 'connected',
                'timestamp' => date('c'),
            ], 200, 'success', 'Online Education API is operational.');
        } catch (Throwable $e) {
            jsonError('Database check failed: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // STATS / OVERVIEW
    // -------------------------------------------------------------
    case 'stats':
    case 'statistics':
    case 'overview':
        try {
            $uniCount      = (int)$pdo->query("SELECT COUNT(*) FROM university WHERE status = 'ACTIVE' OR status IS NULL")->fetchColumn();
            $collegeCount  = (int)$pdo->query("SELECT COUNT(*) FROM college")->fetchColumn();
            $instCount     = (int)$pdo->query("SELECT COUNT(*) FROM institute")->fetchColumn();
            $schoolCount   = (int)$pdo->query("SELECT COUNT(*) FROM `school`")->fetchColumn();
            $lawCount      = (int)$pdo->query("SELECT COUNT(DISTINCT slug) FROM law_college WHERE status = 'ACTIVE'")->fetchColumn();
            $examCount     = (int)$pdo->query("SELECT COUNT(*) FROM examination")->fetchColumn();
            $tutorCount    = (int)$pdo->query("SELECT COUNT(*) FROM educator")->fetchColumn();
            $scholarCount  = (int)$pdo->query("SELECT COUNT(*) FROM scholarship WHERE is_active = 1")->fetchColumn();
            $statesCount   = (int)$pdo->query("SELECT COUNT(DISTINCT state) FROM op_sdb WHERE state IS NOT NULL AND state != ''")->fetchColumn();
            $districtCount = (int)$pdo->query("SELECT COUNT(DISTINCT district) FROM op_sdb WHERE district IS NOT NULL AND district != ''")->fetchColumn();

            $totalInstitutions = $uniCount + $collegeCount + $instCount + $schoolCount + $lawCount;

            jsonResponse([
                'total_institutions' => $totalInstitutions,
                'universities'       => $uniCount,
                'colleges'           => $collegeCount,
                'standalone_institutes' => $instCount,
                'cbse_schools'       => $schoolCount,
                'law_colleges'       => $lawCount,
                'examinations'       => $examCount,
                'verified_educators' => $tutorCount,
                'scholarships'       => $scholarCount,
                'coverage' => [
                    'states_and_uts' => $statesCount ?: 36,
                    'districts'      => $districtCount ?: 780,
                ],
            ], 200, 'success', 'Platform directory statistics retrieved successfully.');
        } catch (Throwable $e) {
            jsonError('Failed to retrieve statistics: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // STATES
    // -------------------------------------------------------------
    case 'states':
        try {
            $stmt = $pdo->query("SELECT DISTINCT state FROM op_sdb WHERE state IS NOT NULL AND state != '' ORDER BY state ASC");
            $states = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Fallback to university table if op_sdb is empty
            if (empty($states)) {
                $stmt = $pdo->query("SELECT DISTINCT state FROM university WHERE state IS NOT NULL AND state != '' ORDER BY state ASC");
                $states = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            jsonResponse($states, 200, 'success', count($states) . ' states and union territories retrieved.');
        } catch (Throwable $e) {
            jsonError('Failed to fetch states: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // DISTRICTS
    // -------------------------------------------------------------
    case 'districts':
    case 'district':
        $state = trim((string)($params['state'] ?? ''));
        if ($state === '') {
            jsonError('The "state" query parameter is required to fetch districts.', 400);
        }

        try {
            $stmt = $pdo->prepare("SELECT DISTINCT district FROM op_sdb WHERE state = :state AND district IS NOT NULL AND district != '' ORDER BY district ASC");
            $stmt->execute([':state' => $state]);
            $districts = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Backward compatibility flag: format=flat returns pure array like api/districts.php
            if (($params['format'] ?? '') === 'flat') {
                echo json_encode($districts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }

            jsonResponse($districts, 200, 'success', count($districts) . " districts retrieved for {$state}.");
        } catch (Throwable $e) {
            jsonError('Failed to fetch districts: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // UNIFIED SEARCH / AUTOCOMPLETE
    // -------------------------------------------------------------
    case 'search':
    case 'find':
    case 'autocomplete':
    case 'suggest':
        $query    = trim((string)($params['q'] ?? $params['query'] ?? ''));
        $type     = strtolower(trim((string)($params['type'] ?? 'all')));
        $state    = trim((string)($params['state'] ?? ''));
        $district = trim((string)($params['district'] ?? ''));
        $page     = cleanInt($params['page'] ?? 1, 1, 1);
        $limit    = cleanInt($params['limit'] ?? 20, 20, 1, 100);
        $offset   = ($page - 1) * $limit;

        $wildcard = "%{$query}%";
        $results  = [];

        try {
            // 1. Universities
            if (in_array($type, ['all', 'universities', 'university', 'institutions'])) {
                $sql = "SELECT id, name, slug, aishe_code, state, district, university_type FROM university WHERE 1=1";
                $p = [];
                if ($query !== '') {
                    $sql .= " AND (name LIKE ? OR aishe_code LIKE ? OR district LIKE ? OR state LIKE ?)";
                    $p = array_merge($p, [$wildcard, $wildcard, $wildcard, $wildcard]);
                }
                if ($state !== '') {
                    $sql .= " AND state = ?";
                    $p[] = $state;
                }
                if ($district !== '') {
                    $sql .= " AND district LIKE ?";
                    $p[] = "%{$district}%";
                }
                $sql .= " ORDER BY (CASE WHEN name LIKE ? THEN 1 ELSE 2 END), name ASC LIMIT ?";
                $p[] = "{$query}%";
                $p[] = $limit;

                $stmt = $pdo->prepare($sql);
                // Bind integers for LIMIT
                foreach ($p as $idx => $val) {
                    $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    $results[] = [
                        'id'         => (int)$row['id'],
                        'type'       => 'university',
                        'type_label' => 'University',
                        'title'      => $row['name'],
                        'subtitle'   => ($row['university_type'] ?? 'University') . ' • AISHE: ' . ($row['aishe_code'] ?? 'N/A'),
                        'state'      => $row['state'] ?? '',
                        'district'   => $row['district'] ?? '',
                        'code'       => $row['aishe_code'] ?? '',
                        'slug'       => $row['slug'] ?? '',
                        'url'        => makeInstUrl('university', $row['slug'] ?? '', $row['id']),
                    ];
                }
            }

            // 2. Colleges
            if (in_array($type, ['all', 'colleges', 'college', 'institutions'])) {
                $sql = "SELECT id, name, slug, aishe_code, state, district, university_name FROM college WHERE 1=1";
                $p = [];
                if ($query !== '') {
                    $sql .= " AND (name LIKE ? OR aishe_code LIKE ? OR university_name LIKE ? OR district LIKE ? OR state LIKE ?)";
                    $p = array_merge($p, [$wildcard, $wildcard, $wildcard, $wildcard, $wildcard]);
                }
                if ($state !== '') {
                    $sql .= " AND state = ?";
                    $p[] = $state;
                }
                if ($district !== '') {
                    $sql .= " AND district LIKE ?";
                    $p[] = "%{$district}%";
                }
                $sql .= " ORDER BY (CASE WHEN name LIKE ? THEN 1 ELSE 2 END), name ASC LIMIT ?";
                $p[] = "{$query}%";
                $p[] = $limit;

                $stmt = $pdo->prepare($sql);
                foreach ($p as $idx => $val) {
                    $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    $results[] = [
                        'id'         => (int)$row['id'],
                        'type'       => 'college',
                        'type_label' => 'Affiliated College',
                        'title'      => $row['name'],
                        'subtitle'   => 'Affiliated to ' . ($row['university_name'] ?? 'State University') . ' • AISHE: ' . ($row['aishe_code'] ?? 'N/A'),
                        'state'      => $row['state'] ?? '',
                        'district'   => $row['district'] ?? '',
                        'code'       => $row['aishe_code'] ?? '',
                        'slug'       => $row['slug'] ?? '',
                        'url'        => makeInstUrl('college', $row['slug'] ?? '', $row['id']),
                    ];
                }
            }

            // 3. Standalone Institutes
            if (in_array($type, ['all', 'institutes', 'institute', 'standalones', 'institutions'])) {
                $sql = "SELECT id, name, slug, aishe_code, state, district, institute_type FROM institute WHERE 1=1";
                $p = [];
                if ($query !== '') {
                    $sql .= " AND (name LIKE ? OR aishe_code LIKE ? OR institute_type LIKE ? OR district LIKE ? OR state LIKE ?)";
                    $p = array_merge($p, [$wildcard, $wildcard, $wildcard, $wildcard, $wildcard]);
                }
                if ($state !== '') {
                    $sql .= " AND state = ?";
                    $p[] = $state;
                }
                if ($district !== '') {
                    $sql .= " AND district LIKE ?";
                    $p[] = "%{$district}%";
                }
                $sql .= " ORDER BY (CASE WHEN name LIKE ? THEN 1 ELSE 2 END), name ASC LIMIT ?";
                $p[] = "{$query}%";
                $p[] = $limit;

                $stmt = $pdo->prepare($sql);
                foreach ($p as $idx => $val) {
                    $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    $results[] = [
                        'id'         => (int)$row['id'],
                        'type'       => 'institute',
                        'type_label' => 'Standalone Institute',
                        'title'      => $row['name'],
                        'subtitle'   => ($row['institute_type'] ?? 'Diploma/Polytechnic') . ' • AISHE: ' . ($row['aishe_code'] ?? 'N/A'),
                        'state'      => $row['state'] ?? '',
                        'district'   => $row['district'] ?? '',
                        'code'       => $row['aishe_code'] ?? '',
                        'slug'       => $row['slug'] ?? '',
                        'url'        => makeInstUrl('institute', $row['slug'] ?? '', $row['id']),
                    ];
                }
            }

            // 4. CBSE Schools
            if (in_array($type, ['all', 'schools', 'school', 'cbse', 'institutions'])) {
                $sql = "SELECT id, name, slug, affiliation_no, school_code, state, district FROM `school` WHERE 1=1";
                $p = [];
                if ($query !== '') {
                    $sql .= " AND (name LIKE ? OR affiliation_no LIKE ? OR school_code LIKE ? OR district LIKE ? OR state LIKE ?)";
                    $p = array_merge($p, [$wildcard, $wildcard, $wildcard, $wildcard, $wildcard]);
                }
                if ($state !== '') {
                    $sql .= " AND state = ?";
                    $p[] = $state;
                }
                if ($district !== '') {
                    $sql .= " AND district LIKE ?";
                    $p[] = "%{$district}%";
                }
                $sql .= " ORDER BY (CASE WHEN name LIKE ? THEN 1 ELSE 2 END), name ASC LIMIT ?";
                $p[] = "{$query}%";
                $p[] = $limit;

                $stmt = $pdo->prepare($sql);
                foreach ($p as $idx => $val) {
                    $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    $results[] = [
                        'id'         => (int)$row['id'],
                        'type'       => 'school',
                        'type_label' => 'CBSE School',
                        'title'      => $row['name'],
                        'subtitle'   => 'CBSE Affiliation: ' . ($row['affiliation_no'] ?? 'N/A') . ' • Code: ' . ($row['school_code'] ?? 'N/A'),
                        'state'      => $row['state'] ?? '',
                        'district'   => $row['district'] ?? '',
                        'code'       => $row['affiliation_no'] ?? '',
                        'slug'       => $row['slug'] ?? '',
                        'url'        => makeInstUrl('school', $row['slug'] ?? '', $row['id']),
                    ];
                }
            }

            // 5. Law Colleges (BCI Approved)
            if (in_array($type, ['all', 'law', 'law_colleges', 'law_college'])) {
                $sql = "SELECT id, name, slug, state, affiliating_university, course_name FROM law_college WHERE status = 'ACTIVE'";
                $p = [];
                if ($query !== '') {
                    $sql .= " AND (name LIKE ? OR affiliating_university LIKE ? OR course_name LIKE ? OR state LIKE ?)";
                    $p = array_merge($p, [$wildcard, $wildcard, $wildcard, $wildcard]);
                }
                if ($state !== '') {
                    $sql .= " AND state = ?";
                    $p[] = $state;
                }
                $sql .= " GROUP BY slug, name, state, affiliating_university ORDER BY name ASC LIMIT ?";
                $p[] = $limit;

                $stmt = $pdo->prepare($sql);
                foreach ($p as $idx => $val) {
                    $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    $results[] = [
                        'id'         => (int)$row['id'],
                        'type'       => 'law_college',
                        'type_label' => 'BCI Law College',
                        'title'      => $row['name'],
                        'subtitle'   => 'BCI Approved • ' . ($row['affiliating_university'] ?? 'Recognized University'),
                        'state'      => $row['state'] ?? '',
                        'district'   => '',
                        'code'       => '',
                        'slug'       => $row['slug'] ?? '',
                        'url'        => makeInstUrl('law_college', $row['slug'] ?? '', $row['id']),
                    ];
                }
            }

            // 6. Examinations
            if (in_array($type, ['all', 'exams', 'exam', 'examinations'])) {
                $sql = "SELECT id, title, code, category, conducting_body, exam_date FROM examination WHERE 1=1";
                $p = [];
                if ($query !== '') {
                    $sql .= " AND (title LIKE ? OR code LIKE ? OR category LIKE ? OR conducting_body LIKE ?)";
                    $p = array_merge($p, [$wildcard, $wildcard, $wildcard, $wildcard]);
                }
                $sql .= " ORDER BY is_featured DESC, id ASC LIMIT ?";
                $p[] = $limit;

                $stmt = $pdo->prepare($sql);
                foreach ($p as $idx => $val) {
                    $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    $results[] = [
                        'id'         => (int)$row['id'],
                        'type'       => 'exam',
                        'type_label' => 'Entrance Exam',
                        'title'      => $row['title'] . (!empty($row['code']) ? " ({$row['code']})" : ''),
                        'subtitle'   => ($row['category'] ?? 'National') . ' • Conducted by ' . ($row['conducting_body'] ?? 'NTA'),
                        'state'      => 'All India',
                        'district'   => '',
                        'code'       => $row['code'] ?? '',
                        'slug'       => '',
                        'url'        => 'exam.php?category=' . rawurlencode($row['category'] ?? ''),
                    ];
                }
            }

            // Cap results if 'all' is requested
            if ($type === 'all' && count($results) > $limit) {
                $results = array_slice($results, 0, $limit);
            }

            jsonResponse($results, 200, 'success', count($results) . ' matching results found.', [
                'query'    => $query,
                'type'     => $type,
                'count'    => count($results),
                'page'     => $page,
                'limit'    => $limit,
            ]);
        } catch (Throwable $e) {
            jsonError('Search execution failed: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // UNIVERSITIES
    // -------------------------------------------------------------
    case 'universities':
    case 'university':
        $slug  = trim((string)($params['slug'] ?? ''));
        $aishe = trim((string)($params['aishe'] ?? $params['aishe_code'] ?? ''));
        $id    = cleanInt($params['id'] ?? 0);

        // Single lookup
        if ($slug !== '' || $aishe !== '' || $id > 0) {
            try {
                if ($slug !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM university WHERE slug = ? LIMIT 1");
                    $stmt->execute([$slug]);
                } elseif ($aishe !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM university WHERE aishe_code = ? LIMIT 1");
                    $stmt->execute([$aishe]);
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM university WHERE id = ? LIMIT 1");
                    $stmt->execute([$id]);
                }
                $item = $stmt->fetch();
                if (!$item) {
                    jsonError('University not found.', 404);
                }
                $item['canonical_url'] = makeInstUrl('university', $item['slug'] ?? '', $item['id']);
                jsonResponse($item, 200, 'success', 'University details retrieved successfully.');
            } catch (Throwable $e) {
                jsonError('Database query failed: ' . $e->getMessage(), 500);
            }
        }

        // List lookup with filters & pagination
        $state    = trim((string)($params['state'] ?? ''));
        $district = trim((string)($params['district'] ?? ''));
        $uniType  = trim((string)($params['type_name'] ?? $params['university_type'] ?? ''));
        $search   = trim((string)($params['q'] ?? ''));
        $page     = cleanInt($params['page'] ?? 1, 1, 1);
        $limit    = cleanInt($params['limit'] ?? 20, 20, 1, 100);
        $offset   = ($page - 1) * $limit;

        try {
            $where = ["1=1"];
            $binds = [];
            if ($search !== '') {
                $where[] = "(name LIKE ? OR aishe_code LIKE ?)";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
            }
            if ($state !== '') {
                $where[] = "state = ?";
                $binds[] = $state;
            }
            if ($district !== '') {
                $where[] = "district LIKE ?";
                $binds[] = "%{$district}%";
            }
            if ($uniType !== '') {
                $where[] = "university_type = ?";
                $binds[] = $uniType;
            }

            $whereSql = implode(' AND ', $where);
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM university WHERE {$whereSql}");
            $countStmt->execute($binds);
            $total = (int)$countStmt->fetchColumn();

            $sql = "SELECT id, name, slug, aishe_code, state, district, university_type, website, year_of_establishment 
                    FROM university 
                    WHERE {$whereSql} 
                    ORDER BY name ASC 
                    LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($sql);
            $pIdx = 1;
            foreach ($binds as $b) {
                $stmt->bindValue($pIdx++, $b);
            }
            $stmt->bindValue($pIdx++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($pIdx++, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();

            foreach ($items as &$it) {
                $it['canonical_url'] = makeInstUrl('university', $it['slug'] ?? '', $it['id']);
            }

            jsonResponse($items, 200, 'success', count($items) . ' universities retrieved.', [
                'total'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => (int)ceil($total / max(1, $limit)),
            ]);
        } catch (Throwable $e) {
            jsonError('Failed to fetch universities: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // COLLEGES
    // -------------------------------------------------------------
    case 'colleges':
    case 'college':
        $slug  = trim((string)($params['slug'] ?? ''));
        $aishe = trim((string)($params['aishe'] ?? $params['aishe_code'] ?? ''));
        $id    = cleanInt($params['id'] ?? 0);

        if ($slug !== '' || $aishe !== '' || $id > 0) {
            try {
                if ($slug !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM college WHERE slug = ? LIMIT 1");
                    $stmt->execute([$slug]);
                } elseif ($aishe !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM college WHERE aishe_code = ? LIMIT 1");
                    $stmt->execute([$aishe]);
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM college WHERE id = ? LIMIT 1");
                    $stmt->execute([$id]);
                }
                $item = $stmt->fetch();
                if (!$item) {
                    jsonError('College not found.', 404);
                }
                $item['canonical_url'] = makeInstUrl('college', $item['slug'] ?? '', $item['id']);
                jsonResponse($item, 200, 'success', 'College details retrieved successfully.');
            } catch (Throwable $e) {
                jsonError('Database query failed: ' . $e->getMessage(), 500);
            }
        }

        $state      = trim((string)($params['state'] ?? ''));
        $district   = trim((string)($params['district'] ?? ''));
        $university = trim((string)($params['university'] ?? ''));
        $search     = trim((string)($params['q'] ?? ''));
        $page       = cleanInt($params['page'] ?? 1, 1, 1);
        $limit      = cleanInt($params['limit'] ?? 20, 20, 1, 100);
        $offset     = ($page - 1) * $limit;

        try {
            $where = ["1=1"];
            $binds = [];
            if ($search !== '') {
                $where[] = "(name LIKE ? OR aishe_code LIKE ?)";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
            }
            if ($state !== '') {
                $where[] = "state = ?";
                $binds[] = $state;
            }
            if ($district !== '') {
                $where[] = "district LIKE ?";
                $binds[] = "%{$district}%";
            }
            if ($university !== '') {
                $where[] = "university_name LIKE ?";
                $binds[] = "%{$university}%";
            }

            $whereSql = implode(' AND ', $where);
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM college WHERE {$whereSql}");
            $countStmt->execute($binds);
            $total = (int)$countStmt->fetchColumn();

            $sql = "SELECT id, name, slug, aishe_code, state, district, university_name, management_type 
                    FROM college 
                    WHERE {$whereSql} 
                    ORDER BY name ASC 
                    LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($sql);
            $pIdx = 1;
            foreach ($binds as $b) {
                $stmt->bindValue($pIdx++, $b);
            }
            $stmt->bindValue($pIdx++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($pIdx++, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();

            foreach ($items as &$it) {
                $it['canonical_url'] = makeInstUrl('college', $it['slug'] ?? '', $it['id']);
            }

            jsonResponse($items, 200, 'success', count($items) . ' colleges retrieved.', [
                'total'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => (int)ceil($total / max(1, $limit)),
            ]);
        } catch (Throwable $e) {
            jsonError('Failed to fetch colleges: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // STANDALONE INSTITUTES
    // -------------------------------------------------------------
    case 'institutes':
    case 'institute':
    case 'standalones':
        $slug  = trim((string)($params['slug'] ?? ''));
        $aishe = trim((string)($params['aishe'] ?? $params['aishe_code'] ?? ''));
        $id    = cleanInt($params['id'] ?? 0);

        if ($slug !== '' || $aishe !== '' || $id > 0) {
            try {
                if ($slug !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM institute WHERE slug = ? LIMIT 1");
                    $stmt->execute([$slug]);
                } elseif ($aishe !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM institute WHERE aishe_code = ? LIMIT 1");
                    $stmt->execute([$aishe]);
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM institute WHERE id = ? LIMIT 1");
                    $stmt->execute([$id]);
                }
                $item = $stmt->fetch();
                if (!$item) {
                    jsonError('Institute not found.', 404);
                }
                $item['canonical_url'] = makeInstUrl('institute', $item['slug'] ?? '', $item['id']);
                jsonResponse($item, 200, 'success', 'Institute details retrieved successfully.');
            } catch (Throwable $e) {
                jsonError('Database query failed: ' . $e->getMessage(), 500);
            }
        }

        $state    = trim((string)($params['state'] ?? ''));
        $district = trim((string)($params['district'] ?? ''));
        $instType = trim((string)($params['type_name'] ?? $params['institute_type'] ?? ''));
        $search   = trim((string)($params['q'] ?? ''));
        $page     = cleanInt($params['page'] ?? 1, 1, 1);
        $limit    = cleanInt($params['limit'] ?? 20, 20, 1, 100);
        $offset   = ($page - 1) * $limit;

        try {
            $where = ["1=1"];
            $binds = [];
            if ($search !== '') {
                $where[] = "(name LIKE ? OR aishe_code LIKE ?)";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
            }
            if ($state !== '') {
                $where[] = "state = ?";
                $binds[] = $state;
            }
            if ($district !== '') {
                $where[] = "district LIKE ?";
                $binds[] = "%{$district}%";
            }
            if ($instType !== '') {
                $where[] = "institute_type LIKE ?";
                $binds[] = "%{$instType}%";
            }

            $whereSql = implode(' AND ', $where);
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM institute WHERE {$whereSql}");
            $countStmt->execute($binds);
            $total = (int)$countStmt->fetchColumn();

            $sql = "SELECT id, name, slug, aishe_code, state, district, institute_type 
                    FROM institute 
                    WHERE {$whereSql} 
                    ORDER BY name ASC 
                    LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($sql);
            $pIdx = 1;
            foreach ($binds as $b) {
                $stmt->bindValue($pIdx++, $b);
            }
            $stmt->bindValue($pIdx++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($pIdx++, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();

            foreach ($items as &$it) {
                $it['canonical_url'] = makeInstUrl('institute', $it['slug'] ?? '', $it['id']);
            }

            jsonResponse($items, 200, 'success', count($items) . ' standalone institutes retrieved.', [
                'total'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => (int)ceil($total / max(1, $limit)),
            ]);
        } catch (Throwable $e) {
            jsonError('Failed to fetch institutes: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // CBSE SCHOOLS
    // -------------------------------------------------------------
    case 'schools':
    case 'school':
    case 'cbse':
        $slug    = trim((string)($params['slug'] ?? ''));
        $affNo   = trim((string)($params['aff'] ?? $params['affiliation_no'] ?? ''));
        $schCode = trim((string)($params['code'] ?? $params['school_code'] ?? ''));
        $id      = cleanInt($params['id'] ?? 0);

        if ($slug !== '' || $affNo !== '' || $schCode !== '' || $id > 0) {
            try {
                if ($slug !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM `school` WHERE slug = ? LIMIT 1");
                    $stmt->execute([$slug]);
                    $item = $stmt->fetch();
                    // Fallback retry with 'cbse-' prefix
                    if (!$item && !str_starts_with($slug, 'cbse-')) {
                        $stmt->execute(['cbse-' . $slug]);
                        $item = $stmt->fetch();
                    }
                } elseif ($affNo !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM `school` WHERE affiliation_no = ? LIMIT 1");
                    $stmt->execute([$affNo]);
                    $item = $stmt->fetch();
                } elseif ($schCode !== '') {
                    $stmt = $pdo->prepare("SELECT * FROM `school` WHERE school_code = ? LIMIT 1");
                    $stmt->execute([$schCode]);
                    $item = $stmt->fetch();
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM `school` WHERE id = ? LIMIT 1");
                    $stmt->execute([$id]);
                    $item = $stmt->fetch();
                }

                if (!$item) {
                    jsonError('CBSE school not found.', 404);
                }
                $item['canonical_url'] = makeInstUrl('school', $item['slug'] ?? '', $item['id']);
                jsonResponse($item, 200, 'success', 'School details retrieved successfully.');
            } catch (Throwable $e) {
                jsonError('Database query failed: ' . $e->getMessage(), 500);
            }
        }

        $state    = trim((string)($params['state'] ?? ''));
        $district = trim((string)($params['district'] ?? ''));
        $search   = trim((string)($params['q'] ?? ''));
        $page     = cleanInt($params['page'] ?? 1, 1, 1);
        $limit    = cleanInt($params['limit'] ?? 20, 20, 1, 100);
        $offset   = ($page - 1) * $limit;

        try {
            $where = ["1=1"];
            $binds = [];
            if ($search !== '') {
                $where[] = "(name LIKE ? OR affiliation_no LIKE ? OR school_code LIKE ?)";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
            }
            if ($state !== '') {
                $where[] = "state = ?";
                $binds[] = $state;
            }
            if ($district !== '') {
                $where[] = "district LIKE ?";
                $binds[] = "%{$district}%";
            }

            $whereSql = implode(' AND ', $where);
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `school` WHERE {$whereSql}");
            $countStmt->execute($binds);
            $total = (int)$countStmt->fetchColumn();

            $sql = "SELECT id, name, slug, affiliation_no, school_code, udise_code, state, district, head_name, phone_no, email 
                    FROM `school` 
                    WHERE {$whereSql} 
                    ORDER BY name ASC 
                    LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($sql);
            $pIdx = 1;
            foreach ($binds as $b) {
                $stmt->bindValue($pIdx++, $b);
            }
            $stmt->bindValue($pIdx++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($pIdx++, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();

            foreach ($items as &$it) {
                $it['canonical_url'] = makeInstUrl('school', $it['slug'] ?? '', $it['id']);
            }

            jsonResponse($items, 200, 'success', count($items) . ' CBSE schools retrieved.', [
                'total'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => (int)ceil($total / max(1, $limit)),
            ]);
        } catch (Throwable $e) {
            jsonError('Failed to fetch CBSE schools: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // LAW COLLEGES (BCI Approved)
    // -------------------------------------------------------------
    case 'law_colleges':
    case 'law-colleges':
    case 'law_college':
    case 'law':
        $slug = trim((string)($params['slug'] ?? ''));
        $id   = cleanInt($params['id'] ?? 0);

        if ($slug !== '' || $id > 0) {
            try {
                if ($slug === '' && $id > 0) {
                    $slugStmt = $pdo->prepare("SELECT slug FROM law_college WHERE id = ? LIMIT 1");
                    $slugStmt->execute([$id]);
                    $slug = (string)$slugStmt->fetchColumn();
                }

                $stmt = $pdo->prepare("SELECT * FROM law_college WHERE slug = ? ORDER BY id ASC");
                $stmt->execute([$slug]);
                $courses = $stmt->fetchAll();

                if (empty($courses)) {
                    jsonError('Law college not found.', 404);
                }

                $primary = $courses[0];
                $primary['courses'] = $courses;
                $primary['canonical_url'] = makeInstUrl('law_college', $slug, $primary['id'] ?? 0);

                jsonResponse($primary, 200, 'success', 'Law college details retrieved successfully.');
            } catch (Throwable $e) {
                jsonError('Database query failed: ' . $e->getMessage(), 500);
            }
        }

        $state  = trim((string)($params['state'] ?? ''));
        $search = trim((string)($params['q'] ?? ''));
        $page   = cleanInt($params['page'] ?? 1, 1, 1);
        $limit  = cleanInt($params['limit'] ?? 20, 20, 1, 100);
        $offset = ($page - 1) * $limit;

        try {
            $where = ["status = 'ACTIVE'"];
            $binds = [];
            if ($search !== '') {
                $where[] = "(name LIKE ? OR affiliating_university LIKE ? OR course_name LIKE ?)";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
            }
            if ($state !== '') {
                $where[] = "state = ?";
                $binds[] = $state;
            }

            $whereSql = implode(' AND ', $where);

            $countSql = "SELECT COUNT(DISTINCT slug) FROM law_college WHERE {$whereSql}";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($binds);
            $total = (int)$countStmt->fetchColumn();

            $sql = "SELECT 
                        MIN(id) as id,
                        slug,
                        name,
                        state,
                        affiliating_university,
                        MIN(NULLIF(e_year, '')) as est_year,
                        COUNT(*) as total_courses,
                        GROUP_CONCAT(course_name SEPARATOR ' || ') as courses_list,
                        MAX(approval_till) as latest_approval
                    FROM law_college 
                    WHERE {$whereSql}
                    GROUP BY slug, name, state, affiliating_university
                    ORDER BY name ASC 
                    LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($sql);
            $pIdx = 1;
            foreach ($binds as $b) {
                $stmt->bindValue($pIdx++, $b);
            }
            $stmt->bindValue($pIdx++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($pIdx++, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();

            foreach ($items as &$it) {
                $it['canonical_url'] = makeInstUrl('law_college', $it['slug'] ?? '', $it['id']);
            }

            jsonResponse($items, 200, 'success', count($items) . ' BCI law colleges retrieved.', [
                'total'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => (int)ceil($total / max(1, $limit)),
            ]);
        } catch (Throwable $e) {
            jsonError('Failed to fetch law colleges: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // ENTRANCE EXAMINATIONS
    // -------------------------------------------------------------
    case 'exams':
    case 'examination':
    case 'entrance_exams':
        $category = trim((string)($params['category'] ?? ''));
        $search   = trim((string)($params['q'] ?? ''));

        try {
            $where = ["1=1"];
            $binds = [];
            if ($category !== '') {
                $where[] = "category = ?";
                $binds[] = $category;
            }
            if ($search !== '') {
                $where[] = "(title LIKE ? OR code LIKE ? OR conducting_body LIKE ?)";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
            }
            $whereSql = implode(' AND ', $where);
            $stmt = $pdo->prepare("SELECT * FROM examination WHERE {$whereSql} ORDER BY is_featured DESC, id ASC");
            $stmt->execute($binds);
            $exams = $stmt->fetchAll();

            jsonResponse($exams, 200, 'success', count($exams) . ' entrance examinations retrieved.');
        } catch (Throwable $e) {
            jsonError('Failed to fetch examinations: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // SCHOLARSHIPS
    // -------------------------------------------------------------
    case 'scholarships':
    case 'scholarship':
        $provider = trim((string)($params['provider'] ?? ''));
        $state    = trim((string)($params['state'] ?? ''));
        $search   = trim((string)($params['q'] ?? ''));

        try {
            $where = ["is_active = 1"];
            $binds = [];
            if ($provider !== '') {
                $where[] = "provider_type = ?";
                $binds[] = $provider;
            }
            if ($state !== '') {
                $where[] = "(state = ? OR state = 'All India' OR state IS NULL OR state = '')";
                $binds[] = $state;
            }
            if ($search !== '') {
                $where[] = "(title LIKE ? OR ministry_or_body LIKE ? OR eligibility LIKE ?)";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
            }
            $whereSql = implode(' AND ', $where);
            $stmt = $pdo->prepare("SELECT * FROM scholarship WHERE {$whereSql} ORDER BY id ASC");
            $stmt->execute($binds);
            $scholarships = $stmt->fetchAll();

            jsonResponse($scholarships, 200, 'success', count($scholarships) . ' scholarships retrieved.');
        } catch (Throwable $e) {
            jsonError('Failed to fetch scholarships: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // EDUCATORS & TUTORS
    // -------------------------------------------------------------
    case 'tutors':
    case 'educators':
    case 'tutor':
    case 'teachers':
        $id = cleanInt($params['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM educator WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $educator = $stmt->fetch();
                if (!$educator) {
                    jsonError('Educator not found.', 404);
                }
                jsonResponse($educator, 200, 'success', 'Educator profile retrieved successfully.');
            } catch (Throwable $e) {
                jsonError('Failed to fetch educator: ' . $e->getMessage(), 500);
            }
        }

        $state  = trim((string)($params['state'] ?? ''));
        $mode   = trim((string)($params['mode'] ?? ''));
        $search = trim((string)($params['q'] ?? ''));

        try {
            $where = ["1=1"];
            $binds = [];
            if ($state !== '') {
                $where[] = "state LIKE ?";
                $binds[] = "%{$state}%";
            }
            if ($mode !== '') {
                $where[] = "mode = ?";
                $binds[] = $mode;
            }
            if ($search !== '') {
                $where[] = "(name LIKE ? OR headline LIKE ? OR city LIKE ?)";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
                $binds[] = "%{$search}%";
            }
            $whereSql = implode(' AND ', $where);
            $stmt = $pdo->prepare("SELECT * FROM educator WHERE {$whereSql} ORDER BY verification_status = 'verified' DESC, rating DESC");
            $stmt->execute($binds);
            $tutors = $stmt->fetchAll();

            jsonResponse($tutors, 200, 'success', count($tutors) . ' verified educators retrieved.');
        } catch (Throwable $e) {
            jsonError('Failed to fetch tutors: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // ENQUIRY / LEAD SUBMISSION (POST)
    // -------------------------------------------------------------
    case 'enquiry':
    case 'lead':
    case 'contact':
        if ($requestMethod !== 'POST') {
            jsonError('The enquiry endpoint only accepts POST requests.', 405);
        }

        $senderName  = trim((string)($params['sender_name'] ?? $params['name'] ?? $params['student_name'] ?? ''));
        $senderPhone = trim((string)($params['sender_phone'] ?? $params['phone'] ?? $params['mobile'] ?? ''));
        $senderEmail = trim((string)($params['sender_email'] ?? $params['email'] ?? ''));
        $senderType  = in_array($params['sender_type'] ?? '', ['parent', 'student']) ? $params['sender_type'] : 'student';

        $rawTargetType = strtolower(trim((string)($params['target_type'] ?? '')));
        $targetType = in_array($rawTargetType, ['educator', 'institution', 'university', 'college', 'institute', 'school']) ? $rawTargetType : 'institution';
        $targetId   = cleanInt($params['target_id'] ?? $params['institution_id'] ?? 1, 1);

        $cityOrPincode = trim((string)($params['city_or_pincode'] ?? $params['state'] ?? $params['city'] ?? ''));
        $message       = trim((string)($params['message'] ?? $params['query'] ?? 'General admission and fee counseling requested.'));

        // Validation
        if ($senderName === '') {
            jsonError('Please provide your full name.', 422);
        }

        $cleanDigits = preg_replace('/[^0-9]/', '', $senderPhone);
        if (strlen($cleanDigits) < 10) {
            jsonError('Please provide a valid 10-digit mobile number for SMS verification.', 422);
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO enquiry (sender_name, sender_phone, sender_email, sender_type, target_type, target_id, message, city_or_pincode, status, created_at, updated_at)
                VALUES (:name, :phone, :email, :stype, :ttype, :tid, :msg, :city, 'new', NOW(), NOW())
            ");
            $stmt->execute([
                ':name'  => $senderName,
                ':phone' => $senderPhone,
                ':email' => $senderEmail,
                ':stype' => $senderType,
                ':ttype' => $targetType,
                ':tid'   => $targetId,
                ':msg'   => $message,
                ':city'  => $cityOrPincode,
            ]);
            $insertedId = (int)$pdo->lastInsertId();

            // Attempt DLT OTP SMS dispatch if sms module exists
            $smsDispatched = false;
            $smsFile = __DIR__ . '/includes/sms.php';
            if (file_exists($smsFile)) {
                @require_once $smsFile;
                if (function_exists('sendOeduOtp')) {
                    $generatedOtp = (string)rand(100000, 999999);
                    try {
                        sendOeduOtp($senderPhone, $senderName, $generatedOtp);
                        $smsDispatched = true;
                    } catch (Throwable) {
                        $smsDispatched = false;
                    }
                }
            }

            jsonResponse([
                'enquiry_id'     => $insertedId,
                'otp_dispatched' => $smsDispatched,
                'status'         => 'submitted',
            ], 201, 'success', 'Admission enquiry successfully submitted. Our academic counselor will connect with you shortly.');
        } catch (Throwable $e) {
            jsonError('Failed to record enquiry: ' . $e->getMessage(), 500);
        }
        break;

    // -------------------------------------------------------------
    // DEFAULT: API ROOT DIRECTORY & DOCUMENTATION
    // -------------------------------------------------------------
    default:
        jsonResponse([
            'platform'    => 'Online Education — National Education Platform of India',
            'api_version' => '1.0.0',
            'motto'       => 'विद्या ददाति विनयम् — One Education Platform for Every People',
            'status'      => 'operational',
            'timestamp'   => date('c'),
            'endpoints'   => [
                'GET  /api?action=stats'        => 'Platform counts (universities, colleges, institutes, schools, law, states, districts)',
                'GET  /api?action=states'       => 'All 36 Indian States and Union Territories from op_sdb',
                'GET  /api?action=districts&state={State}' => 'Cascading district list for a state',
                'GET  /api?action=search&q={Query}&type={all|universities|colleges|institutes|schools|law}' => 'Omnibox universal institution search',
                'GET  /api?action=universities&state={State}&page={1}&limit={20}' => 'AISHE verified universities directory with filters',
                'GET  /api?action=university&slug={Slug}|id={ID}|aishe={Code}' => 'Detailed university profile',
                'GET  /api?action=colleges&state={State}&university={Name}' => 'AISHE affiliated colleges directory',
                'GET  /api?action=college&slug={Slug}|id={ID}|aishe={Code}' => 'Detailed college profile',
                'GET  /api?action=institutes&state={State}&type_name={Type}' => 'Standalone institutes directory (Polytechnics, Nursing, PGDM)',
                'GET  /api?action=schools&state={State}&district={District}' => 'Official CBSE schools directory (33,180+ schools)',
                'GET  /api?action=school&slug={Slug}|aff={AffiliationNo}|code={Code}' => 'Detailed CBSE school profile',
                'GET  /api?action=law_colleges&state={State}' => 'BCI approved law colleges and LLB/LLM courses',
                'GET  /api?action=exams&category={Engineering|Medical|Civil}' => 'National entrance examinations calendar (JEE, NEET, CUET, UPSC)',
                'GET  /api?action=scholarships&provider={Central+Government}' => 'Government welfare and merit scholarships tracker',
                'GET  /api?action=tutors&state={State}&mode={online|offline}' => 'Verified home tuition and online educators network',
                'POST /api?action=enquiry'      => 'Submit student/parent counseling enquiry (name, phone, target_type, message)',
                'GET  /api?action=ping'         => 'Healthcheck endpoint verifying DB connection',
            ],
            'documentation' => 'https://oedu.in/api',
        ], 200, 'success', 'Welcome to Online Education REST API. Refer to the endpoints schema to query educational directories.');
        break;
}
