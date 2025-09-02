@extends('layouts.app')

@section('title', 'Organizations')

@section('page-title', 'Organizations')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold">Organizations</h2>
        <a href="{{ route('admin.organizations.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-plus mr-2"></i> Register Organization
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Organization</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Address</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Staff</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Items</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($organizations as $organization)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-6">
                                <div class="flex items-center">
                                    @if($organization->logo)
                                        <img class="h-10 w-10 rounded-full object-cover mr-3" src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }} logo">
                                    @else
                                        <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-sm font-bold">{{ substr($organization->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold">{{ $organization->name }}</p>
                                        <p class="text-xs text-gray-500">Created {{ $organization->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-6">{{ $organization->address }}</td>
                            <td class="py-3 px-6">{{ $organization->users->where('role', 'tenant')->count() }}</td>
                            <td class="py-3 px-6">
                                <div class="flex flex-col">
                                    <span>Lost: {{ $organization->lostItems->count() }}</span>
                                    <span>Found: {{ $organization->foundItems->count() }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-6">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.organizations.show', $organization->id) }}" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.organizations.edit', $organization->id) }}" class="text-yellow-500 hover:text-yellow-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.organizations.destroy', $organization->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this organization?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-3 px-6 text-center text-gray-500">No organizations found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4">
            {{ $organizations->links() }}
        </div>
    </div>
@endsection
