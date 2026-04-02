<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'date',
        'attendance_time',
        'departure_time',
        'hours_worked',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance_time' => 'datetime',
        'departure_time' => 'datetime',
        'hours_worked' => 'integer',
    ];

    /**
     * Get the employee that owns the attendance record.
     * Employee is stored in admins table with role = 'employee'
     */
    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    /**
     * Get the branch for this attendance record.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Calculate hours worked in hours (from minutes).
     */
    public function getHoursWorkedInHoursAttribute(): ?float
    {
        if (!$this->hours_worked) {
            return null;
        }
        return round($this->hours_worked / 60, 2);
    }

    /**
     * Format hours worked as H:MM.
     */
    public function getHoursWorkedFormattedAttribute(): ?string
    {
        if (!$this->hours_worked) {
            return null;
        }
        $hours = floor($this->hours_worked / 60);
        $minutes = $this->hours_worked % 60;
        return sprintf('%d:%02d', $hours, $minutes);
    }
}
