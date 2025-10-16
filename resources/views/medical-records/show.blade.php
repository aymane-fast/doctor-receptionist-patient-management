@extends('layouts.app')

@section('title', __('medical_records.full_record_details'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Medical Record Header -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-file-medical text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('medical_records.full_record_details') }}</h1>
                    <p class="text-gray-600">Medical Record #{{ $medicalRecord->id }} • {{ \Carbon\Carbon::parse($medicalRecord->visit_date)->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('medical-records.index') }}" 
                   class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('common.back_to', ['item' => __('medical_records.title')]) }}</span>
                </a>
                <a href="{{ route('medical-records.edit', $medicalRecord) }}" 
                   class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-edit"></i>
                    <span>{{ __('common.edit') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Patient & Visit Info -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                    <h2 class="text-lg font-bold text-blue-800 flex items-center">
                        <div class="w-8 h-8 bg-blue-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-circle text-blue-700"></i>
                        </div>
                        {{ __('medical_records.patient_visit_info') }}
                    </h2>
                </div>
                <div class="p-6">
                
                <!-- Patient Info -->
                <div class="mb-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-4 mb-1">
                                <h3 class="text-xl font-bold text-gray-900">{{ $medicalRecord->patient->first_name }} {{ $medicalRecord->patient->last_name }}</h3>
                                <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded-full text-sm font-semibold">{{ $medicalRecord->patient->age }}y</span>
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm font-semibold">{{ ucfirst($medicalRecord->patient->gender) }}</span>
                            </div>
                            <p class="text-gray-600">{{ $medicalRecord->patient->phone }} • {{ $medicalRecord->patient->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Visit Details -->
                <div class="border-t pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="far fa-calendar text-gray-600 mr-2"></i>
                                <label class="text-sm font-medium text-gray-600">{{ __('medical_records.visit_date') }}</label>
                            </div>
                            <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($medicalRecord->visit_date)->format('M d, Y') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-user-md text-gray-600 mr-2"></i>
                                <label class="text-sm font-medium text-gray-600">{{ __('medical_records.doctor') }}</label>
                            </div>
                            <p class="font-bold text-gray-900">Dr. {{ $medicalRecord->doctor->name }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-stethoscope text-gray-600 mr-2"></i>
                                <label class="text-sm font-medium text-gray-600">{{ __('medical_records.visit_type') }}</label>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                @if($medicalRecord->visit_type == 'consultation') bg-blue-100 text-blue-800
                                @elseif($medicalRecord->visit_type == 'follow-up') bg-green-100 text-green-800
                                @elseif($medicalRecord->visit_type == 'emergency') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                @if($medicalRecord->visit_type == 'consultation') Consultation
                                @elseif($medicalRecord->visit_type == 'follow-up') Follow-up
                                @elseif($medicalRecord->visit_type == 'emergency') Emergency
                                @else {{ ucfirst($medicalRecord->visit_type) }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Vital Signs -->
            @if($medicalRecord->blood_pressure || $medicalRecord->heart_rate || $medicalRecord->temperature || $medicalRecord->weight || $medicalRecord->height || $medicalRecord->oxygen_saturation)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-red-50 to-red-100 px-6 py-4 border-b border-red-200">
                    <h2 class="text-lg font-bold text-red-800 flex items-center">
                        <div class="w-8 h-8 bg-red-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-heartbeat text-red-700"></i>
                        </div>
                        {{ __('medical_records.vital_signs') }}
                    </h2>
                </div>
                <div class="p-6">
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @if($medicalRecord->blood_pressure)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-heartbeat text-white text-sm"></i>
                        </div>
                        <p class="text-xs font-medium text-gray-600 mb-1">{{ __('medical_records.blood_pressure') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->blood_pressure }}</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->heart_rate)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <div class="w-8 h-8 bg-pink-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-heart text-white text-sm"></i>
                        </div>
                        <p class="text-xs font-medium text-gray-600 mb-1">{{ __('medical_records.heart_rate') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->heart_rate }}</p>
                        <p class="text-xs text-gray-500">{{ __('medical_records.bpm') }}</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->temperature)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-thermometer-half text-white text-sm"></i>
                        </div>
                        <p class="text-xs font-medium text-gray-600 mb-1">{{ __('medical_records.temperature') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->temperature }}°</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->weight)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-weight text-white text-sm"></i>
                        </div>
                        <p class="text-xs font-medium text-gray-600 mb-1">{{ __('medical_records.weight') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->weight }}</p>
                        <p class="text-xs text-gray-500">kg</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->height)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-ruler-vertical text-white text-sm"></i>
                        </div>
                        <p class="text-xs font-medium text-gray-600 mb-1">{{ __('medical_records.height') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->height }}</p>
                        <p class="text-xs text-gray-500">cm</p>
                    </div>
                    @endif
                    
                    @if($medicalRecord->oxygen_saturation)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-lungs text-white text-sm"></i>
                        </div>
                        <p class="text-xs font-medium text-gray-600 mb-1">{{ __('medical_records.oxygen_saturation') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $medicalRecord->oxygen_saturation }}%</p>
                    </div>
                    @endif
                </div>
                </div>
            </div>
            @endif

            <!-- Clinical Information -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-purple-200">
                    <h2 class="text-lg font-bold text-purple-800 flex items-center">
                        <div class="w-8 h-8 bg-purple-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-clipboard-list text-purple-700"></i>
                        </div>
                        {{ __('medical_records.clinical_information') }}
                    </h2>
                </div>
                <div class="p-6">
                
                <div class="space-y-6">
                    <!-- Chief Complaint -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-exclamation-circle text-red-600"></i>
                            </div>
                            <label class="font-bold text-gray-900">{{ __('medical_records.chief_complaint') }}</label>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->symptoms }}</p>
                        </div>
                    </div>

                    <!-- History of Present Illness -->
                    @if($medicalRecord->history_present_illness)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-history text-blue-600"></i>
                            </div>
                            <label class="font-bold text-gray-900">{{ __('medical_records.history_present_illness') }}</label>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->history_present_illness }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Physical Examination -->
                    @if($medicalRecord->physical_examination)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-search text-purple-600"></i>
                            </div>
                            <label class="font-bold text-gray-900">{{ __('medical_records.physical_examination') }}</label>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->physical_examination }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Diagnosis -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-diagnoses text-green-600"></i>
                            </div>
                            <label class="font-bold text-gray-900">{{ __('medical_records.assessment_diagnosis') }}</label>
                        </div>
                        <div class="bg-green-50 border-l-4 border-green-400 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap leading-relaxed font-medium">{{ $medicalRecord->diagnosis }}</p>
                        </div>
                    </div>

                    <!-- Treatment Plan -->
                    @if($medicalRecord->treatment_plan)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-prescription-bottle-alt text-cyan-600"></i>
                            </div>
                            <label class="font-bold text-gray-900">{{ __('medical_records.treatment_plan') }}</label>
                        </div>
                        <div class="bg-cyan-50 border-l-4 border-cyan-400 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->treatment_plan }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Additional Notes -->
                    @if($medicalRecord->notes)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-sticky-note text-yellow-600"></i>
                            </div>
                            <label class="font-bold text-gray-900">{{ __('medical_records.additional_notes') }}</label>
                        </div>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 px-6 py-4 border-b border-emerald-200">
                    <h3 class="text-lg font-bold text-emerald-800 flex items-center">
                        <div class="w-8 h-8 bg-emerald-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-bolt text-emerald-700"></i>
                        </div>
                        {{ __('medical_records.quick_actions') }}
                    </h3>
                </div>
                <div class="p-6">
                <div class="space-y-3">
                    <a href="{{ route('prescriptions.create', ['patient_id' => $medicalRecord->patient_id, 'medical_record_id' => $medicalRecord->id]) }}" 
                       class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-4 py-3 rounded-lg transition-all duration-200 block text-center font-medium shadow-lg hover:shadow-xl">
                        <i class="fas fa-prescription-bottle mr-2"></i>{{ __('prescriptions.add_prescription') }}
                    </a>
                    
                    <a href="{{ route('appointments.create', ['patient_id' => $medicalRecord->patient_id]) }}" 
                       class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-3 rounded-lg transition-all duration-200 block text-center font-medium shadow-lg hover:shadow-xl">
                        <i class="fas fa-calendar-plus mr-2"></i>{{ __('medical_records.schedule_followup') }}
                    </a>
                </div>
                </div>
            </div>

            <!-- Record Info -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-gray-700"></i>
                        </div>
                        {{ __('medical_records.record_information') }}
                    </h3>
                </div>
                <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ __('medical_records.record_id') }}:</span>
                        <span class="text-gray-900 font-bold">#{{ $medicalRecord->id }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Created:</span>
                        <span class="text-gray-900 font-bold text-sm">{{ $medicalRecord->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Updated:</span>
                        <span class="text-gray-900 font-bold text-sm">{{ $medicalRecord->updated_at->format('M d, Y') }}</span>
                    </div>
                    @if($medicalRecord->appointment)
                    <div class="pt-3 border-t">
                        <a href="{{ route('appointments.show', $medicalRecord->appointment) }}" 
                           class="flex items-center justify-center bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-3 rounded-lg transition-colors font-medium">
                            <i class="fas fa-calendar mr-2"></i>{{ __('medical_records.view_related_appointment') }}
                        </a>
                    </div>
                    @endif
                </div>
                </div>
            </div>

            <!-- Related Records -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                    <h3 class="text-lg font-bold text-blue-800 flex items-center">
                        <div class="w-8 h-8 bg-blue-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-link text-blue-700"></i>
                        </div>
                        Related Records
                    </h3>
                </div>
                <div class="p-6">
                <div class="space-y-3">
                    <a href="{{ route('medical-records.index', ['patient_id' => $medicalRecord->patient_id]) }}" 
                       class="flex items-center bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-3 rounded-lg transition-colors font-medium">
                        <i class="fas fa-file-medical mr-3"></i>Patient's Medical History
                    </a>
                    <a href="{{ route('prescriptions.index', ['patient_id' => $medicalRecord->patient_id]) }}" 
                       class="flex items-center bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-4 py-3 rounded-lg transition-colors font-medium">
                        <i class="fas fa-prescription-bottle mr-3"></i>Patient's Prescriptions
                    </a>
                    <a href="{{ route('appointments.index', ['patient_id' => $medicalRecord->patient_id]) }}" 
                       class="flex items-center bg-purple-50 hover:bg-purple-100 text-purple-700 px-4 py-3 rounded-lg transition-colors font-medium">
                        <i class="fas fa-calendar mr-3"></i>Patient's Appointments
                    </a>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
