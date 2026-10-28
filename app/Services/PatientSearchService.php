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
        $query = trim($query);
        if (strlen($query) < 2) {
            return collect();
        }

        $terms = array_filter(explode(' ', trim($query)));

        $patients = Patient::search($query)
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
            elseif (strcasecmp((string) $patient->id_card_number, $query) === 0) {
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
                // Score each term once, even when both names match.
                if (strcasecmp($patient->first_name, $term) === 0 || strcasecmp($patient->last_name, $term) === 0) {
                    $score += 600;
                }
                elseif (stripos($patient->first_name, $term) === 0 || stripos($patient->last_name, $term) === 0) {
                    $score += 400;
                }
                elseif (stripos($patient->first_name, $term) !== false || stripos($patient->last_name, $term) !== false) {
                    $score += 200;
                }
            }

            // Phone number matches
            if (stripos($patient->phone, $query) !== false) {
                $score += stripos($patient->phone, $query) === 0 ? 500 : 300;
            }

            // Email matches
            if (stripos((string) $patient->email, $query) !== false) {
                $score += stripos($patient->email, $query) === 0 ? 400 : 200;
            }

            return [
                'id' => $patient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'name' => $patient->is_pediatric ? $patient->selection_label : $fullName,
                'patient_name' => $fullName,
                'phone' => $patient->phone,
                'email' => $patient->email,
                'id_card_number' => $patient->id_card_number,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'display' => $patient->selection_label,
                'score' => $score
            ];
        })
        ->sortByDesc('score') // Sort by relevance score
        ->take(20) // Limit final results
        ->values(); // Reset array keys
    }

    /** Shared response contract for medical-record and prescription selectors. */
    public static function autocomplete(string $query): Collection
    {
        return self::search($query)->map(fn ($patient) => [
            'id' => $patient['id'],
            'name' => $patient['name'],
            'phone' => $patient['phone'] ?? 'No phone',
            'email' => $patient['email'] ?? 'No email',
            'display' => $patient['display'],
        ]);
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
            'name' => $patient->is_pediatric ? $patient->selection_label : $patient->full_name,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'display' => $patient->selection_label
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
