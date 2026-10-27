@extends('layouts.app')

@section('title', __('appointments.edit_appointment'))

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        :title="__('appointments.edit_appointment')"
        :description="__('appointments.update_appointment_description')"
        icon="fa-calendar-days"
        :eyebrow="__('Booking details')"
    >
        <x-slot:actions>
            <button onclick="history.back()" class="btn-secondary px-4 py-2">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('common.back') }}</span>
            </button>
        </x-slot:actions>
    </x-ui.page-header>

        <!-- Modern Edit Form -->
        <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
            <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Patient & Doctor Selection Section -->
                <div class="p-6 pb-0">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 brand-mark rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('appointments.patient_information') }}</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Patient Selection -->
                        <div>
                            <label for="patient_id" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('patients.patient') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="patient_id" name="patient_id" required 
                                    class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white @error('patient_id') border-red-500 @enderror">
                                <option value="">{{ __('appointments.select_patient') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" 
                                            {{ (old('patient_id', $appointment->patient_id) == $patient->id) ? 'selected' : '' }}>
                                        {{ $patient->selection_label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Doctor Selection -->
                        <div>
                            @if(auth()->user()->isReceptionist())
                            <label for="doctor_id" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('Doctor') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="doctor_id" name="doctor_id" required 
                                    class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white @error('doctor_id') border-red-500 @enderror">
                                <option value="">{{ __('Select a doctor') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" 
                                            {{ (old('doctor_id', $appointment->doctor_id) == $doctor->id) ? 'selected' : '' }}>
                                        {{ $doctor->professional_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                            @else
                            <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('Assigned Doctor') }}
                            </label>
                            <div class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600">
                                {{ $appointment->doctor->professional_name }}
                            </div>
                            <input type="hidden" name="doctor_id" value="{{ $appointment->doctor_id }}">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                <!-- Appointment Details Section -->
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 brand-mark rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('Appointment Details') }}</h3>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-4">
                        <!-- Appointment Date -->
                        <div>
                            <label for="appointment_date" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('Date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="appointment_date" name="appointment_date" 
                                   value="{{ old('appointment_date', \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')) }}" required
                                   class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white @error('appointment_date') border-red-500 @enderror">
                            @error('appointment_date')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Appointment Time -->
                        <div>
                            <label for="appointment_time" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ $appointment->isQueueMode() ? __('Estimated Time') : __('Time') }} <span class="text-red-500">*</span>
                            </label>
                            @if($appointment->isQueueMode())
                            <div class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-700">
                                {{ __('Queue') }} #{{ $appointment->queue_number }} - {{ $appointment->displayTime() }}
                            </div>
                            <input type="hidden" name="appointment_time" value="{{ $appointment->appointment_time->format('H:i') }}">
                            <p class="text-xs text-gray-500 mt-1">{{ __('Queue estimated time is recalculated automatically when the queue order changes.') }}</p>
                            @else
                            <input type="time" id="appointment_time" name="appointment_time" 
                                   value="{{ old('appointment_time', \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i')) }}" required
                                   class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white @error('appointment_time') border-red-500 @enderror">
                            @endif
                            @error('appointment_time')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('Status') }}
                            </label>
                            <select id="status" name="status" 
                                    class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white @error('status') border-red-500 @enderror">
                                <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>{{ __('appointments.scheduled') }}</option>
                                <option value="in_progress" {{ old('status', $appointment->status) == 'in_progress' ? 'selected' : '' }}>{{ __('appointments.in_progress') }}</option>
                                <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>{{ __('appointments.completed') }}</option>
                                <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>{{ __('appointments.cancelled') }}</option>
                                <option value="no_show" {{ old('status', $appointment->status) == 'no_show' ? 'selected' : '' }}>{{ __('appointments.no_show') }}</option>
                            </select>
                            @error('status')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>
                    </div>

                    @if($appointment->isScheduledMode()) @include('appointments.override-fields', ['appointment' => $appointment]) @endif

                    <!-- Purpose & Notes in 2 columns -->
                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <!-- Purpose -->
                        <div>
                        <label for="reason" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __($clinicSpecialty['label']) }} {{ __('visit reason') }}
                            </label>
                            <textarea id="reason" name="reason" rows="3" 
                                      placeholder="{{ __('Brief description of the reason for this appointment...') }}"
                                      class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white resize-none @error('reason') border-red-500 @enderror">{{ old('reason', $appointment->reason) }}</textarea>
                        @error('reason')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                        </p>
                        @enderror
                        @if(! empty($clinicSpecialty['appointment_reasons']))
                            <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __($clinicSpecialty['label']) }} {{ __('suggestions') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($clinicSpecialty['appointment_reasons'] as $suggestion)
                                    <button type="button" data-reason-suggestion="{{ __($suggestion) }}" class="rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-medium text-teal-800 hover:bg-teal-100">{{ __($suggestion) }}</button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                                {{ __('Additional Notes') }}
                            </label>
                            <textarea id="notes" name="notes" rows="3" 
                                      placeholder="{{ __('Any additional notes or special instructions...') }}"
                                      class="form-control w-full px-3 py-2 bg-white/80 backdrop-blur-sm focus:bg-white resize-none @error('notes') border-red-500 @enderror">{{ old('notes', $appointment->notes) }}</textarea>
                            @error('notes')
                            <p class="mt-1 text-sm text-red-600 flex items-center space-x-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="bg-gray-50/80 p-4 border-t border-gray-200 flex items-center justify-between">
                    <div>
                        @if($appointment->status != 'completed' && $appointment->status != 'cancelled')
                            <button type="submit" 
                                    form="delete-appointment-form"
                                    class="btn-danger px-4 py-2"
                                    onclick="return confirm(@js(__('appointments.confirm_delete')))">
                                <i class="fas fa-trash text-sm"></i>
                                <span>{{ __('Delete') }}</span>
                            </button>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('appointments.show', $appointment) }}" 
                           class="btn-secondary px-5 py-2">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" 
                                class="btn-primary px-5 py-2">
                            <i class="fas fa-save text-sm"></i>
                            <span>{{ __('Update Appointment') }}</span>
                        </button>
                    </div>
                </div>
            </form>
            <form id="delete-appointment-form" action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('[data-reason-suggestion]').forEach((button) => {
    button.addEventListener('click', () => {
        const reasonInput = document.getElementById('reason');
        reasonInput.value = button.dataset.reasonSuggestion;
        reasonInput.focus();
    });
});
</script>
@endsection
