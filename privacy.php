<?php
// privacy.php — Privacy Policy & Student Data Protection (.php only)
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Privacy Policy & Student Data Protection — Online Education';
$pageDesc = 'Learn how Online Education (oedu.in) protects student personal information, ensures compliance with India\'s DPDP Act 2023, and upholds a strict zero-spam pledge.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<div style="background: radial-gradient(circle at 90% 15%, rgba(255, 107, 0, 0.25) 0%, transparent 45%), linear-gradient(135deg, #070A10 0%, #111827 55%, #1F2937 100%); border: 1px solid rgba(255, 107, 0, 0.22); color: #FFFFFF; border-radius: var(--radius-lg); padding: 44px 32px; margin-bottom: 36px; box-shadow: var(--shadow-lg);">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #FFA35B; font-weight: 700; margin-bottom: 10px;">
        <span>🔒</span> Privacy &amp; Data Dignity
    </div>
    <h1 style="font-size: 2.3rem; font-weight: 800; line-height: 1.2; margin-bottom: 12px;">
        Privacy Policy &amp; Data Safeguards
    </h1>
    <p style="font-size: 1.05rem; color: #E5E7EB; max-width: 800px; line-height: 1.6; margin-bottom: 18px;">
        At Online Education (<strong style="color: #FFF;">oedu.in</strong>), protecting student and parent privacy is fundamental. We are committed to transparency, strict data confidentiality, and compliance with the <strong>Digital Personal Data Protection Act (DPDP Act, 2023)</strong> of India.
    </p>
    <div style="display: flex; gap: 16px; flex-wrap: wrap; font-size: 0.82rem; color: #9CA3AF;">
        <span>📅 <strong>Effective Date:</strong> January 1, 2026</span>
        <span>🛡️ <strong>Standards:</strong> DPDP Act 2023 &amp; TRAI DLT Compliant</span>
        <span>🏢 <strong>Entity:</strong> OfferPlant Technologies Pvt. Ltd.</span>
    </div>
</div>

<!-- Anti-Spam Pledge Card -->
<div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-left: 6px solid #10B981; border-radius: var(--radius-md); padding: 24px 28px; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
        <span style="font-size: 1.5rem;">🛡️</span>
        <h2 style="font-size: 1.2rem; font-weight: 800; color: #065F46; margin: 0;">
            The OEDU Zero-Spam Pledge
        </h2>
    </div>
    <p style="font-size: 0.95rem; color: #047857; line-height: 1.7; margin: 0;">
        <strong>We NEVER sell, rent, monetize, or broadcast student mobile numbers or email addresses to bulk call centers, lead syndicates, or predatory telemarketing agencies.</strong> Your personal contact information is held with strict confidentiality and is used exclusively to service the specific academic inquiry you voluntarily initiated.
    </p>
</div>

<!-- Layout: Navigation Index + Main Content -->
<div style="display: grid; grid-template-columns: 280px 1fr; gap: 36px; margin-bottom: 48px; align-items: start;">

    <!-- Left Sticky Sidebar: Navigation Index -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; position: sticky; top: 20px; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 0.92rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--charcoal); margin-bottom: 14px;">
            Policy Navigation
        </h3>
        <nav style="display: flex; flex-direction: column; gap: 8px; font-size: 0.84rem;">
            <a href="privacy.php#collection" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">1. Information We Collect</a>
            <a href="privacy.php#what-we-dont-do" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">2. What We Strictly Avoid</a>
            <a href="privacy.php#usage" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">3. How Your Data is Used</a>
            <a href="privacy.php#dlt-sms" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">4. DLT SMS &amp; TRAI Compliance</a>
            <a href="privacy.php#legal-basis" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">5. DPDP Act 2023 Lawful Basis</a>
            <a href="privacy.php#security" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">6. Data Security &amp; Encryption</a>
            <a href="privacy.php#cookies" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">7. Cookies &amp; Local Storage</a>
            <a href="privacy.php#minors" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">8. Child &amp; Minor Safety</a>
            <a href="privacy.php#user-rights" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">9. Your Privacy Rights</a>
            <a href="privacy.php#grievance" style="color: var(--slate-body); text-decoration: none; padding: 4px 0;">10. Grievance Officer Contact</a>
        </nav>
        <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border-color); font-size: 0.78rem; color: var(--slate-muted);">
            Also see our <a href="terms.php" style="color: var(--orange); font-weight: 700;">Terms of Service</a> &amp; <a href="disclaimer.php" style="color: var(--orange); font-weight: 700;">Disclaimer</a>.
        </div>
    </div>

    <!-- Right Main Legal Text Body -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px 32px; box-shadow: var(--shadow-sm); line-height: 1.8; color: var(--slate-body); font-size: 0.93rem;">

        <!-- Section 1 -->
        <section id="collection" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                1. Information We Collect
            </h2>
            <p style="margin-bottom: 12px;">
                Online Education collects information in two principal categories:
            </p>
            <div style="margin-bottom: 12px;">
                <strong style="color: var(--charcoal); display: block; margin-bottom: 4px;">A. Information You Voluntarily Provide:</strong>
                <ul style="list-style: disc; margin-left: 24px; display: flex; flex-direction: column; gap: 6px;">
                    <li><strong>Contact Identifiers:</strong> Your full name, mobile telephone number, and email address when you register for an account, request admission counseling, or submit a message through <a href="contact.php" style="color: var(--orange); font-weight: 700;">contact.php</a>.</li>
                    <li><strong>Academic Profile:</strong> Grade level, preferred academic streams (e.g. Engineering, Medicine, Humanities), state/district of residence, or target entrance examinations.</li>
                    <li><strong>Institutional Verification Documents:</strong> Official institutional email addresses, AISHE registration codes, and designation proofs supplied by university or school authorities for profile claiming.</li>
                </ul>
            </div>
            <div>
                <strong style="color: var(--charcoal); display: block; margin-bottom: 4px;">B. Automatically Generated Technical Data:</strong>
                <ul style="list-style: disc; margin-left: 24px; display: flex; flex-direction: column; gap: 6px;">
                    <li>Standard server access logs including IP address, browser user-agent, operating system, and pages browsed to maintain site performance, security, and DDOS mitigation.</li>
                </ul>
            </div>
        </section>

        <!-- Section 2 -->
        <section id="what-we-dont-do" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                2. What We Strictly DO NOT Do
            </h2>
            <p style="margin-bottom: 12px;">
                To safeguard student dignity and combat abusive education marketing practices in India, we commit to the following principles:
            </p>
            <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 10px;">
                <li style="display: flex; gap: 10px; align-items: flex-start;">
                    <span style="color: #EF4444; font-weight: 800;">✕</span>
                    <span><strong>No Bulk Lead Selling:</strong> We do NOT auction or wholesale student databases to third-party marketing companies, coaching class aggregators, or private lenders.</span>
                </li>
                <li style="display: flex; gap: 10px; align-items: flex-start;">
                    <span style="color: #EF4444; font-weight: 800;">✕</span>
                    <span><strong>No Unsolicited Robocalls:</strong> You will never receive automated voice robocalls or aggressive promotional blasts triggered by our systems.</span>
                </li>
                <li style="display: flex; gap: 10px; align-items: flex-start;">
                    <span style="color: #EF4444; font-weight: 800;">✕</span>
                    <span><strong>No Cross-Site Ad Tracking:</strong> We do not deploy intrusive third-party behavioural ad surveillance across other websites you visit.</span>
                </li>
            </ul>
        </section>

        <!-- Section 3 -->
        <section id="usage" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                3. How Your Data is Used
            </h2>
            <p style="margin-bottom: 12px;">
                Information collected on Online Education is utilized strictly for legitimate, user-initiated purposes:
            </p>
            <ul style="list-style: disc; margin-left: 24px; display: flex; flex-direction: column; gap: 8px;">
                <li>To authenticate user sessions and student logins via One-Time Password (OTP) verification.</li>
                <li>To route your specific inquiry or counseling request to the concerned educator, institution, or editorial desk.</li>
                <li>To notify you regarding updates to an inquiry or profile correction you submitted.</li>
                <li>To maintain system integrity, detect fraudulent entries, and prevent automated abuse.</li>
            </ul>
        </section>

        <!-- Section 4 -->
        <section id="dlt-sms" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                4. DLT SMS &amp; Telecom Regulations (TRAI Compliance)
            </h2>
            <p style="margin-bottom: 12px;">
                All SMS notifications, student login OTPs, and inquiry confirmations dispatched by Online Education strictly adhere to the Telecom Commercial Communications Customer Preference Regulations (TCCCPR, 2018) established by the Telecom Regulatory Authority of India (TRAI).
            </p>
            <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px 18px; margin-bottom: 12px; font-size: 0.88rem;">
                <strong>Registered Sender ID:</strong> <span style="font-family: monospace; font-size: 1rem; color: var(--orange-dark); font-weight: 800;">OEDUIN</span><br>
                <strong>Template ID:</strong> 1277179178303893981 (Transactional OTP &amp; Verification Alerts)<br>
                <strong>Category:</strong> Service Implicit / Transactional
            </div>
            <p>
                We do not dispatch unsolicited promotional SMS messages. All transactional communications can be verified by checking the sender header on your mobile handset.
            </p>
        </section>

        <!-- Section 5 -->
        <section id="legal-basis" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                5. Lawful Basis under DPDP Act 2023
            </h2>
            <p style="margin-bottom: 12px;">
                Under India's Digital Personal Data Protection Act, 2023, personal data is processed solely under the following lawful grounds:
            </p>
            <ul style="list-style: disc; margin-left: 24px; display: flex; flex-direction: column; gap: 8px;">
                <li><strong>Explicit Consent:</strong> Provided when you enter your mobile number and click "Submit Inquiry" or "Send OTP".</li>
                <li><strong>Certain Legitimate Uses:</strong> Permitted for cybersecurity, fraud mitigation, and complying with statutory judicial requests.</li>
            </ul>
        </section>

        <!-- Section 6 -->
        <section id="security" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                6. Data Security &amp; Encryption Standards
            </h2>
            <p style="margin-bottom: 12px;">
                We maintain robust technical, organizational, and physical safeguards to protect student data:
            </p>
            <ul style="list-style: disc; margin-left: 24px; display: flex; flex-direction: column; gap: 8px;">
                <li><strong>Transport Layer Security (TLS/SSL):</strong> All web communication between your browser and our servers is secured using modern cryptographic encryption protocols.</li>
                <li><strong>Restricted Database Access:</strong> Student inquiries are maintained on private database clusters protected by perimeter firewalls, least-privilege role authentication, and continuous security audits.</li>
                <li><strong>Sanitization &amp; Prepared Statements:</strong> All form inputs are sanitized to protect against SQL injection and cross-site scripting (XSS) vectors.</li>
            </ul>
        </section>

        <!-- Section 7 -->
        <section id="cookies" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                7. Cookies &amp; Local Storage Usage
            </h2>
            <p style="margin-bottom: 12px;">
                Online Education utilizes lightweight, functional cookies strictly for:
            </p>
            <ul style="list-style: disc; margin-left: 24px; display: flex; flex-direction: column; gap: 8px;">
                <li>Maintaining authenticated student or administrator login sessions (`PHPSESSID`).</li>
                <li>Remembering UI preferences, such as selected language (English or Hindi).</li>
                <li>Preserving state and district selections in directory filters for faster navigation.</li>
            </ul>
            <p>
                You may disable cookies via your browser settings, though session logins may be affected.
            </p>
        </section>

        <!-- Section 8 -->
        <section id="minors" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                8. Child &amp; Minor Safety Safeguards
            </h2>
            <p style="margin-bottom: 12px;">
                Recognizing that secondary school students explore CBSE directories and entrance syllabi, we uphold elevated standards of care for young learners under the age of 18:
            </p>
            <ul style="list-style: disc; margin-left: 24px; display: flex; flex-direction: column; gap: 8px;">
                <li>Inquiries for secondary school listings or minors must be submitted with verifiable parental or guardian knowledge.</li>
                <li>We do not profile, track behavioral patterns, or serve targeted commercial marketing to minor students.</li>
                <li>Parents and guardians may at any time request the immediate erasure of any data submitted by a minor.</li>
            </ul>
        </section>

        <!-- Section 9 -->
        <section id="user-rights" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                9. Your Rights as a Data Principal
            </h2>
            <p style="margin-bottom: 12px;">
                In accordance with the DPDP Act 2023, you enjoy full sovereignty over your personal data:
            </p>
            <ul style="list-style: disc; margin-left: 24px; display: flex; flex-direction: column; gap: 8px;">
                <li><strong>Right to Access:</strong> Request a summary of the personal information stored in relation to your mobile number.</li>
                <li><strong>Right to Correction:</strong> Request updates or corrections to any outdated or inaccurate details.</li>
                <li><strong>Right to Erasure:</strong> Request the deletion of your inquiry records from our active database.</li>
                <li><strong>Right to Grievance Redressal:</strong> Submit a complaint to our designated Data Protection Officer.</li>
            </ul>
        </section>

        <!-- Section 10 -->
        <section id="grievance">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                10. Grievance Redressal Officer
            </h2>
            <p style="margin-bottom: 12px;">
                If you have any questions, concerns, or requests regarding this Privacy Policy or wish to exercise your data principal rights, please contact our designated Grievance Officer:
            </p>
            <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px 24px; font-size: 0.9rem; line-height: 1.7;">
                <strong>Grievance Redressal &amp; Data Protection Officer</strong><br>
                OfferPlant Technologies Private Limited<br>
                Online Education Initiative (<a href="https://oedu.in" style="color: var(--charcoal); font-weight: 700;">oedu.in</a>)<br>
                Official Email: <a href="mailto:contact@oedu.in" style="color: var(--orange); font-weight: 700;">contact@oedu.in</a><br>
                Response Time: Within 24 to 72 business hours
            </div>
        </section>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
