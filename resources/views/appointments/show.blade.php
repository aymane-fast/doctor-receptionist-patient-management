@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Appointment Details</h1>
        <div class="flex space-x-3">
            <a href="{{ route('appointments.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Appointments
            </a>
            <a href="{{ route('appointments.edit', $appointment) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Appointment Info -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Patient Info -->
                    <div class="md:col-span-2 border-b pb-4 mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Patient Information</h2>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</h3>
                                <p class="text-gray-600">{{ $appointment->patient->phone }} • {{ $appointment->patient->email }}</p>
                                <p class="text-gray-600">Age: {{ $appointment->patient->age }} years</p>
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Info -->
                    <div class="md:col-span-2 border-b pb-4 mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Doctor Information</h2>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user-md text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg">Dr. {{ $appointment->doctor->name }}</h3>
                                <p class="text-gray-600">{{ $appointment->doctor->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Details -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Date</label>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, F j, Y') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Time</label>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($appointment->status == 'scheduled') bg-yellow-100 text-yellow-800
                            @elseif($appointment->status == 'confirmed') bg-blue-100 text-blue-800
                            @elseif($appointment->status == 'completed') bg-green-100 text-green-800
                            @elseif($appointment->status == 'cancelled') bg-red-100 text-red-800
                            @elseif($appointment->status == 'no-show') bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst(str_replace('-', ' ', $appointment->status)) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Type</label>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ ucfirst(str_replace('-', ' ', $appointment->appointment_type)) }}
                        </p>
                    </div>

                    @if($appointment->purpose)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Purpose</label>
                        <p class="text-gray-900">{{ $appointment->purpose }}</p>
                    </div>
                    @endif

                    @if($appointment->notes)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Notes</label>
                        <p class="text-gray-900">{{ $appointment->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    @if($appointment->status != 'completed' && $appointment->status != 'cancelled')
                        <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-check mr-2"></i>Mark as Completed
                            </button>
                        </form>
                    @endif

                    @if($appointment->status == 'scheduled')
                        <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-check-circle mr-2"></i>Confirm Appointment
                            </button>
                        </form>
                    @endif

                    @if($appointment->status != 'cancelled' && $appointment->status != 'completed')
                        <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors"
                                    onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                <i class="fas fa-times mr-2"></i>Cancel Appointment
                            </button>
                        </form>
                    @endif

                    @if(auth()->user()->isDoctor() && $appointment->status == 'completed')
                        <a href="{{ route('medical-records.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}" 
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors block text-center">
                            <i class="fas fa-file-medical mr-2"></i>Add Medical Record
                        </a>
                    @endif
                </div>
            </div>

            <!-- Appointment Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Appointment Info</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Created:</span>
                        <span class="text-gray-900">{{ $appointment->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Last Updated:</span>
                        <span class="text-gray-900">{{ $appointment->updated_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">ID:</span>
                        <span class="text-gray-900">#{{ $appointment->id }}</span>
                    </div>
                </div>
            </div>

            <!-- Related Records -->
            @if(auth()->user()->isDoctor())
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Records</h3>
                <div class="space-y-2">
                    <a href="{{ route('medical-records.index', ['patient_id' => $appointment->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-file-medical mr-2"></i>Medical Records
                    </a>
                    <a href="{{ route('prescriptions.index', ['patient_id' => $appointment->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-prescription-bottle mr-2"></i>Prescriptions
                    </a>
                    <a href="{{ route('documents.index', ['patient_id' => $appointment->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-file-alt mr-2"></i>Documents
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
