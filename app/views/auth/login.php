<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-icon"><i class="bi bi-lightning-fill"></i></div>
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-sub">Sign in to your PromptShare account</p>

    <form method="post" action="/login">
      <?= csrf_field() ?>
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
          <input id="password" type="password" name="password" placeholder="Your password" autocomplete="current-password" required>
        </div>
      </div>
      <button class="btn btn-primary btn-full" type="submit" style="height:44px;font-size:0.9375rem;margin-top:0.5rem;">
        Sign in
      </button>
    </form>

    <p style="text-align:center;font-size:0.875rem;color:var(--muted);margin-top:1.25rem;margin-bottom:0;">
      No account? <a href="/register" style="font-weight:700;color:var(--p);">Create one free</a>
    </p>
  </div>
</div>
