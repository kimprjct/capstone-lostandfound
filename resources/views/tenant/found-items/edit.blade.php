@extends('layouts.tenantApp')

@section('page-title', 'Edit Found Item')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Edit Found Item</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('tenant.found-items.index') }}" 
                           class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to List
                        </a>
                        <a href="{{ route('tenant.found-items.show', $foundItem->id) }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </a>
                    </div>
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

                <form action="{{ route('tenant.found-items.update', $foundItem->id) }}" 
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Item Name/Title
                            </label>
                            <input type="text" name="title" id="title" 
                                   value="{{ old('title', $foundItem->title) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 
                                          focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                Category
                            </label>
                            <select name="category" id="category" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 
                                           focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                                <option value="" disabled>Select a category</option>
                                <option value="Electronics" {{ old('category', $foundItem->category) == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                                <option value="Clothing" {{ old('category', $foundItem->category) == 'Clothing' ? 'selected' : '' }}>Clothing</option>
                                <option value="Accessories" {{ old('category', $foundItem->category) == 'Accessories' ? 'selected' : '' }}>Accessories</option>
                                <option value="Documents" {{ old('category', $foundItem->category) == 'Documents' ? 'selected' : '' }}>Documents</option>
                                <option value="Keys" {{ old('category', $foundItem->category) == 'Keys' ? 'selected' : '' }}>Keys</option>
                                <option value="Other" {{ old('category', $foundItem->category) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_found" class="block text-sm font-medium text-gray-700 mb-1">
                                Date Found
                            </label>
                            <input type="date" name="date_found" id="date_found" 
                                   value="{{ old('date_found', $foundItem->date_found->format('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 
                                          focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                                Location Found
                            </label>
                            <input type="text" name="location" id="location" 
                                   value="{{ old('location', $foundItem->location) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 
                                          focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select name="status" id="status" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 
                                           focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                                <option value="found" {{ old('status', $foundItem->status) == 'found' ? 'selected' : '' }}>Found (Available)</option>
                                <option value="claimed" {{ old('status', $foundItem->status) == 'claimed' ? 'selected' : '' }}>Claimed</option>
                                <option value="archived" {{ old('status', $foundItem->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="5"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 
                                             focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>{{ old('description', $foundItem->description) }}</textarea>
                        </div>

                        <div class="col-span-2">
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                                Item Image
                            </label>
                            
                            @if($foundItem->image)
                                <div class="mb-3 flex items-center">
                                    <img src="{{ asset('storage/' . $foundItem->image) }}" 
                                         alt="{{ $foundItem->title }}" 
                                         class="h-24 w-auto rounded border border-gray-200 mr-3">
                                    <div>
                                        <p class="text-sm font-medium">Current Image</p>
                                        <div class="mt-1">
                                            <label for="remove_image" class="inline-flex items-center">
                                                <input type="checkbox" name="remove_image" id="remove_image"
                                                       class="rounded border-gray-300 text-blue-600 shadow-sm 
                                                              focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-600">Remove existing image</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <input type="file" name="image" id="image"
                                   class="w-full text-sm text-gray-500 
                                          file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 
                                          file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                   accept="image/*">
                            <p class="text-sm text-gray-500 mt-1">
                                Upload a new image of the found item (optional)
                            </p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>Update Found Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
