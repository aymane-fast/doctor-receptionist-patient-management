<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\DataExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(DataExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Show the export form
     */
    public function index()
    {
        // Only doctors can access exports
        if (!Auth::user()->isDoctor()) {
            abort(403, 'Seuls les médecins peuvent accéder aux exports de données.');
        }

        return view('exports.index');
    }

    /**
     * Handle the export request
     */
    public function export(Request $request)
    {
        // Only doctors can export data
        if (!Auth::user()->isDoctor()) {
            abort(403, 'Seuls les médecins peuvent exporter des données.');
        }

        $request->validate([
            'data_types' => 'required|array|min:1',
            'data_types.*' => 'in:patients,appointments,medical_records,prescriptions,users',
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:today',
        ], [
            'data_types.required' => 'Veuillez sélectionner au moins un type de données à exporter.',
            'start_date.before_or_equal' => 'La date de début ne peut pas être dans le futur.',
            'end_date.after_or_equal' => 'La date de fin doit être postérieure à la date de début.',
            'end_date.before_or_equal' => 'La date de fin ne peut pas être dans le futur.',
        ]);

        try {
            // Export the data
            $exportResult = $this->exportService->exportData(
                $request->data_types,
                $request->start_date,
                $request->end_date
            );

            return $this->downloadCSV($exportResult, $request->data_types);

        } catch (\Exception $e) {
            return back()->with('error', 'Échec de l\'export : ' . $e->getMessage());
        }
    }

    /**
     * Download data as CSV
     */
    private function downloadCSV(array $exportResult, array $dataTypes): \Symfony\Component\HttpFoundation\Response
    {
        $csv = $this->exportService->generateCSV($exportResult);
        
        $filename = 'export_clinique_' . implode('_', $dataTypes) . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($csv),
        ]);
    }
}