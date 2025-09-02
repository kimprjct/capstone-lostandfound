@extends('layouts.user')

@section('title', 'My Pets')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>My Pets</h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPetModal">
                        <i class="fas fa-plus"></i> Add New Pet
                    </button>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        @if(session('success'))
                            <div class="alert alert-success mx-4">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger mx-4">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if(count($pets) > 0)
                            <div class="row px-3">
                                @foreach($pets as $pet)
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-header p-0 position-relative">
                                            @if($pet->image)
                                                <img src="{{ asset('storage/'.$pet->image) }}" class="card-img-top pet-image" alt="{{ $pet->name }}">
                                            @else
                                                <img src="{{ asset('images/default-pet.jpg') }}" class="card-img-top pet-image" alt="{{ $pet->name }}">
                                            @endif
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light rounded-circle" type="button" id="petOptions{{ $pet->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="petOptions{{ $pet->id }}">
                                                        <li>
                                                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editPetModal{{ $pet->id }}">
                                                                <i class="fas fa-edit text-warning"></i> Edit
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('user.pets.destroy', $pet->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this pet?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-trash text-danger"></i> Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $pet->name }}</h5>
                                            <p class="card-text mb-1">
                                                <strong>Breed:</strong> {{ $pet->breed }}
                                            </p>
                                            <p class="card-text mb-1">
                                                <strong>Age:</strong> {{ $pet->age }} years
                                            </p>
                                            <p class="card-text mb-1">
                                                <strong>Birthday:</strong> {{ $pet->birthday->format('M d, Y') }}
                                            </p>
                                            <p class="card-text mb-1">
                                                <strong>Last Vaccination:</strong> 
                                                @if($pet->last_vaccination_date)
                                                    {{ $pet->last_vaccination_date->format('M d, Y') }}
                                                @else
                                                    Not recorded
                                                @endif
                                            </p>
                                            <button class="btn btn-info btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#viewVaccinationsModal{{ $pet->id }}">
                                                View Vaccination History
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-paw fa-4x text-secondary"></i>
                                </div>
                                <h5>You haven't added any pets yet</h5>
                                <p class="text-secondary">Click the "Add New Pet" button to register your pet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Pet Modal -->
<div class="modal fade" id="addPetModal" tabindex="-1" aria-labelledby="addPetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPetModalLabel">Add New Pet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.pets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Pet Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="breed" class="form-label">Breed <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="breed" name="breed" required>
                    </div>
                    <div class="mb-3">
                        <label for="age" class="form-label">Age (years) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="age" name="age" min="0" step="0.1" required>
                    </div>
                    <div class="mb-3">
                        <label for="birthday" class="form-label">Birthday <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="birthday" name="birthday" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_vaccination_date" class="form-label">Last Vaccination Date</label>
                        <input type="date" class="form-control" id="last_vaccination_date" name="last_vaccination_date">
                    </div>
                    <div class="mb-3">
                        <label for="vaccination_details" class="form-label">Vaccination Details</label>
                        <textarea class="form-control" id="vaccination_details" name="vaccination_details" rows="3" placeholder="Enter details about vaccinations (type, vet, etc.)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Pet Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Upload a photo of your pet (optional)</div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional information about your pet"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Pet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Pet Modals -->
@foreach($pets as $pet)
<div class="modal fade" id="editPetModal{{ $pet->id }}" tabindex="-1" aria-labelledby="editPetModalLabel{{ $pet->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPetModalLabel{{ $pet->id }}">Edit Pet: {{ $pet->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.pets.update', $pet->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name{{ $pet->id }}" class="form-label">Pet Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name{{ $pet->id }}" name="name" value="{{ $pet->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_breed{{ $pet->id }}" class="form-label">Breed <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_breed{{ $pet->id }}" name="breed" value="{{ $pet->breed }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_age{{ $pet->id }}" class="form-label">Age (years) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_age{{ $pet->id }}" name="age" min="0" step="0.1" value="{{ $pet->age }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_birthday{{ $pet->id }}" class="form-label">Birthday <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_birthday{{ $pet->id }}" name="birthday" value="{{ $pet->birthday->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_vaccination_date{{ $pet->id }}" class="form-label">Last Vaccination Date</label>
                        <input type="date" class="form-control" id="edit_last_vaccination_date{{ $pet->id }}" name="last_vaccination_date" 
                            value="{{ $pet->last_vaccination_date ? $pet->last_vaccination_date->format('Y-m-d') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="edit_vaccination_details{{ $pet->id }}" class="form-label">Vaccination Details</label>
                        <textarea class="form-control" id="edit_vaccination_details{{ $pet->id }}" name="vaccination_details" rows="3">{{ $pet->vaccination_details }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image{{ $pet->id }}" class="form-label">Pet Image</label>
                        @if($pet->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$pet->image) }}" alt="{{ $pet->name }}" class="img-thumbnail" style="max-height: 100px">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="edit_image{{ $pet->id }}" name="image" accept="image/*">
                        <div class="form-text">Upload a new photo (leave empty to keep current image)</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes{{ $pet->id }}" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="edit_notes{{ $pet->id }}" name="notes" rows="3">{{ $pet->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Vaccinations Modal -->
<div class="modal fade" id="viewVaccinationsModal{{ $pet->id }}" tabindex="-1" aria-labelledby="viewVaccinationsModalLabel{{ $pet->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewVaccinationsModalLabel{{ $pet->id }}">{{ $pet->name }}'s Vaccination History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(count($pet->vaccinations) > 0)
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Vaccine</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Administered By</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Next Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pet->vaccinations as $vaccination)
                                <tr>
                                    <td>{{ $vaccination->vaccination_date->format('M d, Y') }}</td>
                                    <td>{{ $vaccination->vaccine_name }}</td>
                                    <td>{{ $vaccination->administered_by }}</td>
                                    <td>
                                        @if($vaccination->next_due_date)
                                            {{ $vaccination->next_due_date->format('M d, Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p>No vaccination records found.</p>
                    </div>
                @endif
                
                <hr>
                
                <h6 class="mb-3">Add Vaccination Record</h6>
                <form action="{{ route('user.pet.vaccinations.store', $pet->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vaccine_name{{ $pet->id }}" class="form-label">Vaccine Name</label>
                            <input type="text" class="form-control" id="vaccine_name{{ $pet->id }}" name="vaccine_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="vaccination_date{{ $pet->id }}" class="form-label">Date</label>
                            <input type="date" class="form-control" id="vaccination_date{{ $pet->id }}" name="vaccination_date" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="administered_by{{ $pet->id }}" class="form-label">Administered By</label>
                            <input type="text" class="form-control" id="administered_by{{ $pet->id }}" name="administered_by" placeholder="Veterinarian/Clinic Name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="next_due_date{{ $pet->id }}" class="form-label">Next Due Date</label>
                            <input type="date" class="form-control" id="next_due_date{{ $pet->id }}" name="next_due_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes{{ $pet->id }}" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes{{ $pet->id }}" name="notes" rows="2"></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Add Record</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<style>
    .pet-image {
        height: 200px;
        object-fit: cover;
    }
</style>
@endsection
