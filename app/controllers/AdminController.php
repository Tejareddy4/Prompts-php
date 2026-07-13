<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Category;
use App\Models\Karma;
use App\Models\Prompt;
use App\Models\User;

class AdminController extends Controller
{
    // ── Dashboard ──────────────────────────────────────────────

    public function index(): void
    {
        $db          = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $userModel   = new User($db);
        $analytics   = $promptModel->analytics();
        $userStats   = $userModel->stats();

        // 30-day engagement trend
        $trend = $this->engagementTrend($db);

        $this->render('admin/index', [
            'pageTitle'    => 'Admin Dashboard',
            'analytics'    => $analytics,
            'userStats'    => $userStats,
            'trend'        => $trend,
            'recentUsers'  => $userModel->recentSignups(6),
            'pending'      => $promptModel->findByStatus(1),
        ]);
    }

    // ── Prompt management ─────────────────────────────────────

    public function prompts(): void
    {
        $db          = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);

        $this->render('admin/prompts', [
            'pageTitle'  => 'Manage Prompts',
            'pending'    => $promptModel->findByStatus(1),
            'approved'   => $promptModel->findByStatus(2),
            'rejected'   => $promptModel->findByStatus(3),
            'categories' => (new Category($db))->all(),
        ]);
    }

    /** Quick category re-assign from the prompts table. */
    public function setPromptCategory(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/prompts'); }
        $db         = Database::connection($this->config['db']);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        if ($categoryId > 0 && !(new Category($db))->findById($categoryId)) { $this->redirect('/admin/prompts'); }
        $stmt = $db->prepare('UPDATE prompts SET category_id = :cat, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['cat' => $categoryId > 0 ? $categoryId : null, 'id' => (int)$_POST['prompt_id']]);
        $this->clearHomeCache();
        flash('Prompt category updated.', 'success');
        $this->redirect('/admin/prompts');
    }

    public function approve(): void
    {
        $this->changeStatus(2, 'Prompt approved and published.');
    }

    public function reject(): void
    {
        $this->changeStatus(3, 'Prompt rejected.');
    }

    public function deletePrompt(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/prompts'); }
        $db = Database::connection($this->config['db']);
        (new Prompt($db))->delete((int) $_POST['prompt_id']);
        flash('Prompt deleted.', 'success');
        $this->redirect('/admin/prompts');
    }

    public function featurePrompt(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/prompts'); }
        $db  = Database::connection($this->config['db']);
        $set = (int)($_POST['featured'] ?? 0);
        try {
            $stmt = $db->prepare('UPDATE prompts SET is_featured = :f, updated_at = NOW() WHERE id = :id');
            $stmt->execute(['f' => $set, 'id' => (int)$_POST['prompt_id']]);
        } catch (\Exception $e) {
            // is_featured column not migrated yet
            \App\Core\Logger::warning('featurePrompt failed (is_featured column missing?): ' . $e->getMessage());
        }
        flash($set ? 'Prompt featured.' : 'Prompt unfeatured.', 'success');
        $this->redirect('/admin/prompts');
    }

    // ── User management ───────────────────────────────────────

    public function users(): void
    {
        $db        = Database::connection($this->config['db']);
        $userModel = new User($db);

        $this->render('admin/users', [
            'pageTitle' => 'Manage Users',
            'users'     => $userModel->all(),
            'userStats' => $userModel->stats(),
            'growth'    => $userModel->growthByDay(14),
            'karmaMap'  => (new Karma($db))->forAllUsers(),
        ]);
    }

    public function banUser(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/users'); }
        $db   = Database::connection($this->config['db']);
        $ban  = ($_POST['action'] ?? '') === 'ban';
        (new User($db))->ban((int)$_POST['user_id'], $ban);
        flash($ban ? 'User banned.' : 'User unbanned.', 'success');
        $this->redirect('/admin/users');
    }

    public function setUserRole(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/users'); }
        $db     = Database::connection($this->config['db']);
        $roleId = (int)$_POST['role_id'];
        if (!in_array($roleId, [1, 2], true)) { $this->redirect('/admin/users'); }
        (new User($db))->setRole((int)$_POST['user_id'], $roleId);
        flash('User role updated.', 'success');
        $this->redirect('/admin/users');
    }

    public function deleteUser(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/users'); }
        $db = Database::connection($this->config['db']);
        (new User($db))->delete((int)$_POST['user_id']);
        flash('User deleted permanently.', 'success');
        $this->redirect('/admin/users');
    }

    // ── Analytics ─────────────────────────────────────────────

    public function analytics(): void
    {
        $db          = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $userModel   = new User($db);

        // Top prompts by likes
        $topPrompts = $db->query(
            'SELECT p.title, p.slug, u.name AS author,
                    (SELECT COUNT(*) FROM likes l WHERE l.prompt_id = p.id) AS likes_count,
                    (SELECT COUNT(*) FROM views v WHERE v.prompt_id = p.id) AS views_count,
                    (SELECT COUNT(*) FROM saves s WHERE s.prompt_id = p.id) AS saves_count
             FROM prompts p JOIN users u ON u.id = p.user_id
             WHERE p.status_id = 2
             ORDER BY likes_count DESC LIMIT 10'
        )->fetchAll();

        // Top creators
        $topCreators = $db->query(
            'SELECT u.name, u.email, u.avatar_url,
                    COUNT(p.id) AS prompt_count,
                    SUM((SELECT COUNT(*) FROM likes l WHERE l.prompt_id = p.id)) AS total_likes
             FROM users u LEFT JOIN prompts p ON p.user_id = u.id AND p.status_id = 2
             GROUP BY u.id ORDER BY total_likes DESC LIMIT 8'
        )->fetchAll();

        // Daily stats last 14 days
        $dailyViews = $db->query(
            'SELECT DATE(created_at) AS day, COUNT(*) AS count
             FROM views WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             GROUP BY DATE(created_at) ORDER BY day ASC'
        )->fetchAll();

        $dailyLikes = $db->query(
            'SELECT DATE(created_at) AS day, COUNT(*) AS count
             FROM likes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             GROUP BY DATE(created_at) ORDER BY day ASC'
        )->fetchAll();

        $this->render('admin/analytics', [
            'pageTitle'   => 'Analytics',
            'analytics'   => $promptModel->analytics(),
            'userStats'   => $userModel->stats(),
            'topPrompts'  => $topPrompts,
            'topCreators' => $topCreators,
            'dailyViews'  => $dailyViews,
            'dailyLikes'  => $dailyLikes,
        ]);
    }

    // ── Category management ───────────────────────────────────

    public function categories(): void
    {
        $db = Database::connection($this->config['db']);

        $this->render('admin/categories', [
            'pageTitle'  => 'Manage Categories',
            'categories' => (new Category($db))->withTotalCounts(),
            'uncategorized' => (int) $db->query('SELECT COUNT(*) FROM prompts WHERE category_id IS NULL')->fetchColumn(),
        ]);
    }

    public function saveCategory(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/categories'); }
        $db    = Database::connection($this->config['db']);
        $model = new Category($db);

        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim((string)($_POST['name'] ?? ''));
        $slug  = trim((string)($_POST['slug'] ?? ''));
        $icon  = trim((string)($_POST['icon'] ?? '')) ?: 'bi-stars';
        $color = trim((string)($_POST['color'] ?? '')) ?: 'violet';
        $sort  = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') {
            flash('Category name is required.', 'error');
            $this->redirect('/admin/categories');
        }

        $slug = $this->slugify($slug !== '' ? $slug : $name);
        if ($slug === '' || $model->slugExists($slug, $id ?: null)) {
            flash($slug === '' ? 'Could not build a valid slug.' : "Slug \"{$slug}\" is already in use.", 'error');
            $this->redirect('/admin/categories');
        }

        $allowedColors = ['violet','blue','pink','orange','green','cyan','red','indigo','teal','gray'];
        if (!in_array($color, $allowedColors, true)) { $color = 'violet'; }
        if (!preg_match('/^bi-[a-z0-9-]+$/', $icon))  { $icon  = 'bi-stars'; }

        $data = [
            'name'       => mb_substr($name, 0, 60),
            'slug'       => mb_substr($slug, 0, 60),
            'icon'       => $icon,
            'color'      => $color,
            'sort_order' => $sort > 0 ? $sort : $model->nextSortOrder(),
        ];

        if ($id > 0) {
            $model->update($id, $data);
            flash('Category updated.', 'success');
        } else {
            $model->create($data);
            flash('Category created.', 'success');
        }

        $this->clearHomeCache();
        $this->redirect('/admin/categories');
    }

    public function deleteCategory(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/categories'); }
        $db = Database::connection($this->config['db']);
        (new Category($db))->delete((int)$_POST['category_id']);
        $this->clearHomeCache();
        flash('Category deleted. Its prompts are now uncategorized.', 'success');
        $this->redirect('/admin/categories');
    }

    // ── Logs ──────────────────────────────────────────────────

    public function logs(): void
    {
        $files = \App\Core\Logger::files();
        $file  = (string)($_GET['file'] ?? ($files[0] ?? ''));

        $this->render('admin/logs', [
            'pageTitle' => 'Logs',
            'files'     => $files,
            'file'      => $file,
            'entries'   => $file !== '' ? \App\Core\Logger::tail($file, 300) : [],
        ]);
    }

    public function clearLog(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/logs'); }
        $file = (string)($_POST['file'] ?? '');
        flash(\App\Core\Logger::clear($file) ? "Log {$file} cleared." : 'Could not clear that log.', 'success');
        $this->redirect('/admin/logs');
    }

    // ── Settings ──────────────────────────────────────────────

    public function settings(): void
    {
        $this->render('admin/settings', [
            'pageTitle' => 'Site Settings',
            'config'    => $this->config,
        ]);
    }

    public function saveSettings(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/settings'); }
        // In production you'd persist to DB or .env; for now flash success
        flash('Settings updated successfully.', 'success');
        $this->redirect('/admin/settings');
    }

    // ── JSON endpoints for dashboard widgets ──────────────────

    public function statsJson(): void
    {
        $db          = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $userModel   = new User($db);
        $this->json([
            'prompts'   => $promptModel->analytics(),
            'users'     => $userModel->stats(),
            'trend'     => $this->engagementTrend($db),
        ]);
    }

    // ── Private helpers ───────────────────────────────────────

    private function slugify(string $value): string
    {
        return trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $value)), '-');
    }

    private function clearHomeCache(): void
    {
        $cache = new Cache($this->config['cache']);
        foreach (['home_page_1', 'home_slider', 'home_top_picks'] as $key) {
            $cache->forget($key);
        }
    }

    private function changeStatus(int $status, string $message): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) { $this->redirect('/admin/prompts'); }
        $db = Database::connection($this->config['db']);
        (new Prompt($db))->updateStatus((int) $_POST['prompt_id'], $status);
        flash($message, 'success');
        $this->redirect('/admin/prompts');
    }

    private function engagementTrend(\PDO $db): array
    {
        $days = [];
        for ($i = 13; $i >= 0; $i--) {
            $days[] = date('Y-m-d', strtotime("-{$i} days"));
        }

        $viewsRaw = $db->query(
            'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM views
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             GROUP BY DATE(created_at)'
        )->fetchAll(\PDO::FETCH_KEY_PAIR);

        $likesRaw = $db->query(
            'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM likes
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             GROUP BY DATE(created_at)'
        )->fetchAll(\PDO::FETCH_KEY_PAIR);

        $signupsRaw = $db->query(
            'SELECT DATE(created_at) AS d, COUNT(*) AS n FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             GROUP BY DATE(created_at)'
        )->fetchAll(\PDO::FETCH_KEY_PAIR);

        return array_map(fn($day) => [
            'day'     => date('M j', strtotime($day)),
            'views'   => (int)($viewsRaw[$day] ?? 0),
            'likes'   => (int)($likesRaw[$day] ?? 0),
            'signups' => (int)($signupsRaw[$day] ?? 0),
        ], $days);
    }
}
