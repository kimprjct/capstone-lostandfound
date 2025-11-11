@extends('layouts.tenantApp')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('content')
<!-- First Row: Three Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
    <!-- Lost Items Stats -->
    <div class="bg-white rounded-lg shadow-lg border-t-4 border-red-500 p-6 hover:shadow-xl transition-shadow duration-300 h-full">
        <div class="flex items-center mb-4">
            <div class="p-3 rounded-full bg-red-100 mr-4">
                <i class="fas fa-search text-red-500 text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Lost Items</p>
                <h3 class="font-bold text-2xl">{{ $lostItemsCount }}</h3>
            </div>
        </div>
        <p class="text-sm text-gray-600">Total lost items reported</p>
    </div>

    <!-- Found Items Stats -->
    <div class="bg-white rounded-lg shadow-lg border-t-4 border-green-500 p-6 hover:shadow-xl transition-shadow duration-300 h-full">
        <div class="flex items-center mb-4">
            <div class="p-3 rounded-full bg-green-100 mr-4">
                <i class="fas fa-box-open text-green-500 text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Found Items</p>
                <h3 class="font-bold text-2xl">{{ $foundItemsCount }}</h3>
            </div>
        </div>
        <p class="text-sm text-gray-600">Total found items collected</p>
    </div>

    <!-- Pending Claims -->
    <div class="bg-white rounded-lg shadow-lg border-t-4 border-yellow-500 p-6 hover:shadow-xl transition-shadow duration-300 h-full">
        <div class="flex items-center mb-4">
            <div class="p-3 rounded-full bg-yellow-100 mr-4">
                <i class="fas fa-clipboard-check text-yellow-500 text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Pending Claims</p>
                <h3 class="font-bold text-2xl">{{ $pendingClaimsCount }}</h3>
            </div>
        </div>
        <p class="text-sm text-gray-600">Claims awaiting review</p>
    </div>
</div>

<!-- Second Row: Two Centered Cards -->
<div class="flex justify-center mb-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl w-full">
        <!-- Unclaimed Items Stats -->
        <div class="bg-white rounded-lg shadow-lg border-t-4 border-blue-500 p-6 hover:shadow-xl transition-shadow duration-300 h-full">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-full bg-blue-100 mr-4">
                    <i class="fas fa-exclamation-circle text-blue-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Unclaimed Items</p>
                    <h3 class="font-bold text-2xl">{{ $unclaimedItemsCount }}</h3>
                </div>
            </div>
            <p class="text-sm text-gray-600">Total items not yet claimed</p>
        </div>

        <!-- Claimed Items Stats -->
        <div class="bg-white rounded-lg shadow-lg border-t-4 border-indigo-500 p-6 hover:shadow-xl transition-shadow duration-300 h-full">
            <div class="flex items-center mb-4">
                <div class="p-3 rounded-full bg-indigo-100 mr-4">
                    <i class="fas fa-check-circle text-indigo-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Claimed Items</p>
                    <h3 class="font-bold text-2xl">{{ $claimedItemsCount }}</h3>
                </div>
            </div>
            <p class="text-sm text-gray-600">Total items successfully claimed</p>
        </div>
    </div>
</div>
 
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Lost Items -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="font-bold text-lg mb-4">Recent Lost Items</h3>
        @if(count($recentLostItems) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Item</th>
                            <th class="text-left py-2">Location</th>
                            <th class="text-left py-2">Date</th>
                            <th class="text-left py-2">Reported By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLostItems as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2">{{ $item->title ?? 'Unknown' }}</td>
                                <td class="py-2">{{ $item->location ?? 'Unknown' }}</td>
                                <td class="py-2">{{ $item->date_lost ? $item->date_lost->format('M d, Y') : 'Unknown Date' }}</td>
                                <td class="py-2">
                                    @if($item->user)
                                        <a href="{{ route('tenant.staff.show', $item->user_id) }}" class="text-indigo-600 hover:underline">
                                            {{ $item->user->first_name }} {{ $item->user->last_name }}
                                        </a>
                                        <div class="text-xs text-gray-500">{{ $item->user->email }}</div>
                                    @else
                                        Unknown Reporter
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <a href="{{ route('tenant.lost-items.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all lost items →</a>
            </div>
        @else
            <p class="text-gray-500">No lost items reported yet.</p>
        @endif
    </div>

    <!-- Recent Found Items -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="font-bold text-lg mb-4">Recent Found Items</h3>
        @if(count($recentFoundItems) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Item</th>
                            <th class="text-left py-2">Location</th>
                            <th class="text-left py-2">Date</th>
                            <th class="text-left py-2">Reported By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentFoundItems as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2">{{ $item->title ?? 'Unknown' }}</td>
                                <td class="py-2">{{ $item->location ?? 'Unknown' }}</td>
                                <td class="py-2">{{ $item->date_found ? $item->date_found->format('M d, Y') : 'Unknown Date' }}</td>
                                <td class="py-2">
                                    @if($item->user)
                                        <a href="{{ route('tenant.staff.show', $item->user_id) }}" class="text-indigo-600 hover:underline">
                                            {{ $item->user->first_name }} {{ $item->user->last_name }}
                                        </a>
                                        <div class="text-xs text-gray-500">{{ $item->user->email }}</div>
                                    @else
                                        Unknown Reporter
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <a href="{{ route('tenant.found-items.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all found items →</a>
            </div>
        @else
            <p class="text-gray-500">No found items collected yet.</p>
        @endif
    </div>
</div>

<!-- Recent Approved Claims -->
<div class="bg-white rounded-lg shadow-md p-6 mt-6">
    <h3 class="font-bold text-lg mb-4">Recent Approved Claims</h3>
    @if(count($recentClaims) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Item Claimed</th>
                        <th class="text-left py-2">Claimed By</th>
                        <th class="text-left py-2">Date</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-left py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentClaims as $claim)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2">
                                @if($claim->foundItem)
                                    {{ $claim->foundItem->title }}
                                    <span class="text-xs text-green-600 ml-1">(Found Item)</span>
                                @elseif($claim->lostItem)
                                    {{ $claim->lostItem->title }}
                                    <span class="text-xs text-red-600 ml-1">(Lost Item)</span>
                                @else
                                    Unknown Item
                                @endif
                            </td>
                            <td class="py-2">
                                @if($claim->user)
                                    <a href="{{ route('tenant.staff.show', $claim->user_id) }}" class="text-indigo-600 hover:underline">
                                        {{ $claim->user->first_name }} {{ $claim->user->last_name }}
                                    </a>
                                    <div class="text-xs text-gray-500">{{ $claim->user->email }}</div>
                                @else
                                    Unknown User
                                @endif
                            </td>
                            <td class="py-2">{{ $claim->created_at ? $claim->created_at->format('M d, Y') : 'Unknown Date' }}</td>
                            <td class="py-2">
                                <span class="px-2 py-1 rounded-full text-xs {{
                                    $claim->status == 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                    ($claim->status == 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')
                                }}">
                                    {{ ucfirst($claim->status) }}
                                </span>
                            </td>
                            <td class="py-2">
                                @if($claim->status == 'pending')
                                    <a href="{{ route('tenant.claims.show', $claim->id) }}" class="text-indigo-600 hover:text-indigo-800">Review</a>
                                @else
                                    <a href="{{ route('tenant.claims.show', $claim->id) }}" class="text-gray-600 hover:text-gray-800">View</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <a href="{{ route('tenant.claims.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View all claims →</a>
        </div>
    @else
        <p class="text-gray-500">No approved claims yet.</p>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Add any dashboard-specific JavaScript here
</script>
@endpush
