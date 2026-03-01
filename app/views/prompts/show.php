<article class="card shadow-sm">
    <?php if (!empty($prompt['image_path'])): ?>
        <img src="<?= e($prompt['image_path']) ?>" class="card-img-top" alt="<?= e($prompt['title']) ?>">
    <?php endif; ?>
    <div class="card-body">
        <h1 class="h3"><?= e($prompt['title']) ?></h1>
        <p class="text-muted">by <?= e($prompt['author']) ?></p>
        <p><?= nl2br(e($prompt['description'])) ?></p>
        <pre class="bg-light p-3 rounded" id="prompt-text"><?= e($prompt['prompt_text']) ?></pre>

        <div class="d-flex flex-wrap gap-2" data-prompt-id="<?= (int)$prompt['id'] ?>">
            <button class="btn <?= !empty($prompt['is_liked']) ? 'btn-danger' : 'btn-outline-danger' ?> js-like" aria-pressed="<?= !empty($prompt['is_liked']) ? 'true' : 'false' ?>"><i class="bi bi-heart-fill"></i> Likes <span class="count"><?= (int)$prompt['likes_count'] ?></span></button>
            <button class="btn <?= !empty($prompt['is_saved']) ? 'btn-secondary' : 'btn-outline-secondary' ?> js-save" aria-pressed="<?= !empty($prompt['is_saved']) ? 'true' : 'false' ?>"><i class="bi bi-bookmark"></i> Saves <span class="count"><?= (int)$prompt['saves_count'] ?></span></button>
            <button class="btn btn-outline-primary js-copy"><i class="bi bi-clipboard"></i> Copy (<span class="count"><?= (int)$prompt['copies_count'] ?></span>)</button>
            <span class="badge text-bg-light"><i class="bi bi-eye"></i> <?= (int)$prompt['views_count'] ?> views</span>
        </div>
    </div>
</article>
