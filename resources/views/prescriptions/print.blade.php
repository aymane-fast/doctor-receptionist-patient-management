@php
    $clinicName = trim($clinic['name'] ?? '') ?: __('common.medical_management');
    $patient = $prescription->patient;
    $doctor = $prescription->doctor;
    $medications = $prescription->items;

    if ($medications->isEmpty() && $prescription->medication_name) {
        $medications = collect([(object) [
            'medication_name' => $prescription->medication_name,
            'dosage' => $prescription->dosage,
            'frequency' => $prescription->frequency,
            'duration_days' => $prescription->duration_days,
            'instructions' => $prescription->instructions,
        ]]);
    }

    $reference = sprintf('RX-%s-%06d', $prescription->prescribed_date->format('Y'), $prescription->id);
    $doctorContacts = collect([$doctor->phone, $doctor->email])
        ->filter(fn ($value) => filled($value))
        ->implode('  |  ');
    $clinicContacts = collect([$clinic['phone'] ?? null, $clinic['email'] ?? null, $clinic['website'] ?? null])
        ->filter(fn ($value) => filled($value))
        ->implode('  |  ');
    $generatedDate = now()->locale(app()->getLocale())->isoFormat(
        app()->isLocale('fr') ? 'D MMMM YYYY [à] HH:mm' : 'D MMMM YYYY [at] HH:mm'
    );
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ __('prescriptions.print_title') }} - {{ $reference }}</title>
    <style>
        :root {
            --teal-950: #073f43;
            --teal-800: #096c70;
            --teal-600: #0ca6a4;
            --teal-400: #36c8be;
            --teal-100: #e4f7f4;
            --ink: #183238;
            --muted: #6f7f82;
            --line: #c9dcda;
            --paper: #ffffff;
            --canvas: #e8f0ef;
            --alert: #b87524;
            --alert-soft: #fff8e9;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            color: var(--ink);
            background: var(--canvas);
            font-family: "Trebuchet MS", "Segoe UI", sans-serif;
            font-size: 9.5pt;
            line-height: 1.42;
            -webkit-font-smoothing: antialiased;
        }

        button {
            font: inherit;
        }

        .preview-toolbar {
            position: sticky;
            z-index: 20;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            width: min(210mm, calc(100% - 24px));
            margin: 12px auto 0;
            padding: 10px 12px;
            color: #fff;
            background: rgba(7, 63, 67, 0.97);
            border-radius: 12px;
            box-shadow: 0 12px 34px rgba(7, 63, 67, 0.22);
        }

        .toolbar-copy {
            display: grid;
            gap: 1px;
        }

        .toolbar-copy strong {
            font-size: 10pt;
        }

        .toolbar-copy span {
            color: #bfe9e4;
            font-size: 7.5pt;
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
        }

        .toolbar-button {
            min-height: 35px;
            padding: 0 14px;
            color: #fff;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 8px;
            cursor: pointer;
        }

        .toolbar-button-primary {
            color: var(--teal-950);
            background: #fff;
            border-color: #fff;
            font-weight: 700;
        }

        .sheet {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 210mm;
            min-height: 297mm;
            margin: 12px auto 24px;
            overflow: hidden;
            background: var(--paper);
            box-shadow: 0 24px 70px rgba(26, 74, 74, 0.18);
        }

        .sheet::before {
            position: absolute;
            z-index: 0;
            top: 0;
            left: 0;
            width: 100%;
            height: 48mm;
            background: linear-gradient(118deg, var(--teal-800) 0%, var(--teal-600) 46%, #72ddd0 100%);
            clip-path: polygon(0 0, 100% 0, 100% 42%, 61% 42%, 48% 100%, 0 84%);
            content: "";
        }

        .sheet::after {
            position: absolute;
            z-index: 0;
            right: -25mm;
            bottom: -22mm;
            width: 78mm;
            height: 42mm;
            background: linear-gradient(135deg, #73ddd0, var(--teal-600));
            clip-path: polygon(36% 0, 100% 0, 100% 100%, 0 100%);
            content: "";
            opacity: 0.95;
        }

        .document-header {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            min-height: 45mm;
            padding: 9mm 18mm 0;
            color: #fff;
        }

        .clinic-kicker {
            margin: 0 0 2mm;
            font-size: 7pt;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            opacity: 0.82;
        }

        .doctor-name {
            max-width: 120mm;
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 24pt;
            font-weight: 700;
            line-height: 1.08;
        }

        .doctor-contact {
            margin: 2.5mm 0 0;
            font-size: 7.5pt;
            opacity: 0.9;
        }

        .medical-seal {
            display: grid;
            width: 25mm;
            height: 25mm;
            place-items: center;
            color: var(--teal-800);
            background: rgba(255, 255, 255, 0.96);
            border: 2px solid rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            box-shadow: 0 8px 24px rgba(7, 63, 67, 0.2);
        }

        .seal-mark {
            position: relative;
            width: 12mm;
            height: 12mm;
        }

        .seal-mark::before,
        .seal-mark::after {
            position: absolute;
            background: currentColor;
            border-radius: 1mm;
            content: "";
        }

        .seal-mark::before {
            top: 0;
            left: 4.5mm;
            width: 3mm;
            height: 12mm;
        }

        .seal-mark::after {
            top: 4.5mm;
            left: 0;
            width: 12mm;
            height: 3mm;
        }

        .document-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 3mm 18mm 13mm;
        }

        .document-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 10mm;
            margin-bottom: 5mm;
        }

        .document-heading h1 {
            margin: 0;
            color: var(--teal-950);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 17pt;
            line-height: 1;
            text-transform: uppercase;
        }

        .document-reference {
            display: grid;
            gap: 1mm;
            text-align: right;
        }

        .document-reference strong {
            color: var(--teal-800);
            font-family: Consolas, monospace;
            font-size: 8pt;
        }

        .document-reference span {
            color: var(--muted);
            font-size: 7.5pt;
        }

        .patient-panel {
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 4mm 12mm;
            padding: 5mm 0 4.5mm;
            border-top: 2px solid var(--teal-800);
            border-bottom: 1px solid var(--line);
        }

        .field {
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: end;
            gap: 3mm;
            min-width: 0;
        }

        .field-label {
            color: var(--muted);
            font-size: 7pt;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .field-value {
            min-width: 0;
            padding-bottom: 1mm;
            overflow-wrap: anywhere;
            border-bottom: 1px solid var(--line);
            font-size: 9pt;
            font-weight: 700;
        }

        .medical-alerts {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 5mm;
            margin-top: 4mm;
            padding: 3mm 4mm;
            color: #674412;
            background: var(--alert-soft);
            border-left: 3px solid var(--alert);
        }

        .alert-title {
            font-size: 7pt;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .alert-content {
            display: flex;
            flex-wrap: wrap;
            gap: 2mm 8mm;
            font-size: 8pt;
        }

        .prescription-body {
            margin-top: 6mm;
        }

        .rx-heading {
            display: flex;
            align-items: center;
            gap: 4mm;
            margin-bottom: 2mm;
        }

        .rx-symbol {
            color: var(--teal-800);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 32pt;
            font-weight: 700;
            line-height: 1;
        }

        .rx-title h2 {
            margin: 0;
            color: var(--teal-950);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 15pt;
        }

        .rx-title p {
            margin: 0.5mm 0 0;
            color: var(--muted);
            font-size: 7.5pt;
        }

        .medication-list {
            border-top: 1px solid var(--teal-800);
        }

        .medication-item {
            display: grid;
            grid-template-columns: 8mm minmax(0, 1fr);
            gap: 4mm;
            padding: 4mm 0;
            border-bottom: 1px solid var(--line);
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .medication-number {
            display: grid;
            width: 7mm;
            height: 7mm;
            place-items: center;
            color: #fff;
            background: var(--teal-600);
            border-radius: 50%;
            font-size: 7.5pt;
            font-weight: 800;
        }

        .medication-name {
            margin: 0 0 2mm;
            color: var(--ink);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.15;
        }

        .regimen {
            display: flex;
            flex-wrap: wrap;
            gap: 2mm 10mm;
        }

        .regimen-item {
            display: flex;
            align-items: baseline;
            gap: 2mm;
        }

        .regimen-label {
            color: var(--teal-800);
            font-size: 6.5pt;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .regimen-value {
            font-size: 8.5pt;
            font-weight: 700;
        }

        .instructions {
            margin-top: 2mm;
            padding-left: 3mm;
            color: #455c60;
            border-left: 2px solid var(--teal-400);
            font-size: 8pt;
        }

        .notes-section {
            margin-top: 4mm;
            padding: 3mm 4mm;
            background: var(--teal-100);
            border-radius: 1.5mm;
            break-inside: avoid;
        }

        .notes-section strong {
            color: var(--teal-800);
            font-size: 7pt;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .notes-section p {
            margin: 1mm 0 0;
            font-size: 8pt;
        }

        .empty-state {
            min-height: 45mm;
            padding-top: 5mm;
            color: var(--muted);
            border-top: 1px solid var(--teal-800);
        }

        .document-closing {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 64mm;
            align-items: end;
            gap: 12mm;
            margin-top: auto;
            padding-top: 7mm;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .document-notice {
            color: var(--muted);
            font-size: 6.8pt;
        }

        .document-notice strong {
            display: block;
            margin-bottom: 1mm;
            color: var(--ink);
            font-size: 7.2pt;
        }

        .signature {
            padding-top: 14mm;
            text-align: center;
        }

        .signature-line {
            padding: 2mm 2mm 0;
            border-top: 1px solid var(--teal-800);
        }

        .signature-label {
            color: var(--muted);
            font-size: 6.5pt;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .signature-name {
            margin-top: 1mm;
            color: var(--teal-950);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 9pt;
            font-weight: 700;
        }

        .document-footer {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 8mm;
            min-height: 13mm;
            padding: 3mm 18mm 4mm;
            color: var(--teal-950);
            font-size: 6.5pt;
        }

        .footer-clinic {
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .footer-contact {
            max-width: 95mm;
            text-align: right;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            html,
            body {
                width: 210mm;
                min-height: 297mm;
                background: #fff;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .preview-toolbar {
                display: none !important;
            }

            .sheet {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                box-shadow: none;
            }
        }

        @media screen and (max-width: 850px) {
            .preview-toolbar,
            .sheet {
                width: calc(100% - 20px);
            }

            .toolbar-copy span {
                display: none;
            }

            .sheet {
                min-height: auto;
            }

            .sheet::before {
                height: 170px;
                clip-path: polygon(0 0, 100% 0, 100% 60%, 55% 60%, 42% 100%, 0 88%);
            }

            .document-header {
                grid-template-columns: minmax(0, 1fr) 64px;
                min-height: 170px;
                padding: 26px 20px 0;
            }

            .doctor-name {
                font-size: 20pt;
            }

            .medical-seal {
                width: 58px;
                height: 58px;
            }

            .seal-mark {
                transform: scale(0.65);
            }

            .document-content {
                padding: 18px 20px 34px;
            }

            .document-heading,
            .document-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .document-heading {
                display: flex;
                gap: 12px;
            }

            .document-reference {
                text-align: left;
            }

            .patient-panel,
            .document-closing {
                grid-template-columns: 1fr;
            }

            .patient-panel {
                gap: 12px;
            }

            .medical-alerts {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .signature {
                width: min(100%, 280px);
                margin-left: auto;
            }

            .document-footer {
                display: flex;
                padding: 12px 20px 18px;
            }

            .footer-contact {
                max-width: 75%;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="preview-toolbar" role="toolbar" aria-label="{{ __('prescriptions.print_title') }}">
        <div class="toolbar-copy">
            <strong>{{ __('prescriptions.print_title') }}</strong>
            <span>A4 | {{ $reference }}</span>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="toolbar-button" onclick="window.opener ? window.close() : history.back()">{{ __('common.back') }}</button>
            <button type="button" class="toolbar-button toolbar-button-primary" onclick="window.print()">{{ __('common.print') }}</button>
        </div>
    </div>

    <main class="sheet">
        <header class="document-header">
            <div>
                <p class="clinic-kicker">{{ $clinicName }}</p>
                <h2 class="doctor-name">{{ $doctor->professional_name }}</h2>
                @if($doctorContacts)
                    <p class="doctor-contact">{{ $doctorContacts }}</p>
                @endif
            </div>
            <div class="medical-seal" aria-hidden="true">
                <span class="seal-mark"></span>
            </div>
        </header>

        <section class="document-content">
            <div class="document-heading">
                <h1>{{ __('prescriptions.print_title') }}</h1>
                <div class="document-reference">
                    <strong>{{ $reference }}</strong>
                    <span>{{ $prescription->prescribed_date->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}</span>
                </div>
            </div>

            <section class="patient-panel" aria-label="{{ __('prescriptions.patient_details') }}">
                <div class="field">
                    <span class="field-label">{{ __('patients.full_name') }}</span>
                    <span class="field-value">{{ $patient->full_name }}</span>
                </div>
                <div class="field">
                    <span class="field-label">{{ __('prescriptions.prescription_date') }}</span>
                    <span class="field-value">{{ $prescription->prescribed_date->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}</span>
                </div>
                <div class="field">
                    <span class="field-label">{{ __('patients.birth_date') }}</span>
                    <span class="field-value">
                        @if($patient->birth_date)
                            {{ $patient->birth_date->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}
                            @if($patient->age !== null)
                                ({{ $patient->age }} {{ __('patients.years') }})
                            @endif
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="field">
                    <span class="field-label">{{ __('patients.gender') }}</span>
                    <span class="field-value">{{ filled($patient->gender) ? __('patients.'.$patient->gender) : '-' }}</span>
                </div>
                @if(filled($patient->phone))
                    <div class="field">
                        <span class="field-label">{{ __('common.phone') }}</span>
                        <span class="field-value">{{ $patient->phone }}</span>
                    </div>
                @endif
                @if(filled($patient->id_card_number))
                    <div class="field">
                        <span class="field-label">{{ __('patients.id_card') }}</span>
                        <span class="field-value">{{ $patient->id_card_number }}</span>
                    </div>
                @endif
            </section>

            @if(filled($patient->allergies) || filled($patient->chronic_conditions))
                <section class="medical-alerts">
                    <div class="alert-title">{{ __('prescriptions.medical_alerts') }}</div>
                    <div class="alert-content">
                        @if(filled($patient->allergies))
                            <span><strong>{{ __('patients.allergies') }}:</strong> {{ $patient->allergies }}</span>
                        @endif
                        @if(filled($patient->chronic_conditions))
                            <span><strong>{{ __('patients.chronic_conditions') }}:</strong> {{ $patient->chronic_conditions }}</span>
                        @endif
                    </div>
                </section>
            @endif

            <section class="prescription-body">
                <div class="rx-heading">
                    <span class="rx-symbol" aria-hidden="true">Rx</span>
                    <div class="rx-title">
                        <h2>{{ __('prescriptions.treatment') }}</h2>
                        <p>{{ $medications->count() }} {{ $medications->count() === 1 ? __('prescriptions.medication') : __('prescriptions.medications') }}</p>
                    </div>
                </div>

                @if($medications->isNotEmpty())
                    <div class="medication-list">
                        @foreach($medications as $index => $item)
                            <article class="medication-item">
                                <div class="medication-number">{{ $index + 1 }}</div>
                                <div>
                                    <h3 class="medication-name">{{ $item->medication_name }}</h3>
                                    <div class="regimen">
                                        @if(filled($item->dosage))
                                            <div class="regimen-item">
                                                <span class="regimen-label">{{ __('prescriptions.dosage') }}</span>
                                                <span class="regimen-value">{{ $item->dosage }}</span>
                                            </div>
                                        @endif
                                        @if(filled($item->frequency))
                                            <div class="regimen-item">
                                                <span class="regimen-label">{{ __('prescriptions.frequency') }}</span>
                                                <span class="regimen-value">{{ $item->frequency }}</span>
                                            </div>
                                        @endif
                                        @if(filled($item->duration_days))
                                            <div class="regimen-item">
                                                <span class="regimen-label">{{ __('prescriptions.duration') }}</span>
                                                <span class="regimen-value">{{ $item->duration_days }} {{ __('prescriptions.days') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    @if(filled($item->instructions))
                                        <div class="instructions"><strong>{{ __('prescriptions.instructions') }}:</strong> {{ $item->instructions }}</div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">{{ __('prescriptions.no_medication') }}</div>
                @endif

                @if(filled($prescription->notes))
                    <div class="notes-section">
                        <strong>{{ __('prescriptions.notes') }}</strong>
                        <p>{{ $prescription->notes }}</p>
                    </div>
                @endif
            </section>

            <section class="document-closing">
                <p class="document-notice">
                    <strong>{{ __('prescriptions.document_notice') }}</strong>
                    {{ __('prescriptions.generated_on', ['date' => $generatedDate]) }}
                </p>
                <div class="signature">
                    <div class="signature-line">
                        <div class="signature-label">{{ __('prescriptions.stamp_signature') }}</div>
                        <div class="signature-name">{{ $doctor->professional_name }}</div>
                    </div>
                </div>
            </section>
        </section>

        <footer class="document-footer">
            <span class="footer-clinic">{{ $clinicName }}</span>
            <span class="footer-contact">
                {{ collect([$clinic['address'] ?? null, $clinicContacts])->filter()->implode('  |  ') }}
            </span>
        </footer>
    </main>
</body>
</html>
