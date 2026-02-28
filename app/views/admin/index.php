<h1 class="h3 mb-3">Super Admin Dashboard</h1>
<div class="row g-3 mb-4">
    <div class="col"><div class="card card-body">Total Prompts: <?= (int)$analytics['total_prompts'] ?></div></div>
    <div class="col"><div class="card card-body">Pending: <?= (int)$analytics['pending_prompts'] ?></div></div>
    <div class="col"><div class="card card-body">Likes: <?= (int)$analytics['total_likes'] ?></div></div>
</div>

<?php foreach (['Pending' => $pending, 'Approved' => $approved, 'Rejected' => $rejected] as $label => $rows): ?>
    <h2 class="h5 mt-4"><?= $label ?></h2>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <tr><th>Title</th><th>Author</th><th>Status Action</th><th>Delete</th></tr>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['title']) ?></td>
                    <td><?= e($r['author']) ?></td>
                    <td>
                        <form class="d-inline" method="post" action="/admin/prompts/approve"><?= csrf_field() ?><input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-success btn-sm">Approve</button></form>
                        <form class="d-inline" method="post" action="/admin/prompts/reject"><?= csrf_field() ?><input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-warning btn-sm">Reject</button></form>
                    </td>
                    <td><form method="post" action="/admin/prompts/delete"><?= csrf_field() ?><input type="hidden" name="prompt_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endforeach; ?>

<h2 class="h5 mt-4">Users</h2>
<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>
        <?php foreach ($users as $u): ?>
            <tr><td><?= (int)$u['id'] ?></td><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role_name']) ?></td></tr>
        <?php endforeach; ?>
    </table>
</div>
