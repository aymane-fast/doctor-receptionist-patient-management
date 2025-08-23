<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch(Request $request, $language)
    {
        // Validate the language
        if (!Setting::isLanguageSupported($language)) {
            return redirect()->back()->with('error', 'Language not supported');
        }

        // Update the language in settings
        Setting::setLanguage($language);
        
        // Update session
        session(['locale' => $language]);

        return redirect()->back()->with('success', 'Language changed successfully');
    }

    /**
     * Get available languages for API
     */
    public function getAvailableLanguages()
    {
        return response()->json([
            'languages' => Setting::getAvailableLanguages(),
            'current' => Setting::getLanguage()
        ]);
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage()
    {
        return response()->json([
            'language' => Setting::getLanguage()
        ]);
    }
}
