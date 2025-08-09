<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription</title>
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
        <div class="clinic">Medical Management System</div>
        <div class="meta">Date: {{ $prescription->prescribed_date->format('M j, Y') }}</div>
    </div>

    <div class="box">
        <div class="title">Patient</div>
        <div>{{ $prescription->patient->full_name }} ({{ $prescription->patient->patient_id }})</div>
    </div>

    <div class="box">
        <div class="title">Doctor</div>
        <div>Dr. {{ $prescription->doctor->name }}</div>
    </div>

    <div class="box">
        <div class="title">Medication</div>
        <div>{{ $prescription->medication_name }} ({{ $prescription->dosage }})</div>
        <div>Frequency: {{ $prescription->frequency }}</div>
        <div>Duration: {{ $prescription->duration_days }} days</div>
    </div>

    <div class="box">
        <div class="title">Instructions</div>
        <div>{{ $prescription->instructions }}</div>
    </div>

    @if($prescription->notes)
    <div class="box">
        <div class="title">Notes</div>
        <div>{{ $prescription->notes }}</div>
    </div>
    @endif
</body>
</html>


