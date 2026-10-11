<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'temperature_unit',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'visit_date',
        'visit_type',
        'history_present_illness',
        'physical_examination',
        'symptoms',
        'diagnosis',
        'treatment',
        'notes',
        'weight',
        'height',
        'blood_pressure',
        'temperature',
        'heart_rate',
        'oxygen_saturation',
        'specialty_data',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'temperature' => 'decimal:1',
        'oxygen_saturation' => 'integer',
        'specialty_data' => 'array',
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
     * Appointment relationship
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Prescriptions relationship
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function orders() { return $this->hasMany(Order::class); }
    public function documents() { return $this->hasMany(MedicalDocument::class); }
}
