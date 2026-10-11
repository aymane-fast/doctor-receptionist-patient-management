@extends('layouts.app')
@section('title', __('workflow.clinical_tools_history'))
@section('content')
<div class="space-y-4">
    <x-ui.page-header :title="__('workflow.clinical_tools_history')" :description="$patient->full_name" icon="fa-file-medical">
        <x-slot:actions><a class="btn-secondary px-3 py-2" href="{{ route('patients.show', $patient) }}">{{ __('current_patient.view_full_patient_history') }}</a></x-slot:actions>
    </x-ui.page-header>
    @include('clinical.context-bar')
    @if($medicalRecord)<p class="text-sm">{{ __('workflow.historical') }} #{{ $medicalRecord->id }} · {{ $medicalRecord->visit_date->format('d/m/Y') }}</p>@endif
    @include('clinical.tools')
</div>
@endsection
