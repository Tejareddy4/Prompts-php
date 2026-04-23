<?php
$user = auth_user();
$totalLikesReceived = array_sum(array_column($myPrompts, 'likes_count'));
$totalSavesReceived = array_sum(array_column($myPrompts, 'saves_count'));
$totalCopies       = array_sum(array_column($myPrompts, 'copies_count'));
$statusMap = [1 => 'pending', 2 => 'approved', 3 => 'rejected'];
?>

<!-- Profile header -->
<div class="dash-header">
  <span class="avatar avatar-md"><?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?></span>
  <div class="dash-header-info">
    <h1><?= e($user['name']) ?></h1>
    <p><?= e($user['email'] ?? '') ?></p>
  </div>
  <a href="/prompts/create" class="btn btn-primary btn-sm" style="margin-left:auto;flex-shrink:0;">
    <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">New</span>
  </a>
</div>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-card-icon ic-purple"><i class="bi bi-file-text"></i></div>
    <div class="stat-card-val"><?= count($myPrompts) ?></div>
    <div class="stat-card-lbl">Prompts</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon ic-orange"><i class="bi bi-heart-fill"></i></div>
    <div class="stat-card-val"><?= $totalLikesReceived ?></div>
    <div class="stat-card-lbl">Likes</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon ic-blue"><i class="bi bi-bookmark-fill"></i></div>
    <div class="stat-card-val"><?= $totalSavesReceived ?></div>
    <div class="stat-card-lbl">Saves</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon ic-green"><i class="bi bi-clipboard-fill"></i></div>
    <div class="stat-card-val"><?= $totalCopies ?></div>
    <div class="stat-card-lbl">Copies</div>
  </div>
</div>

<!-- Tabs -->
<div class="tabs" id="dashTabs">
  <button class="tab-btn active" data-target="my-prompts">
    My Prompts <span class="tab-badge"><?= count($myPrompts) ?></span>
  </button>
  <button class="tab-btn" data-target="saved">
    Saved <span class="tab-badge"><?= count($savedPrompts) ?></span>
  </button>
  <button class="tab-btn" data-target="liked">
    Liked <span class="tab-badge"><?= count($likedPrompts) ?></span>
  </button>
</div>

<!-- My Prompts -->
<div class="tab-panel active" id="my-prompts">
  <?php if (empty($myPrompts)): ?>
    <div class="empty">
      <i class="bi bi-file-earmark-plus"></i>
      <h4>No prompts yet</h4>
      <p><a href="/prompts/create">Submit your first prompt</a></p>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:0.625rem;">
      <?php foreach ($myPrompts as $p):
        $statusLabel = $statusMap[$p['status_id']] ?? 'pending';
      ?>
        <div class="dash-prompt-card">
          <div class="dash-prompt-thumb">
            <?php if ($p['image_path']): ?>
              <img src="<?= e($p['image_path']) ?>" alt="">
            <?php else: ?>
              <i class="bi bi-stars"></i>
            <?php endif; ?>
          </div>
          <div class="dash-prompt-body">
            <div class="dash-prompt-title">
              <?php if ($statusLabel === 'approved'): ?>
                <a href="/prompt/<?= e($p['slug']) ?>" style="color:var(--text);"><?= e($p['title']) ?></a>
              <?php else: ?>
                <?= e($p['title']) ?>
              <?php endif; ?>
            </div>
            <div class="dash-prompt-meta">
              <span class="badge badge-<?= $statusLabel ?>"><?= $statusLabel ?></span>
              <?php if ($statusLabel !== 'approved'): ?>
                <a href="/prompts/<?= (int)$p['id'] ?>/edit" class="btn btn-sm btn-outline" style="height:24px;padding:0 0.5rem;font-size:0.72rem;">
                  <i class="bi bi-pencil"></i> Edit
                </a>
              <?php endif; ?>
            </div>
            <div class="dash-prompt-stats">
              <span><i class="bi bi-eye-fill"></i> <?= (int)$p['views_count'] ?></span>
              <span><i class="bi bi-heart-fill" style="color:#EF4444;"></i> <?= (int)$p['likes_count'] ?></span>
              <span><i class="bi bi-bookmark-fill" style="color:#3B82F6;"></i> <?= (int)$p['saves_count'] ?></span>
              <span><i class="bi bi-clipboard-fill"></i> <?= (int)$p['copies_count'] ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Saved -->
<div class="tab-panel" id="saved">
  <?php if (empty($savedPrompts)): ?>
    <div class="empty">
      <i class="bi bi-bookmark"></i>
      <h4>Nothing saved yet</h4>
      <p>Hit <strong>Save</strong> on any prompt to collect it here.</p>
    </div>
  <?php else: ?>
    <div class="prompt-grid">
      <?php foreach ($savedPrompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Liked -->
<div class="tab-panel" id="liked">
  <?php if (empty($likedPrompts)): ?>
    <div class="empty">
      <i class="bi bi-heart"></i>
      <h4>No liked prompts</h4>
      <p>Like prompts to find them here quickly.</p>
    </div>
  <?php else: ?>
    <div class="prompt-grid">
      <?php foreach ($likedPrompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(btn.dataset.target).classList.add('active');
  });
});
</script>
