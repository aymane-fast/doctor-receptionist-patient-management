<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Clinic Setup Wizard') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $days = [
        'monday' => __('Monday'),
        'tuesday' => __('Tuesday'),
        'wednesday' => __('Wednesday'),
        'thursday' => __('Thursday'),
        'friday' => __('Friday'),
        'saturday' => __('Saturday'),
        'sunday' => __('Sunday'),
    ];

    $initialStep = 1;
    if ($errors->hasAny(['clinic_name', 'clinic_phone', 'clinic_address', 'clinic_email', 'clinic_website'])) {
        $initialStep = 2;
    }
    if ($errors->hasAny([
        'appointment_mode', 'appointment_duration', 'queue_daily_limit',
        'monday_start', 'monday_end', 'tuesday_start', 'tuesday_end', 'wednesday_start', 'wednesday_end',
        'thursday_start', 'thursday_end', 'friday_start', 'friday_end', 'saturday_start', 'saturday_end',
        'sunday_start', 'sunday_end'
    ])) {
        $initialStep = 3;
    }
    if ($errors->hasAny([
        'owner_doctor.name', 'owner_doctor.email', 'owner_doctor.password',
        'doctors.*.name', 'doctors.*.email', 'doctors.*.password',
        'receptionists.*.name', 'receptionists.*.email', 'receptionists.*.password', 'staff'
    ])) {
        $initialStep = 4;
    }

    $oldDoctors = collect(is_array(old('doctors')) ? old('doctors') : [])->filter(fn($row) => is_array($row))->take(50)->values()->all();
    $oldReceptionists = collect(is_array(old('receptionists')) ? old('receptionists') : [])->filter(fn($row) => is_array($row))->take(50)->values()->all();
@endphp
<body class="compact-page min-h-screen py-4 px-4">
    <div class="max-w-6xl mx-auto flex justify-end pb-3">@include('components.language-switcher')</div>
    <div class="max-w-6xl mx-auto">
        <div class="wizard-shell rounded-[2rem] overflow-hidden">
            <div id="setup-intro" class="brand-gradient px-6 py-5 text-white">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="max-w-2xl">
                        <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center mb-3">
                            <i class="fas fa-stethoscope text-3xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ __('Welcome to your clinic setup') }}</h1>
                        <p class="mt-3 text-white/85 text-lg">
                            {{ __('Launch a ready-to-show demo instantly, or configure a production clinic in one guided pass.') }}
                        </p>
                    </div>
                    <div class="bg-white/10 rounded-2xl px-5 py-4 min-w-[18rem]">
                        <p class="text-sm uppercase tracking-[0.18em] text-white/70">{{ __('Launch mode') }}</p>
                        <p class="mt-2 text-2xl font-semibold" id="environment-label">
                            {{ old('environment_mode', 'production') === 'demo' ? __('Demo clinic') : __('Production clinic') }}
                        </p>
                        <p class="mt-2 text-sm text-white/75">
                            {{ __('Demo mode includes staff accounts, patients, and appointments. Production starts clean.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 lg:px-8 py-6 border-b border-slate-200 bg-white/70">
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="step-pill active rounded-full px-4 py-2 text-sm font-semibold" data-step-nav="1">{{ __('1. Mode') }}</button>
                    <button type="button" class="step-pill rounded-full px-4 py-2 text-sm font-semibold" data-step-nav="2" data-production-only>{{ __('2. Clinic') }}</button>
                    <button type="button" class="step-pill rounded-full px-4 py-2 text-sm font-semibold" data-step-nav="3" data-production-only>{{ __('3. Workflow') }}</button>
                    <button type="button" class="step-pill rounded-full px-4 py-2 text-sm font-semibold" data-step-nav="4" data-production-only>{{ __('4. Team') }}</button>
                </div>
            </div>

            <form action="{{ route('setup.store') }}" method="POST" id="setup-form" class="px-6 lg:px-8 py-8 space-y-8">
                @csrf

                @if($errors->any())
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-circle-exclamation mt-1"></i>
                            <div>
                                <p class="font-semibold">{{ __('Setup needs a couple of fixes.') }}</p>
                                <ul class="mt-2 list-disc list-inside text-sm space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <input type="hidden" name="environment_mode" id="environment_mode" value="{{ old('environment_mode', 'production') }}">
                <input type="hidden" name="appointment_mode" id="appointment_mode" value="{{ old('appointment_mode', 'scheduled') }}">

                <section data-step="1" class="step-panel space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">{{ __('Choose how this clinic starts') }}</h2>
                        <p class="mt-2 text-slate-600">{{ __('Demo mode is ready to present immediately. Production lets you configure your own clinic.') }}</p>
                    </div>

                    <div class="grid lg:grid-cols-2 gap-5">
                        <button type="button" class="choice-card text-left" data-environment-option="production">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-lg font-semibold text-slate-900">{{ __('Production') }}</p>
                                    <p class="mt-2 text-sm text-slate-600">{{ __('Start with a clean clinic and real staff accounts only.') }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 text-slate-700 text-xs font-semibold px-3 py-1">{{ __('Live') }}</span>
                            </div>
                        </button>

                        <button type="button" class="choice-card text-left" data-environment-option="demo">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-lg font-semibold text-slate-900">{{ __('Demo') }}</p>
                                    <p class="mt-2 text-sm text-slate-600">{{ __('Open a ready-made clinic with default staff, patients, and appointments. No details required.') }}</p>
                                </div>
                                <span class="rounded-full bg-teal-100 text-teal-800 text-xs font-semibold px-3 py-1">{{ __('Sample data') }}</span>
                            </div>
                        </button>
                    </div>

                    @include('setup.practice-type')
                    <div id="demo-details" class="hidden rounded-2xl border border-teal-200 bg-teal-50 p-5">
                        <div class="flex items-start gap-3">
                            <div class="icon-tile flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"><i class="fas fa-circle-play"></i></div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ __('Your showcase clinic is ready') }}</h3>
                                <p class="mt-1 text-sm text-slate-700">{{ __('We will create the default doctor and receptionist, sample patients, and appointments, then sign you in as the doctor.') }}</p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                                    <div class="rounded-xl bg-white p-3 text-slate-700"><strong>{{ __('Doctor:') }}</strong> doctor@example.com<br><strong>{{ __('Password:') }}</strong> password</div>
                                    <div class="rounded-xl bg-white p-3 text-slate-700"><strong>{{ __('Receptionist:') }}</strong> receptionist@example.com<br><strong>{{ __('Password:') }}</strong> password</div>
                                </div>
                                <label class="mt-4 flex items-start gap-3"><input type="checkbox" name="demo_always_open" value="1" @checked(old('demo_always_open'))><span>{{ __('clinical_ui.demo_24h') }}<small class="block mt-1">{{ __('clinical_ui.demo_24h_help') }}</small></span></label>
                                <button type="submit" id="launch-demo-btn" name="launch_demo" value="1" formnovalidate class="mt-5 brand-gradient rounded-full px-6 py-3 font-semibold text-white shadow-lg shadow-teal-900/10">
                                    {{ __('Launch demo') }} <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6" data-production-only>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                            <label for="appointment_duration" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Default consultation duration') }}</label>
                            <select name="appointment_duration" id="appointment_duration" class="form-control">
                                @foreach([15, 20, 30, 45, 60] as $minutes)
                                    <option value="{{ $minutes }}" {{ (int) old('appointment_duration', $specialties['general_practice']['defaults']['appointment_duration']) === $minutes ? 'selected' : '' }}>{{ $minutes }}{{ __(' minutes') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <section data-step="2" class="step-panel hidden space-y-6" data-production-only>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">{{ __('Add your clinic identity') }}</h2>
                        <p class="mt-2 text-slate-600">{{ __('These details will power prescriptions, records, and the rest of the clinic UI.') }}</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="setup_clinic_name" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Clinic name') }}</label>
                            <input id="setup_clinic_name" type="text" name="clinic_name" value="{{ old('clinic_name') }}" class="form-control" required>
                        </div>
                        <div>
                            <label for="setup_clinic_phone" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Clinic phone') }}</label>
                            <input id="setup_clinic_phone" type="text" name="clinic_phone" value="{{ old('clinic_phone') }}" class="form-control" required>
                        </div>
                        <div class="md:col-span-2">
                            <label for="setup_clinic_address" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Clinic address') }}</label>
                            <textarea id="setup_clinic_address" name="clinic_address" rows="3" class="form-control" required>{{ old('clinic_address') }}</textarea>
                        </div>
                        <div>
                            <label for="setup_clinic_email" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Clinic email') }}</label>
                            <input id="setup_clinic_email" type="email" name="clinic_email" value="{{ old('clinic_email') }}" class="form-control">
                        </div>
                        <div>
                            <label for="setup_clinic_website" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Clinic website') }}</label>
                            <input id="setup_clinic_website" type="url" name="clinic_website" value="{{ old('clinic_website') }}" class="form-control" placeholder="https://">
                        </div>
                    </div>

                </section>

                <section data-step="3" class="step-panel hidden space-y-6" data-production-only>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">{{ __('Choose the workflow your clinic uses') }}</h2>
                        <p class="mt-2 text-slate-600">{{ __('You can keep fixed time slots or run a simple queue-number system with estimated turn times.') }}</p>
                    </div>

                    <div class="grid lg:grid-cols-2 gap-5">
                        <button type="button" class="choice-card text-left" data-appointment-option="scheduled">
                            <p class="text-lg font-semibold text-slate-900">{{ __('Scheduled appointments') }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ __('Patients book a specific date and time. Best for tightly planned calendars.') }}</p>
                        </button>

                        <button type="button" class="choice-card text-left" data-appointment-option="queue">
                            <p class="text-lg font-semibold text-slate-900">{{ __('Queue numbers') }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ __('Patients receive queue numbers and an estimated consultation time based on the clinic start hour.') }}</p>
                        </button>
                    </div>

                    <div id="queue-limit-wrapper" class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 hidden">
                        <label for="setup_queue_daily_limit" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Daily queue limit per doctor') }}</label>
                        <input id="setup_queue_daily_limit" type="number" name="queue_daily_limit" min="1" max="500" value="{{ old('queue_daily_limit') }}" class="form-control" placeholder="{{ __('Optional') }}">
                        <p class="mt-2 text-sm text-slate-500">{{ __('Leave empty if each doctor can accept as many queue entries as needed.') }}</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900">{{ __('Working hours') }}</h3>
                                <p class="text-sm text-slate-600">{{ __('These hours define booking availability and queue estimates.') }}</p>
                            </div>
                        </div>

                        <div class="grid lg:grid-cols-2 gap-4">
                            @foreach($days as $day => $label)
                                @php
                                    $defaults = $defaultWorkingHours[$day];
                                    $isWorking = old("{$day}_working", $defaults['is_working']);
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <h4 class="font-semibold text-slate-900">{{ $label }}</h4>
                                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                            <input type="checkbox" name="{{ $day }}_working" value="1" class="rounded border-slate-300" {{ $isWorking ? 'checked' : '' }} data-working-toggle="{{ $day }}">
                                            {{ __('Working day') }}
                                        </label>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-3" data-working-fields="{{ $day }}">
                                        <div>
                                            <label for="setup_{{ $day }}_start" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">{{ __('Start') }}</label>
                                            <input id="setup_{{ $day }}_start" type="time" name="{{ $day }}_start" value="{{ old("{$day}_start", $defaults['start']) }}" class="form-control">
                                        </div>
                                        <div>
                                            <label for="setup_{{ $day }}_end" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">{{ __('End') }}</label>
                                            <input id="setup_{{ $day }}_end" type="time" name="{{ $day }}_end" value="{{ old("{$day}_end", $defaults['end']) }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section data-step="4" class="step-panel hidden space-y-8" data-production-only>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">{{ __('Create your clinic team') }}</h2>
                        <p class="mt-2 text-slate-600">{{ __('We will sign you in as the first doctor after setup, and you can add more doctors or receptionists right here.') }}</p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 p-5 space-y-5">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl brand-gradient text-white flex items-center justify-center">
                                <i class="fas fa-user-doctor"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ __('Primary doctor account') }}</h3>
                                <p class="text-sm text-slate-600">{{ __('This account becomes the owner account right after setup.') }}</p>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="setup_owner_doctor_name" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Full name') }}</label>
                                <input id="setup_owner_doctor_name" type="text" name="owner_doctor[name]" value="{{ old('owner_doctor.name') }}" class="form-control" required>
                            </div>
                            <div>
                                <label for="setup_owner_doctor_email" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Email') }}</label>
                                <input id="setup_owner_doctor_email" type="email" name="owner_doctor[email]" value="{{ old('owner_doctor.email') }}" class="form-control" required>
                            </div>
                            <div>
                                <label for="setup_owner_doctor_password" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Password') }}</label>
                                <input id="setup_owner_doctor_password" type="password" name="owner_doctor[password]" class="form-control" required>
                            </div>
                            <div>
                                <label for="setup_owner_doctor_password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Confirm password') }}</label>
                                <input id="setup_owner_doctor_password_confirmation" type="password" name="owner_doctor[password_confirmation]" class="form-control" required>
                            </div>
                            <div>
                                <label for="setup_owner_doctor_phone" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Phone') }}</label>
                                <input id="setup_owner_doctor_phone" type="text" name="owner_doctor[phone]" value="{{ old('owner_doctor.phone') }}" class="form-control">
                            </div>
                            <div>
                                <label for="setup_owner_doctor_address" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Address') }}</label>
                                <input id="setup_owner_doctor_address" type="text" name="owner_doctor[address]" value="{{ old('owner_doctor.address') }}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 p-5 space-y-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ __('Additional doctors') }}</h3>
                                <p class="text-sm text-slate-600">{{ __('Optional. New doctors inherit the clinic workflow choice from this setup.') }}</p>
                            </div>
                            <button type="button" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" id="add-doctor-btn">
                                <i class="fas fa-plus mr-2"></i>{{ __('Add doctor') }}
                            </button>
                        </div>

                        <div id="doctors-list" class="space-y-4">
                            @foreach($oldDoctors as $index => $doctor)
                                <div class="staff-card rounded-2xl border border-slate-200 p-4">
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div><label for="setup_doctors_{{ $index }}_name" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Name') }}</label><input id="setup_doctors_{{ $index }}_name" type="text" name="doctors[{{ $index }}][name]" value="{{ $doctor['name'] ?? '' }}" class="form-control"></div>
                                        <div><label for="setup_doctors_{{ $index }}_email" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Email') }}</label><input id="setup_doctors_{{ $index }}_email" type="email" name="doctors[{{ $index }}][email]" value="{{ $doctor['email'] ?? '' }}" class="form-control"></div>
                                        <div><label for="setup_doctors_{{ $index }}_password" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Password') }}</label><input id="setup_doctors_{{ $index }}_password" type="password" name="doctors[{{ $index }}][password]" class="form-control"></div>
                                        <div><label for="setup_doctors_{{ $index }}_phone" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Phone') }}</label><input id="setup_doctors_{{ $index }}_phone" type="text" name="doctors[{{ $index }}][phone]" value="{{ $doctor['phone'] ?? '' }}" class="form-control"></div>
                                        <div class="md:col-span-2"><label for="setup_doctors_{{ $index }}_address" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Address') }}</label><input id="setup_doctors_{{ $index }}_address" type="text" name="doctors[{{ $index }}][address]" value="{{ $doctor['address'] ?? '' }}" class="form-control"></div>
                                    </div>
                                    <div class="mt-4 text-right">
                                        <button type="button" class="text-sm font-semibold text-red-600 hover:text-red-700" data-remove-staff>{{ __('Remove') }}</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 p-5 space-y-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ __('Receptionists') }}</h3>
                                <p class="text-sm text-slate-600">{{ __('Optional. Add front-desk users now so the clinic is ready on day one.') }}</p>
                            </div>
                            <button type="button" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" id="add-receptionist-btn">
                                <i class="fas fa-plus mr-2"></i>{{ __('Add receptionist') }}
                            </button>
                        </div>

                        <div id="receptionists-list" class="space-y-4">
                            @foreach($oldReceptionists as $index => $receptionist)
                                <div class="staff-card rounded-2xl border border-slate-200 p-4">
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div><label for="setup_receptionists_{{ $index }}_name" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Name') }}</label><input id="setup_receptionists_{{ $index }}_name" type="text" name="receptionists[{{ $index }}][name]" value="{{ $receptionist['name'] ?? '' }}" class="form-control"></div>
                                        <div><label for="setup_receptionists_{{ $index }}_email" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Email') }}</label><input id="setup_receptionists_{{ $index }}_email" type="email" name="receptionists[{{ $index }}][email]" value="{{ $receptionist['email'] ?? '' }}" class="form-control"></div>
                                        <div><label for="setup_receptionists_{{ $index }}_password" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Password') }}</label><input id="setup_receptionists_{{ $index }}_password" type="password" name="receptionists[{{ $index }}][password]" class="form-control"></div>
                                        <div><label for="setup_receptionists_{{ $index }}_phone" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Phone') }}</label><input id="setup_receptionists_{{ $index }}_phone" type="text" name="receptionists[{{ $index }}][phone]" value="{{ $receptionist['phone'] ?? '' }}" class="form-control"></div>
                                        <div class="md:col-span-2"><label for="setup_receptionists_{{ $index }}_address" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Address') }}</label><input id="setup_receptionists_{{ $index }}_address" type="text" name="receptionists[{{ $index }}][address]" value="{{ $receptionist['address'] ?? '' }}" class="form-control"></div>
                                    </div>
                                    <div class="mt-4 text-right">
                                        <button type="button" class="text-sm font-semibold text-red-600 hover:text-red-700" data-remove-staff>{{ __('Remove') }}</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-between gap-4 border-t border-slate-200 pt-6" data-production-only>
                    <button type="button" id="back-btn" class="hidden rounded-full border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
                    </button>

                    <div class="ml-auto flex items-center gap-3">
                        <button type="button" id="next-btn" class="brand-gradient rounded-full px-6 py-3 font-semibold text-white shadow-lg shadow-teal-900/10">
                            {{ __('Next step') }} <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                        <button type="submit" id="submit-btn" class="hidden brand-gradient rounded-full px-6 py-3 font-semibold text-white shadow-lg shadow-teal-900/10">
                            {{ __('Finish setup') }} <i class="fas fa-check ml-2"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <template id="doctor-template">
        <div class="staff-card rounded-2xl border border-slate-200 p-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div><label for="setup_name" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Name') }}</label><input id="setup_name" type="text" data-name="name" class="form-control"></div>
                <div><label for="setup_email" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Email') }}</label><input id="setup_email" type="email" data-name="email" class="form-control"></div>
                <div><label for="setup_password" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Password') }}</label><input id="setup_password" type="password" data-name="password" class="form-control"></div>
                <div><label for="setup_phone" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Phone') }}</label><input id="setup_phone" type="text" data-name="phone" class="form-control"></div>
                <div class="md:col-span-2"><label for="setup_address" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Address') }}</label><input id="setup_address" type="text" data-name="address" class="form-control"></div>
            </div>
            <div class="mt-4 text-right">
                <button type="button" class="text-sm font-semibold text-red-600 hover:text-red-700" data-remove-staff>{{ __('Remove') }}</button>
            </div>
        </div>
    </template>

    <template id="receptionist-template">
        <div class="staff-card rounded-2xl border border-slate-200 p-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div><label for="setup_name" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Name') }}</label><input id="setup_name" type="text" data-name="name" class="form-control"></div>
                <div><label for="setup_email" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Email') }}</label><input id="setup_email" type="email" data-name="email" class="form-control"></div>
                <div><label for="setup_password" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Password') }}</label><input id="setup_password" type="password" data-name="password" class="form-control"></div>
                <div><label for="setup_phone" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Phone') }}</label><input id="setup_phone" type="text" data-name="phone" class="form-control"></div>
                <div class="md:col-span-2"><label for="setup_address" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('Address') }}</label><input id="setup_address" type="text" data-name="address" class="form-control"></div>
            </div>
            <div class="mt-4 text-right">
                <button type="button" class="text-sm font-semibold text-red-600 hover:text-red-700" data-remove-staff>{{ __('Remove') }}</button>
            </div>
        </div>
    </template>

    <script>
        const panels = [...document.querySelectorAll('.step-panel')];
        const navButtons = [...document.querySelectorAll('[data-step-nav]')];
        const backBtn = document.getElementById('back-btn');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        const environmentInput = document.getElementById('environment_mode');
        const appointmentModeInput = document.getElementById('appointment_mode');
        const specialtyInput = document.getElementById('clinic_specialty');
        const queueLimitWrapper = document.getElementById('queue-limit-wrapper');
        const environmentLabel = document.getElementById('environment-label');
        const demoDetails = document.getElementById('demo-details');
        const specialtyDescription = document.getElementById('specialty-description');
        let currentStep = {{ $initialStep }};
        let doctorIndex = {{ count($oldDoctors) }};
        let receptionistIndex = {{ count($oldReceptionists) }};

        function showStep(step) {
            currentStep = step;
            document.getElementById('setup-intro').classList.toggle('hidden', step > 1);

            panels.forEach((panel) => {
                panel.classList.toggle('hidden', Number(panel.dataset.step) !== step);
            });

            navButtons.forEach((button) => {
                button.classList.toggle('active', Number(button.dataset.stepNav) === step);
            });

            backBtn.classList.toggle('hidden', step === 1);
            nextBtn.classList.toggle('hidden', step === panels.length);
            submitBtn.classList.toggle('hidden', step !== panels.length);
        }

        function syncEnvironmentCards() {
            const mode = environmentInput.value;
            const isDemo = mode === 'demo';
            document.querySelectorAll('[data-environment-option]').forEach((button) => {
                button.classList.toggle('active', button.dataset.environmentOption === mode);
            });
            document.querySelectorAll('[data-production-only]').forEach((element) => {
                element.classList.toggle('hidden', isDemo);
            });
            demoDetails.classList.toggle('hidden', !isDemo);
            environmentLabel.textContent = isDemo ? @json(__('Demo clinic')) : @json(__('Production clinic'));

            showStep(1);
        }

        function syncAppointmentCards() {
            const mode = appointmentModeInput.value;
            document.querySelectorAll('[data-appointment-option]').forEach((button) => {
                button.classList.toggle('active', button.dataset.appointmentOption === mode);
            });
            queueLimitWrapper.classList.toggle('hidden', mode !== 'queue');
        }

        function syncSpecialtyDetails(applyDefaults = false) {
            const option = specialtyInput.options[specialtyInput.selectedIndex];
            specialtyDescription.textContent = option.dataset.description;

            if (!applyDefaults) {
                return;
            }

            document.getElementById('appointment_duration').value = option.dataset.duration;
            appointmentModeInput.value = option.dataset.mode;
            syncAppointmentCards();
        }

        function syncWorkingHoursState(day) {
            const checkbox = document.querySelector(`[data-working-toggle="${day}"]`);
            const container = document.querySelector(`[data-working-fields="${day}"]`);

            if (!checkbox || !container) {
                return;
            }

            container.classList.toggle('opacity-40', !checkbox.checked);
            container.classList.toggle('pointer-events-none', !checkbox.checked);
        }

        function addStaffCard(templateId, listId, prefix, index) {
            const template = document.getElementById(templateId);
            const list = document.getElementById(listId);
            const fragment = template.content.cloneNode(true);

            fragment.querySelectorAll('[data-name]').forEach((input) => {
                input.name = `${prefix}[${index}][${input.dataset.name}]`;
                const label = input.parentElement.querySelector('label');
                input.id = `setup_${prefix}_${index}_${input.dataset.name}`;
                if (label) label.htmlFor = input.id;
            });

            list.appendChild(fragment);
        }

        document.querySelectorAll('[data-environment-option]').forEach((button) => {
            button.addEventListener('click', () => {
                environmentInput.value = button.dataset.environmentOption;
                syncEnvironmentCards();
            });
        });

        document.querySelectorAll('[data-appointment-option]').forEach((button) => {
            button.addEventListener('click', () => {
                appointmentModeInput.value = button.dataset.appointmentOption;
                syncAppointmentCards();
            });
        });

        specialtyInput.addEventListener('change', () => syncSpecialtyDetails(true));

        document.querySelectorAll('[data-working-toggle]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => syncWorkingHoursState(checkbox.dataset.workingToggle));
            syncWorkingHoursState(checkbox.dataset.workingToggle);
        });

        navButtons.forEach((button) => {
            button.addEventListener('click', () => showStep(Number(button.dataset.stepNav)));
        });

        backBtn.addEventListener('click', () => showStep(Math.max(1, currentStep - 1)));
        nextBtn.addEventListener('click', () => {
            const panel = panels.find(panel => Number(panel.dataset.step) === currentStep);
            const invalid = [...panel.querySelectorAll('input, select, textarea')].find(input => !input.checkValidity());
            if (invalid) {
                invalid.reportValidity();
                return;
            }
            showStep(Math.min(panels.length, currentStep + 1));
        });
        // Native validation must reveal hidden wizard panels before focusing
        // their fields; otherwise submission appears stuck on the final step.
        const setupForm = document.getElementById('setup-form');
        setupForm.noValidate = true;
        setupForm.addEventListener('submit', event => {
            if (event.submitter?.formNoValidate || environmentInput.value === 'demo') return;
            const invalid = [...setupForm.elements].find(input => input.willValidate && !input.validity.valid);
            if (!invalid) return;
            event.preventDefault();
            const panel = invalid.closest('[data-step]');
            if (panel) showStep(Number(panel.dataset.step));
            invalid.reportValidity();
        });

        document.getElementById('add-doctor-btn').addEventListener('click', () => {
            addStaffCard('doctor-template', 'doctors-list', 'doctors', doctorIndex);
            doctorIndex++;
        });

        document.getElementById('add-receptionist-btn').addEventListener('click', () => {
            addStaffCard('receptionist-template', 'receptionists-list', 'receptionists', receptionistIndex);
            receptionistIndex++;
        });

        document.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-staff]');
            if (!removeButton) {
                return;
            }

            removeButton.closest('.staff-card')?.remove();
        });

        const restoredStep = currentStep;
        syncEnvironmentCards();
        syncAppointmentCards();
        syncSpecialtyDetails();
        showStep(environmentInput.value === 'demo' ? 1 : restoredStep);
    </script>
</body>
</html>
