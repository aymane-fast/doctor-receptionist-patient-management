<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    /**
     * Container binding used to memoize all settings for the current request.
     */
    private const CACHE_BINDING = 'app.settings.cache';

    protected $fillable = [
        'key',
        'value',
        'type',
        'clinic_name',
        'clinic_address',
        'clinic_phone',
        'clinic_email',
        'clinic_website',
        'clinic_logo'
    ];

    protected static function booted(): void
    {
        $flush = static fn () => app()->forgetInstance(self::CACHE_BINDING);

        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        if (!self::settingsTableExists()) {
            return $default;
        }

        $settings = self::allCached();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'string')
    {
        if (!self::settingsTableExists()) {
            return null;
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    public static function settingsTableExists(): bool
    {
        return Schema::hasTable('settings');
    }

    /**
     * All settings, loaded once per request. Settings are read dozens of
     * times per request (middleware, working hours, clinic info), so a
     * single query replaces one query per key.
     */
    private static function allCached(): array
    {
        if (!app()->bound(self::CACHE_BINDING)) {
            app()->instance(self::CACHE_BINDING, self::query()->pluck('value', 'key')->all());
        }

        return app(self::CACHE_BINDING);
    }

    /**
     * Get working hours for a specific day
     */
    public static function demoAlwaysOpen(): bool
    {
        return self::get('deployment_mode') === 'demo' && filter_var(self::get('demo_always_open', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getWorkingHours($day = null, bool $applyDemoOverride = true)
    {
        if ($applyDemoOverride && self::demoAlwaysOpen()) {
            return ['is_working' => true, 'start_time' => '00:00', 'end_time' => '23:59'];
        }
        if (!$day) {
            $day = strtolower(Carbon::now()->format('l')); // monday, tuesday, etc.
        }
        
        $isWorkingDay = self::get("{$day}_working", true);
        $startTime = self::get("{$day}_start", '09:00');
        $endTime = self::get("{$day}_end", '17:00');
        
        return [
            'is_working' => filter_var($isWorkingDay, FILTER_VALIDATE_BOOLEAN),
            'start_time' => $startTime,
            'end_time' => $endTime
        ];
    }

    /**
     * Check if current time is within working hours
     */
    public static function isWithinWorkingHours($datetime = null)
    {
        if (!$datetime) {
            $datetime = Carbon::now();
        }
        
        $day = strtolower($datetime->format('l'));
        $workingHours = self::getWorkingHours($day);
        
        if (!$workingHours['is_working']) {
            return false;
        }
        
        $currentTime = $datetime->format('H:i');
        return $currentTime >= $workingHours['start_time'] && $currentTime <= $workingHours['end_time'];
    }

    /**
     * Get next available working time
     */
    public static function getNextWorkingTime()
    {
        $current = Carbon::now();

        if (self::isWithinWorkingHours($current)) {
            return $current;
        }

        // Return the next future opening, never a time that has already passed.
        for ($i = 0; $i < 7; $i++) {
            $testDate = $current->copy()->addDays($i);
            $day = strtolower($testDate->format('l'));
            $workingHours = self::getWorkingHours($day);

            if (!$workingHours['is_working']) {
                continue;
            }

            $openingTime = $testDate->copy()->setTimeFromTimeString($workingHours['start_time']);
            $closingTime = $testDate->copy()->setTimeFromTimeString($workingHours['end_time']);

            if ($current->lt($openingTime)) {
                return $openingTime;
            }

            if ($current->betweenIncluded($openingTime, $closingTime)) {
                return $current;
            }

            if ($current->gt($closingTime)) {
                continue;
            }
        }

        return null; // No working day found in next 7 days
    }

    /**
     * Check if a specific time is within working hours
     */
    public static function isTimeWithinWorkingHours($datetime)
    {
        $day = strtolower($datetime->format('l'));
        $workingHours = self::getWorkingHours($day);
        
        if (!$workingHours['is_working']) {
            return false;
        }
        
        $time = $datetime->format('H:i');
        return $time >= $workingHours['start_time'] && $time <= $workingHours['end_time'];
    }

    /**
     * Get all working days with hours
     */
    public static function getAllWorkingHours()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $workingHours = [];
        
        foreach ($days as $day) {
            $workingHours[$day] = self::getWorkingHours($day);
        }
        
        return $workingHours;
    }

    /**
     * Get the current application language
     */
    public static function getLanguage()
    {
        return self::get('app_language', 'en');
    }

    /**
     * Set the application language
     */
    public static function setLanguage($language)
    {
        return self::set('app_language', $language, 'string');
    }

    /**
     * Get available languages
     */
    public static function getAvailableLanguages()
    {
        return [
            'en' => 'English',
            'fr' => 'Français'
        ];
    }

    /**
     * Check if a language is supported
     */
    public static function isLanguageSupported($language)
    {
        return array_key_exists($language, self::getAvailableLanguages());
    }

    /**
     * Get clinic information
     */
    public static function getClinicInfo()
    {
        return [
            'name' => self::get('clinic_name', 'Medical Clinic'),
            'address' => self::get('clinic_address', ''),
            'phone' => self::get('clinic_phone', ''),
            'email' => self::get('clinic_email', ''),
            'website' => self::get('clinic_website', ''),
            'logo' => self::get('clinic_logo', '')
        ];
    }

    /**
     * Set clinic information
     */
    public static function setClinicInfo($data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'address', 'phone', 'email', 'website', 'logo'])) {
                // Convert null/empty values to empty string for database
                $value = $value ?? '';
                self::set("clinic_{$key}", $value);
            }
        }
    }

    /**
     * Get appointment duration in minutes
     */
    public static function getAppointmentDuration()
    {
        return (int) self::get('appointment_duration', 30);
    }

    /**
     * Set appointment duration in minutes
     */
    public static function setAppointmentDuration($minutes)
    {
        self::set('appointment_duration', (int) $minutes);
    }

    public static function isSetupComplete(): bool
    {
        return filter_var(self::get('setup_complete', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function setSetupComplete(bool $complete): void
    {
        self::set('setup_complete', $complete ? '1' : '0', 'boolean');
    }

    public static function getDeploymentMode(): string
    {
        return self::get('deployment_mode', 'production');
    }

    public static function getDefaultAppointmentMode(): string
    {
        $mode = self::get('default_appointment_mode', 'scheduled');

        return in_array($mode, ['scheduled', 'queue'], true) ? $mode : 'scheduled';
    }

    public static function getDefaultQueueDailyLimit(): ?int
    {
        $value = self::get('default_queue_daily_limit');

        return $value === null || $value === '' ? null : (int) $value;
    }
}
