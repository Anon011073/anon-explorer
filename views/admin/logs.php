<?php $content = ob_start(); ?>

<div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-slate-800 flex justify-between items-center">
        <h3 class="font-bold text-white"><?= $action ? 'Recent ' . ucfirst($action) . 's' : 'System Activity Logs' ?></h3>
        <a href="<?= \App\Core\App::url('/admin') ?>" class="text-xs text-slate-500 hover:text-white transition-colors">Back to Dashboard</a>
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
                        <?= date('M j, Y H:i', strtotime($log['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">No more logs found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="p-6 bg-slate-950/30 flex justify-center gap-4">
        <?php if ($page > 1): ?>
            <a href="<?= \App\Core\App::url('/admin/logs?page=' . ($page - 1) . ($action ? '&action=' . $action : '')) ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-lg transition-colors">Previous Page</a>
        <?php endif; ?>

        <?php if (count($logs) === 20): ?>
            <a href="<?= \App\Core\App::url('/admin/logs?page=' . ($page + 1) . ($action ? '&action=' . $action : '')) ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-lg transition-colors">View Older</a>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
echo \App\Core\View::layout('admin', $content, ['title' => 'Detailed Logs']);
?>
