@extends('layouts.app')

@section('title', 'Create Prescription')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create New Prescription</h1>
            <a href="{{ route('prescriptions.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Prescriptions
            </a>
        </div>

        <!-- Prescription Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('prescriptions.store') }}" method="POST">
                @csrf
                
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
                                            {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Medical Record Link -->
                        @if(request('medical_record_id'))
                        <input type="hidden" name="medical_record_id" value="{{ request('medical_record_id') }}">
                        @else
                        <div>
                            <label for="medical_record_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Related Medical Record
                            </label>
                            <select id="medical_record_id" name="medical_record_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">No medical record</option>
                                @foreach($medicalRecords as $record)
                                    <option value="{{ $record->id }}" {{ old('medical_record_id') == $record->id ? 'selected' : '' }}>
                                        {{ $record->patient->first_name }} {{ $record->patient->last_name }} - 
                                        {{ \Carbon\Carbon::parse($record->visit_date)->format('M j, Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Prescribed Date -->
                        <div>
                            <label for="prescribed_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Prescribed Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="prescribed_date" name="prescribed_date" 
                                   value="{{ old('prescribed_date', date('Y-m-d')) }}" required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('prescribed_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- No status field; state managed implicitly -->
                    </div>
                </div>

                <!-- Medication Items (multiple) -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Medications</h2>
                    <div id="items">
                        <div class="item grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Medication *</label>
                                <input type="text" name="items[0][medication_name]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Amoxicillin">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dosage *</label>
                                <input type="text" name="items[0][dosage]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="500mg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Frequency *</label>
                                <select name="items[0][frequency]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Select</option>
                                    <option>Once daily</option>
                                    <option>Twice daily</option>
                                    <option>Three times daily</option>
                                    <option>Four times daily</option>
                                    <option>Every 4 hours</option>
                                    <option>Every 6 hours</option>
                                    <option>Every 8 hours</option>
                                    <option>Every 12 hours</option>
                                    <option>As needed</option>
                                    <option>Weekly</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Duration (days)</label>
                                <input type="number" name="items[0][duration_days]" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="7">
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Instructions (optional)</label>
                                <input type="text" name="items[0][instructions]" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Before meals">
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-item" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded">+ Add another medication</button>
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
                                   value="{{ old('start_date') }}"
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
                                   value="{{ old('end_date') }}"
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
                                   value="{{ old('quantity') }}"
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
                                   value="{{ old('refills', 0) }}" 
                                   min="0" max="12"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('refills')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optional)</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Notes -->
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Notes
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="Any additional notes or warnings for the patient or pharmacist..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('prescriptions.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i>Create Prescription
                    </button>
                </div>
            </form>
        </div>
    </div>
<script>
let itemIndex = 1;
document.getElementById('add-item').addEventListener('click', () => {
  const wrapper = document.createElement('div');
  wrapper.className = 'item grid grid-cols-1 md:grid-cols-4 gap-4 mb-4';
  wrapper.innerHTML = `
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Medication *</label>
      <input type="text" name="items[${itemIndex}][medication_name]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Amoxicillin">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Dosage *</label>
      <input type="text" name="items[${itemIndex}][dosage]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="500mg">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Frequency *</label>
      <select name="items[${itemIndex}][frequency]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        <option value="">Select</option>
        <option>Once daily</option>
        <option>Twice daily</option>
        <option>Three times daily</option>
        <option>Four times daily</option>
        <option>Every 4 hours</option>
        <option>Every 6 hours</option>
        <option>Every 8 hours</option>
        <option>Every 12 hours</option>
        <option>As needed</option>
        <option>Weekly</option>
        <option>Other</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Duration (days)</label>
      <input type="number" name="items[${itemIndex}][duration_days]" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="7">
    </div>
    <div class="md:col-span-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Instructions (optional)</label>
      <input type="text" name="items[${itemIndex}][instructions]" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Before meals">
    </div>
  `;
  document.getElementById('items').appendChild(wrapper);
  itemIndex++;
});
</script>
</div>
@endsection
