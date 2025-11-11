@extends('layouts.app')

@section('title', 'Claim Details')
@section('page-title', 'Claim Details')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-gray-50 min-h-screen">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Claim Information</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.claims.edit', $claim) }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Edit</a>
            <a href="{{ route('admin.claims.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">Back to List</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow rounded-xl p-6 ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold mb-4">Claim Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">Claim ID</dt>
                        <dd class="mt-1 font-medium text-gray-900">#{{ $claim->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @php($status = strtolower($claim->status))
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $status === 'approved' ? 'bg-green-100 text-green-700' : ($status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($claim->status) }}</span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-gray-500">Claim Description</dt>
                        <dd class="mt-1 text-gray-900">{{ $claim->description ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Claimed Date</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $claim->claimed_date ? \Carbon\Carbon::parse($claim->claimed_date)->format('M d, Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Claimed Location</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $claim->claimed_location ?: '—' }}</dd>
                    </div>
                    @if($claim->claim_code)
                    <div>
                        <dt class="text-sm text-gray-500">Claim Code</dt>
                        <dd class="mt-1 font-mono text-gray-900">{{ $claim->claim_code }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white shadow rounded-xl p-6 ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold mb-4">Claimant Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">Claimant</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            @php(
                                $claimantName = null
                            )
                            @if(isset($claim->user))
                                @php(
                                    $claimantName = trim(($claim->user->first_name ?? '').' '.($claim->user->middle_name ?? '').' '.($claim->user->last_name ?? ''))
                                )
                            @endif
                            {{ $claimantName && trim($claimantName) !== '' ? $claimantName : ($claim->user->name ?? '—') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $claim->user->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Phone</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $claim->user->phone_number ?? $claim->user->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Organization</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $claim->foundItem->organization->name ?? $claim->lostItem->organization->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Submitted On</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $claim->created_at ? \Carbon\Carbon::parse($claim->created_at)->format('M d, Y H:i') : '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Sidebar: Claimed Item and Proof -->
        <div class="space-y-6">
            <div class="bg-white shadow rounded-xl p-6 ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold mb-4">Claimed Item</h2>
                <div class="space-y-2">
                    <div class="text-sm text-gray-500">Type</div>
                    <div class="font-medium text-gray-900">{{ $claim->found_item_id ? 'Found Item' : 'Lost Item' }}</div>
                    <div class="text-sm text-gray-500 mt-4">Title</div>
                    <div class="font-medium text-gray-900">{{ $claim->foundItem->title ?? $claim->lostItem->title ?? 'N/A' }}</div>
                    @if(($claim->foundItem && $claim->foundItem->photos && $claim->foundItem->photos->count()) || ($claim->lostItem && $claim->lostItem->photos && $claim->lostItem->photos->count()))
                        @php(
                            $photoPath = $claim->foundItem ? ($claim->foundItem->photos->first()->image_path ?? null) : ($claim->lostItem->photos->first()->image_path ?? null)
                        )
                        @if($photoPath)
                            <img src="{{ asset('storage/' . $photoPath) }}" alt="Item photo" class="mt-3 w-full h-40 object-cover rounded-md"/>
                        @endif
                    @endif
                </div>
            </div>

            @if($claim->photo)
            <div class="bg-white shadow rounded-xl p-4 ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold mb-4">Proof of Ownership</h2>
                <img src="{{ asset('storage/' . $claim->photo) }}" alt="Proof of ownership" class="w-full h-72 object-cover rounded-lg">
            </div>
            @endif

            @if($claim->admin_notes)
            <div class="bg-blue-50 border border-blue-100 text-blue-800 text-sm rounded-lg p-4">
                <div class="font-semibold mb-1">Admin Notes</div>
                <div>{{ $claim->admin_notes }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
