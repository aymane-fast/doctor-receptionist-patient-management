@extends('layouts.app')

@section('title', $patient->full_name . ' - ' . __('patients.patient_details'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Critical Allergy Alert -->
    @if($patient->allergies)
    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-6 shadow-lg">
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
            <div>
                <h4 class="text-lg font-bold text-red-800 mb-1">{{ __('patients.allergy_alert_header') }}</h4>
                <p class="text-red-700 font-medium">{{ $patient->allergies }}</p>
            </div>
        </div>
    </div>
    @endif

    <x-ui.page-header
        class="flex-wrap"
        :title="$patient->full_name"
        :description="'Patient #' . $patient->id . ' — ' . $patient->contact_label"
        icon="fa-user"
        eyebrow="Patient profile"
    >
        <x-slot:actions>
                @if(auth()->user()->isDoctor())
                <button id="aiSummaryBtn"
                        class="btn-secondary px-4 py-2">
                    <i class="fas fa-brain"></i>
                    <span>{{ __('AI Summary') }}</span>
                </button>
                @endif
                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                   class="btn-primary px-4 py-2"
                   onclick="return confirmAppointment({{ Js::from($patient->full_name) }}, {{ $patient->allergies ? 'true' : 'false' }}, {{ Js::from($patient->allergies ?? '') }})">
                    <i class="fas fa-calendar-plus"></i>
                    <span>{{ __('patients.book') }}</span>
                </a>
                @if(auth()->user()->isDoctor())
                <a href="{{ route('prescriptions.create', ['patient_id' => $patient->id]) }}" 
                   class="btn-secondary px-4 py-2">
                    <i class="fas fa-prescription-bottle"></i>
                    <span>{{ __('patients.new_prescription') }}</span>
                </a>
                @endif
                <a href="{{ route('patients.edit', $patient) }}" 
                   class="btn-secondary px-4 py-2"
                   onclick="return confirmEdit({{ Js::from($patient->full_name) }})">
                    <i class="fas fa-edit"></i>
                    <span>{{ __('patients.edit') }}</span>
                </a>
                <button onclick="history.back()" 
                        class="btn-secondary px-4 py-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('common.back') }}</span>
                </button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-patients.guardian-summary :patient="$patient" />
    <div class="section-card flex flex-wrap items-center gap-3 px-5 py-4 text-sm">
        <span class="rounded-full bg-[var(--clinic-primary-soft)] px-3 py-1 font-semibold text-[var(--clinic-primary-strong)]">
            {{ $patient->age_display }}
        </span>
        <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">
            {{ $patient->gender ? ucfirst($patient->gender) : '—' }}
        </span>
        @if($patient->appointments->where('status', 'completed')->isNotEmpty())
            <span class="text-gray-600">
                <i class="fas fa-calendar-check mr-1 text-[var(--clinic-primary)]"></i>
                Last visit: {{ $patient->appointments->where('status', 'completed')->sortByDesc('appointment_date')->first()->appointment_date->format('M d, Y') }}
            </span>
        @endif
    </div>

    <!-- Patient Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-emerald-600">{{ $patient->appointments->count() }}</p>
                    <p class="text-gray-600 font-medium">Total Visits</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-check text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-blue-600">{{ $patient->appointments->where('status', 'completed')->count() }}</p>
                    <p class="text-gray-600 font-medium">Completed</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        @if(auth()->user()->isDoctor())
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-emerald-700">{{ $patient->medicalRecords->count() }}</p>
                    <p class="text-gray-600 font-medium">Medical Records</p>
                </div>
                <div class="icon-tile w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-medical text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-orange-600">{{ $patient->medicalRecords->filter(function($record) { return $record->prescriptions->count() > 0; })->count() }}</p>
                    <p class="text-gray-600 font-medium">Prescriptions</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-prescription-bottle text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-amber-600">{{ $patient->appointments->where('status', 'scheduled')->count() }}</p>
                    <p class="text-gray-600 font-medium">Upcoming</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-red-600">{{ $patient->appointments->where('status', 'cancelled')->count() }}</p>
                    <p class="text-gray-600 font-medium">Cancelled</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Patient Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                    <h3 class="text-lg font-bold text-blue-800 flex items-center">
                        <div class="w-8 h-8 bg-blue-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-circle text-blue-700"></i>
                        </div>
                        {{ __('patients.personal_info') }}
                    </h3>
                </div>
                <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ $patient->is_pediatric ? __('Primary guardian phone') : __('patients.phone') }}</span>
                        <span class="font-semibold text-gray-900">{{ $patient->phone }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ $patient->is_pediatric ? __('Primary guardian email') : __('patients.email') }}</span>
                        <span class="font-semibold text-gray-900">{{ $patient->email ?: 'Not provided' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ __('patients.birth_date') }}</span>
                        <span class="font-semibold text-gray-900">{{ $patient->birth_date?->format('M d, Y') ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ __('patients.id_card') }}</span>
                        <span class="font-semibold text-gray-900">{{ $patient->id_card_number ?: 'Not provided' }}</span>
                    </div>
                    <div class="pt-2">
                        <span class="text-gray-600 font-medium block mb-2">{{ __('patients.address') }}</span>
                        <p class="font-semibold text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $patient->address }}</p>
                    </div>
                </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-red-50 to-red-100 px-6 py-4 border-b border-red-200">
                    <h3 class="text-lg font-bold text-red-800 flex items-center">
                        <div class="w-8 h-8 bg-red-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-heartbeat text-red-700"></i>
                        </div>
                        {{ __('patients.medical_info') }}
                    </h3>
                </div>
                <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <span class="text-gray-600 font-medium block mb-2">{{ __('patients.allergies') }}</span>
                        @if($patient->allergies)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <p class="font-semibold text-red-800">{{ $patient->allergies }}</p>
                        </div>
                        @else
                        <p class="text-gray-500 italic">{{ __('patients.none_reported') }}</p>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-600 font-medium block mb-2">{{ __('patients.chronic_conditions') }}</span>
                        @if($patient->chronic_conditions)
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                            <p class="font-semibold text-orange-800">{{ $patient->chronic_conditions }}</p>
                        </div>
                        @else
                        <p class="text-gray-500 italic">{{ __('patients.none_reported') }}</p>
                        @endif
                    </div>
                </div>
                </div>
            </div>

            <x-specialty.summary
                :fields="$patient->is_pediatric ? array_filter($specialtyFieldCatalog['patient'], fn ($field) => !in_array($field['key'], ['guardian_name', 'guardian_relationship'])) : $specialtyFieldCatalog['patient']"
                :values="$patient->specialty_data ?? []"
                title="Specialty patient details"
                :icon="$clinicSpecialty['icon']"
            />

            <!-- Emergency Contact -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-orange-200">
                    <h3 class="text-lg font-bold text-orange-800 flex items-center">
                        <div class="w-8 h-8 bg-orange-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-phone-alt text-orange-700"></i>
                        </div>
                        {{ __('patients.emergency_contact') }}
                    </h3>
                </div>
                <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ __('patients.name') }}</span>
                        <span class="font-semibold text-gray-900">{{ $patient->emergency_contact_name ?: 'Not provided' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-600 font-medium">{{ __('patients.phone') }}</span>
                        <span class="font-semibold text-gray-900">{{ $patient->emergency_contact_phone ?: 'Not provided' }}</span>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Appointments & Records -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Recent Appointments -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 px-6 py-4 border-b border-emerald-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-emerald-800 flex items-center">
                            <div class="w-8 h-8 bg-emerald-200 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-calendar text-emerald-700"></i>
                            </div>
                            {{ __('patients.recent_appointments') }}
                        </h3>
                        <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                           class="bg-emerald-200 hover:bg-emerald-300 text-emerald-800 px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                            <i class="fas fa-plus text-sm"></i>
                            <span>{{ __('patients.new_appointment') }}</span>
                        </a>
                    </div>
                </div>
                <div class="p-6">
                @if($patient->appointments->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($patient->appointments->sortByDesc('appointment_date')->take(8) as $appointment)
                        <div class="border border-gray-200 hover:border-gray-300 rounded-xl p-4 hover:bg-gray-50 transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-{{ $appointment->status === 'completed' ? 'emerald' : ($appointment->status === 'cancelled' ? 'red' : 'blue') }}-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-{{ $appointment->status === 'completed' ? 'check' : ($appointment->status === 'cancelled' ? 'times' : 'clock') }} text-{{ $appointment->status === 'completed' ? 'emerald' : ($appointment->status === 'cancelled' ? 'red' : 'blue') }}-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $appointment->appointment_date->format('M d, Y') }}</p>
                                        <p class="text-sm text-gray-600">{{ $appointment->appointment_time }} • {{ __('appointments.' . $appointment->status) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">{{ $appointment->doctor->professional_name }}</span>
                                    <a href="{{ route('appointments.show', $appointment) }}" 
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">{{ __('patients.no_appointments_yet') }}</p>
                        <p class="text-gray-400 text-sm mt-1">Book the first appointment to get started</p>
                    </div>
                @endif
                </div>
            </div>

            <!-- Medical Records -->
            @if(auth()->user()->isDoctor())
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <div class="icon-tile w-8 h-8 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            {{ __('patients.medical_records') }}
                        </h3>
                        <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" 
                           class="btn-primary px-4 py-2">
                            <i class="fas fa-plus text-sm"></i>
                            <span>{{ __('patients.new_record') }}</span>
                        </a>
                    </div>
                </div>
                <div class="p-6">
                @if($patient->medicalRecords->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($patient->medicalRecords->sortByDesc('created_at')->take(8) as $record)
                        <div class="border border-gray-200 hover:border-gray-300 rounded-xl p-4 hover:bg-gray-50 transition-all duration-200">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <p class="font-bold text-gray-900">{{ $record->created_at->format('M d, Y') }}</p>
                                        <span class="text-sm text-gray-500">{{ $record->created_at->format('H:i') }}</span>
                                        @if($record->prescriptions->count() > 0)
                                        <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full text-xs font-semibold">Rx {{ $record->prescriptions->count() }}</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2 leading-relaxed">{{ Str::limit($record->chief_complaint, 60) }}</p>
                                    @if($record->diagnosis)
                                    <div class="soft-panel rounded-lg p-2 inline-block">
                                        <p class="text-sm text-emerald-900 font-medium">{{ Str::limit($record->diagnosis, 50) }}</p>
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('medical-records.show', $record) }}" 
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($patient->medicalRecords->count() > 8)
                    <div class="text-center mt-6 pt-4 border-t border-gray-200">
                        <a href="{{ route('medical-records.index', ['patient_id' => $patient->id]) }}" 
                           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                            View all {{ $patient->medicalRecords->count() }} records →
                        </a>
                    </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-medical text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">{{ __('patients.no_medical_records_yet') }}</p>
                        <p class="text-gray-400 text-sm mt-1">Create the first medical record for this patient</p>
                    </div>
                @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- AI Summary Modal -->
<div id="aiSummaryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden animate-fade-in">
        <!-- Modal Header -->
        <div class="gradient-bg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <i class="fas fa-brain text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">AI Medical Summary</h3>
                        <p class="text-white/75 text-sm">Powered by Advanced AI</p>
                    </div>
                </div>
                <button id="closeAiModal" class="text-white hover:bg-white/20 rounded-lg p-2 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Modal Body -->
        <div id="aiSummaryContent" class="p-8 overflow-y-auto max-h-[calc(90vh-200px)]">
            <!-- Loading State -->
            <div id="aiLoading" class="text-center py-12">
                <div class="inline-block">
                    <div class="w-16 h-16 border-4 border-emerald-100 border-t-emerald-700 rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-gray-600 text-lg font-medium">Analyzing patient history...</p>
                    <p class="text-gray-400 text-sm mt-2">This may take a few seconds</p>
                </div>
            </div>
            
            <!-- Error State -->
            <div id="aiError" class="hidden text-center py-12">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-800 mb-2">Failed to Generate Summary</h4>
                <p class="text-gray-600 mb-6" id="aiErrorMessage"></p>
                <button onclick="generateAISummary()" class="btn-primary px-6 py-2">
                    <i class="fas fa-redo mr-2"></i>Try Again
                </button>
            </div>
            
            <!-- Success State -->
            <div id="aiSuccess" class="hidden">
                <div class="prose prose-lg max-w-none">
                    <div id="aiSummaryText" class="text-gray-800 leading-relaxed whitespace-pre-wrap"></div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <i class="fas fa-clock"></i>
                        <span id="aiGeneratedTime"></span>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="copyToClipboard(this)" class="btn-secondary px-4 py-2">
                            <i class="fas fa-copy"></i>
                            <span>Copy</span>
                        </button>
                        <button onclick="generateAISummary()" class="btn-primary px-4 py-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Regenerate</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Confirmation dialog for editing patient
function confirmEdit(patientName) {
    return confirm(`{{ __('patients.edit_confirmation', ['name' => '${patientName}']) }}`.replace('${patientName}', patientName));
}

// Confirmation dialog with allergy alert for appointment booking
function confirmAppointment(patientName, hasAllergies, allergies) {
    let message = `{{ __('patients.appointment_confirmation', ['name' => '${patientName}']) }}`.replace('${patientName}', patientName);
    
    if (hasAllergies) {
        message += `\n\n{{ __('patients.allergy_alert', ['allergies' => '${allergies}']) }}`.replace('${allergies}', allergies);
    }
    
    return confirm(message);
}

// AI Summary functionality
const patientId = {{ $patient->id }};
const aiSummaryModal = document.getElementById('aiSummaryModal');
const aiSummaryBtn = document.getElementById('aiSummaryBtn');
const closeAiModal = document.getElementById('closeAiModal');
const aiLoading = document.getElementById('aiLoading');
const aiError = document.getElementById('aiError');
const aiSuccess = document.getElementById('aiSuccess');
const aiSummaryText = document.getElementById('aiSummaryText');
const aiGeneratedTime = document.getElementById('aiGeneratedTime');
const aiErrorMessage = document.getElementById('aiErrorMessage');

// Open modal and generate summary
aiSummaryBtn?.addEventListener('click', function() {
    aiSummaryModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    generateAISummary();
});

// Close modal
closeAiModal.addEventListener('click', closeModal);
aiSummaryModal.addEventListener('click', function(e) {
    if (e.target === aiSummaryModal) {
        closeModal();
    }
});

function closeModal() {
    aiSummaryModal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Generate AI Summary
function generateAISummary() {
    // Reset states
    aiLoading.classList.remove('hidden');
    aiError.classList.add('hidden');
    aiSuccess.classList.add('hidden');
    
    fetch(`/patients/${patientId}/ai-summary`)
        .then(response => response.json())
        .then(data => {
            aiLoading.classList.add('hidden');
            
            if (data.success) {
                aiSuccess.classList.remove('hidden');
                aiSummaryText.innerHTML = formatSummary(data.summary);
                aiGeneratedTime.textContent = `Generated: ${new Date(data.generated_at).toLocaleString()}`;
            } else {
                showError(data.error || 'An unexpected error occurred');
            }
        })
        .catch(error => {
            aiLoading.classList.add('hidden');
            showError('Network error. Please check your connection and try again.');
            console.error('AI Summary Error:', error);
        });
}

function showError(message) {
    aiError.classList.remove('hidden');
    aiErrorMessage.textContent = message;
}

function formatSummary(text) {
    // The model output is not trusted HTML. Escape it before applying the
    // small, explicit Markdown subset used for presentation.
    const escaped = String(text ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);

    return escaped
        .replace(/\*\*(.*?)\*\*/g, '<strong class="text-gray-900">$1</strong>')
        .replace(/### (.*?)$/gm, '<h3 class="text-xl font-bold text-gray-900 mt-6 mb-3">$1</h3>')
        .replace(/## (.*?)$/gm, '<h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">$1</h2>')
        .replace(/# (.*?)$/gm, '<h1 class="text-3xl font-bold text-gray-900 mt-8 mb-4">$1</h1>')
        .replace(/^\* (.*?)$/gm, '<li class="ml-4 mb-2">$1</li>')
        .replace(/^\d+\. (.*?)$/gm, '<li class="ml-4 mb-2 list-decimal">$1</li>')
        .replace(/\n\n/g, '</p><p class="mb-4">')
        .replace(/^(?!<[h|l|p])(.+)$/gm, '<p class="mb-4">$1</p>');
}

function copyToClipboard(btn) {
    const text = aiSummaryText.innerText;
    if (!navigator.clipboard?.writeText) {
        showError('Clipboard access is unavailable. Select and copy the summary manually.');
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        // Show success feedback
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i><span>Copied!</span>';
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
        btn.classList.remove('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700');
        }, 2000);
    }).catch(() => showError('Clipboard access was denied. Select and copy the summary manually.'));
}

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !aiSummaryModal.classList.contains('hidden')) {
        closeModal();
    }
});
</script>
@endpush
@endsection
