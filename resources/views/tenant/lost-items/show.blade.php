@extends('layouts.tenantApp')

@section('page-title', 'Lost Item Details')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">

                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Lost Item Details</h2>
                    <a href="{{ route('tenant.lost-items.index') }}" 
                       class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>

                <!-- Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left: Image -->
                    <div class="flex justify-center items-center bg-gray-50 p-6 rounded-lg">
                        @if($lostItem->image)
                            <img src="{{ asset('storage/' . $lostItem->image) }}" 
                                 alt="{{ $lostItem->title }}" 
                                 class="rounded-xl shadow-md max-h-[400px] object-contain">
                        @else
                            <div class="w-full h-64 flex items-center justify-center bg-gray-200 rounded-xl">
                                <span class="text-gray-500">No Image Available</span>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Details -->
                    <div class="p-6">
                        <h3 class="text-2xl font-semibold text-gray-900 mb-4">{{ $lostItem->title }}</h3>

                        <div class="space-y-3 text-gray-700">
                            <p><span class="font-medium text-gray-900">Category:</span> {{ $lostItem->category ?? 'N/A' }}</p>
                            <p><span class="font-medium text-gray-900">Description:</span> {{ $lostItem->description ?? 'N/A' }}</p>
                            <p><span class="font-medium text-gray-900">Location Lost:</span> {{ $lostItem->location ?? 'N/A' }}</p>
                            <p><span class="font-medium text-gray-900">Date Lost:</span> {{ $lostItem->date_lost ? $lostItem->date_lost->format('F d, Y') : 'N/A' }}</p>
                            <p>
                                <span class="font-medium text-gray-900">Status:</span> 
                                <span class="px-2 py-1 text-sm rounded-full 
                                    @if($lostItem->status == 'lost') bg-yellow-100 text-yellow-700 
                                    @elseif($lostItem->status == 'claimed') bg-green-100 text-green-700 
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($lostItem->status) }}
                                </span>
                            </p>
                        </div>

                        <hr class="my-6">

                        <!-- Reporter Information -->
                        <h4 class="text-xl font-semibold text-gray-900 mb-3">Reported By</h4>
                        <div class="space-y-2 text-gray-700">
                        <p>
                            <span class="font-medium text-gray-900">Name:</span> 
                            @if($lostItem->user)
                                <a href="{{ route('tenant.staff.show', $lostItem->user->id) }}" class="text-blue-600 hover:underline">
                                    {{ trim($lostItem->user->first_name . ' ' . ($lostItem->user->middle_name ?? '') . ' ' . $lostItem->user->last_name) }}
                                </a>
                            @else
                                N/A
                            @endif
                        </p>

                            <p>
                                <span class="font-medium text-gray-900">Email:</span> 
                                {{ $lostItem->user->email ?? 'N/A' }}
                            </p>
                            <p>
                                <span class="font-medium text-gray-900">Contact:</span> 
                                {{ $lostItem->user->phone_number ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
