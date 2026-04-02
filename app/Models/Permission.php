<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'section',
        'section_en',
        'action',
        'action_label_ar',
        'action_label_en',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['section_trans', 'action_label_trans', 'created_at_label'];

    /**
     * Get translated section based on current locale
     */
    public function getSectionTransAttribute()
    {
        return \getTransAttribute($this, 'section');
    }

    /**
     * Get translated action label based on current locale
     */
    public function getActionLabelTransAttribute()
    {
        return \getTransAttribute($this, 'action_label');
    }

    /**
     * Get formatted created at label
     */
    public function getCreatedAtLabelAttribute()
    {
        return date('Y-m-d H:i A', strtotime($this->created_at));
    }

    /**
     * Relationship: Permission belongs to many roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
