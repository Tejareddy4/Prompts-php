<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Karma;
use App\Models\Prompt;
use App\Models\User;

class UserController extends Controller
{
    public function profile(array $params): void
    {
        $db        = Database::connection($this->config['db']);
        $userModel = new User($db);
        $user      = $userModel->findByUsername($params['username']);

        if (!$user) {
            http_response_code(404);
            $this->render('errors/404', ['pageTitle' => 'User Not Found']);
            return;
        }

        $promptModel = new Prompt($db);

        $viewer = Auth::user();

        $this->render('user/profile', [
            'pageTitle'       => $user['name'],
            'metaDescription' => 'Browse prompts by ' . $user['name'] . ' on PromptShare.',
            'profile'         => $user,
            'prompts'         => $promptModel->approvedByUser((int) $user['id']),
            'stats'           => $userModel->stats((int) $user['id']),
            'karma'           => (new Karma($db))->forUser((int) $user['id']),
            'viewerIsAdmin'   => ($viewer['role_name'] ?? '') === 'super_admin',
        ]);
    }
}
