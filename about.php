<?php
// about.php — About Online Education: National Education Platform of India (.php only)
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'About Us — Online Education | India\'s Unified Education Directory & Platform';
$pageDesc = 'Discover Online Education (oedu.in) — India\'s comprehensive independent platform mapping 72,800+ Universities, Colleges, Standalone Institutes, and 33,000+ CBSE Schools.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<div style="background: radial-gradient(circle at 90% 15%, rgba(255, 107, 0, 0.25) 0%, transparent 45%), linear-gradient(135deg, #070A10 0%, #111827 55%, #1F2937 100%); border: 1px solid rgba(255, 107, 0, 0.22); color: #FFFFFF; border-radius: var(--radius-lg); padding: 48px 32px; margin-bottom: 40px; box-shadow: var(--shadow-lg);">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #FFA35B; font-weight: 700; margin-bottom: 10px;">
        <span>🇮🇳</span> National Educational Initiative
    </div>
    <h1 style="font-size: 2.4rem; font-weight: 800; line-height: 1.2; margin-bottom: 12px;">
        About Online Education
    </h1>
    <div style="font-family: var(--font-hindi); font-size: 1.25rem; color: #FFA35B; font-weight: 600; margin-bottom: 14px;">
        विद्या ददाति विनयम् — One Education Platform for Every People
    </div>
    <p style="font-size: 1.05rem; color: #E5E7EB; max-width: 820px; line-height: 1.6; margin-bottom: 24px;">
        Online Education (<strong style="color: #FFF;">oedu.in</strong>) is India's premier independent public directory and discovery portal for Higher and Secondary Education. We connect over 250 million students, parents, and educators across all 36 States &amp; Union Territories with verified facts on schools, colleges, universities, cutoffs, and curriculums.
    </p>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="mission.php" class="btn btn-primary" style="padding: 10px 22px;">
            Read Our Mission →
        </a>
        <a href="vision.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            Explore Our Vision 2030
        </a>
        <a href="contact.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            Get in Touch
        </a>
    </div>
</div>

<!-- Scale & Impact Statistics -->
<div style="margin-bottom: 48px;">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 28px auto;">
        <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
            India's Most Comprehensive Educational Repository
        </h2>
        <p style="font-size: 0.92rem; color: var(--slate-muted);">
            Real-time, structured access to accredited institutions across every tier of Indian education.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px 20px; text-align: center; box-shadow: var(--shadow-sm); border-top: 4px solid var(--orange);">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--charcoal); line-height: 1.1; margin-bottom: 6px;">
                1,505+
            </div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--orange-dark); margin-bottom: 4px;">
                Universities
            </div>
            <div style="font-size: 0.8rem; color: var(--slate-muted);">
                Central, State Public, Deemed &amp; Institutes of National Importance
            </div>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px 20px; text-align: center; box-shadow: var(--shadow-sm); border-top: 4px solid #3B82F6;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--charcoal); line-height: 1.1; margin-bottom: 6px;">
                54,351+
            </div>
            <div style="font-size: 0.95rem; font-weight: 700; color: #2563EB; margin-bottom: 4px;">
                Affiliated Colleges
            </div>
            <div style="font-size: 0.8rem; color: var(--slate-muted);">
                Govt, Aided &amp; Private Degree Colleges with Affiliation Mapping
            </div>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px 20px; text-align: center; box-shadow: var(--shadow-sm); border-top: 4px solid #10B981;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--charcoal); line-height: 1.1; margin-bottom: 6px;">
                17,010+
            </div>
            <div style="font-size: 0.95rem; font-weight: 700; color: #059669; margin-bottom: 4px;">
                Standalone Institutes
            </div>
            <div style="font-size: 0.8rem; color: var(--slate-muted);">
                Polytechnics, Teacher Training, Nursing &amp; PGDM Business Schools
            </div>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px 20px; text-align: center; box-shadow: var(--shadow-sm); border-top: 4px solid #8B5CF6;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--charcoal); line-height: 1.1; margin-bottom: 6px;">
                33,233+
            </div>
            <div style="font-size: 0.95rem; font-weight: 700; color: #7C3AED; margin-bottom: 4px;">
                CBSE Schools
            </div>
            <div style="font-size: 0.8rem; color: var(--slate-muted);">
                Kendriya Vidyalayas, Navodaya Vidyalayas, Private &amp; Public Schools
            </div>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px 20px; text-align: center; box-shadow: var(--shadow-sm); border-top: 4px solid #EC4899;">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--charcoal); line-height: 1.1; margin-bottom: 6px;">
                780 / 36
            </div>
            <div style="font-size: 0.95rem; font-weight: 700; color: #DB2777; margin-bottom: 4px;">
                Districts &amp; States/UTs
            </div>
            <div style="font-size: 0.8rem; color: var(--slate-muted);">
                100% Hyperlocal Geographic Coverage across the Republic of India
            </div>
        </div>
    </div>
</div>

<!-- Our Story & Why We Built Online Education -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 36px; margin-bottom: 48px; align-items: center;">
    <div>
        <span style="font-size: 0.75rem; background: var(--orange-subtle); color: var(--orange-dark); font-weight: 700; padding: 4px 10px; border-radius: var(--radius-full); text-transform: uppercase;">
            Our Genesis
        </span>
        <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--charcoal); margin-top: 10px; margin-bottom: 16px; line-height: 1.3;">
            Tackling Information Asymmetry in Indian Education
        </h2>
        <p style="font-size: 0.95rem; color: var(--slate-body); line-height: 1.7; margin-bottom: 16px;">
            Every year, over 25 million Indian students graduate high school and face a labyrinth of admission decisions. Traditionally, education portals have operated as <strong>aggressive lead-generation farms</strong>: student mobile numbers are collected under the guise of "brochure downloads" and instantly auctioned off to dozens of private call centers.
        </p>
        <p style="font-size: 0.95rem; color: var(--slate-body); line-height: 1.7; margin-bottom: 16px;">
            Meanwhile, reliable data on genuine accreditation (UGC recognition, AICTE approvals, NAAC grades, and NIRF rankings) was scattered across multiple obscure government gazettes, hard-to-navigate databases, and siloed PDFs.
        </p>
        <p style="font-size: 0.95rem; color: var(--slate-body); line-height: 1.7;">
            <strong>Online Education was born to fix this broken landscape.</strong> We set out to build an open, dignified, and authoritative public educational directory that places student welfare and transparency above commercial syndication.
        </p>
    </div>

    <!-- Highlight Box -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; right: 0; width: 120px; height: 120px; background: radial-gradient(circle, rgba(255, 107, 0, 0.12) 0%, transparent 70%);"></div>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <span>🛡️</span> The OEDU Ethical Commitment
        </h3>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 14px;">
            <li style="display: flex; gap: 12px; font-size: 0.9rem; color: var(--slate-body); line-height: 1.5;">
                <span style="color: #10B981; font-weight: 800; font-size: 1.1rem;">✓</span>
                <div>
                    <strong>No Spam Policy:</strong> We never sell, lease, or auction student contact information to aggressive telemarketers or third-party brokers.
                </div>
            </li>
            <li style="display: flex; gap: 12px; font-size: 0.9rem; color: var(--slate-body); line-height: 1.5;">
                <span style="color: #10B981; font-weight: 800; font-size: 1.1rem;">✓</span>
                <div>
                    <strong>Neutral Discovery:</strong> Institutions are listed with their genuine AISHE codes, statutory affiliations, and official location coordinates.
                </div>
            </li>
            <li style="display: flex; gap: 12px; font-size: 0.9rem; color: var(--slate-body); line-height: 1.5;">
                <span style="color: #10B981; font-weight: 800; font-size: 1.1rem;">✓</span>
                <div>
                    <strong>Always Free for Students:</strong> Access to cutoffs, syllabus, scholarship directories, and college profiles is 100% free of charge.
                </div>
            </li>
            <li style="display: flex; gap: 12px; font-size: 0.9rem; color: var(--slate-body); line-height: 1.5;">
                <span style="color: #10B981; font-weight: 800; font-size: 1.1rem;">✓</span>
                <div>
                    <strong>Empowering Tier-2, 3 &amp; Rural India:</strong> Equal discoverability for regional government universities, rural polytechnics, and district schools.
                </div>
            </li>
        </ul>
    </div>
</div>

<!-- What We Cover Section -->
<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px 32px; margin-bottom: 48px; box-shadow: var(--shadow-sm);">
    <div style="text-align: center; max-width: 720px; margin: 0 auto 32px auto;">
        <span style="font-size: 0.75rem; background: var(--primary-subtle); color: var(--charcoal); font-weight: 700; padding: 4px 12px; border-radius: var(--radius-full); text-transform: uppercase;">
            Ecosystem Architecture
        </span>
        <h3 style="font-size: 1.6rem; font-weight: 800; color: var(--charcoal); margin-top: 10px; margin-bottom: 8px;">
            A Multidimensional Education Platform
        </h3>
        <p style="font-size: 0.9rem; color: var(--slate-muted);">
            From kindergarten to doctoral research, Online Education maps every transition point in a student's academic path.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
        <div style="padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: #FAFBFD;">
            <div style="font-size: 1.8rem; margin-bottom: 10px;">🏫</div>
            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                CBSE &amp; Secondary Schools
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6; margin-bottom: 12px;">
                33,233+ CBSE affiliated schools cataloged with state, district, affiliation status, and board examination syllabus breakdowns.
            </p>
            <a href="school.php" style="font-size: 0.82rem; font-weight: 700; color: var(--orange);">Explore Schools Directory →</a>
        </div>

        <div style="padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: #FAFBFD;">
            <div style="font-size: 1.8rem; margin-bottom: 10px;">🏛️</div>
            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                Universities &amp; Colleges
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6; margin-bottom: 12px;">
                Complete categorization of Central, State Public, Deemed, and Institutes of National Importance (IITs, IIMs, AIIMS, NITs, IISc).
            </p>
            <a href="institution.php" style="font-size: 0.82rem; font-weight: 700; color: var(--orange);">Explore Universities →</a>
        </div>

        <div style="padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: #FAFBFD;">
            <div style="font-size: 1.8rem; margin-bottom: 10px;">📖</div>
            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                National Syllabus Hub
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6; margin-bottom: 12px;">
                Comprehensive unit-wise curriculum tracking for CBSE Class 10 &amp; 12, CUET-UG domain subjects, JEE Main, and NEET-UG.
            </p>
            <a href="syllabus.php" style="font-size: 0.82rem; font-weight: 700; color: var(--orange);">View Syllabi Directory →</a>
        </div>

        <div style="padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: #FAFBFD;">
            <div style="font-size: 1.8rem; margin-bottom: 10px;">🎯</div>
            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                Entrance Cutoffs &amp; Calendars
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6; margin-bottom: 12px;">
                Authoritative schedules and historical cut-off marks for CUET, JEE, NEET, GATE, and Central University admissions.
            </p>
            <a href="exam.php" style="font-size: 0.82rem; font-weight: 700; color: var(--orange);">Check Entrance Cutoffs →</a>
        </div>

        <div style="padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: #FAFBFD;">
            <div style="font-size: 1.8rem; margin-bottom: 10px;">💰</div>
            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                Scholarships &amp; Welfare
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6; margin-bottom: 12px;">
                Central and State scholarship schemes, National Scholarship Portal (NSP) procedures, merit awards, and minority fee assistance.
            </p>
            <a href="scholarship.php" style="font-size: 0.82rem; font-weight: 700; color: var(--orange);">View Scholarships →</a>
        </div>

        <div style="padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: #FAFBFD;">
            <div style="font-size: 1.8rem; margin-bottom: 10px;">🌏</div>
            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">
                Global Indian Diaspora (NRI)
            </h4>
            <p style="font-size: 0.85rem; color: var(--slate-muted); line-height: 1.6; margin-bottom: 12px;">
                Specialized admissions guidance for NRI students abroad under DASA, CIWG, and mother tongue language mentorship worldwide.
            </p>
            <a href="global.php" style="font-size: 0.82rem; font-weight: 700; color: var(--orange);">Explore Global Hub →</a>
        </div>
    </div>
</div>

<!-- Backing Organization Section -->
<div style="background: linear-gradient(135deg, #070A10 0%, #111827 100%); color: #FFF; border-radius: var(--radius-lg); padding: 36px 32px; margin-bottom: 40px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 24px;">
    <div style="max-width: 680px;">
        <div style="font-size: 0.8rem; color: #FFA35B; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">
            Initiative By OfferPlant
        </div>
        <h3 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 8px; color: #FFF;">
            Building India's Digital Education Infrastructure
        </h3>
        <p style="font-size: 0.9rem; color: #9CA3AF; line-height: 1.6; margin: 0;">
            Online Education is engineered, maintained, and operated by <a href="https://offerplant.com" target="_blank" rel="noopener noreferrer" style="color: var(--orange); font-weight: 700;">OfferPlant</a> as a public-service technology contribution to the Indian education ecosystem. We believe that access to reliable educational data is a fundamental prerequisite for national progress.
        </p>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="contact.php" class="btn btn-primary" style="padding: 10px 22px;">
            Contact Desk
        </a>
        <a href="disclaimer.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            Read Disclaimer
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
