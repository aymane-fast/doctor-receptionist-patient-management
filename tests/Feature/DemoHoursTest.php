<?php

use App\Models\Setting;
use App\Models\User;
use App\Models\Appointment;
use App\Services\AppointmentSchedulingService;

it('does not offer arrival or consultation actions for a future appointment', function () {
    $doctor = User::factory()->doctor()->create();
    $visit = Appointment::factory()->create(['doctor_id'=>$doctor->id, 'appointment_date'=>today()->addDay()]);
    $this->actingAs(User::factory()->receptionist()->create())->get(route('appointments.show',$visit))->assertOk()
        ->assertDontSee(route('appointments.check-in',$visit))
        ->assertDontSee(route('appointments.set-current',$visit))
        ->assertDontSee(route('appointments.no-show',$visit))
        ->assertSee(route('appointments.reschedule',$visit));
});

it('launches a 24 hour demo and restores its original schedule when disabled', function () {
    Setting::setSetupComplete(false);
    $this->post('/setup', ['environment_mode'=>'demo','launch_demo'=>1,'demo_always_open'=>1])
        ->assertRedirect(route('dashboard'));
    expect(Setting::demoAlwaysOpen())->toBeTrue()->and(Setting::getWorkingHours('sunday')['is_working'])->toBeTrue();
    $scheduler=app(AppointmentSchedulingService::class);
    expect($scheduler->validateScheduledTime('2026-11-01','23:30'))->toBeNull()
        ->and($scheduler->workingWindow('2026-11-01')['end']->format('Y-m-d H:i'))->toBe('2026-11-02 00:00');
    $this->get(route('settings.index'))->assertOk()->assertSee('value="09:00"',false);
    $this->post(route('settings.demo-hours'), ['demo_always_open'=>0])->assertSessionHasNoErrors();
    expect(Setting::demoAlwaysOpen())->toBeFalse()->and(Setting::getWorkingHours('sunday')['is_working'])->toBeFalse()
        ->and(Setting::getWorkingHours('monday')['start_time'])->toBe('09:00');
});

it('does not expose demo hours to receptionists or production clinics', function () {
    Setting::set('deployment_mode','production');
    $this->actingAs(User::factory()->doctor()->create())->post(route('settings.demo-hours'),['demo_always_open'=>1])->assertForbidden();
    Setting::set('demo_always_open',true,'boolean');
    expect(Setting::demoAlwaysOpen())->toBeFalse();
    Setting::set('deployment_mode','demo');
    $this->actingAs(User::factory()->receptionist()->create())->post(route('settings.demo-hours'),['demo_always_open'=>0])->assertForbidden();
});
