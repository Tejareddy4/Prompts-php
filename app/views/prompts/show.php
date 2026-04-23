<a href="/" class="show-back"><i class="bi bi-arrow-left"></i> Explore</a>

<!-- Header -->
<div class="show-header">
  <h1><?= e($prompt['title']) ?></h1>
  <div class="show-author">
    <span class="avatar avatar-xs"><?= strtoupper(substr($prompt['author'] ?? 'U', 0, 2)) ?></span>
    by
    <?php if (!empty($prompt['author_username'])): ?>
      <a href="/u/<?= e($prompt['author_username']) ?>" style="color:rgba(255,255,255,.9);font-weight:700;">
        <?= e($prompt['author']) ?>
      </a>
    <?php else: ?>
      <strong><?= e($prompt['author']) ?></strong>
    <?php endif; ?>
  </div>
</div>

<!-- Layout: stacked on mobile, side-by-side on desktop -->
<div style="display:flex;flex-direction:column;gap:1rem;">

  <!-- Main -->
  <div style="flex:1;min-width:0;">

    <?php if (!empty($prompt['image_path'])): ?>
      <div class="show-card" style="padding:0;overflow:hidden;margin-bottom:1rem;">
        <img src="<?= e($prompt['image_path']) ?>" alt="<?= e($prompt['title']) ?>"
             style="width:100%;max-height:280px;object-fit:cover;display:block;">
      </div>
    <?php endif; ?>

    <?php if (!empty($prompt['description'])): ?>
      <div class="show-card" style="margin-bottom:1rem;">
        <div class="show-card-body" style="color:var(--muted);font-size:0.9rem;line-height:1.7;">
          <?= nl2br(e($prompt['description'])) ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Prompt text -->
    <div class="show-card">
      <div class="show-card-head">
        <h2><i class="bi bi-code-slash" style="color:var(--p);"></i> Prompt</h2>
        <button class="btn btn-sm btn-outline" id="copy-btn" onclick="copyPrompt(this)">
          <i class="bi bi-clipboard"></i> Copy
        </button>
      </div>
      <div class="show-card-body">
        <div class="prompt-code" id="prompt-text"><?= e($prompt['prompt_text']) ?></div>
      </div>
    </div>

    <!-- Sticky action bar on mobile -->
    <div class="show-actions" data-prompt-id="<?= (int)$prompt['id'] ?>">
      <button class="btn <?= !empty($prompt['is_liked']) ? 'btn-danger-fill' : 'btn-danger-outline' ?> js-like" style="flex:1;justify-content:center;"
              aria-pressed="<?= !empty($prompt['is_liked']) ? 'true' : 'false' ?>">
        <i class="bi bi-heart<?= !empty($prompt['is_liked']) ? '-fill' : '' ?>"></i>
        <span><?= !empty($prompt['is_liked']) ? 'Liked' : 'Like' ?></span>
        <span class="count" style="font-weight:800;"><?= (int)$prompt['likes_count'] ?></span>
      </button>
      <button class="btn <?= !empty($prompt['is_saved']) ? 'btn-save-fill' : 'btn-save-outline' ?> js-save" style="flex:1;justify-content:center;"
              aria-pressed="<?= !empty($prompt['is_saved']) ? 'true' : 'false' ?>">
        <i class="bi bi-bookmark<?= !empty($prompt['is_saved']) ? '-fill' : '' ?>"></i>
        <span><?= !empty($prompt['is_saved']) ? 'Saved' : 'Save' ?></span>
        <span class="count" style="font-weight:800;"><?= (int)$prompt['saves_count'] ?></span>
      </button>
      <button class="btn btn-outline js-copy" style="flex:1;justify-content:center;">
        <i class="bi bi-clipboard"></i>
        <span class="count"><?= (int)$prompt['copies_count'] ?></span>
      </button>
    </div>

  </div>

  <!-- Sidebar stats (desktop only, hidden on mobile since sticky bar handles it) -->
  <div style="width:100%;max-width:260px;display:none;" class="show-sidebar">
    <div class="show-card">
      <div class="show-card-head"><h2><i class="bi bi-bar-chart-line" style="color:var(--p)"></i> Stats</h2></div>
      <div class="show-card-body">
        <div class="stat-row">
          <div class="stat-pill"><i class="bi bi-heart-fill" style="color:#EF4444;"></i> <?= (int)$prompt['likes_count'] ?> likes</div>
          <div class="stat-pill"><i class="bi bi-bookmark-fill" style="color:#3B82F6;"></i> <?= (int)$prompt['saves_count'] ?> saves</div>
          <div class="stat-pill"><i class="bi bi-clipboard" style="color:var(--muted)"></i> <?= (int)$prompt['copies_count'] ?> copies</div>
          <div class="stat-pill"><i class="bi bi-eye-fill" style="color:var(--muted)"></i> <?= (int)$prompt['views_count'] ?> views</div>
        </div>
      </div>
    </div>
  </div>

</div>

<style>
@media (min-width: 768px) {
  .show-sidebar { display: block !important; }
  div:has(> .show-sidebar) { flex-direction: row !important; align-items: flex-start; }
}
</style>

<script>
function copyPrompt(btn) {
  const text = document.getElementById('prompt-text').innerText;
  navigator.clipboard.writeText(text).then(() => {
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
    setTimeout(() => { btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
  });
}
</script>
