<!-- Show page hero -->
<div class="show-hero">
  <div class="d-flex align-items-center gap-2 mb-3">
    <a href="/" class="btn btn-sm btn-outline-light py-1 px-2"><i class="bi bi-arrow-left"></i> Explore</a>
  </div>
  <h1><?= e($prompt['title']) ?></h1>
  <div class="d-flex align-items-center gap-2 mt-2">
    <span class="avatar-xs"><?= strtoupper(substr($prompt['author'] ?? 'U', 0, 2)) ?></span>
    <span style="color:rgba(255,255,255,.8); font-size:.9rem;">by <strong><?= e($prompt['author']) ?></strong></span>
  </div>
</div>

<div class="row g-4">
  <!-- Main content -->
  <div class="col-lg-8">
    <div class="prompt-body-card">
      <?php if (!empty($prompt['image_path'])): ?>
        <img src="<?= e($prompt['image_path']) ?>" class="img-fluid rounded mb-3"
             style="max-height:320px; width:100%; object-fit:cover;"
             alt="<?= e($prompt['title']) ?>">
      <?php endif; ?>

      <?php if (!empty($prompt['description'])): ?>
        <p class="text-muted mb-4" style="font-size:1rem; line-height:1.7;"><?= nl2br(e($prompt['description'])) ?></p>
      <?php endif; ?>

      <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-code-slash me-2 text-primary"></i>Prompt Text</h6>
        <button class="btn btn-sm btn-outline-secondary action-btn js-copy"
                data-prompt-id="not-counted"
                onclick="copyPromptText(this)"
                id="copy-btn-inline">
          <i class="bi bi-clipboard"></i> Copy
        </button>
      </div>
      <div class="prompt-text-box" id="prompt-text"><?= e($prompt['prompt_text']) ?></div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-lg-4">
    <div class="prompt-sidebar-card mb-3">
      <h6 class="fw-semibold mb-3"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Stats</h6>
      <div class="d-flex flex-column gap-2">
        <div class="stat-badge">
          <span class="stat-icon text-danger"><i class="bi bi-heart-fill"></i></span>
          <span class="flex-1"><?= (int)$prompt['likes_count'] ?> likes</span>
        </div>
        <div class="stat-badge">
          <span class="stat-icon text-primary"><i class="bi bi-bookmark-fill"></i></span>
          <span><?= (int)$prompt['saves_count'] ?> saves</span>
        </div>
        <div class="stat-badge">
          <span class="stat-icon text-secondary"><i class="bi bi-clipboard-fill"></i></span>
          <span><?= (int)$prompt['copies_count'] ?> copies</span>
        </div>
        <div class="stat-badge">
          <span class="stat-icon text-muted"><i class="bi bi-eye-fill"></i></span>
          <span><?= (int)$prompt['views_count'] ?> views</span>
        </div>
      </div>
    </div>

    <div class="prompt-sidebar-card">
      <h6 class="fw-semibold mb-3"><i class="bi bi-hand-thumbs-up me-2 text-primary"></i>Actions</h6>
      <div class="d-flex flex-column gap-2" data-prompt-id="<?= (int)$prompt['id'] ?>">
        <button class="btn action-btn <?= !empty($prompt['is_liked']) ? 'btn-danger' : 'btn-outline-danger' ?> js-like"
                aria-pressed="<?= !empty($prompt['is_liked']) ? 'true' : 'false' ?>">
          <i class="bi bi-heart-fill"></i>
          <span><?= !empty($prompt['is_liked']) ? 'Liked' : 'Like' ?></span>
          <span class="ms-auto count"><?= (int)$prompt['likes_count'] ?></span>
        </button>

        <button class="btn action-btn <?= !empty($prompt['is_saved']) ? 'btn-primary' : 'btn-outline-primary' ?> js-save"
                aria-pressed="<?= !empty($prompt['is_saved']) ? 'true' : 'false' ?>">
          <i class="bi bi-bookmark-fill"></i>
          <span><?= !empty($prompt['is_saved']) ? 'Saved' : 'Save' ?></span>
          <span class="ms-auto count"><?= (int)$prompt['saves_count'] ?></span>
        </button>

        <button class="btn action-btn btn-outline-secondary js-copy">
          <i class="bi bi-clipboard"></i>
          Copy prompt
          <span class="ms-auto count"><?= (int)$prompt['copies_count'] ?></span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function copyPromptText(btn) {
  const text = document.getElementById('prompt-text').innerText;
  navigator.clipboard.writeText(text).then(() => {
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
    setTimeout(() => { btn.innerHTML = orig; }, 2000);
  });
}
</script>
