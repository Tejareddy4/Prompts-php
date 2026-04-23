<div class="page-hd">
  <a href="/dashboard" class="btn btn-sm btn-outline btn-icon"><i class="bi bi-arrow-left"></i></a>
  <h1>Edit Prompt</h1>
</div>

<div class="form-card">
  <p style="font-size:0.8125rem;color:var(--muted);margin-bottom:1.25rem;padding:0.75rem;background:#FFFBEB;border-radius:var(--r-sm);border:1px solid #FDE68A;">
    <i class="bi bi-exclamation-triangle" style="color:#D97706;"></i>
    Saving will re-submit for review and temporarily hide it from public.
  </p>

  <form method="post" action="/prompts/<?= (int)$prompt['id'] ?>/edit" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="field">
      <label for="title">Title <span class="req">*</span></label>
      <input class="input" id="title" type="text" name="title"
             value="<?= e($prompt['title']) ?>" maxlength="160" required>
    </div>

    <div class="field">
      <label for="description">Short description</label>
      <textarea class="textarea" id="description" name="description" rows="2"
                maxlength="500"><?= e($prompt['description']) ?></textarea>
    </div>

    <div class="field">
      <label for="prompt_text">Prompt text <span class="req">*</span></label>
      <textarea class="textarea textarea-code" id="prompt_text" name="prompt_text" rows="10" required><?= e($prompt['prompt_text']) ?></textarea>
    </div>

    <div class="field">
      <label for="image">Replace cover image</label>
      <?php if (!empty($prompt['image_path'])): ?>
        <div style="margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
          <img src="<?= e($prompt['image_path']) ?>" style="height:56px;width:56px;object-fit:cover;border-radius:var(--r-sm);" alt="">
          <span style="font-size:0.8rem;color:var(--muted);">Current image</span>
        </div>
      <?php endif; ?>
      <input class="file-input" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
      <div class="hint">Leave empty to keep current image</div>
    </div>

    <div style="display:flex;gap:0.5rem;justify-content:flex-end;padding-top:0.5rem;">
      <a href="/dashboard" class="btn btn-outline">Cancel</a>
      <button class="btn btn-primary" type="submit">
        <i class="bi bi-save"></i> Save &amp; re-submit
      </button>
    </div>
  </form>
</div>
