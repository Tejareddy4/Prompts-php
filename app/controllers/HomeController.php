<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Cache;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Category;
use App\Models\Interaction;
use App\Models\Prompt;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->list(null);
    }

    public function category(array $params): void
    {
        $db = Database::connection($this->config['db']);
        $category = (new Category($db))->findBySlug((string) $params['slug']);

        if (!$category) {
            http_response_code(404);
            $this->render('errors/404', ['pageTitle' => 'Category Not Found']);
            return;
        }

        $this->list($category);
    }

    private function list(?array $category): void
    {
        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $categoryModel = new Category($db);
        $cache = new Cache($this->config['cache']);
        $filters = $this->filters($category['slug'] ?? null);

        // Guests all get the same generic mix → cacheable. Logged-in feeds are personal.
        $canUseCache = !Auth::id() && empty($filters['q']) && empty($filters['cat']) && $filters['sort'] === 'for_you';
        if ($canUseCache) {
            $prompts = $cache->remember('home_page_1', fn() => $promptModel->paginateApproved(12, 0, null, $filters));
        } else {
            $prompts = $promptModel->paginateApproved(12, 0, Auth::id(), $filters);
        }

        // Hero slider (5) + Top Picks (3) — only on the plain homepage
        $slider = $topPicks = [];
        if (!$category && empty($filters['q'])) {
            $slider = $cache->remember('home_slider', fn() => $promptModel->topByEngagement(5));
            $topPicks = $cache->remember(
                'home_top_picks',
                fn() => $promptModel->topByEngagement(3, array_column($slider, 'id'))
            );
        }

        $analytics = $promptModel->analytics();

        $pageTitle = $category ? $category['name'] . ' Prompts' : 'Free AI Prompts for ChatGPT, Claude & Gemini';
        $metaDescription = $category
            ? 'Browse free ' . $category['name'] . ' AI prompts for ChatGPT, Claude & Gemini. Copy and use instantly.'
            : 'Discover and share high-performing AI prompts for ChatGPT, Claude, Gemini & more.';

        $this->render('home/index', [
            'prompts' => $prompts,
            'slider' => $slider,
            'topPicks' => $topPicks,
            'filters' => $filters,
            'categories' => $categoryModel->withCounts(),
            'activeCategory' => $category,
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonical' => rtrim(config('app.base_url'), '/') . ($category ? '/category/' . $category['slug'] : '/'),
            'totalCount' => $analytics['approved_prompts'],
            'totalLikes' => $analytics['total_likes'],
            'totalViews' => $analytics['total_views'],
        ]);
    }

    public function loadMore(array $params): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $rows = $promptModel->paginateApproved($limit, $offset, Auth::id(), $this->filters());

        $this->json(['data' => $rows, 'next_page' => $page + 1, 'has_more' => count($rows) === $limit]);
    }

    /** Typeahead: top title matches as JSON for the search box. */
    public function suggest(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        if (mb_strlen($q) < 2) {
            $this->json(['data' => []]);
            return;
        }

        $db = Database::connection($this->config['db']);
        $this->json(['data' => (new Prompt($db))->suggestByTitle(mb_substr($q, 0, 80), 8)]);
    }

    private function filters(?string $forcedCategory = null): array
    {
        $sort = (string) ($_GET['sort'] ?? 'for_you');
        $allowedSorts = ['for_you', 'newest', 'most_liked', 'most_saved', 'most_viewed', 'trending'];

        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'sort' => in_array($sort, $allowedSorts, true) ? $sort : 'for_you',
            'cat' => $forcedCategory ?? trim((string) ($_GET['cat'] ?? '')),
        ];

        // Personalize "For You" with the viewer's most-visited categories.
        if ($filters['sort'] === 'for_you' && Auth::id()) {
            $db = Database::connection($this->config['db']);
            $filters['top_cats'] = (new Interaction($db))->topCategories((int) Auth::id());
        }

        return $filters;
    }
}
