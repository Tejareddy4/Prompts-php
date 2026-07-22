<a href="/prompt/<?= e($item['slug']) ?>" class="pcard">
  <?php if (!empty($item['image_path'])): ?>
    <div class="pcard-thumb">
      <img loading="lazy" src="<?= e($item['image_path']) ?>" alt="<?= e($item['title']) ?>">
      <?php if (!empty($item['category_slug'])): ?>
        <span class="pcard-cat cat-<?= e($item['category_color']) ?>">
          <i class="bi <?= e($item['category_icon']) ?>"></i> <?= e($item['category_name']) ?>
        </span>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  <div class="pcard-body">
    <h3 class="pcard-title"><?= e($item['title']) ?></h3>
    <?php if (!empty($item['description'])): ?>
      <p class="pcard-desc"><?= e($item['description']) ?></p>
    <?php endif; ?>
    <?php if (!empty($item['prompt_text'])): ?>
      <button type="button" class="pcard-copy js-card-copy"
              data-id="<?= (int)$item['id'] ?>"
              data-copy="<?= e($item['prompt_text']) ?>">
        Copy
      </button>
    <?php endif; ?>
  </div>
</a>
