<!-- Hero -->
<section class="hero-panel mb-4">
  <div class="row align-items-center">
    <div class="col-lg-8">
      <span class="hero-tag"><i class="bi bi-lightning-fill me-1"></i> AI Prompt Marketplace</span>
      <h1>Discover, save &amp; ship<br>better AI prompts faster.</h1>
      <p class="hero-sub">Browse <?= (int)($totalCount ?? 0) ?> community-curated prompts for ChatGPT, Claude, Gemini &amp; more.</p>
      <div class="hero-stats">
        <div class="hero-stat">
          <span class="hero-stat-num"><?= (int)($totalCount ?? 0) ?></span>
          <span class="hero-stat-label">Prompts</span>
        </div>
        <div class="hero-stat">
          <span class="hero-stat-num"><?= (int)($totalLikes ?? 0) ?></span>
          <span class="hero-stat-label">Likes</span>
        </div>
        <div class="hero-stat">
          <span class="hero-stat-num"><?= (int)($totalViews ?? 0) ?></span>
          <span class="hero-stat-label">Views</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Filters -->
<section class="filter-card p-3 mb-4">
  <form method="get" action="/" class="row g-2 align-items-end">
    <div class="col-12 col-md-6">
      <label for="q" class="form-label">Search</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
        <input id="q" name="q" class="form-control border-start-0"
               placeholder="Search prompts by title, description, or content"
               value="<?= e($filters['q'] ?? '') ?>">
      </div>
    </div>
    <div class="col-12 col-md-4">
      <label for="sort" class="form-label">Sort by</label>
      <select id="sort" name="sort" class="form-select">
        <option value="newest"     <?= ($filters['sort'] ?? 'newest') === 'newest'     ? 'selected' : '' ?>>Newest first</option>
        <option value="most_liked" <?= ($filters['sort'] ?? '') === 'most_liked' ? 'selected' : '' ?>>Most liked</option>
        <option value="most_saved" <?= ($filters['sort'] ?? '') === 'most_saved' ? 'selected' : '' ?>>Most saved</option>
        <option value="most_viewed"<?= ($filters['sort'] ?? '') === 'most_viewed'? 'selected' : '' ?>>Most viewed</option>
      </select>
    </div>
    <div class="col-12 col-md-2 d-grid">
      <button class="btn btn-primary" type="submit">Apply</button>
    </div>
  </form>
</section>

<!-- Grid -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <h2 class="section-heading mb-0">
    <i class="bi bi-fire text-danger"></i>
    <?php if (!empty($filters['q'])): ?>
      Results for &ldquo;<?= e($filters['q']) ?>&rdquo;
    <?php elseif (($filters['sort'] ?? 'newest') !== 'newest'): ?>
      Top Prompts
    <?php else: ?>
      Latest Prompts
    <?php endif; ?>
  </h2>
  <?php if (!empty($prompts)): ?>
    <span class="text-muted small"><?= count($prompts) ?> shown</span>
  <?php endif; ?>
</div>

<div id="prompt-grid" class="row g-3">
  <?php foreach ($prompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?>
</div>

<?php if (empty($prompts)): ?>
  <div class="empty-state py-5">
    <div><i class="bi bi-search d-block"></i></div>
    <h5 class="mt-2">No prompts found</h5>
    <p class="small">Try different keywords or <a href="/">clear your filters</a>.</p>
  </div>
<?php else: ?>
  <div class="text-center mt-4">
    <button id="load-more" class="btn btn-outline-primary" data-page="2">
      <i class="bi bi-arrow-down-circle me-1"></i> Load more prompts
    </button>
  </div>
<?php endif; ?>
