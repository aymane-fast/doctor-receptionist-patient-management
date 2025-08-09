@extends('layouts.app')

@section('title', 'Edit Prescription')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Prescription</h1>
            <div class="flex space-x-3">
                <a href="{{ route('prescriptions.show', $prescription) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Details
                </a>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('prescriptions.update', $prescription) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Patient and Basic Info -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Patient Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Patient Selection -->
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Patient <span class="text-red-500">*</span>
                            </label>
                            <select id="patient_id" name="patient_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select a patient</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" 
                                            {{ (old('patient_id', $prescription->patient_id) == $patient->id) ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Medical Record Link -->
                        <div>
                            <label for="medical_record_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Related Medical Record
                            </label>
                            <select id="medical_record_id" name="medical_record_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">No medical record</option>
                                @foreach($medicalRecords as $record)
                                    <option value="{{ $record->id }}" 
                                            {{ old('medical_record_id', $prescription->medical_record_id) == $record->id ? 'selected' : '' }}>
                                        {{ $record->patient->first_name }} {{ $record->patient->last_name }} - 
                                        {{ \Carbon\Carbon::parse($record->visit_date)->format('M j, Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Prescribed Date -->
                        <div>
                            <label for="prescribed_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Prescribed Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="prescribed_date" name="prescribed_date" 
                                   value="{{ old('prescribed_date', $prescription->prescribed_date) }}" required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('prescribed_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select id="status" name="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="active" {{ old('status', $prescription->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $prescription->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $prescription->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="expired" {{ old('status', $prescription->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Medication Details -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Medication Details</h2>
                    
                    <!-- Medication Name -->
                    <div class="mb-6">
                        <label for="medication_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Medication Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="medication_name" name="medication_name" 
                               value="{{ old('medication_name', $prescription->medication_name) }}" required
                               placeholder="Enter medication name..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('medication_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Dosage -->
                        <div>
                            <label for="dosage" class="block text-sm font-medium text-gray-700 mb-2">
                                Dosage <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="dosage" name="dosage" 
                                   value="{{ old('dosage', $prescription->dosage) }}" required
                                   placeholder="e.g., 500mg"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('dosage')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Frequency -->
                        <div>
                            <label for="frequency" class="block text-sm font-medium text-gray-700 mb-2">
                                Frequency <span class="text-red-500">*</span>
                            </label>
                            <select id="frequency" name="frequency" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select frequency</option>
                                <option value="Once daily" {{ old('frequency', $prescription->frequency) == 'Once daily' ? 'selected' : '' }}>Once daily</option>
                                <option value="Twice daily" {{ old('frequency', $prescription->frequency) == 'Twice daily' ? 'selected' : '' }}>Twice daily</option>
                                <option value="Three times daily" {{ old('frequency', $prescription->frequency) == 'Three times daily' ? 'selected' : '' }}>Three times daily</option>
                                <option value="Four times daily" {{ old('frequency', $prescription->frequency) == 'Four times daily' ? 'selected' : '' }}>Four times daily</option>
                                <option value="Every 4 hours" {{ old('frequency', $prescription->frequency) == 'Every 4 hours' ? 'selected' : '' }}>Every 4 hours</option>
                                <option value="Every 6 hours" {{ old('frequency', $prescription->frequency) == 'Every 6 hours' ? 'selected' : '' }}>Every 6 hours</option>
                                <option value="Every 8 hours" {{ old('frequency', $prescription->frequency) == 'Every 8 hours' ? 'selected' : '' }}>Every 8 hours</option>
                                <option value="Every 12 hours" {{ old('frequency', $prescription->frequency) == 'Every 12 hours' ? 'selected' : '' }}>Every 12 hours</option>
                                <option value="As needed" {{ old('frequency', $prescription->frequency) == 'As needed' ? 'selected' : '' }}>As needed (PRN)</option>
                                <option value="Weekly" {{ old('frequency', $prescription->frequency) == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="Other" {{ old('frequency', $prescription->frequency) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('frequency')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Duration -->
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                                Duration <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="duration" name="duration" 
                                   value="{{ old('duration', $prescription->duration) }}" required
                                   placeholder="e.g., 7 days, 2 weeks"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('duration')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date
                            </label>
                            <input type="date" id="start_date" name="start_date" 
                                   value="{{ old('start_date', $prescription->start_date) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('start_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                End Date
                            </label>
                            <input type="date" id="end_date" name="end_date" 
                                   value="{{ old('end_date', $prescription->end_date) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('end_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Quantity
                            </label>
                            <input type="text" id="quantity" name="quantity" 
                                   value="{{ old('quantity', $prescription->quantity) }}"
                                   placeholder="e.g., 30 tablets, 100ml"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('quantity')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Refills -->
                        <div>
                            <label for="refills" class="block text-sm font-medium text-gray-700 mb-2">
                                Number of Refills
                            </label>
                            <input type="number" id="refills" name="refills" 
                                   value="{{ old('refills', $prescription->refills) }}" 
                                   min="0" max="12"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('refills')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-6">
                        <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">
                            Instructions for Use <span class="text-red-500">*</span>
                        </label>
                        <textarea id="instructions" name="instructions" rows="4" required
                                  placeholder="Detailed instructions for the patient on how to take the medication..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('instructions', $prescription->instructions) }}</textarea>
                        @error('instructions')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Notes
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="Any additional notes or warnings for the patient or pharmacist..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes', $prescription->notes) }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                    <div>
                        <form action="{{ route('prescriptions.destroy', $prescription) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors"
                                    onclick="return confirm('Are you sure you want to delete this prescription? This action cannot be undone.')">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </button>
                        </form>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('prescriptions.show', $prescription) }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>Update Prescription
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
