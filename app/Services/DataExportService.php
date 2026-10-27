<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;

class DataExportService
{
    public function __construct(private SpecialtyService $specialties) {}

    /**
     * Format date safely
     */
    private function formatDate($date, $format = 'Y-m-d'): string
    {
        if (! $date) {
            return '';
        }
        if (is_string($date)) {
            return $date;
        }

        return $date->format($format);
    }

    /**
     * Export selected data types with date range filtering
     */
    public function exportData(array $dataTypes, ?string $startDate = null, ?string $endDate = null): array
    {
        $exportData = [];
        $exportInfo = [
            'generated_at' => Carbon::now(),
            'clinic_specialty' => __($this->specialties->current()['label']),
            'date_range' => [
                'start' => $startDate ? Carbon::parse($startDate) : null,
                'end' => $endDate ? Carbon::parse($endDate) : null,
            ],
            'data_types' => $dataTypes,
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
            'info' => $exportInfo,
        ];
    }

    /**
     * Export patients data
     */
    private function exportPatients(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Patient::with(['appointments' => function ($q) {
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
                __('Patient ID'), __('First Name'), __('Last Name'), __('Email'), __('Phone'), __('Gender'),
                __('Date of Birth'), __('Age'), __('Address'), __('ID Card Number'), __('Emergency Contact'),
                __('Emergency Phone'), __('Allergies'), __('Chronic Conditions'), __('Specialty Details'),
                __('Total Appointments'), __('Last Visit'), __('Registration Date'), __('Parent / Guardian Contacts'),
            ],
            'data' => $patients->map(function ($patient) {
                $lastAppointment = $patient->appointments->first(fn ($visit) =>
                    $visit->status === 'completed' && $visit->appointment_date->lte(today()));

                return [
                    $patient->id,
                    $patient->first_name,
                    $patient->last_name,
                    $patient->email ?? '',
                    $patient->phone ?? '',
                    $patient->gender === 'male' ? __('Male') : ($patient->gender === 'female' ? __('Female') : __('Not specified')),
                    $patient->birth_date ? $patient->birth_date->format('Y-m-d') : '',
                    $patient->age ?? '',
                    $patient->address ?? '',
                    $patient->id_card_number ?? '',
                    $patient->emergency_contact_name ?? '',
                    $patient->emergency_contact_phone ?? '',
                    $patient->allergies ?? '',
                    $patient->chronic_conditions ?? '',
                    $this->specialties->formatData('patient', $patient->specialty_data),
                    $patient->appointments->count(),
                    $lastAppointment ? $this->formatDate($lastAppointment->appointment_date) : '',
                    $patient->created_at->format('Y-m-d H:i:s'),
                    $patient->guardians->map(fn ($guardian) => ($guardian->is_primary ? __('Primary').': ' : '').$guardian->name.' ('.$guardian->relationship.') '.$guardian->phone.' '.($guardian->email ?? ''))->implode('; '),
                ];
            })->toArray(),
            'summary' => [
                'total_count' => $patients->count(),
                'male_count' => $patients->where('gender', 'male')->count(),
                'female_count' => $patients->where('gender', 'female')->count(),
                'with_emergency_contact' => $patients->whereNotNull('emergency_contact_name')->count(),
            ],
        ];
    }

    /**
     * Export appointments data
     */
    private function exportAppointments(?string $startDate = null, ?string $endDate = null): array
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
                __('Appointment ID'), __('Patient ID'), __('Patient Name'), __('Doctor Name'), __('Appointment Date'),
                __('Appointment Time'), __('Status'), __('Reason'), __('Notes'), __('Created Date'),
            ],
            'data' => $appointments->map(function ($appointment) {
                return [
                    $appointment->id,
                    $appointment->patient->id,
                    $appointment->patient->first_name.' '.$appointment->patient->last_name,
                    $appointment->doctor->name ?? __('Not assigned'),
                    $this->formatDate($appointment->appointment_date),
                    $appointment->displayTime(),
                    match ($appointment->status) {
                        'completed' => __('Completed'),
                        'scheduled' => __('Scheduled'),
                        'cancelled' => __('Cancelled'),
                        'in_progress' => __('In Progress'),
                        'no_show' => __('No Show'),
                        default => __(ucfirst(str_replace('_', ' ', $appointment->status)))
                    },
                    $appointment->reason ?? '',
                    $appointment->notes ?? '',
                    $appointment->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray(),
            'summary' => [
                'total_count' => $appointments->count(),
                'completed' => $appointments->where('status', 'completed')->count(),
                'scheduled' => $appointments->where('status', 'scheduled')->count(),
                'cancelled' => $appointments->where('status', 'cancelled')->count(),
                'in_progress' => $appointments->where('status', 'in_progress')->count(),
                'no_show' => $appointments->where('status', 'no_show')->count(),
            ],
        ];
    }

    /**
     * Export medical records data
     */
    private function exportMedicalRecords(?string $startDate = null, ?string $endDate = null): array
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
                __('Record ID'), __('Patient ID'), __('Patient Name'), __('Doctor Name'), __('Visit Date'),
                __('Symptoms'), __('Diagnosis'), __('Treatment'),
                'Weight (kg)',
                'Height (cm)',
                __('Blood Pressure'), __('Temperature'), __('Heart Rate'), __('Oxygen Saturation'),
                __('Specialty Details'), __('Notes'), __('Created Date'),
                __('medical_records.visit_type'), __('medical_records.history_present_illness'), __('medical_records.physical_examination'),
            ],
            'data' => $records->map(function ($record) {
                return [
                    $record->id,
                    $record->patient->id,
                    $record->patient->first_name.' '.$record->patient->last_name,
                    $record->doctor->name ?? __('Not specified'),
                    $this->formatDate($record->visit_date),
                    $record->symptoms ?? '',
                    $record->diagnosis ?? '',
                    $record->treatment ?? '',
                    $record->weight ?? '',
                    $record->height ?? '',
                    $record->blood_pressure ?? '',
                    $record->temperature ?? '',
                    $record->heart_rate ?? '',
                    $record->oxygen_saturation ?? '',
                    $this->specialties->formatData('record', $record->specialty_data),
                    $record->notes ?? '',
                    $record->created_at->format('Y-m-d H:i:s'),
                    $record->visit_type ? __('medical_records.visit_types.'.($record->visit_type === 'routine-checkup' ? 'routine' : $record->visit_type)) : '',
                    $record->history_present_illness ?? '',
                    $record->physical_examination ?? '',
                ];
            })->toArray(),
            'summary' => [
                'total_count' => $records->count(),
                'with_diagnosis' => $records->whereNotNull('diagnosis')->count(),
                'with_treatment' => $records->whereNotNull('treatment')->count(),
                'with_vitals' => $records->whereNotNull('weight')->whereNotNull('height')->count(),
            ],
        ];
    }

    /**
     * Export prescriptions data
     */
    private function exportPrescriptions(?string $startDate = null, ?string $endDate = null): array
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
                        $prescription->patient->id,
                        $prescription->patient->first_name.' '.$prescription->patient->last_name,
                        $prescription->doctor->name ?? 'Not specified',
                        $this->formatDate($prescription->prescribed_date),
                        $item->medication_name ?? '',
                        $item->dosage ?? '',
                        $item->frequency ?? '',
                        $item->duration_days ?? '',
                        $item->instructions ?? '',
                        $prescription->notes ?? '',
                        $prescription->created_at->format('Y-m-d H:i:s'),
                    ];
                }
            } else {
                // Single prescription (legacy)
                $prescriptionData[] = [
                    $prescription->id,
                    $prescription->patient->id,
                    $prescription->patient->first_name.' '.$prescription->patient->last_name,
                    $prescription->doctor->name ?? 'Not specified',
                    $this->formatDate($prescription->prescribed_date),
                    $prescription->medication_name ?? '',
                    $prescription->dosage ?? '',
                    $prescription->frequency ?? '',
                    $prescription->duration_days ?? '',
                    $prescription->instructions ?? '',
                    $prescription->notes ?? '',
                    $prescription->created_at->format('Y-m-d H:i:s'),
                ];
            }
        }

        return [
            'headers' => [
                __('Prescription ID'), __('Patient ID'), __('Patient Name'), __('Doctor Name'), __('Prescribed Date'),
                __('Medication Name'), __('Dosage'), __('Frequency'), __('Duration (Days)'), __('Instructions'), __('Notes'), __('Created Date'),
            ],
            'data' => $prescriptionData,
            'summary' => [
                'total_prescriptions' => $prescriptions->count(),
                'total_medication_items' => collect($prescriptionData)->count(),
                'unique_patients' => $prescriptions->unique('patient_id')->count(),
            ],
        ];
    }

    /**
     * Export users data
     */
    private function exportUsers(?string $startDate = null, ?string $endDate = null): array
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
                __('User ID'), __('Name'), __('Email'), __('Role'), __('Email Verified'), __('Created Date'),
            ],
            'data' => $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    __(ucfirst($user->role)),
                    $user->email_verified_at ? __('Yes') : __('No'),
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray(),
            'summary' => [
                'total_count' => $users->count(),
                'doctors' => $users->where('role', 'doctor')->count(),
                'receptionists' => $users->where('role', 'receptionist')->count(),
                'verified_users' => $users->whereNotNull('email_verified_at')->count(),
            ],
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
        fputcsv($output, [__('Clinic Data Export')]);
        fputcsv($output, [__('Generated at:'), $exportResult['info']['generated_at']->format('d/m/Y H:i:s')]);
        fputcsv($output, [__('Clinic specialty:'), $exportResult['info']['clinic_specialty']]);
        $rangeStart = $exportResult['info']['date_range']['start'];
        $rangeEnd = $exportResult['info']['date_range']['end'];
        if ($rangeStart || $rangeEnd) {
            fputcsv($output, [__('Date range:'), ($rangeStart ? $rangeStart->format('d/m/Y') : '...').' '.__('to').' '.($rangeEnd ? $rangeEnd->format('d/m/Y') : '...')]);
        }
        fputcsv($output, [__('Data types:'), implode(', ', array_map(fn ($type) => __(ucwords(str_replace('_', ' ', $type))), $exportResult['info']['data_types']))]);
        fputcsv($output, []);

        // Export each data type
        foreach ($exportResult['data'] as $dataType => $data) {
            fputcsv($output, [__(ucwords(str_replace('_', ' ', $dataType)))]);
            fputcsv($output, $data['headers']);

            foreach ($data['data'] as $row) {
                // CSV quoting does not prevent spreadsheet formula execution.
                fputcsv($output, array_map(function ($value) {
                    if (is_string($value) && preg_match('/^[\s\x00-\x1f]*[=+@-]/u', $value)) {
                        return "'".$value;
                    }

                    return $value;
                }, $row));
            }

            fputcsv($output, []);
            fputcsv($output, [__('Summary:')]);
            foreach ($data['summary'] as $key => $value) {
                $translatedKey = match ($key) {
                    'total_count' => __('Total'),
                    'male_count' => __('Male'),
                    'female_count' => __('Female'),
                    'with_emergency_contact' => __('With emergency contact'),
                    'completed' => __('Completed'),
                    'scheduled' => __('Scheduled'),
                    'cancelled' => __('Cancelled'),
                    'in_progress' => __('In Progress'),
                    'no_show' => __('No Show'),
                    'with_diagnosis' => __('With diagnosis'),
                    'with_treatment' => __('With treatment'),
                    'with_vitals' => __('With vital signs'),
                    'total_prescriptions' => __('Total prescriptions'),
                    'total_medication_items' => __('Total medication items'),
                    'unique_patients' => __('Unique patients'),
                    'pending' => __('Pending'),
                    'doctors' => __('Doctors'),
                    'receptionists' => __('Receptionists'),
                    'verified_users' => __('Verified users'),
                    default => __(ucfirst(str_replace('_', ' ', $key)))
                };
                fputcsv($output, [$translatedKey.':', $value]);
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
