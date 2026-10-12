@props(['title', 'description' => null, 'icon' => 'fa-stethoscope', 'tone' => 'green'])
<div {{ $attributes->class(['care-section-heading']) }}>
    <span class="care-icon {{ $tone }}"><i class="fas {{ $icon }}" aria-hidden="true"></i></span>
    <div><h2>{{ $title }}</h2>@if($description)<p>{{ $description }}</p>@endif</div>
    {{ $slot }}
</div>
