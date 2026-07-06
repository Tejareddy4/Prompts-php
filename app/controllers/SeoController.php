<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Category;
use App\Models\Prompt;

class SeoController extends Controller
{
    public function sitemap(): void
    {
        $db = Database::connection($this->config['db']);
        $categories = (new Category($db))->all();
        $prompts = (new Prompt($db))->sitemapEntries();
        $base = rtrim(config('app.base_url'), '/');

        header('Content-Type: application/xml; charset=UTF-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        echo $this->urlTag($base . '/', 'daily', '1.0');

        foreach ($categories as $category) {
            echo $this->urlTag($base . '/category/' . $category['slug'], 'daily', '0.8');
        }

        foreach ($prompts as $prompt) {
            $lastmod = date('Y-m-d', strtotime($prompt['updated_at'] ?? $prompt['created_at']));
            echo $this->urlTag($base . '/prompt/' . $prompt['slug'], 'weekly', '0.6', $lastmod);
        }

        echo '</urlset>';
    }

    private function urlTag(string $loc, string $changefreq, string $priority, ?string $lastmod = null): string
    {
        $xml = "  <url>\n    <loc>" . e($loc) . "</loc>\n";
        if ($lastmod) {
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        }
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n    <priority>{$priority}</priority>\n  </url>\n";
        return $xml;
    }
}
