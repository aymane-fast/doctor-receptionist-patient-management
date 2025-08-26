@extends('layouts.app')

@section('title', __('prescriptions.prescription_details'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('prescriptions.prescription_details') }}</h1>
        <div class="flex space-x-3">
            <a href="{{ route('prescriptions.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('prescriptions.back_to_prescriptions') }}
            </a>
            <a href="{{ route('prescriptions.edit', $prescription) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-edit mr-2"></i>{{ __('common.edit') }}
            </a>
            <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-print mr-2"></i>{{ __('common.print') }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Prescription Header -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-center border-b pb-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('prescriptions.prescription').strtoupper() }}</h2>
                    <p class="text-gray-600">{{ __('prescriptions.prescription_id') }}: #{{ $prescription->id }}</p>
                    <div class="mt-4">
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                            @if($prescription->status == 'active') bg-green-100 text-green-800
                            @elseif($prescription->status == 'completed') bg-blue-100 text-blue-800
                            @elseif($prescription->status == 'cancelled') bg-red-100 text-red-800
                            @elseif($prescription->status == 'expired') bg-gray-100 text-gray-800
                            @endif">
                            {{ __('prescriptions.' . $prescription->status) }}
                        </span>
                    </div>
                </div>

                <!-- Patient Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('prescriptions.patient_information') }}</h3>
                        <div class="space-y-2">
                            <div class="flex">
                                <span class="w-20 text-gray-500">{{ __('patients.name') }}:</span>
                                <span class="font-semibold">{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-20 text-gray-500">{{ __('patients.age') }}:</span>
                                <span>{{ $prescription->patient->age }} {{ __('patients.years') }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-20 text-gray-500">{{ __('patients.gender') }}:</span>
                                <span>{{ ucfirst($prescription->patient->gender) }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-20 text-gray-500">{{ __('patients.phone') }}:</span>
                                <span>{{ $prescription->patient->phone }}</span>
                            </div>
                            @if($prescription->patient->address)
                            <div class="flex">
                                <span class="w-20 text-gray-500">{{ __('patients.address') }}:</span>
                                <span>{{ $prescription->patient->address }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('prescriptions.doctor_information') }}</h3>
                        <div class="space-y-2">
                            <div class="flex">
                                <span class="w-20 text-gray-500">{{ __('prescriptions.doctor_name') }}:</span>
                                <span class="font-semibold">Dr. {{ $prescription->doctor->name }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-20 text-gray-500">{{ __('patients.email') }}:</span>
                                <span>{{ $prescription->doctor->email }}</span>
                            </div>
                            @if($prescription->doctor->phone)
                            <div class="flex">
                                <span class="w-20 text-gray-500">{{ __('patients.phone') }}:</span>
                                <span>{{ $prescription->doctor->phone }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Prescription Dates -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('prescriptions.prescribed_date') }}</label>
                        <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($prescription->prescribed_date)->format('M j, Y') }}</p>
                    </div>
                    @if($prescription->start_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('prescriptions.start_date') }}</label>
                        <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($prescription->start_date)->format('M j, Y') }}</p>
                    </div>
                    @endif
                    @if($prescription->end_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('prescriptions.end_date') }}</label>
                        <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($prescription->end_date)->format('M j, Y') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Medication Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ __('prescriptions.medication_details') }}</h3>
                
                <!-- Main Medication Info -->
                <div class="bg-blue-50 rounded-lg p-6 mb-6 border-l-4 border-blue-400">
                    <h4 class="text-2xl font-bold text-blue-900 mb-2">{{ $prescription->medication_name }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-blue-600">{{ __('prescriptions.dosage') }}</label>
                            <p class="text-lg font-semibold text-blue-900">{{ $prescription->dosage }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-600">{{ __('prescriptions.frequency') }}</label>
                            <p class="text-lg font-semibold text-blue-900">{{ $prescription->frequency }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-600">{{ __('prescriptions.duration') }}</label>
                            <p class="text-lg font-semibold text-blue-900">{{ $prescription->duration }}</p>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">{{ __('prescriptions.instructions_for_use') }}</h4>
                    <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-400">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $prescription->instructions }}</p>
                    </div>
                </div>

                <!-- Additional Info -->
                @if($prescription->quantity || $prescription->refills !== null)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    @if($prescription->quantity)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('prescriptions.quantity') }}</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $prescription->quantity }}</p>
                    </div>
                    @endif
                    @if($prescription->refills !== null)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('prescriptions.refills_allowed') }}</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $prescription->refills }}</p>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Notes -->
                @if($prescription->notes)
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">{{ __('prescriptions.additional_notes') }}</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $prescription->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status & Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('prescriptions.quick_actions') }}</h3>
                <div class="space-y-3">
                    @if($prescription->status == 'active')
                        <form action="{{ route('prescriptions.update', $prescription) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-check mr-2"></i>{{ __('prescriptions.mark_as_completed') }}
                            </button>
                        </form>
                    @endif

                    @if($prescription->status != 'cancelled')
                        <form action="{{ route('prescriptions.update', $prescription) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors"
                                    onclick="return confirm('{{ __('prescriptions.cancel_confirmation') }}')">
                                <i class="fas fa-times mr-2"></i>{{ __('prescriptions.cancel_prescription') }}
                            </button>
                        </form>
                    @endif

                    <!-- Create New Prescription for same patient -->
                    <a href="{{ route('prescriptions.create', ['patient_id' => $prescription->patient_id]) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors block text-center">
                        <i class="fas fa-plus mr-2"></i>{{ __('prescriptions.new_prescription') }}
                    </a>
                </div>
            </div>

            <!-- Prescription Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('prescriptions.prescription_information') }}</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">ID:</span>
                        <span class="text-gray-900">#{{ $prescription->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('prescriptions.created') }}:</span>
                        <span class="text-gray-900">{{ $prescription->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('prescriptions.last_updated') }}:</span>
                        <span class="text-gray-900">{{ $prescription->updated_at->format('M j, Y g:i A') }}</span>
                    </div>
                    @if($prescription->end_date)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('prescriptions.expires') }}:</span>
                        <span class="text-gray-900 {{ \Carbon\Carbon::parse($prescription->end_date)->isPast() ? 'text-red-600 font-semibold' : '' }}">
                            {{ \Carbon\Carbon::parse($prescription->end_date)->format('M j, Y') }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Related Records -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('prescriptions.related_records') }}</h3>
                <div class="space-y-2">
                    @if($prescription->medicalRecord)
                    <a href="{{ route('medical-records.show', $prescription->medicalRecord) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-file-medical mr-2"></i>{{ __('prescriptions.related_medical_record') }}
                    </a>
                    @endif
                    <a href="{{ route('prescriptions.index', ['patient_id' => $prescription->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-prescription-bottle mr-2"></i>{{ __('prescriptions.patient_prescriptions') }}
                    </a>
                    <a href="{{ route('medical-records.index', ['patient_id' => $prescription->patient_id]) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-file-medical mr-2"></i>{{ __('prescriptions.patient_medical_records') }}
                    </a>
                    <a href="{{ route('patients.show', $prescription->patient) }}" 
                       class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-user mr-2"></i>{{ __('prescriptions.patient_profile') }}
                    </a>
                </div>
            </div>

            <!-- Warning Messages -->
            @if($prescription->status == 'active' && $prescription->end_date && \Carbon\Carbon::parse($prescription->end_date)->isPast())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2 mt-1"></i>
                    <div>
                        <h4 class="text-red-800 font-semibold">{{ __('prescriptions.prescription_expired') }}</h4>
                        <p class="text-red-700 text-sm">{{ __('prescriptions.prescription_expired_on', ['date' => \Carbon\Carbon::parse($prescription->end_date)->format('M j, Y')]) }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
