// assets/js/main.js — Online Education Interactive Scripts (Vanilla JavaScript)
// Powered by op_sdb table for Indian States & Districts

document.addEventListener('DOMContentLoaded', function () {
    // 1. Language Toggle (English <-> हिन्दी)
    const langEnBtn = document.getElementById('lang-en-btn');
    const langHiBtn = document.getElementById('lang-hi-btn');

    let currentLang = localStorage.getItem('oedu_lang') || 'en';

    function setLanguage(lang) {
        currentLang = lang;
        localStorage.setItem('oedu_lang', lang);

        if (lang === 'hi') {
            document.body.classList.add('lang-hindi-mode');
            if (langHiBtn) langHiBtn.style.color = '#FFA35B';
            if (langEnBtn) langEnBtn.style.color = '#9CA3AF';
        } else {
            document.body.classList.remove('lang-hindi-mode');
            if (langEnBtn) langEnBtn.style.color = '#FFFFFF';
            if (langHiBtn) langHiBtn.style.color = '#FFA35B';
        }
    }

    if (langEnBtn) langEnBtn.addEventListener('click', () => setLanguage('en'));
    if (langHiBtn) langHiBtn.addEventListener('click', () => setLanguage('hi'));
    setLanguage(currentLang);

    // 2. Cascading State -> District Selector (using op_sdb database)
    const stateSelects = document.querySelectorAll('.state-select-input');
    stateSelects.forEach(stateSelect => {
        const targetDistrictId = stateSelect.getAttribute('data-district-target') || 'district-select';
        const districtSelect = document.getElementById(targetDistrictId);

        if (!districtSelect) return;

        stateSelect.addEventListener('change', function () {
            const selectedState = this.value.trim();
            if (!selectedState) {
                districtSelect.innerHTML = '<option value="">All Districts</option>';
                districtSelect.disabled = false;
                return;
            }

            districtSelect.innerHTML = '<option value="">Loading districts from op_sdb...</option>';
            districtSelect.disabled = true;

            fetch('api/districts.php?state=' + encodeURIComponent(selectedState))
                .then(response => response.json())
                .then(districts => {
                    let options = '<option value="">All Districts (' + districts.length + ' in ' + selectedState + ')</option>';
                    districts.forEach(d => {
                        options += '<option value="' + d.replace(/"/g, '&quot;') + '">' + d + '</option>';
                    });
                    districtSelect.innerHTML = options;
                    districtSelect.disabled = false;
                })
                .catch(err => {
                    console.error('Failed to load districts from op_sdb:', err);
                    districtSelect.innerHTML = '<option value="">All Districts</option>';
                    districtSelect.disabled = false;
                });
        });
    });

    // 3. Enquiry Form Submission Feedback
    const enquiryForms = document.querySelectorAll('.oedu-enquiry-form');
    enquiryForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Sending via OEDUIN OTP... ⏳';
            }
        });
    });

    // 4. Auto-Dismiss Flash Alerts
    const flashAlerts = document.querySelectorAll('.flash-alert');
    flashAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 6000);
    });
});
