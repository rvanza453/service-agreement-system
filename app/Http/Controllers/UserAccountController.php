<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserAccountController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with(['roles:id,name', 'moduleRoles:id,user_id,module_key,role_name'])
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', [
            'users' => $users,
            'moduleRoleConfig' => config('module-roles.modules', []),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(),
            'spatieRoles' => Role::query()->orderBy('name')->pluck('name'),
            'moduleRoleConfig' => config('module-roles.modules', []),
            'selectedGlobalRole' => null,
            'selectedModuleRoles' => [],
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $this->syncRoles($user, $validated);

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(User $account): View
    {
        $account->load(['roles:id,name', 'moduleRoles:id,user_id,module_key,role_name']);

        return view('users.edit', [
            'user' => $account,
            'spatieRoles' => Role::query()->orderBy('name')->pluck('name'),
            'moduleRoleConfig' => config('module-roles.modules', []),
            'selectedGlobalRole' => optional($account->roles->first())->name,
            'selectedModuleRoles' => $account->moduleRoles->pluck('role_name', 'module_key')->toArray(),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, User $account): RedirectResponse
    {
        $validated = $this->validatePayload($request, $account->id);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $account->update($payload);
        $this->syncRoles($account, $validated);

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    private function validatePayload(Request $request, ?int $ignoreUserId = null): array
    {
        $moduleRoleConfig = config('module-roles.modules', []);
        $moduleKeys = array_keys($moduleRoleConfig);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email' . ($ignoreUserId ? ',' . $ignoreUserId : '')],
            'password' => [$ignoreUserId ? 'nullable' : 'required', 'string', 'min:6'],
            'global_role' => ['nullable', 'string', 'exists:roles,name'],
            'module_roles' => ['nullable', 'array'],
        ];

        foreach ($moduleKeys as $moduleKey) {
            $roles = Arr::get($moduleRoleConfig, $moduleKey . '.roles', []);
            $rules['module_roles.' . $moduleKey] = ['nullable', 'string', 'in:' . implode(',', $roles)];
        }

        return $request->validate($rules);
    }

    private function syncRoles(User $user, array $validated): void
    {
        $globalRole = $validated['global_role'] ?? null;
        $moduleRoles = array_filter($validated['module_roles'] ?? []);

        $user->syncRoles($globalRole ? [$globalRole] : []);
        $user->syncModuleRoles($moduleRoles);
    }
}
