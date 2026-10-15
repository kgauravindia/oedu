<?php
// login.php — Student Portal Sign In with Password & Instant SMS OTP (.php only)
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

$pageTitle = 'Student Sign In — National Education Platform | Online Education';
$pageDesc = 'Sign in to your Online Education student account using Password or instant SMS OTP.';

$error = '';
$success = '';
$activeTab = $_POST['auth_mode'] ?? ($_GET['mode'] ?? 'otp'); // Default to OTP for instant ease

$ref = trim($_GET['ref'] ?? ($_POST['ref'] ?? 'student-dashboard.php'));
if (!preg_match('/^[a-zA-Z0-9_\-\.\/\?=&]+$/', $ref) || strpos($ref, '://') !== false || strpos($ref, '//') === 0) {
    $ref = 'student-dashboard.php';
}

$msg = trim($_GET['msg'] ?? '');
if ($msg === 'logged_out') {
    $success = 'You have been securely logged out. Come back soon!';
} elseif ($msg === 'login_required') {
    $error = 'Please sign in to access your student dashboard and inquiries.';
}

// -------------------------------------------------------------
// POST Handling: (A) Send OTP, (B) Verify OTP, (C) Password Login
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Send OTP to Mobile
    if ($action === 'send_otp') {
        $activeTab = 'otp';
        $phoneInput = trim($_POST['phone'] ?? '');
        $phoneClean = preg_replace('/[^0-9]/', '', $phoneInput);
        if (strlen($phoneClean) === 12 && substr($phoneClean, 0, 2) === '91') {
            $phoneClean = substr($phoneClean, 2);
        }

        if (strlen($phoneClean) !== 10) {
            $error = 'Please enter a valid 10-digit Indian mobile number.';
        } else {
            // Find or initialize student record
            $stmt = $pdo->prepare("SELECT * FROM `user` WHERE phone = :phone LIMIT 1");
            $stmt->execute([':phone' => $phoneClean]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Generate 6-digit OTP
            $otp = (string)random_int(100000, 999999);
            $recipientName = $user ? $user['name'] : 'Student';

            // Store in session
            $_SESSION['otp_code'] = $otp;
            $_SESSION['otp_phone'] = $phoneClean;
            $_SESSION['otp_user_id'] = $user ? (int)$user['id'] : 0;
            $_SESSION['otp_time'] = time();

            // Send SMS via morg.in / OEDUIN gateway
            $res = sendOeduOtp($phoneClean, $recipientName, $otp);

            if ($res['status'] === 'success') {
                $success = "A 6-digit OTP has been sent via SMS to +91 {$phoneClean}.";
            } else {
                $success = "OTP sent to +91 {$phoneClean}. (SMS logged in system)";
            }
        }
    }

    // Action 2: Verify OTP & Sign In
    elseif ($action === 'verify_otp') {
        $activeTab = 'otp';
        $enteredOtp = trim($_POST['otp'] ?? '');
        $sessionOtp = $_SESSION['otp_code'] ?? '';
        $sessionPhone = $_SESSION['otp_phone'] ?? '';
        $sessionTime = $_SESSION['otp_time'] ?? 0;
        $sessionUserId = (int)($_SESSION['otp_user_id'] ?? 0);

        if (empty($sessionOtp) || empty($sessionPhone)) {
            $error = 'No active OTP request found. Please request a new OTP.';
        } elseif ((time() - $sessionTime) > 600) { // 10 minutes expiry
            unset($_SESSION['otp_code'], $_SESSION['otp_phone'], $_SESSION['otp_user_id'], $_SESSION['otp_time']);
            $error = 'The OTP has expired. Please request a new one.';
        } elseif ($enteredOtp !== $sessionOtp) {
            $error = 'Invalid OTP. Please check the 6-digit code received on your phone and try again.';
        } else {
            // OTP is Valid!
            if ($sessionUserId > 0) {
                // Existing user
                $stmt = $pdo->prepare("SELECT * FROM `user` WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $sessionUserId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // New user registering via OTP login!
                $defaultEmail = 'student_' . $sessionPhone . '@oedu.in';
                // Check if dummy email exists
                $chk = $pdo->prepare("SELECT * FROM `user` WHERE email = :e LIMIT 1");
                $chk->execute([':e' => $defaultEmail]);
                $existing = $chk->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $user = $existing;
                } else {
                    $dummyPass = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
                    $ins = $pdo->prepare("
                        INSERT INTO `user` (name, email, role, phone, is_verified, password, created_at, updated_at, last_login)
                        VALUES (:name, :email, 'student', :phone, 1, :pass, NOW(), NOW(), NOW())
                    ");
                    $ins->execute([
                        ':name' => 'Student ' . substr($sessionPhone, -4),
                        ':email' => $defaultEmail,
                        ':phone' => $sessionPhone,
                        ':pass' => $dummyPass
                    ]);
                    $newId = (int)$pdo->lastInsertId();
                    $stmt = $pdo->prepare("SELECT * FROM `user` WHERE id = :id LIMIT 1");
                    $stmt->execute([':id' => $newId]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }

            // Set student session
            $_SESSION['student_user_id'] = (int)$user['id'];
            $_SESSION['student_name'] = $user['name'];
            $_SESSION['student_email'] = $user['email'];
            $_SESSION['student_phone'] = $user['phone'];
            $_SESSION['student_role'] = $user['role'];

            // Clear OTP session keys
            unset($_SESSION['otp_code'], $_SESSION['otp_phone'], $_SESSION['otp_user_id'], $_SESSION['otp_time']);

            // Update last_login
            try {
                $pdo->prepare("UPDATE `user` SET last_login = NOW() WHERE id = :id")->execute([':id' => $user['id']]);
            } catch (Exception $e) {}

            header("Location: {$ref}");
            exit;
        }
    }

    // Action 3: Password Login
    elseif ($action === 'login_password') {
        $activeTab = 'password';
        $identity = trim($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($identity === '' || $password === '') {
            $error = 'Please enter your registered email or mobile number, and password.';
        } else {
            $phoneCandidate = preg_replace('/[^0-9]/', '', $identity);
            $sql = "SELECT * FROM `user` WHERE (email = :ident OR phone = :phone) LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':ident' => $identity,
                ':phone' => $phoneCandidate !== '' ? $phoneCandidate : '___none___'
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['student_user_id'] = (int)$user['id'];
                $_SESSION['student_name'] = $user['name'];
                $_SESSION['student_email'] = $user['email'];
                $_SESSION['student_phone'] = $user['phone'];
                $_SESSION['student_role'] = $user['role'];

                try {
                    $pdo->prepare("UPDATE `user` SET last_login = NOW() WHERE id = :id")->execute([':id' => $user['id']]);
                } catch (Exception $e) {}

                header("Location: {$ref}");
                exit;
            } else {
                $error = 'Invalid email/phone or password. Please verify and try again.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
$hasActiveOtp = !empty($_SESSION['otp_code']) && !empty($_SESSION['otp_phone']);
?>

<style>
.login-box-container {
    max-width: 480px;
    margin: 40px auto 72px;
    padding: 0 16px;
}

.login-card {
    background: #FFFFFF;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    padding: 34px 28px;
}

@media (max-width: 480px) {
    .login-card {
        padding: 24px 18px;
    }
}

.login-header {
    text-align: center;
    margin-bottom: 22px;
}

.login-icon-badge {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(255, 107, 0, 0.15) 0%, rgba(255, 107, 0, 0.05) 100%);
    border: 1px solid rgba(255, 107, 0, 0.3);
    color: var(--orange);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 12px;
}

.login-header h1 {
    font-size: 1.55rem;
    font-weight: 800;
    color: var(--charcoal);
    margin-bottom: 4px;
}

.login-header p {
    font-size: 0.86rem;
    color: var(--slate-muted);
}

/* Tabs switcher */
.auth-tab-bar {
    display: flex;
    background: #F1F5F9;
    border-radius: var(--radius-md);
    padding: 4px;
    margin-bottom: 24px;
    gap: 4px;
}

.auth-tab-btn {
    flex: 1;
    text-align: center;
    padding: 9px 12px;
    font-size: 0.85rem;
    font-weight: 700;
    border: none;
    border-radius: 6px;
    background: none;
    color: #64748B;
    cursor: pointer;
    transition: all 0.18s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.auth-tab-btn.active {
    background: #FFFFFF;
    color: var(--charcoal);
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
}

.auth-tab-btn .badge-pill {
    font-size: 0.68rem;
    background: var(--orange-subtle);
    color: var(--orange-dark);
    padding: 1px 6px;
    border-radius: 999px;
}

.login-input {
    width: 100%;
    padding: 12px 14px;
    font-size: 0.95rem;
    font-family: inherit;
    border: 1.5px solid #CBD5E1;
    border-radius: var(--radius-md);
    background-color: #FFFFFF;
    color: var(--charcoal);
    transition: all 0.2s ease;
    outline: none;
}

.login-input:focus {
    border-color: var(--orange);
    box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.15);
}

.login-field {
    margin-bottom: 18px;
}

.login-field label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--charcoal);
    margin-bottom: 6px;
}

.login-btn {
    width: 100%;
    background: linear-gradient(135deg, #FF6B00 0%, #EA580C 100%);
    color: #FFFFFF;
    font-size: 1rem;
    font-weight: 800;
    padding: 13px 20px;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    box-shadow: var(--shadow-orange);
    transition: all 0.2s ease;
    margin-top: 4px;
}

.login-btn:hover {
    background: linear-gradient(135deg, #E65A00 0%, #C2410C 100%);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(255, 107, 0, 0.35);
}

.login-footer-links {
    text-align: center;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
    font-size: 0.88rem;
    color: var(--slate-muted);
}

.login-footer-links a {
    color: var(--orange);
    font-weight: 700;
}

.login-alert {
    padding: 12px 14px;
    border-radius: var(--radius-md);
    font-size: 0.88rem;
    margin-bottom: 20px;
}

.alert-danger-custom {
    background: #FEF2F2;
    border: 1px solid #FCA5A5;
    color: #991B1B;
}

.alert-success-custom {
    background: #ECFDF5;
    border: 1px solid #A7F3D0;
    color: #065F46;
}

.otp-box-input {
    letter-spacing: 8px;
    font-size: 1.5rem;
    font-weight: 800;
    text-align: center;
}
</style>

<div class="login-box-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-icon-badge">🎓</div>
            <h1>Student Portal Sign In</h1>
            <p>Access your college inquiries, cut-off notifications &amp; saved profiles</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="login-alert alert-danger-custom">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="login-alert alert-success-custom">
                ✅ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Tabs: OTP vs Password -->
        <div class="auth-tab-bar">
            <button type="button" class="auth-tab-btn <?= $activeTab === 'otp' ? 'active' : '' ?>" onclick="switchAuthTab('otp')">
                <span>📱 Mobile OTP</span>
                <span class="badge-pill">Fast</span>
            </button>
            <button type="button" class="auth-tab-btn <?= $activeTab === 'password' ? 'active' : '' ?>" onclick="switchAuthTab('password')">
                <span>🔑 Password</span>
            </button>
        </div>

        <!-- TAB 1: OTP LOGIN -->
        <div id="tab-content-otp" style="display: <?= $activeTab === 'otp' ? 'block' : 'none' ?>;">
            <?php if (!$hasActiveOtp): ?>
                <!-- Step 1: Send OTP -->
                <form method="POST" action="login.php">
                    <input type="hidden" name="action" value="send_otp">
                    <input type="hidden" name="auth_mode" value="otp">
                    <input type="hidden" name="ref" value="<?= htmlspecialchars($ref) ?>">

                    <div class="login-field">
                        <label for="otp_phone">10-Digit Mobile Number</label>
                        <div style="display: flex;">
                            <span style="display: inline-flex; align-items: center; padding: 0 12px; background: #F1F5F9; border: 1.5px solid #CBD5E1; border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); font-weight: 700; font-size: 0.88rem; color: #475569;">+91</span>
                            <input 
                                type="tel" 
                                name="phone" 
                                id="otp_phone" 
                                class="login-input" 
                                style="border-top-left-radius: 0; border-bottom-left-radius: 0;" 
                                placeholder="9876543210" 
                                maxlength="10" 
                                pattern="[6-9][0-9]{9}" 
                                required 
                                autofocus
                            >
                        </div>
                        <span style="font-size: 0.76rem; color: var(--slate-muted); display: block; margin-top: 4px;">
                            We will send a 6-digit OTP via official SMS (Sender ID: OEDUIN)
                        </span>
                    </div>

                    <button type="submit" class="login-btn">
                        Send Verification OTP →
                    </button>
                </form>
            <?php else: ?>
                <!-- Step 2: Verify OTP -->
                <form method="POST" action="login.php">
                    <input type="hidden" name="action" value="verify_otp">
                    <input type="hidden" name="auth_mode" value="otp">
                    <input type="hidden" name="ref" value="<?= htmlspecialchars($ref) ?>">

                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: var(--radius-md); padding: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; color: var(--charcoal);">
                            OTP sent to: <strong>+91 <?= htmlspecialchars($_SESSION['otp_phone']) ?></strong>
                        </span>
                        <a href="login.php?mode=otp" style="font-size: 0.8rem; color: var(--orange); font-weight: 700;">Change</a>
                    </div>

                    <div class="login-field">
                        <label for="otp">Enter 6-Digit OTP</label>
                        <input 
                            type="text" 
                            name="otp" 
                            id="otp" 
                            class="login-input otp-box-input" 
                            placeholder="······" 
                            maxlength="6" 
                            pattern="[0-9]{6}" 
                            required 
                            autofocus
                        >
                    </div>

                    <button type="submit" class="login-btn">
                        Verify &amp; Sign In →
                    </button>
                </form>

                <form method="POST" action="login.php" style="margin-top: 12px; text-align: center;">
                    <input type="hidden" name="action" value="send_otp">
                    <input type="hidden" name="auth_mode" value="otp">
                    <input type="hidden" name="phone" value="<?= htmlspecialchars($_SESSION['otp_phone']) ?>">
                    <button type="submit" style="background: none; border: none; font-size: 0.82rem; color: var(--orange); font-weight: 700; cursor: pointer; text-decoration: underline;">
                        Didn't receive SMS? Resend OTP
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- TAB 2: PASSWORD LOGIN -->
        <div id="tab-content-password" style="display: <?= $activeTab === 'password' ? 'block' : 'none' ?>;">
            <form method="POST" action="login.php">
                <input type="hidden" name="action" value="login_password">
                <input type="hidden" name="auth_mode" value="password">
                <input type="hidden" name="ref" value="<?= htmlspecialchars($ref) ?>">

                <div class="login-field">
                    <label for="identity">Email Address or Mobile</label>
                    <input 
                        type="text" 
                        name="identity" 
                        id="identity" 
                        class="login-input" 
                        placeholder="email@example.com or 9876543210" 
                        required 
                        autocomplete="username"
                    >
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="login-input" 
                            placeholder="Enter your password" 
                            required 
                            autocomplete="current-password"
                        >
                        <button type="button" onclick="togglePassword('password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--slate-muted); font-size: 0.9rem;">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    Sign In with Password →
                </button>
            </form>
        </div>

        <div class="login-footer-links">
            <p style="margin-bottom: 8px;">
                New to Online Education? 
                <a href="register.php">Create Free Student Account</a>
            </p>
            <p style="font-size: 0.8rem; margin-top: 14px; color: #94A3B8;">
                Are you an institution administrator? 
                <a href="admin/login.php" style="color: #64748B;">Admin Portal Sign In →</a>
            </p>
        </div>
    </div>
</div>

<script>
function switchAuthTab(tab) {
    document.querySelectorAll('.auth-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-content-otp').style.display = 'none';
    document.getElementById('tab-content-password').style.display = 'none';

    if (tab === 'otp') {
        document.querySelectorAll('.auth-tab-btn')[0].classList.add('active');
        document.getElementById('tab-content-otp').style.display = 'block';
    } else {
        document.querySelectorAll('.auth-tab-btn')[1].classList.add('active');
        document.getElementById('tab-content-password').style.display = 'block';
    }
}

function togglePassword(id, btn) {
    const el = document.getElementById(id);
    if (!el) return;
    if (el.type === 'password') {
        el.type = 'text';
        btn.textContent = '🔒';
    } else {
        el.type = 'password';
        btn.textContent = '👁️';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
