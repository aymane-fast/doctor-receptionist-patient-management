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

        // Split query into individual terms for better matching
        $terms = array_filter(explode(' ', trim($query)));
        
        return Patient::where(function ($q) use ($query, $terms) {
            // First, try exact matches on individual fields
            $q->where('first_name', 'LIKE', "%{$query}%")
              ->orWhere('last_name', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('id_card_number', 'LIKE', "%{$query}%");
            
            // If multiple terms, try to match first name + last name combinations
            if (count($terms) >= 2) {
                $q->orWhere(function ($subQuery) use ($terms) {
                    // Try all combinations of terms as first/last name
                    foreach ($terms as $i => $firstTerm) {
                        foreach ($terms as $j => $lastTerm) {
                            if ($i !== $j) {
                                $subQuery->orWhere(function ($combo) use ($firstTerm, $lastTerm) {
                                    $combo->where('first_name', 'LIKE', "%{$firstTerm}%")
                                          ->where('last_name', 'LIKE', "%{$lastTerm}%");
                                });
                            }
                        }
                    }
                });
            }
            
            // Also search for any term in full name concatenation
            foreach ($terms as $term) {
                if (strlen($term) >= 2) {
                    $q->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
                }
            }
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
                'id_card_number' => $patient->id_card_number,
                'age' => $patient->age,
                'gender' => $patient->gender,
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