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

        $prompts = $cache->remember('home_page_1', fn() => $promptModel->paginateApproved(12, 0, Auth::id()));
        $this->render('home/index', ['prompts' => $prompts, 'pageTitle' => 'Discover Prompts']);
    }

    public function loadMore(array $params): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $rows = $promptModel->paginateApproved($limit, $offset, Auth::id());

        $this->json(['data' => $rows, 'next_page' => $page + 1, 'has_more' => count($rows) === $limit]);
    }
}
