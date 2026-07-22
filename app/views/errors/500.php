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
           background:#07070F; color:#F0EEFF; }
    .box { text-align:center; padding:2rem; max-width:640px; }
    .code { font-size:3.5rem; font-weight:800; margin:0;
            background:linear-gradient(135deg,#A78BFA,#67E8F9);
            -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
    h1 { font-size:1.25rem; margin:.5rem 0 .75rem; }
    p { color:rgba(240,238,255,.52); font-size:.9375rem; line-height:1.5; }
    a.btn { display:inline-block; margin-top:1rem; padding:.625rem 1.5rem; border-radius:999px;
            background:linear-gradient(135deg,#7C3AED,#06B6D4); color:#fff; text-decoration:none;
            font-weight:600; font-size:.875rem; }
    pre { text-align:left; background:rgba(124,58,237,.08); border:1px solid rgba(124,58,237,.22);
          color:#FCA5A5; font-size:.75rem; padding:1rem;
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
