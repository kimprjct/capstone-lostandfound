<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Found Items</h2>
                        <a href="{{ route('tenant.found-items.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add Found Item
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        @if($foundItems->count() > 0)
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                    <th class="py-3 px-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="py-3 px-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="py-3 px-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="py-3 px-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Found</th>
                                    <th class="py-3 px-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="py-3 px-4 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($foundItems as $item)
                                <tr>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}" class="h-16 w-16 object-cover rounded">
                                        @else
                                            <div class="h-16 w-16 bg-gray-100 flex items-center justify-center rounded">
                                                <i class="fas fa-image text-gray-400 text-2xl"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">{{ $item->title }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">{{ $item->category }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">{{ $item->location }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">{{ $item->date_found->format('M d, Y') }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $item->status === 'found' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $item->status === 'claimed' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $item->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('tenant.found-items.show', $item->id) }}" class="text-blue-600 hover:text-blue-900" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tenant.found-items.edit', $item->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('tenant.found-items.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $foundItems->links() }}
                        </div>
                        @else
                        <div class="text-center py-8">
                            <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-xl">No found items reported yet</p>
                            <p class="text-gray-400 mt-1">Found items reported by your organization members will appear here.</p>
                            <a href="{{ route('tenant.found-items.create') }}" class="mt-4 inline-block px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Add First Item
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
