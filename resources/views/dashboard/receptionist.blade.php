@extends('layouts.app')

@section('title', __('dashboard.reception_dashboard'))

@section('content')
<div class="space-y-8">
    <!-- Modern Quick Actions - Moved to Top -->
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

    <!-- Current Patients Section -->
    @if(isset($currentByDoctor) && $currentByDoctor->count())
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-user-clock text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ __('dashboard.active_patients_today') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">Currently in consultation</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium text-blue-600">{{ $currentByDoctor->count() }} Active</span>
            </div>
        </div>

        @foreach($currentByDoctor as $docId => $curr)
        <div class="bg-white border border-gray-200 hover:border-blue-300 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 card-hover mb-6">
            <!-- Status Bar -->
            <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
            
            <!-- Main Content -->
            <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Doctor & Patient Info -->
                    <div class="lg:col-span-1">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="relative">
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-user-md text-blue-600 text-2xl"></i>
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-circle text-white text-sm animate-pulse"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-1">
                                    Dr. {{ $curr->doctor->name }}
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 leading-tight">
                                    {{ $curr->patient->full_name }}
                                </h3>
                                <div class="flex items-center space-x-2 mt-3">
                                    <i class="fas fa-clock text-gray-400"></i>
                                    <span class="text-sm text-gray-600 font-medium">
                                        Started at {{ $curr->appointment_time->format('g:i A') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Middle Column: Next Patient Info -->
                    <div class="lg:col-span-1">
                        @if(isset($nextByDoctor[$docId]) && $nextByDoctor[$docId])
                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-blue-200 rounded-xl p-6 h-full flex flex-col justify-center">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-clock text-blue-600"></i>
                                </div>
                                <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">
                                    {{ __('dashboard.next_up') }}
                                </div>
                                <div class="text-lg font-semibold text-blue-900 mb-1">
                                    {{ $nextByDoctor[$docId]->patient->full_name }}
                                </div>
                                <div class="text-sm text-blue-600 mb-4">
                                    Scheduled for {{ $nextByDoctor[$docId]->appointment_time->format('g:i A') }}
                                </div>
                                <button onclick="setAsCurrent({{ $nextByDoctor[$docId]->id }})" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 mx-auto shadow-sm hover:shadow-md" 
                                        title="Set as Current">
                                    <i class="fas fa-play"></i>
                                    <span>Start Session</span>
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 h-full flex flex-col justify-center">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-clock text-gray-400"></i>
                                </div>
                                <div class="text-sm text-gray-500">No next patient scheduled</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right Column: Action Buttons -->
                    <div class="lg:col-span-1">
                        <div class="space-y-4">
                            <!-- Primary Action: Mark Complete -->
                            <form action="{{ route('appointments.mark-current-done') }}" method="POST">
                                @csrf
                                <input type="hidden" name="doctor_id" value="{{ $curr->doctor_id }}">
                                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 flex items-center justify-center space-x-3 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                    <i class="fas fa-check-circle text-lg"></i>
                                    <span class="text-lg">{{ __('dashboard.mark_complete') }}</span>
                                </button>
                            </form>
                            
                            <!-- Secondary Actions -->
                            <div class="grid grid-cols-1 gap-3">
                                <!-- View Details -->
                                <a href="{{ route('appointments.show', $curr) }}" 
                                   class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2">
                                    <i class="fas fa-eye"></i>
                                    <span>View Patient Details</span>
                                </a>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <!-- Reschedule -->
                                    <a href="{{ route('appointments.reschedule', $curr) }}" 
                                            class="bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2" 
                                            title="Reschedule">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Reschedule</span>
                                    </a>
                                    
                                    <!-- Cancel -->
                                    <button onclick="cancelAppointment({{ $curr->id }})" 
                                            class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2" 
                                            title="Cancel">
                                        <i class="fas fa-times"></i>
                                        <span>Cancel</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

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
                    <div class="flex items-center space-x-3">
                        <button onclick="openReceptionAppointmentFilters()" class="filter-btn">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('appointments.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>{{ __('dashboard.book_appointment_btn') }}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="p-6">
                                @if($todayAppointments->count() > 0)
                    <div class="space-y-2">
                        @foreach($todayAppointments as $index => $appointment)
                        <div class="dashboard-card zebra-stripe rounded-2xl p-4 hover:shadow-lg transition-all duration-200 card-hover border border-gray-200 {{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-clock text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $appointment->patient->full_name }}</h3>
                                        <p class="text-sm text-gray-500 flex items-center space-x-3">
                                            <span class="flex items-center space-x-1">
                                                <i class="fas fa-clock w-3"></i>
                                                <span>{{ $appointment->appointment_time->format('H:i') }}</span>
                                            </span>
                                            <span class="flex items-center space-x-1">
                                                <i class="fas fa-stethoscope w-3"></i>
                                                <span>{{ $appointment->doctor->full_name }}</span>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs px-2 py-1 rounded-full {{ $appointment->status === 'confirmed' ? 'bg-green-100 text-green-600' : ($appointment->status === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600') }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                    <div class="flex items-center space-x-1">
                                        <a href="{{ route('appointments.show', $appointment) }}" class="w-8 h-8 bg-blue-100 hover:bg-blue-200 rounded-lg flex items-center justify-center transition-colors" title="View Details">
                                            <i class="fas fa-eye text-blue-600 text-sm"></i>
                                        </a>
                                        @if($appointment->status === 'scheduled')
                                            <button onclick="setAsCurrent({{ $appointment->id }})" class="w-8 h-8 bg-green-100 hover:bg-green-200 rounded-lg flex items-center justify-center transition-colors" title="Set as Current Patient">
                                                <i class="fas fa-play text-green-600 text-sm"></i>
                                            </button>
                                            <a href="{{ route('appointments.reschedule', $appointment) }}" class="w-8 h-8 bg-purple-100 hover:bg-purple-200 rounded-lg flex items-center justify-center transition-colors" title="Reschedule Appointment">
                                                <i class="fas fa-clock text-purple-600 text-sm"></i>
                                            </a>
                                            <button onclick="cancelAppointment({{ $appointment->id }})" class="w-8 h-8 bg-red-100 hover:bg-red-200 rounded-lg flex items-center justify-center transition-colors" title="Cancel Appointment">
                                                <i class="fas fa-times text-red-600 text-sm"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Enhanced Pagination Section -->
                    @if($todayAppointments->hasPages())
                    <div class="border-t border-gray-100 pt-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="pagination-info flex items-center space-x-2 text-sm text-gray-600">
                                <i class="fas fa-info-circle text-blue-500"></i>
                                <span>Showing {{ $todayAppointments->firstItem() ?? 0 }} to {{ $todayAppointments->lastItem() ?? 0 }} of {{ $todayAppointments->total() }} appointments</span>
                            </div>
                            <div class="pagination-page-indicator flex items-center space-x-2 text-xs">
                                <i class="fas fa-calendar-check"></i>
                                <span>Page {{ $todayAppointments->currentPage() }} of {{ $todayAppointments->lastPage() }}</span>
                            </div>
                        </div>
                        <div class="pagination-wrapper">
                            {{ $todayAppointments->appends(request()->except(['doc_recent_page']))->links('pagination.smart') }}
                        </div>
                    </div>
                    @endif
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
                    <div class="flex items-center space-x-3">
                        <button onclick="openReceptionPatientFilters()" class="filter-btn">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('patients.create') }}" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>{{ __('dashboard.add_patient') }}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @if($recentPatients->count() > 0)
                    <div class="space-y-2">
                        @foreach($recentPatients as $index => $patient)
                        <div class="dashboard-card zebra-stripe rounded-2xl p-4 hover:shadow-lg transition-all duration-200 card-hover border border-gray-200 {{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-user text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $patient->full_name }}</h3>
                                        <p class="text-sm text-gray-500 flex items-center space-x-3">
                                            <span>{{ $patient->id_card_number }}</span>
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
                    
                    <!-- Enhanced Pagination Section -->
                    @if($recentPatients->hasPages())
                    <div class="border-t border-gray-100 pt-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="pagination-info flex items-center space-x-2 text-sm text-gray-600">
                                <i class="fas fa-info-circle text-emerald-500"></i>
                                <span>Showing {{ $recentPatients->firstItem() ?? 0 }} to {{ $recentPatients->lastItem() ?? 0 }} of {{ $recentPatients->total() }} patients</span>
                            </div>
                            <div class="pagination-page-indicator flex items-center space-x-2 text-xs">
                                <i class="fas fa-users"></i>
                                <span>Page {{ $recentPatients->currentPage() }} of {{ $recentPatients->lastPage() }}</span>
                            </div>
                        </div>
                        <div class="pagination-wrapper">
                            {{ $recentPatients->appends(request()->except(['rec_today_page']))->links('pagination.smart') }}
                        </div>
                    </div>
                    @endif
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
                            <tr class="hover:bg-blue-50 transition-colors {{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $appointment->patient->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $appointment->patient->id_card_number }}</div>
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

</div>

<!-- Filter Modals -->
<div id="receptionAppointmentFiltersModal" class="fixed inset-0 modal-overlay z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Filter Appointments</h3>
                    <button onclick="closeReceptionAppointmentFilters()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form method="GET" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Patient</label>
                        <input type="text" name="patient_search" class="modal-input focus:ring-blue-500" placeholder="Search by name, phone, CIN, or patient ID..." value="{{ request('patient_search') }}">
                        <p class="text-xs text-gray-500 mt-1">Search across: name, phone number, CIN, patient ID</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Doctor</label>
                        <select name="doctor_filter" class="modal-input focus:ring-blue-500">
                            <option value="">All Doctors</option>
                            @foreach(\App\Models\User::where('role', 'doctor')->get() as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_filter') == $doctor->id ? 'selected' : '' }}>Dr. {{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status_filter" class="modal-input focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="scheduled" {{ request('status_filter') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="in_progress" {{ request('status_filter') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status_filter') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status_filter') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="flex-1 btn-primary">
                            <i class="fas fa-search mr-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('dashboard') }}" class="flex-1 btn-secondary text-center">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="receptionPatientFiltersModal" class="fixed inset-0 modal-overlay z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Filter Patients</h3>
                    <button onclick="closeReceptionPatientFilters()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form method="GET" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Patient</label>
                        <input type="text" name="patient_name_search" class="modal-input focus:ring-emerald-500" placeholder="Search by name, phone, CIN, or patient ID..." value="{{ request('patient_name_search') }}">
                        <p class="text-xs text-gray-500 mt-1">Search across: name, phone number, CIN, patient ID</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Age Range</label>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="number" name="age_from" class="modal-input focus:ring-emerald-500" placeholder="From" value="{{ request('age_from') }}">
                            <input type="number" name="age_to" class="modal-input focus:ring-emerald-500" placeholder="To" value="{{ request('age_to') }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Registration Date</label>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="date" name="reg_date_from" class="modal-input focus:ring-emerald-500" value="{{ request('reg_date_from') }}">
                            <input type="date" name="reg_date_to" class="modal-input focus:ring-emerald-500" value="{{ request('reg_date_to') }}">
                        </div>
                    </div>
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-search mr-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('dashboard') }}" class="flex-1 btn-secondary text-center">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openReceptionAppointmentFilters() {
    document.getElementById('receptionAppointmentFiltersModal').classList.remove('hidden');
}

function closeReceptionAppointmentFilters() {
    document.getElementById('receptionAppointmentFiltersModal').classList.add('hidden');
}

function openReceptionPatientFilters() {
    document.getElementById('receptionPatientFiltersModal').classList.remove('hidden');
}

function closeReceptionPatientFilters() {
    document.getElementById('receptionPatientFiltersModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('receptionAppointmentFiltersModal').addEventListener('click', function(e) {
    if (e.target === this) closeReceptionAppointmentFilters();
});

document.getElementById('receptionPatientFiltersModal').addEventListener('click', function(e) {
    if (e.target === this) closeReceptionPatientFilters();
});

// Quick action functions
function setAsCurrent(appointmentId) {
    if (confirm('Set this appointment as the current patient?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/appointments/${appointmentId}/set-current`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelAppointment(appointmentId) {
    if (confirm('Are you sure you want to cancel this appointment? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/appointments/${appointmentId}/cancel`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function rescheduleAppointment(appointmentId) {
    // Simple redirect to reschedule (edit) page
    window.location.href = `/appointments/${appointmentId}/reschedule`;
}
</script>
@endsection
