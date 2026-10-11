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
                            <br><strong>{{ __('appointments.next_available') }}:</strong> {{ $nextWorking->translatedFormat('l, M j \a\t g:i A') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    <x-ui.page-header
        :title="__('appointments.schedule_new_appointment')"
        :description="__('appointments.create_appointment_description')"
        icon="fa-calendar-plus"
        :eyebrow="__('New booking')"
    >
        <x-slot:actions>
            <button onclick="history.back()" class="btn-secondary px-4 py-2">
                <i class="fas fa-arrow-left text-sm"></i>
                <span>{{ __('common.back') }}</span>
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <!-- Modern Appointment Form -->
    <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
        <form action="{{ route('appointments.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Patient & Doctor Selection Section -->
            <div class="p-6 pb-0">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 brand-mark rounded-lg flex items-center justify-center">
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
                                   placeholder="{{ __('appointments.search_patients_placeholder') }}" 
                                   class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white @error('patient_id') border-red-500 @enderror"
                                   autocomplete="off"
                                   value="{{ $selectedPatient?->selection_label ?? '' }}">
                            
                            <!-- Hidden input for patient_id -->
                            <input type="hidden" 
                                   id="patient_id" 
                                   name="patient_id" 
                                   value="{{ $selectedPatient?->id ?? '' }}"
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
                                   class="text-sm text-teal-700 hover:text-teal-900 transition-colors duration-200 flex items-center space-x-1">
                                    <i class="fas fa-plus text-xs"></i>
                                    <span>{{ __('patients.add_new_patient') }}</span>
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
                                class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white @error('doctor_id') border-red-500 @enderror">
                            <option value="">{{ __('appointments.select_doctor') }}</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" data-mode="{{ $doctor->appointment_mode ?? 'scheduled' }}" {{ old('doctor_id', request('doctor_id')) == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->professional_name }}
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
                            {{ __('appointments.assigned_doctor') }}
                        </label>
                        <div class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600">
                            {{ auth()->user()->professional_name }}
                        </div>
                        <input type="hidden" id="doctor_id" name="doctor_id" value="{{ auth()->id() }}" data-mode="{{ auth()->user()->appointment_mode ?? 'scheduled' }}">
                        @endif
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200"></div>

            <!-- Appointment Details Section -->
            <div class="p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 brand-mark rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('appointments.appointment_details') }}</h3>
                </div>
                
                <div class="grid md:grid-cols-3 gap-4 mb-4">
                    <!-- Appointment Date -->
                    <div>
                        <label for="appointment_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('appointments.appointment_date') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="appointment_date" name="appointment_date" 
                               value="{{ old('appointment_date') }}" required
                               min="{{ date('Y-m-d') }}"
                               class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white @error('appointment_date') border-red-500 @enderror">
                        @error('appointment_date')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <!-- Appointment Time -->
                    <div id="appointment_time_wrapper">
                        <label for="appointment_time" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('appointments.appointment_time') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="appointment_time" name="appointment_time" 
                               value="{{ old('appointment_time') }}"
                               class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white @error('appointment_time') border-red-500 @enderror">
                        @error('appointment_time')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                        
                        <!-- Book Now Button -->
                        <div class="mt-3">
                            <button type="button" id="book_now_btn" 
                                    class="btn-ghost w-full px-4 py-2">
                                <i class="fas fa-magic text-sm"></i>
                                <span>{{ __('appointments.book_now') }}</span>
                            </button>
                            <p id="book_now_message" class="mt-2 text-sm text-gray-600 hidden"></p>
                        </div>
                    </div>

                    <div id="queue_mode_notice" class="hidden md:col-span-2 soft-panel rounded-xl p-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-list-ol text-teal-700 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-teal-900">{{ __('Queue mode') }}</h4>
                                <p class="text-sm text-teal-800 mt-1">{{ __('This doctor uses queue mode. The patient will receive the next queue number and an estimated consultation time automatically.') }}</p>
                                <p id="queue_mode_estimate" class="text-sm text-teal-900 font-semibold mt-2 hidden"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Status (Hidden - New appointments are always scheduled) -->
                    <input type="hidden" name="status" value="scheduled">
                </div>

                @include('appointments.override-fields', ['appointment' => null])

                <!-- Purpose & Notes in 2 columns -->
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Purpose -->
                    <div>
                        <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __($clinicSpecialty['label']) }} {{ __('visit reason') }}
                        </label>
                        <textarea id="reason" name="reason" rows="3" 
                                  placeholder="{{ __('appointments.reason_placeholder') }}"
                                  class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white resize-none @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                        @error('reason')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                        @if(! empty($clinicSpecialty['appointment_reasons']))
                            <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __($clinicSpecialty['label']) }} {{ __('suggestions') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($clinicSpecialty['appointment_reasons'] as $suggestion)
                                    <button type="button" data-reason-suggestion="{{ __($suggestion) }}" class="rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-medium text-teal-800 hover:bg-teal-100">{{ __($suggestion) }}</button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('appointments.notes') }}
                        </label>
                        <textarea id="notes" name="notes" rows="3" 
                                  placeholder="{{ __('appointments.notes_placeholder') }}"
                                  class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white resize-none @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
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
                    <button type="button" onclick="history.back()" 
                            class="btn-secondary w-full sm:w-auto px-6 py-2 text-center">
                        {{ __('common.cancel') }}
                    </button>

                    <button type="submit" 
                            class="btn-primary w-full sm:w-auto px-6 py-2 deep-shadow">
                        <i class="fas fa-save"></i>
                        <span>{{ __('appointments.schedule_appointment') }}</span>
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
    const doctorInput = document.getElementById('doctor_id');
    const timeWrapper = document.getElementById('appointment_time_wrapper');
    const timeInput = document.getElementById('appointment_time');
    const bookNowBtn = document.getElementById('book_now_btn');
    const queueNotice = document.getElementById('queue_mode_notice');
    const queueEstimate = document.getElementById('queue_mode_estimate');
    const reasonInput = document.getElementById('reason');
    let searchTimeout;
    let selectedPatientId = patientIdInput.value;
    const doctorModes = @json($doctorModes);

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
        })[character]);
    }

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
                    {{ __('No patients found') }}
                </div>
            `;
        } else {
            resultsContainer.innerHTML = patients.map(patient => `
                <div class="patient-result p-3 hover:bg-teal-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                     data-id="${patient.id}" 
                     data-display="${escapeHtml(patient.display)}">
                    <div class="font-medium text-gray-900">${escapeHtml(patient.name)}</div>
                    <div class="text-sm text-gray-600">${escapeHtml(patient.phone)} • ${escapeHtml(patient.email)}</div>
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

    function selectedDoctorId() {
        return doctorInput ? doctorInput.value : null;
    }

    function selectedDoctorMode() {
        const doctorId = selectedDoctorId();
        if (doctorModes[doctorId]) {
            return doctorModes[doctorId].mode || 'scheduled';
        }

        return doctorInput?.dataset.mode || 'scheduled';
    }

    async function refreshAppointmentMode() {
        const mode = selectedDoctorMode();
        const isQueue = mode === 'queue';

        timeWrapper.classList.toggle('hidden', isQueue);
        queueNotice.classList.toggle('hidden', !isQueue);
        const overrideFields = document.getElementById('scheduling_override_fields');
        overrideFields.classList.toggle('hidden', isQueue);
        overrideFields.querySelectorAll('input').forEach(input => { input.disabled = isQueue; });
        timeInput.required = !isQueue;
        if (bookNowBtn) {
            bookNowBtn.classList.toggle('hidden', isQueue);
        }

        if (isQueue) {
            timeInput.value = '';
            await refreshQueueEstimate();
        } else {
            queueEstimate.classList.add('hidden');
        }
    }

    async function refreshQueueEstimate() {
        const dateInput = document.querySelector('input[name="appointment_date"]');
        const doctorId = selectedDoctorId();
        if (!dateInput.value || !doctorId) {
            queueEstimate.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`{{ route('api.appointments.next-available-slot') }}?date=${dateInput.value}&doctor_id=${doctorId}`);
            const data = await response.json();
            if (data.mode === 'queue') {
                queueEstimate.textContent = @json(__('Next queue number: #:number. Estimated time: :time.', ['number' => ':number', 'time' => ':time']))
                    .replace(':number', data.queue_number).replace(':time', data.estimated_time);
                queueEstimate.classList.remove('hidden');
            }
        } catch (error) {
            queueEstimate.classList.add('hidden');
        }
    }

    if (doctorInput) {
        doctorInput.addEventListener('change', refreshAppointmentMode);
    }

    const appointmentDateInput = document.querySelector('input[name="appointment_date"]');
    if (appointmentDateInput) {
        appointmentDateInput.addEventListener('change', refreshAppointmentMode);
    }

    document.querySelectorAll('[data-reason-suggestion]').forEach((button) => {
        button.addEventListener('click', () => {
            reasonInput.value = button.dataset.reasonSuggestion;
            reasonInput.focus();
        });
    });

    refreshAppointmentMode();

    // Book Now functionality
    window.bookNow = function() {
        // Validate that a patient is selected
        if (!patientIdInput.value) {
            alert(@json(__('Please select a patient first.')));
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
            reasonInput.value = @json(isset($clinicSpecialty['appointment_reasons'][0]) ? __($clinicSpecialty['appointment_reasons'][0]) : __('appointments.walk_in_consultation'));
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

    const bookNowMessage = document.getElementById('book_now_message');
    
    if (bookNowBtn) {
        bookNowBtn.addEventListener('click', async function() {
            const dateInput = document.querySelector('input[name="appointment_date"]');
            const timeInput = document.querySelector('input[name="appointment_time"]');
            
            if (!dateInput.value) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }
            
            const originalText = bookNowBtn.innerHTML;
            bookNowBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>' + @json(__('Finding best time...'));
            bookNowBtn.disabled = true;
            
            try {
                const doctorId = selectedDoctorId();
                const response = await fetch(`{{ route('api.appointments.next-available-slot') }}?date=${dateInput.value}&doctor_id=${doctorId || ''}`);
                const data = await response.json();
                
                if (data.mode === 'queue') {
                    bookNowMessage.innerHTML = `<i class="fas fa-info-circle text-teal-600 mr-1"></i>${data.message}`;
                    bookNowMessage.classList.remove('hidden');
                } else if (data.available) {
                    if (!timeInput.value || confirm(@json(__('workflow.replace_time')) + ' ' + data.next_slot.time)) {
                        timeInput.value = data.next_slot.time;
                    }
                    bookNowMessage.innerHTML = `<i class="fas fa-info-circle text-teal-600 mr-1"></i>${data.message}`;
                    bookNowMessage.classList.remove('hidden');
                } else {
                    if (data.next_available_date && data.next_available_time) {
                        if (confirm(@json(__('workflow.use_next_suggestion')) + ' ' + data.next_available_date + ' ' + data.next_available_time)) {
                            dateInput.value = data.next_available_date;
                            timeInput.value = data.next_available_time;
                        }
                    }
                    bookNowMessage.innerHTML = `<i class="fas fa-exclamation-triangle text-orange-500 mr-1"></i>${data.message}`;
                    bookNowMessage.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                bookNowMessage.innerHTML = `<i class="fas fa-exclamation-circle text-red-500 mr-1"></i>${@json(__('Error finding available slots. Please select manually.'))}`;
                bookNowMessage.classList.remove('hidden');
            }
            
            setTimeout(() => {
                bookNowBtn.innerHTML = originalText;
                bookNowBtn.disabled = false;
            }, 1000);
        });
    }

    // Form validation before submit
    const form = document.querySelector('form[action="{{ route('appointments.store') }}"]');
    form.addEventListener('submit', function(e) {
        if (!patientIdInput.value) {
            e.preventDefault();
            alert(@json(__('Please select a patient.')));
            searchInput.focus();
            return false;
        }
    });
});
</script>
@endsection
