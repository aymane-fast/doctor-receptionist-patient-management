<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><title>{{ __('workflow.examination_request') }}</title>
<style>body{font:16px/1.6 Arial,sans-serif;max-width:750px;margin:40px auto;color:#172925}header{border-bottom:2px solid #126e64;margin-bottom:30px}h1{font-size:24px}button{padding:10px 20px}@media print{button{display:none}body{margin:0}}</style></head>
<body>
<header><h1>{{ $clinic['name'] ?? $clinic['clinic_name'] ?? config('app.name') }}</h1><p>{{ $clinic['address'] ?? '' }} · {{ $clinic['phone'] ?? '' }}</p></header>
<h2>{{ __('workflow.examination_request') }}</h2>
<p>{{ __('patients.patient') }}: <strong>{{ $order->patient->full_name }}</strong> · {{ $order->patient->birth_date?->format('d/m/Y') }}</p>
<p>{{ __('common.doctor') }}: {{ $order->doctor->professional_name }} · {{ $order->requested_date->format('d/m/Y') }}</p>
<h3>{{ $order->test_name }}</h3><p style="white-space:pre-wrap">{{ $order->notes }}</p>
<p style="margin-top:80px">{{ __('workflow.signature') }}: __________________</p>
<button onclick="window.print()">{{ __('common.print') }}</button>
</body></html>
