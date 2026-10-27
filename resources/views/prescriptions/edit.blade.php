@extends('layouts.app')

@section('title', __('prescriptions.edit_prescription'))

@section('content')
<datalist id="specialty-medication-suggestions">
</datalist>
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <x-ui.page-header
            :title="__('prescriptions.edit_prescription')"
            :description="__('Update medication details, dosage, and instructions.')"
            icon="fa-prescription-bottle-medical"
            :eyebrow="__('Medication plan')"
            class="mb-6"
        >
            <x-slot:actions>
                <button onclick="history.back()" class="btn-secondary px-4 py-2">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('common.back') }}
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <!-- Edit Form -->
        <div class="section-card p-6">
            <form action="{{ route('prescriptions.update', $prescription) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Patient and Basic Info -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('prescriptions.patient_information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Patient Selection -->
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('prescriptions.patient_name') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="patient_id" name="patient_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('prescriptions.select_patient') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}"
                                            {{ (old('patient_id', $prescription->patient_id) == $patient->id) ? 'selected' : '' }}>
                                        {{ $patient->selection_label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Medical Record Link -->
                        <div>
                            <label for="medical_record_id" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('prescriptions.related_medical_record') }}
                            </label>
                            <select id="medical_record_id" name="medical_record_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('prescriptions.no_medical_record') }}</option>
                                @foreach($medicalRecords as $record)
                                    <option value="{{ $record->id }}"
                                            {{ old('medical_record_id', $prescription->medical_record_id) == $record->id ? 'selected' : '' }}>
                                        {{ $record->patient->first_name }} {{ $record->patient->last_name }} -
                                        {{ \Carbon\Carbon::parse($record->visit_date)->translatedFormat('M j, Y') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('medical_record_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prescribed Date -->
                        <div>
                            <label for="prescribed_date" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('prescriptions.prescribed_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="prescribed_date" name="prescribed_date"
                                   value="{{ old('prescribed_date', $prescription->prescribed_date?->format('Y-m-d')) }}" required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('prescribed_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Medication Items -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('prescriptions.medications') }}</h2>

                    @error('items')
                        <p class="text-red-500 text-sm mb-3">{{ $message }}</p>
                    @enderror

                    @php
                        // Prefer validation-failure input, then saved items, then the
                        // legacy single-medication columns for old prescriptions.
                        $existingItems = old('items');
                        if (!$existingItems) {
                            $existingItems = $prescription->items
                                ->map(fn ($item) => [
                                    'medication_name' => $item->medication_name,
                                    'dosage' => $item->dosage,
                                    'frequency' => $item->frequency,
                                    'duration_days' => $item->duration_days,
                                    'instructions' => $item->instructions,
                                ])
                                ->values()
                                ->all();
                        }
                        if (!$existingItems && $prescription->medication_name) {
                            $existingItems = [[
                                'medication_name' => $prescription->medication_name,
                                'dosage' => $prescription->dosage,
                                'frequency' => $prescription->frequency,
                                'duration_days' => $prescription->duration_days,
                                'instructions' => $prescription->instructions,
                            ]];
                        }
                        if (!$existingItems) {
                            $existingItems = [[
                                'medication_name' => '',
                                'dosage' => '',
                                'frequency' => '',
                                'duration_days' => '',
                                'instructions' => '',
                            ]];
                        }
                        $frequencies = [
                            __('prescriptions.once_daily'),
                            __('prescriptions.twice_daily'),
                            __('prescriptions.three_times_daily'),
                            __('prescriptions.four_times_daily'),
                            __('prescriptions.every_4_hours'),
                            __('prescriptions.every_6_hours'),
                            __('prescriptions.every_8_hours'),
                            __('prescriptions.every_12_hours'),
                            __('prescriptions.as_needed'),
                            __('prescriptions.weekly'),
                            __('prescriptions.other'),
                        ];
                    @endphp

                    <div id="items" class="space-y-4">
                        @foreach($existingItems as $index => $item)
                            <div class="item bg-white border-2 border-gray-200 rounded-xl p-4">
                                <div class="grid md:grid-cols-4 gap-4">
                                    <div>
                                        <label for="rx_{{ $index }}_medication_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('prescriptions.medication_name') }} <span class="text-red-500">*</span></label>
                                        <input type="text" id="rx_{{ $index }}_medication_name" name="items[{{ $index }}][medication_name]" list="specialty-medication-suggestions" required
                                               value="{{ $item['medication_name'] ?? '' }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label for="rx_{{ $index }}_dosage" class="block text-sm font-medium text-gray-700 mb-2">{{ __('prescriptions.dosage') }} <span class="text-red-500">*</span></label>
                                        <input type="text" id="rx_{{ $index }}_dosage" name="items[{{ $index }}][dosage]" required
                                               value="{{ $item['dosage'] ?? '' }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label for="rx_{{ $index }}_frequency" class="block text-sm font-medium text-gray-700 mb-2">{{ __('prescriptions.frequency') }} <span class="text-red-500">*</span></label>
                                        <select id="rx_{{ $index }}_frequency" name="items[{{ $index }}][frequency]" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">{{ __('prescriptions.select_frequency') }}</option>
                                            @foreach($frequencies as $frequency)
                                                <option value="{{ $frequency }}" {{ ($item['frequency'] ?? '') === $frequency ? 'selected' : '' }}>{{ $frequency }}</option>
                                            @endforeach
                                            @if(($item['frequency'] ?? '') && !in_array($item['frequency'], $frequencies, true))
                                                <option value="{{ $item['frequency'] }}" selected>{{ $item['frequency'] }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div>
                                        <label for="rx_{{ $index }}_duration_days" class="block text-sm font-medium text-gray-700 mb-2">{{ __('prescriptions.duration_days') }}</label>
                                        <input type="number" id="rx_{{ $index }}_duration_days" name="items[{{ $index }}][duration_days]" min="1"
                                               value="{{ $item['duration_days'] ?? '' }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                                <div class="mt-3 flex items-end gap-3">
                                    <div class="flex-1">
                                        <label for="rx_{{ $index }}_instructions" class="block text-sm font-medium text-gray-700 mb-2">{{ __('prescriptions.instructions') }}</label>
                                        <input type="text" id="rx_{{ $index }}_instructions" name="items[{{ $index }}][instructions]"
                                               value="{{ $item['instructions'] ?? '' }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <button type="button" aria-label="{{ __('common.remove') }}" class="remove-item bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg transition-colors" title="{{ __('common.remove') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="add-item" class="mt-4 bg-blue-100 hover:bg-blue-200 text-blue-800 px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>{{ __('prescriptions.add_another_medication') }}
                    </button>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('prescriptions.notes') }}
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes', $prescription->notes) }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col justify-end gap-3 sm:flex-row">
                    <button type="button" onclick="history.back()"
                            class="btn-secondary px-6 py-2">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit"
                            class="btn-primary px-6 py-2">
                        <i class="fas fa-save mr-2"></i>{{ __('prescriptions.update_prescription') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const itemsContainer = document.getElementById('items');

    function reindexItems() {
        itemsContainer.querySelectorAll('.item').forEach((item, index) => {
            item.querySelectorAll('input, select').forEach(field => {
                const label = item.querySelector('label[for="' + field.id + '"]');
                field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
                const key = field.name.match(/\[([^\]]+)\]$/)?.[1];
                if (key) {
                    field.id = `rx_${index}_${key}`;
                    if (label) label.htmlFor = field.id;
                }
            });
        });
    }

    itemsContainer.addEventListener('click', function (e) {
        const removeButton = e.target.closest('.remove-item');
        if (!removeButton) return;

        if (itemsContainer.querySelectorAll('.item').length <= 1) {
            alert('{{ __('prescriptions.at_least_one_medication') }}');
            return;
        }

        removeButton.closest('.item').remove();
        reindexItems();
    });

    document.getElementById('add-item').addEventListener('click', function () {
        const template = itemsContainer.querySelector('.item');
        const clone = template.cloneNode(true);

        clone.querySelectorAll('input').forEach(input => input.value = '');
        clone.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

        itemsContainer.appendChild(clone);
        reindexItems();
    });
});
</script>
@endsection
