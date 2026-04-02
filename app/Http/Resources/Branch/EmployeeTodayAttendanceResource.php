<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EmployeeTodayAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attendance = $this->resource['attendance'] ?? null;
        $employee = $this->resource['employee'] ?? null;
        $today = $this->resource['date'] ?? Carbon::today();

        return [
            'employee_id' => $employee->id ?? null,
            'employee_name' => $employee->name ?? null,
            'employee_email' => $employee->email ?? null,
            'has_attendance' => $attendance && !is_null($attendance->attendance_time ?? null),
            'has_departure' => $attendance && !is_null($attendance->departure_time ?? null),
            'attendance_id' => $attendance->id ?? null,
            'date' => is_string($today) ? $today : $today->format('Y-m-d'),
            'attendance_time' => $attendance && $attendance->attendance_time 
                ? (is_string($attendance->attendance_time) 
                    ? Carbon::parse($attendance->attendance_time)->format('H:i') 
                    : $attendance->attendance_time->format('H:i')) 
                : null,
            'departure_time' => $attendance && $attendance->departure_time 
                ? (is_string($attendance->departure_time) 
                    ? Carbon::parse($attendance->departure_time)->format('H:i') 
                    : $attendance->departure_time->format('H:i')) 
                : null,
            'hours_worked' => $attendance->hours_worked_formatted ?? null,
            'notes' => $attendance->notes ?? null,
            'orders' => $this->resource['orders'] ?? 0,
            'material_errors' => $this->resource['material_errors'] ?? [],
        ];
    }
}
