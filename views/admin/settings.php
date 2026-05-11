<?php $content = ob_start(); ?>

<div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 max-w-2xl" x-data="adminSettings(<?= htmlspecialchars(json_encode($settings)) ?>)">
    <div class="space-y-6">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">Site Title</label>
            <input type="text" x-model="config.app_name" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex items-center justify-between p-4 bg-slate-950 rounded-xl border border-slate-800">
            <div>
                <p class="text-sm font-medium text-white">Enable Registration</p>
                <p class="text-xs text-slate-500">Allow new users to create accounts.</p>
            </div>
            <button @click="config.registration_enabled = config.registration_enabled === '1' ? '0' : '1'"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                    :class="config.registration_enabled === '1' ? 'bg-blue-600' : 'bg-slate-700'">
                <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                      :class="config.registration_enabled === '1' ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">Allowed File Extensions</label>
            <input type="text" x-model="config.allowed_extensions" placeholder="e.g. jpg,png,pdf,zip" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="mt-1 text-[10px] text-slate-500 italic">Comma-separated list (e.g. jpg,png). Leave empty for all.</p>
        </div>

        <div class="flex items-center justify-between p-4 bg-slate-950 rounded-xl border border-slate-800">
            <div>
                <p class="text-sm font-medium text-white">Hide System Files</p>
                <p class="text-xs text-slate-500">Hide files like .env and vendor from Admin root view.</p>
            </div>
            <button @click="config.hide_system_files = config.hide_system_files === '1' ? '0' : '1'"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                    :class="config.hide_system_files === '1' ? 'bg-blue-600' : 'bg-slate-700'">
                <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                      :class="config.hide_system_files === '1' ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">Default Storage Limit (GB)</label>
            <input type="number" step="0.1" :value="config.default_storage_limit / 1024 / 1024 / 1024" @input="config.default_storage_limit = $event.target.value * 1024 * 1024 * 1024" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">Public Folder Path (Absolute)</label>
            <input type="text" x-model="config.public_path" placeholder="e.g. C:/laragon/www/public-files" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="mt-1 text-[10px] text-slate-500 italic">Example: C:/laragon/www/zipply-drive/storage/public (No trailing slash)</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">Root Browser Path (Absolute)</label>
            <input type="text" x-model="config.root_path" placeholder="e.g. C:/laragon/www" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="mt-1 text-[10px] text-slate-500 italic">This is the directory the Admin can browse. Example: C:/laragon/www</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">Default Theme</label>
            <select x-model="config.theme" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="dark">Dark</option>
                <option value="light">Light</option>
            </select>
        </div>

        <div class="pt-6">
            <button @click="save()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-colors flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                Save All Settings
            </button>
        </div>
    </div>
</div>

<script>
    function adminSettings(initial) {
        return {
            config: initial,
            async save() {
                const response = await fetch('<?= \App\Core\App::url('/admin/settings/save') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.config)
                });
                if ((await response.json()).success) {
                    alert('Settings saved successfully!');
                    location.reload();
                }
            }
        }
    }
</script>

<?php
$content = ob_get_clean();
echo \App\Core\View::layout('admin', $content, ['title' => 'System Settings']);
?>
