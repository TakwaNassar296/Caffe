<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title_ar',
        'title_en',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['title_trans', 'created_at_label', 'permissions_count'];

    /**
     * Get translated title based on current locale
     */
    public function getTitleTransAttribute()
    {
        return \getTransAttribute($this, 'title');
    }

    /**
     * Get formatted created at label
     */
    public function getCreatedAtLabelAttribute()
    {
        return date('Y-m-d H:i A', strtotime($this->created_at));
    }

    /**
     * Get permissions count
     */
    public function getPermissionsCountAttribute()
    {
        return $this->permissions()->count();
    }

    /**
     * Relationship: Role has many permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    /**
     * Relationship: Role has many employees
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'role_id');
    }
}
