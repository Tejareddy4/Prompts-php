<!-- Header -->
<div class="page-hd" style="margin-bottom:1.25rem;">
  <div style="width:36px;height:36px;border-radius:10px;background:var(--p-l);display:flex;align-items:center;justify-content:center;color:var(--p);">
    <i class="bi bi-shield-check"></i>
  </div>
  <h1>Admin Panel</h1>
  <a href="/" class="btn btn-sm btn-outline" style="margin-left:auto;"><i class="bi bi-arrow-left"></i> Site</a>
</div>

<!-- Stats -->
<div class="admin-stats">
  <div class="admin-stat">
    <div class="admin-stat-val" style="color:var(--p);"><?= (int)$analytics['total_prompts'] ?></div>
    <div class="admin-stat-lbl">Total</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-val" style="color:var(--warning);"><?= (int)$analytics['pending_prompts'] ?></div>
    <div class="admin-stat-lbl">Pending</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-val" style="color:var(--success);"><?= (int)$analytics['approved_prompts'] ?></div>
    <div class="admin-stat-lbl">Published</div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-val" style="color:var(--danger);"><?= (int)$analytics['total_likes'] ?></div>
    <div class="admin-stat-lbl">Total Likes</div>
  </div>
</div>

<!-- Tabs -->
<div class="tabs" id="adminTabs">
  <button class="tab-btn active" data-target="tab-pending">
    Pending <span class="tab-badge"><?= count($pending) ?></span>
  </button>
  <button class="tab-btn" data-target="tab-approved">
    Approved <span class="tab-badge"><?= count($approved) ?></span>
  </button>
  <button class="tab-btn" data-target="tab-rejected">
    Rejected <span class="tab-badge"><?= count($rejected) ?></span>
  </button>
  <button class="tab-btn" data-target="tab-users">
    Users <span class="tab-badge"><?= count($users) ?></span>
  </button>
</div>

<?php
$sections = [
  'tab-pending'  => ['rows' => $pending,  'label' => 'pending'],
  'tab-approved' => ['rows' => $approved, 'label' => 'approved'],
  'tab-rejected' => ['rows' => $rejected, 'label' => 'rejected'],
];
foreach ($sections as $tabId => $section):
  $rows = $section['rows'];
  $key  = $section['label'];
?>
<div class="tab-panel <?= $tabId === 'tab-pending' ? 'active' : '' ?>" id="<?= $tabId ?>">
  <?php if (empty($rows)): ?>
    <div class="empty" style="padding:2rem 0;">
      <i class="bi bi-check-circle"></i>
      <p>No <?= $key ?> prompts.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th style="min-width:140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td>
                <?php if ($key === 'approved'): ?>
                  <a href="/prompt/<?= e($r['slug']) ?>" target="_blank" style="font-weight:600;color:var(--text);">
                    <?= e($r['title']) ?> <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;color:var(--muted);"></i>
                  </a>
                <?php else: ?>
                  <span style="font-weight:600;"><?= e($r['title']) ?></span>
                <?php endif; ?>
                <div style="font-size:0.72rem;color:var(--muted);margin-top:2px;"><?= date('M j, Y', strtotime($r['created_at'])) ?></div>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:0.375rem;">
                  <span class="avatar avatar-xs"><?= strtoupper(substr($r['author'] ?? 'U', 0, 2)) ?></span>
                  <span style="font-size:0.8125rem;"><?= e($r['author']) ?></span>
                </div>
              </td>
              <td>
                <div style="display:flex;gap:0.375rem;flex-wrap:wrap;">
                  <?php if ($key !== 'approved'): ?>
                    <form class="d-inline" method="post" action="/admin/prompts/approve">
                      <?= csrf_field() ?>
                      <input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>">
                      <button class="btn btn-sm" style="background:#DCFCE7;color:#166534;border-color:#BBF7D0;">
                        <i class="bi bi-check-lg"></i> Approve
                      </button>
                    </form>
                  <?php endif; ?>
                  <?php if ($key !== 'rejected'): ?>
                    <form class="d-inline" method="post" action="/admin/prompts/reject">
                      <?= csrf_field() ?>
                      <input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>">
                      <button class="btn btn-sm" style="background:#FEF9C3;color:#854D0E;border-color:#FDE68A;">
                        <i class="bi bi-x-lg"></i> Reject
                      </button>
                    </form>
                  <?php endif; ?>
                  <form method="post" action="/admin/prompts/delete" onsubmit="return confirm('Delete permanently?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-danger-outline"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- Users -->
<div class="tab-panel" id="tab-users">
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td style="color:var(--muted);font-size:0.8rem;"><?= (int)$u['id'] ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:0.375rem;">
                <span class="avatar avatar-xs"><?= strtoupper(substr($u['name'] ?? 'U', 0, 2)) ?></span>
                <span style="font-weight:600;font-size:0.875rem;"><?= e($u['name']) ?></span>
              </div>
            </td>
            <td style="font-size:0.8rem;color:var(--muted);"><?= e($u['email']) ?></td>
            <td>
              <?php if ($u['role_name'] === 'super_admin'): ?>
                <span class="badge badge-admin">Admin</span>
              <?php else: ?>
                <span class="badge badge-user">User</span>
              <?php endif; ?>
            </td>
            <td style="font-size:0.8rem;color:var(--muted);">
              <?= !empty($u['created_at']) ? date('M j, Y', strtotime($u['created_at'])) : '—' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.querySelectorAll('#adminTabs .tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('#adminTabs .tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('#adminTabs ~ .tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(btn.dataset.target).classList.add('active');
  });
});
</script>
