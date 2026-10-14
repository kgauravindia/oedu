# Online Education — National Education Platform of India

<p align="center">
  <img src="logo_full.png" alt="Online Education Logo" width="320">
</p>

> **विद्या ददाति विनयम्** — One Education Platform for Every People  
> 📘 **Official Facebook Page:** [facebook.com/oeduin](https://www.facebook.com/oeduin)

Comprehensive national directory of **72,800+** higher education institutions across all **36 States & UTs** and **780 Districts** of India, powered by official AISHE and `op_sdb` data.

---

## 🏛️ Directory Coverage

- **1,505+ Universities** — Central, State Public, Deemed, and Institutes of National Importance (IITs, IIMs, AIIMS).
- **54,351+ Affiliated Colleges** — Government, Private Aided, and Private Un-aided colleges with affiliating university linkage.
- **17,010+ Standalone Institutes** — Polytechnics, Nursing diploma colleges, Teacher Training institutes, Paramedical, and PGDM business schools.
- **36 States & 780 Districts** — Cascading location filter powered by `op_sdb`.
- **National Entrance Examinations** — CUET-UG/PG, JEE Main, NEET-UG, GATE, CAT cutoffs and dates.

---

## 🛠️ Tech Stack & Architecture

- **Backend:** PHP 8.3 with PDO MySQL (`utf8mb4`).
- **Frontend:** Pure HTML5, Semantic CSS3 (custom design system), and Vanilla JavaScript.
- **Database:** MySQL 8.x with indexed schemas for sub-second search and pagination.
- **SMS Gateway:** DLT-approved OTP dispatch via OfferPlant.

---

## 🚀 Setup & Local Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/kgauravindia/oedu.git
   cd oedu
   ```

2. **Configure Database:**
   Copy the example database config:
   ```bash
   cp includes/db.php.example includes/db.php
   ```
   Update `includes/db.php` with your local MySQL credentials (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).

3. **Serve Locally:**
   Run via PHP built-in server or Laragon:
   ```bash
   php -S localhost:8000
   ```
   Or open `http://onlineeducation.test` in Laragon.

---

## 📁 Project Structure

```
├── api/
│   └── districts.php         # Fast JSON endpoint for cascading district dropdowns
├── assets/
│   ├── css/style.css         # Online Education responsive design system
│   └── js/main.js            # Vanilla JS scripts (state/district cascading, language)
├── includes/
│   ├── db.php.example        # Database config template (credentials gitignored)
│   ├── header.php            # Global navigation ribbon & omnibox
│   ├── footer.php            # Footer links & national accreditation badges
│   └── sms.php               # DLT SMS OTP sender
├── index.php                 # National portal homepage with stats & 3-step finder
├── institution.php          # 1,505+ Universities directory
├── university-detail.php     # Detailed university profile & affiliated institutions
├── college.php              # 54,351+ Affiliated colleges directory
├── college-detail.php        # Detailed college profile & affiliating university info
├── institute.php            # 17,010+ Standalone institutes directory (Polytechnics, Nursing)
├── institute-detail.php      # Detailed standalone institute profile
├── exam.php                 # National entrance examinations & cutoffs
├── search.php                # Unified Omnibox search engine across all sectors
├── enquiry-submit.php        # Admission counseling lead submission
├── favicon.ico               # Brand favicon
└── robots.txt                # Search engine crawlers configuration
```

---

## 🔒 Security & Privacy

Database passwords, SQL dumps, and raw spreadsheets are strictly excluded via `.gitignore`. Never commit `includes/db.php` or `.env` files.
