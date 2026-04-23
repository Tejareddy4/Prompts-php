<div class="col-12 col-md-6 col-lg-4">
  <div class="prompt-card">
    <div class="card-img-wrap">
      <?php if (!empty($item['image_path'])): ?>
        <img loading="lazy" src="<?= e($item['image_path']) ?>" alt="<?= e($item['title']) ?>">
      <?php else: ?>
        <span class="card-img-placeholder"><i class="bi bi-stars"></i></span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <h6 class="card-title">
        <a href="/prompt/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a>
      </h6>
      <?php if (!empty($item['description'])): ?>
        <p class="card-desc"><?= e($item['description']) ?></p>
      <?php endif; ?>
      <div class="card-author">
        <span class="avatar-xs"><?= strtoupper(substr($item['author'] ?? 'U', 0, 2)) ?></span>
        <span class="small text-muted"><?= e($item['author'] ?? 'Unknown') ?></span>
      </div>
      <div class="card-stats">
        <span title="Likes"><i class="bi bi-heart-fill text-danger"></i> <?= (int)($item['likes_count'] ?? 0) ?></span>
        <span title="Saves"><i class="bi bi-bookmark-fill text-primary"></i> <?= (int)($item['saves_count'] ?? 0) ?></span>
        <span title="Copies"><i class="bi bi-clipboard-fill text-secondary"></i> <?= (int)($item['copies_count'] ?? 0) ?></span>
        <span title="Views" class="ms-auto"><i class="bi bi-eye-fill"></i> <?= (int)($item['views_count'] ?? 0) ?></span>
      </div>
    </div>
  </div>
</div>
