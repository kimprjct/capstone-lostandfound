<!-- resources/views/auth/login.blade.php -->
<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#E8C0D8] via-[#DEB1A8] to-[#C2AA6A] p-6">
        <div class="bg-white shadow-xl rounded-2xl flex flex-col md:flex-row overflow-hidden max-w-4xl w-full animate-fade-in">
            
            <!-- Left (Login Form) -->
            <div class="w-full md:w-1/2 p-8">
                <div class="flex justify-center mb-1">
                    <a href="/">
                        <img src="/images/pet-logo.png" alt="SuPehDah Logo" class="w-[120px] h-[120px] object-contain">
                    </a>
                </div>

                <h1 class="text-center text-2xl font-bold text-[#1C5B38] mb-1">SuPehDah</h1>
                <p class="text-center text-gray-600 text-sm mb-6">Login to your account</p>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Validation Errors -->
                <x-auth-validation-errors class="mb-4" :errors="$errors" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div>
                        <x-label for="email" :value="__('Email')" class="text-[#3D7256]" />
                        <x-input id="email" class="block mt-1 w-full border-[#C2AA6A] focus:ring-[#1C5B38] focus:border-[#1C5B38]" type="email" name="email" :value="old('email')" required autofocus />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <x-label for="password" :value="__('Password')" class="text-[#3D7256]" />
                        <x-input id="password" class="block mt-1 w-full border-[#C2AA6A] focus:ring-[#1C5B38] focus:border-[#1C5B38]" type="password" name="password" required autocomplete="current-password" />
                    </div>

                    <!-- Remember Me -->
                    <div class="block mt-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#3D7256] shadow-sm focus:ring-[#1C5B38]" name="remember">
                            <span class="ml-2 text-sm text-gray-700">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between mt-6">
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-[#1C5B38] hover:text-[#09371F]" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif

                        <x-button class="ml-3 bg-[#C2AA6A] hover:bg-[#b69a5f] text-white">
                            {{ __('Log in') }}
                        </x-button>
                    </div>
                </form>
            </div>

            <!-- Right (Image/Design) -->
            <div class="hidden md:flex w-1/2 relative">
                <img src="/images/welcome2.jpg" alt="Side Illustration" class="w-full h-full object-cover brightness-90">
                <!-- <div class="absolute bottom-6 left-6 bg-[#1C5B38]/80 px-4 py-2 rounded-lg">
                    <span class="text-white text-xl font-semibold tracking-wide">SuPehDah</span> -->
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
