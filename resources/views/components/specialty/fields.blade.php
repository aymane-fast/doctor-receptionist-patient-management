@props([
    'fields' => [],
    'values' => [],
    'title' => 'Specialty information',
    'description' => 'Optional details used by this practice profile.',
    'icon' => 'fa-stethoscope',
])

@if(! empty($fields))
    <section class="rounded-2xl border border-teal-100 bg-teal-50/40 p-5">
        <div class="mb-5 flex items-start gap-3">
            <div class="icon-tile flex h-10 w-10 shrink-0 items-center justify-center rounded-xl">
                <i class="fas {{ $icon }}"></i>
            </div>
            <div>
        <h3 class="text-lg font-semibold text-slate-900">{{ __($title) }}</h3>
        <p class="mt-1 text-sm text-slate-600">{{ __($description) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach($fields as $field)
                @continue(in_array($field['key'], ['guardian_name', 'guardian_relationship'], true))
                @php
                    $key = $field['key'];
                    $errorKey = 'specialty_data.'.$key;
                    $value = old($errorKey, data_get($values, $key));
                    $isWide = ($field['type'] ?? 'text') === 'textarea';
                @endphp
                <div>
                    <label for="specialty_{{ $key }}" class="mb-2 block text-sm font-semibold text-slate-700">
                        {{ __($field['label']) }}
                        <span class="font-normal text-slate-400">({{ __('common.optional') }})</span>
                    </label>

                    @if(($field['type'] ?? 'text') === 'textarea')
                        <textarea id="specialty_{{ $key }}" name="specialty_data[{{ $key }}]" rows="2"
                            class="form-control w-full @error($errorKey) border-red-500 @enderror">{{ $value }}</textarea>
                    @elseif(($field['type'] ?? 'text') === 'select')
                        <select id="specialty_{{ $key }}" name="specialty_data[{{ $key }}]"
                            class="form-control w-full @error($errorKey) border-red-500 @enderror">
                            <option value="">{{ __('common.select') }}</option>
                            @foreach($field['options'] ?? [] as $option)
                                <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ __($option) }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="specialty_{{ $key }}" name="specialty_data[{{ $key }}]" type="{{ $field['type'] ?? 'text' }}"
                            value="{{ $value }}" class="form-control w-full @error($errorKey) border-red-500 @enderror">
                    @endif

                    @error($errorKey)
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </section>
@endif
