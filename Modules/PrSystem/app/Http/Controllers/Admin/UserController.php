<?php

namespace Modules\PrSystem\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Modules\PrSystem\Models\Department;
use Modules\PrSystem\Models\Site;
use Modules\PrSystem\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['roles', 'site', 'department', 'moduleRoles'])
                    ->orderBy('name')
                    ->get()
                    ->groupBy(function($user) {
                        return $user->site->name ?? 'No Site';
                    });

        return view('prsystem::admin.users.index', [
            'users' => $users,
            'moduleRoleConfig' => config('module-roles.modules', []),
        ]);
    }

    public function create()
    {
        return view('prsystem::admin.users.create', [
            'roles' => Role::all(),
            'sites' => Site::all(),
            'departments' => Department::all(),
            'moduleRoleConfig' => config('module-roles.modules', []),
            'selectedModuleRoles' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'site_id' => $validated['site_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'position' => $validated['position'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        $this->syncRoles($user, $validated);

        \Modules\PrSystem\Helpers\ActivityLogger::log('created', 'Created user: ' . $user->name, $user);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $user->load(['roles', 'moduleRoles']);
        $roles = Role::all();
        $sites = Site::all();
        
        // Filter departments based on user's site
        if ($user->site_id) {
            $departments = Department::where('site_id', $user->site_id)->orderBy('name')->get();
        } else {
            $departments = \Illuminate\Database\Eloquent\Collection::make(); // Empty if no site
        }
        
        return view('prsystem::admin.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'sites' => $sites,
            'departments' => $departments,
            'moduleRoleConfig' => config('module-roles.modules', []),
            'selectedModuleRoles' => $user->moduleRoles->pluck('role_name', 'module_key')->toArray(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $this->validatePayload($request, $user->id);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'site_id' => $validated['site_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'position' => $validated['position'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $this->syncRoles($user, $validated);

        \Modules\PrSystem\Helpers\ActivityLogger::log('updated', 'Updated user: ' . $user->name, $user);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }
        $name = $user->name;
        $user->delete();
        
        \Modules\PrSystem\Helpers\ActivityLogger::log('deleted', 'Deleted user: ' . $name);

        return back()->with('success', 'User deleted successfully.');
    }
    
    public function impersonate(User $user, \Illuminate\Http\Request $request)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Only admin can impersonate users.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        // Verify password
        $password = $request->input('admin_password');
        if ($password !== config('prsystem.app.admin_verification_password', config('app.admin_verification_password'))) {
            return back()->with('error', 'Password verifikasi salah!');
        }

        session(['impersonate_admin_id' => auth()->id()]);
        
        \Illuminate\Support\Facades\Auth::login($user);

        \Modules\PrSystem\Helpers\ActivityLogger::log('impersonated', 'Admin impersonated user: ' . $user->name, $user);

        return redirect()->route('modules.index')->with('success', 'Now logged in as ' . $user->name);
    }

    public function leaveImpersonate()
    {
        if (!session()->has('impersonate_admin_id')) {
            return redirect()->route('modules.index')->with('error', 'You are not impersonating anyone.');
        }

        $adminId = session('impersonate_admin_id');
        $currentUser = auth()->user();
        
        session()->forget('impersonate_admin_id');
        
        $admin = User::findOrFail($adminId);
        \Illuminate\Support\Facades\Auth::login($admin);

        \Modules\PrSystem\Helpers\ActivityLogger::log('left-impersonation', 'Admin left impersonation of user: ' . $currentUser->name);

        return redirect()->route('users.index')->with('success', 'Returned to admin account.');
    }

    private function validatePayload(Request $request, ?int $ignoreUserId = null): array
    {
        $moduleRoleConfig = config('module-roles.modules', []);
        $moduleKeys = array_keys($moduleRoleConfig);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email' . ($ignoreUserId ? ',' . $ignoreUserId : '')],
            'password' => [$ignoreUserId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'global_role' => ['nullable', 'string', 'exists:roles,name', 'required_without:role'],
            'role' => ['nullable', 'string', 'exists:roles,name', 'required_without:global_role'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'module_roles' => ['nullable', 'array'],
        ];

        foreach ($moduleKeys as $moduleKey) {
            $roles = Arr::get($moduleRoleConfig, $moduleKey . '.roles', []);
            $rules['module_roles.' . $moduleKey] = empty($roles)
                ? ['nullable', 'string']
                : ['nullable', 'string', 'in:' . implode(',', $roles)];
        }

        return $request->validate($rules);
    }

    private function syncRoles(User $user, array $validated): void
    {
        $globalRole = $validated['global_role'] ?? $validated['role'] ?? null;
        $moduleRoles = array_filter($validated['module_roles'] ?? []);

        $user->syncRoles($globalRole ? [$globalRole] : []);
        $user->syncModuleRoles($moduleRoles);
    }
}
