@extends('layouts.app')

@section('title', __('medical_records.title'))

@section('content')
<div class="space-y-8">
    <!-- Modern Header -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-2xl flex items-center justify-center animate-float">
                    <i class="fas fa-file-medical text-emerald-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('medical_records.title') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">{{ __('medical_records.complete_medical_history') }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('medical-records.create') }}" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 deep-shadow hover:shadow-xl flex items-center space-x-3">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('medical_records.new_record') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Search and Filters -->
    <div class="glass-effect rounded-3xl p-6 modern-shadow relative z-[10001]">
        <form method="GET" action="{{ route('medical-records.index') }}" class="space-y-4">
            <!-- Search Bar and Date Filter -->
            <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
                <div class="flex-1 w-full relative z-[10000]">
                    <label for="patient_search" class="sr-only">{{ __('medical_records.search_patients') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="patient_search" 
                               placeholder="{{ __('medical_records.search_patients') }}"
                               class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm placeholder-gray-500 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all duration-200"
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
                <!-- Date Filter -->
                <div class="w-full md:w-auto">
                    <label for="date" class="sr-only">{{ __('medical_records.filter_by_date') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-calendar text-gray-400"></i>
                        </div>
                        <input type="date" 
                               id="date" 
                               name="date" 
                               value="{{ request('date') }}"
                               class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all duration-200">
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-search"></i>
                        <span>{{ __('medical_records.search') }}</span>
                    </button>
                    @if(request('search') || request('date'))
                    <a href="{{ route('medical-records.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>{{ __('medical_records.clear') }}</span>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Modern Medical Records Grid -->
    @if($hasSearchQuery)
    <div class="glass-effect rounded-3xl modern-shadow overflow-hidden relative z-0">
        <div class="p-6">
            @if($medicalRecords->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($medicalRecords as $record)
                    <div class="bg-white border-2 border-gray-100 rounded-2xl p-6 card-hover modern-shadow group">
                        <!-- Patient Header -->
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center group-hover:from-emerald-200 group-hover:to-emerald-300 transition-colors">
                                    <i class="fas fa-user text-emerald-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $record->patient->first_name }} {{ $record->patient->last_name }}</h3>
                                    <p class="text-sm text-gray-500 flex items-center space-x-2">
                                        <i class="fas fa-id-card w-3"></i>
                                        <span>{{ $record->patient->id_card_number }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span>{{ $record->patient->age }} years</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('medical-records.show', $record) }}" class="w-9 h-9 bg-emerald-100 hover:bg-emerald-200 rounded-xl flex items-center justify-center transition-colors group/btn">
                                    <i class="fas fa-eye text-emerald-600 text-sm group-hover/btn:scale-110 transition-transform"></i>
                                </a>
                                <a href="{{ route('medical-records.edit', $record) }}" class="w-9 h-9 bg-blue-100 hover:bg-blue-200 rounded-xl flex items-center justify-center transition-colors group/btn">
                                    <i class="fas fa-edit text-blue-600 text-sm group-hover/btn:scale-110 transition-transform"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Visit Information -->
                        <div class="space-y-4 mb-6">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex items-center text-sm text-gray-600 bg-gray-50 rounded-xl p-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-calendar text-blue-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($record->visit_date)->format('M d, Y') }}</p>
                                        <p class="text-xs text-gray-500">Visit Date</p>
                                    </div>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 bg-gray-50 rounded-xl p-3">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-user-md text-purple-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Dr. {{ $record->doctor->name }}</p>
                                        <p class="text-xs text-gray-500">Doctor</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Visit Type Badge -->
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-medium
                                    @if($record->visit_type == 'consultation') bg-blue-100 text-blue-800
                                    @elseif($record->visit_type == 'follow-up') bg-emerald-100 text-emerald-800
                                    @elseif($record->visit_type == 'emergency') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    <div class="w-2 h-2 rounded-full mr-2
                                        @if($record->visit_type == 'consultation') bg-blue-500
                                        @elseif($record->visit_type == 'follow-up') bg-emerald-500
                                        @elseif($record->visit_type == 'emergency') bg-red-500
                                        @else bg-gray-500
                                        @endif"></div>
                                    @if($record->visit_type == 'consultation') Consultation
                                    @elseif($record->visit_type == 'follow-up') Follow-up
                                    @elseif($record->visit_type == 'emergency') Emergency
                                    @else {{ ucfirst($record->visit_type) }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Diagnosis Preview -->
                        @if($record->diagnosis)
                        <div class="mb-6 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fas fa-stethoscope text-blue-600 text-xs"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-blue-800 mb-1">Diagnosis</h4>
                                    <p class="text-sm text-blue-700 leading-relaxed">{{ Str::limit($record->diagnosis, 100) }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Vital Signs (if available) -->
                        @if($record->blood_pressure || $record->heart_rate || $record->temperature || $record->weight)
                        <div class="mb-6">
                            <div class="grid grid-cols-2 gap-2">
                                @if($record->blood_pressure)
                                <div class="bg-red-50 rounded-lg p-3 text-center border border-red-100">
                                    <div class="w-6 h-6 bg-red-200 rounded-full flex items-center justify-center mx-auto mb-1">
                                        <i class="fas fa-heartbeat text-red-600 text-xs"></i>
                                    </div>
                                    <p class="text-xs text-red-600 font-medium">Blood Pressure</p>
                                    <p class="text-sm font-bold text-red-800">{{ $record->blood_pressure }}</p>
                                </div>
                                @endif
                                @if($record->heart_rate)
                                <div class="bg-pink-50 rounded-lg p-3 text-center border border-pink-100">
                                    <div class="w-6 h-6 bg-pink-200 rounded-full flex items-center justify-center mx-auto mb-1">
                                        <i class="fas fa-heart text-pink-600 text-xs"></i>
                                    </div>
                                    <p class="text-xs text-pink-600 font-medium">Heart Rate</p>
                                    <p class="text-sm font-bold text-pink-800">{{ $record->heart_rate }} bpm</p>
                                </div>
                                @endif
                                @if($record->temperature)
                                <div class="bg-orange-50 rounded-lg p-3 text-center border border-orange-100">
                                    <div class="w-6 h-6 bg-orange-200 rounded-full flex items-center justify-center mx-auto mb-1">
                                        <i class="fas fa-thermometer-half text-orange-600 text-xs"></i>
                                    </div>
                                    <p class="text-xs text-orange-600 font-medium">Temperature</p>
                                    <p class="text-sm font-bold text-orange-800">{{ $record->temperature }}°F</p>
                                </div>
                                @endif
                                @if($record->weight)
                                <div class="bg-indigo-50 rounded-lg p-3 text-center border border-indigo-100">
                                    <div class="w-6 h-6 bg-indigo-200 rounded-full flex items-center justify-center mx-auto mb-1">
                                        <i class="fas fa-weight text-indigo-600 text-xs"></i>
                                    </div>
                                    <p class="text-xs text-indigo-600 font-medium">Weight</p>
                                    <p class="text-sm font-bold text-indigo-800">{{ $record->weight }} kg</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Footer -->
                        <div class="pt-4 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                    <span class="text-xs text-gray-500 font-medium">
                                        Created {{ $record->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                @if($record->appointment)
                                <a href="{{ route('appointments.show', $record->appointment) }}" 
                                   class="bg-gradient-to-r from-blue-100 to-blue-50 hover:from-blue-200 hover:to-blue-100 text-blue-800 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center space-x-2 border border-blue-200">
                                    <i class="fas fa-calendar"></i>
                                    <span>View Appointment</span>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Modern Pagination -->
                <div class="mt-8 flex justify-center">
                    {{ $medicalRecords->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6 animate-float">
                        <i class="fas fa-file-medical text-gray-400 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">
                        {{ __('medical_records.no_search_results') }}
                    </h3>
                    <p class="text-gray-500 text-lg mb-8 max-w-md mx-auto">
                        @if(request('search'))
                            {{ __('medical_records.no_search_results_desc', ['search' => request('search')]) }}
                        @elseif(request('date'))
                            {{ __('medical_records.no_records_for_date') }}
                        @else
                            {{ __('medical_records.no_records_found_desc') }}
                        @endif
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="{{ route('medical-records.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>{{ __('medical_records.clear_search') }}</span>
                        </a>
                        <a href="{{ route('medical-records.create') }}" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2 deep-shadow">
                            <i class="fas fa-plus"></i>
                            <span>{{ __('medical_records.create_medical_record') }}</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @else
    <!-- Initial State - No Search Performed -->
    <div class="glass-effect rounded-3xl modern-shadow overflow-hidden relative z-0">
        <div class="p-6">
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-3xl flex items-center justify-center mx-auto mb-6 animate-float">
                    <i class="fas fa-search text-emerald-600 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">
                    {{ __('medical_records.search_records_title') }}
                </h3>
                <p class="text-gray-500 text-lg mb-8 max-w-md mx-auto">
                    {{ __('medical_records.search_records_desc') }}
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('medical-records.create') }}" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2 deep-shadow">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('medical_records.create_medical_record') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('patient_search');
    const searchHidden = document.getElementById('search');
    const resultsContainer = document.getElementById('patient_results');
    let searchTimeout;

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
            fetch(`{{ route('api.medical-records.patients.search') }}?q=${encodeURIComponent(query)}`)
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
                    <div class="patient-result p-3 hover:bg-emerald-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                         data-search="${patient.display}">
                        <div class="font-medium text-gray-900">${patient.name}</div>
                        <div class="text-sm text-gray-600">${patient.phone} • ${patient.email}</div>
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
@endsection
