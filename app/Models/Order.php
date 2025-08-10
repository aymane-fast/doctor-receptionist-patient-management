<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'medical_record_id',
        'order_type',
        'test_name',
        'notes',
        'requested_date',
        'status',
    ];

    protected $casts = [
        'requested_date' => 'date',
    ];

    public function patient() { return $this->belongsTo(Patient::class); }
    public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }
    public function medicalRecord() { return $this->belongsTo(MedicalRecord::class); }
}


