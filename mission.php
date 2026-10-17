<?php
// mission.php — The Mission of Online Education (.php only)
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Our Mission — Online Education | Democratizing Educational Access Across India';
$pageDesc = 'Discover the mission of Online Education: bridging information asymmetry in Indian education, protecting student data privacy, and empowering 250M+ learners.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<div style="background: radial-gradient(circle at 90% 15%, rgba(255, 107, 0, 0.25) 0%, transparent 45%), linear-gradient(135deg, #070A10 0%, #111827 55%, #1F2937 100%); border: 1px solid rgba(255, 107, 0, 0.22); color: #FFFFFF; border-radius: var(--radius-lg); padding: 48px 32px; margin-bottom: 36px; box-shadow: var(--shadow-lg);">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #FFA35B; font-weight: 700; margin-bottom: 10px;">
        <span>🎯</span> Our Core Purpose
    </div>
    <h1 style="font-size: 2.4rem; font-weight: 800; line-height: 1.2; margin-bottom: 14px;">
        Our Mission: Empowering Every Learner in India
    </h1>
    <p style="font-size: 1.15rem; color: #FFA35B; font-weight: 600; line-height: 1.5; margin-bottom: 14px;">
        To democratize access to authentic, unbiased, and comprehensive educational intelligence across all 36 States &amp; Union Territories of India.
    </p>
    <p style="font-size: 1rem; color: #E5E7EB; max-width: 820px; line-height: 1.7; margin-bottom: 24px;">
        We believe that quality education begins with reliable information. By cataloging 72,800+ higher education institutions and 33,000+ schools, we dismantle information barriers so that every student — from rural districts to bustling metros — can make informed, life-changing educational choices without falling prey to deceptive marketing.
    </p>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="vision.php" class="btn btn-primary" style="padding: 10px 22px;">
            Explore Our Vision 2030 →
        </a>
        <a href="about.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            About Online Education
        </a>
        <a href="contact.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            Get in Touch
        </a>
    </div>
</div>

<!-- Mission Statement Callout Box -->
<div style="background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%); border: 1px solid var(--orange-border); border-left: 6px solid var(--orange); border-radius: var(--radius-md); padding: 26px 30px; margin-bottom: 44px; box-shadow: var(--shadow-sm);">
    <div style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; color: var(--orange-dark); margin-bottom: 8px;">
        Official Mission Declaration
    </div>
    <div style="font-size: 1.15rem; font-weight: 700; color: #7C2D12; line-height: 1.6;">
        "To build India's most trustworthy, neutral, and comprehensive digital educational directory, ensuring that no aspiring student is denied the right guidance due to geographical isolation, economic disparity, or commercial exploitation."
    </div>
</div>

<!-- 5 Strategic Pillars of the Mission -->
<div style="margin-bottom: 48px;">
    <div style="text-align: center; max-width: 720px; margin: 0 auto 32px auto;">
        <span style="font-size: 0.75rem; background: var(--primary-subtle); color: var(--charcoal); font-weight: 700; padding: 4px 12px; border-radius: var(--radius-full); text-transform: uppercase;">
            Pillars of Action
        </span>
        <h2 style="font-size: 1.7rem; font-weight: 800; color: var(--charcoal); margin-top: 10px; margin-bottom: 8px;">
            The 5 Commitments That Drive Our Mission
        </h2>
        <p style="font-size: 0.92rem; color: var(--slate-muted);">
            How we translate our vision into measurable, everyday impact for Indian students and institutions.
        </p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Pillar 1 -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); display: grid; grid-template-columns: auto 1fr; gap: 24px; align-items: start;">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(255, 107, 0, 0.12); color: var(--orange); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 800; flex-shrink: 0;">
                01
            </div>
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                    Democratizing Access to Statutory &amp; Accredited Data
                </h3>
                <p style="font-size: 0.92rem; color: var(--slate-body); line-height: 1.7; margin-bottom: 12px;">
                    In India, authentic educational data (AISHE codes, UGC university recognitions, AICTE approvals, NAAC grades, and NIRF rankings) has traditionally been locked inside disparate PDF reports and unindexed government databases. Our mission is to clean, index, and organize this data into a lightning-fast, searchable public directory where every citizen can verify college credentials in seconds.
                </p>
                <div style="font-size: 0.82rem; font-weight: 700; color: var(--orange);">
                    Key Milestone: 72,800+ Higher Education Institutions &amp; 33,233+ CBSE Schools indexed.
                </div>
            </div>
        </div>

        <!-- Pillar 2 -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); display: grid; grid-template-columns: auto 1fr; gap: 24px; align-items: start;">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(59, 130, 246, 0.12); color: #3B82F6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 800; flex-shrink: 0;">
                02
            </div>
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                    Absolute Student Privacy &amp; Anti-Spam Guarantee
                </h3>
                <p style="font-size: 0.92rem; color: var(--slate-body); line-height: 1.7; margin-bottom: 12px;">
                    Traditional education aggregators treat students as commercial commodities, selling their contact numbers to aggressive lead syndicates and private telemarketers. Online Education exists to reject this abusive model. We pledge that student phone numbers and emails are <strong>never sold, leased, or broadcasted</strong> to unauthorized third parties. All notifications are strictly consent-based and dispatched through our DLT-registered sender ID <strong style="color: var(--charcoal);">OEDUIN</strong>.
                </p>
                <div style="font-size: 0.82rem; font-weight: 700; color: #2563EB;">
                    Key Principle: Zero unsolicited marketing calls. DPDP Act 2023 compliance.
                </div>
            </div>
        </div>

        <!-- Pillar 3 -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); display: grid; grid-template-columns: auto 1fr; gap: 24px; align-items: start;">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(16, 185, 129, 0.12); color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 800; flex-shrink: 0;">
                03
            </div>
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                    Bridging the Urban-Rural Digital Divide
                </h3>
                <p style="font-size: 0.92rem; color: var(--slate-body); line-height: 1.7; margin-bottom: 12px;">
                    Over 65% of India's students reside in Tier-2, Tier-3 towns and rural panchayats where career counselling and verified information are virtually nonexistent. By providing district-level filtering across all 780 districts and 36 States/UTs, we give rural government colleges, district polytechnics, and Navodaya Vidyalayas the same national digital discoverability as premier metro universities.
                </p>
                <div style="font-size: 0.82rem; font-weight: 700; color: #059669;">
                    Key Metric: All 780 Districts of India mapped with cascading local discovery.
                </div>
            </div>
        </div>

        <!-- Pillar 4 -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); display: grid; grid-template-columns: auto 1fr; gap: 24px; align-items: start;">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(139, 92, 246, 0.12); color: #8B5CF6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 800; flex-shrink: 0;">
                04
            </div>
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                    Comprehensive Syllabus &amp; Entrance Transparency
                </h3>
                <p style="font-size: 0.92rem; color: var(--slate-body); line-height: 1.7; margin-bottom: 12px;">
                    Examinations like CUET-UG/PG, JEE Main, NEET-UG, GATE, and CBSE Board exams decide the future of millions. Our mission is to provide free, structured, unit-wise curriculum tracking, official marking schemes, and historical cutoff trends so students can plan their preparation strategically without expensive coaching subscriptions.
                </p>
                <div style="font-size: 0.82rem; font-weight: 700; color: #7C3AED;">
                    Key Feature: Free National Syllabus &amp; Cutoff Directory across all streams.
                </div>
            </div>
        </div>

        <!-- Pillar 5 -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); display: grid; grid-template-columns: auto 1fr; gap: 24px; align-items: start;">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(236, 72, 153, 0.12); color: #EC4899; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 800; flex-shrink: 0;">
                05
            </div>
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                    Championing the Spirit of NEP 2020
                </h3>
                <p style="font-size: 0.92rem; color: var(--slate-body); line-height: 1.7; margin-bottom: 12px;">
                    India's National Education Policy 2020 envisions multidisciplinary education, academic flexibility, multiple entry-exit points, and vocational training integration. Our platform actively indexes multidisciplinary institutes, Academic Bank of Credits (ABC) readiness, and skill certifications to foster a flexible, future-ready learner mindset.
                </p>
                <div style="font-size: 0.82rem; font-weight: 700; color: #DB2777;">
                    Key Alignment: Promoting multidisciplinary higher education and vocational dignity.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Core Values Section -->
<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px 32px; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
    <div style="text-align: center; max-width: 680px; margin: 0 auto 28px auto;">
        <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
            Our Operating Values
        </h3>
        <p style="font-size: 0.9rem; color: var(--slate-muted);">
            The fundamental beliefs that steer our design choices, editorial policies, and engineering priorities.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
        <div style="padding: 20px; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--border-color);">
            <div style="font-size: 1.5rem; margin-bottom: 8px;">⚖️</div>
            <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
                Factual Impartiality
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.5;">
                We refuse to accept money to boost institutional rankings. Every college and university stands on its official NIRF, NAAC, and AISHE credentials.
            </p>
        </div>

        <div style="padding: 20px; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--border-color);">
            <div style="font-size: 1.5rem; margin-bottom: 8px;">🛡️</div>
            <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
                Student Dignity
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.5;">
                Students and parents are not sales leads. We safeguard their peace of mind by prohibiting abusive telephone marketing and data auctioning.
            </p>
        </div>

        <div style="padding: 20px; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--border-color);">
            <div style="font-size: 1.5rem; margin-bottom: 8px;">🌍</div>
            <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
                Universal Inclusivity
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.5;">
                Whether studying in Hindi, English, or regional languages, every student deserves equal dignity and ease of access to higher education information.
            </p>
        </div>

        <div style="padding: 20px; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--border-color);">
            <div style="font-size: 1.5rem; margin-bottom: 8px;">⚡</div>
            <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
                Technological Speed
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.5;">
                Fast, lightweight web engineering optimized for 3G/4G connections across rural India without heavy bloatware or mandatory app downloads.
            </p>
        </div>
    </div>
</div>

<!-- Navigation Call to Action -->
<div style="background: linear-gradient(135deg, #070A10 0%, #111827 100%); color: #FFF; border-radius: var(--radius-lg); padding: 36px 32px; margin-bottom: 40px; text-align: center;">
    <h3 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 10px;">
        Join Us in Transforming Education in India
    </h3>
    <p style="font-size: 0.95rem; color: #9CA3AF; max-width: 680px; margin: 0 auto 24px auto; line-height: 1.6;">
        Explore our comprehensive directories, check official syllabus breakdowns, or contribute by updating your institution's profile.
    </p>
    <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
        <a href="institution.php" class="btn btn-primary" style="padding: 11px 24px;">
            Search Universities
        </a>
        <a href="school.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF; padding: 11px 24px;">
            Browse CBSE Schools
        </a>
        <a href="contact.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF; padding: 11px 24px;">
            Contact Desk
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
