<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin - Zipply-Drive' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full text-slate-200 overflow-hidden">
    <div class="flex h-full">
        <!-- Admin Sidebar -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col">
            <div class="p-4 border-b border-slate-800 flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                </div>
                <span class="font-bold text-xl tracking-tight text-white">Zipply Admin</span>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                <a href="/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:bg-slate-800 transition-colors">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    Back to Drive
                </a>
                <div class="pt-4 pb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Management</div>
                <a href="/admin" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $_SERVER['REQUEST_URI'] === '/admin' ? 'bg-blue-600/10 text-blue-400 font-medium' : 'text-slate-400 hover:bg-slate-800' ?>">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    Dashboard
                </a>
                <a href="/admin/users" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $_SERVER['REQUEST_URI'] === '/admin/users' ? 'bg-blue-600/10 text-blue-400 font-medium' : 'text-slate-400 hover:bg-slate-800' ?>">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    Users
                </a>
                <a href="/admin/settings" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $_SERVER['REQUEST_URI'] === '/admin/settings' ? 'bg-blue-600/10 text-blue-400 font-medium' : 'text-slate-400 hover:bg-slate-800' ?>">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    System Settings
                </a>
            </nav>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-950 overflow-y-auto">
            <header class="h-16 border-b border-slate-800 flex items-center justify-between px-8 shrink-0 bg-slate-900/50">
                <h1 class="text-lg font-bold text-white"><?= $title ?></h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-400">Admin Session</span>
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-xs font-bold text-white">A</div>
                </div>
            </header>

            <div class="p-8">
                <?= $content ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
