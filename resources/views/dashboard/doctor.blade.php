@extends('layouts.app')

@section('title', 'Doctor Dashboard')

@section('content')
<div class="space-y-6">
    @if(isset($currentAppointment))
    <div class="bg-white rounded-lg shadow p-6 border-2 border-blue-500">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Current Patient</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $currentAppointment->patient->full_name }}</div>
                    <div class="text-gray-600">{{ $currentAppointment->appointment_time->format('g:i A') }}</div>
                </div>
            </div>
            <div class="space-x-2">
                <a href="{{ route('patients.show', $currentAppointment->patient_id) }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded">History</a>
                <a href="{{ route('medical-records.create', ['patient_id' => $currentAppointment->patient_id, 'appointment_id' => $currentAppointment->id]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">New Record</a>
                <a href="{{ route('prescriptions.create', ['patient_id' => $currentAppointment->patient_id]) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">Prescription</a>
                <form action="{{ route('appointments.mark-current-done') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Mark Done</button>
                </form>
            </div>
        </div>
    </div>
    @endif
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-user-md text-blue-600 mr-2"></i>
                    Welcome back, Dr. {{ auth()->user()->name }}
                </h1>
                <p class="text-gray-600 mt-1">Here's what's happening in your practice today</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</div>
                <div class="text-lg font-semibold text-gray-900">{{ now()->format('g:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Patients</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_patients'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-day text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Today's Appointments</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['today_appointments'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Pending Appointments</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_appointments'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Completed Today</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_today'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Appointments -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                        Today's Appointments
                    </h2>
                    <a href="{{ route('appointments.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-1"></i> New
                    </a>
                </div>
            </div>
            <div class="p-6">
                @if($todayAppointments->count() > 0)
                    <div class="space-y-4">
                        @foreach($todayAppointments as $appointment)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $appointment->patient->full_name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $appointment->appointment_time->format('g:i A') }}</p>
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
                        <p class="text-gray-500">No appointments scheduled for today</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Medical Records -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-file-medical text-green-600 mr-2"></i>
                        Recent Medical Records
                    </h2>
                    <a href="{{ route('medical-records.create') }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition">
                        <i class="fas fa-plus mr-1"></i> New Record
                    </a>
                </div>
            </div>
            <div class="p-6">
                @if($recentRecords->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentRecords as $record)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-file-medical text-green-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $record->patient->full_name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $record->visit_date->format('M j, Y') }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('medical-records.show', $record) }}" class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            @if($record->diagnosis)
                            <p class="text-sm text-gray-600 mt-2">{{ Str::limit($record->diagnosis, 60) }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-file-medical text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No recent medical records</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-bolt text-yellow-600 mr-2"></i>
            Quick Actions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <a href="{{ route('patients.create') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-user-plus text-blue-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Add Patient</span>
            </a>
            <a href="{{ route('appointments.create') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-calendar-plus text-green-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Schedule Appointment</span>
            </a>
            <a href="{{ route('medical-records.create') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-file-medical-alt text-purple-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-gray-900">New Medical Record</span>
            </a>
            <a href="{{ route('prescriptions.create') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-prescription text-orange-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Write Prescription</span>
            </a>
            <a href="{{ route('patients.index') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-search text-gray-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Search Patients</span>
            </a>
        </div>
    </div>
</div>
@endsection
