<?php

namespace App\Filament\Pages;

use App\Models\EmployeeAttendance;
use App\Models\MaterialRequestApproval;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public static function getNavigationLabel(): string
    {
        return __('admin.dashboard');
    }

    public function getTitle(): string
    {
        return __('admin.dashboard');
    }

    protected static string $view = 'filament.pages.dashboard';

    public function getColumns(): int | string | array
    {
        return 1;
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getFooterWidgets(): array
    {
        return [];
    }

    public function getViewData(): array
    {
        return [
            'sections' => $this->getDashboardSections(),
        ];
    }

    /**
     * Dashboard sections follow the same order as sidebar navigation groups
     * (see AdminPanelProvider::navigationGroups).
     */
    protected function getDashboardSections(): array
    {
        return [
            // 1. Orders (sidebar: strings.orders)
            [
                'title' => __('strings.orders'),
                'color' => 'primary',
                'count' => $this->safeCount(fn () => \App\Filament\Resources\OrderResource::getNavigationBadge()),
                'items' => [
                    ['label' => __('strings.orders'), 'url' => \App\Filament\Resources\OrderResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\OrderResource::getNavigationBadge())],
                    ['label' => __('admin.reports'), 'url' => \App\Filament\Pages\ReportsPage::getUrl(), 'count' => null],

                    ],
            ],
            // 2. Products & Categories (sidebar: admin.products_category)
            [
                'title' => __('admin.products_category'),
                'color' => 'teal',
                'count' => $this->safeCount(fn () => \App\Filament\Resources\CategoryResource::getNavigationBadge()) + $this->safeCount(fn () => \App\Filament\Resources\ProductResource::getNavigationBadge()) + $this->safeCount(fn () => \App\Filament\Resources\BranchProductResource::getNavigationBadge()) + $this->safeCount(fn () => \App\Filament\Resources\ProductsMaterialResource::getNavigationBadge()),
                'items' => [
                    ['label' => __('admin.categories'), 'url' => \App\Filament\Resources\CategoryResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\CategoryResource::getNavigationBadge())],
                    ['label' => __('admin.products'), 'url' => \App\Filament\Resources\ProductResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\ProductResource::getNavigationBadge())],
                    ['label' => __('strings.branch_products'), 'url' => \App\Filament\Resources\BranchProductResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\BranchProductResource::getNavigationBadge())],
                    ['label' => __('admin.product_materail'), 'url' => \App\Filament\Resources\ProductsMaterialResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\ProductsMaterialResource::getNavigationBadge())],
                ],
            ],
            // 3. Employees & Users (sidebar: admin.section_employees_users)
            [
                'title' => __('admin.section_employees_users'),
                'color' => 'teal',
                'count' => $this->safeCount(fn () => \App\Models\User::count()) + $this->safeCount(fn () => \App\Models\Rank::count()) + $this->safeCount(fn () => \App\Models\Driver::count()),
                'items' => [
                    ['label' => __('strings.users'), 'url' => \App\Filament\Resources\UserResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\UserResource::getNavigationBadge())],
                    ['label' => __('admin.ranks'), 'url' => \App\Filament\Resources\RankResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\RankResource::getNavigationBadge())],
                    ['label' => __('strings.drivers'), 'url' => \App\Filament\Resources\DriverResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\DriverResource::getNavigationBadge())],
                ],
            ],
            // 4. Branches & Locations (sidebar: admin.section_branches_cities)
            [
                'title' => __('admin.section_branches_cities'),
                'color' => 'orange',
                'count' => $this->safeCount(fn () => \App\Models\Branch::count()) + $this->safeCount(fn () => \App\Models\City::count()) + $this->safeCount(fn () => \App\Models\Governorate::count()),
                'items' => [
                    ['label' => __('strings.branches'), 'url' => \App\Filament\Resources\BranchResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\BranchResource::getNavigationBadge())],
                    ['label' => __('admin.cities'), 'url' => \App\Filament\Resources\CityResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\CityResource::getNavigationBadge())],
                    ['label' => __('strings.governorates'), 'url' => \App\Filament\Resources\GovernorateResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\GovernorateResource::getNavigationBadge())],
                ],
            ],
            // 5. Departments (sidebar: admin.section_departments)
            [
                'title' => __('admin.section_departments'),
                'color' => 'purple',
                'count' => null,
                'items' => [
                    ['label' => __('strings.branches_materail'), 'url' => \App\Filament\Resources\BranchMaterialResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\BranchMaterialResource::getNavigationBadge())],
                    ['label' => __('admin.materials'), 'url' => \App\Filament\Resources\MaterialResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\MaterialResource::getNavigationBadge())],
                    ['label' => __('admin.external_materials'), 'url' => \App\Filament\Resources\MaterialExternalResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\MaterialExternalResource::getNavigationBadge())],
                    ['label' => __('admin.waste_material'), 'url' => \App\Filament\Resources\WasteMaterialResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\WasteMaterialResource::getNavigationBadge())],
                    ['label' => __('admin.coupons'), 'url' => \App\Filament\Resources\CouponResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\CouponResource::getNavigationBadge())],
                    ['label' => __('strings.vehicle_type'), 'url' => \App\Filament\Resources\VehicleTypeResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\VehicleTypeResource::getNavigationBadge())],
                ],
            ],
            // 6. Branches / Inventory (sidebar: admin.branches)
            [
                'title' => __('admin.materials'),
                'color' => 'purple',
                'count' => $this->safeCount(fn () => \App\Filament\Resources\RequestMaterialResource::getNavigationBadge()) + $this->safeCount(fn () => MaterialRequestApproval::count()) + $this->safeCount(fn () => EmployeeAttendance::count()) + $this->safeCount(fn () => \App\Filament\Resources\BranchMaterialShipmentResource::getNavigationBadge()),
                'items' => [
                    ['label' => __('admin.request_material'), 'url' => \App\Filament\Resources\RequestMaterialResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\RequestMaterialResource::getNavigationBadge())],
                    ['label' => __('admin.employee_attendance'), 'url' => \App\Filament\Resources\EmployeeAttendanceResource::getUrl('index'), 'count' => $this->safeCount(fn () => EmployeeAttendance::count())],
                    ['label' => __('admin.material_approval'), 'url' => \App\Filament\Resources\MaterialRequestApprovalResource::getUrl('index'), 'count' => $this->safeCount(fn () => MaterialRequestApproval::count())],
                    ['label' => __('admin.material_shipment'), 'url' => \App\Filament\Resources\BranchMaterialShipmentResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\BranchMaterialShipmentResource::getNavigationBadge())],
                ],
            ],
           
             [
                'title' => __('admin.branches_managment'),
                'color' => 'orange',
                 'items' => [
                    ['label' => __('strings.attendance_absence'), 'url' => \App\Filament\Resources\EmployeeAttendanceResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\EmployeeAttendanceResource::getNavigationBadge())],
                    ['label' => __('admin.material_requests'), 'url' => \App\Filament\Resources\RequestMaterialResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\RequestMaterialResource::getNavigationBadge())],
                    ['label' => __('strings.approval_history_material'), 'url' => \App\Filament\Resources\MaterialRequestApprovalResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\MaterialRequestApprovalResource::getNavigationBadge())],
                    ['label' => __('strings.material_shipments_history'), 'url' => \App\Filament\Resources\BranchMaterialShipmentResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\BranchMaterialShipmentResource::getNavigationBadge())],
                ],
            ],

            // 8. Content (sidebar: admin.section_content)
            [
                'title' => __('admin.section_content'),
                'color' => 'orange',
                'count' => $this->safeCount(fn () => \App\Filament\Resources\PageResource::getNavigationBadge()) + $this->safeCount(fn () => \App\Models\Advertisement::count()),
                'items' => [
                    ['label' => __('admin.pages'), 'url' => \App\Filament\Resources\PageResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Filament\Resources\PageResource::getNavigationBadge())],
                    ['label' => __('admin.advertisements'), 'url' => \App\Filament\Resources\AdvertisementResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Models\Advertisement::count())],
                ],
            ],
            // 9. Settings (sidebar: admin.settings)
            [
                'title' => __('admin.settings'),
                'color' => 'warning',
                'count' => null,
                'items' => [
                    ['label' => __('admin.send_notifications'), 'url' => \App\Filament\Pages\Notification::getUrl(), 'count' => null],
                    ['label' => __('admin.notifications'), 'url' => \App\Filament\Resources\NotificationResource::getUrl(), 'count' => null],
                    ['label' => __('admin.settings'), 'url' => \App\Filament\Pages\SiteSettings::getUrl(), 'count' => null],
                    ['label' => __('strings.employees'), 'url' => \App\Filament\Resources\AdminResource::getUrl('index'), 'count' => $this->safeCount(fn () => \App\Models\Admin::count())],
                ],
            ],
        ];
    }

    protected function safeCount(callable $callback): ?int
    {
        try {
            $result = $callback();
            return $result !== null ? (int) $result : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
