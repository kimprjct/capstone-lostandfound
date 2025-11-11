@extends('layouts.app')

@section('title', 'User Management')

@section('page-title', 'User Management')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold"></h2>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-user-plus mr-2"></i> Create Tenant
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('admin.users.index') }}" class="px-3 py-1 rounded {{ request()->has('role') ? 'bg-gray-200' : 'bg-blue-500 text-white' }}">
                    All Users
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="px-3 py-1 rounded {{ request('role') === 'admin' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                    Admins
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'tenant']) }}" class="px-3 py-1 rounded {{ request('role') === 'tenant' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                    Tenants
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'user']) }}" class="px-3 py-1 rounded {{ request('role') === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                    End Users
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">User</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Contact Info</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Role</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Organization</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-6">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-bold">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->address }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-6">
                                <p>{{ $user->email }}</p>
                                <p>{{ $user->phone_number }}</p>
                            </td>
                            <td class="py-3 px-6">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                    {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 
                                       ($user->role === 'tenant' ? 'bg-blue-100 text-blue-800' : 
                                       'bg-green-100 text-green-800') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="py-3 px-6">
                                {{ $user->organization ? $user->organization->name : 'N/A' }}
                            </td>
                            <td class="py-3 px-6">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="text-yellow-500 hover:text-yellow-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-3 px-6 text-center text-gray-500">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
