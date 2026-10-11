@php
    $orders = $orders ?? \App\Models\Order::where('doctor_id', auth()->id())->where('patient_id', $patient->id)
        ->when($medicalRecord, fn($q) => $q->where('medical_record_id', $medicalRecord->id))->latest()->limit(30)->get();
    $documents = $documents ?? \App\Models\MedicalDocument::where('doctor_id', auth()->id())->where('patient_id', $patient->id)
        ->when($medicalRecord, fn($q) => $q->where('medical_record_id', $medicalRecord->id))->latest()->limit(30)->get();
@endphp
<section id="examinations" class="workflow-panel">
    <h2><i class="fas fa-flask mr-2 text-teal-700"></i>{{ __('workflow.examinations') }}</h2>
    <form method="POST" action="{{ route('clinical.orders.store', $patient) }}">@csrf
        <input type="hidden" name="workspace_form" value="examinations">
        <input type="hidden" name="medical_record_id" value="{{ $medicalRecord?->id }}">
        <div class="workflow-grid">
            <div><label for="order_type">{{ __('workflow.examination_type') }}</label><select name="order_type" id="order_type"><option value="lab">{{ __('workflow.lab') }}</option><option value="scan">{{ __('workflow.scan') }}</option></select></div>
            <div><label for="test_name">{{ __('workflow.examination_name') }}</label><input id="test_name" name="test_name" list="examination-suggestions" maxlength="255" value="{{ old('test_name') }}" required></div>
            <div class="full-width"><label for="order_notes">{{ __('workflow.instructions') }}</label><input id="order_notes" name="notes" value="{{ old('workspace_form') === 'examinations' ? old('notes') : '' }}" maxlength="16000"></div>
        </div>
        <datalist id="examination-suggestions">@foreach(['cbc', 'glucose', 'renal', 'lipid', 'liver', 'urine', 'xray', 'ultrasound', 'ct', 'mri', 'ecg'] as $exam)<option value="{{ __('workflow.exam_'.$exam) }}"></option>@endforeach</datalist>
        <button class="btn-secondary mt-3 px-3 py-2">{{ __('workflow.request_examination') }}</button>
    </form>
    @foreach($orders as $order)
        <div class="clinical-row flex flex-wrap justify-between gap-2"><div><strong>{{ $order->test_name }}</strong><p class="text-xs text-slate-500">{{ $order->requested_date->format('d/m/Y') }} · {{ __('workflow.status_'.$order->status) }}</p></div>
        <div class="workflow-actions"><a class="text-sm text-teal-700" target="_blank" rel="noopener" href="{{ route('clinical.orders.print', $order) }}">{{ __('common.print') }}</a><form class="flex gap-2" method="POST" action="{{ route('clinical.orders.update', $order) }}">@csrf @method('PATCH')<select name="status" aria-label="{{ __('common.status') }}">@foreach(['requested', 'completed', 'cancelled'] as $status)<option value="{{ $status }}" @selected($order->status === $status)>{{ __('workflow.status_'.$status) }}</option>@endforeach</select><button class="btn-secondary">{{ __('common.save') }}</button></form></div></div>
    @endforeach
    @if($orders instanceof \Illuminate\Contracts\Pagination\Paginator)<div class="mt-3">{{ $orders->links() }}</div>@else<a class="mt-3 inline-block text-sm text-teal-700" href="{{ route('clinical.history', ['patient' => $patient, 'medical_record_id' => $medicalRecord?->id]) }}#examinations">{{ __('workflow.view_all_examinations') }}</a>@endif
</section>
<section id="documents" class="workflow-panel">
    <h2><i class="fas fa-file-medical mr-2 text-teal-700"></i>{{ __('workflow.documents') }}</h2>
    <p class="mb-3 text-xs text-slate-500">{{ __('workflow.document_help') }}</p>
    <form method="POST" enctype="multipart/form-data" action="{{ route('clinical.documents.store', $patient) }}">@csrf
        <input type="hidden" name="medical_record_id" value="{{ $medicalRecord?->id }}">
        <div class="workflow-grid"><div><label for="document_title">{{ __('workflow.document_title') }}</label><input id="document_title" name="title" maxlength="255" value="{{ old('title') }}" required></div><div><label for="document_file">{{ __('workflow.file') }}</label><input id="document_file" name="document" type="file" accept="application/pdf,image/jpeg,image/png" required></div>
        <div class="full-width"><label for="document_order">{{ __('workflow.link_result') }}</label><select id="document_order" name="order_id"><option value="">{{ __('common.optional') }}</option>@foreach($orders as $order)<option value="{{ $order->id }}">#{{ $order->id }} · {{ $order->test_name }}</option>@endforeach</select></div></div>
        <button class="btn-secondary mt-3 px-3 py-2">{{ __('workflow.upload_document') }}</button>
    </form>
    @foreach($documents as $document)<div class="clinical-row flex justify-between gap-3"><span>{{ $document->title }} <small class="text-slate-500">{{ $document->created_at->format('d/m/Y') }} · {{ round($document->size / 1024) }} KB @if($document->order_id) · {{ __('workflow.result_for') }} #{{ $document->order_id }} @endif</small></span><a class="text-teal-700 text-sm" target="_blank" rel="noopener" href="{{ route('clinical.documents.view', $document) }}">{{ __('common.view') }}</a></div>@endforeach
    @if($documents instanceof \Illuminate\Contracts\Pagination\Paginator)<div class="mt-3">{{ $documents->links() }}</div>@else<a class="mt-3 inline-block text-sm text-teal-700" href="{{ route('clinical.history', ['patient' => $patient, 'medical_record_id' => $medicalRecord?->id]) }}#documents">{{ __('workflow.view_all_documents') }}</a>@endif
</section>
