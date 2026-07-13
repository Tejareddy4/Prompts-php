<?php
$user = auth_user();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

$privatePrefixes = ['/admin', '/dashboard', '/login', '/register', '/prompts/create', '/auth/'];
$isPrivatePage = false;
foreach ($privatePrefixes as $prefix) {
    if (str_starts_with($currentPath, $prefix)) { $isPrivatePage = true; break; }
}
$isPrivatePage = $isPrivatePage || preg_match('#^/prompts/\d+/edit$#', $currentPath) === 1;

$appName = config('app.name');
$metaTitle = (!empty($pageTitle) && $pageTitle !== $appName)
    ? e($pageTitle) . ' | ' . e($appName)
    : e($appName);
$metaDesc = $metaDescription ?? 'Discover and share high-performing AI prompts for ChatGPT, Claude, Gemini & more.';
$canonicalUrl = $canonical ?? rtrim(config('app.base_url'), '/') . $currentPath;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#7C3AED">
  <title><?= $metaTitle ?></title>
  <meta name="description" content="<?= e($metaDesc) ?>">
  <meta name="robots" content="<?= $isPrivatePage ? 'noindex, nofollow' : 'index, follow' ?>">
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">

  <meta property="og:site_name" content="<?= e(config('app.name')) ?>">
  <meta property="og:type" content="<?= !empty($prompt) ? 'article' : 'website' ?>">
  <meta property="og:title" content="<?= $metaTitle ?>">
  <meta property="og:description" content="<?= e($metaDesc) ?>">
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
  <?php if (!empty($prompt['image_path'])): ?>
    <meta property="og:image" content="<?= e(rtrim(config('app.base_url'), '/') . $prompt['image_path']) ?>">
    <meta name="twitter:card" content="summary_large_image">
  <?php else: ?>
    <meta name="twitter:card" content="summary">
  <?php endif; ?>
  <meta name="twitter:title" content="<?= $metaTitle ?>">
  <meta name="twitter:description" content="<?= e($metaDesc) ?>">

  <?php if (!$isPrivatePage && $currentPath === '/'): ?>
  <script type="application/ld+json">
  <?= json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'WebSite',
      'name' => config('app.name'),
      'url' => rtrim(config('app.base_url'), '/') . '/',
      'potentialAction' => [
          '@type' => 'SearchAction',
          'target' => rtrim(config('app.base_url'), '/') . '/?q={search_term_string}',
          'query-input' => 'required name=search_term_string',
      ],
  ]) ?>
  </script>
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
  <?php if (str_starts_with($currentPath, '/admin')): ?>
  <link href="/assets/css/admin.css" rel="stylesheet">
  <?php endif; ?>
</head>
<body>

<!-- ── Top navbar ─────────────────────────────────────────────── -->
<?php if (!str_starts_with($currentPath, '/admin')): ?>
<nav class="site-nav">
  <div class="container">
    <a class="brand" href="/">
      <span class="brand-icon"><i class="bi bi-lightning-fill"></i></span>
      PromptShare
    </a>

    <!-- Desktop nav -->
    <div class="nav-desktop">
      <a href="/" class="nbtn nbtn-ghost">Explore</a>
      <?php if ($user): ?>
        <a href="/prompts/create" class="nbtn nbtn-outline">
          <i class="bi bi-plus-lg"></i> Submit
        </a>
        <div class="dropdown">
          <button class="nbtn nbtn-ghost dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
                  style="display:flex;align-items:center;gap:0.4rem;">
            <span class="avatar avatar-xs"><?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?></span>
            <?= e(explode(' ', $user['name'])[0]) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-label"><?= e($user['email'] ?? '') ?></span></li>
            <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-grid-1x2"></i> Dashboard</a></li>
            <li><a class="dropdown-item" href="/prompts/create"><i class="bi bi-plus-circle"></i> Submit prompt</a></li>
            <?php if (($user['role_name'] ?? '') === 'super_admin'): ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="/admin" style="color:var(--p)"><i class="bi bi-shield-check"></i> Admin panel</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="post" action="/logout" style="padding:0.25rem 0.375rem;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger-outline btn-full" style="border-radius:var(--r-xs);">
                  <i class="bi bi-box-arrow-right"></i> Logout
                </button>
              </form>
            </li>
          </ul>
        </div>
      <?php else: ?>
        <a href="/login" class="nbtn nbtn-ghost">Login</a>
        <a href="/register" class="nbtn nbtn-primary">Sign up free</a>
      <?php endif; ?>
    </div>

    <!-- Mobile: just submit or sign up -->
    <div class="nav-mobile-right">
      <?php if ($user): ?>
        <a href="/prompts/create" class="nbtn nbtn-primary" style="height:32px;padding:0 0.75rem;font-size:0.75rem;">
          <i class="bi bi-plus-lg"></i> Submit
        </a>
      <?php else: ?>
        <a href="/register" class="nbtn nbtn-primary" style="height:32px;padding:0 0.75rem;font-size:0.75rem;">Sign up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<?php endif; ?>

<!-- ── Page content ───────────────────────────────────────────── -->
<?php if (str_starts_with($currentPath, '/admin')): ?>
<main style="min-height:100vh;">
  <?php $flash = flash_get(); if ($flash): ?>
    <?php $icons = ['success'=>'check-circle-fill','error'=>'x-circle-fill','warning'=>'exclamation-triangle-fill','info'=>'info-circle-fill']; $icon = $icons[$flash['type']] ?? 'info-circle-fill'; ?>
    <div class="flash flash-<?= e($flash['type']) ?>" style="margin:1rem 1.5rem 0;">
      <i class="bi bi-<?= $icon ?>"></i> <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>
  <?php require $viewPath; ?>
</main>
<?php else: ?>
<main class="site-main">
  <div class="container">
    <?php $flash = flash_get(); if ($flash): ?>
      <?php
        $icons = ['success'=>'check-circle-fill','error'=>'x-circle-fill','warning'=>'exclamation-triangle-fill','info'=>'info-circle-fill'];
        $icon = $icons[$flash['type']] ?? 'info-circle-fill';
      ?>
      <div class="flash flash-<?= e($flash['type']) ?>">
        <i class="bi bi-<?= $icon ?>"></i>
        <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>
    <?php require $viewPath; ?>
  </div>
</main>
<?php endif; ?>

<!-- ── Footer ─────────────────────────────────────────────────── -->
<?php if (!str_starts_with($currentPath, '/admin')):
  $footerCats = all_categories();
?>
<footer class="footer-rich">
  <div class="container">
    <div class="footer-cols">
      <div class="footer-col">
        <div class="footer-brand-row">
          <span class="brand-icon"><i class="bi bi-lightning-fill"></i></span>
          <span class="footer-brand-name">PromptShare</span>
        </div>
        <p class="footer-tagline">A free, curated library of high-performing AI prompts for ChatGPT, Claude &amp; Gemini. Copy any prompt in one click.</p>
      </div>

      <div class="footer-col">
        <h4>Categories</h4>
        <ul>
          <?php foreach (array_slice($footerCats, 0, 6) as $fc): ?>
            <li><a href="/category/<?= e($fc['slug']) ?>"><?= e($fc['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="footer-col">
        <h4>PromptShare</h4>
        <ul>
          <li><a href="/">Explore prompts</a></li>
          <li><a href="/prompts/create">Submit a prompt</a></li>
          <?php if ($user): ?>
            <li><a href="/dashboard">Dashboard</a></li>
          <?php else: ?>
            <li><a href="/register">Create account</a></li>
            <li><a href="/login">Sign in</a></li>
          <?php endif; ?>
          <li><a href="/sitemap.xml">Sitemap</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> PromptShare &mdash; Part of <a href="https://xpanda.in">Xpanda.in</a></span>
      <span>Made for AI creators</span>
    </div>
  </div>
</footer>

<!-- ── Bottom nav (mobile only) ──────────────────────────────── -->
<nav class="bottom-nav">
  <a href="/" class="bnav-item <?= $currentPath === '/' ? 'active' : '' ?>">
    <i class="bi bi-<?= $currentPath === '/' ? 'house-fill' : 'house' ?>"></i>
    Home
  </a>

  <?php if ($user): ?>
    <a href="/prompts/create" class="bnav-submit">
      <div class="bnav-submit-pill"><i class="bi bi-plus-lg"></i></div>
      <span>Submit</span>
    </a>
    <a href="/dashboard" class="bnav-item <?= str_starts_with($currentPath, '/dashboard') ? 'active' : '' ?>">
      <i class="bi bi-<?= str_starts_with($currentPath, '/dashboard') ? 'grid-fill' : 'grid' ?>"></i>
      Dashboard
    </a>
    <?php if (($user['role_name'] ?? '') === 'super_admin'): ?>
      <a href="/admin" class="bnav-item <?= str_starts_with($currentPath, '/admin') ? 'active' : '' ?>">
        <i class="bi bi-shield<?= str_starts_with($currentPath, '/admin') ? '-fill' : '' ?>"></i>
        Admin
      </a>
    <?php else: ?>
      <div class="dropdown" style="flex:1;">
        <button class="bnav-item w-100" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%;">
          <span class="avatar avatar-xs"><?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?></span>
          <?= e(explode(' ', $user['name'])[0]) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-label"><?= e($user['email'] ?? '') ?></span></li>
          <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-grid-1x2"></i> Dashboard</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="post" action="/logout" style="padding:0.25rem 0.375rem;">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm btn-danger-outline btn-full" style="border-radius:var(--r-xs);">
                <i class="bi bi-box-arrow-right"></i> Logout
              </button>
            </form>
          </li>
        </ul>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <a href="/login" class="bnav-item <?= $currentPath === '/login' ? 'active' : '' ?>">
      <i class="bi bi-person<?= $currentPath === '/login' ? '-fill' : '' ?>"></i>
      Login
    </a>
    <a href="/register" class="bnav-item <?= $currentPath === '/register' ? 'active' : '' ?>">
      <i class="bi bi-person-plus<?= $currentPath === '/register' ? '-fill' : '' ?>"></i>
      Sign up
    </a>
  <?php endif; ?>
</nav>
<?php endif; ?>

<script>window.CSRF_TOKEN = '<?= e(App\Core\Csrf::token()) ?>';</script>
<script src="/assets/js/app.js" defer></script>
</body>
</html>
