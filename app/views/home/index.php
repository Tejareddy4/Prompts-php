<section class="hero-panel p-4 p-md-5 mb-4">
    <p class="text-uppercase small fw-semibold mb-2">Latest prompt marketplace</p>
    <h1 class="display-6 mb-2">Discover, save, and ship better prompts faster.</h1>
    <p class="text-muted mb-0">Use filters to quickly find top-performing prompts for your current task.</p>
</section>

<section class="filter-panel card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="/" class="row g-3 align-items-end">
            <div class="col-12 col-md-7">
                <label for="q" class="form-label">Search prompts</label>
                <input id="q" name="q" class="form-control" placeholder="Search by title, description, or content" value="<?= e($filters['q'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-3">
                <label for="sort" class="form-label">Sort by</label>
                <select id="sort" name="sort" class="form-select">
                    <option value="newest" <?= ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="most_liked" <?= ($filters['sort'] ?? '') === 'most_liked' ? 'selected' : '' ?>>Most liked</option>
                    <option value="most_saved" <?= ($filters['sort'] ?? '') === 'most_saved' ? 'selected' : '' ?>>Most saved</option>
                    <option value="most_viewed" <?= ($filters['sort'] ?? '') === 'most_viewed' ? 'selected' : '' ?>>Most viewed</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-dark" type="submit">Apply</button>
            </div>
        </form>
    </div>
</section>

<h2 class="h4 mb-3">Trending Prompts</h2>
<div id="prompt-grid" class="row g-3">
    <?php foreach ($prompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?>
</div>
<?php if (!empty($prompts)): ?>
    <div class="text-center mt-4">
        <button id="load-more" class="btn btn-outline-primary px-4" data-page="2">Load More</button>
    </div>
<?php else: ?>
    <div class="alert alert-light border text-center">No prompts found for your filters. Try changing your search or sort.</div>
<?php endif; ?>
