<?php
$publicKarma  = $karma['public']  ?? ['score' => 0, 'level' => 'Newcomer', 'color' => 'gray', 'breakdown' => []];
$privateKarma = $karma['private'] ?? null;
$karmaTip = implode("\n", array_map(
    fn($rule, $pts) => "{$rule}: " . ($pts >= 0 ? '+' : '') . $pts,
    array_keys($publicKarma['breakdown']),
    $publicKarma['breakdown']
));
?>
<!-- Profile hero -->
<div class="hero" style="margin-bottom:1.25rem;">
  <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
    <span class="avatar" style="width:64px;height:64px;font-size:1.5rem;font-weight:700;flex-shrink:0;">
      <?= strtoupper(substr($profile['name'] ?? 'U', 0, 2)) ?>
    </span>
    <div>
      <h1 style="font-size:1.5rem;font-weight:700;letter-spacing:-0.02em;margin-bottom:0.2rem;display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
        <?= e($profile['name']) ?>
        <span class="karma-badge karma-<?= e($publicKarma['color']) ?>" title="<?= e($karmaTip) ?>">
          <i class="bi bi-award-fill"></i> <?= e($publicKarma['level']) ?>
        </span>
      </h1>
      <div style="font-size:0.875rem;color:var(--muted);">
        @<?= e($profile['username'] ?? '') ?>
        &nbsp;&middot;&nbsp;
        Joined <?= date('M Y', strtotime($profile['created_at'])) ?>
      </div>
    </div>
  </div>

  <!-- Stats row -->
  <div class="hero-stats">
    <div>
      <div class="hero-stat-num"><?= (int)$stats['prompt_count'] ?></div>
      <div class="hero-stat-lbl">Prompts</div>
    </div>
    <span class="hero-stat-sep"></span>
    <div>
      <div class="hero-stat-num"><?= (int)$stats['likes_received'] ?></div>
      <div class="hero-stat-lbl">Likes</div>
    </div>
    <span class="hero-stat-sep"></span>
    <div>
      <div class="hero-stat-num"><?= (int)$stats['saves_received'] ?></div>
      <div class="hero-stat-lbl">Saves</div>
    </div>
    <span class="hero-stat-sep"></span>
    <div>
      <div class="hero-stat-num"><?= (int)$stats['views_received'] ?></div>
      <div class="hero-stat-lbl">Views</div>
    </div>
    <span class="hero-stat-sep"></span>
    <div title="<?= e($karmaTip) ?>">
      <div class="hero-stat-num" style="background:linear-gradient(135deg,#FBBF24,#F59E0B);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;"><?= (int)$publicKarma['score'] ?></div>
      <div class="hero-stat-lbl">Karma</div>
    </div>
  </div>

  <?php if (!empty($viewerIsAdmin) && $privateKarma): ?>
  <!-- Trust score — rendered only for super_admin viewers -->
  <div style="margin-top:1rem;padding:0.75rem 1rem;background:rgba(0,0,0,.35);border:1px dashed var(--border);border-radius:var(--r-sm);">
    <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
      <i class="bi bi-shield-lock-fill" style="color:var(--subtle);"></i>
      <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--subtle);">Admin only · Trust score</span>
      <span class="karma-badge karma-<?= e($privateKarma['color']) ?>">
        <?= (int)$privateKarma['score'] ?>/100 · <?= e($privateKarma['band']) ?>
      </span>
    </div>
    <div style="margin-top:0.5rem;display:flex;gap:0.35rem;flex-wrap:wrap;">
      <?php foreach ($privateKarma['breakdown'] as $rule => $pts): ?>
        <span style="font-size:0.7rem;padding:2px 8px;border-radius:99px;background:rgba(255,255,255,.1);color:rgba(255,255,255,.75);">
          <?= e($rule) ?>: <strong style="color:<?= $pts >= 0 ? '#4ADE80' : '#F87171' ?>;"><?= $pts >= 0 ? '+' : '' ?><?= (int)$pts ?></strong>
        </span>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Prompts -->
<div class="section-hd">
  <h2><i class="bi bi-file-text"></i> Prompts by <?= e(explode(' ', $profile['name'])[0]) ?></h2>
  <span class="count-chip"><?= count($prompts) ?></span>
</div>

<?php if (empty($prompts)): ?>
  <div class="empty">
    <i class="bi bi-stars"></i>
    <h4>No published prompts yet</h4>
    <p>Check back later!</p>
  </div>
<?php else: ?>
  <div class="prompt-grid">
    <?php foreach ($prompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?>
  </div>
<?php endif; ?>
