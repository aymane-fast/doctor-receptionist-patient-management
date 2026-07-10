<?php

namespace App\Http\Controllers;

use App\Http\Requests\Traits\ValidatesAppointments;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use App\Services\AppointmentSchedulingService;
use App\Services\PatientSearchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    use ValidatesAppointments;

    private function scheduler(): AppointmentSchedulingService
    {
        return app(AppointmentSchedulingService::class);
    }

    /**
     * Display a listing of appointments
     */
    public function index(Request $request)
    {
        // Check if there's a search query or show_today request
        $hasSearchQuery = $request->filled('search') || $request->filled('date') || $request->filled('status') || $request->filled('doctor_id') || $request->has('show_today');

        if (! $hasSearchQuery) {
            // No search performed, return empty collection
            $appointments = collect();
        } else {
            $query = Appointment::with(['patient', 'doctor']);

            // Handle "Today" button
            if ($request->has('show_today')) {
                $query->whereDate('appointment_date', Carbon::today());
            }

            // Search by patient information
            if ($request->filled('search')) {
                $search = trim($request->string('search'));
                $query->whereHas('patient', fn ($q) => $q->search($search));
            }

            // Filter by date
            if ($request->filled('date')) {
                $query->whereDate('appointment_date', $request->date);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('appointment_mode')) {
                $query->where('appointment_mode', $request->appointment_mode);
            }

            // Filter by doctor for receptionists
            if ($request->filled('doctor_id') && Auth::user()->isReceptionist()) {
                $query->where('doctor_id', $request->doctor_id);
            }

            // If user is doctor, only show their appointments
            if (Auth::user()->isDoctor()) {
                $query->where('doctor_id', Auth::id());
            }

            $appointments = $query->orderBy('appointment_date', 'desc')
                ->orderByRaw("CASE WHEN appointment_mode = 'queue' THEN COALESCE(queue_position, 999999) ELSE 0 END")
                ->orderBy('appointment_time', 'desc')
                ->paginate(15)
                ->appends($request->query());
        }

        $doctors = User::where('role', 'doctor')->get();

        return view('appointments.index', compact('appointments', 'doctors', 'hasSearchQuery'));
    }

    /**
     * Show the form for creating a new appointment
     */
    public function create(Request $request)
    {
        $patients = collect(); // Start with empty collection
        $selectedPatient = null;

        // If patient_id is provided in URL, get that patient
        if ($request->has('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        $doctors = Auth::user()->isDoctor()
            ? User::where('id', Auth::id())->get()
            : User::where('role', 'doctor')->get();
        $doctorModes = $doctors->mapWithKeys(fn ($doctor) => [
            $doctor->id => [
                'mode' => $doctor->appointment_mode ?? 'scheduled',
                'queue_limit' => $doctor->queue_daily_limit,
            ],
        ]);

        return view('appointments.create', compact('patients', 'doctors', 'selectedPatient', 'doctorModes'));
    }

    /**
     * API endpoint for patient search autocomplete
     */
    public function searchPatients(Request $request)
    {
        $query = $request->get('q', '');

        return response()->json(PatientSearchService::search($query));
    }

    /**
     * API endpoint to get next available appointment slot
     */
    public function getNextAvailableSlot(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->get('date', now()->toDateString());
        $doctorId = $this->resolveDoctorId($request);

        if (! $doctorId) {
            return response()->json([
                'available' => false,
                'message' => 'Please select a doctor first',
            ], 422);
        }

        $doctor = User::find($doctorId);
        if ($doctor?->usesQueueMode()) {
            if (! $this->scheduler()->workingWindow($date)) {
                return response()->json([
                    'available' => false,
                    'mode' => 'queue',
                    'message' => 'No working hours for this day',
                ]);
            }

            $capacityError = $this->scheduler()->validateQueueCapacity($doctorId, $date);
            if ($capacityError) {
                return response()->json([
                    'available' => false,
                    'mode' => 'queue',
                    'message' => $capacityError,
                ]);
            }

            $ticket = $this->scheduler()->nextQueueNumber($doctorId, $date);
            $estimatedTime = $this->scheduler()->estimatedQueueJoinTime($doctorId, $date);

            return response()->json([
                'available' => true,
                'mode' => 'queue',
                'queue_number' => $ticket,
                'estimated_time' => $estimatedTime->format('H:i'),
                'message' => "Queue mode: next number is #{$ticket}, estimated around ".$estimatedTime->format('g:i A').'.',
            ]);
        }

        try {
            $requestedDate = \Carbon\Carbon::parse($date)->startOfDay();
        } catch (\Exception $e) {
            return response()->json([
                'available' => false,
                'message' => 'Invalid date format provided',
            ], 400);
        }

        // Check if the requested date is today
        $isToday = $requestedDate->isToday();

        // Get working hours for the requested day
        $dayOfWeek = strtolower($requestedDate->format('l'));
        $workingHours = \App\Models\Setting::getWorkingHours($dayOfWeek);

        if (! $workingHours || ! $workingHours['is_working']) {
            return response()->json([
                'available' => false,
                'message' => 'No working hours for this day',
            ]);
        }

        $availableSlots = $this->scheduler()->availableSlots($doctorId, $requestedDate);

        // Return the first available slot or indicate no availability
        if (empty($availableSlots)) {
            $next = $this->scheduler()->nextWorkingDayWithSlot($doctorId, $requestedDate);
            if ($next) {
                return response()->json([
                    'available' => false,
                    'message' => __('appointments.no_slots_date_next', [
                        'date' => Carbon::parse($next['date'])->format('M j, Y'),
                        'time' => $next['slot']['display'],
                    ]),
                    'next_available_date' => $next['date'],
                    'next_available_time' => $next['slot']['time'],
                    'working_hours' => [
                        'start' => $workingHours['start_time'],
                        'end' => $workingHours['end_time'],
                    ],
                ]);
            }

            return response()->json([
                'available' => false,
                'message' => __('appointments.no_slots_week'),
                'working_hours' => [
                    'start' => $workingHours['start_time'],
                    'end' => $workingHours['end_time'],
                ],
            ]);
        }

        $nextSlot = $availableSlots[0];

        // Generate UX message explaining the choice
        $message = '';
        if ($isToday) {
            $message = count($availableSlots) === 1
                ? __('appointments.only_one_slot_today', ['time' => $nextSlot['display']])
                : __('appointments.next_slot_today', ['time' => $nextSlot['display'], 'count' => count($availableSlots)]);
        } else {
            $message = __('appointments.earliest_slot_date', [
                'date' => $requestedDate->format('M j, Y'),
                'time' => $nextSlot['display'],
            ]);
        }

        return response()->json([
            'available' => true,
            'mode' => 'scheduled',
            'next_slot' => $nextSlot,
            'all_slots' => array_slice($availableSlots, 0, 8), // Return first 8 slots
            'message' => $message,
            'working_hours' => [
                'start' => $workingHours['start_time'],
                'end' => $workingHours['end_time'],
            ],
        ]);
    }

    /**
     * Store a newly created appointment
     */
    public function store(Request $request)
    {
        // Doctors always book for themselves
        if (Auth::user()->isDoctor()) {
            $request->merge(['doctor_id' => Auth::id()]);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'doctor'),
            ],
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'nullable|date_format:H:i',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $doctor = User::findOrFail($validated['doctor_id']);
        $validated['appointment_mode'] = $doctor->appointment_mode ?? 'scheduled';
        $validated['status'] = 'scheduled';

        // A patient should not hold two active appointments with the same doctor on one day
        if ($this->scheduler()->hasActiveSameDayAppointment($validated['patient_id'], $validated['doctor_id'], $validated['appointment_date'])) {
            return back()->withErrors(['patient_id' => __('appointments.already_has_appointment_today')])->withInput();
        }

        if ($validated['appointment_mode'] === 'queue') {
            try {
                $appointment = $this->scheduler()->createQueueAppointment($validated);
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['appointment_date' => $e->getMessage()])->withInput();
            }

            return redirect()->route('appointments.show', $appointment)
                ->with('success', "Queue appointment created successfully. Queue #{$appointment->queue_number}, estimated around {$appointment->displayTime()}.");
        }

        if (empty($validated['appointment_time'])) {
            return back()->withErrors(['appointment_time' => 'Appointment time is required for scheduled appointments.'])->withInput();
        }

        // Reject times that have already passed today
        if (Carbon::parse($validated['appointment_date'].' '.$validated['appointment_time'])->isPast()) {
            return back()->withErrors(['appointment_time' => 'Appointment time has already passed. Please choose a future time.'])->withInput();
        }

        // Validate working hours
        $workingHoursError = $this->validateWorkingHours($validated['appointment_date'], $validated['appointment_time']);
        if ($workingHoursError) {
            return back()->withErrors(['appointment_time' => $workingHoursError])->withInput();
        }

        // Conflict check + create inside one transaction, serialized per
        // doctor/day, so two receptionists cannot land in the same slot.
        try {
            $appointment = \DB::transaction(function () use ($validated) {
                Appointment::where('doctor_id', $validated['doctor_id'])
                    ->whereDate('appointment_date', $validated['appointment_date'])
                    ->lockForUpdate()
                    ->count();

                if ($this->hasAppointmentConflict($validated['doctor_id'], $validated['appointment_date'], $validated['appointment_time'])) {
                    throw new \InvalidArgumentException('Doctor already has an appointment at this time.');
                }

                return Appointment::create($validated);
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['appointment_time' => $e->getMessage()])->withInput();
        }

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment created successfully!');
    }

    /**
     * Display the specified appointment
     */
    public function show(Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        $appointment->load(['patient', 'doctor', 'medicalRecord', 'statusLogs.changedBy']);

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the appointment
     */
    public function edit(Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        $patients = Patient::orderBy('first_name')->orderBy('last_name')->get();
        $doctors = Auth::user()->isDoctor()
            ? User::where('id', Auth::id())->get()
            : User::where('role', 'doctor')->get();

        return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    /**
     * Update the specified appointment
     */
    public function update(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);
        $previousDoctorId = $appointment->doctor_id;
        $previousDate = $appointment->appointment_date->toDateString();

        // Doctors cannot reassign appointments to someone else
        if (Auth::user()->isDoctor()) {
            $request->merge(['doctor_id' => Auth::id()]);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'doctor'),
            ],
            'appointment_date' => 'required|date',
            'appointment_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled,no_show',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // The edit form must obey the same status rules as every other path.
        if ($validated['status'] !== $appointment->status && ! $appointment->canTransitionTo($validated['status'])) {
            return back()->withErrors(['status' => __('appointments.invalid_status_transition', [
                'from' => Appointment::statusLabel($appointment->status),
                'to' => Appointment::statusLabel($validated['status']),
            ])])->withInput();
        }

        // A consultation in progress is happening right now: its date/time
        // must not be moved, or it becomes an orphaned "current patient"
        // that no today-scoped screen can ever finish.
        if ($appointment->status === 'in_progress'
            && Carbon::parse($validated['appointment_date'])->toDateString() !== $appointment->appointment_date->toDateString()) {
            return back()->withErrors(['appointment_date' => __('appointments.cannot_move_in_progress')])->withInput();
        }

        // A visit that produced a medical record belongs to that patient;
        // repointing the appointment would detach the record's history.
        if ((int) $validated['patient_id'] !== $appointment->patient_id && $appointment->medicalRecord()->exists()) {
            return back()->withErrors(['patient_id' => __('appointments.patient_locked_by_record')])->withInput();
        }

        // Moving the appointment must not double-book the patient elsewhere.
        if (in_array($validated['status'], Appointment::ACTIVE_STATUSES, true)
            && $this->scheduler()->hasActiveSameDayAppointment((int) $validated['patient_id'], (int) $validated['doctor_id'], $validated['appointment_date'], $appointment->id)) {
            return back()->withErrors(['patient_id' => __('appointments.already_has_appointment_today')])->withInput();
        }

        if ($appointment->isQueueMode()) {
            $validated['appointment_mode'] = 'queue';
            $validated['appointment_time'] = $appointment->appointment_time->format('H:i');

            // A ticket moved to another day/doctor takes a fresh number
            // there — otherwise two patients could hold the same ticket.
            $movedQueue = (int) $validated['doctor_id'] !== $previousDoctorId
                || Carbon::parse($validated['appointment_date'])->toDateString() !== $previousDate;

            if ($movedQueue) {
                $validated['queue_number'] = $this->scheduler()->nextQueueNumber((int) $validated['doctor_id'], $validated['appointment_date']);
                $validated['queue_position'] = $this->scheduler()->nextQueuePosition((int) $validated['doctor_id'], $validated['appointment_date']);
            }
        } else {
            if (empty($validated['appointment_time'])) {
                return back()->withErrors(['appointment_time' => 'Appointment time is required for scheduled appointments.'])->withInput();
            }

            if (in_array($validated['status'], Appointment::ACTIVE_STATUSES, true)) {
                $workingHoursError = $this->validateWorkingHours($validated['appointment_date'], $validated['appointment_time']);
                if ($workingHoursError) {
                    return back()->withErrors(['appointment_time' => $workingHoursError])->withInput();
                }
            }

            if ($this->hasAppointmentConflict($validated['doctor_id'], $validated['appointment_date'], $validated['appointment_time'], $appointment->id)) {
                return back()->withErrors(['appointment_time' => 'Doctor already has an appointment at this time.'])->withInput();
            }
        }

        // Starting a consultation through the edit form follows the same
        // rules as "Start consultation": today only, one current patient.
        if ($validated['status'] === 'in_progress' && $appointment->status !== 'in_progress') {
            if (Carbon::parse($validated['appointment_date'])->toDateString() !== today()->toDateString()) {
                return back()->withErrors(['status' => __('appointments.start_only_today')])->withInput();
            }

            $validated['checked_in_at'] = $appointment->checked_in_at ?? now();
        }

        // Patients who cancelled or never showed are not in the waiting room.
        if (in_array($validated['status'], ['cancelled', 'no_show'], true)) {
            $validated['checked_in_at'] = null;
        }

        // A moved appointment needs a fresh reminder for its new date.
        $moved = (int) $validated['doctor_id'] !== $previousDoctorId
            || Carbon::parse($validated['appointment_date'])->toDateString() !== $previousDate;
        if ($moved) {
            $validated['reminder_sent_at'] = null;
        }

        \DB::transaction(function () use ($appointment, $validated) {
            if ($validated['status'] === 'in_progress' && $appointment->status !== 'in_progress') {
                Appointment::where('doctor_id', $validated['doctor_id'])
                    ->whereDate('appointment_date', today())
                    ->where('status', 'in_progress')
                    ->where('id', '!=', $appointment->id)
                    ->update(['status' => 'scheduled']);
            }

            $appointment->update($validated);
        });

        if ($appointment->isQueueMode()) {
            $this->scheduler()->recalculateQueue($previousDoctorId, $previousDate);
            $this->scheduler()->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());
        }

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment updated successfully!');
    }

    /**
     * Remove the specified appointment
     */
    public function destroy(Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        // Visits that happened (or are happening) are medical history:
        // they can be cancelled, never erased.
        if (in_array($appointment->status, ['completed', 'in_progress'], true) || $appointment->medicalRecord()->exists()) {
            return back()->with('error', __('appointments.cannot_delete_history'));
        }

        $appointment->delete();

        if ($appointment->isQueueMode()) {
            $this->scheduler()->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());
        }

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted successfully!');
    }

    /**
     * Update appointment status
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled,no_show',
        ]);

        $newStatus = $request->status;

        // Double-clicks and stale pages: same status is a harmless no-op.
        if ($appointment->status === $newStatus) {
            return back()->with('success', 'Appointment status updated successfully!');
        }

        if (! $appointment->canTransitionTo($newStatus)) {
            return back()->with('error', __('appointments.invalid_status_transition', [
                'from' => Appointment::statusLabel($appointment->status),
                'to' => Appointment::statusLabel($newStatus),
            ]));
        }

        // Starting a consultation must behave exactly like "set current":
        // today only, one current patient per doctor, arrival stamped.
        if ($newStatus === 'in_progress') {
            return $this->setCurrent($request, $appointment);
        }

        // Resurrecting a cancelled/no-show slot must not double-book it.
        if ($newStatus === 'scheduled'
            && $appointment->isScheduledMode()
            && $this->hasAppointmentConflict($appointment->doctor_id, $appointment->appointment_date->toDateString(), $appointment->appointment_time->format('H:i'), $appointment->id)) {
            return back()->with('error', __('appointments.slot_taken_reschedule'));
        }

        $changes = ['status' => $newStatus];

        // Patients who cancelled or never showed are not in the waiting room.
        if (in_array($newStatus, ['cancelled', 'no_show'], true)) {
            $changes['checked_in_at'] = null;
        }

        $appointment->update($changes);

        if ($appointment->isQueueMode()) {
            $this->scheduler()->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());
        }

        return back()->with('success', 'Appointment status updated successfully!');
    }

    /**
     * Record that a patient has physically arrived at the clinic.
     */
    public function checkIn(Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        if ($appointment->status !== 'scheduled') {
            return back()->with('error', __('appointments.appointment_not_checkinable') ?? 'Only scheduled appointments can be checked in.');
        }

        // Arrival only makes sense on the day of the visit.
        if (! $appointment->appointment_date->isToday()) {
            return back()->with('error', __('appointments.check_in_only_today'));
        }

        if (! $appointment->isCheckedIn()) {
            $appointment->update(['checked_in_at' => now()]);
        }

        return back()->with('success', __('appointments.patient_checked_in'));
    }

    /**
     * Mark an appointment as a no-show (patient never arrived).
     */
    public function markNoShow(Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        // A patient in consultation or already seen cannot be a no-show.
        if ($appointment->status !== 'scheduled') {
            return back()->with('error', __('appointments.cannot_change_finished') ?? 'This appointment can no longer be changed.');
        }

        // You cannot know someone missed a visit that has not happened yet.
        if ($appointment->appointment_date->isFuture()) {
            return back()->with('error', __('appointments.no_show_only_past'));
        }

        $appointment->update([
            'status' => 'no_show',
            'checked_in_at' => null,
            'notes' => ($appointment->notes ? $appointment->notes."\n" : '').'Marked no-show on '.now()->format('Y-m-d H:i'),
        ]);

        if ($appointment->isQueueMode()) {
            $this->scheduler()->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());
        }

        return back()->with('success', __('appointments.appointment_marked_no_show'));
    }

    private function authorizeAppointmentAccess(Appointment $appointment): void
    {
        if (! $this->canManageAppointment($appointment)) {
            abort(403);
        }
    }

    /**
     * Receptionists manage every appointment; doctors only their own.
     */
    private function canManageAppointment(Appointment $appointment): bool
    {
        return Auth::user()->isReceptionist()
            || (Auth::user()->isDoctor() && $appointment->doctor_id === Auth::id());
    }

    private function resolveDoctorId(Request $request): ?int
    {
        if (Auth::user()->isDoctor()) {
            return Auth::id();
        }

        if ($request->filled('doctor_id')) {
            return (int) $request->doctor_id;
        }

        return User::where('role', 'doctor')->value('id');
    }

    public function queue(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->get('date', today()->toDateString());
        $doctors = User::where('role', 'doctor')->orderBy('name')->get();
        $doctorId = $this->resolveDoctorId($request);

        if (! $doctorId) {
            $queueAppointments = collect();
            $selectedDoctor = null;
        } else {
            $selectedDoctor = User::findOrFail($doctorId);
            if (Auth::user()->isDoctor() && $selectedDoctor->id !== Auth::id()) {
                abort(403);
            }

            $queueAppointments = $this->scheduler()->queueAppointments($selectedDoctor->id, $date);
        }

        return view('appointments.queue', compact('queueAppointments', 'doctors', 'selectedDoctor', 'date'));
    }

    public function moveQueueUp(Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        if (! $this->scheduler()->moveQueueAppointment($appointment, 'up')) {
            return back()->with('error', 'Unable to move this queue entry.');
        }

        return back()->with('success', 'Queue order updated.');
    }

    public function moveQueueDown(Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        if (! $this->scheduler()->moveQueueAppointment($appointment, 'down')) {
            return back()->with('error', 'Unable to move this queue entry.');
        }

        return back()->with('success', 'Queue order updated.');
    }

    /**
     * Set the current patient for the doctor's queue
     */
    public function setCurrent(Request $request, Appointment $appointment)
    {
        if (! $this->canManageAppointment($appointment)) {
            abort(403);
        }

        // Already current: nothing to do (double-click safe).
        if ($appointment->status === 'in_progress') {
            return back()->with('success', 'Patient session started successfully.');
        }

        // Allow starting from scheduled, or reopening an accidentally
        // completed visit — but never a cancelled / no-show one.
        if (! $appointment->canTransitionTo('in_progress')) {
            return back()->with('error', __('appointments.cannot_start_finished') ?? 'Only a waiting patient can be set as current.');
        }

        // A consultation can only happen on the day of the appointment;
        // otherwise a "current patient" for another day lingers invisibly.
        if (! $appointment->appointment_date->isToday()) {
            return back()->with('error', __('appointments.start_only_today'));
        }

        // Use database transaction to ensure atomicity
        \DB::transaction(function () use ($appointment) {
            // Serialize against another staff member starting a patient at
            // the same moment (two "current" patients otherwise).
            Appointment::where('doctor_id', $appointment->doctor_id)
                ->whereDate('appointment_date', today())
                ->lockForUpdate()
                ->count();

            // Return the previous current patient to the waiting list
            Appointment::where('doctor_id', $appointment->doctor_id)
                ->whereDate('appointment_date', today())
                ->where('status', 'in_progress')
                ->where('id', '!=', $appointment->id)
                ->update(['status' => 'scheduled']);

            // The patient is in the room, so they have arrived
            $appointment->update([
                'status' => 'in_progress',
                'checked_in_at' => $appointment->checked_in_at ?? now(),
            ]);
        });

        if ($appointment->isQueueMode()) {
            $this->scheduler()->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());
        }

        return back()->with('success', 'Patient session started successfully.');
    }

    /**
     * Mark the current patient as done
     */
    public function markCurrentDone(Request $request)
    {
        $request->validate([
            'doctor_id' => 'nullable|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);

        $doctorId = $request->doctor_id ?: Auth::id();

        // Only receptionists can specify a doctor; doctors can only mark their own
        if ($request->doctor_id && ! Auth::user()->isReceptionist()) {
            abort(403);
        }

        // Use database transaction to ensure atomicity
        $result = \DB::transaction(function () use ($doctorId, $request) {
            // Serialize with other staff completing/starting consultations
            Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', today())
                ->lockForUpdate()
                ->count();

            // Find current appointment for today (in_progress status)
            $current = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', today())
                ->where('status', 'in_progress')
                ->first();

            if (! $current) {
                return ['success' => false, 'message' => 'No active consultation found.'];
            }

            // A stale page or double-click must not complete the NEXT
            // patient's consultation the instant it starts: the form says
            // which consultation it meant to complete.
            if ($request->filled('appointment_id') && (int) $request->appointment_id !== $current->id) {
                return ['success' => false, 'message' => __('appointments.consultation_already_completed')];
            }

            // Mark current as completed, then call the next patient (arrived first)
            $current->update(['status' => 'completed']);
            $next = $this->scheduler()->promoteNextPatient($doctorId);

            return [
                'success' => true,
                'message' => $next
                    ? 'Patient consultation completed successfully. Next patient is now ready.'
                    : 'Patient consultation completed successfully. No more patients for today.',
            ];
        });

        if ($result['success']) {
            $date = today()->toDateString();
            $this->scheduler()->recalculateQueue($doctorId, $date);

            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }

    /**
     * Create a follow-up appointment for a patient
     */
    public function createFollowUp(Request $request, Patient $patient)
    {
        if (! Auth::user()->isDoctor()) {
            abort(403);
        }

        $validated = $request->validate([
            'current_appointment_id' => 'required|exists:appointments,id',
            'follow_up_number' => 'required|integer|min:1|max:365',
            'follow_up_unit' => 'required|in:days,weeks,months',
            'follow_up_reason' => 'nullable|string|max:500',
        ]);

        // Calculate the follow-up date
        $currentAppointment = Appointment::where('id', $validated['current_appointment_id'])
            ->where('patient_id', $patient->id)
            ->where('doctor_id', Auth::id())
            ->firstOrFail();

        $followUpAmount = (int) $validated['follow_up_number'];
        $followUpDate = match ($validated['follow_up_unit']) {
            'days' => Carbon::now()->addDays($followUpAmount),
            'weeks' => Carbon::now()->addWeeks($followUpAmount),
            'months' => Carbon::now()->addMonths($followUpAmount),
        };

        // Land on a working day (search up to a week past the target date)
        $targetDate = null;
        for ($i = 0; $i <= 7; $i++) {
            $candidate = $followUpDate->copy()->addDays($i)->startOfDay();
            if ($this->scheduler()->workingWindow($candidate)) {
                $targetDate = $candidate;
                break;
            }
        }

        if (! $targetDate) {
            return back()->with('error', 'No working day was found near the requested follow-up date.');
        }

        $reason = $validated['follow_up_reason'] ?: __('appointments.follow_up_appointment');
        $notes = 'Follow-up from appointment on '.Carbon::parse($currentAppointment->appointment_date)->format('M j, Y');

        if ($this->scheduler()->hasActiveSameDayAppointment($patient->id, Auth::id(), $targetDate->toDateString())) {
            return back()->with('error', __('appointments.already_has_appointment_today'));
        }

        if (Auth::user()->usesQueueMode()) {
            try {
                $appointment = $this->scheduler()->createQueueAppointment([
                    'patient_id' => $patient->id,
                    'doctor_id' => Auth::id(),
                    'appointment_date' => $targetDate->toDateString(),
                    'reason' => $reason,
                    'notes' => $notes,
                ]);
            } catch (\InvalidArgumentException $e) {
                return back()->with('error', $e->getMessage());
            }

            return redirect()->route('doctor.current')->with('success',
                "Follow-up queued for {$targetDate->format('M j, Y')} (queue #{$appointment->queue_number}, estimated around {$appointment->displayTime()}).");
        }

        // Find a free slot on the target date, or the next day that has one
        $slot = $this->scheduler()->nextAvailableSlot(Auth::id(), $targetDate);
        if (! $slot) {
            $next = $this->scheduler()->nextWorkingDayWithSlot(Auth::id(), $targetDate);
            if (! $next) {
                return back()->with('error', 'No available follow-up slots were found during working hours.');
            }

            $targetDate = Carbon::parse($next['date']);
            $slot = $next['slot'];
        }

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => Auth::id(),
            'appointment_date' => $targetDate->toDateString(),
            'appointment_time' => $slot['time'],
            'status' => 'scheduled',
            'reason' => $reason,
            'notes' => $notes,
        ]);

        $timeAmount = $validated['follow_up_number'];
        $timeUnit = $validated['follow_up_unit'];
        $appointmentDateTime = Carbon::parse($targetDate->toDateString().' '.$slot['time'])->format('M j, Y \a\t g:i A');

        return redirect()->route('doctor.current')->with('success',
            "Follow-up appointment scheduled for {$timeAmount} {$timeUnit} from now on {$appointmentDateTime}");
    }

    /**
     * Get working hours for the frontend
     */
    public function getWorkingHours()
    {
        $workingHours = Setting::getAllWorkingHours();
        $currentWorkingHours = Setting::getWorkingHours();

        return response()->json([
            'all_days' => $workingHours,
            'today' => $currentWorkingHours,
            'is_within_hours' => Setting::isWithinWorkingHours(),
            'next_working_time' => Setting::getNextWorkingTime()?->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Cancel an appointment
     */
    public function cancel(Appointment $appointment)
    {
        if (! $this->canManageAppointment($appointment)) {
            return back()->with('error', 'Unauthorized action.');
        }

        if (! $appointment->isCancellable()) {
            return back()->with('error', __('appointments.cannot_change_finished') ?? 'This appointment can no longer be changed.');
        }

        // Use database transaction to ensure atomicity
        $result = \DB::transaction(function () use ($appointment) {
            // Check if this was the current patient
            $wasCurrentPatient = ($appointment->status === 'in_progress');

            // Cancel the appointment; the patient is no longer waiting
            $appointment->update([
                'status' => 'cancelled',
                'checked_in_at' => null,
                'notes' => ($appointment->notes ? $appointment->notes."\n" : '').'Cancelled on '.now()->format('Y-m-d H:i'),
            ]);

            // If we cancelled the current patient, automatically call the next one
            if ($wasCurrentPatient) {
                return $this->scheduler()->promoteNextPatient($appointment->doctor_id)
                    ? 'Current appointment cancelled. Next patient is now ready.'
                    : 'Appointment cancelled successfully. No more patients for today.';
            }

            return 'Appointment cancelled successfully!';
        });

        if ($appointment->isQueueMode()) {
            $this->scheduler()->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());
        }

        // A freed slot is an opportunity: surface the waitlist to the front desk.
        $waitingCount = \App\Models\WaitlistEntry::waiting()
            ->where('doctor_id', $appointment->doctor_id)
            ->count();

        if ($waitingCount > 0) {
            $result .= ' '.__('appointments.waitlist_hint_after_cancel', ['count' => $waitingCount]);
        }

        return back()->with('success', $result);
    }

    /**
     * Move a waiting appointment to the next free slot (today or the next
     * day with availability), without hunting through the calendar.
     */
    public function rebookNextSlot(Appointment $appointment)
    {
        if (! $this->canManageAppointment($appointment)) {
            abort(403);
        }

        if ($appointment->isQueueMode()) {
            return back()->with('error', __('appointments.rebook_queue_mode'));
        }

        if ($appointment->status !== 'scheduled') {
            return back()->with('error', __('appointments.rebook_only_scheduled'));
        }

        // Search up to two weeks ahead, skipping days where the patient
        // already has another active appointment with this doctor.
        $newDate = null;
        $newSlot = null;

        for ($i = 0; $i <= 14; $i++) {
            $candidate = today()->addDays($i);

            if ($this->scheduler()->hasActiveSameDayAppointment($appointment->patient_id, $appointment->doctor_id, $candidate, $appointment->id)) {
                continue;
            }

            $slot = $this->scheduler()->nextAvailableSlot($appointment->doctor_id, $candidate, $appointment->id);
            if ($slot) {
                $newDate = $candidate;
                $newSlot = $slot;
                break;
            }
        }

        if (! $newSlot) {
            return back()->with('error', __('appointments.rebook_no_slot'));
        }

        // Double-click / already-optimal: rebooking onto its own slot is a no-op.
        if ($newDate->toDateString() === $appointment->appointment_date->toDateString()
            && $newSlot['time'] === $appointment->appointment_time->format('H:i')) {
            return back()->with('info', __('appointments.already_earliest_slot'));
        }

        $previous = $appointment->appointment_date->format('M j, Y').' '.$appointment->displayTime();

        $appointment->update([
            'appointment_date' => $newDate->toDateString(),
            'appointment_time' => $newSlot['time'],
            'reminder_sent_at' => null,
            'notes' => ($appointment->notes ? $appointment->notes."\n" : '')
                ."Rebooked from {$previous} on ".now()->format('Y-m-d H:i'),
        ]);

        $datetime = Carbon::parse($newDate->toDateString().' '.$newSlot['time'])->format('M j, Y \a\t g:i A');

        return back()->with('success', __('appointments.rebooked_to', ['datetime' => $datetime]));
    }

    /**
     * Simple reschedule - redirect to edit page
     */
    public function reschedule(Appointment $appointment)
    {
        // Only receptionists or the owning doctor can reschedule
        if (! (Auth::user()->isReceptionist() || (Auth::user()->isDoctor() && $appointment->doctor_id === Auth::id()))) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Redirect to edit page for manual rescheduling
        return redirect()->route('appointments.edit', $appointment)
            ->with('info', 'Please select a new date and time for this appointment.');
    }
}
