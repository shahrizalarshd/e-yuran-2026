<?php

namespace App\Providers;

use App\Models\House;
use App\Observers\HouseObserver;
use Illuminate\Support\ServiceProvider;

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
        // Register House observer for auto-generating bills
        House::observe(HouseObserver::class);
    }
}
