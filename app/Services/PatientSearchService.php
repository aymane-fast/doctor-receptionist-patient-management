<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Collection;

class PatientSearchService
{
    /**
     * Search patients by query string with proper relevance scoring
     */
    public static function search(string $query): Collection
    {
        if (strlen($query) < 2) {
            return collect();
        }

        // Split query into individual terms for better matching
        $terms = array_filter(explode(' ', trim($query)));
        
        $patients = Patient::where(function ($q) use ($query, $terms) {
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
        ->limit(50) // Get more results to sort properly
        ->get();

        // Add relevance scoring and sort by best matches first
        return $patients->map(function ($patient) use ($query, $terms) {
            $fullName = $patient->first_name . ' ' . $patient->last_name;
            $score = 0;

            // Perfect full name match gets highest score
            if (strcasecmp($fullName, $query) === 0) {
                $score += 1000;
            }
            // Exact ID match gets very high score
            elseif (strcasecmp($patient->id_card_number, $query) === 0) {
                $score += 900;
            }
            // Full name starts with query gets high score
            elseif (stripos($fullName, $query) === 0) {
                $score += 800;
            }
            // Full name contains query gets good score
            elseif (stripos($fullName, $query) !== false) {
                $score += 700;
            }

            // Individual name matches
            foreach ($terms as $term) {
                // Exact first name match
                if (strcasecmp($patient->first_name, $term) === 0) {
                    $score += 600;
                }
                // Exact last name match
                elseif (strcasecmp($patient->last_name, $term) === 0) {
                    $score += 600;
                }
                // First name starts with term
                elseif (stripos($patient->first_name, $term) === 0) {
                    $score += 400;
                }
                // Last name starts with term
                elseif (stripos($patient->last_name, $term) === 0) {
                    $score += 400;
                }
                // Name contains term
                elseif (stripos($patient->first_name, $term) !== false) {
                    $score += 200;
                }
                elseif (stripos($patient->last_name, $term) !== false) {
                    $score += 200;
                }
            }

            // Phone number matches
            if (stripos($patient->phone, $query) !== false) {
                $score += stripos($patient->phone, $query) === 0 ? 500 : 300;
            }

            // Email matches
            if (stripos($patient->email, $query) !== false) {
                $score += stripos($patient->email, $query) === 0 ? 400 : 200;
            }

            return [
                'id' => $patient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'name' => $fullName,
                'phone' => $patient->phone,
                'email' => $patient->email,
                'id_card_number' => $patient->id_card_number,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'display' => $fullName . ' - ' . $patient->phone,
                'score' => $score
            ];
        })
        ->sortByDesc('score') // Sort by relevance score
        ->take(20) // Limit final results
        ->values(); // Reset array keys
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