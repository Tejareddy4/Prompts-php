<div class="col-6 col-md-4 col-lg-3">
    <div class="card h-100 shadow-sm">
        <?php if (!empty($item['image_path'])): ?>
            <img loading="lazy" src="<?= e($item['image_path']) ?>" class="card-img-top ratio ratio-1x1 object-fit-cover" alt="<?= e($item['title']) ?>">
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
            <h6 class="card-title mb-1"><a class="text-decoration-none" href="/prompt/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a></h6>
            <p class="small text-muted mb-2">by <?= e($item['author'] ?? 'Unknown') ?></p>
            <div class="mt-auto d-flex justify-content-between small text-muted">
                <span><i class="bi bi-heart-fill text-danger"></i> <?= (int)($item['likes_count'] ?? 0) ?></span>
                <span><i class="bi bi-bookmark-fill"></i> <?= (int)($item['saves_count'] ?? 0) ?></span>
                <span><i class="bi bi-eye-fill"></i> <?= (int)($item['views_count'] ?? 0) ?></span>
            </div>
        </div>
    </div>
</div>
