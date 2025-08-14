<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type'
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'string')
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    /**
     * Get working hours for a specific day
     */
    public static function getWorkingHours($day = null)
    {
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
        
        // Check today first
        if (self::isWithinWorkingHours($current)) {
            return $current;
        }
        
        // Look for next working day within 7 days
        for ($i = 0; $i < 7; $i++) {
            $testDate = $current->copy()->addDays($i);
            $day = strtolower($testDate->format('l'));
            $workingHours = self::getWorkingHours($day);
            
            if ($workingHours['is_working']) {
                return $testDate->setTimeFromTimeString($workingHours['start_time']);
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
}
