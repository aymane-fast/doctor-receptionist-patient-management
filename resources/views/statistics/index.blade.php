@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-6">
    <!-- Header Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="bg-white rounded-3xl shadow-2xl p-8 backdrop-blur-lg border border-white/20">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            {{ __('statistics.title') }}
                        </h1>
                        <p class="text-gray-600 text-lg mt-2">{{ __('statistics.comprehensive_insights') }}</p>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 flex items-center space-x-4">
                    <div class="bg-gradient-to-r from-green-100 to-emerald-100 px-4 py-2 rounded-xl">
                        <span class="text-green-800 font-semibold">{{ __('statistics.live_data') }}</span>
                        <div class="w-2 h-2 bg-green-500 rounded-full inline-block ml-2 animate-pulse"></div>
                    </div>
                    <button onclick="refreshData()" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>{{ __('statistics.refresh') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Patients -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-white/20 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">{{ __('statistics.total_patients') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-patients">{{ $statistics['total_patients'] ?? 0 }}</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium">+{{ $statistics['new_patients_this_month'] ?? 0 }} {{ __('statistics.this_month') }}</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-white"></i>
                    </div>
                </div>
            </div>

            <!-- Total Appointments -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-white/20 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">{{ __('statistics.total_appointments') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="total-appointments">{{ $statistics['total_appointments'] ?? 0 }}</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium">+{{ $statistics['appointments_this_week'] ?? 0 }} {{ __('statistics.this_week') }}</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                </div>
            </div>

            <!-- Success Rate -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-white/20 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">{{ __('statistics.success_rate') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" id="success-rate">{{ $statistics['success_rate'] ?? '0' }}%</p>
                        <div class="flex items-center mt-2">
                            <span class="text-green-600 text-sm font-medium">{{ __('statistics.completed_appointments') }}</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-trophy text-white"></i>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border border-white/20 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
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
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Appointments Trend Chart -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-white/20">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">{{ __('statistics.appointments_trend') }}</h3>
                    <div class="flex space-x-2">
                        <button onclick="changeTimeframe('week')" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-200 transition-colors" id="week-btn">{{ __('statistics.week') }}</button>
                        <button onclick="changeTimeframe('month')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors" id="month-btn">{{ __('statistics.month') }}</button>
                        <button onclick="changeTimeframe('year')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors" id="year-btn">{{ __('statistics.year') }}</button>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="appointmentsTrendChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-white/20">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ __('statistics.appointment_status_distribution') }}</h3>
                <div class="h-80">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Revenue Calculator & Monthly Revenue Chart -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-white/20">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ __('statistics.revenue_calculator') }}</h3>
                
                <!-- Calculator Section -->
                <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                    <div class="flex items-center justify-between mb-4">
                        <label class="text-sm font-semibold text-gray-700">{{ __('statistics.consultation_price') }}</label>
                        <input type="number" id="consultationPrice" value="{{ $statistics['consultation_price'] ?? 50 }}" 
                               class="w-24 px-3 py-1 border border-gray-300 rounded-lg text-center font-bold"
                               onchange="updateRevenue()">
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="text-center p-3 bg-white rounded-lg">
                            <p class="text-gray-600">{{ __('statistics.completed_this_month') }}</p>
                            <p class="text-xl font-bold text-blue-600">{{ $statistics['completed_appointments'] ?? 0 }}</p>
                        </div>
                        <div class="text-center p-3 bg-white rounded-lg">
                            <p class="text-gray-600">{{ __('statistics.estimated_revenue') }}</p>
                            <p class="text-xl font-bold text-green-600" id="calculatedRevenue">${{ number_format(($statistics['completed_appointments'] ?? 0) * ($statistics['consultation_price'] ?? 50), 2) }}</p>
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
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-white/20">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ __('statistics.peak_hours_analysis') }}</h3>
                <div class="h-64">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>

            <!-- Patient Demographics -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-white/20">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ __('statistics.patient_demographics') }}</h3>
                <div class="space-y-4">
                    @foreach($statistics['age_groups'] ?? [] as $group)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">{{ $group['range'] }}</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-500" style="width: {{ $group['percentage'] }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-8">{{ $group['count'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Top Patients -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Activity -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-white/20">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ __('statistics.recent_activity') }}</h3>
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @foreach($statistics['recent_activities'] ?? [] as $activity)
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="w-8 h-8 bg-gradient-to-r {{ $activity['color'] }} rounded-full flex items-center justify-center flex-shrink-0">
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
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-white/20">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ __('statistics.most_active_patients') }}</h3>
                <div class="space-y-4">
                    @foreach($statistics['top_patients'] ?? [] as $index => $patient)
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl hover:from-blue-50 hover:to-purple-50 transition-all duration-300">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-xs">#{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $patient['name'] }}</p>
                                <p class="text-sm text-gray-600">{{ $patient['phone'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-blue-600">{{ $patient['visits'] }}</p>
                            <p class="text-xs text-gray-500">visits</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>

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
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(59, 130, 246)',
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
            labels: ['Completed', 'Scheduled', 'In Progress', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $statistics['completed_appointments'] ?? 0 }},
                    {{ $statistics['scheduled_appointments'] ?? 0 }},
                    {{ $statistics['in_progress_appointments'] ?? 0 }},
                    {{ $statistics['cancelled_appointments'] ?? 0 }}
                ],
                backgroundColor: [
                    '#10B981',
                    '#3B82F6', 
                    '#F59E0B',
                    '#EF4444'
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
                backgroundColor: 'rgba(139, 92, 246, 0.8)',
                borderColor: 'rgb(139, 92, 246)',
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
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(34, 197, 94)',
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
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Revenue Calculator Functions
function updateRevenue() {
    const price = parseFloat(document.getElementById('consultationPrice').value) || 0;
    const completed = {{ $statistics['completed_appointments'] ?? 0 }};
    const revenue = price * completed;
    document.getElementById('calculatedRevenue').textContent = '$' + revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function changeTimeframe(timeframe) {
    // Remove active class from all buttons
    document.querySelectorAll('[id$="-btn"]').forEach(btn => {
        btn.classList.remove('bg-blue-100', 'text-blue-700');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    
    // Add active class to selected button
    document.getElementById(timeframe + '-btn').classList.remove('bg-gray-100', 'text-gray-700');
    document.getElementById(timeframe + '-btn').classList.add('bg-blue-100', 'text-blue-700');
    
    // Here you would typically make an AJAX call to get new data
    // For demo purposes, we'll just update the chart with mock data
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
    // Add loading animation
    document.querySelector('[onclick="refreshData()"] i').classList.add('animate-spin');
    
    // Simulate data refresh
    setTimeout(() => {
        document.querySelector('[onclick="refreshData()"] i').classList.remove('animate-spin');
        
        // Here you would typically make an AJAX call to refresh all data
        // For demo, we'll just show a success message
        showNotification('Data refreshed successfully!', 'success');
    }, 1500);
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Auto-refresh every 5 minutes
setInterval(refreshData, 300000);
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