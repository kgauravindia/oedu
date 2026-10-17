<?php
// vision.php — The Vision 2030 of Online Education (.php only)
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Our Vision 2030 — Online Education | India\'s Unified Digital Education Backbone';
$pageDesc = 'Discover the Vision 2030 of Online Education: building India\'s most trusted, equitable, and intelligent public educational discovery highway in harmony with NEP 2020.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<div style="background: radial-gradient(circle at 90% 15%, rgba(255, 107, 0, 0.25) 0%, transparent 45%), linear-gradient(135deg, #070A10 0%, #111827 55%, #1F2937 100%); border: 1px solid rgba(255, 107, 0, 0.22); color: #FFFFFF; border-radius: var(--radius-lg); padding: 48px 32px; margin-bottom: 36px; box-shadow: var(--shadow-lg);">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #FFA35B; font-weight: 700; margin-bottom: 10px;">
        <span>🔭</span> The 2030 Horizon
    </div>
    <h1 style="font-size: 2.4rem; font-weight: 800; line-height: 1.2; margin-bottom: 14px;">
        Our Vision: Building India's Digital Knowledge Backbone
    </h1>
    <div style="font-family: var(--font-hindi); font-size: 1.2rem; color: #FFA35B; font-weight: 600; margin-bottom: 14px;">
        विद्या ददाति विनयम्, विनयाद्याति पात्रताम्।
    </div>
    <p style="font-size: 1.05rem; color: #E5E7EB; max-width: 820px; line-height: 1.7; margin-bottom: 24px;">
        To become India's definitive, unbiased, and universally accessible educational discovery compass — empowering every family with transparent data, fostering institutional integrity, and connecting the rich tapestry of Indian scholarship with the world.
    </p>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="mission.php" class="btn btn-primary" style="padding: 10px 22px;">
            Read Our Mission →
        </a>
        <a href="about.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            About the Platform
        </a>
        <a href="institution.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            Explore Institutions
        </a>
    </div>
</div>

<!-- Philosophical Rooting Card -->
<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; margin-bottom: 44px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
    <div style="position: absolute; right: -20px; bottom: -20px; font-size: 9rem; font-family: var(--font-hindi); color: rgba(255, 107, 0, 0.04); font-weight: 800; pointer-events: none;">
        विद्या
    </div>
    <div style="max-width: 800px;">
        <span style="font-size: 0.75rem; background: var(--orange-subtle); color: var(--orange-dark); font-weight: 700; padding: 4px 10px; border-radius: var(--radius-full); text-transform: uppercase;">
            Ancient Philosophy, Modern Technology
        </span>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--charcoal); margin-top: 10px; margin-bottom: 12px;">
            The Philosophy of True Learning
        </h2>
        <div style="font-family: var(--font-hindi); font-size: 1.15rem; color: var(--charcoal); font-weight: 700; line-height: 1.8; margin-bottom: 12px; border-left: 4px solid var(--orange); padding-left: 16px;">
            विद्या ददाति विनयम्, विनयाद्याति पात्रताम्।<br>
            पात्रत्वाद्धनमाप्नोति, धनाद्धर्मं ततः सुखम्॥
        </div>
        <p style="font-size: 0.92rem; color: var(--slate-body); line-height: 1.7; margin-bottom: 12px;">
            <em>"From true knowledge comes humility; from humility comes worthiness; from worthiness one attains prosperity; through prosperity one fulfills righteous duty, and thence attains lasting joy."</em>
        </p>
        <p style="font-size: 0.92rem; color: var(--slate-muted); line-height: 1.7;">
            In an era overwhelmed by commercial noise, our vision is to restore this timeless dignity to educational discovery. Technology should serve as a humble, transparent torchbearer — not a transactional paywall.
        </p>
    </div>
</div>

<!-- 4 Core Vision Dimensions -->
<div style="margin-bottom: 48px;">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 32px auto;">
        <span style="font-size: 0.75rem; background: var(--primary-subtle); color: var(--charcoal); font-weight: 700; padding: 4px 12px; border-radius: var(--radius-full); text-transform: uppercase;">
            The 2030 Roadmap
        </span>
        <h3 style="font-size: 1.7rem; font-weight: 800; color: var(--charcoal); margin-top: 10px; margin-bottom: 8px;">
            Our Vision Across 4 Critical Stakeholder Dimensions
        </h3>
        <p style="font-size: 0.92rem; color: var(--slate-muted);">
            Building a seamless digital ecosystem where students, parents, universities, and the diaspora interact with clarity and trust.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
        <!-- 1. For Students -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); border-top: 5px solid var(--orange);">
            <div style="font-size: 2.2rem; margin-bottom: 12px;">🎓</div>
            <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
                Vision for India's Students
            </h4>
            <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 14px;">
                An intelligent, unbiased, and lifelong learning compass. Every candidate, whether aspiring for CUET, JEE, NEET, or local polytechnics, will have transparent visibility into courses, cutoff trends, seat matrices, and genuine placement realities without paying extortionate career coaching fees.
            </p>
            <ul style="font-size: 0.82rem; color: var(--slate-muted); list-style: disc; margin-left: 20px; line-height: 1.6;">
                <li>Zero algorithmic bias or paid rankings</li>
                <li>Comprehensive syllabus breakdowns for every entrance exam</li>
                <li>Personalized scholarship matching across central &amp; state schemes</li>
            </ul>
        </div>

        <!-- 2. For Parents -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); border-top: 5px solid #3B82F6;">
            <div style="font-size: 2.2rem; margin-bottom: 12px;">👨‍👩‍👧</div>
            <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
                Vision for Parents &amp; Guardians
            </h4>
            <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 14px;">
                Complete peace of mind. We envision an educational admissions landscape where parents are liberated from aggressive, unsolicited sales calls, hidden capitation fees, and dubious accreditation claims. Every fee, hostel availability, and recognition status is verified against official records.
            </p>
            <ul style="font-size: 0.82rem; color: var(--slate-muted); list-style: disc; margin-left: 20px; line-height: 1.6;">
                <li>Strict parental consent &amp; child safety safeguards</li>
                <li>UGC, AICTE, and NAAC verification badges on every institution</li>
                <li>Transparent fee structure and loan eligibility guidance</li>
            </ul>
        </div>

        <!-- 3. For Institutions -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); border-top: 5px solid #10B981;">
            <div style="font-size: 2.2rem; margin-bottom: 12px;">🏛️</div>
            <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
                Vision for Schools &amp; Colleges
            </h4>
            <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 14px;">
                A merit-based national stage. Grassroots state colleges, rural autonomous institutes, and exemplary district schools gain equal discoverability as multibillion-rupee private conglomerates, fostering genuine academic excellence over marketing dominance.
            </p>
            <ul style="font-size: 0.82rem; color: var(--slate-muted); list-style: disc; margin-left: 20px; line-height: 1.6;">
                <li>Direct profile verification via AISHE &amp; CBSE affiliations</li>
                <li>Legitimate, verified student enquiries without commission cuts</li>
                <li>Promoting research output, patents, and faculty achievements</li>
            </ul>
        </div>

        <!-- 4. For Global Diaspora -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-sm); border-top: 5px solid #8B5CF6;">
            <div style="font-size: 2.2rem; margin-bottom: 12px;">🌏</div>
            <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--charcoal); margin-bottom: 10px;">
                Vision for the Global Indian Diaspora
            </h4>
            <p style="font-size: 0.88rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 14px;">
                A proud bridge connecting 32 million Non-Resident Indians (NRIs) across the Gulf, North America, Europe, and Asia-Pacific with India's premier educational institutions and cultural roots.
            </p>
            <ul style="font-size: 0.82rem; color: var(--slate-muted); list-style: disc; margin-left: 20px; line-height: 1.6;">
                <li>Transparent DASA, CIWG, and NRI quota admissions assistance</li>
                <li>Indian language mastery (Hindi, Sanskrit, Tamil, Telugu, Malayalam)</li>
                <li>Promoting India as a premier global study destination</li>
            </ul>
        </div>
    </div>
</div>

<!-- Harmonizing with NEP 2020 -->
<div style="background: linear-gradient(135deg, #070A10 0%, #111827 55%, #1F2937 100%); color: #FFF; border-radius: var(--radius-lg); padding: 40px 32px; margin-bottom: 40px; box-shadow: var(--shadow-lg);">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <span style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 1.5px; color: #FFA35B; font-weight: 700; margin-bottom: 8px; display: inline-block;">
            🇮🇳 Aligned with the National Education Policy 2020
        </span>
        <h3 style="font-size: 1.7rem; font-weight: 800; margin-top: 6px; margin-bottom: 14px; color: #FFF;">
            Pioneering the Future of Indian Learning
        </h3>
        <p style="font-size: 0.98rem; color: #E5E7EB; line-height: 1.7; margin-bottom: 24px;">
            The National Education Policy 2020 marks a historic paradigm shift: breaking rigid disciplinary silos, encouraging vocational education, recognizing the Academic Bank of Credits (ABC), and celebrating Indian languages. Online Education is engineered to be the digital engine that turns these policy aspirations into everyday reality for every Indian household.
        </p>
        <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
            <a href="government.php" class="btn btn-primary" style="padding: 10px 22px;">
                View UGC &amp; MoE Portals
            </a>
            <a href="syllabus.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF; padding: 10px 22px;">
                Explore National Syllabus
            </a>
            <a href="contact.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF; padding: 10px 22px;">
                Partner With Us
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
