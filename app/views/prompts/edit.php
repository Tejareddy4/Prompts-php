<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="page-header">
      <a href="/dashboard" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
      <h1>Edit Prompt</h1>
    </div>

    <div class="form-card">
      <p class="text-muted small mb-4">
        <i class="bi bi-info-circle me-1"></i>
        Saving changes will re-submit this prompt for review. It will be hidden from public until approved.
      </p>

      <form method="post" action="/prompts/<?= (int)$prompt['id'] ?>/edit" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="mb-3">
          <label for="title" class="form-label">Prompt title <span class="text-danger">*</span></label>
          <input id="title" class="form-control" type="text" name="title"
                 value="<?= e($prompt['title']) ?>" maxlength="160" required>
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Short description</label>
          <textarea id="description" class="form-control" name="description" rows="2"
                    maxlength="500"><?= e($prompt['description']) ?></textarea>
        </div>

        <div class="mb-3">
          <label for="prompt_text" class="form-label">Prompt text <span class="text-danger">*</span></label>
          <textarea id="prompt_text" class="form-control" name="prompt_text" rows="10" required
                    style="font-family: monospace; font-size: .875rem;"><?= e($prompt['prompt_text']) ?></textarea>
        </div>

        <div class="mb-4">
          <label for="image" class="form-label">Replace cover image <span class="text-muted">(optional)</span></label>
          <?php if (!empty($prompt['image_path'])): ?>
            <div class="mb-2">
              <img src="<?= e($prompt['image_path']) ?>" class="rounded" style="height:80px; object-fit:cover;" alt="Current image">
              <span class="text-muted small ms-2">Current image</span>
            </div>
          <?php endif; ?>
          <input id="image" class="form-control" type="file" name="image"
                 accept="image/jpeg,image/png,image/webp">
          <div class="form-text">Leave empty to keep the current image.</div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
          <a href="/dashboard" class="btn btn-outline-secondary">Cancel</a>
          <button class="btn btn-primary px-4" type="submit">
            <i class="bi bi-save me-1"></i> Save &amp; re-submit
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
