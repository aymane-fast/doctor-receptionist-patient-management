@props(['action', 'patient' => null])
<section class="section-card clinical-filter-card">
    <form method="GET" action="{{ $action }}" class="clinical-filters" data-clinical-filters data-search-url="{{ route('api.patients.search') }}">
        <div class="patient-filter">
            <label for="patient_search">{{ __('clinical_ui.patient_search') }}</label>
            <div class="field-with-icon"><i class="fas fa-search" aria-hidden="true"></i><input id="patient_search" name="search" type="search" maxlength="255" value="{{ request('search', $patient?->full_name) }}" autocomplete="off" aria-controls="filter-patients" aria-expanded="false"></div>
            <input type="hidden" name="patient_id" value="{{ request('patient_id') }}" data-patient-id>
            <div id="filter-patients" class="filter-suggestions" hidden></div>
        </div>
        <div><label for="from_date">{{ __('clinical_ui.from_date') }}</label><input id="from_date" name="from_date" type="date" value="{{ request('from_date', request('date')) }}"></div>
        <div><label for="to_date">{{ __('clinical_ui.to_date') }}</label><input id="to_date" name="to_date" type="date" value="{{ request('to_date', request('date')) }}"></div>
        <button class="btn-primary"><i class="fas fa-search"></i>{{ __('clinical_ui.search') }}</button>
        <div class="filter-footer"><span>{{ __('clinical_ui.filter_help') }}</span>@if(request()->hasAny(['search','date','from_date','to_date','patient_id']))<a class="text-link" href="{{ $action }}"><i class="fas fa-xmark"></i>{{ __('clinical_ui.clear') }}</a>@endif</div>
    </form>
</section>
