<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EmployeeAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'name' => $this->employee->name ?? null,
            'date' => $this->date->format('Y-m-d'),
            'attendance_time' => $this->attendance_time 
                ? (is_string($this->attendance_time) 
                    ? Carbon::parse($this->attendance_time)->format('H:i') 
                    : $this->attendance_time->format('H:i')) 
                : null,
            'departure_time' => $this->departure_time 
                ? (is_string($this->departure_time) 
                    ? Carbon::parse($this->departure_time)->format('H:i') 
                    : $this->departure_time->format('H:i')) 
                : null,
            'hours_worked' => $this->hours_worked_formatted,
            'notes' => $this->notes,
        ];
    }
}
