<?php

namespace App\Http\Requests\Traits;

use App\Models\Setting;
use Carbon\Carbon;

trait ValidatesAppointments
{
    /**
     * Get validation rules for appointments
     */
    protected function getAppointmentValidationRules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
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
        $appointmentDateTime = Carbon::parse($date . ' ' . $time);
        
        if (!Setting::isTimeWithinWorkingHours($appointmentDateTime)) {
            $workingHours = Setting::getWorkingHours($appointmentDateTime->format('l'));
            $errorMessage = 'Appointment time is outside working hours. ';
            
            if ($workingHours && $workingHours['is_working']) {
                $errorMessage .= 'Working hours for ' . $appointmentDateTime->format('l') . ' are ' . 
                               $workingHours['start_time'] . ' - ' . $workingHours['end_time'] . '.';
            } else {
                $errorMessage .= 'We are closed on ' . $appointmentDateTime->format('l') . '.';
            }
            
            return $errorMessage;
        }

        return null;
    }

    /**
     * Check for appointment conflicts
     */
    protected function hasAppointmentConflict(int $doctorId, string $date, string $time, ?int $excludeId = null): bool
    {
        $appointmentDateTime = Carbon::parse($date . ' ' . $time);
        
        $query = \App\Models\Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $appointmentDateTime->toDateString())
            ->whereTime('appointment_time', $appointmentDateTime->format('H:i:s'))
            ->whereNotIn('status', ['cancelled', 'completed']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}