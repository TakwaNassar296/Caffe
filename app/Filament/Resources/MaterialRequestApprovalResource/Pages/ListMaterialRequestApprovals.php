<?php

namespace App\Filament\Resources\MaterialRequestApprovalResource\Pages;

use App\Filament\Resources\MaterialRequestApprovalResource;
use App\Models\Branch;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListMaterialRequestApprovals extends ListRecords
{
    protected static string $resource = MaterialRequestApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Approval history is read-only - records are created automatically
        ];
    }

    public function getTabs(): array
    {
        $now = Carbon::now();
        $auth = auth()->guard('admin')->user();
        $isSuperAdmin = $auth && $auth->super_admin == 1;

        $tabs = [
            null => Tab::make(__('admin.all'))
                ->badge(fn () => MaterialRequestApprovalResource::getEloquentQuery()->count()),

            'today' => Tab::make(__('admin.today'))
                ->modifyQueryUsing(function ($query) use ($now) {
                    return $query->whereBetween('created_at', [
                        $now->copy()->startOfDay(),
                        $now->copy()->endOfDay(),
                    ]);
                })
                ->badge(fn () => MaterialRequestApprovalResource::getEloquentQuery()
                    ->whereBetween('created_at', [
                        Carbon::now()->startOfDay(),
                        Carbon::now()->endOfDay(),
                    ])->count()),

            'this_week' => Tab::make(__('admin.this_week'))
                ->modifyQueryUsing(function ($query) use ($now) {
                    return $query->whereBetween('created_at', [
                        $now->copy()->startOfWeek(),
                        $now->copy()->endOfWeek(),
                    ]);
                })
                ->badge(fn () => MaterialRequestApprovalResource::getEloquentQuery()
                    ->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ])->count()),

            'this_month' => Tab::make(__('admin.this_month'))
                ->modifyQueryUsing(function ($query) use ($now) {
                    return $query->whereBetween('created_at', [
                        $now->copy()->startOfMonth(),
                        $now->copy()->endOfMonth(),
                    ]);
                })
                ->badge(fn () => MaterialRequestApprovalResource::getEloquentQuery()
                    ->whereBetween('created_at', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth(),
                    ])->count()),

            'this_year' => Tab::make(__('admin.this_year'))
                ->modifyQueryUsing(function ($query) use ($now) {
                    return $query->whereBetween('created_at', [
                        $now->copy()->startOfYear(),
                        $now->copy()->endOfYear(),
                    ]);
                })
                ->badge(fn () => MaterialRequestApprovalResource::getEloquentQuery()
                    ->whereBetween('created_at', [
                        Carbon::now()->startOfYear(),
                        Carbon::now()->endOfYear(),
                    ])->count()),
        ];

        // Add branch tabs (only for super admin and only branches with approvals)
        if ($isSuperAdmin) {
            $branchesWithApprovals = Branch::whereHas('requestMaterials.approvals')
                ->orderBy('name')
                ->get();

            foreach ($branchesWithApprovals as $branch) {
                $tabs['branch_' . $branch->id] = Tab::make($branch->name)
                    ->modifyQueryUsing(function ($query) use ($branch) {
                        return $query->whereHas('requestMaterial', function ($q) use ($branch) {
                            $q->where('branch_id', $branch->id);
                        });
                    })
                    ->badge(fn () => MaterialRequestApprovalResource::getEloquentQuery()
                        ->whereHas('requestMaterial', function ($q) use ($branch) {
                            $q->where('branch_id', $branch->id);
                        })
                        ->count());
            }
        }

        return $tabs;
    }
}
