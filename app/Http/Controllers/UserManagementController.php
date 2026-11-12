<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:manage-users']);
    }

    /* ------------------------------------------------------------------ */
    /*  List                                                               */
    /* ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $sortable = ['name' => 'first_name', 'last_login' => 'last_login_at', 'created' => 'created_at'];
        $sort = array_key_exists((string) $request->query('sort'), $sortable) ? (string) $request->query('sort') : 'name';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $users = User::with(['roles', 'department'])
            ->when($status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $q->where(function ($q) use ($like) {
                    $q->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like])
                        ->orWhere('email', 'like', $like)
                        ->orWhere('position', 'like', $like);
                });
            })
            ->when($request->query('role'), fn ($q, $role) => $q->role($role))
            ->when($request->query('department'), fn ($q, $dept) => $q->where('department_id', $dept))
            ->orderBy($sortable[$sort], $direction)
            ->when($sort === 'name', fn ($q) => $q->orderBy('last_name', $direction))
            ->orderBy('id')
            ->paginate(15)
            ->withPath(route('users.index'));

        $counts = [
            '' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'deleted' => User::onlyTrashed()->count(),
        ];

        $view = compact('users', 'sort', 'direction', 'status', 'counts');

        if ($request->ajax()) {
            return view('users.partials.table', $view);
        }

        return view('users.index', $view + [
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function edit(Request $request, User $user)
    {
        if (! $request->ajax()) {
            return redirect()->route('users.index');
        }

        return response()->json(['success' => true, 'user' => $this->payload($user)]);
    }

    /* ------------------------------------------------------------------ */
    /*  Create / update                                                    */
    /* ------------------------------------------------------------------ */

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        if ($validated['role'] === 'administrator' && ! $this->isAdmin()) {
            return $this->fail('Only administrators can create administrator accounts.');
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'department_id' => $validated['department_id'],
            'position' => $validated['position'],
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        $this->log($user, 'created', 'User account created', ['role' => $validated['role']]);

        return response()->json(['success' => true, 'message' => "{$user->name} was added."]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($denied = $this->guardTarget($user)) {
            return $denied;
        }

        $validated = $request->validate($this->rules($user));
        $isSelf = $user->is(auth()->user());
        $currentRole = $user->roles->first()?->name;

        if ($isSelf && $validated['role'] !== $currentRole) {
            return $this->fail('You cannot change your own role.');
        }

        if ($validated['role'] === 'administrator' && ! $this->isAdmin()) {
            return $this->fail('Only administrators can assign the administrator role.');
        }

        if ($currentRole === 'administrator' && $validated['role'] !== 'administrator' && $this->activeAdminCount() <= 1) {
            return $this->fail('This is the last administrator. Make someone else an administrator first.');
        }

        $user->fill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => strtolower($validated['email']),
            'department_id' => $validated['department_id'],
            'position' => $validated['position'],
        ]);

        $passwordChanged = filled($validated['password'] ?? null);
        if ($passwordChanged) {
            $user->password = Hash::make($validated['password']);
            $user->remember_token = null; // sign out "remember me" sessions elsewhere
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        $this->log($user, 'updated', 'User account updated', [
            'role' => $validated['role'],
            'password_changed' => $passwordChanged,
        ]);

        return response()->json(['success' => true, 'message' => "{$user->name} was updated."]);
    }

    /* ------------------------------------------------------------------ */
    /*  Status / delete / restore                                          */
    /* ------------------------------------------------------------------ */

    public function toggleStatus(User $user): JsonResponse
    {
        if ($user->is(auth()->user())) {
            return $this->fail('You cannot deactivate your own account.');
        }

        if ($denied = $this->guardTarget($user)) {
            return $denied;
        }

        if ($user->is_active && $user->hasRole('administrator') && $this->activeAdminCount() <= 1) {
            return $this->fail('This is the last active administrator and cannot be deactivated.');
        }

        $user->is_active = ! $user->is_active;
        if (! $user->is_active) {
            $user->remember_token = null;
        }
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        $this->log($user, $status, "User account {$status}");

        return response()->json([
            'success' => true,
            'message' => $user->is_active
                ? "{$user->name} can sign in again."
                : "{$user->name} has been deactivated and signed out.",
            'is_active' => $user->is_active,
        ]);
    }

    /** Soft delete: the account can be restored, and their files keep their owner. */
    public function destroy(User $user): JsonResponse
    {
        if ($user->is(auth()->user())) {
            return $this->fail('You cannot delete your own account.');
        }

        if ($denied = $this->guardTarget($user)) {
            return $denied;
        }

        if ($user->hasRole('administrator') && $this->activeAdminCount() <= 1) {
            return $this->fail('This is the last administrator and cannot be deleted.');
        }

        $user->forceFill(['is_active' => false, 'remember_token' => null])->save();
        $user->delete();

        $this->log($user, 'deleted', 'User account deleted');

        return response()->json(['success' => true, 'message' => "{$user->name} was deleted. You can restore the account from the Deleted tab."]);
    }

    public function restore(User $user): JsonResponse
    {
        if ($denied = $this->guardTarget($user)) {
            return $denied;
        }

        $user->restore();
        $this->log($user, 'restored', 'User account restored');

        return response()->json(['success' => true, 'message' => "{$user->name} was restored. The account is still deactivated; switch it on to let them sign in."]);
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    private function rules(?User $user = null): array
    {
        $password = Password::min(8)->letters()->numbers();

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255',
                // Deleted accounts still hold their email, so they can be restored.
                Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => $user ? ['nullable', $password] : ['required', $password],
            'role' => ['required', Rule::exists('roles', 'name')],
            'department_id' => ['required', Rule::exists('departments', 'id')],
            'position' => ['required', 'string', 'max:150'],
        ];
    }

    private function isAdmin(): bool
    {
        return auth()->user()->hasRole('administrator');
    }

    /** Only administrators may change administrator accounts. */
    private function guardTarget(User $user): ?JsonResponse
    {
        if ($user->hasRole('administrator') && ! $this->isAdmin()) {
            return $this->fail('Only administrators can change administrator accounts.');
        }

        return null;
    }

    private function activeAdminCount(): int
    {
        return User::role('administrator')->where('is_active', true)->count();
    }

    private function fail(string $message, int $status = 403): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    private function log(User $user, string $action, string $description, array $extra = []): void
    {
        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['action' => $action, 'user_name' => $user->name] + $extra)
            ->log($description);
    }

    /** Data the edit form needs (no hashes or tokens). */
    public static function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->roles->first()?->name ?? '',
            'department_id' => $user->department_id ? (string) $user->department_id : '',
            'position' => $user->position ?? '',
        ];
    }
}
