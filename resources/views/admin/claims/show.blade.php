@extends('layouts.adminApp')

@section('header', 'Claim Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Claim Information</h5>
                    <div>
                        <a href="{{ route('admin.claims.edit', $claim) }}" class="btn btn-primary btn-sm">Edit</a>
                        <a href="{{ route('admin.claims.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Claim Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Claim ID:</strong></td>
                                    <td>#{{ $claim->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $claim->status === 'approved' ? 'success' : ($claim->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($claim->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Claimed Item:</strong></td>
                                    <td>{{ $claim->foundItem->title ?? $claim->lostItem->title ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Item Type:</strong></td>
                                    <td>{{ $claim->found_item_id ? 'Found Item' : 'Lost Item' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Claim Description:</strong></td>
                                    <td>{{ $claim->description }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Claimed Date:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($claim->claimed_date)->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Claimed Location:</strong></td>
                                    <td>{{ $claim->claimed_location }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Claimant Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Claimant:</strong></td>
                                    <td>{{ $claim->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $claim->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $claim->user->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Organization:</strong></td>
                                    <td>{{ $claim->foundItem->organization->name ?? $claim->lostItem->organization->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Submitted On:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($claim->created_at)->format('M d, Y H:i') }}</td>
                                </tr>
                                @if($claim->claim_code)
                                <tr>
                                    <td><strong>Claim Code:</strong></td>
                                    <td><code>{{ $claim->claim_code }}</code></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($claim->photo)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Proof of Ownership</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="{{ asset('storage/' . $claim->photo) }}" 
                                         alt="Proof of ownership" 
                                         class="img-fluid rounded"
                                         style="max-height: 300px; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($claim->admin_notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Admin Notes</h6>
                            <div class="alert alert-info">
                                {{ $claim->admin_notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
