@extends('layouts.app')

@section('title', __('statistics.title'))

@section('content')
<div class="space-y-8">
    <x-ui.page-header
        :title="__('statistics.title')"
        :description="__('statistics.comprehensive_insights')"
        icon="fa-chart-line"
        :eyebrow="__($clinicSpecialty['label']).' '. __('intelligence')"
    >
        <x-slot:actions>
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ __('statistics.live_data') }}</span>
            </div>
            <button onclick="refreshData()" class="btn-primary px-5 py-3">
                <i class="fas fa-sync-alt"></i>
                <span>{{ __('statistics.refresh') }}</span>
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <!-- Quick Stats Grid -->
    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Patients -->
            <div class="metric-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">{{ __('statistics.total_patients') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-patients">{{ $statistics['total_patients'] ?? 0 }}</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium">+{{ $statistics['new_patients_this_month'] ?? 0 }} {{ __('statistics.this_month') }}</span>
                        </div>
                    </div>
                    <div class="metric-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Total Appointments -->
            <div class="metric-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">{{ __('statistics.total_appointments') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-appointments">{{ $statistics['total_appointments'] ?? 0 }}</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium">+{{ $statistics['appointments_this_week'] ?? 0 }} {{ __('statistics.this_week') }}</span>
                        </div>
                    </div>
                    <div class="metric-card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>

            <!-- Success Rate -->
            <div class="metric-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">{{ __('statistics.success_rate') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="success-rate">{{ $statistics['success_rate'] ?? '0' }}%</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium">{{ __('statistics.completed_appointments') }}</span>
                        </div>
                    </div>
                    <div class="metric-card-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="metric-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">{{ __('statistics.this_month_revenue') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="monthly-revenue">${{ number_format($statistics['this_month_revenue'] ?? 0, 2) }}</p>
                        <div class="flex items-center mt-2">
                            @if(($statistics['revenue_growth'] ?? 0) >= 0)
                                <span class="text-green-600 text-sm font-medium">+{{ $statistics['revenue_growth'] ?? 0 }}% {{ __('statistics.from_last_month') }}</span>
                            @else
                                <span class="text-red-600 text-sm font-medium">{{ $statistics['revenue_growth'] ?? 0 }}% {{ __('statistics.from_last_month') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="metric-card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Appointments Trend Chart -->
            <div class="analytics-card p-6 lg:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">{{ __('statistics.appointments_trend') }}</h3>
                    <div class="timeframe-switch" aria-label="{{ __('Trend timeframe') }}">
                        <button onclick="changeTimeframe('week')" class="timeframe-button is-active" id="week-btn">{{ __('statistics.week') }}</button>
                        <button onclick="changeTimeframe('month')" class="timeframe-button" id="month-btn">{{ __('statistics.month') }}</button>
                        <button onclick="changeTimeframe('year')" class="timeframe-button" id="year-btn">{{ __('statistics.year') }}</button>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="appointmentsTrendChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="analytics-card p-6 lg:p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">{{ __('statistics.appointment_status_distribution') }}</h3>
                <div class="h-80">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Revenue Calculator & Monthly Revenue Chart -->
            <div class="analytics-card p-6 lg:p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">{{ __('statistics.revenue_calculator') }}</h3>
                
                <!-- Calculator Section -->
                <div class="soft-panel mb-6 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <label for="consultationPrice" class="text-sm font-semibold text-gray-700">{{ __('statistics.consultation_price') }}</label>
                        <input type="number" id="consultationPrice" value="{{ $statistics['consultation_price'] ?? 50 }}" 
                               class="w-24 px-3 py-1 border border-gray-300 rounded-lg text-center font-bold"
                               step="any" aria-describedby="consultation-price-error" oninput="updateRevenue()">
                    </div>
                    <p id="consultation-price-error" class="hidden text-sm text-red-600 mb-3" role="alert">{{ __('statistics.valid_price') }}</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="text-center p-3 bg-white rounded-lg">
                            <p class="text-gray-600">{{ __('statistics.completed_this_month') }}</p>
                            <p class="text-xl font-bold text-emerald-700">{{ $statistics['completed_this_month'] ?? 0 }}</p>
                        </div>
                        <div class="text-center p-3 bg-white rounded-lg">
                            <p class="text-gray-600">{{ __('statistics.estimated_revenue') }}</p>
                            <p class="text-xl font-bold text-green-600" id="calculatedRevenue">${{ number_format(($statistics['completed_this_month'] ?? 0) * ($statistics['consultation_price'] ?? 50), 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Monthly Revenue Chart -->
                <div class="h-48">
                    <h4 class="text-lg font-semibold text-gray-700 mb-3">{{ __('statistics.twelve_month_revenue_trend') }}</h4>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Peak Hours -->
            <div class="analytics-card p-6 lg:p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">{{ __('statistics.peak_hours_analysis') }}</h3>
                <div class="h-64">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>

            <!-- Patient Demographics -->
            <div class="analytics-card p-6 lg:p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">{{ __('statistics.patient_demographics') }}</h3>
                <div class="space-y-4">
                    @foreach($statistics['age_groups'] ?? [] as $group)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">{{ $group['range'] }}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full gradient-bg rounded-full transition-all duration-500" style="width: {{ $group['percentage'] }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-8">{{ $group['count'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Doctor Performance -->
    <div>
        <div class="analytics-card p-6 lg:p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6">
                <i class="fas fa-user-md text-teal-600 mr-2"></i>{{ __('statistics.doctor_performance') }}
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="text-left text-xs font-bold text-gray-500 uppercase">
                            <th class="px-4 py-3">{{ __('appointments.doctor') }}</th>
                            <th class="px-4 py-3">{{ __('Mode') }}</th>
                            <th class="px-4 py-3">{{ __('statistics.total_appointments') }}</th>
                            <th class="px-4 py-3">{{ __('appointments.completed') }}</th>
                            <th class="px-4 py-3">{{ __('appointments.cancelled') }}</th>
                            <th class="px-4 py-3">{{ __('appointments.no_show') }}</th>
                            <th class="px-4 py-3">{{ __('statistics.no_show_rate') }}</th>
                            <th class="px-4 py-3">{{ __('statistics.estimated_revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($statistics['doctor_performance'] ?? [] as $doctor)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $doctor['name'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($doctor['mode']) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $doctor['total'] }}</td>
                            <td class="px-4 py-3 text-sm text-emerald-700 font-semibold">{{ $doctor['completed'] }}</td>
                            <td class="px-4 py-3 text-sm text-red-600">{{ $doctor['cancelled'] }}</td>
                            <td class="px-4 py-3 text-sm text-orange-600">{{ $doctor['no_show'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $doctor['no_show_rate'] }}%</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ number_format($doctor['revenue'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Top Patients -->
    <div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Activity -->
            <div class="analytics-card p-6 lg:p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">{{ __('statistics.recent_activity') }}</h3>
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @foreach($statistics['recent_activities'] ?? [] as $activity)
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="w-8 h-8 gradient-bg rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="{{ $activity['icon'] }} text-white text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $activity['title'] }}</p>
                            <p class="text-sm text-gray-600">{{ $activity['description'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Patients -->
            <div class="analytics-card p-6 lg:p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">{{ __('statistics.most_active_patients') }}</h3>
                <div class="space-y-4">
                    @foreach($statistics['top_patients'] ?? [] as $index => $patient)
                    <div class="flex items-center justify-between p-4 soft-panel">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 gradient-bg rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-xs">#{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $patient['name'] }}</p>
                                <p class="text-sm text-gray-600">{{ $patient['phone'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-emerald-700">{{ $patient['visits'] }}</p>
                            <p class="text-xs text-gray-500">{{ __('visits') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Chart configurations and initialization
let appointmentsTrendChart, statusChart, peakHoursChart, revenueChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Appointments Trend Chart
    const ctx1 = document.getElementById('appointmentsTrendChart').getContext('2d');
    appointmentsTrendChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: {!! json_encode($statistics['trend_labels'] ?? []) !!},
            datasets: [{
                label: 'Appointments',
                data: {!! json_encode($statistics['trend_data'] ?? []) !!},
                borderColor: '#126e64',
                backgroundColor: 'rgba(18, 110, 100, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#126e64',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Status Distribution Chart
    const ctx2 = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: [@json(__('Completed')), @json(__('Scheduled')), @json(__('In Progress')), @json(__('Cancelled'))],
            datasets: [{
                data: [
                    {{ $statistics['completed_appointments'] ?? 0 }},
                    {{ $statistics['scheduled_appointments'] ?? 0 }},
                    {{ $statistics['in_progress_appointments'] ?? 0 }},
                    {{ $statistics['cancelled_appointments'] ?? 0 }}
                ],
                backgroundColor: [
                    '#16805f',
                    '#326b85',
                    '#d89745',
                    '#b74242'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });

    // Peak Hours Chart
    const ctx3 = document.getElementById('peakHoursChart').getContext('2d');
    peakHoursChart = new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: ['8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM'],
            datasets: [{
                label: 'Appointments',
                data: {!! json_encode($statistics['hourly_distribution'] ?? []) !!},
                backgroundColor: 'rgba(18, 110, 100, 0.78)',
                borderColor: '#126e64',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Revenue Chart
    const ctx4 = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(ctx4, {
        type: 'line',
        data: {
            labels: {!! json_encode($statistics['monthly_revenue_labels'] ?? []) !!},
            datasets: [{
                label: 'Revenue ($)',
                data: {!! json_encode($statistics['monthly_revenue'] ?? []) !!},
                borderColor: '#16805f',
                backgroundColor: 'rgba(22, 128, 95, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#16805f',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Revenue Calculator Functions
function updateRevenue() {
    const input = document.getElementById('consultationPrice');
    const price = input.value === '' ? 0 : Number(input.value);
    const completed = {{ $statistics['completed_this_month'] ?? 0 }};
    const invalid = !input.validity.valid || !Number.isFinite(price) || !Number.isFinite(price * completed);
    input.setAttribute('aria-invalid', String(invalid));
    document.getElementById('consultation-price-error').classList.toggle('hidden', !invalid);
    if (invalid) return;
    const revenue = price * completed;
    document.getElementById('calculatedRevenue').textContent = '$' + revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function changeTimeframe(timeframe) {
    document.querySelectorAll('[id$="-btn"]').forEach(btn => {
        btn.classList.remove('is-active');
    });

    document.getElementById(timeframe + '-btn').classList.add('is-active');
    updateChartData(timeframe);
}

function updateChartData(timeframe) {
    // Show loading state
    appointmentsTrendChart.data.datasets[0].data = [];
    appointmentsTrendChart.update();
    
    // Fetch real data from server
    fetch(`/api/statistics/trend?timeframe=${timeframe}`)
        .then(response => response.json())
        .then(data => {
            appointmentsTrendChart.data.labels = data.labels;
            appointmentsTrendChart.data.datasets[0].data = data.data;
            appointmentsTrendChart.update('active');
        })
        .catch(error => {
            console.error('Error fetching chart data:', error);
            // Fallback to default data on error
            appointmentsTrendChart.data.labels = {!! json_encode($statistics['trend_labels'] ?? []) !!};
            appointmentsTrendChart.data.datasets[0].data = {!! json_encode($statistics['trend_data'] ?? []) !!};
            appointmentsTrendChart.update();
        });
}

function refreshData() {
    const icon = document.querySelector('[onclick="refreshData()"] i');
    icon?.classList.add('animate-spin');
    window.location.reload();
}
</script>

<style>
.chart-container {
    position: relative;
    height: 300px;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animation for number counting */
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-count {
    animation: countUp 0.6s ease-out;
}
</style>
@endsection
