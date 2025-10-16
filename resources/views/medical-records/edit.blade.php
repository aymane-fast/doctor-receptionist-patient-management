@extends('layouts.app')

@section('title', __('medical_records.edit_record'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('medical_records.edit_record') }}</h1>
            <div class="flex space-x-3">
                <a href="{{ route('medical-records.show', $medicalRecord) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('common.back_to_details') }}
                </a>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('medical-records.update', $medicalRecord) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Patient and Basic Info -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('medical_records.patient_information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Patient Selection -->
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.patient_name') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="patient_id" name="patient_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('patients.select_patient') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" 
                                            {{ (old('patient_id', $medicalRecord->patient_id) == $patient->id) ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Visit Date -->
                        <div>
                            <label for="visit_date" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.visit_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="visit_date" name="visit_date" 
                                   value="{{ old('visit_date', $medicalRecord->visit_date) }}" required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('visit_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Visit Type -->
                        <div>
                            <label for="visit_type" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.visit_type') }}
                            </label>
                            <select id="visit_type" name="visit_type" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="consultation" {{ old('visit_type', $medicalRecord->visit_type) == 'consultation' ? 'selected' : '' }}>{{ __('medical_records.visit_types.consultation') }}</option>
                                <option value="follow-up" {{ old('visit_type', $medicalRecord->visit_type) == 'follow-up' ? 'selected' : '' }}>{{ __('medical_records.visit_types.follow-up') }}</option>
                                <option value="emergency" {{ old('visit_type', $medicalRecord->visit_type) == 'emergency' ? 'selected' : '' }}>{{ __('medical_records.visit_types.emergency') }}</option>
                                <option value="routine-checkup" {{ old('visit_type', $medicalRecord->visit_type) == 'routine-checkup' ? 'selected' : '' }}>{{ __('medical_records.visit_types.routine') }}</option>
                            </select>
                            @error('visit_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Appointment Link -->
                        <div>
                            <label for="appointment_id" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.related_appointment') }}
                            </label>
                            <select id="appointment_id" name="appointment_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('medical_records.no_appointment') }}</option>
                                @foreach($appointments as $appointment)
                                    <option value="{{ $appointment->id }}" 
                                            {{ old('appointment_id', $medicalRecord->appointment_id) == $appointment->id ? 'selected' : '' }}>
                                        {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }} - 
                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M j, Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Vital Signs -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('medical_records.vital_signs') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="blood_pressure" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.blood_pressure') }}
                            </label>
                            <input type="text" id="blood_pressure" name="blood_pressure" 
                                   value="{{ old('blood_pressure', $medicalRecord->blood_pressure) }}" 
                                   placeholder="120/80"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('blood_pressure')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="heart_rate" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.heart_rate') }} ({{ __('medical_records.bpm') }})
                            </label>
                            <input type="number" id="heart_rate" name="heart_rate" 
                                   value="{{ old('heart_rate', $medicalRecord->heart_rate) }}" 
                                   min="0" max="300"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('heart_rate')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="temperature" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.temperature') }} (°F)
                            </label>
                            <input type="number" id="temperature" name="temperature" 
                                   value="{{ old('temperature', $medicalRecord->temperature) }}" 
                                   step="0.1" min="90" max="110"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('temperature')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.weight') }} (kg)
                            </label>
                            <input type="number" id="weight" name="weight" 
                                   value="{{ old('weight', $medicalRecord->weight) }}" 
                                   step="0.1" min="0" max="500"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('weight')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="height" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.height') }} (cm)
                            </label>
                            <input type="number" id="height" name="height" 
                                   value="{{ old('height', $medicalRecord->height) }}" 
                                   min="0" max="300"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('height')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="oxygen_saturation" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('medical_records.oxygen_saturation') }} (%)
                            </label>
                            <input type="number" id="oxygen_saturation" name="oxygen_saturation" 
                                   value="{{ old('oxygen_saturation', $medicalRecord->oxygen_saturation) }}" 
                                   min="0" max="100"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('oxygen_saturation')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Clinical Information -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('medical_records.clinical_information') }}</h2>
                    
                    <!-- Symptoms -->
                    <div class="mb-6">
                        <label for="symptoms" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('medical_records.chief_complaint') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea id="symptoms" name="symptoms" rows="3" required
                                  placeholder="{{ __('medical_records.primary_reason_visit') }}"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('symptoms', $medicalRecord->symptoms) }}</textarea>
                        @error('symptoms')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- History of Present Illness -->
                    <div class="mb-6">
                        <label for="history_present_illness" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('medical_records.history_present_illness') }}
                        </label>
                        <textarea id="history_present_illness" name="history_present_illness" rows="4"
                                  placeholder="{{ __('medical_records.detailed_description_illness') }}"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('history_present_illness', $medicalRecord->history_present_illness) }}</textarea>
                        @error('history_present_illness')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Physical Examination -->
                    <div class="mb-6">
                        <label for="physical_examination" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('medical_records.physical_examination') }}
                        </label>
                        <textarea id="physical_examination" name="physical_examination" rows="4"
                                  placeholder="{{ __('medical_records.physical_exam_findings') }}"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('physical_examination', $medicalRecord->physical_examination) }}</textarea>
                        @error('physical_examination')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assessment/Diagnosis -->
                    <div class="mb-6">
                        <label for="diagnosis" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('medical_records.assessment_diagnosis') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea id="diagnosis" name="diagnosis" rows="3" required
                                  placeholder="{{ __('medical_records.medical_diagnosis_placeholder') }}"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('diagnosis', $medicalRecord->diagnosis) }}</textarea>
                        @error('diagnosis')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Treatment Plan -->
                    <div class="mb-6">
                        <label for="treatment_plan" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('medical_records.treatment_plan') }}
                        </label>
                        <textarea id="treatment_plan" name="treatment_plan" rows="4"
                                  placeholder="{{ __('medical_records.treatment_recommendations') }}"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('treatment_plan', $medicalRecord->treatment_plan) }}</textarea>
                        @error('treatment_plan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Additional Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('medical_records.additional_notes') }}
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="{{ __('medical_records.additional_observations') }}"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes', $medicalRecord->notes) }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                    <div>
                        <form action="{{ route('medical-records.destroy', $medicalRecord) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors"
                                    onclick="return confirm('{{ __('medical_records.confirm_delete_record') }}')">
                                <i class="fas fa-trash mr-2"></i>{{ __('common.delete') }}
                            </button>
                        </form>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('medical-records.show', $medicalRecord) }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>{{ __('medical_records.update_record') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
