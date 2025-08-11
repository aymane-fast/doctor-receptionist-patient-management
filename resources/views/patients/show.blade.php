@extends('layouts.app')

@section('title', $patient->full_name . ' - Patient Details')

@section('content')
<div class="space-y-8">
    <!-- Modern Header -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-6 lg:space-y-0">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-blue-200 rounded-3xl flex items-center justify-center animate-float">
                    <i class="fas fa-user text-blue-600 text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $patient->full_name }}</h1>
                    <div class="flex items-center space-x-4 text-gray-600 mb-2">
                        <span class="flex items-center space-x-2 bg-blue-50 px-3 py-1 rounded-xl">
                            <i class="fas fa-id-card text-blue-600"></i>
                            <span class="font-medium">{{ $patient->patient_id }}</span>
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="flex items-center space-x-2 bg-amber-50 px-3 py-1 rounded-xl text-amber-700">
                            <i class="fas fa-birthday-cake text-amber-600"></i>
                            <span class="font-medium">{{ $patient->age }} years old</span>
                        </span>
                        <span class="flex items-center space-x-2 bg-purple-50 px-3 py-1 rounded-xl text-purple-700">
                            <i class="fas fa-venus-mars text-purple-600"></i>
                            <span class="font-medium">{{ ucfirst($patient->gender) }}</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                   class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2 deep-shadow">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Book Appointment</span>
                </a>
                <a href="{{ route('patients.edit', $patient) }}" 
                   class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-edit"></i>
                    <span>Edit Patient</span>
                </a>
                <a href="{{ route('patients.index') }}" 
                   class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Patients</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Patient Information Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Patient Details Sidebar -->
        <div class="xl:col-span-1 space-y-6">
            <!-- Basic Information -->
            <div class="glass-effect rounded-3xl p-6 modern-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-info-circle text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Basic Information</h3>
                </div>
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Full Name</label>
                        <p class="text-gray-900 font-medium mt-1">{{ $patient->full_name }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Date of Birth</label>
                        <p class="text-gray-900 font-medium mt-1">{{ $patient->birth_date->format('F j, Y') }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Age</label>
                        <p class="text-gray-900 font-medium mt-1">{{ $patient->age }} years old</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Gender</label>
                        <p class="text-gray-900 font-medium mt-1">{{ ucfirst($patient->gender) }}</p>
                    </div>
                    @if($patient->id_card_number)
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">ID Card Number</label>
                        <p class="text-gray-900 font-medium mt-1">{{ $patient->id_card_number }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Contact Information -->
            <div class="glass-effect rounded-3xl p-6 modern-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-phone text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Contact Information</h3>
                </div>
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Phone</label>
                        <p class="mt-1">
                            <a href="tel:{{ $patient->phone }}" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                {{ $patient->phone }}
                            </a>
                        </p>
                    </div>
                    @if($patient->email)
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Email</label>
                        <p class="mt-1">
                            <a href="mailto:{{ $patient->email }}" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                {{ $patient->email }}
                            </a>
                        </p>
                    </div>
                    @endif
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Address</label>
                        <p class="text-gray-900 font-medium mt-1">{{ $patient->address }}</p>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            @if($patient->emergency_contact_name || $patient->emergency_contact_phone)
            <div class="glass-effect rounded-3xl p-6 modern-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center animate-pulse-glow">
                        <i class="fas fa-phone-alt text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Emergency Contact</h3>
                </div>
                <div class="space-y-4">
                    @if($patient->emergency_contact_name)
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Name</label>
                        <p class="text-gray-900 font-medium mt-1">{{ $patient->emergency_contact_name }}</p>
                    </div>
                    @endif
                    @if($patient->emergency_contact_phone)
                    <div class="bg-white rounded-2xl p-4 border border-gray-100">
                        <label class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Phone</label>
                        <p class="mt-1">
                            <a href="tel:{{ $patient->emergency_contact_phone }}" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                {{ $patient->emergency_contact_phone }}
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Medical Information -->
            @if($patient->allergies || $patient->chronic_conditions)
            <div class="glass-effect rounded-3xl p-6 modern-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-heartbeat text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Medical Information</h3>
                </div>
                <div class="space-y-4">
                    @if($patient->allergies)
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-4 border border-red-200">
                        <label class="text-sm font-semibold text-red-700 uppercase tracking-wide flex items-center space-x-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Allergies</span>
                        </label>
                        <p class="text-red-900 font-medium mt-2">{{ $patient->allergies }}</p>
                    </div>
                    @endif
                    @if($patient->chronic_conditions)
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-4 border border-orange-200">
                        <label class="text-sm font-semibold text-orange-700 uppercase tracking-wide flex items-center space-x-2">
                            <i class="fas fa-heartbeat"></i>
                            <span>Chronic Conditions</span>
                        </label>
                        <p class="text-orange-900 font-medium mt-2">{{ $patient->chronic_conditions }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Recent Activities -->
        <div class="xl:col-span-2 space-y-6">
            <!-- Recent Appointments -->
            <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar text-white"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Recent Appointments</h3>
                        </div>
                        <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                           class="bg-gradient-to-r from-blue-100 to-blue-50 hover:from-blue-200 hover:to-blue-100 text-blue-800 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center space-x-2 border border-blue-200">
                            <i class="fas fa-plus"></i>
                            <span>New Appointment</span>
                        </a>
                    </div>
                </div>
                
                <div class="p-6 max-h-96 overflow-y-auto">
                    @if($recentAppointments->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentAppointments as $appointment)
                            <div class="bg-white border border-gray-100 rounded-2xl p-4 hover:shadow-lg transition-all duration-200 card-hover">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-calendar text-blue-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-lg">
                                                {{ $appointment->appointment_date->format('M j, Y') }}
                                            </h4>
                                            <p class="text-sm text-gray-500 flex items-center space-x-3">
                                                <span class="flex items-center space-x-1">
                                                    <i class="fas fa-clock w-3"></i>
                                                    <span>{{ $appointment->appointment_time->format('g:i A') }}</span>
                                                </span>
                                                <span class="flex items-center space-x-1">
                                                    <i class="fas fa-user-md w-3"></i>
                                                    <span>Dr. {{ $appointment->doctor->name }}</span>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full
                                            @if($appointment->status == 'scheduled') bg-gradient-to-r from-amber-100 to-amber-50 text-amber-800 border border-amber-200
                                            @elseif($appointment->status == 'in_progress') bg-gradient-to-r from-blue-100 to-blue-50 text-blue-800 border border-blue-200
                                            @elseif($appointment->status == 'completed') bg-gradient-to-r from-emerald-100 to-emerald-50 text-emerald-800 border border-emerald-200
                                            @else bg-gradient-to-r from-red-100 to-red-50 text-red-800 border border-red-200 @endif">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                        <a href="{{ route('appointments.show', $appointment) }}" class="w-9 h-9 bg-blue-100 hover:bg-blue-200 rounded-xl flex items-center justify-center transition-colors">
                                            <i class="fas fa-eye text-blue-600 text-sm"></i>
                                        </a>
                                    </div>
                                </div>
                                @if($appointment->reason)
                                <p class="text-sm text-gray-600 mt-3 pl-16 bg-gray-50 rounded-xl p-3">{{ Str::limit($appointment->reason, 120) }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">No appointments yet</h4>
                            <p class="text-gray-500 mb-4">Schedule the first appointment for this patient</p>
                            <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                               class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 inline-flex items-center space-x-2">
                                <i class="fas fa-plus"></i>
                                <span>Schedule Appointment</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Medical Records -->
            @if(auth()->user()->isDoctor())
            <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-file-medical text-white"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Medical Records</h3>
                        </div>
                        <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" 
                           class="bg-gradient-to-r from-emerald-100 to-emerald-50 hover:from-emerald-200 hover:to-emerald-100 text-emerald-800 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center space-x-2 border border-emerald-200">
                            <i class="fas fa-plus"></i>
                            <span>New Record</span>
                        </a>
                    </div>
                </div>
                
                <div class="p-6 max-h-96 overflow-y-auto">
                    @if($recentRecords->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentRecords as $record)
                            <div class="bg-white border border-gray-100 rounded-2xl p-4 hover:shadow-lg transition-all duration-200 card-hover">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-file-medical text-emerald-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-lg">
                                                {{ $record->visit_date->format('M j, Y') }}
                                            </h4>
                                            <p class="text-sm text-gray-500 flex items-center space-x-2">
                                                <i class="fas fa-user-md w-3"></i>
                                                <span>Dr. {{ $record->doctor->name }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ route('medical-records.show', $record) }}" class="w-9 h-9 bg-emerald-100 hover:bg-emerald-200 rounded-xl flex items-center justify-center transition-colors">
                                        <i class="fas fa-eye text-emerald-600 text-sm"></i>
                                    </a>
                                </div>
                                @if($record->diagnosis)
                                <p class="text-sm text-gray-600 mt-3 pl-16 bg-gray-50 rounded-xl p-3">{{ Str::limit($record->diagnosis, 120) }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-file-medical text-gray-400 text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">No medical records yet</h4>
                            <p class="text-gray-500 mb-4">Create the first medical record for this patient</p>
                            <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" 
                               class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 inline-flex items-center space-x-2">
                                <i class="fas fa-plus"></i>
                                <span>Create Record</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
