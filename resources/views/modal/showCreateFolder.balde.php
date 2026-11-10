<div x-show="showCreateFolder" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="fixed inset-0 bg-primary-900/50 backdrop-blur-sm" @click="showCreateFolder = false; newFolderName = ''"></div>
        <div class="relative transform overflow-hidden rounded-2xl bg-white px-6 pb-6 pt-6 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
            <!-- Modal Icon -->
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-gold-100 to-gold-200 shadow-sm">
                <svg class="h-7 w-7 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            
            <div class="mt-4 text-center">
                <h3 class="text-xl font-bold text-primary-900">Create New Folder</h3>
                <p class="text-sm text-primary-500 mt-1">Add a new folder to organize your files</p>
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-primary-700 mb-2">Folder Name</label>
                <input type="text" 
                       x-model="newFolderName" 
                       @keyup.enter="createFolder"
                       placeholder="Enter folder name"
                       autofocus
                       class="block w-full rounded-xl border-primary-200 py-3 px-4 text-primary-900 placeholder:text-primary-400 focus:border-gold-500 focus:ring-2 focus:ring-gold-500 sm:text-sm">
            </div>
            
            <!-- Color Picker -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-primary-700 mb-2">Folder Color</label>
                <div class="flex gap-2">
                    <template x-for="color in ['#1a5632', '#c9a31e', '#0f9d58', '#4285f4', '#db4437', '#ab47bc', '#00acc1', '#ff7043']" :key="color">
                        <button @click="newFolderColor = color" 
                                :class="{'ring-2 ring-offset-2 ring-gold-500 scale-110': newFolderColor === color}"
                                class="h-8 w-8 rounded-full transition-all duration-200 hover:scale-110 shadow-sm"
                                :style="'background-color: ' + color"
                                :title="color"></button>
                    </template>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="mt-6 flex gap-3">
                <button @click="createFolder" 
                        :disabled="!newFolderName"
                        class="flex-1 py-3 bg-gold-500 hover:bg-gold-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl text-sm font-semibold text-primary-900 shadow-sm transition-all duration-200">
                    Create Folder
                </button>
                <button @click="showCreateFolder = false; newFolderName = ''"
                        class="flex-1 py-3 bg-white border-2 border-primary-200 hover:bg-primary-50 rounded-xl text-sm font-semibold text-primary-700 transition-all duration-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>