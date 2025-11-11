@extends('layouts.tenantApp')

@section('title', 'Claim Details & Matching')

@section('content')
<div class="px-6 py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Claim Details & Matching</h1>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-sm font-medium
                @if($claim->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($claim->status === 'approved') bg-green-100 text-green-800
                @else bg-red-100 text-red-800 @endif">
                {{ ucfirst($claim->status) }}
            </span>
            <span class="text-sm text-gray-500">Claim #{{ $claim->id }}</span>
        </div>
    </div>

    <!-- Auto-Match Section -->
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Auto-Match Analysis</h2>
                <p class="text-sm text-gray-600">Compare reported item with claim details</p>
            </div>
            <button id="runAutoMatch" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                Run Auto-Match
            </button>
        </div>
        
        <!-- Match Score Display -->
        <div id="matchScore" class="mt-4 hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="text-2xl font-bold text-gray-900">Match Score:</div>
                    <div id="scoreValue" class="text-3xl font-bold text-blue-600">0%</div>
                    <div id="scoreBar" class="w-32 bg-gray-200 rounded-full h-3">
                        <div id="scoreFill" class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>
                <button id="toggleAnalysis" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-medium hidden">
                    Show Analysis
                </button>
            </div>
            
            <!-- Detailed Analysis (Hidden by Default) -->
            <div id="detailedAnalysis" class="mt-4 hidden">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Field-by-Field Analysis</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Item Name</span>
                            <span id="nameScore" class="text-sm font-medium">0%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Location</span>
                            <span id="locationScore" class="text-sm font-medium">0%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Date & Time</span>
                            <span id="dateScore" class="text-sm font-medium">0%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Description</span>
                            <span id="descriptionScore" class="text-sm font-medium">0%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Comparison Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Side: Reported Item -->
        <div class="bg-white rounded-lg shadow">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b">
                <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Reported Item
                </h2>
            </div>
            
            <div class="p-6 space-y-6">
                @php 
                    $item = $claim->foundItem ?? $claim->lostItem; 
                    $imagePath = $item?->image_url ?? $item?->image ?? null;
                    if ($imagePath && !preg_match('/^https?:\/\//i', $imagePath)) {
                        $imagePath = asset('storage/'.preg_replace('/^storage\/?/','',$imagePath));
                    }
                @endphp

                <!-- Item Name at Top -->
                <div class="text-center border-b pb-4">
                    <h3 class="text-xl font-bold text-gray-900">{{ $item?->title ?? '—' }}</h3>
                </div>

                <!-- Item Image -->
                <div class="text-center">
                    @if($imagePath)
                        <img src="{{ $imagePath }}" 
                             class="mx-auto w-48 h-48 object-cover rounded-lg shadow-md cursor-pointer" 
                             alt="Item Image"
                             onclick="openImageModal('{{ $imagePath }}')" />
                    @else
                        <div class="mx-auto w-48 h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Item Details -->
                <div class="space-y-4">
                    <div class="field-group">
                        <label class="field-label">Description</label>
                        <div id="reported-description" class="field-value">{{ $item?->description ?? '—' }}</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Location</label>
                        <div id="reported-location" class="field-value">{{ $item?->location ?? '—' }}</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">{{ $claim->foundItem ? 'Found' : 'Lost' }} Date & Time</label>
                        <div id="reported-date" class="field-value">
                            {{ $item ? ($claim->foundItem ? $item->date_found : $item->date_lost) : '—' }}
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Submission Date & Time</label>
                        <div class="field-value">{{ $item?->created_at?->format('M d, Y H:i') ?? '—' }}</div>
                    </div>
                </div>

                <!-- Reporter Info -->
                <div class="border-t pt-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Reporter Information</h3>
                    <div class="space-y-2">
                        <a href="{{ route('tenant.staff.show', $item?->user?->id) }}" class="flex items-center gap-3 hover:bg-gray-50 p-2 rounded-lg transition-colors">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ substr($item?->user?->first_name ?? 'U', 0, 1) }}{{ substr($item?->user?->last_name ?? 'N', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $item?->user?->first_name }} {{ $item?->user?->last_name }}</div>
                                <div class="text-sm text-gray-600">{{ $item?->user?->email }}</div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Claim Details -->
        <div class="bg-white rounded-lg shadow">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b">
                <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Claim Details
                </h2>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Item Name at Top (from claimed item) -->
                <div class="text-center border-b pb-4">
                    @php 
                        $claimedItem = $claim->foundItem ?? $claim->lostItem; 
                    @endphp
                    <h3 class="text-xl font-bold text-gray-900">{{ $claimedItem?->title ?? 'Claimed Item' }}</h3>
                </div>

                <!-- Claimant Image -->
            <div class="text-center">
                @php
                    $photoUrl = null;
                    if ($claim->photo) {
                        $photoPath = preg_replace('/^storage\/?/','', $claim->photo);
                        $photoUrl = asset('storage/' . $photoPath);
                    }
                @endphp

                @if($photoUrl)
                    <img src="{{ $photoUrl }}"
                        class="mx-auto w-48 h-48 object-cover rounded-lg shadow-md cursor-pointer"
                        alt="Claim Proof"
                        onclick="openImageModal('{{ $photoUrl }}')" />
                @else
                    <div class="mx-auto w-48 h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                @endif
            </div>


                <!-- Claim Details -->
                <div class="space-y-4">
                    <div class="field-group">
                        <label class="field-label">Claim Description</label>
                        <div id="claim-description" class="field-value">{{ $claim->claim_reason }}</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">{{ $claim->foundItem ? 'Lost Location' : ($claim->lostItem ? 'Found Location' : 'Claimed Location') }}</label>
                        <div id="claim-location" class="field-value">
                            @if($claim->location)
                                {{ $claim->location }}
                            @else
                                <span class="text-gray-500 italic">Not provided by claimant</span>
                            @endif
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Date and Time of {{ $claim->foundItem ? 'Lost' : ($claim->lostItem ? 'Found' : 'Item') }}</label>
                        <div id="claim-date" class="field-value">
                            @php $dt = $claim->claim_datetime ?? $claim->claim_date; @endphp
                            @if($dt)
                                {{ $dt }}
                            @else
                                <span class="text-gray-500 italic">Not provided by claimant</span>
                            @endif
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Submission Date & Time</label>
                        <div class="field-value">{{ $claim->created_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>

                <!-- Claimant Info -->
                <div class="border-t pt-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Claimant Information</h3>
                    <div class="space-y-2">
                        <a href="{{ route('tenant.staff.show', $claim->user?->id) }}" class="flex items-center gap-3 hover:bg-gray-50 p-2 rounded-lg transition-colors">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ substr($claim->user?->first_name ?? 'U', 0, 1) }}{{ substr($claim->user?->last_name ?? 'N', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $claim->user?->first_name }} {{ $claim->user?->last_name }}</div>
                                <div class="text-sm text-gray-600">{{ $claim->user?->email }}</div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($claim->status === 'pending')
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Claim Decision</h3>
                <p class="text-sm text-gray-600">Review the information above and make your decision</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('tenant.claims.approve', $claim->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve
                    </button>
                </form>
                
                <button type="button" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Request More Info
                </button>
                
                <form action="{{ route('tenant.claims.reject', $claim->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2" onclick="return confirm('Are you sure you want to reject this claim?')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reject
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
    <img class="image-modal-content" id="modalImage">
</div>

<style>
.field-group {
    @apply space-y-1;
}
.field-label {
    @apply text-sm font-medium text-gray-700;
}
.field-value {
    @apply text-gray-900 p-2 rounded border bg-gray-50;
}
.match-highlight {
    @apply bg-yellow-200 border-yellow-400;
}
.match-perfect {
    @apply bg-green-200 border-green-400;
}
.match-partial {
    @apply bg-blue-200 border-blue-400;
}

/* Image Modal Styles */
.image-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}

.image-modal-content {
    margin: auto;
    display: block;
    width: 80%;
    max-width: 700px;
    max-height: 80%;
    object-fit: contain;
}

.image-modal-close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.image-modal-close:hover {
    color: #bbb;
}

/* Carousel styles removed - only single photo supported */
</style>

<script>

document.getElementById('runAutoMatch').addEventListener('click', function() {
    // Show loading state
    this.textContent = 'Analyzing...';
    this.disabled = true;
    
    // Simulate auto-match analysis
    setTimeout(() => {
        const analysis = calculateMatchScore();
        displayMatchResults(analysis);
        
        // Reset button
        this.textContent = 'Run Auto-Match';
        this.disabled = false;
    }, 1500);
});

// Toggle analysis button
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleAnalysis');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const analysis = document.getElementById('detailedAnalysis');
            if (analysis.classList.contains('hidden')) {
                analysis.classList.remove('hidden');
                this.textContent = 'Hide Analysis';
            } else {
                analysis.classList.add('hidden');
                this.textContent = 'Show Analysis';
            }
        });
    }
});

function calculateMatchScore() {
    let totalScore = 0;
    let analysis = {
        name: 0,
        location: 0,
        date: 0,
        description: 0
    };
    
    // Get item names from the headers
    const reportedItemHeader = document.querySelector('.bg-gradient-to-r.from-blue-50').parentElement.querySelector('h3');
    const claimedItemHeader = document.querySelector('.bg-gradient-to-r.from-green-50').parentElement.querySelector('h3');
    
    const reportedName = reportedItemHeader ? reportedItemHeader.textContent.toLowerCase().trim() : '';
    const claimedName = claimedItemHeader ? claimedItemHeader.textContent.toLowerCase().trim() : '';
    const claimDescription = document.getElementById('claim-description').textContent.toLowerCase().trim();
    const reportedDescription = document.getElementById('reported-description').textContent.toLowerCase().trim();
    
    // Compare item names (30% weight)
    if (reportedName && claimedName) {
        const reportedWords = reportedName.split(/\s+/);
        const claimedWords = claimedName.split(/\s+/);
        
        let nameMatches = 0;
        reportedWords.forEach(word => {
            if (word.length > 2 && claimedWords.includes(word)) {
                nameMatches++;
            }
        });
        
        if (reportedWords.length > 0) {
            analysis.name = Math.round((nameMatches / reportedWords.length) * 100);
            totalScore += analysis.name * 0.30;
        }
    }
    
    // Compare locations (25% weight)
    const reportedLocation = document.getElementById('reported-location').textContent.toLowerCase().trim();
    const claimLocation = document.getElementById('claim-location').textContent.toLowerCase().trim();
    
    if (reportedLocation !== '—' && claimLocation !== '—') {
        const locationWords = reportedLocation.split(/\s+/);
        const claimLocationWords = claimLocation.split(/\s+/);
        
        let locationMatches = 0;
        locationWords.forEach(word => {
            if (word.length > 2 && claimLocationWords.includes(word)) {
                locationMatches++;
            }
        });
        
        if (locationWords.length > 0) {
            analysis.location = Math.round((locationMatches / locationWords.length) * 100);
            totalScore += analysis.location * 0.25;
        }
    }
    
    // Compare dates (25% weight)
    const reportedDate = document.getElementById('reported-date').textContent.trim();
    const claimDate = document.getElementById('claim-date').textContent.trim();
    
    if (reportedDate !== '—' && claimDate !== '—') {
        if (reportedDate === claimDate) {
            analysis.date = 100;
        } else {
            analysis.date = 50; // Partial credit for having dates
        }
        totalScore += analysis.date * 0.25;
    }
    
    // Compare descriptions (20% weight)
    if (reportedDescription !== '—' && claimDescription !== '—') {
        const reportedWords = reportedDescription.split(/\s+/);
        const claimWords = claimDescription.split(/\s+/);
        
        let descMatches = 0;
        reportedWords.forEach(word => {
            if (word.length > 3 && claimWords.includes(word)) {
                descMatches++;
            }
        });
        
        if (reportedWords.length > 0) {
            analysis.description = Math.round((descMatches / reportedWords.length) * 100);
            totalScore += analysis.description * 0.20;
        }
    }
    
    return {
        total: Math.min(Math.round(totalScore), 100),
        analysis: analysis
    };
}

function displayMatchResults(result) {
    const scoreElement = document.getElementById('matchScore');
    const scoreValue = document.getElementById('scoreValue');
    const scoreFill = document.getElementById('scoreFill');
    const toggleBtn = document.getElementById('toggleAnalysis');
    
    // Show score and toggle button
    scoreElement.classList.remove('hidden');
    toggleBtn.classList.remove('hidden');
    
    scoreValue.textContent = result.total + '%';
    scoreFill.style.width = result.total + '%';
    
    // Color code the score
    if (result.total >= 80) {
        scoreValue.className = 'text-3xl font-bold text-green-600';
        scoreFill.className = 'bg-green-600 h-3 rounded-full transition-all duration-500';
    } else if (result.total >= 60) {
        scoreValue.className = 'text-3xl font-bold text-yellow-600';
        scoreFill.className = 'bg-yellow-600 h-3 rounded-full transition-all duration-500';
    } else {
        scoreValue.className = 'text-3xl font-bold text-red-600';
        scoreFill.className = 'bg-red-600 h-3 rounded-full transition-all duration-500';
    }
    
    // Update detailed analysis
    document.getElementById('nameScore').textContent = result.analysis.name + '%';
    document.getElementById('locationScore').textContent = result.analysis.location + '%';
    document.getElementById('dateScore').textContent = result.analysis.date + '%';
    document.getElementById('descriptionScore').textContent = result.analysis.description + '%';
    
    // Highlight matching fields
    highlightMatchingFields(result.total);
}

function highlightMatchingFields(score) {
    // Remove existing highlights
    document.querySelectorAll('.match-highlight, .match-perfect, .match-partial').forEach(el => {
        el.classList.remove('match-highlight', 'match-perfect', 'match-partial');
    });
    
    // Add highlights based on score
    if (score >= 80) {
        document.querySelectorAll('.field-value').forEach(el => {
            el.classList.add('match-perfect');
        });
    } else if (score >= 60) {
        document.querySelectorAll('.field-value').forEach(el => {
            el.classList.add('match-partial');
        });
    } else {
        document.querySelectorAll('.field-value').forEach(el => {
            el.classList.add('match-highlight');
        });
    }
}

// Image Modal Functions
function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = 'block';
    modalImg.src = imageSrc;
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

// Close modal when clicking outside the image
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Carousel functions removed - only single photo supported
</script>
@endsection