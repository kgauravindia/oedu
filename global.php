<?php
// global.php — Oedu Global for Indians Living Abroad (NRI Section) (.php only)
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Oedu Global — Indian Education for Diaspora & NRI Families Worldwide | Oedu.in';
require_once __DIR__ . '/includes/header.php';
?>

<div style="background: radial-gradient(circle at 90% 15%, rgba(255, 107, 0, 0.25) 0%, transparent 45%), linear-gradient(135deg, #070A10 0%, #111827 55%, #1F2937 100%); border: 1px solid rgba(255, 107, 0, 0.22); color: #FFFFFF; border-radius: var(--radius-lg); padding: 48px 32px; margin-bottom: 40px; box-shadow: var(--shadow-lg);">
    <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #FFA35B; font-weight: 700; margin-bottom: 8px;">
        🌏 Connecting The Indian Diaspora
    </div>
    <h1 style="font-size: 2.4rem; font-weight: 800; line-height: 1.2; margin-bottom: 12px;">
        Oedu Global: Indian Education for NRI Families
    </h1>
    <p style="font-size: 1.05rem; color: #E5E7EB; max-width: 780px; line-height: 1.6; margin-bottom: 24px;">
        Serving Indian students living across the United Arab Emirates, United States, United Kingdom, Canada, Australia, and Singapore. Bringing the rich Indian curriculum, language mastery, and cultural heritage to learners worldwide.
    </p>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="tutors.php?mode=online" class="btn btn-primary" style="padding: 10px 22px;">
            Find Global Online Tutor →
        </a>
        <a href="learn.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.4); color: #FFF;">
            Access CBSE Digital Textbooks
        </a>
    </div>
</div>

<!-- 4 Key Pillars for NRIs -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 48px;">
    <!-- 1. Indian Curriculum Abroad -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">📘</div>
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">CBSE & Indian Curriculum Abroad</h3>
        <p style="font-size: 0.85rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 14px;">
            Structured study materials, syllabus tracking, sample question papers, and NCERT solutions tailored for students enrolled in Indian curriculum schools across Dubai, Abu Dhabi, Doha, and Riyadh.
        </p>
        <span style="font-size: 0.78rem; font-weight: 700; color: var(--primary);">Full Class 1–12 Coverage</span>
    </div>

    <!-- 2. Mother Tongue & Language -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">🗣️</div>
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">Indian Languages & Heritage</h3>
        <p style="font-size: 0.85rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 14px;">
            Help children growing up abroad master their mother tongue with live 1-on-1 tutoring in Hindi, Sanskrit, Tamil, Telugu, Marathi, Malayalam, and Bengali, alongside Indian history and cultural literacy.
        </p>
        <span style="font-size: 0.78rem; font-weight: 700; color: var(--primary);">Native Indian Language Mentors</span>
    </div>

    <!-- 3. Timezone Matched Tutors -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">🕒</div>
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">Timezone-Matched Online Tutors</h3>
        <p style="font-size: 0.85rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 14px;">
            Verified top educators in India available to teach in your evening hours across Gulf Standard Time (GST), Eastern Time (EST), Pacific Time (PST), and Greenwich Mean Time (GMT).
        </p>
        <span style="font-size: 0.78rem; font-weight: 700; color: var(--primary);">Flexible 1-on-1 Sessions</span>
    </div>

    <!-- 4. Study in India -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
        <div style="font-size: 2rem; margin-bottom: 12px;">🎓</div>
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); margin-bottom: 8px;">Study in India Admissions</h3>
        <p style="font-size: 0.85rem; color: var(--slate-body); line-height: 1.6; margin-bottom: 14px;">
            Comprehensive admission guidance for DASA (Direct Admission of Students Abroad), CIWG (Children of Indian Workers in Gulf), NRI quota in IITs, NITs, Medical Colleges, and Central Universities like DU and BHU.
        </p>
        <span style="font-size: 0.78rem; font-weight: 700; color: var(--primary);">Official Quota Guidelines</span>
    </div>
</div>

<!-- NRI Parent Enquiry Form -->
<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); max-width: 760px; margin: 0 auto;">
    <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px; text-align: center;">
        Request an NRI Tutoring Consultation
    </h3>
    <p style="font-size: 0.88rem; color: var(--slate-muted); text-align: center; margin-bottom: 24px;">
        Tell us your country, child's grade, and subjects needed. We will match you with a verified educator suited to your timezone.
    </p>

    <form action="enquiry-submit.php" method="POST" class="oedu-enquiry-form">
        <input type="hidden" name="target_type" value="educator">
        <input type="hidden" name="target_id" value="1">
        <input type="hidden" name="redirect_url" value="global.php">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Parent Name</label>
                <input type="text" name="sender_name" required placeholder="e.g. Ramesh Patel" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
            </div>
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Phone / WhatsApp with Country Code</label>
                <input type="tel" name="sender_phone" required placeholder="+971 50... or +1..." style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Country of Residence</label>
                <select name="city_or_pincode" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                    <option value="United Arab Emirates (UAE)">United Arab Emirates (UAE)</option>
                    <option value="United States (USA)">United States (USA)</option>
                    <option value="United Kingdom (UK)">United Kingdom (UK)</option>
                    <option value="Canada">Canada</option>
                    <option value="Australia">Australia</option>
                    <option value="Singapore">Singapore</option>
                    <option value="Saudi Arabia">Saudi Arabia / Qatar</option>
                </select>
            </div>
            <div>
                <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Enquiry Type</label>
                <select name="sender_type" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;">
                    <option value="parent">CBSE School Subject Tutoring</option>
                    <option value="parent">Indian Language Learning (Hindi / Regional)</option>
                    <option value="student">Study in India Admissions (DASA/CIWG)</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="font-size: 0.78rem; font-weight: 700; display: block; margin-bottom: 4px;">Learning Requirements & Preferred Timezone</label>
            <textarea name="message" rows="3" required placeholder="Looking for Class 8 Maths & Hindi tutor on UAE timezone (GMT+4)..." style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; font-family: inherit; font-size: 0.88rem;"></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 0.95rem;">
            Submit Global Request →
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
