<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Illuminate\Support\Carbon;

class CheckWorkingHours
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for settings and logout routes
        if ($request->routeIs('settings.*') || $request->routeIs('logout') || $request->routeIs('dashboard')) {
            return $next($request);
        }

        // Check if we're currently within working hours
        if (!Setting::isWithinWorkingHours()) {
            $nextWorkingTime = Setting::getNextWorkingTime();
            
            $message = 'The clinic is currently closed. ';
            if ($nextWorkingTime) {
                $message .= 'We will reopen on ' . $nextWorkingTime->format('l, M j \a\t g:i A') . '.';
            }
            
            // For AJAX requests, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Clinic is closed',
                    'message' => $message,
                    'next_opening' => $nextWorkingTime ? $nextWorkingTime->toISOString() : null
                ], 423); // 423 Locked
            }
            
            // For regular requests, redirect with error
            return redirect()->route('dashboard')->with('error', $message);
        }

        return $next($request);
    }
}
