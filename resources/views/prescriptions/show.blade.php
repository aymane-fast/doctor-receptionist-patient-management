@extends('layouts.app')

@section('title', __('prescriptions.prescription_details'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between mb-6 print:hidden">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('prescriptions.prescription_details') }}</h1>
        <div class="flex space-x-3">
            <button onclick="history.back()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('common.back') }}
            </button>
            <a href="{{ route('prescriptions.edit', $prescription) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-edit mr-2"></i>{{ __('common.edit') }}
            </a>
            <a href="{{ route('prescriptions.print', $prescription) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-print mr-2"></i>{{ __('common.print') }}
            </a>
        </div>
    </div>

    <!-- Professional Prescription Layout -->
    <div class="max-w-4xl mx-auto bg-white shadow-2xl rounded-lg overflow-hidden print:shadow-none print:max-w-none">
        <!-- Clinic Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ \App\Models\Setting::get('clinic_name', 'CLINIQUE MÉDICALE') }}</h1>
                    @if(\App\Models\Setting::get('clinic_address'))
                        <p class="text-blue-100 mb-1">{{ \App\Models\Setting::get('clinic_address') }}</p>
                    @endif
                    <div class="flex space-x-4 text-sm text-blue-100">
                        @if(\App\Models\Setting::get('clinic_phone'))
                            <span><i class="fas fa-phone mr-1"></i>{{ \App\Models\Setting::get('clinic_phone') }}</span>
                        @endif
                        @if(\App\Models\Setting::get('clinic_email'))
                            <span><i class="fas fa-envelope mr-1"></i>{{ \App\Models\Setting::get('clinic_email') }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold border-2 border-white px-4 py-2 rounded">
                        ORDONNANCE
                    </div>
                    <p class="text-sm text-blue-100 mt-2">N° {{ $prescription->id }}</p>
                </div>
            </div>
        </div>

        <!-- Prescription Content -->
        <div class="p-8">
            <!-- Doctor and Date Info -->
            <div class="flex justify-between items-start mb-6 border-b pb-4">
                <div>
                    <h3 class="font-bold text-base text-gray-800 mb-1">Dr. {{ $prescription->doctor->name }}</h3>
                    <p class="text-gray-600 text-sm">{{ $prescription->doctor->email }}</p>
                    @if($prescription->doctor->phone)
                        <p class="text-gray-600 text-sm">{{ $prescription->doctor->phone }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-gray-600 text-sm">Date:</p>
                    <p class="font-semibold">{{ $prescription->prescribed_date->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}</p>
                </div>
            </div>

            <!-- Patient Name Only -->
            <div class="mb-5 text-center">
                <h3 class="text-base font-bold text-gray-800">
                    Patient: {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}
                </h3>
            </div>

            <!-- Prescription Items -->
            <div class="mb-6">
                <h3 class="font-bold text-base text-gray-800 mb-4 flex items-center border-b pb-2">
                    <i class="fas fa-pills text-green-600 mr-2"></i>
                    Médicaments Prescrits
                </h3>
                
                @if($prescription->items && $prescription->items->count() > 0)
                    <div class="space-y-4">
                        @foreach($prescription->items as $index => $item)
                        <div class="border border-gray-200 rounded-lg p-4 bg-white">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h4 class="text-base font-bold text-gray-800 mb-2">
                                        {{ $index + 1 }}. {{ $item->medication_name }}
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-gray-700 text-sm">
                                        <div>
                                            <span class="font-semibold text-blue-600">Dosage:</span>
                                            <p>{{ $item->dosage }}</p>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-blue-600">Fréquence:</span>
                                            <p>{{ $item->frequency }}</p>
                                        </div>
                                        @if($item->duration_days)
                                        <div>
                                            <span class="font-semibold text-blue-600">Durée:</span>
                                            <p>{{ $item->duration_days }} jours</p>
                                        </div>
                                        @endif
                                    </div>
                                    @if($item->instructions)
                                    <div class="mt-2 p-2 bg-yellow-50 border-l-4 border-yellow-400">
                                        <span class="font-semibold text-yellow-800 text-sm">Instructions:</span>
                                        <p class="text-yellow-800 text-sm">{{ $item->instructions }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <!-- Fallback to old prescription format if no items -->
                    <div class="border border-gray-200 rounded-lg p-4 bg-white">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="text-base font-bold text-gray-800 mb-2">
                                    1. {{ $prescription->medication_name }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-gray-700 text-sm">
                                    <div>
                                        <span class="font-semibold text-blue-600">Dosage:</span>
                                        <p>{{ $prescription->dosage }}</p>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-blue-600">Fréquence:</span>
                                        <p>{{ $prescription->frequency }}</p>
                                    </div>
                                    @if($prescription->duration)
                                    <div>
                                        <span class="font-semibold text-blue-600">Durée:</span>
                                        <p>{{ $prescription->duration }}</p>
                                    </div>
                                    @endif
                                </div>
                                @if($prescription->instructions)
                                <div class="mt-2 p-2 bg-yellow-50 border-l-4 border-yellow-400">
                                    <span class="font-semibold text-yellow-800 text-sm">Instructions:</span>
                                    <p class="text-yellow-800 text-sm">{{ $prescription->instructions }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Additional Notes -->
            @if($prescription->notes)
            <div class="mb-6">
                <h3 class="font-bold text-sm text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-sticky-note text-orange-600 mr-2"></i>
                    Notes Additionnelles
                </h3>
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                    <p class="text-gray-700 text-sm">{{ $prescription->notes }}</p>
                </div>
            </div>
            @endif

            <!-- Footer -->
            <div class="border-t pt-4 mt-6">
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-xs text-gray-600">Prescription générée le {{ now()->locale(app()->getLocale())->isoFormat('D MMMM YYYY [à] HH:mm') }}</p>
                    </div>
                    <div class="text-right">
                        <div class="border-t-2 border-gray-400 pt-1 mt-6" style="min-width: 180px;">
                            <p class="text-xs text-gray-600">Signature du médecin</p>
                            <p class="font-semibold text-sm mt-1">Dr. {{ $prescription->doctor->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print-specific styling -->
    <style>
        @media print {
            body { font-size: 10pt; }
            .print\\:hidden { display: none !important; }
            .print\\:shadow-none { box-shadow: none !important; }
            .print\\:max-w-none { max-width: none !important; }
            @page { margin: 0.5in; }
        }
    </style>
</div>
@endsection
