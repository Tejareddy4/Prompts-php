<div class="col-12 col-md-6 col-lg-4">
    <div class="card h-100 shadow-sm border-0 prompt-card">
        <?php if (!empty($item['image_path'])): ?>
            <img loading="lazy" src="<?= e($item['image_path']) ?>" class="card-img-top object-fit-cover" alt="<?= e($item['title']) ?>">
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
            <h6 class="card-title mb-1"><a class="text-decoration-none stretched-link" href="/prompt/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a></h6>
            <p class="small text-muted mb-3">by <?= e($item['author'] ?? 'Unknown') ?></p>
            <div class="mt-auto d-flex justify-content-between small text-muted pt-2 border-top">
                <span><i class="bi bi-heart-fill text-danger"></i> <?= (int)($item['likes_count'] ?? 0) ?></span>
                <span><i class="bi bi-bookmark-fill"></i> <?= (int)($item['saves_count'] ?? 0) ?></span>
                <span><i class="bi bi-eye-fill"></i> <?= (int)($item['views_count'] ?? 0) ?></span>
            </div>
        </div>
    </div>
</div>
