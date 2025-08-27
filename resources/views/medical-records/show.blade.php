@extends('layouts.app')

@section('title', __('medical_records.full_record_details'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('medical_records.full_record_details') }}</h1>
        <div class="flex space-x-3">
            <a href="{{ route('medical-records.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('common.back_to', ['item' => __('medical_records.title')]) }}
            </a>
            <a href="{{ route('medical-records.edit', $medicalRecord) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-edit mr-2"></i>{{ __('common.edit') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Patient & Visit Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('medical_records.patient_visit_info') }}</h2>
                
                <!-- Patient Info -->
                <div class="border-b pb-4 mb-4">
                    <div class="flex items-center mb-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">{{ $medicalRecord->patient->first_name }} {{ $medicalRecord->patient->last_name }}</h3>
                            <p class="text-gray-600">{{ $medicalRecord->patient->phone }} • {{ $medicalRecord->patient->email }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <label class="block text-gray-500">{{ __('medical_records.age') }}</label>
                            <p class="font-semibold">{{ $medicalRecord->patient->age }} {{ __('medical_records.years') }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-500">{{ __('patients.gender') }}</label>
                            <p class="font-semibold">{{ __('patients.genders.' . $medicalRecord->patient->gender) }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-500">{{ __('patients.blood_type') }}</label>
                            <p class="font-semibold">{{ $medicalRecord->patient->blood_type ?: __('common.not_specified') }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-500">{{ __('patients.emergency_contact') }}</label>
                            <p class="font-semibold">{{ $medicalRecord->patient->emergency_contact ?: __('common.not_specified') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Visit Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('medical_records.visit_date') }}</label>
                        <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($medicalRecord->visit_date)->format('l, F j, Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('medical_records.doctor') }}</label>
                        <p class="text-lg font-semibold text-gray-900">Dr. {{ $medicalRecord->doctor->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('medical_records.visit_type') }}</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($medicalRecord->visit_type == 'consultation') bg-blue-100 text-blue-800
                            @elseif($medicalRecord->visit_type == 'follow-up') bg-green-100 text-green-800
                            @elseif($medicalRecord->visit_type == 'emergency') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ __('medical_records.visit_types.' . $medicalRecord->visit_type) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Vital Signs -->
            @if($medicalRecord->blood_pressure || $medicalRecord->heart_rate || $medicalRecord->temperature || $medicalRecord->weight || $medicalRecord->height || $medicalRecord->oxygen_saturation)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('medical_records.vital_signs') }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @if($medicalRecord->blood_pressure)
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <i class="fas fa-heartbeat text-red-600 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-600">{{ __('medical_records.blood_pressure') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->blood_pressure }}</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->heart_rate)
                    <div class="text-center p-4 bg-pink-50 rounded-lg">
                        <i class="fas fa-heart text-pink-600 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-600">{{ __('medical_records.heart_rate') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->heart_rate }} {{ __('medical_records.bpm') }}</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->temperature)
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <i class="fas fa-thermometer-half text-orange-600 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-600">{{ __('medical_records.temperature') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->temperature }}°F</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->weight)
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <i class="fas fa-weight text-blue-600 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-600">{{ __('medical_records.weight') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->weight }} kg</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->height)
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <i class="fas fa-ruler-vertical text-green-600 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-600">{{ __('medical_records.height') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->height }} cm</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->oxygen_saturation)
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <i class="fas fa-lungs text-purple-600 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-600">{{ __('medical_records.oxygen_saturation') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->oxygen_saturation }}%</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Clinical Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('medical_records.clinical_information') }}</h2>
                
                <div class="space-y-6">
                    <!-- Chief Complaint -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ __('medical_records.chief_complaint') }}</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $medicalRecord->chief_complaint }}</p>
                        </div>
                    </div>

                    <!-- History of Present Illness -->
                    @if($medicalRecord->history_present_illness)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ __('medical_records.history_present_illness') }}</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $medicalRecord->history_present_illness }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Physical Examination -->
                    @if($medicalRecord->physical_examination)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ __('medical_records.physical_examination') }}</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $medicalRecord->physical_examination }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Diagnosis -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ __('medical_records.assessment_diagnosis') }}</label>
                        <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-400">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $medicalRecord->diagnosis }}</p>
                        </div>
                    </div>

                    <!-- Treatment Plan -->
                    @if($medicalRecord->treatment_plan)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ __('medical_records.treatment_plan') }}</label>
                        <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-400">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $medicalRecord->treatment_plan }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Additional Notes -->
                    @if($medicalRecord->notes)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ __('medical_records.additional_notes') }}</label>
                        <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-400">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $medicalRecord->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('medical_records.quick_actions') }}</h3>
                <div class="space-y-3">
                    <a href="{{ route('prescriptions.create', ['patient_id' => $medicalRecord->patient_id, 'medical_record_id' => $medicalRecord->id]) }}" 
                       class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors block text-center">
                        <i class="fas fa-prescription-bottle mr-2"></i>{{ __('prescriptions.add_prescription') }}
                    </a>
                    
                    <a href="{{ route('appointments.create', ['patient_id' => $medicalRecord->patient_id]) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors block text-center">
                        <i class="fas fa-calendar-plus mr-2"></i>{{ __('medical_records.schedule_followup') }}
                    </a>
                </div>
            </div>

            <!-- Record Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('medical_records.record_information') }}</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('medical_records.record_id') }}:</span>
                        <span class="text-gray-900">#{{ $medicalRecord->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('common.created') }}:</span>
                        <span class="text-gray-900">{{ $medicalRecord->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('common.last_updated') }}:</span>
                        <span class="text-gray-900">{{ $medicalRecord->updated_at->format('M j, Y g:i A') }}</span>
                    </div>
                    @if($medicalRecord->appointment)
                    <div class="pt-3 border-t">
                        <a href="{{ route('appointments.show', $medicalRecord->appointment) }}" 
                           class="text-blue-600 hover:text-blue-800 transition-colors">
                            <i class="fas fa-calendar mr-1"></i>{{ __('medical_records.view_related_appointment') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Related Records -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Records</h3>
                <div class="space-y-2">
                    <a href="{{ route('medical-records.index', ['patient_id' => $medicalRecord->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-file-medical mr-2"></i>Patient's Medical History
                    </a>
                    <a href="{{ route('prescriptions.index', ['patient_id' => $medicalRecord->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-prescription-bottle mr-2"></i>Patient's Prescriptions
                    </a>
                    <a href="{{ route('appointments.index', ['patient_id' => $medicalRecord->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-calendar mr-2"></i>Patient's Appointments
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
