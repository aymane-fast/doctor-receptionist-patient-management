<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d2d2a">
    <title>@yield('title', __('common.medical_management_system'))</title>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @auth
        @php
            $clinicName = \App\Models\Setting::get('clinic_name', __('common.medical_management'));
            $todayQuery = \App\Models\Appointment::whereDate('appointment_date', today());
            $pendingQuery = \App\Models\Appointment::whereDate('appointment_date', today())->where('status', 'scheduled');

            if (auth()->user()->isDoctor()) {
                $todayQuery->where('doctor_id', auth()->id());
                $pendingQuery->where('doctor_id', auth()->id());
            }

            $todayCount = $todayQuery->count();
            $pendingCount = $pendingQuery->count();
        @endphp

        <div class="app-shell">
            <aside id="app-sidebar" class="app-sidebar" aria-label="{{ __('Primary navigation') }}">
                <a href="{{ route('dashboard') }}" class="app-brand">
                    <span class="brand-mark" aria-hidden="true">
                        <i class="fas fa-heartbeat"></i>
                    </span>
                    <span class="app-brand-copy">
                        <span class="app-brand-kicker">{{ __('Clinic workspace') }}</span>
                        <span class="app-brand-title">{{ $clinicName }}</span>
                        <span class="text-xs text-white/65">{{ __($clinicSpecialty['label']) }}</span>
                    </span>
                </a>

                <nav class="app-sidebar-nav">
                    <span class="app-nav-label">{{ __('Workspace') }}</span>
                    <a href="{{ route('dashboard') }}" class="app-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                        <i class="fas fa-border-all"></i>
                        <span>{{ __('common.dashboard') }}</span>
                    </a>
                    <a href="{{ route('patients.index') }}" class="app-nav-link {{ request()->routeIs('patients.*') ? 'is-active' : '' }}">
                        <i class="fas fa-user-group"></i>
                        <span>{{ __('common.patients') }}</span>
                    </a>
                    <a href="{{ route('appointments.index') }}" class="app-nav-link {{ request()->routeIs('appointments.index', 'appointments.create', 'appointments.edit', 'appointments.show') ? 'is-active' : '' }}">
                        <i class="fas fa-calendar-check"></i>
                        <span>{{ __('common.appointments') }}</span>
                    </a>
                    <a href="{{ route('appointments.queue') }}" class="app-nav-link {{ request()->routeIs('appointments.queue') ? 'is-active' : '' }}">
                        <i class="fas fa-list-ol"></i>
                        <span>{{ __('Queue') }}</span>
                    </a>
                    <a href="{{ route('waitlist.index') }}" class="app-nav-link {{ request()->routeIs('waitlist.*') ? 'is-active' : '' }}">
                        <i class="fas fa-hourglass-half"></i>
                        <span>{{ __('appointments.waitlist') }}</span>
                    </a>

                    @if(auth()->user()->isDoctor())
                        <span class="app-nav-label">{{ __('Clinical') }}</span>
                        <a href="{{ route('doctor.current') }}" class="app-nav-link {{ request()->routeIs('doctor.current') ? 'is-active' : '' }}">
                            <i class="fas fa-user-clock"></i>
                            <span>{{ __('dashboard.current_patient') }}</span>
                        </a>
                        <a href="{{ route('medical-records.index') }}" class="app-nav-link {{ request()->routeIs('medical-records.*') ? 'is-active' : '' }}">
                            <i class="fas fa-notes-medical"></i>
                            <span>{{ __('common.medical_records') }}</span>
                        </a>
                        <a href="{{ route('prescriptions.index') }}" class="app-nav-link {{ request()->routeIs('prescriptions.*') ? 'is-active' : '' }}">
                            <i class="fas fa-prescription"></i>
                            <span>{{ __('common.prescriptions') }}</span>
                        </a>
                    @endif

                    <span class="app-nav-label">{{ __('Management') }}</span>
                    @if(auth()->user()->isDoctor())
                        <a href="{{ route('statistics.index') }}" class="app-nav-link {{ request()->routeIs('statistics.*') ? 'is-active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span>{{ __('common.statistics') }}</span>
                        </a>
                    @endif
                    @if(auth()->user()->isDoctor())
                        <a href="{{ route('exports.index') }}" class="app-nav-link {{ request()->routeIs('exports.*') ? 'is-active' : '' }}">
                            <i class="fas fa-file-export"></i>
                            <span>{{ __('Export') }}</span>
                        </a>
                    @endif
                    <a href="{{ route('settings.index') }}" class="app-nav-link {{ request()->routeIs('settings.*') ? 'is-active' : '' }}">
                        <i class="fas fa-sliders"></i>
                        <span>{{ __('common.settings') }}</span>
                    </a>
                </nav>

                <div class="app-sidebar-footer">
                    <div class="sidebar-user">
                        <span class="sidebar-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="sidebar-user-name block">{{ auth()->user()->name }}</span>
                            <span class="sidebar-user-role block">{{ auth()->user()->role }}</span>
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="sidebar-logout" title="{{ __('auth.logout') }}" aria-label="{{ __('auth.logout') }}">
                                <i class="fas fa-arrow-right-from-bracket"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <button id="sidebar-backdrop" class="sidebar-backdrop" type="button" aria-label="{{ __('Close navigation') }}"></button>

            <div class="app-main">
                <header class="app-topbar">
                    <div class="flex min-w-0 items-center gap-3">
                        <button id="mobile-menu-btn" type="button" class="topbar-icon-button mobile-nav-button" aria-controls="app-sidebar" aria-expanded="false" aria-label="{{ __('Open navigation') }}">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="topbar-context">
                            <span class="topbar-eyebrow">{{ now()->translatedFormat('l, j F') }} · {{ __($clinicSpecialty['label']) }}</span>
                            <h1 class="topbar-title">@yield('title', __('common.medical_management_system'))</h1>
                        </div>
                    </div>

                    <div class="topbar-actions">
                        <a href="{{ route('appointments.index', ['show_today' => '1']) }}" class="topbar-chip">
                            <i class="fas fa-calendar-day text-[var(--clinic-primary)]"></i>
                            <span class="topbar-chip-label">{{ __('common.today') }}</span>
                            <strong>{{ $todayCount }}</strong>
                        </a>
                        @if($pendingCount > 0)
                            <a href="{{ route('appointments.index', ['show_today' => '1', 'status' => 'scheduled']) }}" class="topbar-chip">
                                <i class="fas fa-clock text-[var(--clinic-warning)]"></i>
                                <span class="topbar-chip-label">{{ __('common.waiting') }}</span>
                                <strong>{{ $pendingCount }}</strong>
                            </a>
                        @endif
                        <a href="{{ route('patients.create') }}" class="topbar-icon-button topbar-quick-action" title="{{ __('patients.add_new_patient') }}" aria-label="{{ __('patients.add_new_patient') }}">
                            <i class="fas fa-user-plus"></i>
                        </a>
                        <a href="{{ route('appointments.create') }}" class="topbar-icon-button topbar-quick-action" title="{{ __('appointments.new_appointment') }}" aria-label="{{ __('appointments.new_appointment') }}">
                            <i class="fas fa-calendar-plus"></i>
                        </a>

                        <div class="relative">
                            <button id="language-menu-button" type="button" class="topbar-menu-button" aria-expanded="false" aria-controls="language-menu">
                                <span class="text-base">{{ language_flag(app()->getLocale()) }}</span>
                                <span>{{ strtoupper(app()->getLocale()) }}</span>
                                <i class="fas fa-chevron-down text-[0.6rem]"></i>
                            </button>
                            <div id="language-menu" class="app-menu hidden">
                                @foreach(\App\Models\Setting::getAvailableLanguages() as $code => $name)
                                    <a href="{{ route('language.switch', $code) }}" class="{{ app()->getLocale() === $code ? 'is-active' : '' }}">
                                        <span>{{ language_flag($code) }}</span>
                                        <span class="flex-1">{{ $name }}</span>
                                        @if(app()->getLocale() === $code)
                                            <i class="fas fa-check"></i>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </header>

                <main class="app-content">
                    @if(!\App\Models\Setting::isWithinWorkingHours())
                        @php $nextWorking = \App\Models\Setting::getNextWorkingTime(); @endphp
                        <div id="clinic-status-alert" class="app-alert app-alert-warning" role="status">
                            <span class="app-alert-icon"><i class="fas fa-clock"></i></span>
                            <div class="min-w-0 flex-1">
                                <strong class="block text-sm">{{ __('dashboard.currently_outside_hours') }}</strong>
                                <p class="mt-0.5 text-xs opacity-90">
                                    {{ __('dashboard.scheduling_limited') }}
                                    @if($nextWorking)
                                        <span class="ml-1"><strong>{{ __('dashboard.next_opening') }}:</strong> {{ $nextWorking->translatedFormat('M j, Y \a\t g:i A') }}</span>
                                    @endif
                                </p>
                            </div>
                            <button type="button" onclick="dismissAlert()" class="p-1 opacity-70 hover:opacity-100" aria-label="{{ __('Dismiss') }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="app-alert app-alert-success" role="alert">
                            <span class="app-alert-icon"><i class="fas fa-check"></i></span>
                            <p class="flex-1 text-sm font-semibold">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="app-alert app-alert-danger" role="alert">
                            <span class="app-alert-icon"><i class="fas fa-exclamation"></i></span>
                            <p class="flex-1 text-sm font-semibold">{{ session('error') }}</p>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="app-alert app-alert-info" role="alert">
                            <span class="app-alert-icon"><i class="fas fa-info"></i></span>
                            <p class="flex-1 text-sm font-semibold">{{ session('info') }}</p>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <main>
            @yield('content')
        </main>
    @endauth

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const mobileButton = document.getElementById('mobile-menu-btn');
            const sidebarBackdrop = document.getElementById('sidebar-backdrop');
            const languageButton = document.getElementById('language-menu-button');
            const languageMenu = document.getElementById('language-menu');

            function setSidebar(open) {
                body.classList.toggle('sidebar-open', open);
                mobileButton?.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            mobileButton?.addEventListener('click', function () {
                setSidebar(!body.classList.contains('sidebar-open'));
            });

            sidebarBackdrop?.addEventListener('click', function () {
                setSidebar(false);
            });

            document.querySelectorAll('.app-nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    setSidebar(false);
                });
            });

            languageButton?.addEventListener('click', function (event) {
                event.stopPropagation();
                const willOpen = languageMenu.classList.contains('hidden');
                languageMenu.classList.toggle('hidden');
                languageButton.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            document.addEventListener('click', function (event) {
                if (languageMenu && !event.target.closest('#language-menu') && !event.target.closest('#language-menu-button')) {
                    languageMenu.classList.add('hidden');
                    languageButton?.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setSidebar(false);
                    languageMenu?.classList.add('hidden');
                    languageButton?.setAttribute('aria-expanded', 'false');
                }
            });

            const dismissedAt = Number(localStorage.getItem('clinic-alert-dismissed') || 0);
            if (Date.now() - dismissedAt < 5 * 60 * 1000) {
                document.getElementById('clinic-status-alert')?.remove();
            }
        });

        function dismissAlert() {
            document.getElementById('clinic-status-alert')?.remove();
            localStorage.setItem('clinic-alert-dismissed', Date.now().toString());
        }

        function toggleLanguageMenu() {
            document.getElementById('language-menu-button')?.click();
        }

        function toggleUserMenu() {
            document.getElementById('user-menu')?.classList.toggle('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
