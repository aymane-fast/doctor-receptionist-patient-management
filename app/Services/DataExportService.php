<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DataExportService
{
    /**
     * Format date safely
     */
    private function formatDate($date, $format = 'Y-m-d'): string
    {
        if (!$date) return '';
        if (is_string($date)) return $date;
        return $date->format($format);
    }

    /**
     * Export selected data types with date range filtering
     */
    public function exportData(array $dataTypes, string $startDate = null, string $endDate = null): array
    {
        $exportData = [];
        $exportInfo = [
            'generated_at' => Carbon::now(),
            'date_range' => [
                'start' => $startDate ? Carbon::parse($startDate) : null,
                'end' => $endDate ? Carbon::parse($endDate) : null,
            ],
            'data_types' => $dataTypes
        ];

        // Export each selected data type
        foreach ($dataTypes as $dataType) {
            switch ($dataType) {
                case 'patients':
                    $exportData['patients'] = $this->exportPatients($startDate, $endDate);
                    break;
                case 'appointments':
                    $exportData['appointments'] = $this->exportAppointments($startDate, $endDate);
                    break;
                case 'medical_records':
                    $exportData['medical_records'] = $this->exportMedicalRecords($startDate, $endDate);
                    break;
                case 'prescriptions':
                    $exportData['prescriptions'] = $this->exportPrescriptions($startDate, $endDate);
                    break;

                case 'users':
                    $exportData['users'] = $this->exportUsers($startDate, $endDate);
                    break;
            }
        }

        return [
            'data' => $exportData,
            'info' => $exportInfo
        ];
    }

    /**
     * Export patients data
     */
    private function exportPatients(string $startDate = null, string $endDate = null): array
    {
        $query = Patient::with(['appointments' => function($q) {
            $q->orderBy('appointment_date', 'desc');
        }]);

        // Apply date filter
        if ($startDate) {
            $query->where('created_at', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $patients = $query->orderBy('created_at', 'desc')->get();

        return [
            'headers' => [
                'ID Patient',
                'Prénom',
                'Nom',
                'Email',
                'Téléphone',
                'Sexe',
                'Date de naissance',
                'Age',
                'Adresse',
                'Numéro de carte d\'identité',
                'Contact d\'urgence',
                'Téléphone d\'urgence',
                'Allergies',
                'Conditions chroniques',
                'Total rendez-vous',
                'Dernière visite',
                'Date d\'inscription'
            ],
            'data' => $patients->map(function ($patient) {
                $lastAppointment = $patient->appointments->first();
                return [
                    $patient->patient_id,
                    $patient->first_name,
                    $patient->last_name,
                    $patient->email ?? '',
                    $patient->phone ?? '',
                    $patient->gender === 'male' ? 'Homme' : ($patient->gender === 'female' ? 'Femme' : 'Non spécifié'),
                    $patient->birth_date ? $patient->birth_date->format('Y-m-d') : '',
                    $patient->age ?? '',
                    $patient->address ?? '',
                    $patient->id_card_number ?? '',
                    $patient->emergency_contact_name ?? '',
                    $patient->emergency_contact_phone ?? '',
                    $patient->allergies ?? '',
                    $patient->chronic_conditions ?? '',
                    $patient->appointments->count(),
                    $lastAppointment ? $this->formatDate($lastAppointment->appointment_date) : '',
                    $patient->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray(),
            'summary' => [
                'total_count' => $patients->count(),
                'male_count' => $patients->where('gender', 'male')->count(),
                'female_count' => $patients->where('gender', 'female')->count(),
                'with_emergency_contact' => $patients->whereNotNull('emergency_contact_name')->count(),
            ]
        ];
    }

    /**
     * Export appointments data
     */
    private function exportAppointments(string $startDate = null, string $endDate = null): array
    {
        $query = Appointment::with(['patient', 'doctor']);

        // Apply date filter on appointment_date
        if ($startDate) {
            $query->where('appointment_date', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->where('appointment_date', '<=', Carbon::parse($endDate));
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
                             ->orderBy('appointment_time', 'desc')
                             ->get();

        return [
            'headers' => [
                'ID Rendez-vous',
                'ID Patient',
                'Nom du Patient',
                'Nom du Médecin',
                'Date du Rendez-vous',
                'Heure du Rendez-vous',
                'Statut',
                'Motif',
                'Notes',
                'Date de Création'
            ],
            'data' => $appointments->map(function ($appointment) {
                return [
                    $appointment->id,
                    $appointment->patient->patient_id,
                    $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                    $appointment->doctor->name ?? 'Not assigned',
                    $this->formatDate($appointment->appointment_date),
                    $appointment->appointment_time ? $appointment->appointment_time->format('H:i') : '',
                    match($appointment->status) {
                        'completed' => 'Terminé',
                        'scheduled' => 'Planifié',
                        'cancelled' => 'Annulé',
                        'in_progress' => 'En cours',
                        default => ucfirst($appointment->status)
                    },
                    $appointment->reason ?? '',
                    $appointment->notes ?? '',
                    $appointment->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray(),
            'summary' => [
                'total_count' => $appointments->count(),
                'completed' => $appointments->where('status', 'completed')->count(),
                'scheduled' => $appointments->where('status', 'scheduled')->count(),
                'cancelled' => $appointments->where('status', 'cancelled')->count(),
                'in_progress' => $appointments->where('status', 'in_progress')->count(),
            ]
        ];
    }

    /**
     * Export medical records data
     */
    private function exportMedicalRecords(string $startDate = null, string $endDate = null): array
    {
        $query = MedicalRecord::with(['patient', 'doctor']);

        // Apply date filter on visit_date
        if ($startDate) {
            $query->where('visit_date', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->where('visit_date', '<=', Carbon::parse($endDate));
        }

        $records = $query->orderBy('visit_date', 'desc')->get();

        return [
            'headers' => [
                'Record ID',
                'Patient ID',
                'Patient Name',
                'Doctor Name',
                'Visit Date',
                'Visit Type',
                'Symptoms',
                'Diagnosis',
                'Treatment',
                'Weight (kg)',
                'Height (cm)',
                'Blood Pressure',
                'Temperature',
                'Heart Rate',
                'Oxygen Saturation',
                'Notes',
                'Created Date'
            ],
            'data' => $records->map(function ($record) {
                return [
                    $record->id,
                    $record->patient->patient_id,
                    $record->patient->first_name . ' ' . $record->patient->last_name,
                    $record->doctor->name ?? 'Not specified',
                    $this->formatDate($record->visit_date),
                    ucfirst($record->visit_type ?? 'consultation'),
                    $record->symptoms ?? '',
                    $record->diagnosis ?? '',
                    $record->treatment ?? '',
                    $record->weight ?? '',
                    $record->height ?? '',
                    $record->blood_pressure ?? '',
                    $record->temperature ?? '',
                    $record->heart_rate ?? '',
                    $record->oxygen_saturation ?? '',
                    $record->notes ?? '',
                    $record->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray(),
            'summary' => [
                'total_count' => $records->count(),
                'with_diagnosis' => $records->whereNotNull('diagnosis')->count(),
                'with_treatment' => $records->whereNotNull('treatment')->count(),
                'with_vitals' => $records->whereNotNull('weight')->whereNotNull('height')->count(),
            ]
        ];
    }

    /**
     * Export prescriptions data
     */
    private function exportPrescriptions(string $startDate = null, string $endDate = null): array
    {
        $query = Prescription::with(['patient', 'doctor', 'items']);

        // Apply date filter on prescribed_date
        if ($startDate) {
            $query->where('prescribed_date', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->where('prescribed_date', '<=', Carbon::parse($endDate));
        }

        $prescriptions = $query->orderBy('prescribed_date', 'desc')->get();

        $prescriptionData = [];
        foreach ($prescriptions as $prescription) {
            if ($prescription->items->count() > 0) {
                // Multiple items per prescription
                foreach ($prescription->items as $item) {
                    $prescriptionData[] = [
                        $prescription->id,
                        $prescription->patient->patient_id,
                        $prescription->patient->first_name . ' ' . $prescription->patient->last_name,
                        $prescription->doctor->name ?? 'Not specified',
                        $this->formatDate($prescription->prescribed_date),
                        $item->medication_name ?? '',
                        $item->dosage ?? '',
                        $item->frequency ?? '',
                        $item->duration_days ?? '',
                        $item->instructions ?? '',
                        $prescription->notes ?? '',
                        $prescription->created_at->format('Y-m-d H:i:s')
                    ];
                }
            } else {
                // Single prescription (legacy)
                $prescriptionData[] = [
                    $prescription->id,
                    $prescription->patient->patient_id,
                    $prescription->patient->first_name . ' ' . $prescription->patient->last_name,
                    $prescription->doctor->name ?? 'Not specified',
                    $this->formatDate($prescription->prescribed_date),
                    $prescription->medication_name ?? '',
                    $prescription->dosage ?? '',
                    $prescription->frequency ?? '',
                    $prescription->duration_days ?? '',
                    $prescription->instructions ?? '',
                    $prescription->notes ?? '',
                    $prescription->created_at->format('Y-m-d H:i:s')
                ];
            }
        }

        return [
            'headers' => [
                'Prescription ID',
                'Patient ID',
                'Patient Name',
                'Doctor Name',
                'Prescribed Date',
                'Medication Name',
                'Dosage',
                'Frequency',
                'Duration (Days)',
                'Instructions',
                'Notes',
                'Created Date'
            ],
            'data' => $prescriptionData,
            'summary' => [
                'total_prescriptions' => $prescriptions->count(),
                'total_medication_items' => collect($prescriptionData)->count(),
                'unique_patients' => $prescriptions->unique('patient_id')->count(),
            ]
        ];
    }

    /**
     * Export users data
     */
    private function exportUsers(string $startDate = null, string $endDate = null): array
    {
        $query = User::query();

        // Apply date filter
        if ($startDate) {
            $query->where('created_at', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return [
            'headers' => [
                'User ID',
                'Name',
                'Email',
                'Role',
                'Email Verified',
                'Created Date',
                'Last Login'
            ],
            'data' => $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    ucfirst($user->role),
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never'
                ];
            })->toArray(),
            'summary' => [
                'total_count' => $users->count(),
                'doctors' => $users->where('role', 'doctor')->count(),
                'receptionists' => $users->where('role', 'receptionist')->count(),
                'verified_users' => $users->whereNotNull('email_verified_at')->count(),
            ]
        ];
    }

    /**
     * Generate CSV content from export data
     */
    public function generateCSV(array $exportResult): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add UTF-8 BOM
        fwrite($output, "\xEF\xBB\xBF");

        // Export info
        fputcsv($output, ['Export des Données de la Clinique']);
        fputcsv($output, ['Généré le:', $exportResult['info']['generated_at']->format('d/m/Y H:i:s')]);
        if ($exportResult['info']['date_range']['start']) {
            fputcsv($output, ['Période:', $exportResult['info']['date_range']['start']->format('d/m/Y') . ' au ' . $exportResult['info']['date_range']['end']->format('d/m/Y')]);
        }
        fputcsv($output, ['Types de données:', implode(', ', $exportResult['info']['data_types'])]);
        fputcsv($output, []);

        // Export each data type
        foreach ($exportResult['data'] as $dataType => $data) {
            fputcsv($output, [strtoupper(str_replace('_', ' ', $dataType))]);
            fputcsv($output, $data['headers']);
            
            foreach ($data['data'] as $row) {
                fputcsv($output, $row);
            }
            
            fputcsv($output, []);
            fputcsv($output, ['Résumé:']);
            foreach ($data['summary'] as $key => $value) {
                $translatedKey = match($key) {
                    'total_count' => 'Total',
                    'male_count' => 'Hommes',
                    'female_count' => 'Femmes',
                    'with_emergency_contact' => 'Avec contact d\'urgence',
                    'completed' => 'Terminés',
                    'scheduled' => 'Planifiés',
                    'cancelled' => 'Annulés',
                    'in_progress' => 'En cours',
                    'with_diagnosis' => 'Avec diagnostic',
                    'with_treatment' => 'Avec traitement',
                    'with_vitals' => 'Avec signes vitaux',
                    'total_prescriptions' => 'Total ordonnances',
                    'total_medication_items' => 'Total médicaments',
                    'unique_patients' => 'Patients uniques',
                    'pending' => 'En attente',
                    'doctors' => 'Médecins',
                    'receptionists' => 'Réceptionnistes',
                    'verified_users' => 'Utilisateurs vérifiés',
                    default => ucfirst(str_replace('_', ' ', $key))
                };
                fputcsv($output, [$translatedKey . ':', $value]);
            }
            fputcsv($output, []);
            fputcsv($output, []);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}