@extends('layouts.app')

@section('title', $patient->full_name . ' - ' . __('patients.patient_details'))

@section('content')
<div class="space-y-6">
    <!-- Critical Allergy Alert -->
    @if($patient->allergies)
    <div class="bg-red-50 border-2 border-red-300 rounded-xl p-4 shadow-lg">
        <div class="flex items-center justify-center space-x-3">
            <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
            <div class="text-center">
                <span class="text-lg font-bold text-red-700">⚠️ {{ __('patients.allergy_alert') }}:</span>
                <span class="text-red-600 font-medium ml-2">{{ $patient->allergies }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Compact Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $patient->full_name }}</h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-lg text-sm font-medium">{{ $patient->id_card_number }}</span>
                        <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded-lg text-sm font-medium">{{ $patient->age }} years</span>
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-lg text-sm font-medium">{{ ucfirst($patient->gender) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                   onclick="return confirmAppointment('{{ $patient->full_name }}', {{ $patient->allergies ? 'true' : 'false' }}, '{{ $patient->allergies }}')">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Book</span>
                </a>
                <a href="{{ route('patients.edit', $patient) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                   onclick="return confirmEdit('{{ $patient->full_name }}')">
                    <i class="fas fa-edit"></i>
                    <span>Edit</span>
                </a>
                <button onclick="history.back()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('common.back') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Patient Info -->
        <div class="lg:col-span-1">
            <!-- Personal Information -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-circle text-blue-600 mr-2"></i>
                    Personal Info
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('patients.phone') }}:</span>
                        <span class="font-medium">{{ $patient->phone }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('patients.email') }}:</span>
                        <span class="font-medium">{{ $patient->email ?: __('patients.not_provided') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Birth Date:</span>
                        <span class="font-medium">{{ $patient->birth_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">ID Card:</span>
                        <span class="font-medium">{{ $patient->id_card_number ?: __('patients.not_provided') }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <span class="text-gray-600 block text-sm mb-1">Address:</span>
                        <span class="font-medium text-sm">{{ $patient->address }}</span>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-heartbeat text-red-600 mr-2"></i>
                    Medical Info
                </h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-600 block">Allergies:</span>
                        <span class="font-medium {{ $patient->allergies ? 'text-red-600' : 'text-gray-500' }}">
                            {{ $patient->allergies ?: __('patients.none_reported') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600 block">Chronic Conditions:</span>
                        <span class="font-medium">{{ $patient->chronic_conditions ?: __('patients.none_reported') }}</span>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-phone-alt text-orange-600 mr-2"></i>
                    {{ __('patients.emergency_contact') }}
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('patients.name') }}:</span>
                        <span class="font-medium">{{ $patient->emergency_contact_name ?: __('patients.not_provided') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('patients.phone') }}:</span>
                        <span class="font-medium">{{ $patient->emergency_contact_phone ?: __('patients.not_provided') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Appointments & Records -->
        <div class="lg:col-span-2">
            <!-- Recent Appointments -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-calendar text-green-600 mr-2"></i>
                        {{ __('patients.recent_appointments') }}
                    </h3>
                    <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                       class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + New Appointment
                    </a>
                </div>
                @if($patient->appointments->count() > 0)
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($patient->appointments->take(5) as $appointment)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-{{ $appointment->status === 'completed' ? 'green' : ($appointment->status === 'cancelled' ? 'red' : 'blue') }}-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-{{ $appointment->status === 'completed' ? 'check' : ($appointment->status === 'cancelled' ? 'times' : 'clock') }} text-{{ $appointment->status === 'completed' ? 'green' : ($appointment->status === 'cancelled' ? 'red' : 'blue') }}-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $appointment->appointment_date->format('M d, Y') }}</p>
                                        <p class="text-sm text-gray-600">{{ $appointment->appointment_time }} - {{ __('appointments.' . $appointment->status) }}</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded">Dr. {{ $appointment->doctor->name }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">{{ __('patients.no_appointments_yet') }}</p>
                    </div>
                @endif
            </div>

            <!-- Medical Records -->
            @if(auth()->user()->isDoctor())
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-file-medical text-purple-600 mr-2"></i>
                        {{ __('patients.medical_records') }}
                    </h3>
                    <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" 
                       class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + New Record
                    </a>
                </div>
                @if($patient->medicalRecords->count() > 0)
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($patient->medicalRecords->take(5) as $record)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $record->created_at->format('M d, Y') }}</p>
                                    <p class="text-sm text-gray-600">{{ Str::limit($record->chief_complaint, 50) }}</p>
                                    @if($record->diagnosis)
                                    <p class="text-xs text-purple-600 mt-1">{{ Str::limit($record->diagnosis, 60) }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('medical-records.show', $record) }}" 
                                   class="text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-file-medical text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">{{ __('patients.no_medical_records_yet') }}</p>
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Confirmation dialog for editing patient
function confirmEdit(patientName) {
    return confirm(`{{ __('patients.confirm_edit_prefix') }} "${patientName}"?\n\n{{ __('patients.edit_warning') }}`);
}

// Confirmation dialog with allergy alert for appointment booking
function confirmAppointment(patientName, hasAllergies, allergies) {
    let message = `{{ __('patients.book_appointment_for') }} "${patientName}"?`;
    
    if (hasAllergies) {
        message += `\n\n⚠️ {{ __('patients.allergy_alert') }} ⚠️\n{{ __('patients.patient_has_allergies') }}: ${allergies}\n\n{{ __('patients.notify_medical_staff') }}`;
    }
    
    return confirm(message);
}
</script>
@endpush
@endsection
