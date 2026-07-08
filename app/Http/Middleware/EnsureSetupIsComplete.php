<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Setting::settingsTableExists()) {
            return $next($request);
        }

        $setupComplete = Setting::isSetupComplete();
        $isSetupRoute = $request->routeIs('setup.*');
        $isAllowedPublicRoute = $request->routeIs('language.*')
            || $request->routeIs('api.languages')
            || $request->routeIs('api.language.current');

        if (!$setupComplete && !$isSetupRoute && !$isAllowedPublicRoute) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Application setup is required before using the clinic system.',
                    'setup_url' => route('setup.show'),
                ], 409);
            }

            return redirect()->route('setup.show');
        }

        if ($setupComplete && $isSetupRoute) {
            return auth()->check()
                ? redirect()->route('dashboard')
                : redirect()->route('login');
        }

        return $next($request);
    }
}
