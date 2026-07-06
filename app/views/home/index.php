<?php
$q    = $filters['q'] ?? '';
$sort = $filters['sort'] ?? 'newest';
$sortLabels = ['newest'=>'Newest','trending'=>'Trending','most_liked'=>'Top Liked','most_saved'=>'Most Saved','most_viewed'=>'Most Viewed'];
$sortIcons  = ['newest'=>'clock','trending'=>'fire','most_liked'=>'heart-fill','most_saved'=>'bookmark-fill','most_viewed'=>'eye-fill'];
$listAction = $activeCategory ? '/category/' . $activeCategory['slug'] : '/';
$sortFilters = $filters;
unset($sortFilters['cat']);
?>

<?php if (!$activeCategory): ?>
<!-- Hero -->
<section class="hero">
  <span class="hero-badge"><i class="bi bi-lightning-fill"></i> AI Prompt Marketplace</span>
  <h1>Discover &amp; share<br>better AI prompts.</h1>
  <p class="hero-sub">Find top-performing prompts for ChatGPT, Claude, Gemini &amp; more.</p>

  <form method="get" action="/" class="hero-search">
    <input type="text" name="q" placeholder="Search prompts…" value="<?= e($q) ?>" autocomplete="off">
    <button type="submit"><i class="bi bi-search"></i> Search</button>
  </form>

  <div class="hero-stats">
    <div>
      <div class="hero-stat-num"><?= (int)($totalCount ?? 0) ?></div>
      <div class="hero-stat-lbl">Prompts</div>
    </div>
    <div>
      <div class="hero-stat-num"><?= (int)($totalLikes ?? 0) ?></div>
      <div class="hero-stat-lbl">Likes</div>
    </div>
    <div>
      <div class="hero-stat-num"><?= (int)($totalViews ?? 0) ?></div>
      <div class="hero-stat-lbl">Views</div>
    </div>
  </div>
</section>

<!-- Browse by category -->
<div class="section-hd">
  <h2><i class="bi bi-grid-fill" style="color:var(--p);"></i> Browse by Category</h2>
</div>
<div class="cat-grid">
  <?php foreach ($categories as $cat): ?>
    <a href="/category/<?= e($cat['slug']) ?>" class="cat-card cat-<?= e($cat['color']) ?>">
      <span class="cat-card-icon"><i class="bi <?= e($cat['icon']) ?>"></i></span>
      <span class="cat-card-name"><?= e($cat['name']) ?></span>
      <span class="cat-card-count"><?= (int)$cat['prompt_count'] ?> prompts</span>
    </a>
  <?php endforeach; ?>
</div>
<?php else: ?>
<!-- Category header -->
<section class="cat-hero cat-<?= e($activeCategory['color']) ?>">
  <a href="/" class="cat-hero-back"><i class="bi bi-arrow-left"></i> All categories</a>
  <span class="cat-hero-icon"><i class="bi <?= e($activeCategory['icon']) ?>"></i></span>
  <h1><?= e($activeCategory['name']) ?> Prompts</h1>
  <p class="hero-sub">Free, ready-to-use <?= e($activeCategory['name']) ?> prompts for ChatGPT, Claude &amp; Gemini.</p>
  <form method="get" action="<?= e($listAction) ?>" class="hero-search">
    <input type="text" name="q" placeholder="Search in <?= e($activeCategory['name']) ?>…" value="<?= e($q) ?>" autocomplete="off">
    <button type="submit"><i class="bi bi-search"></i> Search</button>
  </form>
</section>

<!-- Other categories -->
<div class="cat-tabs">
  <a href="/" class="cat-tab">All</a>
  <?php foreach ($categories as $cat): ?>
    <a href="/category/<?= e($cat['slug']) ?>" class="cat-tab <?= $cat['slug'] === $activeCategory['slug'] ? 'active' : '' ?>">
      <i class="bi <?= e($cat['icon']) ?>"></i> <?= e($cat['name']) ?>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Sort pills -->
<div class="sort-bar">
  <?php foreach ($sortLabels as $key => $label): ?>
    <a href="<?= e($listAction) ?>?<?= e(http_build_query(array_merge($sortFilters, ['sort' => $key]))) ?>"
       class="sort-pill <?= $sort === $key ? 'active' : '' ?>">
      <i class="bi bi-<?= $sortIcons[$key] ?>"></i>
      <?= $label ?>
    </a>
  <?php endforeach; ?>
</div>

<!-- Section header -->
<div class="section-hd">
  <h2>
    <?php if ($q): ?>
      <i class="bi bi-search"></i> &ldquo;<?= e($q) ?>&rdquo;
    <?php else: ?>
      <i class="bi bi-fire text-danger"></i>
      <?= $sortLabels[$sort] ?? 'Latest' ?> Prompts
    <?php endif; ?>
  </h2>
  <?php if (!empty($prompts)): ?>
    <span class="count-chip"><?= count($prompts) ?> shown</span>
  <?php endif; ?>
</div>

<!-- Grid -->
<div class="prompt-grid" id="prompt-grid">
  <?php foreach ($prompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?>
</div>

<?php if (empty($prompts)): ?>
  <div class="empty">
    <i class="bi bi-search"></i>
    <h4>No prompts found</h4>
    <p><?= $q ? 'Try different keywords or ' : '' ?><a href="<?= e($listAction) ?>">clear filters</a></p>
  </div>
<?php else: ?>
  <div class="load-more-wrap">
    <button id="load-more" class="btn-load-more" data-page="2" data-cat="<?= e($activeCategory['slug'] ?? '') ?>">
      <i class="bi bi-arrow-down-circle"></i> Load more
    </button>
  </div>
<?php endif; ?>
