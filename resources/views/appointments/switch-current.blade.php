@extends('layouts.app')

@section('title', __('appointments.switch_title'))

@push('styles')
<style>
    #current-switch-dialog { width: min(42rem, calc(100vw - 2rem)); max-height: calc(100dvh - 2rem); overflow-y: auto; margin: auto; }
    #current-switch-dialog::backdrop { background: rgba(13, 45, 42, .55); backdrop-filter: blur(3px); }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-2xl py-8">
    <dialog open id="current-switch-dialog" aria-labelledby="switch-title" aria-describedby="switch-description" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
        <div class="mb-4 flex items-center gap-3">
            <i class="fas fa-exchange-alt text-2xl text-teal-700" aria-hidden="true"></i>
            <h1 id="switch-title" class="text-2xl font-bold text-slate-900">{{ __('appointments.switch_title') }}</h1>
        </div>
        <p id="switch-description" class="mb-6 text-slate-600">{{ __('appointments.switch_description', ['current' => $current->patient->full_name, 'next' => $appointment->patient->full_name]) }}</p>
        <form method="POST" action="{{ route('appointments.set-current', $appointment) }}">
            @csrf
            <input type="hidden" name="previous_appointment_id" value="{{ $current->id }}">
            <label for="previous_action" class="mb-2 block font-semibold">{{ __('appointments.switch_question', ['name' => $current->patient->full_name]) }}</label>
            <select id="previous_action" name="previous_action" required autofocus class="form-control w-full">
                <option value="">{{ __('appointments.switch_choose') }}</option>
                <option value="completed">{{ __('appointments.switch_completed') }}</option>
                <option value="waiting">{{ __('appointments.switch_waiting') }}</option>
                <option value="reschedule">{{ __('appointments.switch_reschedule') }}</option>
                @if(!$current->medicalRecord()->exists())
                    <option value="no_show">{{ __('appointments.switch_no_show') }}</option>
                @endif
                <option value="cancelled">{{ __('appointments.switch_cancelled') }}</option>
            </select>
            <p class="mt-2 text-sm text-slate-500">{{ __('appointments.switch_no_show_help') }}</p>
            <fieldset id="reschedule-fields" class="mt-5 rounded-xl bg-slate-50 p-4">
                <legend class="font-semibold">{{ __('appointments.switch_reschedule_fields') }}</legend>
                <p class="mb-3 text-sm text-slate-500">{{ __('appointments.switch_reschedule_help') }}</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="reschedule_date" class="mb-1 block">{{ __('appointments.switch_date') }}</label>
                        <input id="reschedule_date" name="reschedule_date" type="date" min="{{ today()->toDateString() }}" class="form-control w-full">
                    </div>
                    <div>
                        <label for="reschedule_time" class="mb-1 block">{{ __('appointments.switch_time') }}</label>
                        <input id="reschedule_time" name="reschedule_time" type="time" class="form-control w-full">
                    </div>
                </div>
            </fieldset>
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-teal-700 px-5 py-3 font-semibold text-white">{{ __('appointments.switch_confirm', ['name' => $appointment->patient->full_name]) }}</button>
                <a href="{{ route('appointments.show', $current) }}" class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700">{{ __('appointments.switch_do_nothing') }}</a>
            </div>
        </form>
    </dialog>
</div>
@endsection

@push('scripts')
<script>
    const dialog = document.getElementById('current-switch-dialog');
    if (typeof dialog.showModal === 'function') {
        dialog.close();
        dialog.showModal();
    }
    dialog.addEventListener('cancel', event => {
        event.preventDefault();
        window.location.href = @json(route('appointments.show', $current));
    });
    const choice = document.getElementById('previous_action');
    const fields = document.getElementById('reschedule-fields');
    function updateSwitchFields() {
        const reschedule = choice.value === 'reschedule';
        fields.hidden = !reschedule;
        fields.querySelectorAll('input').forEach(input => {
            input.required = reschedule;
            input.disabled = !reschedule;
        });
    }
    choice.addEventListener('change', updateSwitchFields);
    updateSwitchFields();
</script>
@endpush
