<?php
// terms.php — Terms of Service & User Agreement (.php only)
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Terms of Service & User Agreement — Online Education';
$pageDesc = 'Terms and Conditions governing the use of Online Education (oedu.in) — directory data, user inquiries, intellectual property, and acceptable use policy.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Document Hero Header -->
<div style="background: radial-gradient(circle at 90% 15%, rgba(255, 107, 0, 0.25) 0%, transparent 45%), linear-gradient(135deg, #070A10 0%, #111827 55%, #1F2937 100%); border: 1px solid rgba(255, 107, 0, 0.22); color: #FFFFFF; border-radius: var(--radius-lg); padding: 44px 32px; margin-bottom: 36px; box-shadow: var(--shadow-lg);">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #FFA35B; font-weight: 700; margin-bottom: 10px;">
        <span>📜</span> Legal &amp; Governance
    </div>
    <h1 style="font-size: 2.3rem; font-weight: 800; line-height: 1.2; margin-bottom: 12px;">
        Terms of Service &amp; User Agreement
    </h1>
    <p style="font-size: 1rem; color: #E5E7EB; max-width: 780px; line-height: 1.6; margin-bottom: 18px;">
        Please read these Terms of Service carefully before accessing or using the Online Education platform (<strong style="color: #FFF;">oedu.in</strong>). By accessing or browsing our directory, you agree to be bound by these terms.
    </p>
    <div style="display: flex; gap: 16px; flex-wrap: wrap; font-size: 0.82rem; color: #9CA3AF;">
        <span>📅 <strong>Effective Date:</strong> January 1, 2026</span>
        <span>🔄 <strong>Last Revised:</strong> October 2026</span>
        <span>⚖️ <strong>Jurisdiction:</strong> Republic of India</span>
    </div>
</div>

<!-- Layout: Table of Contents + Content Body -->
<div style="display: grid; grid-template-columns: 280px 1fr; gap: 36px; margin-bottom: 48px; align-items: start;">

    <!-- Left Sticky Sidebar: Navigation Index -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; position: sticky; top: 20px; box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 0.92rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--charcoal); margin-bottom: 14px;">
            Contents
        </h3>
        <nav style="display: flex; flex-direction: column; gap: 8px; font-size: 0.84rem;">
            <a href="terms.php#acceptance" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">1. Acceptance &amp; Eligibility</a>
            <a href="terms.php#nature" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">2. Nature of Service</a>
            <a href="terms.php#accuracy" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">3. Data Accuracy &amp; Disclaimers</a>
            <a href="terms.php#conduct" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">4. Acceptable Use &amp; Scraping</a>
            <a href="terms.php#enquiries" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">5. Inquiries &amp; DLT SMS</a>
            <a href="terms.php#ip" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">6. Intellectual Property &amp; Logos</a>
            <a href="terms.php#third-party" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">7. External Portals &amp; Links</a>
            <a href="terms.php#liability" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">8. Limitation of Liability</a>
            <a href="terms.php#indemnity" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">9. Indemnification</a>
            <a href="terms.php#jurisdiction" style="color: var(--slate-body); text-decoration: none; padding: 4px 0; border-bottom: 1px solid #F1F5F9;">10. Governing Law &amp; Jurisdiction</a>
            <a href="terms.php#contact" style="color: var(--slate-body); text-decoration: none; padding: 4px 0;">11. Legal Inquiries &amp; Contact</a>
        </nav>
        <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border-color); font-size: 0.78rem; color: var(--slate-muted);">
            Need privacy information? Read our <a href="privacy.php" style="color: var(--orange); font-weight: 700;">Privacy Policy</a>.
        </div>
    </div>

    <!-- Right Main Legal Text Body -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px 32px; box-shadow: var(--shadow-sm); line-height: 1.8; color: var(--slate-body); font-size: 0.93rem;">

        <!-- Section 1 -->
        <section id="acceptance" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                1. Acceptance of Terms &amp; Eligibility
            </h2>
            <p style="margin-bottom: 12px;">
                These Terms of Service constitute a legally binding agreement between you ("User", "Visitor", "Student", or "Parent") and <strong>OfferPlant Technologies Private Limited</strong>, the operator and owner of <strong>Online Education</strong> (accessible at <a href="https://oedu.in" style="color: var(--orange); font-weight: 700;">oedu.in</a>).
            </p>
            <p style="margin-bottom: 12px;">
                By using our directory, browsing institutional profiles, downloading syllabi, or submitting contact inquiries, you affirm that you are at least 18 years of age or possess legal parental/guardian consent if you are a minor, and are competent to enter into this agreement.
            </p>
            <p>
                If you do not agree to all provisions of these Terms, you must immediately discontinue use of the platform.
            </p>
        </section>

        <!-- Section 2 -->
        <section id="nature" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                2. Nature of Service &amp; Independent Directory Status
            </h2>
            <p style="margin-bottom: 12px;">
                Online Education is an independent digital information directory, aggregator, and educational search engine. Our primary objective is to present structured, publicly available higher and secondary education data for educational research, student welfare, and informational purposes.
            </p>
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-left: 4px solid #3B82F6; padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 12px; font-size: 0.88rem; color: #1E40AF;">
                <strong>⚠️ Non-Government Entity Clarification:</strong> Online Education is an independent technology project. We are NOT an organ, ministry, or department of the Government of India, UGC, AICTE, NTA, or CBSE. We do not issue degrees, conduct admissions, or represent quota allocations.
            </div>
        </section>

        <!-- Section 3 -->
        <section id="accuracy" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                3. Data Sourcing &amp; Accuracy Disclaimers
            </h2>
            <p style="margin-bottom: 12px;">
                Institutional profiles, AISHE codes, affiliation numbers, fee indications, cutoff marks, and contact phone numbers published on this portal are compiled from official public domain registries (such as AISHE, UGC, AICTE, NIRF, and CBSE official gazettes) and voluntary institutional submissions.
            </p>
            <p style="margin-bottom: 12px;">
                While we make diligent efforts to maintain up-to-date data, admission calendars, fee schedules, seat counts, and statutory affiliations are subject to frequent revisions by individual universities, examination boards, and state governments without prior notice.
            </p>
            <p style="font-weight: 600; color: var(--charcoal);">
                Users are strictly advised to cross-verify all critical admission deadlines, entrance cutoffs, and fee structures directly on the official designated website of the respective institution or examination conducting body before making any financial or academic commitments.
            </p>
        </section>

        <!-- Section 4 -->
        <section id="conduct" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                4. Acceptable Use Policy &amp; Anti-Scraping Restrictions
            </h2>
            <p style="margin-bottom: 12px;">
                You agree to use Online Education exclusively for lawful personal, academic, or research purposes. You expressly agree that you shall NOT:
            </p>
            <ul style="list-style: disc; margin-left: 24px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;">
                <li>Deploy automated scrapers, crawlers, harvest bots, or extraction scripts to replicate our database or directory content without prior written permission from OfferPlant.</li>
                <li>Attempt to bypass, overwhelm, or circumvent platform rate limits, security headers, or server infrastructure (e.g. Distributed Denial of Service attacks).</li>
                <li>Submit false, fraudulent, defamatory, or deceptive inquiries or impersonate any individual, university official, or statutory authority.</li>
                <li>Use student inquiry channels to transmit unsolicited advertising, promotional spam, or malicious payloads.</li>
            </ul>
        </section>

        <!-- Section 5 -->
        <section id="enquiries" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                5. Student Inquiries, Consent &amp; DLT SMS Notifications
            </h2>
            <p style="margin-bottom: 12px;">
                When you submit an inquiry through our portal, you explicitly consent to receive transactional and informational verification communications via SMS, WhatsApp, or phone call regarding your specific query.
            </p>
            <p style="margin-bottom: 12px;">
                All SMS verification alerts and OTPs are dispatched under our registered TRAI DLT header <strong style="color: var(--charcoal);">OEDUIN</strong> in accordance with the Telecom Commercial Communications Customer Preference Regulations (TCCCPR). We do not send promotional broadcast messages without explicit opt-in.
            </p>
            <p>
                You retain the right to withdraw your enquiry at any time by contacting our data protection desk at <a href="mailto:contact@oedu.in" style="color: var(--orange); font-weight: 700;">contact@oedu.in</a>.
            </p>
        </section>

        <!-- Section 6 -->
        <section id="ip" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                6. Intellectual Property, Institutional Logos &amp; Fair Use
            </h2>
            <p style="margin-bottom: 12px;">
                The platform design, layout, compilation database, source code, graphics, branding, and the motto <em>"विद्या ददाति विनयम्"</em> are the intellectual property of OfferPlant Technologies Private Limited.
            </p>
            <p style="margin-bottom: 12px;">
                All institutional logos, university crests, and regulatory insignias displayed on this site are the trademarks and copyright of their respective statutory bodies, universities, and schools. They are depicted strictly under <strong>Nominative Fair Use</strong> provisions for educational reference, identification, and public discovery. Their display does not imply sponsorship, endorsement, or commercial affiliation.
            </p>
        </section>

        <!-- Section 7 -->
        <section id="third-party" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                7. External Gateways &amp; Third-Party Links
            </h2>
            <p style="margin-bottom: 12px;">
                Our directory provides outbound hyperlinks to official government websites (such as <code>education.gov.in</code>, <code>ugc.gov.in</code>, <code>nta.ac.in</code>) and individual university admission portals.
            </p>
            <p>
                We exercise no control over the content, uptime, availability, or privacy practices of external third-party sites. Accessing outbound links is undertaken at your own discretion.
            </p>
        </section>

        <!-- Section 8 -->
        <section id="liability" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                8. Limitation of Liability &amp; Disclaimer of Warranties
            </h2>
            <p style="margin-bottom: 12px;">
                The platform and all directory listings are provided on an <strong>"AS IS"</strong> and <strong>"AS AVAILABLE"</strong> basis without warranties of any kind, whether express, statutory, or implied.
            </p>
            <p style="margin-bottom: 12px;">
                OfferPlant and Online Education disclaim all liability for any direct, indirect, incidental, consequential, or punitive damages arising from:
            </p>
            <ul style="list-style: disc; margin-left: 24px; margin-bottom: 12px;">
                <li>Decisions taken based upon fee details, syllabus outlines, or cutoff trends displayed on the portal.</li>
                <li>Failure to secure admission, interview calls, or scholarship awards from any listed institution.</li>
                <li>Temporary server downtime, network interruptions, or data transmission latency.</li>
            </ul>
        </section>

        <!-- Section 9 -->
        <section id="indemnity" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                9. Indemnification
            </h2>
            <p>
                You agree to indemnify, defend, and hold harmless OfferPlant Technologies, its directors, developers, and affiliates from and against any claims, liabilities, damages, losses, or legal costs arising out of your violation of these Terms or misuse of the platform.
            </p>
        </section>

        <!-- Section 10 -->
        <section id="jurisdiction" style="margin-bottom: 32px;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                10. Governing Law &amp; Dispute Resolution
            </h2>
            <p style="margin-bottom: 12px;">
                These Terms of Service and any disputes arising out of or related to the platform shall be governed by and construed in accordance with the laws of the Republic of India.
            </p>
            <p>
                Any legal action or proceeding shall be subject to the exclusive jurisdiction of the competent courts of law in India.
            </p>
        </section>

        <!-- Section 11 -->
        <section id="contact">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid var(--orange-subtle);">
                11. Legal Inquiries &amp; Grievance Redressal
            </h2>
            <p style="margin-bottom: 12px;">
                For questions, clarifications, or formal notices regarding these Terms of Service, please contact our Legal &amp; Compliance desk:
            </p>
            <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px 22px; font-size: 0.88rem; line-height: 1.6;">
                <strong>Legal &amp; Compliance Officer</strong><br>
                Online Education — An Initiative by OfferPlant Technologies Pvt. Ltd.<br>
                Email: <a href="mailto:contact@oedu.in" style="color: var(--orange); font-weight: 700;">contact@oedu.in</a><br>
                Official Website: <a href="https://oedu.in" style="color: var(--charcoal); font-weight: 700;">https://oedu.in</a>
            </div>
        </section>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
