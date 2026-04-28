<?php
$googleEnabled = config('google_oauth.enabled') && config('google_oauth.client_id');
?>
<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-brand">
      <div class="auth-icon"><i class="bi bi-lightning-fill"></i></div>
      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-sub">Sign in to your PromptShare account</p>
    </div>

    <?php if ($googleEnabled): ?>
    <a href="/auth/google" class="btn-google">
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 01-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
        <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z" fill="#34A853"/>
        <path d="M3.964 10.71A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 6.29C4.672 4.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
      </svg>
      Continue with Google
    </a>

    <div class="auth-divider"><span>or</span></div>
    <?php endif; ?>

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
      <button class="btn btn-primary btn-full auth-submit" type="submit">
        <i class="bi bi-box-arrow-in-right"></i> Sign in
      </button>
    </form>

    <p class="auth-footer-link">
      No account? <a href="/register">Create one free</a>
    </p>
  </div>
</div>
