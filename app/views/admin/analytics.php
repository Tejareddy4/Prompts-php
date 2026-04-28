<?php
$viewsJson  = json_encode($dailyViews);
$likesJson  = json_encode($dailyLikes);
?>

<div class="adm-wrap">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

  <div class="adm-main">
    <div class="adm-topbar">
      <div>
        <h1 class="adm-page-title">Analytics</h1>
        <p class="adm-page-sub">Platform-wide engagement metrics</p>
      </div>
    </div>

    <!-- KPI summary -->
    <div class="adm-kpi-grid">
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-green"><i class="bi bi-eye-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= number_format($analytics['total_views']) ?></div>
          <div class="adm-kpi-lbl">Total views</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-orange"><i class="bi bi-heart-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= number_format($analytics['total_likes']) ?></div>
          <div class="adm-kpi-lbl">Total likes</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-purple"><i class="bi bi-file-text-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= number_format($analytics['approved_prompts']) ?></div>
          <div class="adm-kpi-lbl">Published prompts</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-blue"><i class="bi bi-people-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= number_format($userStats['total']) ?></div>
          <div class="adm-kpi-lbl">Registered users</div>
        </div>
      </div>
    </div>

    <!-- Views + Likes charts side by side -->
    <div class="adm-two-col" style="margin-bottom:1.25rem;">
      <div class="adm-card" style="flex:1;">
        <div class="adm-card-head"><h2>Daily views (14d)</h2></div>
        <div class="adm-card-body"><canvas id="viewsChart" height="180"></canvas></div>
      </div>
      <div class="adm-card" style="flex:1;">
        <div class="adm-card-head"><h2>Daily likes (14d)</h2></div>
        <div class="adm-card-body"><canvas id="likesChart" height="180"></canvas></div>
      </div>
    </div>

    <!-- Top prompts -->
    <div class="adm-two-col">
      <div class="adm-card" style="flex:1.4;">
        <div class="adm-card-head"><h2>Top prompts by likes</h2></div>
        <div class="adm-card-body" style="padding:0;">
          <table class="adm-table">
            <thead><tr><th>#</th><th>Prompt</th><th>Author</th><th>Likes</th><th>Views</th></tr></thead>
            <tbody>
              <?php foreach ($topPrompts as $i => $p): ?>
              <tr>
                <td class="adm-muted"><?= $i + 1 ?></td>
                <td>
                  <a href="/prompt/<?= e($p['slug']) ?>" target="_blank"
                     style="color:var(--text);font-weight:600;font-size:.8125rem;">
                    <?= e(mb_strimwidth($p['title'], 0, 40, '…')) ?>
                  </a>
                </td>
                <td class="adm-muted"><?= e($p['author']) ?></td>
                <td>
                  <span style="color:#EF4444;font-weight:600;">
                    <i class="bi bi-heart-fill"></i> <?= number_format($p['likes_count']) ?>
                  </span>
                </td>
                <td class="adm-muted"><?= number_format($p['views_count']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top creators -->
      <div class="adm-card" style="flex:1;">
        <div class="adm-card-head"><h2>Top creators</h2></div>
        <div class="adm-card-body" style="padding:0;">
          <?php foreach ($topCreators as $i => $c): ?>
          <div class="adm-user-row">
            <div class="adm-rank"><?= $i + 1 ?></div>
            <div class="adm-user-avatar">
              <?php if (!empty($c['avatar_url'])): ?>
                <img src="<?= e($c['avatar_url']) ?>" alt="">
              <?php else: ?>
                <?= strtoupper(substr($c['name'] ?? 'U', 0, 2)) ?>
              <?php endif; ?>
            </div>
            <div class="adm-user-info">
              <div class="adm-user-name"><?= e($c['name']) ?></div>
              <div class="adm-user-email"><?= (int)$c['prompt_count'] ?> prompts</div>
            </div>
            <div style="font-weight:700;font-size:.875rem;color:#EF4444;">
              <i class="bi bi-heart-fill"></i> <?= number_format($c['total_likes'] ?? 0) ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
  const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const gridColor = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)';
  const textColor = isDark ? '#9CA3AF' : '#6B7280';

  const viewsData  = <?= $viewsJson ?>;
  const likesData  = <?= $likesJson ?>;

  function fillDays(rawData) {
    const map = {};
    rawData.forEach(d => { map[d.day] = parseInt(d.count); });
    const days = [];
    for (let i = 13; i >= 0; i--) {
      const d = new Date(); d.setDate(d.getDate() - i);
      const key = d.toISOString().split('T')[0];
      const label = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
      days.push({ label, count: map[key] || 0 });
    }
    return days;
  }

  const vd = fillDays(viewsData);
  const ld = fillDays(likesData);

  const chartDefaults = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 } } },
      y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 } }, beginAtZero: true },
    },
  };

  new Chart(document.getElementById('viewsChart'), {
    type: 'line',
    data: {
      labels: vd.map(d => d.label),
      datasets: [{
        data: vd.map(d => d.count),
        borderColor: '#7C3AED', backgroundColor: 'rgba(124,58,237,.1)',
        tension: .4, fill: true, pointRadius: 2, borderWidth: 2,
      }],
    },
    options: chartDefaults,
  });

  new Chart(document.getElementById('likesChart'), {
    type: 'line',
    data: {
      labels: ld.map(d => d.label),
      datasets: [{
        data: ld.map(d => d.count),
        borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,.08)',
        tension: .4, fill: true, pointRadius: 2, borderWidth: 2,
      }],
    },
    options: chartDefaults,
  });
})();
</script>
