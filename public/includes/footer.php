    </main>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="footer-container">
            <!-- Official Independent Disclaimer -->
            <div class="footer-disclaimer-card" id="child-safety">
                <strong style="color: #FFFFFF;">🇮🇳 National Higher Education Initiative:</strong>
                Oedu.in is an independent national directory and discovery platform for Universities and Colleges in India. Official links (such as UGC, AICTE, NIRF, NAAC, and NTA) are provided for student welfare, accreditation verification, and public reference. We protect student privacy: personal contact info is confidential and protected by parental consent safeguards.
            </div>

            <div class="footer-grid">
                <!-- Brand Info with Official Emblem -->
                <div class="footer-col">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                        <img src="logo.png" alt="Oedu.in" style="height:44px; width:44px; object-fit:contain; border-radius:10px; background:#FFFFFF; padding:3px; box-shadow: 0 4px 12px rgba(0,0,0,0.35);">
                        <div>
                            <h2 style="color:#FFF; font-size:1.35rem; font-weight:800; line-height:1.1; margin:0;">Oedu<span style="color:var(--orange);">.in</span></h2>
                            <div style="font-size:0.75rem; color:#FFA35B; font-family:var(--font-hindi); font-weight:600; margin-top:2px;">विद्या ददाति विनयम्</div>
                        </div>
                    </div>
                    <p style="margin-bottom:12px; color:#9CA3AF; font-size:0.85rem; line-height:1.6;">
                        <strong style="color:#FFFFFF;">One Education Platform for Every People.</strong> Discover and compare India's premier Universities, IITs, Central Universities, and Top Colleges.
                    </p>
                    <p style="font-size:0.8rem; color:#6B7280;">
                        SMS Notifications via DLT Sender ID: <strong style="color:#FFA35B;">OEDUIN</strong>
                    </p>
                </div>

                <!-- 1. Universities -->
                <div class="footer-col">
                    <h3>Universities</h3>
                    <ul>
                        <li><a href="institutions.php?subtype=central_university">Central Universities (DU, BHU, JNU)</a></li>
                        <li><a href="institutions.php?subtype=institute_of_national_importance">Institutes of National Importance</a></li>
                        <li><a href="institutions.php?subtype=state_university">State Universities (Pune, Jadavpur)</a></li>
                        <li><a href="institutions.php?type=university">Deemed Universities (IISc)</a></li>
                        <li><a href="institutions.php?type=university">Private Universities</a></li>
                    </ul>
                </div>

                <!-- 2. Colleges & Degrees -->
                <div class="footer-col">
                    <h3>Top Colleges</h3>
                    <ul>
                        <li><a href="institutions.php?type=college">NIRF #1 Colleges (Miranda House)</a></li>
                        <li><a href="institutions.php?type=college">Autonomous Colleges (Loyola)</a></li>
                        <li><a href="institutions.php?q=Medical">Medical Colleges (AIIMS)</a></li>
                        <li><a href="institutions.php?q=Engineering">Engineering Colleges (IITs)</a></li>
                        <li><a href="institutions.php?q=Law">Law Universities (NLSIU)</a></li>
                    </ul>
                </div>

                <!-- 3. Admissions & Exams -->
                <div class="footer-col">
                    <h3>Admissions & Cutoffs</h3>
                    <ul>
                        <li><a href="exams.php?category=University+Admission">CUET-UG University Calendar</a></li>
                        <li><a href="exams.php?category=Engineering+Entrance">JEE Main & Advanced</a></li>
                        <li><a href="exams.php?category=Medical+Entrance">NEET-UG Medical Admission</a></li>
                        <li><a href="scholarships.php">National Scholarships (NSP)</a></li>
                        <li><a href="government.php">UGC & AICTE Guidelines</a></li>
                    </ul>
                </div>

                <!-- 4. Special & Global -->
                <div class="footer-col">
                    <h3>Oedu Network</h3>
                    <ul>
                        <li><a href="institutions.php">List Your University or College</a></li>
                        <li><a href="global.php">NRI & Diaspora Admissions</a></li>
                        <li><a href="government.php">NEP 2020 Initiatives</a></li>
                        <li><a href="#child-safety">Student Privacy Guarantee</a></li>
                        <li><a href="index.php">One Platform for Every People</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    © <?= date('Y') ?> <strong style="color:#FFFFFF;">Oedu.in</strong> — One Education Platform for Every People. All rights reserved.
                </div>
                <div style="display: flex; gap: 16px;">
                    <a href="government.php">UGC Guidelines</a>
                    <a href="#child-safety">Trust & Safety</a>
                    <a href="index.php">Terms of Service</a>
                    <a href="index.php">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation (PWA Touch Bar) -->
    <nav class="mobile-bottom-nav" aria-label="Mobile Navigation">
        <a href="index.php" class="mobile-nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' && empty($_GET['type']) ? 'active' : '' ?>">
            <span class="mobile-nav-icon">🏠</span>
            <span class="mobile-nav-label">Home</span>
        </a>
        <a href="institutions.php?type=university" class="mobile-nav-item <?= ($_GET['type'] ?? '') == 'university' ? 'active' : '' ?>">
            <span class="mobile-nav-icon">🏛️</span>
            <span class="mobile-nav-label">Universities</span>
        </a>
        <a href="institutions.php?type=college" class="mobile-nav-item <?= ($_GET['type'] ?? '') == 'college' ? 'active' : '' ?>">
            <span class="mobile-nav-icon">🎓</span>
            <span class="mobile-nav-label">Colleges</span>
        </a>
        <a href="search.php" class="mobile-nav-item <?= basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : '' ?>">
            <span class="mobile-nav-icon">🔍</span>
            <span class="mobile-nav-label">Search</span>
        </a>
        <a href="index.php#counseling" class="mobile-nav-item">
            <span class="mobile-nav-icon">📋</span>
            <span class="mobile-nav-label">Enquire</span>
        </a>
    </nav>

    <!-- Pure Vanilla JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
