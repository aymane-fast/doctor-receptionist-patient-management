<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders
                            {--date= : Remind for a specific day (YYYY-MM-DD); defaults to tomorrow}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email reminders to patients with an appointment tomorrow';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date') ?: now()->addDay()->toDateString();
        if (\Illuminate\Support\Facades\Validator::make(['date' => $date], ['date' => 'required|date_format:Y-m-d'])->fails()) {
            $this->error('Use a valid date in YYYY-MM-DD format.');
            return self::FAILURE;
        }

        $appointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', $date)
            ->where('status', 'scheduled')
            ->whereNull('reminder_sent_at')
            ->whereHas('patient', fn ($query) => $query->whereNotNull('email')->where('email', '!=', ''))
            ->get();

        if ($appointments->isEmpty()) {
            $this->info("No reminders to send for {$date}.");

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($appointments as $appointment) {
            try {
                Mail::to($appointment->patient->email)
                    ->send(new AppointmentReminderMail($appointment));

                $appointment->updateQuietly(['reminder_sent_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                // One bad address must not stop the rest of the batch.
                Log::warning('Appointment reminder failed', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$sent} reminder(s) for {$date}.");

        return self::SUCCESS;
    }
}
