<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.register') }} - {{ __('auth.medical_management_system') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-700 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-lg">
        <div class="text-center mb-8">
            <i class="fas fa-heartbeat text-4xl text-blue-600 mb-4"></i>
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
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-user-tag mr-1"></i> {{ __('auth.role') }}
                </label>
                <select id="role" 
                        name="role" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-map-marker-alt mr-1"></i> {{ __('auth.address_optional') }}
                </label>
                <textarea id="address" 
                          name="address" 
                          rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('address') }}</textarea>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-lock mr-1"></i> {{ __('auth.password_label') }}
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-lock mr-1"></i> {{ __('auth.confirm_password') }}
                </label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-user-plus mr-2"></i> {{ __('auth.create_user') }}
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                {{ __('auth.already_have_account') }} 
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ __('auth.sign_in_here') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
