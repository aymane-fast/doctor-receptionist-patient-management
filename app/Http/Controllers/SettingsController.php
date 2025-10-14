<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $workingHours = Setting::getAllWorkingHours();
        return view('settings.index', compact('workingHours'));
    }

    /**
     * Update working hours settings
     */
    public function updateWorkingHours(Request $request)
    {
        $request->validate([
            'monday_working' => 'boolean',
            'monday_start' => 'required_if:monday_working,1|date_format:H:i',
            'monday_end' => 'required_if:monday_working,1|date_format:H:i|after:monday_start',
            'tuesday_working' => 'boolean',
            'tuesday_start' => 'required_if:tuesday_working,1|date_format:H:i',
            'tuesday_end' => 'required_if:tuesday_working,1|date_format:H:i|after:tuesday_start',
            'wednesday_working' => 'boolean',
            'wednesday_start' => 'required_if:wednesday_working,1|date_format:H:i',
            'wednesday_end' => 'required_if:wednesday_working,1|date_format:H:i|after:wednesday_start',
            'thursday_working' => 'boolean',
            'thursday_start' => 'required_if:thursday_working,1|date_format:H:i',
            'thursday_end' => 'required_if:thursday_working,1|date_format:H:i|after:thursday_start',
            'friday_working' => 'boolean',
            'friday_start' => 'required_if:friday_working,1|date_format:H:i',
            'friday_end' => 'required_if:friday_working,1|date_format:H:i|after:friday_start',
            'saturday_working' => 'boolean',
            'saturday_start' => 'required_if:saturday_working,1|date_format:H:i',
            'saturday_end' => 'required_if:saturday_working,1|date_format:H:i|after:saturday_start',
            'sunday_working' => 'boolean',
            'sunday_start' => 'required_if:sunday_working,1|date_format:H:i',
            'sunday_end' => 'required_if:sunday_working,1|date_format:H:i|after:sunday_start',
        ]);

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($days as $day) {
            $working = $request->has("{$day}_working");
            Setting::set("{$day}_working", $working, 'boolean');
            
            if ($working) {
                Setting::set("{$day}_start", $request->input("{$day}_start"), 'time');
                Setting::set("{$day}_end", $request->input("{$day}_end"), 'time');
            }
        }

        return redirect()->route('settings.index')
            ->with('success', 'Working hours updated successfully!');
    }

    /**
     * Update clinic information
     */
    public function updateClinicInfo(Request $request)
    {
        $request->validate([
            'clinic_name' => 'required|string|max:255',
            'clinic_address' => 'required|string',
            'clinic_phone' => 'required|string|max:255',
            'clinic_email' => 'nullable|email|max:255',
            'clinic_website' => 'nullable|url|max:255',
        ]);

        $clinicData = [
            'name' => $request->clinic_name,
            'address' => $request->clinic_address,
            'phone' => $request->clinic_phone,
            'email' => $request->clinic_email,
            'website' => $request->clinic_website,
        ];

        Setting::setClinicInfo($clinicData);

        return redirect()->route('settings.index')
            ->with('success', 'Clinic information updated successfully!');
    }

    /**
     * Get current working status (API endpoint)
     */
    public function workingStatus()
    {
        return response()->json([
            'is_working' => Setting::isWithinWorkingHours(),
            'next_working_time' => Setting::getNextWorkingTime(),
            'today_hours' => Setting::getWorkingHours(),
        ]);
    }
}
