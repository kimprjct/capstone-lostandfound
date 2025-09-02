@extends('layouts.app')

@section('title', $organization->name)

@section('page-title', $organization->name)

@section('content')
    <div class="mb-6">
        <a href="{{ url('/organizations') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
            <i class="fas fa-arrow-left mr-2"></i> Back to Organizations
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="md:flex">
            <div class="md:w-1/3 bg-gray-50 p-6 flex flex-col items-center justify-center border-r">
                @if($organization->logo)
                    <img class="h-48 w-48 object-contain mb-4" src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }} logo">
                @else
                    <div class="h-48 w-48 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                        <span class="text-6xl font-bold text-indigo-500">{{ substr($organization->name, 0, 1) }}</span>
                    </div>
                @endif
                <h2 class="text-2xl font-bold text-center">{{ $organization->name }}</h2>
                <p class="text-gray-600 mt-2 text-center">{{ $organization->address }}</p>
                <p class="text-sm text-gray-500 mt-4">Member since {{ $organization->created_at->format('F Y') }}</p>
            </div>
            
            <div class="md:w-2/3 p-6">
                <h3 class="text-xl font-bold mb-4">Organization Stats</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $organization->users->count() }}</p>
                        <p class="text-sm text-gray-600">Staff Members</p>
                    </div>
                    
                    <div class="bg-red-50 p-4 rounded-lg text-center">
                        <p class="text-2xl font-bold text-red-600">{{ $organization->lostItems->count() }}</p>
                        <p class="text-sm text-gray-600">Lost Items</p>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $organization->foundItems->count() }}</p>
                        <p class="text-sm text-gray-600">Found Items</p>
                    </div>
                    
                    <div class="bg-yellow-50 p-4 rounded-lg text-center">
                        @php
                            $claimsCount = 0;
                            foreach($organization->foundItems as $foundItem) {
                                $claimsCount += $foundItem->claims->count();
                            }
                        @endphp
                        <p class="text-2xl font-bold text-yellow-600">{{ $claimsCount }}</p>
                        <p class="text-sm text-gray-600">Claims</p>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <h3 class="text-lg font-bold mb-4">Recent Found Items</h3>
                    
                    @if($organization->foundItems->count() > 0)
                        <ul class="divide-y">
                            @foreach($organization->foundItems->take(5) as $item)
                                <li class="py-2">
                                    <p class="font-medium">{{ $item->title }}</p>
                                    <p class="text-sm text-gray-600">Found on {{ $item->date_found ? $item->date_found->format('M d, Y') : 'Unknown Date' }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500">No found items yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($organization->users->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-4">Staff Members</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($organization->users->take(6) as $staff)
                        <div class="border rounded-lg p-4">
                            <p class="font-semibold">{{ $staff->first_name }} {{ $staff->last_name }}</p>
                            <p class="text-sm text-gray-600">{{ $staff->email }}</p>
                            <p class="text-xs text-gray-500 mt-1">Member since {{ $staff->created_at->format('M Y') }}</p>
                        </div>
                    @endforeach
                </div>
                
                @if($organization->users->count() > 6)
                    <div class="mt-4 text-center">
                        <p class="text-gray-600">+ {{ $organization->users->count() - 6 }} more staff members</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
