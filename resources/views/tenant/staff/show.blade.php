@extends('layouts.tenantApp')

@section('title', $user->first_name . ' ' . $user->last_name)

@section('content')
<div class="space-y-8">

    {{-- User Info --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">{{ $user->first_name }} {{ $user->last_name }}</h2>
        <p class="text-gray-600"><strong>Email:</strong> {{ $user->email }}</p>
        <p class="text-gray-600"><strong>Phone:</strong> {{ $user->phone_number }}</p>
        <p class="text-gray-600"><strong>Address:</strong> {{ $user->address }}</p>
    </div>

    {{-- Lost Items Reported --}}
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h3 class="text-lg font-bold mb-4">Lost Items Reported</h3>

        @if($user->lostItems->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($user->lostItems as $item)
                    <div class="border rounded-lg shadow hover:shadow-md transition duration-200 bg-white overflow-hidden">
                        {{-- Image --}}
                        <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if($item->image_url)
                                <img src="{{ $item->image_url }}"
                                     alt="{{ $item->title }}"
                                     class="h-full w-full object-cover">
                            @else
                                <span class="text-gray-400">No Image</span>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="p-4">
                            <h4 class="text-lg font-semibold truncate">{{ $item->title }}</h4>
                            <p class="text-sm text-gray-500">
                                Reported on {{ $item->created_at->format('M d, Y') }}
                            </p>
                            <p class="text-sm text-gray-600 mt-2">
                                Status: 
                                <span class="font-medium {{ $item->status === 'claimed' ? 'text-green-600' : 'text-orange-600' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </p>
                            <a href="{{ route('tenant.lost-items.show', $item->id) }}"
                               class="mt-3 inline-block text-sm text-blue-600 hover:underline">
                                View Details →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No lost items reported in this organization.</p>
        @endif
    </div>

    {{-- Found Items Reported --}}
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h3 class="text-lg font-bold mb-4">Found Items Reported</h3>

        @if($user->foundItems->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($user->foundItems as $item)
                    <div class="border rounded-lg shadow hover:shadow-md transition duration-200 bg-white overflow-hidden">
                        {{-- Image --}}
                        <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if($item->image_url)
                                <img src="{{ $item->image_url }}"
                                     alt="{{ $item->title }}"
                                     class="h-full w-full object-cover">
                            @else
                                <span class="text-gray-400">No Image</span>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="p-4">
                            <h4 class="text-lg font-semibold truncate">{{ $item->title }}</h4>
                            <p class="text-sm text-gray-500">
                                Reported on {{ $item->created_at->format('M d, Y') }}
                            </p>
                            <p class="text-sm text-gray-600 mt-2">
                                Status: 
                                <span class="font-medium {{ $item->status === 'claimed' ? 'text-green-600' : 'text-orange-600' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </p>
                            <a href="{{ route('tenant.found-items.show', $item->id) }}"
                               class="mt-3 inline-block text-sm text-blue-600 hover:underline">
                                View Details →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No found items reported in this organization.</p>
        @endif
    </div>

    {{-- Claim Requests --}}
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h3 class="text-lg font-bold mb-4">Claim Requests</h3>

        @if($user->claims->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($user->claims as $claim)
                    @php
                        $claimedItem = $claim->lostItem ?? $claim->foundItem;
                    @endphp

                    <div class="border rounded-lg shadow hover:shadow-md transition duration-200 bg-white overflow-hidden">

                        {{-- Image --}}
                        <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if($claimedItem && $claimedItem->image_url)
                                <img src="{{ $claimedItem->image_url }}"
                                     alt="{{ $claimedItem->title ?? 'Item' }}"
                                     class="h-full w-full object-cover">
                            @else
                                <span class="text-gray-400">No Image</span>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="p-4">
                            <h4 class="text-lg font-semibold truncate">
                                {{ $claimedItem->title ?? 'Unknown Item' }}
                            </h4>

                            <p class="text-sm text-gray-600">
                                Reported by:
                                <span class="font-medium">
                                    {{ $claimedItem && $claimedItem->user
                                        ? $claimedItem->user->first_name . ' ' . $claimedItem->user->last_name
                                        : 'Unknown Reporter' }}
                                </span>
                            </p>

                            <p class="text-sm text-gray-500">
                                Requested on {{ $claim->created_at->format('M d, Y') }}
                            </p>

                            <p class="text-sm text-gray-600 mt-2">
                                Status: 
                                <span class="font-medium {{ $claim->status === 'approved' ? 'text-green-600' : ($claim->status === 'rejected' ? 'text-red-600' : 'text-orange-600') }}">
                                    {{ ucfirst($claim->status) }}
                                </span>
                            </p>

                            @if($claimedItem)
                                @if($claim->lostItem)
                                    <a href="{{ route('tenant.lost-items.show', $claimedItem->id) }}"
                                       class="mt-3 inline-block text-sm text-blue-600 hover:underline">
                                        View Lost Item →
                                    </a>
                                @elseif($claim->foundItem)
                                    <a href="{{ route('tenant.found-items.show', $claimedItem->id) }}"
                                       class="mt-3 inline-block text-sm text-blue-600 hover:underline">
                                        View Found Item →
                                    </a>
                                @endif
                            @else
                                <p class="text-xs text-red-500 mt-2">This item no longer exists.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No claim requests in this organization.</p>
        @endif
    </div>
</div>
@endsection
