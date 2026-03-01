<?php $user = auth_user(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? config('app.name')) ?> | <?= e(config('app.name')) ?></title>
    <meta name="description" content="<?= e($metaDescription ?? 'Share and discover high-performing prompts.') ?>">
    <meta property="og:title" content="<?= e($pageTitle ?? config('app.name')) ?>">
    <meta property="og:description" content="<?= e($metaDescription ?? 'Discover and share prompts.') ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($prompt['image_path'])): ?>
        <meta property="og:image" content="<?= e(config('app.base_url') . $prompt['image_path']) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top nav-glass">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="/">PromptShare</a>
        <div class="ms-auto d-flex gap-2 align-items-center">
            <a class="btn btn-outline-light btn-sm" href="/">Home</a>
            <a class="btn btn-primary btn-sm" href="/prompts/create">Submit</a>
            <?php if ($user): ?>
                <a class="btn btn-outline-info btn-sm" href="/dashboard">Frontend Dashboard</a>
                <?php if (($user['role_name'] ?? '') === 'super_admin'): ?>
                    <a class="btn btn-warning btn-sm" href="/admin">Admin Dashboard</a>
                <?php endif; ?>
                <form method="post" action="/logout" class="d-inline"><?= csrf_field() ?><button class="btn btn-outline-danger btn-sm">Logout</button></form>
            <?php else: ?>
                <a class="btn btn-outline-light btn-sm" href="/login">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-info"><?= e($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
    <?php endif; ?>
    <?php require $viewPath; ?>
</main>

<script>window.CSRF_TOKEN = '<?= e(App\Core\Csrf::token()) ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
