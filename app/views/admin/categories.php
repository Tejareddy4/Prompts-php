<?php
$iconChoices = [
  'bi-person-standing', 'bi-person-standing-dress', 'bi-heart-fill', 'bi-balloon-heart',
  'bi-people-fill', 'bi-people', 'bi-gem', 'bi-flower1', 'bi-stars', 'bi-emoji-smile',
  'bi-sunglasses', 'bi-camera', 'bi-camera-reels', 'bi-image', 'bi-palette', 'bi-brush',
  'bi-magic', 'bi-moon-stars', 'bi-cake2', 'bi-airplane', 'bi-music-note-beamed',
  'bi-controller', 'bi-tree', 'bi-lightning-fill', 'bi-briefcase', 'bi-mortarboard',
  'bi-pencil-square', 'bi-code-slash', 'bi-megaphone', 'bi-bar-chart-line', 'bi-grid',
];
$colorChoices = ['violet', 'blue', 'pink', 'orange', 'green', 'cyan', 'red', 'indigo', 'teal', 'gray'];
?>

<div class="adm-wrap">
  <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

  <div class="adm-main">
    <div class="adm-topbar">
      <div>
        <h1 class="adm-page-title">Categories</h1>
        <p class="adm-page-sub"><?= count($categories) ?> categories · shown on the homepage in this order</p>
      </div>
      <button class="adm-btn-primary" onclick="openCatForm()">
        <i class="bi bi-plus-lg"></i> New category
      </button>
    </div>

    <?php if ($uncategorized > 0): ?>
      <div class="adm-callout">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
          <strong><?= (int)$uncategorized ?> prompt<?= $uncategorized === 1 ? '' : 's' ?></strong> have no category.
          Assign them from <a href="/admin/prompts">Manage Prompts</a> so they show up when browsing by category.
        </div>
      </div>
    <?php endif; ?>

    <!-- Add / edit form -->
    <div class="adm-card cat-form-card" id="cat-form-card" hidden>
      <div class="adm-card-head">
        <h2 id="cat-form-title">New category</h2>
        <button type="button" class="adm-btn" onclick="closeCatForm()" title="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="adm-card-body">
        <form method="post" action="/admin/categories/save" id="cat-form">
          <?= csrf_field() ?>
          <input type="hidden" name="id" id="cat-id" value="0">

          <div class="cat-form-grid">
            <div>
              <label class="adm-label" for="cat-name">Name <span class="req">*</span></label>
              <input type="text" name="name" id="cat-name" class="adm-input" maxlength="60" required
                     placeholder="e.g. Women" oninput="suggestSlug()">
            </div>
            <div>
              <label class="adm-label" for="cat-slug">Slug (URL)</label>
              <input type="text" name="slug" id="cat-slug" class="adm-input" maxlength="60"
                     placeholder="auto from name" pattern="[a-z0-9\-]*">
            </div>
            <div>
              <label class="adm-label" for="cat-sort">Position</label>
              <input type="number" name="sort_order" id="cat-sort" class="adm-input" min="0" value="0"
                     placeholder="0 = last">
            </div>
          </div>

          <label class="adm-label">Icon</label>
          <div class="cat-icon-picker" id="cat-icon-picker">
            <?php foreach ($iconChoices as $ic): ?>
              <button type="button" class="cat-icon-opt" data-icon="<?= e($ic) ?>"
                      onclick="pickIcon('<?= e($ic) ?>')" title="<?= e($ic) ?>">
                <i class="bi <?= e($ic) ?>"></i>
              </button>
            <?php endforeach; ?>
          </div>
          <input type="text" name="icon" id="cat-icon" class="adm-input cat-icon-input" value="bi-stars"
                 pattern="bi-[a-z0-9\-]+" title="A Bootstrap icon class, e.g. bi-heart-fill"
                 oninput="highlightIcon(this.value)">

          <label class="adm-label">Color</label>
          <div class="cat-color-picker" id="cat-color-picker">
            <?php foreach ($colorChoices as $c): ?>
              <button type="button" class="cat-color-opt cat-<?= e($c) ?>" data-color="<?= e($c) ?>"
                      onclick="pickColor('<?= e($c) ?>')" title="<?= e($c) ?>"></button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="color" id="cat-color" value="violet">

          <div class="cat-form-footer">
            <span class="adm-muted" style="font-size:.75rem;">Preview:</span>
            <span class="cat-badge cat-violet" id="cat-preview"><i class="bi bi-stars"></i> <span>Category</span></span>
            <button type="submit" class="adm-btn-primary" style="margin-left:auto;">
              <i class="bi bi-check-lg"></i> <span id="cat-submit-label">Create category</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Categories table -->
    <div class="adm-card" style="padding:0;overflow:hidden;">
      <table class="adm-table">
        <thead>
          <tr>
            <th style="width:60px;">Order</th>
            <th>Category</th>
            <th>Slug</th>
            <th>Prompts</th>
            <th style="width:110px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
          <tr>
            <td class="adm-muted"><?= (int)$cat['sort_order'] ?></td>
            <td>
              <div class="adm-user-chip">
                <span class="cat-chip cat-<?= e($cat['color']) ?>"><i class="bi <?= e($cat['icon']) ?>"></i></span>
                <div style="font-weight:600;font-size:.8125rem;"><?= e($cat['name']) ?></div>
              </div>
            </td>
            <td class="adm-muted" style="font-size:.75rem;">
              <a href="/category/<?= e($cat['slug']) ?>" target="_blank" style="color:inherit;">/category/<?= e($cat['slug']) ?></a>
            </td>
            <td class="adm-muted"><?= (int)$cat['prompt_count'] ?></td>
            <td>
              <div class="adm-action-group">
                <button class="adm-btn" title="Edit"
                        onclick='openCatForm(<?= json_encode([
                          'id' => (int)$cat['id'],
                          'name' => $cat['name'],
                          'slug' => $cat['slug'],
                          'icon' => $cat['icon'],
                          'color' => $cat['color'],
                          'sort_order' => (int)$cat['sort_order'],
                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                  <i class="bi bi-pencil"></i>
                </button>
                <form method="post" action="/admin/categories/delete" style="display:inline;"
                      onsubmit="return confirm('Delete “<?= e($cat['name']) ?>”? Its <?= (int)$cat['prompt_count'] ?> prompt(s) will become uncategorized.')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                  <button class="adm-btn adm-btn-delete" title="Delete"><i class="bi bi-trash3"></i></button>
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

<script>
const form = {
  card:   document.getElementById('cat-form-card'),
  title:  document.getElementById('cat-form-title'),
  submit: document.getElementById('cat-submit-label'),
  id:     document.getElementById('cat-id'),
  name:   document.getElementById('cat-name'),
  slug:   document.getElementById('cat-slug'),
  sort:   document.getElementById('cat-sort'),
  icon:   document.getElementById('cat-icon'),
  color:  document.getElementById('cat-color'),
};
let slugEdited = false;

document.getElementById('cat-slug').addEventListener('input', () => slugEdited = true);

function openCatForm(cat) {
  const editing = !!cat;
  form.title.textContent  = editing ? 'Edit category' : 'New category';
  form.submit.textContent = editing ? 'Save changes'  : 'Create category';
  form.id.value    = editing ? cat.id : 0;
  form.name.value  = editing ? cat.name : '';
  form.slug.value  = editing ? cat.slug : '';
  form.sort.value  = editing ? cat.sort_order : 0;
  form.icon.value  = editing ? cat.icon : 'bi-stars';
  slugEdited = editing;
  pickColor(editing ? cat.color : 'violet');
  highlightIcon(form.icon.value);
  form.card.hidden = false;
  form.card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  form.name.focus();
}

function closeCatForm() { form.card.hidden = true; }

function suggestSlug() {
  if (!slugEdited) {
    form.slug.value = form.name.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }
  updatePreview();
}

function pickIcon(icon) {
  form.icon.value = icon;
  highlightIcon(icon);
}

function highlightIcon(icon) {
  document.querySelectorAll('.cat-icon-opt').forEach(b =>
    b.classList.toggle('active', b.dataset.icon === icon));
  updatePreview();
}

function pickColor(color) {
  form.color.value = color;
  document.querySelectorAll('.cat-color-opt').forEach(b =>
    b.classList.toggle('active', b.dataset.color === color));
  updatePreview();
}

function updatePreview() {
  const p = document.getElementById('cat-preview');
  p.className = 'cat-badge cat-' + form.color.value;
  p.innerHTML = '<i class="bi ' + form.icon.value + '"></i> <span></span>';
  p.querySelector('span').textContent = form.name.value || 'Category';
}
</script>
