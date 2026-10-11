<div class="workspace-medication-row rounded-lg border border-slate-200 p-3">
    <div class="workflow-grid">
        @foreach(['medication_name', 'dosage', 'frequency', 'duration_days', 'instructions'] as $field)
        <div><label for="rx_{{ $index }}_{{ $field }}">{{ __('prescriptions.'.$field) }} @if(in_array($field, ['medication_name', 'dosage', 'frequency'])) * @endif</label>
            <input id="rx_{{ $index }}_{{ $field }}" name="items[{{ $index }}][{{ $field }}]" type="{{ $field === 'duration_days' ? 'number' : 'text' }}" value="{{ is_scalar($item[$field] ?? '') ? ($item[$field] ?? '') : '' }}"
            @if(in_array($field, ['medication_name', 'dosage', 'frequency'])) required maxlength="255" @endif @if($field === 'medication_name') list="workspace-medications" autocomplete="off" @endif @if($field === 'duration_days') min="1" max="2147483647" @endif>
        </div>
        @endforeach
        <div class="flex items-end"><button type="button" data-remove-medication class="btn-secondary px-3 py-2">{{ __('common.remove') }}</button></div>
    </div>
</div>
