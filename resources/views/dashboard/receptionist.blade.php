@extends('layouts.app')

@section('title', 'Reception Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-user-tie text-blue-600 mr-2"></i>
                    Welcome back, {{ auth()->user()->name }}
                </h1>
                <p class="text-gray-600 mt-1">Reception Dashboard - Manage patients and appointments</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</div>
                <div class="text-lg font-semibold text-gray-900">{{ now()->format('g:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Current Patients By Doctor -->
    @if(isset($currentByDoctor) && $currentByDoctor->count())
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-user-clock text-blue-600 mr-2"></i>
            Current Patients (Today)
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($currentByDoctor as $docId => $curr)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="text-sm text-gray-500">Dr. {{ $curr->doctor->name }}</div>
                <div class="font-semibold text-gray-900">{{ $curr->patient->full_name }}</div>
                <div class="text-gray-600 text-sm">{{ $curr->appointment_time->format('g:i A') }}</div>
                @if(isset($nextByDoctor[$docId]) && $nextByDoctor[$docId])
                <div class="mt-2 text-xs text-gray-500">Next up: {{ $nextByDoctor[$docId]->patient->full_name }} at {{ $nextByDoctor[$docId]->appointment_time->format('g:i A') }}</div>
                @endif
                <div class="mt-3 space-x-2">
                    <form action="{{ route('appointments.mark-current-done') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="doctor_id" value="{{ $curr->doctor_id }}">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">Mark Done</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

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
                    <h3 class="text-sm font-medium text-gray-500">Scheduled Appointments</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['scheduled_appointments'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-plus text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">New Patients This Week</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['new_patients_this_week'] }}</p>
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
                     Today's Appointments <span class="text-sm text-gray-500">({{ $stats['today_appointments'] }})</span>
                    </h2>
                    <a href="{{ route('appointments.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-1"></i> Book Appointment
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
                                 <form action="{{ route('appointments.set-current', $appointment) }}" method="POST" class="inline">
                                     @csrf
                                     <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm">Set Current</button>
                                 </form>
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
                <div class="mt-4">{{ $todayAppointments->links() }}</div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No appointments scheduled for today</p>
                    </div>
                @endif
            </div>
        </div>

     <!-- Recent Patients -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-users text-green-600 mr-2"></i>
                        Recent Patients
                    </h2>
                    <a href="{{ route('patients.create') }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition">
                        <i class="fas fa-plus mr-1"></i> Add Patient
                    </a>
                </div>
            </div>
            <div class="p-6">
                @if($recentPatients->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentPatients as $patient)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-green-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $patient->full_name }}</h3>
                                        <p class="text-sm text-gray-500">
                                            {{ $patient->patient_id }} • Age: {{ $patient->age }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500">{{ $patient->created_at->format('M j') }}</span>
                                    <a href="{{ route('patients.show', $patient) }}" class="text-green-600 hover:text-green-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">{{ $patient->phone }}</p>
                        </div>
                        @endforeach
                </div>
                <div class="mt-4">{{ $recentPatients->links() }}</div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-user-plus text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No patients registered yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Appointments -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                Upcoming Appointments
            </h2>
        </div>
        <div class="p-6">
            @if($upcomingAppointments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($upcomingAppointments as $appointment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-blue-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $appointment->patient->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $appointment->patient->patient_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Dr. {{ $appointment->doctor->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $appointment->appointment_date->format('M j, Y') }}<br>
                                    {{ $appointment->appointment_time->format('g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($appointment->status == 'scheduled') bg-yellow-100 text-yellow-800
                                        @elseif($appointment->status == 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($appointment->status == 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="{{ route('appointments.show', $appointment) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('appointments.edit', $appointment) }}" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">No upcoming appointments</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-bolt text-yellow-600 mr-2"></i>
            Quick Actions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('patients.create') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-user-plus text-blue-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Register New Patient</span>
            </a>
            <a href="{{ route('appointments.create') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-calendar-plus text-green-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Book Appointment</span>
            </a>
            <a href="{{ route('patients.index') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-search text-purple-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-gray-900">Search Patients</span>
            </a>
            <a href="{{ route('appointments.index') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-calendar-alt text-orange-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-gray-900">View All Appointments</span>
            </a>
        </div>
    </div>
</div>
@endsection
