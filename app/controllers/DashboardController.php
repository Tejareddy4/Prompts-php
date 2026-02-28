<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Prompt;

class DashboardController extends Controller
{
    public function index(): void
    {
        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $userId = (int) Auth::id();

        $this->render('dashboard/index', [
            'pageTitle' => 'My Dashboard',
            'myPrompts' => $promptModel->userPrompts($userId),
            'savedPrompts' => $promptModel->userSaved($userId),
            'likedPrompts' => $promptModel->userLiked($userId),
        ]);
    }
}
