@php($active = \App\Models\Appointment::with('patient')->where('doctor_id', auth()->id())->today()->where('status', 'in_progress')->first())
@if($active)
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-teal-200 bg-teal-50 p-3 text-sm">
        <span>{{ __('workflow.active_consultation') }}: <strong>{{ $active->patient->full_name }}</strong> · #{{ $active->id }}</span>
        <a href="{{ route('doctor.current') }}" class="font-semibold text-teal-800"><i class="fas fa-arrow-left mr-1"></i>{{ __('workflow.back_workspace') }}</a>
    </div>
@endif
