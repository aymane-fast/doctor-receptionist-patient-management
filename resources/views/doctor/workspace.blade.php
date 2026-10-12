@extends('layouts.app')
@section('title', __('current_patient.title'))
@section('content')
@php
    $clinicalOld = fn($key, $default = null) => old('workspace_form') === 'consultation' ? old($key, $default) : $default;
    $workspaceTabs = ['consultation' => 'fa-stethoscope', 'history' => 'fa-clock-rotate-left', 'prescription' => 'fa-prescription', 'examinations' => 'fa-flask', 'documents' => 'fa-folder-open'];
@endphp
<div class="care-workspace" data-workspace data-visit="{{ $currentAppointment?->id }}" data-initial-tab="{{ $errors->any() && is_string(old('workspace_form', 'consultation')) ? old('workspace_form', 'consultation') : '' }}">
    <div class="workspace-heading">
        <div><p class="page-header-eyebrow">{{ __($clinicSpecialty['label']) }}</p><h1>{{ __('current_patient.title') }}</h1></div>
        <a href="{{ route('dashboard') }}" class="btn-secondary px-3 py-2"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('common.back') }}</a>
    </div>
    @if(!$currentAppointment)
        <div class="workspace-empty section-card">
            <span class="care-icon"><i class="fas fa-user-clock"></i></span>
            <h2>{{ __('current_patient.no_current_patient') }}</h2><p>{{ __('current_patient.no_patient_assigned') }}</p>
            <form method="POST" action="{{ route('dashboard.initialize-first-patient') }}">@csrf<button class="btn-primary px-5 py-3"><i class="fas fa-user-check"></i>{{ __('appointments.call_next_patient') }}</button></form>
            <a href="{{ route('appointments.queue') }}" class="text-link">{{ __('workflow.manage_queue') }}</a>
        </div>
    @else
        <section class="active-patient-card" aria-labelledby="active-patient-name">
            <div class="patient-identity">
                <span class="patient-avatar"><i class="fas {{ $patient->is_pediatric ? 'fa-child' : 'fa-user' }}" aria-hidden="true"></i></span>
                <div><span class="care-eyebrow"><span class="live-dot"></span>{{ __('workflow.active_consultation') }} · #{{ $currentAppointment->id }}</span>
                    <h2 id="active-patient-name">{{ $patient->full_name }}</h2>
                    <div class="patient-meta"><span>{{ $patient->age_display }}</span><span><i class="far fa-clock"></i> {{ $currentAppointment->displayTime() }}</span>@if($patient->birth_date)<span>{{ $patient->birth_date->format('d/m/Y') }}</span>@endif</div>
                    <x-patients.guardian-summary :patient="$patient" :compact="true" :show-identity="false" />
                    @if(!$patient->is_pediatric)<p class="patient-contact">{{ $patient->contact_label }}</p>@endif
                </div>
            </div>
            <div class="patient-header-actions">
                <a href="{{ route('patients.show', $patient) }}" class="text-link"><i class="far fa-address-card"></i>{{ __('clinical_ui.patient_profile') }}</a>
                <button type="button" class="btn-care" data-dialog-open="finish-visit"><i class="fas fa-check"></i>{{ __('clinical_ui.finish_visit') }}</button>
            </div>
            @if($patient->allergies)<div class="patient-alert"><i class="fas fa-triangle-exclamation"></i><strong>{{ __('patients.allergies') }}</strong><span>{{ $patient->allergies }}</span></div>@endif
        </section>
        <div class="care-layout">
            <div class="care-main section-card">
                <nav class="care-tabs" aria-label="{{ __('workflow.workspace_sections') }}" data-workspace-tabs>
                    @foreach($workspaceTabs as $section => $icon)<a href="#{{ $section }}" id="tab-{{ $section }}" data-workspace-tab="{{ $section }}"><i class="fas {{ $icon }}" aria-hidden="true"></i><span>{{ __('workflow.'.$section) }}</span></a>@endforeach
                </nav>
                <section id="consultation" class="care-pane" data-workspace-panel>
                    <x-ui.care-heading icon="fa-stethoscope" :title="__('workflow.consultation')" :description="__('clinical_ui.consultation_help')" />
                    @include('clinical.consultation-form')
                </section>
                <section id="history" class="care-pane" data-workspace-panel>
                    <x-ui.care-heading icon="fa-clock-rotate-left" tone="blue" :title="__('workflow.history')" :description="__('clinical_ui.history_help')" />
                    @if($patient->medical_history)<div class="history-summary">{{ $patient->medical_history }}</div>@endif
                    <div class="care-timeline">
                    @forelse($history as $previous)
                        <article class="history-entry"><span class="timeline-marker"><i class="fas fa-file-medical"></i></span><div><div class="history-entry-meta">{{ $previous->visit_date->translatedFormat('d M Y') }}<span class="care-badge">{{ $previous->id === $record?->id ? __('workflow.this_consultation') : __('workflow.historical') }}</span></div><h3>{{ $previous->diagnosis ?: __('workflow.consultation') }}</h3>@if($previous->symptoms)<p>{{ Str::limit($previous->symptoms, 180) }}</p>@endif<a class="text-link" href="{{ route('medical-records.show', $previous) }}">{{ __('common.view') }} #{{ $previous->id }} <i class="fas fa-arrow-right"></i></a></div></article>
                    @empty<div class="care-empty"><i class="fas fa-clock-rotate-left"></i><p>{{ __('current_patient.no_medical_records') }}</p></div>@endforelse
                    </div>
                    <a class="btn-secondary px-4 py-2 mt-4" href="{{ route('patients.show', $patient) }}">{{ __('current_patient.view_full_patient_history') }}</a>
                </section>
                <section id="prescription" class="care-pane" data-workspace-panel>
                    <x-ui.care-heading icon="fa-prescription" :title="__('workflow.prescription')" :description="__('clinical_ui.prescription_help')" />
                    @if($record) @include('clinical.prescription-form') @else <div class="care-empty"><i class="fas fa-file-circle-plus"></i><p>{{ __('workflow.save_first') }}</p><a class="btn-primary px-4 py-2" href="#consultation" data-workspace-go="consultation">{{ __('clinical_ui.return_notes') }}</a></div> @endif
                    @if($prescriptions->isNotEmpty())<h3 class="care-subheading">{{ __('current_patient.recent_prescriptions') }}</h3>@endif
                    @foreach($prescriptions as $rx)<div class="clinical-row"><div><strong>{{ $rx->prescribed_date->format('d/m/Y') }} · #{{ $rx->id }}</strong><p class="text-sm text-slate-500">{{ $rx->medical_record_id === $record?->id ? __('workflow.this_consultation') : __('workflow.historical') }}</p></div><div class="workflow-actions"><a class="btn-secondary" href="{{ route('prescriptions.show', $rx) }}">{{ __('common.view') }}</a><a class="btn-secondary" target="_blank" rel="noopener" href="{{ route('prescriptions.print', $rx) }}"><i class="fas fa-print"></i>{{ __('common.print') }}</a></div></div>@endforeach
                </section>
                @if($record)
                    @include('clinical.tools', ['medicalRecord' => $record, 'inWorkspace' => true])
                @else
                    @foreach(['examinations' => 'fa-flask', 'documents' => 'fa-folder-open'] as $section => $icon)<section id="{{ $section }}" class="care-pane" data-workspace-panel><x-ui.care-heading :icon="$icon" :title="__('workflow.'.$section)" /><div class="care-empty"><i class="fas fa-file-circle-plus"></i><p>{{ __('workflow.save_first') }}</p><a href="#consultation" data-workspace-go="consultation" class="btn-primary px-4 py-2">{{ __('clinical_ui.return_notes') }}</a></div></section>@endforeach
                @endif
            </div>
            <aside class="care-rail">
                <section class="section-card rail-card">
                    <div class="rail-heading"><span class="care-icon blue"><i class="fas fa-users"></i></span><h2>{{ __('clinical_ui.up_next') }}</h2><span class="care-count">{{ $waiting->count() }}</span></div>
                    @forelse($waiting->take(3) as $visit)
                        <div class="next-visit {{ $loop->first ? 'next-visit-first' : '' }}">
                            <div class="next-visit-time">{{ $visit->displayTime() }}<span class="care-badge {{ $visit->checked_in_at ? 'arrived' : '' }}">{{ $visit->checked_in_at ? __('workflow.waiting_room') : __('workflow.not_arrived') }}</span></div>
                            <h3>{{ $visit->patient->full_name }}</h3>
                            <form method="POST" action="{{ route('appointments.set-current', $visit) }}">@csrf<input type="hidden" name="return_to_workspace" value="1"><button class="text-link"><i class="fas fa-arrow-right-arrow-left"></i>{{ __('clinical_ui.switch_patient') }}</button></form>
                        </div>
                    @empty<div class="care-empty compact"><i class="far fa-calendar-check"></i><p>{{ __('workflow.no_waiting') }}</p></div>@endforelse
                    <a href="{{ route('appointments.queue') }}" class="rail-link">{{ __('workflow.manage_queue') }}<i class="fas fa-arrow-right"></i></a>
                </section>
                <section class="section-card rail-card">
                    <div class="rail-heading"><span class="care-icon"><i class="fas fa-calendar-plus"></i></span><h2>{{ __('clinical_ui.after_visit') }}</h2></div>
                    <p class="rail-description">{{ __('clinical_ui.follow_up_help') }}</p>
                    <button type="button" class="btn-secondary w-full px-3 py-2" data-dialog-open="follow-up-dialog"><i class="fas fa-calendar-plus"></i>{{ __('current_patient.schedule_follow_up') }}</button>
                </section>
                <p class="workspace-mode"><i class="fas {{ auth()->user()->usesQueueMode() ? 'fa-list-ol' : 'fa-calendar-days' }}"></i>{{ __('clinical_ui.booking_mode') }}<strong>{{ auth()->user()->usesQueueMode() ? __('Queue') : __('Scheduled') }}</strong></p>
            </aside>
        </div>
        <x-ui.dialog id="follow-up-dialog" :title="__('current_patient.schedule_follow_up')" icon="fa-calendar-plus" form="follow-up">
            <p class="dialog-patient">{{ $patient->full_name }}</p>
                            <form method="POST" action="{{ route('appointments.create-follow-up', $patient) }}">@csrf<input type="hidden" name="workspace_form" value="follow-up"><input type="hidden" name="current_appointment_id" value="{{ $currentAppointment->id }}">
                    <div class="workflow-grid"><div><label for="follow_up_number">{{ __('current_patient.time_amount') }}</label><input id="follow_up_number" type="number" name="follow_up_number" min="1" max="365" value="{{ old('follow_up_number', 7) }}" required></div><div><label for="follow_up_unit">{{ __('current_patient.time_unit') }}</label><select id="follow_up_unit" name="follow_up_unit">@foreach(['days', 'weeks', 'months'] as $unit)<option value="{{ $unit }}" @selected(old('follow_up_unit', 'days') === $unit)>{{ __('current_patient.'.$unit) }}</option>@endforeach</select></div><div class="full-width"><label for="follow_up_reason">{{ __('current_patient.reason_optional') }}</label><input id="follow_up_reason" name="follow_up_reason" value="{{ old('follow_up_reason', app(\App\Services\SpecialtyService::class)->defaultFollowUpReason()) }}"></div></div>
                    <p class="my-2 text-xs text-slate-500">{{ __('current_patient.auto_scheduling_description') }}</p><button class="btn-secondary px-3 py-2">{{ __('current_patient.schedule_appointment') }}</button>
                </form>
        </x-ui.dialog>
        <x-ui.dialog id="finish-visit" :title="__('clinical_ui.finish_visit')" icon="fa-circle-check" tone="blue">
            <p class="dialog-patient">{{ $patient->full_name }}</p>
            <p class="dialog-description">{{ __('clinical_ui.finish_help') }}</p>
            <div class="finish-summary"><span>{{ __('workflow.next_patient') }}</span><strong>{{ $waiting->first()?->patient->full_name ?? __('workflow.no_waiting') }}</strong></div>
            <div class="patient-alert" data-unsaved-warning hidden>{{ __('clinical_ui.save_before_finish') }}</div>
            <div class="dialog-actions"><button type="button" class="btn-secondary" data-dialog-close>{{ __('common.cancel') }}</button><button type="button" class="btn-primary" data-save-before-finish hidden>{{ __('clinical_ui.return_notes') }}</button><form method="POST" action="{{ route('appointments.mark-current-done') }}" data-finish-form>@csrf<input type="hidden" name="appointment_id" value="{{ $currentAppointment->id }}"><button class="btn-care"><i class="fas fa-check"></i>{{ __('current_patient.complete_visit_next_patient') }}</button></form></div>
        </x-ui.dialog>
    @endif
</div>
@endsection
