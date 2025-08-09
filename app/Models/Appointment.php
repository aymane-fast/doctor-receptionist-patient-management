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
        'appointment_date',
        'appointment_time',
        'status',
        'reason',
        'notes',
        'is_current',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'is_current' => 'boolean',
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
}
