<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckAppointmentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check current appointment statuses for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Current appointment statuses for today:');
        
        $appointments = \App\Models\Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', today())
            ->orderBy('doctor_id')
            ->orderBy('appointment_time')
            ->get();

        if ($appointments->isEmpty()) {
            $this->warn('No appointments found for today.');
            return;
        }

        $currentDoctor = null;
        foreach ($appointments as $appointment) {
            if ($currentDoctor !== $appointment->doctor_id) {
                $currentDoctor = $appointment->doctor_id;
                $this->line('');
                $this->info("👨‍⚕️ Dr. {$appointment->doctor->name} (ID: {$appointment->doctor_id}):");
            }
            
            $statusColor = match($appointment->status) {
                'in_progress' => '<fg=green>',
                'completed' => '<fg=blue>',
                'cancelled' => '<fg=red>',
                'scheduled' => '<fg=yellow>',
                default => '<fg=white>'
            };
            
            $this->line("  {$statusColor}{$appointment->appointment_time->format('H:i')} - {$appointment->patient->full_name} ({$appointment->status})</>");
        }
        
        // Check for multiple in_progress
        $inProgressCount = $appointments->where('status', 'in_progress')->count();
        if ($inProgressCount > 1) {
            $this->error("⚠️  WARNING: Found {$inProgressCount} appointments with 'in_progress' status!");
        } elseif ($inProgressCount === 1) {
            $current = $appointments->where('status', 'in_progress')->first();
            $this->info("✅ Current patient: {$current->patient->full_name} with Dr. {$current->doctor->name}");
        } else {
            $this->warn("ℹ️  No current patient set (no 'in_progress' appointments)");
        }
    }
}
