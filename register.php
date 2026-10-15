<?php
// register.php — Student Registration for Online Education Portal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sms.php';

// If student is already logged in, redirect to dashboard
if (isset($_SESSION['student_user_id'])) {
    header("Location: student-dashboard.php");
    exit;
}

$pageTitle = 'Student Registration — Free National Admission & College Discovery Portal | Online Education';
$pageDesc = 'Create your free student account on Online Education. Access verified cutoff lists, save favourite colleges & universities, and receive instant admission notifications.';

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$state = trim($_POST['state'] ?? '');
$district = trim($_POST['district'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');
$education_level = trim($_POST['education_level'] ?? '');
$target_course = trim($_POST['target_course'] ?? '');
$gender = trim($_POST['gender'] ?? '');

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);

    // Validation
    if ($name === '') {
        $errors[] = 'Please enter your Full Name.';
    } elseif (mb_strlen($name) < 3) {
        $errors[] = 'Full Name must be at least 3 characters.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    // Clean Phone
    $phoneClean = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phoneClean) === 12 && substr($phoneClean, 0, 2) === '91') {
        $phoneClean = substr($phoneClean, 2);
    }
    if (strlen($phoneClean) !== 10) {
        $errors[] = 'Please enter a valid 10-digit Indian mobile number.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if ($state === '') {
        $errors[] = 'Please select your State or Union Territory.';
    }

    if (!$terms) {
        $errors[] = 'Please agree to the Terms of Service & Privacy Policy to continue.';
    }

    // Check duplicate email
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM `user` WHERE email = :email LIMIT 1");
        $checkStmt->execute([':email' => $email]);
        if ($checkStmt->fetch()) {
            $errors[] = 'An account with this email address already exists. Please sign in instead.';
        }
    }

    // Check duplicate phone
    if (empty($errors)) {
        $checkPhone = $pdo->prepare("SELECT id FROM `user` WHERE phone = :phone LIMIT 1");
        $checkPhone->execute([':phone' => $phoneClean]);
        if ($checkPhone->fetch()) {
            $errors[] = 'An account with this mobile number already exists. Please sign in or use another number.';
        }
    }

    // If no errors, create student user
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $insertSql = "
            INSERT INTO `user` (
                name, email, role, phone, state, district, pincode, 
                education_level, target_course, gender, is_verified, 
                password, created_at, updated_at, last_login
            ) VALUES (
                :name, :email, 'student', :phone, :state, :district, :pincode,
                :education_level, :target_course, :gender, 1,
                :password, NOW(), NOW(), NOW()
            )
        ";

        $stmt = $pdo->prepare($insertSql);
        $res = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phoneClean,
            ':state' => $state,
            ':district' => $district,
            ':pincode' => $pincode,
            ':education_level' => $education_level,
            ':target_course' => $target_course,
            ':gender' => $gender,
            ':password' => $passwordHash,
        ]);

        if ($res) {
            $studentId = (int)$pdo->lastInsertId();

            // Set student session
            $_SESSION['student_user_id'] = $studentId;
            $_SESSION['student_name'] = $name;
            $_SESSION['student_email'] = $email;
            $_SESSION['student_phone'] = $phoneClean;
            $_SESSION['student_role'] = 'student';

            // Send welcome OTP/SMS notification via DLT OfferPlant gateway
            try {
                sendOeduOtp($phoneClean, $name, 'WELCOME');
            } catch (Exception $e) {
                // Non-critical fallback
            }

            // Redirect to Student Dashboard
            header("Location: student-dashboard.php?registered=1");
            exit;
        } else {
            $errors[] = 'Unable to create your account at this moment. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Student Registration Styling */
.reg-container {
    max-width: 1180px;
    margin: 32px auto 60px;
    padding: 0 16px;
}

.reg-grid {
    display: grid;
    grid-template-columns: 1fr 1.35fr;
    gap: 36px;
    align-items: start;
}

@media (max-width: 900px) {
    .reg-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
}

.reg-sidebar {
    background: linear-gradient(145deg, #070A10 0%, #111827 60%, #1F2937 100%);
    border: 1px solid rgba(255, 107, 0, 0.25);
    border-radius: var(--radius-lg);
    padding: 36px 28px;
    color: #FFFFFF;
    position: sticky;
    top: 90px;
    box-shadow: var(--shadow-lg);
}

.reg-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 107, 0, 0.18);
    border: 1px solid rgba(255, 107, 0, 0.4);
    color: #FFA35B;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 4px 10px;
    border-radius: var(--radius-full);
    margin-bottom: 16px;
}

.reg-sidebar h1 {
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1.25;
    margin-bottom: 14px;
    color: #FFFFFF;
}

.reg-sidebar p {
    color: #CBD5E1;
    font-size: 0.92rem;
    line-height: 1.6;
    margin-bottom: 28px;
}

.reg-perks {
    list-style: none;
    padding: 0;
    margin: 0 0 28px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.reg-perks li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 0.88rem;
    color: #E2E8F0;
    line-height: 1.5;
}

.reg-perks-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 107, 0, 0.2);
    border: 1px solid rgba(255, 107, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FF8533;
    font-weight: 800;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.reg-stat-bar {
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius-md);
    padding: 16px;
    display: flex;
    justify-content: space-between;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.reg-stat-num {
    font-size: 1.2rem;
    font-weight: 800;
    color: #FFA35B;
}

.reg-stat-lbl {
    font-size: 0.72rem;
    color: #94A3B8;
    text-transform: uppercase;
}

.reg-form-card {
    background: #FFFFFF;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: 36px 32px;
}

@media (max-width: 600px) {
    .reg-form-card {
        padding: 24px 18px;
    }
}

.reg-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    font-weight: 800;
    color: var(--charcoal);
    margin: 24px 0 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--primary-subtle);
}

.reg-section-title:first-of-type {
    margin-top: 0;
}

.reg-section-num {
    background: var(--orange);
    color: #FFF;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 800;
}

.reg-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

@media (max-width: 640px) {
    .reg-form-row {
        grid-template-columns: 1fr;
        gap: 14px;
    }
}

.reg-field {
    margin-bottom: 16px;
}

.reg-field label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--charcoal);
    margin-bottom: 6px;
}

.reg-field label .req {
    color: #DC2626;
    margin-left: 2px;
}

.reg-input, .reg-select {
    width: 100%;
    padding: 11px 14px;
    font-size: 0.92rem;
    font-family: inherit;
    border: 1.5px solid #CBD5E1;
    border-radius: var(--radius-md);
    background-color: #FFFFFF;
    color: var(--charcoal);
    transition: all 0.2s ease;
    outline: none;
}

.reg-input:focus, .reg-select:focus {
    border-color: var(--orange);
    box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.15);
}

.pwd-wrapper {
    position: relative;
}

.pwd-toggle-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.85rem;
    color: var(--slate-muted);
    padding: 4px;
}

.reg-terms-box {
    background: var(--bg-page);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 14px 16px;
    margin: 22px 0;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.reg-terms-box input[type="checkbox"] {
    margin-top: 3px;
    accent-color: var(--orange);
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.reg-terms-text {
    font-size: 0.82rem;
    color: var(--slate-body);
    line-height: 1.5;
}

.reg-submit-btn {
    width: 100%;
    background: linear-gradient(135deg, #FF6B00 0%, #EA580C 100%);
    color: #FFFFFF;
    font-size: 1.05rem;
    font-weight: 800;
    padding: 14px 24px;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    box-shadow: var(--shadow-orange);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.reg-submit-btn:hover {
    background: linear-gradient(135deg, #E65A00 0%, #C2410C 100%);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(255, 107, 0, 0.35);
}

.reg-login-cta {
    text-align: center;
    margin-top: 24px;
    font-size: 0.9rem;
    color: var(--slate-muted);
}

.reg-login-cta a {
    color: var(--orange);
    font-weight: 700;
    text-decoration: underline;
}

.reg-error-box {
    background: #FEF2F2;
    border: 1px solid #FCA5A5;
    border-radius: var(--radius-md);
    padding: 14px 16px;
    margin-bottom: 24px;
    color: #991B1B;
    font-size: 0.88rem;
}

.reg-error-box ul {
    margin: 6px 0 0 20px;
    padding: 0;
}
</style>

<div class="reg-container">
    <div class="reg-grid">
        <!-- Sidebar Information -->
        <aside class="reg-sidebar">
            <span class="reg-badge">
                🎓 Free Student Access
            </span>
            <h1>Join India's Largest Higher Education Network</h1>
            <p>
                One registration opens direct doors to 1,505+ Universities, 54,000+ Colleges, and verified National cutoffs.
            </p>

            <ul class="reg-perks">
                <li>
                    <span class="reg-perks-icon">✓</span>
                    <div>
                        <strong style="color: #FFF;">Direct Admission Alerts:</strong>
                        Get SMS &amp; email updates on CUET, JEE, NEET, and State CET counseling dates.
                    </div>
                </li>
                <li>
                    <span class="reg-perks-icon">✓</span>
                    <div>
                        <strong style="color: #FFF;">Track Admission Inquiries:</strong>
                        Monitor all your college and university inquiries from one central student dashboard.
                    </div>
                </li>
                <li>
                    <span class="reg-perks-icon">✓</span>
                    <div>
                        <strong style="color: #FFF;">Tailored Recommendations:</strong>
                        Discover top-ranked institutions matching your target course, state, and qualification.
                    </div>
                </li>
                <li>
                    <span class="reg-perks-icon">✓</span>
                    <div>
                        <strong style="color: #FFF;">100% Free &amp; Private:</strong>
                        No hidden consultation fees. Your personal contact details remain confidential.
                    </div>
                </li>
            </ul>

            <div class="reg-stat-bar">
                <div>
                    <div class="reg-stat-num">1,505</div>
                    <div class="reg-stat-lbl">Universities</div>
                </div>
                <div style="border-left: 1px solid rgba(255,255,255,0.1); padding-left: 14px;">
                    <div class="reg-stat-num">54,351</div>
                    <div class="reg-stat-lbl">Colleges</div>
                </div>
                <div style="border-left: 1px solid rgba(255,255,255,0.1); padding-left: 14px;">
                    <div class="reg-stat-num">100%</div>
                    <div class="reg-stat-lbl">Verified</div>
                </div>
            </div>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.82rem; color: #94A3B8;">
                Already have an account? <br>
                <a href="login.php" style="color: #FFA35B; font-weight: 700; text-decoration: underline; font-size: 0.9rem;">
                    Sign in to your Student Portal →
                </a>
            </div>
        </aside>

        <!-- Registration Form Card -->
        <div class="reg-form-card">
            <div style="margin-bottom: 22px;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
                    Create Your Student Account
                </h2>
                <p style="font-size: 0.88rem; color: var(--slate-muted);">
                    Fill in your details below to activate your student profile and start exploring institutions.
                </p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="reg-error-box">
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" id="studentRegForm" autocomplete="on">
                <!-- Section 1: Basic Identity -->
                <div class="reg-section-title">
                    <span class="reg-section-num">1</span>
                    <span>Basic Details &amp; Contact</span>
                </div>

                <div class="reg-field">
                    <label for="name">Full Name <span class="req">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="reg-input" 
                        placeholder="e.g. Rahul Sharma" 
                        value="<?= htmlspecialchars($name) ?>" 
                        required
                    >
                </div>

                <div class="reg-form-row">
                    <div class="reg-field">
                        <label for="email">Email Address <span class="req">*</span></label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="reg-input" 
                            placeholder="e.g. rahul@example.com" 
                            value="<?= htmlspecialchars($email) ?>" 
                            required
                        >
                    </div>

                    <div class="reg-field">
                        <label for="phone">10-Digit Mobile Number <span class="req">*</span></label>
                        <div style="display: flex;">
                            <span style="display: inline-flex; align-items: center; padding: 0 12px; background: #F1F5F9; border: 1.5px solid #CBD5E1; border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); font-weight: 700; font-size: 0.85rem; color: #475569;">+91</span>
                            <input 
                                type="tel" 
                                name="phone" 
                                id="phone" 
                                class="reg-input" 
                                style="border-top-left-radius: 0; border-bottom-left-radius: 0;" 
                                placeholder="9876543210" 
                                maxlength="10" 
                                pattern="[6-9][0-9]{9}" 
                                value="<?= htmlspecialchars($phone) ?>" 
                                required
                            >
                        </div>
                    </div>
                </div>

                <div class="reg-form-row">
                    <div class="reg-field">
                        <label for="password">Create Password <span class="req">*</span></label>
                        <div class="pwd-wrapper">
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="reg-input" 
                                placeholder="Minimum 6 characters" 
                                required
                            >
                            <button type="button" class="pwd-toggle-btn" onclick="togglePassword('password', this)" title="Show/Hide Password">👁️</button>
                        </div>
                    </div>

                    <div class="reg-field">
                        <label for="confirm_password">Confirm Password <span class="req">*</span></label>
                        <div class="pwd-wrapper">
                            <input 
                                type="password" 
                                name="confirm_password" 
                                id="confirm_password" 
                                class="reg-input" 
                                placeholder="Re-enter password" 
                                required
                            >
                            <button type="button" class="pwd-toggle-btn" onclick="togglePassword('confirm_password', this)" title="Show/Hide Password">👁️</button>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Academic Interest & Location -->
                <div class="reg-section-title">
                    <span class="reg-section-num">2</span>
                    <span>Academic Interest &amp; Location</span>
                </div>

                <div class="reg-form-row">
                    <div class="reg-field">
                        <label for="education_level">Current Education Level</label>
                        <select name="education_level" id="education_level" class="reg-select">
                            <option value="">-- Select Qualification / Class --</option>
                            <?php foreach ($educationLevels as $lvl): ?>
                                <option value="<?= htmlspecialchars($lvl) ?>" <?= $education_level === $lvl ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($lvl) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="reg-field">
                        <label for="target_course">Target Course / Discipline</label>
                        <select name="target_course" id="target_course" class="reg-select">
                            <option value="">-- Select Field of Interest --</option>
                            <?php foreach ($targetCourses as $tc): ?>
                                <option value="<?= htmlspecialchars($tc) ?>" <?= $target_course === $tc ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tc) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="reg-form-row">
                    <div class="reg-field">
                        <label for="state">State / Union Territory <span class="req">*</span></label>
                        <select name="state" id="state" class="reg-select" required>
                            <option value="">-- Select Your State --</option>
                            <?php foreach ($indianStates as $st): ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= $state === $st ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="reg-field">
                        <label for="district">City / District</label>
                        <input 
                            type="text" 
                            name="district" 
                            id="district" 
                            class="reg-input" 
                            placeholder="e.g. Patna, Lucknow, Jaipur" 
                            value="<?= htmlspecialchars($district) ?>"
                        >
                    </div>
                </div>

                <div class="reg-form-row">
                    <div class="reg-field">
                        <label for="pincode">Pincode (Optional)</label>
                        <input 
                            type="text" 
                            name="pincode" 
                            id="pincode" 
                            class="reg-input" 
                            placeholder="6-digit pincode" 
                            maxlength="6" 
                            pattern="[0-9]{6}" 
                            value="<?= htmlspecialchars($pincode) ?>"
                        >
                    </div>

                    <div class="reg-field">
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender" class="reg-select">
                            <option value="">-- Select Gender --</option>
                            <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $gender === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <!-- Terms and Privacy Checkbox -->
                <div class="reg-terms-box">
                    <input type="checkbox" name="terms" id="terms" value="1" required checked>
                    <label for="terms" class="reg-terms-text">
                        I confirm that the details provided are accurate and agree to the 
                        <a href="index.php" target="_blank" style="color: var(--orange); font-weight: 700;">Terms of Service</a> &amp; 
                        <a href="index.php" target="_blank" style="color: var(--orange); font-weight: 700;">Privacy Policy</a>. 
                        I consent to receive important admission notifications, counseling reminders, and cut-off updates via SMS (DLT ID: OEDUIN) or WhatsApp.
                    </label>
                </div>

                <button type="submit" class="reg-submit-btn">
                    <span>Complete Student Registration</span>
                    <span style="font-size: 1.2rem;">→</span>
                </button>
            </form>

            <div class="reg-login-cta">
                Already registered with Online Education? 
                <a href="login.php">Sign In to Student Portal</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🔒';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
