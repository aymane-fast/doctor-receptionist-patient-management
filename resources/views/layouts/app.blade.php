<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Medical Management System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    colors: {
                        'primary': {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
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
    </style>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 font-sans">
    <div class="min-h-screen">
        <!-- Modern Navigation -->
        <nav class="gradient-bg shadow-2xl">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center animate-pulse-glow">
                            <i class="fas fa-heartbeat text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">MediCare Pro</h1>
                            <p class="text-blue-100 text-xs">Advanced Medical Management</p>
                        </div>
                    </div>
                    
                    @auth
                    <div class="flex items-center space-x-6">
                        <!-- Navigation Links -->
                        <div class="hidden md:flex space-x-1">
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg text-white/90 hover:bg-white/20 hover:text-white transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('patients.index') }}" class="px-4 py-2 rounded-lg text-white/90 hover:bg-white/20 hover:text-white transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-users"></i>
                                <span>Patients</span>
                            </a>
                            <a href="{{ route('appointments.index') }}" class="px-4 py-2 rounded-lg text-white/90 hover:bg-white/20 hover:text-white transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-calendar"></i>
                                <span>Appointments</span>
                            </a>
                            @if(auth()->user()->isDoctor())
                            <a href="{{ route('doctor.current') }}" class="px-4 py-2 rounded-lg text-white/90 hover:bg-white/20 hover:text-white transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-user-clock"></i>
                                <span>Current</span>
                            </a>
                            <a href="{{ route('medical-records.index') }}" class="px-4 py-2 rounded-lg text-white/90 hover:bg-white/20 hover:text-white transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-file-medical"></i>
                                <span>Records</span>
                            </a>
                            <a href="{{ route('prescriptions.index') }}" class="px-4 py-2 rounded-lg text-white/90 hover:bg-white/20 hover:text-white transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-prescription"></i>
                                <span>Prescriptions</span>
                            </a>
                            @endif
                        </div>
                        
                        <!-- User Profile -->
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-3 bg-white/10 rounded-xl px-4 py-2">
                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div class="text-right hidden md:block">
                                    <div class="text-sm font-medium text-white">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-blue-100">{{ ucfirst(auth()->user()->role) }}</div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl transition-all duration-200 flex items-center space-x-2">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span class="hidden md:inline">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-8 px-4">
            <!-- Modern Alerts -->
            @if(session('success'))
            <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 border-l-4 border-emerald-400 text-emerald-700 p-4 rounded-r-xl mb-6 modern-shadow" role="alert">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-check-circle text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="font-medium">Success!</p>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-400 text-red-700 p-4 rounded-r-xl mb-6 modern-shadow" role="alert">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                    <div>
                        <p class="font-medium">Error!</p>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
