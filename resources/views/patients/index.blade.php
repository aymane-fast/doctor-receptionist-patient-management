@extends('layouts.app')

@section('title', 'Patients')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-users text-blue-600 mr-2"></i>
                    Patients
                </h1>
                <p class="text-gray-600 mt-1">Manage patient information and records</p>
            </div>
            <a href="{{ route('patients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Add New Patient
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('patients.index') }}" class="flex items-center space-x-4">
            <div class="flex-1">
                <label for="search" class="sr-only">Search patients</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by name, patient ID, or phone..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            @if(request('search'))
            <a href="{{ route('patients.index') }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                <i class="fas fa-times mr-2"></i>Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Patients List -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            @if($patients->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($patients as $patient)
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600 text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $patient->full_name }}</h3>
                                    <p class="text-sm text-gray-500">ID: {{ $patient->patient_id }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('patients.show', $patient) }}" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('patients.edit', $patient) }}" class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-birthday-cake text-gray-400 mr-2"></i>
                                Age: {{ $patient->age }} years
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-venus-mars text-gray-400 mr-2"></i>
                                {{ ucfirst($patient->gender) }}
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-phone text-gray-400 mr-2"></i>
                                {{ $patient->phone }}
                            </div>
                            @if($patient->email)
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                {{ $patient->email }}
                            </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">
                                    Added {{ $patient->created_at->diffForHumans() }}
                                </span>
                                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" 
                                   class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs hover:bg-green-200 transition">
                                    <i class="fas fa-calendar-plus mr-1"></i>Book Appointment
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $patients->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-users text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No patients found</h3>
                    <p class="text-gray-500 mb-6">
                        @if(request('search'))
                            No patients match your search criteria.
                        @else
                            Get started by adding your first patient.
                        @endif
                    </p>
                    <a href="{{ route('patients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>Add First Patient
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
