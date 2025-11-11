@extends('layouts.tenantApp')

@section('title', 'Claim Details & Matching')
@section('page-title', 'Claim Details & Matching')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Claim Details & Matching</h2>
            <p class="text-sm text-gray-600">Review and make a decision on this claim</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ url()->previous() }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                @if($claim->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($claim->status === 'approved') bg-green-100 text-green-800
                @elseif($claim->status === 'rejected') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ ucfirst($claim->status) }}
            </span>
            <span class="text-sm text-gray-500">Claim #{{ $claim->id }}</span>
        </div>
    </div>

    <!-- Auto-Match Section -->
    <div class="mb-8 bg-white rounded-lg shadow-lg border border-gray-200 p-6" 
         data-is-found-item="{{ $claim->foundItem ? 'true' : 'false' }}" 
         data-is-lost-item="{{ $claim->lostItem ? 'true' : 'false' }}">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-magic mr-3 text-green-600"></i>Auto-Match Analysis
                </h3>
                <p class="text-sm text-gray-600 mt-1">Compare reported item with claim details to assess match quality</p>
            </div>
            <button id="runAutoMatch" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                <i class="fas fa-magic mr-2"></i> Run Auto-Match
            </button>
        </div>
        
        <!-- Match Score Display -->
        <div id="matchScore" class="hidden">
            <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-lg p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="text-2xl font-bold text-gray-900">Match Score:</div>
                        <div id="scoreValue" class="text-4xl font-bold text-green-600">0%</div>
                        <div id="scoreBar" class="w-40 bg-gray-200 rounded-full h-4">
                            <div id="scoreFill" class="bg-green-600 h-4 rounded-full transition-all duration-1000" style="width: 0%"></div>
                        </div>
                    </div>
                    <button id="toggleAnalysis" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-chevron-down mr-1"></i> Show Analysis
                    </button>
                </div>
                
                <!-- Detailed Analysis -->
                <div id="detailedAnalysis" class="hidden">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-chart-bar mr-2 text-blue-600"></i>Field-by-Field Analysis
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Item Name</span>
                                <span id="nameScore" class="text-sm font-bold text-blue-600">0%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700" id="locationLabel">Location</span>
                                <span id="locationScore" class="text-sm font-bold text-blue-600">0%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700" id="dateLabel">Date & Time</span>
                                <span id="dateScore" class="text-sm font-bold text-blue-600">0%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Description</span>
                                <span id="descriptionScore" class="text-sm font-bold text-blue-600">0%</span>
                            </div>
                        </div>
                        <div id="matchAnalysis" class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <h5 class="font-medium text-blue-900 mb-2">Analysis Summary</h5>
                            <p id="analysisText" class="text-sm text-blue-800"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Reported Item (Left Panel) -->
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6">
            <h3 class="text-xl font-bold text-indigo-600 mb-6 flex items-center">
                <i class="fas fa-info-circle mr-3"></i>Reported Item
            </h3>
            
            @if($claim->foundItem)
                <!-- Found Item Details -->
                <div class="space-y-6">
                    <!-- Item Name -->
                    <div class="text-center border-b border-gray-200 pb-4">
                        <h4 class="text-2xl font-bold text-gray-900">{{ $claim->foundItem->title }}</h4>
                        <p class="text-sm text-gray-500 mt-1">Reported Item</p>
                    </div>

                    <!-- Image -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Item Photo</label>
                        @if($claim->foundItem->image_url)
                            <div class="relative">
                                <img src="{{ $claim->foundItem->image_url }}" alt="{{ $claim->foundItem->title }}" 
                                     class="w-full max-h-80 object-contain rounded-lg border border-gray-200 bg-white shadow-sm">
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

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Description Card -->
                        <div class="md:col-span-2 bg-blue-50 rounded-lg p-4">
                            <h5 class="font-semibold text-blue-800 mb-3 flex items-center">
                                <i class="fas fa-align-left mr-2"></i>Description
                            </h5>
                            <p class="text-sm text-gray-900 leading-relaxed">
                                {{ $claim->foundItem->description ?? 'No description provided' }}
                            </p>
                        </div>
                        
                        <!-- Location Card -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <h5 class="font-semibold text-green-800 mb-3 flex items-center">
                                <i class="fas fa-map-marker-alt mr-2"></i>Location Found
                            </h5>
                            <p class="text-sm font-medium text-gray-900">{{ $claim->foundItem->location ?? 'N/A' }}</p>
                        </div>
                        
                        <!-- Date Card -->
                        <div class="bg-purple-50 rounded-lg p-4">
                            <h5 class="font-semibold text-purple-800 mb-3 flex items-center">
                                <i class="fas fa-calendar-alt mr-2"></i>Date Found
                            </h5>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $claim->foundItem->date_found ? $claim->foundItem->date_found->format('M d, Y') : 'N/A' }}
                            </p>
                        </div>
                        
                        <!-- Time Card -->
                        <div class="bg-indigo-50 rounded-lg p-4">
                            <h5 class="font-semibold text-indigo-800 mb-3 flex items-center">
                                <i class="fas fa-clock mr-2"></i>Time Found
                            </h5>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $claim->foundItem->time_found ? \Carbon\Carbon::createFromFormat('H:i:s', $claim->foundItem->time_found)->format('g:i A') : 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <!-- Reporter Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-user mr-2"></i>Reporter Information
                        </h5>
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-indigo-600">
                                        {{ substr(optional($claim->foundItem->user)->first_name ?? 'U', 0, 1) }}{{ substr(optional($claim->foundItem->user)->last_name ?? 'N', 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ optional($claim->foundItem->user)->first_name ?? 'Unknown' }} {{ optional($claim->foundItem->user)->last_name ?? '' }}
                                </p>
                                <p class="text-sm text-gray-500">{{ optional($claim->foundItem->user)->email ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-400">Reported on {{ $claim->foundItem->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($claim->lostItem)
                <!-- Lost Item Details -->
                <div class="space-y-6">
                    <!-- Item Name -->
                    <div class="text-center border-b border-gray-200 pb-4">
                        <h4 class="text-2xl font-bold text-gray-900">{{ $claim->lostItem->title }}</h4>
                        <p class="text-sm text-gray-500 mt-1">Reported Item</p>
                    </div>

                    <!-- Image -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Item Photo</label>
                        @if($claim->lostItem->image_url)
                            <div class="relative">
                                <img src="{{ $claim->lostItem->image_url }}" alt="{{ $claim->lostItem->title }}" 
                                     class="w-full max-h-80 object-contain rounded-lg border border-gray-200 bg-white shadow-sm">
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

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Description Card -->
                        <div class="md:col-span-2 bg-blue-50 rounded-lg p-4">
                            <h5 class="font-semibold text-blue-800 mb-3 flex items-center">
                                <i class="fas fa-align-left mr-2"></i>Description
                            </h5>
                            <p class="text-sm text-gray-900 leading-relaxed">
                                {{ $claim->lostItem->description ?? 'No description provided' }}
                            </p>
                        </div>
                        
                        <!-- Location Card -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <h5 class="font-semibold text-green-800 mb-3 flex items-center">
                                <i class="fas fa-map-marker-alt mr-2"></i>Location Lost
                            </h5>
                            <p class="text-sm font-medium text-gray-900">{{ $claim->lostItem->location ?? 'N/A' }}</p>
                        </div>
                        
                        <!-- Date Card -->
                        <div class="bg-purple-50 rounded-lg p-4">
                            <h5 class="font-semibold text-purple-800 mb-3 flex items-center">
                                <i class="fas fa-calendar-alt mr-2"></i>Date Lost
                            </h5>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $claim->lostItem->date_lost ? $claim->lostItem->date_lost->format('M d, Y') : 'N/A' }}
                            </p>
                        </div>
                        
                        <!-- Time Card -->
                        <div class="bg-indigo-50 rounded-lg p-4">
                            <h5 class="font-semibold text-indigo-800 mb-3 flex items-center">
                                <i class="fas fa-clock mr-2"></i>Time Lost
                            </h5>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $claim->lostItem->time_lost ? \Carbon\Carbon::createFromFormat('H:i:s', $claim->lostItem->time_lost)->format('g:i A') : 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <!-- Reporter Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-user mr-2"></i>Reporter Information
                        </h5>
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-indigo-600">
                                        {{ substr(optional($claim->lostItem->user)->first_name ?? 'U', 0, 1) }}{{ substr(optional($claim->lostItem->user)->last_name ?? 'N', 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ optional($claim->lostItem->user)->first_name ?? 'Unknown' }} {{ optional($claim->lostItem->user)->last_name ?? '' }}
                                </p>
                                <p class="text-sm text-gray-500">{{ optional($claim->lostItem->user)->email ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-400">Reported on {{ $claim->lostItem->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- No Item Found -->
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Item Not Found</h4>
                    <p class="text-sm text-gray-600">The associated item for this claim could not be found. This might indicate a data integrity issue.</p>
                </div>
            @endif
        </div>

        <!-- Claim Details (Right Panel) -->
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6">
            <h3 class="text-xl font-bold text-green-600 mb-6 flex items-center">
                <i class="fas fa-user-check mr-3"></i>Claim Details
            </h3>
            
            <div class="space-y-6">
                <!-- Item Name -->
                <div class="text-center border-b border-gray-200 pb-4">
                    <h4 class="text-2xl font-bold text-gray-900">
                        {{ $claim->foundItem ? $claim->foundItem->title : ($claim->lostItem ? $claim->lostItem->title : 'Unknown Item') }}
                    </h4>
                    <p class="text-sm text-gray-500 mt-1">Claimed Item</p>
                </div>

                <!-- Proof of Ownership Image -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Proof of Ownership</label>
                    @if($claim->photo)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $claim->photo) }}" alt="Proof of ownership" 
                                 class="w-full max-h-80 object-contain rounded-lg border border-gray-200 bg-white shadow-sm">
                        </div>
                    @elseif($claim->proof_of_ownership)
                        <div class="relative">
                            <img src="{{ $claim->proof_of_ownership }}" alt="Proof of ownership" 
                                 class="w-full max-h-80 object-contain rounded-lg border border-gray-200 bg-white shadow-sm">
                        </div>
                    @else
                        <div class="w-full h-64 flex items-center justify-center bg-gray-200 text-gray-400 rounded-lg border border-gray-200">
                            <div class="text-center">
                                <i class="fas fa-image text-4xl mb-2"></i>
                                <p class="text-sm">No proof image submitted</p>
                                <p class="text-xs text-gray-400 mt-1">Claimant did not provide proof of ownership</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Claim Description Card -->
                    <div class="md:col-span-2 bg-orange-50 rounded-lg p-4">
                        <h5 class="font-semibold text-orange-800 mb-3 flex items-center">
                            <i class="fas fa-comment-alt mr-2"></i>Claim Description
                        </h5>
                        <p class="text-sm text-gray-900 leading-relaxed">
                            {{ $claim->claim_reason }}
                        </p>
                    </div>
                    
                    <!-- Claimed Location Card -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <h5 class="font-semibold text-yellow-800 mb-3 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>{{ $claim->foundItem ? 'Lost Location' : ($claim->lostItem ? 'Found Location' : 'Claimed Location') }}
                        </h5>
                        <p class="text-sm font-medium text-gray-900">{{ $claim->location ?? 'Not provided by claimant' }}</p>
                    </div>
                    
                    <!-- Claimed Date Card -->
                    <div class="bg-red-50 rounded-lg p-4">
                        <h5 class="font-semibold text-red-800 mb-3 flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>Date of {{ $claim->foundItem ? 'Lost' : ($claim->lostItem ? 'Found' : 'Item') }}
                        </h5>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $claim->claim_datetime ? \Carbon\Carbon::parse($claim->claim_datetime)->format('M d, Y') : 'Not provided by claimant' }}
                        </p>
                    </div>
                    
                    <!-- Claimed Time Card -->
                    <div class="bg-pink-50 rounded-lg p-4">
                        <h5 class="font-semibold text-pink-800 mb-3 flex items-center">
                            <i class="fas fa-clock mr-2"></i>Time of {{ $claim->foundItem ? 'Lost' : ($claim->lostItem ? 'Found' : 'Item') }}
                        </h5>
                        <p class="text-sm font-medium text-gray-900">
                            @if($claim->foundItem && $claim->time_lost)
                                {{ \Carbon\Carbon::createFromFormat('H:i:s', $claim->time_lost)->format('g:i A') }}
                            @elseif($claim->lostItem && $claim->time_found)
                                {{ \Carbon\Carbon::createFromFormat('H:i:s', $claim->time_found)->format('g:i A') }}
                            @else
                                Not provided by claimant
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Claimant Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-user mr-2"></i>Claimant Information
                    </h5>
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-green-600">
                                    {{ substr(optional($claim->user)->first_name ?? 'U', 0, 1) }}{{ substr(optional($claim->user)->last_name ?? 'N', 0, 1) }}
                                    </span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ optional($claim->user)->first_name ?? 'Unknown' }} {{ optional($claim->user)->last_name ?? '' }}
                            </p>
                            <p class="text-sm text-gray-500">{{ optional($claim->user)->email ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-400">Claim submitted on {{ $claim->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Claim Decision (Bottom Panel) -->
    <div class="mt-8 bg-white rounded-lg shadow-lg border border-gray-200 p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-gavel mr-3 text-gray-600"></i>Claim Decision
        </h3>
        
        <p class="text-sm text-gray-600 mb-6">Review the information above and make your decision</p>
        
        <div class="flex space-x-4">
            @if($claim->status === 'pending')
                <form action="{{ route('tenant.claims.approve', $claim->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md font-medium flex items-center"
                            onclick="return confirm('Are you sure you want to approve this claim? This will automatically reject all other pending claims for this item.')">
                        <i class="fas fa-check mr-2"></i> Approve
                    </button>
                </form>
                
                <button onclick="openRejectModal()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-md font-medium flex items-center">
                    <i class="fas fa-question mr-2"></i> Request More Info
                </button>
                
                <button onclick="openRejectModal()" 
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-md font-medium flex items-center">
                    <i class="fas fa-times mr-2"></i> Reject
                </button>
            @else
                <div class="text-sm text-gray-500">
                    <p><strong>Status:</strong> {{ ucfirst($claim->status) }}</p>
                    @if($claim->resolvedBy)
                        <p><strong>Reviewed By:</strong> {{ $claim->resolvedBy->first_name }} {{ $claim->resolvedBy->last_name }}</p>
                    @endif
                    @if($claim->resolved_at)
                        <p><strong>Decision Date:</strong> {{ $claim->resolved_at->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Claim</h3>
                <form action="{{ route('tenant.claims.reject', $claim->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for rejection (optional)
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Enter reason for rejection..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectModal()" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                            Reject Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function openRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

// Auto-Match Functionality
document.addEventListener('DOMContentLoaded', function() {
    const runAutoMatchBtn = document.getElementById('runAutoMatch');
    const matchScore = document.getElementById('matchScore');
    const scoreValue = document.getElementById('scoreValue');
    const scoreFill = document.getElementById('scoreFill');
    const toggleAnalysis = document.getElementById('toggleAnalysis');
    const detailedAnalysis = document.getElementById('detailedAnalysis');
    const nameScore = document.getElementById('nameScore');
    const locationScore = document.getElementById('locationScore');
    const dateScore = document.getElementById('dateScore');
    const descriptionScore = document.getElementById('descriptionScore');
    const analysisText = document.getElementById('analysisText');
    const locationLabel = document.getElementById('locationLabel');
    const dateLabel = document.getElementById('dateLabel');

    // Set correct labels based on claim type
    const autoMatchSection = document.querySelector('[data-is-found-item]');
    const isFoundItem = autoMatchSection ? autoMatchSection.getAttribute('data-is-found-item') === 'true' : false;
    const isLostItem = autoMatchSection ? autoMatchSection.getAttribute('data-is-lost-item') === 'true' : false;
    
    if (locationLabel && dateLabel) {
        if (isFoundItem) {
            locationLabel.textContent = 'Lost Location';
            dateLabel.textContent = 'Date & Time of Lost';
        } else if (isLostItem) {
            locationLabel.textContent = 'Found Location';
            dateLabel.textContent = 'Date & Time of Found';
        } else {
            locationLabel.textContent = 'Location';
            dateLabel.textContent = 'Date & Time';
        }
    }

    if (runAutoMatchBtn) {
        runAutoMatchBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Analyzing...';
            
            // Simulate auto-match analysis
            setTimeout(() => {
                const analysis = calculateMatchScore();
                displayMatchResults(analysis);
                
                // Reset button
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-magic mr-2"></i> Run Auto-Match';
            }, 2000);
        });
    }

    if (toggleAnalysis) {
        toggleAnalysis.addEventListener('click', function() {
            const isHidden = detailedAnalysis.classList.contains('hidden');
            const icon = this.querySelector('i');
            
            if (isHidden) {
                detailedAnalysis.classList.remove('hidden');
                icon.className = 'fas fa-chevron-up mr-1';
                this.innerHTML = '<i class="fas fa-chevron-up mr-1"></i> Hide Analysis';
            } else {
                detailedAnalysis.classList.add('hidden');
                icon.className = 'fas fa-chevron-down mr-1';
                this.innerHTML = '<i class="fas fa-chevron-down mr-1"></i> Show Analysis';
            }
        });
    }

    function calculateMatchScore() {
        // Get data from the page
        const reportedItem = {
            title: '{{ $claim->foundItem ? $claim->foundItem->title : ($claim->lostItem ? $claim->lostItem->title : "") }}',
            location: '{{ $claim->foundItem ? $claim->foundItem->location : ($claim->lostItem ? $claim->lostItem->location : "") }}',
            date: '{{ $claim->foundItem ? ($claim->foundItem->date_found ? $claim->foundItem->date_found->format("Y-m-d") : "") : ($claim->lostItem ? ($claim->lostItem->date_lost ? $claim->lostItem->date_lost->format("Y-m-d") : "") : "") }}',
            description: '{{ $claim->foundItem ? $claim->foundItem->description : ($claim->lostItem ? $claim->lostItem->description : "") }}'
        };

        const claimedItem = {
            title: '{{ $claim->foundItem ? $claim->foundItem->title : ($claim->lostItem ? $claim->lostItem->title : "") }}',
            location: '{{ $claim->location ?? "" }}',
            date: '{{ $claim->claim_datetime ? \Carbon\Carbon::parse($claim->claim_datetime)->format("Y-m-d") : "" }}',
            description: '{{ $claim->claim_reason }}'
        };

        // Calculate individual scores
        const nameScore = calculateStringSimilarity(reportedItem.title, claimedItem.title);
        const locationScore = calculateStringSimilarity(reportedItem.location, claimedItem.location);
        const dateScore = calculateDateSimilarity(reportedItem.date, claimedItem.date);
        const descriptionScore = calculateStringSimilarity(reportedItem.description, claimedItem.description);

        // Weighted overall score
        const overallScore = Math.round(
            (nameScore * 0.4) + 
            (locationScore * 0.3) + 
            (dateScore * 0.2) + 
            (descriptionScore * 0.1)
        );

        return {
            overall: overallScore,
            name: nameScore,
            location: locationScore,
            date: dateScore,
            description: descriptionScore
        };
    }

    function calculateStringSimilarity(str1, str2) {
        if (!str1 || !str2) return 0;
        
        const s1 = str1.toLowerCase().trim();
        const s2 = str2.toLowerCase().trim();
        
        if (s1 === s2) return 100;
        
        // Simple similarity calculation
        const longer = s1.length > s2.length ? s1 : s2;
        const shorter = s1.length > s2.length ? s2 : s1;
        
        if (longer.length === 0) return 100;
        
        const distance = levenshteinDistance(longer, shorter);
        return Math.round(((longer.length - distance) / longer.length) * 100);
    }

    function calculateDateSimilarity(date1, date2) {
        if (!date1 || !date2) return 0;
        
        const d1 = new Date(date1);
        const d2 = new Date(date2);
        
        if (isNaN(d1.getTime()) || isNaN(d2.getTime())) return 0;
        
        const diffTime = Math.abs(d1 - d2);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return 100;
        if (diffDays <= 1) return 80;
        if (diffDays <= 3) return 60;
        if (diffDays <= 7) return 40;
        if (diffDays <= 30) return 20;
        return 0;
    }

    function levenshteinDistance(str1, str2) {
        const matrix = [];
        
        for (let i = 0; i <= str2.length; i++) {
            matrix[i] = [i];
        }
        
        for (let j = 0; j <= str1.length; j++) {
            matrix[0][j] = j;
        }
        
        for (let i = 1; i <= str2.length; i++) {
            for (let j = 1; j <= str1.length; j++) {
                if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1,
                        matrix[i][j - 1] + 1,
                        matrix[i - 1][j] + 1
                    );
                }
            }
        }
        
        return matrix[str2.length][str1.length];
    }

    function displayMatchResults(analysis) {
        // Update score display
        scoreValue.textContent = analysis.overall + '%';
        scoreFill.style.width = analysis.overall + '%';
        
        // Update score color based on percentage
        if (analysis.overall >= 80) {
            scoreValue.className = 'text-4xl font-bold text-green-600';
            scoreFill.className = 'bg-green-600 h-4 rounded-full transition-all duration-1000';
        } else if (analysis.overall >= 50) {
            scoreValue.className = 'text-4xl font-bold text-yellow-600';
            scoreFill.className = 'bg-yellow-600 h-4 rounded-full transition-all duration-1000';
        } else {
            scoreValue.className = 'text-4xl font-bold text-red-600';
            scoreFill.className = 'bg-red-600 h-4 rounded-full transition-all duration-1000';
        }
        
        // Update individual scores
        nameScore.textContent = analysis.name + '%';
        locationScore.textContent = analysis.location + '%';
        dateScore.textContent = analysis.date + '%';
        descriptionScore.textContent = analysis.description + '%';
        
        // Generate analysis text with correct terminology
        let analysisSummary = '';
        const itemType = isFoundItem ? 'found item' : (isLostItem ? 'lost item' : 'item');
        const claimType = isFoundItem ? 'lost details' : (isLostItem ? 'found details' : 'claim details');
        
        if (analysis.overall >= 80) {
            analysisSummary = `Strong match detected! The reported ${itemType} and ${claimType} show high similarity across multiple fields.`;
        } else if (analysis.overall >= 50) {
            analysisSummary = `Moderate match found. Some similarities exist between the reported ${itemType} and ${claimType}, but there are notable differences that require careful review.`;
        } else {
            analysisSummary = `Weak match detected. Significant differences between the reported ${itemType} and ${claimType} suggest this may not be the same item.`;
        }
        
        analysisText.textContent = analysisSummary;
        
        // Show the results
        matchScore.classList.remove('hidden');
    }
});
</script>
@endsection
