<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

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
        'reason',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'estimated_time' => 'datetime:H:i',
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
     * Scope for upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', today())
                    ->where('status', '!=', 'cancelled');
    }

    public function isQueueMode(): bool
    {
        return $this->appointment_mode === 'queue';
    }

    public function isScheduledMode(): bool
    {
        return !$this->isQueueMode();
    }

    public function displayTime(): string
    {
        return $this->appointment_time?->format('g:i A') ?? '-';
    }
}
