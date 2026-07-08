<?php

namespace App\Http\Controllers;

use App\Services\SetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SetupController extends Controller
{
    public function show()
    {
        return view('setup.wizard', [
            'defaultWorkingHours' => [
                'monday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
                'tuesday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
                'wednesday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
                'thursday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
                'friday' => ['is_working' => true, 'start' => '09:00', 'end' => '17:00'],
                'saturday' => ['is_working' => false, 'start' => '09:00', 'end' => '13:00'],
                'sunday' => ['is_working' => false, 'start' => '09:00', 'end' => '13:00'],
            ],
        ]);
    }

    public function store(Request $request, SetupService $setupService)
    {
        $payload = $request->all();
        $payload['doctors'] = $this->normalizeStaffRows($request->input('doctors', []));
        $payload['receptionists'] = $this->normalizeStaffRows($request->input('receptionists', []));

        $validator = Validator::make($payload, [
            'environment_mode' => ['required', Rule::in(['demo', 'production'])],
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_phone' => ['required', 'string', 'max:255'],
            'clinic_address' => ['required', 'string'],
            'clinic_email' => ['nullable', 'email', 'max:255'],
            'clinic_website' => ['nullable', 'url', 'max:255'],
            'appointment_mode' => ['required', Rule::in(['scheduled', 'queue'])],
            'appointment_duration' => ['required', 'integer', Rule::in([15, 20, 30, 45, 60])],
            'queue_daily_limit' => ['nullable', 'integer', 'min:1', 'max:500', 'required_if:appointment_mode,queue'],
            'owner_doctor.name' => ['required', 'string', 'max:255'],
            'owner_doctor.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_doctor.password' => ['required', 'string', 'min:8', 'confirmed'],
            'owner_doctor.phone' => ['nullable', 'string', 'max:255'],
            'owner_doctor.address' => ['nullable', 'string'],
            'doctors.*.name' => ['required', 'string', 'max:255'],
            'doctors.*.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'doctors.*.password' => ['required', 'string', 'min:8'],
            'doctors.*.phone' => ['nullable', 'string', 'max:255'],
            'doctors.*.address' => ['nullable', 'string'],
            'receptionists.*.name' => ['required', 'string', 'max:255'],
            'receptionists.*.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'receptionists.*.password' => ['required', 'string', 'min:8'],
            'receptionists.*.phone' => ['nullable', 'string', 'max:255'],
            'receptionists.*.address' => ['nullable', 'string'],
            'monday_working' => ['nullable', 'boolean'],
            'monday_start' => ['required_if:monday_working,1', 'date_format:H:i'],
            'monday_end' => ['required_if:monday_working,1', 'date_format:H:i', 'after:monday_start'],
            'tuesday_working' => ['nullable', 'boolean'],
            'tuesday_start' => ['required_if:tuesday_working,1', 'date_format:H:i'],
            'tuesday_end' => ['required_if:tuesday_working,1', 'date_format:H:i', 'after:tuesday_start'],
            'wednesday_working' => ['nullable', 'boolean'],
            'wednesday_start' => ['required_if:wednesday_working,1', 'date_format:H:i'],
            'wednesday_end' => ['required_if:wednesday_working,1', 'date_format:H:i', 'after:wednesday_start'],
            'thursday_working' => ['nullable', 'boolean'],
            'thursday_start' => ['required_if:thursday_working,1', 'date_format:H:i'],
            'thursday_end' => ['required_if:thursday_working,1', 'date_format:H:i', 'after:thursday_start'],
            'friday_working' => ['nullable', 'boolean'],
            'friday_start' => ['required_if:friday_working,1', 'date_format:H:i'],
            'friday_end' => ['required_if:friday_working,1', 'date_format:H:i', 'after:friday_start'],
            'saturday_working' => ['nullable', 'boolean'],
            'saturday_start' => ['required_if:saturday_working,1', 'date_format:H:i'],
            'saturday_end' => ['required_if:saturday_working,1', 'date_format:H:i', 'after:saturday_start'],
            'sunday_working' => ['nullable', 'boolean'],
            'sunday_start' => ['required_if:sunday_working,1', 'date_format:H:i'],
            'sunday_end' => ['required_if:sunday_working,1', 'date_format:H:i', 'after:sunday_start'],
        ]);

        $validator->after(function ($validator) use ($payload) {
            $emails = collect([
                data_get($payload, 'owner_doctor.email'),
                ...collect($payload['doctors'] ?? [])->pluck('email')->all(),
                ...collect($payload['receptionists'] ?? [])->pluck('email')->all(),
            ])
                ->filter()
                ->map(fn ($email) => strtolower($email))
                ->values();

            if ($emails->count() !== $emails->unique()->count()) {
                $validator->errors()->add('staff', 'Each staff account must use a unique email address.');
            }
        });

        $validated = $validator->validate();
        $validated['doctors'] = $payload['doctors'];
        $validated['receptionists'] = $payload['receptionists'];

        $primaryDoctor = $setupService->complete($validated);

        Auth::login($primaryDoctor);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Clinic setup completed successfully.');
    }

    private function normalizeStaffRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                return [
                    'name' => trim((string) ($row['name'] ?? '')),
                    'email' => trim((string) ($row['email'] ?? '')),
                    'password' => (string) ($row['password'] ?? ''),
                    'phone' => trim((string) ($row['phone'] ?? '')),
                    'address' => trim((string) ($row['address'] ?? '')),
                ];
            })
            ->filter(function ($row) {
                return collect($row)->contains(fn ($value) => $value !== '');
            })
            ->values()
            ->all();
    }
}
