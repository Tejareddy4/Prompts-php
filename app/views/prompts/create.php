<div class="page-hd">
  <a href="/dashboard" class="btn btn-sm btn-outline btn-icon"><i class="bi bi-arrow-left"></i></a>
  <h1>Submit a Prompt</h1>
</div>

<div class="form-card">
  <p style="font-size:0.8125rem;color:var(--muted);margin-bottom:1.25rem;padding:0.75rem;background:var(--p-xl);border-radius:var(--r-sm);">
    <i class="bi bi-info-circle" style="color:var(--p);"></i>
    Your prompt will be reviewed before going live. Usually within a few hours.
  </p>

  <form method="post" action="/prompts" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="field">
      <label for="title">Title <span class="req">*</span></label>
      <input class="input" id="title" type="text" name="title"
             placeholder="e.g. Expert TypeScript code reviewer" maxlength="160" required>
      <div class="hint">Clear, descriptive titles get discovered more.</div>
    </div>

    <div class="field">
      <label for="description">Short description</label>
      <textarea class="textarea" id="description" name="description" rows="2"
                placeholder="What does this prompt do? Who is it for?" maxlength="500"></textarea>
    </div>

    <div class="field">
      <label for="prompt_text">Prompt text <span class="req">*</span></label>
      <textarea class="textarea textarea-code" id="prompt_text" name="prompt_text" rows="10"
                placeholder="Write your full prompt here..." required></textarea>
    </div>

    <div class="field">
      <label for="image">Cover image <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
      <input class="file-input" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
      <div class="hint">JPEG, PNG or WebP — max 5MB</div>
    </div>

    <div style="display:flex;gap:0.5rem;justify-content:flex-end;padding-top:0.5rem;">
      <a href="/dashboard" class="btn btn-outline">Cancel</a>
      <button class="btn btn-primary" type="submit">
        <i class="bi bi-send"></i> Submit for review
      </button>
    </div>
  </form>
</div>
