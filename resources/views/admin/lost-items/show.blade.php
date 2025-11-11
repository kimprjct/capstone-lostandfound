@extends('layouts.app')

@section('title', 'Lost Item Details')
@section('page-title', 'Lost Item Details')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-gray-50 min-h-screen">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Lost Item Details</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.lost-items.edit', $lostItem) }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Edit</a>
            <a href="{{ route('admin.lost-items.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">Back to List</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow rounded-xl p-6 ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">Title</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $lostItem->title }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Category</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $lostItem->category }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-gray-500">Description</dt>
                        <dd class="mt-1 text-gray-900">{{ $lostItem->description }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Location Lost</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $lostItem->location }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Date Lost</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $lostItem->date_lost ? \Carbon\Carbon::parse($lostItem->date_lost)->format('M d, Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @php($status = strtolower($lostItem->status))
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $status === 'resolved' ? 'bg-green-100 text-green-700' : ($status === 'returned' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($lostItem->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white shadow rounded-xl p-6 ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold mb-4">Reporter Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">Reporter</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            @php(
                                $reporterName = null
                            )
                            @if(isset($lostItem->user))
                                @php(
                                    $reporterName = trim(($lostItem->user->first_name ?? '').' '.($lostItem->user->middle_name ?? '').' '.($lostItem->user->last_name ?? ''))
                                )
                            @endif
                            {{ $reporterName && trim($reporterName) !== '' ? $reporterName : ($lostItem->user->name ?? '—') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $lostItem->user->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Organization</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $lostItem->organization->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Reported On</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $lostItem->created_at ? \Carbon\Carbon::parse($lostItem->created_at)->format('M d, Y H:i') : '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Image Gallery -->
        <div class="space-y-6">
            <div class="bg-white shadow rounded-xl p-4 ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold mb-4">Item Photo</h2>
                @php(
                    $firstPhoto = ($lostItem->photos && $lostItem->photos->count()) ? asset('storage/' . $lostItem->photos->first()->image_path) : null
                )
                @php($primaryImage = $firstPhoto ?? ($lostItem->image ? asset('storage/'.$lostItem->image) : null))
                @if($primaryImage)
                    <img src="{{ $primaryImage }}" alt="Lost item photo" class="w-full h-72 object-cover rounded-lg">
                @else
                    <div class="w-full h-72 flex items-center justify-center bg-gray-100 text-gray-400 rounded-lg">No photo uploaded</div>
                @endif
            </div>

            @if(($lostItem->photos && $lostItem->photos->count() > 1))
            <div class="bg-white shadow rounded-xl p-4 ring-1 ring-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">More Photos</h3>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($lostItem->photos->skip(1) as $photo)
                        <img src="{{ asset('storage/' . $photo->image_path) }}" alt="Lost item photo" class="w-full h-24 object-cover rounded-md"/>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
