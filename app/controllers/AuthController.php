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
            flash('Session expired, please try again.', 'warning');
            $this->redirect('/login');
        }

        $db = Database::connection($this->config['db']);
        $userModel = new User($db);
        $user = $userModel->findByEmail(trim($_POST['email'] ?? ''));

        if (!$user || !password_verify($_POST['password'] ?? '', $user['password_hash'])) {
            flash('Invalid email or password.', 'error');
            $this->redirect('/login');
        }

        Auth::login($user);
        $this->redirect('/dashboard');
    }

    public function register(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            flash('Session expired, please try again.', 'warning');
            $this->redirect('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '') {
            flash('Please enter your full name.', 'error');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('Please enter a valid email address.', 'error');
            $this->redirect('/register');
        }

        if (strlen($password) < 8) {
            flash('Password must be at least 8 characters.', 'error');
            $this->redirect('/register');
        }

        $db = Database::connection($this->config['db']);
        $userModel = new User($db);
        if ($userModel->findByEmail($email)) {
            flash('An account with that email already exists.', 'error');
            $this->redirect('/register');
        }

        $userId = $userModel->create($name, $email, $password);
        $user = $userModel->findByEmail($email);
        $user['id'] = $userId;
        Auth::login($user);
        flash('Welcome to PromptShare, ' . $name . '!', 'success');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/');
    }

    public function googleRedirect(): void
    {
        flash('Google sign-in is not configured yet.', 'warning');
        $this->redirect('/login');
    }

    public function googleCallback(): void
    {
        flash('Google sign-in is not configured yet.', 'warning');
        $this->redirect('/login');
    }
}
