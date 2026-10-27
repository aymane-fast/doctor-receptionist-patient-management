<?php

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

it('keeps all twelve months and separates monthly from lifetime completions at month end', function () {
    $this->travelTo(Carbon::parse('2026-03-31 10:00:00'));
    $doctor = User::factory()->doctor()->create();
    foreach (['2025-04-15', '2026-02-10', '2026-03-10'] as $date) {
        Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'completed', 'appointment_date' => $date]);
    }
    $this->actingAs($doctor)->getJson('/api/statistics')->assertOk()
        ->assertJsonPath('completed_appointments', 3)
        ->assertJsonPath('completed_this_month', 1)
        ->assertJsonPath('monthly_revenue_labels.0', 'Apr 2025')
        ->assertJsonPath('monthly_revenue_labels.10', 'Feb 2026')
        ->assertJsonPath('monthly_revenue_labels.11', 'Mar 2026')
        ->assertJsonPath('monthly_revenue.0', 50)
        ->assertJsonPath('monthly_revenue.10', 50)
        ->assertJsonPath('monthly_revenue.11', 50);
    $this->getJson('/api/statistics/trend?timeframe=year')->assertOk()
        ->assertJsonPath('labels.0', 'Apr 2025')->assertJsonPath('labels.10', 'Feb 2026')
        ->assertJsonPath('data.0', 1)->assertJsonPath('data.10', 1)->assertJsonPath('data.11', 1);
});
