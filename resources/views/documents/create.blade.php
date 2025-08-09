@extends('layouts.app')

@section('title', 'Upload Document')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Upload New Document</h1>
            <a href="{{ route('documents.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Documents
            </a>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Patient Selection -->
                <div class="mb-6">
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

                <!-- Document Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Document Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" 
                           value="{{ old('title') }}" required
                           placeholder="Enter document title..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Document Type -->
                <div class="mb-6">
                    <label for="document_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Document Type <span class="text-red-500">*</span>
                    </label>
                    <select id="document_type" name="document_type" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select document type</option>
                        <option value="lab-report" {{ old('document_type') == 'lab-report' ? 'selected' : '' }}>Lab Report</option>
                        <option value="x-ray" {{ old('document_type') == 'x-ray' ? 'selected' : '' }}>X-Ray</option>
                        <option value="mri" {{ old('document_type') == 'mri' ? 'selected' : '' }}>MRI</option>
                        <option value="ct-scan" {{ old('document_type') == 'ct-scan' ? 'selected' : '' }}>CT Scan</option>
                        <option value="ultrasound" {{ old('document_type') == 'ultrasound' ? 'selected' : '' }}>Ultrasound</option>
                        <option value="prescription" {{ old('document_type') == 'prescription' ? 'selected' : '' }}>Prescription</option>
                        <option value="insurance" {{ old('document_type') == 'insurance' ? 'selected' : '' }}>Insurance Document</option>
                        <option value="consent-form" {{ old('document_type') == 'consent-form' ? 'selected' : '' }}>Consent Form</option>
                        <option value="discharge-summary" {{ old('document_type') == 'discharge-summary' ? 'selected' : '' }}>Discharge Summary</option>
                        <option value="referral" {{ old('document_type') == 'referral' ? 'selected' : '' }}>Referral Letter</option>
                        <option value="vaccination-record" {{ old('document_type') == 'vaccination-record' ? 'selected' : '' }}>Vaccination Record</option>
                        <option value="other" {{ old('document_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('document_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Upload -->
                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                        File <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <i class="fas fa-cloud-upload-alt text-3xl"></i>
                            </div>
                            <div class="flex text-sm text-gray-600">
                                <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload a file</span>
                                    <input id="file" name="file" type="file" class="sr-only" required onchange="displayFileName(this)">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PDF, DOC, DOCX, JPG, PNG, GIF up to 10MB
                            </p>
                            <p id="file-name" class="text-sm text-green-600 font-medium hidden"></p>
                        </div>
                    </div>
                    @error('file')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Enter a description for this document..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Upload Date -->
                <div class="mb-6">
                    <label for="upload_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Document Date
                    </label>
                    <input type="date" id="upload_date" name="upload_date" 
                           value="{{ old('upload_date', date('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-sm text-gray-500 mt-1">Date when the document was created/taken (leave blank to use today's date)</p>
                    @error('upload_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Privacy Settings -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input id="is_private" name="is_private" type="checkbox" value="1" 
                               {{ old('is_private') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_private" class="ml-2 block text-sm text-gray-900">
                            Mark as private (only visible to doctors)
                        </label>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Private documents are only accessible by doctors and the uploading user</p>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Additional Notes
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              placeholder="Any additional notes about this document..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('documents.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-upload mr-2"></i>Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function displayFileName(input) {
    const fileNameElement = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        fileNameElement.textContent = `Selected: ${input.files[0].name}`;
        fileNameElement.classList.remove('hidden');
    } else {
        fileNameElement.textContent = '';
        fileNameElement.classList.add('hidden');
    }
}

// Drag and drop functionality
const dropZone = document.querySelector('.border-dashed');
const fileInput = document.getElementById('file');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropZone.classList.add('border-blue-400', 'bg-blue-50');
}

function unhighlight(e) {
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
}

dropZone.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 0) {
        fileInput.files = files;
        displayFileName(fileInput);
    }
}
</script>
@endsection
