<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'is_active',
        'position',
        'signature_path',
        'site_id',
        'department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function site()
    {
        return $this->belongsTo(\Modules\ServiceAgreementSystem\Models\Site::class);
    }

    public function department()
    {
        return $this->belongsTo(\Modules\ServiceAgreementSystem\Models\Department::class);
    }

    public function moduleRoles(): HasMany
    {
        return $this->hasMany(ModuleRoleAssignment::class);
    }

    public function moduleRole(string $moduleKey): ?string
    {
        return $this->moduleRoles
            ->firstWhere('module_key', $moduleKey)
            ?->role_name;
    }

    public function hasModuleRole(string $moduleKey, string|array $roles): bool
    {
        $roleName = $this->moduleRole($moduleKey);

        if (!$roleName) {
            return false;
        }

        $roleSet = is_array($roles) ? $roles : [$roles];

        return in_array($roleName, $roleSet, true);
    }

    public function syncModuleRoles(array $moduleRoles): void
    {
        $moduleRoles = array_filter($moduleRoles, fn ($role) => filled($role));

        if (empty($moduleRoles)) {
            $this->moduleRoles()->delete();

            return;
        }

        $this->moduleRoles()
            ->whereNotIn('module_key', array_keys($moduleRoles))
            ->delete();

        foreach ($moduleRoles as $moduleKey => $roleName) {
            $this->moduleRoles()->updateOrCreate(
                ['module_key' => $moduleKey],
                ['role_name' => $roleName]
            );
        }
    }
}
