@extends('layouts.tenantApp')

@section('title', 'Report Found Item')

@section('page-title', 'Report Found Item')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-6">Add New Found Item</h3>

    <form action="{{ route('tenant.found-items.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Title --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Item Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
        </div>

        {{-- Category --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Category</label>
            <input type="text" name="category" value="{{ old('category') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        {{-- Location Found --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Location Found</label>
            <input type="text" name="location" value="{{ old('location') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        {{-- Date Found --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Date Found</label>
            <input type="date" name="date_found" value="{{ old('date_found') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        {{-- Image --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Upload Item Image</label>
            <input type="file" name="image" accept="image/*"
                class="mt-1 block w-full text-sm text-gray-500">
        </div>

        {{-- Reporter Name --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Reporter Name</label>
            <input type="text" name="reporter_name" value="{{ old('reporter_name') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        {{-- Reporter Email --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Reporter Email</label>
            <input type="email" name="reporter_email" value="{{ old('reporter_email') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        {{-- Reporter Phone --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Reporter Phone</label>
            <input type="text" name="reporter_phone" value="{{ old('reporter_phone') }}"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                <i class="fas fa-save mr-1"></i> Save Item
            </button>
        </div>
    </form>
</div>
@endsection
