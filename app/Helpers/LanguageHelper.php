<?php

if (!function_exists('trans_choice_custom')) {
    /**
     * Custom translation choice function that handles pluralization
     */
    function trans_choice_custom($key, $number, array $replace = [], $locale = null)
    {
        return trans_choice($key, $number, $replace, $locale);
    }
}

if (!function_exists('get_current_language')) {
    /**
     * Get the current application language
     */
    function get_current_language()
    {
        return app()->getLocale();
    }
}

if (!function_exists('get_available_languages')) {
    /**
     * Get available languages
     */
    function get_available_languages()
    {
        return \App\Models\Setting::getAvailableLanguages();
    }
}

if (!function_exists('is_rtl_language')) {
    /**
     * Check if current language is right-to-left
     */
    function is_rtl_language($language = null)
    {
        $language = $language ?: get_current_language();
        $rtlLanguages = ['ar', 'he', 'fa', 'ur'];
        
        return in_array($language, $rtlLanguages);
    }
}

if (!function_exists('language_flag')) {
    /**
     * Get flag emoji for language
     */
    function language_flag($language = null)
    {
        $language = $language ?: get_current_language();
        
        $flags = [
            'en' => '🇺🇸',
            'fr' => '🇫🇷',
        ];
        
        return $flags[$language] ?? '🌐';
    }
}

if (!function_exists('t')) {
    /**
     * Short alias for translation function
     */
    function t($key, $replace = [], $locale = null)
    {
        return __($key, $replace, $locale);
    }
}

if (!function_exists('translate_appointment_reason')) {
    /**
     * Translate common appointment reasons
     */
    function translate_appointment_reason($reason)
    {
        if (empty($reason)) {
            return '-';
        }
        
        // Common appointment reason translations
        $reasonTranslations = [
            'Follow-up appointment' => __('appointments.follow_up_appointment'),
            'Walk-in consultation' => __('appointments.walk_in_consultation'),
            'Emergency consultation' => __('appointments.emergency_consultation'),
            'Routine check-up' => __('appointments.routine_checkup'),
            'General consultation' => __('appointments.general_consultation'),
            'Urgent consultation' => __('appointments.urgent_consultation'),
            'Annual checkup' => __('appointments.annual_checkup'),
            'Vaccination' => __('appointments.vaccination'),
            'Lab results review' => __('appointments.lab_results_review'),
            'Medication review' => __('appointments.medication_review'),
        ];
        
        return $reasonTranslations[$reason] ?? $reason;
    }
}
