<h1 class="h4 mb-3">Submit a Prompt</h1>
<form method="post" action="/prompts" enctype="multipart/form-data" class="card card-body shadow-sm">
    <?= csrf_field() ?>
    <input class="form-control mb-2" type="text" name="title" placeholder="Prompt title" required>
    <textarea class="form-control mb-2" name="description" placeholder="Short description" rows="3"></textarea>
    <textarea class="form-control mb-2" name="prompt_text" placeholder="Write your full prompt" rows="6" required></textarea>
    <input class="form-control mb-3" type="file" name="image" accept="image/jpeg,image/png,image/webp">
    <button class="btn btn-primary">Submit for Review</button>
</form>
