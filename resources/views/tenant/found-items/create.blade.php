<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Report Found Item</h2>
                        <a href="{{ route('tenant.found-items.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to List
                        </a>
                    </div>

                    @if($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p class="font-bold">Please fix the following errors:</p>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('tenant.found-items.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Item Name/Title</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="category" id="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select a category</option>
                                    <option value="Electronics" {{ old('category') == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                                    <option value="Clothing" {{ old('category') == 'Clothing' ? 'selected' : '' }}>Clothing</option>
                                    <option value="Accessories" {{ old('category') == 'Accessories' ? 'selected' : '' }}>Accessories</option>
                                    <option value="Documents" {{ old('category') == 'Documents' ? 'selected' : '' }}>Documents</option>
                                    <option value="Keys" {{ old('category') == 'Keys' ? 'selected' : '' }}>Keys</option>
                                    <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="date_found" class="block text-sm font-medium text-gray-700 mb-1">Date Found</label>
                                <input type="date" name="date_found" id="date_found" value="{{ old('date_found', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                            </div>

                            <div class="col-span-2">
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location Found</label>
                                <input type="text" name="location" id="location" value="{{ old('location') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                            </div>

                            <div class="col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" id="description" rows="5" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>{{ old('description') }}</textarea>
                                <p class="text-sm text-gray-500 mt-1">Provide as much detail as possible about the item</p>
                            </div>

                            <div class="col-span-2">
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Item Image</label>
                                <input type="file" name="image" id="image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*">
                                <p class="text-sm text-gray-500 mt-1">Upload an image of the found item (optional)</p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i>Save Found Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
