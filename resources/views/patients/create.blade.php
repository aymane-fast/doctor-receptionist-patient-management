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
            <button onclick="history.back()" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('common.back') }}</span>
            </button>
        </div>
    </div>

    <!-- Walk-in Patient Quick Booking -->
    <div class="glass-effect rounded-3xl p-6 modern-shadow">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-clock text-white"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-xl font-bold text-gray-900 mb-2">{{ __('patients.walkin_patient') }}</h4>
                <p class="text-gray-600 mb-4">{{ __('patients.walkin_description') }}</p>
                
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
                
                <div class="flex items-center space-x-3">
                    <label class="flex items-center space-x-3 cursor-pointer {{ !\App\Models\Setting::isWithinWorkingHours() ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <input type="checkbox" 
                               id="book_today" 
                               name="book_today" 
                               value="1" 
                               {{ !\App\Models\Setting::isWithinWorkingHours() ? 'disabled' : '' }}
                               class="w-5 h-5 text-green-600 bg-white border-2 border-gray-300 rounded focus:ring-green-500 focus:border-green-500">
                        <span class="text-lg font-medium text-gray-700">{{ __('patients.book_appointment_today') }}</span>
                    </label>
                </div>
                
                <!-- Appointment Slot Details (shown when checkbox is checked) -->
                <div id="appointment_details" class="hidden mt-6 p-4 bg-white rounded-xl border-2 border-green-200">
                    <div class="flex items-center space-x-3 mb-4">
                        <i class="fas fa-calendar-check text-green-600"></i>
                        <h5 class="font-semibold text-gray-900">{{ __('patients.appointment_details') }}</h5>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.appointment_time') }}</label>
                            <div class="space-y-2">
                                <input type="date" 
                                       id="appointment_date" 
                                       name="appointment_date" 
                                       value="{{ date('Y-m-d') }}"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500">
                                
                                <div class="flex space-x-2">
                                    <input type="time" 
                                           id="appointment_time" 
                                           name="appointment_time" 
                                           value="{{ date('H:i', strtotime('+1 hour')) }}"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500">
                                    <button type="button" 
                                            id="refresh_slots" 
                                            class="px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-xl transition-colors"
                                            title="Refresh available slots">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Available slots preview -->
                            <div id="available_slots_preview" class="mt-2 hidden">
                                <p class="text-xs font-medium text-gray-600 mb-1">Available slots today:</p>
                                <div id="slots_container" class="flex flex-wrap gap-1">
                                    <!-- Slots will be populated by JavaScript -->
                                </div>
                            </div>
                            
                            <p class="text-xs text-gray-500 mt-1" id="slot_info">{{ __('patients.auto_slot_info') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('patients.reason_for_visit') }}</label>
                            <input type="text" 
                                   name="appointment_reason" 
                                   placeholder="{{ __('patients.reason_placeholder') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:border-green-500">
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-green-50 rounded-lg">
                        <p class="text-sm text-green-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>{{ __('patients.walk_in_scheduling_info') }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Patient Form -->
    <div class="glass-effect rounded-3xl modern-shadow overflow-hidden">
        <form method="POST" action="{{ route('patients.store') }}" class="space-y-8" id="patient_form">
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
                <div class="flex flex-col sm:flex-row items-center justify-end space-y-4 sm:space-y-0 sm:space-x-4">
                    <button type="button" onclick="history.back()" class="w-full sm:w-auto bg-gradient-to-r from-gray-300 to-gray-400 hover:from-gray-400 hover:to-gray-500 text-gray-800 px-8 py-3 rounded-2xl font-medium transition-all duration-200 text-center">
                        {{ __('patients.cancel') }}
                    </button>
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
    const patientForm = document.getElementById('patient_form');
    const appointmentTimeInput = document.getElementById('appointment_time');
    const appointmentDateInput = document.getElementById('appointment_date');
    
    // Fetch next available slot from API
    async function setNextAvailableSlot(date = null) {
        const slotInfo = document.getElementById('slot_info');
        const slotsPreview = document.getElementById('available_slots_preview');
        const slotsContainer = document.getElementById('slots_container');
        
        // Show loading state
        slotInfo.textContent = 'Loading available slots...';
        
        try {
            const dateParam = date || appointmentDateInput.value;
            const response = await fetch(`{{ route('api.appointments.next-available-slot') }}?date=${dateParam}`);
            const data = await response.json();
            
            if (data.available && data.next_slot) {
                // Set the next available slot
                appointmentTimeInput.value = data.next_slot.time;
                
                // Update info text
                slotInfo.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i>Next available: ${data.next_slot.display} (${data.all_slots.length} slots available)`;
                
                // Show available slots as clickable buttons
                if (data.all_slots && data.all_slots.length > 1) {
                    slotsContainer.innerHTML = '';
                    data.all_slots.slice(0, 8).forEach(slot => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'px-2 py-1 text-xs bg-green-100 hover:bg-green-200 text-green-700 rounded border transition-colors';
                        button.textContent = slot.display;
                        button.onclick = () => {
                            appointmentTimeInput.value = slot.time;
                            // Update active state
                            slotsContainer.querySelectorAll('button').forEach(b => b.classList.remove('bg-green-500', 'text-white'));
                            button.classList.add('bg-green-500', 'text-white');
                        };
                        
                        // Mark first slot as active
                        if (slot.time === data.next_slot.time) {
                            button.classList.add('bg-green-500', 'text-white');
                        }
                        
                        slotsContainer.appendChild(button);
                    });
                    slotsPreview.classList.remove('hidden');
                } else {
                    slotsPreview.classList.add('hidden');
                }
                
                // Update the main info text
                const mainInfo = document.querySelector('.text-green-700 strong');
                if (mainInfo) {
                    mainInfo.textContent = `Smart scheduling: ${data.next_slot.display} automatically selected based on availability`;
                }
            } else {
                // No slots available
                slotInfo.innerHTML = `<i class="fas fa-exclamation-triangle text-orange-500 mr-1"></i>${data.message || 'No available slots for this date'}`;
                slotsPreview.classList.add('hidden');
                
                // Set to working hours start time if available
                if (data.working_hours) {
                    appointmentTimeInput.value = data.working_hours.start;
                }
                
                // Update main info
                const mainInfo = document.querySelector('.text-green-700 strong');
                if (mainInfo) {
                    mainInfo.textContent = 'No available slots - appointment will be scheduled during working hours';
                }
            }
        } catch (error) {
            console.error('Error fetching available slots:', error);
            slotInfo.innerHTML = `<i class="fas fa-exclamation-circle text-red-500 mr-1"></i>Error loading slots. Using default time.`;
            slotsPreview.classList.add('hidden');
            
            // Fallback to simple time calculation
            const now = new Date();
            now.setHours(now.getHours() + 1);
            const timeString = now.toTimeString().slice(0, 5);
            appointmentTimeInput.value = timeString;
        }
    }
    
    // Function to refresh slots
    function refreshSlots() {
        const refreshBtn = document.getElementById('refresh_slots_btn');
        if (!refreshBtn) return;
        
        const originalHtml = refreshBtn.innerHTML;
        
        // Show loading state
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Refreshing...';
        refreshBtn.disabled = true;
        
        setNextAvailableSlot().then(() => {
            // Reset button state
            setTimeout(() => {
                refreshBtn.innerHTML = originalHtml;
                refreshBtn.disabled = false;
            }, 500);
        }).catch(() => {
            // Reset button even on error
            setTimeout(() => {
                refreshBtn.innerHTML = originalHtml;
                refreshBtn.disabled = false;
            }, 500);
        });
    }
    
    // Set default time on page load
    setNextAvailableSlot();
    
    // Handle walk-in checkbox toggle
    bookTodayCheckbox.addEventListener('change', function() {
        if (this.checked) {
            appointmentDetails.classList.remove('hidden');
            submitText.textContent = '{{ __('patients.create_patient_book_today') }}';
            
            // Add appointment form inputs to the main form
            const appointmentInputs = appointmentDetails.querySelectorAll('input, select, textarea');
            appointmentInputs.forEach(input => {
                input.setAttribute('form', 'patient_form');
            });
        } else {
            appointmentDetails.classList.add('hidden');
            submitText.textContent = '{{ __('patients.create_patient') }}';
            
            // Remove form attribute from appointment inputs
            const appointmentInputs = appointmentDetails.querySelectorAll('input, select, textarea');
            appointmentInputs.forEach(input => {
                input.removeAttribute('form');
            });
        }
    });
    
    // Update available slots when date changes
    appointmentDateInput.addEventListener('change', function() {
        if (bookTodayCheckbox.checked) {
            setNextAvailableSlot(this.value);
        }
    });
    
    // Form submission handler
    patientForm.addEventListener('submit', function(e) {
        if (bookTodayCheckbox.checked) {
            // Validate appointment time is not in the past
            const appointmentDate = new Date(appointmentDateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (appointmentDate.getTime() === today.getTime()) {
                const now = new Date();
                const selectedTime = appointmentTimeInput.value.split(':');
                const appointmentTime = new Date();
                appointmentTime.setHours(parseInt(selectedTime[0]), parseInt(selectedTime[1]), 0, 0);
                
                if (appointmentTime <= now) {
                    e.preventDefault();
                    alert('{{ __('patients.appointment_time_past_error') }}');
                    return false;
                }
            }
        }
    });
});
</script>
@endpush
@endsection
