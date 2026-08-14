<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'college_id',
        'phone',
        'designation',
        'avatar',
        'is_active',
        'is_locked',
        'account_status',
        'force_password_reset',
        'last_login_at',
        'last_login_ip',
        'custom_permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'force_password_reset' => 'boolean',
            'last_login_at' => 'datetime',
            'custom_permissions' => 'array',
        ];
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function submissions()
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    public function notifications()
    {
        return $this->hasMany(PortalNotification::class)->orderBy('created_at', 'desc');
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        // Check explicit user-level custom permission overrides first
        if (is_array($this->custom_permissions) && !empty($this->custom_permissions)) {
            if (in_array('-' . $permission, $this->custom_permissions)) {
                return false; // Explicitly revoked at user level
            }
            if (in_array('*', $this->custom_permissions) || in_array($permission, $this->custom_permissions)) {
                return true; // Explicitly granted at user level
            }
            $parts = explode('.', $permission);
            if (count($parts) === 2 && in_array($parts[0] . '.*', $this->custom_permissions)) {
                return true;
            }
        }

        static $roleCache = [];
        if (!isset($roleCache[$this->role])) {
            $roleCache[$this->role] = \App\Models\Role::where('slug', $this->role)->first();
        }
        $roleRecord = $roleCache[$this->role];

        if ($roleRecord) {
            $perms = is_array($roleRecord->permissions) ? $roleRecord->permissions : [];
        } else {
            $perms = \App\Models\Role::getDefaultPermissions($this->role);
        }

        if (in_array('*', $perms) || in_array($permission, $perms)) {
            return true;
        }

        $parts = explode('.', $permission);
        if (count($parts) === 2 && in_array($parts[0] . '.*', $perms)) {
            return true;
        }

        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $perm) {
            if ($this->hasPermission($perm)) {
                return true;
            }
        }
        return false;
    }

    public function getRoleTitleAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Administrator',
            'college_admin' => 'College Admin / DEO',
            'registrar' => 'Registrar (Initial Verifier)',
            'office_registrar' => 'Office of the Registrar',
            'vc' => 'Vice Chancellor',
            'pro_vc' => 'Pro Vice Chancellor',
            'media_editor' => 'Media Editor',
            'viewer' => 'Viewer / Reports Access',
            default => 'User',
        };
    }
}
