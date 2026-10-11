@props(['patient', 'compact' => false])
@if($patient->is_pediatric)
    <div class="rounded-xl border border-teal-100 bg-teal-50 p-3 my-2 text-sm break-words">
        <p><strong>{{ __('Child') }}: {{ $patient->full_name }}</strong> · {{ $patient->age_display }} · {{ __('Born') }} {{ $patient->birth_date?->translatedFormat('Y-m-d') ?? '—' }}</p>
        @forelse(($compact ? $patient->guardians->where('is_primary', true) : $patient->guardians) as $guardian)
            <p class="mt-1">{{ $guardian->is_primary ? __('Primary guardian') : __('Guardian') }}: <strong>{{ $guardian->name }}</strong> ({{ __($guardian->relationship) }}) · {{ $guardian->phone }}@if(!$compact && $guardian->email) · {{ $guardian->email }}@endif</p>
        @empty
            <p>{{ __('Guardian details need review. Update the patient profile before contacting the family.') }}</p>
        @endforelse
    </div>
@endif
