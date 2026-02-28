<h1 class="h3 mb-3">Trending Prompts</h1>
<div id="prompt-grid" class="row g-3">
    <?php foreach ($prompts as $item): require __DIR__ . '/../partials/prompt-card.php'; endforeach; ?>
</div>
<div class="text-center mt-4">
    <button id="load-more" class="btn btn-outline-primary" data-page="2">Load More</button>
</div>
