@extends('layouts.app')

@section('title', 'Drive')

@section('content')
    <div x-data="driveManager()" x-init="init()" @dragenter.prevent="handleDragEnter" @dragover.prevent="handleDragOver"
        @dragleave.prevent="handleDragLeave" @drop.prevent="handleDrop" class="animate-fade-in relative min-h-[70vh]">

        <!-- Upload Overlay -->
        <div x-show="isDragging" x-cloak style="display: none;" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 bg-primary-900/95 backdrop-blur-sm flex items-center justify-center">
            <div class="text-center max-w-lg mx-auto px-4">
                <div class="relative inline-flex items-center justify-center mb-8">
                    <div
                        class="w-32 h-32 bg-gold-500/20 rounded-full flex items-center justify-center border-4 border-dashed border-gold-500/50 animate-bounce">
                        <svg class="w-16 h-16 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                    </div>
                    <div class="absolute inset-0 w-32 h-32 rounded-full border-4 border-gold-500/30 animate-ping"></div>
                </div>
                <h2 class="text-3xl font-bold text-white mb-3">Drop files or folders here</h2>
                <p class="text-lg text-primary-200 mb-8">
                    Release to upload to
                    <span class="text-gold-400 font-semibold" x-text="currentFolder ? currentFolder.name : 'My Drive'">My
                        Drive</span>
                </p>
            </div>
        </div>

        <!-- Page Header -->
        <div class="mb-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-x-1.5 text-sm">
                    <li class="flex items-center">
                        <a href="{{ route('drive.index') }}"
                            class="flex items-center px-2.5 py-1.5 rounded-lg text-primary-500 hover:text-gold-500 hover:bg-primary-50 transition-all duration-200 font-medium">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                            My Drive
                        </a>
                    </li>
                    <template x-for="(folder, index) in breadcrumbs" :key="folder.id">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-primary-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <a :href="baseUrl + '/folder/' + folder.id"
                                class="px-2.5 py-1.5 rounded-lg text-primary-500 hover:text-gold-500 hover:bg-primary-50 transition-all duration-200 font-medium truncate max-w-[150px]"
                                x-text="folder.name"></a>
                        </li>
                    </template>
                </ol>
            </nav>

            <!-- Title and Actions Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="hidden sm:flex items-center justify-center w-12 h-12 rounded-xl bg-primary-50 text-primary-700 ring-1 ring-primary-100 flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h1 class="text-2xl sm:text-3xl font-bold text-primary-900 truncate" x-text="currentFolder ? currentFolder.name : 'My Drive'">My Drive</h1>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-0.5 tabular">
                                        <span x-text="baseFolders.length"></span> <span x-text="baseFolders.length === 1 ? 'folder' : 'folders'"></span>
                                    </span>
                                    <span class="text-primary-300">·</span>
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-0.5 tabular">
                                        <span x-text="baseFiles.length"></span> <span x-text="baseFiles.length === 1 ? 'file' : 'files'"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button @click="showCreateFolder = true"
                                class="btn-secondary whitespace-nowrap">
                                <svg class="w-4 h-4 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Folder
                            </button>
                            <button @click="$refs.fileInput.click()"
                                class="btn-primary whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Upload Files
                            </button>
                            <button @click="$refs.folderInput.click()"
                                class="btn-secondary whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Upload Folder
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Search -->
                <div class="border-t border-gray-100 bg-gray-50/60 px-5 py-3 sm:px-6">
                    <form role="search" @submit.prevent="runSearch(true)" class="relative">
                        <label for="drive-search" class="sr-only">Search</label>
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input id="drive-search" x-ref="searchInput" type="search" autocomplete="off" spellcheck="false"
                            x-model="searchQuery" @input.debounce.300ms="runSearch()" @keydown.escape="clearSearch()"
                            :placeholder="currentFolder ? 'Search files and folders in ' + currentFolder.name : 'Search folders and files in My Drive'"
                            class="block w-full rounded-lg border-gray-300 bg-white py-2 pl-9 pr-24 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-600 focus:ring-primary-600 [&::-webkit-search-cancel-button]:hidden">
                        <div class="absolute inset-y-0 right-2 flex items-center gap-1.5">
                            <svg x-show="searchLoading" x-cloak class="h-4 w-4 animate-spin text-gray-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3" />
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            </svg>
                            <button type="button" x-show="searchQuery" x-cloak @click="clearSearch()"
                                class="rounded px-1.5 py-0.5 text-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900">Clear</button>
                            <kbd x-show="!searchQuery" class="hidden rounded border border-gray-200 bg-gray-50 px-1.5 font-sans text-[11px] text-gray-400 sm:inline">/</kbd>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Search status -->
        <div x-show="searchActive" x-cloak class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
            <p class="text-sm text-gray-600" aria-live="polite">
                <template x-if="!searchLoading">
                    <span>
                        <span class="font-medium text-gray-900 tabular" x-text="folders.length + files.length"></span>
                        <span x-text="(folders.length + files.length) === 1 ? 'result' : 'results'"></span>
                        for <span class="font-medium text-gray-900" x-text="'“' + searchedFor + '”'"></span>
                        in <span x-text="currentFolder ? currentFolder.name + ' and its subfolders' : 'all of My Drive'"></span>
                        <span x-show="searchTruncated" class="text-gray-500">(showing the first matches; refine your search to narrow it down)</span>
                    </span>
                </template>
                <template x-if="searchLoading"><span>Searching…</span></template>
            </p>
            <button type="button" @click="clearSearch()" class="text-sm font-medium text-primary-700 hover:underline">Back to folder</button>
        </div>

        <!-- No results -->
        <div x-show="searchActive && !searchLoading && folders.length === 0 && files.length === 0" x-cloak
            class="panel px-6 py-14 text-center">
            <p class="text-sm font-medium text-gray-900">No folders or files match “<span x-text="searchedFor"></span>”</p>
            <p class="mt-1 text-sm text-gray-500">Check the spelling, or try part of the name, e.g. a year or a document number.</p>
        </div>

        <!-- Hidden File Inputs -->
        <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" class="hidden" multiple accept="*/*">
        <input type="file" x-ref="folderInput" @change="handleFolderSelect($event)" class="hidden" webkitdirectory multiple>

        <!-- Empty State -->
        <div x-show="folders.length === 0 && files.length === 0 && !isDragging && !searchActive" x-cloak style="display: none;">
            <div class="text-center py-20">
                <div class="relative inline-flex items-center justify-center mb-8">
                    <div class="w-32 h-32 bg-primary-50 rounded-3xl flex items-center justify-center shadow-lg">
                        <svg class="w-16 h-16 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-primary-900 mb-3">Drag & drop files or folders here</h3>
                <p class="text-primary-500 mb-8 max-w-md mx-auto text-base leading-relaxed">
                    You can drag and drop individual files or entire folders directly into this window.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <button @click="$refs.fileInput.click()" class="inline-flex items-center gap-2 px-6 py-3.5 bg-primary-700 hover:bg-primary-800 rounded-lg text-sm font-medium text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                        Choose Files
                    </button>
                    <button @click="$refs.folderInput.click()" class="inline-flex items-center gap-2 px-6 py-3.5 bg-primary-600 hover:bg-primary-700 rounded-xl text-sm font-semibold text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                        Choose Folder
                    </button>
                </div>
            </div>
        </div>

        <!-- Folders Section -->
        <div x-show="folders.length > 0" class="mb-8">
            <h2 class="text-lg font-semibold text-primary-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gold-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                </svg>
                Folders
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <template x-for="folder in folders" :key="folder.id">
                    <div class="card-hover group relative bg-white rounded-xl shadow-sm border border-primary-100 cursor-pointer overflow-hidden"
                        @click="navigateToFolder(folder.id)" @contextmenu.prevent="showContextMenu($event, folder)">
                        <div x-data="{ menuOpen: false }" style="position: absolute; top: 8px; right: 8px; z-index: 20;">
                            <button @click.stop="menuOpen = !menuOpen"
                                style="display: none; padding: 6px; border-radius: 8px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #dcfce7;"
                                :style="menuOpen ? 'display:flex' : ''" class="group-hover:flex">
                                <svg style="width: 16px; height: 16px; color: #1a5632;" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>
                            <div x-show="menuOpen" @click.stop.outside="menuOpen = false" x-cloak
                                style="position: absolute; right: 0; top: 100%; margin-top: 4px; width: 140px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #dcfce7; padding: 4px 0;">
                                <button @click.stop="editFolder(folder); menuOpen = false"
                                    style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 14px; color: #1a5632; background: transparent; border: none; cursor: pointer;"
                                    onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='transparent'">
                                    Rename
                                </button>
                                <hr style="margin: 4px 0; border-color: #dcfce7;">
                                <button @click.stop="deleteFolder(folder); menuOpen = false"
                                    style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 14px; color: #dc2626; background: transparent; border: none; cursor: pointer;"
                                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                    Delete
                                </button>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="relative mb-3 w-fit">
                                <svg class="w-10 h-10" :style="'color: ' + (folder.color || '#2F5C42')" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z" />
                                </svg>
                                <span x-show="itemCount(folder) > 0"
                                    class="absolute -right-2 -top-1.5 min-w-[20px] rounded-full bg-white px-1.5 text-center text-[11px] font-semibold leading-5 text-gray-800 shadow-sm ring-1 ring-gray-200 tabular"
                                    :title="itemCount(folder) + ' item(s) inside'"
                                    x-text="itemCount(folder) > 999 ? '999+' : itemCount(folder)"></span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 truncate" :title="folder.name" x-text="folder.name"></h3>
                            <p class="mt-1 truncate text-xs text-gray-500 tabular" x-text="folderSummary(folder)"></p>
                            <p x-show="searchActive" class="mt-1 truncate text-[11px] text-gray-400"
                                :title="'In ' + locationLabel(folder)" x-text="'In ' + locationLabel(folder)"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Files Section -->
        <div x-show="files.length > 0">
            <h2 class="text-lg font-semibold text-primary-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Files
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <template x-for="file in files" :key="file.id">
                    <div class="card-hover group relative bg-white rounded-xl shadow-sm border border-primary-100 overflow-hidden cursor-pointer"
                        @click="previewFile(file)" @contextmenu.prevent="showFileContextMenu($event, file)">
                        
                        <!-- Sync Status Badge -->
                        <div style="position: absolute; top: 8px; left: 8px; z-index: 20;">
                            <template x-if="file.status === 'synced'">
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/95 px-2 py-0.5 text-[11px] font-medium text-primary-700 shadow-sm ring-1 ring-gray-200">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" />
                                    </svg>
                                    Synced
                                </span>
                            </template>
                            <template x-if="file.status !== 'synced'">
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/95 px-2 py-0.5 text-[11px] font-medium text-gray-600 shadow-sm ring-1 ring-gray-200">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm11 1H6v8h8V6z" clip-rule="evenodd" />
                                    </svg>
                                    Local
                                </span>
                            </template>
                        </div>

                        <!-- 3-Dot Menu Button -->
                        <div x-data="{ menuOpen: false }" style="position: absolute; top: 8px; right: 8px; z-index: 20;">
                            <button @click.stop="menuOpen = !menuOpen"
                                style="display: none; padding: 6px; border-radius: 8px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #dcfce7;"
                                :style="menuOpen ? 'display:flex' : ''" class="group-hover:flex">
                                <svg style="width: 16px; height: 16px; color: #1a5632;" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>
                            <div x-show="menuOpen" @click.stop.outside="menuOpen = false" x-cloak
                                style="position: absolute; right: 0; top: 100%; margin-top: 4px; width: 160px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #dcfce7; padding: 4px 0;">
                                <template x-if="file.status === 'synced'">
                                    <a :href="file.metadata?.google_drive_link || 'https://drive.google.com/file/d/' + file.metadata?.google_drive_id + '/view'" 
                                       target="_blank" 
                                       @click.stop="menuOpen = false"
                                       style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 14px; color: #1a5632; text-decoration: none;"
                                       onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='transparent'">
                                        <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        View in Drive
                                    </a>
                                </template>
                                <template x-if="file.status !== 'synced'">
                                    <a :href="baseUrl + '/download/' + file.id" @click.stop="menuOpen = false"
                                        style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 14px; color: #1a5632; text-decoration: none;"
                                        onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='transparent'">
                                        <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Download
                                    </a>
                                </template>
                                <button @click.stop="editFile(file); menuOpen = false"
                                    style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 14px; color: #1a5632; background: transparent; border: none; cursor: pointer;"
                                    onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='transparent'">
                                    Rename
                                </button>
                                <hr style="margin: 4px 0; border-color: #dcfce7;">
                                <button @click.stop="deleteFile(file); menuOpen = false"
                                    style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 14px; color: #dc2626; background: transparent; border: none; cursor: pointer;"
                                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- File Preview: type tile underneath, real thumbnail layered on top when available -->
                        <div class="relative aspect-[4/3] overflow-hidden border-b border-gray-100 bg-gray-50">
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 pt-6">
                                <template x-if="file.status === 'synced'">
                                    <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z" />
                                    </svg>
                                </template>
                                <span class="rounded px-2 py-1 font-mono text-xs font-medium uppercase"
                                    :class="typeClass(file)" x-text="(file.extension || 'file').slice(0, 5)"></span>
                                <span x-show="file.status === 'synced'" class="text-[11px] text-gray-500">On Google Drive</span>
                            </div>

                            <template x-if="isImage(file) && file.status !== 'synced'">
                                <img :src="baseUrl + '/thumbnail/' + file.uuid" alt="" loading="lazy" decoding="async"
                                    class="absolute inset-0 h-full w-full bg-gray-50 object-cover"
                                    x-on:error="$el.remove()">
                            </template>
                            <template x-if="isPdf(file) && file.status !== 'synced'">
                                <canvas x-pdf-thumb="baseUrl + '/stream/' + file.uuid"
                                    class="absolute inset-0 h-full w-full bg-white object-cover object-top opacity-0 transition-opacity duration-300"></canvas>
                            </template>
                        </div>

                        <div class="p-3">
                            <h3 class="text-sm font-medium text-primary-900 truncate mb-1" x-text="file.original_name"></h3>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-primary-400" x-text="file.formatted_size"></p>
                            </div>
                            <p x-show="searchActive" class="mt-1 truncate text-[11px] text-gray-400"
                                :title="'In ' + locationLabel(file)" x-text="'In ' + locationLabel(file)"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Create Folder Modal -->
        @include('drive.partials.create-folder-modal')

        <!-- Upload Progress -->
        <div x-show="uploading" x-cloak x-transition.opacity role="status" aria-live="polite"
            class="fixed bottom-4 right-4 z-50 w-[min(22rem,calc(100vw-2rem))] rounded-xl border border-gray-200 bg-white p-4 shadow-lg">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900">
                        Uploading <span class="tabular" x-text="uploadStats.current + ' of ' + uploadStats.total"></span> files
                    </p>
                    <p class="mt-0.5 truncate text-xs text-gray-500" x-text="uploadStats.currentFile"></p>
                </div>
                <button type="button" @click="cancelUpload" class="btn-ghost -mr-1.5 -mt-1 px-2 py-1 text-xs">Cancel</button>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-primary-600 transition-[width] duration-300" :style="'width: ' + uploadProgress + '%'"></div>
            </div>
            <p class="mt-1.5 text-xs text-gray-500 tabular" x-text="uploadProgress + '%'"></p>
        </div>
    </div>

    @push('scripts')
        <script>
            function driveManager() {
                return {
                    isDragging: false,
                    dragCounter: 0,
                    showCreateFolder: false,
                    newFolderName: '',
                    newFolderColor: '#1a5632',
                    uploading: false,
                    cancelled: false,
                    currentXhr: null,
                    uploadProgress: 0,
                    uploadStats: {
                        current: 0,
                        total: 0,
                        currentFile: ''
                    },
                    currentFolder: @json($currentFolder ?? null),
                    folders: @json($folders->items()),
                    files: @json($files->items()),
                    // The folder's own contents; folders/files are swapped for results while searching
                    baseFolders: [],
                    baseFiles: [],
                    searchQuery: new URLSearchParams(window.location.search).get('q') || '',
                    searchedFor: '',
                    searchActive: false,
                    searchLoading: false,
                    searchTruncated: false,
                    searchController: null,
                    breadcrumbs: @json($breadcrumbs ?? []),

                    // Base path generated by Laravel so it works locally AND when hosted
                    // in a subdirectory (e.g. https://host/cpsu-ras/public/drive).
                    // Path only (no scheme/host) to avoid mixed-content issues behind proxies.
                    baseUrl: @json(parse_url(url('/drive'), PHP_URL_PATH)),

                    init() {
                        this.baseFolders = this.folders;
                        this.baseFiles = this.files;

                        // "/" focuses the search box (unless already typing somewhere)
                        window.addEventListener('keydown', (e) => {
                            const tag = (e.target.tagName || '').toLowerCase();
                            if (e.key === '/' && !['input', 'textarea', 'select'].includes(tag) && !e.target.isContentEditable) {
                                e.preventDefault();
                                this.$refs.searchInput.focus();
                            }
                        });

                        // Restore a search from the URL (?q=...) after refresh / back
                        if (this.searchQuery.trim()) this.runSearch(true);
                    },

                    async runSearch(immediate = false) {
                        const q = this.searchQuery.trim();
                        if (!q) {
                            this.clearSearch(false);
                            return;
                        }
                        if (q === this.searchedFor && this.searchActive && !immediate) return;

                        this.searchController?.abort();
                        this.searchController = new AbortController();
                        this.searchLoading = true;
                        this.searchActive = true;

                        const params = new URLSearchParams({ q });
                        if (this.currentFolder) params.set('folder_id', this.currentFolder.id);

                        try {
                            const response = await fetch(this.baseUrl + '/search?' + params, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                signal: this.searchController.signal,
                            });
                            const data = await this.readJson(response);
                            if (!response.ok) throw new Error(data.message || 'Search failed.');

                            this.folders = data.folders;
                            this.files = data.files;
                            this.searchedFor = q;
                            this.searchTruncated = !!data.truncated;
                            this.syncUrl(q);
                        } catch (err) {
                            if (err.name === 'AbortError') return;
                            this.showError(err);
                        } finally {
                            this.searchLoading = false;
                        }
                    },

                    clearSearch(focus = true) {
                        this.searchController?.abort();
                        this.searchQuery = '';
                        this.searchedFor = '';
                        this.searchActive = false;
                        this.searchLoading = false;
                        this.searchTruncated = false;
                        this.folders = this.baseFolders;
                        this.files = this.baseFiles;
                        this.syncUrl('');
                        if (focus) this.$refs.searchInput?.focus();
                    },

                    syncUrl(q) {
                        const url = new URL(window.location.href);
                        if (q) url.searchParams.set('q', q);
                        else url.searchParams.delete('q');
                        history.replaceState(null, '', url);
                    },

                    locationLabel(item) {
                        const root = this.currentFolder ? this.currentFolder.name : 'My Drive';
                        return item.location ? root + ' / ' + item.location : root;
                    },

                    // Safely read a JSON response; produces a readable error when the
                    // server returns HTML (404/419/500) instead of JSON.
                    readJson(response) {
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                throw new Error('Server returned HTTP ' + response.status +
                                    '. Please refresh the page and try again.');
                            }
                        });
                    },

                    showError(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: (error && error.message) ? error.message : 'Something went wrong.',
                            confirmButtonColor: '#1a5632'
                        });
                    },

                    handleDragEnter(e) {
                        this.dragCounter++;
                        this.isDragging = true;
                    },
                    handleDragOver(e) {
                        this.isDragging = true;
                    },
                    handleDragLeave(e) {
                        this.dragCounter--;
                        if (this.dragCounter <= 0) {
                            this.dragCounter = 0;
                            this.isDragging = false;
                        }
                    },

                    async handleDrop(e) {
                        this.dragCounter = 0;
                        this.isDragging = false;

                        // DataTransfer is emptied once this handler yields, so grab the
                        // entries synchronously before awaiting anything.
                        const entries = [];
                        const items = e.dataTransfer?.items;
                        if (items && items.length && typeof items[0].webkitGetAsEntry === 'function') {
                            for (const item of items) {
                                if (item.kind !== 'file') continue;
                                const entry = item.webkitGetAsEntry();
                                if (entry) entries.push(entry);
                            }
                        }

                        if (!entries.length) {
                            // Very old browsers: no directory support, plain files only
                            const files = Array.from(e.dataTransfer?.files || []);
                            if (files.length) this.startUpload(files.map(f => ({ file: f, path: null })), []);
                            return;
                        }

                        try {
                            const looseFiles = [];
                            const folders = [];
                            for (const entry of entries) {
                                if (entry.isFile) {
                                    looseFiles.push({ file: await this.entryToFile(entry), path: null });
                                } else if (entry.isDirectory) {
                                    folders.push({ name: entry.name, items: await this.readDirectory(entry, entry.name) });
                                }
                            }
                            this.startUpload(looseFiles, folders);
                        } catch (err) {
                            this.showError({ message: 'Could not read the dropped folder: ' + (err?.message || err) });
                        }
                    },

                    entryToFile(entry) {
                        return new Promise((resolve, reject) => entry.file(resolve, reject));
                    },

                    // Recursively collect every file below a dropped directory.
                    async readDirectory(dirEntry, prefix) {
                        const reader = dirEntry.createReader();
                        const children = [];
                        // readEntries returns results in chunks (~100 in Chrome); call until empty.
                        while (true) {
                            const batch = await new Promise((resolve, reject) => reader.readEntries(resolve, reject));
                            if (!batch.length) break;
                            children.push(...batch);
                        }

                        const results = [];
                        for (const child of children) {
                            const childPath = prefix + '/' + child.name;
                            if (child.isFile) {
                                if (this.isJunkFile(child.name)) continue;
                                results.push({ file: await this.entryToFile(child), path: childPath });
                            } else if (child.isDirectory) {
                                results.push(...await this.readDirectory(child, childPath));
                            }
                        }
                        return results;
                    },

                    isJunkFile(name) {
                        return ['.DS_Store', 'Thumbs.db', 'desktop.ini'].includes(name) || name.startsWith('~$');
                    },

                    handleFileSelect(e) {
                        const files = Array.from(e.target.files || []);
                        e.target.value = '';
                        if (!files.length) return;
                        this.startUpload(files.map(f => ({ file: f, path: null })), []);
                    },

                    handleFolderSelect(e) {
                        const files = Array.from(e.target.files || []);
                        e.target.value = '';
                        if (!files.length) return;
                        // Group by top-level folder name (a picker returns one, but be safe)
                        const groups = {};
                        for (const f of files) {
                            if (this.isJunkFile(f.name)) continue;
                            const path = f.webkitRelativePath || f.name;
                            const root = path.split('/')[0];
                            (groups[root] ||= []).push({ file: f, path });
                        }
                        this.startUpload([], Object.entries(groups).map(([name, items]) => ({ name, items })));
                    },

                    // Split a list into request-sized batches (PHP caps files per request).
                    makeBatches(items) {
                        const MAX_FILES = 20;
                        const MAX_BYTES = 64 * 1024 * 1024;
                        const batches = [];
                        let current = [];
                        let bytes = 0;
                        for (const item of items) {
                            if (current.length && (current.length >= MAX_FILES || bytes + item.file.size > MAX_BYTES)) {
                                batches.push(current);
                                current = [];
                                bytes = 0;
                            }
                            current.push(item);
                            bytes += item.file.size;
                        }
                        if (current.length) batches.push(current);
                        return batches;
                    },

                    async startUpload(looseFiles, folders) {
                        if (this.uploading) return;

                        const allItems = [...looseFiles, ...folders.flatMap(f => f.items)];
                        const totalBytes = allItems.reduce((sum, i) => sum + i.file.size, 0) || 1;
                        const hasEmptyFolder = folders.some(f => !f.items.length);
                        if (!allItems.length && !hasEmptyFolder) {
                            this.showError({ message: 'Nothing to upload. The folder is empty.' });
                            return;
                        }

                        this.uploading = true;
                        this.cancelled = false;
                        this.uploadProgress = 0;
                        this.uploadStats = { current: 0, total: allItems.length, currentFile: '' };
                        let sentBytes = 0;
                        let error = null;

                        const send = async (url, batch, extra) => {
                            const formData = new FormData();
                            batch.forEach(item => {
                                formData.append('files[]', item.file, item.file.name);
                                if (item.path) formData.append('paths[]', item.path);
                            });
                            for (const [k, v] of Object.entries(extra)) {
                                if (v !== null && v !== undefined) formData.append(k, v);
                            }
                            if (this.currentFolder) formData.append('folder_id', this.currentFolder.id);

                            this.uploadStats.currentFile = batch.length === 1
                                ? batch[0].file.name
                                : batch[0].file.name + ' and ' + (batch.length - 1) + ' more';

                            const batchBytes = batch.reduce((s, i) => s + i.file.size, 0);
                            const result = await this.postForm(url, formData, loaded => {
                                this.uploadProgress = Math.min(99, Math.round((sentBytes + Math.min(loaded, batchBytes)) / totalBytes * 100));
                            });
                            sentBytes += batchBytes;
                            this.uploadStats.current += batch.length;
                            this.uploadProgress = Math.min(99, Math.round(sentBytes / totalBytes * 100));
                            return result;
                        };

                        try {
                            for (const batch of this.makeBatches(looseFiles)) {
                                if (this.cancelled) break;
                                await send(this.baseUrl + '/upload', batch, {});
                            }

                            for (const folder of folders) {
                                if (this.cancelled) break;
                                if (!folder.items.length) {
                                    await this.createEmptyFolder(folder.name);
                                    continue;
                                }
                                let rootId = null;
                                for (const batch of this.makeBatches(folder.items)) {
                                    if (this.cancelled) break;
                                    const result = await send(this.baseUrl + '/upload-folder', batch, {
                                        folder_name: folder.name,
                                        root_folder_id: rootId,
                                    });
                                    rootId = result.folder?.id ?? rootId;
                                }
                            }
                        } catch (err) {
                            error = err?.message || String(err);
                        }

                        this.uploadProgress = 100;
                        this.uploading = false;
                        const done = this.uploadStats.current;

                        if (error && !this.cancelled) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload stopped',
                                text: error + (done ? ` ${done} of ${allItems.length} file(s) were uploaded before the error.` : ''),
                                confirmButtonColor: '#234A34'
                            }).then(() => { if (done) window.location.reload(); });
                            return;
                        }

                        Swal.fire({
                            icon: this.cancelled ? 'info' : 'success',
                            title: this.cancelled ? 'Upload cancelled' : 'Upload complete',
                            text: `${done} of ${allItems.length} file(s) uploaded` + (folders.length ? ` into ${folders.length} folder(s).` : '.'),
                            confirmButtonColor: '#234A34',
                            timer: this.cancelled ? undefined : 1800
                        }).then(() => window.location.reload());
                    },

                    postForm(url, formData, onProgress) {
                        return new Promise((resolve, reject) => {
                            const xhr = new XMLHttpRequest();
                            this.currentXhr = xhr;
                            xhr.open('POST', url, true);
                            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content || '');
                            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                            xhr.setRequestHeader('Accept', 'application/json');
                            xhr.upload.onprogress = (ev) => { if (ev.lengthComputable) onProgress(ev.loaded); };
                            xhr.onload = () => {
                                let result = null;
                                try { result = JSON.parse(xhr.responseText); } catch (e) {}

                                if (xhr.status >= 200 && xhr.status < 300 && result?.success) return resolve(result);

                                if (xhr.status === 419) return reject(new Error('Your session expired. Refresh the page and sign in again.'));
                                if (xhr.status === 413) return reject(new Error('These files are larger than the server allows.'));
                                if (xhr.status === 422 && result?.errors) {
                                    return reject(new Error(Object.values(result.errors).flat()[0] || 'Some files were rejected.'));
                                }
                                reject(new Error(result?.message || ('The server responded with HTTP ' + xhr.status + '.')));
                            };
                            xhr.onerror = () => reject(new Error('Could not reach the server. Check your connection and try again.'));
                            xhr.onabort = () => reject(new Error('Upload cancelled.'));
                            xhr.send(formData);
                        });
                    },

                    createEmptyFolder(name) {
                        return fetch(this.baseUrl + '/folder/create', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ name, parent_id: this.currentFolder?.id })
                        }).then(r => this.readJson(r)).then(result => {
                            if (!result.success) throw new Error(result.message || 'Could not create folder "' + name + '".');
                        });
                    },

                    // ---- Card helpers ----
                    itemCount(folder) {
                        return (folder.files_count || 0) + (folder.subfolders_count || 0);
                    },

                    folderSummary(folder) {
                        const files = folder.files_count || 0;
                        const subs = folder.subfolders_count || 0;
                        if (!files && !subs) return 'Empty';
                        const parts = [];
                        if (files) parts.push(files.toLocaleString() + (files === 1 ? ' file' : ' files'));
                        if (subs) parts.push(subs.toLocaleString() + (subs === 1 ? ' folder' : ' folders'));
                        return parts.join(' · ');
                    },

                    isImage(file) {
                        return !!file.mime_type && file.mime_type.startsWith('image/') && file.mime_type !== 'image/svg+xml';
                    },

                    isPdf(file) {
                        return file.mime_type === 'application/pdf' || (file.extension || '').toLowerCase() === 'pdf';
                    },

                    typeClass(file) {
                        const ext = (file.extension || '').toLowerCase();
                        const groups = {
                            'bg-red-50 text-red-700': ['pdf'],
                            'bg-sky-50 text-sky-700': ['doc', 'docx', 'odt', 'rtf', 'txt'],
                            'bg-emerald-50 text-emerald-700': ['xls', 'xlsx', 'csv', 'ods'],
                            'bg-orange-50 text-orange-700': ['ppt', 'pptx', 'odp'],
                            'bg-violet-50 text-violet-700': ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tif', 'tiff'],
                        };
                        for (const [cls, list] of Object.entries(groups)) {
                            if (list.includes(ext)) return cls;
                        }
                        return 'bg-gray-100 text-gray-600';
                    },

                    previewFile(file) {
                        // Check if file is synced to Google Drive
                        if (file.status === 'synced' && file.metadata?.google_drive_id) {
                            // Open Google Drive link in new tab
                            const driveLink = file.metadata.google_drive_link || 
                                `https://drive.google.com/file/d/${file.metadata.google_drive_id}/view`;
                            window.open(driveLink, '_blank');
                        } else {
                            // For local files
                            if (file.mime_type?.startsWith('image/') || file.mime_type === 'application/pdf') {
                                window.open(this.baseUrl + '/preview/' + file.id, '_blank');
                            } else {
                                window.location.href = this.baseUrl + '/download/' + file.id;
                            }
                        }
                    },

                    showFileContextMenu(event, file) {
                        event.preventDefault();
                        const isSynced = file.status === 'synced';
                        const driveLink = file.metadata?.google_drive_link || 
                            (file.metadata?.google_drive_id ? `https://drive.google.com/file/d/${file.metadata.google_drive_id}/view` : '');
                        
                        Swal.fire({
                            title: file.original_name,
                            html: `
                                <div class="text-left space-y-2">
                                    <p class="text-sm"><strong>Size:</strong> ${file.formatted_size}</p>
                                    <p class="text-sm"><strong>Type:</strong> ${file.mime_type || 'Unknown'}</p>
                                    <p class="text-sm"><strong>Status:</strong> ${isSynced ? '🟢 Synced to Drive' : '🔵 Local Storage'}</p>
                                    <p class="text-sm"><strong>Uploaded:</strong> ${new Date(file.created_at).toLocaleString()}</p>
                                    ${isSynced ? `<a href="${driveLink}" target="_blank" class="text-blue-500 hover:text-blue-700">Open in Google Drive →</a>` : ''}
                                </div>
                            `,
                            showDenyButton: !isSynced,
                            showCancelButton: true,
                            confirmButtonText: isSynced ? 'Open in Drive' : 'Download',
                            denyButtonText: 'Delete',
                            confirmButtonColor: '#1a5632',
                            denyButtonColor: '#d33',
                            cancelButtonColor: '#6b7280',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                if (isSynced) {
                                    window.open(driveLink, '_blank');
                                } else {
                                    window.location.href = this.baseUrl + '/download/' + file.id;
                                }
                            } else if (result.isDenied) {
                                this.deleteFile(file);
                            }
                        });
                    },

                    createFolder() {
                        if (!this.newFolderName.trim()) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Enter folder name',
                                confirmButtonColor: '#1a5632'
                            });
                            return;
                        }
                        fetch(this.baseUrl + '/folder/create', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.newFolderName,
                                parent_id: this.currentFolder?.id,
                                color: this.newFolderColor
                            })
                        })
                        .then(r => this.readJson(r))
                        .then(result => {
                            if (result.success) {
                                this.showCreateFolder = false;
                                this.newFolderName = '';
                                window.location.reload();
                            } else {
                                this.showError({ message: result.message || 'Failed to create folder' });
                            }
                        })
                        .catch(err => this.showError(err));
                    },

                    navigateToFolder(id) {
                        window.location.href = this.baseUrl + '/folder/' + id;
                    },

                    deleteFile(file) {
                        Swal.fire({
                            title: 'Delete File?',
                            text: `Delete "${file.original_name}"?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#1a5632',
                            confirmButtonText: 'Yes, delete!'
                        })
                        .then(r => {
                            if (r.isConfirmed) {
                                fetch(this.baseUrl + '/file/' + file.id, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(r => this.readJson(r))
                                .then(d => {
                                    if (d.success) {
                                        this.files = this.files.filter(f => f.id !== file.id);
                                        this.baseFiles = this.baseFiles.filter(f => f.id !== file.id);
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Deleted!',
                                            confirmButtonColor: '#1a5632',
                                            timer: 1500
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: d.message || 'Failed',
                                            confirmButtonColor: '#1a5632'
                                        });
                                    }
                                })
                                .catch(err => this.showError(err));
                            }
                        });
                    },

                    editFile(file) {
                        Swal.fire({
                            title: 'Rename File',
                            input: 'text',
                            inputValue: file.original_name,
                            showCancelButton: true,
                            confirmButtonColor: '#1a5632',
                            inputValidator: v => v ? null : 'Enter name'
                        })
                        .then(r => {
                            if (r.isConfirmed && r.value !== file.original_name) {
                                fetch(this.baseUrl + '/file/' + file.id, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        name: r.value
                                    })
                                })
                                .then(r => this.readJson(r))
                                .then(d => {
                                    if (d.success) window.location.reload();
                                    else this.showError({ message: d.message || 'Failed to rename file' });
                                })
                                .catch(err => this.showError(err));
                            }
                        });
                    },

                    deleteFolder(folder) {
                        Swal.fire({
                            title: 'Delete Folder?',
                            text: `Delete "${folder.name}" and its contents?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#1a5632',
                            confirmButtonText: 'Yes, delete!'
                        })
                        .then(r => {
                            if (r.isConfirmed) {
                                fetch(this.baseUrl + '/folder/' + folder.id, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(r => this.readJson(r))
                                .then(d => {
                                    if (d.success) {
                                        this.folders = this.folders.filter(f => f.id !== folder.id);
                                        this.baseFolders = this.baseFolders.filter(f => f.id !== folder.id);
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Deleted!',
                                            confirmButtonColor: '#1a5632',
                                            timer: 1500
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Cannot Delete',
                                            text: d.message || 'Folder not empty',
                                            confirmButtonColor: '#1a5632'
                                        });
                                    }
                                })
                                .catch(err => this.showError(err));
                            }
                        });
                    },

                    editFolder(folder) {
                        Swal.fire({
                            title: 'Rename Folder',
                            input: 'text',
                            inputValue: folder.name,
                            showCancelButton: true,
                            confirmButtonColor: '#1a5632',
                            inputValidator: v => v ? null : 'Enter name'
                        })
                        .then(r => {
                            if (r.isConfirmed && r.value !== folder.name) {
                                fetch(this.baseUrl + '/folder/' + folder.id, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        name: r.value
                                    })
                                })
                                .then(r => this.readJson(r))
                                .then(d => {
                                    if (d.success) window.location.reload();
                                    else this.showError({ message: d.message || 'Failed to rename folder' });
                                })
                                .catch(err => this.showError(err));
                            }
                        });
                    },

                    showContextMenu(e) {
                        e.preventDefault();
                    },
                    cancelUpload() {
                        this.cancelled = true;
                        this.currentXhr?.abort();
                    }
                }
            }
        </script>
    @endpush
@endsection