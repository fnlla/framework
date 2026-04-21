<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'FnllaPHP') ?></title>
    <style>
        :root { --bg: #f8fafc; --text: #0f172a; --card: #ffffff; --border: #e2e8f0; --accent: #0f766e; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 40px; background: var(--bg); color: var(--text); }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 24px; max-width: 860px; }
        a { color: var(--accent); text-decoration: none; }
        .actions { margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 10px 16px; border-radius: 8px; background: var(--accent); color: #fff; font-weight: 600; }
        .btn.outline { background: transparent; color: var(--accent); border: 1px solid var(--accent); }
        code, pre { background: #0b1020; color: #e2e8f0; padding: 2px 6px; border-radius: 6px; }
        pre { padding: 12px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="card">
        <?= $content ?? '' ?>
    </div>
</body>
</html>
