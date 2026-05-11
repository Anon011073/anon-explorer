<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared File - Zipply-Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full text-slate-200 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl text-center">
        <div class="w-20 h-20 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="file" class="w-10 h-10 text-blue-500"></i>
        </div>
        <h1 class="text-xl font-bold mb-2 truncate"><?= htmlspecialchars($meta['name']) ?></h1>
        <p class="text-slate-500 text-sm mb-8"><?= $meta['size'] > 0 ? 'Size: ' . round($meta['size'] / 1024 / 1024, 2) . ' MB' : 'Folder' ?></p>

        <a href="/s/<?= $share['token'] ?>/download" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
            <i data-lucide="download" class="w-5 h-5"></i>
            Download File
        </a>

        <div class="mt-8 pt-8 border-t border-slate-800">
            <p class="text-xs text-slate-600">Shared via Zipply-Drive</p>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
