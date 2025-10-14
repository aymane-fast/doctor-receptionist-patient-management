<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register language helper functions
        require_once app_path('Helpers/LanguageHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set Carbon locale based on app locale
        \Carbon\Carbon::setLocale(app()->getLocale());
    }
}
