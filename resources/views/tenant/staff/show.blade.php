@extends('layouts.app')

@section('title', 'Staff Details')

@section('page-title', 'Staff Details')

@section('content')
<div class="mb-6 flex justify-between">
    <a href="{{ route('tenant.staff.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
        <i class="fas fa-arrow-left mr-2"></i> {{ __('Back to Staff List') }}
    </a>
    
    <div>
        <a href="{{ route('tenant.staff.edit', $user ? $user->id : 0) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
            <i class="fas fa-edit mr-2"></i> {{ __('Edit Staff') }}
        </a>
        
        <form action="{{ route('tenant.staff.destroy', $user ? $user->id : 0) }}" method="POST" class="inline" id="delete-staff-form">
            @csrf
            @method('DELETE')
            <button type="button" onclick="confirmDelete()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-trash-alt mr-2"></i> {{ __('Delete Staff') }}
            </button>
        </form>
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

<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-bold mb-6">{{ __('Staff Member Information') }}</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h4 class="text-md font-semibold mb-4 border-b pb-2">Personal Information</h4>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">Name</p>
                <p class="text-base">{{ $user->first_name ?? '' }} {{ $user->middle_name ?? '' }} {{ $user->last_name ?? '' }}</p>
            </div>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">Email</p>
                <p class="text-base">{{ $user->email ?? 'N/A' }}</p>
            </div>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">Phone Number</p>
                <p class="text-base">{{ $user->phone_number ?? 'N/A' }}</p>
            </div>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">Address</p>
                <p class="text-base">{{ $user->address ?? 'N/A' }}</p>
            </div>
        </div>
        
        <div>
            <h4 class="text-md font-semibold mb-4 border-b pb-2">Account Information</h4>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">Role</p>
                <p class="text-base capitalize">{{ $user->role ?? 'N/A' }}</p>
            </div>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">Member Since</p>
                <p class="text-base">{{ $user->created_at ? $user->created_at->format('F d, Y') : 'N/A' }}</p>
            </div>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">Last Updated</p>
                <p class="text-base">{{ $user->updated_at ? $user->updated_at->format('F d, Y') : 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this staff member? This action cannot be undone.')) {
            document.getElementById('delete-staff-form').submit();
        }
    }
</script>
@endpush
