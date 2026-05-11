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
    <div class="p-6 border-b border-slate-800 flex justify-between items-center">
        <div class="flex items-center gap-6">
            <h3 class="font-bold text-white">Recent Activity</h3>
            <div class="flex items-center gap-3">
                <a href="<?= \App\Core\App::url('/admin/logs?action=upload') ?>" class="text-[10px] font-bold text-blue-500 hover:text-blue-400 uppercase tracking-widest bg-blue-500/10 px-2 py-1 rounded border border-blue-500/20 transition-colors">View New Uploads</a>
                <a href="<?= \App\Core\App::url('/admin/logs?action=share') ?>" class="text-[10px] font-bold text-purple-500 hover:text-purple-400 uppercase tracking-widest bg-purple-500/10 px-2 py-1 rounded border border-purple-500/20 transition-colors">View Shares</a>
            </div>
        </div>
        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Last 20 events</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="text-[10px] font-bold text-slate-500 border-b border-slate-800 uppercase tracking-widest bg-slate-950/50">
                    <th class="px-6 py-3">User</th>
                    <th class="px-6 py-3">Action</th>
                    <th class="px-6 py-3">Details</th>
                    <th class="px-6 py-3">IP Address</th>
                    <th class="px-6 py-3 text-right">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach ($logs as $log): ?>
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 font-medium text-slate-300"><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter bg-blue-600/10 text-blue-400 border border-blue-600/20">
                            <?= str_replace('_', ' ', $log['action']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-500 max-w-xs truncate" title="<?= htmlspecialchars($log['details']) ?>">
                        <?= htmlspecialchars($log['details']) ?>
                    </td>
                    <td class="px-6 py-4 text-slate-500 font-mono text-[11px]"><?= $log['ip_address'] ?></td>
                    <td class="px-6 py-4 text-right text-slate-600 text-[11px]">
                        <?= date('M j, H:i', strtotime($log['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">No activity logs found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
echo \App\Core\View::layout('admin', $content, ['title' => 'Dashboard']);
?>
