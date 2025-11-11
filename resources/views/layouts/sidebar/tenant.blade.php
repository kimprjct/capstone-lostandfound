@php
    // Organization + theme defaults
    $organization = auth()->user() && auth()->user()->organization ? auth()->user()->organization : null;
    $colorTheme = $organization && $organization->color_theme ? $organization->color_theme : 'indigo';
    $sidebarBg = $organization && $organization->sidebar_bg ? $organization->sidebar_bg : 'default';
    
    // Color map
    $colorMap = [
        'indigo' => [
            'base' => '#4338ca',    // indigo-700
            'hover' => '#3730a3',   // indigo-800
            'border' => '#312e81'   // indigo-900
        ],
        'blue' => [
            'base' => '#1d4ed8',    // blue-700
            'hover' => '#1e40af',   // blue-800
            'border' => '#1e3a8a'   // blue-900
        ],
        'green' => [
            'base' => '#15803d',    // green-700
            'hover' => '#166534',   // green-800
            'border' => '#14532d'   // green-900
        ],
        'red' => [
            'base' => '#b91c1c',    // red-700
            'hover' => '#991b1b',   // red-800
            'border' => '#7f1d1d'   // red-900
        ],
        'purple' => [
            'base' => '#7e22ce',    // purple-700
            'hover' => '#6b21a8',   // purple-800
            'border' => '#581c87'   // purple-900
        ],
        'pink' => [
            'base' => '#be185d',    // pink-700
            'hover' => '#9d174d',   // pink-800
            'border' => '#831843'   // pink-900
        ],
        'yellow' => [
            'base' => '#a16207',    // yellow-700
            'hover' => '#854d0e',   // yellow-800
            'border' => '#713f12'   // yellow-900
        ],
        'gray' => [
            'base' => '#374151',    // gray-700
            'hover' => '#1f2937',   // gray-800
            'border' => '#111827'   // gray-900
        ]
    ];
    
    $colors = isset($colorMap[$colorTheme]) ? $colorMap[$colorTheme] : $colorMap['indigo'];
    
    // Styles used in inline attributes
    $borderStyle = "border-bottom: 1px solid {$colors['border']};";
    $hoverStyle = "transition: background-color 0.2s;";
    $activeStyle = "background-color: {$colors['hover']};";
@endphp

@if($organization)
<div class="px-4 py-4 mb-6" style="{{ $borderStyle }}">
    <div class="flex flex-col items-center text-center">
        @if($organization->logo)
            <div class="mb-3 bg-white rounded-full p-1 shadow-md" style="width: 80px; height: 80px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <img src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }}" class="max-h-full max-w-full object-contain">
            </div>
        @else
            <div class="mb-3 bg-white rounded-full p-1 shadow-md flex items-center justify-center" style="width: 80px; height: 80px;">
                <div class="text-2xl font-bold" style="color: {{ $colors['base'] }}">
                    {{ isset($organization->name) ? strtoupper(substr($organization->name, 0, 2)) : 'OR' }}
                </div>
            </div>
        @endif

        {{-- Name should wrap into two (or more) lines instead of truncating --}}
        <h3 class="text-lg font-semibold text-white text-center break-words whitespace-normal leading-tight w-full">
            {{ $organization->name }}
        </h3>
    </div>
</div>
@else
<div class="px-4 py-4 mb-6" style="{{ $borderStyle }}">
    <div class="flex flex-col items-center text-center">
        <div class="mb-3 bg-white rounded-full p-1 shadow-md flex items-center justify-center" style="width: 80px; height: 80px;">
            <div class="text-2xl font-bold" style="color: {{ $colors['base'] }}">
                LF
            </div>
        </div>
        <h3 class="text-lg font-semibold text-white">Lost & Found</h3>
    </div>
</div>
@endif

<ul class="space-y-3">
    <li>
        <a href="{{ route('tenant.dashboard') }}" class="flex items-center py-2 px-4 text-white rounded-md" style="{{ $hoverStyle }} {{ request()->routeIs('tenant.dashboard') ? $activeStyle : '' }}" onmouseover="this.style.backgroundColor='{{ $colors['hover'] }}'" onmouseout="this.style.backgroundColor='{{ request()->routeIs('tenant.dashboard') ? $colors['hover'] : 'transparent' }}'">
            <i class="fas fa-tachometer-alt mr-3"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li>
        <a href="{{ route('tenant.staff.index') }}" class="flex items-center py-2 px-4 text-white rounded-md" style="{{ $hoverStyle }} {{ request()->routeIs('tenant.staff.*') ? $activeStyle : '' }}" onmouseover="this.style.backgroundColor='{{ $colors['hover'] }}'" onmouseout="this.style.backgroundColor='{{ request()->routeIs('tenant.staff.*') ? $colors['hover'] : 'transparent' }}'">
            <i class="fas fa-users-cog mr-3"></i>
            <span>User Management</span>
        </a>
    </li>
    <li>
        <a href="{{ route('tenant.lost-items.index') }}" class="flex items-center py-2 px-4 text-white rounded-md" style="{{ $hoverStyle }} {{ request()->routeIs('tenant.lost-items.*') ? $activeStyle : '' }}" onmouseover="this.style.backgroundColor='{{ $colors['hover'] }}'" onmouseout="this.style.backgroundColor='{{ request()->routeIs('tenant.lost-items.*') ? $colors['hover'] : 'transparent' }}'">
            <i class="fas fa-search mr-3"></i>
            <span>Lost Items</span>
        </a>
    </li>
    <li>
        <a href="{{ route('tenant.found-items.index') }}" class="flex items-center py-2 px-4 text-white rounded-md" style="{{ $hoverStyle }} {{ request()->routeIs('tenant.found-items.*') ? $activeStyle : '' }}" onmouseover="this.style.backgroundColor='{{ $colors['hover'] }}'" onmouseout="this.style.backgroundColor='{{ request()->routeIs('tenant.found-items.*') ? $colors['hover'] : 'transparent' }}'">
            <i class="fas fa-box-open mr-3"></i>
            <span>Found Items</span>
        </a>
    </li>
    <li>
        <a href="{{ route('tenant.claims.index') }}" class="flex items-center py-2 px-4 text-white rounded-md" style="{{ $hoverStyle }} {{ request()->routeIs('tenant.claims.*') ? $activeStyle : '' }}" onmouseover="this.style.backgroundColor='{{ $colors['hover'] }}'" onmouseout="this.style.backgroundColor='{{ request()->routeIs('tenant.claims.*') ? $colors['hover'] : 'transparent' }}'">
            <i class="fas fa-clipboard-check mr-3"></i>
            <span>Claims</span>
        </a>
    </li>
    <li>
        <a href="{{ route('tenant.settings') }}" class="flex items-center py-2 px-4 text-white rounded-md" style="{{ $hoverStyle }} {{ request()->routeIs('tenant.settings') ? $activeStyle : '' }}" onmouseover="this.style.backgroundColor='{{ $colors['hover'] }}'" onmouseout="this.style.backgroundColor='{{ request()->routeIs('tenant.settings') ? $colors['hover'] : 'transparent' }}'">
            <i class="fas fa-cog mr-3"></i>
            <span>Settings</span>
        </a>
    </li>
    <li>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center py-2 px-4 text-white rounded-md" style="{{ $hoverStyle }}" onmouseover="this.style.backgroundColor='{{ $colors['hover'] }}'" onmouseout="this.style.backgroundColor='transparent'">
                <i class="fas fa-sign-out-alt mr-3"></i>
                <span>Logout</span>
            </button>
        </form>
    </li>
</ul>
