<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Found Item Details</h2>
                        <div class="flex space-x-2">
                            <a href="{{ route('tenant.found-items.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>Back to List
                            </a>
                            <a href="{{ route('tenant.found-items.edit', $foundItem->id) }}" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                @if($foundItem->image)
                                    <img src="{{ asset('storage/' . $foundItem->image) }}" alt="{{ $foundItem->title }}" class="w-full h-auto rounded-lg shadow-md">
                                @else
                                    <div class="h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400 text-5xl"></i>
                                    </div>
                                @endif
                                
                                <div class="mt-4">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                        {{ $foundItem->status === 'found' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $foundItem->status === 'claimed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $foundItem->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}
                                    ">
                                        {{ ucfirst($foundItem->status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-gray-700 mb-2">Reported By</h3>
                                <p>{{ $foundItem->user->first_name }} {{ $foundItem->user->last_name }}</p>
                                <p class="text-sm text-gray-500">{{ $foundItem->user->email }}</p>
                                <p class="text-sm text-gray-500">{{ $foundItem->created_at->format('M d, Y - h:i A') }}</p>
                            </div>
                        </div>
                        
                        <div class="md:col-span-2">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h1 class="text-2xl font-bold mb-4">{{ $foundItem->title }}</h1>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <h3 class="font-semibold text-gray-700">Category</h3>
                                        <p>{{ $foundItem->category }}</p>
                                    </div>
                                    
                                    <div>
                                        <h3 class="font-semibold text-gray-700">Date Found</h3>
                                        <p>{{ $foundItem->date_found->format('M d, Y') }}</p>
                                    </div>
                                    
                                    <div>
                                        <h3 class="font-semibold text-gray-700">Location Found</h3>
                                        <p>{{ $foundItem->location }}</p>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <h3 class="font-semibold text-gray-700 mb-2">Description</h3>
                                    <div class="bg-white p-3 rounded border border-gray-200">
                                        <p>{{ $foundItem->description }}</p>
                                    </div>
                                </div>
                                
                                <hr class="my-6">
                                
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-2">Claim Requests</h3>
                                    @if(isset($foundItem->claims) && $foundItem->claims->count() > 0)
                                        <div class="bg-white rounded border border-gray-200">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead>
                                                    <tr>
                                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimant</th>
                                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($foundItem->claims as $claim)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="text-sm font-medium text-gray-900">{{ $claim->user->first_name }} {{ $claim->user->last_name }}</div>
                                                            <div class="text-sm text-gray-500">{{ $claim->user->email }}</div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {{ $claim->created_at->format('M d, Y') }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                {{ $claim->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                                {{ $claim->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                                                {{ $claim->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                                            ">
                                                                {{ ucfirst($claim->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            @if($claim->status === 'pending')
                                                            <div class="flex space-x-2">
                                                                <form action="{{ route('tenant.claims.approve', $claim->id) }}" method="POST">
                                                                    @csrf
                                                                    <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Are you sure you want to approve this claim?')">
                                                                        <i class="fas fa-check"></i> Approve
                                                                    </button>
                                                                </form>
                                                                <form action="{{ route('tenant.claims.reject', $claim->id) }}" method="POST">
                                                                    @csrf
                                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to reject this claim?')">
                                                                        <i class="fas fa-times"></i> Reject
                                                                    </button>
                                                                </form>
                                                            </div>
                                                            @else
                                                                <span class="text-gray-500">Processed</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-gray-500 italic">No claim requests yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
