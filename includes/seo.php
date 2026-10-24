<?php
// includes/seo.php — Centralized SEO Engine for Online Education (oedu.in)

if (!isset($pdo)) {
    @require_once __DIR__ . '/db.php';
}

/**
 * Fetch cached or live admin settings
 */
function getSeoSettings(?PDO $pdo = null): array {
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $defaults = [
        'site_name' => 'Online Education',
        'site_tagline' => 'National Higher Education & Institutional Directory of India',
        'site_url' => 'https://oedu.in',
        'meta_description' => 'Discover 106,000+ verified Universities, Affiliated Colleges, Standalone Institutes, CBSE Schools, and BCI Law Colleges across all 36 States & 780 Districts of India.',
        'seo_keywords' => 'Universities in India, AISHE Colleges, CBSE Schools, BCI Law Colleges, Standalone Institutes, Entrance Exams, CUET Cutoff, NIRF Ranking, Higher Education India',
        'google_site_verification' => '',
        'bing_site_verification' => '',
        'google_analytics_id' => 'G-HN2E45GF5T',
        'seo_og_image' => 'logo_full.png',
    ];

    $db = $pdo ?? ($GLOBALS['pdo'] ?? null);
    if ($db instanceof PDO) {
        try {
            $rows = $db->query("SELECT setting_key, setting_value FROM admin_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
            if ($rows) {
                $defaults = array_merge($defaults, array_filter($rows, fn($v) => $v !== null && $v !== ''));
            }
        } catch (Throwable $e) {
            // Fallback to defaults if table is not accessible
        }
    }

    $settings = $defaults;
    return $settings;
}

/**
 * Build the canonical URL for the current page
 */
function getCanonicalUrl(?string $customCanonical = null): string {
    if (!empty($customCanonical)) {
        return $customCanonical;
    }

    $settings = getSeoSettings();
    $base = rtrim($settings['site_url'] ?? 'https://oedu.in', '/');

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // Strip query string for canonical unless specified
    $cleanPath = strtok($uri, '?');

    // On local environment, preserve host
    $host = $_SERVER['HTTP_HOST'] ?? 'oedu.in';
    if ($host === 'localhost' || str_contains($host, '.test')) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        $scheme = $isHttps ? 'https://' : 'http://';
        return rtrim($scheme . $host . $cleanPath, '/');
    }

    // Clean index.php to root
    if ($cleanPath === '/index.php' || $cleanPath === '') {
        $cleanPath = '/';
    }

    return rtrim($base . $cleanPath, '/') ?: $base . '/';
}

/**
 * Strip double escaping and sanitize title/description for head
 */
function cleanSeoText(?string $text): string {
    if ($text === null) {
        return '';
    }
    // Decode first to prevent &amp;amp; double escaping
    $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return htmlspecialchars(trim(strip_tags($decoded)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Generate Global Schema.org JSON-LD for WebSite and Organization
 */
function getGlobalSchemaJsonLd(): array {
    $settings = getSeoSettings();
    $siteUrl = rtrim($settings['site_url'] ?? 'https://oedu.in', '/');

    return [
        "@context" => "https://schema.org",
        "@graph" => [
            [
                "@type" => "WebSite",
                "@id" => "{$siteUrl}/#website",
                "url" => "{$siteUrl}/",
                "name" => $settings['site_name'] ?? "Online Education",
                "alternateName" => ["OEDU", "OnlineEducation India", "Online Education Portal"],
                "description" => $settings['meta_description'] ?? "National Higher Education & Institutional Directory of India",
                "inLanguage" => "en-IN",
                "potentialAction" => [
                    "@type" => "SearchAction",
                    "target" => [
                        "@type" => "EntryPoint",
                        "urlTemplate" => "{$siteUrl}/search.php?q={search_term_string}"
                    ],
                    "query-input" => "required name=search_term_string"
                ]
            ],
            [
                "@type" => "EducationalOrganization",
                "@id" => "{$siteUrl}/#organization",
                "name" => $settings['site_name'] ?? "Online Education",
                "url" => "{$siteUrl}/",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => "{$siteUrl}/logo_full.png"
                ],
                "description" => "Independent National Education Portal mapping 106,000+ verified Universities, Colleges, Institutes, and CBSE Schools of India.",
                "address" => [
                    "@type" => "PostalAddress",
                    "addressCountry" => "IN"
                ]
            ]
        ]
    ];
}
