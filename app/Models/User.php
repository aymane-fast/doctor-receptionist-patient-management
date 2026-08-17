<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'appointment_mode',
        'queue_daily_limit',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'queue_daily_limit' => 'integer',
        ];
    }

    /**
     * Check if user is a doctor
     */
    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    /**
     * Check if user is a receptionist
     */
    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function usesQueueMode(): bool
    {
        return $this->appointment_mode === 'queue';
    }

    public function usesScheduledMode(): bool
    {
        return !$this->usesQueueMode();
    }

    public function getProfessionalNameAttribute(): string
    {
        if (!$this->isDoctor() || preg_match('/^\s*dr\.?\s+/i', $this->name) === 1) {
            return $this->name;
        }

        return 'Dr. '.$this->name;
    }

    /**
     * Appointments as doctor
     */
    public function doctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * Appointments (alias for doctorAppointments for easier querying)
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * Medical records as doctor
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_id');
    }

    /**
     * Prescriptions as doctor
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }
}
