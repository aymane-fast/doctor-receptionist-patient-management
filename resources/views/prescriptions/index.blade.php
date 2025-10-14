@extends('layouts.app')

@section('title', __('prescriptions.title'))

@section('content')
<div class="space-y-8">
    <!-- Pharmacy-Style Header -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-500/5 via-cyan-500/5 to-blue-500/5"></div>
        <div class="relative bg-white/90 backdrop-blur-xl rounded-3xl p-8 border border-white/20 shadow-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="relative">
                        <div class="w-20 h-20 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-3xl flex items-center justify-center shadow-xl">
                            <i class="fas fa-prescription-bottle text-white text-3xl"></i>
                        </div>
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-plus text-white text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-teal-700 via-cyan-700 to-blue-700 bg-clip-text text-transparent">
                            {{ __('prescriptions.title') }}
                        </h1>
                        <p class="text-gray-600 text-lg mt-2">Digital pharmacy management & prescription tracking</p>
                        <div class="flex items-center mt-3 space-x-4 text-sm text-gray-500">
                            <span class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span>{{ $prescriptions->where('status', 'active')->count() }} Active</span>
                            </span>
                            <span class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <span>{{ $prescriptions->where('status', 'completed')->count() }} Completed</span>
                            </span>
                            <span class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                <span>{{ $prescriptions->count() }} Total Records</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('prescriptions.create') }}" class="group relative overflow-hidden bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white px-8 py-4 rounded-2xl font-semibold transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                        <div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
                        <div class="relative flex items-center space-x-3">
                            <i class="fas fa-plus"></i>
                            <span>New Prescription</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filter Controls -->
    <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-gray-200/50 shadow-lg">
        <form method="GET" action="{{ route('prescriptions.index') }}" class="flex flex-col lg:flex-row items-center space-y-4 lg:space-y-0 lg:space-x-6">
            <div class="flex-1 w-full">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-user-injured text-teal-500"></i>
                    </div>
                    <select name="patient_id" onchange="this.form.submit()" 
                            class="block w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl bg-white/90 focus:outline-none focus:border-teal-500 focus:bg-white transition-all duration-200 text-gray-900">
                        <option value="">All Patients - Complete Prescription History</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->first_name }} {{ $patient->last_name }} • {{ $patient->patient_id }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if(request('patient_id'))
            <a href="{{ route('prescriptions.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-4 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg">
                <i class="fas fa-times"></i>
                <span>Clear Filter</span>
            </a>
            @endif
        </form>
    </div>

    <!-- Professional Prescription Table -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 overflow-hidden">
        @if($prescriptions->count() > 0)
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-teal-50 via-cyan-50 to-blue-50 border-b border-gray-200/70 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center space-x-3">
                        <i class="fas fa-pills text-teal-600"></i>
                        <span>Active Prescriptions Registry</span>
                    </h2>
                    <div class="text-sm text-gray-600 flex items-center space-x-2">
                        <i class="fas fa-database text-gray-400"></i>
                        <span>{{ $prescriptions->count() }} records found</span>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50/80 border-b border-gray-200/70">
                        <tr>
                            <th class="text-left py-4 px-6 font-semibold text-gray-700 text-sm">Patient & Doctor</th>
                            <th class="text-left py-4 px-6 font-semibold text-gray-700 text-sm">Medication Details</th>
                            <th class="text-left py-4 px-6 font-semibold text-gray-700 text-sm">Treatment Schedule</th>
                            <th class="text-left py-4 px-6 font-semibold text-gray-700 text-sm">Status & Dates</th>
                            <th class="text-center py-4 px-6 font-semibold text-gray-700 text-sm">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($prescriptions as $prescription)
                        <tr class="group hover:bg-gradient-to-r hover:from-teal-50/30 hover:to-transparent transition-all duration-300 border-b border-gray-100">
                            <!-- Patient & Doctor Column -->
                            <td class="py-6 px-6">
                                <div class="flex items-center space-x-4">
                                    <div class="relative">
                                        <div class="w-12 h-12 bg-gradient-to-br from-teal-100 to-cyan-100 rounded-2xl flex items-center justify-center group-hover:from-teal-200 group-hover:to-cyan-200 transition-colors">
                                            <i class="fas fa-user text-teal-600"></i>
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full border-2 border-gray-200 flex items-center justify-center">
                                            <i class="fas fa-prescription-bottle text-teal-500 text-xs"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-base">{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $prescription->patient->patient_id }}</p>
                                        <p class="text-xs text-gray-500 mt-1 flex items-center space-x-2">
                                            <i class="fas fa-user-md text-teal-500"></i>
                                            <span>Dr. {{ $prescription->doctor->name }}</span>
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Medication Details Column -->
                            <td class="py-6 px-6">
                                @if($prescription->items && $prescription->items->count() > 0)
                                    @php $firstMed = $prescription->items->first() @endphp
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $firstMed->medication_name }}</p>
                                        <p class="text-sm text-gray-500 mt-1">{{ $firstMed->dosage }}</p>
                                        @if($prescription->items->count() > 1)
                                            <p class="text-xs text-gray-400 mt-2">+{{ $prescription->items->count() - 1 }} more</p>
                                        @endif
                                    </div>
                                @else
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $prescription->medication_name ?: 'Medication' }}</p>
                                        @if($prescription->dosage)
                                            <p class="text-sm text-gray-500 mt-1">{{ $prescription->dosage }}</p>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Treatment Schedule Column -->
                            <td class="py-6 px-6 text-center">
                                @if($prescription->items && $prescription->items->count() > 0)
                                    @php $firstMed = $prescription->items->first() @endphp
                                    @if($firstMed->duration_days)
                                        <p class="font-semibold text-gray-900">{{ $firstMed->duration_days }} days</p>
                                    @endif
                                    <p class="text-sm text-gray-500 mt-1">{{ $firstMed->frequency }}</p>
                                @else
                                    @if($prescription->duration_days)
                                        <p class="font-semibold text-gray-900">{{ $prescription->duration_days }} days</p>
                                    @endif
                                    @if($prescription->frequency)
                                        <p class="text-sm text-gray-500 mt-1">{{ $prescription->frequency }}</p>
                                    @endif
                                @endif
                            </td>

                            <!-- Status & Dates Column -->
                            <td class="py-6 px-6 text-center">
                                <!-- Status Badge -->
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($prescription->status == 'active') bg-green-100 text-green-800
                                    @elseif($prescription->status == 'completed') bg-blue-100 text-blue-800
                                    @elseif($prescription->status == 'cancelled') bg-red-100 text-red-800
                                    @elseif($prescription->status == 'expired') bg-gray-100 text-gray-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($prescription->status ?: 'Active') }}
                                </span>
                                
                                <!-- Prescribed Date -->
                                <p class="font-medium text-gray-900 mt-3">{{ \Carbon\Carbon::parse($prescription->prescribed_date)->format('M j, Y') }}</p>
                                <p class="text-xs text-gray-500 mt-1">Prescribed</p>
                                
                                <!-- End Date if available -->
                                @if($prescription->items && $prescription->items->first() && $prescription->items->first()->duration_days)
                                    @php 
                                        $endDate = \Carbon\Carbon::parse($prescription->prescribed_date)->addDays($prescription->items->first()->duration_days);
                                    @endphp
                                    <p class="text-sm text-gray-600 mt-2">{{ $endDate->format('M j, Y') }}</p>
                                    <p class="text-xs text-gray-400">Est. end</p>
                                @endif
                            </td>

                            <!-- Actions Column -->
                            <td class="py-6 px-6">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('prescriptions.show', $prescription) }}" 
                                       class="w-9 h-9 bg-teal-100 hover:bg-teal-200 rounded-xl flex items-center justify-center transition-all duration-200 group/btn">
                                        <i class="fas fa-eye text-teal-600 text-sm group-hover/btn:scale-110 transition-transform"></i>
                                    </a>
                                    <a href="{{ route('prescriptions.edit', $prescription) }}" 
                                       class="w-9 h-9 bg-blue-100 hover:bg-blue-200 rounded-xl flex items-center justify-center transition-all duration-200 group/btn">
                                        <i class="fas fa-edit text-blue-600 text-sm group-hover/btn:scale-110 transition-transform"></i>
                                    </a>
                                    @if($prescription->medicalRecord)
                                    <a href="{{ route('medical-records.show', $prescription->medicalRecord) }}" 
                                       class="w-9 h-9 bg-purple-100 hover:bg-purple-200 rounded-xl flex items-center justify-center transition-all duration-200 group/btn" 
                                       title="View Medical Record">
                                        <i class="fas fa-file-medical text-purple-600 text-sm group-hover/btn:scale-110 transition-transform"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Table Footer with Pagination -->
            @if($prescriptions->hasPages())
            <div class="bg-gray-50/50 border-t border-gray-200/70 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Showing {{ $prescriptions->firstItem() }} to {{ $prescriptions->lastItem() }} of {{ $prescriptions->total() }} prescriptions
                    </div>
                    <div>
                        {{ $prescriptions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            @endif
        @else
            <!-- Professional Empty State -->
            <div class="text-center py-20">
                <div class="relative inline-block mb-8">
                    <div class="w-32 h-32 bg-gradient-to-br from-teal-100 via-cyan-100 to-blue-100 rounded-full flex items-center justify-center shadow-2xl">
                        <i class="fas fa-prescription-bottle text-teal-500 text-5xl"></i>
                    </div>
                    <div class="absolute -top-2 -right-2 w-12 h-12 bg-gradient-to-br from-orange-400 to-red-400 rounded-full flex items-center justify-center shadow-xl">
                        <i class="fas fa-plus text-white text-xl"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-800 mb-4">
                    @if(request('patient_id'))
                        No Prescriptions Found
                    @else
                        Digital Pharmacy Ready
                    @endif
                </h3>
                <p class="text-gray-600 text-lg mb-8 max-w-2xl mx-auto leading-relaxed">
                    @if(request('patient_id'))
                        This patient doesn't have any prescriptions in the system yet. Create their first prescription to start building their medication history.
                    @else
                        Your digital pharmacy management system is ready to handle prescriptions. Start by creating comprehensive medication records for your patients.
                    @endif
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                    @if(request('patient_id'))
                    <a href="{{ route('prescriptions.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-8 py-4 rounded-2xl font-semibold transition-all duration-300 flex items-center space-x-3 shadow-xl">
                        <i class="fas fa-arrow-left"></i>
                        <span>View All Prescriptions</span>
                    </a>
                    @endif
                    <a href="{{ route('prescriptions.create') }}" class="group relative overflow-hidden bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white px-8 py-4 rounded-2xl font-semibold transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                        <div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
                        <div class="relative flex items-center space-x-3">
                            <i class="fas fa-plus"></i>
                            <span>{{ request('patient_id') ? 'Create Prescription' : 'Create First Prescription' }}</span>
                        </div>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
