<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePoint extends Model
{
    protected $fillable = [
        'employee_id',
        'point_amount',
        'other_reason',
        'notes',
        'type_reason',
    ];

    protected $casts = [
        'notes' => 'array',
        'point_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }
}
