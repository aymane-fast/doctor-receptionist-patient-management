<?php

namespace App\Http\Requests\Traits;

use App\Services\AppointmentSchedulingService;
use Illuminate\Validation\Rule;

trait ValidatesAppointments
{
    /**
     * Get validation rules for appointments
     */
    protected function getAppointmentValidationRules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'doctor'),
            ],
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Validate appointment is within working hours
     */
    protected function validateWorkingHours(string $date, string $time): ?string
    {
        return app(AppointmentSchedulingService::class)->validateScheduledTime($date, $time);
    }

    /**
     * Check for appointment conflicts
     */
    protected function hasAppointmentConflict(int $doctorId, string $date, string $time, ?int $excludeId = null): bool
    {
        return app(AppointmentSchedulingService::class)->hasScheduledConflict($doctorId, $date, $time, $excludeId);
    }
}
