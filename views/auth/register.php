<?php
$content = ob_start(); ?>

<form class="space-y-6" action="<?= \App\Core\App::url('/register') ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generate() ?>">
    <?php if (isset($error)): ?>
        <div class="bg-red-900/50 border border-red-500 text-red-200 p-3 rounded text-sm">
            <?= $error ?>
        </div>
    <?php endif; ?>
    <div>
        <label for="username" class="block text-sm font-medium text-slate-300">Username</label>
        <div class="mt-1">
            <input id="username" name="username" type="text" required class="appearance-none block w-full px-3 py-2 border border-slate-700 rounded-md shadow-sm placeholder-slate-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-slate-800 text-white sm:text-sm">
        </div>
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
        <div class="mt-1">
            <input id="password" name="password" type="password" required class="appearance-none block w-full px-3 py-2 border border-slate-700 rounded-md shadow-sm placeholder-slate-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-slate-800 text-white sm:text-sm">
        </div>
    </div>

    <div>
        <label for="confirm_password" class="block text-sm font-medium text-slate-300">Confirm Password</label>
        <div class="mt-1">
            <input id="confirm_password" name="confirm_password" type="password" required class="appearance-none block w-full px-3 py-2 border border-slate-700 rounded-md shadow-sm placeholder-slate-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-slate-800 text-white sm:text-sm">
        </div>
    </div>

    <div>
        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            Register
        </button>
    </div>
</form>

<div class="mt-6 text-center">
    <a href="<?= \App\Core\App::url('/login') ?>" class="text-sm font-medium text-blue-400 hover:text-blue-300">
        Already have an account? Sign in
    </a>
</div>

<?php
$content = ob_get_clean();
echo \App\Core\View::layout('auth', $content, ['title' => 'Register - Zipply-Drive']);
?>
