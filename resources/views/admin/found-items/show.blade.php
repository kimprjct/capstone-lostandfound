@extends('layouts.app')

@section('title', 'Found Item Details')
@section('page-title', 'Found Item Details')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Found Item Details</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">Title</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $foundItem->title }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Category</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $foundItem->category }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-gray-500">Description</dt>
                        <dd class="mt-1 text-gray-900">{{ $foundItem->description }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Location Found</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $foundItem->location }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Date Found</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $foundItem->date_found ? \Carbon\Carbon::parse($foundItem->date_found)->format('M d, Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @php($status = strtolower($foundItem->status))
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $status === 'claimed' ? 'bg-green-100 text-green-700' : ($status === 'returned' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($foundItem->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <h2 class="text-lg font-semibold mb-4">Finder Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">Finder</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $foundItem->user->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $foundItem->user->email ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Organization</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $foundItem->organization->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Reported On</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $foundItem->created_at ? \Carbon\Carbon::parse($foundItem->created_at)->format('M d, Y H:i') : '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Image Gallery -->
        <div class="space-y-6">
            <div class="bg-white shadow rounded-xl p-4">
                <h2 class="text-lg font-semibold mb-4">Item Photo</h2>
                @php(
                    $firstPhoto = ($foundItem->photos && $foundItem->photos->count()) ? asset('storage/' . $foundItem->photos->first()->image_path) : null
                )
                @php($primaryImage = $firstPhoto ?? $foundItem->image_url)
                @if($primaryImage)
                    <img src="{{ $primaryImage }}" alt="Found item photo" class="w-full h-72 object-cover rounded-lg">
                @else
                    <div class="w-full h-72 flex items-center justify-center bg-gray-100 text-gray-400 rounded-lg">No photo uploaded</div>
                @endif
            </div>

            @if(($foundItem->photos && $foundItem->photos->count() > 1))
            <div class="bg-white shadow rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">More Photos</h3>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($foundItem->photos->skip(1) as $photo)
                        <img src="{{ asset('storage/' . $photo->image_path) }}" alt="Found item photo" class="w-full h-24 object-cover rounded-md"/>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
