<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('prescriptions.prescription') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .clinic { font-size: 20px; font-weight: bold; }
        .meta { color: #555; }
        .box { border: 1px solid #ddd; padding: 16px; border-radius: 6px; margin-bottom: 16px; }
        .title { font-weight: bold; margin-bottom: 8px; }
    </style>
    <script>
        window.addEventListener('load', function(){ window.print(); });
    </script>
    </head>
<body>
    <div class="header">
        <div class="clinic">{{ __('prescriptions.medical_management_system') }}</div>
        <div class="meta">{{ __('prescriptions.date') }}: {{ $prescription->prescribed_date->format('M j, Y') }}</div>
    </div>

    <div class="box">
        <div class="title">{{ __('prescriptions.patient_name') }}</div>
        <div>{{ $prescription->patient->full_name }} ({{ $prescription->patient->patient_id }})</div>
    </div>

    <div class="box">
        <div class="title">{{ __('prescriptions.doctor_name') }}</div>
        <div>Dr. {{ $prescription->doctor->name }}</div>
    </div>

    <div class="box">
        <div class="title">{{ __('prescriptions.medications') }}</div>
        <ul>
        @foreach($prescription->items as $item)
            <li>
                {{ $item->medication_name }} ({{ $item->dosage }}) — {{ $item->frequency }}@if($item->duration_days), {{ $item->duration_days }} {{ __('prescriptions.days') }} @endif
                @if($item->instructions)
                    <div>{{ __('prescriptions.instructions') }}: {{ $item->instructions }}</div>
                @endif
            </li>
        @endforeach
        </ul>
    </div>

    <div class="box">
        <div class="title">{{ __('prescriptions.instructions') }}</div>
        <div>{{ $prescription->instructions }}</div>
    </div>

    @if($prescription->notes)
    <div class="box">
        <div class="title">{{ __('prescriptions.notes') }}</div>
        <div>{{ $prescription->notes }}</div>
    </div>
    @endif
</body>
</html>


