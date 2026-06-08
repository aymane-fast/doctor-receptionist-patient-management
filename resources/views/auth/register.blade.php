<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.register') }} - {{ __('auth.medical_management_system') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --clinic-primary: #0f766e;
            --clinic-accent: #0284c7;
            --clinic-border: #dbe3ea;
        }

        body {
            background:
                radial-gradient(circle at 15% 15%, rgba(15, 118, 110, 0.18), transparent 24rem),
                radial-gradient(circle at 85% 20%, rgba(2, 132, 199, 0.16), transparent 22rem),
                linear-gradient(180deg, #f7fbfb 0%, #eef4f5 100%);
        }

        .brand-mark {
            background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-accent));
        }

        .form-control {
            border: 1px solid var(--clinic-border);
            border-radius: 0.75rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--clinic-primary);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="bg-white/95 border border-slate-200 p-8 rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="text-center mb-8">
            <div class="brand-mark w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-user-plus text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('auth.create_new_user') }}</h1>
            <p class="text-gray-600 mt-2">{{ __('auth.register_doctor_receptionist') }}</p>
        </div>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-user mr-1"></i> {{ __('auth.full_name') }}
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       required 
                       class="form-control w-full px-3 py-2.5 shadow-sm">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-envelope mr-1"></i> {{ __('auth.email_address') }}
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       required 
                       class="form-control w-full px-3 py-2.5 shadow-sm">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-user-tag mr-1"></i> {{ __('auth.role') }}
                </label>
                <select id="role" 
                        name="role" 
                        required 
                        class="form-control w-full px-3 py-2.5 shadow-sm">
                    <option value="">{{ __('auth.select_role') }}</option>
                    <option value="doctor" {{ old('role') == 'doctor' ? 'selected' : '' }}>{{ __('auth.doctor') }}</option>
                    <option value="receptionist" {{ old('role') == 'receptionist' ? 'selected' : '' }}>{{ __('auth.receptionist') }}</option>
                </select>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-phone mr-1"></i> {{ __('auth.phone_optional') }}
                </label>
                <input type="text" 
                       id="phone" 
                       name="phone" 
                       value="{{ old('phone') }}"
                       class="form-control w-full px-3 py-2.5 shadow-sm">
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-map-marker-alt mr-1"></i> {{ __('auth.address_optional') }}
                </label>
                <textarea id="address" 
                          name="address" 
                          rows="2"
                          class="form-control w-full px-3 py-2.5 shadow-sm">{{ old('address') }}</textarea>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-lock mr-1"></i> {{ __('auth.password_label') }}
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       class="form-control w-full px-3 py-2.5 shadow-sm">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-lock mr-1"></i> {{ __('auth.confirm_password') }}
                </label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       required 
                       class="form-control w-full px-3 py-2.5 shadow-sm">
            </div>

            <button type="submit" 
                    class="w-full text-white py-2.5 px-4 rounded-xl font-semibold transition duration-200 shadow-lg hover:shadow-xl"
                    style="background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-accent));">
                <i class="fas fa-user-plus mr-2"></i> {{ __('auth.create_user') }}
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                {{ __('auth.already_have_account') }} 
                <a href="{{ route('login') }}" class="text-teal-700 hover:text-teal-900 font-medium">{{ __('auth.sign_in_here') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
