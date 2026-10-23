@extends('layouts.app')

@section('title', __('Appointment Queue'))

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        :title="__('Appointment Queue')"
        :description="__('clinical_ui.active_only')"
        icon="fa-list-ol"
        :eyebrow="__('Daily flow')"
    >
        <x-slot:actions>
            <a href="{{ route('appointments.index', ['date' => $date, 'doctor_id' => $selectedDoctor?->id]) }}" class="btn-secondary px-4 py-2"><i class="fas fa-calendar-days"></i>{{ __('clinical_ui.daily_schedule') }}</a>
            <a href="{{ route('appointments.create') }}" class="btn-primary px-4 py-2">
                <i class="fas fa-plus text-sm"></i>
                <span>{{ __('Add Patient') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="section-card p-6">
        <form method="GET" action="{{ route('appointments.queue') }}" class="grid md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Date') }}</label>
                <input type="date" id="date" name="date" value="{{ $date }}" class="form-control w-full px-3 py-2">
            </div>

            @if(auth()->user()->isReceptionist())
            <div>
                <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Doctor') }}</label>
                <select id="doctor_id" name="doctor_id" class="form-control w-full px-3 py-2">
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ optional($selectedDoctor)->id === $doctor->id ? 'selected' : '' }}>
                            {{ $doctor->professional_name }} - {{ $doctor->appointment_mode === 'queue' ? __('Queue') : __('Scheduled') }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <button type="submit" class="btn-primary px-5 py-2">
                    <i class="fas fa-filter mr-2"></i>{{ __('View Queue') }}
                </button>
            </div>
        </form>
    </div>

    @if($selectedDoctor)
        <div class="mode-summary"><div><strong><i class="fas {{ $selectedDoctor->usesQueueMode() ? 'fa-list-ol' : 'fa-calendar-days' }} mr-2"></i>{{ $selectedDoctor->professional_name }} · {{ __('clinical_ui.booking_mode') }} {{ $selectedDoctor->usesQueueMode() ? __('Queue') : __('Scheduled') }}</strong><p>{{ __('clinical_ui.mode_help') }}</p></div><span class="care-badge">{{ __('clinical_ui.all_modes') }}</span></div>
    @endif

    <div class="section-card overflow-hidden">
        @if($queueAppointments->count())
            <p class="px-6 py-3 text-sm text-slate-500 border-b border-slate-100"><i class="fas fa-arrow-down-short-wide mr-2" aria-hidden="true"></i>{{ __('clinical_ui.priority_help') }}</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('Queue') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('Patient') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('appointments.time') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('Reason') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($queueAppointments as $appointment)
                        <tr class="{{ $appointment->status === 'in_progress' ? 'bg-sky-50' : 'bg-white' }}">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-100 text-teal-800 font-bold">
                                    {{ $appointment->queue_number ? '#'.$appointment->queue_number : ($appointment->queue_position ?? '—') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $appointment->patient->full_name }}</div>
                                <div class="text-sm text-gray-500">{{ $appointment->patient->contact_label }}</div>
                                @if($appointment->patient->is_pediatric)<div class="text-sm text-gray-500">{{ $appointment->patient->age_display }} · {{ $appointment->patient->birth_date?->translatedFormat('Y-m-d') }}</div>@endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $appointment->displayTime() }}</div>
                                <div class="text-xs text-gray-500">{{ $appointment->isQueueMode() ? __('Approximate') : __('Scheduled') }}</div>
                                @if($appointment->scheduling_override)<span class="text-xs text-amber-700">{{ __('workflow.overridden') }}</span>@endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full text-center {{ \App\Models\Appointment::statusBadgeClasses($appointment->status) }}">
                                        {{ \App\Models\Appointment::statusLabel($appointment->status) }}
                                    </span>
                                    @if($appointment->isCheckedIn() && $appointment->status === 'scheduled')
                                        <span class="text-xs text-teal-700 font-medium text-center">
                                            <i class="fas fa-user-check mr-1"></i>{{ __('appointments.checked_in') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $appointment->reason ?: '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="queue-actions">
                                    @if($appointment->status === 'scheduled')
                                        <form method="POST" action="{{ route('appointments.queue-up', $appointment) }}">
                                            @csrf
                                            <button class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700" title="{{ __('Move up') }}" aria-label="{{ __('Move up') }}">
                                                <i class="fas fa-arrow-up"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('appointments.queue-down', $appointment) }}">
                                            @csrf
                                            <button class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700" title="{{ __('Move down') }}" aria-label="{{ __('Move down') }}">
                                                <i class="fas fa-arrow-down"></i>
                                            </button>
                                        </form>
                                        @if(!$appointment->isCheckedIn())
                                            <form method="POST" action="{{ route('appointments.check-in', $appointment) }}">
                                                @csrf
                                                <button class="px-3 py-2 bg-teal-100 hover:bg-teal-200 text-teal-700 rounded-lg text-sm font-medium" title="{{ __('appointments.check_in') }}">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('appointments.set-current', $appointment) }}">
                                            @csrf
                                            @if(auth()->user()->isDoctor())<input type="hidden" name="return_to_workspace" value="1">@endif
                                            <button class="btn-ghost px-3 py-2 text-sm">
                                                {{ __('Start') }}
                                            </button>
                                        </form>
                                        <details><summary aria-label="{{ __('Actions') }}"><i class="fas fa-ellipsis"></i></summary>
                                        <x-appointments.reschedule-actions :appointment="$appointment" />
                                        <form method="POST" action="{{ route('appointments.no-show', $appointment) }}">
                                            @csrf
                                            <button class="px-3 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg text-sm font-medium" onclick="return confirm(@js(__('appointments.mark_no_show').'?'))">
                                                {{ __('appointments.mark_no_show') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('appointments.cancel', $appointment) }}">
                                            @csrf
                                            <button class="btn-danger px-3 py-2 text-sm" onclick="return confirm(@js(__('Cancel this queue entry?')))">
                                                {{ __('Cancel') }}
                                            </button>
                                        </form>
                                        </details>
                                    @elseif($appointment->status === 'in_progress')
                                        <form method="POST" action="{{ route('appointments.update-status', $appointment) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button class="px-3 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 rounded-lg text-sm font-medium">
                                                {{ __('common.completed') }}
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('appointments.show', $appointment) }}" class="w-9 h-9 bg-teal-100 hover:bg-teal-200 text-teal-700 rounded-lg flex items-center justify-center" title="{{ __('View') }}" aria-label="{{ __('View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-16 px-6">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-list-ol text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">{{ __('clinical_ui.no_active') }}</h3>
                <p class="text-gray-500 mt-1">{{ __('clinical_ui.active_only') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
