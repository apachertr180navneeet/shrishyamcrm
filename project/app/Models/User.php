<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    protected $appends = ['avatar_full_path'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return $this->roles->pluck('name')->intersect($roles)->isNotEmpty() ||
                   in_array($this->role, $roles) ||
                   ($this->roleModel && in_array($this->roleModel->name, $roles));
        }

        return $this->roles->contains('name', $roles) ||
               $this->role === $roles ||
               ($this->roleModel && $this->roleModel->name === $roles);
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->hasRole('super_admin') || $this->role === 'super_admin' || $this->role === 'admin') {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionName)) {
                return true;
            }
        }

        if ($this->roleModel && $this->roleModel->hasPermission($permissionName)) {
            return true;
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAgent(): bool
    {
        return $this->hasRole('agent') || $this->agent_id !== null;
    }

    public function getAvatarFullPathAttribute()
    {
        if (!empty($this->avatar)) {
            return asset($this->avatar);
        }
        return "https://ui-avatars.com/api/?name=" . urlencode($this->full_name ?: 'User') . "&background=1B365D&color=fff";
    }
}
