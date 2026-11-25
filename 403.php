<?php require_once __DIR__ . "/config/base.php"; http_response_code(403); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Accès refusé</title>
    <script src="<?= escape_html(url("/assets/vendor/tailwindcss.js")) ?>"></script>
    <style>body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif}</style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center">
    <div class="text-center px-4">
        <h1 class="text-6xl font-bold text-slate-800 mb-2">403</h1>
        <p class="text-slate-600 mb-6">Accès refusé. Vous n'avez pas les droits nécessaires.</p>
        <a href="<?= escape_html(url("/index.php")) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition">Accueil</a>
    </div>
</body>
</html>
