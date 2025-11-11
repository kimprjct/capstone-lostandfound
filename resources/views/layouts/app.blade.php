<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Lost & Found Management') }} - @yield('title')</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('styles')
    <style>
        /* Slightly darker backgrounds for admin pages */
        .admin-tone .bg-white { background-color: #f3f4f6 !important; } /* tailwind gray-100 */
        .admin-tone .hover\:bg-gray-100:hover { background-color: #e5e7eb !important; } /* gray-200 */
        .admin-tone .text-gray-700 { color: #374151; }
        .admin-tone .text-gray-600 { color: #4b5563; }
        .admin-tone .text-gray-800 { color: #1f2937; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        @auth
            @php
                $organization = auth()->user() && auth()->user()->organization ? auth()->user()->organization : null;
                $colorTheme = $organization && $organization->color_theme ? $organization->color_theme : 'indigo';
                $sidebarBg = $organization && $organization->sidebar_bg ? $organization->sidebar_bg : 'default';
                
                // Set default sidebar background class based on color theme
                $colorMap = [
                    'indigo' => '#4338ca',   // indigo-700
                    'blue' => '#1d4ed8',     // blue-700
                    'green' => '#15803d',    // green-700
                    'red' => '#b91c1c',      // red-700
                    'purple' => '#7e22ce',   // purple-700
                    'pink' => '#be185d',     // pink-700
                    'yellow' => '#a16207',   // yellow-700
                    'gray' => '#374151'      // gray-700
                ];
                
                $baseColor = isset($colorMap[$colorTheme]) ? $colorMap[$colorTheme] : $colorMap['indigo'];
                $darkerColor = isset($colorMap[$colorTheme]) ? $colorMap[$colorTheme] : $colorMap['indigo']; 
                
                // Apply additional styling based on the sidebar background type
                $sidebarStyle = "background-color: {$baseColor};";
                if ($sidebarBg === 'gradient') {
                    $sidebarStyle = "background: linear-gradient(135deg, {$baseColor} 0%, shade-color({$baseColor}, 20%) 100%);";
                } elseif ($sidebarBg === 'pattern-dots') {
                    $sidebarStyle = "background-color: {$baseColor}; background-image: radial-gradient(rgba(255, 255, 255, 0.15) 1px, transparent 1px); background-size: 10px 10px;";
                } elseif ($sidebarBg === 'pattern-lines') {
                    $sidebarStyle = "background-color: {$baseColor}; background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.1) 50%, rgba(255, 255, 255, 0.1) 75%, transparent 75%, transparent); background-size: 20px 20px;";
                } elseif ($sidebarBg === 'pattern-grid') {
                    $sidebarStyle = "background-color: {$baseColor}; background-image: linear-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px), linear-gradient(to right, rgba(255, 255, 255, 0.1) 1px, transparent 1px); background-size: 20px 20px;";
                }
            @endphp
            <div class="w-64 text-white fixed h-full" style="<?php echo $sidebarStyle; ?>">
                <div class="p-4">
                    @if(auth()->user()->role === 'admin')
                        @include('layouts.sidebar.admin')
                    @elseif(auth()->user()->role === 'tenant')
                        @include('layouts.sidebar.tenant')
                    @else
                        @include('layouts.sidebar.user')
                    @endif
                </div>
            </div>
            
            <!-- Content -->
            <div class="flex-1 ml-64<?php echo (auth()->user()->role === 'admin') ? ' admin-tone' : ''; ?>">
                <!-- Top Navigation -->
                <nav class="bg-white shadow-md p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold">@yield('page-title', 'Dashboard')</h2>
                            @if(auth()->user()->role === 'tenant' && auth()->user()->organization)
                                <p class="text-sm text-gray-600">{{ auth()->user()->organization->name }}</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center">
                            <div class="mr-6">
                                <a href="{{ url('/organizations') }}" class="text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-building mr-1"></i> Organizations
                                </a>
                            </div>
                            
                            <!-- Notification Bell -->
                            <div class="mr-4">
                                @include('components.notification-bell')
                            </div>
                            
                            <div class="mr-4">
                                <span class="text-sm text-gray-700">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                            </div>
                            
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-gray-700 focus:outline-none">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-bold">{{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}</span>
                                    </div>
                                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                                    
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Main Content -->
                <main class="p-6">
                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif
                    
                    @yield('content')
                </main>
            </div>
        @else
            <!-- Guest Layout -->
            <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
                @yield('content')
            </div>
        @endauth
    </div>
    
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
</body>
</html>
