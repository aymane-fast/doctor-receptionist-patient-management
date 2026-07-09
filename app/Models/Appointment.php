<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * All statuses an appointment can be in. "no_show" means the patient
     * never arrived, which clinics track separately from cancellations.
     */
    public const STATUSES = ['scheduled', 'in_progress', 'completed', 'cancelled', 'no_show'];

    /**
     * Statuses that still occupy the doctor's time (block a slot / hold a queue spot).
     */
    public const ACTIVE_STATUSES = ['scheduled', 'in_progress'];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_mode',
        'appointment_date',
        'appointment_time',
        'queue_number',
        'queue_position',
        'estimated_time',
        'status',
        'checked_in_at',
        'reason',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'estimated_time' => 'datetime:H:i',
        'checked_in_at' => 'datetime',
        'queue_number' => 'integer',
        'queue_position' => 'integer',
    ];

    /**
     * Patient relationship
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Doctor relationship
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Medical record relationship
     */
    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    /**
     * Scope for today's appointments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    /**
     * Scope for upcoming appointments (still awaiting the patient)
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', today())
            ->whereIn('status', self::ACTIVE_STATUSES);
    }

    /**
     * Scope for appointments still waiting to be seen
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function isQueueMode(): bool
    {
        return $this->appointment_mode === 'queue';
    }

    public function isScheduledMode(): bool
    {
        return ! $this->isQueueMode();
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    /**
     * Whether the appointment can still be cancelled or marked as a no-show.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function displayTime(): string
    {
        return $this->appointment_time?->format('g:i A') ?? '-';
    }

    /**
     * Human label for a status (shared by views and exports).
     */
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'in_progress' => __('appointments.in_progress'),
            'no_show' => __('appointments.no_show'),
            default => __('appointments.'.$status),
        };
    }

    /**
     * Tailwind badge classes per status (shared by views).
     */
    public static function statusBadgeClasses(string $status): string
    {
        return match ($status) {
            'completed' => 'bg-green-100 text-green-700',
            'in_progress' => 'bg-blue-100 text-blue-700',
            'cancelled' => 'bg-red-100 text-red-600',
            'no_show' => 'bg-orange-100 text-orange-700',
            default => 'bg-yellow-100 text-yellow-700',
        };
    }
}
