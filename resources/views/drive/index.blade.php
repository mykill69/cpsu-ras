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
                            <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex-shrink-0 shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h1 class="text-2xl sm:text-3xl font-bold text-primary-900 truncate" x-text="currentFolder ? currentFolder.name : 'My Drive'">My Drive</h1>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="inline-flex items-center text-xs font-medium text-primary-600 bg-primary-50 rounded-full px-3 py-1">
                                        <span x-text="folders.length"></span> folders
                                    </span>
                                    <span class="text-primary-300">·</span>
                                    <span class="inline-flex items-center text-xs font-medium text-primary-600 bg-primary-50 rounded-full px-3 py-1">
                                        <span x-text="files.length"></span> files
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button @click="showCreateFolder = true"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-primary-200 rounded-xl text-sm font-semibold text-primary-700 hover:bg-primary-50 hover:border-primary-300 hover:shadow-md transition-all duration-200 whitespace-nowrap">
                                <svg class="w-4 h-4 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Folder
                            </button>
                            <button @click="$refs.fileInput.click()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700 rounded-xl text-sm font-semibold text-primary-900 shadow-md hover:shadow-lg transition-all duration-200 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Upload Files
                            </button>
                            <button @click="$refs.folderInput.click()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 rounded-xl text-sm font-semibold text-white shadow-md hover:shadow-lg transition-all duration-200 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Upload Folder
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden File Inputs -->
        <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" class="hidden" multiple accept="*/*">
        <input type="file" x-ref="folderInput" @change="handleFolderSelect($event)" class="hidden" webkitdirectory multiple>

        <!-- Empty State -->
        <div x-show="folders.length === 0 && files.length === 0 && !isDragging" x-cloak style="display: none;">
            <div class="text-center py-20">
                <div class="relative inline-flex items-center justify-center mb-8">
                    <div class="w-32 h-32 bg-gradient-to-br from-primary-100 to-gold-100 rounded-3xl flex items-center justify-center shadow-lg">
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
                    <button @click="$refs.fileInput.click()" class="inline-flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700 rounded-xl text-sm font-semibold text-primary-900 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                        Choose Files
                    </button>
                    <button @click="$refs.folderInput.click()" class="inline-flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 rounded-xl text-sm font-semibold text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
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
                        <div class="h-1" :style="'background-color: ' + (folder.color || '#1a5632')"></div>
                        <div class="p-4">
                            <svg class="w-10 h-10 mb-3" :style="'color: ' + (folder.color || '#1a5632')" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z" />
                            </svg>
                            <h3 class="text-sm font-medium text-primary-900 truncate mb-1" x-text="folder.name"></h3>
                            <p class="text-xs text-primary-400" x-text="new Date(folder.created_at).toLocaleDateString()"></p>
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
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" />
                                    </svg>
                                    Synced
                                </span>
                            </template>
                            <template x-if="file.status !== 'synced'">
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
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

                        <!-- File Preview -->
                        <div class="aspect-w-16 aspect-h-12 bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center">
                            <template x-if="file.mime_type && file.mime_type.startsWith('image/') && file.status !== 'synced'">
                                <img :src="baseUrl + '/thumbnail/' + file.uuid" :alt="file.original_name" class="object-cover w-full h-full">
                            </template>
                            <template x-if="!file.mime_type || !file.mime_type.startsWith('image/') || file.status === 'synced'">
                                <div class="text-center p-4">
                                    <div class="w-12 h-12 mx-auto mb-2 rounded-xl bg-white shadow-sm flex items-center justify-center">
                                        <template x-if="file.status === 'synced'">
                                            <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </template>
                                        <template x-if="file.status !== 'synced' && file.mime_type === 'application/pdf'">
                                            <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </template>
                                        <template x-if="file.status !== 'synced' && (!file.mime_type || (file.mime_type !== 'application/pdf' && !file.mime_type.startsWith('image/')))">
                                            <svg class="w-6 h-6 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <p class="text-xs text-primary-500 uppercase font-medium" x-text="file.extension"></p>
                                </div>
                            </template>
                        </div>

                        <div class="p-3">
                            <h3 class="text-sm font-medium text-primary-900 truncate mb-1" x-text="file.original_name"></h3>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-primary-400" x-text="file.formatted_size"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Create Folder Modal -->
        @include('drive.partials.create-folder-modal')

        <!-- Upload Progress -->
        <div x-show="uploading" x-cloak
            class="fixed bottom-6 right-6 z-50 bg-white rounded-2xl shadow-2xl p-5 w-80 border border-primary-100"
            x-transition>
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-primary-900">
                        Uploading <span x-text="uploadStats.current + '/' + uploadStats.total"></span> files
                    </h3>
                    <p class="text-xs text-primary-500 mt-0.5" x-text="uploadStats.currentFile"></p>
                </div>
                <button @click="cancelUpload" class="text-primary-400 hover:text-primary-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="w-full bg-primary-100 rounded-full h-2.5 mb-2 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-600 to-gold-500 h-2.5 rounded-full transition-all duration-500 relative"
                    :style="'width: ' + uploadProgress + '%'">
                    <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                </div>
            </div>
            <p class="text-xs text-primary-500" x-text="uploadProgress + '% complete'"></p>
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
                    uploadProgress: 0,
                    uploadStats: {
                        current: 0,
                        total: 0,
                        currentFile: ''
                    },
                    currentFolder: @json($currentFolder ?? null),
                    folders: @json($folders->items()),
                    files: @json($files->items()),
                    breadcrumbs: @json($breadcrumbs ?? []),

                    // Base path generated by Laravel so it works locally AND when hosted
                    // in a subdirectory (e.g. https://host/cpsu-ras/public/drive).
                    // Path only (no scheme/host) to avoid mixed-content issues behind proxies.
                    baseUrl: @json(parse_url(url('/drive'), PHP_URL_PATH)),

                    init() {
                        console.log('Drive ready');
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

                    handleDrop(e) {
                        this.dragCounter = 0;
                        this.isDragging = false;
                        const files = e.dataTransfer.files;
                        if (!files || files.length === 0) return;
                        const dt = new DataTransfer();
                        for (let f of files) dt.items.add(f);
                        if (files[0].webkitRelativePath) {
                            this.$refs.folderInput.files = dt.files;
                            this.handleFolderSelect({
                                target: this.$refs.folderInput
                            });
                        } else {
                            this.$refs.fileInput.files = dt.files;
                            this.handleFileSelect({
                                target: this.$refs.fileInput
                            });
                        }
                    },

                    handleFileSelect(e) {
                        const files = e.target.files;
                        if (!files || files.length === 0) return;
                        this.doUpload(this.baseUrl + '/upload', files, null);
                        e.target.value = '';
                    },
                    handleFolderSelect(e) {
                        const files = e.target.files;
                        if (!files || files.length === 0) return;
                        const folderName = files[0].webkitRelativePath.split('/')[0];
                        this.doUpload(this.baseUrl + '/upload-folder', files, folderName);
                        e.target.value = '';
                    },

                    doUpload(url, files, folderName) {
                        Swal.fire({
                            title: 'Uploading...',
                            text: `Uploading ${files.length} file(s)...`,
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                        const formData = new FormData();
                        for (let f of files) formData.append('files[]', f);
                        if (folderName) {
                            formData.append('folder_name', folderName);
                            for (let f of files) {
                                if (f.webkitRelativePath) formData.append('paths[]', f.webkitRelativePath);
                            }
                        }
                        if (this.currentFolder) formData.append('folder_id', this.currentFolder.id);
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', url, true);
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content || '');
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.onload = () => {
                            Swal.close();
                            let result = null;
                            try {
                                result = JSON.parse(xhr.responseText);
                            } catch (e) {
                                // Server returned HTML (404/419/413/500) instead of JSON
                                let message = 'Upload failed (HTTP ' + xhr.status + ').';
                                if (xhr.status === 419) {
                                    message = 'Your session expired. Please refresh the page and log in again.';
                                } else if (xhr.status === 413 || xhr.status === 0) {
                                    message = 'The file(s) are too large for the server. Please upload smaller files.';
                                } else if (xhr.status === 404) {
                                    message = 'Upload endpoint not found. Please contact the administrator.';
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Upload Failed',
                                    text: message,
                                    confirmButtonColor: '#1a5632'
                                });
                                return;
                            }
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Done!',
                                    text: result.message,
                                    confirmButtonColor: '#1a5632',
                                    timer: 2000
                                }).then(() => {
                                    if (result.folder) window.location.href = this.baseUrl + '/folder/' + result.folder.id;
                                    else window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: result.message || 'Upload failed.',
                                    confirmButtonColor: '#1a5632'
                                });
                            }
                        };
                        xhr.onerror = () => {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Upload failed. Please check your connection or contact the administrator.',
                                confirmButtonColor: '#1a5632'
                            });
                        };
                        xhr.send(formData);
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
                        Swal.close();
                    }
                }
            }
        </script>
    @endpush
@endsection