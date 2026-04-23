<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <i class="bi bi-lightning-fill"></i>
    </div>
    <h1 class="h4 text-center fw-bold mb-1">Welcome back</h1>
    <p class="text-center text-muted small mb-4">Sign in to your PromptShare account</p>

    <form method="post" action="/login">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
          <input id="email" class="form-control" type="email" name="email"
                 placeholder="you@example.com" autocomplete="email" required>
        </div>
      </div>

      <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
          <input id="password" class="form-control" type="password" name="password"
                 placeholder="Your password" autocomplete="current-password" required>
        </div>
      </div>

      <button class="btn btn-primary w-100 fw-semibold py-2" type="submit">Sign in</button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
      Don't have an account? <a href="/register" class="fw-semibold">Create one free</a>
    </p>
  </div>
</div>
