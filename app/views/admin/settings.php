<div class="adm-wrap">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

  <div class="adm-main">
    <div class="adm-topbar">
      <div>
        <h1 class="adm-page-title">Settings</h1>
        <p class="adm-page-sub">Site configuration and integrations</p>
      </div>
    </div>

    <form method="post" action="/admin/settings/save">
      <?= csrf_field() ?>

      <!-- General -->
      <div class="adm-card" style="margin-bottom:1rem;">
        <div class="adm-card-head"><h2>General</h2></div>
        <div class="adm-card-body">
          <div class="adm-settings-grid">
            <div class="field">
              <label>Site name</label>
              <input class="input" type="text" name="app_name" value="<?= e($config['app']['name'] ?? 'PromptShare') ?>">
            </div>
            <div class="field">
              <label>Base URL</label>
              <input class="input" type="text" name="base_url" value="<?= e($config['app']['base_url'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Google OAuth -->
      <div class="adm-card" style="margin-bottom:1rem;">
        <div class="adm-card-head">
          <h2><i class="bi bi-google" style="color:#4285F4;"></i> Google OAuth</h2>
          <span class="badge <?= ($config['google_oauth']['enabled'] ?? false) ? 'badge-approved' : 'badge-rejected' ?>">
            <?= ($config['google_oauth']['enabled'] ?? false) ? 'Enabled' : 'Disabled' ?>
          </span>
        </div>
        <div class="adm-card-body">
          <div class="adm-settings-notice">
            <i class="bi bi-info-circle"></i>
            Configure these values in your <code>.env</code> file. Get credentials from
            <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a> →
            APIs & Services → Credentials → OAuth 2.0 Client ID.
          </div>
          <div class="adm-settings-grid">
            <div class="field">
              <label>Client ID</label>
              <input class="input" type="text" value="<?= e($config['google_oauth']['client_id'] ?? '') ?>"
                     placeholder="xxxx.apps.googleusercontent.com" readonly>
              <div class="hint">Set via GOOGLE_CLIENT_ID in .env</div>
            </div>
            <div class="field">
              <label>Client secret</label>
              <input class="input" type="password" value="<?= empty($config['google_oauth']['client_secret']) ? '' : '••••••••' ?>"
                     placeholder="GOCSPX-…" readonly>
              <div class="hint">Set via GOOGLE_CLIENT_SECRET in .env</div>
            </div>
            <div class="field">
              <label>Redirect URI</label>
              <input class="input" type="text" value="<?= e($config['google_oauth']['redirect_uri'] ?? '') ?>" readonly>
              <div class="hint">Must exactly match what you set in Google Console</div>
            </div>
          </div>
          <div class="adm-setup-steps">
            <strong>Setup steps:</strong>
            <ol>
              <li>Go to <a href="https://console.cloud.google.com/" target="_blank">console.cloud.google.com</a></li>
              <li>Create a project → Enable "Google+ API" and "People API"</li>
              <li>Credentials → Create OAuth 2.0 Client → Web application</li>
              <li>Add <code><?= e($config['app']['base_url'] ?? 'https://yourdomain.com') ?>/auth/google/callback</code> as Authorized Redirect URI</li>
              <li>Copy Client ID & Secret → add to your <code>.env</code> file</li>
              <li>Set <code>GOOGLE_OAUTH_ENABLED=true</code> in <code>.env</code></li>
            </ol>
          </div>
        </div>
      </div>

      <!-- Cache -->
      <div class="adm-card" style="margin-bottom:1rem;">
        <div class="adm-card-head"><h2>Caching</h2></div>
        <div class="adm-card-body">
          <div class="adm-settings-grid">
            <div class="field">
              <label>Cache TTL (seconds)</label>
              <input class="input" type="number" name="cache_ttl" value="<?= (int)($config['cache']['ttl'] ?? 60) ?>">
            </div>
            <div class="field">
              <label>Cache enabled</label>
              <select class="select" name="cache_enabled">
                <option value="1" <?= ($config['cache']['enabled'] ?? true) ? 'selected' : '' ?>>Yes</option>
                <option value="0" <?= !($config['cache']['enabled'] ?? true) ? 'selected' : '' ?>>No</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;">
        <button class="btn btn-primary" type="submit">
          <i class="bi bi-check-lg"></i> Save settings
        </button>
      </div>
    </form>

  </div>
</div>
