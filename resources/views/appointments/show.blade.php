@extends('layouts.app')

@section('title', __('appointments.appointment_details'))

@section('content')
@if($appointment->scheduling_override)
    <p class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm">{{ __('workflow.overridden') }} · {{ $appointment->override_reason }}</p>
@endif
<div class="max-w-7xl mx-auto space-y-6">
    <x-ui.page-header
        :title="__('appointments.appointment_details')"
        :description="__('Appointment #:id - :date', ['id' => $appointment->id, 'date' => \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('M d, Y')])"
        icon="fa-calendar-check"
        :eyebrow="__('Booking record')"
    >
        <x-slot:actions>
                <button onclick="history.back()" 
                   class="btn-secondary px-4 py-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('common.back') }}</span>
                </button>
                <a href="{{ route('appointments.edit', $appointment) }}" 
                   class="btn-primary px-4 py-2">
                    <i class="fas fa-edit"></i>
                    <span>{{ __('appointments.edit') }}</span>
                </a>
        </x-slot:actions>
    </x-ui.page-header>
    <x-patients.guardian-summary :patient="$appointment->patient" />

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
                                    <span>{{ $appointment->patient->age_display }}</span>
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
                            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $appointment->doctor->professional_name }}</h3>
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
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('l, M d, Y') }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">{{ $appointment->isQueueMode() ? __('Estimated Time') : __('appointments.time') }}</label>
                            <p class="font-bold text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 flex items-center space-x-2">
                                <i class="fas fa-clock text-gray-400"></i>
                                <span>{{ $appointment->displayTime() }}</span>
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">{{ __('appointments.status') }}</label>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ \App\Models\Appointment::statusBadgeClasses($appointment->status) }}">
                                    {{ \App\Models\Appointment::statusLabel($appointment->status) }}
                                </span>
                                @if($appointment->isCheckedIn())
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-teal-100 text-teal-700">
                                        <i class="fas fa-user-check mr-1"></i>{{ __('appointments.arrived_at', ['time' => $appointment->checked_in_at->format('g:i A')]) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">{{ __('Mode') }}</label>
                            <p class="font-bold text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                {{ $appointment->isQueueMode() ? __('Queue') : __('Scheduled') }}
                                @if($appointment->isQueueMode())
                                    <span class="text-teal-700">#{{ $appointment->queue_number }}</span>
                                @endif
                            </p>
                        </div>

                        @if($appointment->reason)
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-medium text-gray-600">{{ __('Purpose') }}</label>
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
                    @if($appointment->status == 'scheduled' && !$appointment->isCheckedIn() && $appointment->appointment_date->isToday())
                        <form action="{{ route('appointments.check-in', $appointment) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="btn-primary w-full px-4 py-3">
                                <i class="fas fa-user-check"></i>
                                <span>{{ __('appointments.check_in') }}</span>
                            </button>
                        </form>
                    @endif

                    @if($appointment->status == 'scheduled' && $appointment->appointment_date->isToday())
                        <form action="{{ route('appointments.set-current', $appointment) }}" method="POST" class="w-full">
                            @csrf
                            @if(auth()->user()->isDoctor())<input type="hidden" name="return_to_workspace" value="1">@endif
                            <button type="submit" class="btn-ghost w-full px-4 py-3">
                                <i class="fas fa-play-circle"></i>
                                <span>{{ __('appointments.start_consultation') }}</span>
                            </button>
                        </form>
                    @endif

                    @if($appointment->status == 'in_progress')
                        <form action="{{ route('appointments.update-status', $appointment) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn-primary w-full px-4 py-3">
                                <i class="fas fa-check"></i>
                                <span>{{ __('appointments.mark_completed') }}</span>
                            </button>
                        </form>
                    @endif

                    @if($appointment->status == 'scheduled')
                        <x-appointments.reschedule-actions :appointment="$appointment" />

                        @if(!$appointment->appointment_date->isFuture())
                        <form action="{{ route('appointments.no-show', $appointment) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="btn-ghost w-full px-4 py-3"
                                    onclick="return confirm(@js(__('appointments.mark_no_show').'?'))">
                                <i class="fas fa-user-slash"></i>
                                <span>{{ __('appointments.mark_no_show') }}</span>
                            </button>
                        </form>
                        @endif
                    @endif

                    @if($appointment->isCancellable())
                        <form action="{{ route('appointments.cancel', $appointment) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="btn-danger w-full px-4 py-3"
                                    onclick="return confirm(@js(__('appointments.cancel_confirmation')))">
                                <i class="fas fa-times"></i>
                                <span>{{ __('appointments.cancel_appointment') ?? 'Cancel appointment' }}</span>
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
                        <span class="text-gray-900 font-bold">{{ $appointment->created_at->translatedFormat('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">{{ __('appointments.last_updated') }}:</span>
                        <span class="text-gray-900 font-bold">{{ $appointment->updated_at->translatedFormat('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600 font-medium">{{ __('appointments.appointment_id') }}:</span>
                        <span class="text-gray-900 font-bold">#{{ $appointment->id }}</span>
                    </div>
                </div>
                </div>
            </div>

            <!-- Status History (audit trail) -->
            @if($appointment->statusLogs->isNotEmpty())
            <div class="section-card overflow-hidden">
                <div class="soft-panel px-6 py-4 border-b border-teal-100">
                    <h3 class="text-lg font-bold text-teal-900 flex items-center">
                        <div class="w-8 h-8 icon-tile rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-history"></i>
                        </div>
                        {{ __('appointments.status_history') }}
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($appointment->statusLogs as $log)
                        <div class="flex items-start justify-between py-2 border-b border-gray-100 last:border-b-0">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">
                                    @if($log->from_status)
                                        {{ \App\Models\Appointment::statusLabel($log->from_status) }}
                                        <i class="fas fa-arrow-right text-gray-400 text-xs mx-1"></i>
                                    @endif
                                    <span class="px-2 py-0.5 rounded-full text-xs {{ \App\Models\Appointment::statusBadgeClasses($log->to_status) }}">
                                        {{ \App\Models\Appointment::statusLabel($log->to_status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $log->changedBy?->name ?? __('appointments.system_action') }}
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 whitespace-nowrap ml-3">
                                {{ $log->created_at->translatedFormat('M j, g:i A') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

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
