@extends('layouts.app')

@section('title', __('prescriptions.new_prescription'))

@section('content')
<div class="space-y-6">
    <!-- Modern Header -->
    <div class="glass-effect rounded-2xl p-6 modern-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-prescription-bottle-alt text-purple-600 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('prescriptions.new_prescription') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-1">{{ __('prescriptions.prescribe_medications_subtitle') }}</p>
                </div>
            </div>
            <a href="{{ route('prescriptions.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left text-sm"></i>
                <span>{{ __('prescriptions.back_to_prescriptions') }}</span>
            </a>
        </div>
    </div>

        <!-- Prescription Form -->
        <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
            <form action="{{ route('prescriptions.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Patient and Basic Info -->
                <div class="p-6 pb-0">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('prescriptions.patient_prescription_info') }}</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Patient Selection with Autocomplete -->
                        <div>
                            <label for="patient_search" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('prescriptions.patient_name') }} <span class="text-red-500">*</span>
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

                        <!-- Prescribed Date -->
                        <div>
                            <label for="prescribed_date" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('prescriptions.prescribed_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="prescribed_date" name="prescribed_date" 
                                   value="{{ old('prescribed_date', date('Y-m-d')) }}" required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('prescribed_date') border-red-500 @enderror">
                            @error('prescribed_date')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                <!-- Medication Items -->
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-pills text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('prescriptions.medications') }}</h3>
                    </div>
                    
                    <div id="items" class="space-y-4">
                        <div class="item bg-white border-2 border-gray-200 rounded-xl p-4">
                            <div class="grid md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.medication_name') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="items[0][medication_name]" required 
                                           class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200" 
                                           placeholder="{{ __('prescriptions.medication_placeholder') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.dosage') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="items[0][dosage]" required 
                                           class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200" 
                                           placeholder="{{ __('prescriptions.dosage_placeholder') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.frequency') }} <span class="text-red-500">*</span></label>
                                    <select name="items[0][frequency]" required 
                                            class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200">
                                        <option value="">{{ __('prescriptions.select_frequency') }}</option>
                                        <option>{{ __('prescriptions.once_daily') }}</option>
                                        <option>{{ __('prescriptions.twice_daily') }}</option>
                                        <option>{{ __('prescriptions.three_times_daily') }}</option>
                                        <option>{{ __('prescriptions.four_times_daily') }}</option>
                                        <option>{{ __('prescriptions.every_4_hours') }}</option>
                                        <option>{{ __('prescriptions.every_6_hours') }}</option>
                                        <option>{{ __('prescriptions.every_8_hours') }}</option>
                                        <option>{{ __('prescriptions.every_12_hours') }}</option>
                                        <option>{{ __('prescriptions.as_needed') }}</option>
                                        <option>{{ __('prescriptions.weekly') }}</option>
                                        <option>{{ __('prescriptions.other') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.duration_days') }}</label>
                                    <input type="number" name="items[0][duration_days]" min="1" 
                                           class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200" 
                                           placeholder="7">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.instructions') }}</label>
                                <input type="text" name="items[0][instructions]" 
                                       class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200" 
                                       placeholder="{{ __('prescriptions.instructions_placeholder') }}">
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" id="add-item" class="mt-4 bg-gradient-to-r from-purple-100 to-purple-50 hover:from-purple-200 hover:to-purple-100 text-purple-800 px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2 border border-purple-200">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('prescriptions.add_another_medication') }}</span>
                    </button>
                </div>

                <!-- Form Actions -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-b-2xl">
                    <div class="flex flex-col sm:flex-row items-center justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('prescriptions.index') }}" 
                           class="w-full sm:w-auto bg-gradient-to-r from-gray-300 to-gray-400 hover:from-gray-400 hover:to-gray-500 text-gray-800 px-6 py-3 rounded-xl font-medium transition-all duration-200 text-center">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>{{ __('prescriptions.create_prescription') }}</span>
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

    // Patient Search Autocomplete
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideResults();
            if (!patientIdInput.value) {
                // Only clear if no patient was previously selected
                patientIdInput.value = '';
            }
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

    function searchPatients(query) {
        fetch(`{{ route('api.prescriptions.patients.search') }}?q=${encodeURIComponent(query)}`)
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
        hideResults();
    }

    function showResults() {
        resultsContainer.classList.remove('hidden');
    }

    function hideResults() {
        resultsContainer.classList.add('hidden');
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

// Original medication items functionality
let itemIndex = 1;
document.getElementById('add-item').addEventListener('click', () => {
  const wrapper = document.createElement('div');
  wrapper.className = 'item bg-white border-2 border-gray-200 rounded-xl p-4';
  wrapper.innerHTML = `
    <div class="grid md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.medication_name') }} <span class="text-red-500">*</span></label>
        <input type="text" name="items[${itemIndex}][medication_name]" required class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200" placeholder="{{ __('prescriptions.medication_placeholder') }}">
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.dosage') }} <span class="text-red-500">*</span></label>
        <input type="text" name="items[${itemIndex}][dosage]" required class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200" placeholder="{{ __('prescriptions.dosage_placeholder') }}">
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.frequency') }} <span class="text-red-500">*</span></label>
        <select name="items[${itemIndex}][frequency]" required class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200">
          <option value="">{{ __('prescriptions.select_frequency') }}</option>
          <option>{{ __('prescriptions.once_daily') }}</option>
          <option>{{ __('prescriptions.twice_daily') }}</option>
          <option>{{ __('prescriptions.three_times_daily') }}</option>
          <option>{{ __('prescriptions.four_times_daily') }}</option>
          <option>{{ __('prescriptions.every_4_hours') }}</option>
          <option>{{ __('prescriptions.every_6_hours') }}</option>
          <option>{{ __('prescriptions.every_8_hours') }}</option>
          <option>{{ __('prescriptions.every_12_hours') }}</option>
          <option>{{ __('prescriptions.as_needed') }}</option>
          <option>{{ __('prescriptions.weekly') }}</option>
          <option>{{ __('prescriptions.other') }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.duration_days') }}</label>
        <input type="number" name="items[${itemIndex}][duration_days]" min="1" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200" placeholder="7">
      </div>
    </div>
    <div class="mt-3">
      <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('prescriptions.instructions') }}</label>
      <input type="text" name="items[${itemIndex}][instructions]" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-purple-500 transition-all duration-200" placeholder="{{ __('prescriptions.instructions_placeholder') }}">
    </div>
  `;
  document.getElementById('items').appendChild(wrapper);
  itemIndex++;
});
});
</script>
</div>
@endsection
