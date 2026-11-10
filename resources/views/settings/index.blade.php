@extends('layouts.app')

@section('title', 'System Settings')

@push('styles')
<style>
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 28px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider {
        background-color: #dc2626;
    }
    input:checked + .toggle-slider:before {
        transform: translateX(22px);
    }
</style>
@endpush

@section('content')
    <div x-data="settingsManager()" class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-5 sm:p-6">
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex-shrink-0 shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-primary-900">System Settings</h1>
                    <p class="text-sm text-primary-500 mt-1">Manage system configuration and maintenance</p>
                </div>
            </div>
        </div>

        <!-- Maintenance Mode -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="p-6 border-b border-primary-100">
                <h2 class="text-lg font-semibold text-primary-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Maintenance Mode
                </h2>
                <p class="text-sm text-primary-500 mt-1">Toggle maintenance mode to prevent users from accessing the system</p>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-primary-900">System Maintenance</h3>
                        <p class="text-sm text-primary-500 mt-0.5" x-text="maintenanceMode ? 'System is currently under maintenance' : 'System is currently online'"></p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" x-model="maintenanceMode" @change="toggleMaintenance()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div x-show="maintenanceMode" class="mt-4" x-transition>
                    <label class="block text-sm font-medium text-primary-700 mb-1">Maintenance Message</label>
                    <input type="text" x-model="maintenanceMessage" 
                        class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500"
                        placeholder="System is under maintenance. Please check back later.">
                </div>
            </div>
        </div>

        <!-- General Settings -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="p-6 border-b border-primary-100">
                <h2 class="text-lg font-semibold text-primary-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    General Settings
                </h2>
            </div>
            <form @submit.prevent="saveSettings()" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-primary-700 mb-1">System Name</label>
                        <input type="text" x-model="form.system_name" required
                            class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary-700 mb-1">System Email</label>
                        <input type="email" x-model="form.system_email" required
                            class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary-700 mb-1">Max File Size (KB)</label>
                        <input type="number" x-model="form.max_file_size" required min="1"
                            class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-primary-700 mb-1">Session Lifetime (minutes)</label>
                        <input type="number" x-model="form.session_lifetime" required min="5" max="1440"
                            class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-primary-700 mb-1">Allowed File Types</label>
                        <input type="text" x-model="form.allowed_file_types" required
                            class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                        <p class="text-xs text-primary-400 mt-1">Comma-separated list of file extensions</p>
                    </div>
                </div>
                <div class="flex justify-end pt-4">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-gold-500 hover:bg-gold-600 rounded-xl text-sm font-semibold text-primary-900 shadow-md transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- System Actions -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="p-6 border-b border-primary-100">
                <h2 class="text-lg font-semibold text-primary-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    System Actions
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-primary-900">Clear System Cache</h3>
                        <p class="text-sm text-primary-500 mt-0.5">Clear all cached data to improve performance</p>
                    </div>
                    <button @click="clearCache()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-xl text-sm font-semibold text-primary-700 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Clear Cache
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function settingsManager() {
        return {
            maintenanceMode: @json($settings['maintenance_mode']),
            maintenanceMessage: @json($settings['maintenance_message']),
            form: {
                system_name: @json($settings['system_name']),
                system_email: @json($settings['system_email']),
                max_file_size: @json($settings['max_file_size']),
                allowed_file_types: @json($settings['allowed_file_types']),
                session_lifetime: @json($settings['session_lifetime']),
            },

            async toggleMaintenance() {
                const mode = this.maintenanceMode ? 'down' : 'up';
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                
                try {
                    const response = await fetch('/settings/toggle-maintenance', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            mode: mode,
                            message: this.maintenanceMessage
                        }),
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.maintenanceMode = data.maintenance_mode;
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            confirmButtonColor: '#1a5632',
                            timer: 1500
                        });
                    } else {
                        this.maintenanceMode = !this.maintenanceMode;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to toggle maintenance mode',
                            confirmButtonColor: '#1a5632'
                        });
                    }
                } catch (e) {
                    this.maintenanceMode = !this.maintenanceMode;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Network error occurred',
                        confirmButtonColor: '#1a5632'
                    });
                }
            },

            async saveSettings() {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                
                try {
                    const response = await fetch('/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.form),
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: data.message,
                            confirmButtonColor: '#1a5632',
                            timer: 1500
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to save settings',
                            confirmButtonColor: '#1a5632'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Network error occurred',
                        confirmButtonColor: '#1a5632'
                    });
                }
            },

            async clearCache() {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                
                try {
                    const response = await fetch('/settings/clear-cache', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cleared!',
                            text: data.message,
                            confirmButtonColor: '#1a5632',
                            timer: 1500
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to clear cache',
                        confirmButtonColor: '#1a5632'
                    });
                }
            }
        }
    }
</script>
@endpush