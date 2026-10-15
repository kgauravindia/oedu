<?php
// student-dashboard.php — Student Portal Dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

// Auth check
if (!isset($_SESSION['student_user_id'])) {
    header("Location: login.php?ref=student-dashboard.php&msg=login_required");
    exit;
}

$studentId = (int)$_SESSION['student_user_id'];

// Handle Profile Update POST
$updateMsg = '';
$updateType = 'success';

$indianStates = [
    'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 
    'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 
    'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 
    'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 
    'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands', 'Chandigarh', 
    'Dadra and Nagar Haveli and Daman and Diu', 'Delhi (NCT)', 'Jammu and Kashmir', 
    'Ladakh', 'Lakshadweep', 'Puducherry'
];

$educationLevels = [
    'Class 9–10 (Secondary)',
    'Class 11–12 (Higher Secondary / Intermediate)',
    'Diploma / Polytechnic',
    'Undergraduate (B.Tech, MBBS, B.Sc, B.Com, BA, BBA, BCA)',
    'Postgraduate (M.Tech, MBA, M.Sc, M.Com, MA, MCA)',
    'Doctorate / Ph.D. / Research',
    'Working Professional / Continuing Education'
];

$targetCourses = [
    'Engineering & Technology (B.Tech / B.E / M.Tech)',
    'Medicine & Allied Health (MBBS / BDS / Nursing / B.Pharm)',
    'Business & Management (MBA / BBA / PGDM)',
    'Computer Applications & IT (BCA / MCA / Data Science)',
    'Pure Sciences (B.Sc / M.Sc Physics, Chemistry, Maths)',
    'Commerce & Finance (B.Com / M.Com / CA / CMA)',
    'Humanities, Arts & Social Sciences (BA / MA)',
    'Law & Legal Studies (LLB / BA LLB / LLM)',
    'Design, Architecture & Fine Arts (B.Arch / B.Des)',
    'Agriculture & Veterinary Science',
    'Teaching & Education (B.Ed / D.El.Ed)',
    'Civil Services & Government Exam Preparation',
    'Other / Open'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $education_level = trim($_POST['education_level'] ?? '');
    $target_course = trim($_POST['target_course'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    $phoneClean = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phoneClean) === 12 && substr($phoneClean, 0, 2) === '91') {
        $phoneClean = substr($phoneClean, 2);
    }

    if ($name === '') {
        $updateMsg = 'Name cannot be empty.';
        $updateType = 'danger';
    } else {
        $updStmt = $pdo->prepare("
            UPDATE `user` 
            SET name = :name, phone = :phone, state = :state, district = :district, 
                pincode = :pincode, education_level = :education_level, 
                target_course = :target_course, gender = :gender, bio = :bio, 
                updated_at = NOW() 
            WHERE id = :id
        ");
        $updStmt->execute([
            ':name' => $name,
            ':phone' => $phoneClean,
            ':state' => $state,
            ':district' => $district,
            ':pincode' => $pincode,
            ':education_level' => $education_level,
            ':target_course' => $target_course,
            ':gender' => $gender,
            ':bio' => $bio,
            ':id' => $studentId
        ]);

        $_SESSION['student_name'] = $name;
        $_SESSION['student_phone'] = $phoneClean;
        $updateMsg = 'Your profile details have been updated successfully!';
    }
}

// Fetch fresh student record
$stmt = $pdo->prepare("SELECT * FROM `user` WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    session_destroy();
    header("Location: login.php?msg=login_required");
    exit;
}

// Fetch inquiries submitted by this student (by phone OR email)
$studentPhone = trim($student['phone'] ?? '');
$studentEmail = trim($student['email'] ?? '');

$enquiryWhere = [];
$enquiryParams = [];

if ($studentPhone !== '') {
    $enquiryWhere[] = "sender_phone = :phone";
    $enquiryParams[':phone'] = $studentPhone;
}

if ($studentEmail !== '') {
    $enquiryWhere[] = "sender_email = :email";
    $enquiryParams[':email'] = $studentEmail;
}

if (!empty($enquiryWhere)) {
    $sqlEnq = "SELECT * FROM enquiry WHERE (" . implode(" OR ", $enquiryWhere) . ") ORDER BY id DESC";
    $enquiryStmt = $pdo->prepare($sqlEnq);
    $enquiryStmt->execute($enquiryParams);
    $inquiries = $enquiryStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $inquiries = [];
}

// Fetch Recommended Institutions (based on student state)
$recommended = [];
if (!empty($student['state'])) {
    $recStmt = $pdo->prepare("
        SELECT id, name, state, 'university' as type, slug, '' as college_type 
        FROM university 
        WHERE state = :st 
        LIMIT 4
    ");
    $recStmt->execute([':st' => $student['state']]);
    $recommended = $recStmt->fetchAll(PDO::FETCH_ASSOC);
}
if (empty($recommended)) {
    // Fallback top universities
    $recStmt = $pdo->query("
        SELECT id, name, state, 'university' as type, slug, '' as college_type 
        FROM university 
        ORDER BY id ASC 
        LIMIT 4
    ");
    $recommended = $recStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate profile completion percentage
$fieldsToCheck = ['name', 'email', 'phone', 'state', 'district', 'education_level', 'target_course', 'gender'];
$completedFields = 0;
foreach ($fieldsToCheck as $f) {
    if (!empty($student[$f])) {
        $completedFields++;
    }
}
$completionPercent = round(($completedFields / count($fieldsToCheck)) * 100);

$pageTitle = 'Student Dashboard — ' . htmlspecialchars($student['name']) . ' | Online Education';
require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Student Dashboard Styling */
.dash-wrapper {
    max-width: 1240px;
    margin: 28px auto 60px;
    padding: 0 16px;
}

.dash-hero {
    background: radial-gradient(circle at 85% 20%, rgba(255, 107, 0, 0.22) 0%, transparent 50%), linear-gradient(135deg, #070A10 0%, #111827 55%, #1E293B 100%);
    border: 1px solid rgba(255, 107, 0, 0.25);
    border-radius: var(--radius-lg);
    padding: 32px 28px;
    color: #FFFFFF;
    margin-bottom: 30px;
    box-shadow: var(--shadow-lg);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.dash-user-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.dash-avatar {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FF6B00 0%, #FF8533 100%);
    color: #FFFFFF;
    font-size: 1.8rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(255, 107, 0, 0.4);
    border: 3px solid rgba(255, 255, 255, 0.2);
    flex-shrink: 0;
}

.dash-hero h1 {
    font-size: 1.65rem;
    font-weight: 800;
    margin-bottom: 4px;
}

.dash-hero-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 6px;
}

.dash-tag {
    font-size: 0.76rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: var(--radius-full);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.dash-tag-orange {
    background: rgba(255, 107, 0, 0.2);
    color: #FFA35B;
    border: 1px solid rgba(255, 107, 0, 0.4);
}

.dash-tag-green {
    background: rgba(16, 185, 129, 0.2);
    color: #6EE7B7;
    border: 1px solid rgba(16, 185, 129, 0.4);
}

.dash-hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.dash-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 28px;
}

@media (max-width: 992px) {
    .dash-grid {
        grid-template-columns: 1fr;
    }
}

.dash-card {
    background: #FFFFFF;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    padding: 24px 24px;
    margin-bottom: 24px;
}

.dash-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.dash-card-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--charcoal);
    display: flex;
    align-items: center;
    gap: 8px;
}

.dash-stat-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.dash-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: var(--shadow-sm);
}

.dash-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--orange-subtle);
    color: var(--orange);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.dash-stat-val {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--charcoal);
    line-height: 1.1;
}

.dash-stat-label {
    font-size: 0.78rem;
    color: var(--slate-muted);
    font-weight: 600;
}

.dash-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

.dash-table th {
    background: #F8FAFC;
    color: #475569;
    font-weight: 700;
    text-align: left;
    padding: 10px 14px;
    border-bottom: 1.5px solid #E2E8F0;
    font-size: 0.8rem;
    text-transform: uppercase;
}

.dash-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #F1F5F9;
    color: var(--slate-body);
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
}

.status-new {
    background: #FEF3C7;
    color: #92400E;
}

.status-contacted {
    background: #DBEAFE;
    color: #1E40AF;
}

.status-closed {
    background: #D1FAE5;
    color: #065F46;
}

.rec-card {
    border: 1px solid #E2E8F0;
    border-radius: var(--radius-md);
    padding: 14px;
    margin-bottom: 12px;
    transition: all 0.18s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.rec-card:hover {
    border-color: var(--orange);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.progress-bar-container {
    background: #E2E8F0;
    height: 8px;
    border-radius: 999px;
    overflow: hidden;
    margin: 8px 0;
}

.progress-bar-fill {
    background: linear-gradient(90deg, #FF6B00 0%, #10B981 100%);
    height: 100%;
    border-radius: 999px;
    transition: width 0.4s ease;
}

.btn-profile-edit {
    background: var(--bg-page);
    border: 1px solid var(--border-color);
    font-weight: 700;
    padding: 8px 16px;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-profile-edit:hover {
    background: #E2E8F0;
}
</style>

<div class="dash-wrapper">
    <!-- Welcome Alert on new registration -->
    <?php if (isset($_GET['registered'])): ?>
        <div style="background: #ECFDF5; border: 1.5px solid #10B981; border-radius: var(--radius-md); padding: 16px 20px; margin-bottom: 24px; color: #065F46; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="font-size: 1.05rem;">🎉 Registration Successful! Welcome to Online Education, <?= htmlspecialchars($student['name']) ?>!</strong>
                <p style="margin: 4px 0 0; font-size: 0.88rem; color: #047857;">
                    Your student account is now active. You can browse 70,000+ institutions, send inquiries, and receive cutoff notifications.
                </p>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:#065F46;">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($updateMsg)): ?>
        <div style="background: <?= $updateType === 'success' ? '#ECFDF5' : '#FEF2F2' ?>; border: 1px solid <?= $updateType === 'success' ? '#10B981' : '#F87171' ?>; border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 24px; color: <?= $updateType === 'success' ? '#065F46' : '#991B1B' ?>;">
            <?= htmlspecialchars($updateMsg) ?>
        </div>
    <?php endif; ?>

    <!-- Hero Header -->
    <div class="dash-hero">
        <div class="dash-user-info">
            <div class="dash-avatar">
                <?= strtoupper(mb_substr($student['name'] ?? 'S', 0, 1)) ?>
            </div>
            <div>
                <h1><?= htmlspecialchars($student['name']) ?></h1>
                <div style="font-size: 0.88rem; color: #CBD5E1;">
                    <span>📧 <?= htmlspecialchars($student['email']) ?></span>
                    <?php if (!empty($student['phone'])): ?>
                        <span style="margin-left: 12px;">📱 +91 <?= htmlspecialchars($student['phone']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="dash-hero-badges">
                    <span class="dash-tag dash-tag-orange">
                        🎓 Student Portal
                    </span>
                    <?php if (!empty($student['state'])): ?>
                        <span class="dash-tag dash-tag-green">
                            📍 <?= htmlspecialchars($student['state']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($student['education_level'])): ?>
                        <span class="dash-tag" style="background: rgba(255,255,255,0.1); color: #FFF;">
                            <?= htmlspecialchars($student['education_level']) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="dash-hero-actions">
            <a href="institution.php" class="btn btn-primary" style="font-size: 0.85rem; padding: 10px 18px;">
                🔍 Browse Universities
            </a>
            <a href="logout.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.3); color: #FFF; font-size: 0.85rem;">
                Sign Out
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="dash-stat-row">
        <div class="dash-stat-card">
            <div class="dash-stat-icon">📋</div>
            <div>
                <div class="dash-stat-val"><?= count($inquiries) ?></div>
                <div class="dash-stat-label">Inquiries Sent</div>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon">🏛️</div>
            <div>
                <div class="dash-stat-val">1,505</div>
                <div class="dash-stat-label">Universities Available</div>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon">🎓</div>
            <div>
                <div class="dash-stat-val">54,351</div>
                <div class="dash-stat-label">Colleges in Directory</div>
            </div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon">⚡</div>
            <div>
                <div class="dash-stat-val"><?= $completionPercent ?>%</div>
                <div class="dash-stat-label">Profile Completed</div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dash-grid">
        <!-- Left Column: Inquiries & Recommended -->
        <div>
            <!-- My Inquiries Card -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h2 class="dash-card-title">
                        <span>📬</span> My Admission &amp; Course Inquiries
                    </h2>
                    <span style="font-size: 0.8rem; color: var(--slate-muted);">
                        <?= count($inquiries) ?> total
                    </span>
                </div>

                <?php if (empty($inquiries)): ?>
                    <div style="text-align: center; padding: 36px 16px; color: var(--slate-muted);">
                        <div style="font-size: 2.4rem; margin-bottom: 8px;">📭</div>
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--charcoal); margin-bottom: 4px;">
                            No Inquiries Submitted Yet
                        </h3>
                        <p style="font-size: 0.88rem; max-width: 420px; margin: 0 auto 16px;">
                            When you submit an admission or course inquiry on any University or College profile, it will appear here so you can track its response status.
                        </p>
                        <a href="institution.php" class="btn btn-primary" style="font-size: 0.85rem;">
                            Find Institutions to Inquire →
                        </a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Target Institution</th>
                                    <th>Inquiry Message</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inquiries as $inq): ?>
                                    <tr>
                                        <td>
                                            <strong style="color: var(--charcoal);">
                                                <?= htmlspecialchars(ucfirst($inq['target_type'] ?? 'General')) ?> #<?= (int)$inq['target_id'] ?>
                                            </strong>
                                            <?php if (!empty($inq['city_or_pincode'])): ?>
                                                <div style="font-size: 0.75rem; color: var(--slate-muted);">
                                                    📍 <?= htmlspecialchars($inq['city_or_pincode']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width: 260px;">
                                            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($inq['message']) ?>">
                                                <?= htmlspecialchars($inq['message']) ?>
                                            </div>
                                        </td>
                                        <td style="font-size: 0.8rem; color: var(--slate-muted); white-space: nowrap;">
                                            <?= date('M d, Y', strtotime($inq['created_at'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $stClass = 'status-new';
                                            if ($inq['status'] === 'contacted') $stClass = 'status-contacted';
                                            if ($inq['status'] === 'closed') $stClass = 'status-closed';
                                            ?>
                                            <span class="status-badge <?= $stClass ?>">
                                                <?= htmlspecialchars($inq['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recommended Institutions Card -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h2 class="dash-card-title">
                        <span>🎯</span> Recommended for You in <?= htmlspecialchars($student['state'] ?? 'India') ?>
                    </h2>
                    <a href="institution.php" style="font-size: 0.82rem; color: var(--orange); font-weight: 700;">
                        View All →
                    </a>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px;">
                    <?php foreach ($recommended as $rec): ?>
                        <div class="rec-card">
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--charcoal); margin-bottom: 2px;">
                                    <?= htmlspecialchars($rec['name']) ?>
                                </h4>
                                <span style="font-size: 0.78rem; color: var(--slate-muted);">
                                    📍 <?= htmlspecialchars($rec['state']) ?>
                                </span>
                            </div>
                            <a href="university-detail.php?id=<?= (int)$rec['id'] ?>" class="btn btn-outline" style="font-size: 0.78rem; padding: 6px 12px; white-space: nowrap;">
                                View →
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Profile Summary & Edit Form -->
        <div>
            <!-- Profile Card -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h2 class="dash-card-title">
                        <span>👤</span> Student Profile
                    </h2>
                    <span style="font-size: 0.78rem; font-weight: 700; color: #10B981; background: #ECFDF5; padding: 3px 8px; border-radius: 999px;">
                        Active
                    </span>
                </div>

                <!-- Profile Completion Indicator -->
                <div style="margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700;">
                        <span>Profile Completion</span>
                        <span style="color: var(--orange);"><?= $completionPercent ?>%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?= $completionPercent ?>%;"></div>
                    </div>
                </div>

                <form method="POST" action="student-dashboard.php">
                    <input type="hidden" name="action" value="update_profile">

                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($student['name'] ?? '') ?>" required style="width: 100%; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Email (Permanent ID)</label>
                        <input type="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" disabled style="width: 100%; padding: 8px 12px; border: 1px solid #E2E8F0; background: #F8FAFC; border-radius: 6px; font-family: inherit; font-size: 0.88rem; color: #64748B;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">10-Digit Mobile Number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" maxlength="10" placeholder="9876543210" style="width: 100%; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Current Education Level</label>
                        <select name="education_level" style="width: 100%; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                            <option value="">-- Select Level --</option>
                            <?php foreach ($educationLevels as $lvl): ?>
                                <option value="<?= htmlspecialchars($lvl) ?>" <?= ($student['education_level'] ?? '') === $lvl ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($lvl) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Target Course / Interest</label>
                        <select name="target_course" style="width: 100%; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                            <option value="">-- Select Interest --</option>
                            <?php foreach ($targetCourses as $tc): ?>
                                <option value="<?= htmlspecialchars($tc) ?>" <?= ($student['target_course'] ?? '') === $tc ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tc) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">State / UT</label>
                        <select name="state" style="width: 100%; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                            <option value="">-- Select State --</option>
                            <?php foreach ($indianStates as $st): ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= ($student['state'] ?? '') === $st ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">District / City</label>
                            <input type="text" name="district" value="<?= htmlspecialchars($student['district'] ?? '') ?>" style="width: 100%; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                        </div>
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Pincode</label>
                            <input type="text" name="pincode" value="<?= htmlspecialchars($student['pincode'] ?? '') ?>" maxlength="6" style="width: 100%; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Gender</label>
                        <select name="gender" style="width: 100%; padding: 8px 12px; border: 1.5px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                            <option value="">-- Select Gender --</option>
                            <option value="Male" <?= ($student['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($student['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($student['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px; font-size: 0.9rem;">
                        Save Profile Updates
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
