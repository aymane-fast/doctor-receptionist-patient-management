@extends('layouts.app')

@section('title', 'Prescriptions')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Prescriptions</h1>
        <div class="flex items-center space-x-3">
            <!-- Filter by Patient -->
            <form method="GET" action="{{ route('prescriptions.index') }}" class="flex items-center space-x-2">
                <select name="patient_id" onchange="this.form.submit()" 
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Patients</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                            {{ $patient->first_name }} {{ $patient->last_name }}
                        </option>
                    @endforeach
                </select>
            </form>
            
            <a href="{{ route('prescriptions.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>New Prescription
            </a>
        </div>
    </div>

    <!-- Prescriptions Grid -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($prescriptions as $prescription)
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Patient & Doctor Info -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-prescription-bottle text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}
                                    </h3>
                                    <p class="text-gray-600">
                                        Prescribed by Dr. {{ $prescription->doctor->name }} • 
                                        {{ $prescription->patient->phone }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($prescription->status == 'active') bg-green-100 text-green-800
                                @elseif($prescription->status == 'completed') bg-blue-100 text-blue-800
                                @elseif($prescription->status == 'cancelled') bg-red-100 text-red-800
                                @elseif($prescription->status == 'expired') bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($prescription->status) }}
                            </span>
                        </div>

                        <!-- Prescription Details -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Medication</label>
                                <p class="text-gray-900 font-semibold">{{ $prescription->medication_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Dosage</label>
                                <p class="text-gray-900">{{ $prescription->dosage }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Frequency</label>
                                <p class="text-gray-900">{{ $prescription->frequency }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                                <p class="text-gray-900">{{ $prescription->duration }}</p>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Prescribed Date</label>
                                <p class="text-gray-900">{{ \Carbon\Carbon::parse($prescription->prescribed_date)->format('M j, Y') }}</p>
                            </div>
                            @if($prescription->start_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Start Date</label>
                                <p class="text-gray-900">{{ \Carbon\Carbon::parse($prescription->start_date)->format('M j, Y') }}</p>
                            </div>
                            @endif
                            @if($prescription->end_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">End Date</label>
                                <p class="text-gray-900">{{ \Carbon\Carbon::parse($prescription->end_date)->format('M j, Y') }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Instructions -->
                        @if($prescription->instructions)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Instructions</label>
                            <p class="text-gray-900">{{ Str::limit($prescription->instructions, 200) }}</p>
                        </div>
                        @endif

                        <!-- Quantity & Refills -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            @if($prescription->quantity)
                            <div>
                                <label class="block text-gray-500">Quantity</label>
                                <p class="font-semibold">{{ $prescription->quantity }}</p>
                            </div>
                            @endif
                            @if($prescription->refills !== null)
                            <div>
                                <label class="block text-gray-500">Refills</label>
                                <p class="font-semibold">{{ $prescription->refills }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2 ml-4">
                        <a href="{{ route('prescriptions.show', $prescription) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('prescriptions.edit', $prescription) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg transition-colors">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>

                <!-- Prescription Footer -->
                <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between text-sm text-gray-500">
                    <span>Created {{ $prescription->created_at->diffForHumans() }}</span>
                    <div class="flex items-center space-x-4">
                        @if($prescription->medicalRecord)
                            <a href="{{ route('medical-records.show', $prescription->medicalRecord) }}" 
                               class="text-blue-600 hover:text-blue-800 transition-colors">
                                <i class="fas fa-file-medical mr-1"></i>Medical Record
                            </a>
                        @endif
                        @if($prescription->status == 'active' && $prescription->end_date && \Carbon\Carbon::parse($prescription->end_date)->isPast())
                            <span class="text-orange-600 font-medium">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Expired
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-prescription-bottle text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Prescriptions Found</h3>
                <p class="text-gray-600 mb-6">
                    @if(request('patient_id'))
                        No prescriptions found for the selected patient.
                    @else
                        There are no prescriptions in the system yet.
                    @endif
                </p>
                <a href="{{ route('prescriptions.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create First Prescription
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($prescriptions->hasPages())
    <div class="mt-6">
        {{ $prescriptions->links() }}
    </div>
    @endif
</div>
@endsection
