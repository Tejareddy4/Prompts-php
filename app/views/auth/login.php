<div class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="h4 mb-3">Login</h1>
        <form method="post" action="/login" class="card card-body shadow-sm">
            <?= csrf_field() ?>
            <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
            <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
            <button class="btn btn-dark">Login</button>
            <a class="btn btn-outline-danger mt-2" href="/auth/google">Continue with Google</a>
        </form>
    </div>
</div>
