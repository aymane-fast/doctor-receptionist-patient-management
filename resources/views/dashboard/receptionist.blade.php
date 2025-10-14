@extends('layouts.app')

@section('title', __('dashboard.reception_dashboard'))

@section('content')
<div class="space-y-8">
    <!-- Current Patients Section -->
    @if(isset($currentByDoctor) && $currentByDoctor->count())
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center animate-pulse-glow">
                <i class="fas fa-user-clock text-white text-lg"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('dashboard.active_patients_today') }}</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($currentByDoctor as $docId => $curr)
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 rounded-2xl p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-md text-white"></i>
                    </div>
                    <div class="text-blue-600">
                        <i class="fas fa-circle animate-pulse"></i>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="text-sm text-blue-700 mb-1">Dr. {{ $curr->doctor->name }}</div>
                    <div class="text-xl font-bold text-blue-900">{{ $curr->patient->full_name }}</div>
                    <div class="text-blue-600 text-sm flex items-center space-x-2">
                        <i class="fas fa-clock w-3"></i>
                        <span>{{ $curr->appointment_time->format('g:i A') }}</span>
                    </div>
                </div>
                @if(isset($nextByDoctor[$docId]) && $nextByDoctor[$docId])
                <div class="bg-white/60 rounded-xl p-3 mb-4">
                    <div class="text-xs text-blue-700 mb-1">{{ __('dashboard.next_up') }}:</div>
                    <div class="text-sm font-medium text-blue-900">{{ $nextByDoctor[$docId]->patient->full_name }}</div>
                    <div class="text-xs text-blue-600">{{ $nextByDoctor[$docId]->appointment_time->format('g:i A') }}</div>
                </div>
                @endif
                <form action="{{ route('appointments.mark-current-done') }}" method="POST">
                    @csrf
                    <input type="hidden" name="doctor_id" value="{{ $curr->doctor_id }}">
                    <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-medium px-4 py-3 rounded-xl transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-check"></i>
                        <span>{{ __('dashboard.mark_complete') }}</span>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Welcome Header -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-user-tie text-indigo-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ __('dashboard.welcome_back') }}, <span class="text-gradient">{{ auth()->user()->name }}</span>
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">{{ __('dashboard.reception_dashboard') }} - {{ __('dashboard.manage_patients_appointments') }}</p>
                </div>
            </div>
            <div class="text-right bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4">
                <div class="text-sm text-gray-500 mb-1">{{ now()->format('l') }}</div>
                <div class="text-xl font-semibold text-gray-900">{{ now()->format('F j, Y') }}</div>
                <div class="text-lg font-medium text-indigo-600">{{ now()->format('g:i A') }}</div>
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
                <h3 class="text-sm font-medium text-blue-700 mb-1">{{ __('dashboard.total_patients') }}</h3>
                <p class="text-3xl font-bold text-blue-900 mb-1">{{ $stats['total_patients'] }}</p>
                <p class="text-xs text-blue-600">{{ __('dashboard.registered_patients') }}</p>
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
                <h3 class="text-sm font-medium text-emerald-700 mb-1">{{ __('dashboard.todays_schedule') }}</h3>
                <p class="text-3xl font-bold text-emerald-900 mb-1">{{ $stats['today_appointments'] }}</p>
                <p class="text-xs text-emerald-600">{{ __('dashboard.appointments_today') }}</p>
            </div>
        </div>

        <!-- Scheduled Appointments -->
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
                <h3 class="text-sm font-medium text-amber-700 mb-1">{{ __('dashboard.scheduled') }}</h3>
                <p class="text-3xl font-bold text-amber-900 mb-1">{{ $stats['scheduled_appointments'] }}</p>
                <p class="text-xs text-amber-600">{{ __('dashboard.upcoming_appointments') }}</p>
            </div>
        </div>

        <!-- New Patients This Week -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 card-hover modern-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-plus text-white text-lg"></i>
                </div>
                <div class="text-purple-600">
                    <i class="fas fa-arrow-up text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-medium text-purple-700 mb-1">{{ __('dashboard.new_this_week') }}</h3>
                <p class="text-3xl font-bold text-purple-900 mb-1">{{ $stats['new_patients_this_week'] }}</p>
                <p class="text-xs text-purple-600">{{ __('dashboard.new_registrations') }}</p>
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
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ __('dashboard.todays_appointments') }}</h2>
                            <p class="text-sm text-gray-500">{{ $stats['today_appointments'] }} {{ __('dashboard.scheduled_today') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('appointments.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('dashboard.book_appointment_btn') }}</span>
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
                                        {{ __('appointments.' . $appointment->status) }}
                                    </span>
                                    @if($appointment->is_current)
                                        <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-lg font-medium">{{ __('dashboard.current_patient') }}</span>
                                    @else
                                        <form action="{{ route('appointments.set-current', $appointment) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-lg font-medium transition-colors">{{ __('dashboard.set_current') }}</button>
                                        </form>
                                    @endif
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
                        <p class="text-gray-500 font-medium">{{ __('dashboard.no_appointments_today') }}</p>
                        <p class="text-gray-400 text-sm">{{ __('dashboard.schedule_first_appointment') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Patients -->
        <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">{{ __('dashboard.recent_patients') }}</h2>
                    </div>
                    <a href="{{ route('patients.create') }}" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('dashboard.add_patient') }}</span>
                    </a>
                </div>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                @if($recentPatients->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentPatients as $patient)
                        <div class="bg-white border border-gray-100 rounded-2xl p-4 hover:shadow-lg transition-all duration-200 card-hover">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-user text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $patient->full_name }}</h3>
                                        <p class="text-sm text-gray-500 flex items-center space-x-3">
                                            <span>{{ $patient->patient_id }}</span>
                                            <span>•</span>
                                            <span>{{ __('dashboard.age') }}: {{ $patient->age }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-lg">{{ $patient->created_at->format('M j') }}</span>
                                    <a href="{{ route('patients.show', $patient) }}" class="w-8 h-8 bg-emerald-100 hover:bg-emerald-200 rounded-lg flex items-center justify-center transition-colors">
                                        <i class="fas fa-eye text-emerald-600 text-sm"></i>
                                    </a>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-3 pl-16 flex items-center space-x-2">
                                <i class="fas fa-phone w-3 text-gray-400"></i>
                                <span>{{ $patient->phone }}</span>
                            </p>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-6">{{ $recentPatients->links() }}</div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-plus text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">{{ __('dashboard.no_patients_registered') }}</p>
                        <p class="text-gray-400 text-sm">{{ __('dashboard.add_first_patient') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Appointments Table -->
    <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-white"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">{{ __('dashboard.upcoming_appointments_title') }}</h2>
            </div>
        </div>
        <div class="p-6">
            @if($upcomingAppointments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('dashboard.patient') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('dashboard.doctor') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('dashboard.date_time') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('dashboard.status') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('dashboard.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($upcomingAppointments as $appointment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $appointment->patient->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $appointment->patient->patient_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    Dr. {{ $appointment->doctor->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $appointment->appointment_date->format('M j, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $appointment->appointment_time->format('g:i A') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-medium rounded-full
                                        @if($appointment->status == 'scheduled') bg-gradient-to-r from-amber-100 to-amber-50 text-amber-800 border border-amber-200
                                        @elseif($appointment->status == 'in_progress') bg-gradient-to-r from-blue-100 to-blue-50 text-blue-800 border border-blue-200
                                        @elseif($appointment->status == 'completed') bg-gradient-to-r from-emerald-100 to-emerald-50 text-emerald-800 border border-emerald-200
                                        @else bg-gradient-to-r from-red-100 to-red-50 text-red-800 border border-red-200 @endif">
                                        {{ __('appointments.' . $appointment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('appointments.show', $appointment) }}" class="w-8 h-8 bg-blue-100 hover:bg-blue-200 rounded-lg flex items-center justify-center transition-colors">
                                            <i class="fas fa-eye text-blue-600 text-sm"></i>
                                        </a>
                                        <a href="{{ route('appointments.edit', $appointment) }}" class="w-8 h-8 bg-emerald-100 hover:bg-emerald-200 rounded-lg flex items-center justify-center transition-colors">
                                            <i class="fas fa-edit text-emerald-600 text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">{{ __('dashboard.no_upcoming_appointments') }}</p>
                    <p class="text-gray-400 text-sm">{{ __('dashboard.schedule_appointments') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modern Quick Actions -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-bolt text-white"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('dashboard.quick_actions') }}</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <a href="{{ route('patients.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-blue-100 border-2 border-gray-100 hover:border-blue-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-blue-100 group-hover:bg-blue-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-user-plus text-blue-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.register_patient') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.add_new_patient') }}</span>
            </a>
            
            <a href="{{ route('appointments.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-emerald-50 hover:to-emerald-100 border-2 border-gray-100 hover:border-emerald-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-emerald-100 group-hover:bg-emerald-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-calendar-plus text-emerald-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.book_appointment_action') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.schedule_visit') }}</span>
            </a>
            
            <a href="{{ route('patients.index') }}" class="group bg-white hover:bg-gradient-to-br hover:from-purple-50 hover:to-purple-100 border-2 border-gray-100 hover:border-purple-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-purple-100 group-hover:bg-purple-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-search text-purple-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.search_patients') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.find_patient_records') }}</span>
            </a>
            
            <a href="{{ route('appointments.index') }}" class="group bg-white hover:bg-gradient-to-br hover:from-orange-50 hover:to-orange-100 border-2 border-gray-100 hover:border-orange-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-orange-100 group-hover:bg-orange-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-calendar-alt text-orange-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.all_appointments') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.view_schedule') }}</span>
            </a>
        </div>
    </div>
</div>
@endsection
