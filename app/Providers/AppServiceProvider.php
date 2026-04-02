<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\BranchMaterial;
use App\Models\Order;
use App\Observers\BranchMaterialObserver;
use App\Observers\BranchObserver;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        
            BranchMaterial::observe(BranchMaterialObserver::class);


        //   LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
        //     $switch
        //         ->locales(['ar','en','tr']); 
        // });
    }
}

