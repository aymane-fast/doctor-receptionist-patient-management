<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\SpecialtyService;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function updateDemoHours(Request $request)
    {
        abort_unless($request->user()->isDoctor() && Setting::get('deployment_mode') === 'demo', 403);
        $request->validate(['demo_always_open' => ['required', 'boolean']]);
        Setting::set('demo_always_open', $request->boolean('demo_always_open'), 'boolean');
        return back()->with('success', __('clinical_ui.demo_hours_saved'));
    }

    /**
     * Display the settings page
     */
    public function index(SpecialtyService $specialties)
    {
        $workingHours = Setting::getAllWorkingHours();
        // Edit the saved schedule, not the temporary demo override.
        foreach (array_keys($workingHours) as $day) {
            $workingHours[$day] = Setting::getWorkingHours($day, false);
        }
        $specialtyProfiles = $specialties->selectableProfiles();
        $currentSpecialty = $specialties->current();

        return view('settings.index', compact('workingHours', 'specialtyProfiles', 'currentSpecialty'));
    }

    /**
     * Update working hours settings
     */
    public function updateWorkingHours(Request $request)
    {
        if (!auth()->user()->isDoctor()) {
            abort(403, __('Only doctors can modify working hours.'));
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $rules = [];
        foreach ($days as $day) {
            $rules["{$day}_working"] = ['sometimes', 'boolean'];
            $closed = Rule::excludeIf(fn () => ! $request->boolean("{$day}_working"));
            $rules["{$day}_start"] = [$closed, 'required', 'date_format:H:i'];
            $rules["{$day}_end"] = [$closed, 'required', 'date_format:H:i', "after:{$day}_start"];
        }
        $request->validate($rules);
        
        foreach ($days as $day) {
            $working = $request->boolean("{$day}_working");
            Setting::set("{$day}_working", $working, 'boolean');
            
            if ($working) {
                Setting::set("{$day}_start", $request->input("{$day}_start"), 'time');
                Setting::set("{$day}_end", $request->input("{$day}_end"), 'time');
            }
        }

        return redirect()->route('settings.index')
            ->with('success', __('Working hours updated successfully!'));
    }

    /**
     * Update clinic information
     */
    public function updateClinicInfo(Request $request)
    {
        if (!auth()->user()->isDoctor()) {
            abort(403, __('Only doctors can modify clinic information.'));
        }

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
            ->with('success', __('Clinic information updated successfully!'));
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



    /**
     * Update appointment duration setting
     */
    public function updateAppointmentDuration(Request $request)
    {
        $request->validate([
            'appointment_duration' => 'required|integer|in:15,20,30,45,60'
        ]);

        Setting::setAppointmentDuration($request->appointment_duration);

        return redirect()->route('settings.index')
            ->with('success', __('appointments.appointment_duration_updated'));
    }

    public function updateAppointmentMode(Request $request)
    {
        if (!auth()->user()->isDoctor()) {
            abort(403, __('Only doctors can modify appointment mode.'));
        }

        $validated = $request->validate([
            'appointment_mode' => 'required|in:scheduled,queue',
            'queue_daily_limit' => 'nullable|integer|min:1|max:500',
        ]);

        auth()->user()->update([
            'appointment_mode' => $validated['appointment_mode'],
            'queue_daily_limit' => $validated['appointment_mode'] === 'queue'
                ? ($validated['queue_daily_limit'] ?? null)
                : null,
        ]);

        return redirect()->route('settings.index')
            ->with('success', __('Appointment mode updated successfully.'));
    }

    /** Change the practice profile without modifying any existing records. */
    public function updateSpecialty(Request $request, SpecialtyService $specialties)
    {
        if (! auth()->user()->isDoctor()) {
            abort(403, __('Only doctors can modify the clinic specialty.'));
        }

        $validated = $request->validate([
            'clinic_specialty' => ['required', Rule::in(array_keys($specialties->selectableProfiles()))],
            'apply_specialty_defaults' => ['nullable', 'boolean'],
        ]);

        $specialties->setCurrent($validated['clinic_specialty']);

        if ($request->boolean('apply_specialty_defaults')) {
            $defaults = $specialties->suggestedDefaults($validated['clinic_specialty']);
            Setting::setAppointmentDuration($defaults['appointment_duration']);
            Setting::set('default_appointment_mode', $defaults['appointment_mode'], 'string');

            auth()->user()->update([
                'appointment_mode' => $defaults['appointment_mode'],
                'queue_daily_limit' => null,
            ]);
        }

        return redirect()->route('settings.index')
            ->with('success', __('Clinic specialty updated successfully.'));
    }
}
