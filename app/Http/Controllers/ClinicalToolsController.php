<?php

namespace App\Http\Controllers;

use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use App\Models\Order;
use App\Models\Patient;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClinicalToolsController extends Controller
{
    public function history(Request $request, Patient $patient)
    {
        $request->validate(['medical_record_id' => 'nullable|integer']);
        $medicalRecord = $this->record($request, $patient);
        $scope = fn ($query) => $query->where('doctor_id', auth()->id())->where('patient_id', $patient->id)
            ->when($medicalRecord, fn ($q) => $q->where('medical_record_id', $medicalRecord->id));
        $orders = $scope(Order::query())->latest()->orderByDesc('id')->paginate(20, ['*'], 'orders_page')->withQueryString();
        $documents = $scope(MedicalDocument::query())->latest()->orderByDesc('id')->paginate(20, ['*'], 'documents_page')->withQueryString();

        return view('clinical.history', compact('patient', 'medicalRecord', 'orders', 'documents'));
    }

    private function record(Request $request, Patient $patient): ?MedicalRecord
    {
        if (! $request->filled('medical_record_id')) {
            return null;
        }

        return MedicalRecord::where('doctor_id', auth()->id())->where('patient_id', $patient->id)
            ->findOrFail($request->medical_record_id);
    }

    public function storeOrder(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'medical_record_id' => 'nullable|integer', 'order_type' => 'required|in:lab,scan',
            'test_name' => 'required|string|max:255', 'notes' => 'nullable|string|max:16000',
        ]);
        $record = $this->record($request, $patient);
        Order::create($data + ['patient_id' => $patient->id, 'doctor_id' => auth()->id(), 'requested_date' => today(), 'status' => 'requested']);

        return back()->with('success', __('workflow.order_saved'));
    }

    public function updateOrder(Request $request, Order $order)
    {
        abort_unless($order->doctor_id === auth()->id(), 403);
        $data = $request->validate(['status' => 'required|in:requested,completed,cancelled']);
        $order->update($data);

        return back()->with('success', __('workflow.order_saved'));
    }

    public function printOrder(Order $order)
    {
        abort_unless($order->doctor_id === auth()->id(), 403);
        $order->load('patient', 'doctor');

        return view('clinical.order-print', ['order' => $order, 'clinic' => \App\Models\Setting::getClinicInfo()]);
    }

    public function storeDocument(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255', 'medical_record_id' => 'nullable|integer', 'order_id' => 'nullable|integer',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
        $record = $this->record($request, $patient);
        if ($request->filled('order_id')) {
            $order = Order::where('doctor_id', auth()->id())->where('patient_id', $patient->id)->findOrFail($request->order_id);
            if ($record && $order->medical_record_id && $order->medical_record_id !== $record->id) {
                throw ValidationException::withMessages(['order_id' => __('workflow.context_mismatch')]);
            }
            $data['medical_record_id'] = $record?->id ?? $order->medical_record_id;
        }
        $file = $request->file('document');
        $path = $file->store('medical-documents', 'local');
        if (! $path) {
            throw ValidationException::withMessages(['document' => __('workflow.upload_failed')]);
        }
        unset($data['document']);
        try {
            MedicalDocument::create($data + [
                'patient_id' => $patient->id, 'doctor_id' => auth()->id(), 'path' => $path,
                'mime_type' => $file->getMimeType(), 'size' => $file->getSize(),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return back()->with('success', __('workflow.document_saved'));
    }

    public function viewDocument(Request $request, MedicalDocument $document)
    {
        abort_unless($document->doctor_id === auth()->id(), 403);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        // No public storage URL; every view/download is authenticated.
        return Storage::disk('local')->response($document->path, 'document-'.$document->id.'.'.pathinfo($document->path, PATHINFO_EXTENSION), [
            'Content-Type' => $document->mime_type, 'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store', 'Content-Security-Policy' => "sandbox; default-src 'none'",
        ], $request->boolean('download') ? 'attachment' : 'inline');
    }

    public function medications(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);
        // Reuse this doctor's own prescription vocabulary. These are not
        // assertions of marketing authorization or recommended dosages.
        $names = PrescriptionItem::whereHas('prescription', fn ($q) => $q->where('doctor_id', auth()->id()))
            ->where('medication_name', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->q).'%')
            ->select('medication_name')->distinct()->orderBy('medication_name')->limit(15)->pluck('medication_name');

        return response()->json($names);
    }
}
