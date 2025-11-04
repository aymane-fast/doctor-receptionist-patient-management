@extends('layouts.app')

@section('title', __('common.settings'))

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
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('common.settings') }}</h1>
                    <p class="text-gray-600">{{ __('common.configure_preferences') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Language Settings -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-language text-green-600 mr-2"></i>
                {{ __('common.language') }} {{ __('common.settings') }}
            </h3>
            <p class="text-gray-600 text-sm">Choose your preferred language for the application interface.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach(\App\Models\Setting::getAvailableLanguages() as $code => $name)
                <div class="border-2 rounded-lg p-4 transition-all duration-200 hover:shadow-md
                    {{ app()->getLocale() == $code ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">{{ language_flag($code) }}</span>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $name }}</h4>
                                <p class="text-sm text-gray-500">{{ strtoupper($code) }}</p>
                            </div>
                        </div>
                        @if(app()->getLocale() == $code)
                            <div class="flex items-center space-x-2">
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded-full">{{ __('common.active') }}</span>
                                <i class="fas fa-check text-blue-600"></i>
                            </div>
                        @else
                            <a href="{{ route('language.switch', $code) }}" 
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                {{ __('common.select') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if(auth()->user()->isDoctor())
    <!-- Clinic Information -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-clinic-medical text-purple-600 mr-2"></i>
                Informations de la Clinique
            </h3>
            <p class="text-gray-600 text-sm">Configurez les informations de votre clinique pour les prescriptions et documents officiels.</p>
            <p class="text-red-600 text-sm mt-1">
                <i class="fas fa-info-circle mr-1"></i>
                Les champs marqués d'un <span class="text-red-500">*</span> sont obligatoires.
            </p>
        </div>

        <form action="{{ route('settings.clinic-info') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de la Clinique <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="clinic_name" value="{{ \App\Models\Setting::get('clinic_name', '') }}" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Téléphone <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="clinic_phone" value="{{ \App\Models\Setting::get('clinic_phone', '') }}" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse Complète <span class="text-red-500">*</span>
                </label>
                <textarea name="clinic_address" rows="3" 
                          required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">{{ \App\Models\Setting::get('clinic_address', '') }}</textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="clinic_email" value="{{ \App\Models\Setting::get('clinic_email', '') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Web</label>
                    <input type="url" name="clinic_website" value="{{ \App\Models\Setting::get('clinic_website', '') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    Sauvegarder les Informations
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Appointment Settings -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-calendar-check text-indigo-600 mr-2"></i>
                {{ __('appointments.appointment_settings') }}
            </h3>
            <p class="text-gray-600 text-sm">{{ __('appointments.appointment_settings_description') }}</p>
        </div>

        <form action="{{ route('settings.appointment-duration') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hourglass-half text-indigo-500 mr-1"></i>
                        {{ __('appointments.appointment_duration') }}
                    </label>
                    <div class="relative">
                        <select name="appointment_duration" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 appearance-none bg-white">
                            @php $currentDuration = \App\Models\Setting::getAppointmentDuration(); @endphp
                            <option value="15" {{ $currentDuration == 15 ? 'selected' : '' }}>15 {{ __('common.minutes') }}</option>
                            <option value="20" {{ $currentDuration == 20 ? 'selected' : '' }}>20 {{ __('common.minutes') }}</option>
                            <option value="30" {{ $currentDuration == 30 ? 'selected' : '' }}>30 {{ __('common.minutes') }}</option>
                            <option value="45" {{ $currentDuration == 45 ? 'selected' : '' }}>45 {{ __('common.minutes') }}</option>
                            <option value="60" {{ $currentDuration == 60 ? 'selected' : '' }}>60 {{ __('common.minutes') }}</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ __('appointments.duration_help_text') }}</p>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                        <i class="fas fa-save text-sm"></i>
                        <span>{{ __('common.save_changes') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if(auth()->user()->isDoctor())
    <!-- Working Hours Settings -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-clock text-blue-600 mr-2"></i>
                {{ __('common.working_hours') }}
            </h3>
            <p class="text-gray-600 text-sm">{{ __('common.working_hours_description') }}</p>
        </div>

        <form action="{{ route('settings.working-hours') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $days = [
                        'monday' => __('common.monday'),
                        'tuesday' => __('common.tuesday'), 
                        'wednesday' => __('common.wednesday'),
                        'thursday' => __('common.thursday'),
                        'friday' => __('common.friday'),
                        'saturday' => __('common.saturday'),
                        'sunday' => __('common.sunday')
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('common.start_time') }}</label>
                            <input type="time" 
                                   name="{{ $day }}_start" 
                                   value="{{ $workingHours[$day]['start_time'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   {{ $workingHours[$day]['is_working'] ? 'required' : '' }}>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('common.end_time') }}</label>
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
                                {{ \App\Models\Setting::isWithinWorkingHours() ? __('common.open') : __('common.closed') }}
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
                    <span>{{ __('common.save_working_hours') }}</span>
                </button>
                
                <button type="button" onclick="setDefaultHours()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Reset to Default</span>
                </button>
            </div>
        </form>
    </div>
    @endif

    @if(auth()->user()->isDoctor())
    <!-- Data Export Section -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-download text-emerald-600 mr-2"></i>
                Data Export
            </h3>
            <p class="text-gray-600 text-sm">Export all your clinic data to a comprehensive Excel file with multiple organized sheets.</p>
        </div>

        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-lg p-6 border border-emerald-200">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h4 class="font-semibold text-emerald-900 mb-2 flex items-center">
                        <i class="fas fa-file-csv text-emerald-600 mr-2"></i>
                        Complete Clinic Data Export
                    </h4>
                    <p class="text-emerald-700 text-sm mb-4">
                        This will generate a comprehensive CSV file containing all your clinic data organized in sections:
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                        <div class="flex items-center text-xs text-emerald-700">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            Overview & Statistics
                        </div>
                        <div class="flex items-center text-xs text-emerald-700">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            All Patients Data
                        </div>
                        <div class="flex items-center text-xs text-emerald-700">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            Appointments History
                        </div>
                        <div class="flex items-center text-xs text-emerald-700">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            Medical Records
                        </div>
                        <div class="flex items-center text-xs text-emerald-700">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            Prescriptions Details
                        </div>
                        <div class="flex items-center text-xs text-emerald-700">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            Lab Orders
                        </div>
                        <div class="flex items-center text-xs text-emerald-700">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            Analytics & Demographics
                        </div>
                        <div class="flex items-center text-xs text-emerald-700">
                            <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                            Excel Compatible
                        </div>
                    </div>
                    <div class="text-xs text-emerald-600 bg-emerald-100 rounded-md px-3 py-2 inline-flex items-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Single CSV file with all clinic data, perfectly formatted for Excel, Google Sheets, and data analysis
                    </div>
                </div>
                <div class="ml-6">
                    <form action="{{ route('settings.export-data') }}" method="POST" id="exportForm">
                        @csrf
                        <button 
                            type="submit" 
                            id="exportBtn"
                            class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center space-x-2 transform hover:scale-105">
                            <i class="fas fa-download text-lg"></i>
                            <span>Export All Data</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Today's Schedule -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-calendar-day text-green-600 mr-2"></i>
                {{ __('common.today_schedule') }}
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

        <!-- Export Status -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-database text-purple-600 mr-2"></i>
                Data Status
            </h4>
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>Patients:</span>
                    <span class="font-medium">{{ \App\Models\Patient::count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Appointments:</span>
                    <span class="font-medium">{{ \App\Models\Appointment::count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Records:</span>
                    <span class="font-medium">{{ \App\Models\MedicalRecord::count() }}</span>
                </div>
            </div>
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

// Enhanced export functionality with loading animation
document.addEventListener('DOMContentLoaded', function() {
    ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
        toggleDayHours(day);
    });
    
    // Export form handling
    const exportForm = document.getElementById('exportForm');
    const exportBtn = document.getElementById('exportBtn');
    
    if (exportForm && exportBtn) {
        exportForm.addEventListener('submit', function() {
            // Update button to show loading state
            exportBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Generating Excel...</span>
            `;
            
            exportBtn.disabled = true;
            exportBtn.classList.add('opacity-75', 'cursor-not-allowed');
            
            // Show progress notification
            showNotification('Preparing your comprehensive data export...', 'info');
            
            // Reset button after 30 seconds (in case of timeout)
            setTimeout(() => {
                resetExportBtn();
            }, 30000);
        });
    }
    
    function resetExportBtn() {
        if (exportBtn) {
            exportBtn.innerHTML = `
                <i class="fas fa-download text-lg"></i>
                <span>Export All Data</span>
            `;
            exportBtn.disabled = false;
            exportBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    }
    
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 p-4 transition-all duration-300 transform translate-x-full ${
            type === 'info' ? 'border-blue-500' : 
            type === 'success' ? 'border-green-500' : 
            type === 'error' ? 'border-red-500' : 'border-gray-500'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas ${
                        type === 'info' ? 'fa-info-circle text-blue-500' :
                        type === 'success' ? 'fa-check-circle text-green-500' :
                        type === 'error' ? 'fa-exclamation-circle text-red-500' : 'fa-bell text-gray-500'
                    }"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Slide in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
    
    // Reset export button if page loads with success message (after successful export)
    @if(session('success'))
        setTimeout(resetExportBtn, 1000);
        showNotification('{{ session('success') }}', 'success');
    @endif
    
    // Show error message if export failed
    @if(session('error'))
        setTimeout(resetExportBtn, 1000);
        showNotification('{{ session('error') }}', 'error');
    @endif
});
</script>
@endpush
@endsection
