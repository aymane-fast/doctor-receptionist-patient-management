@props(['appointment', 'compact' => false, 'manual' => true])
@if($appointment->status === 'scheduled')
<div class="reschedule-actions">
    @if($manual)
    <a href="{{ route('appointments.reschedule', $appointment) }}" class="btn-ghost {{ $compact ? 'w-9 h-9 p-0' : 'px-3 py-2' }}" title="{{ __('Reschedule Appointment') }}" aria-label="{{ __('Reschedule Appointment') }}"><i class="fas fa-calendar-days" aria-hidden="true"></i>@unless($compact)<span>{{ __('Reschedule') }}</span>@endunless</a>
    @endif
    @if(!$appointment->isQueueMode())
    <form method="POST" action="{{ route('appointments.rebook-next-slot', $appointment) }}" data-confirm-reschedule data-patient-name="{{ $appointment->patient->full_name }}">
        @csrf
        <button type="submit" class="btn-ghost {{ $compact ? 'w-9 h-9 p-0' : 'px-3 py-2' }}" title="{{ __('clinical_ui.quick_reschedule') }}" aria-label="{{ __('clinical_ui.quick_reschedule') }}"><i class="fas fa-calendar-day" aria-hidden="true"></i>@unless($compact)<span>{{ __('clinical_ui.quick_reschedule') }}</span>@endunless</button>
    </form>
    @endif
</div>
@endif
