<?php
$adminPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/admin';
$navItems = [
  ['href' => '/admin',            'icon' => 'bi-speedometer2',   'label' => 'Dashboard'],
  ['href' => '/admin/prompts',    'icon' => 'bi-file-text',       'label' => 'Prompts'],
  ['href' => '/admin/users',      'icon' => 'bi-people',          'label' => 'Users'],
  ['href' => '/admin/analytics',  'icon' => 'bi-bar-chart-line',  'label' => 'Analytics'],
  ['href' => '/admin/settings',   'icon' => 'bi-gear',            'label' => 'Settings'],
];
?>
<aside class="adm-sidebar">
  <div class="adm-sidebar-brand">
    <span class="brand-icon"><i class="bi bi-lightning-fill"></i></span>
    <span class="adm-brand-text">PromptShare</span>
    <span class="adm-admin-badge">Admin</span>
  </div>

  <nav class="adm-nav">
    <?php foreach ($navItems as $item): ?>
      <?php
        $isActive = $adminPath === $item['href']
          || ($item['href'] !== '/admin' && str_starts_with($adminPath, $item['href']));
      ?>
      <a href="<?= e($item['href']) ?>" class="adm-nav-item <?= $isActive ? 'active' : '' ?>">
        <i class="bi <?= $item['icon'] ?>"></i>
        <span><?= e($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="adm-sidebar-footer">
    <a href="/" class="adm-nav-item">
      <i class="bi bi-arrow-left-circle"></i>
      <span>Back to site</span>
    </a>
    <form method="post" action="/logout">
      <?= csrf_field() ?>
      <button class="adm-nav-item adm-logout">
        <i class="bi bi-box-arrow-left"></i>
        <span>Sign out</span>
      </button>
    </form>
  </div>
</aside>
