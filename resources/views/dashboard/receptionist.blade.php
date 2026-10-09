@extends('layouts.app')

@section('title', __('dashboard.reception_dashboard'))

@section('content')
<div class="space-y-8">
    <x-ui.page-header
        :title="__('dashboard.reception_dashboard')"
        description="Coordinate patient arrivals, appointments, and the clinic queue from one workspace."
        icon="fa-headset"
        eyebrow="Front desk"
    >
        <x-slot:actions>
            <a href="{{ route('appointments.queue') }}" class="btn-secondary px-5 py-3">
                <i class="fas fa-list-ol"></i>
                <span>Open queue</span>
            </a>
            <a href="{{ route('patients.create') }}" class="btn-primary px-5 py-3">
                <i class="fas fa-user-plus"></i>
                <span>{{ __('dashboard.register_patient') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <!-- Modern Quick Actions - Moved to Top -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center space-x-3 mb-6">
            <div class="gradient-bg w-10 h-10 rounded-xl flex items-center justify-center">
                <i class="fas fa-bolt text-white"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('dashboard.quick_actions') }}</h2>
        </div>
        <div class="dashboard-quick-actions grid grid-cols-1 md:grid-cols-4 gap-6">
            <a href="{{ route('patients.create') }}" class="quick-action-card group p-6 card-hover">
                <div class="icon-tile w-12 h-12 rounded-xl flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-user-plus text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.register_patient') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.add_new_patient') }}</span>
            </a>
            
            <a href="{{ route('appointments.create') }}" class="quick-action-card group p-6 card-hover">
                <div class="icon-tile w-12 h-12 rounded-xl flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-calendar-plus text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.book_appointment_action') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.schedule_visit') }}</span>
            </a>
            
            <a href="{{ route('patients.index') }}" class="quick-action-card group p-6 card-hover">
                <div class="icon-tile w-12 h-12 rounded-xl flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-search text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.search_patients') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.find_patient_records') }}</span>
            </a>
            
            <a href="{{ route('appointments.index') }}" class="quick-action-card group p-6 card-hover">
                <div class="icon-tile w-12 h-12 rounded-xl flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-calendar-alt text-xl"></i>
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
                <div class="gradient-bg w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-user-clock text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ __('dashboard.active_patients_today') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('dashboard.currently_in_consultation') }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-emerald-600 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium text-emerald-700">{{ $currentByDoctor->count() }} Active</span>
            </div>
        </div>

        @foreach($currentByDoctor as $docId => $curr)
        <div class="bg-white border border-gray-200 hover:border-blue-300 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 card-hover mb-6">
            <!-- Status Bar -->
            <div class="h-1 gradient-bg"></div>
            
            <!-- Main Content -->
            <div class="p-8">
                <!-- Current Patient Info Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Left: Current Patient Info -->
                    <div class="flex items-center space-x-6">
                        <div class="relative">
                            <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-user-md text-blue-600 text-3xl"></i>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center shadow-md">
                                <i class="fas fa-circle text-white text-sm animate-pulse"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">
                                <i class="fas fa-stethoscope mr-1"></i>
                                {{ $curr->doctor->professional_name }}
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 leading-tight mb-3">
                                {{ $curr->patient->full_name }}
                                @if($curr->patient->is_pediatric)<small class="block">{{ $curr->patient->age_display }} · {{ $curr->patient->contact_label }}</small>@endif
                            </h3>
                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-clock text-blue-500"></i>
                                    <span class="font-medium">Started: {{ $curr->displayTime() }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-id-card text-blue-500"></i>
                                    <span>{{ $curr->patient->id_card_number }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Current Patient Actions -->
                    <div class="flex items-center justify-center">
                        <div class="grid grid-cols-1 gap-4 w-full max-w-sm">
                            <!-- Primary Action: Mark Complete -->
                            <form action="{{ route('appointments.mark-current-done') }}" method="POST" onsubmit="handleMarkComplete(this)">
                                @csrf
                                <input type="hidden" name="doctor_id" value="{{ $curr->doctor_id }}">
                                <input type="hidden" name="appointment_id" value="{{ $curr->id }}">
                                <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 flex items-center justify-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                    <i class="fas fa-check-circle text-xl"></i>
                                    <span class="text-lg">{{ __('dashboard.mark_complete') }}</span>
                                </button>
                            </form>
                            
                            <!-- Secondary Actions -->
                            <div class="grid grid-cols-3 gap-2">
                                <!-- View Details -->
                                <a href="{{ route('appointments.show', $curr) }}" 
                                   class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-3 px-3 rounded-lg transition-all duration-200 flex items-center justify-center space-x-1">
                                    <i class="fas fa-eye"></i>
                                    <span class="text-sm">View</span>
                                </a>
                                
                                <!-- Reschedule -->
                                <a href="{{ route('appointments.reschedule', $curr) }}" 
                                   class="bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium py-3 px-3 rounded-lg transition-all duration-200 flex items-center justify-center space-x-1">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span class="text-sm">Reschedule</span>
                                </a>
                                
                                <!-- Cancel -->
                                <button onclick="cancelAppointment({{ $curr->id }})" 
                                        class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-3 px-3 rounded-lg transition-all duration-200 flex items-center justify-center space-x-1" 
                                        title="Cancel">
                                    <i class="fas fa-times"></i>
                                    <span class="text-sm">Cancel</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Patient Section (Separate) -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-arrow-right text-gray-400 mr-2"></i>
                            Next Patient
                        </h4>
                    </div>
                    
                    @if(isset($nextByDoctor[$docId]) && $nextByDoctor[$docId])
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-amber-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-user text-amber-600 text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-lg font-semibold text-amber-900 mb-1">
                                        {{ $nextByDoctor[$docId]->patient->full_name }}
                                        @if($nextByDoctor[$docId]->patient->is_pediatric)<small class="block">{{ $nextByDoctor[$docId]->patient->age_display }} · {{ $nextByDoctor[$docId]->patient->contact_label }}</small>@endif
                                    </div>
                                    <div class="text-sm text-amber-700 flex items-center space-x-3">
                                        <span><i class="fas fa-clock mr-1"></i>{{ $nextByDoctor[$docId]->displayTime() }}</span>
                                        <span><i class="fas fa-id-card mr-1"></i>{{ $nextByDoctor[$docId]->patient->id_card_number }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <button onclick="setAsCurrent({{ $nextByDoctor[$docId]->id }})" 
                                        class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1" 
                                        title="Start Session with Next Patient">
                                    <i class="fas fa-play text-lg"></i>
                                    <span>{{ __('dashboard.start_session') }}</span>
                                </button>
                                <button onclick="cancelAppointment({{ $nextByDoctor[$docId]->id }})" 
                                        class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-3 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2" 
                                        title="Cancel Next Patient">
                                    <i class="fas fa-times"></i>
                                    <span class="hidden sm:inline">Cancel</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                        <div class="text-center text-gray-500">
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-clock text-gray-400 text-xl"></i>
                            </div>
                            <p>{{ __('dashboard.no_next_patient_scheduled') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Today's Appointments -->
        <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="gradient-bg w-10 h-10 rounded-xl flex flex-none items-center justify-center">
                            <i class="fas fa-calendar-day text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ __('appointments.today_appointments') }}</h2>
                            <p class="text-sm text-gray-500">{{ $stats['today_appointments'] }} {{ __('dashboard.scheduled_today') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <button onclick="openReceptionAppointmentFilters()" class="filter-btn">
                            <i class="fas fa-filter"></i>
                            <span>{{ __('dashboard.filter') }}</span>
                        </button>
                        <a href="{{ route('appointments.create') }}" class="btn-primary whitespace-nowrap">
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
                            <div class="appointment-summary-row flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-clock text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $appointment->patient->full_name }}</h3>
                                        <p class="text-sm text-gray-500 flex items-center space-x-3">
                                            <span class="flex items-center space-x-1">
                                                <i class="fas fa-clock w-3"></i>
                                                <span>{{ $appointment->displayTime() }}</span>
                                            </span>
                                            <span class="flex items-center space-x-1">
                                                <i class="fas fa-stethoscope w-3"></i>
                                                <span>{{ $appointment->doctor->full_name }}</span>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="appointment-summary-actions flex items-center space-x-2">
                                    @if($appointment->isCheckedIn() && $appointment->status === 'scheduled')
                                        <span class="text-xs px-2 py-1 rounded-full bg-teal-100 text-teal-700" title="{{ __('appointments.arrived_at', ['time' => $appointment->checked_in_at->format('g:i A')]) }}">
                                            <i class="fas fa-user-check mr-1"></i>{{ __('appointments.checked_in') }}
                                        </span>
                                    @endif
                                    <span class="text-xs px-2 py-1 rounded-full {{ \App\Models\Appointment::statusBadgeClasses($appointment->status) }}">
                                        {{ \App\Models\Appointment::statusLabel($appointment->status) }}
                                    </span>
                                    <div class="appointment-action-buttons flex items-center space-x-1">
                                        <a href="{{ route('appointments.show', $appointment) }}" class="w-8 h-8 bg-blue-100 hover:bg-blue-200 rounded-lg flex items-center justify-center transition-colors" title="View Details">
                                            <i class="fas fa-eye text-blue-600 text-sm"></i>
                                        </a>
                                        @if($appointment->status === 'scheduled')
                                            @if(!$appointment->isCheckedIn())
                                                <button onclick="submitAction('{{ route('appointments.check-in', $appointment) }}')" class="w-8 h-8 bg-teal-100 hover:bg-teal-200 rounded-lg flex items-center justify-center transition-colors" title="{{ __('appointments.check_in') }}">
                                                    <i class="fas fa-user-check text-teal-600 text-sm"></i>
                                                </button>
                                            @endif
                                            <button onclick="setAsCurrent({{ $appointment->id }})" class="w-8 h-8 bg-green-100 hover:bg-green-200 rounded-lg flex items-center justify-center transition-colors" title="Set as Current Patient">
                                                <i class="fas fa-play text-green-600 text-sm"></i>
                                            </button>
                                            <a href="{{ route('appointments.reschedule', $appointment) }}" class="btn-ghost w-8 h-8 p-0" title="Reschedule Appointment">
                                                <i class="fas fa-clock text-sm"></i>
                                            </a>
                                            <button onclick="submitAction('{{ route('appointments.no-show', $appointment) }}', '{{ __('appointments.mark_no_show') }}?')" class="w-8 h-8 bg-orange-100 hover:bg-orange-200 rounded-lg flex items-center justify-center transition-colors" title="{{ __('appointments.mark_no_show') }}">
                                                <i class="fas fa-user-slash text-orange-600 text-sm"></i>
                                            </button>
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
                            <span>{{ __('dashboard.filter') }}</span>
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
                                            <span>{{ __('dashboard.age') }}: {{ $patient->age_display }}</span>
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
                <div class="gradient-bg w-10 h-10 rounded-xl flex items-center justify-center">
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
                                    {{ $appointment->doctor->professional_name }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $appointment->appointment_date->format('M j, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $appointment->displayTime() }}</div>
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
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.filter_appointments') }}</h3>
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
                            <option value="{{ $doctor->id }}" {{ request('doctor_filter') == $doctor->id ? 'selected' : '' }}>{{ $doctor->professional_name }}</option>
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

// Generic POST action with optional confirmation
function submitAction(url, confirmMessage = null) {
    if (confirmMessage && !confirm(confirmMessage)) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';

    form.appendChild(csrfToken);
    document.body.appendChild(form);
    form.submit();
}

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

// Handle mark complete form submission
function handleMarkComplete(form) {
    const button = form.querySelector('button[type="submit"]');
    button.innerHTML = '<i class="fas fa-spinner animate-spin text-xl mr-3"></i><span class="text-lg">Completing...</span>';
    button.disabled = true;
    
    return true; // Allow form to submit normally
}

// Enhanced action functions with immediate sync trigger
function setAsCurrent(appointmentId) {
    if (confirm('Set this appointment as the current patient?')) {
        // Show loading state
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Starting...';
        button.disabled = true;
        
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
        // Show loading state
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Cancelling...';
        button.disabled = true;
        
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


</script>
@endsection
