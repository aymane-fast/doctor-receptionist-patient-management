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
        'specialty_data',
        'is_pediatric',
    ];

    protected $casts = [
        'birth_date' => 'datetime',
        'specialty_data' => 'array',
        'is_pediatric' => 'boolean',
    ];

    protected $with = ['guardians'];

    public function guardians()
    {
        return $this->hasMany(PatientGuardian::class)->orderByDesc('is_primary')->orderBy('id');
    }

    public function getPrimaryGuardianAttribute(): ?PatientGuardian
    {
        return $this->guardians->firstWhere('is_primary', true);
    }

    public function getAgeDisplayAttribute(): string
    {
        if (! $this->birth_date) {
            return __('Age unknown');
        }
        $months = (int) $this->birth_date->diffInMonths(today());
        if ($this->is_pediatric && $months < 24) {
            return $months < 1
                ? (int) $this->birth_date->diffInDays(today()).' '.__('days old')
                : $months.' '.__('months old');
        }

        return $this->age.' '.__('years old');
    }

    public function getContactLabelAttribute(): string
    {
        return $this->primary_guardian
            ? $this->primary_guardian->name.' ('.$this->primary_guardian->relationship.') — '.$this->phone
            : $this->phone;
    }

    public function getSelectionLabelAttribute(): string
    {
        return $this->is_pediatric
            ? $this->full_name.' · '.$this->birth_date?->format('Y-m-d').' · '.$this->contact_label
            : $this->full_name.' - '.$this->phone;
    }

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
        $terms = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY);
        $fullName = self::fullNameSql();

        return $query->where(function ($q) use ($search, $terms, $fullName) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('id_card_number', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhereHas('guardians', fn ($guardian) => $guardian
                  ->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%"));

            // Every name term must match the same patient, in any order.
            // An OR per word would return unrelated people sharing one name.
            if ($terms) {
                $q->orWhere(function ($nameQuery) use ($terms, $fullName) {
                    foreach ($terms as $term) {
                        $nameQuery->whereRaw("{$fullName} LIKE ?", ["%{$term}%"]);
                    }
                });
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
