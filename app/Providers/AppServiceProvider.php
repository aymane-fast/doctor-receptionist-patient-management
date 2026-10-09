<?php

namespace App\Providers;

use App\Services\SpecialtyService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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

        // Throttle credential guessing per email + IP.
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email');
            $key = (is_string($email) ? strtolower($email) : 'invalid') . '|' . $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        // Keep specialty-specific fields and appointment suggestions aligned
        // across the existing workflow without controller-specific checks.
        View::composer([
            'patients.*',
            'appointments.*',
            'medical-records.*',
            'dashboard.*',
            'doctor.*',
            'waitlist.*',
            'prescriptions.*',
            'exports.*',
            'statistics.*',
            'settings.index',
            'layouts.app',
        ], function ($view) {
            $specialties = app(SpecialtyService::class);
            $patient = $view->getData()['patient'] ?? null;

            $view->with([
                'clinicSpecialty' => $specialties->current(),
                'usesGuardians' => app(\App\Services\PatientProfileService::class)->usesGuardians($patient instanceof \App\Models\Patient ? $patient : null),
                'specialtyFieldCatalog' => [
                    'patient' => $specialties->allFields('patient'),
                    'record' => $specialties->allFields('record'),
                ],
            ]);
        });
    }
}
