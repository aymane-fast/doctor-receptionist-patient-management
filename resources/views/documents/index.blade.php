@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Documents</h1>
        <div class="flex items-center space-x-3">
            <!-- Filter by Patient -->
            <form method="GET" action="{{ route('documents.index') }}" class="flex items-center space-x-2">
                <select name="patient_id" onchange="this.form.submit()" 
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Patients</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                            {{ $patient->first_name }} {{ $patient->last_name }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter by Document Type -->
                <select name="document_type" onchange="this.form.submit()" 
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="lab-report" {{ request('document_type') == 'lab-report' ? 'selected' : '' }}>Lab Report</option>
                    <option value="x-ray" {{ request('document_type') == 'x-ray' ? 'selected' : '' }}>X-Ray</option>
                    <option value="mri" {{ request('document_type') == 'mri' ? 'selected' : '' }}>MRI</option>
                    <option value="ct-scan" {{ request('document_type') == 'ct-scan' ? 'selected' : '' }}>CT Scan</option>
                    <option value="prescription" {{ request('document_type') == 'prescription' ? 'selected' : '' }}>Prescription</option>
                    <option value="insurance" {{ request('document_type') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                    <option value="consent-form" {{ request('document_type') == 'consent-form' ? 'selected' : '' }}>Consent Form</option>
                    <option value="discharge-summary" {{ request('document_type') == 'discharge-summary' ? 'selected' : '' }}>Discharge Summary</option>
                    <option value="other" {{ request('document_type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </form>
            
            <a href="{{ route('documents.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>Upload Document
            </a>
        </div>
    </div>

    <!-- Documents Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($documents as $document)
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Document Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            @php
                                $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                $iconClass = match($extension) {
                                    'pdf' => 'fas fa-file-pdf text-red-600',
                                    'doc', 'docx' => 'fas fa-file-word text-blue-600',
                                    'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image text-green-600',
                                    'xls', 'xlsx' => 'fas fa-file-excel text-green-600',
                                    default => 'fas fa-file text-gray-600'
                                };
                            @endphp
                            <i class="{{ $iconClass }} text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 truncate max-w-[200px]" title="{{ $document->title }}">
                                {{ $document->title }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ ucfirst(str_replace('-', ' ', $document->document_type)) }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Document Actions Dropdown -->
                    <div class="relative group">
                        <button class="text-gray-400 hover:text-gray-600 p-2">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="absolute right-0 top-8 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                            <a href="{{ route('documents.show', $document) }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </a>
                            <a href="{{ route('documents.download', $document) }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-download mr-2"></i>Download
                            </a>
                            <a href="{{ route('documents.edit', $document) }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </a>
                            <div class="border-t border-gray-200"></div>
                            <form action="{{ route('documents.destroy', $document) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this document?')" 
                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-trash mr-2"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Patient Info -->
                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                            <i class="fas fa-user text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $document->patient->first_name }} {{ $document->patient->last_name }}</p>
                            <p class="text-sm text-gray-600">{{ $document->patient->phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Document Details -->
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">File Name:</span>
                        <span class="text-gray-900 truncate max-w-[150px]" title="{{ $document->file_name }}">{{ $document->file_name }}</span>
                    </div>
                    
                    @if($document->file_size)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">File Size:</span>
                        <span class="text-gray-900">{{ number_format($document->file_size / 1024, 1) }} KB</span>
                    </div>
                    @endif

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Uploaded:</span>
                        <span class="text-gray-900">{{ $document->created_at->format('M j, Y') }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Uploaded by:</span>
                        <span class="text-gray-900">{{ $document->uploader->name }}</span>
                    </div>
                </div>

                <!-- Description -->
                @if($document->description)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">{{ Str::limit($document->description, 100) }}</p>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-2">
                    <a href="{{ route('documents.show', $document) }}" 
                       class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-center text-sm transition-colors">
                        View
                    </a>
                    <a href="{{ route('documents.download', $document) }}" 
                       class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-center text-sm transition-colors">
                        Download
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-lg shadow-md p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Documents Found</h3>
                <p class="text-gray-600 mb-6">
                    @if(request('patient_id') || request('document_type'))
                        No documents found matching your filter criteria.
                    @else
                        There are no documents in the system yet.
                    @endif
                </p>
                <a href="{{ route('documents.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Upload First Document
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($documents->hasPages())
    <div class="mt-6">
        {{ $documents->links() }}
    </div>
    @endif
</div>

<style>
/* Custom styles for dropdown hover effect */
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}
.group:hover .group-hover\:visible {
    visibility: visible;
}
</style>
@endsection
