<?php
$allPrompts = array_merge(
    array_map(fn($p) => array_merge($p, ['_status' => 'pending']),  $pending),
    array_map(fn($p) => array_merge($p, ['_status' => 'approved']), $approved),
    array_map(fn($p) => array_merge($p, ['_status' => 'rejected']), $rejected),
);
?>

<div class="adm-wrap">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

  <div class="adm-main">
    <div class="adm-topbar">
      <div>
        <h1 class="adm-page-title">Prompts</h1>
        <p class="adm-page-sub"><?= count($pending) ?> pending · <?= count($approved) ?> live · <?= count($rejected) ?> rejected</p>
      </div>
      <a href="/prompts/create" class="nbtn nbtn-primary" style="font-size:.8rem;">
        <i class="bi bi-plus-lg"></i> New Prompt
      </a>
    </div>

    <!-- Tab bar -->
    <div class="adm-tabs">
      <button class="adm-tab active" data-target="tab-pending">
        Pending <span class="adm-tab-badge adm-badge-warn"><?= count($pending) ?></span>
      </button>
      <button class="adm-tab" data-target="tab-approved">
        Approved <span class="adm-tab-badge adm-badge-ok"><?= count($approved) ?></span>
      </button>
      <button class="adm-tab" data-target="tab-rejected">
        Rejected <span class="adm-tab-badge adm-badge-muted"><?= count($rejected) ?></span>
      </button>
    </div>

    <!-- Search bar -->
    <div style="margin-bottom:1rem;">
      <input type="text" id="prompt-search" class="adm-search-input"
             placeholder="Filter by title or author…" oninput="filterPrompts(this.value)">
    </div>

    <?php
    $sections = [
      'tab-pending'  => ['rows' => $pending,  'status' => 'pending'],
      'tab-approved' => ['rows' => $approved, 'status' => 'approved'],
      'tab-rejected' => ['rows' => $rejected, 'status' => 'rejected'],
    ];
    foreach ($sections as $tabId => $section):
      $rows = $section['rows'];
      $status = $section['status'];
    ?>
    <div class="adm-tab-panel <?= $tabId === 'tab-pending' ? 'active' : '' ?>" id="<?= $tabId ?>">
      <?php if (empty($rows)): ?>
        <div class="adm-empty">
          <i class="bi bi-inbox"></i>
          <p>No <?= $status ?> prompts.</p>
        </div>
      <?php else: ?>
        <div class="adm-card" style="padding:0;overflow:hidden;">
          <table class="adm-table prompt-table">
            <thead>
              <tr>
                <th style="width:40%;">Title</th>
                <th>Author</th>
                <th>Stats</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
              <tr data-search="<?= strtolower(e($r['title']) . ' ' . e($r['author'])) ?>">
                <td>
                  <div class="adm-prompt-title"><?= e($r['title']) ?></div>
                  <?php if (!empty($r['description'])): ?>
                    <div class="adm-prompt-desc"><?= e(substr($r['description'], 0, 70)) ?>…</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="adm-user-chip">
                    <span class="avatar avatar-xs"><?= strtoupper(substr($r['author'] ?? 'U', 0, 2)) ?></span>
                    <?= e($r['author']) ?>
                  </div>
                </td>
                <td>
                  <div class="adm-stats-mini">
                    <span title="Likes"><i class="bi bi-heart-fill" style="color:#EF4444;"></i> <?= (int)($r['likes_count'] ?? 0) ?></span>
                    <span title="Views"><i class="bi bi-eye-fill" style="color:var(--muted);"></i> <?= (int)($r['views_count'] ?? 0) ?></span>
                  </div>
                </td>
                <td><span class="adm-muted"><?= date('M j, Y', strtotime($r['created_at'])) ?></span></td>
                <td>
                  <div class="adm-action-group">
                    <?php if ($status === 'approved'): ?>
                      <a href="/prompt/<?= e($r['slug']) ?>" target="_blank" class="adm-btn adm-btn-view" title="View live">
                        <i class="bi bi-box-arrow-up-right"></i>
                      </a>
                    <?php endif; ?>
                    <?php if ($status !== 'approved'): ?>
                      <form method="post" action="/admin/prompts/approve" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>">
                        <button class="adm-btn adm-btn-approve" title="Approve"><i class="bi bi-check-lg"></i></button>
                      </form>
                    <?php endif; ?>
                    <?php if ($status !== 'rejected'): ?>
                      <form method="post" action="/admin/prompts/reject" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>">
                        <button class="adm-btn adm-btn-reject" title="Reject"><i class="bi bi-x-lg"></i></button>
                      </form>
                    <?php endif; ?>
                    <form method="post" action="/admin/prompts/delete"
                          onsubmit="return confirm('Permanently delete this prompt?')" style="display:inline;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>">
                      <button class="adm-btn adm-btn-delete" title="Delete"><i class="bi bi-trash3"></i></button>
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

  </div>
</div>

<script>
document.querySelectorAll('.adm-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.adm-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.adm-tab-panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById(tab.dataset.target).classList.add('active');
  });
});

function filterPrompts(q) {
  q = q.toLowerCase();
  document.querySelectorAll('.prompt-table tbody tr').forEach(row => {
    row.style.display = row.dataset.search.includes(q) ? '' : 'none';
  });
}
</script>
