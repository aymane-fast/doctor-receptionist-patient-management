@extends('layouts.app')

@section('title', __('patients.title'))

@section('content')
<div class="space-y-8">
    <!-- Modern Header -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl flex items-center justify-center animate-float">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('patients.patient_management') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">{{ __('patients.manage_efficiently') }}</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3">
                <!-- Regular Add Patient Button -->
                <a href="{{ route('patients.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 deep-shadow hover:shadow-xl flex items-center space-x-3">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('patients.add_new_patient') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Search and Filters -->
    <div class="glass-effect rounded-3xl p-6 modern-shadow">
        <form method="GET" action="{{ route('patients.index') }}" class="space-y-4">
            <!-- Search Bar and Date Filter -->
            <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
                <div class="flex-1 w-full">
                    <label for="search" class="sr-only">{{ __('patients.search_patients') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="{{ __('patients.search_placeholder') }}"
                               class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200">
                    </div>
                </div>
                <!-- Date Filter -->
                <div class="w-full md:w-auto">
                    <label for="date" class="sr-only">{{ __('patients.filter_by_date') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-calendar text-gray-400"></i>
                        </div>
                        <input type="date" 
                               id="date" 
                               name="date" 
                               value="{{ request('date') }}"
                               class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200">
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-search"></i>
                        <span>{{ __('patients.search') }}</span>
                    </button>
                    @if(request('search') || request('date'))
                    <a href="{{ route('patients.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>{{ __('patients.clear') }}</span>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Modern Patients Grid -->
    @if($hasSearchQuery)
    <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
        <div class="p-6">
            @if($patients->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($patients as $patient)
                    <div class="bg-white border-2 border-gray-100 rounded-2xl p-6 card-hover modern-shadow group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center group-hover:from-blue-200 group-hover:to-blue-300 transition-colors">
                                    <i class="fas fa-user text-blue-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $patient->full_name }}</h3>
                                    <p class="text-sm text-gray-500 flex items-center space-x-2">
                                        <i class="fas fa-id-card w-3"></i>
                                        <span>{{ $patient->id_card_number }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('patients.show', $patient) }}" class="w-9 h-9 bg-blue-100 hover:bg-blue-200 rounded-xl flex items-center justify-center transition-colors group/btn">
                                    <i class="fas fa-eye text-blue-600 text-sm group-hover/btn:scale-110 transition-transform"></i>
                                </a>
                                <a href="{{ route('patients.edit', $patient) }}" class="w-9 h-9 bg-emerald-100 hover:bg-emerald-200 rounded-xl flex items-center justify-center transition-colors group/btn" onclick="return confirmEdit('{{ $patient->full_name }}')">
                                    <i class="fas fa-edit text-emerald-600 text-sm group-hover/btn:scale-110 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-4">
                            <!-- Age and Gender side by side -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex items-center text-sm text-gray-600 bg-gray-50 rounded-xl p-3">
                                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-birthday-cake text-amber-600 text-xs"></i>
                                    </div>
                                    <span class="font-medium">{{ $patient->age }} {{ __('patients.years') }}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 bg-gray-50 rounded-xl p-3">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-venus-mars text-purple-600 text-xs"></i>
                                    </div>
                                    <span class="font-medium">{{ ucfirst($patient->gender) }}</span>
                                </div>
                            </div>
                            
                            <!-- Phone number full width -->
                            <div class="flex items-center text-sm text-gray-600 bg-gray-50 rounded-xl p-3">
                                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-phone text-emerald-600 text-xs"></i>
                                </div>
                                <span class="font-medium">{{ $patient->phone }}</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                    <span class="text-xs text-gray-500 font-medium">
                                        {{ __('patients.added') }} {{ $patient->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                                   class="bg-gradient-to-r from-emerald-100 to-emerald-50 hover:from-emerald-200 hover:to-emerald-100 text-emerald-800 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center space-x-2 border border-emerald-200"
                                   onclick="return confirmAppointment('{{ $patient->full_name }}', {{ $patient->allergies ? 'true' : 'false' }}, '{{ $patient->allergies }}')">
                                    <i class="fas fa-calendar-plus"></i>
                                    <span>{{ __('patients.book_appointment') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Modern Pagination -->
                <div class="mt-8 flex justify-center">
                    {{ $patients->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6 animate-float">
                        <i class="fas fa-users text-gray-400 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">
                        {{ __('patients.no_search_results') }}
                    </h3>
                    <p class="text-gray-500 text-lg mb-8 max-w-md mx-auto">
                        @if(request('search'))
                            {{ __('patients.no_search_results_desc', ['search' => request('search')]) }}
                        @elseif(request('date'))
                            {{ __('patients.no_patients_for_date') }}
                        @else
                            {{ __('patients.no_patients_found_desc') }}
                        @endif
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="{{ route('patients.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>{{ __('patients.clear_search') }}</span>
                        </a>
                        <a href="{{ route('patients.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2 deep-shadow">
                            <i class="fas fa-plus"></i>
                            <span>{{ __('patients.add_new_patient') }}</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @else
    <!-- Initial State - No Search Performed -->
    <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
        <div class="p-6">
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-3xl flex items-center justify-center mx-auto mb-6 animate-float">
                    <i class="fas fa-search text-blue-600 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">
                    {{ __('patients.search_patients_title') }}
                </h3>
                <p class="text-gray-500 text-lg mb-8 max-w-md mx-auto">
                    {{ __('patients.search_patients_desc') }}
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('patients.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2 deep-shadow">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('patients.add_new_patient') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>



@push('scripts')
<script>
// Confirmation dialog for editing patient
function confirmEdit(patientName) {
    return confirm(`{{ __('patients.confirm_edit_prefix') }} "${patientName}"?\n\n{{ __('patients.edit_warning') }}`);
}

// Confirmation dialog with allergy alert for appointment booking
function confirmAppointment(patientName, hasAllergies, allergies) {
    let message = `{{ __('patients.book_appointment_for') }} "${patientName}"?`;
    
    if (hasAllergies) {
        message += `\n\n⚠️ {{ __('patients.allergy_alert') }} ⚠️\n{{ __('patients.patient_has_allergies') }}: ${allergies}\n\n{{ __('patients.notify_medical_staff') }}`;
    }
    
    return confirm(message);
}
</script>
@endpush
@endsection
