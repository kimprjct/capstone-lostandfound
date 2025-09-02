<x-app-layout>
    <div class="flex min-h-screen bg-gray-100">
        {{-- Sidebar --}}
        <div class="w-64 bg-white shadow-md">
            @include('admin.components.sidebar')
        </div>

        {{-- Main Content --}}
        <div class="flex-1 p-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">👥 User Management</h2>

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
                                    <td class="px-6 py-4 flex items-center justify-center space-x-2">
                                        <!-- Edit Button -->
                                        <button onclick="openModal('editModal-{{ $user->id }}')"
                                            class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-2 rounded-lg shadow">
                                            Edit
                                        </button>

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

                                <!-- Edit Modal -->
                                <div id="editModal-{{ $user->id }}" 
                                    class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50">

                                    <!-- Draggable Modal -->
                                    <div id="draggable-{{ $user->id }}" 
                                        class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 relative cursor-move"
                                        style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);">

                                        <!-- Close button -->
                                        <button onclick="closeModal('editModal-{{ $user->id }}')"
                                            class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">
                                            &times;
                                        </button>

                                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Edit User</h2>

                                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-4">
                                            @csrf
                                            @method('PUT')

                                            <div>
                                                <label class="block text-gray-600 mb-1">Name</label>
                                                <input type="text" name="name" value="{{ $user->name }}"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 ">
                                            </div>

                                            <div>
                                                <label class="block text-gray-600 mb-1">Email</label>
                                                <input type="email" name="email" value="{{ $user->email }}"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
                                            </div>

                                            <div>
                                                <label class="block text-gray-600 mb-1">Password (leave blank if unchanged)</label>
                                                <input type="password" name="password"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
                                                <input type="password" name="password_confirmation" placeholder="Confirm password"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-2 focus:ring focus:ring-blue-200">
                                            </div>

                                            <div class="flex justify-end space-x-2">
                                                <button type="button" onclick="closeModal('editModal-{{ $user->id }}')"
                                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow mt-3">
                                                    Cancel
                                                </button>
                                                <button type="submit"
                                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow mt-3">
                                                    Update
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Script --}}
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        // Make modals draggable
        document.querySelectorAll("[id^='draggable-']").forEach(modal => {
            let isDragging = false;
            let offsetX, offsetY;

            modal.addEventListener("mousedown", (e) => {
                // Only drag if clicked outside inputs/forms/buttons
                if (e.target.closest("input, button, form, textarea, label")) return;

                isDragging = true;
                offsetX = e.clientX - modal.offsetLeft;
                offsetY = e.clientY - modal.offsetTop;
                modal.style.transition = "none"; // prevent snapping glitch
            });

            document.addEventListener("mousemove", (e) => {
                if (isDragging) {
                    modal.style.left = (e.clientX - offsetX) + "px";
                    modal.style.top = (e.clientY - offsetY) + "px";
                    modal.style.transform = "none"; // cancel center transform once moved
                }
            });

            document.addEventListener("mouseup", () => {
                isDragging = false;
            });
        });
    </script>
</x-app-layout>
