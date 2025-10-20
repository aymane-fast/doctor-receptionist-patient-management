@extends('layouts.app')

@section('title', __('appointments.title'))

@section('content')
<div class="space-y-8">
    <!-- Modern Header -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center animate-float">
                    <i class="fas fa-calendar text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('appointments.title') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">{{ __('appointments.manage_appointments_scheduling') }}</p>
                </div>
            </div>
            <a href="{{ route('appointments.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2 deep-shadow">
                <i class="fas fa-plus"></i>
                <span>{{ __('appointments.schedule_appointment') }}</span>
            </a>
        </div>
    </div>

    <!-- Modern Filters -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <form method="GET" action="{{ route('appointments.index') }}" class="space-y-6">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-filter text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ __('appointments.filter_appointments') }}</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="space-y-2">
                    <label for="search" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('appointments.search') }}</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="{{ __('appointments.search_placeholder') }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200">
                </div>
                <div class="space-y-2">
                    <label for="date" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('appointments.date') }}</label>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           value="{{ request('date') }}"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200">
                </div>

                <div class="space-y-2">
                    <label for="status" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('appointments.status') }}</label>
                    <select id="status" 
                            name="status" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200">
                        <option value="">{{ __('appointments.all_statuses') }}</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>{{ __('appointments.scheduled') }}</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('appointments.in_progress') }}</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('appointments.completed') }}</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('appointments.cancelled') }}</option>
                    </select>
                </div>

                @if(auth()->user()->isReceptionist())
                <div class="space-y-2">
                    <label for="doctor_id" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('appointments.doctor') }}</label>
                    <select id="doctor_id" 
                            name="doctor_id" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200">
                        <option value="">{{ __('appointments.all_doctors') }}</option>
                        @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                            Dr. {{ $doctor->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex flex-col justify-end space-y-3">
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-filter"></i>
                        <span>{{ __('appointments.filter') }}</span>
                    </button>
                    @if(request()->query())
                    <a href="{{ route('appointments.index') }}" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>{{ __('appointments.clear') }}</span>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Modern Appointments List -->
    <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
        @if($appointments->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.patient') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.doctor') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.date_time') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.status') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.reason') }}</th>
                            <th class="px-8 py-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('appointments.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($appointments as $appointment)
                        <tr class="hover:bg-gray-50/50 transition-all duration-200">
                            <td class="px-8 py-6">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center mr-4">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-base font-semibold text-gray-900">{{ $appointment->patient->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $appointment->patient->id_card_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-user-md text-emerald-600 text-sm"></i>
                                    </div>
                                    <span class="text-base font-medium text-gray-900">Dr. {{ $appointment->doctor->name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="space-y-1">
                                    <div class="text-base font-semibold text-gray-900">{{ $appointment->appointment_date->format('M j, Y') }}</div>
                                    <div class="text-sm text-gray-500 flex items-center space-x-1">
                                        <i class="fas fa-clock text-xs"></i>
                                        <span>{{ $appointment->appointment_time->format('g:i A') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center space-x-3">
                                    <span class="px-3 py-2 text-sm font-bold rounded-xl
                                        @if($appointment->status == 'scheduled') bg-yellow-100 text-yellow-800
                                        @elseif($appointment->status == 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($appointment->status == 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ __('appointments.' . $appointment->status) }}
                                    </span>
                                    
                                    <!-- Quick Status Update -->
                                    <div class="relative">
                                        <button onclick="toggleStatusDropdown({{ $appointment->id }})" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-all duration-200">
                                            <i class="fas fa-edit text-gray-500 text-xs"></i>
                                        </button>
                                        <div id="status-dropdown-{{ $appointment->id }}" class="absolute right-0 top-10 bg-white border border-gray-200 rounded-2xl shadow-xl py-2 z-20 hidden min-w-40">
                                            @if($appointment->status != 'scheduled')
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="scheduled">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left rounded-xl mx-1 transition-colors">
                                                    <i class="fas fa-clock text-yellow-600 mr-2"></i>{{ __('appointments.scheduled') }}
                                                </button>
                                            </form>
                                            @endif
                                            
                                            @if($appointment->status != 'in_progress')
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left rounded-xl mx-1 transition-colors">
                                                    <i class="fas fa-play text-blue-600 mr-2"></i>{{ __('appointments.in_progress') }}
                                                </button>
                                            </form>
                                            @endif
                                            
                                            @if($appointment->status != 'completed')
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left rounded-xl mx-1 transition-colors">
                                                    <i class="fas fa-check text-green-600 mr-2"></i>{{ __('appointments.completed') }}
                                                </button>
                                            </form>
                                            @endif
                                            
                                            @if($appointment->status != 'cancelled')
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left rounded-xl mx-1 transition-colors" 
                                                        onclick="return confirm('{{ __('appointments.confirm_cancel') }}')">
                                                    <i class="fas fa-times text-red-600 mr-2"></i>{{ __('appointments.cancelled') }}
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-sm text-gray-900">
                                <div class="max-w-xs">
                                    {{ $appointment->reason ? Str::limit(translate_appointment_reason($appointment->reason), 50) : '-' }}
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('appointments.show', $appointment) }}" class="w-10 h-10 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-xl flex items-center justify-center transition-all duration-200 group">
                                        <i class="fas fa-eye text-sm group-hover:scale-110"></i>
                                    </a>
                                    <a href="{{ route('appointments.edit', $appointment) }}" class="w-10 h-10 bg-emerald-100 hover:bg-emerald-200 text-emerald-600 rounded-xl flex items-center justify-center transition-all duration-200 group">
                                        <i class="fas fa-edit text-sm group-hover:scale-110"></i>
                                    </a>
                                    @if(auth()->user()->isDoctor() && $appointment->status == 'completed' && !$appointment->medicalRecord)
                                    <a href="{{ route('medical-records.create', ['appointment_id' => $appointment->id]) }}" 
                                       class="w-10 h-10 bg-purple-100 hover:bg-purple-200 text-purple-600 rounded-xl flex items-center justify-center transition-all duration-200 group" 
                                       title="Add Medical Record">
                                        <i class="fas fa-file-medical text-sm group-hover:scale-110"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Modern Pagination -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-t border-gray-100">
                {{ $appointments->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-16 px-8">
                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6 animate-float">
                    <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ __('appointments.no_appointments_found') }}</h3>
                <p class="text-gray-500 mb-8 text-lg max-w-md mx-auto">
                    @if(request()->query())
                        {{ __('appointments.no_match_filter_criteria') }}
                    @else
                        {{ __('appointments.get_started_first_appointment') }}
                    @endif
                </p>
                <a href="{{ route('appointments.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-4 rounded-2xl font-medium transition-all duration-200 inline-flex items-center space-x-2 deep-shadow">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('appointments.schedule_appointment') }}</span>
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Toggle status dropdown for specific appointment
function toggleStatusDropdown(appointmentId) {
    const dropdown = document.getElementById('status-dropdown-' + appointmentId);
    const allDropdowns = document.querySelectorAll('[id^="status-dropdown-"]');
    
    // Hide all other dropdowns first
    allDropdowns.forEach(dd => {
        if (dd.id !== 'status-dropdown-' + appointmentId) {
            dd.classList.add('hidden');
        }
    });
    
    // Toggle current dropdown
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    // Check if click is outside any dropdown button or dropdown content
    if (!event.target.closest('[onclick^="toggleStatusDropdown"]') && 
        !event.target.closest('[id^="status-dropdown-"]')) {
        const allDropdowns = document.querySelectorAll('[id^="status-dropdown-"]');
        allDropdowns.forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Prevent dropdown from closing when clicking inside it
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('[id^="status-dropdown-"]');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});
</script>
@endpush
@endsection
