<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\DocumentController;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

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
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Patient management (accessible by both roles)
    Route::resource('patients', PatientController::class);
    
    // Appointment management (accessible by both roles)
    Route::resource('appointments', AppointmentController::class);
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    Route::post('/appointments/{appointment}/set-current', [AppointmentController::class, 'setCurrent'])->name('appointments.set-current');
    Route::post('/appointments/current/mark-done', [AppointmentController::class, 'markCurrentDone'])->name('appointments.mark-current-done');
    
    // Medical records (doctors only)
    Route::resource('medical-records', MedicalRecordController::class);
    Route::resource('prescriptions', PrescriptionController::class);
    
    // Document management (accessible by both roles)
    Route::resource('documents', DocumentController::class);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
});
