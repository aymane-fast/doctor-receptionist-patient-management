@extends('layouts.app')

@section('title', __('current_patient.title'))

@section('content')
<div class="space-y-6">
    <!-- Modern Header -->
    <div class="glass-effect rounded-2xl p-6 modern-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center animate-float">
                    <i class="fas fa-user-md text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('current_patient.title') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-1">{{ __('current_patient.subtitle') }}</p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left text-sm"></i>
                <span>{{ __('current_patient.back_to_dashboard') }}</span>
            </a>
        </div>
    </div>

    @if(!$currentAppointment)
        <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6 animate-float">
                    <i class="fas fa-user-clock text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ __('current_patient.no_current_patient') }}</h3>
                <p class="text-gray-500 text-lg mb-8 max-w-md mx-auto">
                    {{ __('current_patient.no_patient_assigned') }}
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('appointments.index') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-calendar-alt"></i>
                        <span>{{ __('current_patient.view_appointments') }}</span>
                    </a>
                </div>
            </div>
        </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Patient Overview -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Patient Info Card -->
            <div class="glass-effect rounded-2xl p-6 modern-shadow">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-user text-emerald-600 text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 mb-1">{{ __('current_patient.current_patient') }}</div>
                        <div class="text-xl font-bold text-gray-900">{{ $patient->full_name }}</div>
                        <div class="text-emerald-600 font-medium">{{ $patient->id_card_number }}</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-amber-50 rounded-xl p-3 border border-amber-200">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-birthday-cake text-amber-600 text-sm"></i>
                            <span class="text-amber-800 font-medium text-sm">{{ $patient->age }} {{ __('current_patient.years') }}</span>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3 border border-purple-200">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-venus-mars text-purple-600 text-sm"></i>
                            <span class="text-purple-800 font-medium text-sm">{{ ucfirst($patient->gender) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-blue-50 rounded-xl p-3 border border-blue-200 mb-4">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-clock text-blue-600"></i>
                        <span class="text-blue-800 font-medium">{{ __('current_patient.appointment') }}: {{ $currentAppointment->appointment_time->format('g:i A') }}</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('patients.show', $patient) }}" class="w-full bg-gradient-to-r from-blue-100 to-blue-50 hover:from-blue-200 hover:to-blue-100 text-blue-800 px-4 py-3 rounded-xl font-medium transition-all duration-200 flex items-center justify-center space-x-2 border border-blue-200">
                        <i class="fas fa-history"></i>
                        <span>{{ __('current_patient.view_full_patient_history') }}</span>
                    </a>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="glass-effect rounded-2xl p-6 modern-shadow">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('current_patient.quick_actions') }}</h3>
                </div>
                
                <div class="space-y-3">
                    <a href="{{ route('medical-records.create', ['patient_id' => $patient->id, 'appointment_id' => $currentAppointment->id]) }}" 
                       class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-3 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-file-medical-alt"></i>
                        <span>{{ __('current_patient.new_medical_record') }}</span>
                    </a>
                    
                    <a href="{{ route('prescriptions.create', ['patient_id' => $patient->id]) }}" 
                       class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-4 py-3 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-prescription-bottle-alt"></i>
                        <span>{{ __('current_patient.write_prescription') }}</span>
                    </a>

                    <!-- Quick Follow-up Button -->
                    <button onclick="openFollowUpModal()" 
                            class="w-full bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white px-4 py-3 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-calendar-plus"></i>
                        <span>{{ __('current_patient.schedule_follow_up') }}</span>
                    </button>
                    
                    <form action="{{ route('appointments.mark-current-done') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl font-medium transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ __('current_patient.complete_visit_next_patient') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Timeline and Recent Items -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Recent Appointments -->
            <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-check text-white text-sm"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">{{ __('current_patient.recent_appointments') }}</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    @php($recentAppointments = $patient->appointments()->with('doctor')->latest('appointment_date')->limit(5)->get())
                    @if($recentAppointments->count())
                        <div class="space-y-3">
                            @foreach($recentAppointments as $ap)
                            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow {{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-calendar text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $ap->appointment_date->format('M j, Y') }} at {{ $ap->appointment_time->format('g:i A') }}</div>
                                            <div class="text-sm text-gray-600">Dr. {{ $ap->doctor->name }}</div>
                                            <div class="flex items-center space-x-2 mt-1">
                                                @if($ap->status === 'completed')
                                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-lg">{{ __('current_patient.completed') }}</span>
                                                @elseif($ap->status === 'in_progress')
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-lg">{{ __('current_patient.in_progress') }}</span>
                                                @elseif($ap->status === 'cancelled')
                                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-lg">{{ __('current_patient.cancelled') }}</span>
                                                @else
                                                    <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg">{{ __('current_patient.scheduled') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('appointments.show', $ap) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                        <i class="fas fa-eye text-sm"></i>
                                        <span>{{ __('current_patient.view') }}</span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500">{{ __('current_patient.no_recent_appointments') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Medical Records -->
            <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-medical text-white text-sm"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">{{ __('current_patient.medical_records') }}</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    @php($recentRecords = $patient->medicalRecords()->with('doctor')->latest('visit_date')->limit(5)->get())
                    @if($recentRecords->count())
                        <div class="space-y-3">
                            @foreach($recentRecords as $rec)
                            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow {{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-stethoscope text-emerald-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $rec->visit_date->format('M j, Y') }}</div>
                                            <div class="text-sm text-gray-600">Dr. {{ $rec->doctor->name }}</div>
                                            @if($rec->diagnosis)
                                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($rec->diagnosis, 50) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ route('medical-records.show', $rec) }}" class="bg-emerald-100 hover:bg-emerald-200 text-emerald-800 px-3 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                        <i class="fas fa-eye text-sm"></i>
                                        <span>{{ __('current_patient.view') }}</span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-file-medical text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500">{{ __('current_patient.no_medical_records') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Prescriptions -->
            <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-prescription-bottle-alt text-white text-sm"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">{{ __('current_patient.recent_prescriptions') }}</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    @php($recentRx = $patient->prescriptions()->with('doctor')->latest('prescribed_date')->limit(5)->get())
                    @if($recentRx->count())
                        <div class="space-y-3">
                            @foreach($recentRx as $rx)
                            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow {{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-pills text-purple-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $rx->medication_name }}</div>
                                            <div class="text-sm text-purple-600 font-medium">{{ $rx->dosage }}</div>
                                            <div class="text-xs text-gray-500">{{ $rx->prescribed_date->format('M j, Y') }} • Dr. {{ $rx->doctor->name }}</div>
                                        </div>
                                    </div>
                                    <a href="{{ route('prescriptions.show', $rx) }}" class="bg-purple-100 hover:bg-purple-200 text-purple-800 px-3 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                        <i class="fas fa-eye text-sm"></i>
                                        <span>{{ __('current_patient.view') }}</span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-prescription-bottle-alt text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500">{{ __('current_patient.no_prescriptions') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Follow-up Appointment Modal -->
@if($currentAppointment)
<div id="followUpModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4 modern-shadow">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">{{ __('current_patient.schedule_follow_up') }}</h3>
            <button onclick="closeFollowUpModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('appointments.create-follow-up', $patient->id) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="current_appointment_id" value="{{ $currentAppointment->id }}">
            
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200 mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-blue-900">{{ $patient->full_name }}</div>
                        <div class="text-sm text-blue-600">{{ $patient->id_card_number }}</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="follow_up_number" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('current_patient.time_amount') }}
                    </label>
                    <input type="number" 
                           id="follow_up_number" 
                           name="follow_up_number" 
                           min="1" 
                           max="365" 
                           value="7"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>
                <div>
                    <label for="follow_up_unit" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('current_patient.time_unit') }}
                    </label>
                    <select id="follow_up_unit" 
                            name="follow_up_unit" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="days">{{ __('current_patient.days') }}</option>
                        <option value="weeks">{{ __('current_patient.weeks') }}</option>
                        <option value="months">{{ __('current_patient.months') }}</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="follow_up_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('current_patient.reason_optional') }}
                </label>
                <textarea id="follow_up_reason" 
                          name="follow_up_reason" 
                          rows="3"
                          placeholder="{{ __('current_patient.follow_up_reason_placeholder') }}"
                          class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
            </div>

            <div class="bg-amber-50 rounded-xl p-3 border border-amber-200">
                <div class="flex items-start space-x-2">
                    <i class="fas fa-info-circle text-amber-600 mt-0.5"></i>
                    <div class="text-sm text-amber-800">
                        <div class="font-medium">{{ __('current_patient.auto_scheduling') }}</div>
                        <div>{{ __('current_patient.auto_scheduling_description') }}</div>
                    </div>
                </div>
            </div>

            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeFollowUpModal()"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-3 rounded-xl font-medium transition-colors">
                    {{ __('current_patient.cancel') }}
                </button>
                <button type="submit" 
                        class="flex-1 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white px-4 py-3 rounded-xl font-medium transition-all duration-200">
                    {{ __('current_patient.schedule_appointment') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function openFollowUpModal() {
    document.getElementById('followUpModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFollowUpModal() {
    document.getElementById('followUpModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('followUpModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeFollowUpModal();
    }
});
</script>

@endsection


