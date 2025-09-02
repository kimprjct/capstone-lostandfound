@extends('layouts.app')

@section('title', 'User Details')

@section('page-title', 'User Details')

@section('content')
    <div class="bg-white shadow-md rounded-lg overflow-hidden max-w-2xl mx-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">User Details</h2>
                <div>
                    <a href="{{ route('admin.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                        Back to Users
                    </a>
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Edit User
                    </a>
                </div>
            </div>
            
            <div class="flex items-center mb-8">
                <div class="h-20 w-20 bg-gray-200 rounded-full flex items-center justify-center mr-6">
                    <span class="text-2xl font-bold">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</span>
                </div>
                <div>
                    <h3 class="text-2xl font-bold">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</h3>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold 
                        {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 
                           ($user->role === 'tenant' ? 'bg-blue-100 text-blue-800' : 
                           'bg-green-100 text-green-800') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-4">
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-2">Personal Information</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Full Name</p>
                            <p class="font-semibold">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Email Address</p>
                            <p class="font-semibold">{{ $user->email }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Contact Number</p>
                            <p class="font-semibold">{{ $user->phone_number }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Address</p>
                            <p class="font-semibold">{{ $user->address }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-2">Account Information</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">User Role</p>
                            <p class="font-semibold">{{ ucfirst($user->role) }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Organization</p>
                            <p class="font-semibold">{{ $user->organization ? $user->organization->name : 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Member Since</p>
                            <p class="font-semibold">{{ $user->created_at->format('F d, Y') }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="font-semibold">{{ $user->updated_at->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($user->id !== auth()->id())
                <div class="border-t border-gray-200 pt-6 mt-6 flex justify-end">
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                            Delete User
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
