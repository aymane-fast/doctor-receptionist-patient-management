<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d2d2a">
    <title>{{ __('auth.login') }} - {{ __('auth.medical_management_system') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body">
    <div class="auth-shell">
        <section class="auth-story">
            <div>
                <div class="auth-story-brand">
                    <span class="brand-mark h-10 w-10 rounded-xl"><i class="fas fa-heartbeat"></i></span>
                    <span>{{ __('auth.medical_management_system') }}</span>
                </div>
                <h1 class="auth-story-title">{{ __('Care runs better when the day stays clear.') }}</h1>
                <p class="auth-story-copy">{{ __('A focused workspace for appointments, queue flow, patient records, and the clinical decisions that matter.') }}</p>
            </div>

            <div class="auth-feature-list" aria-label="{{ __('System capabilities') }}">
                <div class="auth-feature"><i class="fas fa-calendar-check"></i><span>{{ __('Scheduled appointments and flexible queue mode') }}</span></div>
                <div class="auth-feature"><i class="fas fa-file-medical"></i><span>{{ __('Patient history and clinical records in one place') }}</span></div>
                <div class="auth-feature"><i class="fas fa-user-shield"></i><span>{{ __('Role-based access for doctors and reception staff') }}</span></div>
            </div>
        </section>

        <section class="auth-panel">
            <span class="auth-kicker">{{ __('Secure workspace') }}</span>
            <h2 class="auth-title">{{ __('auth.sign_in') }}</h2>
            <p class="auth-subtitle">{{ __('auth.sign_in_to_account') }}</p>

            @if($errors->any())
                <div class="app-alert app-alert-danger mt-6" role="alert">
                    <span class="app-alert-icon"><i class="fas fa-exclamation"></i></span>
                    <div class="text-sm">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="app-alert app-alert-success mt-6" role="alert">
                    <span class="app-alert-icon"><i class="fas fa-check"></i></span>
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-bold">
                        {{ __('auth.email_address') }}
                    </label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="form-control w-full py-3 pl-11 pr-4"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-bold">
                        {{ __('auth.password_label') }}
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="form-control w-full py-3 pl-11 pr-4"
                        >
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full px-5 py-3">
                    <span>{{ __('auth.sign_in') }}</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </section>
    </div>
</body>
</html>
