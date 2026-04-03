<?php

namespace App\Models;

use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable implements FilamentUser
{
     use HasRoles, Notifiable , HasApiTokens;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'super_admin',
        "branch_id",
        'total_points',
        'fcm_token'
    ];
      protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'total_points' => 'decimal:2',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {

            // //  remove any previous roles
             $model->syncRoles([]);
             // add new role
             $model->assignRole($model->role);

             // get all permissions for the new role
             $permissions = Role::where('name', $model->role)->first()->permissions;
             // assign the new permissions to the user
             $model->syncPermissions([]);
             if ($permissions->isNotEmpty())
                 $model->syncPermissions($permissions);

             // Check if password is present and not empty
             if (is_null($model->password)) {
                 // Unset password so it won't be updated
                 unset($model->password);
             }


        });
    }

     public function branch()
    {
        return $this->hasOne(Branch::class);
    }

    public function assignedBranch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function points()
    {
        return $this->hasMany(EmployeePoint::class,'employee_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
