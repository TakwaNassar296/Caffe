<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreEmployeeAttendanceRequest;
use App\Http\Requests\Branch\UpdateEmployeeAttendanceStatusRequest;
use App\Http\Resources\Branch\EmployeeAttendanceResource;
use App\Http\Resources\Branch\EmployeeTodayAttendanceResource;
use App\Models\EmployeeAttendance;
use App\Models\Admin;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeAttendanceController extends Controller
{
    use ApiResponse;

    /**
     * Get authenticated branch manager
     */
    protected function getManager()
    {
        $manager = Auth::user();
        
        if (!$manager || !$manager->branch_id) {
            abort(404, 'Branch manager or branch not found');
        }

        return $manager;
    }

    /**
     * Get and validate employee belongs to manager's branch
     */
    protected function getEmployee($employeeId)
    {
        $manager = $this->getManager();
        
        $employee = Admin::where('id', $employeeId)
            ->where('role', 'employee')
            ->where('branch_id', $manager->branch_id)
            ->first();
        
        if (!$employee) {
            abort(404, 'Employee not found or does not belong to your branch');
        }

        return $employee;
    }

    /**
     * Get or create attendance record for today
     */
    protected function getOrCreateAttendance($employeeId, $branchId, $notes = null)
    {
        $today = Carbon::today();
        
        return EmployeeAttendance::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $today,
            ],
            [
                'branch_id' => $branchId,
                'attendance_time' => null,
                'departure_time' => null,
                'hours_worked' => null,
                'notes' => $notes,
            ]
        );
    }

    /**
     * Record attendance (check-in)
     */
    public function attendance(StoreEmployeeAttendanceRequest $request)
    {
        $manager = $this->getManager();
        $employee = $this->getEmployee($request->employee_id);
        $today = Carbon::today();

        DB::beginTransaction();
        try {
            $attendance = EmployeeAttendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if ($attendance && $attendance->attendance_time) {
                return $this->errorResponse('Attendance already recorded for today', 400);
            }

            if (!$attendance) {
                $attendance = EmployeeAttendance::create([
                    'employee_id' => $employee->id,
                    'branch_id' => $employee->branch_id,
                    'date' => $today,
                    'attendance_time' => Carbon::now(),
                    'notes' => $request->notes,
                ]);
            } else {
                $attendance->update([
                    'attendance_time' => Carbon::now(),
                    'notes' => $request->notes ?? $attendance->notes,
                ]);
            }

            DB::commit();

            return $this->successResponse(
                'Attendance recorded successfully',
                new EmployeeAttendanceResource($attendance->load('employee'))
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to record attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Record departure (check-out)
     */
    public function departure(StoreEmployeeAttendanceRequest $request)
    {
        $manager = $this->getManager();
        $employee = $this->getEmployee($request->employee_id);
        $today = Carbon::today();

        DB::beginTransaction();
        try {
            $attendance = EmployeeAttendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if (!$attendance || !$attendance->attendance_time) {
                return $this->errorResponse('Please record attendance first', 400);
            }

            if ($attendance->departure_time) {
                return $this->errorResponse('Departure already recorded for today', 400);
            }

            $departureTime = Carbon::now();
            $attendanceTime = Carbon::parse($attendance->attendance_time);
            $hoursWorked = $attendanceTime->diffInMinutes($departureTime);

            $attendance->update([
                'departure_time' => $departureTime,
                'hours_worked' => $hoursWorked,
                'notes' => $request->notes ?? $attendance->notes,
            ]);

            DB::commit();

            return $this->successResponse(
                'Departure recorded successfully',
                new EmployeeAttendanceResource($attendance->load('employee'))
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to record departure: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Toggle attendance/departure
     */
    public function toggle(StoreEmployeeAttendanceRequest $request)
    {
        $manager = $this->getManager();
        $employee = $this->getEmployee($request->employee_id);
        $today = Carbon::today();
        $now = Carbon::now();

        DB::beginTransaction();
        try {
            $attendance = EmployeeAttendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if (!$attendance) {
                $attendance = EmployeeAttendance::create([
                    'employee_id' => $employee->id,
                    'branch_id' => $employee->branch_id,
                    'date' => $today,
                    'attendance_time' => $now,
                    'notes' => $request->notes,
                ]);
                $action = 'attendance';
            } elseif (!$attendance->attendance_time) {
                $attendance->update([
                    'attendance_time' => $now,
                    'notes' => $request->notes ?? $attendance->notes,
                ]);
                $action = 'attendance';
            } elseif (!$attendance->departure_time) {
                $attendanceTime = Carbon::parse($attendance->attendance_time);
                $hoursWorked = $attendanceTime->diffInMinutes($now);

                $attendance->update([
                    'departure_time' => $now,
                    'hours_worked' => $hoursWorked,
                    'notes' => $request->notes ?? $attendance->notes,
                ]);
                $action = 'departure';
            } else {
                return $this->errorResponse('Both attendance and departure already recorded for today', 400);
            }

            DB::commit();

            $resource = new EmployeeAttendanceResource($attendance->load('employee'));
            $data = $resource->toArray($request);
            $data['action'] = $action;

            return $this->successResponse(ucfirst($action) . ' recorded successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to toggle attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get attendance records
     */
    public function index(Request $request)
    {
        $manager = $this->getManager();

        $query = EmployeeAttendance::where('branch_id', $manager->branch_id)
            ->with(['employee'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        if ($request->has('today') && $request->today) {
            $query->where('date', Carbon::today());
        }

        $perPage = $request->get('per_page', 15);
        $attendances = $query->paginate($perPage);

        return $this->PaginationResponse(
            $attendances->setCollection(
                $attendances->getCollection()->map(function ($attendance) {
                    return new EmployeeAttendanceResource($attendance);
                })
            ),
            'Attendance records retrieved successfully'
        );
    }

    /**
     * Get today's attendance status for all employees in the branch
     */
    public function today(Request $request)
    {
        $manager = $this->getManager();
        $today = Carbon::today();

        $employees = Admin::where('branch_id', $manager->branch_id)
            ->where('role', 'employee')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $attendances = EmployeeAttendance::where('branch_id', $manager->branch_id)
            ->where('date', $today)
            ->with('employee')
            ->get()
            ->keyBy('employee_id');

        $data = $employees->map(function ($employee) use ($attendances, $today) {
            $attendance = $attendances->get($employee->id);

            return new EmployeeTodayAttendanceResource([
                'employee' => $employee,
                'attendance' => $attendance,
                'date' => $today,
                'orders' => 3,  
                'material_errors' => [
                    'coffee' => '3 kg',
                    'milk' => '2 L',
                ],  
            ]);
        });

        return $this->successResponse('Today\'s attendance retrieved successfully', [
            'date' => $today->format('Y-m-d'),
            'total_employees' => $employees->count(),
            'employees_with_attendance' => $data->where('has_attendance', true)->count(),
            'employees_with_departure' => $data->where('has_departure', true)->count(),
            'employees' => $data->values(),
        ]);
    }

    /**
     * Get all employees in the branch
     */
    public function employees()
    {
        $manager = $this->getManager();

        $employees = Admin::where('branch_id', $manager->branch_id)
            ->where('role', 'employee')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return $this->successResponse('Employees retrieved successfully', $employees);
    }

    /**
     * Update attendance/departure status
     */
    public function updateStatus(UpdateEmployeeAttendanceStatusRequest $request)
    {
        $manager = $this->getManager();
        $employee = $this->getEmployee($request->employee_id);
        $today = Carbon::today();
        $now = Carbon::now();

        DB::beginTransaction();
        try {
            $attendance = $this->getOrCreateAttendance($employee->id, $employee->branch_id, $request->notes);

            if ($request->status === 'attendance') {
                if ($attendance->attendance_time) {
                    return $this->errorResponse('Attendance already recorded for today', 400);
                }

                $attendance->update([
                    'attendance_time' => $now,
                    'notes' => $request->notes ?? $attendance->notes,
                ]);

                $message = 'Attendance recorded successfully';
            } elseif ($request->status === 'departure') {
                if (!$attendance->attendance_time) {
                    return $this->errorResponse('Please record attendance first', 400);
                }

                if ($attendance->departure_time) {
                    return $this->errorResponse('Departure already recorded for today', 400);
                }

                $attendanceTime = Carbon::parse($attendance->attendance_time);
                $hoursWorked = $attendanceTime->diffInMinutes($now);

                $attendance->update([
                    'departure_time' => $now,
                    'hours_worked' => $hoursWorked,
                    'notes' => $request->notes ?? $attendance->notes,
                ]);

                $message = 'Departure recorded successfully';
            }

            $attendance->refresh();

            $resource = new EmployeeAttendanceResource($attendance->load('employee'));
            $data = $resource->toArray($request);
            $data['status'] = $request->status;

            DB::commit();

            return $this->successResponse($message, $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update status: ' . $e->getMessage(), 500);
        }
    }
}
