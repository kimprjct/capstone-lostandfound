<div class="px-4 py-4 mb-6" style="border-bottom: 1px solid rgba(255, 255, 255, 0.2);">
    <div class="flex flex-col items-center text-center">
        <div class="mb-3 bg-white rounded-full p-1 shadow-md flex items-center justify-center" style="width: 80px; height: 80px;">
            <div class="text-2xl font-bold" style="color: #4338ca;">
                LF
            </div>
        </div>
        <h3 class="text-lg font-semibold text-white">Lost & Found Admin</h3>
    </div>
</div>

<ul class="space-y-3">
    <li>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center py-2 px-4 text-white hover:bg-indigo-700 rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-700' : '' }}">
            <i class="fas fa-tachometer-alt mr-3"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.organizations.index') }}" class="flex items-center py-2 px-4 text-white hover:bg-indigo-700 rounded-md {{ request()->routeIs('admin.organizations.*') ? 'bg-indigo-700' : '' }}">
            <i class="fas fa-building mr-3"></i>
            <span>Organizations</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.users.index') }}" class="flex items-center py-2 px-4 text-white hover:bg-indigo-700 rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-indigo-700' : '' }}">
            <i class="fas fa-users mr-3"></i>
            <span>User Management</span>
        </a>
    </li>
    <li>
        <a href="{{ route('admin.settings') }}" class="flex items-center py-2 px-4 text-white hover:bg-indigo-700 rounded-md {{ request()->routeIs('admin.settings') ? 'bg-indigo-700' : '' }}">
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
