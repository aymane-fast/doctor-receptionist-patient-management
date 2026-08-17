@props([
    'title',
    'description' => null,
    'icon' => 'fa-layer-group',
    'eyebrow' => null,
])

<section {{ $attributes->class(['page-header']) }}>
    <div class="page-header-copy">
        <span class="page-header-icon" aria-hidden="true">
            <i class="fas {{ $icon }}"></i>
        </span>
        <div class="min-w-0">
            @if($eyebrow)
                <span class="page-header-eyebrow">{{ $eyebrow }}</span>
            @endif
            <h1 class="page-header-title">{{ $title }}</h1>
            @if($description)
                <p class="page-header-description">{{ $description }}</p>
            @endif
        </div>
    </div>

    @if(isset($actions))
        <div class="page-header-actions">
            {{ $actions }}
        </div>
    @endif
</section>
