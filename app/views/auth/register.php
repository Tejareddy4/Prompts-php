<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-icon"><i class="bi bi-lightning-fill"></i></div>
    <h1 class="auth-title">Create account</h1>
    <p class="auth-sub">Join PromptShare and start sharing AI prompts</p>

    <form method="post" action="/register">
      <?= csrf_field() ?>
      <div class="field">
        <label for="name">Full name</label>
        <div class="input-group">
          <span class="ig-icon"><i class="bi bi-person"></i></span>
          <input id="name" type="text" name="name" placeholder="Your name" autocomplete="name" required>
        </div>
      </div>
      <div class="field">
        <label for="email">Email</label>
        <div class="input-group">
          <span class="ig-icon"><i class="bi bi-envelope"></i></span>
          <input id="email" type="email" name="email" placeholder="you@example.com" autocomplete="email" required>
        </div>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <div class="input-group">
          <span class="ig-icon"><i class="bi bi-lock"></i></span>
          <input id="password" type="password" name="password" placeholder="Min. 8 characters" autocomplete="new-password" minlength="8" required>
        </div>
        <div class="hint">At least 8 characters</div>
      </div>
      <button class="btn btn-primary btn-full" type="submit" style="height:44px;font-size:0.9375rem;margin-top:0.5rem;">
        Create account
      </button>
    </form>

    <p style="text-align:center;font-size:0.875rem;color:var(--muted);margin-top:1.25rem;margin-bottom:0;">
      Already a member? <a href="/login" style="font-weight:700;color:var(--p);">Sign in</a>
    </p>
  </div>
</div>
