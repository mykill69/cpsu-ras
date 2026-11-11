@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <div x-data="userManagement()" id="user-management-app">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-5 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-primary-900">User Management</h1>
                    <p class="text-sm text-primary-500 mt-1">Manage system users and their roles</p>
                </div>
                <button type="button" @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gold-500 hover:bg-gold-600 rounded-xl text-sm font-semibold text-primary-900 shadow-md transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add User
                </button>
            </div>

            <!-- Search & Filters -->
            <div class="mt-4 flex flex-wrap gap-3">
                <input type="text" x-model="search" @input.debounce.500="filterUsers" placeholder="Search users..."
                    class="flex-1 min-w-[200px] rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                <select x-model="roleFilter" @change="filterUsers"
                    class="rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                    @endforeach
                </select>
                <select x-model="statusFilter" @change="filterUsers"
                    class="rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full w-full divide-y divide-primary-100">
                    <thead class="bg-primary-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-primary-600 uppercase">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-primary-600 uppercase">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-primary-600 uppercase">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-primary-600 uppercase">Department</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-primary-600 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-primary-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-primary-50" id="users-table-body">
                        @include('users.partials.table')
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-primary-100">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Create/Edit User Modal -->
        <div x-show="showCreateModal || showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity style="display: none;">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-primary-900/50 backdrop-blur-sm" @click="closeModals()"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-bold text-primary-900 mb-4" x-text="showEditModal ? 'Edit User' : 'Create User'"></h3>

                    <form @submit.prevent="showEditModal ? updateUser() : createUser()" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-primary-700 mb-1">First Name</label>
                                <input type="text" x-model="form.first_name" required
                                    class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-primary-700 mb-1">Last Name</label>
                                <input type="text" x-model="form.last_name" required
                                    class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-700 mb-1">Email</label>
                            <input type="email" x-model="form.email" required
                                class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                        </div>
                        <div x-show="!showEditModal">
                            <label class="block text-sm font-medium text-primary-700 mb-1">Password</label>
                            <input type="password" x-model="form.password" :required="!showEditModal"
                                class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                        </div>
                        <div x-show="showEditModal">
                            <label class="block text-sm font-medium text-primary-700 mb-1">New Password (optional)</label>
                            <input type="password" x-model="form.password"
                                class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-700 mb-1">Role</label>
                            <select x-model="form.role" required
                                class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    @if ($role->name !== 'administrator' || auth()->user()->hasRole('administrator'))
                                        <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-700 mb-1">Department</label>
                            <select x-model="form.department_id" required
                                class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                                <option value="">Select Department</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-700 mb-1">Position</label>
                            <input type="text" x-model="form.position" required
                                class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="submit"
                                class="flex-1 py-2.5 bg-gold-500 hover:bg-gold-600 rounded-xl text-sm font-semibold text-primary-900 transition-all duration-200"
                                x-text="showEditModal ? 'Update User' : 'Create User'"></button>
                            <button type="button" @click="closeModals()"
                                class="flex-1 py-2.5 bg-white border-2 border-primary-200 hover:bg-primary-50 rounded-xl text-sm font-semibold text-primary-700 transition-all duration-200">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function userManagement() {
        return {
            showCreateModal: false,
            showEditModal: false,
            editId: null,
            search: '',
            roleFilter: '',
            statusFilter: '',
            form: {
                first_name: '',
                last_name: '',
                email: '',
                password: '',
                role: '',
                department_id: '',
                position: '',
            },

            filterUsers() {
                this.fetchUsers();
            },

            async fetchUsers() {
                const params = new URLSearchParams();
                if (this.search) params.set('search', this.search);
                if (this.roleFilter) params.set('role', this.roleFilter);
                if (this.statusFilter) params.set('status', this.statusFilter);

                try {
                    const response = await fetch('{{ route('users.index') }}?' + params.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        }
                    });

                    if (response.ok) {
                        const html = await response.text();
                        document.querySelector('#users-table-body').innerHTML = html;
                    }
                } catch (e) {
                    console.error('Search failed:', e);
                }
            },

            async createUser() {
                await this.submitForm('{{ route('users.store') }}', 'POST');
            },

            async updateUser() {
                await this.submitForm('{{ route('users.update', '') }}/' + this.editId, 'PUT');
            },

            async submitForm(url, method) {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(this.form),
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: result.message || 'Saved successfully',
                            confirmButtonColor: '#1a5632',
                            timer: 1500
                        });
                        this.closeModals();
                        setTimeout(function() { window.location.reload(); }, 1000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Operation failed',
                            confirmButtonColor: '#1a5632'
                        });
                    }
                } catch (e) {
                    console.error('Submit error:', e);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Network error occurred',
                        confirmButtonColor: '#1a5632'
                    });
                }
            },

            closeModals() {
                this.showCreateModal = false;
                this.showEditModal = false;
                this.editId = null;
                this.form = {
                    first_name: '',
                    last_name: '',
                    email: '',
                    password: '',
                    role: '',
                    department_id: '',
                    position: ''
                };
            }
        };
    }

    // Global functions for table actions
    window.editUserFromTable = function(user) {
        console.log('Edit user clicked:', user);
        const alpineElement = document.getElementById('user-management-app');
        if (alpineElement && alpineElement.__x) {
            const data = alpineElement.__x.$data;
            data.editId = user.id;
            data.form = {
                first_name: user.first_name || '',
                last_name: user.last_name || '',
                email: user.email || '',
                password: '',
                role: user.roles && user.roles[0] ? user.roles[0].name : '',
                department_id: user.department_id || '',
                position: user.position || '',
            };
            data.showEditModal = true;
        } else {
            console.error('Alpine component not found');
        }
    };

    window.deleteUserFromTable = async function(userId, userName) {
        console.log('Delete user clicked:', userId, userName);
        const result = await Swal.fire({
            title: 'Delete User?',
            text: 'Are you sure you want to delete "' + userName + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#1a5632',
            confirmButtonText: 'Yes, delete!'
        });

        if (result.isConfirmed) {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const response = await fetch(@json(parse_url(url('/users'), PHP_URL_PATH)) + '/' + userId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'User deleted successfully',
                        confirmButtonColor: '#1a5632',
                        timer: 1500
                    });
                    setTimeout(function() { window.location.reload(); }, 1000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete user',
                        confirmButtonColor: '#1a5632'
                    });
                }
            } catch (e) {
                console.error('Delete error:', e);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Network error occurred',
                    confirmButtonColor: '#1a5632'
                });
            }
        }
    };

    window.toggleUserStatus = async function(userId) {
    console.log('Toggle status clicked:', userId);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    
    // Use the new URL pattern
    const url = '/users/user/' + userId + '/toggle-status';
    console.log('Toggle URL:', url);
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
        });
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        // Clean the response if needed
        let cleanText = text.trim();
        if (cleanText.startsWith('folder')) {
            cleanText = cleanText.substring(6);
        }
        
        let data;
        try {
            data = JSON.parse(cleanText);
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Clean text:', cleanText);
            throw new Error('Invalid response format');
        }
        
        if (response.ok && data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Status Updated',
                text: data.message,
                confirmButtonColor: '#1a5632',
                timer: 1500
            });
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to update status',
                confirmButtonColor: '#1a5632'
            });
        }
    } catch (e) {
        console.error('Toggle status error:', e);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: e.message || 'Network error occurred',
            confirmButtonColor: '#1a5632'
        });
    }
};
</script>
@endpush