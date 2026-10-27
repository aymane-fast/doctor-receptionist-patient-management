@extends('layouts.app')

@section('title', __('workflow.appointment_openings'))

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        :title="__('workflow.appointment_openings')"
        :description="__('workflow.appointment_openings_help')"
        icon="fa-hourglass-half"
        :eyebrow="__('Scheduling support')"
    >
        <x-slot:actions>
            <a href="{{ route('appointments.index') }}" class="btn-secondary px-4 py-2">
                <i class="fas fa-calendar-alt text-sm mr-2"></i>{{ __('appointments.title') ?? 'Appointments' }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <!-- Add to waitlist -->
    <div class="section-card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-user-plus text-teal-700 mr-2"></i>{{ __('appointments.add_to_waitlist') }}
        </h2>

        @error('patient_id')
            <p class="text-red-600 text-sm mb-3"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('waitlist.store') }}" class="grid md:grid-cols-5 gap-4 items-end">
            @csrf
            <div class="md:col-span-2">
                <label for="patient_search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('appointments.patient') }} <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" id="patient_search" autocomplete="off" value="{{ $selectedPatient?->full_name }}"
                           data-confirm-patient="{{ __('Please select a patient.') }}"
                           placeholder="{{ __('appointments.search_patients_placeholder') }}"
                           class="form-control w-full px-3 py-2">
                    <input type="hidden" id="patient_id" name="patient_id" value="{{ $selectedPatient?->id }}" required>
                    <div id="patient_results" class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
                </div>
            </div>

            @if(auth()->user()->isReceptionist())
            <div>
                <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('appointments.doctor') }} <span class="text-red-500">*</span></label>
                <select id="doctor_id" name="doctor_id" required class="form-control w-full px-3 py-2">
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((int) old('doctor_id') === $doctor->id)>{{ $doctor->professional_name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label for="preferred_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('appointments.preferred_date') }}</label>
                <input type="date" id="preferred_date" name="preferred_date" value="{{ old('preferred_date') }}" min="{{ today()->toDateString() }}" class="form-control w-full px-3 py-2">
            </div>

            <div>
                <button type="submit" class="btn-primary w-full px-4 py-2">
                    <i class="fas fa-plus mr-2"></i>{{ __('appointments.add_to_waitlist') }}
                </button>
            </div>

            <div class="md:col-span-5">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">{{ __($clinicSpecialty['label']) }} {{ strtolower(__('appointments.reason')) }}</label>
                <input type="text" id="reason" name="reason" maxlength="500" list="specialty-appointment-reasons" value="{{ old('reason') }}" class="form-control w-full px-3 py-2">
                <datalist id="specialty-appointment-reasons">
                    @foreach($clinicSpecialty['appointment_reasons'] as $reason)
                        <option value="{{ __($reason) }}"></option>
                    @endforeach
                </datalist>
            </div>
        </form>
    </div>

    <!-- Waiting entries -->
    <div class="section-card overflow-hidden">
        @if($entries->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('appointments.patient') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('appointments.doctor') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('appointments.preferred_date') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('appointments.reason') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('appointments.waiting_since') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($entries as $entry)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $entry->patient->full_name }}</div>
                                <div class="text-sm text-gray-500">{{ $entry->patient->phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $entry->doctor->professional_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $entry->preferred_date?->translatedFormat('M j, Y') ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $entry->reason ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('waitlist.book', $entry) }}">
                                        @csrf
                                        <button class="btn-primary px-3 py-2 text-sm">
                                            <i class="fas fa-calendar-check mr-1"></i>{{ __('appointments.book_next_free_slot') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('waitlist.cancel', $entry) }}">
                                        @csrf
                                        <button class="btn-danger px-3 py-2 text-sm" onclick="return confirm(@js(__('appointments.waitlist_removed').'?'))">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
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
                    <i class="fas fa-hourglass-half text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">{{ __('workflow.no_openings_waitlist') }}</h3>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('patient_search');
    const patientIdInput = document.getElementById('patient_id');
    const resultsContainer = document.getElementById('patient_results');
    let searchTimeout;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
        })[character]);
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        patientIdInput.value = '';

        if (query.length < 2) {
            resultsContainer.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('api.patients.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(patients => {
                    if (searchInput.value.trim() !== query) return;
                    if (!patients.length) {
                        resultsContainer.innerHTML = '<div class="p-3 text-gray-500 text-center">—</div>';
                    } else {
                        resultsContainer.innerHTML = patients.map(patient => `
                            <button type="button" class="patient-result block w-full text-left p-3 hover:bg-teal-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                 data-id="${patient.id}" data-display="${escapeHtml(patient.display)}">
                                <div class="font-medium text-gray-900">${escapeHtml(patient.name)}</div>
                                <div class="text-sm text-gray-600">${escapeHtml(patient.phone)}</div>
                            </button>
                        `).join('');

                        resultsContainer.querySelectorAll('.patient-result').forEach(result => {
                            result.addEventListener('click', function () {
                                patientIdInput.value = this.dataset.id;
                                searchInput.value = this.dataset.display;
                                resultsContainer.classList.add('hidden');
                            });
                        });
                    }
                    resultsContainer.classList.remove('hidden');
                })
                .catch(() => resultsContainer.classList.add('hidden'));
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('hidden');
        }
    });

    document.querySelector('form[action="{{ route('waitlist.store') }}"]').addEventListener('submit', function (e) {
        if (!patientIdInput.value) {
            e.preventDefault();
            alert(@json(__('Please select a patient.')));
            searchInput.focus();
        }
    });
});
</script>
@endsection
