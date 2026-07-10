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
    protected $description = 'Close out past days: still-scheduled visits become no-shows, unclosed in-progress visits become completed';

    /**
     * Execute the console command.
     */
    public function handle(AppointmentSchedulingService $scheduler): int
    {
        $dateFilter = function ($query) {
            if ($date = $this->option('date')) {
                $query->whereDate('appointment_date', $date);
            } else {
                $query->whereDate('appointment_date', '<', today());
            }
        };

        // Still "scheduled" on a past day → the patient never came.
        $noShows = Appointment::where('status', 'scheduled')->tap($dateFilter)->get();

        // Left "in_progress" on a past day → the visit happened but was
        // never closed (doctor forgot to complete it before leaving).
        $unclosed = Appointment::where('status', 'in_progress')->tap($dateFilter)->get();

        $touchedQueues = [];

        foreach ($noShows as $appointment) {
            $appointment->update([
                'status' => 'no_show',
                'checked_in_at' => null,
                'notes' => ($appointment->notes ? $appointment->notes."\n" : '')
                    .'Automatically marked no-show on '.now()->format('Y-m-d H:i'),
            ]);

            if ($appointment->isQueueMode()) {
                $touchedQueues[$appointment->doctor_id.'|'.$appointment->appointment_date->toDateString()] = $appointment;
            }
        }

        foreach ($unclosed as $appointment) {
            $appointment->update([
                'status' => 'completed',
                'notes' => ($appointment->notes ? $appointment->notes."\n" : '')
                    .'Automatically closed (left in progress) on '.now()->format('Y-m-d H:i'),
            ]);

            if ($appointment->isQueueMode()) {
                $touchedQueues[$appointment->doctor_id.'|'.$appointment->appointment_date->toDateString()] = $appointment;
            }
        }

        foreach ($touchedQueues as $appointment) {
            $scheduler->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());
        }

        $this->info("Marked {$noShows->count()} no-show(s) and closed {$unclosed->count()} unfinished consultation(s).");

        return self::SUCCESS;
    }
}
