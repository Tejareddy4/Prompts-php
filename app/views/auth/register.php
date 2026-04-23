<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <i class="bi bi-lightning-fill"></i>
    </div>
    <h1 class="h4 text-center fw-bold mb-1">Create your account</h1>
    <p class="text-center text-muted small mb-4">Join PromptShare and start sharing AI prompts</p>

    <form method="post" action="/register">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label for="name" class="form-label">Full name</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
          <input id="name" class="form-control" type="text" name="name"
                 placeholder="Your full name" autocomplete="name" required>
        </div>
      </div>

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
                 placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
        </div>
        <div class="form-text">Minimum 8 characters</div>
      </div>

      <button class="btn btn-primary w-100 fw-semibold py-2" type="submit">Create account</button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
      Already have an account? <a href="/login" class="fw-semibold">Sign in</a>
    </p>
  </div>
</div>
