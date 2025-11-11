@extends('layouts.adminApp')

@section('header', 'Lost Item Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lost Item Information</h5>
                    <div>
                        <a href="{{ route('admin.lost-items.edit', $lostItem) }}" class="btn btn-primary btn-sm">Edit</a>
                        <a href="{{ route('admin.lost-items.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $lostItem->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $lostItem->description }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>{{ $lostItem->category }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location Lost:</strong></td>
                                    <td>{{ $lostItem->location }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date Lost:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($lostItem->date_lost)->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $lostItem->status === 'resolved' ? 'success' : ($lostItem->status === 'returned' ? 'info' : 'warning') }}">
                                            {{ ucfirst($lostItem->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Reporter Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Reporter:</strong></td>
                                    <td>{{ $lostItem->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $lostItem->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Organization:</strong></td>
                                    <td>{{ $lostItem->organization->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Reported On:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($lostItem->created_at)->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($lostItem->photos && $lostItem->photos->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Photos</h6>
                            <div class="row">
                                @foreach($lostItem->photos as $photo)
                                <div class="col-md-3 mb-3">
                                    <img src="{{ asset('storage/' . $photo->image_path) }}" 
                                         alt="Lost item photo" 
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
