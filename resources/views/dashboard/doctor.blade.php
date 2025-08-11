@extends('layouts.app')

@section('title', 'Doctor Dashboard')

@section('content')
<div class="space-y-8">
    @if(isset($currentAppointment))
    <!-- Current Patient - Hero Section -->
    <div class="relative bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 rounded-3xl p-8 text-white overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-24 -translate-x-24"></div>
        
        <div class="relative flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center animate-float">
                    <i class="fas fa-user text-white text-3xl"></i>
                </div>
                <div>
                    <div class="text-sm text-blue-100 mb-1">Current Patient</div>
                    <div class="text-3xl font-bold text-white mb-2">{{ $currentAppointment->patient->full_name }}</div>
                    <div class="flex items-center space-x-4 text-blue-100">
                        <span><i class="fas fa-clock mr-2"></i>{{ $currentAppointment->appointment_time->format('g:i A') }}</span>
                        <span><i class="fas fa-calendar mr-2"></i>{{ $currentAppointment->appointment_date->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('doctor.current') }}" class="bg-white hover:bg-gray-100 text-blue-700 font-semibold px-8 py-4 rounded-2xl transition-all duration-200 deep-shadow hover:shadow-xl flex items-center space-x-3">
                    <i class="fas fa-arrow-right"></i>
                    <span>Open Workspace</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Welcome Header -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-user-md text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Welcome back, <span class="text-gradient">Dr. {{ auth()->user()->name }}</span>
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">Here's what's happening in your practice today</p>
                </div>
            </div>
            <div class="text-right bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4">
                <div class="text-sm text-gray-500 mb-1">{{ now()->format('l') }}</div>
                <div class="text-xl font-semibold text-gray-900">{{ now()->format('F j, Y') }}</div>
                <div class="text-lg font-medium text-blue-600">{{ now()->format('g:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Modern Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Patients -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 card-hover modern-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-white text-lg"></i>
                </div>
                <div class="text-blue-600">
                    <i class="fas fa-arrow-up text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-medium text-blue-700 mb-1">Total Patients</h3>
                <p class="text-3xl font-bold text-blue-900 mb-1">{{ $stats['total_patients'] }}</p>
                <p class="text-xs text-blue-600">Active in system</p>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl p-6 card-hover modern-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-day text-white text-lg"></i>
                </div>
                <div class="text-emerald-600">
                    <i class="fas fa-arrow-up text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-medium text-emerald-700 mb-1">Today's Schedule</h3>
                <p class="text-3xl font-bold text-emerald-900 mb-1">{{ $stats['today_appointments'] }}</p>
                <p class="text-xs text-emerald-600">Appointments today</p>
            </div>
        </div>

        <!-- Pending Appointments -->
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl p-6 card-hover modern-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
                <div class="text-amber-600">
                    <i class="fas fa-minus text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-medium text-amber-700 mb-1">Pending</h3>
                <p class="text-3xl font-bold text-amber-900 mb-1">{{ $stats['pending_appointments'] }}</p>
                <p class="text-xs text-amber-600">Awaiting attention</p>
            </div>
        </div>

        <!-- Completed Today -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 card-hover modern-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
                <div class="text-purple-600">
                    <i class="fas fa-arrow-up text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-medium text-purple-700 mb-1">Completed</h3>
                <p class="text-3xl font-bold text-purple-900 mb-1">{{ $stats['completed_today'] }}</p>
                <p class="text-xs text-purple-600">Finished today</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Today's Appointments -->
        <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-day text-white"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Today's Schedule</h2>
                    </div>
                    <a href="{{ route('appointments.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>New</span>
                    </a>
                </div>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                @if($todayAppointments->count() > 0)
                    <div class="space-y-4">
                        @foreach($todayAppointments as $appointment)
                        <div class="bg-white border border-gray-100 rounded-2xl p-4 hover:shadow-lg transition-all duration-200 card-hover">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $appointment->patient->full_name }}</h3>
                                        <p class="text-sm text-gray-500 flex items-center space-x-2">
                                            <i class="fas fa-clock w-3"></i>
                                            <span>{{ $appointment->appointment_time->format('g:i A') }}</span>
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
                                    <a href="{{ route('appointments.show', $appointment) }}" class="w-8 h-8 bg-blue-100 hover:bg-blue-200 rounded-lg flex items-center justify-center transition-colors">
                                        <i class="fas fa-eye text-blue-600 text-sm"></i>
                                    </a>
                                </div>
                            </div>
                            @if($appointment->reason)
                            <p class="text-sm text-gray-600 mt-3 pl-16">{{ Str::limit($appointment->reason, 80) }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-6">{{ $todayAppointments->links() }}</div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No appointments today</p>
                        <p class="text-gray-400 text-sm">Enjoy your free schedule!</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Medical Records -->
        <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-medical text-white"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Medical Records</h2>
                    </div>
                    <a href="{{ route('medical-records.create') }}" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
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
                                        <h3 class="font-semibold text-gray-900">{{ $record->patient->full_name }}</h3>
                                        <p class="text-sm text-gray-500 flex items-center space-x-2">
                                            <i class="fas fa-calendar w-3"></i>
                                            <span>{{ $record->visit_date->format('M j, Y') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('medical-records.show', $record) }}" class="w-8 h-8 bg-emerald-100 hover:bg-emerald-200 rounded-lg flex items-center justify-center transition-colors">
                                    <i class="fas fa-eye text-emerald-600 text-sm"></i>
                                </a>
                            </div>
                            @if($record->diagnosis)
                            <p class="text-sm text-gray-600 mt-3 pl-16">{{ Str::limit($record->diagnosis, 80) }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-6">{{ $recentRecords->links() }}</div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-medical text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No recent records</p>
                        <p class="text-gray-400 text-sm">Start creating medical records</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modern Quick Actions -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-bolt text-white"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Quick Actions</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <a href="{{ route('patients.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-blue-100 border-2 border-gray-100 hover:border-blue-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-blue-100 group-hover:bg-blue-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-user-plus text-blue-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">Add Patient</span>
                <span class="block text-center text-xs text-gray-500 mt-1">Register new</span>
            </a>
            
            <a href="{{ route('appointments.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-emerald-50 hover:to-emerald-100 border-2 border-gray-100 hover:border-emerald-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-emerald-100 group-hover:bg-emerald-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-calendar-plus text-emerald-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">Schedule</span>
                <span class="block text-center text-xs text-gray-500 mt-1">New appointment</span>
            </a>
            
            <a href="{{ route('medical-records.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-purple-50 hover:to-purple-100 border-2 border-gray-100 hover:border-purple-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-purple-100 group-hover:bg-purple-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-file-medical-alt text-purple-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">Medical Record</span>
                <span class="block text-center text-xs text-gray-500 mt-1">Create new</span>
            </a>
            
            <a href="{{ route('prescriptions.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-orange-50 hover:to-orange-100 border-2 border-gray-100 hover:border-orange-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-orange-100 group-hover:bg-orange-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-prescription text-orange-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">Prescription</span>
                <span class="block text-center text-xs text-gray-500 mt-1">Write new</span>
            </a>
            
            <a href="{{ route('patients.index') }}" class="group bg-white hover:bg-gradient-to-br hover:from-gray-50 hover:to-gray-100 border-2 border-gray-100 hover:border-gray-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-gray-100 group-hover:bg-gray-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-search text-gray-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">Search</span>
                <span class="block text-center text-xs text-gray-500 mt-1">Find patients</span>
            </a>
        </div>
    </div>
</div>
@endsection
