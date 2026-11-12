@extends('layouts.app')

@section('title', 'User Management')

@section('content')
@php
    $viewerIsAdmin = auth()->user()->hasRole('administrator');
    $tabs = ['' => 'All', 'active' => 'Active', 'inactive' => 'Inactive', 'deleted' => 'Deleted'];
@endphp

<div class="space-y-6" x-data="userManagement(@js([
    'indexUrl' => route('users.index'),
    'baseUrl' => url('users'),
    'counts' => $counts,
]))" @keydown.escape.window="closeModal()">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-1 text-3xl font-semibold text-gray-900">Users</h1>
            <p class="mt-1 text-sm text-gray-500">Who can sign in, what they can do, and which office they belong to.</p>
        </div>
        <button type="button" @click="openCreate()" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" /></svg>
            Add user
        </button>
    </div>

    <div class="panel overflow-hidden">
        {{-- Status tabs --}}
        <div class="flex gap-1 overflow-x-auto border-b border-gray-200 px-4 sm:px-6" role="tablist" aria-label="Filter by status">
            @foreach ($tabs as $value => $label)
                <button type="button" role="tab" @click="setStatus(@js($value))"
                    :aria-selected="(filters.status === @js($value)).toString()"
                    :class="filters.status === @js($value) ? 'border-primary-700 text-primary-900' : 'border-transparent text-gray-500 hover:text-gray-800'"
                    class="-mb-px flex items-center gap-2 whitespace-nowrap border-b-2 px-3 py-3 text-sm font-medium">
                    {{ $label }}
                    <span class="tabular rounded-full bg-gray-100 px-1.5 py-px text-xs text-gray-600" x-text="counts[@js($value)] ?? 0">{{ $counts[$value] ?? 0 }}</span>
                </button>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3 border-b border-gray-200 px-4 py-3 sm:px-6">
            <div class="relative w-full sm:w-72">
                <label for="user-search" class="sr-only">Search users</label>
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                <input id="user-search" type="search" x-model="filters.search" @input.debounce.300ms="reload()" @keydown.escape.stop="filters.search = ''; reload()"
                    placeholder="Name, email or position" autocomplete="off"
                    class="block w-full rounded-lg border-gray-300 bg-white py-2 pl-9 pr-9 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-600 focus:ring-primary-600">
                <svg x-show="loading" x-cloak class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-primary-700" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3" /><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
            </div>

            <label class="sr-only" for="role-filter">Role</label>
            <select id="role-filter" x-model="filters.role" @change="reload()"
                class="rounded-lg border-gray-300 py-2 pl-3 pr-9 text-sm text-gray-800 shadow-sm focus:border-primary-600 focus:ring-primary-600">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ Str::headline($role->name) }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="dept-filter">Department</label>
            <select id="dept-filter" x-model="filters.department" @change="reload()"
                class="max-w-[14rem] rounded-lg border-gray-300 py-2 pl-3 pr-9 text-sm text-gray-800 shadow-sm focus:border-primary-600 focus:ring-primary-600">
                <option value="">All departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>

            <button type="button" x-show="hasFilters()" x-cloak @click="resetFilters()" class="btn-ghost py-2 text-sm">Reset</button>
        </div>

        <div x-ref="table" @click="paginate($event)" class="transition-opacity" :class="loading && 'opacity-50'" :aria-busy="loading.toString()">
            @include('users.partials.table')
        </div>
    </div>

    {{-- Add / edit dialog --}}
    <div x-show="modal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modal" x-transition.opacity class="fixed inset-0 bg-gray-900/40" @click="closeModal()"></div>

            <div x-show="modal" x-transition class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-gray-200">
                <form @submit.prevent="save()" novalidate>
                    <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4">
                        <div>
                            <h2 id="user-modal-title" class="text-lg font-semibold text-gray-900" x-text="modal === 'edit' ? 'Edit user' : 'Add user'"></h2>
                            <p class="mt-0.5 text-sm text-gray-500" x-text="modal === 'edit' ? 'Changes apply the next time they load a page.' : 'They can sign in as soon as the account is created.'"></p>
                        </div>
                        <button type="button" @click="closeModal()" class="-mr-2 rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <div x-show="formError" x-cloak class="rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-800" role="alert" x-text="formError"></div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="f-first" class="block text-sm font-medium text-gray-800">First name</label>
                                <input id="f-first" x-ref="first" type="text" x-model="form.first_name" autocomplete="off" :class="errors.first_name && 'border-red-400'"
                                    class="mt-1.5 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">
                                <p x-show="errors.first_name" x-text="errors.first_name?.[0]" class="mt-1 text-xs text-red-700"></p>
                            </div>
                            <div>
                                <label for="f-last" class="block text-sm font-medium text-gray-800">Last name</label>
                                <input id="f-last" type="text" x-model="form.last_name" autocomplete="off" :class="errors.last_name && 'border-red-400'"
                                    class="mt-1.5 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">
                                <p x-show="errors.last_name" x-text="errors.last_name?.[0]" class="mt-1 text-xs text-red-700"></p>
                            </div>
                        </div>

                        <div>
                            <label for="f-email" class="block text-sm font-medium text-gray-800">Email</label>
                            <input id="f-email" type="email" x-model="form.email" autocomplete="off" placeholder="name@cpsu.edu.ph" :class="errors.email && 'border-red-400'"
                                class="mt-1.5 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">
                            <p x-show="errors.email" x-text="errors.email?.[0]" class="mt-1 text-xs text-red-700"></p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="f-role" class="block text-sm font-medium text-gray-800">Role</label>
                                <select id="f-role" x-model="form.role" :disabled="editingSelf" :class="errors.role && 'border-red-400'"
                                    class="mt-1.5 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600 disabled:bg-gray-50 disabled:text-gray-500">
                                    <option value="">Choose a role</option>
                                    @foreach ($roles as $role)
                                        @if ($role->name !== 'administrator' || $viewerIsAdmin)
                                            <option value="{{ $role->name }}">{{ Str::headline($role->name) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <p x-show="editingSelf" class="mt-1 text-xs text-gray-500">You can't change your own role.</p>
                                <p x-show="errors.role" x-text="errors.role?.[0]" class="mt-1 text-xs text-red-700"></p>
                            </div>
                            <div>
                                <label for="f-dept" class="block text-sm font-medium text-gray-800">Department</label>
                                <select id="f-dept" x-model="form.department_id" :class="errors.department_id && 'border-red-400'"
                                    class="mt-1.5 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">
                                    <option value="">Choose a department</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <p x-show="errors.department_id" x-text="errors.department_id?.[0]" class="mt-1 text-xs text-red-700"></p>
                            </div>
                        </div>

                        <div>
                            <label for="f-position" class="block text-sm font-medium text-gray-800">Position</label>
                            <input id="f-position" type="text" x-model="form.position" autocomplete="off" placeholder="e.g. Records Officer II" :class="errors.position && 'border-red-400'"
                                class="mt-1.5 block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:ring-primary-600">
                            <p x-show="errors.position" x-text="errors.position?.[0]" class="mt-1 text-xs text-red-700"></p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="f-password" class="block text-sm font-medium text-gray-800"
                                    x-text="modal === 'edit' ? 'New password' : 'Password'"></label>
                                <button type="button" @click="generatePassword()" class="text-xs font-medium text-primary-700 hover:underline">Generate</button>
                            </div>
                            <div class="relative mt-1.5">
                                <input id="f-password" :type="showPassword ? 'text' : 'password'" x-model="form.password" autocomplete="new-password"
                                    :placeholder="modal === 'edit' ? 'Leave blank to keep the current password' : 'At least 8 characters, letters and numbers'"
                                    :class="errors.password && 'border-red-400'"
                                    class="block w-full rounded-lg border-gray-300 py-2 pl-3 pr-16 font-mono text-sm shadow-sm placeholder:font-sans focus:border-primary-600 focus:ring-primary-600">
                                <button type="button" @click="showPassword = !showPassword" x-text="showPassword ? 'Hide' : 'Show'"
                                    class="absolute inset-y-1 right-1 rounded-md px-2.5 text-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900"></button>
                            </div>
                            <p x-show="errors.password" x-text="errors.password?.[0]" class="mt-1 text-xs text-red-700"></p>
                            <p x-show="!errors.password && modal === 'edit' && form.password" x-cloak class="mt-1 text-xs text-gray-500">Their "keep me signed in" sessions on other devices will end.</p>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50/60 px-6 py-4 sm:flex-row sm:justify-end">
                        <button type="button" @click="closeModal()" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary" :disabled="saving">
                            <svg x-show="saving" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3" /><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
                            <span x-text="saving ? 'Saving…' : (modal === 'edit' ? 'Save changes' : 'Add user')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function userManagement(config) {
        const blankForm = () => ({ first_name: '', last_name: '', email: '', password: '', role: '', department_id: '', position: '' });
        const csrf = () => document.querySelector('meta[name="csrf-token"]').content;
        const swal = (opts) => window.Swal ? Swal.fire({ confirmButtonColor: '#234A34', ...opts }) : Promise.resolve({ isConfirmed: confirm(opts.text || opts.title) });
        const toast = (icon, title) => window.Swal
            ? Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 3000, timerProgressBar: true })
            : alert(title);

        return {
            counts: config.counts,
            filters: { search: '', role: '', department: '', status: '', sort: 'name', direction: 'asc', page: 1 },
            loading: false,
            controller: null,

            modal: null,          // null | 'create' | 'edit'
            editId: null,
            editingSelf: false,
            form: blankForm(),
            errors: {},
            formError: '',
            saving: false,
            showPassword: false,

            /* ---------- table ---------- */

            hasFilters() {
                const f = this.filters;
                return f.search !== '' || f.role !== '' || f.department !== '' || f.status !== '';
            },

            setStatus(status) { this.filters.status = status; this.reload(); },

            sortBy(column) {
                if (this.filters.sort === column) {
                    this.filters.direction = this.filters.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    this.filters.sort = column;
                    this.filters.direction = column === 'last_login' ? 'desc' : 'asc';
                }
                this.reload();
            },

            resetFilters() {
                Object.assign(this.filters, { search: '', role: '', department: '', status: '' });
                this.reload();
            },

            reload() { this.filters.page = 1; return this.load(); },

            paginate(event) {
                const link = event.target.closest('a[href]');
                if (!link || !link.href.includes('page=')) return;
                event.preventDefault();
                this.filters.page = new URL(link.href).searchParams.get('page') || 1;
                this.load().then(() => this.$refs.table.scrollIntoView({ behavior: 'smooth', block: 'start' }));
            },

            async load() {
                this.controller?.abort();
                this.controller = new AbortController();
                this.loading = true;

                const params = new URLSearchParams();
                Object.entries(this.filters).forEach(([k, v]) => { if (v !== '' && v !== null) params.set(k, v); });

                try {
                    const res = await fetch(`${config.indexUrl}?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                        signal: this.controller.signal,
                    });
                    if (res.status === 401 || res.status === 419) return window.location.reload();
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    this.$refs.table.innerHTML = await res.text();
                    const counts = this.$refs.table.querySelector('[data-counts]')?.dataset.counts;
                    if (counts && counts !== 'null') this.counts = JSON.parse(counts);
                } catch (e) {
                    if (e.name === 'AbortError') return;
                    console.error(e);
                    toast('error', 'Could not load users. Please try again.');
                } finally {
                    if (!this.controller.signal.aborted) this.loading = false;
                }
            },

            /* ---------- requests ---------- */

            async send(url, method, body = null) {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                        ...(body ? { 'Content-Type': 'application/json' } : {}),
                    },
                    body: body ? JSON.stringify(body) : null,
                });

                if (res.status === 419) {
                    await swal({ icon: 'warning', title: 'Session expired', text: 'The page will reload so you can try again.' });
                    window.location.reload();
                    return { ok: false, status: 419, data: {} };
                }

                let data = {};
                try { data = await res.json(); } catch (_) { /* non-JSON error page */ }
                return { ok: res.ok && data.success !== false, status: res.status, data };
            },

            /* ---------- add / edit ---------- */

            openCreate() {
                this.modal = 'create';
                this.editId = null;
                this.editingSelf = false;
                this.form = blankForm();
                this.resetFormState();
                this.$nextTick(() => this.$refs.first.focus());
            },

            openEdit(user) {
                this.modal = 'edit';
                this.editId = user.id;
                this.editingSelf = user.id === {{ auth()->id() }};
                this.form = { ...blankForm(), ...user, password: '' };
                this.resetFormState();
                this.$nextTick(() => this.$refs.first.focus());
            },

            closeModal() {
                if (!this.modal || this.saving) return;
                this.modal = null;
            },

            resetFormState() {
                this.errors = {};
                this.formError = '';
                this.showPassword = false;
            },

            generatePassword() {
                const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
                const bytes = crypto.getRandomValues(new Uint32Array(12));
                let pwd = Array.from(bytes, b => chars[b % chars.length]).join('');
                if (!/\d/.test(pwd)) pwd = pwd.slice(0, -1) + '7';
                if (!/[a-z]/i.test(pwd)) pwd = 'k' + pwd.slice(1);
                this.form.password = pwd;
                this.showPassword = true;
                this.errors.password = null;
            },

            async save() {
                this.saving = true;
                this.resetFormState();
                const editing = this.modal === 'edit';
                const url = editing ? `${config.baseUrl}/${this.editId}` : config.indexUrl;
                const password = this.form.password;

                try {
                    const { ok, status, data } = await this.send(url, editing ? 'PUT' : 'POST', this.form);

                    if (ok) {
                        this.saving = false;
                        this.modal = null;
                        this.load();
                        if (password) {
                            swal({ icon: 'success', title: data.message, html: `Share this password with them privately:<br><code style="font-size:1.05em;user-select:all">${password.replace(/[&<>"]/g, '')}</code>` });
                        } else {
                            toast('success', data.message);
                        }
                        return;
                    }

                    if (status === 422 && data.errors) {
                        this.errors = data.errors;
                        this.formError = 'Please fix the highlighted fields.';
                    } else {
                        this.formError = data.message || 'Something went wrong. Please try again.';
                    }
                } catch (e) {
                    console.error(e);
                    this.formError = 'Could not reach the server. Check your connection and try again.';
                } finally {
                    this.saving = false;
                }
            },

            /* ---------- status / delete / restore ---------- */

            async toggleStatus(id, name, isActive) {
                if (isActive) {
                    const { isConfirmed } = await swal({
                        icon: 'warning', title: `Deactivate ${name}?`,
                        text: 'They will be signed out and won\'t be able to sign in until you turn their account back on.',
                        showCancelButton: true, confirmButtonText: 'Deactivate', confirmButtonColor: '#B91C1C',
                    });
                    if (!isConfirmed) return;
                }
                const { ok, data } = await this.send(`${config.baseUrl}/${id}/toggle-status`, 'POST');
                ok ? toast('success', data.message) : swal({ icon: 'error', title: 'Not changed', text: data.message || 'Please try again.' });
                this.load();
            },

            async deleteUser(id, name) {
                const { isConfirmed } = await swal({
                    icon: 'warning', title: `Delete ${name}?`,
                    text: 'They will lose access immediately. Their files stay in the archive, and you can restore the account later from the Deleted tab.',
                    showCancelButton: true, confirmButtonText: 'Delete user', confirmButtonColor: '#B91C1C', focusCancel: true,
                });
                if (!isConfirmed) return;
                const { ok, data } = await this.send(`${config.baseUrl}/${id}`, 'DELETE');
                ok ? toast('success', data.message) : swal({ icon: 'error', title: 'Not deleted', text: data.message || 'Please try again.' });
                this.load();
            },

            async restoreUser(id, name) {
                const { ok, data } = await this.send(`${config.baseUrl}/${id}/restore`, 'POST');
                ok ? swal({ icon: 'success', title: 'Restored', text: data.message }) : swal({ icon: 'error', title: 'Not restored', text: data.message || 'Please try again.' });
                this.load();
            },
        };
    }
</script>
@endpush
