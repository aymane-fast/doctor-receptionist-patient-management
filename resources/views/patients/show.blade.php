@extends('layouts.app')

@section('title', $patient->full_name . ' - Patient Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $patient->full_name }}</h1>
                    <p class="text-gray-600">Patient ID: {{ $patient->patient_id }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-birthday-cake mr-1"></i>{{ $patient->age }} years old
                        </span>
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-venus-mars mr-1"></i>{{ ucfirst($patient->gender) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                </a>
                <a href="{{ route('patients.edit', $patient) }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-edit mr-2"></i>Edit Patient
                </a>
                <a href="{{ route('patients.index') }}" 
                   class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Patients
                </a>
            </div>
        </div>
    </div>

    <!-- Patient Information Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Basic Information -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    Basic Information
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Full Name</label>
                        <p class="text-gray-900">{{ $patient->full_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                        <p class="text-gray-900">{{ $patient->birth_date->format('F j, Y') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Age</label>
                        <p class="text-gray-900">{{ $patient->age }} years old</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Gender</label>
                        <p class="text-gray-900">{{ ucfirst($patient->gender) }}</p>
                    </div>
                    @if($patient->id_card_number)
                    <div>
                        <label class="text-sm font-medium text-gray-500">ID Card Number</label>
                        <p class="text-gray-900">{{ $patient->id_card_number }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-phone text-green-600 mr-2"></i>
                    Contact Information
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-gray-900">
                            <a href="tel:{{ $patient->phone }}" class="text-blue-600 hover:text-blue-800">
                                {{ $patient->phone }}
                            </a>
                        </p>
                    </div>
                    @if($patient->email)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-900">
                            <a href="mailto:{{ $patient->email }}" class="text-blue-600 hover:text-blue-800">
                                {{ $patient->email }}
                            </a>
                        </p>
                    </div>
                    @endif
                    <div>
                        <label class="text-sm font-medium text-gray-500">Address</label>
                        <p class="text-gray-900">{{ $patient->address }}</p>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            @if($patient->emergency_contact_name || $patient->emergency_contact_phone)
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-phone-alt text-red-600 mr-2"></i>
                    Emergency Contact
                </h3>
                <div class="space-y-4">
                    @if($patient->emergency_contact_name)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Name</label>
                        <p class="text-gray-900">{{ $patient->emergency_contact_name }}</p>
                    </div>
                    @endif
                    @if($patient->emergency_contact_phone)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-gray-900">
                            <a href="tel:{{ $patient->emergency_contact_phone }}" class="text-blue-600 hover:text-blue-800">
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
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-heartbeat text-purple-600 mr-2"></i>
                    Medical Information
                </h3>
                <div class="space-y-4">
                    @if($patient->allergies)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Allergies</label>
                        <p class="text-gray-900">{{ $patient->allergies }}</p>
                    </div>
                    @endif
                    @if($patient->chronic_conditions)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Chronic Conditions</label>
                        <p class="text-gray-900">{{ $patient->chronic_conditions }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Recent Activities -->
        <div class="lg:col-span-2">
            <!-- Recent Appointments -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-calendar text-blue-600 mr-2"></i>
                        Recent Appointments
                    </h3>
                    <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                       class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-plus mr-1"></i>New Appointment
                    </a>
                </div>
                
                @if($recentAppointments->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentAppointments as $appointment)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-calendar text-blue-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">
                                            {{ $appointment->appointment_date->format('M j, Y') }}
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            {{ $appointment->appointment_time->format('g:i A') }} - Dr. {{ $appointment->doctor->name }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($appointment->status == 'scheduled') bg-yellow-100 text-yellow-800
                                        @elseif($appointment->status == 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($appointment->status == 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                    <a href="{{ route('appointments.show', $appointment) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            @if($appointment->reason)
                            <p class="text-sm text-gray-600 mt-2">{{ $appointment->reason }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No appointments yet</p>
                    </div>
                @endif
            </div>

            <!-- Recent Medical Records -->
            @if(auth()->user()->isDoctor())
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-file-medical text-green-600 mr-2"></i>
                        Medical Records
                    </h3>
                    <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" 
                       class="text-green-600 hover:text-green-800 text-sm">
                        <i class="fas fa-plus mr-1"></i>New Record
                    </a>
                </div>
                
                @if($recentRecords->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentRecords as $record)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-file-medical text-green-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">
                                            {{ $record->visit_date->format('M j, Y') }}
                                        </h4>
                                        <p class="text-sm text-gray-500">Dr. {{ $record->doctor->name }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('medical-records.show', $record) }}" class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            @if($record->diagnosis)
                            <p class="text-sm text-gray-600 mt-2">{{ Str::limit($record->diagnosis, 100) }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-file-medical text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No medical records yet</p>
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
