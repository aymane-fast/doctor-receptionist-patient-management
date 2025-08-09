@extends('layouts.app')

@section('title', 'Appointments')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-calendar text-blue-600 mr-2"></i>
                    Appointments
                </h1>
                <p class="text-gray-600 mt-1">Manage patient appointments and scheduling</p>
            </div>
            <a href="{{ route('appointments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Schedule Appointment
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('appointments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input type="date" 
                       id="date" 
                       name="date" 
                       value="{{ request('date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" 
                        name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            @if(auth()->user()->isReceptionist())
            <div>
                <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-2">Doctor</label>
                <select id="doctor_id" 
                        name="doctor_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                        Dr. {{ $doctor->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                @if(request()->query())
                <a href="{{ route('appointments.index') }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Appointments List -->
    <div class="bg-white rounded-lg shadow">
        @if($appointments->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($appointments as $appointment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $appointment->patient->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $appointment->patient->patient_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Dr. {{ $appointment->doctor->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $appointment->appointment_date->format('M j, Y') }}<br>
                                <span class="text-gray-500">{{ $appointment->appointment_time->format('g:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($appointment->status == 'scheduled') bg-yellow-100 text-yellow-800
                                        @elseif($appointment->status == 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($appointment->status == 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                    
                                    <!-- Quick Status Update -->
                                    <div class="relative group">
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <div class="absolute right-0 top-6 bg-white border border-gray-200 rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="scheduled">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                                                    Scheduled
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                                                    In Progress
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                                                    Completed
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('appointments.update-status', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                                                    Cancelled
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $appointment->reason ? Str::limit($appointment->reason, 50) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('appointments.show', $appointment) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('appointments.edit', $appointment) }}" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->user()->isDoctor() && $appointment->status == 'completed' && !$appointment->medicalRecord)
                                    <a href="{{ route('medical-records.create', ['appointment_id' => $appointment->id]) }}" 
                                       class="text-purple-600 hover:text-purple-900" 
                                       title="Add Medical Record">
                                        <i class="fas fa-file-medical"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $appointments->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-calendar-times text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No appointments found</h3>
                <p class="text-gray-500 mb-6">
                    @if(request()->query())
                        No appointments match your filter criteria.
                    @else
                        Get started by scheduling your first appointment.
                    @endif
                </p>
                <a href="{{ route('appointments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i>Schedule Appointment
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Auto-hide dropdown menus when clicking outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.group');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove('hover');
        }
    });
});
</script>
@endpush
@endsection
