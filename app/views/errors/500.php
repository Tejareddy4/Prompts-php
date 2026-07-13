<?php /** Standalone 500 page — no layout/DB dependencies, safe to render mid-crash. @var Throwable|null $detail */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Something went wrong — PromptShare</title>
  <style>
    body { margin:0; font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
           min-height:100vh; display:flex; align-items:center; justify-content:center;
           background:#F8FAFC; color:#0F172A; }
    @media (prefers-color-scheme: dark) { body { background:#0F172A; color:#F1F5F9; } }
    .box { text-align:center; padding:2rem; max-width:640px; }
    .code { font-size:3.5rem; font-weight:800; color:#7C3AED; margin:0; }
    h1 { font-size:1.25rem; margin:.5rem 0 .75rem; }
    p { color:#64748B; font-size:.9375rem; line-height:1.5; }
    a.btn { display:inline-block; margin-top:1rem; padding:.625rem 1.5rem; border-radius:999px;
            background:#7C3AED; color:#fff; text-decoration:none; font-weight:600; font-size:.875rem; }
    pre { text-align:left; background:#1E293B; color:#FCA5A5; font-size:.75rem; padding:1rem;
          border-radius:8px; overflow-x:auto; margin-top:1.5rem; white-space:pre-wrap; }
  </style>
</head>
<body>
  <div class="box">
    <p class="code">500</p>
    <h1>Something went wrong on our side</h1>
    <p>The error has been logged and we'll look into it. Please try again in a moment.</p>
    <a class="btn" href="/">Back to home</a>
    <?php if (!empty($detail)): ?>
      <pre><?= htmlspecialchars(get_class($detail) . ': ' . $detail->getMessage()
          . "\nat " . $detail->getFile() . ':' . $detail->getLine()
          . "\n\n" . $detail->getTraceAsString(), ENT_QUOTES, 'UTF-8') ?></pre>
    <?php endif; ?>
  </div>
</body>
</html>
