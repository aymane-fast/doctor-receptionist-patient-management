@props(['patient' => null])
@php
    $rows = old('guardians', $patient?->guardians->map->only(['name', 'relationship', 'phone', 'email'])->values()->all() ?: [['name' => '', 'relationship' => '', 'phone' => '', 'email' => '']]);
    $rows = is_array($rows) ? array_values($rows) : [[]];
@endphp
<section class="p-8" id="guardian-form">
    <h3 class="text-2xl font-bold">{{ __('Parent / guardian contacts') }}</h3>
    <p class="text-sm text-gray-600 mt-2">{{ __('The child is the patient. Choose one primary contact for calls and appointment reminders. Other contacts are not sent reminders automatically.') }}</p>
    @error('guardians')<p class="text-red-600">{{ $message }}</p>@enderror
    @error('primary_guardian')<p class="text-red-600">{{ $message }}</p>@enderror
    <div data-guardian-rows>
        @foreach($rows as $index => $row)
            <fieldset class="border rounded-xl p-4 mt-4" data-guardian-row>
                <legend>{{ __('Guardian') }} <span data-number>{{ $index + 1 }}</span></legend>
                <label class="flex gap-2 mb-3"><input type="radio" name="primary_guardian" value="{{ $index }}" @checked((int) old('primary_guardian', 0) === $index) required> {{ __('Primary contact') }}</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(['name' => 'Full name', 'relationship' => 'Relationship to child', 'phone' => 'Phone', 'email' => 'Email (optional)'] as $field => $label)
                        <label class="block text-sm font-semibold">{{ __($label) }}
                            <input class="form-control w-full mt-1" data-field="{{ $field }}" name="guardians[{{ $index }}][{{ $field }}]" type="{{ $field === 'email' ? 'email' : ($field === 'phone' ? 'tel' : 'text') }}" value="{{ is_array($row) ? ($row[$field] ?? '') : '' }}" maxlength="{{ $field === 'relationship' ? 100 : 255 }}" @required($field !== 'email')>
                            @error('guardians.'.$index.'.'.$field)<span class="text-red-600">{{ $message }}</span>@enderror
                        </label>
                    @endforeach
                </div>
                <button type="button" data-remove class="btn-secondary mt-3">{{ __('Remove guardian') }}</button>
            </fieldset>
        @endforeach
    </div>
    <button type="button" data-add class="btn-secondary mt-4">{{ __('Add another guardian') }}</button>
</section>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('guardian-form');
    const rows = form.querySelector('[data-guardian-rows]');
    const add = form.querySelector('[data-add]');
    const update = () => {
        [...rows.children].forEach((row, index) => {
            row.querySelector('[data-number]').textContent = index + 1;
            row.querySelector('[type=radio]').value = index;
            row.querySelectorAll('[data-field]').forEach(input => input.name = `guardians[${index}][${input.dataset.field}]`);
            row.querySelector('[data-remove]').disabled = rows.children.length === 1;
        });
        if (!rows.querySelector('[type=radio]:checked')) rows.querySelector('[type=radio]').checked = true;
        add.disabled = rows.children.length >= 5;
    };
    add.addEventListener('click', () => {
        if (rows.children.length >= 5) return;
        const row = rows.firstElementChild.cloneNode(true);
        row.querySelectorAll('input').forEach(input => { if (input.type === 'radio') input.checked = false; else input.value = ''; });
        row.querySelectorAll('.text-red-600').forEach(error => error.remove());
        rows.append(row);
        update();
        row.querySelector('[data-field]').focus();
    });
    rows.addEventListener('click', event => {
        if (event.target.closest('[data-remove]') && rows.children.length > 1) {
            event.target.closest('[data-guardian-row]').remove();
            update();
        }
    });
    update();
});
</script>
@endpush
