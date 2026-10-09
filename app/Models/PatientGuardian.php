<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientGuardian extends Model
{
    protected $fillable = ['name', 'relationship', 'phone', 'email', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
