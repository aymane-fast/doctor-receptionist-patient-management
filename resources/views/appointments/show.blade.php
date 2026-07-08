@extends('layouts.app')

@section('title', __('appointments.appointment_details'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Appointment Header -->
    <div class="section-card p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 brand-mark rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-calendar-check text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('appointments.appointment_details') }}</h1>
                    <p class="text-gray-600">Appointment #{{ $appointment->id }} • {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <button onclick="history.back()" 
                   class="btn-secondary px-4 py-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('common.back') }}</span>
                </button>
                <a href="{{ route('appointments.edit', $appointment) }}" 
                   class="btn-primary px-4 py-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-edit"></i>
                    <span>{{ __('appointments.edit') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Appointment Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Patient Info -->
            <div class="section-card overflow-hidden">
                <div class="soft-panel px-6 py-4 border-b border-teal-100">
                    <h2 class="text-lg font-bold text-teal-900 flex items-center">
                        <div class="w-8 h-8 icon-tile rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user"></i>
                        </div>
                        {{ __('appointments.patient_information') }}
                    </h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="w-12 h-12 brand-mark rounded-lg flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-1">
                                <a href="{{ route('patients.show', $appointment->patient) }}" class="hover:text-teal-700 transition-colors">
                                    {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}
                                </a>
                            </h3>
                            <div class="flex flex-wrap gap-4 text-gray-600">
                                <span class="flex items-center space-x-1">
                                    <i class="fas fa-phone text-sm"></i>
                                    <span>{{ $appointment->patient->phone }}</span>
                                </span>
                                @if($appointment->patient->email)
                                <span class="flex items-center space-x-1">
                                    <i class="fas fa-envelope text-sm"></i>
                                    <span>{{ $appointment->patient->email }}</span>
                                </span>
                                @endif
                                <span class="flex items-center space-x-1">
                                    <i class="fas fa-birthday-cake text-sm"></i>
                                    <span>{{ $appointment->patient->age !== null ? $appointment->patient->age . ' years old' : 'Age unknown' }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctor Info -->
            <div class="section-card overflow-hidden">
                <div class="soft-panel px-6 py-4 border-b border-teal-100">
                    <h2 class="text-lg font-bold text-teal-900 flex items-center">
                        <div class="w-8 h-8 icon-tile rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-md"></i>
                        </div>
                        {{ __('appointments.doctor_information') }}
                    </h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="w-12 h-12 brand-mark rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-md text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-1">Dr. {{ $appointment->doctor->name }}</h3>
                            <div class="flex items-center space-x-1 text-gray-600">
                                <i class="fas fa-envelope text-sm"></i>
                                <span>{{ $appointment->doctor->email }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointment Details -->
            <div class="section-card overflow-hidden">
                <div class="soft-panel px-6 py-4 border-b border-teal-100">
                    <h2 class="text-lg font-bold text-teal-900 flex items-center">
                        <div class="w-8 h-8 icon-tile rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info"></i>
                        </div>
                        {{ __('appointments.appointment_details') }}
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">{{ __('appointments.date') }}</label>
                            <p class="font-bold text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, M d, Y') }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">{{ $appointment->isQueueMode() ? 'Estimated Time' : __('appointments.time') }}</label>
                            <p class="font-bold text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 flex items-center space-x-2">
                                <i class="fas fa-clock text-gray-400"></i>
                                <span>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">{{ __('appointments.status') }}</label>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                    @if($appointment->status == 'scheduled') status-scheduled
                                    @elseif($appointment->status == 'confirmed') status-in-progress
                                    @elseif($appointment->status == 'completed') status-completed
                                    @elseif($appointment->status == 'cancelled') status-cancelled
                                    @elseif($appointment->status == 'no-show') bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('-', ' ', $appointment->status)) }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Mode</label>
                            <p class="font-bold text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                {{ $appointment->isQueueMode() ? 'Queue' : 'Scheduled' }}
                                @if($appointment->isQueueMode())
                                    <span class="text-teal-700">#{{ $appointment->queue_number }}</span>
                                @endif
                            </p>
                        </div>

                        @if($appointment->reason)
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Purpose</label>
                            <p class="text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 leading-relaxed">{{ $appointment->reason }}</p>
                        </div>
                        @endif

                        @if($appointment->notes)
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-medium text-gray-600">{{ __('appointments.notes') }}</label>
                            <p class="text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 leading-relaxed">{{ $appointment->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="section-card overflow-hidden">
                <div class="soft-panel px-6 py-4 border-b border-teal-100">
                    <h3 class="text-lg font-bold text-teal-900 flex items-center">
                        <div class="w-8 h-8 icon-tile rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-bolt"></i>
                        </div>
                        {{ __('appointments.quick_actions') }}
                    </h3>
                </div>
                <div class="p-6">
                <div class="space-y-3">
                    @if($appointment->status != 'completed' && $appointment->status != 'cancelled')
                        <form action="{{ route('appointments.update-status', $appointment) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn-primary w-full px-4 py-3">
                                <i class="fas fa-check"></i>
                                <span>Marquer comme Terminé</span>
                            </button>
                        </form>
                    @endif

                    @if($appointment->status == 'scheduled')
                        <form action="{{ route('appointments.set-current', $appointment) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="btn-ghost w-full px-4 py-3">
                                <i class="fas fa-check-circle"></i>
                                <span>Start consultation</span>
                            </button>
                        </form>
                    @endif

                    @if($appointment->status != 'cancelled' && $appointment->status != 'completed')
                        <form action="{{ route('appointments.cancel', $appointment) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="btn-danger w-full px-4 py-3"
                                    onclick="return confirm('{{ __('appointments.cancel_confirmation') }}')">
                                <i class="fas fa-times"></i>
                                <span>Annuler le rendez-vous</span>
                            </button>
                        </form>
                    @endif

                    @if(auth()->user()->isDoctor() && $appointment->status == 'completed')
                        <a href="{{ route('medical-records.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}" 
                           class="btn-primary w-full px-4 py-3">
                            <i class="fas fa-file-medical"></i>
                            <span>{{ __('appointments.add_medical_record') }}</span>
                        </a>
                    @endif
                </div>
                </div>
            </div>

            <!-- Appointment Info -->
            <div class="section-card overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-gray-700"></i>
                        </div>
                        {{ __('appointments.appointment_info') }}
                    </h3>
                </div>
                <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ __('appointments.created') }}:</span>
                        <span class="text-gray-900 font-bold">{{ $appointment->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ __('appointments.last_updated') }}:</span>
                        <span class="text-gray-900 font-bold">{{ $appointment->updated_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600 font-medium">{{ __('appointments.appointment_id') }}:</span>
                        <span class="text-gray-900 font-bold">#{{ $appointment->id }}</span>
                    </div>
                </div>
                </div>
            </div>

            <!-- Related Records -->
            @if(auth()->user()->isDoctor())
            <div class="section-card overflow-hidden">
                <div class="soft-panel px-6 py-4 border-b border-teal-100">
                    <h3 class="text-lg font-bold text-teal-900 flex items-center">
                        <div class="w-8 h-8 icon-tile rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-link"></i>
                        </div>
                        {{ __('appointments.related_records') }}
                    </h3>
                </div>
                <div class="p-6">
                <div class="space-y-3">
                    <a href="{{ route('medical-records.index', ['patient_id' => $appointment->patient_id]) }}" 
                       class="flex items-center bg-teal-50 hover:bg-teal-100 text-teal-700 px-4 py-3 rounded-lg transition-colors font-medium">
                        <i class="fas fa-file-medical mr-3"></i>
                        <span>{{ __('appointments.medical_records') }}</span>
                    </a>
                    <a href="{{ route('prescriptions.index', ['patient_id' => $appointment->patient_id]) }}" 
                       class="flex items-center bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-4 py-3 rounded-lg transition-colors font-medium">
                        <i class="fas fa-prescription-bottle mr-3"></i>
                        <span>{{ __('appointments.prescriptions') }}</span>
                    </a>
                </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
