<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\PromptController;

// ── Public routes ──────────────────────────────────────────────
$router->get('/', [HomeController::class, 'index']);
$router->get('/prompts/load', [HomeController::class, 'loadMore']);

// ── Auth ───────────────────────────────────────────────────────
$router->get('/register',             [AuthController::class, 'showRegister']);
$router->post('/register',            [AuthController::class, 'register']);
$router->get('/login',                [AuthController::class, 'showLogin']);
$router->post('/login',               [AuthController::class, 'login']);
$router->post('/logout',              [AuthController::class, 'logout'], ['auth']);
$router->get('/auth/google',          [AuthController::class, 'googleRedirect']);
$router->get('/auth/google/callback', [AuthController::class, 'googleCallback']);

// ── Prompts ────────────────────────────────────────────────────
$router->get('/prompt/{slug}',        [PromptController::class, 'show']);
$router->get('/prompts/create',       [PromptController::class, 'createForm'],  ['auth']);
$router->post('/prompts',             [PromptController::class, 'store'],       ['auth']);
$router->get('/prompts/{id}/edit',    [PromptController::class, 'editForm'],    ['auth']);
$router->post('/prompts/{id}/edit',   [PromptController::class, 'update'],      ['auth']);
$router->post('/prompts/like',        [PromptController::class, 'like'],        ['auth']);
$router->post('/prompts/save',        [PromptController::class, 'save'],        ['auth']);
$router->post('/prompts/copy',        [PromptController::class, 'copy']);

// ── Dashboard ──────────────────────────────────────────────────
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

// ── Admin ──────────────────────────────────────────────────────
$router->get('/admin',                    [AdminController::class, 'index'],       ['auth', 'admin']);
$router->get('/admin/prompts',            [AdminController::class, 'prompts'],     ['auth', 'admin']);
$router->post('/admin/prompts/approve',   [AdminController::class, 'approve'],     ['auth', 'admin']);
$router->post('/admin/prompts/reject',    [AdminController::class, 'reject'],      ['auth', 'admin']);
$router->post('/admin/prompts/delete',    [AdminController::class, 'deletePrompt'],['auth', 'admin']);
$router->post('/admin/prompts/feature',   [AdminController::class, 'featurePrompt'],['auth', 'admin']);

$router->get('/admin/users',              [AdminController::class, 'users'],       ['auth', 'admin']);
$router->post('/admin/users/ban',         [AdminController::class, 'banUser'],     ['auth', 'admin']);
$router->post('/admin/users/role',        [AdminController::class, 'setUserRole'], ['auth', 'admin']);
$router->post('/admin/users/delete',      [AdminController::class, 'deleteUser'],  ['auth', 'admin']);

$router->get('/admin/analytics',          [AdminController::class, 'analytics'],   ['auth', 'admin']);

$router->get('/admin/settings',           [AdminController::class, 'settings'],    ['auth', 'admin']);
$router->post('/admin/settings/save',     [AdminController::class, 'saveSettings'],['auth', 'admin']);

$router->get('/admin/stats.json',         [AdminController::class, 'statsJson'],   ['auth', 'admin']);
