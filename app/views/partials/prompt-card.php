<a href="/prompt/<?= e($item['slug']) ?>" class="pcard">
  <div class="pcard-thumb">
    <?php if (!empty($item['image_path'])): ?>
      <img loading="lazy" src="<?= e($item['image_path']) ?>" alt="<?= e($item['title']) ?>">
    <?php else: ?>
      <i class="bi bi-stars"></i>
    <?php endif; ?>
  </div>
  <div class="pcard-body">
    <div class="pcard-title"><?= e($item['title']) ?></div>
    <?php if (!empty($item['description'])): ?>
      <div class="pcard-desc"><?= e($item['description']) ?></div>
    <?php endif; ?>
    <div class="pcard-author">
      <span class="avatar avatar-xs"><?= strtoupper(substr($item['author'] ?? 'U', 0, 2)) ?></span>
      <?php if (!empty($item['author_username'])): ?>
        <a href="/u/<?= e($item['author_username']) ?>" style="color:inherit;text-decoration:none;" onclick="event.stopPropagation()">
          <?= e($item['author']) ?>
        </a>
      <?php else: ?>
        <?= e($item['author'] ?? 'Unknown') ?>
      <?php endif; ?>
    </div>
    <div class="pcard-stats">
      <span title="Likes"><i class="bi bi-heart-fill" style="color:#EF4444;"></i> <?= (int)($item['likes_count'] ?? 0) ?></span>
      <span title="Saves"><i class="bi bi-bookmark-fill" style="color:#3B82F6;"></i> <?= (int)($item['saves_count'] ?? 0) ?></span>
      <span title="Views" style="margin-left:auto;"><i class="bi bi-eye-fill"></i> <?= (int)($item['views_count'] ?? 0) ?></span>
    </div>
  </div>
</a>
