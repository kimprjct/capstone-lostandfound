@extends('layouts.tenantApp')

@section('title', 'Claim History')

@section('page-title', 'Claim History')

@section('content')
<div id="claimsPageContainer" class="px-6 py-4 relative">
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
                                            {{ substr(optional($claim->user)->first_name ?? 'U', 0, 1) }}{{ substr(optional($claim->user)->last_name ?? 'N', 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ optional($claim->user)->first_name ?? 'Unknown' }} {{ optional($claim->user)->last_name ?? '' }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ optional($claim->user)->email ?? 'N/A' }}</div>
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
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md text-sm view-claim-btn whitespace-nowrap">
                                    <i class="fas fa-eye mr-1"></i> View
                                </button>
                                <button data-claim-id="{{ $claim->id }}"
                                        class="bg-green-600 {{ $claim->status === 'pending' ? 'hover:bg-green-700' : '' }} text-white px-2.5 py-1 rounded-md text-sm claim-btn whitespace-nowrap {{ $claim->status !== 'pending' ? 'opacity-50 cursor-not-allowed blur-[0.5px]' : '' }}"
                                        {{ $claim->status !== 'pending' ? 'disabled' : '' }}>
                                    <i class="fas fa-check-double mr-1"></i> Claim
                                </button>
                                <button data-claim-id="{{ $claim->id }}"
                                        class="bg-red-600 {{ $claim->status === 'pending' ? 'hover:bg-red-700' : '' }} text-white px-2.5 py-1 rounded-md text-sm reject-in-person-btn whitespace-nowrap {{ $claim->status !== 'pending' ? 'opacity-50 cursor-not-allowed blur-[0.5px]' : '' }}"
                                        {{ $claim->status !== 'pending' ? 'disabled' : '' }}>
                                    <i class="fas fa-user-times mr-1"></i> Reject
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

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="absolute z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div id="modalContentWrapper" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 1rem; pointer-events: none;">
            <div class="bg-white rounded-xl overflow-hidden pointer-events-auto" style="width: 420px; max-width: 90vw; position: relative; z-index: 10; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(0, 0, 0, 0.1);">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">Confirm Action</h3>
                </div>
                <div class="px-4 py-4">
                    <p id="confirmMessage" class="text-sm text-gray-700 leading-6"></p>
                </div>
                <div class="px-4 py-3 bg-gray-50 flex justify-end space-x-2">
                    <button id="confirmCancelBtn" type="button" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-1 rounded-md text-sm">Cancel</button>
                    <button id="confirmProceedBtn" type="button" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md text-sm">Confirm</button>
                </div>
            </div>
        </div>
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

    // Modal logic
    const confirmModal = document.getElementById('confirmModal');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    const confirmProceedBtn = document.getElementById('confirmProceedBtn');

    function openConfirmModal(action, claimId, message) {
        confirmModal.dataset.action = action;
        confirmModal.dataset.claimId = claimId;
        confirmMessage.textContent = message;
        
        // Ensure modal covers full container for proper centering
        const claimsContainer = document.getElementById('claimsPageContainer');
        if (claimsContainer) {
            // Calculate the full dimensions including scrollable content
            const containerHeight = Math.max(
                claimsContainer.scrollHeight,
                claimsContainer.offsetHeight,
                claimsContainer.clientHeight
            );
            const containerWidth = Math.max(
                claimsContainer.scrollWidth,
                claimsContainer.offsetWidth,
                claimsContainer.clientWidth
            );
            
            // Set modal to cover entire container for centering
            confirmModal.style.position = 'absolute';
            confirmModal.style.top = '0';
            confirmModal.style.left = '0';
            confirmModal.style.width = containerWidth + 'px';
            confirmModal.style.height = containerHeight + 'px';
            
            // Ensure content wrapper also covers full area for centering
            const contentWrapper = document.getElementById('modalContentWrapper');
            if (contentWrapper) {
                contentWrapper.style.width = containerWidth + 'px';
                contentWrapper.style.height = containerHeight + 'px';
            }
            
            claimsContainer.style.overflow = 'hidden';
        }
        
        confirmModal.classList.remove('hidden');
    }

    function closeConfirmModal() {
        confirmModal.classList.add('hidden');
        delete confirmModal.dataset.action;
        delete confirmModal.dataset.claimId;
        // Reset dimensions and restore scrolling
        confirmModal.style.height = '';
        confirmModal.style.width = '';
        const contentWrapper = document.getElementById('modalContentWrapper');
        if (contentWrapper) {
            contentWrapper.style.width = '';
            contentWrapper.style.height = '';
        }
        const claimsContainer = document.getElementById('claimsPageContainer');
        if (claimsContainer) {
            claimsContainer.style.overflow = '';
        }
    }

    // Open modal for Claim button
    document.addEventListener('click', function(e) {
        const claimBtn = e.target.closest('.claim-btn');
        if (claimBtn && !claimBtn.disabled) {
            const claimId = claimBtn.getAttribute('data-claim-id');
            openConfirmModal('claim', claimId, 'Confirm: The item ownership was verified in person and released.');
            return;
        }
    });

    // Open modal for Reject button
    document.addEventListener('click', function(e) {
        const rejectBtn = e.target.closest('.reject-in-person-btn');
        if (rejectBtn && !rejectBtn.disabled) {
            const claimId = rejectBtn.getAttribute('data-claim-id');
            openConfirmModal('reject-in-person', claimId, 'Confirm: The claim was rejected after in-person verification.');
            return;
        }
    });

    // Close modal
    confirmCancelBtn.addEventListener('click', closeConfirmModal);
    confirmModal.addEventListener('click', function(e) {
        if (e.target === confirmModal) closeConfirmModal();
    });

    // Confirm action
    confirmProceedBtn.addEventListener('click', async function() {
        const action = confirmModal.dataset.action;
        const claimId = confirmModal.dataset.claimId;
        if (!action || !claimId) { closeConfirmModal(); return; }
        
        const url = action === 'claim' 
            ? `/tenant/claims/${claimId}/claim`
            : `/tenant/claims/${claimId}/reject-in-person`;
        
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                location.reload();
            } else {
                const data = await res.json();
                alert(data.message || 'Failed to process request.');
                closeConfirmModal();
            }
        } catch (err) {
            alert('Failed to process request.');
            closeConfirmModal();
        }
    });
});
</script>
@endsection
