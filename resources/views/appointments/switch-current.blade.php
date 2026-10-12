@extends('layouts.app')
@section('title', __('appointments.switch_title'))
@section('content')
<div class="mx-auto max-w-2xl py-4">
    <dialog open id="current-switch-dialog" aria-labelledby="switch-title" aria-describedby="switch-description" class="care-dialog">
        <header class="dialog-heading"><span class="care-icon blue"><i class="fas fa-arrow-right-arrow-left"></i></span><h1 id="switch-title">{{ __('appointments.switch_title') }}</h1></header>
        <div class="dialog-body">
            @if(session('error'))<div class="patient-alert mb-4" role="alert">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="patient-alert mb-4" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <p id="switch-description" class="dialog-description">{{ __('clinical_ui.switch_help') }}</p>
            <div class="switch-people"><div class="switch-person"><span>{{ __('clinical_ui.current') }}</span><strong>{{ $current->patient->full_name }}</strong></div><i class="fas fa-arrow-right text-slate-400"></i><div class="switch-person"><span>{{ __('clinical_ui.next') }}</span><strong>{{ $appointment->patient->full_name }}</strong></div></div>
            <form method="POST" action="{{ route('appointments.set-current', $appointment) }}" id="switch-form">
                @csrf
                @if($returnToWorkspace)<input type="hidden" name="return_to_workspace" value="1">@endif
                <input type="hidden" name="previous_appointment_id" value="{{ $current->id }}">
                <fieldset><legend class="mb-3 font-semibold text-sm">{{ __('appointments.switch_question', ['name' => $current->patient->full_name]) }}</legend>
                    <div class="switch-options">
                    @foreach(['completed' => 'fa-circle-check', 'waiting' => 'fa-clock', 'reschedule' => 'fa-calendar-days', 'no_show' => 'fa-user-slash', 'cancelled' => 'fa-ban'] as $action => $icon)
                        @if($action !== 'no_show' || !$current->medicalRecord()->exists())
                        <label class="switch-option {{ in_array($action, ['no_show', 'cancelled']) ? 'danger' : '' }}"><input type="radio" name="previous_action" value="{{ $action }}" @checked(old('previous_action') === $action) required><i class="fas {{ $icon }}"></i><span><strong>{{ __('appointments.switch_'.$action) }}</strong><small>{{ __('clinical_ui.'.$action.'_help') }}</small></span></label>
                        @endif
                    @endforeach
                    </div>
                </fieldset>
                <fieldset id="reschedule-fields" class="mt-4 rounded-xl bg-slate-50 p-4">
                    <legend class="font-semibold text-sm">{{ __('appointments.switch_reschedule_fields') }}</legend>
                    <p class="mb-3 text-sm text-slate-500">{{ __('appointments.switch_reschedule_help') }}</p>
                    <div class="workflow-grid">
                        <div><label for="reschedule_date">{{ __('appointments.switch_date') }}</label><input id="reschedule_date" name="reschedule_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('reschedule_date') }}"></div>
                        <div><label for="reschedule_time">{{ __('appointments.switch_time') }}</label><input id="reschedule_time" name="reschedule_time" type="time" value="{{ old('reschedule_time') }}"></div>
                    </div>
                </fieldset>
                <div class="dialog-actions">
                    <a href="{{ $returnToWorkspace ? route('doctor.current') : route('appointments.show', $current) }}" class="btn-secondary">{{ __('clinical_ui.stay_current') }}</a>
                    <button type="submit" class="btn-care">{{ __('appointments.switch_confirm', ['name' => $appointment->patient->full_name]) }} <i class="fas fa-arrow-right"></i></button>
                </div>
            </form>
        </div>
    </dialog>
</div>
@endsection
@push('scripts')
<script>
    const switchDialog = document.getElementById('current-switch-dialog');
    if (typeof switchDialog.showModal === 'function') { switchDialog.close(); switchDialog.showModal(); }
    switchDialog.addEventListener('cancel', event => {
        event.preventDefault();
        window.location.href = @json($returnToWorkspace ? route('doctor.current') : route('appointments.show', $current));
    });
    const switchForm = document.getElementById('switch-form');
    const switchFields = document.getElementById('reschedule-fields');
    function updateSwitchFields() {
        const reschedule = switchForm.querySelector('[name=previous_action]:checked')?.value === 'reschedule';
        switchFields.hidden = !reschedule;
        switchFields.querySelectorAll('input').forEach(input => { input.required = reschedule; input.disabled = !reschedule; });
    }
    switchForm.addEventListener('change', updateSwitchFields);
    updateSwitchFields();
</script>
@endpush
