@props([
    'fields' => [],
    'values' => [],
    'title' => 'Specialty information',
    'icon' => 'fa-stethoscope',
])

@php
    $displayFields = collect($fields)->filter(function ($field) use ($values) {
        return filled(data_get($values, $field['key']));
    });
@endphp

@if($displayFields->isNotEmpty())
    <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5">
        <h3 class="flex items-center gap-2 text-lg font-semibold text-slate-900">
            <i class="fas {{ $icon }} text-teal-700"></i>{{ $title }}
        </h3>
        <dl class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach($displayFields as $field)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $field['label'] }}</dt>
                    <dd class="mt-1 whitespace-pre-wrap text-sm text-slate-800">{{ data_get($values, $field['key']) }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
@endif
