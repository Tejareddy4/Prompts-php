<?php
// Parse "[ts] LEVEL: rest" lines into structured entries for display.
$parsed = array_map(function (string $line) {
    if (preg_match('/^\[([^\]]+)\] (DEBUG|INFO|WARNING|ERROR): (.*)$/s', $line, $m)) {
        return ['ts' => $m[1], 'level' => strtolower($m[2]), 'msg' => $m[3]];
    }
    return ['ts' => '', 'level' => 'info', 'msg' => $line];
}, $entries);
$levelCounts = array_count_values(array_column($parsed, 'level'));
$isErrorLog  = str_starts_with($file, 'error-');
?>

<div class="adm-wrap">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

  <div class="adm-main">
    <div class="adm-topbar">
      <div>
        <h1 class="adm-page-title">Logs</h1>
        <p class="adm-page-sub">
          app-*.log = everything · error-*.log = warnings &amp; errors only
        </p>
      </div>
      <?php if ($file !== '' && $entries): ?>
        <form method="post" action="/admin/logs/clear"
              onsubmit="return confirm('Delete <?= e($file) ?>?')">
          <?= csrf_field() ?>
          <input type="hidden" name="file" value="<?= e($file) ?>">
          <button class="adm-btn adm-btn-delete" style="height:36px;padding:0 1rem;">
            <i class="bi bi-trash3"></i> Clear this log
          </button>
        </form>
      <?php endif; ?>
    </div>

    <?php if (empty($files)): ?>
      <div class="adm-empty">
        <i class="bi bi-journal-check"></i>
        <p>No log files yet — that's a good sign. They'll appear in <code>storage/logs/</code> as the app runs.</p>
      </div>
    <?php else: ?>

      <!-- File picker + level filter -->
      <div class="log-toolbar">
        <form method="get" action="/admin/logs">
          <select name="file" class="adm-select-sm" style="height:34px;font-size:.8125rem;" onchange="this.form.submit()">
            <?php foreach ($files as $f): ?>
              <option value="<?= e($f) ?>" <?= $f === $file ? 'selected' : '' ?>><?= e($f) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
        <div class="log-filters">
          <button class="log-filter active" data-level="all">All (<?= count($parsed) ?>)</button>
          <?php foreach (['error', 'warning', 'info', 'debug'] as $lv): if (empty($levelCounts[$lv])) continue; ?>
            <button class="log-filter lf-<?= $lv ?>" data-level="<?= $lv ?>">
              <?= ucfirst($lv) ?> (<?= $levelCounts[$lv] ?>)
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if (empty($parsed)): ?>
        <div class="adm-empty">
          <i class="bi bi-journal-check"></i>
          <p>This file is empty<?= $isErrorLog ? ' — nothing broke that day 🎉' : '' ?>.</p>
        </div>
      <?php else: ?>
        <div class="adm-card" style="padding:0;overflow:hidden;">
          <div class="log-list" id="log-list">
            <?php foreach ($parsed as $entry): ?>
              <div class="log-row" data-level="<?= e($entry['level']) ?>">
                <span class="log-badge lb-<?= e($entry['level']) ?>"><?= e(strtoupper($entry['level'])) ?></span>
                <span class="log-ts"><?= e($entry['ts'] !== '' ? substr($entry['ts'], 11) : '—') ?></span>
                <span class="log-msg"><?= e($entry['msg']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <p class="adm-muted" style="font-size:.72rem;margin-top:.5rem;">
          Newest first · showing last <?= count($parsed) ?> entries of <code>storage/logs/<?= e($file) ?></code>
        </p>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</div>

<script>
document.querySelectorAll('.log-filter').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.log-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const level = btn.dataset.level;
    document.querySelectorAll('.log-row').forEach(row => {
      row.style.display = (level === 'all' || row.dataset.level === level) ? '' : 'none';
    });
  });
});
</script>
