<?php $content = ob_start(); ?>

<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-slate-800">
            <h2 class="text-2xl font-bold text-white">Your Profile</h2>
            <p class="text-slate-500 text-sm">Update your account settings and profile picture.</p>
        </div>

        <form action="/profile" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <div class="flex items-center gap-6 mb-8">
                <div class="shrink-0">
                    <?php if ($user['avatar']): ?>
                        <img class="h-20 w-20 object-cover rounded-full ring-2 ring-blue-600" src="<?= $user['avatar'] ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="h-20 w-20 rounded-full bg-slate-800 flex items-center justify-center text-2xl font-bold text-blue-500 ring-2 ring-slate-700">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <label class="block">
                    <span class="sr-only">Choose profile photo</span>
                    <input type="file" name="avatar" class="block w-full text-sm text-slate-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-600/10 file:text-blue-400
                        hover:file:bg-blue-600/20 transition-all cursor-pointer">
                </label>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="pt-6 border-t border-slate-800 flex justify-between items-center">
                <div class="text-xs text-slate-500">
                    Joined <?= date('F j, Y', strtotime($user['created_at'])) ?>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded-xl transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <div class="mt-8 flex justify-center">
        <a href="/" class="text-sm font-medium text-slate-500 hover:text-white transition-colors flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to My Files
        </a>
    </div>
</div>

<script>lucide.createIcons();</script>

<?php
$content = ob_get_clean();
// We can use a simple layout or reuse index one. Let's make a basic one for profiles.
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <title>Profile - Zipply-Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full text-slate-200">
    <?= $content ?>
</body>
</html>
