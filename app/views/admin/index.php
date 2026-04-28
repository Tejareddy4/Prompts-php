<?php
// Encode trend data for chart
$trendJson = json_encode($trend);
$pendingCount = count($pending);
?>

<div class="adm-wrap">

  <!-- Sidebar -->
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

  <!-- Main content -->
  <div class="adm-main">

    <!-- Header bar -->
    <div class="adm-topbar">
      <div>
        <h1 class="adm-page-title">Dashboard</h1>
        <p class="adm-page-sub">Welcome back — here's what's happening on PromptShare.</p>
      </div>
      <div style="display:flex;gap:.5rem;align-items:center;">
        <?php if ($pendingCount > 0): ?>
          <a href="/admin/prompts" class="adm-alert-pill">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= $pendingCount ?> pending review
          </a>
        <?php endif; ?>
        <a href="/" class="nbtn nbtn-ghost" target="_blank" style="font-size:.8rem;">
          <i class="bi bi-box-arrow-up-right"></i> View site
        </a>
      </div>
    </div>

    <!-- KPI cards -->
    <div class="adm-kpi-grid">
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-purple"><i class="bi bi-file-text-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= number_format($analytics['total_prompts']) ?></div>
          <div class="adm-kpi-lbl">Total prompts</div>
          <div class="adm-kpi-sub"><span class="kpi-up"><?= $analytics['approved_prompts'] ?> live</span> · <?= $analytics['pending_prompts'] ?> pending</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-blue"><i class="bi bi-people-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= number_format($userStats['total']) ?></div>
          <div class="adm-kpi-lbl">Total users</div>
          <div class="adm-kpi-sub"><span class="kpi-up">+<?= $userStats['today'] ?> today</span> · <?= $userStats['this_week'] ?> this week</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-orange"><i class="bi bi-heart-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= number_format($analytics['total_likes']) ?></div>
          <div class="adm-kpi-lbl">Total likes</div>
          <div class="adm-kpi-sub">Across all prompts</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-green"><i class="bi bi-eye-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= number_format($analytics['total_views']) ?></div>
          <div class="adm-kpi-lbl">Total views</div>
          <div class="adm-kpi-sub">Unique sessions</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-teal"><i class="bi bi-google"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= $userStats['google_oauth'] ?></div>
          <div class="adm-kpi-lbl">Google signups</div>
          <div class="adm-kpi-sub"><?= $userStats['total'] > 0 ? round(($userStats['google_oauth'] / $userStats['total']) * 100) : 0 ?>% of users</div>
        </div>
      </div>
      <div class="adm-kpi adm-kpi-action" onclick="window.location='/admin/prompts'">
        <div class="adm-kpi-icon ic-warn"><i class="bi bi-clock-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= $pendingCount ?></div>
          <div class="adm-kpi-lbl">Awaiting review</div>
          <div class="adm-kpi-sub" style="color:var(--warning);">Click to moderate →</div>
        </div>
      </div>
    </div>

    <!-- Chart + Recent users -->
    <div class="adm-two-col">

      <!-- 14-day engagement chart -->
      <div class="adm-card" style="flex:1.6;">
        <div class="adm-card-head">
          <h2>14-day engagement</h2>
          <div class="chart-legend">
            <span class="cl-dot" style="background:#7C3AED;"></span>Views
            <span class="cl-dot" style="background:#EF4444;"></span>Likes
            <span class="cl-dot" style="background:#10B981;"></span>Signups
          </div>
        </div>
        <div class="adm-card-body">
          <canvas id="trendChart" height="200"></canvas>
        </div>
      </div>

      <!-- Recent signups -->
      <div class="adm-card" style="flex:1;">
        <div class="adm-card-head">
          <h2>Recent signups</h2>
          <a href="/admin/users" class="adm-link">View all</a>
        </div>
        <div class="adm-card-body" style="padding:0;">
          <?php foreach ($recentUsers as $u): ?>
          <div class="adm-user-row">
            <div class="adm-user-avatar">
              <?php if (!empty($u['avatar_url'])): ?>
                <img src="<?= e($u['avatar_url']) ?>" alt="">
              <?php else: ?>
                <?= strtoupper(substr($u['name'] ?? 'U', 0, 2)) ?>
              <?php endif; ?>
            </div>
            <div class="adm-user-info">
              <div class="adm-user-name"><?= e($u['name']) ?></div>
              <div class="adm-user-email"><?= e($u['email']) ?></div>
            </div>
            <div class="adm-user-meta">
              <?php if (!empty($u['google_id'])): ?>
                <span class="badge-mini badge-google"><i class="bi bi-google"></i> Google</span>
              <?php endif; ?>
              <div class="adm-user-date"><?= date('M j', strtotime($u['created_at'])) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Pending prompts quick-action -->
    <?php if (!empty($pending)): ?>
    <div class="adm-card">
      <div class="adm-card-head">
        <h2><i class="bi bi-clock" style="color:var(--warning);"></i> Prompts awaiting review</h2>
        <a href="/admin/prompts" class="adm-link">View all prompts</a>
      </div>
      <div class="adm-card-body" style="padding:0;">
        <table class="adm-table">
          <thead><tr><th>Title</th><th>Author</th><th>Submitted</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach (array_slice($pending, 0, 5) as $p): ?>
            <tr>
              <td><span class="adm-prompt-title"><?= e($p['title']) ?></span></td>
              <td><span class="adm-muted"><?= e($p['author']) ?></span></td>
              <td><span class="adm-muted"><?= date('M j, g:ia', strtotime($p['created_at'])) ?></span></td>
              <td>
                <div style="display:flex;gap:.375rem;">
                  <form method="post" action="/admin/prompts/approve" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="prompt_id" value="<?= (int)$p['id'] ?>">
                    <button class="adm-btn adm-btn-approve"><i class="bi bi-check-lg"></i> Approve</button>
                  </form>
                  <form method="post" action="/admin/prompts/reject" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="prompt_id" value="<?= (int)$p['id'] ?>">
                    <button class="adm-btn adm-btn-reject"><i class="bi bi-x-lg"></i> Reject</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /adm-main -->
</div><!-- /adm-wrap -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
  const data = <?= $trendJson ?>;
  const labels = data.map(d => d.day);
  const isDark = document.documentElement.classList.contains('dark')
    || window.matchMedia('(prefers-color-scheme: dark)').matches;
  const gridColor = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)';
  const textColor = isDark ? '#9CA3AF' : '#6B7280';

  new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Views',
          data: data.map(d => d.views),
          borderColor: '#7C3AED',
          backgroundColor: 'rgba(124,58,237,.08)',
          tension: .4, fill: true, pointRadius: 3, borderWidth: 2,
        },
        {
          label: 'Likes',
          data: data.map(d => d.likes),
          borderColor: '#EF4444',
          backgroundColor: 'rgba(239,68,68,.06)',
          tension: .4, fill: true, pointRadius: 3, borderWidth: 2,
        },
        {
          label: 'Signups',
          data: data.map(d => d.signups),
          borderColor: '#10B981',
          backgroundColor: 'rgba(16,185,129,.06)',
          tension: .4, fill: true, pointRadius: 3, borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: isDark ? '#1F2937' : '#fff',
          borderColor: isDark ? '#374151' : '#E5E7EB',
          borderWidth: 1,
          titleColor: isDark ? '#F9FAFB' : '#111827',
          bodyColor: textColor,
          padding: 10,
        },
      },
      scales: {
        x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
        y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } }, beginAtZero: true },
      },
    },
  });
})();
</script>
