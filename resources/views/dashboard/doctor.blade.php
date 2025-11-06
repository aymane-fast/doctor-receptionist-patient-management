@extends('layouts.app')

@section('title', __('dashboard.doctor_dashboard'))

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
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <a href="{{ route('patients.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-blue-50 hover:to-blue-100 border-2 border-gray-100 hover:border-blue-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-blue-100 group-hover:bg-blue-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-user-plus text-blue-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.add_patient') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.register_new') }}</span>
            </a>
            
            <a href="{{ route('appointments.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-emerald-50 hover:to-emerald-100 border-2 border-gray-100 hover:border-emerald-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-emerald-100 group-hover:bg-emerald-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-calendar-plus text-emerald-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.schedule') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.new_appointment') }}</span>
            </a>
            
            <a href="{{ route('medical-records.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-purple-50 hover:to-purple-100 border-2 border-gray-100 hover:border-purple-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-purple-100 group-hover:bg-purple-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-file-medical-alt text-purple-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.medical_record') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.create_new') }}</span>
            </a>
            
            <a href="{{ route('prescriptions.create') }}" class="group bg-white hover:bg-gradient-to-br hover:from-orange-50 hover:to-orange-100 border-2 border-gray-100 hover:border-orange-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-orange-100 group-hover:bg-orange-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-prescription text-orange-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.prescription') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.write_new') }}</span>
            </a>
            
            <a href="{{ route('patients.index') }}" class="group bg-white hover:bg-gradient-to-br hover:from-gray-50 hover:to-gray-100 border-2 border-gray-100 hover:border-gray-200 rounded-2xl p-6 transition-all duration-300 card-hover">
                <div class="w-12 h-12 bg-gray-100 group-hover:bg-gray-200 rounded-xl flex items-center justify-center mb-4 mx-auto transition-colors">
                    <i class="fas fa-search text-gray-600 text-xl"></i>
                </div>
                <span class="block text-center text-sm font-semibold text-gray-900">{{ __('dashboard.search') }}</span>
                <span class="block text-center text-xs text-gray-500 mt-1">{{ __('dashboard.find_patients') }}</span>
            </a>
        </div>
    </div>

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
                    <div class="text-sm text-blue-100 mb-1">{{ __('dashboard.current_patient') }}</div>
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
                    <span>{{ __('dashboard.open_workspace') }}</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Next Up Patient - Enhanced -->
    @if(isset($nextAppointment) && $nextAppointment)
    <div class="glass-effect rounded-2xl p-6 modern-shadow border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
                <div>
                    <div class="text-sm text-purple-600 mb-1 font-medium">{{ __('dashboard.next_up') }}</div>
                    <div class="text-xl font-bold text-gray-900 mb-1">{{ $nextAppointment->patient->full_name }}</div>
                    <div class="flex items-center space-x-3 text-gray-600">
                        <span><i class="fas fa-clock mr-1"></i>{{ $nextAppointment->appointment_time->format('g:i A') }}</span>
                        <span><i class="fas fa-stethoscope mr-1"></i>{{ Str::limit($nextAppointment->reason ?? 'General consultation', 30) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="setAsCurrent({{ $nextAppointment->id }})" class="bg-purple-100 hover:bg-purple-200 text-purple-700 font-medium px-4 py-2 rounded-xl transition-all duration-200 flex items-center space-x-2" title="Set as Current Patient">
                    <i class="fas fa-play text-sm"></i>
                    <span>Set as Current</span>
                </button>
                <a href="{{ route('appointments.reschedule', $nextAppointment) }}" class="bg-orange-100 hover:bg-orange-200 text-orange-700 font-medium px-3 py-2 rounded-xl transition-all duration-200" title="Reschedule">
                    <i class="fas fa-clock text-sm"></i>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Modern Statistics Cards - Commented out since we have dedicated statistics page
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
                <p class="text-xs text-blue-600">{{ __('dashboard.active_in_system') }}</p>
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
                <h3 class="text-sm font-medium text-amber-700 mb-1">{{ __('dashboard.pending') }}</h3>
                <p class="text-3xl font-bold text-amber-900 mb-1">{{ $stats['pending_appointments'] }}</p>
                <p class="text-xs text-amber-600">{{ __('dashboard.awaiting_attention') }}</p>
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
                <h3 class="text-sm font-medium text-purple-700 mb-1">{{ __('dashboard.completed') }}</h3>
                <p class="text-3xl font-bold text-purple-900 mb-1">{{ $stats['completed_today'] }}</p>
                <p class="text-xs text-purple-600">{{ __('dashboard.finished_today') }}</p>
            </div>
        </div>
    </div>
    --}}

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
                        <h2 class="text-xl font-bold text-gray-900">{{ __('dashboard.todays_schedule') }}</h2>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="openAppointmentFilters()" class="filter-btn">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('appointments.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>{{ __('common.add') }}</span>
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
                                        {{ __('appointments.' . $appointment->status) }}
                                    </span>
                                    <div class="flex items-center space-x-2">
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
                            @if($appointment->reason)
                            <p class="text-sm text-gray-600 mt-3 pl-16">{{ Str::limit($appointment->reason, 80) }}</p>
                            @endif
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
                                <i class="fas fa-calendar-day"></i>
                                <span>Page {{ $todayAppointments->currentPage() }} of {{ $todayAppointments->lastPage() }}</span>
                            </div>
                        </div>
                        <div class="pagination-wrapper">
                            {{ $todayAppointments->appends(request()->except(['doc_records_page']))->links('pagination.smart') }}
                        </div>
                    </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">{{ __('dashboard.no_appointments_today') }}</p>
                        <p class="text-gray-400 text-sm">{{ __('dashboard.enjoy_free_schedule') }}</p>
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
                        <h2 class="text-xl font-bold text-gray-900">{{ __('dashboard.medical_records') }}</h2>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="openRecordsFilters()" class="filter-btn">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('medical-records.create') }}" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>{{ __('dashboard.new_record') }}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @if($recentRecords->count() > 0)
                    <div class="space-y-2">
                        @foreach($recentRecords as $index => $record)
                        <div class="dashboard-card zebra-stripe rounded-2xl p-4 hover:shadow-lg transition-all duration-200 card-hover border border-gray-200 {{ $loop->even ? 'bg-gray-100' : 'bg-white' }}">
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
                    
                    <!-- Enhanced Pagination Section -->
                    @if($recentRecords->hasPages())
                    <div class="border-t border-gray-100 pt-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="pagination-info flex items-center space-x-2 text-sm text-gray-600">
                                <i class="fas fa-info-circle text-emerald-500"></i>
                                <span>Showing {{ $recentRecords->firstItem() ?? 0 }} to {{ $recentRecords->lastItem() ?? 0 }} of {{ $recentRecords->total() }} records</span>
                            </div>
                            <div class="pagination-page-indicator flex items-center space-x-2 text-xs">
                                <i class="fas fa-file-medical"></i>
                                <span>Page {{ $recentRecords->currentPage() }} of {{ $recentRecords->lastPage() }}</span>
                            </div>
                        </div>
                        <div class="pagination-wrapper">
                            {{ $recentRecords->appends(request()->except(['doc_today_page']))->links('pagination.smart') }}
                        </div>
                    </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-medical text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">{{ __('dashboard.no_recent_records') }}</p>
                        <p class="text-gray-400 text-sm">{{ __('dashboard.start_creating_records') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Filter Modals -->
<div id="appointmentFiltersModal" class="fixed inset-0 modal-overlay z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Filter Appointments</h3>
                    <button onclick="closeAppointmentFilters()" class="text-gray-400 hover:text-gray-600">
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

<div id="recordsFiltersModal" class="fixed inset-0 modal-overlay z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Filter Medical Records</h3>
                    <button onclick="closeRecordsFilters()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form method="GET" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Patient</label>
                        <input type="text" name="record_patient_search" class="modal-input focus:ring-emerald-500" placeholder="Search by name, phone, CIN, or patient ID..." value="{{ request('record_patient_search') }}">
                        <p class="text-xs text-gray-500 mt-1">Search across: name, phone number, CIN, patient ID</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="date" name="record_date_from" class="modal-input focus:ring-emerald-500" value="{{ request('record_date_from') }}">
                            <input type="date" name="record_date_to" class="modal-input focus:ring-emerald-500" value="{{ request('record_date_to') }}">
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
function openAppointmentFilters() {
    document.getElementById('appointmentFiltersModal').classList.remove('hidden');
}

function closeAppointmentFilters() {
    document.getElementById('appointmentFiltersModal').classList.add('hidden');
}

function openRecordsFilters() {
    document.getElementById('recordsFiltersModal').classList.remove('hidden');
}

function closeRecordsFilters() {
    document.getElementById('recordsFiltersModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('appointmentFiltersModal').addEventListener('click', function(e) {
    if (e.target === this) closeAppointmentFilters();
});

document.getElementById('recordsFiltersModal').addEventListener('click', function(e) {
    if (e.target === this) closeRecordsFilters();
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

// Action functions
function setAsCurrent(appointmentId) {
    if (confirm('Set this appointment as the current patient?')) {
        // Show loading state
        const button = event.target.closest('button');
        if (button) {
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Starting...';
            button.disabled = true;
        }
        
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
        if (button) {
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Cancelling...';
            button.disabled = true;
        }
        
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
