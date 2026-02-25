<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

    <title>{{ __('messages.register') }} - e-Yuran</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center py-6 px-4">
        <div class="max-w-md w-full mx-auto">
            <!-- Logo -->
            <div class="text-center mb-6">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('images/logo.png') }}" alt="Persatuan Penduduk Taman Tropika Kajang" class="h-24 w-auto mx-auto">
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm p-6 lg:p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-1">{{ __('messages.register') }}</h2>
                <p class="text-gray-500 text-sm mb-6">{{ __('Daftar sebagai penduduk Taman Tropika Kajang') }}</p>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Ahmad bin Abdullah">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.email') }} <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="ahmad@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.phone') }}</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0123456789">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- IC Number -->
                    <div>
                        <label for="ic_number" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.ic_number') }}</label>
                        <input type="text" name="ic_number" id="ic_number" value="{{ old('ic_number') }}" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="800101-01-1234">
                        @error('ic_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- House Selection -->
                    <div>
                        <label for="house_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.house') }} <span class="text-red-500">*</span></label>
                        <select name="house_id" id="house_id" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- {{ __('Pilih Rumah') }} --</option>
                            @foreach($houses as $house)
                                <option value="{{ $house->id }}" {{ old('house_id') == $house->id ? 'selected' : '' }}>
                                    {{ $house->full_address }}
                                </option>
                            @endforeach
                        </select>
                        @error('house_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Relationship -->
                    <div>
                        <label for="relationship" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.relationship') }} <span class="text-red-500">*</span></label>
                        <select name="relationship" id="relationship" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- {{ __('Pilih Hubungan') }} --</option>
                            <option value="owner" {{ old('relationship') === 'owner' ? 'selected' : '' }}>{{ __('messages.owner') }}</option>
                            <option value="spouse" {{ old('relationship') === 'spouse' ? 'selected' : '' }}>{{ __('messages.spouse') }}</option>
                            <option value="child" {{ old('relationship') === 'child' ? 'selected' : '' }}>{{ __('messages.child') }}</option>
                            <option value="family" {{ old('relationship') === 'family' ? 'selected' : '' }}>{{ __('messages.family') }}</option>
                            <option value="tenant" {{ old('relationship') === 'tenant' ? 'selected' : '' }}>{{ __('messages.tenant') }}</option>
                        </select>
                        @error('relationship')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.password') }} <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 pr-10">
                            <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute inset-y-0 flex items-center text-gray-400 hover:text-gray-600 transition-colors" style="right: 12px;" tabindex="-1">
                                <svg class="eye-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg class="eye-off-icon h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.confirm_password') }} <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 pr-10">
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute inset-y-0 flex items-center text-gray-400 hover:text-gray-600 transition-colors" style="right: 12px;" tabindex="-1">
                                <svg class="eye-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg class="eye-off-icon h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-blue-800">{{ __('Selepas pendaftaran, akaun anda perlu disahkan oleh pentadbir sebelum boleh mengakses sistem.') }}</p>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition min-h-touch">
                        {{ __('messages.register') }}
                    </button>
                </form>

                <p class="text-center text-gray-500 text-sm mt-6">
                    {{ __('Sudah mempunyai akaun?') }}
                    <a href="{{ route('login') }}" class="text-primary-600 font-medium hover:text-primary-800">{{ __('messages.login') }}</a>
                </p>
            </div>
        </div>
    </div>
    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const eyeIcon = button.querySelector('.eye-icon');
            const eyeOffIcon = button.querySelector('.eye-off-icon');
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
