<h1 class="h3 mb-3">My Profile Dashboard</h1>

<ul class="nav nav-tabs" id="dashTabs" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#my-prompts">My Prompts</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#saved">Saved</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#liked">Liked</button></li>
</ul>
<div class="tab-content border border-top-0 p-3 bg-white">
  <div class="tab-pane fade show active" id="my-prompts">
    <div class="row g-3">
      <?php foreach ($myPrompts as $p): ?>
        <div class="col-md-4">
          <div class="card h-100">
            <?php if ($p['image_path']): ?><img src="<?= e($p['image_path']) ?>" class="card-img-top" alt="<?= e($p['title']) ?>"><?php endif; ?>
            <div class="card-body">
              <h6><?= e($p['title']) ?></h6>
              <div class="small text-muted">Views: <?= (int)$p['views_count'] ?> · Likes: <?= (int)$p['likes_count'] ?> · Saves: <?= (int)$p['saves_count'] ?> · Copies: <?= (int)$p['copies_count'] ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="tab-pane fade" id="saved"><div class="row g-3"><?php foreach ($savedPrompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?></div></div>
  <div class="tab-pane fade" id="liked"><div class="row g-3"><?php foreach ($likedPrompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?></div></div>
</div>
