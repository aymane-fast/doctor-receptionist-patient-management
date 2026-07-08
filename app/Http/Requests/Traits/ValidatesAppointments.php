<?php

namespace App\Http\Requests\Traits;

use App\Services\AppointmentSchedulingService;

trait ValidatesAppointments
{
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
