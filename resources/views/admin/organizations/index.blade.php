@extends('layouts.app')

@section('title', 'Organizations')

@section('page-title', 'Organizations')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold"></h2>
        <a href="{{ route('admin.organizations.create') }}" 
           class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Register Organization
        </a>
    </div>

    @if($organizations->isEmpty())
        <p class="text-center text-gray-500 py-10">No organizations found</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($organizations as $organization)
                <a href="{{ route('admin.organizations.show', $organization->id) }}" 
                   class="bg-white shadow-md rounded-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-2xl flex flex-col cursor-pointer">
                    
                    <div class="flex flex-col items-center p-6 border-b border-gray-200">
                        @if($organization->logo)
                            <img class="h-32 w-32 rounded-full object-cover mb-3" 
                                 src="{{ asset('storage/' . $organization->logo) }}" 
                                 alt="{{ $organization->name }} logo">
                        @else
                            <div class="h-24 w-24 bg-gray-200 rounded-full flex items-center justify-center mb-3">
                                <span class="text-2xl font-bold text-gray-600">
                                    {{ substr($organization->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <div class="text-center">
                            <p class="text-xl font-semibold text-gray-900">{{ $organization->name }}</p>
                            <p class="text-sm text-gray-500">Created {{ $organization->created_at ? $organization->created_at->format('M d, Y') : 'Unknown' }}</p>
                        </div>
                    </div>

                    <div class="p-4 flex-grow">
                        <p class="text-gray-700 mb-3">{{ $organization->address ? Str::limit($organization->address, 80) : 'No address provided' }}</p>
                        <div class="flex justify-between text-sm text-gray-600 font-medium">
                            <div>
                                Staff: {{ $organization->users ? $organization->users->where('role', 'tenant')->count() : 0 }}
                            </div>
                            <div>
                                Lost: {{ $organization->lostItems ? $organization->lostItems->count() : 0 }} | Found: {{ $organization->foundItems ? $organization->foundItems->count() : 0 }}
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6 px-6 py-4 flex justify-center">
            {{ $organizations->links() }}
        </div>
    @endif
@endsection
