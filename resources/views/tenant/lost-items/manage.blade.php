@extends('layouts.tenantApp')

@section('title', 'Manage Lost Item')
@section('page-title', 'Manage Lost Item')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Manage Lost Item</h3>
        <a href="{{ route('tenant.lost-items.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Back to Lost Items
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

<div class="space-y-6">
    <!-- Item Details -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h4 class="text-lg font-semibold mb-4 text-indigo-600">
            <i class="fas fa-info-circle mr-2"></i>Item Details
        </h4>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Picture Section -->
            <div class="lg:col-span-1">
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-camera mr-2 text-indigo-600"></i>Item Photo
                    </label>
                    @if($lostItem->image_url)
                        <div class="relative">
                            <img src="{{ $lostItem->image_url }}" alt="{{ $lostItem->title }}" 
                                 class="w-full max-h-80 object-contain rounded-lg border border-gray-200 bg-white">
                        </div>
                    @else
                        <div class="w-full h-64 flex items-center justify-center bg-gray-200 text-gray-400 rounded-lg border border-gray-200">
                            <div class="text-center">
                                <i class="fas fa-image text-4xl mb-2"></i>
                                <p class="text-sm">No image available</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Details Section -->
            <div class="lg:col-span-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Basic Information Card -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h5 class="font-semibold text-blue-800 mb-3">
                            <i class="fas fa-info-circle mr-2"></i>Basic Information
                        </h5>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-blue-700 uppercase tracking-wide">Title</label>
                                <p class="mt-1 text-sm font-medium text-gray-900">{{ $lostItem->title }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-blue-700 uppercase tracking-wide">Category</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $lostItem->category ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-blue-700 uppercase tracking-wide">Status</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($lostItem->status === 'unresolved') bg-red-100 text-red-800
                                    @elseif($lostItem->status === 'under_review') bg-yellow-100 text-yellow-800
                                    @elseif($lostItem->status === 'claimed') bg-green-100 text-green-800
                                    @elseif($lostItem->status === 'cancelled') bg-gray-100 text-gray-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $lostItem->status)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Time Card -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h5 class="font-semibold text-green-800 mb-3">
                            <i class="fas fa-map-marker-alt mr-2"></i>Location & Time
                        </h5>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-green-700 uppercase tracking-wide">Location Lost</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $lostItem->location ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-green-700 uppercase tracking-wide">Date Lost</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $lostItem->date_lost ? $lostItem->date_lost->format('F d, Y') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-green-700 uppercase tracking-wide">Time Lost</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $lostItem->time_lost ? \Carbon\Carbon::createFromFormat('H:i:s', $lostItem->time_lost)->format('g:i A') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-green-700 uppercase tracking-wide">Reported By</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $lostItem->user?->first_name }} {{ $lostItem->user?->last_name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Description Card -->
                    <div class="md:col-span-2 bg-purple-50 rounded-lg p-4">
                        <h5 class="font-semibold text-purple-800 mb-3">
                            <i class="fas fa-align-left mr-2"></i>Description
                        </h5>
                        <p class="text-sm text-gray-900 leading-relaxed">
                            {{ $lostItem->description ?? 'No description provided' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Claim Requests List -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h4 class="text-lg font-semibold mb-4 text-blue-600">
            <i class="fas fa-list mr-2"></i>Claim Requests List
        </h4>
        
        @if($claims->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claim Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof of Ownership</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($claims as $claim)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-indigo-600">
                                                    {{ substr($claim->user->first_name, 0, 1) }}{{ substr($claim->user->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $claim->user->first_name }} {{ $claim->user->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $claim->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $claim->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($claim->proof_of_ownership)
                                        <span class="text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>Provided
                                        </span>
                                    @else
                                        <span class="text-gray-400">
                                            <i class="fas fa-times-circle mr-1"></i>Not provided
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($claim->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($claim->status === 'approved') bg-green-100 text-green-800
                                        @elseif($claim->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($claim->status) }}
                                    </span>
                                </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($claim->proof_of_ownership)
                                                <button data-proof-url="{{ $claim->proof_of_ownership }}" 
                                                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm view-proof-btn">
                                                    <i class="fas fa-image mr-1"></i> View Proof
                                                </button>
                                            @endif
                                            <button data-claim-id="{{ $claim->id }}" 
                                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md text-sm review-claim-btn">
                                                <i class="fas fa-eye mr-1"></i> Review
                                            </button>
                                        </div>
                                    </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No claim requests yet.</p>
            </div>
        @endif
    </div>
</div>

<!-- Proof of Ownership Modal -->
<div id="proofModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Proof of Ownership</h3>
                <button id="closeProofModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="text-center">
                <img id="proofImage" src="" alt="Proof of Ownership" class="max-w-full max-h-96 mx-auto rounded-lg border border-gray-200">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const proofModal = document.getElementById('proofModal');
    const proofImage = document.getElementById('proofImage');
    const closeProofModal = document.getElementById('closeProofModal');


    // Proof of ownership modal functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-proof-btn')) {
            const proofUrl = e.target.closest('.view-proof-btn').getAttribute('data-proof-url');
            proofImage.src = proofUrl;
            proofModal.classList.remove('hidden');
        }
        
        if (e.target.closest('.review-claim-btn')) {
            const claimId = e.target.closest('.review-claim-btn').getAttribute('data-claim-id');
            window.open(`/tenant/claims/${claimId}/review`, '_blank');
        }
    });

    // Close modal functionality
    closeProofModal.addEventListener('click', function() {
        proofModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    proofModal.addEventListener('click', function(e) {
        if (e.target === proofModal) {
            proofModal.classList.add('hidden');
        }
    });
});
</script>
@endsection
