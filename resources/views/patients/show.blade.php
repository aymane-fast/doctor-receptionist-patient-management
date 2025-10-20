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

    <!-- Patient Header -->
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-2xl"></i>
                    </div>
                    @if($patient->appointments->where('status', 'completed')->isNotEmpty())
                    <div class="absolute -bottom-2 -right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                        {{ $patient->appointments->count() }}
                    </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $patient->full_name }}</h1>
                    <div class="flex items-center space-x-4 mb-2">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">ID: {{ $patient->id_card_number }}</span>
                        <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-sm font-semibold">{{ $patient->age }} years old</span>
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-semibold">{{ ucfirst($patient->gender) }}</span>
                    </div>
                    <div class="flex items-center space-x-4 text-gray-600">
                        <span class="flex items-center space-x-2">
                            <i class="fas fa-phone text-blue-500"></i>
                            <span class="font-medium">{{ $patient->phone }}</span>
                        </span>
                        @if($patient->appointments->where('status', 'completed')->isNotEmpty())
                        <span class="flex items-center space-x-2">
                            <i class="fas fa-calendar text-green-500"></i>
                            <span>Last visit: {{ $patient->appointments->where('status', 'completed')->sortByDesc('appointment_date')->first()->appointment_date->format('M d, Y') }}</span>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                   class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                   onclick="return confirmAppointment('{{ $patient->full_name }}', {{ $patient->allergies ? 'true' : 'false' }}, '{{ $patient->allergies }}')">
                    <i class="fas fa-calendar-plus"></i>
                    <span>{{ __('patients.book') }}</span>
                </a>
                <a href="{{ route('prescriptions.create', ['patient_id' => $patient->id]) }}" 
                   class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-prescription-bottle"></i>
                    <span>{{ __('patients.new_prescription') }}</span>
                </a>
                <a href="{{ route('patients.edit', $patient) }}" 
                   class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                   onclick="return confirmEdit('{{ $patient->full_name }}')">
                    <i class="fas fa-edit"></i>
                    <span>{{ __('patients.edit') }}</span>
</a>
                <a href="{{ route('patients.index') }}" 
                   class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('patients.back') }}</span>
                </a>
            </div>
        </div>
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
                    <p class="text-3xl font-bold text-purple-600">{{ $patient->medicalRecords->count() }}</p>
                    <p class="text-gray-600 font-medium">Medical Records</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-medical text-purple-600 text-xl"></i>
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
                        <span class="text-gray-600 font-medium">{{ __('patients.phone') }}</span>
                        <span class="font-semibold text-gray-900">{{ $patient->phone }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ __('patients.email') }}</span>
                        <span class="font-semibold text-gray-900">{{ $patient->email ?: 'Not provided' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ __('patients.birth_date') }}</span>
                        <span class="font-semibold text-gray-900">{{ $patient->birth_date->format('M d, Y') }}</span>
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
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Dr. {{ $appointment->doctor->name }}</span>
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
                <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-purple-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-purple-800 flex items-center">
                            <div class="w-8 h-8 bg-purple-200 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-file-medical text-purple-700"></i>
                            </div>
                            {{ __('patients.medical_records') }}
                        </h3>
                        <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" 
                           class="bg-purple-200 hover:bg-purple-300 text-purple-800 px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
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
                                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-2 inline-block">
                                        <p class="text-sm text-purple-800 font-medium">{{ Str::limit($record->diagnosis, 50) }}</p>
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
</script>
@endpush
@endsection
