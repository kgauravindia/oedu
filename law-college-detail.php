<?php
// law-college-detail.php — Dedicated Profile for BCI Recognized Law College (.php only)
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

if (!$slug && !$id) {
    header("Location: /law-college.php");
    exit;
}

$coursesList = [];
try {
    if ($slug) {
        $stmt = $pdo->prepare("SELECT * FROM `law_college` WHERE slug = :slug ORDER BY id ASC");
        $stmt->execute([':slug' => $slug]);
    } else {
        // If by ID, find its slug first to show full courses of that college
        $idStmt = $pdo->prepare("SELECT slug FROM `law_college` WHERE id = :id LIMIT 1");
        $idStmt->execute([':id' => $id]);
        $foundSlug = $idStmt->fetchColumn();
        if ($foundSlug) {
            $stmt = $pdo->prepare("SELECT * FROM `law_college` WHERE slug = :slug ORDER BY id ASC");
            $stmt->execute([':slug' => $foundSlug]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `law_college` WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
    }
    $coursesList = $stmt->fetchAll();
} catch (Throwable $e) {
    $coursesList = [];
}

if (empty($coursesList)) {
    http_response_code(404);
    $pageRobots = 'noindex, nofollow';
    $pageTitle = 'Law College Not Found (404) | Online Education';
    require_once __DIR__ . '/includes/header.php';
    echo '<div style="background:#FFF; padding:48px 16px; text-align:center; border:1px solid #E5E7EB; border-radius:12px; margin:40px auto; max-width:600px;">';
    echo '<div style="font-size:3rem; margin-bottom:12px;">⚖️</div>';
    echo '<h2 style="color:#111827; font-weight:800;">Law College Not Found</h2>';
    echo '<p style="color:#6B7280; margin:8px 0 20px;">The requested law college profile could not be located in the national BCI directory.</p>';
    echo '<a href="law-college.php" class="btn btn-primary">Browse All Law Colleges</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$college = $coursesList[0];
$collegeName = $college['name'];
$state = $college['state'];
$university = $college['affiliating_university'];
$slug = $college['slug'];

// 301 Redirect to clean URL if accessed directly via law-college-detail.php
if (!empty($slug) && isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], 'law-college-detail.php')) {
    header("Location: law-college/" . urlencode($slug), true, 301);
    exit;
}

// Canonical URL
$canonicalUrl = "https://oedu.in/law-college/" . urlencode($slug);

// Find establishment year across courses
$estYear = '';
foreach ($coursesList as $c) {
    if (!empty($c['e_year'])) {
        $estYear = $c['e_year'];
        break;
    }
}

// Fetch other law colleges under same university or state
$relatedColleges = [];
if (!empty($university)) {
    $relStmt = $pdo->prepare("SELECT slug, name, state, COUNT(*) as prog_cnt FROM `law_college` WHERE affiliating_university = :univ AND slug != :slug GROUP BY slug, name, state ORDER BY name ASC LIMIT 6");
    $relStmt->execute([':univ' => $university, ':slug' => $slug]);
    $relatedColleges = $relStmt->fetchAll();
}
if (empty($relatedColleges) && !empty($state)) {
    $relStmt = $pdo->prepare("SELECT slug, name, state, COUNT(*) as prog_cnt FROM `law_college` WHERE state = :state AND slug != :slug GROUP BY slug, name, state ORDER BY name ASC LIMIT 6");
    $relStmt->execute([':state' => $state, ':slug' => $slug]);
    $relatedColleges = $relStmt->fetchAll();
}

$pageTitle = "{$collegeName} — BCI Approved Law Courses & Intake | Online Education";
$pageDesc = "{$collegeName} in {$state}. Affiliated with {$university}. View BCI approved law courses (3-Yr LL.B, 5-Yr BA LL.B), sanctioned intake seats, and affiliation validity.";

// Rich Schema.org Structured Data
$pageJsonLd = [
    "@context" => "https://schema.org",
    "@graph" => [
        array_filter([
            "@type" => "EducationalOrganization",
            "@id" => $canonicalUrl . "#institution",
            "name" => $collegeName,
            "url" => $canonicalUrl,
            "description" => $pageDesc,
            "parentOrganization" => !empty($university) ? [
                "@type" => "CollegeOrUniversity",
                "name" => $university
            ] : null,
            "address" => [
                "@type" => "PostalAddress",
                "addressRegion" => $state,
                "addressCountry" => "IN"
            ]
        ]),
        [
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Home",
                    "item" => "https://oedu.in/"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => "Law Colleges",
                    "item" => "https://oedu.in/law-college.php"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 3,
                    "name" => $collegeName,
                    "item" => $canonicalUrl
                ]
            ]
        ]
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<div style="font-size: 0.8rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
    <a href="index.php" style="color: #64748B; text-decoration: none;">Home</a>
    <span>/</span>
    <a href="law-college.php" style="color: #64748B; text-decoration: none;">Law Colleges</a>
    <span>/</span>
    <a href="law-college.php?state=<?= urlencode($state) ?>" style="color: #64748B; text-decoration: none;"><?= htmlspecialchars($state) ?></a>
    <span>/</span>
    <span style="color: #0F172A; font-weight: 700;"><?= htmlspecialchars(substr($collegeName, 0, 45)) ?><?= strlen($collegeName) > 45 ? '...' : '' ?></span>
</div>

<!-- College Hero Header -->
<div style="background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.04);">
    <div style="display: flex; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
        <div style="width: 58px; height: 58px; border-radius: 12px; background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1D4ED8; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; flex-shrink: 0; font-weight: 800;">
            ⚖️
        </div>
        <div style="flex: 1; min-width: 260px;">
            <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;">
                <span style="font-size: 0.74rem; font-weight: 800; background: #1E3A8A; color: #FFFFFF; padding: 3px 9px; border-radius: 4px;">
                    Bar Council of India (BCI) Recognized
                </span>
                <span style="font-size: 0.74rem; font-weight: 700; background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; padding: 3px 9px; border-radius: 4px;">
                    <?= count($coursesList) ?> Approved Program<?= count($coursesList) > 1 ? 's' : '' ?>
                </span>
                <?php if ($estYear): ?>
                    <span style="font-size: 0.74rem; font-weight: 700; background: var(--orange-light); color: var(--orange-dark); padding: 3px 9px; border-radius: 4px; border: 1px solid var(--orange-border);">
                        Established <?= htmlspecialchars($estYear) ?>
                    </span>
                <?php endif; ?>
            </div>

            <h1 style="font-size: 1.65rem; font-weight: 800; color: #0F172A; line-height: 1.3; margin-bottom: 6px;">
                <?= htmlspecialchars($collegeName) ?>
            </h1>

            <div style="font-size: 0.92rem; color: #475569; margin-bottom: 12px;">
                📍 State Jurisdiction: <strong><?= htmlspecialchars($state) ?></strong>
            </div>

            <?php if (!empty($university)): ?>
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px; font-size: 0.85rem; max-width: 780px;">
                    <span style="color: #64748B; font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">Affiliating University:</span>
                    <div style="color: #0F172A; font-weight: 700; margin-top: 2px;">
                        <?= htmlspecialchars($university) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; min-width: 170px;">
            <a href="#inquiry-section" class="btn btn-primary" style="justify-content: center; padding: 10px 16px; font-size: 0.88rem;">
                Admission Counseling 📝
            </a>
            <a href="law-college.php?state=<?= urlencode($state) ?>" class="btn btn-outline" style="justify-content: center; padding: 8px 14px; font-size: 0.82rem;">
                More Colleges in <?= htmlspecialchars($state) ?> →
            </a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 40px;" class="detail-columns-layout">
    <!-- Left Column: Approved Programs & Details -->
    <div>
        <!-- Section: Approved Law Programs Table -->
        <div style="background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: var(--radius-md); padding: 22px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 800; color: #0F172A; margin: 0 0 2px;">
                        Approved Legal Programs &amp; Intake
                    </h2>
                    <p style="font-size: 0.82rem; color: #64748B; margin: 0;">
                        Official recognition &amp; sanctioned seats approved by the Bar Council of India (BCI).
                    </p>
                </div>
                <span style="font-size: 0.76rem; font-weight: 700; color: #166534; background: #DCFCE7; padding: 3px 10px; border-radius: 999px;">
                    BCI Statutory File
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.86rem; text-align: left;">
                    <thead>
                        <tr style="background: #F1F5F9; border-bottom: 2px solid #CBD5E1; color: #334155; font-size: 0.78rem; text-transform: uppercase;">
                            <th style="padding: 10px 12px;">#</th>
                            <th style="padding: 10px 12px;">Course Name &amp; Strength</th>
                            <th style="padding: 10px 12px;">Approval Validity</th>
                            <th style="padding: 10px 12px;">Est. Year</th>
                            <th style="padding: 10px 12px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coursesList as $idx => $course): 
                            $isClosed = stripos($course['course_name'] ?? '', 'closed') !== false || stripos($course['remarks'] ?? '', 'closed') !== false;
                        ?>
                            <tr style="border-bottom: 1px solid #E2E8F0; background: <?= $idx % 2 === 0 ? '#FFFFFF' : '#F8FAFC' ?>;">
                                <td style="padding: 12px; font-weight: 700; color: #64748B;"><?= $idx + 1 ?></td>
                                <td style="padding: 12px;">
                                    <div style="font-weight: 800; color: #0F172A; font-size: 0.92rem;"><?= htmlspecialchars($course['course_name']) ?></div>
                                    <?php if (!empty($course['remarks'])): ?>
                                        <div style="font-size: 0.76rem; color: #D97706; margin-top: 4px; font-weight: 600; background: #FEF3C7; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                                            ℹ️ <?= htmlspecialchars($course['remarks']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; font-weight: 700; color: #1E3A8A;">
                                    <?= htmlspecialchars($course['approval_till'] ?: 'Pending Review') ?>
                                </td>
                                <td style="padding: 12px; color: #475569;">
                                    <?= htmlspecialchars($course['e_year'] ?: ($estYear ?: '—')) ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?php if ($isClosed): ?>
                                        <span style="font-size: 0.74rem; font-weight: 700; background: #FEE2E2; color: #991B1B; padding: 2px 8px; border-radius: 4px;">Closed</span>
                                    <?php else: ?>
                                        <span style="font-size: 0.74rem; font-weight: 700; background: #DCFCE7; color: #166534; padding: 2px 8px; border-radius: 4px;">Active</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section: Key Highlights & Regulatory Framework -->
        <div style="background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: var(--radius-md); padding: 22px; margin-bottom: 24px;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0 0 14px;">
                Legal Education &amp; Regulatory Highlights
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Statutory Authority</div>
                    <div style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin-top: 2px;">Bar Council of India (BCI)</div>
                    <div style="font-size: 0.76rem; color: #64748B; margin-top: 4px;">Regulated under the Advocates Act, 1961</div>
                </div>

                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase;">State Bar Jurisdiction</div>
                    <div style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin-top: 2px;">Bar Council of <?= htmlspecialchars($state) ?></div>
                    <div style="font-size: 0.76rem; color: #64748B; margin-top: 4px;">Advocate enrollment eligibility upon completion</div>
                </div>

                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Entrance Pathways</div>
                    <div style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin-top: 2px;">CLAT / State Law CET / CUET</div>
                    <div style="font-size: 0.76rem; color: #64748B; margin-top: 4px;">Merit-based admission in LL.B programs</div>
                </div>
            </div>
        </div>

        <!-- Section: Related Law Colleges -->
        <?php if (!empty($relatedColleges)): ?>
            <div style="background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: var(--radius-md); padding: 22px;">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0 0 14px;">
                    Other Law Colleges in <?= htmlspecialchars($state) ?>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px;">
                    <?php foreach ($relatedColleges as $rel): ?>
                        <a href="law-college/<?= urlencode($rel['slug']) ?>" style="display: block; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; text-decoration: none; color: inherit; transition: border-color 0.2s;">
                            <div style="font-weight: 700; font-size: 0.88rem; color: #0F172A; line-height: 1.3; margin-bottom: 4px;">
                                <?= htmlspecialchars($rel['name']) ?>
                            </div>
                            <div style="font-size: 0.76rem; color: #64748B;">
                                📍 <?= htmlspecialchars($rel['state']) ?> • <span style="color: #166534; font-weight: 700;"><?= $rel['prog_cnt'] ?> Law Course<?= $rel['prog_cnt'] > 1 ? 's' : '' ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Counseling & Quick Inquiry Form -->
    <div>
        <div id="inquiry-section" style="background: #FFFFFF; border: 1.5px solid #111827; border-radius: var(--radius-md); padding: 22px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); position: sticky; top: 20px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 1.4rem;">⚖️</span>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #111827; margin: 0;">
                    Legal Studies Counseling
                </h3>
            </div>
            <p style="font-size: 0.8rem; color: #64748B; margin-bottom: 16px; line-height: 1.4;">
                Get personalized guidance on 3-Year LL.B &amp; 5-Year Integrated BA/BBA LL.B admissions, CLAT/CET cutoffs, fees, and scholarship eligibility.
            </p>

            <form action="enquiry-submit.php" method="POST">
                <input type="hidden" name="institution_type" value="law_college">
                <input type="hidden" name="institution_name" value="<?= htmlspecialchars($collegeName) ?>">
                <input type="hidden" name="institution_state" value="<?= htmlspecialchars($state) ?>">
                <input type="hidden" name="redirect_url" value="law-college/<?= urlencode($slug) ?>">

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.75rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Student Name *</label>
                    <input type="text" name="name" required placeholder="Full Name" style="width: 100%; height: 38px; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.75rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Mobile Number (OTP Verified) *</label>
                    <input type="tel" name="phone" required pattern="[0-9]{10}" placeholder="10-digit mobile number" style="width: 100%; height: 38px; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.75rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Interested Course *</label>
                    <select name="course_interest" style="width: 100%; height: 38px; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.85rem; background: #FFF;">
                        <option value="3-Year LL.B">3-Year LL.B (Graduate Entry)</option>
                        <option value="5-Year BA LL.B">5-Year Integrated BA LL.B</option>
                        <option value="5-Year BBA LL.B">5-Year Integrated BBA LL.B</option>
                        <option value="5-Year B.Com LL.B">5-Year Integrated B.Com LL.B</option>
                        <option value="LL.M">LL.M (Postgraduate Master)</option>
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.75rem; font-weight: 800; color: #111827; display: block; margin-bottom: 4px;">Query / Questions</label>
                    <textarea name="message" rows="2" placeholder="Fee structure, cutoff marks, hostel facility..." style="width: 100%; padding: 6px 10px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-size: 0.82rem; resize: vertical;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px; font-size: 0.9rem; justify-content: center;">
                    Submit Inquiry Free →
                </button>
            </form>

            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #E2E8F0; text-align: center; font-size: 0.72rem; color: #64748B;">
                🔒 100% Privacy Protected • Free National Educational Counseling
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 860px) {
    .detail-columns-layout {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
