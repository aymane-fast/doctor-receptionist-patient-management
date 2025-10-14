@extends('layouts.app')

@section('title', __('medical_records.title'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Compact Header -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-medical text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ __('medical_records.title') }}</h1>
                        <p class="text-gray-600 text-sm">Historique médical des patients</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Filter by Patient -->
                    <form method="GET" action="{{ route('medical-records.index') }}" class="flex items-center">
                        <select name="patient_id" onchange="this.form.submit()" 
                                class="px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                            <option value="">Tous les Patients</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                    {{ $patient->first_name }} {{ $patient->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    
                    <a href="{{ route('medical-records.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Nouveau dossier médical
                    </a>
                </div>
            </div>
        </div>

        <!-- Medical Records Grid -->
        <div class="space-y-8">
            @forelse($medicalRecords as $record)
                <div class="bg-white rounded-xl shadow-lg border-2 border-gray-200 overflow-hidden hover:shadow-xl hover:border-blue-300 transition-all duration-200">
                    <!-- Patient Header Strip -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-100 px-6 py-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <div class="flex items-center gap-4">
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ $record->patient->first_name }} {{ $record->patient->last_name }}
                                </h3>
                                <span class="text-sm text-gray-600 bg-white rounded-full px-3 py-1">
                                    <i class="fas fa-birthday-cake mr-1"></i>{{ $record->patient->age }} ans
                                </span>
                                <span class="text-sm text-gray-600 bg-white rounded-full px-3 py-1">
                                    <i class="fas fa-phone mr-1"></i>{{ $record->patient->phone }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">

                                <!-- Record Details - Compact Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Date de visite</label>
                                        <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($record->visit_date)->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Médecin</label>
                                        <p class="text-sm font-semibold text-gray-900">{{ $record->doctor->name }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Type de Visite</label>
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                                            @if($record->visit_type == 'consultation') bg-blue-100 text-blue-800
                                            @elseif($record->visit_type == 'follow-up') bg-green-100 text-green-800
                                            @elseif($record->visit_type == 'emergency') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($record->visit_type == 'consultation') Consultation
                                            @elseif($record->visit_type == 'follow-up') Suivi
                                            @elseif($record->visit_type == 'emergency') Urgence
                                            @else {{ $record->visit_type }}
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <!-- Diagnostic -->
                                @if($record->diagnosis)
                                <div class="mb-6 bg-blue-50 rounded-lg p-4 border-l-4 border-blue-400">
                                    <label class="block text-xs font-medium text-blue-700 mb-2">Diagnostic</label>
                                    <p class="text-sm text-blue-900 leading-relaxed">{{ Str::limit($record->diagnosis, 120) }}</p>
                                </div>
                                @endif

                                <!-- Vital Signs - Compact -->
                                @if($record->blood_pressure || $record->heart_rate || $record->temperature || $record->weight)
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    @if($record->blood_pressure)
                                    <div class="bg-red-50 rounded p-2 text-center">
                                        <div class="text-red-600 font-medium">Tension artérielle</div>
                                        <div class="text-red-900 font-bold">{{ $record->blood_pressure }}</div>
                                    </div>
                                    @endif
                                    @if($record->heart_rate)
                                    <div class="bg-pink-50 rounded p-2 text-center">
                                        <div class="text-pink-600 font-medium">Fréquence cardiaque</div>
                                        <div class="text-pink-900 font-bold">{{ $record->heart_rate }} bpm</div>
                                    </div>
                                    @endif
                                    @if($record->temperature)
                                    <div class="bg-orange-50 rounded p-2 text-center">
                                        <div class="text-orange-600 font-medium">Température</div>
                                        <div class="text-orange-900 font-bold">{{ $record->temperature }}°F</div>
                                    </div>
                                    @endif
                                    @if($record->weight)
                                    <div class="bg-blue-50 rounded p-2 text-center">
                                        <div class="text-blue-600 font-medium">Poids</div>
                                        <div class="text-blue-900 font-bold">{{ $record->weight }} kg</div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2 ml-4">
                                <a href="{{ route('medical-records.show', $record) }}" 
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('medical-records.edit', $record) }}" 
                                   class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Record Footer -->
                        <div class="mt-6 pt-4 border-t-2 border-gray-100 flex items-center justify-between text-xs text-gray-500">
                            <span>Créé il y a {{ $record->created_at->diffForHumans() }}</span>
                            @if($record->appointment)
                                <a href="{{ route('appointments.show', $record->appointment) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition-colors flex items-center">
                                    <i class="fas fa-calendar mr-1"></i>Voir le Rendez-vous
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Bottom Border for Extra Separation -->
                    <div class="h-1 bg-gradient-to-r from-blue-100 to-indigo-100"></div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-medical text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun dossier médical trouvé</h3>
                    <p class="text-gray-600 mb-6">
                        @if(request('patient_id'))
                            Aucun dossier médical pour ce patient
                        @else
                            Aucun dossier médical créé pour le moment
                        @endif
                    </p>
                    <a href="{{ route('medical-records.create') }}" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Créer le premier dossier
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
</div>
@endsection
