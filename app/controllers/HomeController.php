<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Cache;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Prompt;

class HomeController extends Controller
{
    public function index(): void
    {
        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $cache = new Cache($this->config['cache']);
        $filters = $this->filters();

        $canUseCache = !Auth::id() && empty($filters['q']) && $filters['sort'] === 'newest';
        if ($canUseCache) {
            $prompts = $cache->remember('home_page_1', fn() => $promptModel->paginateApproved(12, 0, null, $filters));
        } else {
            $prompts = $promptModel->paginateApproved(12, 0, Auth::id(), $filters);
        }

        $analytics = $promptModel->analytics();

        $this->render('home/index', [
            'prompts' => $prompts,
            'filters' => $filters,
            'pageTitle' => 'Discover Prompts',
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

    private function filters(): array
    {
        $sort = (string) ($_GET['sort'] ?? 'newest');
        $allowedSorts = ['newest', 'most_liked', 'most_saved', 'most_viewed'];

        return [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'sort' => in_array($sort, $allowedSorts, true) ? $sort : 'newest',
        ];
    }
}
