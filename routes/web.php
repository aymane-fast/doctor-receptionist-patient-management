<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\StatisticsController;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

// Language switching routes (accessible to all)
Route::get('/lang/{language}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/api/languages', [LanguageController::class, 'getAvailableLanguages'])->name('api.languages');
Route::get('/api/language/current', [LanguageController::class, 'getCurrentLanguage'])->name('api.language.current');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard (accessible by both roles)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Settings (accessible by both roles)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/working-hours', [SettingsController::class, 'updateWorkingHours'])->name('settings.working-hours');
    Route::post('/settings/clinic-info', [SettingsController::class, 'updateClinicInfo'])->name('settings.clinic-info');
    Route::post('/settings/appointment-duration', [SettingsController::class, 'updateAppointmentDuration'])->name('settings.appointment-duration');
    Route::post('/settings/export-data', [SettingsController::class, 'exportClinicData'])->name('settings.export-data');
    Route::get('/api/settings/working-status', [SettingsController::class, 'workingStatus'])->name('api.settings.working-status');
    
    // Patient management (accessible by both roles)
    Route::resource('patients', PatientController::class);
    
    // Appointment management (accessible by both roles)
    Route::resource('appointments', AppointmentController::class);
    Route::get('/api/patients/search', [AppointmentController::class, 'searchPatients'])->name('api.patients.search');
    Route::get('/api/working-hours', [AppointmentController::class, 'getWorkingHours'])->name('api.working-hours');
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    Route::post('/appointments/{appointment}/set-current', [AppointmentController::class, 'setCurrent'])->name('appointments.set-current');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/current/mark-done', [AppointmentController::class, 'markCurrentDone'])->name('appointments.mark-current-done');
    Route::post('/appointments/{patient}/follow-up', [AppointmentController::class, 'createFollowUp'])->name('appointments.create-follow-up');
    
    // Patient search APIs (accessible by both roles for autocomplete)
    Route::get('/api/medical-records/patients/search', [MedicalRecordController::class, 'searchPatients'])->name('api.medical-records.patients.search');
    Route::get('/api/prescriptions/patients/search', [PrescriptionController::class, 'searchPatients'])->name('api.prescriptions.patients.search');
    
    // Available slots API
    Route::get('/api/appointments/next-available-slot', [AppointmentController::class, 'getNextAvailableSlot'])->name('api.appointments.next-available-slot');
    
    // Prescription management (accessible by both roles)
    Route::resource('prescriptions', PrescriptionController::class);
    Route::get('/prescriptions/{prescription}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');
    
    // Statistics (accessible by both roles)
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/api/statistics', [StatisticsController::class, 'api'])->name('api.statistics');
    Route::get('/api/statistics/trend', [StatisticsController::class, 'getTrendData'])->name('api.statistics.trend');
});

// Doctor-only routes
Route::middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/doctor/current', [DoctorController::class, 'current'])->name('doctor.current');
    Route::resource('medical-records', MedicalRecordController::class);
});
