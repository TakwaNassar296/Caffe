<?php

namespace App\Filament\Resources\RequestMaterialResource\Pages;

use App\Filament\Resources\RequestMaterialResource;
use App\Models\Branch;
use App\Models\RequestMaterial;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListRequestMaterials extends ListRecords
{
    protected static string $resource = RequestMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Material requests are created via API by branches
        ];
    }

    public function getTabs(): array
    {
        $now = Carbon::now();
        $auth = auth()->guard('admin')->user();
        $isSuperAdmin = $auth && $auth->super_admin == 1;

        $tabs = [
            null => Tab::make(__('admin.all'))
                ->badge(fn () => RequestMaterialResource::getEloquentQuery()->count()),

            'today' => Tab::make(__('admin.today'))
                ->modifyQueryUsing(function ($query) use ($now) {
                    return $query->whereBetween('created_at', [
                        $now->copy()->startOfDay(),
                        $now->copy()->endOfDay(),
                    ]);
                })
                ->badge(fn () => RequestMaterialResource::getEloquentQuery()
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
                ->badge(fn () => RequestMaterialResource::getEloquentQuery()
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
                ->badge(fn () => RequestMaterialResource::getEloquentQuery()
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
                ->badge(fn () => RequestMaterialResource::getEloquentQuery()
                    ->whereBetween('created_at', [
                        Carbon::now()->startOfYear(),
                        Carbon::now()->endOfYear(),
                    ])->count()),
        ];

        // Add branch tabs (only for super admin and only branches with requests)
        if ($isSuperAdmin) {
            $branchesWithRequests = Branch::whereHas('requestMaterials')
                ->orderBy('name')
                ->get();

            foreach ($branchesWithRequests as $branch) {
                $tabs['branch_' . $branch->id] = Tab::make($branch->name)
                    ->modifyQueryUsing(function ($query) use ($branch) {
                        return $query->where('branch_id', $branch->id);
                    })
                    ->badge(fn () => RequestMaterialResource::getEloquentQuery()
                        ->where('branch_id', $branch->id)
                        ->count());
            }
        }

        return $tabs;
    }
}
