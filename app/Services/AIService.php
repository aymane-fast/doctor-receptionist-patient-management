<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Generate a comprehensive AI summary of a patient's medical history
     */
    public static function generatePatientSummary(Patient $patient): array
    {
        try {
            // Gather all patient data
            $patientData = self::gatherPatientData($patient);
            
            // Build optimized prompt
            $prompt = self::buildSummaryPrompt($patientData);
            
            // Call Groq API
            $response = self::callGroqAPI($prompt);
            
            return [
                'success' => true,
                'summary' => $response,
                'generated_at' => now()->toDateTimeString()
            ];
            
        } catch (\Exception $e) {
            Log::error('AI Summary Generation Failed', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to generate AI summary. Please try again.'
            ];
        }
    }
    
    /**
     * Gather comprehensive patient data
     */
    private static function gatherPatientData(Patient $patient): array
    {
        // Load relationships
        $patient->load([
            'appointments' => function($query) {
                $query->latest('appointment_date')->take(20);
            },
            'appointments.doctor',
            'prescriptions' => function($query) {
                $query->latest('created_at')->take(15);
            },
            'prescriptions.doctor',
            'prescriptions.items',
            'medicalRecords' => function($query) {
                $query->latest('visit_date')->take(15);
            },
            'medicalRecords.doctor'
        ]);
        
        return [
            'patient' => [
                'name' => $patient->full_name,
                'age' => $patient->age ?? 'N/A',
                'gender' => $patient->gender,
                'allergies' => $patient->allergies,
                'chronic_conditions' => $patient->chronic_conditions,
            ],
            'appointments' => $patient->appointments->map(function($apt) {
                return [
                    'date' => $apt->appointment_date->format('Y-m-d'),
                    'reason' => $apt->reason,
                    'status' => $apt->status,
                    'doctor' => $apt->doctor->name ?? 'N/A',
                    'notes' => $apt->notes
                ];
            }),
            'prescriptions' => $patient->prescriptions->map(function($rx) {
                return [
                    'date' => $rx->created_at->format('Y-m-d'),
                    'medications' => $rx->items->map(function($item) {
                        return $item->medication_name . ' ' . 
                               $item->dosage . ' - ' . 
                               $item->instructions;
                    })->join('; '),
                    'doctor' => $rx->doctor->name ?? 'N/A',
                    'notes' => $rx->notes
                ];
            }),
            'medical_records' => $patient->medicalRecords->map(function($record) {
                return [
                    'date' => $record->visit_date->format('Y-m-d'),
                    'diagnosis' => $record->diagnosis,
                    'symptoms' => $record->symptoms,
                    'vital_signs' => [
                        'bp' => $record->blood_pressure,
                        'temp' => $record->temperature,
                        'pulse' => $record->pulse_rate,
                        'weight' => $record->weight
                    ],
                    'doctor' => $record->doctor->name ?? 'N/A',
                    'treatment' => $record->treatment_plan
                ];
            })
        ];
    }
    
    /**
     * Build an optimized prompt for medical summary
     */
    private static function buildSummaryPrompt(array $data): string
    {
        $patient = $data['patient'];
        
        $prompt = "You are a professional medical AI assistant. Generate a clear, concise clinical summary.\n\n";
        $prompt .= "=== PATIENT INFORMATION ===\n";
        $prompt .= "Name: {$patient['name']}\n";
        $prompt .= "Age: {$patient['age']} | Gender: {$patient['gender']}\n";
        $prompt .= "Allergies: " . ($patient['allergies'] ?: 'None reported') . "\n";
        $prompt .= "Chronic Conditions: " . ($patient['chronic_conditions'] ?: 'None reported') . "\n\n";
        
        // Appointments
        if ($data['appointments']->isNotEmpty()) {
            $prompt .= "=== APPOINTMENT HISTORY (Most Recent) ===\n";
            foreach ($data['appointments'] as $apt) {
                $prompt .= "• {$apt['date']} - {$apt['reason']} (Status: {$apt['status']})\n";
                if ($apt['notes']) {
                    $prompt .= "  Notes: {$apt['notes']}\n";
                }
            }
            $prompt .= "\n";
        }
        
        // Medical Records
        if ($data['medical_records']->isNotEmpty()) {
            $prompt .= "=== MEDICAL RECORDS ===\n";
            foreach ($data['medical_records'] as $record) {
                $prompt .= "• {$record['date']} - Dr. {$record['doctor']}\n";
                if ($record['diagnosis']) {
                    $prompt .= "  Diagnosis: {$record['diagnosis']}\n";
                }
                if ($record['symptoms']) {
                    $prompt .= "  Symptoms: {$record['symptoms']}\n";
                }
                $vitals = array_filter($record['vital_signs']);
                if (!empty($vitals)) {
                    $prompt .= "  Vitals: " . implode(', ', array_map(function($k, $v) {
                        return ucfirst($k) . ': ' . $v;
                    }, array_keys($vitals), $vitals)) . "\n";
                }
                if ($record['treatment']) {
                    $prompt .= "  Treatment: {$record['treatment']}\n";
                }
            }
            $prompt .= "\n";
        }
        
        // Prescriptions
        if ($data['prescriptions']->isNotEmpty()) {
            $prompt .= "=== PRESCRIPTION HISTORY ===\n";
            foreach ($data['prescriptions'] as $rx) {
                $prompt .= "• {$rx['date']} - {$rx['medications']}\n";
                if ($rx['notes']) {
                    $prompt .= "  Notes: {$rx['notes']}\n";
                }
            }
            $prompt .= "\n";
        }
        
        $prompt .= "=== TASK ===\n";
        $prompt .= "Provide a structured medical summary with these sections:\n\n";
        $prompt .= "1. OVERVIEW (2-3 sentences about patient's general health status and visit frequency)\n";
        $prompt .= "2. ACTIVE CONCERNS (Current health issues or ongoing treatments)\n";
        $prompt .= "3. MEDICATION SUMMARY (Current or recent medications)\n";
        $prompt .= "4. KEY ALERTS (Important warnings: allergies, chronic conditions, patterns)\n";
        $prompt .= "5. HEALTH TREND (Improvement, stable, or concerns)\n\n";
        $prompt .= "Use professional medical language but keep it concise. Focus on clinically relevant information.";
        
        return $prompt;
    }
    
    /**
     * Call Groq API with the prompt
     */
    private static function callGroqAPI(string $prompt): string
    {
        $apiKey = env('GROQ_API_KEY');
        
        if (empty($apiKey)) {
            throw new \Exception('GROQ_API_KEY not configured in .env file');
        }
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->withOptions([
            'verify' => false, // Disable SSL verification for local development
        ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile', // Updated model (Jan 2026)

            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert medical assistant that creates clear, professional clinical summaries.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3, // Lower = more focused and consistent
            'max_tokens' => 1500, // Enough for comprehensive summary
            'top_p' => 1,
            'stream' => false
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Groq API request failed: ' . $response->body());
        }
        
        $data = $response->json();
        
        return $data['choices'][0]['message']['content'] ?? 'No summary generated';
    }
}
