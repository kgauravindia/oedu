<?php
// syllabus.php — National Syllabus & Curriculum Directory (.php only)
// Official syllabus for CBSE (Class 9-12), Entrance Exams (CUET, JEE, NEET), Universities (UGC/AICTE), & State Boards
// Aligned with NEP 2020 & National Curriculum Framework (NCF)
require_once __DIR__ . '/includes/db.php';

// Filter parameters
$selectedCategory = trim($_GET['category'] ?? 'all');
$selectedStream = trim($_GET['stream'] ?? '');
$searchKeyword = trim($_GET['q'] ?? '');

// Master Syllabus Dataset (Curated directly from CBSE Academic, NTA, UGC, AICTE, and UPSC)
$curriculumData = [
    // --- 1. CBSE Class 10 (Secondary) ---
    [
        'id' => 'cbse-10-math',
        'category' => 'cbse_10',
        'category_name' => 'CBSE Class 10',
        'subject' => 'Mathematics (Standard & Basic)',
        'code' => '041 / 241',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'General',
        'academic_year' => '2026–2027',
        'scheme' => '80 Theory + 20 Internal Assessment',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Comprehensive core syllabus encompassing Real Numbers, Polynomials, Linear Equations, Quadratic Equations, Arithmetic Progressions, Coordinate Geometry, Triangles, Circles, Trigonometry, Mensuration, Statistics, and Probability.',
        'units' => [
            ['name' => 'Unit I: Number Systems (Real Numbers)', 'marks' => '6'],
            ['name' => 'Unit II: Algebra (Polynomials, Linear & Quadratic Eq, AP)', 'marks' => '20'],
            ['name' => 'Unit III: Coordinate Geometry', 'marks' => '6'],
            ['name' => 'Unit IV: Geometry (Triangles & Circles)', 'marks' => '15'],
            ['name' => 'Unit V: Trigonometry & Heights and Distances', 'marks' => '12'],
            ['name' => 'Unit VI: Mensuration (Areas Related to Circles, Surface Areas)', 'marks' => '10'],
            ['name' => 'Unit VII: Statistics & Probability', 'marks' => '11'],
        ],
        'highlights' => ['Basic & Standard level choice for students', 'Internal assessment includes Pen-Paper test, Portfolio, and Lab Activities', 'Rationalized NCERT aligned']
    ],
    [
        'id' => 'cbse-10-science',
        'category' => 'cbse_10',
        'category_name' => 'CBSE Class 10',
        'subject' => 'Science (Physics, Chemistry & Biology)',
        'code' => '086',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'General',
        'academic_year' => '2026–2027',
        'scheme' => '80 Theory + 20 Internal Assessment / Practical',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Core scientific concepts covering Chemical Reactions, Acids, Bases & Salts, Metals & Non-metals, Carbon Compounds, Life Processes, Control & Coordination, Reproduction, Heredity, Light, Electricity, Magnetic Effects, and Our Environment.',
        'units' => [
            ['name' => 'Unit I: Chemical Substances - Nature and Behaviour', 'marks' => '25'],
            ['name' => 'Unit II: World of Living (Life Processes, Control, Reproduction, Heredity)', 'marks' => '25'],
            ['name' => 'Unit III: Natural Phenomena (Light - Reflection & Refraction, Human Eye)', 'marks' => '12'],
            ['name' => 'Unit IV: Effects of Current (Electricity, Magnetic Effects)', 'marks' => '13'],
            ['name' => 'Unit V: Natural Resources (Our Environment)', 'marks' => '5'],
        ],
        'highlights' => ['Hands-on laboratory practical experiments', 'Case-based competency assertion-reasoning questions', 'Focus on green energy & sustainability']
    ],
    [
        'id' => 'cbse-10-social',
        'category' => 'cbse_10',
        'category_name' => 'CBSE Class 10',
        'subject' => 'Social Science',
        'code' => '087',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'General',
        'academic_year' => '2026–2027',
        'scheme' => '80 Theory + 20 Internal Assessment (Project & Maps)',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Integrated study of History (India and Contemporary World II), Geography (Contemporary India II), Political Science (Democratic Politics II), and Economics (Understanding Economic Development).',
        'units' => [
            ['name' => 'History: Rise of Nationalism in Europe & India, Global World, Print Culture', 'marks' => '20'],
            ['name' => 'Geography: Resources, Agriculture, Minerals, Manufacturing, Lifelines of Economy', 'marks' => '20'],
            ['name' => 'Political Science: Power Sharing, Federalism, Gender/Religion/Caste, Political Parties', 'marks' => '20'],
            ['name' => 'Economics: Development, Sectors of Economy, Money & Credit, Globalisation', 'marks' => '20'],
        ],
        'highlights' => ['Prescribed Map Work: 5 marks included', 'Interdisciplinary project on Consumer Rights / Social Issues', 'Critical thinking on Indian democratic institutions']
    ],
    [
        'id' => 'cbse-10-english',
        'category' => 'cbse_10',
        'category_name' => 'CBSE Class 10',
        'subject' => 'English Language & Literature',
        'code' => '184',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'General',
        'academic_year' => '2026–2027',
        'scheme' => '80 Theory + 20 Assessment of Listening & Speaking (ALS)',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Core communicative proficiency including unseen passages, analytical writing, formal letters, grammar rules (tenses, modals, determiners, reported speech), and literature (First Flight & Footprints Without Feet).',
        'units' => [
            ['name' => 'Section A: Reading Skills (Discursive & Case-based Passages)', 'marks' => '20'],
            ['name' => 'Section B: Writing Skills and Grammar (Formal Letter, Analytical Paragraph)', 'marks' => '20'],
            ['name' => 'Section C: Language through Literature (First Flight & Footprints without Feet)', 'marks' => '40'],
        ],
        'highlights' => ['Listening and Speaking Skills assessment (ALS) 20 marks', 'Emphasis on vocabulary and context analysis']
    ],

    // --- 2. CBSE Class 12 (Senior Secondary - Science, Commerce, Arts) ---
    [
        'id' => 'cbse-12-physics',
        'category' => 'cbse_12',
        'category_name' => 'CBSE Class 12',
        'subject' => 'Physics',
        'code' => '042',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'Science',
        'academic_year' => '2026–2027',
        'scheme' => '70 Theory + 30 Practical & Viva',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Rigorous conceptual physics covering Electrostatics, Current Electricity, Magnetism & Matter, Electromagnetic Induction, AC Currents, Optics (Wave & Ray), Dual Nature of Radiation, Atoms & Nuclei, and Semiconductor Electronics.',
        'units' => [
            ['name' => 'Unit I & II: Electrostatics & Current Electricity', 'marks' => '16'],
            ['name' => 'Unit III & IV: Magnetic Effects, Magnetism, EMI & AC', 'marks' => '17'],
            ['name' => 'Unit V & VI: Electromagnetic Waves & Optics (Ray and Wave Optics)', 'marks' => '18'],
            ['name' => 'Unit VII & VIII: Dual Nature of Radiation/Matter & Atoms and Nuclei', 'marks' => '12'],
            ['name' => 'Unit IX: Electronic Devices (Semiconductor Diodes & Circuits)', 'marks' => '7'],
        ],
        'highlights' => ['Direct alignment with JEE Main & NEET syllabus', 'Practical lab examination: Two experiments + Viva Voce + Project (30 Marks)']
    ],
    [
        'id' => 'cbse-12-chemistry',
        'category' => 'cbse_12',
        'category_name' => 'CBSE Class 12',
        'subject' => 'Chemistry',
        'code' => '043',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'Science',
        'academic_year' => '2026–2027',
        'scheme' => '70 Theory + 30 Practical Examination',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Physical, Inorganic, and Organic Chemistry covering Solutions, Electrochemistry, Chemical Kinetics, d and f Block Elements, Coordination Compounds, Haloalkanes, Alcohols, Phenols, Ethers, Aldehydes, Ketones, Carboxylic Acids, Amines, and Biomolecules.',
        'units' => [
            ['name' => 'Solutions, Electrochemistry & Chemical Kinetics (Physical)', 'marks' => '21'],
            ['name' => 'd- and f-Block Elements & Coordination Compounds (Inorganic)', 'marks' => '14'],
            ['name' => 'Haloalkanes, Alcohols, Aldehydes, Ketones, Carboxylic Acids (Organic)', 'marks' => '28'],
            ['name' => 'Biomolecules (Proteins, Nucleic Acids, Carbohydrates)', 'marks' => '7'],
        ],
        'highlights' => ['Salt Analysis, Volumetric Titration, and Project File in Practicals', 'High relevance for NEET-UG and JEE Advanced']
    ],
    [
        'id' => 'cbse-12-math',
        'category' => 'cbse_12',
        'category_name' => 'CBSE Class 12',
        'subject' => 'Mathematics',
        'code' => '041',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'Science / Commerce',
        'academic_year' => '2026–2027',
        'scheme' => '80 Theory + 20 Internal Assessment',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Advanced mathematics encompassing Relations and Functions, Inverse Trigonometric Functions, Matrices, Determinants, Continuity and Differentiability, Applications of Derivatives, Integrals, Differential Equations, Vector Algebra, 3D Geometry, Linear Programming, and Probability.',
        'units' => [
            ['name' => 'Unit I: Relations and Functions', 'marks' => '8'],
            ['name' => 'Unit II: Algebra (Matrices and Determinants)', 'marks' => '10'],
            ['name' => 'Unit III: Calculus (Continuity, Differentiation, Integrals, Differential Equations)', 'marks' => '35'],
            ['name' => 'Unit IV: Vectors and Three-Dimensional Geometry', 'marks' => '14'],
            ['name' => 'Unit V: Linear Programming', 'marks' => '5'],
            ['name' => 'Unit VI: Probability', 'marks' => '8'],
        ],
        'highlights' => ['Calculus constitutes 35 out of 80 marks', 'Crucial base for CUET Mathematics domain and JEE Main']
    ],
    [
        'id' => 'cbse-12-biology',
        'category' => 'cbse_12',
        'category_name' => 'CBSE Class 12',
        'subject' => 'Biology',
        'code' => '044',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'Science',
        'academic_year' => '2026–2027',
        'scheme' => '70 Theory + 30 Practical',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Covers Sexual Reproduction in Flowering Plants, Human Reproduction, Reproductive Health, Principles of Inheritance & Variation, Molecular Basis of Inheritance, Evolution, Human Health & Disease, Microbes, Biotechnology Principles & Applications, Organisms & Populations, Ecosystem, and Biodiversity Conservation.',
        'units' => [
            ['name' => 'Unit VI: Reproduction in Plants and Humans', 'marks' => '16'],
            ['name' => 'Unit VII: Genetics and Evolution', 'marks' => '20'],
            ['name' => 'Unit VIII: Biology and Human Welfare', 'marks' => '12'],
            ['name' => 'Unit IX: Biotechnology and Its Applications', 'marks' => '12'],
            ['name' => 'Unit X: Ecology and Environment', 'marks' => '10'],
        ],
        'highlights' => ['100% identical syllabus with NEET-UG Botany & Zoology', 'Spotting, slide preparation, and investigative project in lab']
    ],
    [
        'id' => 'cbse-12-accountancy',
        'category' => 'cbse_12',
        'category_name' => 'CBSE Class 12',
        'subject' => 'Accountancy',
        'code' => '055',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'Commerce',
        'academic_year' => '2026–2027',
        'scheme' => '80 Theory + 20 Project Work & Viva',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Corporate and partnership accounting: Partnership Deed, Goodwill, Admission, Retirement, Dissolution; Accounting for Share Capital, Debentures; Analysis of Financial Statements, Accounting Ratios, and Cash Flow Statement.',
        'units' => [
            ['name' => 'Part A: Accounting for Partnership Firms and Companies', 'marks' => '60'],
            ['name' => 'Part B: Financial Statement Analysis & Cash Flow Statement', 'marks' => '20'],
            ['name' => 'Part C: Comprehensive Project Work & Viva Voce', 'marks' => '20'],
        ],
        'highlights' => ['Comprehensive real-world company financial statement analysis', 'Foundation for CA Foundation and CUET B.Com / BBA entrance']
    ],
    [
        'id' => 'cbse-12-economics',
        'category' => 'cbse_12',
        'category_name' => 'CBSE Class 12',
        'subject' => 'Economics',
        'code' => '030',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'Commerce / Arts',
        'academic_year' => '2026–2027',
        'scheme' => '80 Theory + 20 Project Work',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'Structured two-part curriculum: Part A covers Introductory Macroeconomics (National Income, Money & Banking, Determination of Income and Employment, Government Budget, Balance of Payments); Part B covers Indian Economic Development (1947–1990 reforms, current challenges, comparative development with China & Pakistan).',
        'units' => [
            ['name' => 'Part A: Introductory Macroeconomics', 'marks' => '40'],
            ['name' => 'Part B: Indian Economic Development (Post-Independence to Current)', 'marks' => '40'],
            ['name' => 'Part C: Project Work & Viva Voce', 'marks' => '20'],
        ],
        'highlights' => ['Critical analysis of contemporary Indian fiscal & monetary policies', 'CUET Domain Economics alignment']
    ],
    [
        'id' => 'cbse-12-polscience',
        'category' => 'cbse_12',
        'category_name' => 'CBSE Class 12',
        'subject' => 'Political Science',
        'code' => '028',
        'board' => 'CBSE (New Delhi)',
        'stream' => 'Humanities / Arts',
        'academic_year' => '2026–2027',
        'scheme' => '80 Theory + 20 Project Work',
        'official_url' => 'https://cbseacademic.nic.in/curriculum_2025.html',
        'summary' => 'In-depth political analysis divided into Contemporary World Politics (End of Bipolarity, Contemporary Centres of Power, South Asia, International Organisations, Security, Environment, Globalisation) and Politics in India Since Independence (Challenges of Nation-Building, Planned Development, India\'s Foreign Policy, Crisis of Democratic Order, Regional Aspirations, Recent Developments in Indian Politics).',
        'units' => [
            ['name' => 'Part A: Contemporary World Politics', 'marks' => '40'],
            ['name' => 'Part B: Politics in India Since Independence', 'marks' => '40'],
            ['name' => 'Part C: Project Work & Viva', 'marks' => '20'],
        ],
        'highlights' => ['Essential for UPSC Civil Services Foundation & CUET BA Political Science', 'Covers multilateral diplomacy (G20, UN, BRICS, Quad)']
    ],

    // --- 3. National Entrance Exams (CUET, JEE, NEET, UPSC, GATE, CAT) ---
    [
        'id' => 'entrance-cuet-ug',
        'category' => 'entrance',
        'category_name' => 'National Entrance Exams',
        'subject' => 'CUET-UG (Common University Entrance Test)',
        'code' => 'NTA-CUET-UG',
        'board' => 'National Testing Agency (NTA) & UGC',
        'stream' => 'All Streams (Central & State Universities)',
        'academic_year' => '2026–2027',
        'scheme' => 'Computer Based Test (CBT) • Hybrid Mode • MCQs',
        'official_url' => 'https://cuetug.ntaonline.in',
        'summary' => 'Mandatory national entrance for undergraduate admissions to all 45+ Central Universities (DU, BHU, JNU, Jamia, AMU) and 250+ participating State and Private Universities. Based entirely on NCERT Class 12 syllabus across 29 domain subjects + Language + General Test.',
        'units' => [
            ['name' => 'Section IA & IB: 33 Languages (Reading Comprehension, Vocabulary, Synonyms/Antonyms)', 'marks' => '50 Qs (Attempt 40)'],
            ['name' => 'Section II: 29 Domain Subjects (Strictly NCERT Class 12 Syllabus Only)', 'marks' => '50 Qs (Attempt 40)'],
            ['name' => 'Section III: General Test (GK, Current Affairs, Numerical Ability, Reasoning, Quantitative)', 'marks' => '60 Qs (Attempt 50)'],
        ],
        'highlights' => ['Marking: +5 for correct answer, -1 for incorrect answer', 'Single window admission for over 3 lakh Central University seats', 'Available in 13 Indian languages']
    ],
    [
        'id' => 'entrance-jee-main',
        'category' => 'entrance',
        'category_name' => 'National Entrance Exams',
        'subject' => 'JEE Main & Advanced (Engineering)',
        'code' => 'NTA-JEE',
        'board' => 'National Testing Agency (NTA) & Joint Admission Board (JAB)',
        'stream' => 'Engineering / Architecture (IITs, NITs, IIITs, GFTIs)',
        'academic_year' => '2026–2027',
        'scheme' => '300 Marks (Paper 1: B.E/B.Tech) • 75 Questions',
        'official_url' => 'https://jeemain.nta.nic.in',
        'summary' => 'Premier entrance examination for admissions to National Institutes of Technology (NITs), IIITs, and qualifying benchmark for JEE Advanced (Indian Institutes of Technology — IITs). Covers Physics, Chemistry, and Mathematics from Class 11 and 12.',
        'units' => [
            ['name' => 'Mathematics: Calculus, Coordinate Geometry, Matrices, Vectors & 3D, Probability, Complex Numbers', 'marks' => '100 (25 Qs)'],
            ['name' => 'Physics: Mechanics, Thermodynamics, Electrodynamics, Optics, Modern Physics, Oscillations', 'marks' => '100 (25 Qs)'],
            ['name' => 'Chemistry: Physical (Chemical Kinetics, Thermodynamics), Inorganic (Coordination, p/d-block), Organic (Reactions & Mechanisms)', 'marks' => '100 (25 Qs)'],
        ],
        'highlights' => ['Conducted in two sessions (Session 1 January & Session 2 April)', 'Each subject: 20 Section A (MCQs) + 5 Section B (Numerical value questions)', 'Negative marking applies to both MCQs and Numerical questions (-1)']
    ],
    [
        'id' => 'entrance-neet-ug',
        'category' => 'entrance',
        'category_name' => 'National Entrance Exams',
        'subject' => 'NEET-UG (National Eligibility cum Entrance Test)',
        'code' => 'NTA-NEET',
        'board' => 'National Testing Agency (NTA) & National Medical Commission (NMC)',
        'stream' => 'Medical & Allied Health (MBBS, BDS, BAMS, BHMS, Nursing)',
        'academic_year' => '2026–2027',
        'scheme' => '720 Marks • Pen & Paper (OMR) • 180 Questions (out of 200)',
        'official_url' => 'https://exams.nta.ac.in/NEET/',
        'summary' => 'Single nationwide entrance examination for all medical admissions across India including AIIMS New Delhi, JIPMER, Central Universities, State Government Medical Colleges, and Armed Forces Medical College (AFMC). Core subjects: Physics, Chemistry, and Biology (Botany & Zoology).',
        'units' => [
            ['name' => 'Physics: Class 11 & Class 12 Core NMC Medical Physics Syllabus', 'marks' => '180 Marks (45 Qs)'],
            ['name' => 'Chemistry: Physical, Organic, and Inorganic Chemistry as rationalized by NMC', 'marks' => '180 Marks (45 Qs)'],
            ['name' => 'Biology (Botany): Plant Physiology, Cell Biology, Genetics, Ecology, Reproduction', 'marks' => '180 Marks (45 Qs)'],
            ['name' => 'Biology (Zoology): Human Physiology, Diversity of Living Organisms, Biotechnology, Health', 'marks' => '180 Marks (45 Qs)'],
        ],
        'highlights' => ['Total 720 Marks with +4 for correct and -1 for wrong', 'Duration: 3 hours 20 minutes', 'Available in 13 languages including Hindi, English, Tamil, Telugu, Gujarati, Bengali']
    ],
    [
        'id' => 'entrance-upsc-cse',
        'category' => 'entrance',
        'category_name' => 'National Entrance Exams',
        'subject' => 'UPSC Civil Services Examination (IAS / IPS / IFS)',
        'code' => 'UPSC-CSE',
        'board' => 'Union Public Service Commission (UPSC, New Delhi)',
        'stream' => 'Civil Services & National Administration',
        'academic_year' => '2026–2027',
        'scheme' => 'Prelims (400 Marks) + Mains (1750 Marks) + Personality Test (275 Marks)',
        'official_url' => 'https://upsc.gov.in',
        'summary' => 'Premier civil service recruitment examination of India. Prelims features GS Paper I (General Studies) and GS Paper II (CSAT). Mains features 9 written descriptive papers: Essay, General Studies I, II, III, IV, Compulsory Languages, and two Optional Subject papers.',
        'units' => [
            ['name' => 'Prelims Paper I: Indian Polity, Modern History, Geography, Economy, Environment, Current Affairs', 'marks' => '200 Marks (100 Qs)'],
            ['name' => 'Prelims Paper II (CSAT): Reading Comprehension, Logical Reasoning, Basic Numeracy (Class 10)', 'marks' => '200 Marks (Qualifying 33%)'],
            ['name' => 'Mains GS I: Indian Heritage & Culture, History & Geography of the World and Society', 'marks' => '250 Marks'],
            ['name' => 'Mains GS II: Governance, Constitution, Polity, Social Justice and International Relations', 'marks' => '250 Marks'],
            ['name' => 'Mains GS III: Technology, Economic Development, Bio-diversity, Environment, Security & Disaster', 'marks' => '250 Marks'],
            ['name' => 'Mains GS IV: Ethics, Integrity, and Aptitude (Case Studies)', 'marks' => '250 Marks'],
            ['name' => 'Mains Essay & Optional Subject Papers I & II', 'marks' => '750 Marks'],
        ],
        'highlights' => ['One of the most competitive exams globally', 'Strict qualifying criterion on CSAT Paper II', 'Negative marking of 1/3rd in Prelims']
    ],
    [
        'id' => 'entrance-gate',
        'category' => 'entrance',
        'category_name' => 'National Entrance Exams',
        'subject' => 'GATE (Graduate Aptitude Test in Engineering)',
        'code' => 'GATE-IIT',
        'board' => 'IISc Bangalore & IIT Consortium (MoE)',
        'stream' => 'Postgraduate Engineering (M.Tech / Ph.D.) & PSU Recruitment',
        'academic_year' => '2026–2027',
        'scheme' => '100 Marks • Computer Based Test (CBT) • 65 Questions',
        'official_url' => 'https://gate.iitk.ac.in',
        'summary' => 'National examination testing comprehensive understanding of undergraduate engineering, technology, architecture, science, and humanities disciplines. Direct gateway for M.Tech/Ph.D. at IITs, IISc, and executive recruitment in Maharatna/Navratna PSUs (ONGC, IOCL, NTPC, BHEL, DRDO).',
        'units' => [
            ['name' => 'General Aptitude (Verbal Ability & Numerical Ability)', 'marks' => '15 Marks (10 Qs)'],
            ['name' => 'Engineering Mathematics (Linear Algebra, Calculus, Differential Eq, Probability)', 'marks' => '13 Marks'],
            ['name' => 'Core Discipline Subject Matter (CS, EC, EE, ME, CE, CH, etc.)', 'marks' => '72 Marks (55 Qs)'],
        ],
        'highlights' => ['Valid score for 3 years for postgraduate admissions', 'Direct recruitment into Maharatna Public Sector Undertakings', 'Mix of MCQs, MSQs (Multiple Select), and NATs (Numerical Answer Type)']
    ],
    [
        'id' => 'entrance-cat',
        'category' => 'entrance',
        'category_name' => 'National Entrance Exams',
        'subject' => 'CAT (Common Admission Test for IIMs)',
        'code' => 'IIM-CAT',
        'board' => 'Indian Institutes of Management (IIMs)',
        'stream' => 'Management & Business Administration (MBA / PGDM)',
        'academic_year' => '2026–2027',
        'scheme' => 'Computer Based Test • 2 Hours (40 min per section) • 66 Questions',
        'official_url' => 'https://iimcat.ac.in',
        'summary' => 'Premier national entrance test for admissions to 21 Indian Institutes of Management (IIM Ahmedabad, IIM Bangalore, IIM Calcutta, IIM Lucknow, etc.) and over 1,200 premier business schools in India (FMS Delhi, SPJIMR, MDI Gurgaon, IIT DoMS).',
        'units' => [
            ['name' => 'Section I: Verbal Ability & Reading Comprehension (VARC)', 'marks' => '24 Qs (40 Minutes)'],
            ['name' => 'Section II: Data Interpretation & Logical Reasoning (DILR)', 'marks' => '20 Qs (40 Minutes)'],
            ['name' => 'Section III: Quantitative Aptitude (Arithmetic, Algebra, Geometry, Numbers)', 'marks' => '22 Qs (40 Minutes)'],
        ],
        'highlights' => ['Sectional time limits strictly enforced', 'Non-MCQ (Type In The Answer / TITA) questions without negative marking', 'Percentile score accepted nationwide']
    ],

    // --- 4. University Higher Education Frameworks (UGC LOCF / NEP 2020) ---
    [
        'id' => 'ugc-fyup-framework',
        'category' => 'university',
        'category_name' => 'University Higher Education',
        'subject' => 'UGC FYUP (Four Year Undergraduate Curriculum Framework)',
        'code' => 'UGC-NEP-2020',
        'board' => 'University Grants Commission (UGC, New Delhi)',
        'stream' => 'All Universities (B.A., B.Sc., B.Com., BCA, BBA)',
        'academic_year' => '2026–2027',
        'scheme' => 'Choice-Based Credit System (CBCS) • 160 Credits for 4-Year Honours with Research',
        'official_url' => 'https://www.ugc.gov.in',
        'summary' => 'National holistic and flexible curriculum framework notified by the UGC under National Education Policy 2020. Features multiple entry and exit points with Academic Bank of Credits (ABC) integration across Central, State, Deemed, and Private Universities.',
        'units' => [
            ['name' => 'Year 1 (Certificate): Core Major Basics + Minor + Skill Enhancement Courses (SEC)', 'marks' => '40 Credits (Exit Option: UG Certificate)'],
            ['name' => 'Year 2 (Diploma): Major Advanced + Multidisciplinary + Value Added Courses (VAC)', 'marks' => '80 Credits (Exit Option: UG Diploma)'],
            ['name' => 'Year 3 (Degree): In-depth Major Core + Professional Electives + Internship', 'marks' => '120 Credits (Exit Option: Bachelor Degree)'],
            ['name' => 'Year 4 (Honours/Research): Advanced Research Methodology + Dissertation / Thesis', 'marks' => '160 Credits (Degree: Bachelor with Honours/Research)'],
        ],
        'highlights' => ['Multiple entry and exit points (Certificate, Diploma, Degree, Honours with Research)', 'Direct eligibility to 1-Year Master\'s or Ph.D. after 4-Year UG degree', 'Academic Bank of Credits (ABC) credit transfer nationwide']
    ],
    [
        'id' => 'aicte-model-engineering',
        'category' => 'university',
        'category_name' => 'University Higher Education',
        'subject' => 'AICTE Model Curriculum for B.Tech / BE Engineering',
        'code' => 'AICTE-MODEL-BTECH',
        'board' => 'All India Council for Technical Education (AICTE)',
        'stream' => 'Engineering & Technology (Computer Science, Mechanical, Civil, Electrical, AI/ML)',
        'academic_year' => '2026–2027',
        'scheme' => '160 Total Credits for 4-Year B.Tech Degree',
        'official_url' => 'https://www.aicte-india.org',
        'summary' => 'National model syllabus developed by AICTE with industry experts for all affiliated engineering colleges and technological universities in India (AKTU, VTU, Anna University, JNTU, CSVTU, BPUT, RTU, etc.).',
        'units' => [
            ['name' => 'Humanities & Social Sciences (English, Professional Ethics, Constitution of India)', 'marks' => '12 Credits'],
            ['name' => 'Basic Science Courses (Engineering Physics, Chemistry, Calculus & Linear Algebra)', 'marks' => '25 Credits'],
            ['name' => 'Engineering Science Courses (Programming, CAD/Graphics, Basic Electrical & Electronics)', 'marks' => '24 Credits'],
            ['name' => 'Professional Core Courses (PCC - Branch specific core discipline subjects)', 'marks' => '48 Credits'],
            ['name' => 'Professional Electives (PEC - Advanced specialisations e.g. Cloud, Robotics, IoT)', 'marks' => '18 Credits'],
            ['name' => 'Open Electives (OEC) & Mandatory Non-Credit Induction / Internships / Capstone Project', 'marks' => '33 Credits'],
        ],
        'highlights' => ['Mandatory 3-week student induction program', 'Mandatory 6-month industry internship / capstone design project', 'Inclusion of emerging technologies: AI, IoT, Cybersecurity, Data Analytics, Quantum Computing']
    ],

    // --- 5. State Boards & Open Schooling ---
    [
        'id' => 'state-upmsp-inter',
        'category' => 'state_board',
        'category_name' => 'State Boards & NIOS',
        'subject' => 'UP Board (UPMSP) Class 10 & 12 Curriculum',
        'code' => 'UPMSP-NCERT',
        'board' => 'Uttar Pradesh Madhyamik Shiksha Parishad (Prayagraj)',
        'stream' => 'Secondary & Intermediate (Science, Commerce, Arts)',
        'academic_year' => '2026–2027',
        'scheme' => '70 Theory + 30 Project/Practical for Inter; 100 Marks per subject',
        'official_url' => 'https://upmsp.edu.in',
        'summary' => 'Official curriculum of India\'s largest state education board (over 55 lakh candidates annually). Fully aligned with NCERT rationalized textbooks in Hindi and English medium for High School (Matric) and Intermediate.',
        'units' => [
            ['name' => 'High School (Class 10): Hindi, English, Mathematics, Science, Social Science, Sanskrit/Computer', 'marks' => '6 Subjects • 600 Marks'],
            ['name' => 'Intermediate (Class 12 Science): General Hindi, English, Physics, Chemistry, Mathematics / Biology', 'marks' => '5 Subjects • 500 Marks'],
            ['name' => 'Intermediate (Class 12 Commerce & Arts): Accountancy, Business Organisation, Economics, Civics, History', 'marks' => '5 Subjects • 500 Marks'],
        ],
        'highlights' => ['NCERT syllabus implemented in both Hindi & English mediums', 'Monthly division of syllabus with quarterly assessments', 'Direct eligibility for CUET-UG and JEE/NEET']
    ],
    [
        'id' => 'state-bseb-matric',
        'category' => 'state_board',
        'category_name' => 'State Boards & NIOS',
        'subject' => 'Bihar Board (BSEB) Matric & Intermediate Syllabus',
        'code' => 'BSEB-PATNA',
        'board' => 'Bihar School Examination Board (BSEB, Patna)',
        'stream' => 'Class 10 (Matric) & Class 12 (I.Sc, I.Com, I.A)',
        'academic_year' => '2026–2027',
        'scheme' => '50% Objective (OMR MCQs) + 50% Subjective Descriptive',
        'official_url' => 'http://biharboardonline.bihar.gov.in',
        'summary' => 'Comprehensive state curriculum for over 30 lakh students. Features a student-friendly 50% objective MCQ format on OMR sheets and 50% descriptive analytical questions based on SCERT/NCERT curriculum.',
        'units' => [
            ['name' => 'Matriculation (Class 10): Matribhasha (Hindi/Urdu), Second Lang (Sanskrit/Hindi), Math, Science, Social Science', 'marks' => '500 Marks'],
            ['name' => 'Intermediate Science (I.Sc): Physics, Chemistry, Biology/Math, Language 1 & Language 2', 'marks' => '500 Marks'],
            ['name' => 'Intermediate Commerce (I.Com) & Arts (I.A): Accountancy, Entrepreneurship, History, Pol Science, Geography', 'marks' => '500 Marks'],
        ],
        'highlights' => ['100% extra choice in question papers (e.g., attempt 50 out of 100 MCQs)', 'Conducted in February annually with rapid computerized results']
    ],
    [
        'id' => 'state-nios-open',
        'category' => 'state_board',
        'category_name' => 'State Boards & NIOS',
        'subject' => 'NIOS Open Schooling Secondary & Sr. Secondary',
        'code' => 'NIOS-MOE',
        'board' => 'National Institute of Open Schooling (MoE, Govt of India)',
        'stream' => 'Open & Distance Learning (10th & 12th Equivalent)',
        'academic_year' => '2026–2027',
        'scheme' => 'Theory + Tutor Marked Assignments (TMA 20%) + Practical',
        'official_url' => 'https://www.nios.ac.in',
        'summary' => 'Recognized by the Ministry of Education, Govt of India, for flexible distance secondary and senior secondary education. Valid for all national entrance examinations (JEE, NEET, CUET, NDA) and university admissions.',
        'units' => [
            ['name' => 'Secondary Course (Class 10): Choice of 32 subjects across languages and sciences', 'marks' => 'Minimum 5 Subjects'],
            ['name' => 'Senior Secondary (Class 12): Science, Commerce, and Humanities streams with On-Demand Examination (ODE)', 'marks' => 'Minimum 5 Subjects'],
            ['name' => 'Tutor Marked Assignments (TMA): 20% internal continuous weightage for theory subjects', 'marks' => '20% Marks'],
        ],
        'highlights' => ['On-Demand Examination (ODE) facility available year-round', 'Complete parity with CBSE/ICSE certificates recognized by AIU and MoE']
    ]
];

// Calculate category counts
$categoryCounts = [
    'all' => count($curriculumData),
    'cbse_10' => 0,
    'cbse_12' => 0,
    'entrance' => 0,
    'university' => 0,
    'state_board' => 0,
];

foreach ($curriculumData as $item) {
    if (isset($categoryCounts[$item['category']])) {
        $categoryCounts[$item['category']]++;
    }
}

// Filter dataset based on user selection
$filteredCurriculum = array_filter($curriculumData, function($item) use ($selectedCategory, $selectedStream, $searchKeyword) {
    // 1. Category filter
    if ($selectedCategory !== 'all' && $item['category'] !== $selectedCategory) {
        return false;
    }

    // 2. Stream filter
    if ($selectedStream !== '' && stripos($item['stream'], $selectedStream) === false) {
        return false;
    }

    // 3. Keyword search
    if ($searchKeyword !== '') {
        $haystack = strtolower(
            $item['subject'] . ' ' . 
            $item['code'] . ' ' . 
            $item['board'] . ' ' . 
            $item['stream'] . ' ' . 
            $item['summary'] . ' ' . 
            implode(' ', array_column($item['units'], 'name'))
        );
        $needle = strtolower($searchKeyword);
        if (strpos($haystack, $needle) === false) {
            return false;
        }
    }

    return true;
});

$pageTitle = 'National Syllabus & Curriculum Directory 2026–2027 — CBSE, CUET, JEE, NEET, UGC | Online Education';
$pageDesc = 'Explore and download official, NEP 2020 aligned syllabus and marking schemes for CBSE Classes 9-12, National Entrance Exams (CUET, JEE, NEET, UPSC), UGC FYUP Universities, and State Boards.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs -->
<div style="font-size: 0.82rem; color: #6B7280; margin-bottom: 20px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
    <a href="index.php" style="color: #111827; font-weight: 700;">Home</a>
    <span>/</span>
    <span style="color: var(--orange-dark); font-weight: 700;">Curriculum &amp; Syllabus Directory</span>
</div>

<!-- Header Hero Banner -->
<div style="background: #FFFFFF; border: 2px solid #111827; border-radius: var(--radius-lg); padding: 32px 24px; margin-bottom: 28px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 8px; background: var(--orange-light); border: 1.5px solid var(--orange-border); color: var(--orange-dark); font-size: 0.78rem; font-weight: 800; padding: 4px 12px; border-radius: 999px; margin-bottom: 10px;">
                <span>🇮🇳 राष्ट्रीय पाठ्यक्रम एवं विषय-वस्तु मंच</span>
                <span>•</span>
                <span>Official Board &amp; Entrance Registry</span>
            </div>
            <h1 style="font-size: 2.1rem; font-weight: 800; color: #111827; letter-spacing: -0.5px; line-height: 1.25; margin-bottom: 8px;">
                National Curriculum &amp; Syllabus Portal
            </h1>
            <p style="color: var(--slate-body); font-size: 0.98rem; max-width: 820px; line-height: 1.6;">
                Verified curriculum outlines, unit-wise marks distributions, examination patterns, and official board syllabus documents for <strong>CBSE (Class 9–12)</strong>, <strong>National Entrance Examinations (CUET, JEE, NEET, UPSC, GATE, CAT)</strong>, <strong>UGC / AICTE Higher Education</strong>, and <strong>State Boards</strong>.
            </p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-end;">
            <div style="font-size: 0.82rem; background: #DEF7EC; color: #03543F; padding: 6px 14px; border-radius: 999px; font-weight: 800; border: 1.5px solid #BCF0DA; display: inline-flex; align-items: center; gap: 6px;">
                <span>✅ Aligned with NEP 2020 &amp; NCF</span>
            </div>
            <div style="font-size: 0.76rem; color: #64748B;">
                Academic Year: <strong style="color: #111827;">2026–2027</strong>
            </div>
        </div>
    </div>

    <!-- Quick Stats Metric Pills -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin-top: 18px; padding-top: 18px; border-top: 1px solid #F1F5F9;">
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px 14px; border-radius: 8px;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase;">CBSE Curriculums</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: #111827;">Class 9 to 12</div>
            <div style="font-size: 0.72rem; color: var(--orange-dark); font-weight: 700;">Science • Commerce • Arts</div>
        </div>
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px 14px; border-radius: 8px;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Entrance Exams</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--orange);">CUET • JEE • NEET</div>
            <div style="font-size: 0.72rem; color: #111827; font-weight: 700;">NTA Exam Patterns</div>
        </div>
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px 14px; border-radius: 8px;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Higher Education</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: #111827;">UGC FYUP / AICTE</div>
            <div style="font-size: 0.72rem; color: var(--orange-dark); font-weight: 700;">Choice-Based Credit System</div>
        </div>
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px 14px; border-radius: 8px;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase;">State Boards</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: #111827;">UP, Bihar, NIOS</div>
            <div style="font-size: 0.72rem; color: #111827; font-weight: 700;">NCERT Rationalized</div>
        </div>
    </div>
</div>

<!-- Search & Category Filters Bar -->
<div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
    <form action="syllabus.php" method="GET" style="display: flex; flex-direction: column; gap: 14px;">
        <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 260px;">
                <input 
                    type="text" 
                    name="q" 
                    class="form-control" 
                    placeholder="Search by subject, code, unit topic (e.g. Mathematics, Physics, Calculus, Organic Chemistry, Accountancy)..." 
                    value="<?= htmlspecialchars($searchKeyword) ?>"
                    style="width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95rem; font-weight: 500;"
                >
            </div>

            <div style="width: 220px; min-width: 170px;">
                <select name="stream" style="width: 100%; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; background: #FFF;">
                    <option value="">All Streams / Subjects</option>
                    <option value="Science" <?= $selectedStream === 'Science' ? 'selected' : '' ?>>Science (PCM / PCB)</option>
                    <option value="Commerce" <?= $selectedStream === 'Commerce' ? 'selected' : '' ?>>Commerce (Accounts/Eco/BST)</option>
                    <option value="Humanities" <?= $selectedStream === 'Humanities' || $selectedStream === 'Arts' ? 'selected' : '' ?>>Humanities / Arts</option>
                    <option value="General" <?= $selectedStream === 'General' ? 'selected' : '' ?>>Secondary Core (Class 10)</option>
                    <option value="Engineering" <?= $selectedStream === 'Engineering' ? 'selected' : '' ?>>Engineering (JEE / B.Tech / GATE)</option>
                    <option value="Medical" <?= $selectedStream === 'Medical' ? 'selected' : '' ?>>Medical (NEET / Allied)</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 0.95rem; font-weight: 700; height: 100%;">
                    🔍 Filter Syllabus
                </button>
            </div>

            <?php if ($searchKeyword !== '' || $selectedStream !== '' || $selectedCategory !== 'all'): ?>
                <div>
                    <a href="syllabus.php" class="btn btn-outline" style="padding: 12px 16px; font-size: 0.9rem;">
                        ✕ Reset
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <!-- Category Filter Tabs -->
    <div style="margin-top: 16px; border-top: 1px solid #F1F5F9; display: flex; gap: 8px; overflow-x: auto; padding-top: 14px; padding-bottom: 4px;">
        <a href="syllabus.php?category=all<?= $searchKeyword ? '&q='.urlencode($searchKeyword) : '' ?><?= $selectedStream ? '&stream='.urlencode($selectedStream) : '' ?>" class="btn <?= $selectedCategory === 'all' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
            All Curriculums (<?= $categoryCounts['all'] ?>)
        </a>
        <a href="syllabus.php?category=cbse_10<?= $searchKeyword ? '&q='.urlencode($searchKeyword) : '' ?><?= $selectedStream ? '&stream='.urlencode($selectedStream) : '' ?>" class="btn <?= $selectedCategory === 'cbse_10' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
            🏫 CBSE Class 10 (<?= $categoryCounts['cbse_10'] ?>)
        </a>
        <a href="syllabus.php?category=cbse_12<?= $searchKeyword ? '&q='.urlencode($searchKeyword) : '' ?><?= $selectedStream ? '&stream='.urlencode($selectedStream) : '' ?>" class="btn <?= $selectedCategory === 'cbse_12' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
            🎓 CBSE Class 12 (<?= $categoryCounts['cbse_12'] ?>)
        </a>
        <a href="syllabus.php?category=entrance<?= $searchKeyword ? '&q='.urlencode($searchKeyword) : '' ?><?= $selectedStream ? '&stream='.urlencode($selectedStream) : '' ?>" class="btn <?= $selectedCategory === 'entrance' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
            🎯 Entrance Exams (<?= $categoryCounts['entrance'] ?>)
        </a>
        <a href="syllabus.php?category=university<?= $searchKeyword ? '&q='.urlencode($searchKeyword) : '' ?><?= $selectedStream ? '&stream='.urlencode($selectedStream) : '' ?>" class="btn <?= $selectedCategory === 'university' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
            🏛️ University &amp; Colleges (<?= $categoryCounts['university'] ?>)
        </a>
        <a href="syllabus.php?category=state_board<?= $searchKeyword ? '&q='.urlencode($searchKeyword) : '' ?><?= $selectedStream ? '&stream='.urlencode($selectedStream) : '' ?>" class="btn <?= $selectedCategory === 'state_board' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 0.82rem; white-space: nowrap;">
            📜 State Boards &amp; NIOS (<?= $categoryCounts['state_board'] ?>)
        </a>
    </div>
</div>

<!-- Results Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div style="font-size: 0.95rem; color: var(--slate-body);">
        Showing <strong><?= count($filteredCurriculum) ?></strong> official curriculum structure<?= count($filteredCurriculum) === 1 ? '' : 's' ?>
        <?php if ($searchKeyword): ?> matching <span style="color: var(--orange); font-weight: 700;">"<?= htmlspecialchars($searchKeyword) ?>"</span><?php endif; ?>
    </div>
    <div style="font-size: 0.82rem; color: var(--slate-muted);">
        Official Source: <strong>CBSE Academic • NTA • UGC • AICTE • UPSC</strong>
    </div>
</div>

<!-- Curriculum Cards Grid -->
<?php if (empty($filteredCurriculum)): ?>
    <div style="background: #FFFFFF; border: 1.5px solid var(--border-color); border-radius: var(--radius-lg); padding: 48px 20px; text-align: center; margin-bottom: 36px;">
        <div style="font-size: 3rem; margin-bottom: 12px;">📖</div>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 8px;">No Syllabus Matches Your Search</h3>
        <p style="color: #64748B; font-size: 0.92rem; max-width: 480px; margin: 0 auto 20px;">
            We couldn't find any curriculum items matching your filter criteria. Try clearing the search keyword or selecting a different board category.
        </p>
        <a href="syllabus.php" class="btn btn-primary">View All Curriculums</a>
    </div>
<?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 24px; margin-bottom: 36px;">
        <?php foreach ($filteredCurriculum as $item): ?>
            <div id="<?= htmlspecialchars($item['id']) ?>" style="background: #FFFFFF; border: 1.5px solid #111827; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); transition: transform 0.15s ease-in-out;">
                <!-- Card Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
                    <div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 6px;">
                            <span style="font-size: 0.74rem; font-weight: 800; background: #111827; color: #FFFFFF; padding: 2px 8px; border-radius: 4px;">
                                <?= htmlspecialchars($item['category_name']) ?>
                            </span>
                            <span style="font-size: 0.74rem; font-weight: 700; background: var(--orange-light); border: 1px solid var(--orange-border); color: var(--orange-dark); padding: 2px 8px; border-radius: 4px;">
                                Code: <?= htmlspecialchars($item['code']) ?>
                            </span>
                            <span style="font-size: 0.74rem; font-weight: 700; background: #EFF6FF; color: #1D4ED8; padding: 2px 8px; border-radius: 4px;">
                                <?= htmlspecialchars($item['stream']) ?>
                            </span>
                        </div>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: #111827; margin: 0;">
                            <?= htmlspecialchars($item['subject']) ?>
                        </h2>
                        <div style="font-size: 0.82rem; color: #64748B; margin-top: 4px;">
                            Authority: <strong style="color: #111827;"><?= htmlspecialchars($item['board']) ?></strong> • Academic Session: <strong style="color: var(--orange);"><?= htmlspecialchars($item['academic_year']) ?></strong>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="<?= htmlspecialchars($item['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="font-size: 0.8rem; padding: 6px 12px; display: inline-flex; align-items: center; gap: 5px;">
                            <span>Official Portal</span>
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        </a>
                    </div>
                </div>

                <!-- Summary Description -->
                <p style="font-size: 0.92rem; color: #374151; line-height: 1.55; margin-bottom: 16px;">
                    <?= htmlspecialchars($item['summary']) ?>
                </p>

                <!-- Examination Scheme Banner -->
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div style="font-size: 0.84rem; color: #111827;">
                        <strong style="color: var(--orange-dark);">Examination Scheme &amp; Pattern:</strong> <?= htmlspecialchars($item['scheme']) ?>
                    </div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: #059669; background: #ECFDF5; padding: 2px 8px; border-radius: 4px; border: 1px solid #A7F3D0;">
                        100% Verified Structure
                    </div>
                </div>

                <!-- Units & Marks Weightage Breakdown Table -->
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 0.84rem; font-weight: 800; color: #111827; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px;">
                        📋 Course Units &amp; Marks Distribution
                    </div>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.86rem; background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 6px;">
                            <thead>
                                <tr style="background: #111827; color: #FFFFFF;">
                                    <th style="padding: 8px 12px; text-align: left; font-weight: 700;">Unit / Module Topic</th>
                                    <th style="padding: 8px 12px; text-align: right; width: 140px; font-weight: 700;">Weightage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($item['units'] as $idx => $unit): ?>
                                    <tr style="border-bottom: 1px solid #F1F5F9; background: <?= $idx % 2 === 0 ? '#FFFFFF' : '#F9FAFB' ?>;">
                                        <td style="padding: 9px 12px; color: #1F2937; font-weight: 600;">
                                            <?= htmlspecialchars($unit['name']) ?>
                                        </td>
                                        <td style="padding: 9px 12px; text-align: right; font-weight: 800; color: var(--orange);">
                                            <?= htmlspecialchars($unit['marks']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Highlights & Action Links -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-top: 1px solid #F1F5F9; padding-top: 14px;">
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <?php foreach ($item['highlights'] as $hl): ?>
                            <span style="font-size: 0.74rem; background: #F3F4F6; color: #4B5563; padding: 3px 8px; border-radius: 4px; font-weight: 600;">
                                • <?= htmlspecialchars($hl) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="learn.php?subject=<?= urlencode(strtolower(explode(' ', $item['subject'])[0])) ?>" class="btn btn-outline" style="font-size: 0.8rem; padding: 6px 12px;">
                            📚 NCERT Books &amp; Notes →
                        </a>
                        <a href="<?= htmlspecialchars($item['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="font-size: 0.8rem; padding: 6px 14px;">
                            Official Syllabus PDF ↗
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- NEP 2020 & Curriculum Guidance Section -->
<div style="background: #FFFFFF; border: 2px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px 24px; margin-bottom: 32px; box-shadow: var(--shadow-sm);">
    <h3 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        <span>🇮🇳</span> National Education Policy (NEP 2020) &amp; Curriculum Framework
    </h3>
    <p style="font-size: 0.92rem; color: #4B5563; line-height: 1.6; margin-bottom: 16px;">
        Under the National Curriculum Framework for School Education (NCF-SE) and the UGC National Higher Education Qualifications Framework (NHEQF), education in India focuses on core conceptual understanding, multidisciplinary flexibility, and reducing content overload to promote critical thinking.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
        <div style="background: #FFF7ED; border: 1px solid #FED7AA; border-radius: 8px; padding: 14px;">
            <h4 style="font-size: 0.92rem; font-weight: 800; color: var(--orange-dark); margin-bottom: 6px;">
                5+3+3+4 Pedagogical Structure
            </h4>
            <p style="font-size: 0.82rem; color: #7C2D12; line-height: 1.5; margin: 0;">
                CBSE and State Boards have transitioned to Foundational (3-8 yrs), Preparatory (8-11 yrs), Middle (11-14 yrs), and Secondary (14-18 yrs) curriculum stages.
            </p>
        </div>

        <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 14px;">
            <h4 style="font-size: 0.92rem; font-weight: 800; color: #1E40AF; margin-bottom: 6px;">
                Competency-Based Questions
            </h4>
            <p style="font-size: 0.82rem; color: #1E3A8A; line-height: 1.5; margin: 0;">
                Class 10 and 12 Board examinations now feature a minimum 50% competency-focused questions (case-based, source-based, assertion-reasoning).
            </p>
        </div>

        <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 14px;">
            <h4 style="font-size: 0.92rem; font-weight: 800; color: #166534; margin-bottom: 6px;">
                Academic Bank of Credits (ABC)
            </h4>
            <p style="font-size: 0.82rem; color: #14532D; line-height: 1.5; margin: 0;">
                Higher education university students can store and transfer digital credits earned across recognized HEIs under the UGC FYUP choice-based credit system.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
