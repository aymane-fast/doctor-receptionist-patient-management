<form method="POST" action="{{ route('prescriptions.store') }}">
    @csrf
    <input type="hidden" name="workspace_form" value="prescription">
    <input type="hidden" name="patient_id" value="{{ $patient->id }}"><input type="hidden" name="medical_record_id" value="{{ $record->id }}"><input type="hidden" name="return_to_workspace" value="1">
    <label for="prescribed_date">{{ __('prescriptions.prescribed_date') }}</label><input class="mb-3" id="prescribed_date" type="date" name="prescribed_date" value="{{ old('prescribed_date', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required>
    <p class="mb-3 text-xs text-slate-500">{{ __('workflow.medication_help') }} <a href="https://www.ammps.gov.ma/recherche-medicaments" target="_blank" rel="noopener" class="text-teal-700">AMMPS ↗</a></p>
    <datalist id="workspace-medications"></datalist>
    <div id="workspace-items" class="space-y-3">
        @php($rxItems = collect(is_array(old('items')) ? old('items') : [[]])->filter(fn($item) => is_array($item))->take(100)->values())
        @foreach($rxItems as $index => $item)
            @include('clinical.prescription-row', ['index' => $index, 'item' => $item])
        @endforeach
    </div>
    <template id="workspace-item-template">@include('clinical.prescription-row', ['index' => '__INDEX__', 'item' => []])</template>
    <label for="rx_notes" class="mt-3">{{ __('prescriptions.notes') }}</label><textarea id="rx_notes" name="notes" rows="2">{{ old('workspace_form') === 'prescription' ? old('notes') : '' }}</textarea>
    <div class="workflow-actions mt-3"><button type="button" id="workspace-add-item" class="btn-secondary"><i class="fas fa-plus"></i> {{ __('prescriptions.add_another_medication') }}</button><button class="btn-primary">{{ __('prescriptions.create_prescription') }}</button></div>
</form>
@push('scripts')
<script>
    let workspaceItemIndex = {{ $rxItems->count() }};
    const workspaceItems = document.getElementById('workspace-items');
    document.getElementById('workspace-add-item').addEventListener('click', () => {
        if (workspaceItems.children.length >= 100) return;
        workspaceItems.insertAdjacentHTML('beforeend', document.getElementById('workspace-item-template').innerHTML.replaceAll('__INDEX__', workspaceItemIndex++));
    });
    workspaceItems.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-medication]');
        if (button && workspaceItems.children.length > 1) button.closest('.workspace-medication-row').remove();
    });
</script>
@endpush
