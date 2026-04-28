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
        if (Auth::check()) { $this->redirect('/dashboard'); }
        $this->render('auth/login', ['pageTitle' => 'Sign In']);
    }

    public function showRegister(): void
    {
        if (Auth::check()) { $this->redirect('/dashboard'); }
        $this->render('auth/register', ['pageTitle' => 'Create Account']);
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            flash('Session expired, please try again.', 'warning');
            $this->redirect('/login');
        }

        $db   = Database::connection($this->config['db']);
        $user = (new User($db))->findByEmail(trim($_POST['email'] ?? ''));

        if (!$user || !password_verify($_POST['password'] ?? '', $user['password_hash'])) {
            flash('Invalid email or password.', 'error');
            $this->redirect('/login');
        }

        if (($user['is_banned'] ?? 0)) {
            flash('Your account has been suspended. Contact support.', 'error');
            $this->redirect('/login');
        }

        Auth::login($user);
        flash('Welcome back, ' . explode(' ', $user['name'])[0] . '!', 'success');
        $this->redirect($_SESSION['redirect_after_login'] ?? '/dashboard');
    }

    public function register(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            flash('Session expired, please try again.', 'warning');
            $this->redirect('/register');
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            flash('Please fill all fields correctly (password min. 8 chars).', 'error');
            $this->redirect('/register');
        }

        $db        = Database::connection($this->config['db']);
        $userModel = new User($db);

        if ($userModel->findByEmail($email)) {
            flash('An account with that email already exists.', 'error');
            $this->redirect('/register');
        }

        $userId = $userModel->create($name, $email, $password);
        $user   = $userModel->findByEmail($email);
        Auth::login($user);
        flash('Welcome to PromptShare, ' . $name . '!', 'success');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('You have been signed out.', 'info');
        $this->redirect('/');
    }

    // ── Google OAuth ───────────────────────────────────────────

    public function googleRedirect(): void
    {
        $cfg = $this->config['google_oauth'];
        if (!$cfg['enabled'] || empty($cfg['client_id'])) {
            flash('Google sign-in is not configured yet.', 'warning');
            $this->redirect('/login');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = http_build_query([
            'client_id'     => $cfg['client_id'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'offline',
            'prompt'        => 'select_account',
        ]);

        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
        exit;
    }

    public function googleCallback(): void
    {
        $cfg = $this->config['google_oauth'];

        // Validate state to prevent CSRF
        $state = $_GET['state'] ?? '';
        if (!isset($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
            flash('OAuth state mismatch. Please try again.', 'error');
            $this->redirect('/login');
        }
        unset($_SESSION['oauth_state']);

        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            flash('Google sign-in was cancelled or failed.', 'warning');
            $this->redirect('/login');
        }

        // Exchange code for access token
        $tokenData = $this->exchangeGoogleCode($code, $cfg);
        if (!$tokenData || empty($tokenData['access_token'])) {
            flash('Failed to authenticate with Google. Please try again.', 'error');
            $this->redirect('/login');
        }

        // Fetch user profile from Google
        $profile = $this->fetchGoogleProfile($tokenData['access_token']);
        if (!$profile || empty($profile['email'])) {
            flash('Could not retrieve your Google profile. Please try again.', 'error');
            $this->redirect('/login');
        }

        $db        = Database::connection($this->config['db']);
        $userModel = new User($db);

        // Find existing user by Google ID or email
        $user = $userModel->findByGoogleId($profile['sub'])
             ?? $userModel->findByEmail($profile['email']);

        if ($user) {
            if ($user['is_banned'] ?? 0) {
                flash('Your account has been suspended.', 'error');
                $this->redirect('/login');
            }
            // Update Google ID if not set
            if (empty($user['google_id'])) {
                $userModel->updateGoogleId((int)$user['id'], $profile['sub'], $profile['picture'] ?? null);
                $user['google_id'] = $profile['sub'];
            }
            Auth::login($user);
            flash('Welcome back, ' . explode(' ', $user['name'])[0] . '!', 'success');
        } else {
            // New user — create account
            $userId = $userModel->createFromGoogle([
                'name'       => $profile['name'] ?? $profile['email'],
                'email'      => $profile['email'],
                'google_id'  => $profile['sub'],
                'avatar_url' => $profile['picture'] ?? null,
            ]);
            $user = $userModel->findByEmail($profile['email']);
            Auth::login($user);
            flash('Account created! Welcome to PromptShare.', 'success');
        }

        $this->redirect($_SESSION['redirect_after_login'] ?? '/dashboard');
    }

    private function exchangeGoogleCode(string $code, array $cfg): ?array
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'code'          => $code,
                'client_id'     => $cfg['client_id'],
                'client_secret' => $cfg['client_secret'],
                'redirect_uri'  => $cfg['redirect_uri'],
                'grant_type'    => 'authorization_code',
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || !$response) return null;
        return json_decode($response, true);
    }

    private function fetchGoogleProfile(string $accessToken): ?array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || !$response) return null;
        return json_decode($response, true);
    }
}
