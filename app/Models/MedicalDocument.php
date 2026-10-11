<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalDocument extends Model
{
    protected $fillable = ['patient_id', 'doctor_id', 'medical_record_id', 'order_id', 'title', 'path', 'mime_type', 'size'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
