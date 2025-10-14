@extends('layouts.app')

@section('title', __('medical_records.add_record'))

@section('content')
<div class="space-y-6">
    <!-- Modern Header -->
    <div class="glass-effect rounded-2xl p-6 modern-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-medical-alt text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('medical_records.add_record') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-1">{{ __('medical_records.document_visit') }}</p>
                </div>
            </div>
            <a href="{{ route('medical-records.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left text-sm"></i>
                <span>{{ __('common.back_to', ['item' => __('medical_records.title')]) }}</span>
            </a>
        </div>
    </div>

        <!-- Medical Record Form -->
        <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
            <form action="{{ route('medical-records.store') }}" method="POST" class="space-y-6">
                @csrf
                @if($appointment)
                    <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                @endif
                
                <!-- Patient and Basic Info -->
                <div class="p-6 pb-0">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('medical_records.patient_visit_info') }}</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-4">
                        <!-- Patient Selection -->
                        <div>
                            <label for="patient_id" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('medical_records.patient_name') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="patient_id" name="patient_id" required 
                                    class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('patient_id') border-red-500 @enderror">
                                <option value="">{{ __('patients.select_patient') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" 
                                            {{ old('patient_id', $appointment ? $appointment->patient_id : request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Visit Date -->
                        <div>
                            <label for="visit_date" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('medical_records.visit_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="visit_date" name="visit_date" 
                                   value="{{ old('visit_date', date('Y-m-d')) }}" required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('visit_date') border-red-500 @enderror">
                            @error('visit_date')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Visit Type -->
                        <div>
                            <label for="visit_type" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('medical_records.visit_type') }}
                            </label>
                            <select id="visit_type" name="visit_type" 
                                    class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('visit_type') border-red-500 @enderror">
                                <option value="consultation" {{ old('visit_type') == 'consultation' ? 'selected' : '' }}>{{ __('medical_records.visit_types.consultation') }}</option>
                                <option value="follow-up" {{ old('visit_type') == 'follow-up' ? 'selected' : '' }}>{{ __('medical_records.visit_types.follow-up') }}</option>
                                <option value="emergency" {{ old('visit_type') == 'emergency' ? 'selected' : '' }}>{{ __('medical_records.visit_types.emergency') }}</option>
                                <option value="routine-checkup" {{ old('visit_type') == 'routine-checkup' ? 'selected' : '' }}>{{ __('medical_records.visit_types.routine') }}</option>
                            </select>
                            @error('visit_type')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Hidden appointment field -->
                    @if(request('appointment_id'))
                    <input type="hidden" name="appointment_id" value="{{ request('appointment_id') }}">
                    @endif
                </div>

                <div class="border-t border-gray-200"></div>

                <!-- Clinical Information -->
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-stethoscope text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('medical_records.clinical_assessment') }}</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                        <!-- Chief Complaint -->
                        <div>
                            <label for="chief_complaint" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('medical_records.chief_complaint') }} <span class="text-red-500">*</span>
                            </label>
                            <textarea id="chief_complaint" name="chief_complaint" rows="3" required
                                      placeholder="{{ __('medical_records.primary_reason_visit') }}"
                                      class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('chief_complaint') border-red-500 @enderror">{{ old('chief_complaint') }}</textarea>
                            @error('chief_complaint')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Assessment/Diagnosis -->
                        <div>
                            <label for="diagnosis" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('medical_records.assessment_diagnosis') }} <span class="text-red-500">*</span>
                            </label>
                            <textarea id="diagnosis" name="diagnosis" rows="3" required
                                      placeholder="{{ __('medical_records.medical_diagnosis_placeholder') }}"
                                      class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('diagnosis') border-red-500 @enderror">{{ old('diagnosis') }}</textarea>
                            @error('diagnosis')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Vital Signs Section -->
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                        <div class="flex items-center space-x-2 mb-3">
                            <i class="fas fa-heartbeat text-blue-600"></i>
                            <h4 class="font-semibold text-blue-800">{{ __('medical_records.vital_signs') }} ({{ __('common.optional') }})</h4>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                            <div>
                                <label for="blood_pressure" class="block text-xs font-medium text-blue-700 mb-1">
                                    {{ __('medical_records.blood_pressure') }}
                                </label>
                                <input type="text" id="blood_pressure" name="blood_pressure" 
                                       value="{{ old('blood_pressure') }}" 
                                       placeholder="120/80"
                                       class="w-full px-2 py-1 text-sm border border-blue-200 rounded-lg bg-white/80 focus:outline-none focus:border-blue-400 transition-colors">
                            </div>

                            <div>
                                <label for="heart_rate" class="block text-xs font-medium text-blue-700 mb-1">
                                    {{ __('medical_records.heart_rate') }}
                                </label>
                                <input type="number" id="heart_rate" name="heart_rate" 
                                       value="{{ old('heart_rate') }}" 
                                       min="0" max="300" placeholder="72"
                                       class="w-full px-2 py-1 text-sm border border-blue-200 rounded-lg bg-white/80 focus:outline-none focus:border-blue-400 transition-colors">
                            </div>

                            <div>
                                <label for="temperature" class="block text-xs font-medium text-blue-700 mb-1">
                                    {{ __('medical_records.temperature') }} (°F)
                                </label>
                                <input type="number" id="temperature" name="temperature" 
                                       value="{{ old('temperature') }}" 
                                       step="0.1" min="90" max="110" placeholder="98.6"
                                       class="w-full px-2 py-1 text-sm border border-blue-200 rounded-lg bg-white/80 focus:outline-none focus:border-blue-400 transition-colors">
                            </div>

                            <div>
                                <label for="weight" class="block text-xs font-medium text-blue-700 mb-1">
                                    {{ __('medical_records.weight') }} (kg)
                                </label>
                                <input type="number" id="weight" name="weight" 
                                       value="{{ old('weight') }}" 
                                       step="0.1" min="0" max="500" placeholder="70"
                                       class="w-full px-2 py-1 text-sm border border-blue-200 rounded-lg bg-white/80 focus:outline-none focus:border-blue-400 transition-colors">
                            </div>

                            <div>
                                <label for="height" class="block text-xs font-medium text-blue-700 mb-1">
                                    {{ __('medical_records.height') }} (cm)
                                </label>
                                <input type="number" id="height" name="height" 
                                       value="{{ old('height') }}" 
                                       min="0" max="300" placeholder="170"
                                       class="w-full px-2 py-1 text-sm border border-blue-200 rounded-lg bg-white/80 focus:outline-none focus:border-blue-400 transition-colors">
                            </div>

                            <div>
                                <label for="oxygen_saturation" class="block text-xs font-medium text-blue-700 mb-1">
                                    {{ __('medical_records.oxygen_saturation') }} (%)
                                </label>
                                <input type="number" id="oxygen_saturation" name="oxygen_saturation" 
                                       value="{{ old('oxygen_saturation') }}" 
                                       min="0" max="100" placeholder="98"
                                       class="w-full px-2 py-1 text-sm border border-blue-200 rounded-lg bg-white/80 focus:outline-none focus:border-blue-400 transition-colors">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-b-2xl">
                    <div class="flex flex-col sm:flex-row items-center justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('medical-records.index') }}" 
                           class="w-full sm:w-auto bg-gradient-to-r from-gray-300 to-gray-400 hover:from-gray-400 hover:to-gray-500 text-gray-800 px-6 py-3 rounded-xl font-medium transition-all duration-200 text-center">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>{{ __('medical_records.save_record') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
