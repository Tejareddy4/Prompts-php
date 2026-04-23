<?php
$q    = $filters['q'] ?? '';
$sort = $filters['sort'] ?? 'newest';
$sortLabels = ['newest'=>'Newest','most_liked'=>'Top Liked','most_saved'=>'Most Saved','most_viewed'=>'Most Viewed','trending'=>'Trending'];
?>

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

<!-- Sort pills -->
<div class="sort-bar">
  <?php foreach ($sortLabels as $key => $label): ?>
    <a href="?<?= e(http_build_query(array_merge($filters, ['sort' => $key]))) ?>"
       class="sort-pill <?= $sort === $key ? 'active' : '' ?>">
      <?php
        $icons = ['newest'=>'clock','most_liked'=>'heart-fill','most_saved'=>'bookmark-fill','most_viewed'=>'eye-fill','trending'=>'fire'];
        echo '<i class="bi bi-' . $icons[$key] . '"></i> ';
      ?>
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
    <p><?= $q ? 'Try different keywords or ' : '' ?><a href="/">clear filters</a></p>
  </div>
<?php else: ?>
  <div class="load-more-wrap">
    <button id="load-more" class="btn-load-more" data-page="2">
      <i class="bi bi-arrow-down-circle"></i> Load more
    </button>
  </div>
<?php endif; ?>
