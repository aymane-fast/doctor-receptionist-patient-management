@extends('layouts.app')

@section('title', __('common.settings'))

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        :title="__('common.settings')"
        :description="__('common.configure_preferences')"
        icon="fa-sliders"
        :eyebrow="__('Clinic configuration')"
    />

    <!-- Language Settings -->
    <div class="section-card p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-language text-teal-700 mr-2"></i>
                {{ __('common.language') }} {{ __('common.settings') }}
            </h3>
            <p class="text-gray-600 text-sm">{{ __('Choose your preferred language for the application interface.') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach(\App\Models\Setting::getAvailableLanguages() as $code => $name)
                <div class="border-2 rounded-lg p-4 transition-all duration-200 hover:shadow-md
                    {{ app()->getLocale() == $code ? 'border-teal-500 bg-teal-50' : 'border-gray-200 hover:border-gray-300' }}">
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
                                <span class="bg-teal-100 text-teal-800 text-xs font-bold px-2 py-1 rounded-full">{{ __('common.active') }}</span>
                                <i class="fas fa-check text-teal-700"></i>
                            </div>
                        @else
                            <a href="{{ route('language.switch', $code) }}" 
                               class="btn-secondary px-4 py-2 text-sm">
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
    <div class="section-card p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-clinic-medical text-teal-700 mr-2"></i>
                {{ __('Clinic Information') }}
            </h3>
            <p class="text-gray-600 text-sm">{{ __('Configure your clinic details for prescriptions and official documents.') }}</p>
            <p class="text-red-600 text-sm mt-1">
                <i class="fas fa-info-circle mr-1"></i>
                {{ __('Fields marked with') }} <span class="text-red-500">*</span> {{ __('are required.') }}
            </p>
        </div>

        <form action="{{ route('settings.clinic-info') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Clinic name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="clinic_name" value="{{ \App\Models\Setting::get('clinic_name', '') }}" 
                           required
                           class="form-control w-full px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Phone') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="clinic_phone" value="{{ \App\Models\Setting::get('clinic_phone', '') }}" 
                           required
                           class="form-control w-full px-3 py-2">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Full address') }} <span class="text-red-500">*</span>
                </label>
                <textarea name="clinic_address" rows="3" 
                          required
                          class="form-control w-full px-3 py-2">{{ \App\Models\Setting::get('clinic_address', '') }}</textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }}</label>
                    <input type="email" name="clinic_email" value="{{ \App\Models\Setting::get('clinic_email', '') }}" 
                           class="form-control w-full px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Website') }}</label>
                    <input type="url" name="clinic_website" value="{{ \App\Models\Setting::get('clinic_website', '') }}" 
                           class="form-control w-full px-3 py-2">
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="btn-primary px-6 py-2">
                    {{ __('Save Information') }}
                </button>
            </div>
        </form>
    </div>
    @endif

    @if(auth()->user()->isDoctor())
    <!-- Practice Specialty -->
    <div class="section-card p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas {{ $currentSpecialty['icon'] }} text-teal-700 mr-2"></i>
                {{ __('Practice Specialty') }}
            </h3>
            <p class="text-gray-600 text-sm">{{ __('Choose the optional patient details, clinical fields, appointment suggestions, and demo data that fit your practice.') }}</p>
            <p class="mt-2 text-sm text-teal-900"><strong>{{ __($currentSpecialty['label']) }}:</strong> {{ __($currentSpecialty['description']) }} {{ __('Suggested consultation duration: :duration minutes; suggested workflow: :mode.', ['duration' => $currentSpecialty['defaults']['appointment_duration'], 'mode' => __($currentSpecialty['defaults']['appointment_mode'])]) }}</p>
        </div>

        <form action="{{ route('settings.specialty') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
                <div class="md:col-span-2">
                    <label for="clinic_specialty" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Clinic specialty') }}</label>
                    <select name="clinic_specialty" id="clinic_specialty" class="form-control w-full px-3 py-2 bg-white" required>
                        @foreach($specialtyProfiles as $key => $profile)
                            <option value="{{ $key }}" @selected(old('clinic_specialty', $currentSpecialty['key']) === $key)>
                                {{ __($profile['label']) }} ({{ $profile['defaults']['appointment_duration'] }} {{ __('min') }}, {{ $profile['defaults']['appointment_mode'] === 'queue' ? __('Queue mode') : __('Scheduled mode') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary px-6 py-2"><i class="fas fa-save text-sm"></i><span>{{ __('Save Specialty') }}</span></button>
            </div>
            <label class="flex items-start gap-3 rounded-lg bg-teal-50 p-4 text-sm text-teal-950">
                <input type="checkbox" name="apply_specialty_defaults" value="1" class="mt-1 rounded border-teal-300 text-teal-700 focus:ring-teal-600">
                <span>{{ __('Also apply this specialty\'s suggested consultation duration and appointment mode to my account. Existing appointments, patients, and clinical records are never changed.') }}</span>
            </label>
        </form>
    </div>
    @endif

    <!-- Appointment Settings -->
    <div class="section-card p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-calendar-check text-teal-700 mr-2"></i>
                {{ __('appointments.appointment_settings') }}
            </h3>
            <p class="text-gray-600 text-sm">{{ __('appointments.appointment_settings_description') }}</p>
        </div>

        <form action="{{ route('settings.appointment-duration') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hourglass-half text-teal-600 mr-1"></i>
                        {{ __('appointments.appointment_duration') }}
                    </label>
                    <div class="relative">
                        <select name="appointment_duration" 
                                class="form-control w-full px-3 py-2 appearance-none bg-white">
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
                    <button type="submit" class="btn-primary px-6 py-2">
                        <i class="fas fa-save text-sm"></i>
                        <span>{{ __('common.save_changes') }}</span>
                    </button>
                </div>
            </div>
        </form>

        @if(auth()->user()->isDoctor())
        <form action="{{ route('settings.appointment-mode') }}" method="POST" class="mt-8 pt-6 border-t border-gray-200 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list-ol text-teal-600 mr-1"></i>
                        {{ __('Appointment Mode') }}
                    </label>
                    <select name="appointment_mode"
                            id="appointment_mode"
                            onchange="toggleQueueLimit()"
                            class="form-control w-full px-3 py-2 bg-white">
                        <option value="scheduled" {{ auth()->user()->appointment_mode !== 'queue' ? 'selected' : '' }}>{{ __('Scheduled appointments') }}</option>
                        <option value="queue" {{ auth()->user()->appointment_mode === 'queue' ? 'selected' : '' }}>{{ __('Queue mode') }}</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Scheduled mode uses fixed times. Queue mode assigns automatic numbers and estimated times.') }}</p>
                </div>

                <div id="queue_limit_wrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-users text-teal-600 mr-1"></i>
                        {{ __('Daily Queue Limit') }}
                    </label>
                    <input type="number"
                           name="queue_daily_limit"
                           min="1"
                           max="500"
                           value="{{ old('queue_daily_limit', auth()->user()->queue_daily_limit) }}"
                           placeholder="{{ __('No limit') }}"
                           class="form-control w-full px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">{{ __('Leave empty if the queue should not have a daily cap.') }}</p>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn-primary px-6 py-2">
                        <i class="fas fa-save text-sm"></i>
                        <span>{{ __('Save Mode') }}</span>
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>

    @if(auth()->user()->isDoctor())
    <!-- Working Hours Settings -->
    <div class="section-card p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-clock text-teal-700 mr-2"></i>
                {{ __('common.working_hours') }}
            </h3>
            <p class="text-gray-600 text-sm">{{ __('common.working_hours_description') }}</p>
        </div>

        <form action="{{ route('settings.working-hours') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="working_hours_form" value="1">
            
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
                @php
                    $dayWorking = session()->hasOldInput('working_hours_form')
                        ? (bool) old($day.'_working', false) : $workingHours[$day]['is_working'];
                @endphp
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-900">{{ $dayName }}</h4>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="{{ $day }}_working" 
                                   value="1"
                                   class="sr-only peer"
                                   {{ $dayWorking ? 'checked' : '' }}
                                   onchange="toggleDayHours('{{ $day }}')">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-700"></div>
                        </label>
                    </div>
                    
                    <div id="{{ $day }}_hours" class="space-y-3 {{ $dayWorking ? '' : 'opacity-50 pointer-events-none' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('common.start_time') }}</label>
                            <input type="time" 
                                   name="{{ $day }}_start" 
                                   value="{{ old($day.'_start', $workingHours[$day]['start_time']) }}"
                                   class="form-control w-full px-3 py-2"
                                   {{ $dayWorking ? 'required' : '' }}>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('common.end_time') }}</label>
                            <input type="time" 
                                   name="{{ $day }}_end" 
                                   value="{{ old($day.'_end', $workingHours[$day]['end_time']) }}"
                                   class="form-control w-full px-3 py-2"
                                   {{ $dayWorking ? 'required' : '' }}>
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
                            {{ __('Next opening') }}: {{ $nextWorking->translatedFormat('M d, Y H:i') }}
                        </span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="flex items-center space-x-4 pt-4 border-t border-gray-200">
                <button type="submit" class="btn-primary px-6 py-2">
                    <i class="fas fa-save"></i>
                    <span>{{ __('common.save_working_hours') }}</span>
                </button>
                
                <button type="button" onclick="setDefaultHours()" class="btn-secondary px-6 py-2">
                    <i class="fas fa-undo"></i>
                    <span>{{ __('Reset to Default') }}</span>
                </button>
            </div>
        </form>
    </div>
    @endif

    @if(auth()->user()->isDoctor())
    <!-- Analytics & Data Management -->
    <div class="section-card p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                <i class="fas fa-chart-bar text-teal-700 mr-2"></i>
                {{ __('Analytics and Data') }}
            </h3>
            <p class="text-gray-600 text-sm">{{ __('Access your clinic statistics and export your data.') }}</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Analytics Button -->
            <div class="action-card p-6">
                <div class="text-center">
                    <div class="w-16 h-16 brand-mark rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">{{ __('Statistics') }}</h4>
                    <p class="text-sm text-gray-600 mb-4">{{ __('View your clinic trends, charts, and reports.') }}</p>
                    <a href="{{ route('statistics.index') }}" 
                       class="btn-primary px-6 py-3">
                        <i class="fas fa-chart-bar"></i>
                        <span>{{ __('View Statistics') }}</span>
                    </a>
                </div>
            </div>

            <!-- Export Data Button -->
            <div class="action-card p-6">
                <div class="text-center">
                    <div class="w-16 h-16 brand-mark rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-download text-white text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">{{ __('Data Export') }}</h4>
                    <p class="text-sm text-gray-600 mb-4">{{ __('Export patients, appointments, and prescriptions to Excel.') }}</p>
                    <a href="{{ route('exports.index') }}" 
                       class="btn-primary px-6 py-3">
                        <i class="fas fa-file-export"></i>
                        <span>{{ __('Export Data') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Today's Schedule -->
        <div class="section-card p-6">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-calendar-day text-teal-700 mr-2"></i>
                {{ __('common.today_schedule') }}
            </h4>
            @php 
                    $today = strtolower(\Illuminate\Support\Carbon::now()->format('l'));
                    $todayName = \Illuminate\Support\Carbon::now()->translatedFormat('l');
                $todayHours = $workingHours[$today];
            @endphp
            @if($todayHours['is_working'])
                <p class="text-sm text-gray-600">
                    <span class="font-medium">{{ $todayName }}</span><br>
                    {{ $todayHours['start_time'] }} - {{ $todayHours['end_time'] }}
                </p>
                <div class="mt-3 text-xs">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">{{ __('Open Today') }}</span>
                </div>
            @else
                <p class="text-sm text-gray-600">{{ __('Closed today') }}</p>
                <div class="mt-3 text-xs">
                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full">{{ __('Closed Today') }}</span>
                </div>
            @endif
        </div>

        <!-- Weekly Summary -->
        <div class="section-card p-6">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-calendar-week text-teal-700 mr-2"></i>
                {{ __('Weekly Summary') }}
            </h4>
            @php
                $workingDaysCount = collect($workingHours)->filter(function($day) { return $day['is_working']; })->count();
            @endphp
            <p class="text-sm text-gray-600">
                <span class="font-medium">{{ $workingDaysCount }} {{ __('working days') }}</span><br>
                {{ 7 - $workingDaysCount }} {{ __('days closed') }}
            </p>
        </div>

        <!-- Export Status -->
        <div class="section-card p-6">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-database text-teal-700 mr-2"></i>
                {{ __('Data Status') }}
            </h4>
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>{{ __('Patients:') }}</span>
                    <span class="font-medium">{{ \App\Models\Patient::count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('Appointments:') }}</span>
                    <span class="font-medium">{{ \App\Models\Appointment::count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('Records:') }}</span>
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
    
    if (!checkbox || !hoursDiv || !startInput || !endInput) {
        return;
    }

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

function toggleQueueLimit() {
    const mode = document.getElementById('appointment_mode');
    const wrapper = document.getElementById('queue_limit_wrapper');

    if (!mode || !wrapper) {
        return;
    }

    wrapper.classList.toggle('opacity-50', mode.value !== 'queue');
}

// Enhanced export functionality with loading animation
document.addEventListener('DOMContentLoaded', function() {
    ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
        toggleDayHours(day);
    });
    toggleQueueLimit();
    


    
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
                        type === 'info' ? 'fa-info-circle text-teal-600' :
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
        showNotification(@json(session('success')), 'success');
    @endif
    
    // Show error message if export failed
    @if(session('error'))
        showNotification(@json(session('error')), 'error');
    @endif
});
</script>
@endpush
@endsection
