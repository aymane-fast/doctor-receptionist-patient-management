@extends('layouts.app')

@section('title', __('medical_records.full_record_details'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Enhanced Header -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-file-medical text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ __('medical_records.full_record_details') }}</h1>
                        <p class="text-gray-600 mt-1">Dossier médical complet • ID #{{ $medicalRecord->id }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('medical-records.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl transition-all duration-200 font-medium flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>{{ __('common.back_to', ['item' => __('medical_records.title')]) }}
                    </a>
                    <a href="{{ route('medical-records.edit', $medicalRecord) }}" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-6 py-3 rounded-xl transition-all duration-200 font-medium flex items-center shadow-lg">
                        <i class="fas fa-edit mr-2"></i>{{ __('common.edit') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Patient & Visit Info -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-8 py-6">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-user-circle mr-3"></i>
                            {{ __('medical_records.patient_visit_info') }}
                        </h2>
                    </div>
                    
                    <div class="p-8">
                        <!-- Patient Info -->
                        <div class="mb-6">
                            <div class="flex items-center mb-4">
                                <div class="w-14 h-14 bg-blue-500 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-user text-white text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-4 mb-1">
                                        <h3 class="text-xl font-bold text-gray-900">{{ $medicalRecord->patient->first_name }} {{ $medicalRecord->patient->last_name }}</h3>
                                        <span class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-birthday-cake mr-1"></i>{{ $medicalRecord->patient->age }} ans
                                        </span>
                                        <span class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-{{ $medicalRecord->patient->gender == 'male' ? 'mars' : 'venus' }} mr-1"></i>{{ __('patients.genders.' . $medicalRecord->patient->gender) }}
                                        </span>
                                    </div>
                                    <p class="text-gray-600">{{ $medicalRecord->patient->phone }} • {{ $medicalRecord->patient->email }}</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-red-50 rounded-lg p-3 text-center">
                                    <div class="text-red-600 text-xs font-medium mb-1">{{ __('patients.blood_type') }}</div>
                                    <div class="text-sm font-bold text-red-900">{{ $medicalRecord->patient->blood_type ?: __('common.not_specified') }}</div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-3 text-center">
                                    <div class="text-green-600 text-xs font-medium mb-1">{{ __('patients.emergency_contact') }}</div>
                                    <div class="text-xs font-bold text-green-900">{{ $medicalRecord->patient->emergency_contact ?: __('common.not_specified') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Visit Details -->
                        <div class="border-t pt-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <div class="flex items-center mb-3">
                                        <i class="far fa-calendar text-gray-600 mr-2"></i>
                                        <label class="text-sm font-medium text-gray-600">{{ __('medical_records.visit_date') }}</label>
                                    </div>
                                    <p class="text-xl font-bold text-gray-900">{{ \Carbon\Carbon::parse($medicalRecord->visit_date)->locale(app()->getLocale())->isoFormat('dddd, D MMMM YYYY') }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <div class="flex items-center mb-3">
                                        <i class="fas fa-user-md text-gray-600 mr-2"></i>
                                        <label class="text-sm font-medium text-gray-600">{{ __('medical_records.doctor') }}</label>
                                    </div>
                                    <p class="text-xl font-bold text-gray-900">{{ $medicalRecord->doctor->name }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <div class="flex items-center mb-3">
                                        <i class="fas fa-stethoscope text-gray-600 mr-2"></i>
                                        <label class="text-sm font-medium text-gray-600">{{ __('medical_records.visit_type') }}</label>
                                    </div>
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold
                                        @if($medicalRecord->visit_type == 'consultation') bg-blue-200 text-blue-900
                                        @elseif($medicalRecord->visit_type == 'follow-up') bg-green-200 text-green-900
                                        @elseif($medicalRecord->visit_type == 'emergency') bg-red-200 text-red-900
                                        @else bg-gray-200 text-gray-900
                                        @endif">
                                        @if($medicalRecord->visit_type == 'consultation') Consultation
                                        @elseif($medicalRecord->visit_type == 'follow-up') Suivi
                                        @elseif($medicalRecord->visit_type == 'emergency') Urgence
                                        @else {{ $medicalRecord->visit_type }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vital Signs -->
                @if($medicalRecord->blood_pressure || $medicalRecord->heart_rate || $medicalRecord->temperature || $medicalRecord->weight || $medicalRecord->height || $medicalRecord->oxygen_saturation)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 px-8 py-6">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-heartbeat mr-3"></i>
                            {{ __('medical_records.vital_signs') }}
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            @if($medicalRecord->blood_pressure)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-heartbeat text-white text-sm"></i>
                                </div>
                                <p class="text-xs font-medium text-red-700 mb-1">{{ __('medical_records.blood_pressure') }}</p>
                                <p class="text-lg font-bold text-red-900">{{ $medicalRecord->blood_pressure }}</p>
                                <div class="mt-1 text-xs text-red-600 bg-red-200 rounded px-2 py-1">
                                    @if(strpos($medicalRecord->blood_pressure, '/') !== false)
                                        @php
                                            $bp = explode('/', $medicalRecord->blood_pressure);
                                            $systolic = intval($bp[0]);
                                        @endphp
                                        @if($systolic < 120) Normal
                                        @elseif($systolic < 140) Élevée
                                        @else Forte
                                        @endif
                                    @else
                                        Aufherrhar
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if($medicalRecord->heart_rate)
                            <div class="bg-pink-50 border border-pink-200 rounded-lg p-4 text-center">
                                <div class="w-8 h-8 bg-pink-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-heart text-white text-sm"></i>
                                </div>
                                <p class="text-xs font-medium text-pink-700 mb-1">{{ __('medical_records.heart_rate') }}</p>
                                <p class="text-lg font-bold text-pink-900">{{ $medicalRecord->heart_rate }}</p>
                                <p class="text-xs text-pink-600">{{ __('medical_records.bpm') }}</p>
                            </div>
                            @endif
                            
                            @if($medicalRecord->temperature)
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-thermometer-half text-white text-sm"></i>
                                </div>
                                <p class="text-xs font-medium text-orange-700 mb-1">{{ __('medical_records.temperature') }}</p>
                                <p class="text-lg font-bold text-orange-900">{{ $medicalRecord->temperature }}°F</p>
                                <div class="mt-1 text-xs text-orange-600 bg-orange-200 rounded px-2 py-1">
                                    @if($medicalRecord->temperature >= 100.4) Fièvre
                                    @elseif($medicalRecord->temperature >= 99) Légèrement élevée
                                    @else Normale
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if($medicalRecord->weight)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-weight text-white text-sm"></i>
                                </div>
                                <p class="text-xs font-medium text-blue-700 mb-1">{{ __('medical_records.weight') }}</p>
                                <p class="text-lg font-bold text-blue-900">{{ $medicalRecord->weight }}</p>
                                <p class="text-xs text-blue-600">kg</p>
                            </div>
                            @endif
                            
                            @if($medicalRecord->height)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-ruler-vertical text-white text-sm"></i>
                                </div>
                                <p class="text-xs font-medium text-green-700 mb-1">{{ __('medical_records.height') }}</p>
                                <p class="text-lg font-bold text-green-900">{{ $medicalRecord->height }}</p>
                                <p class="text-xs text-green-600">cm</p>
                            </div>
                            @endif
                            
                            @if($medicalRecord->oxygen_saturation)
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-lungs text-white text-sm"></i>
                                </div>
                                <p class="text-xs font-medium text-purple-700 mb-1">{{ __('medical_records.oxygen_saturation') }}</p>
                                <p class="text-lg font-bold text-purple-900">{{ $medicalRecord->oxygen_saturation }}%</p>
                                <div class="mt-1 text-xs text-purple-600 bg-purple-200 rounded px-2 py-1">
                                    @if($medicalRecord->oxygen_saturation >= 95) Normal
                                    @elseif($medicalRecord->oxygen_saturation >= 90) Faible
                                    @else Critique
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Clinical Information -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-8 py-6">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-clipboard-list mr-3"></i>
                            {{ __('medical_records.clinical_information') }}
                        </h2>
                    </div>
                    
                    <div class="p-8 space-y-8">
                        <!-- Chief Complaint -->
                        <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-2xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-exclamation-circle text-white"></i>
                                </div>
                                <label class="text-lg font-bold text-red-800">{{ __('medical_records.chief_complaint') }}</label>
                            </div>
                            <div class="bg-white rounded-xl p-4 shadow-sm">
                                <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->chief_complaint }}</p>
                            </div>
                        </div>

                        <!-- History of Present Illness -->
                        @if($medicalRecord->history_present_illness)
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-history text-white"></i>
                                </div>
                                <label class="text-lg font-bold text-blue-800">{{ __('medical_records.history_present_illness') }}</label>
                            </div>
                            <div class="bg-white rounded-xl p-4 shadow-sm">
                                <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->history_present_illness }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Physical Examination -->
                        @if($medicalRecord->physical_examination)
                        <div class="bg-gradient-to-r from-purple-50 to-violet-50 border border-purple-200 rounded-2xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-search text-white"></i>
                                </div>
                                <label class="text-lg font-bold text-purple-800">{{ __('medical_records.physical_examination') }}</label>
                            </div>
                            <div class="bg-white rounded-xl p-4 shadow-sm">
                                <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->physical_examination }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Diagnosis -->
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-diagnoses text-white"></i>
                                </div>
                                <label class="text-lg font-bold text-green-800">{{ __('medical_records.assessment_diagnosis') }}</label>
                            </div>
                            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
                                <p class="text-gray-900 whitespace-pre-wrap leading-relaxed font-medium">{{ $medicalRecord->diagnosis }}</p>
                            </div>
                        </div>

                        <!-- Treatment Plan -->
                        @if($medicalRecord->treatment_plan)
                        <div class="bg-gradient-to-r from-cyan-50 to-blue-50 border border-cyan-200 rounded-2xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-cyan-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-prescription-bottle-alt text-white"></i>
                                </div>
                                <label class="text-lg font-bold text-cyan-800">{{ __('medical_records.treatment_plan') }}</label>
                            </div>
                            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-cyan-400">
                                <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->treatment_plan }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Additional Notes -->
                        @if($medicalRecord->notes)
                        <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-2xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-sticky-note text-white"></i>
                                </div>
                                <label class="text-lg font-bold text-yellow-800">{{ __('medical_records.additional_notes') }}</label>
                            </div>
                            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-400">
                                <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">{{ $medicalRecord->notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-teal-500 px-6 py-4">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-bolt mr-2"></i>
                            {{ __('medical_records.quick_actions') }}
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <a href="{{ route('prescriptions.create', ['patient_id' => $medicalRecord->patient_id, 'medical_record_id' => $medicalRecord->id]) }}" 
                           class="group w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-4 rounded-xl transition-all duration-200 block text-center font-medium shadow-lg transform hover:scale-105">
                            <i class="fas fa-prescription-bottle mr-2 group-hover:scale-110 transition-transform"></i>{{ __('prescriptions.add_prescription') }}
                        </a>
                        
                        <a href="{{ route('appointments.create', ['patient_id' => $medicalRecord->patient_id]) }}" 
                           class="group w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-4 rounded-xl transition-all duration-200 block text-center font-medium shadow-lg transform hover:scale-105">
                            <i class="fas fa-calendar-plus mr-2 group-hover:scale-110 transition-transform"></i>{{ __('medical_records.schedule_followup') }}
                        </a>
                    </div>
                </div>

                <!-- Record Info -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 px-6 py-4">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            {{ __('medical_records.record_information') }}
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-600 font-medium">{{ __('medical_records.record_id') }}:</span>
                                <span class="text-gray-900 font-bold">#{{ $medicalRecord->id }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-600 font-medium">Créé le:</span>
                                <span class="text-gray-900 font-bold text-sm">{{ $medicalRecord->created_at->locale(app()->getLocale())->isoFormat('D MMM YYYY [à] HH:mm') }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-600 font-medium">Mis à jour:</span>
                                <span class="text-gray-900 font-bold text-sm">{{ $medicalRecord->updated_at->locale(app()->getLocale())->isoFormat('D MMM YYYY [à] HH:mm') }}</span>
                            </div>
                            @if($medicalRecord->appointment)
                            <div class="pt-3 border-t">
                                <a href="{{ route('appointments.show', $medicalRecord->appointment) }}" 
                                   class="flex items-center justify-center bg-blue-50 hover:bg-blue-100 text-blue-700 hover:text-blue-800 px-4 py-3 rounded-xl transition-all duration-200 font-medium">
                                    <i class="fas fa-calendar mr-2"></i>{{ __('medical_records.view_related_appointment') }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Related Records -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-pink-500 to-rose-500 px-6 py-4">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-link mr-2"></i>
                            Related Records
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('medical-records.index', ['patient_id' => $medicalRecord->patient_id]) }}" 
                           class="group flex items-center bg-blue-50 hover:bg-blue-100 text-blue-700 hover:text-blue-800 px-4 py-3 rounded-xl transition-all duration-200 font-medium">
                            <i class="fas fa-file-medical mr-3 group-hover:scale-110 transition-transform"></i>Patient's Medical History
                        </a>
                        <a href="{{ route('prescriptions.index', ['patient_id' => $medicalRecord->patient_id]) }}" 
                           class="group flex items-center bg-green-50 hover:bg-green-100 text-green-700 hover:text-green-800 px-4 py-3 rounded-xl transition-all duration-200 font-medium">
                            <i class="fas fa-prescription-bottle mr-3 group-hover:scale-110 transition-transform"></i>Patient's Prescriptions
                        </a>
                        <a href="{{ route('appointments.index', ['patient_id' => $medicalRecord->patient_id]) }}" 
                           class="group flex items-center bg-purple-50 hover:bg-purple-100 text-purple-700 hover:text-purple-800 px-4 py-3 rounded-xl transition-all duration-200 font-medium">
                            <i class="fas fa-calendar mr-3 group-hover:scale-110 transition-transform"></i>Patient's Appointments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
