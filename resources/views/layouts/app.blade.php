<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('common.medical_management_system'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'header-gray': '#f5f5f5',
                        'banner-gradient': 'linear-gradient(135deg, #667eea 0%,rgb(0, 6, 58) 100%)',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom styles for modern dashboard */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(0px);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .modern-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .deep-shadow {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.6); }
        }
        
        .animate-pulse-glow {
            animation: pulse-glow 3s ease-in-out infinite;
        }
        
        .header-shadow {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .font-professional {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans">
    <div class="min-h-screen">
        <!-- Fixed Header -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-header-gray header-shadow">
            <!-- Top Navigation Bar -->
            <nav class="bg-header-gray border-b border-gray-200 font-professional">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="flex justify-between items-center py-3">
                        <!-- Left Section: Logo and Navigation -->
                        <div class="flex items-center space-x-6">
                            <!-- Logo -->
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-heartbeat text-white text-xl"></i>
                                </div>
                                <h1 class="text-xl font-bold text-gray-900">Medical Management</h1>
                            </div>
                            
                            <!-- Navigation Links -->
                            @auth
                            <div class="hidden md:flex items-center space-x-1">
                                <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-3 py-2 rounded-lg hover:bg-gray-200 transition-colors {{ request()->routeIs('dashboard') ? 'bg-gray-200 text-blue-600' : 'text-gray-700' }}">
                                    <i class="fas fa-tachometer-alt text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.dashboard') }}</span>
                                </a>
                                <a href="{{ route('patients.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg hover:bg-gray-200 transition-colors {{ request()->routeIs('patients.*') ? 'bg-gray-200 text-blue-600' : 'text-gray-700' }}">
                                    <i class="fas fa-users text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.patients') }}</span>
                                </a>
                                <a href="{{ route('appointments.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg hover:bg-gray-200 transition-colors {{ request()->routeIs('appointments.*') ? 'bg-gray-200 text-blue-600' : 'text-gray-700' }}">
                                    <i class="fas fa-calendar text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.appointments') }}</span>
                                </a>
                                @if(auth()->user()->isDoctor())
                                <a href="{{ route('doctor.current') }}" class="flex flex-col items-center px-3 py-2 rounded-lg hover:bg-gray-200 transition-colors {{ request()->routeIs('doctor.current') ? 'bg-gray-200 text-blue-600' : 'text-gray-700' }}">
                                    <i class="fas fa-user-clock text-lg mb-1"></i>
                                    <span class="text-xs font-medium">Current</span>
                                </a>
                                <a href="{{ route('medical-records.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg hover:bg-gray-200 transition-colors {{ request()->routeIs('medical-records.*') ? 'bg-gray-200 text-blue-600' : 'text-gray-700' }}">
                                    <i class="fas fa-file-medical text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.medical_records') }}</span>
                                </a>
                                <a href="{{ route('prescriptions.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg hover:bg-gray-200 transition-colors {{ request()->routeIs('prescriptions.*') ? 'bg-gray-200 text-blue-600' : 'text-gray-700' }}">
                                    <i class="fas fa-prescription text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.prescriptions') }}</span>
                                </a>
                                @endif
                                <a href="{{ route('settings.index') }}" class="flex flex-col items-center px-3 py-2 rounded-lg hover:bg-gray-200 transition-colors {{ request()->routeIs('settings.*') ? 'bg-gray-200 text-blue-600' : 'text-gray-700' }}">
                                    <i class="fas fa-cog text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.settings') }}</span>
                                </a>
                            </div>
                            @endauth
                        </div>

                        <!-- Right Section: User Actions -->
                        @auth
                        <div class="flex items-center space-x-4">
                            <!-- Today's Stats -->
                            <div class="hidden lg:flex items-center space-x-4">
                                <!-- Today's Appointments -->
                                <div class="flex items-center space-x-2 bg-white rounded-lg px-3 py-2 shadow-sm">
                                    <i class="fas fa-calendar-day text-blue-600"></i>
                                    <span class="text-sm font-medium text-gray-700">Today</span>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded-full">
                                        {{ \App\Models\Appointment::whereDate('appointment_date', today())->count() }}
                                    </span>
                                </div>

                                <!-- Pending Appointments -->
                                @php
                                    $pendingCount = \App\Models\Appointment::where('status', 'scheduled')
                                        ->whereDate('appointment_date', today())
                                        ->count();
                                @endphp
                                @if($pendingCount > 0)
                                <div class="flex items-center space-x-2 bg-orange-50 rounded-lg px-3 py-2 shadow-sm">
                                    <i class="fas fa-clock text-orange-600"></i>
                                    <span class="text-sm font-medium text-gray-700">Waiting</span>
                                    <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded-full">{{ $pendingCount }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- Quick Actions -->
                            <div class="flex items-center space-x-2">
                                <!-- Quick Add Patient -->
                                <a href="{{ route('patients.create') }}" 
                                   class="p-2 text-gray-600 hover:text-green-600 transition-colors" 
                                   title="{{ __('patients.add_new_patient') }}">
                                    <i class="fas fa-user-plus text-lg"></i>
                                </a>
                                
                                <!-- Quick Add Appointment -->
                                <a href="{{ route('appointments.create') }}" 
                                   class="p-2 text-gray-600 hover:text-blue-600 transition-colors" 
                                   title="{{ __('appointments.new_appointment') }}">
                                    <i class="fas fa-calendar-plus text-lg"></i>
                                </a>

                                <!-- Settings -->
                                <a href="{{ route('settings.index') }}" 
                                   class="p-2 text-gray-600 hover:text-purple-600 transition-colors" 
                                   title="{{ __('common.settings') }}">
                                    <i class="fas fa-cog text-lg"></i>
                                </a>
                            </div>

                            <!-- User Profile with Dynamic Status -->
                            <div class="flex items-center space-x-3">
                                <!-- Language Switcher -->
                                <div class="relative">
                                    <button onclick="toggleLanguageMenu()" class="flex items-center space-x-2 px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">
                                        <span class="text-lg">{{ language_flag(app()->getLocale()) }}</span>
                                        <span class="text-sm font-medium">{{ strtoupper(app()->getLocale()) }}</span>
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </button>
                                    <div id="language-menu" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border hidden z-50">
                                        @foreach(\App\Models\Setting::getAvailableLanguages() as $code => $name)
                                            <a href="{{ route('language.switch', $code) }}" 
                                               class="flex items-center space-x-2 px-4 py-2 text-sm hover:bg-gray-100 {{ app()->getLocale() == $code ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                                <span>{{ language_flag($code) }}</span>
                                                <span>{{ $name }}</span>
                                                @if(app()->getLocale() == $code)
                                                    <i class="fas fa-check text-blue-600 ml-auto"></i>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="text-right hidden sm:block">
                                    <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500 flex items-center">
                                        @if(auth()->user()->isDoctor())
                                            <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                                            Available
                                        @else
                                            <span class="w-2 h-2 bg-blue-400 rounded-full mr-1"></span>
                                            {{ ucfirst(auth()->user()->role) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                                
                                <!-- User Menu Dropdown -->
                                <div class="relative">
                                    <button class="text-gray-600 hover:text-blue-600 transition-colors" onclick="toggleUserMenu()">
                                        <i class="fas fa-chevron-down text-sm"></i>
                                    </button>
                                    <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border hidden">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('common.profile_settings') }}</a>
                                        <form method="POST" action="{{ route('logout') }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                <i class="fas fa-sign-out-alt mr-2"></i>{{ __('auth.logout') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endauth

                        <!-- Mobile Menu Button -->
                        <div class="md:hidden">
                            <button id="mobile-menu-btn" class="p-2 text-gray-600 hover:text-blue-600 transition-colors">
                                <i class="fas fa-bars text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden bg-white border-t border-gray-200 shadow-lg">
                <div class="px-4 py-3 space-y-2">
                    @auth
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 mr-3"></i>{{ __('common.dashboard') }}
                    </a>
                    <a href="{{ route('patients.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('patients.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-users w-5 mr-3"></i>{{ __('common.patients') }}
                    </a>
                    <a href="{{ route('appointments.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('appointments.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-calendar w-5 mr-3"></i>{{ __('common.appointments') }}
                    </a>
                    @if(auth()->user()->isDoctor())
                    <a href="{{ route('doctor.current') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('doctor.current') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-user-clock w-5 mr-3"></i>Current Patient
                    </a>
                    <a href="{{ route('medical-records.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('medical-records.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-file-medical w-5 mr-3"></i>{{ __('common.medical_records') }}
                    </a>
                    <a href="{{ route('prescriptions.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('prescriptions.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-prescription w-5 mr-3"></i>{{ __('common.prescriptions') }}
                    </a>
                    @endif
                    <a href="{{ route('settings.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('settings.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-cog w-5 mr-3"></i>{{ __('common.settings') }}
                    </a>
                    @endauth
                </div>
            </div>
        </header>
        <!-- Main Content with Header Offset -->
        <main class="pt-24 max-w-7xl mx-auto py-8 px-4">
            <!-- Working Hours Status Alert -->
            @auth
            @if(!\App\Models\Setting::isWithinWorkingHours())
                @php $nextWorking = \App\Models\Setting::getNextWorkingTime(); @endphp
                                <div id="clinic-status-alert" class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-4 rounded-r-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-orange-600 mr-3"></i>
                            <div>
                                <h4 class="font-semibold text-orange-800">{{ __('dashboard.currently_outside_hours') }}</h4>
                                <p class="text-orange-700 text-sm">
                                    {{ __('dashboard.scheduling_limited') }}
                                    @if($nextWorking)
                                        <br><strong>{{ __('dashboard.next_opening') }}:</strong> {{ $nextWorking->format('M j, Y \a\t g:i A') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button onclick="dismissAlert()" class="text-orange-600 hover:text-orange-800 p-1">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif
            @endauth
            
            <!-- Alerts -->
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Clean JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu functionality
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileBtn && mobileMenu) {
                mobileBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            
            // Close user menu when clicking outside
            document.addEventListener('click', function(e) {
                const userMenu = document.getElementById('user-menu');
                const languageMenu = document.getElementById('language-menu');
                
                if (userMenu && !e.target.closest('.relative')) {
                    userMenu.classList.add('hidden');
                }
                
                if (languageMenu && !e.target.closest('.relative')) {
                    languageMenu.classList.add('hidden');
                }
            });
        });
        
        // Toggle user menu
        function toggleUserMenu() {
            const userMenu = document.getElementById('user-menu');
            if (userMenu) {
                userMenu.classList.toggle('hidden');
            }
        }
        
        // Toggle language menu
        function toggleLanguageMenu() {
            const languageMenu = document.getElementById('language-menu');
            if (languageMenu) {
                languageMenu.classList.toggle('hidden');
            }
        }
        
        // Dismiss clinic status alert
        function dismissAlert() {
            const alert = document.getElementById('clinic-status-alert');
            if (alert) {
                alert.style.display = 'none';
                // Store dismissal in localStorage to remember for session
                localStorage.setItem('clinic-alert-dismissed', Date.now());
            }
        }
        
        // Check if alert was dismissed recently
        document.addEventListener('DOMContentLoaded', function() {
            const dismissed = localStorage.getItem('clinic-alert-dismissed');
            if (dismissed) {
                const dismissTime = parseInt(dismissed);
                const now = Date.now();
                const fiveMinutes = 5 * 60 * 1000;
                
                // Hide alert if dismissed within last 5 minutes
                if ((now - dismissTime) < fiveMinutes) {
                    const alert = document.getElementById('clinic-status-alert');
                    if (alert) {
                        alert.style.display = 'none';
                    }
                }
            }
        });
        
        // Real-time stats and working status update
        function updateStats() {
            // Update working status every 30 seconds
            fetch('/api/settings/working-status')
                .then(response => response.json())
                .then(data => {
                    // Update any working status indicators
                    const alert = document.getElementById('clinic-status-alert');
                    if (alert && !data.is_working) {
                        alert.classList.remove('hidden');
                    } else if (alert && data.is_working) {
                        alert.classList.add('hidden');
                    }
                })
                .catch(error => console.log('Status update failed:', error));
        }
        
        // Update stats every 30 seconds
        setInterval(updateStats, 30000);
    </script>
    @stack('scripts')
</body>
</html>
