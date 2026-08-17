@props([
    'title',
    'description' => null,
    'icon' => 'fa-circle',
])

<div {{ $attributes->class(['section-heading']) }}>
    <span class="section-heading-icon" aria-hidden="true">
        <i class="fas {{ $icon }}"></i>
    </span>
    <div>
        <h2 class="section-heading-title">{{ $title }}</h2>
        @if($description)
            <p class="section-heading-description">{{ $description }}</p>
        @endif
    </div>
</div>
