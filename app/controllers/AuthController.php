<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->render('auth/login');
    }

    public function showRegister(): void
    {
        $this->render('auth/register');
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $this->redirect('/login');
        }

        $db = Database::connection($this->config['db']);
        $userModel = new User($db);
        $user = $userModel->findByEmail(trim($_POST['email'] ?? ''));

        if (!$user || !password_verify($_POST['password'] ?? '', $user['password_hash'])) {
            $_SESSION['flash'] = 'Invalid credentials';
            $this->redirect('/login');
        }

        Auth::login($user);
        $this->redirect('/dashboard');
    }

    public function register(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $this->redirect('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $_SESSION['flash'] = 'Invalid registration data';
            $this->redirect('/register');
        }

        $db = Database::connection($this->config['db']);
        $userModel = new User($db);
        if ($userModel->findByEmail($email)) {
            $_SESSION['flash'] = 'Email already exists';
            $this->redirect('/register');
        }

        $userId = $userModel->create($name, $email, $password);
        $user = $userModel->findByEmail($email);
        $user['id'] = $userId;
        Auth::login($user);
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/');
    }

    public function googleRedirect(): void
    {
        if (!$this->config['google_oauth']['enabled']) {
            $_SESSION['flash'] = 'Google OAuth is disabled. Configure credentials to enable.';
            $this->redirect('/login');
        }
        echo 'Implement OAuth redirect with Google endpoints.';
    }

    public function googleCallback(): void
    {
        echo 'Implement OAuth callback token exchange + upsert user.';
    }
}
