@extends('layouts.app')

@section('title', __('appointments.appointment_details'))

@section('content')
<div class="space-y-8">
    <!-- Modern Header -->
    <div class="glass-effect rounded-3xl p-8 modern-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center animate-float">
                    <i class="fas fa-calendar-check text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <span class="text-gradient">{{ __('appointments.appointment_details') }}</span>
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">{{ __('appointments.appointment_details_comprehensive') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('appointments.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('appointments.back_to_list') }}</span>
                </a>
                <a href="{{ route('appointments.edit', $appointment) }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-2xl font-medium transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-edit"></i>
                    <span>{{ __('appointments.edit') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Appointment Info -->
        <div class="lg:col-span-2 space-y-8">
            <div class="glass-effect rounded-3xl p-8 modern-shadow">
                <!-- Patient Info -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('appointments.patient_information') }}</h2>
                    </div>
                    <div class="flex items-center space-x-6 p-6 bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-user text-blue-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</h3>
                            <div class="flex flex-wrap gap-4 mt-2 text-gray-600">
                                <span class="flex items-center space-x-1">
                                    <i class="fas fa-phone text-sm"></i>
                                    <span>{{ $appointment->patient->phone }}</span>
                                </span>
                                @if($appointment->patient->email)
                                <span class="flex items-center space-x-1">
                                    <i class="fas fa-envelope text-sm"></i>
                                    <span>{{ $appointment->patient->email }}</span>
                                </span>
                                @endif
                                <span class="flex items-center space-x-1">
                                    <i class="fas fa-birthday-cake text-sm"></i>
                                    <span>{{ $appointment->patient->age }} years old</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doctor Info -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-md text-white"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('appointments.doctor_information') }}</h2>
                    </div>
                    <div class="flex items-center space-x-6 p-6 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-2xl">
                        <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-user-md text-emerald-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">Dr. {{ $appointment->doctor->name }}</h3>
                            <div class="flex items-center space-x-1 mt-2 text-gray-600">
                                <i class="fas fa-envelope text-sm"></i>
                                <span>{{ $appointment->doctor->email }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointment Details -->
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-info text-white"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('appointments.appointment_details') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider">{{ __('appointments.date') }}</label>
                            <p class="text-xl font-bold text-gray-900 bg-gray-50 p-4 rounded-2xl">
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->locale(app()->getLocale())->isoFormat('dddd, D MMMM YYYY') }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider">{{ __('appointments.time') }}</label>
                            <p class="text-xl font-bold text-gray-900 bg-gray-50 p-4 rounded-2xl flex items-center space-x-2">
                                <i class="fas fa-clock text-gray-400"></i>
                                <span>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider">{{ __('appointments.status') }}</label>
                            <div class="bg-gray-50 p-4 rounded-2xl">
                                <span class="inline-flex items-center px-4 py-2 rounded-2xl text-base font-bold
                                    @if($appointment->status == 'scheduled') bg-yellow-100 text-yellow-800
                                    @elseif($appointment->status == 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($appointment->status == 'completed') bg-green-100 text-green-800
                                    @elseif($appointment->status == 'cancelled') bg-red-100 text-red-800
                                    @elseif($appointment->status == 'no-show') bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('-', ' ', $appointment->status)) }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider">Type</label>
                            <p class="text-xl font-bold text-gray-900 bg-gray-50 p-4 rounded-2xl">
                                {{ ucfirst(str_replace('-', ' ', $appointment->appointment_type)) }}
                            </p>
                        </div>

                        @if($appointment->purpose)
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider">Purpose</label>
                            <p class="text-lg text-gray-900 bg-gray-50 p-4 rounded-2xl leading-relaxed">{{ $appointment->purpose }}</p>
                        </div>
                        @endif

                        @if($appointment->notes)
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider">{{ __('appointments.notes') }}</label>
                            <p class="text-lg text-gray-900 bg-gray-50 p-4 rounded-2xl leading-relaxed">{{ $appointment->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-8">
            <!-- Quick Actions -->
            <div class="glass-effect rounded-3xl p-8 modern-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bolt text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">{{ __('appointments.quick_actions') }}</h3>
                </div>
                <div class="space-y-4">
                    @if($appointment->status != 'completed' && $appointment->status != 'cancelled')
                        <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="inline w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-6 py-4 rounded-2xl font-medium transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-check"></i>
                                <span>{{ __('appointments.mark_completed') }}</span>
                            </button>
                        </form>
                    @endif

                    @if($appointment->status == 'scheduled')
                        <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="inline w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-4 rounded-2xl font-medium transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ __('appointments.confirm_appointment') }}</span>
                            </button>
                        </form>
                    @endif

                    @if($appointment->status != 'cancelled' && $appointment->status != 'completed')
                        <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="inline w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-6 py-4 rounded-2xl font-medium transition-all duration-200 flex items-center justify-center space-x-2"
                                    onclick="return confirm('{{ __('appointments.cancel_confirmation') }}')">>
                                <i class="fas fa-times"></i>
                                <span>{{ __('appointments.cancel_appointment') }}</span>
                            </button>
                        </form>
                    @endif

                    @if(auth()->user()->isDoctor() && $appointment->status == 'completed')
                        <a href="{{ route('medical-records.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}" 
                           class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-6 py-4 rounded-2xl font-medium transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-file-medical"></i>
                            <span>{{ __('appointments.add_medical_record') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Appointment Info -->
            <div class="glass-effect rounded-3xl p-8 modern-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-info-circle text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">{{ __('appointments.appointment_info') }}</h3>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">{{ __('appointments.created') }}:</span>
                        <span class="text-gray-900 font-semibold">{{ $appointment->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">{{ __('appointments.last_updated') }}:</span>
                        <span class="text-gray-900 font-semibold">{{ $appointment->updated_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3">
                        <span class="text-gray-500 font-medium">{{ __('appointments.appointment_id') }}:</span>
                        <span class="text-gray-900 font-semibold">#{{ $appointment->id }}</span>
                    </div>
                </div>
            </div>

            <!-- Related Records -->
            @if(auth()->user()->isDoctor())
            <div class="glass-effect rounded-3xl p-8 modern-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-link text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">{{ __('appointments.related_records') }}</h3>
                </div>
                <div class="space-y-3">
                    <a href="{{ route('medical-records.index', ['patient_id' => $appointment->patient_id]) }}" 
                       class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl hover:from-blue-100 hover:to-blue-200 transition-all duration-200 group">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-file-medical text-blue-600 group-hover:scale-110 transition-transform"></i>
                            <span class="text-blue-600 font-medium">{{ __('appointments.medical_records') }}</span>
                        </div>
                        <i class="fas fa-arrow-right text-blue-400 group-hover:text-blue-600"></i>
                    </a>
                    <a href="{{ route('prescriptions.index', ['patient_id' => $appointment->patient_id]) }}" 
                       class="flex items-center justify-between p-4 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-2xl hover:from-emerald-100 hover:to-emerald-200 transition-all duration-200 group">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-prescription-bottle text-emerald-600 group-hover:scale-110 transition-transform"></i>
                            <span class="text-emerald-600 font-medium">{{ __('appointments.prescriptions') }}</span>
                        </div>
                        <i class="fas fa-arrow-right text-emerald-400 group-hover:text-emerald-600"></i>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
