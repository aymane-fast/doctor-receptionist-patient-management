<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\AppointmentSchedulingService;
use Illuminate\Console\Command;

class MarkNoShowAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:mark-no-shows
                            {--date= : Process a specific day (YYYY-MM-DD); defaults to yesterday and earlier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark still-scheduled appointments from past days as no-shows';

    /**
     * Execute the console command.
     */
    public function handle(AppointmentSchedulingService $scheduler): int
    {
        $query = Appointment::where('status', 'scheduled');

        if ($date = $this->option('date')) {
            $query->whereDate('appointment_date', $date);
        } else {
            // Anything still "scheduled" before today never happened.
            $query->whereDate('appointment_date', '<', today());
        }

        $appointments = $query->get();

        if ($appointments->isEmpty()) {
            $this->info('No stale appointments to mark as no-show.');

            return self::SUCCESS;
        }

        $touchedQueues = [];

        foreach ($appointments as $appointment) {
            $appointment->update([
                'status' => 'no_show',
                'notes' => ($appointment->notes ? $appointment->notes."\n" : '')
                    .'Automatically marked no-show on '.now()->format('Y-m-d H:i'),
            ]);

            if ($appointment->isQueueMode()) {
                $touchedQueues[$appointment->doctor_id.'|'.$appointment->appointment_date->toDateString()] = $appointment;
            }
        }

        foreach ($touchedQueues as $appointment) {
            $scheduler->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());
        }

        $this->info("Marked {$appointments->count()} appointment(s) as no-show.");

        return self::SUCCESS;
    }
}
