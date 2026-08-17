@extends('layouts.app')

@section('title', __('auth.create_new_user'))

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        :title="__('auth.create_new_user')"
        :description="__('auth.register_doctor_receptionist')"
        icon="fa-user-plus"
        eyebrow="Staff access"
    >
        <x-slot:actions>
            <a href="{{ route('settings.index') }}" class="btn-secondary px-5 py-3">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('common.settings') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="section-card mx-auto max-w-5xl p-6 md:p-8">
        <x-ui.section-heading
            :title="__('auth.create_new_user')"
            description="Create a secure staff profile and assign the correct clinic role."
            icon="fa-id-badge"
            class="mb-7"
        />

        @if($errors->any())
            <div class="app-alert app-alert-danger mb-6" role="alert">
                <span class="app-alert-icon"><i class="fas fa-exclamation"></i></span>
                <ul class="list-inside list-disc text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-bold">{{ __('auth.full_name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="form-control w-full px-4 py-3">
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-bold">{{ __('auth.email_address') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="form-control w-full px-4 py-3">
            </div>

            <div>
                <label for="role" class="mb-2 block text-sm font-bold">{{ __('auth.role') }}</label>
                <select id="role" name="role" required class="form-control w-full px-4 py-3">
                    <option value="">{{ __('auth.select_role') }}</option>
                    <option value="doctor" {{ old('role') === 'doctor' ? 'selected' : '' }}>{{ __('auth.doctor') }}</option>
                    <option value="receptionist" {{ old('role') === 'receptionist' ? 'selected' : '' }}>{{ __('auth.receptionist') }}</option>
                </select>
            </div>

            <div>
                <label for="phone" class="mb-2 block text-sm font-bold">{{ __('auth.phone_optional') }}</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="form-control w-full px-4 py-3">
            </div>

            <div class="md:col-span-2">
                <label for="address" class="mb-2 block text-sm font-bold">{{ __('auth.address_optional') }}</label>
                <textarea id="address" name="address" rows="3" class="form-control w-full px-4 py-3">{{ old('address') }}</textarea>
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-bold">{{ __('auth.password_label') }}</label>
                <input type="password" id="password" name="password" required autocomplete="new-password" class="form-control w-full px-4 py-3">
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-bold">{{ __('auth.confirm_password') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" class="form-control w-full px-4 py-3">
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end md:col-span-2">
                <a href="{{ route('settings.index') }}" class="btn-secondary px-6 py-3">{{ __('patients.cancel') }}</a>
                <button type="submit" class="btn-primary px-6 py-3">
                    <i class="fas fa-user-plus"></i>
                    <span>{{ __('auth.create_user') }}</span>
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
