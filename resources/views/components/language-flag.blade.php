@props(['language' => null])
@php($flag = ['en' => 'us', 'fr' => 'fr'][$language ?? app()->getLocale()] ?? null)
@if($flag)
    <img src="{{ asset('images/flags/'.$flag.'.svg') }}" width="24" height="16" alt="" aria-hidden="true" {{ $attributes->class(['inline-block shrink-0 rounded-sm border border-slate-200 align-middle'])->style(['width:24px', 'height:16px', 'object-fit:cover']) }}>
@else
    <i class="fas fa-globe" aria-hidden="true"></i>
@endif
