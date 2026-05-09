<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Protected - Zipply-Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full text-slate-200 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-red-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="lock" class="w-8 h-8 text-red-500"></i>
            </div>
            <h1 class="text-xl font-bold">Password Protected</h1>
            <p class="text-slate-500 text-sm">Enter the password to access this share.</p>
        </div>

        <form action="/s/<?= $token ?>/auth" method="POST" class="space-y-4">
            <input type="password" name="password" placeholder="Password" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition-colors">
                Access Share
            </button>
        </form>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
