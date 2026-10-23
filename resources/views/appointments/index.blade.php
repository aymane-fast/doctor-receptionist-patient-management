@extends('layouts.app')

@section('title', __('appointments.title'))

@section('content')
<div class="space-y-8">
    <x-ui.page-header
        :title="__('appointments.title')"
        :description="__('appointments.manage_appointments_scheduling')"
        icon="fa-calendar-check"
        :eyebrow="__('Scheduling')"
    >
        <x-slot:actions>
                <a href="{{ route('appointments.queue') }}" class="btn-secondary px-6 py-3 rounded-2xl">
                    <i class="fas fa-list-ol"></i>
                    <span>{{ __('Queue') }}</span>
                </a>
                <a href="{{ route('waitlist.index') }}" class="btn-secondary px-6 py-3 rounded-2xl">
                    <i class="fas fa-hourglass-half"></i>
                    <span>{{ __('appointments.waitlist') }}</span>
                </a>
                <a href="{{ route('appointments.create') }}" class="btn-primary px-6 py-3 rounded-2xl deep-shadow">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('appointments.schedule_appointment') }}</span>
                </a>
        </x-slot:actions>
    </x-ui.page-header>
    @php($modeDoctor = auth()->user()->isDoctor() ? auth()->user() : $doctors->firstWhere('id', request()->integer('doctor_id')))
    @if($modeDoctor)
        <div class="mode-summary"><div><strong><i class="fas {{ $modeDoctor->usesQueueMode() ? 'fa-list-ol' : 'fa-calendar-days' }} mr-2"></i>{{ __('clinical_ui.booking_mode') }} {{ $modeDoctor->usesQueueMode() ? __('Queue') : __('Scheduled') }}</strong><p>{{ __('clinical_ui.mode_help') }}</p></div><a href="{{ route('appointments.queue', ['date' => request('date', today()->toDateString()), 'doctor_id' => $modeDoctor->id]) }}" class="text-link">{{ __('clinical_ui.all_active') }} <i class="fas fa-arrow-right"></i></a></div>
    @endif

    <!-- Modern Filters -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow relative z-[10001]">
        <form method="GET" action="{{ route('appointments.index') }}" class="space-y-6">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 brand-mark rounded-xl flex items-center justify-center">
                    <i class="fas fa-filter text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ __('appointments.filter_appointments') }}</h3>
            </div>
            <div class="space-y-6">
                <!-- Filter Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <div class="space-y-2 relative z-[10000]">
                        <label for="patient_search" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('appointments.search') }}</label>
                        <div class="relative">
                            <input type="text" 
                                   id="patient_search" 
                                   placeholder="{{ __('appointments.search_placeholder') }}" 
                                   class="form-control w-full px-4 py-3 bg-white/80 backdrop-blur-sm focus:bg-white"
                                   autocomplete="off"
                                   value="{{ request('search') }}">
                            
                            <!-- Hidden input for search -->
                            <input type="hidden" id="search" name="search" value="{{ request('search') }}">
                            
                            <!-- Autocomplete dropdown -->
                            <div id="patient_results" 
                                 class="absolute z-[99999] w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-60 overflow-auto hidden">
                                <!-- Results will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="date" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('appointments.date') }}</label>
                        <input type="date" 
                               id="date" 
                               name="date" 
                               value="{{ request('date') }}"
                               class="form-control w-full px-4 py-3 bg-white/80 backdrop-blur-sm focus:bg-white">
                    </div>

                    <div class="space-y-2">
                        <label for="status" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('appointments.status') }}</label>
                        <select id="status" 
                                name="status" 
                                class="form-control w-full px-4 py-3 bg-white/80 backdrop-blur-sm focus:bg-white">
                            <option value="">{{ __('appointments.all_statuses') }}</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>{{ __('appointments.scheduled') }}</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('appointments.in_progress') }}</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('appointments.completed') }}</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('appointments.cancelled') }}</option>
                            <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>{{ __('appointments.no_show') }}</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="appointment_mode" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('Mode') }}</label>
                        <select id="appointment_mode"
                                name="appointment_mode"
                                class="form-control w-full px-4 py-3 bg-white/80 backdrop-blur-sm focus:bg-white">
                            <option value="">{{ __('All Modes') }}</option>
                            <option value="scheduled" {{ request('appointment_mode') == 'scheduled' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                            <option value="queue" {{ request('appointment_mode') == 'queue' ? 'selected' : '' }}>{{ __('Queue') }}</option>
                        </select>
                    </div>

                    @if(auth()->user()->isReceptionist())
                    <div class="space-y-2">
                        <label for="doctor_id" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('appointments.doctor') }}</label>
                        <select id="doctor_id" 
                                name="doctor_id" 
                                class="form-control w-full px-4 py-3 bg-white/80 backdrop-blur-sm focus:bg-white">
                            <option value="">{{ __('appointments.all_doctors') }}</option>
                            @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->professional_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn-primary px-6 py-2.5 text-sm shadow-md hover:shadow-lg">
                        <i class="fas fa-search text-sm"></i>
                        <span>{{ __('appointments.search') }}</span>
                    </button>
                    <button type="submit" name="show_today" value="1" class="btn-ghost px-6 py-2.5 text-sm shadow-md hover:shadow-lg">
                        <i class="fas fa-calendar-day text-sm"></i>
                        <span>{{ __('appointments.today') }}</span>
                    </button>
                    @if(request()->query())
                    <a href="{{ route('appointments.index') }}" class="btn-danger px-4 py-2.5 text-sm shadow-md hover:shadow-lg" title="{{ __('appointments.clear') }}">
                        <i class="fas fa-times text-sm"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Modern Appointments List -->
    <div class="glass-effect rounded-3xl modern-shadow overflow-hidden relative z-0">
        @if(!$hasSearchQuery)
            <!-- Initial state - no search performed -->
            <div class="text-center py-16 px-8">
                <div class="w-24 h-24 icon-tile rounded-3xl flex items-center justify-center mx-auto mb-6 animate-float">
                    <i class="fas fa-search text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ __('appointments.search_to_begin') }}</h3>
                <p class="text-gray-500 mb-6 text-lg max-w-md mx-auto">{{ __('appointments.search_instructions') }}</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <form method="GET" action="{{ route('appointments.index') }}" class="inline">
                        <button type="submit" name="show_today" value="1" class="btn-ghost px-6 py-3 rounded-2xl">
                            <i class="fas fa-calendar-day"></i>
                            <span>{{ __('appointments.show_today') }}</span>
                        </button>
                    </form>
                    <a href="{{ route('appointments.create') }}" class="btn-primary px-6 py-3 rounded-2xl">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('appointments.schedule_appointment') }}</span>
                    </a>
                    <a href="{{ route('appointments.queue') }}" class="btn-secondary px-6 py-3 rounded-2xl">
                        <i class="fas fa-list-ol"></i>
                        <span>{{ __('Queue') }}</span>
                    </a>
                </div>
            </div>
        @elseif($appointments->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.patient') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.doctor') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.date_time') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.status') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.reason') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($appointments as $appointment)
                        <tr class="hover:bg-teal-50/60 transition-all duration-200 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="px-8 py-6">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 icon-tile rounded-2xl flex items-center justify-center mr-4">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="text-base font-semibold text-gray-900">{{ $appointment->patient->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $appointment->patient->id_card_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-teal-50 text-teal-700 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-user-md text-sm"></i>
                                    </div>
                                    <span class="text-base font-medium text-gray-900">{{ $appointment->doctor->professional_name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="space-y-1">
                                    <div class="text-base font-semibold text-gray-900">{{ $appointment->appointment_date->translatedFormat('M j, Y') }}</div>
                                    <div class="text-sm text-gray-500 flex items-center space-x-1">
                                        <i class="fas fa-clock text-xs"></i>
                                        <span>{{ $appointment->displayTime() }}</span>
                                    </div>
                                    @if($appointment->isQueueMode())
                                    <div class="text-xs text-teal-700 font-semibold">
                                        {{ __('Queue') }} #{{ $appointment->queue_number }} {{ __('Estimated Time') }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center space-x-3">
                                    <span class="px-3 py-2 text-sm font-bold rounded-xl {{ \App\Models\Appointment::statusBadgeClasses($appointment->status) }}">
                                        {{ \App\Models\Appointment::statusLabel($appointment->status) }}
                                    </span>
                                    
                                    <!-- Quick Status Update -->
                                    <div class="relative">
                                        <button type="button" popovertarget="status-dropdown-{{ $appointment->id }}" aria-label="{{ __('clinical_ui.status_actions', ['patient' => $appointment->patient->full_name]) }}" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-all duration-200">
                                            <i class="fas fa-edit text-gray-500 text-xs"></i>
                                        </button>
                                        <div id="status-dropdown-{{ $appointment->id }}" popover class="appointment-status-menu bg-white border border-gray-200 rounded-2xl shadow-xl py-2 min-w-40">
                                            @if($appointment->canTransitionTo('scheduled'))
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="scheduled">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left rounded-xl mx-1 transition-colors">
                                                    <i class="fas fa-clock text-yellow-600 mr-2"></i>{{ __('appointments.scheduled') }}
                                                </button>
                                            </form>
                                            @endif
                                            
                                            @if($appointment->canTransitionTo('in_progress') && $appointment->appointment_date->isToday())
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left rounded-xl mx-1 transition-colors">
                                                    <i class="fas fa-play text-sky-600 mr-2"></i>{{ __('appointments.in_progress') }}
                                                </button>
                                            </form>
                                            @endif
                                            
                                            @if($appointment->canTransitionTo('completed'))
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left rounded-xl mx-1 transition-colors">
                                                    <i class="fas fa-check text-green-600 mr-2"></i>{{ __('appointments.completed') }}
                                                </button>
                                            </form>
                                            @endif
                                            
                                            @if($appointment->canTransitionTo('no_show') && !$appointment->appointment_date->isFuture())
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="no_show">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left rounded-xl mx-1 transition-colors">
                                                    <i class="fas fa-user-slash text-orange-600 mr-2"></i>{{ __('appointments.no_show') }}
                                                </button>
                                            </form>
                                            @endif

                                            @if($appointment->canTransitionTo('cancelled'))
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left rounded-xl mx-1 transition-colors"
                                                        onclick="return confirm(@js(__('appointments.confirm_cancel')))">
                                                    <i class="fas fa-times text-red-600 mr-2"></i>{{ __('appointments.cancelled') }}
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-sm text-gray-900">
                                <div class="max-w-xs">
                                    {{ $appointment->reason ? Str::limit(translate_appointment_reason($appointment->reason), 50) : '-' }}
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('appointments.show', $appointment) }}" aria-label="{{ __('View Details') }}" class="w-10 h-10 bg-teal-100 hover:bg-teal-200 text-teal-700 rounded-xl flex items-center justify-center transition-all duration-200 group">
                                        <i class="fas fa-eye text-sm group-hover:scale-110"></i>
                                    </a>
                                    <a href="{{ route('appointments.edit', $appointment) }}" aria-label="{{ __('appointments.edit_appointment') }}" class="w-10 h-10 bg-sky-100 hover:bg-sky-200 text-sky-700 rounded-xl flex items-center justify-center transition-all duration-200 group">
                                        <i class="fas fa-edit text-sm group-hover:scale-110"></i>
                                    </a>
                                    <x-appointments.reschedule-actions :appointment="$appointment" :compact="true" />
                                    @if(auth()->user()->isDoctor() && $appointment->status == 'completed' && !$appointment->medicalRecord)
                                    <a href="{{ route('medical-records.create', ['appointment_id' => $appointment->id]) }}" 
                                       class="w-10 h-10 bg-red-100 hover:bg-red-200 text-red-700 rounded-xl flex items-center justify-center transition-all duration-200 group" 
                                       title="{{ __('Add Medical Record') }}" aria-label="{{ __('Add Medical Record') }}">
                                        <i class="fas fa-file-medical text-sm group-hover:scale-110"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Modern Pagination -->
            @if(method_exists($appointments, 'appends'))
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-t border-gray-100">
                {{ $appointments->appends(request()->query())->links('pagination.smart') }}
            </div>
            @endif
        @else
            <div class="text-center py-16 px-8">
                <div class="w-24 h-24 icon-tile rounded-3xl flex items-center justify-center mx-auto mb-6 animate-float">
                    <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ __('appointments.no_appointments_found') }}</h3>
                <p class="text-gray-500 mb-8 text-lg max-w-md mx-auto">
                    @if(request()->query())
                        {{ __('appointments.no_match_filter_criteria') }}
                    @else
                        {{ __('appointments.get_started_first_appointment') }}
                    @endif
                </p>
                <a href="{{ route('appointments.create') }}" class="btn-primary px-8 py-4 rounded-2xl deep-shadow">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('appointments.schedule_appointment') }}</span>
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Patient Search Autocomplete for Index
    const searchInput = document.getElementById('patient_search');
    const searchHidden = document.getElementById('search');
    const resultsContainer = document.getElementById('patient_results');
    let searchTimeout;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
        })[character]);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            // Update hidden field
            searchHidden.value = query;
            
            if (query.length < 2) {
                hideResults();
                return;
            }
            
            searchTimeout = setTimeout(() => {
                searchPatients(query);
            }, 300);
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                hideResults();
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
                         data-search="${escapeHtml(patient.patient_name || patient.name)}">
                        <div class="font-medium text-gray-900">${escapeHtml(patient.name)}</div>
                        <div class="text-sm text-gray-600">${escapeHtml(patient.phone)} • ${escapeHtml(patient.email)}</div>
                    </div>
                `).join('');

                // Add click handlers to results
                document.querySelectorAll('.patient-result').forEach(result => {
                    result.addEventListener('click', function() {
                        selectPatient(this.dataset.search);
                    });
                });
            }
            
            showResults();
        }

        function selectPatient(display) {
            searchInput.value = display;
            searchHidden.value = display;
            hideResults();
        }

        function showResults() {
            resultsContainer.classList.remove('hidden');
        }

        function hideResults() {
            resultsContainer.classList.add('hidden');
        }
    }
});
</script>
@endpush
@endsection
