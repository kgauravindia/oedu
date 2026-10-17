<?php
// disclaimer.php — Official Disclaimer & Non-Affiliation Notice (.php only)
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Official Disclaimer & Non-Affiliation Policy — Online Education';
$pageDesc = 'Independent public directory notice: Online Education (oedu.in) is an educational reference portal and is not affiliated with the Government of India, UGC, AICTE, or NTA.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<div style="background: radial-gradient(circle at 90% 15%, rgba(255, 107, 0, 0.25) 0%, transparent 45%), linear-gradient(135deg, #070A10 0%, #111827 55%, #1F2937 100%); border: 1px solid rgba(255, 107, 0, 0.22); color: #FFFFFF; border-radius: var(--radius-lg); padding: 44px 32px; margin-bottom: 36px; box-shadow: var(--shadow-lg);">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #FFA35B; font-weight: 700; margin-bottom: 10px;">
        <span>⚠️</span> Statutory Notice &amp; Legal Advisory
    </div>
    <h1 style="font-size: 2.3rem; font-weight: 800; line-height: 1.2; margin-bottom: 12px;">
        Official Disclaimer &amp; Non-Affiliation Notice
    </h1>
    <p style="font-size: 1.05rem; color: #E5E7EB; max-width: 820px; line-height: 1.6; margin-bottom: 20px;">
        Online Education (<strong style="color: #FFF;">oedu.in</strong>) operates as an independent public educational directory. Please review our institutional non-affiliation policy, data sourcing parameters, and admission advisories.
    </p>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="terms.php" class="btn btn-primary" style="padding: 10px 22px;">
            Terms of Service →
        </a>
        <a href="privacy.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            Privacy Policy
        </a>
        <a href="contact.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            Report a Discrepancy
        </a>
    </div>
</div>

<!-- Primary Non-Government Status Banner -->
<div style="background: #FEF3C7; border: 1px solid #FCD34D; border-left: 6px solid #D97706; border-radius: var(--radius-md); padding: 24px 28px; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
        <span style="font-size: 1.5rem;">🇮🇳</span>
        <h2 style="font-size: 1.15rem; font-weight: 800; color: #92400E; margin: 0;">
            Non-Governmental Entity Declaration
        </h2>
    </div>
    <p style="font-size: 0.95rem; color: #78350F; line-height: 1.7; margin: 0;">
        <strong>Online Education (oedu.in) is an independent digital educational portal operated by OfferPlant Technologies Pvt. Ltd.</strong> We are <strong>NOT</strong> an official body, department, ministry, or agency of the Government of India, the Ministry of Education (MoE), the University Grants Commission (UGC), the All India Council for Technical Education (AICTE), the Central Board of Secondary Education (CBSE), the National Testing Agency (NTA), or any State Government Education Department.
    </p>
</div>

<!-- Detailed Disclaimer Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 28px; margin-bottom: 44px;">

    <!-- Card 1: Data Sourcing & Fair Use -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">📊</div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
            1. Source of Information &amp; Public Data
        </h3>
        <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 12px;">
            The data published on this portal — including AISHE codes, affiliation numbers, university classifications, NIRF ranks, NAAC grades, examination dates, and syllabus blueprints — is aggregated from <strong>public domain government records</strong>, statutory notifications, official gazettes, and verified institutional disclosures.
        </p>
        <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6;">
            We curate this data purely for public interest, research, and non-commercial educational transparency under fair use doctrines.
        </p>
    </div>

    <!-- Card 2: Verification of Admissions & Fees -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">🎓</div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
            2. Mandatory Direct Verification by Students
        </h3>
        <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 12px;">
            Admission eligibility, cutoff percentiles, tuition fees, hostel expenses, and seat allocation matrices undergo frequent modifications by university senates and examination authorities without advance notice.
        </p>
        <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6;">
            <strong>Users must cross-check all deadlines and fee schedules directly on the official portal of the respective college, university, or testing agency</strong> before making any academic, travelling, or financial commitment.
        </p>
    </div>

    <!-- Card 3: No Guarantee of Admission -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">🚫</div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
            3. Zero Guarantee of Admission or Placement
        </h3>
        <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 12px;">
            Online Education is strictly an information discovery platform. We do <strong>NOT</strong> sell college seats, do NOT operate management quota admissions, and do NOT guarantee entrance selection, interview clearance, or employment placements.
        </p>
        <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6;">
            Beware of fraudulent third-party agents claiming to offer guaranteed admissions or paid seats on behalf of this platform.
        </p>
    </div>

    <!-- Card 4: Trademark & Logo Fair Use -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">🏛️</div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
            4. Logos, Emblems &amp; Trademarks Fair Use
        </h3>
        <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 12px;">
            All names, trademarks, crests, logos, and emblems of universities, colleges, examination bodies, and government ministries displayed on this website belong exclusively to their respective owners.
        </p>
        <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6;">
            Such references are made solely for descriptive, identifying, and educational discovery purposes (Nominative Fair Use) and do not imply endorsement, affiliation, or partnership.
        </p>
    </div>

    <!-- Card 5: External Hyperlinks -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">🌐</div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
            5. Outbound Links &amp; Third-Party Services
        </h3>
        <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 12px;">
            For the convenience of students, we provide direct outbound links to official regulatory portals (such as <code>education.gov.in</code>, <code>ugc.gov.in</code>, <code>aicte-india.org</code>, and <code>nta.ac.in</code>).
        </p>
        <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6;">
            We do not endorse, guarantee, or assume liability for the contents, server uptime, security, or privacy practices of external third-party portals.
        </p>
    </div>

    <!-- Card 6: Correction & Takedown Protocol -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">✏️</div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
            6. Institutional Correction &amp; Takedown Requests
        </h3>
        <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 12px;">
            If you are a Vice-Chancellor, Registrar, Principal, or authorized representative of an institution and notice an outdated phone number, incorrect fee, or obsolete course list, we provide immediate review and correction.
        </p>
        <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6;">
            Send your correction notice to <a href="mailto:contact@oedu.in" style="color: var(--orange); font-weight: 700;">contact@oedu.in</a> with your official institutional email and AISHE or Affiliation proof.
        </p>
    </div>
</div>

<!-- Grievance Officer & Contact Box -->
<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 24px;">
    <div>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
            Grievance Redressal &amp; Accuracy Inquiries
        </h3>
        <p style="font-size: 0.9rem; color: var(--slate-muted); margin: 0; max-width: 680px; line-height: 1.6;">
            For statutory concerns, copyright notices, or data inaccuracies, our editorial verification desk is reachable at <strong style="color: var(--charcoal);">contact@oedu.in</strong>. Requests are typically acknowledged within 24 to 48 business hours.
        </p>
    </div>
    <a href="contact.php" class="btn btn-primary" style="padding: 11px 24px; white-space: nowrap;">
        Contact Verification Desk →
    </a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
