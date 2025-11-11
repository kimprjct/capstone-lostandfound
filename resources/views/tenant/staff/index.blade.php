@extends('layouts.tenantApp')

@section('title', 'User Management')

@section('page-title', 'User Management')

@section('content')

<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">Users</h3>

    <a href="{{ route('tenant.staff.create') }}" 
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
        <i class="fas fa-plus mr-1"></i> Add New Admin
    </a>
</div>

{{-- Filter Buttons --}}
<div class="mb-4 flex">
    <button onclick="filterUsers('all')" 
        class="px-3 py-1 mr-3 rounded transition-colors duration-200
        {{ request()->has('role') ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' : 'bg-blue-500 text-white hover:bg-blue-600' }}">
        Show All
    </button>

    <button onclick="filterUsers('tenant')" 
        class="px-3 py-1 mr-3 rounded transition-colors duration-200
        {{ request('role') === 'tenant' ? 'bg-blue-500 text-white hover:bg-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Admins
    </button>

    <button onclick="filterUsers('user')" 
        class="px-3 py-1 mr-3 rounded transition-colors duration-200
        {{ request('role') === 'user' ? 'bg-blue-500 text-white hover:bg-blue-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        End-users
    </button>
</div>



{{-- ❌ Removed duplicate error block since layouts.app already handles errors --}}

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    @if(isset($staffMembers) && count($staffMembers) > 0)
        <table class="min-w-full divide-y divide-gray-200" id="usersTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($staffMembers as $staff)
                    <tr data-role="{{ strtolower($staff->role ?? 'user') }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $staff->first_name ?? '' }} {{ $staff->middle_name ?? '' }} {{ $staff->last_name ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $staff->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $staff->phone_number ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-700">
                                @if(strtolower($staff->role) === 'tenant')
                                    Admin
                                @elseif(strtolower($staff->role) === 'user')
                                    End-user
                                @else
                                    {{ ucfirst($staff->role) }}
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $staff->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $staff->status ?? 'Active' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                            {{-- View Button --}}
                            <a href="{{ route('tenant.staff.show', $staff->id) }}" 
                               class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-md hover:bg-indigo-200">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>

                            {{-- Delete Button --}}
                            <form action="{{ route('tenant.staff.destroy', $staff->id) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-100 text-red-700 px-3 py-1 rounded-md hover:bg-red-200">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $staffMembers->links() }}
        </div>
    @else
        <div class="px-6 py-4 text-center text-gray-500">
            No users found.
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    function filterUsers(role) {
        let rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            let currentRole = row.getAttribute('data-role');

            // normalize tenant_admin -> tenant
            if (currentRole === 'tenant_admin') currentRole = 'tenant';

            if (role === 'all' || currentRole === role) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
