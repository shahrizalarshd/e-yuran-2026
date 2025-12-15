@extends('layouts.app')

@section('title', __('messages.profile'))

@section('page-title', __('messages.profile'))

@section('content')
<div class="space-y-6">
    <!-- Profile Information -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.profile_information') }}</h2>
                <p class="text-sm text-gray-500">{{ __('messages.profile_information_desc') }}</p>
            </div>
        </div>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('patch')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" required>
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <p class="text-sm text-amber-800">
                            {{ __('messages.email_unverified') }}
                            <button form="send-verification" class="text-amber-600 hover:text-amber-800 underline font-medium">
                                {{ __('messages.resend_verification') }}
                            </button>
                        </p>
                    </div>
                @endif

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm text-green-600 bg-green-50 p-3 rounded-xl">
                        {{ __('messages.verify_email_sent') }}
                    </p>
                @endif
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('messages.save') }}
                </button>
            </div>

            @if (session('status') === 'profile-updated')
                <p class="text-sm text-green-600 bg-green-50 p-3 rounded-xl">
                    {{ __('messages.profile_updated') }}
                </p>
            @endif
        </form>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
            @csrf
        </form>
    </div>

    <!-- Update Password -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.change_password') }}</h2>
                <p class="text-sm text-gray-500">{{ __('messages.change_password_desc') }}</p>
            </div>
        </div>

        <form method="post" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            @method('put')

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.current_password') }}</label>
                <input type="password" name="current_password" id="current_password" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                @error('current_password', 'updatePassword')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.new_password') }}</label>
                <input type="password" name="password" id="password" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                @error('password', 'updatePassword')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.confirm_new_password') }}</label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                @error('password_confirmation', 'updatePassword')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    {{ __('messages.change_password') }}
                </button>
            </div>

            @if (session('status') === 'password-updated')
                <p class="text-sm text-green-600 bg-green-50 p-3 rounded-xl">
                    {{ __('messages.password_updated') }}
                </p>
            @endif
        </form>
    </div>

    <!-- Delete Account -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border-2 border-red-100">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-red-900">{{ __('messages.delete_account') }}</h2>
                <p class="text-sm text-red-600">{{ __('messages.delete_account_desc') }}</p>
            </div>
        </div>

        @if(auth()->user()->resident && auth()->user()->resident->currentOccupancy())
            @php
                $house = auth()->user()->resident->currentOccupancy()->house;
                $hasOutstanding = $house ? $house->outstanding_amount > 0 : false;
            @endphp
            @if($hasOutstanding)
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm text-red-700">
                            {{ __('messages.delete_account_warning') }}
                        </p>
                    </div>
                </div>
            @endif
        @endif

        <div x-data="{ showDeleteModal: false }">
            <button @click="showDeleteModal = true" type="button" 
                class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('messages.delete_account') }}
            </button>

            <!-- Delete Confirmation Modal -->
            <div x-show="showDeleteModal" x-cloak
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div @click.away="showDeleteModal = false" 
                    class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">
                    
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.delete_account_confirm') }}</h3>
                        </div>
                    </div>

                    <p class="text-gray-600 mb-6">
                        {{ __('messages.delete_account_confirm_desc') }}
                    </p>

                    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
                        @csrf
                        @method('delete')

                        <div>
                            <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.password') }}</label>
                            <input type="password" name="password" id="delete_password" 
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                                placeholder="{{ __('messages.password') }}">
                            @error('password', 'userDeletion')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showDeleteModal = false"
                                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-all">
                                {{ __('messages.cancel') }}
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-3 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition-all">
                                {{ __('messages.delete_account') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
