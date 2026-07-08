<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'id_card_number',
        'allergies',
        'chronic_conditions',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'birth_date' => 'datetime',
    ];

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get age (null when the birth date is unknown)
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        return (int) Carbon::parse($this->birth_date)->diffInYears(Carbon::now());
    }

    /**
     * SQL expression for the patient's full name, per database driver.
     */
    public static function fullNameSql(): string
    {
        return DB::connection()->getDriverName() === 'mysql'
            ? "CONCAT(first_name, ' ', last_name)"
            : "(first_name || ' ' || last_name)";
    }

    /**
     * Search patients by name (any order / partial), phone, email or ID card.
     */
    public function scopeSearch($query, string $search)
    {
        $search = trim($search);
        $terms = array_filter(explode(' ', $search));
        $fullName = self::fullNameSql();

        return $query->where(function ($q) use ($search, $terms, $fullName) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('id_card_number', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");

            // Match multi-term queries as first/last name in any order.
            if (count($terms) >= 2) {
                $q->orWhere(function ($subQuery) use ($terms) {
                    foreach ($terms as $i => $firstTerm) {
                        foreach ($terms as $j => $lastTerm) {
                            if ($i !== $j) {
                                $subQuery->orWhere(function ($combo) use ($firstTerm, $lastTerm) {
                                    $combo->where('first_name', 'like', "%{$firstTerm}%")
                                          ->where('last_name', 'like', "%{$lastTerm}%");
                                });
                            }
                        }
                    }
                });
            }

            foreach ($terms as $term) {
                if (strlen($term) >= 2) {
                    $q->orWhereRaw("{$fullName} LIKE ?", ["%{$term}%"]);
                }
            }
        });
    }

    /**
     * Patient's appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Patient's medical records
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Patient's prescriptions
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

}
