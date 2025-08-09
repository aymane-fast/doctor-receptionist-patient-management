<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Medical Management System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-4">
                        <i class="fas fa-heartbeat text-2xl"></i>
                        <h1 class="text-xl font-bold">Medical Management System</h1>
                    </div>
                    
                    @auth
                    <div class="flex items-center space-x-6">
                        <!-- Navigation Links -->
                        <div class="flex space-x-4">
                            <a href="{{ route('dashboard') }}" class="hover:text-blue-200 transition">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                            <a href="{{ route('patients.index') }}" class="hover:text-blue-200 transition">
                                <i class="fas fa-users mr-1"></i> Patients
                            </a>
                            <a href="{{ route('appointments.index') }}" class="hover:text-blue-200 transition">
                                <i class="fas fa-calendar mr-1"></i> Appointments
                            </a>
                            @if(auth()->user()->isDoctor())
                            <a href="{{ route('medical-records.index') }}" class="hover:text-blue-200 transition">
                                <i class="fas fa-file-medical mr-1"></i> Medical Records
                            </a>
                            <a href="{{ route('prescriptions.index') }}" class="hover:text-blue-200 transition">
                                <i class="fas fa-prescription mr-1"></i> Prescriptions
                            </a>
                            @endif
                            <a href="{{ route('documents.index') }}" class="hover:text-blue-200 transition">
                                <i class="fas fa-folder mr-1"></i> Documents
                            </a>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <div class="text-sm">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-blue-200">{{ ucfirst(auth()->user()->role) }}</div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-blue-700 hover:bg-blue-800 px-3 py-1 rounded transition">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 px-4">
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

    @stack('scripts')
</body>
</html>
