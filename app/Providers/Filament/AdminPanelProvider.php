<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\StatsOverview;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->font('cairo')
            ->brandLogo(asset('images/logo.jpeg'))
            ->favicon(asset('images/logo.jpeg'))
            ->colors([
                'primary' => '#00aef6',
                'secondary' => '#c9903e', 

            ])
            ->brandName('Getin Coffee')
            ->databaseNotifications(true)
            ->databaseNotificationsPolling('1s')
          
               ->navigationGroups([
                'orders' => NavigationGroup::make(fn() => __('admin.orders')),
                'products_category' => NavigationGroup::make(fn() => __('admin.products_category')),
                'section_employees_users' => NavigationGroup::make(fn() => __('admin.section_employees_users')),
                'section_branches_cities' => NavigationGroup::make(fn() => __('admin.section_branches_cities')),
                'branches' => NavigationGroup::make(fn() => __('admin.branches')),
                'section_departments' => NavigationGroup::make(fn() => __('admin.section_departments')),
                'materials' => NavigationGroup::make(fn() => __('admin.materials')),
                'finance' => NavigationGroup::make(fn() => __('admin.finance')),
                'branches_managment' => NavigationGroup::make(fn() => __('admin.branches_managment')),
                'reports' => NavigationGroup::make(fn() => __('admin.reports')),
                'section_content' => NavigationGroup::make(fn() => __('admin.section_content')),
                'settings' => NavigationGroup::make(fn() => __('admin.settings')),
                'notifications' => NavigationGroup::make(fn() => __('admin.notifications')),
                'roles_group' => \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.roles')),
              

            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
              LatestOrders::class,
              StatsOverview::class,

            ])
           
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
             ])
            ->authGuard('admin')
            ->plugins([
                 FilamentShieldPlugin::make(),
                SpatieLaravelTranslatablePlugin::make()
                ->defaultLocales(['en'])
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::head.start',
            fn (): View => view('filament.custom-styles'),
        );
    }
}
