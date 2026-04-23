<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 gap-2">
  <div class="page-header mb-0">
    <div class="dash-stat-icon purple" style="width:40px;height:40px;border-radius:10px;">
      <i class="bi bi-shield-check"></i>
    </div>
    <h1 style="font-size:1.35rem;">Admin Dashboard</h1>
  </div>
  <a href="/" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to site</a>
</div>

<!-- Analytics -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="admin-stat-card">
      <div class="stat-value text-primary"><?= (int)$analytics['total_prompts'] ?></div>
      <div class="stat-label">Total Prompts</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="admin-stat-card">
      <div class="stat-value text-warning"><?= (int)$analytics['pending_prompts'] ?></div>
      <div class="stat-label">Pending Review</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="admin-stat-card">
      <div class="stat-value text-success"><?= (int)$analytics['approved_prompts'] ?></div>
      <div class="stat-label">Published</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="admin-stat-card">
      <div class="stat-value text-danger"><?= (int)$analytics['total_likes'] ?></div>
      <div class="stat-label">Total Likes</div>
    </div>
  </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs" id="adminTabs" role="tablist">
  <li class="nav-item">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pending">
      Pending <span class="badge bg-warning text-dark ms-1" style="font-size:.7rem;"><?= count($pending) ?></span>
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-approved">
      Approved <span class="badge bg-success ms-1" style="font-size:.7rem;"><?= count($approved) ?></span>
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rejected">
      Rejected <span class="badge bg-danger ms-1" style="font-size:.7rem;"><?= count($rejected) ?></span>
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-users">
      Users <span class="badge bg-secondary ms-1" style="font-size:.7rem;"><?= count($users) ?></span>
    </button>
  </li>
</ul>

<div class="tab-content">
  <?php
  $sections = ['pending' => $pending, 'approved' => $approved, 'rejected' => $rejected];
  foreach ($sections as $key => $rows):
  ?>
  <div class="tab-pane fade <?= $key === 'pending' ? 'show active' : '' ?>" id="tab-<?= $key ?>">
    <?php if (empty($rows)): ?>
      <div class="empty-state py-4">
        <i class="bi bi-check-circle d-block"></i>
        <p class="mt-2 small">No <?= $key ?> prompts.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Author</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td>
                  <?php if ($key === 'approved'): ?>
                    <a href="/prompt/<?= e($r['slug']) ?>" target="_blank" class="fw-medium text-decoration-none">
                      <?= e($r['title']) ?> <i class="bi bi-box-arrow-up-right" style="font-size:.7rem;"></i>
                    </a>
                  <?php else: ?>
                    <span class="fw-medium"><?= e($r['title']) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="avatar-xs"><?= strtoupper(substr($r['author'] ?? 'U', 0, 2)) ?></span>
                    <span class="text-muted small"><?= e($r['author']) ?></span>
                  </div>
                </td>
                <td class="text-muted small"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                <td>
                  <div class="d-flex gap-1 flex-wrap">
                    <?php if ($key !== 'approved'): ?>
                      <form class="d-inline" method="post" action="/admin/prompts/approve">
                        <?= csrf_field() ?>
                        <input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-success btn-sm">
                          <i class="bi bi-check-lg"></i> Approve
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php if ($key !== 'rejected'): ?>
                      <form class="d-inline" method="post" action="/admin/prompts/reject">
                        <?= csrf_field() ?>
                        <input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-warning btn-sm text-dark">
                          <i class="bi bi-x-lg"></i> Reject
                        </button>
                      </form>
                    <?php endif; ?>
                    <form class="d-inline" method="post" action="/admin/prompts/delete"
                          onsubmit="return confirm('Delete this prompt permanently?')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>">
                      <button class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i>
                      </button>
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

  <!-- Users tab -->
  <div class="tab-pane fade" id="tab-users">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td class="text-muted small"><?= (int)$u['id'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="avatar-xs"><?= strtoupper(substr($u['name'] ?? 'U', 0, 2)) ?></span>
                  <?= e($u['name']) ?>
                </div>
              </td>
              <td class="text-muted small"><?= e($u['email']) ?></td>
              <td>
                <?php if ($u['role_name'] === 'super_admin'): ?>
                  <span class="badge text-bg-warning">Admin</span>
                <?php else: ?>
                  <span class="badge text-bg-light text-secondary">User</span>
                <?php endif; ?>
              </td>
              <td class="text-muted small">
                <?= !empty($u['created_at']) ? date('M j, Y', strtotime($u['created_at'])) : '—' ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
