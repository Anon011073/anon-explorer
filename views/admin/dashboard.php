<?php $content = ob_start(); ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-sm">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-blue-600/10 rounded-xl text-blue-500">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-slate-500">Total Users</p>
                <p class="text-2xl font-bold text-white"><?= $userCount ?></p>
            </div>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-sm">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-purple-600/10 rounded-xl text-purple-500">
                <i data-lucide="share-2" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-slate-500">Active Shares</p>
                <p class="text-2xl font-bold text-white"><?= $shareCount ?></p>
            </div>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-sm">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-green-600/10 rounded-xl text-green-500">
                <i data-lucide="database" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-slate-500">System Version</p>
                <p class="text-2xl font-bold text-white">v1.0.0</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-8 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-slate-800">
        <h3 class="font-bold text-white">Recent Activity</h3>
    </div>
    <div class="p-6 text-slate-500 text-sm">
        Activity logs will appear here.
    </div>
</div>

<?php
$content = ob_get_clean();
echo \App\Core\View::layout('admin', $content, ['title' => 'Dashboard']);
?>
