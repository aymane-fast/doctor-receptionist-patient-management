@props(['id', 'title', 'icon' => 'fa-circle-info', 'tone' => 'green', 'form' => null])
<dialog id="{{ $id }}" class="care-dialog" aria-labelledby="{{ $id }}-title">
    <header class="dialog-heading"><span class="care-icon {{ $tone }}"><i class="fas {{ $icon }}" aria-hidden="true"></i></span><h2 id="{{ $id }}-title">{{ $title }}</h2><button type="button" class="dialog-close" data-dialog-close aria-label="{{ __('common.cancel') }}"><i class="fas fa-xmark"></i></button></header>
    <div class="dialog-body">
        @if($form && old('workspace_form') === $form && $errors->any())
            <div class="patient-alert mb-4" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        {{ $slot }}
    </div>
</dialog>
