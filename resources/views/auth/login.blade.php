<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.login') }} - {{ __('auth.medical_management_system') }}</title>
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
    <div class="bg-white/95 border border-slate-200 p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <div class="brand-mark w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-heartbeat text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('auth.medical_management_system') }}</h1>
            <p class="text-gray-600 mt-2">{{ __('auth.sign_in_to_account') }}</p>
        </div>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
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
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-1"></i> {{ __('auth.password_label') }}
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       class="form-control w-full px-3 py-2.5 shadow-sm">
            </div>

            <button type="submit" 
                    class="w-full text-white py-2.5 px-4 rounded-xl font-semibold transition duration-200 shadow-lg hover:shadow-xl"
                    style="background: linear-gradient(135deg, var(--clinic-primary), var(--clinic-accent));">
                <i class="fas fa-sign-in-alt mr-2"></i> {{ __('auth.sign_in') }}
            </button>
        </form>
    </div>
</body>
</html>
