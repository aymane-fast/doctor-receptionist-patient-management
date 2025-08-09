@extends('layouts.app')

@section('title', 'Create Medical Record')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create Medical Record</h1>
            <a href="{{ route('medical-records.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Records
            </a>
        </div>

        <!-- Medical Record Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('medical-records.store') }}" method="POST">
                @csrf
                
                <!-- Patient and Basic Info -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Patient Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Patient Selection -->
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Patient <span class="text-red-500">*</span>
                            </label>
                            <select id="patient_id" name="patient_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select a patient</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" 
                                            {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
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
                                Visit Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="visit_date" name="visit_date" 
                                   value="{{ old('visit_date', date('Y-m-d')) }}" required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('visit_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Visit Type -->
                        <div>
                            <label for="visit_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Visit Type
                            </label>
                            <select id="visit_type" name="visit_type" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="consultation" {{ old('visit_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                                <option value="follow-up" {{ old('visit_type') == 'follow-up' ? 'selected' : '' }}>Follow-up</option>
                                <option value="emergency" {{ old('visit_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="routine-checkup" {{ old('visit_type') == 'routine-checkup' ? 'selected' : '' }}>Routine Checkup</option>
                            </select>
                            @error('visit_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Appointment Link -->
                        @if(request('appointment_id'))
                        <input type="hidden" name="appointment_id" value="{{ request('appointment_id') }}">
                        @else
                        <div>
                            <label for="appointment_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Related Appointment
                            </label>
                            <select id="appointment_id" name="appointment_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">No appointment</option>
                                @foreach($appointments as $appointment)
                                    <option value="{{ $appointment->id }}" {{ old('appointment_id') == $appointment->id ? 'selected' : '' }}>
                                        {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }} - 
                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M j, Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Vital Signs -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Vital Signs</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="blood_pressure" class="block text-sm font-medium text-gray-700 mb-2">
                                Blood Pressure
                            </label>
                            <input type="text" id="blood_pressure" name="blood_pressure" 
                                   value="{{ old('blood_pressure') }}" 
                                   placeholder="120/80"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('blood_pressure')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="heart_rate" class="block text-sm font-medium text-gray-700 mb-2">
                                Heart Rate (bpm)
                            </label>
                            <input type="number" id="heart_rate" name="heart_rate" 
                                   value="{{ old('heart_rate') }}" 
                                   min="0" max="300"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('heart_rate')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="temperature" class="block text-sm font-medium text-gray-700 mb-2">
                                Temperature (°F)
                            </label>
                            <input type="number" id="temperature" name="temperature" 
                                   value="{{ old('temperature') }}" 
                                   step="0.1" min="90" max="110"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('temperature')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">
                                Weight (kg)
                            </label>
                            <input type="number" id="weight" name="weight" 
                                   value="{{ old('weight') }}" 
                                   step="0.1" min="0" max="500"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('weight')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="height" class="block text-sm font-medium text-gray-700 mb-2">
                                Height (cm)
                            </label>
                            <input type="number" id="height" name="height" 
                                   value="{{ old('height') }}" 
                                   min="0" max="300"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('height')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="oxygen_saturation" class="block text-sm font-medium text-gray-700 mb-2">
                                Oxygen Saturation (%)
                            </label>
                            <input type="number" id="oxygen_saturation" name="oxygen_saturation" 
                                   value="{{ old('oxygen_saturation') }}" 
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
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Clinical Information</h2>
                    
                    <!-- Chief Complaint -->
                    <div class="mb-6">
                        <label for="chief_complaint" class="block text-sm font-medium text-gray-700 mb-2">
                            Chief Complaint <span class="text-red-500">*</span>
                        </label>
                        <textarea id="chief_complaint" name="chief_complaint" rows="3" required
                                  placeholder="Primary reason for the visit..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('chief_complaint') }}</textarea>
                        @error('chief_complaint')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- History of Present Illness -->
                    <div class="mb-6">
                        <label for="history_present_illness" class="block text-sm font-medium text-gray-700 mb-2">
                            History of Present Illness
                        </label>
                        <textarea id="history_present_illness" name="history_present_illness" rows="4"
                                  placeholder="Detailed description of the current illness..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('history_present_illness') }}</textarea>
                        @error('history_present_illness')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Physical Examination -->
                    <div class="mb-6">
                        <label for="physical_examination" class="block text-sm font-medium text-gray-700 mb-2">
                            Physical Examination
                        </label>
                        <textarea id="physical_examination" name="physical_examination" rows="4"
                                  placeholder="Findings from physical examination..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('physical_examination') }}</textarea>
                        @error('physical_examination')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assessment/Diagnosis -->
                    <div class="mb-6">
                        <label for="diagnosis" class="block text-sm font-medium text-gray-700 mb-2">
                            Assessment/Diagnosis <span class="text-red-500">*</span>
                        </label>
                        <textarea id="diagnosis" name="diagnosis" rows="3" required
                                  placeholder="Medical diagnosis or assessment..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('diagnosis') }}</textarea>
                        @error('diagnosis')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Treatment Plan -->
                    <div class="mb-6">
                        <label for="treatment_plan" class="block text-sm font-medium text-gray-700 mb-2">
                            Treatment Plan
                        </label>
                        <textarea id="treatment_plan" name="treatment_plan" rows="4"
                                  placeholder="Treatment recommendations and plan..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('treatment_plan') }}</textarea>
                        @error('treatment_plan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Additional Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Notes
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="Any additional observations or notes..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('medical-records.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i>Save Medical Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
