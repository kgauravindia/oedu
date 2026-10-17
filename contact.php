<?php
// contact.php — Official Contact & Helpdesk Portal for Online Education (.php only)
require_once __DIR__ . '/includes/db.php';

$successMsg = '';
$errorMsg = '';

// Check URL query flash messages from enquiry-submit or previous redirects
if (!empty($_GET['success'])) {
    $successMsg = trim($_GET['success']);
}
if (!empty($_GET['error'])) {
    $errorMsg = trim($_GET['error']);
}

// Handle Direct Contact Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $category = trim($_POST['category'] ?? 'General Enquiry');
    $city = trim($_POST['city'] ?? '');
    $messageText = trim($_POST['message'] ?? '');

    if (empty($name)) {
        $errorMsg = 'Please enter your full name.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Please enter a valid email address.';
    } elseif (empty($messageText)) {
        $errorMsg = 'Please describe your query or message.';
    } else {
        try {
            $fullMessage = "[Category: {$category}] " . $messageText;

            $stmt = $pdo->prepare("
                INSERT INTO enquiry (sender_name, sender_phone, sender_email, sender_type, target_type, target_id, message, city_or_pincode, status, created_at, updated_at)
                VALUES (:name, '', :email, 'student', 'contact_us', 1, :msg, :city, 'new', NOW(), NOW())
            ");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':msg' => $fullMessage,
                ':city' => $city,
            ]);

            $referenceId = 'OEDU-' . str_pad((string)$pdo->lastInsertId(), 5, '0', STR_PAD_LEFT);
            $successMsg = "Thank you, {$name}! Your message has been received with Reference ID #{$referenceId}. Our team will review your enquiry and respond within 24-48 business hours.";
        } catch (PDOException $e) {
            $errorMsg = 'We could not submit your message at this time. Please try again or email us directly at contact@oedu.in.';
        }
    }
}

$pageTitle = 'Contact Us — Support, Enquiries & Institutional Verification | Online Education';
$pageDesc = 'Contact the Online Education team for student support, institution profile corrections, AISHE verification, partnerships, and technical assistance.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<div style="background: radial-gradient(circle at 90% 15%, rgba(255, 107, 0, 0.25) 0%, transparent 45%), linear-gradient(135deg, #070A10 0%, #111827 55%, #1F2937 100%); border: 1px solid rgba(255, 107, 0, 0.22); color: #FFFFFF; border-radius: var(--radius-lg); padding: 44px 32px; margin-bottom: 36px; box-shadow: var(--shadow-lg);">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #FFA35B; font-weight: 700; margin-bottom: 10px;">
        <span>📬</span> Helpdesk &amp; Institutional Relations
    </div>
    <h1 style="font-size: 2.3rem; font-weight: 800; line-height: 1.2; margin-bottom: 12px;">
        Contact Online Education
    </h1>
    <p style="font-size: 1.05rem; color: #E5E7EB; max-width: 760px; line-height: 1.6; margin-bottom: 20px;">
        Have a question about a university, college, school listing, or need admission guidance? Want to update your institution's profile or report a discrepancy? We are here to help students, parents, educators, and institutions nationwide.
    </p>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="mailto:contact@oedu.in" class="btn btn-primary" style="padding: 10px 22px;">
            ✉️ Email Support: contact@oedu.in
        </a>
    </div>
</div>

<?php if (!empty($successMsg)): ?>
    <div style="background: #ECFDF5; border: 1px solid #6EE7B7; border-left: 5px solid #10B981; color: #065F46; padding: 18px 22px; border-radius: var(--radius-md); margin-bottom: 30px; font-size: 0.95rem; line-height: 1.6;">
        <strong style="display: block; font-size: 1.05rem; margin-bottom: 4px;">✅ Message Sent Successfully!</strong>
        <?= htmlspecialchars($successMsg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div style="background: #FEF2F2; border: 1px solid #FCA5A5; border-left: 5px solid #EF4444; color: #991B1B; padding: 18px 22px; border-radius: var(--radius-md); margin-bottom: 30px; font-size: 0.95rem; line-height: 1.6;">
        <strong style="display: block; font-size: 1.05rem; margin-bottom: 4px;">⚠️ Error</strong>
        <?= htmlspecialchars($errorMsg) ?>
    </div>
<?php endif; ?>

<!-- Main 2-Column Content Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px; margin-bottom: 48px; align-items: start;">

    <!-- Left Column: Modern Contact Form -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--charcoal); margin: 0;">
                Send Us a Message
            </h2>
            <span style="font-size: 0.75rem; background: var(--orange-subtle); color: var(--orange-dark); font-weight: 700; padding: 4px 10px; border-radius: var(--radius-full);">
                Average Response: 24h
            </span>
        </div>
        <p style="font-size: 0.88rem; color: var(--slate-muted); line-height: 1.5; margin-bottom: 24px;">
            Fill out the form below and your message will be routed directly to the concerned departmental desk.
        </p>

        <form action="" method="POST">
            <!-- Full Name & Email 2-col -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 18px;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--charcoal); display: block; margin-bottom: 6px;">
                        Full Name <span style="color: #EF4444;">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="e.g. Dr. Rajesh Sharma or Priya Nair" 
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        style="width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.92rem; background: #FAFAFA; transition: border-color 0.2s;"
                        onfocus="this.style.borderColor='var(--orange)'; this.style.background='#FFF';"
                        onblur="this.style.borderColor='var(--border-color)'; this.style.background='#FAFAFA';"
                    >
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--charcoal); display: block; margin-bottom: 6px;">
                        Email Address <span style="color: #EF4444;">*</span>
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        required
                        placeholder="name@domain.com" 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        style="width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.92rem; background: #FAFAFA; transition: border-color 0.2s;"
                        onfocus="this.style.borderColor='var(--orange)'; this.style.background='#FFF';"
                        onblur="this.style.borderColor='var(--border-color)'; this.style.background='#FAFAFA';"
                    >
                </div>
            </div>

            <!-- Subject / Query Category & Location 2-col -->
            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 16px; margin-bottom: 18px;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--charcoal); display: block; margin-bottom: 6px;">
                        Query Category <span style="color: #EF4444;">*</span>
                    </label>
                    <select 
                        name="category" 
                        required 
                        style="width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.92rem; background: #FAFAFA; cursor: pointer;"
                    >
                        <option value="General Enquiry">General Enquiry</option>
                        <option value="Institution Profile Update">Institution Profile Correction / Update</option>
                        <option value="Add School or College">Request Listing for School / College</option>
                        <option value="Student Admission & Counselling">Student Admission &amp; Cutoff Guidance</option>
                        <option value="NRI Admissions Support">NRI &amp; International Student Admissions</option>
                        <option value="Data Discrepancy & Grievance">Data Discrepancy / Grievance Redressal</option>
                        <option value="Partnership & Press">Partnership, Academic Collab &amp; Press</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--charcoal); display: block; margin-bottom: 6px;">
                        City / State
                    </label>
                    <input 
                        type="text" 
                        name="city" 
                        placeholder="e.g. Pune, MH" 
                        value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
                        style="width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.92rem; background: #FAFAFA;"
                    >
                </div>
            </div>

            <!-- Message / Details -->
            <div style="margin-bottom: 22px;">
                <label style="font-size: 0.82rem; font-weight: 700; color: var(--charcoal); display: block; margin-bottom: 6px;">
                    Your Message / Request Details <span style="color: #EF4444;">*</span>
                </label>
                <textarea 
                    name="message" 
                    required 
                    rows="5" 
                    placeholder="Please include details such as institution name, AISHE code, course name, or your specific query..."
                    style="width: 100%; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.92rem; background: #FAFAFA; resize: vertical;"
                    onfocus="this.style.borderColor='var(--orange)'; this.style.background='#FFF';"
                    onblur="this.style.borderColor='var(--border-color)'; this.style.background='#FAFAFA';"
                ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <!-- Submit Button & Trust Notice -->
            <button 
                type="submit" 
                class="btn btn-primary" 
                style="width: 100%; padding: 14px 24px; font-size: 1rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--shadow-orange); cursor: pointer;"
            >
                Submit Message to Desk →
            </button>
            <div style="font-size: 0.76rem; color: var(--slate-muted); text-align: center; margin-top: 12px;">
                🔒 We uphold strict data privacy: your contact information is never sold to external third-party telemarketers.
            </div>
        </form>
    </div>

    <!-- Right Column: Official Information, Channels & Contacts -->
    <div style="display: flex; flex-direction: column; gap: 24px;">

        <!-- Direct Contact Cards -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 26px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                <span>🏢</span> Official Communication Channels
            </h3>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <!-- Email -->
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 107, 0, 0.1); color: var(--orange); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        ✉️
                    </div>
                    <div>
                        <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--slate-muted); letter-spacing: 0.5px;">
                            Editorial &amp; Verification Desk
                        </div>
                        <a href="mailto:contact@oedu.in" style="font-size: 0.95rem; font-weight: 700; color: var(--charcoal); text-decoration: none;">
                            contact@oedu.in
                        </a>
                        <div style="font-size: 0.76rem; color: var(--slate-muted); margin-top: 2px;">
                            For general queries, data additions &amp; corrections
                        </div>
                    </div>
                </div>

                <!-- DLT Sender ID -->
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        📱
                    </div>
                    <div>
                        <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--slate-muted); letter-spacing: 0.5px;">
                            Official SMS Verification Sender ID
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: var(--charcoal); letter-spacing: 1px;">
                            OEDUIN <span style="font-size: 0.75rem; font-weight: 600; color: #10B981; background: #ECFDF5; padding: 2px 8px; border-radius: 4px; border: 1px solid #A7F3D0;">TRAI Registered</span>
                        </div>
                        <div style="font-size: 0.76rem; color: var(--slate-muted); margin-top: 2px;">
                            Official admission OTPs and verification alerts are delivered strictly via this sender ID.
                        </div>
                    </div>
                </div>

                <!-- Facebook -->
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(37, 99, 235, 0.1); color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </div>
                    <div>
                        <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--slate-muted); letter-spacing: 0.5px;">
                            Official Facebook Page
                        </div>
                        <a href="https://www.facebook.com/oeduin" target="_blank" rel="noopener noreferrer" style="font-size: 0.95rem; font-weight: 700; color: #2563EB; text-decoration: none;">
                            facebook.com/oeduin ↗
                        </a>
                        <div style="font-size: 0.76rem; color: var(--slate-muted); margin-top: 2px;">
                            Follow for national admission news, syllabus updates &amp; announcements
                        </div>
                    </div>
                </div>

                <!-- Publisher info -->
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(107, 114, 128, 0.1); color: #4B5563; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        🏛️
                    </div>
                    <div>
                        <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--slate-muted); letter-spacing: 0.5px;">
                            Platform Initiative &amp; Parent Organization
                        </div>
                        <div style="font-size: 0.92rem; font-weight: 700; color: var(--charcoal);">
                            OfferPlant Technologies Private Limited
                        </div>
                        <div style="font-size: 0.76rem; color: var(--slate-muted); margin-top: 2px;">
                            Empowering public-interest digital infrastructure for education across India.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Institutional Listing & Verification Notice -->
        <div style="background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%); border: 1px solid var(--orange-border); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 1.3rem;">🎓</span>
                <h4 style="font-size: 1rem; font-weight: 800; color: var(--orange-dark); margin: 0;">
                    For Universities, Colleges &amp; Schools
                </h4>
            </div>
            <p style="font-size: 0.85rem; color: #7C2D12; line-height: 1.5; margin-bottom: 12px;">
                Are you a Registrar, Principal, or authorized representative wishing to claim your institution's profile, update fee structures, or upload revised prospectuses?
            </p>
            <div style="font-size: 0.82rem; color: #9A3412; line-height: 1.5; margin-bottom: 14px;">
                Please select <strong>"Institution Profile Update"</strong> in the enquiry category or email us from your official institutional domain (e.g. <code>info@yourcollege.ac.in</code>) with your <strong>AISHE Code</strong> or <strong>CBSE Affiliation Number</strong>.
            </div>
            <a href="mailto:contact@oedu.in?subject=Institutional%20Profile%20Verification%20Request" class="btn btn-outline" style="border-color: var(--orange-dark); color: var(--orange-dark); font-size: 0.82rem; padding: 6px 14px; background: #FFF;">
                Institutional Desk Email →
            </a>
        </div>

    </div>
</div>

<!-- FAQ & Quick Clarifications -->
<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px 32px; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
    <div style="text-align: center; max-width: 680px; margin: 0 auto 32px auto;">
        <span style="font-size: 0.75rem; background: var(--primary-subtle); color: var(--charcoal); font-weight: 700; padding: 4px 12px; border-radius: var(--radius-full); text-transform: uppercase;">
            Frequently Asked Questions
        </span>
        <h3 style="font-size: 1.6rem; font-weight: 800; color: var(--charcoal); margin-top: 10px; margin-bottom: 8px;">
            Quick Help &amp; Common Queries
        </h3>
        <p style="font-size: 0.9rem; color: var(--slate-muted);">
            Find immediate answers to questions frequently asked by students, parents, and academic institutions.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <!-- Q1 -->
        <div style="padding: 20px; background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
            <h4 style="font-size: 0.98rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                ❓ Is Online Education an official government portal?
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-body); line-height: 1.5;">
                No. Online Education (oedu.in) is an <strong>independent public educational directory</strong> created by OfferPlant to simplify institutional discovery. We aggregate official public data from AISHE, UGC, AICTE, and CBSE for student benefit. Read our <a href="disclaimer.php" style="color: var(--orange); font-weight: 700;">Disclaimer</a> for complete details.
            </p>
        </div>

        <!-- Q2 -->
        <div style="padding: 20px; background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
            <h4 style="font-size: 0.98rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                ❓ How can our college or school update its contact details?
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-body); line-height: 1.5;">
                Send an email from your official institutional domain (e.g. @univ.ac.in or @school.edu.in) to <a href="mailto:contact@oedu.in" style="color: var(--orange); font-weight: 700;">contact@oedu.in</a> quoting your AISHE or CBSE affiliation number. Profile corrections are verified and processed free of charge.
            </p>
        </div>

        <!-- Q3 -->
        <div style="padding: 20px; background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
            <h4 style="font-size: 0.98rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                ❓ Are student counselling services on OEDU free?
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-body); line-height: 1.5;">
                Yes, browsing institutions, viewing cutoff trends, downloading syllabi, and submitting enquiries on Online Education is 100% free for students and parents. We never charge candidates for accessing basic directory information.
            </p>
        </div>

        <!-- Q4 -->
        <div style="padding: 20px; background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
            <h4 style="font-size: 0.98rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                ❓ How is my contact information protected from marketing spam?
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-body); line-height: 1.5;">
                We strictly adhere to our <a href="privacy.php" style="color: var(--orange); font-weight: 700;">Privacy Policy</a>. Your details are never sold to bulk lead generation agencies or telemarketers. Information is only utilized to respond to the specific inquiry you submitted.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
