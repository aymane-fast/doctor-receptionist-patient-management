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
