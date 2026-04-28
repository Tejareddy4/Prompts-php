<?php
$growthLabels = array_column($growth, 'day');
$growthCounts = array_column($growth, 'count');
?>

<div class="adm-wrap">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

  <div class="adm-main">
    <div class="adm-topbar">
      <div>
        <h1 class="adm-page-title">Users</h1>
        <p class="adm-page-sub"><?= number_format($userStats['total']) ?> total · <?= $userStats['today'] ?> joined today</p>
      </div>
    </div>

    <!-- Stats row -->
    <div class="adm-kpi-grid" style="grid-template-columns:repeat(4,1fr);">
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-blue"><i class="bi bi-people-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= number_format($userStats['total']) ?></div>
          <div class="adm-kpi-lbl">Total users</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-green"><i class="bi bi-person-plus-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= $userStats['today'] ?></div>
          <div class="adm-kpi-lbl">Today</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-purple"><i class="bi bi-calendar-week-fill"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= $userStats['this_week'] ?></div>
          <div class="adm-kpi-lbl">This week</div>
        </div>
      </div>
      <div class="adm-kpi">
        <div class="adm-kpi-icon ic-teal"><i class="bi bi-google"></i></div>
        <div class="adm-kpi-body">
          <div class="adm-kpi-val"><?= $userStats['google_oauth'] ?></div>
          <div class="adm-kpi-lbl">Via Google</div>
        </div>
      </div>
    </div>

    <!-- Growth chart -->
    <div class="adm-card" style="margin-bottom:1.25rem;">
      <div class="adm-card-head"><h2>14-day signup growth</h2></div>
      <div class="adm-card-body">
        <canvas id="growthChart" height="120"></canvas>
      </div>
    </div>

    <!-- User search -->
    <div style="margin-bottom:1rem;">
      <input type="text" id="user-search" class="adm-search-input"
             placeholder="Search by name or email…" oninput="filterUsers(this.value)">
    </div>

    <!-- Users table -->
    <div class="adm-card" style="padding:0;overflow:hidden;">
      <table class="adm-table user-table">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Auth</th>
            <th>Role</th>
            <th>Prompts</th>
            <th>Joined</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr data-search="<?= strtolower(e($u['name']) . ' ' . e($u['email'])) ?>">
            <td class="adm-muted" style="font-size:.75rem;"><?= (int)$u['id'] ?></td>
            <td>
              <div class="adm-user-chip">
                <?php if (!empty($u['avatar_url'])): ?>
                  <img src="<?= e($u['avatar_url']) ?>" class="avatar avatar-xs" style="object-fit:cover;" alt="">
                <?php else: ?>
                  <span class="avatar avatar-xs"><?= strtoupper(substr($u['name'] ?? 'U', 0, 2)) ?></span>
                <?php endif; ?>
                <div>
                  <div style="font-weight:600;font-size:.8125rem;"><?= e($u['name']) ?></div>
                  <div class="adm-muted" style="font-size:.7rem;"><?= e($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <?php if (!empty($u['google_id'])): ?>
                <span class="badge-mini badge-google"><i class="bi bi-google"></i> Google</span>
              <?php else: ?>
                <span class="badge-mini badge-email"><i class="bi bi-envelope"></i> Email</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="post" action="/admin/users/role" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <select name="role_id" class="adm-select-sm" onchange="this.form.submit()">
                  <option value="1" <?= $u['role_name'] === 'user'        ? 'selected' : '' ?>>User</option>
                  <option value="2" <?= $u['role_name'] === 'super_admin' ? 'selected' : '' ?>>Admin</option>
                </select>
              </form>
            </td>
            <td class="adm-muted"><?= (int)($u['prompt_count'] ?? 0) ?></td>
            <td class="adm-muted" style="font-size:.75rem;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td>
              <?php if ($u['is_banned'] ?? 0): ?>
                <span class="badge badge-rejected">Banned</span>
              <?php else: ?>
                <span class="badge badge-approved">Active</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="adm-action-group">
                <!-- Ban/Unban -->
                <form method="post" action="/admin/users/ban" style="display:inline;"
                      onsubmit="return confirm('<?= ($u['is_banned'] ?? 0) ? 'Unban' : 'Ban' ?> this user?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <input type="hidden" name="action"  value="<?= ($u['is_banned'] ?? 0) ? 'unban' : 'ban' ?>">
                  <button class="adm-btn <?= ($u['is_banned'] ?? 0) ? 'adm-btn-approve' : 'adm-btn-reject' ?>"
                          title="<?= ($u['is_banned'] ?? 0) ? 'Unban' : 'Ban' ?>">
                    <i class="bi bi-<?= ($u['is_banned'] ?? 0) ? 'check-circle' : 'slash-circle' ?>"></i>
                  </button>
                </form>
                <!-- Delete -->
                <form method="post" action="/admin/users/delete" style="display:inline;"
                      onsubmit="return confirm('PERMANENTLY delete this user and all their data?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button class="adm-btn adm-btn-delete" title="Delete user">
                    <i class="bi bi-trash3"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
const gridColor = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)';
const textColor = isDark ? '#9CA3AF' : '#6B7280';

new Chart(document.getElementById('growthChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_map(fn($d) => date('M j', strtotime($d)), $growthLabels)) ?>,
    datasets: [{
      data: <?= json_encode($growthCounts) ?>,
      backgroundColor: 'rgba(124,58,237,.5)',
      borderColor: '#7C3AED',
      borderWidth: 1,
      borderRadius: 4,
    }],
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
      y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } }, beginAtZero: true },
    },
  },
});

function filterUsers(q) {
  q = q.toLowerCase();
  document.querySelectorAll('.user-table tbody tr').forEach(row => {
    row.style.display = row.dataset.search.includes(q) ? '' : 'none';
  });
}
</script>
