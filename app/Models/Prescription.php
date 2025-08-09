<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'medical_record_id',
        'medication_name',
        'dosage',
        'frequency',
        'duration_days',
        'instructions',
        'prescribed_date',
    ];

    protected $casts = [
        'prescribed_date' => 'date',
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
        return $this->belongsTo(MedicalRecord::class);
    }
}
