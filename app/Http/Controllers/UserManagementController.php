<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:manage-users']);
    }

    private function isAdmin(): bool
    {
        return auth()->user()->roles->contains('name', 'administrator');
    }

    public function index(Request $request)
    {
        $query = User::with(['roles', 'department'])
            ->when($request->search, function($q) use ($request) {
                $q->where(function($query) use ($request) {
                    $query->where('first_name', 'like', "%{$request->search}%")
                          ->orWhere('last_name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%")
                          ->orWhere('position', 'like', "%{$request->search}%");
                });
            })
            ->when($request->role, function($q) use ($request) {
                $q->role($request->role);
            })
            ->when($request->department, function($q) use ($request) {
                $q->where('department_id', $request->department);
            })
            ->when($request->status, function($q) use ($request) {
                $q->where('is_active', $request->status === 'active');
            });

        $users = $query->paginate(15);
        $roles = Role::all();
        $departments = Department::where('is_active', true)->get();

        if ($request->ajax()) {
            return view('users.partials.table', compact('users'));
        }

        return view('users.index', compact('users', 'roles', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'min:8'],
            'role' => 'required|exists:roles,name',
            'department_id' => 'required|exists:departments,id',
            'position' => 'required|string|max:255',
        ]);

        if ($validated['role'] === 'administrator' && !$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only administrators can create admin users.'], 403);
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department_id' => $validated['department_id'],
            'position' => $validated['position'],
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'created', 'user_name' => $user->name, 'role' => $validated['role']])
            ->log('User account created');

        return response()->json(['success' => true, 'message' => 'User created successfully']);
    }

    public function edit(Request $request, User $user)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => $user->load('roles')
            ]);
        }
        
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8',
            'role' => 'required|exists:roles,name',
            'department_id' => 'required|exists:departments,id',
            'position' => 'required|string|max:255',
        ]);

        if ($validated['role'] === 'administrator' && !$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only administrators can assign admin role.'], 403);
        }

        if ($user->hasRole('administrator') && $validated['role'] !== 'administrator' && User::role('administrator')->count() <= 1) {
            return response()->json(['success' => false, 'message' => 'Cannot change the role of the last administrator.'], 403);
        }

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'department_id' => $validated['department_id'],
            'position' => $validated['position'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'updated', 'user_name' => $user->name])
            ->log('User account updated');

        return response()->json(['success' => true, 'message' => 'User updated successfully']);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 403);
        }

        if ($user->hasRole('administrator') && User::role('administrator')->count() <= 1) {
            return response()->json(['success' => false, 'message' => 'Cannot delete the last administrator.'], 403);
        }

        $userName = $user->name;
        $user->delete();

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'deleted', 'user_name' => $userName])
            ->log('User account deleted');

        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }

    public function toggleStatus(User $user)
    {
        // Clear any output buffer to prevent "folder" prefix
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own status.'
            ], 403);
        }

        if ($user->hasRole('administrator') && $user->is_active && User::role('administrator')->where('is_active', true)->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate the last active administrator.'
            ], 403);
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['action' => $status, 'user_name' => $user->name])
            ->log("User account {$status}");

        return response()->json([
            'success' => true, 
            'message' => "User {$status} successfully",
            'is_active' => $user->is_active
        ]);
    }
}