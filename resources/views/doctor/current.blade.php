@extends('layouts.app')

@section('title', 'Current Patient Workspace')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-user-md text-blue-600 mr-2"></i>
            Current Patient Workspace
        </h1>
        <div class="space-x-2">
            <a href="{{ route('dashboard') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded">Back to Dashboard</a>
        </div>
    </div>

    @if(!$currentAppointment)
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <i class="fas fa-user-clock text-gray-400 text-4xl mb-3"></i>
            <div class="text-gray-700">No current patient assigned for today.</div>
        </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Patient Overview -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-500 mb-1">Patient</div>
                <div class="text-xl font-semibold text-gray-900">{{ $patient->full_name }}</div>
                <div class="text-gray-600">{{ $patient->patient_id }}</div>
                <div class="text-gray-600 mt-2">Age: {{ $patient->age }} • {{ ucfirst($patient->gender) }}</div>
                <div class="text-gray-600 mt-1">Time: {{ $currentAppointment->appointment_time->format('g:i A') }}</div>
                <div class="mt-4 space-x-2">
                    <a href="{{ route('patients.show', $patient) }}" class="text-blue-600 hover:text-blue-800">View Full History</a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-500 mb-2">Quick Actions</div>
                <div class="space-x-2">
                    <a href="{{ route('medical-records.create', ['patient_id' => $patient->id, 'appointment_id' => $currentAppointment->id]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">New Record</a>
                    <a href="{{ route('prescriptions.create', ['patient_id' => $patient->id]) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">Prescription</a>
                </div>
                <form action="{{ route('appointments.mark-current-done') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">Mark Visit Done & Next</button>
                </form>
            </div>
        </div>

        <!-- Timeline and Recent Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-history text-blue-600 mr-2"></i>Recent Appointments</h2>
                @php($recentAppointments = $patient->appointments()->with('doctor')->latest('appointment_date')->limit(5)->get())
                @if($recentAppointments->count())
                    <ul class="divide-y divide-gray-200">
                        @foreach($recentAppointments as $ap)
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">{{ $ap->appointment_date->format('M j, Y') }} • {{ $ap->appointment_time->format('g:i A') }}</div>
                                <div class="text-sm text-gray-600">Dr. {{ $ap->doctor->name }} • Status: {{ ucfirst($ap->status) }}</div>
                            </div>
                            <a href="{{ route('appointments.show', $ap) }}" class="text-blue-600 hover:text-blue-800">View</a>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-gray-600">No recent appointments.</div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-file-medical text-green-600 mr-2"></i>Recent Medical Records</h2>
                @php($recentRecords = $patient->medicalRecords()->with('doctor')->latest('visit_date')->limit(5)->get())
                @if($recentRecords->count())
                    <ul class="divide-y divide-gray-200">
                        @foreach($recentRecords as $rec)
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">{{ $rec->visit_date->format('M j, Y') }}</div>
                                <div class="text-sm text-gray-600">Dr. {{ $rec->doctor->name }}</div>
                            </div>
                            <a href="{{ route('medical-records.show', $rec) }}" class="text-green-600 hover:text-green-800">View</a>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-gray-600">No records yet.</div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-prescription-bottle-alt text-purple-600 mr-2"></i>Recent Prescriptions</h2>
                @php($recentRx = $patient->prescriptions()->with('doctor')->latest('prescribed_date')->limit(5)->get())
                @if($recentRx->count())
                    <ul class="divide-y divide-gray-200">
                        @foreach($recentRx as $rx)
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">{{ $rx->medication_name }} ({{ $rx->dosage }})</div>
                                <div class="text-sm text-gray-600">{{ $rx->prescribed_date->format('M j, Y') }} • Dr. {{ $rx->doctor->name }}</div>
                            </div>
                            <a href="{{ route('prescriptions.show', $rx) }}" class="text-purple-600 hover:text-purple-800">View</a>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-gray-600">No prescriptions yet.</div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection


