<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Models\Admin;
use App\Models\EmployeePoint;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

  /*  public function getTabs(): array
    {
        $auth = auth()->guard('admin')->user();
        
        // Base query for employees
        $baseQuery = Admin::where('role', 'employee');
        
        if (!$auth->hasRole('super_admin')) {
            $baseQuery->where('branch_id', $auth->branch_id);
        }

        return [
            'all' => Tab::make(__('admin.all_employees'))
                ->badge($baseQuery->count())
                ->badgeColor('primary'),
            
            'total_points' => Tab::make(__('admin.total_points'))
                ->modifyQueryUsing(function (Builder $query) use ($auth) {
                    return $query->where('role', 'employee')
                        ->when(!$auth->hasRole('super_admin'), function($q) use ($auth) {
                            return $q->where('branch_id', $auth->branch_id);
                        })
                        ->orderBy('total_points', 'desc');
                })
                ->badge(number_format($baseQuery->sum('total_points'), 2))
                ->badgeColor('success'),
            
            'this_month' => Tab::make(__('admin.points_this_month'))
                ->modifyQueryUsing(function (Builder $query) use ($auth) {
                    $employeeIds = EmployeePoint::whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->pluck('employee_id')
                        ->unique();
                    
                    return $query->where('role', 'employee')
                        ->when(!$auth->hasRole('super_admin'), function($q) use ($auth) {
                            return $q->where('branch_id', $auth->branch_id);
                        })
                        ->whereIn('id', $employeeIds)
                        ->orderBy('total_points', 'desc');
                })
                ->badge(number_format(EmployeePoint::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->sum('point_amount'), 2))
                ->badgeColor('info'),
            
            'this_week' => Tab::make(__('admin.points_this_week'))
                ->modifyQueryUsing(function (Builder $query) use ($auth) {
                    $employeeIds = EmployeePoint::whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])->pluck('employee_id')->unique();
                    
                    return $query->where('role', 'employee')
                        ->when(!$auth->hasRole('super_admin'), function($q) use ($auth) {
                            return $q->where('branch_id', $auth->branch_id);
                        })
                        ->whereIn('id', $employeeIds)
                        ->orderBy('total_points', 'desc');
                })
                ->badge(number_format(EmployeePoint::whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->sum('point_amount'), 2))
                ->badgeColor('warning'),
            
            'today' => Tab::make(__('admin.points_today'))
                ->modifyQueryUsing(function (Builder $query) use ($auth) {
                    $employeeIds = EmployeePoint::whereDate('created_at', Carbon::today())
                        ->pluck('employee_id')
                        ->unique();
                    
                    return $query->where('role', 'employee')
                        ->when(!$auth->hasRole('super_admin'), function($q) use ($auth) {
                            return $q->where('branch_id', $auth->branch_id);
                        })
                        ->whereIn('id', $employeeIds)
                        ->orderBy('total_points', 'desc');
                })
                ->badge(number_format(EmployeePoint::whereDate('created_at', Carbon::today())
                    ->sum('point_amount'), 2))
                ->badgeColor('danger'),
        ];
    }*/


    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('strings.super_admins'))
                ->badge(\App\Models\Admin::role('super_admin')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->role('super_admin')),
        ];
    }    
}
