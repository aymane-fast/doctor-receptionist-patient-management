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
                    <!-- Patient Selection with Autocomplete -->
                    <div>
                        <label for="patient_search" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('patients.patient') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <!-- Patient Search Input with Autocomplete -->
                            <input type="text" 
                                   id="patient_search" 
                                   placeholder="Search patients by name, phone, or email..." 
                                   class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('patient_id') border-red-500 @enderror"
                                   autocomplete="off"
                                   value="{{ $selectedPatient ? $selectedPatient->first_name . ' ' . $selectedPatient->last_name . ' - ' . $selectedPatient->phone : '' }}">
                            
                            <!-- Hidden input for patient_id -->
                            <input type="hidden" 
                                   id="patient_id" 
                                   name="patient_id" 
                                   value="{{ $selectedPatient ? $selectedPatient->id : old('patient_id') }}" 
                                   required>
                            
                            <!-- Autocomplete dropdown -->
                            <div id="patient_results" 
                                 class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-60 overflow-auto hidden">
                                <!-- Results will be populated by JavaScript -->
                            </div>
                            
                            <!-- Create new patient link -->
                            <div class="mt-2">
                                <a href="{{ route('patients.create') }}" 
                                   target="_blank"
                                   class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center space-x-1">
                                    <i class="fas fa-plus text-xs"></i>
                                    <span>Create New Patient</span>
                                </a>
                            </div>
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
                
                <div class="grid md:grid-cols-3 gap-4 mb-4">
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

                    <!-- Status (Hidden - New appointments are always scheduled) -->
                    <input type="hidden" name="status" value="scheduled">
                </div>

                <!-- Purpose & Notes in 2 columns -->
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Purpose -->
                    <div>
                        <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">
                            Purpose/Reason for Visit
                        </label>
                        <textarea id="reason" name="reason" rows="3" 
                                  placeholder="Brief description of the reason for this appointment..."
                                  class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                        @error('reason')
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
                    <button type="button" onclick="bookNow()" 
                            class="w-full sm:w-auto bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-2 rounded-xl font-medium transition-all duration-200 deep-shadow flex items-center justify-center space-x-2">
                        <i class="fas fa-clock"></i>
                        <span>{{ __('appointments.book_now') }}</span>
                    </button>
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
    const patientIdInput = document.getElementById('patient_id');
    const resultsContainer = document.getElementById('patient_results');
    let searchTimeout;
    let selectedPatientId = patientIdInput.value;

    // Patient Search Autocomplete
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideResults();
            patientIdInput.value = '';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchPatients(query);
        }, 300); // Debounce for 300ms
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            hideResults();
        }
    });

    // Clear search if patient was deselected
    searchInput.addEventListener('focus', function() {
        if (this.value && !patientIdInput.value) {
            this.value = '';
        }
    });

    function searchPatients(query) {
        fetch(`{{ route('api.patients.search') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(patients => {
                displayResults(patients);
            })
            .catch(error => {
                console.error('Search error:', error);
                hideResults();
            });
    }

    function displayResults(patients) {
        if (patients.length === 0) {
            resultsContainer.innerHTML = `
                <div class="p-3 text-gray-500 text-center">
                    <i class="fas fa-search mr-2"></i>
                    No patients found
                </div>
            `;
        } else {
            resultsContainer.innerHTML = patients.map(patient => `
                <div class="patient-result p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                     data-id="${patient.id}" 
                     data-display="${patient.display}">
                    <div class="font-medium text-gray-900">${patient.name}</div>
                    <div class="text-sm text-gray-600">${patient.phone} • ${patient.email}</div>
                </div>
            `).join('');

            // Add click handlers to results
            document.querySelectorAll('.patient-result').forEach(result => {
                result.addEventListener('click', function() {
                    selectPatient(this.dataset.id, this.dataset.display);
                });
            });
        }
        
        showResults();
    }

    function selectPatient(id, display) {
        patientIdInput.value = id;
        searchInput.value = display;
        selectedPatientId = id;
        hideResults();
    }

    function showResults() {
        resultsContainer.classList.remove('hidden');
    }

    function hideResults() {
        resultsContainer.classList.add('hidden');
    }

    // Book Now functionality
    window.bookNow = function() {
        // Validate that a patient is selected
        if (!patientIdInput.value) {
            alert('Please select a patient first.');
            searchInput.focus();
            return;
        }

        // Set today's date
        const today = new Date();
        const dateInput = document.querySelector('input[name="appointment_date"]');
        if (dateInput) {
            dateInput.value = today.toISOString().split('T')[0];
        }
        
        // Set status to scheduled for immediate appointments
        const statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) {
            statusSelect.value = 'scheduled';
        }
        
        // Set reason if empty
        const reasonInput = document.querySelector('textarea[name="reason"]');
        if (reasonInput && !reasonInput.value.trim()) {
            reasonInput.value = 'Walk-in / Immediate consultation';
        }
        
        // Find next available time slot and set it
        setNextAvailableTime();
        

    };
    
    function setNextAvailableTime() {
        const timeInput = document.querySelector('input[name="appointment_time"]');
        const dateInput = document.querySelector('input[name="appointment_date"]');
        
        if (!timeInput || !dateInput) return;

        // Fetch working hours dynamically
        fetch('{{ route('api.working-hours') }}')
            .then(response => response.json())
            .then(workingHours => {
                const now = new Date();
                let appointmentDate = new Date(now);
                
                // Find the next available time slot
                const nextSlot = findNextAvailableSlot(now, workingHours);
                
                if (nextSlot) {
                    // Update both date and time
                    dateInput.value = nextSlot.toISOString().split('T')[0];
                    timeInput.value = nextSlot.toTimeString().slice(0, 5); // HH:MM format
                } else {
                    // Fallback to next working day at start time
                    setFallbackTime(workingHours, dateInput, timeInput);
                }
            })
            .catch(error => {
                console.error('Error fetching working hours:', error);
                // Fallback to basic time setting
                setBasicFallbackTime(timeInput, dateInput);
            });
    }

    function findNextAvailableSlot(currentTime, workingHours) {
        const today = currentTime.getDay(); // 0 = Sunday, 1 = Monday, etc.
        const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        // Try today first if we're within working hours
        let testDate = new Date(currentTime);
        
        // Add 30 minutes buffer for booking
        testDate.setMinutes(testDate.getMinutes() + 30);
        
        // Round to next 15-minute interval
        const minutes = testDate.getMinutes();
        const roundedMinutes = Math.ceil(minutes / 15) * 15;
        testDate.setMinutes(roundedMinutes);
        testDate.setSeconds(0);
        
        // Check if this time is within today's working hours
        const todayName = dayNames[today];
        const todayHours = workingHours.all_days[todayName];
        
        if (todayHours && todayHours.is_working) {
            const currentTimeString = testDate.toTimeString().slice(0, 5); // HH:MM
            
            if (currentTimeString >= todayHours.start_time && currentTimeString <= todayHours.end_time) {
                return testDate;
            }
        }
        
        // If not available today, find next working day
        for (let daysAhead = 1; daysAhead <= 7; daysAhead++) {
            const nextDate = new Date(currentTime);
            nextDate.setDate(nextDate.getDate() + daysAhead);
            
            const dayName = dayNames[nextDate.getDay()];
            const dayHours = workingHours.all_days[dayName];
            
            if (dayHours && dayHours.is_working) {
                // Set to start of working hours
                const [hours, minutes] = dayHours.start_time.split(':');
                nextDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
                return nextDate;
            }
        }
        
        return null; // No working day found in next week
    }

    function setFallbackTime(workingHours, dateInput, timeInput) {
        if (workingHours.next_working_time) {
            const nextWorking = new Date(workingHours.next_working_time);
            dateInput.value = nextWorking.toISOString().split('T')[0];
            timeInput.value = nextWorking.toTimeString().slice(0, 5);
        } else {
            setBasicFallbackTime(timeInput, dateInput);
        }
    }

    function setBasicFallbackTime(timeInput, dateInput) {
        // Emergency fallback - set to tomorrow 9 AM
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(9, 0, 0, 0);
        
        dateInput.value = tomorrow.toISOString().split('T')[0];
        timeInput.value = '09:00';
    }

    // Form validation before submit
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (!patientIdInput.value) {
            e.preventDefault();
            alert('Please select a patient.');
            searchInput.focus();
            return false;
        }
    });
});
</script>
@endsection
