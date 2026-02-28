<div class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="h4 mb-3">Create account</h1>
        <form method="post" action="/register" class="card card-body shadow-sm">
            <?= csrf_field() ?>
            <input class="form-control mb-2" type="text" name="name" placeholder="Full Name" required>
            <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
            <input class="form-control mb-3" type="password" name="password" placeholder="Password (8+ chars)" required>
            <button class="btn btn-dark">Register</button>
        </form>
    </div>
</div>
