<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Collection;

class PatientSearchService
{
    /**
     * Search patients by query string
     */
    public static function search(string $query): Collection
    {
        if (strlen($query) < 2) {
            return collect();
        }

        return Patient::where(function ($q) use ($query) {
            $q->where('first_name', 'LIKE', "%{$query}%")
              ->orWhere('last_name', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%");
        })
        ->limit(20)
        ->get()
        ->map(function ($patient) {
            return [
                'id' => $patient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'name' => $patient->first_name . ' ' . $patient->last_name,
                'phone' => $patient->phone,
                'email' => $patient->email,
                'display' => $patient->first_name . ' ' . $patient->last_name . ' - ' . $patient->phone
            ];
        });
    }

    /**
     * Format patient for display
     */
    public static function formatPatient(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'name' => $patient->first_name . ' ' . $patient->last_name,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'display' => $patient->first_name . ' ' . $patient->last_name . ' - ' . $patient->phone
        ];
    }

    /**
     * Get all patients formatted for select options
     */
    public static function getAllForSelect(): Collection
    {
        return Patient::all()->map(fn($patient) => self::formatPatient($patient));
    }
}