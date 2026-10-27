<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientProfileService
{
    public function usesGuardians(?Patient $patient = null): bool
    {
        return $patient?->is_pediatric || app(SpecialtyService::class)->supports('guardians');
    }

    public function rules(?Patient $patient = null): array
    {
        $pediatric = $this->usesGuardians($patient);

        return [
            'birth_date' => [$pediatric ? 'required' : 'nullable', 'date', 'before_or_equal:today'],
            'phone' => [$pediatric ? 'exclude' : 'required', 'string', 'max:255', new \App\Rules\ContactPhone],
            'emergency_contact_phone' => ['nullable', 'string', 'max:255', new \App\Rules\ContactPhone],
            'email' => [$pediatric ? 'exclude' : 'nullable', 'email', 'max:255'],
            'id_card_number' => [$pediatric ? 'nullable' : 'required', 'string', 'max:255', Rule::unique('patients')->ignore($patient)],
            'guardians' => [$pediatric ? 'required' : 'exclude', 'array', 'list', 'min:1', 'max:5'],
            'guardians.*' => ['array:name,relationship,phone,email'],
            'guardians.*.name' => ['required', 'string', 'max:255'],
            'guardians.*.relationship' => ['required', 'string', 'max:100'],
            'guardians.*.phone' => ['required', 'string', 'max:255', new \App\Rules\ContactPhone],
            'guardians.*.email' => ['nullable', 'email', 'max:255'],
            'primary_guardian' => [$pediatric ? 'required' : 'exclude', 'integer', 'min:0', 'max:4'],
        ];
    }

    public function save(array $data, ?Patient $patient = null): Patient
    {
        return DB::transaction(function () use ($data, $patient) {
            $pediatric = $this->usesGuardians($patient);
            $guardians = $data['guardians'] ?? [];
            $primary = (int) ($data['primary_guardian'] ?? 0);
            if ($pediatric && ! array_key_exists($primary, $guardians)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'primary_guardian' => 'Choose one of the listed guardians as the primary contact.',
                ]);
            }
            unset($data['guardians'], $data['primary_guardian']);
            if ($pediatric) {
                $data['is_pediatric'] = true;
                // Existing scheduling and reminders consistently use the primary contact.
                $data['phone'] = $guardians[$primary]['phone'];
                $data['email'] = $guardians[$primary]['email'] ?? null;
                $details = $data['specialty_data'] ?? $patient?->specialty_data ?? [];
                if (array_key_exists('guardian_name', $details)) {
                    $details['guardian_name'] = $guardians[$primary]['name'];
                    $details['guardian_relationship'] = $guardians[$primary]['relationship'];
                    $data['specialty_data'] = $details;
                }
            }
            $patient ??= new Patient;
            $patient->fill($data)->save();
            if ($pediatric) {
                $patient->guardians()->delete();
                foreach ($guardians as $index => $guardian) {
                    $patient->guardians()->create($guardian + ['is_primary' => (int) $index === $primary]);
                }
                $patient->unsetRelation('guardians');
            }

            return $patient;
        });
    }
}
