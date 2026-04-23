<?php
$user = auth_user();
$totalPrompts = count($myPrompts);
$totalLikesReceived = array_sum(array_column($myPrompts, 'likes_count'));
$totalSavesReceived = array_sum(array_column($myPrompts, 'saves_count'));
$totalCopies       = array_sum(array_column($myPrompts, 'copies_count'));
?>

<!-- Page header -->
<div class="d-flex align-items-center justify-content-between mb-4 gap-2">
  <div class="page-header mb-0">
    <span class="avatar-sm"><?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?></span>
    <div>
      <h1 style="font-size:1.35rem;">Welcome, <?= e(explode(' ', $user['name'])[0]) ?></h1>
      <p class="text-muted small mb-0"><?= e($user['email'] ?? '') ?></p>
    </div>
  </div>
  <a class="btn btn-primary btn-sm" href="/prompts/create">
    <i class="bi bi-plus-lg me-1"></i> New Prompt
  </a>
</div>

<!-- Stats row -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="dash-stat-card">
      <div class="dash-stat-icon purple"><i class="bi bi-file-text"></i></div>
      <div>
        <div class="dash-stat-num"><?= $totalPrompts ?></div>
        <div class="dash-stat-label">My Prompts</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="dash-stat-card">
      <div class="dash-stat-icon orange"><i class="bi bi-heart-fill"></i></div>
      <div>
        <div class="dash-stat-num"><?= $totalLikesReceived ?></div>
        <div class="dash-stat-label">Likes received</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="dash-stat-card">
      <div class="dash-stat-icon blue"><i class="bi bi-bookmark-fill"></i></div>
      <div>
        <div class="dash-stat-num"><?= $totalSavesReceived ?></div>
        <div class="dash-stat-label">Saves received</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="dash-stat-card">
      <div class="dash-stat-icon green"><i class="bi bi-clipboard-fill"></i></div>
      <div>
        <div class="dash-stat-num"><?= $totalCopies ?></div>
        <div class="dash-stat-label">Total copies</div>
      </div>
    </div>
  </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs" id="dashTabs" role="tablist">
  <li class="nav-item">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#my-prompts">
      My Prompts <span class="badge bg-primary ms-1" style="font-size:.7rem;"><?= $totalPrompts ?></span>
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#saved">
      Saved <span class="badge bg-secondary ms-1" style="font-size:.7rem;"><?= count($savedPrompts) ?></span>
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#liked">
      Liked <span class="badge bg-danger ms-1" style="font-size:.7rem;"><?= count($likedPrompts) ?></span>
    </button>
  </li>
</ul>

<div class="tab-content">
  <!-- My Prompts -->
  <div class="tab-pane fade show active" id="my-prompts">
    <?php if (empty($myPrompts)): ?>
      <div class="empty-state">
        <div><i class="bi bi-file-earmark-plus d-block"></i></div>
        <h6 class="mt-2">No prompts yet</h6>
        <p class="small">You haven't submitted any prompts. <a href="/prompts/create">Submit your first prompt</a></p>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($myPrompts as $p):
          $statusMap = [1 => 'pending', 2 => 'approved', 3 => 'rejected'];
          $statusLabel = $statusMap[$p['status_id']] ?? 'pending';
        ?>
          <div class="col-md-6 col-lg-4">
            <div class="prompt-card" style="height:auto;">
              <?php if ($p['image_path']): ?>
                <div class="card-img-wrap" style="height:120px;">
                  <img src="<?= e($p['image_path']) ?>" alt="<?= e($p['title']) ?>">
                </div>
              <?php endif; ?>
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
                  <h6 class="card-title mb-0">
                    <?php if ($statusLabel === 'approved'): ?>
                      <a href="/prompt/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a>
                    <?php else: ?>
                      <?= e($p['title']) ?>
                    <?php endif; ?>
                  </h6>
                  <span class="status-badge status-<?= $statusLabel ?> flex-shrink-0">
                    <?= $statusLabel ?>
                  </span>
                </div>
                <div class="card-stats mt-2">
                  <span title="Views"><i class="bi bi-eye-fill"></i> <?= (int)$p['views_count'] ?></span>
                  <span title="Likes"><i class="bi bi-heart-fill text-danger"></i> <?= (int)$p['likes_count'] ?></span>
                  <span title="Saves"><i class="bi bi-bookmark-fill text-primary"></i> <?= (int)$p['saves_count'] ?></span>
                  <span title="Copies"><i class="bi bi-clipboard-fill"></i> <?= (int)$p['copies_count'] ?></span>
                </div>
                <?php if ($statusLabel !== 'approved'): ?>
                  <div class="mt-2">
                    <a href="/prompts/<?= (int)$p['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
                      <i class="bi bi-pencil"></i> Edit
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Saved -->
  <div class="tab-pane fade" id="saved">
    <?php if (empty($savedPrompts)): ?>
      <div class="empty-state">
        <div><i class="bi bi-bookmark d-block"></i></div>
        <h6 class="mt-2">Nothing saved yet</h6>
        <p class="small">Browse prompts and hit the <strong>Save</strong> button to collect them here.</p>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($savedPrompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Liked -->
  <div class="tab-pane fade" id="liked">
    <?php if (empty($likedPrompts)): ?>
      <div class="empty-state">
        <div><i class="bi bi-heart d-block"></i></div>
        <h6 class="mt-2">No liked prompts</h6>
        <p class="small">Like prompts to find them quickly here.</p>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($likedPrompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
