<x-app-layout>
    <div class="flex min-h-screen bg-gray-100">
        {{-- Sidebar --}}
        <div class="w-64 bg-white shadow-md">
            @include('admin.components.sidebar')
        </div>

        {{-- Main Content --}}
        <div class="flex-1 p-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">User Management</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 text-gray-700 text-sm uppercase tracking-wide">
                            <tr>
                                <th class="px-6 py-3 text-left">#</th>
                                <th class="px-6 py-3 text-left">Name</th>
                                <th class="px-6 py-3 text-left">Email</th>
                                <th class="px-6 py-3 text-left">Registered At</th>
                                <th class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700">
                            @forelse ($users as $index => $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 font-medium">{{ $user->name }}</td>
                                    <td class="px-6 py-4">{{ $user->email }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 flex items-center justify-center">
                                        <!-- Delete Form -->
                                        <form action="{{ route('admin.users.delete', $user->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-600 hover:bg-red-500 text-white text-sm px-4 py-2 rounded-lg shadow-md transition">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
