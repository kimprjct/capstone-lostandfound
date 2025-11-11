@extends('layouts.adminApp')

@section('header', 'Found Item Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Found Item Information</h5>
                    <div>
                        <a href="{{ route('admin.found-items.edit', $foundItem) }}" class="btn btn-primary btn-sm">Edit</a>
                        <a href="{{ route('admin.found-items.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $foundItem->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $foundItem->description }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>{{ $foundItem->category }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location Found:</strong></td>
                                    <td>{{ $foundItem->location }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date Found:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($foundItem->date_found)->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $foundItem->status === 'claimed' ? 'success' : ($foundItem->status === 'returned' ? 'info' : 'warning') }}">
                                            {{ ucfirst($foundItem->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Finder Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Finder:</strong></td>
                                    <td>{{ $foundItem->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $foundItem->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Organization:</strong></td>
                                    <td>{{ $foundItem->organization->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Reported On:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($foundItem->created_at)->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($foundItem->photos && $foundItem->photos->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Photos</h6>
                            <div class="row">
                                @foreach($foundItem->photos as $photo)
                                <div class="col-md-3 mb-3">
                                    <img src="{{ asset('storage/' . $photo->image_path) }}" 
                                         alt="Found item photo" 
                                         class="img-fluid rounded"
                                         style="max-height: 200px; object-fit: cover;">
                                </div>
                                @endforeach
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
