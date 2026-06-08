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
                        'header-gray': '#ffffff',
                        'clinic-primary': '#0f766e',
                        'clinic-accent': '#0284c7',
                        'clinic-ink': '#1f2937',
                        'banner-gradient': 'linear-gradient(135deg, #0f766e 0%, #0284c7 100%)',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --clinic-primary: #0f766e;
            --clinic-primary-dark: #115e59;
            --clinic-primary-soft: #ccfbf1;
            --clinic-accent: #0284c7;
            --clinic-accent-soft: #e0f2fe;
            --clinic-ink: #1f2937;
            --clinic-muted: #64748b;
            --clinic-border: #dbe3ea;
            --clinic-page: #eef4f5;
            --clinic-surface: #ffffff;
            --clinic-warning: #d97706;
            --clinic-danger: #dc2626;
            --clinic-success: #059669;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.08), transparent 30rem),
                linear-gradient(180deg, #f7fbfb 0%, var(--clinic-page) 100%);
            color: var(--clinic-ink);
        }

        /* Shared design primitives */
        .gradient-bg {
            background: linear-gradient(135deg, var(--clinic-primary) 0%, var(--clinic-accent) 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(219, 227, 234, 0.9);
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
            box-shadow: 0 12px 28px -20px rgba(31, 41, 55, 0.35), 0 2px 8px -4px rgba(31, 41, 55, 0.12);
        }
        
        .deep-shadow {
            box-shadow: 0 18px 36px -24px rgba(15, 118, 110, 0.45);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, var(--clinic-primary) 0%, var(--clinic-accent) 100%);
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
        
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.6); }
        }
        
        .animate-pulse-glow {
            animation: pulse-glow 3s ease-in-out infinite;
        }
        
        .header-shadow {
            box-shadow: 0 10px 30px -24px rgba(15, 23, 42, 0.45);
        }
        
        .font-professional {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .app-header {
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid var(--clinic-border);
            backdrop-filter: blur(14px);
        }

        .brand-mark {
            background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-accent));
        }

        .section-card {
            background: var(--clinic-surface);
            border: 1px solid var(--clinic-border);
            border-radius: 1rem;
            box-shadow: 0 12px 28px -24px rgba(31, 41, 55, 0.35);
        }

        .soft-panel {
            background: linear-gradient(135deg, rgba(240, 253, 250, 0.92), rgba(240, 249, 255, 0.92));
            border: 1px solid rgba(125, 211, 252, 0.35);
        }

        .icon-tile {
            background: linear-gradient(135deg, var(--clinic-primary-soft), var(--clinic-accent-soft));
            color: var(--clinic-primary);
        }

        .action-card {
            background: linear-gradient(135deg, rgba(240, 253, 250, 0.95), rgba(240, 249, 255, 0.95));
            border: 1px solid rgba(15, 118, 110, 0.16);
            border-radius: 1rem;
        }

        .nav-item {
            color: #475569;
            border: 1px solid transparent;
        }

        .nav-item:hover {
            color: var(--clinic-primary);
            background: var(--clinic-primary-soft);
            border-color: rgba(15, 118, 110, 0.12);
        }

        .nav-item-active {
            color: var(--clinic-primary);
            background: var(--clinic-primary-soft);
            border-color: rgba(15, 118, 110, 0.18);
        }

        .btn-primary,
        .btn-secondary,
        .btn-danger,
        .btn-ghost {
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-accent));
            color: white;
            box-shadow: 0 10px 22px -16px rgba(15, 118, 110, 0.65);
        }

        .btn-primary:hover {
            filter: brightness(0.95);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: white;
            border: 1px solid var(--clinic-border);
            color: var(--clinic-ink);
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .btn-danger:hover {
            background: #fecaca;
        }

        .btn-ghost {
            color: var(--clinic-primary);
            background: var(--clinic-primary-soft);
            border: 1px solid rgba(15, 118, 110, 0.12);
        }

        .btn-ghost:hover {
            background: #99f6e4;
        }

        .form-control {
            border: 1px solid var(--clinic-border);
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.92);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--clinic-primary);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
        }

        .status-scheduled {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .status-in-progress {
            background: var(--clinic-accent-soft);
            color: #075985;
            border: 1px solid #bae6fd;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
    @stack('styles')
</head>
<body class="font-sans">
    <div class="min-h-screen">
        <!-- Fixed Header -->
        <header class="fixed top-0 left-0 right-0 z-50 app-header header-shadow">
            <!-- Top Navigation Bar -->
            <nav class="font-professional">
                <div class="max-w-full mx-auto px-8">
                    <div class="flex justify-between items-center py-4">
                        <!-- Left Section: Logo and Navigation -->
                        <div class="flex items-center space-x-6">
                            <!-- Logo -->
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 brand-mark rounded-lg flex items-center justify-center">
                                    <i class="fas fa-heartbeat text-white text-xl"></i>
                                </div>
                                <h1 class="text-xl font-bold text-gray-900">{{ __('common.medical_management') ?? 'Medical Management' }}</h1>
                            </div>
                            
                            <!-- Navigation Links -->
                            @auth
                            <div class="hidden md:flex items-center space-x-1">
                                <a href="{{ route('dashboard') }}" class="nav-item flex flex-col items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'nav-item-active' : '' }}">
                                    <i class="fas fa-tachometer-alt text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.dashboard') }}</span>
                                </a>
                                <a href="{{ route('patients.index') }}" class="nav-item flex flex-col items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('patients.*') ? 'nav-item-active' : '' }}">
                                    <i class="fas fa-users text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.patients') }}</span>
                                </a>
                                <a href="{{ route('appointments.index') }}" class="nav-item flex flex-col items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('appointments.*') ? 'nav-item-active' : '' }}">
                                    <i class="fas fa-calendar text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.appointments') }}</span>
                                </a>
                                @if(auth()->user()->isDoctor())
                                <a href="{{ route('doctor.current') }}" class="nav-item flex flex-col items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('doctor.current') ? 'nav-item-active' : '' }}">
                                    <i class="fas fa-user-clock text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('dashboard.current_patient') }}</span>
                                </a>
                                <a href="{{ route('medical-records.index') }}" class="nav-item flex flex-col items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('medical-records.*') ? 'nav-item-active' : '' }}">
                                    <i class="fas fa-file-medical text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.medical_records') }}</span>
                                </a>
                                <a href="{{ route('prescriptions.index') }}" class="nav-item flex flex-col items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('prescriptions.*') ? 'nav-item-active' : '' }}">
                                    <i class="fas fa-prescription text-lg mb-1"></i>
                                    <span class="text-xs font-medium">{{ __('common.prescriptions') }}</span>
                                </a>
                                @endif
                                @if(auth()->user()->isReceptionist())
                                <a href="{{ route('exports.index') }}" class="nav-item flex flex-col items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('exports.*') ? 'nav-item-active' : '' }}">
                                    <i class="fas fa-download text-lg mb-1"></i>
                                    <span class="text-xs font-medium">Export</span>
                                </a>
                                @endif
                                <a href="{{ route('settings.index') }}" class="nav-item flex flex-col items-center px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'nav-item-active' : '' }}">
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
                                @php
                                    $todayQuery = \App\Models\Appointment::whereDate('appointment_date', today());
                                    
                                    // If user is a doctor, only show their appointments
                                    if(auth()->user()->isDoctor()) {
                                        $todayQuery->where('doctor_id', auth()->id());
                                    }
                                    
                                    $todayCount = $todayQuery->count();
                                @endphp
                                <a href="{{ route('appointments.index', ['show_today' => '1']) }}" 
                                   class="flex items-center space-x-2 bg-white rounded-lg px-3 py-2 shadow-sm hover:bg-teal-50 transition-colors duration-200">
                                    <i class="fas fa-calendar-day text-teal-700"></i>
                                    <span class="text-sm font-medium text-gray-700">{{ __('common.today') }}</span>
                                    <span class="bg-teal-100 text-teal-800 text-xs font-bold px-2 py-1 rounded-full">
                                        {{ $todayCount }}
                                    </span>
                                </a>

                                <!-- Pending Appointments -->
                                @php
                                    $pendingQuery = \App\Models\Appointment::where('status', 'scheduled')
                                        ->whereDate('appointment_date', today());
                                    
                                    // If user is a doctor, only show their appointments
                                    if(auth()->user()->isDoctor()) {
                                        $pendingQuery->where('doctor_id', auth()->id());
                                    }
                                    
                                    $pendingCount = $pendingQuery->count();
                                @endphp
                                @if($pendingCount > 0)
                                <a href="{{ route('appointments.index', ['show_today' => '1', 'status' => 'scheduled']) }}" 
                                   class="flex items-center space-x-2 bg-orange-50 rounded-lg px-3 py-2 shadow-sm hover:bg-orange-100 transition-colors duration-200">
                                    <i class="fas fa-clock text-orange-600"></i>
                                    <span class="text-sm font-medium text-gray-700">{{ __('common.waiting') }}</span>
                                    <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded-full">{{ $pendingCount }}</span>
                                </a>
                                @endif
                            </div>

                            <!-- Quick Actions -->
                            <div class="flex items-center space-x-2">
                                <!-- Quick Add Patient -->
                                <a href="{{ route('patients.create') }}" 
                                   class="p-2 text-gray-600 hover:text-teal-700 transition-colors" 
                                   title="{{ __('patients.add_new_patient') }}">
                                    <i class="fas fa-user-plus text-lg"></i>
                                </a>
                                
                                <!-- Quick Add Appointment -->
                                <a href="{{ route('appointments.create') }}" 
                                   class="p-2 text-gray-600 hover:text-teal-700 transition-colors" 
                                   title="{{ __('appointments.new_appointment') }}">
                                    <i class="fas fa-calendar-plus text-lg"></i>
                                </a>


                            </div>

                            <!-- User Profile with Dynamic Status -->
                            <div class="flex items-center space-x-3">
                                <!-- Language Switcher -->
                                <div class="relative">
                                    <button onclick="toggleLanguageMenu()" class="flex items-center space-x-2 px-3 py-2 text-gray-600 hover:text-teal-700 transition-colors">
                                        <span class="text-lg">{{ language_flag(app()->getLocale()) }}</span>
                                        <span class="text-sm font-medium">{{ strtoupper(app()->getLocale()) }}</span>
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </button>
                                    <div id="language-menu" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border hidden z-50">
                                        @foreach(\App\Models\Setting::getAvailableLanguages() as $code => $name)
                                            <a href="{{ route('language.switch', $code) }}" 
                                               class="flex items-center space-x-2 px-4 py-2 text-sm hover:bg-gray-100 {{ app()->getLocale() == $code ? 'bg-teal-50 text-teal-700' : 'text-gray-700' }}">
                                                <span>{{ language_flag($code) }}</span>
                                                <span>{{ $name }}</span>
                                                @if(app()->getLocale() == $code)
                                                    <i class="fas fa-check text-teal-700 ml-auto"></i>
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
                                            <span class="w-2 h-2 bg-teal-400 rounded-full mr-1"></span>
                                            {{ ucfirst(auth()->user()->role) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="w-8 h-8 brand-mark rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                                
                                <!-- User Menu Dropdown -->
                                <div class="relative">
                                    <button class="text-gray-600 hover:text-teal-700 transition-colors" onclick="toggleUserMenu()">
                                        <i class="fas fa-chevron-down text-sm"></i>
                                    </button>
                                    <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border hidden">
                                        @if(auth()->user()->isDoctor())
                                        <a href="{{ route('statistics.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('statistics.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <i class="fas fa-chart-line mr-2"></i>{{ __('common.statistics') }}
                                        </a>
                                        <a href="{{ route('exports.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('exports.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                                            <i class="fas fa-file-export mr-2"></i>Export de Données
                                        </a>
                                        <div class="border-t border-gray-100"></div>
                                        @endif
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
                            <button id="mobile-menu-btn" class="p-2 text-gray-600 hover:text-teal-700 transition-colors">
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
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-teal-50 text-teal-700' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 mr-3"></i>{{ __('common.dashboard') }}
                    </a>
                    <a href="{{ route('patients.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('patients.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                        <i class="fas fa-users w-5 mr-3"></i>{{ __('common.patients') }}
                    </a>
                    <a href="{{ route('appointments.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('appointments.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                        <i class="fas fa-calendar w-5 mr-3"></i>{{ __('common.appointments') }}
                    </a>
                    @if(auth()->user()->isDoctor())
                    <a href="{{ route('doctor.current') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('doctor.current') ? 'bg-teal-50 text-teal-700' : '' }}">
                        <i class="fas fa-user-clock w-5 mr-3"></i>Current Patient
                    </a>
                    <a href="{{ route('medical-records.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('medical-records.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                        <i class="fas fa-file-medical w-5 mr-3"></i>{{ __('common.medical_records') }}
                    </a>
                    <a href="{{ route('prescriptions.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('prescriptions.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                        <i class="fas fa-prescription w-5 mr-3"></i>{{ __('common.prescriptions') }}
                    </a>
                    @endif
                    <a href="{{ route('settings.index') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('settings.*') ? 'bg-teal-50 text-teal-700' : '' }}">
                        <i class="fas fa-cog w-5 mr-3"></i>{{ __('common.settings') }}
                    </a>
                    @endauth
                </div>
            </div>
        </header>
        <!-- Main Content with Header Offset -->
        <main class="pt-32 max-w-7xl mx-auto py-8 px-4">
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
