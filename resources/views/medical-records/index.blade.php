@extends('layouts.app')

@section('title', __('medical_records.title'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('medical_records.title') }}</h1>
        <div class="flex items-center space-x-3">
            <!-- Filter by Patient -->
            <form method="GET" action="{{ route('medical-records.index') }}" class="flex items-center space-x-2">
                <select name="patient_id" onchange="this.form.submit()" 
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">{{ __('medical_records.all_patients') }}</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                            {{ $patient->first_name }} {{ $patient->last_name }}
                        </option>
                    @endforeach
                </select>
            </form>
            
            <a href="{{ route('medical-records.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>{{ __('medical_records.new_record') }}
            </a>
        </div>
    </div>

    <!-- Medical Records Grid -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($medicalRecords as $record)
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Patient Info -->
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $record->patient->first_name }} {{ $record->patient->last_name }}
                                </h3>
                                <p class="text-gray-600">
                                    {{ __('medical_records.age') }}: {{ $record->patient->age }} • 
                                    {{ $record->patient->phone }}
                                </p>
                            </div>
                        </div>

                        <!-- Record Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('medical_records.visit_date') }}</label>
                                <p class="text-gray-900">{{ \Carbon\Carbon::parse($record->visit_date)->format('M j, Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('medical_records.doctor') }}</label>
                                <p class="text-gray-900">Dr. {{ $record->doctor->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('medical_records.visit_type') }}</label>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($record->visit_type == 'consultation') bg-blue-100 text-blue-800
                                    @elseif($record->visit_type == 'follow-up') bg-green-100 text-green-800
                                    @elseif($record->visit_type == 'emergency') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ __('medical_records.visit_types.' . $record->visit_type) }}
                                </span>
                            </div>
                        </div>

                        <!-- Chief Complaint -->
                        @if($record->chief_complaint)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('medical_records.chief_complaint') }}</label>
                            <p class="text-gray-900">{{ Str::limit($record->chief_complaint, 150) }}</p>
                        </div>
                        @endif

                        <!-- Diagnosis -->
                        @if($record->diagnosis)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('medical_records.diagnosis') }}</label>
                            <p class="text-gray-900">{{ Str::limit($record->diagnosis, 150) }}</p>
                        </div>
                        @endif

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            @if($record->blood_pressure)
                            <div>
                                <label class="block text-gray-500">{{ __('medical_records.blood_pressure') }}</label>
                                <p class="font-semibold">{{ $record->blood_pressure }}</p>
                            </div>
                            @endif
                            @if($record->heart_rate)
                            <div>
                                <label class="block text-gray-500">{{ __('medical_records.heart_rate') }}</label>
                                <p class="font-semibold">{{ $record->heart_rate }} {{ __('medical_records.bpm') }}</p>
                            </div>
                            @endif
                            @if($record->temperature)
                            <div>
                                <label class="block text-gray-500">{{ __('medical_records.temperature') }}</label>
                                <p class="font-semibold">{{ $record->temperature }}°F</p>
                            </div>
                            @endif
                            @if($record->weight)
                            <div>
                                <label class="block text-gray-500">{{ __('medical_records.weight') }}</label>
                                <p class="font-semibold">{{ $record->weight }} kg</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2 ml-4">
                        <a href="{{ route('medical-records.show', $record) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('medical-records.edit', $record) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg transition-colors">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>

                <!-- Record Footer -->
                <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between text-sm text-gray-500">
                    <span>{{ __('medical_records.created') }} {{ $record->created_at->diffForHumans() }}</span>
                    @if($record->appointment)
                        <a href="{{ route('appointments.show', $record->appointment) }}" 
                           class="text-blue-600 hover:text-blue-800 transition-colors">
                            <i class="fas fa-calendar mr-1"></i>{{ __('medical_records.view_appointment') }}
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-medical text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('medical_records.no_records_found') }}</h3>
                <p class="text-gray-600 mb-6">
                    @if(request('patient_id'))
                        {{ __('medical_records.no_records_for_patient') }}
                    @else
                        {{ __('medical_records.no_records_yet') }}
                    @endif
                </p>
                <a href="{{ route('medical-records.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>{{ __('medical_records.create_first_record') }}
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($medicalRecords->hasPages())
    <div class="mt-6">
        {{ $medicalRecords->links() }}
    </div>
    @endif
</div>
@endsection
