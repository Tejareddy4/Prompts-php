<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Prompt;
use App\Models\User;

class AdminController extends Controller
{
    public function index(): void
    {
        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $userModel = new User($db);

        $this->render('admin/index', [
            'pageTitle' => 'Admin Dashboard',
            'pending' => $promptModel->findByStatus(1),
            'approved' => $promptModel->findByStatus(2),
            'rejected' => $promptModel->findByStatus(3),
            'analytics' => $promptModel->analytics(),
            'users' => $userModel->all(),
        ]);
    }

    public function approve(): void
    {
        $this->changeStatus(2);
    }

    public function reject(): void
    {
        $this->changeStatus(3);
    }

    public function delete(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $this->redirect('/admin');
        }

        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $promptModel->delete((int) $_POST['prompt_id']);
        $this->redirect('/admin');
    }

    private function changeStatus(int $status): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $this->redirect('/admin');
        }

        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $promptModel->updateStatus((int) $_POST['prompt_id'], $status);
        $this->redirect('/admin');
    }
}
