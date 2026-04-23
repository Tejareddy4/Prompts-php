<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\PromptController;
use App\Controllers\UserController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/prompts/load', [HomeController::class, 'loadMore']);

$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);
$router->get('/auth/google', [AuthController::class, 'googleRedirect']);
$router->get('/auth/google/callback', [AuthController::class, 'googleCallback']);

$router->get('/prompt/{slug}', [PromptController::class, 'show']);
$router->get('/prompts/create', [PromptController::class, 'createForm'], ['auth']);
$router->post('/prompts', [PromptController::class, 'store'], ['auth']);
$router->get('/prompts/{id}/edit', [PromptController::class, 'editForm'], ['auth']);
$router->post('/prompts/{id}/edit', [PromptController::class, 'update'], ['auth']);
$router->post('/prompts/like', [PromptController::class, 'like'], ['auth']);
$router->post('/prompts/save', [PromptController::class, 'save'], ['auth']);
$router->post('/prompts/copy', [PromptController::class, 'copy']);

$router->get('/u/{username}', [UserController::class, 'profile']);

$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

$router->get('/admin', [AdminController::class, 'index'], ['auth', 'admin']);
$router->post('/admin/prompts/approve', [AdminController::class, 'approve'], ['auth', 'admin']);
$router->post('/admin/prompts/reject', [AdminController::class, 'reject'], ['auth', 'admin']);
$router->post('/admin/prompts/delete', [AdminController::class, 'delete'], ['auth', 'admin']);
