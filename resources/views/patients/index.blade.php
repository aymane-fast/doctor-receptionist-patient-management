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
                @if(auth()->user()->isDoctor())
                <!-- Walk-in Patient Button -->
                <button onclick="openWalkInModal()" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-running"></i>
                    <span>{{ __('patients.walkin_patient') }}</span>
                </button>
                @endif
                
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
        <form method="GET" action="{{ route('patients.index') }}" class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
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
            <div class="flex space-x-3">
                <button type="submit" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-search"></i>
                    <span>{{ __('patients.search') }}</span>
                </button>
                @if(request('search'))
                <a href="{{ route('patients.index') }}" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>{{ __('patients.clear') }}</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Modern Patients Grid -->
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
                                        <span>{{ $patient->patient_id }}</span>
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
                        @if(request('search'))
                            {{ __('patients.no_patients_found') }}
                        @else
                            {{ __('patients.no_patients_registered') }}
                        @endif
                    </h3>
                    <p class="text-gray-500 text-lg mb-8 max-w-md mx-auto">
                        @if(request('search'))
                            {{ __('patients.no_match_search') }}
                        @else
                            {{ __('patients.start_building_database') }}
                        @endif
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                        @if(request('search'))
                        <a href="{{ route('patients.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>{{ __('patients.view_all_patients') }}</span>
                        </a>
                        @endif
                        <a href="{{ route('patients.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2 deep-shadow">
                            <i class="fas fa-plus"></i>
                            <span>{{ request('search') ? __('patients.add_new_patient') : __('patients.add_first_patient') }}</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Walk-in Patient Modal -->
<div id="walkInModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-300" id="walkInContent">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-running text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ __('patients.walkin_patient') }}</h3>
                        <p class="text-sm text-gray-600">{{ __('patients.walkin_quick_registration') }}</p>
                    </div>
                </div>
                <button onclick="closeWalkInModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('patients.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="book_today" value="1">
                <input type="hidden" name="appointment_priority" value="normal">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.first_name') }} {{ __('patients.required') }}</label>
                        <input type="text" name="first_name" required class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.last_name') }} {{ __('patients.required') }}</label>
                        <input type="text" name="last_name" required class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.phone_number') }}</label>
                    <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-emerald-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.birth_date') }}</label>
                        <input type="date" name="birth_date" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.gender') }}</label>
                        <select name="gender" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-emerald-500">
                            <option value="">{{ __('patients.select') }}</option>
                            <option value="male">{{ __('patients.male') }}</option>
                            <option value="female">{{ __('patients.female') }}</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.address') }}</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-emerald-500 resize-none"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-red-700 mb-2">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-1"></i>
                        {{ __('patients.allergies_important') }}
                    </label>
                    <input type="text" name="allergies" placeholder="{{ __('patients.allergies_walkin_placeholder') }}" class="w-full px-3 py-2 border border-red-300 rounded-xl focus:outline-none focus:border-red-500 bg-red-50">
                    <p class="text-xs text-red-600 mt-1">{{ __('patients.allergies_safety_note') }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.reason_for_visit') }}</label>
                    <input type="text" name="appointment_reason" placeholder="{{ __('patients.reason_placeholder') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-emerald-500">
                </div>
                
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-clock text-emerald-600"></i>
                        <span class="text-sm font-medium text-emerald-700">{{ __('patients.auto_booking_today') }}</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3 pt-4">
                    <button type="button" onclick="closeWalkInModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-3 rounded-xl font-medium transition-colors">
                        {{ __('patients.cancel') }}
                    </button>
                    <button type="submit" class="flex-1 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-3 rounded-xl font-medium transition-all duration-200">
                        {{ __('patients.register_and_book') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openWalkInModal() {
    const modal = document.getElementById('walkInModal');
    const content = document.getElementById('walkInContent');
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeWalkInModal() {
    const modal = document.getElementById('walkInModal');
    const content = document.getElementById('walkInContent');
    
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

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

// Close modal when clicking outside
document.getElementById('walkInModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeWalkInModal();
    }
});
</script>
@endpush
@endsection
