<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h1 class="font-bold text-xl text-gray-900">e-Yuran</h1>
                        <p class="text-xs text-gray-500">Taman Tropika Kajang</p>
                    </div>
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
                        <input type="password" name="password" id="password" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.confirm_password') }} <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
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
</body>
</html>
