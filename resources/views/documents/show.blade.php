@extends('layouts.app')

@section('title', 'Document Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Document Details</h1>
        <div class="flex space-x-3">
            <a href="{{ route('documents.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Documents
            </a>
            <a href="{{ route('documents.download', $document) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-download mr-2"></i>Download
            </a>
            <a href="{{ route('documents.edit', $document) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Document Preview -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Document Preview</h2>
                
                <div class="text-center">
                    @php
                        $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        $isPdf = $extension === 'pdf';
                    @endphp

                    @if($isImage)
                        <!-- Image Preview -->
                        <div class="max-w-full max-h-96 overflow-hidden rounded-lg border border-gray-200">
                            <img src="{{ route('documents.download', $document) }}" 
                                 alt="{{ $document->title }}" 
                                 class="max-w-full h-auto"
                                 style="max-height: 384px; object-fit: contain;">
                        </div>
                    @elseif($isPdf)
                        <!-- PDF Preview -->
                        <div class="bg-gray-100 rounded-lg p-8">
                            <i class="fas fa-file-pdf text-6xl text-red-600 mb-4"></i>
                            <p class="text-gray-600 mb-4">PDF Document</p>
                            <a href="{{ route('documents.download', $document) }}" 
                               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-external-link-alt mr-2"></i>Open in New Tab
                            </a>
                        </div>
                    @else
                        <!-- Other File Types -->
                        <div class="bg-gray-100 rounded-lg p-8">
                            @php
                                $iconClass = match($extension) {
                                    'doc', 'docx' => 'fas fa-file-word text-blue-600',
                                    'xls', 'xlsx' => 'fas fa-file-excel text-green-600',
                                    'ppt', 'pptx' => 'fas fa-file-powerpoint text-orange-600',
                                    'txt' => 'fas fa-file-alt text-gray-600',
                                    'zip', 'rar', '7z' => 'fas fa-file-archive text-yellow-600',
                                    default => 'fas fa-file text-gray-600'
                                };
                            @endphp
                            <i class="{{ $iconClass }} text-6xl mb-4"></i>
                            <p class="text-gray-600 mb-4">{{ strtoupper($extension) }} Document</p>
                            <p class="text-sm text-gray-500 mb-4">This file type cannot be previewed in the browser</p>
                            <a href="{{ route('documents.download', $document) }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-download mr-2"></i>Download to View
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Document Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Document Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Title</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $document->title }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Document Type</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($document->document_type == 'lab-report') bg-blue-100 text-blue-800
                            @elseif($document->document_type == 'x-ray') bg-purple-100 text-purple-800
                            @elseif($document->document_type == 'prescription') bg-green-100 text-green-800
                            @elseif($document->document_type == 'insurance') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst(str_replace('-', ' ', $document->document_type)) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">File Name</label>
                        <p class="text-gray-900">{{ $document->file_name }}</p>
                    </div>

                    @if($document->file_size)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">File Size</label>
                        <p class="text-gray-900">
                            @if($document->file_size < 1024)
                                {{ $document->file_size }} bytes
                            @elseif($document->file_size < 1048576)
                                {{ number_format($document->file_size / 1024, 1) }} KB
                            @else
                                {{ number_format($document->file_size / 1048576, 1) }} MB
                            @endif
                        </p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Upload Date</label>
                        <p class="text-gray-900">{{ $document->created_at->format('l, F j, Y g:i A') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Uploaded By</label>
                        <p class="text-gray-900">{{ $document->uploader->name }}</p>
                    </div>

                    @if($document->is_private)
                    <div class="md:col-span-2">
                        <div class="flex items-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <i class="fas fa-lock text-yellow-600 mr-2"></i>
                            <span class="text-yellow-800 font-medium">Private Document</span>
                            <span class="text-yellow-700 ml-2">- Only visible to doctors</span>
                        </div>
                    </div>
                    @endif
                </div>

                @if($document->description)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $document->description }}</p>
                    </div>
                </div>
                @endif

                @if($document->notes)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Notes</label>
                    <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-400">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $document->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Patient Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Patient Information</h3>
                
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ $document->patient->first_name }} {{ $document->patient->last_name }}</h4>
                        <p class="text-gray-600">{{ $document->patient->phone }}</p>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Age:</span>
                        <span class="text-gray-900">{{ $document->patient->age }} years</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Gender:</span>
                        <span class="text-gray-900">{{ ucfirst($document->patient->gender) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Email:</span>
                        <span class="text-gray-900">{{ $document->patient->email }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('patients.show', $document->patient) }}" 
                       class="text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-user mr-1"></i>View Patient Profile
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('documents.download', $document) }}" 
                       class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors block text-center">
                        <i class="fas fa-download mr-2"></i>Download Document
                    </a>
                    
                    <a href="{{ route('documents.create', ['patient_id' => $document->patient_id]) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors block text-center">
                        <i class="fas fa-plus mr-2"></i>Upload Another Document
                    </a>
                    
                    <button onclick="window.print()" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-print mr-2"></i>Print Details
                    </button>
                </div>
            </div>

            <!-- Related Records -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Records</h3>
                <div class="space-y-2">
                    <a href="{{ route('documents.index', ['patient_id' => $document->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-file-alt mr-2"></i>Patient's Documents
                    </a>
                    <a href="{{ route('medical-records.index', ['patient_id' => $document->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-file-medical mr-2"></i>Medical Records
                    </a>
                    <a href="{{ route('prescriptions.index', ['patient_id' => $document->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-prescription-bottle mr-2"></i>Prescriptions
                    </a>
                    <a href="{{ route('appointments.index', ['patient_id' => $document->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-calendar mr-2"></i>Appointments
                    </a>
                </div>
            </div>

            <!-- Document History -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Document History</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                        <div>
                            <p class="text-gray-900 font-medium">Document uploaded</p>
                            <p class="text-gray-500">{{ $document->created_at->format('M j, Y g:i A') }}</p>
                            <p class="text-gray-500">by {{ $document->uploader->name }}</p>
                        </div>
                    </div>
                    
                    @if($document->updated_at != $document->created_at)
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                        <div>
                            <p class="text-gray-900 font-medium">Document updated</p>
                            <p class="text-gray-500">{{ $document->updated_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
