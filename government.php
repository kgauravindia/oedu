<?php
// government.php — Government Education Directory & Initiatives (.php only)
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Government Education Directory — Ministry, NCERT, UGC, AICTE, NTA | Oedu.in';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 32px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--charcoal); margin-bottom: 6px;">
                🏛️ Government Education Directory & Initiatives
            </h2>
            <p style="color: var(--slate-muted); font-size: 0.95rem; max-width: 800px;">
                Central and State educational departments, national regulatory bodies, official curriculum portals, and student welfare schemes of India.
            </p>
        </div>
        <div class="official-badge" style="font-size: 0.85rem; padding: 6px 14px;">
            🇮🇳 Direct Gateway to Official Portals
        </div>
    </div>
</div>

<!-- Important Platform Distinction Notice -->
<div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--radius-md); padding: 16px 20px; margin-bottom: 32px; font-size: 0.88rem; color: #1E40AF; line-height: 1.6;">
    <strong>ℹ️ Independent Directory Notice:</strong>
    Oedu.in is an independent national education platform that connects learners with official government databases and portals for ease of discovery. We link directly to official government bodies (Ministry of Education, NCERT, UGC, NTA) to protect students from unverified intermediaries.
</div>

<!-- 1. Apex Union Government Bodies -->
<h3 style="font-size: 1.35rem; font-weight: 800; color: var(--charcoal); margin-bottom: 16px;">
    1. Union Government & Apex Educational Bodies
</h3>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal); margin-bottom: 6px;">Ministry of Education (MoE)</h4>
        <p style="font-size: 0.82rem; color: var(--slate-muted); line-height: 1.5; margin-bottom: 14px;">
            Formulates the National Policy on Education (NEP 2020), oversees Department of School Education & Literacy (DoSEL) and Department of Higher Education.
        </p>
        <a href="https://www.education.gov.in" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.8rem; width: 100%;">
            Official Portal (education.gov.in) ↗
        </a>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal); margin-bottom: 6px;">NCERT & ePathshala</h4>
        <p style="font-size: 0.82rem; color: var(--slate-muted); line-height: 1.5; margin-bottom: 14px;">
            National Council of Educational Research and Training. Publishes authorized textbooks, model questions, teacher training curricula, and digital learning assets.
        </p>
        <a href="https://ncert.nic.in" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.8rem; width: 100%;">
            Official Portal (ncert.nic.in) ↗
        </a>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal); margin-bottom: 6px;">University Grants Commission (UGC)</h4>
        <p style="font-size: 0.82rem; color: var(--slate-muted); line-height: 1.5; margin-bottom: 14px;">
            Maintains standards of higher education, determines university recognition, approves degrees, and administers UGC-NET fellowships across India.
        </p>
        <a href="https://www.ugc.gov.in" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.8rem; width: 100%;">
            Official Portal (ugc.gov.in) ↗
        </a>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal); margin-bottom: 6px;">National Testing Agency (NTA)</h4>
        <p style="font-size: 0.82rem; color: var(--slate-muted); line-height: 1.5; margin-bottom: 14px;">
            Conducts premier national entrance examinations including JEE (Main), NEET-UG, CUET (UG/PG), UGC-NET, and CMAT transparently.
        </p>
        <a href="https://nta.ac.in" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.8rem; width: 100%;">
            Official Portal (nta.ac.in) ↗
        </a>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal); margin-bottom: 6px;">AICTE</h4>
        <p style="font-size: 0.82rem; color: var(--slate-muted); line-height: 1.5; margin-bottom: 14px;">
            All India Council for Technical Education. Regulates technical, engineering, management, and pharmacy institutions and schemes like Pragati Scholarship.
        </p>
        <a href="https://www.aicte-india.org" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.8rem; width: 100%;">
            Official Portal (aicte-india.org) ↗
        </a>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal); margin-bottom: 6px;">NIOS (Open Schooling)</h4>
        <p style="font-size: 0.82rem; color: var(--slate-muted); line-height: 1.5; margin-bottom: 14px;">
            National Institute of Open Schooling. Provides open and flexible basic, secondary, senior secondary, and vocational education for all.
        </p>
        <a href="https://nios.ac.in" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.8rem; width: 100%;">
            Official Portal (nios.ac.in) ↗
        </a>
    </div>
</div>

<!-- 2. Flagship Digital Education Initiatives -->
<h3 style="font-size: 1.35rem; font-weight: 800; color: var(--charcoal); margin-bottom: 16px;">
    2. Digital Bharat Educational Platforms
</h3>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 40px;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px;">
        <h5 style="font-size: 1rem; font-weight: 700; color: var(--charcoal); margin-bottom: 4px;">DIKSHA Portal</h5>
        <p style="font-size: 0.8rem; color: var(--slate-muted); margin-bottom: 12px;">Digital Infrastructure for Knowledge Sharing with interactive QR-coded textbooks.</p>
        <a href="https://diksha.gov.in" target="_blank" class="btn btn-outline" style="font-size: 0.78rem;">Visit DIKSHA ↗</a>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px;">
        <h5 style="font-size: 1rem; font-weight: 700; color: var(--charcoal); margin-bottom: 4px;">SWAYAM Online Courses</h5>
        <p style="font-size: 0.8rem; color: var(--slate-muted); margin-bottom: 12px;">Free online degree courses developed by faculty from IITs, IIMs, and Central Universities.</p>
        <a href="https://swayam.gov.in" target="_blank" class="btn btn-outline" style="font-size: 0.78rem;">Visit SWAYAM ↗</a>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px;">
        <h5 style="font-size: 1rem; font-weight: 700; color: var(--charcoal); margin-bottom: 4px;">National Scholarship Portal (NSP)</h5>
        <p style="font-size: 0.8rem; color: var(--slate-muted); margin-bottom: 12px;">Single portal for central and state government scholarships and direct benefit transfer (DBT).</p>
        <a href="https://scholarships.gov.in" target="_blank" class="btn btn-outline" style="font-size: 0.78rem;">Visit NSP ↗</a>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px;">
        <h5 style="font-size: 1rem; font-weight: 700; color: var(--charcoal); margin-bottom: 4px;">PM e-VIDYA</h5>
        <p style="font-size: 0.8rem; color: var(--slate-muted); margin-bottom: 12px;">Multi-mode access to education via dedicated 200+ DTH TV educational channels across India.</p>
        <a href="https://pmevidya.education.gov.in" target="_blank" class="btn btn-outline" style="font-size: 0.78rem;">Visit PM e-VIDYA ↗</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
