<?php
$q    = $filters['q'] ?? '';
$sort = $filters['sort'] ?? 'for_you';
$sortLabels = ['for_you'=>'For You','newest'=>'Newest','trending'=>'Trending','most_liked'=>'Top Liked','most_saved'=>'Most Saved','most_viewed'=>'Most Viewed'];
$sortIcons  = ['for_you'=>'stars','newest'=>'clock','trending'=>'fire','most_liked'=>'heart-fill','most_saved'=>'bookmark-fill','most_viewed'=>'eye-fill'];
$listAction = $activeCategory ? '/category/' . $activeCategory['slug'] : '/';
$sortFilters = $filters;
unset($sortFilters['cat'], $sortFilters['top_cats']);
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

<?php if (!empty($slider)): ?>
<!-- Featured slider -->
<section class="hslider" id="hslider" aria-label="Featured prompts">
  <?php foreach ($slider as $i => $s): ?>
    <a href="/prompt/<?= e($s['slug']) ?>" class="hslide<?= $i === 0 ? ' active' : '' ?>" <?= $i === 0 ? '' : 'tabindex="-1"' ?>>
      <img src="<?= e($s['image_path']) ?>" alt="<?= e($s['title']) ?>"
           <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
      <div class="hslide-overlay">
        <?php if (!empty($s['category_slug'])): ?>
          <span class="cat-badge cat-<?= e($s['category_color']) ?>">
            <i class="bi <?= e($s['category_icon']) ?>"></i> <?= e($s['category_name']) ?>
          </span>
        <?php endif; ?>
        <h2 class="hslide-title"><?= e($s['title']) ?></h2>
        <?php if (!empty($s['description'])): ?>
          <p class="hslide-desc"><?= e(mb_strimwidth($s['description'], 0, 110, '…')) ?></p>
        <?php endif; ?>
        <div class="hslide-meta">
          <span><i class="bi bi-heart-fill"></i> <?= (int)$s['likes_count'] ?></span>
          <span><i class="bi bi-eye-fill"></i> <?= (int)$s['views_count'] ?></span>
          <span class="hslide-cta">View prompt <i class="bi bi-arrow-right"></i></span>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
  <button type="button" class="hslider-arrow hslider-prev" aria-label="Previous slide"><i class="bi bi-chevron-left"></i></button>
  <button type="button" class="hslider-arrow hslider-next" aria-label="Next slide"><i class="bi bi-chevron-right"></i></button>
  <div class="hslider-dots">
    <?php foreach ($slider as $i => $s): ?>
      <button type="button" class="hslider-dot<?= $i === 0 ? ' active' : '' ?>" data-slide="<?= $i ?>"
              aria-label="Go to slide <?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($topPicks)): ?>
<!-- Top picks -->
<div class="section-hd">
  <h2><i class="bi bi-trophy-fill" style="color:#F59E0B;"></i> Top Picks</h2>
</div>
<div class="top-picks">
  <?php foreach ($topPicks as $rank => $item): ?>
    <div class="top-pick">
      <span class="tp-rank tp-rank-<?= $rank + 1 ?>">#<?= $rank + 1 ?></span>
      <?php require __DIR__ . '/../partials/prompt-card.php'; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Browse by category -->
<div class="section-hd">
  <h2><i class="bi bi-grid-fill" style="color:var(--p);"></i> Browse by Category</h2>
</div>
<div class="cat-grid">
  <?php foreach ($categories as $cat): ?>
    <a href="/category/<?= e($cat['slug']) ?>" class="cat-card cat-<?= e($cat['color']) ?>">
      <span class="cat-card-icon"><i class="bi <?= e($cat['icon']) ?>"></i></span>
      <span class="cat-card-name"><?= e($cat['name']) ?></span>
      <span class="cat-card-count"><?= (int)$cat['prompt_count'] ?> prompt<?= (int)$cat['prompt_count'] === 1 ? '' : 's' ?></span>
    </a>
  <?php endforeach; ?>
</div>
<?php else: ?>
<!-- Category header -->
<script type="application/ld+json">
<?= json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(config('app.base_url'), '/') . '/'],
    ['@type' => 'ListItem', 'position' => 2, 'name' => $activeCategory['name'] . ' Prompts', 'item' => rtrim(config('app.base_url'), '/') . '/category/' . $activeCategory['slug']],
  ],
], JSON_UNESCAPED_SLASHES) ?>
</script>
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
<?php elseif (count($prompts) === 12): ?>
  <div class="load-more-wrap">
    <button id="load-more" class="btn-load-more" data-page="2" data-cat="<?= e($activeCategory['slug'] ?? '') ?>">
      <i class="bi bi-arrow-down-circle"></i> Load more
    </button>
  </div>
<?php endif; ?>

<?php if (!$activeCategory && !$q):
  $faqs = [
    ['q' => 'What is PromptShare?', 'a' => 'PromptShare is a free library of high-performing AI prompts for ChatGPT, Claude, Gemini and other AI tools. Browse by category, copy any prompt in one click, and use it instantly.'],
    ['q' => 'Are the prompts free to use?', 'a' => 'Yes. Every prompt on PromptShare is 100% free to copy and use. Just click "Copy prompt" on any card or prompt page and paste it into your favourite AI tool.'],
    ['q' => 'How do I use a prompt?', 'a' => 'Find a prompt you like, hit the copy button, then paste it into ChatGPT, Claude or Gemini. If a prompt asks for an image or details, add yours after pasting for best results.'],
    ['q' => 'Can I submit my own prompts?', 'a' => 'Absolutely. Create a free account, click Submit, choose a category and share your prompt. Once approved it appears in the public library for everyone to discover.'],
    ['q' => 'Which AI models do these prompts work with?', 'a' => 'Most prompts are model-agnostic and work well with ChatGPT (GPT-4/5), Claude, Google Gemini, and similar large language models. Image prompts work with Gemini, Midjourney and other image generators.'],
  ];
?>
<section class="faq-section">
  <div class="section-hd">
    <h2><i class="bi bi-patch-question" style="color:var(--p);"></i> Frequently Asked Questions</h2>
  </div>
  <div class="faq-list">
    <?php foreach ($faqs as $i => $f): ?>
      <details class="faq-item" <?= $i === 0 ? 'open' : '' ?>>
        <summary><?= e($f['q']) ?><i class="bi bi-chevron-down faq-chevron"></i></summary>
        <div class="faq-answer"><?= e($f['a']) ?></div>
      </details>
    <?php endforeach; ?>
  </div>
</section>
<script type="application/ld+json">
<?= json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'FAQPage',
  'mainEntity' => array_map(fn($f) => [
    '@type' => 'Question',
    'name' => $f['q'],
    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
  ], $faqs),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
<?php endif; ?>

<?php if (!empty($slider)): ?>
<script>
(() => {
  const root = document.getElementById('hslider');
  if (!root) return;
  const slides = root.querySelectorAll('.hslide');
  const dots = root.querySelectorAll('.hslider-dot');
  let cur = 0, timer = null;

  const show = (i) => {
    slides[cur].classList.remove('active');
    slides[cur].setAttribute('tabindex', '-1');
    dots[cur].classList.remove('active');
    cur = (i + slides.length) % slides.length;
    slides[cur].classList.add('active');
    slides[cur].removeAttribute('tabindex');
    dots[cur].classList.add('active');
  };
  const play = () => { stop(); timer = setInterval(() => show(cur + 1), 5000); };
  const stop = () => { if (timer) clearInterval(timer); timer = null; };

  root.querySelector('.hslider-prev').addEventListener('click', () => { show(cur - 1); play(); });
  root.querySelector('.hslider-next').addEventListener('click', () => { show(cur + 1); play(); });
  dots.forEach(d => d.addEventListener('click', () => { show(+d.dataset.slide); play(); }));
  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', play);

  let x0 = null;
  root.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; }, { passive: true });
  root.addEventListener('touchend', e => {
    if (x0 === null) return;
    const dx = e.changedTouches[0].clientX - x0;
    if (Math.abs(dx) > 40) { show(cur + (dx < 0 ? 1 : -1)); play(); }
    x0 = null;
  }, { passive: true });

  play();
})();
</script>
<?php endif; ?>
