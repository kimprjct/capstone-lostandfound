@extends('layouts.tenantApp')

@section('title', 'Claim History')

@section('page-title', 'Claim History')

@section('content')
<div class="px-6 py-4">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Claim History</h1>
    </div>

    <form method="GET" class="mb-6 bg-white p-4 rounded-lg shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search claimant or item..." 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="pending" @selected(request('status')==='pending')>Pending</option>
                    <option value="approved" @selected(request('status')==='approved')>Approved</option>
                    <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <option value="found" @selected(request('type')==='found')>Found Items</option>
                    <option value="lost" @selected(request('type')==='lost')>Lost Items</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
            </div>
        </div>
    </form>

    <div class="bg-white shadow rounded">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Lost/Found</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimant Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claim Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($claims as $claim)
                    @php
                        $item = $claim->foundItem ?? $claim->lostItem;
                        $itemType = $claim->foundItem ? 'Found' : 'Lost';
                        $itemDate = null;
                        if ($claim->foundItem && $claim->foundItem->date_found) {
                            $itemDate = $claim->foundItem->date_found;
                        } elseif ($claim->lostItem && $claim->lostItem->date_lost) {
                            $itemDate = $claim->lostItem->date_lost;
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <!-- Type -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $itemType === 'Found' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $itemType }}
                            </span>
                        </td>

                        <!-- Item Title -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $item?->title ?? 'Unknown Item' }}</div>
                            <div class="text-sm text-gray-500">{{ $item?->category ?? 'N/A' }}</div>
                        </td>

                        <!-- Location -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item?->location ?? 'N/A' }}
                        </td>

                        <!-- Date Lost/Found -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $itemDate ? $itemDate->format('M d, Y') : 'N/A' }}
                        </td>

                        <!-- Claimant Name -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-xs font-medium text-indigo-600">
                                            {{ substr($claim->user->first_name, 0, 1) }}{{ substr($claim->user->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $claim->user->first_name }} {{ $claim->user->last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $claim->user->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Claim Date -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $claim->created_at->format('M d, Y') }}
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($claim->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($claim->status === 'approved') bg-green-100 text-green-800
                                @elseif($claim->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($claim->status) }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button data-claim-id="{{ $claim->id }}" 
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md text-sm view-claim-btn">
                                    <i class="fas fa-eye mr-1"></i> View
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg">No claims found.</p>
                            <p class="text-sm">Try adjusting your search or filter criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $claims->withQueryString()->links() }}</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle view claim button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-claim-btn')) {
            const claimId = e.target.closest('.view-claim-btn').getAttribute('data-claim-id');
            window.open(`/tenant/claims/${claimId}/review`, '_blank');
        }
    });


    // Handle view proof button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-proof-btn')) {
            const claimId = e.target.closest('.view-proof-btn').getAttribute('data-claim-id');
            window.open(`/tenant/claims/${claimId}/proof`, '_blank');
        }
    });
});
</script>
@endsection
