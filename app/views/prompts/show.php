<nav class="show-back" aria-label="Breadcrumb">
  <a href="/"><i class="bi bi-arrow-left"></i> Explore</a>
  <?php if (!empty($prompt['category_slug'])): ?>
    <span class="sep">/</span>
    <a href="/category/<?= e($prompt['category_slug']) ?>"><?= e($prompt['category_name']) ?></a>
  <?php endif; ?>
</nav>

<!-- Page header -->
<header class="show-head">
  <?php if (!empty($prompt['category_slug'])): ?>
    <?= category_badge($prompt, 'lg') ?>
  <?php endif; ?>
  <h1><?= e($prompt['title']) ?></h1>
  <div class="show-meta">
    <span class="avatar avatar-xs"><?= strtoupper(substr($prompt['author'] ?? 'U', 0, 2)) ?></span>
    <?php if (!empty($prompt['author_username'])): ?>
      <a href="/u/<?= e($prompt['author_username']) ?>"><?= e($prompt['author']) ?></a>
    <?php else: ?>
      <span class="show-meta-author"><?= e($prompt['author']) ?></span>
    <?php endif; ?>
    <span class="dot">·</span>
    <span><?= date('M j, Y', strtotime($prompt['created_at'])) ?></span>
    <span class="dot">·</span>
    <span><i class="bi bi-eye"></i> <?= number_format((int)$prompt['views_count']) ?> views</span>
  </div>
</header>

<div class="show-layout">

  <!-- Main column -->
  <div class="show-main">

    <?php if (!empty($prompt['image_path'])): ?>
      <figure class="show-card show-media">
        <img src="<?= e($prompt['image_path']) ?>" alt="<?= e($prompt['title']) ?>">
      </figure>
    <?php endif; ?>

    <?php if (!empty($prompt['description'])): ?>
      <div class="show-card">
        <div class="show-card-head">
          <h2><i class="bi bi-info-circle"></i> About this prompt</h2>
        </div>
        <div class="show-card-body show-desc">
          <?= nl2br(e($prompt['description'])) ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Prompt text -->
    <div class="show-card">
      <div class="show-card-head">
        <h2><i class="bi bi-code-slash"></i> Prompt</h2>
        <span class="show-wordcount"><?= number_format(str_word_count($prompt['prompt_text'])) ?> words</span>
        <button class="btn btn-sm btn-outline" id="copy-btn" onclick="copyPrompt(this)">
          <i class="bi bi-clipboard"></i> Copy
        </button>
      </div>
      <div class="show-card-body">
        <div class="prompt-code" id="prompt-text"><?= e($prompt['prompt_text']) ?></div>
      </div>
    </div>

    <div class="show-card">
      <div class="show-card-head">
        <h2><i class="bi bi-lightbulb"></i> How to use this prompt</h2>
      </div>
      <div class="show-card-body show-desc">
        Copy the prompt above and paste it into ChatGPT, Claude or Google Gemini. If it asks for a
        reference photo, upload a clear, well-lit, high-resolution image for the best results.
      </div>
    </div>

    <?= ad_slot('prompt_top', 'ad-inarticle', 'fluid') ?>

  </div>

  <!-- Actions: sticky bar on mobile, sidebar card on desktop -->
  <div class="show-actions" data-prompt-id="<?= (int)$prompt['id'] ?>">
    <button class="btn <?= !empty($prompt['is_liked']) ? 'btn-danger-fill' : 'btn-danger-outline' ?> js-like"
            aria-pressed="<?= !empty($prompt['is_liked']) ? 'true' : 'false' ?>">
      <i class="bi bi-heart<?= !empty($prompt['is_liked']) ? '-fill' : '' ?>"></i>
      <span><?= !empty($prompt['is_liked']) ? 'Liked' : 'Like' ?></span>
      <span class="count"><?= (int)$prompt['likes_count'] ?></span>
    </button>
    <button class="btn <?= !empty($prompt['is_saved']) ? 'btn-save-fill' : 'btn-save-outline' ?> js-save"
            aria-pressed="<?= !empty($prompt['is_saved']) ? 'true' : 'false' ?>">
      <i class="bi bi-bookmark<?= !empty($prompt['is_saved']) ? '-fill' : '' ?>"></i>
      <span><?= !empty($prompt['is_saved']) ? 'Saved' : 'Save' ?></span>
      <span class="count"><?= (int)$prompt['saves_count'] ?></span>
    </button>
    <button class="btn btn-primary js-copy act-copy">
      <i class="bi bi-clipboard"></i>
      <span>Copy<span class="act-copy-ext"> Prompt</span></span>
      <span class="count"><?= (int)$prompt['copies_count'] ?></span>
    </button>
  </div>

  <!-- Sidebar -->
  <aside class="show-side">

    <div class="show-card">
      <div class="show-card-head">
        <h2><i class="bi bi-bar-chart-line"></i> Stats</h2>
      </div>
      <div class="show-card-body">
        <div class="stat-grid">
          <div class="stat-tile">
            <div class="val"><i class="bi bi-heart-fill" style="color:#EF4444;"></i> <?= number_format((int)$prompt['likes_count']) ?></div>
            <div class="lbl">Likes</div>
          </div>
          <div class="stat-tile">
            <div class="val"><i class="bi bi-bookmark-fill" style="color:#3B82F6;"></i> <?= number_format((int)$prompt['saves_count']) ?></div>
            <div class="lbl">Saves</div>
          </div>
          <div class="stat-tile">
            <div class="val"><i class="bi bi-clipboard-fill" style="color:#8B5CF6;"></i> <?= number_format((int)$prompt['copies_count']) ?></div>
            <div class="lbl">Copies</div>
          </div>
          <div class="stat-tile">
            <div class="val"><i class="bi bi-eye-fill" style="color:#10B981;"></i> <?= number_format((int)$prompt['views_count']) ?></div>
            <div class="lbl">Views</div>
          </div>
        </div>
      </div>
    </div>

    <?php if (!empty($prompt['author_username'])): ?>
      <a href="/u/<?= e($prompt['author_username']) ?>" class="show-card author-card">
        <span class="avatar avatar-md"><?= strtoupper(substr($prompt['author'] ?? 'U', 0, 2)) ?></span>
        <span class="author-card-info">
          <span class="nm"><?= e($prompt['author']) ?></span>
          <span class="hd">@<?= e($prompt['author_username']) ?></span>
        </span>
        <i class="bi bi-chevron-right chev"></i>
      </a>
    <?php else: ?>
      <div class="show-card author-card">
        <span class="avatar avatar-md"><?= strtoupper(substr($prompt['author'] ?? 'U', 0, 2)) ?></span>
        <span class="author-card-info">
          <span class="nm"><?= e($prompt['author']) ?></span>
          <span class="hd">Creator</span>
        </span>
      </div>
    <?php endif; ?>

    <?= ad_slot('prompt_side', 'ad-square') ?>

  </aside>

</div>

<?php if (!empty($related)): ?>
  <div class="show-related">
    <div class="section-hd">
      <h2><i class="bi bi-collection" style="color:var(--p);"></i> Related Prompts</h2>
    </div>
    <div class="related-grid">
      <?php foreach ($related as $r): ?>
        <a href="/prompt/<?= e($r['slug']) ?>" class="related-card">
          <?= category_badge($r) ?>
          <span class="related-card-title"><?= e($r['title']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<?php
$base = rtrim(config('app.base_url'), '/');
$crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base . '/']];
if (!empty($prompt['category_slug'])) {
    $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $prompt['category_name'] . ' Prompts', 'item' => $base . '/category/' . $prompt['category_slug']];
}
$crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $prompt['title'], 'item' => $canonical ?? ($base . '/prompt/' . $prompt['slug'])];
?>
<script type="application/ld+json">
<?= json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $crumbs], JSON_UNESCAPED_SLASHES) ?>
</script>

<script type="application/ld+json">
<?= json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'CreativeWork',
  'name' => $prompt['title'],
  'description' => substr($prompt['description'] !== '' ? $prompt['description'] : $prompt['prompt_text'], 0, 300),
  'dateCreated' => date('c', strtotime($prompt['created_at'])),
  'dateModified' => date('c', strtotime($prompt['updated_at'])),
  'author' => ['@type' => 'Person', 'name' => $prompt['author']],
  'keywords' => $prompt['category_name'] ?? null,
  'url' => $canonical ?? null,
], JSON_UNESCAPED_SLASHES) ?>
</script>

<script>
function copyPrompt(btn) {
  const text = document.getElementById('prompt-text').innerText;
  navigator.clipboard.writeText(text).then(() => {
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
    setTimeout(() => { btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
  });
}
</script>
