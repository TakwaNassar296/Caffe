<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
 use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;
     
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
    ];
      protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }


     public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all attendance records for this employee.
     */
    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class);
    }
 
}

