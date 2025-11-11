@extends('layouts.tenantApp')

@section('title', 'Lost Items')
@section('page-title', 'Lost Items')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">Lost Items</h3>

    <!-- Search Form -->
    <form action="{{ route('tenant.lost-items.index') }}" method="GET" class="flex space-x-2">
        <input type="text" name="search" placeholder="Search by keywords..."
               value="{{ request('search') }}"
               class="px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1 rounded-md text-sm font-medium">
            Search
        </button>
    </form>

    <div class="flex space-x-2">
        <a href="{{ route('tenant.lost-items.print') }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow-sm hover:shadow-md transition-shadow"
           title="Download PDF of unresolved lost items for bulletin board">
            <i class="fas fa-file-pdf mr-1"></i> Download PDF
        </a>
        <a href="{{ route('tenant.lost-items.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow-sm hover:shadow-md transition-shadow">
            <i class="fas fa-plus mr-1"></i> Add New Lost Item
        </a>
    </div>
</div>

@if(session('success'))
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
    <p class="font-bold">Success</p>
    <p>{{ session('success') }}</p>
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
    <p class="font-bold">Error</p>
    <p>{{ session('error') }}</p>
</div>
@endif

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    @if(isset($lostItems) && count($lostItems) > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 180px;">Picture</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Lost</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posted By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($lostItems as $item)
                    <tr>
                        <!-- Image -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->image_url)
                                <img src="{{ $item->image_url }}" 
                                     alt="{{ $item->title }}" 
                                     class="h-24 w-24 object-cover rounded-md border border-gray-200 shadow-sm">
                            @else
                                <div class="h-24 w-24 flex items-center justify-center bg-gray-200 text-gray-400 rounded-md border border-gray-200 text-sm">
                                    No Image
                                </div>
                            @endif
                        </td>

                        <!-- Title -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $item->title }}
                        </td>

                        <!-- Category -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->category ?? 'N/A' }}
                        </td>

                        <!-- Location -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->location ?? 'N/A' }}
                        </td>

                        <!-- Date Lost -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->date_lost ? $item->date_lost->format('F d, Y') : 'N/A' }}
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->status === 'unresolved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Unresolved
                                </span>
                            @elseif($item->status === 'under_review')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Under Review
                                </span>
                            @elseif($item->status === 'claimed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Claimed
                                </span>
                            @elseif($item->status === 'cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Cancelled
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </td>

                        <!-- Posted By -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $item->user?->first_name }} {{ $item->user?->last_name }}
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('tenant.lost-items.manage', $item->id) }}" 
                               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow-sm hover:shadow-md transition-shadow">
                                <i class="fas fa-cogs mr-1"></i> Manage
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $lostItems->links() }}
        </div>
    @else
        <div class="px-6 py-4 text-center text-gray-500">
            No lost items recorded.
        </div>
    @endif
</div>
@endsection
