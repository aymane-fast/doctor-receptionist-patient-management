<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#0f766e; padding:20px 32px;">
                            <h1 style="margin:0; color:#ffffff; font-size:20px;">{{ $clinic['name'] }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; color:#111827; font-size:16px;">
                                {{ __('appointments.reminder_greeting', ['name' => $appointment->patient->primary_guardian?->name ?? $appointment->patient->full_name]) }}
                                @if($appointment->patient->is_pediatric)
                                    <br>{{ __('Child') }}: {{ $appointment->patient->full_name }} ({{ $appointment->patient->birth_date?->translatedFormat('Y-m-d') }})
                                @endif
                            </p>

                            <p style="margin:0 0 20px; color:#374151; font-size:15px; line-height:1.6;">
                                {{ __('appointments.reminder_intro') }}
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdfa; border:1px solid #99f6e4; border-radius:10px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <p style="margin:0 0 8px; color:#0f766e; font-size:14px; font-weight:bold;">
                                            {{ __('appointments.date') }}: {{ $appointment->appointment_date->translatedFormat('l, F j, Y') }}
                                        </p>
                                        @if($appointment->isQueueMode())
                                            <p style="margin:0 0 8px; color:#0f766e; font-size:14px; font-weight:bold;">
                                                {{ __('appointments.queue_number') }}: #{{ $appointment->queue_number }}
                                            </p>
                                            <p style="margin:0; color:#0f766e; font-size:14px;">
                                                {{ __('appointments.estimated_time') }}: {{ $appointment->displayTime() }}
                                            </p>
                                        @else
                                            <p style="margin:0; color:#0f766e; font-size:14px; font-weight:bold;">
                                                {{ __('appointments.time') }}: {{ $appointment->displayTime() }}
                                            </p>
                                        @endif
                                        <p style="margin:8px 0 0; color:#0f766e; font-size:14px;">
                                            {{ __('appointments.doctor') }}: {{ $appointment->doctor->professional_name }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:20px 0 0; color:#374151; font-size:14px; line-height:1.6;">
                                {{ __('appointments.reminder_footer') }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px; background:#f9fafb; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; color:#6b7280; font-size:12px;">
                                {{ $clinic['name'] }}
                                @if($clinic['phone']) • {{ $clinic['phone'] }} @endif
                                @if($clinic['address']) • {{ $clinic['address'] }} @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
