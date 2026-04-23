<?php $user = auth_user(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? config('app.name')) ?> | <?= e(config('app.name')) ?></title>
  <meta name="description" content="<?= e($metaDescription ?? 'Share and discover high-performing AI prompts.') ?>">
  <meta property="og:title" content="<?= e($pageTitle ?? config('app.name')) ?>">
  <meta property="og:description" content="<?= e($metaDescription ?? 'Discover and share prompts.') ?>">
  <meta property="og:type" content="website">
  <?php if (!empty($prompt['image_path'])): ?>
    <meta property="og:image" content="<?= e(config('app.base_url') . $prompt['image_path']) ?>">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top site-nav">
  <div class="container">
    <a class="navbar-brand" href="/">
      <span class="brand-icon"><i class="bi bi-lightning-fill"></i></span>
      PromptShare
    </a>

    <div class="ms-auto d-flex gap-2 align-items-center">
      <a class="btn btn-outline-light nav-btn" href="/">Explore</a>

      <?php if ($user): ?>
        <a class="btn btn-primary nav-btn" href="/prompts/create">
          <i class="bi bi-plus-lg"></i> Submit
        </a>
        <div class="dropdown">
          <button class="btn btn-outline-light nav-btn dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="avatar-xs"><?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?></span>
            <?= e(explode(' ', $user['name'])[0]) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <span class="dropdown-item-text small text-secondary px-3 pt-1"><?= e($user['email'] ?? '') ?></span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-grid me-2"></i>My Dashboard</a></li>
            <li><a class="dropdown-item" href="/prompts/create"><i class="bi bi-plus-circle me-2"></i>Submit Prompt</a></li>
            <?php if (($user['role_name'] ?? '') === 'super_admin'): ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-warning" href="/admin"><i class="bi bi-shield-check me-2"></i>Admin Panel</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="post" action="/logout" class="px-2">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                  <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
              </form>
            </li>
          </ul>
        </div>
      <?php else: ?>
        <a class="btn btn-outline-light nav-btn" href="/login">Login</a>
        <a class="btn btn-primary nav-btn" href="/register">Sign up free</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container py-4">
  <?php $flash = flash_get(); if ($flash): ?>
    <div class="flash-bar flash-<?= e($flash['type']) ?> mb-4">
      <?php
        $icons = ['success' => 'check-circle-fill', 'error' => 'x-circle-fill', 'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'];
        $icon = $icons[$flash['type']] ?? 'info-circle-fill';
      ?>
      <i class="bi bi-<?= $icon ?>"></i>
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <?php require $viewPath; ?>
</main>

<footer class="site-footer">
  <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
    <span>© <?= date('Y') ?> <strong style="color:rgba(255,255,255,.7)">PromptShare</strong> &mdash; Discover &amp; share AI prompts</span>
    <div class="d-flex gap-3">
      <a href="/">Explore</a>
      <a href="/prompts/create">Submit</a>
    </div>
  </div>
</footer>

<script>window.CSRF_TOKEN = '<?= e(App\Core\Csrf::token()) ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
