@extends('layouts.app')

@section('title', 'All Organizations')

@section('page-title', 'Organizations Directory')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold">All Registered Organizations</h2>
        <p class="text-gray-600">Browse all registered organizations in our lost and found system</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($organizations as $organization)
            <a href="{{ url('/organizations/' . $organization->id) }}" class="bg-white rounded-lg shadow-md overflow-hidden block hover:shadow-lg transition-shadow duration-300">
                <div class="h-40 bg-gray-100 flex items-center justify-center">
                    @if($organization->logo)
                        <img class="h-full w-full object-cover" src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }} logo">
                    @else
                        <div class="h-24 w-24 bg-indigo-200 rounded-full flex items-center justify-center">
                            <span class="text-3xl font-bold text-indigo-600">{{ substr($organization->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="text-xl font-bold mb-2">{{ $organization->name }}</h3>
                    <p class="text-gray-600 mb-4">{{ $organization->address }}</p>
                    
                    <div class="flex justify-between text-sm">
                        <div>
                            <p><span class="font-semibold">Staff:</span> {{ $organization->users->where('role', 'tenant')->count() }}</p>
                            <p><span class="font-semibold">Founded:</span> {{ $organization->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p><span class="font-semibold">Lost Items:</span> {{ $organization->lostItems->count() }}</p>
                            <p><span class="font-semibold">Found Items:</span> {{ $organization->foundItems->count() }}</p>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500">No organizations found.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $organizations->links() }}
    </div>
@endsection
