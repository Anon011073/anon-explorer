<!DOCTYPE html>
<html lang="en" class="h-full" :class="theme === 'light' ? 'bg-white' : 'bg-slate-950'">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Zipply-Drive' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
    <link rel="stylesheet" data-name="vs/editor/editor.main" href="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/editor/editor.main.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full overflow-hidden transition-colors duration-300"
      :class="theme === 'light' ? 'text-slate-900' : 'text-slate-200'"
      x-data="app()">

    <div class="flex h-full">
        <!-- Sidebar -->
        <aside class="w-64 border-r flex flex-col hidden md:flex"
               :class="theme === 'light' ? 'bg-slate-50 border-slate-200' : 'bg-slate-900 border-slate-800'">
            <div class="p-4 border-b flex items-center gap-2"
                 :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i data-lucide="zap" class="w-5 h-5 text-white"></i>
                </div>
                <span class="font-bold text-xl tracking-tight text-white">Zipply</span>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                <a href="#" @click.prevent="setContext('private')"
                   :class="context === 'private' ? 'bg-blue-600/10 text-blue-500 font-medium' : (theme === 'light' ? 'text-slate-600 hover:bg-slate-200' : 'text-slate-400 hover:bg-slate-800')"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="folder" class="w-5 h-5"></i>
                    My Space
                </a>
                <a href="#" @click.prevent="setContext('public')"
                   :class="context === 'public' ? 'bg-blue-600/10 text-blue-400 font-medium' : 'text-slate-400 hover:bg-slate-800'"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="globe" class="w-5 h-5"></i>
                    Public Files
                </a>
                <?php if (\App\Services\AuthService::isAdmin()): ?>
                <a href="#" @click.prevent="setContext('root')"
                   :class="context === 'root' ? 'bg-blue-600/10 text-blue-400 font-medium' : 'text-slate-400 hover:bg-slate-800'"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="database" class="w-5 h-5"></i>
                    Root Browser
                </a>
                <a href="<?= \App\Core\App::url('/admin') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:bg-slate-800 transition-colors border-t border-slate-800/50 mt-4">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                    Admin Panel
                </a>
                <?php endif; ?>

                <div class="pt-4 pb-2 text-[10px] font-semibold text-slate-600 uppercase tracking-wider px-3">Other</div>
                <a href="<?= \App\Core\App::url('/profile') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:bg-slate-800 transition-colors">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    My Profile
                </a>
            </nav>

            <div class="p-4 border-t" :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'">
                <div class="flex justify-between text-xs text-slate-500 mb-2">
                    <span>Storage</span>
                    <span>45%</span>
                </div>
                <div class="w-full rounded-full h-1.5 mb-2" :class="theme === 'light' ? 'bg-slate-200' : 'bg-slate-800'">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: 45%"></div>
                </div>
                <p class="text-[10px] text-slate-500">450 MB of 1 GB used</p>
            </div>

            <div class="p-4 border-t" :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'">
                <div class="flex items-center gap-3">
                    <?php
                    $currUser = \App\Services\AuthService::user();
                    if ($currUser['avatar']): ?>
                        <img src="<?= $currUser['avatar'] ?>" class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-700">
                    <?php else: ?>
                        <div class="w-8 h-8 bg-slate-700 rounded-full flex items-center justify-center text-xs font-bold">
                            <?= strtoupper(substr($currUser['username'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate"><?= $_SESSION['username'] ?? 'User' ?></p>
                        <p class="text-xs text-slate-500 truncate"><?= ucfirst($_SESSION['role'] ?? 'user') ?></p>
                    </div>
                    <a href="<?= \App\Core\App::url('/logout') ?>" class="text-slate-500 hover:text-white transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0" :class="theme === 'light' ? 'bg-white' : 'bg-slate-950'">
            <!-- Header/Breadcrumbs -->
            <header class="h-16 border-b flex items-center justify-between px-6 shrink-0"
                    :class="theme === 'light' ? 'border-slate-200 bg-white' : 'border-slate-800 bg-slate-950'">
                <div class="flex items-center gap-4">
                    <div class="flex items-center rounded-lg px-2 py-1 mr-2"
                         :class="theme === 'light' ? 'bg-slate-100 border border-slate-200' : 'bg-slate-900 border border-slate-800'"
                         x-show="context !== 'private'">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-500 px-1" x-text="context"></span>
                    </div>
                    <nav class="flex items-center text-sm font-medium">
                        <a href="#" @click.prevent="goToPath('')" class="text-slate-400 hover:text-white" x-text="context === 'private' ? 'Files' : (context === 'public' ? 'Public' : 'Root')"></a>
                        <template x-for="(part, index) in breadcrumbs" :key="index">
                            <div class="flex items-center">
                                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-600"></i>
                                <a href="#" @click.prevent="goToPath(part.path)" class="text-slate-400 hover:text-white" x-text="part.name"></a>
                            </div>
                        </template>
                    </nav>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" placeholder="Search..."
                               :class="theme === 'light' ? 'bg-slate-50 border-slate-200 text-slate-900' : 'bg-slate-900 border-slate-800 text-white'"
                               class="border rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 w-64">
                    </div>
                    <template x-if="context !== 'public' || isAdmin">
                        <div class="flex items-center gap-3">
                            <button @click="showUploadModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Upload
                            </button>
                            <button @click="createFolder()" class="p-2 text-slate-400 hover:text-white transition-colors">
                                <i data-lucide="folder-plus" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </header>

            <!-- Action Bar -->
            <div class="h-12 border-b flex items-center px-6 gap-4 shrink-0"
                 :class="theme === 'light' ? 'border-slate-200 bg-slate-50/50' : 'border-slate-800 bg-slate-900/50'">
                <div class="flex items-center gap-1 border-r pr-4" :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'">
                    <button class="p-1.5 text-slate-400 hover:text-blue-500 hover:bg-slate-200/50 rounded transition-colors" :class="viewMode === 'list' && 'text-blue-500'" @click="viewMode = 'list'">
                        <i data-lucide="list" class="w-4 h-4"></i>
                    </button>
                    <button class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded transition-colors" :class="viewMode === 'grid' && 'text-blue-400'" @click="viewMode = 'grid'">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="flex-1">
                    <span class="text-xs text-slate-500" x-text="items.length + ' items'"></span>
                </div>
                <div class="flex items-center gap-2" x-show="selected.length > 0" x-cloak>
                    <span class="text-xs text-blue-400 font-medium" x-text="selected.length + ' selected'"></span>

                    <template x-if="context === 'public'">
                        <button class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded transition-colors" @click="copyToMySpaceSelected()" title="Copy to My Space">
                            <i data-lucide="copy-plus" class="w-4 h-4 text-green-400"></i>
                        </button>
                    </template>

                    <template x-if="context !== 'public' || isAdmin">
                        <div class="flex items-center gap-2">
                            <button class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded transition-colors" @click="zipSelected()" title="Create ZIP">
                                <i data-lucide="archive" class="w-4 h-4"></i>
                            </button>
                            <button class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded transition-colors" @click="deleteSelected()" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Grid View -->
                <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-6" x-cloak>
                    <template x-for="item in items" :key="item.path">
                        <div class="group relative flex flex-col items-center p-4 rounded-xl border border-transparent transition-all cursor-pointer"
                             :class="[
                                isSelected(item) ? 'bg-blue-600/10 border-blue-500/50' : '',
                                theme === 'light' ? 'hover:border-slate-200 hover:bg-slate-50' : 'hover:border-slate-800 hover:bg-slate-900/50'
                             ]"
                             @click="toggleSelect(item, $event)"
                             @dblclick="openItem(item)">
                            <div class="w-16 h-16 flex items-center justify-center mb-3">
                                <template x-if="item.type === 'dir'">
                                    <i data-lucide="folder" class="w-12 h-12 text-blue-500 fill-blue-500/20"></i>
                                </template>
                                <template x-if="item.type === 'file'">
                                    <i data-lucide="file" class="w-12 h-12 text-slate-400"></i>
                                </template>
                            </div>
                            <span class="text-sm font-medium text-center truncate w-full" x-text="item.name"></span>
                            <span class="text-[10px] text-slate-500 mt-1" x-text="item.type === 'file' ? formatSize(item.size) : ''"></span>
                        </div>
                    </template>
                </div>

                <!-- List View -->
                <div x-show="viewMode === 'list'" class="w-full" x-cloak>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-500 border-b uppercase tracking-wider"
                                :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'">
                                <th class="px-4 py-3 w-10">
                                    <input type="checkbox" @change="toggleAll()" :checked="selected.length === items.length && items.length > 0" class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3 w-32">Size</th>
                                <th class="px-4 py-3 w-48">Modified</th>
                                <th class="px-4 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in items" :key="item.path">
                                <tr class="group border-b transition-colors cursor-pointer"
                                    :class="[
                                        isSelected(item) ? 'bg-blue-600/5' : '',
                                        theme === 'light' ? 'hover:bg-slate-50 border-slate-100' : 'hover:bg-slate-900/50 border-slate-900/50'
                                    ]"
                                    @click="toggleSelect(item, $event)"
                                    @dblclick="openItem(item)">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" :checked="isSelected(item)" @click.stop="toggleSelect(item, $event)" class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <template x-if="item.type === 'dir'">
                                                <i data-lucide="folder" class="w-5 h-5 text-blue-500 fill-blue-500/20"></i>
                                            </template>
                                            <template x-if="item.type === 'file'">
                                                <i data-lucide="file" class="w-5 h-5 text-slate-400"></i>
                                            </template>
                                            <span class="text-sm font-medium" x-text="item.name"></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-500" x-text="item.type === 'file' ? formatSize(item.size) : '--'"></td>
                                    <td class="px-4 py-3 text-sm text-slate-500" x-text="formatDate(item.last_modified)"></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                            <template x-if="context !== 'public' || isAdmin">
                                                <div class="flex items-center gap-1">
                                                    <button @click.stop="openShareModal(item)" class="p-1 text-slate-600 hover:text-blue-400" title="Share">
                                                        <i data-lucide="share-2" class="w-4 h-4"></i>
                                                    </button>
                                                    <button @click.stop="renameItem(item)" class="p-1 text-slate-600 hover:text-white" title="Rename">
                                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="context === 'public'">
                                                <button @click.stop="copyToMySpace(item)" class="p-1 text-slate-600 hover:text-green-400" title="Copy to My Space">
                                                    <i data-lucide="copy-plus" class="w-4 h-4"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <div x-show="showShareModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-bold">Share Link</h3>
                <button @click="showShareModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div x-show="!shareLink">
                    <p class="text-sm text-slate-400 mb-4">Create a public link to share "<span x-text="itemToShare?.name"></span>"</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Password (optional)</label>
                            <input type="password" x-model="shareOptions.password" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Expiration (hours)</label>
                            <input type="number" x-model="shareOptions.expires" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                    <button @click="generateShare()" class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                        Generate Link
                    </button>
                </div>
                <div x-show="shareLink" class="space-y-4">
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-3 break-all text-sm font-mono text-blue-400" x-text="shareLink"></div>
                    <button @click="copyToClipboard(shareLink)" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                        Copy to Clipboard
                    </button>
                    <div class="flex justify-center py-4 bg-white rounded-lg">
                        <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(shareLink)" alt="QR Code">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showEditorModal" class="fixed inset-0 z-50 flex flex-col bg-slate-950" x-cloak>
        <div class="h-14 border-b border-slate-800 flex items-center justify-between px-6 shrink-0 bg-slate-900">
            <div class="flex items-center gap-4">
                <i data-lucide="file-text" class="w-5 h-5 text-blue-400"></i>
                <span class="text-sm font-medium text-white" x-text="editingItem?.name"></span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="saveFile()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    Save
                </button>
                <button @click="closeEditor()" class="p-2 text-slate-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
        <div id="monaco-editor" class="flex-1"></div>
    </div>

    <div x-show="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-bold">Upload Files</h3>
                <button @click="showUploadModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-8">
                <div @click="$refs.fileInput.click()"
                     @dragover.prevent="dragOver = true"
                     @dragleave.prevent="dragOver = false"
                     @drop.prevent="handleDrop($event)"
                     :class="dragOver ? 'border-blue-500 bg-blue-500/10' : 'border-slate-700 bg-slate-950/50'"
                     class="border-2 border-dashed rounded-xl p-12 flex flex-col items-center justify-center hover:bg-slate-800/50 hover:border-blue-500/50 transition-all cursor-pointer">
                    <input type="file" x-ref="fileInput" class="hidden" multiple @change="handleFiles($event.target.files)">
                    <i data-lucide="upload-cloud" class="w-12 h-12 text-slate-500 mb-4"></i>
                    <p class="text-sm font-medium mb-1">Drag & drop files here</p>
                    <p class="text-xs text-slate-500">or click to browse your computer</p>
                </div>

                <div x-show="uploads.length > 0" class="mt-6 space-y-3">
                    <template x-for="upload in uploads" :key="upload.id">
                        <div class="bg-slate-800/50 rounded-lg p-3 border border-slate-700">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-medium truncate" x-text="upload.name"></span>
                                <span class="text-[10px] text-slate-500" x-text="upload.progress + '%'"></span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-1">
                                <div class="bg-blue-500 h-1 rounded-full transition-all duration-300" :style="'width: ' + upload.progress + '%'"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function app() {
            return {
                viewMode: 'grid',
                currentPath: '',
                basePath: '<?= \App\Core\App::get('base_path') ?>',
                context: 'private',
                theme: '<?= \App\Core\Settings::get('theme', 'dark') ?>',
                isAdmin: <?= \App\Services\AuthService::isAdmin() ? 'true' : 'false' ?>,
                items: [],
                selected: [],
                breadcrumbs: [],
                showUploadModal: false,
                dragOver: false,
                uploads: [],
                showShareModal: false,
                itemToShare: null,
                shareLink: '',
                shareOptions: {
                    password: '',
                    expires: ''
                },
                showEditorModal: false,
                editingItem: null,
                editor: null,

                init() {
                    this.fetchFiles();
                    this.$nextTick(() => lucide.createIcons());
                },

                async fetchFiles() {
                    const url = `${this.basePath}/api/files?path=${encodeURIComponent(this.currentPath)}&context=${this.context}`;
                    const response = await fetch(url);
                    const result = await response.json();
                    if (result.success) {
                        this.items = result.data;
                        this.updateBreadcrumbs();
                        this.$nextTick(() => lucide.createIcons());
                    }
                },

                updateBreadcrumbs() {
                    if (!this.currentPath) {
                        this.breadcrumbs = [];
                        return;
                    }
                    const parts = this.currentPath.split('/');
                    let path = '';
                    this.breadcrumbs = parts.map(part => {
                        path = path ? path + '/' + part : part;
                        return { name: part, path: path };
                    });
                },

                goToPath(path) {
                    this.currentPath = path;
                    this.selected = [];
                    this.fetchFiles();
                },

                setContext(ctx) {
                    this.context = ctx;
                    this.currentPath = '';
                    this.selected = [];
                    this.fetchFiles();
                },

                openItem(item) {
                    if (item.type === 'dir') {
                        this.goToPath(item.path);
                    } else {
                        const editableExts = ['txt', 'php', 'js', 'css', 'html', 'md', 'json', 'sql'];
                        const ext = item.name.split('.').pop().toLowerCase();
                        if (editableExts.includes(ext)) {
                            this.openEditor(item);
                        } else {
                            // Direct download or other preview
                            window.open(`${this.basePath}/api/files/download-direct?path=${encodeURIComponent(item.path)}&context=${this.context}`);
                        }
                    }
                },

                async copyToMySpace(item) {
                    const response = await fetch(`${this.basePath}/api/files/copy-to-space?context=public`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ path: item.path })
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert('Copied to My Space!');
                    }
                },

                async copyToMySpaceSelected() {
                    for (const item of this.selected) {
                        await fetch(`${this.basePath}/api/files/copy-to-space?context=public`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ path: item.path })
                        });
                    }
                    this.selected = [];
                    alert('Items copied to My Space!');
                },

                async openEditor(item) {
                    this.editingItem = item;
                    const response = await fetch(`${this.basePath}/api/files/content?path=${encodeURIComponent(item.path)}&context=${this.context}`);
                    const result = await response.json();
                    if (result.success) {
                        this.showEditorModal = true;
                        this.$nextTick(() => {
                            if (!this.editor) {
                                require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
                                require(['vs/editor/editor.main'], () => {
                                    this.editor = monaco.editor.create(document.getElementById('monaco-editor'), {
                                        value: result.content,
                                        language: this.getLanguageFromExt(item.name),
                                        theme: 'vs-dark',
                                        automaticLayout: true
                                    });
                                });
                            } else {
                                this.editor.setValue(result.content);
                                monaco.editor.setModelLanguage(this.editor.getModel(), this.getLanguageFromExt(item.name));
                            }
                        });
                    }
                },

                getLanguageFromExt(filename) {
                    const ext = filename.split('.').pop().toLowerCase();
                    const map = {
                        'js': 'javascript',
                        'ts': 'typescript',
                        'php': 'php',
                        'html': 'html',
                        'css': 'css',
                        'md': 'markdown',
                        'json': 'json',
                        'sql': 'sql',
                        'py': 'python'
                    };
                    return map[ext] || 'plaintext';
                },

                async saveFile() {
                    const content = this.editor.getValue();
                    const response = await fetch(`${this.basePath}/api/files/save?context=${this.context}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            path: this.editingItem.path,
                            content: content
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert('File saved successfully!');
                    }
                },

                closeEditor() {
                    this.showEditorModal = false;
                    this.editingItem = null;
                },

                isSelected(item) {
                    return this.selected.some(s => s.path === item.path);
                },

                toggleSelect(item, event) {
                    if (event.ctrlKey || event.metaKey) {
                        if (this.isSelected(item)) {
                            this.selected = this.selected.filter(s => s.path !== item.path);
                        } else {
                            this.selected.push(item);
                        }
                    } else {
                        this.selected = [item];
                    }
                },

                toggleAll() {
                    if (this.selected.length === this.items.length) {
                        this.selected = [];
                    } else {
                        this.selected = [...this.items];
                    }
                },

                async createFolder() {
                    const name = prompt('Enter folder name:');
                    if (!name) return;

                    const response = await fetch(`${this.basePath}/api/files/create-folder?context=${this.context}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ path: this.currentPath, name: name })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.fetchFiles();
                    }
                },

                async deleteSelected() {
                    if (!confirm('Are you sure you want to delete ' + this.selected.length + ' item(s)?')) return;

                    for (const item of this.selected) {
                        await fetch(`${this.basePath}/api/files/delete?context=${this.context}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ path: item.path })
                        });
                    }
                    this.selected = [];
                    this.fetchFiles();
                },

                async zipSelected() {
                    const name = prompt('Enter ZIP name:', 'archive.zip');
                    if (!name) return;

                    const response = await fetch(`${this.basePath}/api/files/zip?context=${this.context}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            paths: this.selected.map(i => i.path),
                            name: name,
                            current_path: this.currentPath
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.selected = [];
                        this.fetchFiles();
                    } else {
                        alert(result.message);
                    }
                },

                handleDrop(e) {
                    this.dragOver = false;
                    this.handleFiles(e.dataTransfer.files);
                },

                handleFiles(files) {
                    Array.from(files).forEach(file => {
                        this.uploadFile(file);
                    });
                },

                uploadFile(file) {
                    const uploadId = Date.now() + Math.random();
                    const uploadObj = {
                        id: uploadId,
                        name: file.name,
                        progress: 0
                    };
                    this.uploads.unshift(uploadObj);

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('path', this.currentPath);

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', `${this.basePath}/api/files/upload?context=${this.context}`, true);

                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = Math.round((e.loaded / e.total) * 100);
                            uploadObj.progress = percentComplete;
                        }
                    };

                    xhr.onload = () => {
                        if (xhr.status === 200) {
                            setTimeout(() => {
                                this.uploads = this.uploads.filter(u => u.id !== uploadId);
                                if (this.uploads.length === 0) {
                                    this.showUploadModal = false;
                                    this.fetchFiles();
                                }
                            }, 1000);
                        }
                    };

                    xhr.send(formData);
                },

                async renameItem(item) {
                    const newName = prompt('Rename to:', item.name);
                    if (!newName || newName === item.name) return;

                    const response = await fetch(`${this.basePath}/api/files/rename?context=${this.context}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ old_path: item.path, new_name: newName })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.fetchFiles();
                    }
                },

                openShareModal(item) {
                    this.itemToShare = item;
                    this.shareLink = '';
                    this.shareOptions = { password: '', expires: '' };
                    this.showShareModal = true;
                    this.$nextTick(() => lucide.createIcons());
                },

                async generateShare() {
                    const response = await fetch(`${this.basePath}/api/share/create?context=${this.context}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            path: this.itemToShare.path,
                            password: this.shareOptions.password,
                            expires: this.shareOptions.expires
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.shareLink = result.link;
                        this.$nextTick(() => lucide.createIcons());
                    }
                },

                copyToClipboard(text) {
                    navigator.clipboard.writeText(text);
                    alert('Link copied to clipboard!');
                },

                formatSize(bytes) {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                },

                formatDate(timestamp) {
                    if (!timestamp) return '--';
                    return new Date(timestamp * 1000).toLocaleDateString([], {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }
    </script>
</body>
</html>
