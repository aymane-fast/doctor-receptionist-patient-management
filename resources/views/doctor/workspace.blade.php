@extends('layouts.app')
@section('title', __('current_patient.title'))
@section('content')
@php($clinicalOld = fn($key, $default = null) => old('workspace_form') === 'consultation' ? old($key, $default) : $default)
<div class="space-y-4">
<x-ui.page-header :title="__('current_patient.title')" :description="__('workflow.workspace_help')" icon="fa-user-doctor">
    <x-slot:actions><a href="{{ route('dashboard') }}" class="btn-secondary px-3 py-2">{{ __('current_patient.back_to_dashboard') }}</a></x-slot:actions>
</x-ui.page-header>
@if(!$currentAppointment)
    <section class="workflow-panel"><h2>{{ __('current_patient.no_current_patient') }}</h2><p>{{ __('current_patient.no_patient_assigned') }}</p>
    <form class="mt-3" method="POST" action="{{ route('dashboard.initialize-first-patient') }}">@csrf<button class="btn-primary px-4 py-2">{{ __('appointments.call_next_patient') }}</button></form></section>
@else
    <section class="workflow-panel flex flex-wrap items-center justify-between gap-3">
        <div><div class="text-xs font-bold uppercase text-teal-700">{{ __('workflow.active_consultation') }} #{{ $currentAppointment->id }}</div>
        <h2 class="!mb-1">{{ $patient->full_name }} <span class="ml-2 text-sm font-normal text-slate-500">{{ $patient->age_display }} · {{ $currentAppointment->displayTime() }}</span></h2>
        @if($patient->is_pediatric)<p class="text-xs text-slate-500">{{ __('Child') }} · {{ __('Born') }} {{ $patient->birth_date?->format('d/m/Y') }}</p>@endif
        <x-patients.guardian-summary :patient="$patient" :compact="true" :show-identity="false" />@if(!$patient->is_pediatric)<p class="text-sm text-slate-500">{{ $patient->contact_label }}</p>@endif</div>
        <form method="POST" action="{{ route('appointments.mark-current-done') }}">@csrf<input type="hidden" name="appointment_id" value="{{ $currentAppointment->id }}"><button class="btn-primary px-4 py-2"><i class="fas fa-check"></i> {{ __('current_patient.complete_visit_next_patient') }}</button></form>
    </section>
    @if($patient->allergies)<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm"><strong>{{ __('patients.allergies') }}:</strong> {{ $patient->allergies }}</div>@endif
    <nav class="workspace-tabs" aria-label="{{ __('workflow.workspace_sections') }}">
        @foreach(['consultation', 'prescription', 'examinations', 'documents', 'history'] as $section)<a href="#{{ $section }}">{{ __('workflow.'.$section) }}</a>@endforeach
        <a href="#follow-up">{{ __('current_patient.schedule_follow_up') }}</a>
    </nav>
    <div class="workspace-layout">
        <div class="space-y-4">
            <section id="consultation" class="workflow-panel">
                <h2><i class="fas fa-stethoscope mr-2 text-teal-700"></i>{{ __('workflow.consultation') }} @if($record)<span class="text-sm font-normal text-slate-500">#{{ $record->id }}</span>@endif</h2>
                <form id="active-consultation-form" method="POST" action="{{ $record ? route('medical-records.update', $record) : route('medical-records.store') }}">
                    @csrf @if($record) @method('PUT') @endif
                    <input type="hidden" name="workspace_form" value="consultation">
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}"><input type="hidden" name="appointment_id" value="{{ $currentAppointment->id }}"><input type="hidden" name="return_to_workspace" value="1">
                    <div class="workflow-grid">
                        <div><label for="visit_date">{{ __('medical_records.visit_date') }}</label><input id="visit_date" type="date" name="visit_date" max="{{ today()->toDateString() }}" value="{{ old('visit_date', $record?->visit_date?->toDateString() ?? today()->toDateString()) }}" required></div>
                        <div><label for="visit_type">{{ __('medical_records.visit_type') }}</label><select id="visit_type" name="visit_type">@foreach(['consultation', 'follow-up', 'emergency', 'routine-checkup'] as $type)<option value="{{ $type }}" @selected(old('visit_type', $record?->visit_type ?? 'consultation') === $type)>{{ __('medical_records.visit_types.'.($type === 'routine-checkup' ? 'routine' : $type)) }}</option>@endforeach</select></div>
                        @foreach(['symptoms' => 'chief_complaint', 'diagnosis' => 'assessment_diagnosis', 'treatment' => 'treatment_plan', 'notes' => 'notes'] as $field => $label)<div><label for="{{ $field }}">{{ __('medical_records.'.$label) }}</label><textarea id="{{ $field }}" name="{{ $field }}" rows="2" maxlength="16000">{{ $clinicalOld($field, $record?->$field) }}</textarea></div>@endforeach
                    </div>
                    <details class="my-3"><summary class="cursor-pointer font-semibold text-sm">{{ __('medical_records.vital_signs') }} · {{ __('common.optional') }}</summary>
                        @if($record?->temperature && !$record->temperature_unit)<p class="my-2 text-amber-800 text-sm">{{ __('workflow.legacy_temperature_help') }}</p>@endif
                        <div class="workflow-grid mt-3">
                            @foreach(['weight' => 'kg', 'height' => 'cm', 'temperature' => $record?->temperature_unit ? '°'.$record->temperature_unit : ($record?->temperature ? __('workflow.legacy_unit') : '°C'), 'blood_pressure' => 'mmHg', 'heart_rate' => 'bpm', 'oxygen_saturation' => '%'] as $field => $unit)
                            <div><label for="{{ $field }}">{{ __('medical_records.'.$field) }} ({{ $unit }})</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'blood_pressure' ? 'text' : 'number' }}" @if($field !== 'blood_pressure') min="0" step="{{ in_array($field, ['heart_rate', 'oxygen_saturation']) ? '1' : '.1' }}" @endif value="{{ old($field, $record?->$field) }}"></div>
                            @endforeach
                            <div><label for="temperature_unit">{{ __('workflow.temperature_unit') }}</label><select id="temperature_unit" name="temperature_unit">@if($record)<option value="" @selected(!$clinicalOld('temperature_unit', $record->temperature_unit))>{{ __('workflow.legacy_unit') }}</option>@endif<option value="C" @selected($clinicalOld('temperature_unit', $record ? $record->temperature_unit : 'C') === 'C')>°C</option>@if($record)<option value="F" @selected($clinicalOld('temperature_unit', $record->temperature_unit) === 'F')>°F</option>@endif</select></div>
                        </div>
                    </details>
                    <details class="my-3"><summary class="cursor-pointer font-semibold text-sm">{{ __('workflow.additional_clinical_details') }}</summary>
                        <div class="workflow-grid my-3">@foreach(['history_present_illness', 'physical_examination'] as $field)<div><label for="{{ $field }}">{{ __('medical_records.'.$field) }}</label><textarea id="{{ $field }}" name="{{ $field }}" rows="2">{{ old($field, $record?->$field) }}</textarea></div>@endforeach</div>
                        <x-specialty.fields :fields="$clinicSpecialty['record_fields']" :values="$record?->specialty_data ?? []" :title="__('workflow.additional_clinical_details')" />
                    </details>
                    <div class="workflow-actions"><button class="btn-primary"><i class="fas fa-save"></i> {{ __('workflow.save_stay') }}</button><span class="text-xs text-slate-500">{{ __('workflow.save_not_finish') }}</span></div>
                </form>
            </section>
            <section id="prescription" class="workflow-panel"><h2><i class="fas fa-prescription mr-2 text-teal-700"></i>{{ __('workflow.prescription') }}</h2>
                @if($record) @include('clinical.prescription-form') @else <p class="text-sm text-slate-500">{{ __('workflow.save_first') }}</p> @endif
                @foreach($prescriptions as $rx)<div class="clinical-row flex flex-wrap justify-between gap-2"><span>{{ $rx->prescribed_date->format('d/m/Y') }} · #{{ $rx->id }} · {{ $rx->medical_record_id === $record?->id ? __('workflow.this_consultation') : __('workflow.historical') }}</span><div><a class="text-teal-700 mr-3" href="{{ route('prescriptions.show', $rx) }}">{{ __('common.view') }}</a><a class="text-teal-700" target="_blank" rel="noopener" href="{{ route('prescriptions.print', $rx) }}">{{ __('common.print') }}</a></div></div>@endforeach
            </section>
            @if($record)
                @include('clinical.tools', ['medicalRecord' => $record])
            @else
                @foreach(['examinations', 'documents'] as $section)<section id="{{ $section }}" class="workflow-panel"><h2>{{ __('workflow.'.$section) }}</h2><p class="text-sm text-slate-500">{{ __('workflow.save_first') }}</p></section>@endforeach
            @endif
            <section id="follow-up" class="workflow-panel"><h2>{{ __('current_patient.schedule_follow_up') }}</h2>
                <form method="POST" action="{{ route('appointments.create-follow-up', $patient) }}">@csrf<input type="hidden" name="current_appointment_id" value="{{ $currentAppointment->id }}">
                    <div class="workflow-grid"><div><label for="follow_up_number">{{ __('current_patient.time_amount') }}</label><input id="follow_up_number" type="number" name="follow_up_number" min="1" max="365" value="7" required></div><div><label for="follow_up_unit">{{ __('current_patient.time_unit') }}</label><select id="follow_up_unit" name="follow_up_unit">@foreach(['days', 'weeks', 'months'] as $unit)<option value="{{ $unit }}">{{ __('current_patient.'.$unit) }}</option>@endforeach</select></div><div class="full-width"><label for="follow_up_reason">{{ __('current_patient.reason_optional') }}</label><input id="follow_up_reason" name="follow_up_reason" value="{{ old('follow_up_reason', app(\App\Services\SpecialtyService::class)->defaultFollowUpReason()) }}"></div></div>
                    <p class="my-2 text-xs text-slate-500">{{ __('current_patient.auto_scheduling_description') }}</p><button class="btn-secondary px-3 py-2">{{ __('current_patient.schedule_appointment') }}</button>
                </form>
            </section>
        </div>
        <aside class="space-y-4">
            <section class="workflow-panel"><h2>{{ __('workflow.today_queue') }}</h2>
                @forelse($waiting as $visit)<div class="clinical-row flex flex-wrap items-center justify-between gap-2"><div><strong>{{ $visit->patient->full_name }}</strong><p class="text-xs text-slate-500">{{ $visit->displayTime() }} · {{ $visit->checked_in_at ? __('workflow.waiting_room') : __('workflow.not_arrived') }}</p></div><form method="POST" action="{{ route('appointments.set-current', $visit) }}">@csrf<input type="hidden" name="return_to_workspace" value="1"><button class="btn-secondary px-2 py-1 text-sm">{{ __('appointments.start_consultation') }}</button></form></div>@empty<p class="text-sm text-slate-500">{{ __('workflow.no_waiting') }}</p>@endforelse
                <a href="{{ route('appointments.queue') }}" class="mt-3 inline-block text-sm text-teal-700">{{ __('workflow.manage_queue') }}</a>
            </section>
            <section id="history" class="workflow-panel"><h2>{{ __('workflow.history') }}</h2>
                @if($patient->medical_history)<p class="text-sm mb-3 whitespace-pre-wrap">{{ $patient->medical_history }}</p>@endif
                @forelse($history as $previous)<div class="clinical-row"><strong>{{ $previous->visit_date->format('d/m/Y') }} · {{ $previous->id === $record?->id ? __('workflow.this_consultation') : __('workflow.historical') }}</strong><p class="text-sm text-slate-500">{{ Str::limit($previous->diagnosis, 100) }}</p><a class="text-sm text-teal-700" href="{{ route('medical-records.show', $previous) }}">{{ __('common.view') }} #{{ $previous->id }}</a></div>@empty<p>{{ __('current_patient.no_medical_records') }}</p>@endforelse
                <a class="mt-3 inline-block text-sm text-teal-700" href="{{ route('patients.show', $patient) }}">{{ __('current_patient.view_full_patient_history') }}</a>
            </section>
        </aside>
    </div>
@endif
</div>
@endsection
