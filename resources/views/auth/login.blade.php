<!-- resources/views/auth/login.blade.php -->
<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#1E2D3D] p-6 relative overflow-hidden">
        <!-- Layered gradient spots for subtle background -->
        <div class="absolute top-0 left-0 w-64 h-64 sm:w-80 sm:h-80 lg:w-96 lg:h-96 bg-gradient-to-br from-[#1E2D3D] to-[#3A5068] rounded-full opacity-60"></div>
        <div class="absolute top-24 -right-12 w-72 h-72 sm:w-80 sm:h-80 lg:w-96 lg:h-96 bg-gradient-to-bl from-[#3A5068] to-[#1E2D3D] rounded-full opacity-50"></div>
        <div class="absolute -bottom-32 left-10 w-80 h-80 sm:w-96 sm:h-96 lg:w-[28rem] lg:h-[28rem] bg-gradient-to-tr from-[#1E2D3D] to-[#3A5068] rounded-full opacity-45"></div>
        
        <!-- Floating blurred decorative blobs -->
        <div class="absolute top-36 -left-10 w-36 h-36 sm:w-40 sm:h-40 lg:w-44 lg:h-44 bg-white/12 rounded-full blur-sm animate-float-1"></div>
        <div class="absolute top-1/2 -right-8 w-28 h-28 sm:w-32 sm:h-32 lg:w-36 lg:h-36 bg-white/8 rounded-full blur-sm animate-float-2"></div>
        <div class="absolute top-20 right-1/4 w-20 h-20 sm:w-24 sm:h-24 lg:w-28 lg:h-28 bg-white/6 rounded-full blur-sm animate-float-1"></div>
        <div class="absolute bottom-40 left-1/3 w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 bg-white/10 rounded-full blur-sm animate-float-3"></div>
        
        <div class="bg-white/10 backdrop-blur-md shadow-2xl rounded-2xl flex flex-col md:flex-row overflow-hidden max-w-4xl w-full animate-fade-in border border-white/20">
            
            <!-- Left (Login Form) -->
            <div class="w-full md:w-1/2 p-8 relative z-10">
                <div class="flex justify-center mb-1">
                    <a href="/">
                        <img src="/images/foundu-logo.png" alt="FoundU Logo" class="w-[120px] h-[120px] object-contain">
                    </a>
                </div>

                <h1 class="text-center text-2xl font-bold text-white mb-1">FoundU</h1>
                <p class="text-center text-gray-300 text-sm mb-6">Login to your account</p>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Validation Errors -->
                <x-auth-validation-errors class="mb-4" :errors="$errors" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div>
                        <x-label for="email" :value="__('Email')" class="text-gray-300" />
                        <x-input id="email" class="block mt-1 w-full bg-[#2E3B4E]/50 border-gray-400/30 text-white placeholder-gray-400 focus:ring-[#00B4D8] focus:border-[#00B4D8] backdrop-blur-sm" type="email" name="email" :value="old('email')" required autofocus />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <x-label for="password" :value="__('Password')" class="text-gray-300" />
                        <x-input id="password" class="block mt-1 w-full bg-[#2E3B4E]/50 border-gray-400/30 text-white placeholder-gray-400 focus:ring-[#00B4D8] focus:border-[#00B4D8] backdrop-blur-sm" type="password" name="password" required autocomplete="current-password" />
                    </div>

                    <!-- Remember Me -->
                    <div class="block mt-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-400/30 text-[#00B4D8] shadow-sm focus:ring-[#00B4D8] bg-[#2E3B4E]/50" name="remember">
                            <span class="ml-2 text-sm text-gray-300">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between mt-6">
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-[#00B4D8] hover:text-[#0096C7]" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif

                        <x-button class="bg-[#00B4D8] hover:bg-[#0096C7] text-[#1E2D3D] font-bold shadow-lg shadow-[#00B4D8]/35 px-8">
                            {{ __('Log in') }}
                        </x-button>
                    </div>
                </form>
            </div>

            <!-- Right (Image/Design) -->
            <div class="hidden md:flex w-1/2 relative">
                <img src="/images/lost-found-banner.jpg" alt="Side Illustration" class="w-full h-full object-cover brightness-75">
                <div class="absolute inset-0 bg-gradient-to-t from-[#1E2D3D]/60 to-transparent"></div>
            </div>
        </div>
    </div>
</x-guest-layout>
