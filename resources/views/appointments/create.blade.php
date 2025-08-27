@extends('layouts.app')

@section('title', __('appointments.schedule_appointment'))

@section('content')
<div class="space-y-6">
    <!-- Working Hours Warning (if outside hours) -->
    @if(!\App\Models\Setting::isWithinWorkingHours())
        @php $nextWorking = \App\Models\Setting::getNextWorkingTime(); @endphp
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
            <div class="flex items-center">
                <i class="fas fa-clock text-orange-600 mr-3 text-lg"></i>
                <div>
                    <h4 class="font-semibold text-orange-800">{{ __('appointments.outside_working_hours') }}</h4>
                    <p class="text-orange-700 text-sm">
                        {{ __('appointments.clinic_closed_message') }}
                        @if($nextWorking)
                            <br><strong>{{ __('appointments.next_available') }}:</strong> {{ $nextWorking->format('l, M j \a\t g:i A') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Modern Header -->
    <div class="glass-effect rounded-2xl p-6 modern-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-plus text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('appointments.schedule_new_appointment') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-1">{{ __('appointments.create_appointment_description') }}</p>
                </div>
            </div>
            <a href="{{ route('appointments.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left text-sm"></i>
                <span>{{ __('appointments.back_to_appointments') }}</span>
            </a>
        </div>
    </div>

    <!-- Modern Appointment Form -->
    <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
        <form action="{{ route('appointments.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Patient & Doctor Selection Section -->
            <div class="p-6 pb-0">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('appointments.patient_information') }}</h3>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Patient Selection -->
                    <div>
                        <label for="patient_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('patients.patient') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <!-- Search Input -->
                            <input type="text" id="patient_search" placeholder="{{ __('patients.search_patients_name_phone') }}..." 
                                   class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 mb-2">
                            
                            <!-- Patient Select -->
                            <select id="patient_id" name="patient_id" required 
                                    class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('patient_id') border-red-500 @enderror">
                                <option value="">{{ __('appointments.select_patient') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" 
                                            data-search="{{ strtolower($patient->first_name . ' ' . $patient->last_name . ' ' . $patient->phone) }}"
                                            {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->phone }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('patient_id')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <!-- Doctor Selection -->
                    <div>
                        @if(auth()->user()->isReceptionist())
                        <label for="doctor_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('common.doctor') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="doctor_id" name="doctor_id" required 
                                class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('doctor_id') border-red-500 @enderror">
                            <option value="">Select a doctor</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id', request('doctor_id')) == $doctor->id ? 'selected' : '' }}>
                                    Dr. {{ $doctor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                        @else
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Assigned Doctor
                        </label>
                        <div class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600">
                            Dr. {{ auth()->user()->name }}
                        </div>
                        <input type="hidden" name="doctor_id" value="{{ auth()->id() }}">
                        @endif
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200"></div>

            <!-- Appointment Details Section -->
            <div class="p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Appointment Details</h3>
                </div>
                
                <div class="grid md:grid-cols-4 gap-4 mb-4">
                    <!-- Appointment Date -->
                    <div>
                        <label for="appointment_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="appointment_date" name="appointment_date" 
                               value="{{ old('appointment_date') }}" required
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('appointment_date') border-red-500 @enderror">
                        @error('appointment_date')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <!-- Appointment Time -->
                    <div>
                        <label for="appointment_time" class="block text-sm font-semibold text-gray-700 mb-2">
                            Time <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="appointment_time" name="appointment_time" 
                               value="{{ old('appointment_time') }}" required
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('appointment_time') border-red-500 @enderror">
                        @error('appointment_time')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                            Status
                        </label>
                        <select id="status" name="status" 
                                class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('status') border-red-500 @enderror">
                            <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>{{ __('appointments.scheduled') }}</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>{{ __('appointments.in_progress') }}</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('appointments.completed') }}</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('appointments.cancelled') }}</option>
                        </select>
                        @error('status')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <!-- Appointment Type -->
                    <div>
                        <label for="appointment_type" class="block text-sm font-semibold text-gray-700 mb-2">
                            Type
                        </label>
                        <select id="appointment_type" name="appointment_type" 
                                class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('appointment_type') border-red-500 @enderror">
                            <option value="consultation" {{ old('appointment_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                            <option value="follow-up" {{ old('appointment_type') == 'follow-up' ? 'selected' : '' }}>Follow-up</option>
                            <option value="emergency" {{ old('appointment_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                            <option value="routine-checkup" {{ old('appointment_type') == 'routine-checkup' ? 'selected' : '' }}>Routine Checkup</option>
                        </select>
                        @error('appointment_type')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>
                </div>

                <!-- Purpose & Notes in 2 columns -->
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Purpose -->
                    <div>
                        <label for="purpose" class="block text-sm font-semibold text-gray-700 mb-2">
                            Purpose/Reason for Visit
                        </label>
                        <textarea id="purpose" name="purpose" rows="3" 
                                  placeholder="Brief description of the reason for this appointment..."
                                  class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('purpose') border-red-500 @enderror">{{ old('purpose') }}</textarea>
                        @error('purpose')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                            Additional Notes
                        </label>
                        <textarea id="notes" name="notes" rows="3" 
                                  placeholder="Any additional notes or special instructions..."
                                  class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-b-2xl">
                <div class="flex flex-col sm:flex-row items-center justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                    <a href="{{ route('appointments.index') }}" 
                       class="w-full sm:w-auto bg-gradient-to-r from-gray-300 to-gray-400 hover:from-gray-400 hover:to-gray-500 text-gray-800 px-6 py-2 rounded-xl font-medium transition-all duration-200 text-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-2 rounded-xl font-medium transition-all duration-200 deep-shadow flex items-center justify-center space-x-2">
                        <i class="fas fa-save"></i>
                        <span>Schedule Appointment</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('patient_search');
    const patientSelect = document.getElementById('patient_id');
    const allOptions = Array.from(patientSelect.options).slice(1); // Exclude first empty option
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        // Clear current options (keep the first empty option)
        patientSelect.innerHTML = '<option value="">Select a patient</option>';
        
        if (searchTerm === '') {
            // Show all patients if search is empty
            allOptions.forEach(option => {
                patientSelect.appendChild(option.cloneNode(true));
            });
        } else {
            // Filter and show matching patients
            const matchingOptions = allOptions.filter(option => {
                const searchData = option.getAttribute('data-search') || '';
                return searchData.includes(searchTerm);
            });
            
            if (matchingOptions.length > 0) {
                matchingOptions.forEach(option => {
                    patientSelect.appendChild(option.cloneNode(true));
                });
            } else {
                // Show "No patients found" option
                const noResultOption = document.createElement('option');
                noResultOption.value = '';
                noResultOption.textContent = 'No patients found';
                noResultOption.disabled = true;
                patientSelect.appendChild(noResultOption);
            }
        }
    });
    
    // Clear search when a patient is selected
    patientSelect.addEventListener('change', function() {
        if (this.value) {
            searchInput.value = '';
            // Show the selected patient's name in search input
            const selectedOption = this.options[this.selectedIndex];
            searchInput.placeholder = 'Selected: ' + selectedOption.textContent;
        } else {
            searchInput.placeholder = 'Search patients by name or phone...';
        }
    });
});
</script>
@endsection
