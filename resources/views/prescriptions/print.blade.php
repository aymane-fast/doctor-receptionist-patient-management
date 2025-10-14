<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordonnance #{{ $prescription->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: white;
            padding: 0;
            margin: 0;
        }
        
        @page {
            size: A4;
            margin: 20mm 20mm 15mm 20mm;
        }
        
        .prescription-container {
            width: 100%;
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 0;
            position: relative;
        }
        
        /* Header */
        .clinic-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 20px;
            margin: -20mm -20mm 25px -20mm;
            text-align: center;
            border-radius: 0 0 10px 10px;
        }
        
        .clinic-name {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .clinic-info {
            font-size: 10pt;
            opacity: 0.95;
            margin-bottom: 15px;
        }
        
        .prescription-title {
            display: inline-block;
            border: 2px solid white;
            padding: 8px 20px;
            font-size: 18pt;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .prescription-number {
            font-size: 9pt;
            margin-top: 5px;
            opacity: 0.9;
        }
        
        /* Doctor and Date */
        .doctor-date-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ccc;
        }
        
        .doctor-info h3 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #1e40af;
        }
        
        .doctor-info p {
            font-size: 9pt;
            color: #666;
            margin-bottom: 2px;
        }
        
        .date-info {
            text-align: right;
        }
        
        .date-info .date-label {
            font-size: 9pt;
            color: #666;
            margin-bottom: 3px;
        }
        
        .date-info .date-value {
            font-size: 12pt;
            font-weight: bold;
            color: #000;
        }
        
        /* Patient Name */
        .patient-section {
            text-align: center;
            margin-bottom: 25px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 5px;
        }
        
        .patient-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
        }
        
        /* Medications */
        .medications-section {
            margin-bottom: 30px;
        }
        
        .medications-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #1e40af;
            display: flex;
            align-items: center;
        }
        
        .medications-title:before {
            content: "℞";
            font-size: 20pt;
            margin-right: 10px;
            color: #059669;
        }
        
        .medication-item {
            margin-bottom: 15px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            background: white;
        }
        
        .medication-name {
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 8px;
        }
        
        .medication-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 8px;
        }
        
        .detail-item {
            font-size: 10pt;
        }
        
        .detail-label {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 2px;
        }
        
        .detail-value {
            color: #000;
        }
        
        .medication-instructions {
            margin-top: 8px;
            padding: 8px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 3px;
        }
        
        .instructions-label {
            font-weight: bold;
            font-size: 9pt;
            color: #92400e;
            margin-bottom: 3px;
        }
        
        .instructions-text {
            font-size: 9pt;
            color: #92400e;
        }
        
        /* Notes */
        .notes-section {
            margin-bottom: 30px;
        }
        
        .notes-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
        }
        
        .notes-content {
            padding: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 5px;
            font-size: 10pt;
            color: #000;
        }
        
        /* Footer */
        .prescription-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 20px;
            border-top: 1px solid #ccc;
        }
        
        .generation-info {
            font-size: 8pt;
            color: #666;
        }
        
        .signature-section {
            text-align: center;
            min-width: 150px;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            margin-bottom: 5px;
            width: 150px;
        }
        
        .signature-label {
            font-size: 8pt;
            color: #666;
            margin-bottom: 3px;
        }
        
        .doctor-signature {
            font-size: 11pt;
            font-weight: bold;
            color: #000;
        }
        
        /* Print optimizations */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .prescription-container {
                page-break-inside: avoid;
            }
            
            .medication-item {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
        
        /* Hide scrollbars */
        ::-webkit-scrollbar {
            display: none;
        }
        
        html {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body>
    <div class="prescription-container">
        <!-- Clinic Header -->
        <div class="clinic-header">
            <div class="clinic-name">{{ \App\Models\Setting::get('clinic_name', 'CLINIQUE MÉDICALE') }}</div>
            <div class="clinic-info">
                @if(\App\Models\Setting::get('clinic_address'))
                    {{ \App\Models\Setting::get('clinic_address') }}<br>
                @endif
                @if(\App\Models\Setting::get('clinic_phone'))
                    Tél: {{ \App\Models\Setting::get('clinic_phone') }}
                @endif
            </div>
            <div class="prescription-title">ORDONNANCE</div>
            <div class="prescription-number">N° {{ $prescription->id }}</div>
        </div>

        <!-- Doctor and Date -->
        <div class="doctor-date-section">
            <div class="doctor-info">
                <h3>Dr. {{ $prescription->doctor->name }}</h3>
                <p>{{ $prescription->doctor->email }}</p>
                @if($prescription->doctor->phone)
                    <p>{{ $prescription->doctor->phone }}</p>
                @endif
            </div>
            <div class="date-info">
                <div class="date-label">Date:</div>
                <div class="date-value">{{ $prescription->prescribed_date->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}</div>
            </div>
        </div>

        <!-- Patient Name -->
        <div class="patient-section">
            <div class="patient-name">Patient: {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</div>
        </div>

        <!-- Medications -->
        <div class="medications-section">
            <div class="medications-title">Médicaments Prescrits</div>
            
            @if($prescription->items && $prescription->items->count() > 0)
                @foreach($prescription->items as $index => $item)
                <div class="medication-item">
                    <div class="medication-name">{{ $index + 1 }}. {{ $item->medication_name }}</div>
                    <div class="medication-details">
                        <div class="detail-item">
                            <div class="detail-label">Dosage:</div>
                            <div class="detail-value">{{ $item->dosage }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Fréquence:</div>
                            <div class="detail-value">{{ $item->frequency }}</div>
                        </div>
                        @if($item->duration_days)
                        <div class="detail-item">
                            <div class="detail-label">Durée:</div>
                            <div class="detail-value">{{ $item->duration_days }} jours</div>
                        </div>
                        @endif
                    </div>
                    @if($item->instructions)
                    <div class="medication-instructions">
                        <div class="instructions-label">Instructions:</div>
                        <div class="instructions-text">{{ $item->instructions }}</div>
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                <!-- Fallback to old prescription format -->
                <div class="medication-item">
                    <div class="medication-name">1. {{ $prescription->medication_name }}</div>
                    <div class="medication-details">
                        <div class="detail-item">
                            <div class="detail-label">Dosage:</div>
                            <div class="detail-value">{{ $prescription->dosage }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Fréquence:</div>
                            <div class="detail-value">{{ $prescription->frequency }}</div>
                        </div>
                        @if($prescription->duration)
                        <div class="detail-item">
                            <div class="detail-label">Durée:</div>
                            <div class="detail-value">{{ $prescription->duration }}</div>
                        </div>
                        @endif
                    </div>
                    @if($prescription->instructions)
                    <div class="medication-instructions">
                        <div class="instructions-label">Instructions:</div>
                        <div class="instructions-text">{{ $prescription->instructions }}</div>
                    </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Notes -->
        @if($prescription->notes)
        <div class="notes-section">
            <div class="notes-title">Notes Additionnelles</div>
            <div class="notes-content">{{ $prescription->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="prescription-footer">
            <div class="generation-info">
                Prescription générée le {{ now()->locale(app()->getLocale())->isoFormat('D MMMM YYYY [à] HH:mm') }}
            </div>
            <div class="signature-section">
                <div class="signature-label">Signature du médecin</div>
                <div class="signature-line"></div>
                <div class="doctor-signature">Dr. {{ $prescription->doctor->name }}</div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
        
        // Close window after printing
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>


