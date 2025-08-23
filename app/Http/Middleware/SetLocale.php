<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\Setting;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get language from URL parameter, session, or settings
        $locale = $request->get('lang') 
                 ?? session('locale') 
                 ?? Setting::getLanguage();

        // Validate the locale
        if (Setting::isLanguageSupported($locale)) {
            App::setLocale($locale);
            session(['locale' => $locale]);
        } else {
            // Fallback to default language
            $defaultLocale = Setting::getLanguage();
            App::setLocale($defaultLocale);
            session(['locale' => $defaultLocale]);
        }

        return $next($request);
    }
}
