@extends('layouts.app')

@section('title', __('appointments.edit_appointment'))

@section('content')
<div class="space-y-6">
    <!-- Modern Header -->
    <div class="glass-effect rounded-2xl p-6 modern-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center animate-float">
                    <i class="fas fa-calendar-edit text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('appointments.edit_appointment') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-1">{{ __('appointments.update_appointment_description') }}</p>
                </div>
            </div>
            <a href="{{ route('appointments.show', $appointment) }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('appointments.back_to_details') }}</span>
            </a>
        </div>
    </div>

        <!-- Modern Edit Form -->
        <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
            <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Patient & Doctor Selection Section -->
                <div class="p-6 pb-0">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('appointments.patient_information') }}</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Patient Selection -->
                        <div>
                            <label for="patient_id" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('patients.patient') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="patient_id" name="patient_id" required 
                                    class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('patient_id') border-red-500 @enderror">
                                <option value="">{{ __('appointments.select_patient') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" 
                                            {{ (old('patient_id', $appointment->patient_id) == $patient->id) ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Doctor Selection -->
                        <div>
                            @if(auth()->user()->isReceptionist())
                            <label for="doctor_id" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                Doctor <span class="text-red-500">*</span>
                            </label>
                            <select id="doctor_id" name="doctor_id" required 
                                    class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('doctor_id') border-red-500 @enderror">
                                <option value="">Select a doctor</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" 
                                            {{ (old('doctor_id', $appointment->doctor_id) == $doctor->id) ? 'selected' : '' }}>
                                        Dr. {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                            @else
                            <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                Assigned Doctor
                            </label>
                            <div class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600">
                                Dr. {{ $appointment->doctor->name }}
                            </div>
                            <input type="hidden" name="doctor_id" value="{{ $appointment->doctor_id }}">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                <!-- Appointment Details Section -->
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Appointment Details</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-4 gap-4">
                        <!-- Appointment Date -->
                        <div>
                            <label for="appointment_date" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                Date <span class="text-red-500">*</span>
                            </label>
                            <!-- Debug: Show the raw date value -->
                            <small class="text-gray-500">Debug: {{ $appointment->appointment_date }} | Formatted: {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d') }}</small>
                            <input type="date" id="appointment_date" name="appointment_date" 
                                   value="{{ old('appointment_date', \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')) }}" required
                                   class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('appointment_date') border-red-500 @enderror">
                            @error('appointment_date')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Appointment Time -->
                        <div>
                            <label for="appointment_time" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                Time <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="appointment_time" name="appointment_time" 
                                   value="{{ old('appointment_time', \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i')) }}" required
                                   class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('appointment_time') border-red-500 @enderror">
                            @error('appointment_time')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                Status
                            </label>
                            <select id="status" name="status" 
                                    class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('status') border-red-500 @enderror">
                                <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>{{ __('appointments.scheduled') }}</option>
                                <option value="in_progress" {{ old('status', $appointment->status) == 'in_progress' ? 'selected' : '' }}>{{ __('appointments.in_progress') }}</option>
                                <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>{{ __('appointments.completed') }}</option>
                                <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>{{ __('appointments.cancelled') }}</option>
                            </select>
                            @error('status')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Appointment Type -->
                        <div>
                            <label for="appointment_type" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                Type
                            </label>
                            <select id="appointment_type" name="appointment_type" 
                                    class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('appointment_type') border-red-500 @enderror">
                                <option value="consultation" {{ old('appointment_type', $appointment->appointment_type) == 'consultation' ? 'selected' : '' }}>Consultation</option>
                                <option value="follow-up" {{ old('appointment_type', $appointment->appointment_type) == 'follow-up' ? 'selected' : '' }}>Follow-up</option>
                                <option value="emergency" {{ old('appointment_type', $appointment->appointment_type) == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="routine-checkup" {{ old('appointment_type', $appointment->appointment_type) == 'routine-checkup' ? 'selected' : '' }}>Routine Checkup</option>
                            </select>
                            @error('appointment_type')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Purpose & Notes in 2 columns -->
                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <!-- Purpose -->
                        <div>
                            <label for="purpose" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                Purpose/Reason for Visit
                            </label>
                            <textarea id="purpose" name="purpose" rows="3" 
                                      placeholder="Brief description of the reason for this appointment..."
                                      class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('purpose') border-red-500 @enderror">{{ old('purpose', $appointment->purpose) }}</textarea>
                            @error('purpose')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                Additional Notes
                            </label>
                            <textarea id="notes" name="notes" rows="3" 
                                      placeholder="Any additional notes or special instructions..."
                                      class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('notes') border-red-500 @enderror">{{ old('notes', $appointment->notes) }}</textarea>
                            @error('notes')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="bg-gray-50/80 p-4 border-t border-gray-200 flex items-center justify-between">
                    <div>
                        @if($appointment->status != 'completed' && $appointment->status != 'cancelled')
                        <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-2 rounded-xl transition-all duration-200 flex items-center space-x-2"
                                    onclick="return confirm('{{ __('appointments.confirm_delete') }}')">
                                <i class="fas fa-trash text-sm"></i>
                                <span>Delete</span>
                            </button>
                        </form>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('appointments.show', $appointment) }}" 
                           class="bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white px-5 py-2 rounded-xl transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-5 py-2 rounded-xl transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-save text-sm"></i>
                            <span>Update Appointment</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
