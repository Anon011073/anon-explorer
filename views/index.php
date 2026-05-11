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
        .truncate-name { max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body class="h-full overflow-hidden transition-colors duration-300"
      :class="theme === 'light' ? 'text-slate-900' : 'text-slate-200'"
      x-data="app()"
      @contextmenu.prevent="showContextMenu($event, null)">

    <div class="flex h-full">
        <!-- Sidebar -->
        <aside class="w-64 border-r flex flex-col hidden md:flex"
               :class="theme === 'light' ? 'bg-slate-50 border-slate-200' : 'bg-slate-900 border-slate-800'">
            <div class="p-4 border-b flex items-center gap-2"
                 :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-600/30">
                    <i data-lucide="zap" class="w-5 h-5 text-white"></i>
                </div>
                <span class="font-bold text-xl tracking-tight text-white"><?= \App\Core\Settings::get('app_name', 'Zipply') ?></span>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                <a href="#" @click.prevent="setContext('private')"
                   :class="context === 'private' ? 'bg-blue-600/10 text-blue-500 font-medium' : (theme === 'light' ? 'text-slate-600 hover:bg-slate-200' : 'text-slate-400 hover:bg-slate-800')"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="folder" class="w-5 h-5"></i>
                    My Space
                </a>
                <a href="#" @click.prevent="setContext('public')"
                   :class="context === 'public' ? 'bg-blue-600/10 text-blue-500 font-medium' : (theme === 'light' ? 'text-slate-600 hover:bg-slate-200' : 'text-slate-400 hover:bg-slate-800')"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="globe" class="w-5 h-5"></i>
                    Public Files
                </a>
                <?php if (\App\Services\AuthService::isAdmin()): ?>
                <a href="#" @click.prevent="setContext('root')"
                   :class="context === 'root' ? 'bg-blue-600/10 text-blue-500 font-medium' : (theme === 'light' ? 'text-slate-600 hover:bg-slate-200' : 'text-slate-400 hover:bg-slate-800')"
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
                <div class="flex justify-between text-xs text-slate-500 mb-2 font-bold uppercase tracking-tighter">
                    <span>Storage</span>
                    <span x-text="Math.round((storageUsage / storageLimit) * 100) + '%'"></span>
                </div>
                <div class="w-full rounded-full h-1.5 mb-2" :class="theme === 'light' ? 'bg-slate-200' : 'bg-slate-800'">
                    <div class="bg-blue-600 h-1.5 rounded-full shadow-[0_0_8px_rgba(37,99,235,0.4)]" :style="'width: ' + Math.min(100, (storageUsage / storageLimit) * 100) + '%'"></div>
                </div>
                <p class="text-[10px] text-slate-500 font-medium" x-text="formatSize(storageUsage) + ' of ' + formatSize(storageLimit) + ' used'"></p>
            </div>

            <div class="p-4 border-t" :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'">
                <div class="flex items-center gap-3">
                    <?php
                    $currUser = \App\Services\AuthService::user();
                    if ($currUser['avatar']): ?>
                        <img src="<?= \App\Core\App::url($currUser['avatar']) ?>" class="w-8 h-8 rounded-full object-cover ring-2 ring-blue-600/30">
                    <?php else: ?>
                        <div class="w-8 h-8 bg-blue-600/20 rounded-full flex items-center justify-center text-xs font-bold text-blue-500 ring-2 ring-blue-600/30">
                            <?= strtoupper(substr($currUser['username'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate transition-colors" :class="theme === 'light' ? 'text-slate-900' : 'text-white'"><?= $_SESSION['username'] ?? 'User' ?></p>
                        <p class="text-[10px] text-slate-500 truncate uppercase tracking-widest"><?= htmlspecialchars($_SESSION['role'] ?? 'user') ?></p>
                    </div>
                    <a href="<?= \App\Core\App::url('/logout') ?>" class="text-slate-500 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-500/10">
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
                        <a href="#" @click.prevent="goToPath('')" class="text-slate-400 hover:text-blue-500 transition-colors" x-text="context === 'private' ? 'Files' : (context === 'public' ? 'Public' : 'Root')"></a>
                        <template x-for="(part, index) in breadcrumbs" :key="index">
                            <div class="flex items-center">
                                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-600"></i>
                                <a href="#" @click.prevent="goToPath(part.path)" class="text-slate-400 hover:text-blue-500 transition-colors" x-text="part.name"></a>
                            </div>
                        </template>
                    </nav>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" placeholder="Search..." x-model="searchQuery" autocomplete="off"
                               :class="theme === 'light' ? 'bg-slate-50 border-slate-200 text-slate-900' : 'bg-slate-900 border-slate-800 text-white'"
                               class="border rounded-xl pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600/20 w-64 transition-all">
                    </div>
                    <template x-if="context !== 'public' || isAdmin">
                        <div class="flex items-center gap-3">
                            <button @click="showUploadModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition-all shadow-lg shadow-blue-600/20 active:scale-95">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Upload
                            </button>
                            <button @click="createFolder()" class="p-2 text-slate-400 hover:text-blue-500 transition-colors rounded-xl hover:bg-blue-500/10">
                                <i data-lucide="folder-plus" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </header>

            <!-- Action Bar -->
            <div class="h-12 border-b flex items-center px-6 gap-4 shrink-0 shadow-sm"
                 :class="theme === 'light' ? 'border-slate-200 bg-slate-50/50' : 'border-slate-800 bg-slate-900/50'">
                <div class="flex items-center gap-1 border-r pr-4" :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'">
                    <button class="p-1.5 rounded-lg transition-all" :class="viewMode === 'list' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-200/50'" @click="viewMode = 'list'">
                        <i data-lucide="list" class="w-4 h-4"></i>
                    </button>
                    <button class="p-1.5 rounded-lg transition-all" :class="viewMode === 'grid' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-200/50'" @click="viewMode = 'grid'">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="flex-1">
                    <span class="text-xs text-slate-500 font-bold uppercase tracking-widest" x-text="items.length + ' items'"></span>
                </div>
                <div class="flex items-center gap-2" x-show="selected.length > 0" x-cloak>
                    <span class="text-xs text-blue-500 font-bold bg-blue-600/10 px-2 py-1 rounded-lg" x-text="selected.length + ' selected'"></span>

                    <template x-if="context === 'public'">
                        <button class="p-1.5 text-green-500 hover:bg-green-500/10 rounded-lg transition-colors border border-green-500/20" @click="copyToMySpaceSelected()" title="Copy to My Space">
                            <i data-lucide="copy-plus" class="w-4 h-4"></i>
                        </button>
                    </template>

                    <template x-if="context !== 'public' || isAdmin">
                        <div class="flex items-center gap-2">
                            <button class="p-1.5 text-blue-500 hover:bg-blue-500/10 rounded-lg transition-colors border border-blue-500/20" @click="zipSelected()" title="Create ZIP">
                                <i data-lucide="archive" class="w-4 h-4"></i>
                            </button>
                            <button class="p-1.5 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors border border-red-500/20" @click="deleteSelected()" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-6" @click="selected = []">
                <!-- Grid View -->
                <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-6" x-cloak>
                    <template x-for="item in filteredItems" :key="item.path">
                        <div class="group relative flex flex-col items-center p-4 rounded-2xl border border-transparent transition-all cursor-pointer"
                             :class="[
                                isSelected(item) ? 'bg-blue-600/10 border-blue-500/30' : '',
                                theme === 'light' ? 'hover:border-slate-200 hover:bg-slate-50' : 'hover:border-slate-800 hover:bg-slate-900/50'
                             ]"
                             @click.stop="toggleSelect(item, $event)"
                             @contextmenu.prevent.stop="showContextMenu($event, item)"
                             @dblclick="openItem(item)">
                            <div class="w-24 h-24 flex items-center justify-center mb-3 overflow-hidden rounded-xl bg-slate-800/20 border border-slate-700/10 shadow-sm transition-transform group-hover:scale-105">
                                <template x-if="item.type === 'dir'">
                                    <i data-lucide="folder" class="w-16 h-16 text-blue-500 fill-blue-500/20"></i>
                                </template>
                                <template x-if="item.type === 'file'">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <template x-if="isImage(item.name)">
                                            <img :src="`<?= \App\Core\App::url('/api/files/thumbnail') ?>?path=${encodeURIComponent(item.path)}&context=${context}`"
                                                 class="w-full h-full object-cover"
                                                 loading="lazy">
                                        </template>
                                        <template x-if="!isImage(item.name)">
                                            <i data-lucide="file" class="w-12 h-12 text-slate-400"></i>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <span class="text-xs font-bold text-center break-all line-clamp-2 w-full px-1" :title="item.name" x-text="item.name"></span>
                            <span class="text-[9px] text-slate-500 mt-1 uppercase tracking-widest font-bold" x-text="item.type === 'file' ? formatSize(item.size) : ''"></span>
                        </div>
                    </template>
                </div>

                <!-- List View -->
                <div x-show="viewMode === 'list'" class="w-full" x-cloak>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-bold text-slate-500 border-b uppercase tracking-widest"
                                :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'">
                                <th class="px-4 py-3 w-10">
                                    <input type="checkbox" @change="toggleAll()" :checked="selected.length === items.length && items.length > 0" class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3 w-32">Size</th>
                                <th class="px-4 py-3 w-48 text-right pr-12">Modified</th>
                                <th class="px-4 py-3 w-10 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :class="theme === 'light' ? 'divide-slate-100' : 'divide-slate-900/50'">
                            <template x-for="item in filteredItems" :key="item.path">
                                <tr class="group transition-all cursor-pointer"
                                    :class="[
                                        isSelected(item) ? 'bg-blue-600/5' : '',
                                        theme === 'light' ? 'hover:bg-slate-50' : 'hover:bg-slate-900/50'
                                    ]"
                                    @click.stop="toggleSelect(item, $event)"
                                    @contextmenu.prevent.stop="showContextMenu($event, item)"
                                    @dblclick="openItem(item)">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" :checked="isSelected(item)" @click.stop="toggleSelect(item, $event)" class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <template x-if="item.type === 'dir'">
                                                <i data-lucide="folder" class="w-5 h-5 text-blue-500 fill-blue-500/20"></i>
                                            </template>
                                            <template x-if="item.type === 'file'">
                                                <div class="w-6 h-6 flex items-center justify-center overflow-hidden rounded bg-slate-800/20">
                                                    <template x-if="isImage(item.name)">
                                                        <img :src="`<?= \App\Core\App::url('/api/files/thumbnail') ?>?path=${encodeURIComponent(item.path)}&context=${context}`"
                                                            class="w-full h-full object-cover"
                                                            loading="lazy">
                                                    </template>
                                                    <template x-if="!isImage(item.name)">
                                                        <i data-lucide="file" class="w-4 h-4 text-slate-400"></i>
                                                    </template>
                                                </div>
                                            </template>
                                            <span class="text-sm font-bold truncate max-w-md" :title="item.name" x-text="item.name"></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-[10px] text-slate-500 font-bold tabular-nums" x-text="item.type === 'file' ? formatSize(item.size) : '--'"></td>
                                    <td class="px-4 py-4 text-[10px] text-slate-500 font-bold text-right pr-12 tabular-nums" x-text="formatDate(item.last_modified)"></td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                            <template x-if="context !== 'public' || isAdmin">
                                                <div class="flex items-center gap-1">
                                                    <button @click.stop="openShareModal(item)" class="p-1.5 text-slate-500 hover:text-blue-500 hover:bg-blue-500/10 rounded-lg transition-colors" title="Share">
                                                        <i data-lucide="share-2" class="w-4 h-4"></i>
                                                    </button>
                                                    <button @click.stop="renameItem(item)" class="p-1.5 text-slate-500 hover:text-blue-500 hover:bg-blue-500/10 rounded-lg transition-colors" title="Rename">
                                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="context === 'public'">
                                                <button @click.stop="copyToMySpace(item)" class="p-1.5 text-slate-500 hover:text-green-500 hover:bg-green-500/10 rounded-lg transition-colors" title="Copy to My Space">
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

    <!-- Context Menu -->
    <div x-show="contextMenu.show"
         @click.away="contextMenu.show = false"
         class="fixed z-[100] w-56 bg-slate-900/95 backdrop-blur-xl border border-slate-800 rounded-2xl shadow-2xl py-2 text-slate-200 ring-1 ring-white/10"
         :style="`top: ${contextMenu.y}px; left: ${contextMenu.x}px`"
         x-cloak>
        <template x-if="contextMenu.item">
            <div class="space-y-0.5 px-1">
                <button @click="openItem(contextMenu.item); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold hover:bg-blue-600 hover:text-white rounded-xl flex items-center justify-between transition-all group">
                    <span>Open</span>
                    <i data-lucide="external-link" class="w-4 h-4 opacity-50 group-hover:opacity-100"></i>
                </button>
                <template x-if="context !== 'public' || isAdmin">
                    <button @click="openShareModal(contextMenu.item); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold hover:bg-blue-600 hover:text-white rounded-xl flex items-center justify-between transition-all group">
                        <span>Share</span>
                        <i data-lucide="share-2" class="w-4 h-4 text-blue-500 group-hover:text-white"></i>
                    </button>
                </template>
                <template x-if="context === 'public'">
                    <button @click="copyToMySpace(contextMenu.item); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold hover:bg-green-600 hover:text-white rounded-xl flex items-center justify-between transition-all group">
                        <span>Copy to Space</span>
                        <i data-lucide="copy-plus" class="w-4 h-4 text-green-500 group-hover:text-white"></i>
                    </button>
                </template>
                <div class="h-px bg-slate-800 my-1 mx-2"></div>
                <template x-if="context !== 'public' || isAdmin">
                    <button @click="renameItem(contextMenu.item); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold hover:bg-blue-600 hover:text-white rounded-xl flex items-center justify-between transition-all group">
                        <span>Rename</span>
                        <i data-lucide="edit-3" class="w-4 h-4 opacity-50 group-hover:opacity-100"></i>
                    </button>
                    <button @click="deleteItem(contextMenu.item); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold hover:bg-red-600 hover:text-white rounded-xl flex items-center justify-between transition-all group">
                        <span>Delete</span>
                        <i data-lucide="trash-2" class="w-4 h-4 text-red-500 group-hover:text-white"></i>
                    </button>
                </template>
            </div>
        </template>
        <template x-if="!contextMenu.item">
            <div class="space-y-0.5 px-1">
                <button @click="createFolder(); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold hover:bg-blue-600 hover:text-white rounded-xl flex items-center justify-between transition-all group">
                    <span>New Folder</span>
                    <i data-lucide="folder-plus" class="w-4 h-4 opacity-50 group-hover:opacity-100"></i>
                </button>
                <button @click="showUploadModal = true; contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold hover:bg-blue-600 hover:text-white rounded-xl flex items-center justify-between transition-all group">
                    <span>Upload Files</span>
                    <i data-lucide="upload" class="w-4 h-4 opacity-50 group-hover:opacity-100"></i>
                </button>
                <div class="h-px bg-slate-800 my-1 mx-2"></div>
                <button @click="fetchFiles(); contextMenu.show = false" class="w-full text-left px-4 py-2 text-sm font-bold hover:bg-blue-600 hover:text-white rounded-xl flex items-center justify-between transition-all group">
                    <span>Refresh</span>
                    <i data-lucide="refresh-cw" class="w-4 h-4 opacity-50 group-hover:opacity-100"></i>
                </button>
            </div>
        </template>
    </div>

    <!-- Modals -->
    <div x-show="showShareModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md shadow-2xl ring-1 ring-white/10">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Share File</h3>
                <button @click="showShareModal = false" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="p-8 space-y-6">
                <div x-show="!shareLink">
                    <div class="flex items-center gap-4 mb-8 p-4 bg-blue-600/10 rounded-2xl border border-blue-500/20">
                        <i data-lucide="share-2" class="w-10 h-10 text-blue-500"></i>
                        <div>
                            <p class="text-sm font-bold text-white truncate max-w-[200px]" x-text="itemToShare?.name"></p>
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Public Sharing</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-2 uppercase tracking-widest">Protection Password</label>
                            <input type="password" x-model="shareOptions.password" placeholder="Optional" class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-600/30 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-2 uppercase tracking-widest">Expiry (Hours)</label>
                            <input type="number" x-model="shareOptions.expires" placeholder="Never" class="w-full bg-slate-800 border border-slate-700 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-600/30 transition-all">
                        </div>
                    </div>
                    <button @click="generateShare()" class="w-full mt-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-xl shadow-blue-600/20 active:scale-95">
                        Generate Secure Link
                    </button>
                </div>
                <div x-show="shareLink" class="space-y-6 text-center">
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 break-all text-xs font-mono text-blue-400 select-all" x-text="shareLink"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <button @click="copyToClipboard(shareLink)" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3 px-4 rounded-2xl transition-all flex items-center justify-center gap-2">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                            Copy
                        </button>
                        <button @click="shareLink = ''" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-2xl transition-all flex items-center justify-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            New
                        </button>
                    </div>
                    <div class="flex justify-center p-6 bg-white rounded-3xl shadow-inner">
                        <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=160x150&data=' + encodeURIComponent(shareLink)" alt="QR Code" class="rounded-lg">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Editor Modal -->
    <div x-show="showEditorModal" class="fixed inset-0 z-[120] flex flex-col bg-slate-950" x-cloak>
        <div class="h-14 border-b border-slate-800 flex items-center justify-between px-6 shrink-0 bg-slate-900/80 backdrop-blur-xl">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-blue-600/20 rounded-lg">
                    <i data-lucide="file-code" class="w-5 h-5 text-blue-500"></i>
                </div>
                <span class="text-sm font-bold text-white tracking-tight" x-text="editingItem?.name"></span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="saveFile()" :disabled="isSaving"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl text-xs font-bold transition-all shadow-lg shadow-blue-600/20 active:scale-95">
                    <span x-text="isSaving ? 'Saving...' : 'Save Changes'"></span>
                </button>
                <button @click="closeEditor()" class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        <div id="monaco-editor" class="flex-1"></div>
    </div>

    <!-- Upload Modal -->
    <div x-show="showUploadModal" class="fixed inset-0 z-[130] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg shadow-2xl ring-1 ring-white/10">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Upload Files</h3>
                <button @click="showUploadModal = false" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="p-8">
                <div @click="$refs.fileInput.click()"
                     @dragover.prevent="dragOver = true"
                     @dragleave.prevent="dragOver = false"
                     @drop.prevent="handleDrop($event)"
                     :class="dragOver ? 'border-blue-500 bg-blue-600/10' : 'border-slate-700 bg-slate-950/30'"
                     class="border-2 border-dashed rounded-3xl p-16 flex flex-col items-center justify-center hover:bg-slate-800/40 hover:border-blue-600/40 transition-all cursor-pointer group">
                    <input type="file" x-ref="fileInput" class="hidden" multiple @change="handleFiles($event.target.files)">
                    <i data-lucide="cloud-upload" class="w-16 h-16 text-slate-500 mb-6 group-hover:text-blue-500 transition-colors"></i>
                    <p class="text-lg font-bold mb-2 text-white">Drop your files here</p>
                    <p class="text-sm text-slate-500 font-medium">or click to browse local storage</p>
                </div>

                <div x-show="uploads.length > 0" class="mt-8 space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                    <template x-for="upload in uploads" :key="upload.id">
                        <div class="bg-slate-800/50 rounded-2xl p-4 border border-slate-700/50 shadow-sm">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-bold truncate text-white max-w-[200px]" x-text="upload.name"></span>
                                <span class="text-[10px] text-blue-500 font-black" x-text="upload.progress + '%'"></span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-blue-600 h-full transition-all duration-300 rounded-full shadow-[0_0_8px_rgba(37,99,235,0.5)]" :style="'width: ' + upload.progress + '%'"></div>
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
                basePath: '<?= \App\Core\App::url('') ?>',
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
                isSaving: false,
                searchQuery: '',
                storageUsage: <?= $storageUsage ?? 0 ?>,
                storageLimit: <?= $storageLimit ?? 0 ?>,
                contextMenu: {
                    show: false,
                    x: 0,
                    y: 0,
                    item: null
                },

                init() {
                    this.fetchFiles();
                    this.$nextTick(() => lucide.createIcons());
                },

                async fetchFiles() {
                    const url = `<?= \App\Core\App::url('/api/files') ?>?path=${encodeURIComponent(this.currentPath)}&context=${this.context}`;
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
                    this.searchQuery = '';
                    this.fetchFiles();
                },

                setContext(ctx) {
                    this.context = ctx;
                    this.currentPath = '';
                    this.selected = [];
                    this.searchQuery = '';
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
                        } else if (this.isImage(item.name)) {
                            window.open(`<?= \App\Core\App::url('/api/files/thumbnail') ?>?path=${encodeURIComponent(item.path)}&context=${this.context}`, '_blank');
                        } else {
                            window.open(`<?= \App\Core\App::url('/api/files/download-direct') ?>?path=${encodeURIComponent(item.path)}&context=${this.context}`);
                        }
                    }
                },

                async copyToMySpace(item) {
                    const response = await fetch(`<?= \App\Core\App::url('/api/files/copy-to-space') ?>?context=public`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ path: item.path })
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert('Copied to My Space!');
                    } else {
                        alert(result.message);
                    }
                },

                async copyToMySpaceSelected() {
                    for (const item of this.selected) {
                        await fetch(`<?= \App\Core\App::url('/api/files/copy-to-space') ?>?context=public`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ path: item.path })
                        });
                    }
                    this.selected = [];
                    alert('Items copied to My Space!');
                    this.fetchFiles();
                },

                async openEditor(item) {
                    this.editingItem = item;
                    const response = await fetch(`<?= \App\Core\App::url('/api/files/content') ?>?path=${encodeURIComponent(item.path)}&context=${this.context}`);
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
                    this.isSaving = true;
                    try {
                        const response = await fetch(`<?= \App\Core\App::url('/api/files/save') ?>?context=${this.context}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                path: this.editingItem.path,
                                content: content
                            })
                        });
                        const result = await response.json();
                        if (result.success) {
                            console.log('File saved');
                        } else {
                            alert('Save failed: ' + result.message);
                        }
                    } catch (e) {
                        alert('An error occurred while saving.');
                        console.error(e);
                    } finally {
                        this.isSaving = false;
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

                    const response = await fetch(`<?= \App\Core\App::url('/api/files/create-folder') ?>?context=${this.context}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ path: this.currentPath, name: name })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.fetchFiles();
                    } else {
                        alert(result.message);
                    }
                },

                async deleteItem(item) {
                    if (!confirm('Are you sure you want to delete this item?')) return;
                    const response = await fetch(`<?= \App\Core\App::url('/api/files/delete') ?>?context=${this.context}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ path: item.path })
                    });
                    if ((await response.json()).success) this.fetchFiles();
                },

                async deleteSelected() {
                    if (!confirm('Are you sure you want to delete ' + this.selected.length + ' item(s)?')) return;

                    for (const item of this.selected) {
                        await fetch(`<?= \App\Core\App::url('/api/files/delete') ?>?context=${this.context}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ path: item.path })
                        });
                    }
                    this.selected = [];
                    this.fetchFiles();
                },

                async zipSelected() {
                    let defaultName = 'archive';
                    if (this.selected.length === 1) {
                        defaultName = this.selected[0].name.split('.')[0];
                    }
                    const name = prompt('Enter ZIP name:', defaultName + '.zip');
                    if (!name) return;

                    const response = await fetch(`<?= \App\Core\App::url('/api/files/zip') ?>?context=${this.context}`, {
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
                    xhr.open('POST', `<?= \App\Core\App::url('/api/files/upload') ?>?context=${this.context}`, true);

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

                    const response = await fetch(`<?= \App\Core\App::url('/api/files/rename') ?>?context=${this.context}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ old_path: item.path, new_name: newName })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.fetchFiles();
                    } else {
                        alert(result.message);
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
                    const response = await fetch(`<?= \App\Core\App::url('/api/share/create') ?>?context=${this.context}`, {
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
                    } else {
                        alert(result.message);
                    }
                },

                copyToClipboard(text) {
                    navigator.clipboard.writeText(text);
                    alert('Link copied to clipboard!');
                },

                showContextMenu(e, item) {
                    this.contextMenu.show = true;
                    this.contextMenu.x = e.clientX;
                    this.contextMenu.y = e.clientY;
                    this.contextMenu.item = item;
                    this.$nextTick(() => lucide.createIcons());
                },

                isImage(name) {
                    const exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                    return exts.includes(name.split('.').pop().toLowerCase());
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
                },

                get filteredItems() {
                    if (!this.searchQuery) return this.items;
                    const query = this.searchQuery.toLowerCase();
                    return this.items.filter(item => item.name.toLowerCase().includes(query));
                }
            }
        }
    </script>
</body>
</html>
