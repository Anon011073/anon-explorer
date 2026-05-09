<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <title>Install Zipply-Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full text-slate-200 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
        <h1 class="text-2xl font-bold mb-6 text-center text-white">Zipply-Drive Installer</h1>

        <div class="space-y-4 mb-8">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500">Environment Checks</h3>
            <?php foreach ($checks as $name => $ok): ?>
                <div class="flex justify-between items-center text-sm">
                    <span><?= $name ?></span>
                    <span class="<?= $ok ? 'text-green-500' : 'text-red-500' ?> font-bold">
                        <?= $ok ? 'PASSED' : 'FAILED' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <form action="<?= \App\Core\App::url('/install') ?>" method="POST" class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500">Admin Account</h3>
            <input type="text" name="admin_user" placeholder="Admin Username" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            <input type="password" name="admin_pass" placeholder="Admin Password" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-colors mt-4">
                Finish Installation
            </button>
        </form>
    </div>
</body>
</html>
