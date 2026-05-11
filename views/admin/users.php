<?php $content = ob_start(); ?>

<div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden" x-data="userAdmin()">
    <table class="w-full text-left">
        <thead>
            <tr class="text-xs font-semibold text-slate-500 border-b border-slate-800 uppercase tracking-wider">
                <th class="px-6 py-4">User</th>
                <th class="px-6 py-4">Role</th>
                <th class="px-6 py-4">Quota</th>
                <th class="px-6 py-4">Joined</th>
                <th class="px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/50">
            <?php foreach ($users as $user): ?>
            <tr class="hover:bg-slate-800/30 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-slate-800 rounded-full flex items-center justify-center text-xs font-bold"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                        <span class="text-sm font-medium text-white"><?= htmlspecialchars($user['username']) ?></span>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider <?= $user['role'] === 'admin' ? 'bg-blue-600/20 text-blue-400' : 'bg-slate-800 text-slate-400' ?>">
                        <?= $user['role'] ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-400">
                    <div class="flex flex-col">
                        <span class="text-white font-medium"><?= round(($user['usage'] ?? 0) / 1024 / 1024, 1) ?> MB</span>
                        <span class="text-[10px] text-slate-500 uppercase">of <?= round($user['storage_limit'] / 1024 / 1024 / 1024, 2) ?> GB</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500">
                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <button @click="editUser(<?= htmlspecialchars(json_encode($user)) ?>)" class="p-1.5 text-slate-500 hover:text-white transition-colors">
                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                        </button>
                        <?php if ($user['role'] !== 'admin'): ?>
                        <button @click="deleteUser(<?= $user['id'] ?>)" class="p-1.5 text-slate-500 hover:text-red-400 transition-colors">
                            <i data-lucide="user-minus" class="w-4 h-4"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Edit User Modal -->
    <template x-if="editingUser">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
                <h3 class="text-lg font-bold mb-4">Edit User: <span x-text="editingUser.username"></span></h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Role</label>
                        <select x-model="editingUser.role" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Storage Limit (GB)</label>
                        <input type="number" step="0.1" :value="editingUser.storage_limit / 1024 / 1024 / 1024" @input="editingUser.storage_limit = $event.target.value * 1024 * 1024 * 1024" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button @click="saveUser()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition-colors">Save Changes</button>
                        <button @click="editingUser = null" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 rounded-lg transition-colors">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function userAdmin() {
        return {
            editingUser: null,
            editUser(user) {
                this.editingUser = {...user};
            },
            async saveUser() {
                const response = await fetch('<?= \App\Core\App::url('/admin/users/update') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.editingUser)
                });
                if ((await response.json()).success) location.reload();
            },
            async deleteUser(id) {
                if (!confirm('Are you sure you want to delete this user?')) return;
                const response = await fetch('<?= \App\Core\App::url('/admin/users/delete') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({id: id})
                });
                if ((await response.json()).success) location.reload();
            }
        }
    }
</script>

<?php
$content = ob_get_clean();
echo \App\Core\View::layout('admin', $content, ['title' => 'User Management']);
?>
