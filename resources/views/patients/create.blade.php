@extends('layouts.app')

@section('title', __('patients.add_new_patient'))

@section('content')
<div class="space-y-8">
    <!-- Modern Header -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center animate-float">
                    <i class="fas fa-user-plus text-emerald-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('patients.add_new_patient') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">{{ __('patients.create_comprehensive_profile') }}</p>
                </div>
            </div>
            <a href="{{ route('patients.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('patients.back_to_patients') }}</span>
            </a>
        </div>
    </div>

    <!-- Modern Patient Form -->
    <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
        <form method="POST" action="{{ route('patients.store') }}" class="space-y-8">
            @csrf
            
            <!-- Personal Information Section -->
            <div class="p-8 pb-0">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ __('patients.personal_information') }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="first_name" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.first_name') }} <span class="text-red-500">{{ __('patients.required') }}</span>
                        </label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               value="{{ old('first_name') }}"
                               required 
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('first_name') border-red-500 @enderror">
                        @error('first_name')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="last_name" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.last_name') }} <span class="text-red-500">{{ __('patients.required') }}</span>
                        </label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               value="{{ old('last_name') }}"
                               required 
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('last_name') border-red-500 @enderror">
                        @error('last_name')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="birth_date" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.date_of_birth') }}
                        </label>
                        <input type="date" 
                               id="birth_date" 
                               name="birth_date" 
                               value="{{ old('birth_date') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('birth_date') border-red-500 @enderror">
                        @error('birth_date')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="gender" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.gender') }}
                        </label>
                        <select id="gender" 
                                name="gender" 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('gender') border-red-500 @enderror">
                            <option value="">{{ __('patients.select_gender') }}</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>{{ __('patients.male') }}</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>{{ __('patients.female') }}</option>
                        </select>
                        @error('gender')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label for="id_card_number" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.id_card_number') }}
                        </label>
                        <input type="text" 
                               id="id_card_number" 
                               name="id_card_number" 
                               value="{{ old('id_card_number') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('id_card_number') border-red-500 @enderror">
                        @error('id_card_number')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200"></div>

            <!-- Contact Information Section -->
            <div class="p-8 py-0">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-phone text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ __('patients.contact_information') }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="phone" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.phone_number') }}
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('phone') border-red-500 @enderror">
                        @error('phone')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.email_address') }}
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('email') border-red-500 @enderror">
                        @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label for="address" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.address') }}
                        </label>
                        <textarea id="address" 
                                  name="address" 
                                  rows="4" 
                                  placeholder="{{ __('patients.address_placeholder') }}"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                        @error('address')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200"></div>

            <!-- Emergency Contact Section -->
            <div class="p-8 py-0">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-phone-alt text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ __('patients.emergency_contact') }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="emergency_contact_name" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.emergency_contact_name') }}
                        </label>
                        <input type="text" 
                               id="emergency_contact_name" 
                               name="emergency_contact_name" 
                               value="{{ old('emergency_contact_name') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('emergency_contact_name') border-red-500 @enderror">
                        @error('emergency_contact_name')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="emergency_contact_phone" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.emergency_contact_phone') }}
                        </label>
                        <input type="tel" 
                               id="emergency_contact_phone" 
                               name="emergency_contact_phone" 
                               value="{{ old('emergency_contact_phone') }}"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('emergency_contact_phone') border-red-500 @enderror">
                        @error('emergency_contact_phone')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200"></div>

            <!-- Medical Information Section -->
            <div class="p-8 py-0">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-heartbeat text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ __('patients.medical_information') }}</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="allergies" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.known_allergies') }}
                        </label>
                        <textarea id="allergies" 
                                  name="allergies" 
                                  rows="4" 
                                  placeholder="{{ __('patients.allergies_placeholder') }}"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('allergies') border-red-500 @enderror">{{ old('allergies') }}</textarea>
                        @error('allergies')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="chronic_conditions" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('patients.chronic_conditions') }}
                        </label>
                        <textarea id="chronic_conditions" 
                                  name="chronic_conditions" 
                                  rows="4" 
                                  placeholder="{{ __('patients.chronic_conditions_placeholder') }}"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 resize-none @error('chronic_conditions') border-red-500 @enderror">{{ old('chronic_conditions') }}</textarea>
                        @error('chronic_conditions')
                        <p class="mt-2 text-sm text-red-600 flex items-center space-x-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-8 rounded-b-3xl">
                <!-- Quick Booking Option -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 mb-6">
                    @if(!\App\Models\Setting::isWithinWorkingHours())
                        @php $nextWorking = \App\Models\Setting::getNextWorkingTime(); @endphp
                        <div class="bg-orange-100 border border-orange-300 rounded-xl p-4 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-orange-600 mr-3"></i>
                                <div>
                                    <h5 class="font-semibold text-orange-800">{{ __('patients.outside_working_hours') }}</h5>
                                    <p class="text-orange-700 text-sm">
                                        {{ __('patients.walkin_not_available') }}
                                        @if($nextWorking)
                                            {{ __('patients.next_available') }}: {{ $nextWorking->locale(app()->getLocale())->isoFormat('dddd, D MMM [à] HH:mm') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-bold text-gray-900 mb-2">{{ __('patients.walkin_patient') }}</h4>
                            <p class="text-gray-600 text-sm mb-4">{{ __('patients.walkin_description') }}</p>
                            
                            <div class="flex items-center space-x-3">
                                <label class="flex items-center space-x-3 cursor-pointer {{ !\App\Models\Setting::isWithinWorkingHours() ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    <input type="checkbox" 
                                           id="book_today" 
                                           name="book_today" 
                                           value="1" 
                                           {{ !\App\Models\Setting::isWithinWorkingHours() ? 'disabled' : '' }}
                                           class="w-5 h-5 text-blue-600 bg-white border-2 border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                    <span class="text-sm font-medium text-gray-700">{{ __('patients.book_appointment_today') }}</span>
                                </label>
                            </div>
                            
                            <!-- Appointment Details (shown when checkbox is checked) -->
                            <div id="appointment_details" class="hidden mt-4 p-4 bg-white rounded-xl border border-blue-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.reason_for_visit') }}</label>
                                        <input type="text" name="appointment_reason" placeholder="{{ __('patients.reason_placeholder') }}" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.priority') }}</label>
                                        <select name="appointment_priority" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500">
                                            <option value="normal">{{ __('patients.normal') }}</option>
                                            <option value="urgent">{{ __('patients.urgent') }}</option>
                                            <option value="emergency">{{ __('patients.emergency') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-700">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>{{ __('patients.auto_scheduling_info') }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-end space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('patients.index') }}" class="w-full sm:w-auto bg-gradient-to-r from-gray-300 to-gray-400 hover:from-gray-400 hover:to-gray-500 text-gray-800 px-8 py-3 rounded-2xl font-medium transition-all duration-200 text-center">
                        {{ __('patients.cancel') }}
                    </a>
                    <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-3 rounded-2xl font-medium transition-all duration-200 deep-shadow flex items-center justify-center space-x-2">
                        <i class="fas fa-save"></i>
                        <span id="submit_text">{{ __('patients.create_patient') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookTodayCheckbox = document.getElementById('book_today');
    const appointmentDetails = document.getElementById('appointment_details');
    const submitText = document.getElementById('submit_text');
    
    bookTodayCheckbox.addEventListener('change', function() {
        if (this.checked) {
            appointmentDetails.classList.remove('hidden');
            submitText.textContent = '{{ __('patients.create_patient_book_today') }}';
        } else {
            appointmentDetails.classList.add('hidden');
            submitText.textContent = '{{ __('patients.create_patient') }}';
        }
    });
});
</script>
@endpush
@endsection
