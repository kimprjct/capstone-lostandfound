<div class="px-4 py-4 mb-6" style="border-bottom: 1px solid rgba(255, 255, 255, 0.2);">
    <div class="flex flex-col items-center text-center">
        <div class="mb-3 bg-white rounded-full p-1 shadow-md flex items-center justify-center" style="width: 80px; height: 80px;">
            <div class="text-2xl font-bold" style="color: #4338ca;">
                LF
            </div>
        </div>
        <h3 class="text-lg font-semibold text-white">Lost & Found</h3>
        <p class="text-sm text-gray-300">User Portal</p>
    </div>
</div>

<ul class="space-y-3">
    <li>
        <a href="{{ route('user.dashboard') }}" class="flex items-center py-2 px-4 text-white hover:bg-indigo-700 rounded-md {{ request()->routeIs('user.dashboard') ? 'bg-indigo-700' : '' }}">
            <i class="fas fa-tachometer-alt mr-3"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li>
        <a href="{{ route('user.lost-items.index') }}" class="flex items-center py-2 px-4 text-white hover:bg-indigo-700 rounded-md {{ request()->routeIs('user.lost-items.*') ? 'bg-indigo-700' : '' }}">
            <i class="fas fa-search mr-3"></i>
            <span>Report Lost Item</span>
        </a>
    </li>
    <li>
        <a href="{{ route('user.claims.index') }}" class="flex items-center py-2 px-4 text-white hover:bg-indigo-700 rounded-md {{ request()->routeIs('user.claims.*') ? 'bg-indigo-700' : '' }}">
            <i class="fas fa-clipboard-check mr-3"></i>
            <span>My Claims</span>
        </a>
    </li>
    <li>
        <a href="{{ route('user.settings') }}" class="flex items-center py-2 px-4 text-white hover:bg-indigo-700 rounded-md {{ request()->routeIs('user.settings') ? 'bg-indigo-700' : '' }}">
            <i class="fas fa-cog mr-3"></i>
            <span>Settings</span>
        </a>
    </li>
    <li>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center py-2 px-4 text-white hover:bg-indigo-700 rounded-md">
                <i class="fas fa-sign-out-alt mr-3"></i>
                <span>Logout</span>
            </button>
        </form>
    </li>
</ul>
