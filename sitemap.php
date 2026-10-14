<?php
// sitemap.php — Dynamic & Static Google XML Sitemap Generator for Online Education
require_once __DIR__ . '/includes/db.php';

function getSitemapSiteUrl(): string {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $scheme = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'oedu.in';

    if ($host === 'localhost' || str_contains($host, '.test')) {
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $siteUrl = $scheme . $host . ($scriptDir ? $scriptDir : '');
    } else {
        $siteUrl = 'https://oedu.in';
    }
    return rtrim($siteUrl, '/');
}

/**
 * Generate XML Sitemap string for a given section
 */
function generateSitemapXmlContent(string $section, string $siteUrl, PDO $pdo): string {
    $today = date('Y-m-d');

    if (!$section || $section === 'index') {
        $subSitemaps = [
            'main' => 'sitemap-main.xml',
            'universities' => 'sitemap-universities.xml',
            'colleges-1' => 'sitemap-colleges-1.xml',
            'colleges-2' => 'sitemap-colleges-2.xml',
            'colleges-3' => 'sitemap-colleges-3.xml',
            'institutes' => 'sitemap-institutes.xml'
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($subSitemaps as $key => $file) {
            $xml .= "    <sitemap>\n";
            $xml .= "        <loc>" . htmlspecialchars("{$siteUrl}/{$file}") . "</loc>\n";
            $xml .= "        <lastmod>{$today}</lastmod>\n";
            $xml .= "    </sitemap>\n";
        }
        $xml .= '</sitemapindex>';
        return $xml;
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    if ($section === 'main') {
        $mainPages = [
            ['loc' => '', 'priority' => '1.0', 'freq' => 'daily'],
            ['loc' => '/institution.php', 'priority' => '0.9', 'freq' => 'daily'],
            ['loc' => '/college.php', 'priority' => '0.9', 'freq' => 'daily'],
            ['loc' => '/institute.php', 'priority' => '0.9', 'freq' => 'daily'],
            ['loc' => '/search.php', 'priority' => '0.8', 'freq' => 'weekly'],
            ['loc' => '/exam.php', 'priority' => '0.8', 'freq' => 'weekly'],
            ['loc' => '/scholarship.php', 'priority' => '0.8', 'freq' => 'weekly'],
            ['loc' => '/government.php', 'priority' => '0.7', 'freq' => 'monthly'],
            ['loc' => '/global.php', 'priority' => '0.7', 'freq' => 'monthly'],
        ];

        foreach ($mainPages as $p) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($siteUrl . $p['loc']) . "</loc>\n";
            $xml .= "        <lastmod>{$today}</lastmod>\n";
            $xml .= "        <changefreq>{$p['freq']}</changefreq>\n";
            $xml .= "        <priority>{$p['priority']}</priority>\n";
            $xml .= "    </url>\n";
        }
    } elseif ($section === 'universities') {
        $stmt = $pdo->query("SELECT slug, updated_at FROM university WHERE slug IS NOT NULL AND slug != '' ORDER BY id ASC");
        while ($row = $stmt->fetch()) {
            $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : $today;
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars("{$siteUrl}/university/" . urlencode($row['slug'])) . "</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>weekly</changefreq>\n";
            $xml .= "        <priority>0.9</priority>\n";
            $xml .= "    </url>\n";
        }
    } elseif ($section === 'colleges-1') {
        // 1 to 25,000
        $stmt = $pdo->query("SELECT slug, updated_at FROM college WHERE slug IS NOT NULL AND slug != '' ORDER BY id ASC LIMIT 25000 OFFSET 0");
        while ($row = $stmt->fetch()) {
            $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : $today;
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars("{$siteUrl}/college/" . urlencode($row['slug'])) . "</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>monthly</changefreq>\n";
            $xml .= "        <priority>0.8</priority>\n";
            $xml .= "    </url>\n";
        }
    } elseif ($section === 'colleges-2') {
        // 25,001 to 50,000
        $stmt = $pdo->query("SELECT slug, updated_at FROM college WHERE slug IS NOT NULL AND slug != '' ORDER BY id ASC LIMIT 25000 OFFSET 25000");
        while ($row = $stmt->fetch()) {
            $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : $today;
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars("{$siteUrl}/college/" . urlencode($row['slug'])) . "</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>monthly</changefreq>\n";
            $xml .= "        <priority>0.8</priority>\n";
            $xml .= "    </url>\n";
        }
    } elseif ($section === 'colleges-3') {
        // 50,001 onwards
        $stmt = $pdo->query("SELECT slug, updated_at FROM college WHERE slug IS NOT NULL AND slug != '' ORDER BY id ASC LIMIT 25000 OFFSET 50000");
        while ($row = $stmt->fetch()) {
            $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : $today;
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars("{$siteUrl}/college/" . urlencode($row['slug'])) . "</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>monthly</changefreq>\n";
            $xml .= "        <priority>0.8</priority>\n";
            $xml .= "    </url>\n";
        }
    } elseif ($section === 'institutes') {
        $stmt = $pdo->query("SELECT slug, updated_at FROM institute WHERE slug IS NOT NULL AND slug != '' ORDER BY id ASC");
        while ($row = $stmt->fetch()) {
            $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : $today;
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars("{$siteUrl}/institute/" . urlencode($row['slug'])) . "</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>monthly</changefreq>\n";
            $xml .= "        <priority>0.8</priority>\n";
            $xml .= "    </url>\n";
        }
    }

    $xml .= '</urlset>';
    return $xml;
}

// If accessed directly via HTTP request
if (!defined('SITEMAP_INTERNAL_CALL')) {
    $siteUrl = getSitemapSiteUrl();
    $section = trim($_GET['section'] ?? $_GET['type'] ?? '');
    
    header('Content-Type: application/xml; charset=utf-8');
    echo generateSitemapXmlContent($section, $siteUrl, $pdo);
}
