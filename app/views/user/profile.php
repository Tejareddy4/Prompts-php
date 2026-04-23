<!-- Profile hero -->
<div style="background:linear-gradient(135deg,#1E1B4B,#4C1D95);border-radius:var(--r-lg);padding:1.5rem 1.25rem;margin-bottom:1.25rem;color:#fff;">
  <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
    <span class="avatar" style="width:64px;height:64px;font-size:1.5rem;font-weight:800;flex-shrink:0;">
      <?= strtoupper(substr($profile['name'] ?? 'U', 0, 2)) ?>
    </span>
    <div>
      <h1 style="font-size:1.35rem;font-weight:800;letter-spacing:-0.02em;margin-bottom:0.2rem;">
        <?= e($profile['name']) ?>
      </h1>
      <div style="font-size:0.875rem;color:rgba(255,255,255,.65);">
        @<?= e($profile['username'] ?? '') ?>
        &nbsp;&middot;&nbsp;
        Joined <?= date('M Y', strtotime($profile['created_at'])) ?>
      </div>
    </div>
  </div>

  <!-- Stats row -->
  <div style="display:flex;gap:1.5rem;margin-top:1.25rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,.15);flex-wrap:wrap;">
    <div>
      <div style="font-size:1.25rem;font-weight:800;line-height:1;"><?= (int)$stats['prompt_count'] ?></div>
      <div style="font-size:0.7rem;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Prompts</div>
    </div>
    <div>
      <div style="font-size:1.25rem;font-weight:800;line-height:1;"><?= (int)$stats['likes_received'] ?></div>
      <div style="font-size:0.7rem;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Likes</div>
    </div>
    <div>
      <div style="font-size:1.25rem;font-weight:800;line-height:1;"><?= (int)$stats['saves_received'] ?></div>
      <div style="font-size:0.7rem;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Saves</div>
    </div>
    <div>
      <div style="font-size:1.25rem;font-weight:800;line-height:1;"><?= (int)$stats['views_received'] ?></div>
      <div style="font-size:0.7rem;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Views</div>
    </div>
  </div>
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
