@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cog text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                    <p class="text-gray-600">Configure working hours and system preferences</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Working Hours Settings -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-clock text-blue-600 mr-2"></i>
                Working Hours
            </h3>
            <p class="text-gray-600 text-sm">Set the working hours for your practice. Appointments can only be booked during these times.</p>
        </div>

        <form action="{{ route('settings.working-hours') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $days = [
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday', 
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday'
                    ];
                @endphp
                
                @foreach($days as $day => $dayName)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-900">{{ $dayName }}</h4>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="{{ $day }}_working" 
                                   value="1"
                                   class="sr-only peer"
                                   {{ $workingHours[$day]['is_working'] ? 'checked' : '' }}
                                   onchange="toggleDayHours('{{ $day }}')">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div id="{{ $day }}_hours" class="space-y-3 {{ $workingHours[$day]['is_working'] ? '' : 'opacity-50 pointer-events-none' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                            <input type="time" 
                                   name="{{ $day }}_start" 
                                   value="{{ $workingHours[$day]['start_time'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   {{ $workingHours[$day]['is_working'] ? 'required' : '' }}>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <input type="time" 
                                   name="{{ $day }}_end" 
                                   value="{{ $workingHours[$day]['end_time'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   {{ $workingHours[$day]['is_working'] ? 'required' : '' }}>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Current Status -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 rounded-full {{ \App\Models\Setting::isWithinWorkingHours() ? 'bg-green-500' : 'bg-red-500' }}"></div>
                        <span class="font-medium text-gray-900">
                            Current Status: 
                            <span class="{{ \App\Models\Setting::isWithinWorkingHours() ? 'text-green-700' : 'text-red-700' }}">
                                {{ \App\Models\Setting::isWithinWorkingHours() ? 'Open' : 'Closed' }}
                            </span>
                        </span>
                    </div>
                    @if(!\App\Models\Setting::isWithinWorkingHours())
                        @php $nextWorking = \App\Models\Setting::getNextWorkingTime(); @endphp
                        @if($nextWorking)
                        <span class="text-sm text-gray-600">
                            Next opening: {{ $nextWorking->format('M d, Y H:i') }}
                        </span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="flex items-center space-x-4 pt-4 border-t border-gray-200">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                    <i class="fas fa-save"></i>
                    <span>Save Working Hours</span>
                </button>
                
                <button type="button" onclick="setDefaultHours()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Reset to Default</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Today's Schedule -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-calendar-day text-green-600 mr-2"></i>
                Today's Schedule
            </h4>
            @php 
                $today = strtolower(\Illuminate\Support\Carbon::now()->format('l'));
                $todayHours = $workingHours[$today];
            @endphp
            @if($todayHours['is_working'])
                <p class="text-sm text-gray-600">
                    <span class="font-medium">{{ ucfirst($today) }}</span><br>
                    {{ $todayHours['start_time'] }} - {{ $todayHours['end_time'] }}
                </p>
                <div class="mt-3 text-xs">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">Open Today</span>
                </div>
            @else
                <p class="text-sm text-gray-600">Closed today</p>
                <div class="mt-3 text-xs">
                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full">Closed Today</span>
                </div>
            @endif
        </div>

        <!-- Weekly Summary -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-calendar-week text-blue-600 mr-2"></i>
                Weekly Summary
            </h4>
            @php
                $workingDaysCount = collect($workingHours)->filter(function($day) { return $day['is_working']; })->count();
            @endphp
            <p class="text-sm text-gray-600">
                <span class="font-medium">{{ $workingDaysCount }} working days</span><br>
                {{ 7 - $workingDaysCount }} days closed
            </p>
        </div>

        <!-- Emergency Override -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-exclamation-triangle text-orange-600 mr-2"></i>
                Emergency Mode
            </h4>
            <p class="text-sm text-gray-600 mb-3">Temporarily allow appointments outside working hours</p>
            <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Enable Emergency Mode
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleDayHours(day) {
    const checkbox = document.querySelector(`input[name="${day}_working"]`);
    const hoursDiv = document.getElementById(`${day}_hours`);
    const startInput = document.querySelector(`input[name="${day}_start"]`);
    const endInput = document.querySelector(`input[name="${day}_end"]`);
    
    if (checkbox.checked) {
        hoursDiv.classList.remove('opacity-50', 'pointer-events-none');
        startInput.setAttribute('required', 'required');
        endInput.setAttribute('required', 'required');
    } else {
        hoursDiv.classList.add('opacity-50', 'pointer-events-none');
        startInput.removeAttribute('required');
        endInput.removeAttribute('required');
    }
}

function setDefaultHours() {
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    const weekendDays = ['saturday', 'sunday'];
    
    // Enable weekdays with default hours
    days.forEach(day => {
        document.querySelector(`input[name="${day}_working"]`).checked = true;
        document.querySelector(`input[name="${day}_start"]`).value = '09:00';
        document.querySelector(`input[name="${day}_end"]`).value = '17:00';
        toggleDayHours(day);
    });
    
    // Disable weekends
    weekendDays.forEach(day => {
        document.querySelector(`input[name="${day}_working"]`).checked = false;
        toggleDayHours(day);
    });
}

// Initialize toggle states on page load
document.addEventListener('DOMContentLoaded', function() {
    ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
        toggleDayHours(day);
    });
});
</script>
@endpush
@endsection
