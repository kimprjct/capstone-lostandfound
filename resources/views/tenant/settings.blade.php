@extends('layouts.tenantApp')

@section('title', 'Organization Settings')

@section('page-title', 'Organization Settings')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-bold mb-6">Organization Information</h3>
    
    <form action="{{ route('tenant.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                @if($organization && $organization->logo)
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name ?? 'Organization Logo' }}" class="h-32 object-contain">
                    </div>
                @endif
                
                <div class="mb-4">
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Organization Logo</label>
                    <input type="file" id="logo" name="logo" accept="image/*" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                    <p class="text-gray-500 text-sm mt-1">Upload a new logo (optional)</p>
                    @error('logo')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="mb-4 col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Organization Name</label>
                <textarea id="name" name="name" rows="2"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full resize-none break-words whitespace-normal"
                    required>{{ old('name', $organization ? $organization->name : '') }}</textarea>
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="mb-4 col-span-2">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input type="text" id="address" name="address" value="{{ old('address', $organization ? $organization->address : '') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" required>
                @error('address')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="mb-4 col-span-2">
                <label for="claim_location" class="block text-sm font-medium text-gray-700 mb-1">Claim Location</label>
                <input type="text" id="claim_location" name="claim_location" value="{{ old('claim_location', $organization ? $organization->claim_location : '') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" placeholder="e.g., Student Affairs Office, Building B">
                <p class="text-gray-500 text-sm mt-1">Where users should go to retrieve approved items</p>
                @error('claim_location')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="mb-4 col-span-2">
                <label for="office_hours" class="block text-sm font-medium text-gray-700 mb-1">Office Hours</label>
                <input type="text" id="office_hours" name="office_hours" value="{{ old('office_hours', $organization ? $organization->office_hours : '') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" placeholder="e.g., Monday to Friday, 8:00 AM - 5:00 PM">
                <p class="text-gray-500 text-sm mt-1">Office hours for claim retrieval</p>
                @error('office_hours')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="col-span-2">
                <h4 class="font-semibold text-lg mt-4 mb-2 border-b pb-2">Appearance Settings</h4>
            </div>
            
            <div class="mb-4">
                <label for="color_theme" class="block text-sm font-medium text-gray-700 mb-1">Sidebar Color Theme</label>
                <select id="color_theme" name="color_theme" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                    <option value="indigo" {{ old('color_theme', $organization?->color_theme) == 'indigo' ? 'selected' : '' }}>Indigo (Default)</option>
                    <option value="blue" {{ old('color_theme', $organization?->color_theme) == 'blue' ? 'selected' : '' }}>Blue</option>
                    <option value="green" {{ old('color_theme', $organization?->color_theme) == 'green' ? 'selected' : '' }}>Green</option>
                    <option value="red" {{ old('color_theme', $organization?->color_theme) == 'red' ? 'selected' : '' }}>Red</option>
                    <option value="purple" {{ old('color_theme', $organization?->color_theme) == 'purple' ? 'selected' : '' }}>Purple</option>
                    <option value="pink" {{ old('color_theme', $organization?->color_theme) == 'pink' ? 'selected' : '' }}>Pink</option>
                    <option value="yellow" {{ old('color_theme', $organization?->color_theme) == 'yellow' ? 'selected' : '' }}>Yellow</option>
                    <option value="gray" {{ old('color_theme', $organization?->color_theme) == 'gray' ? 'selected' : '' }}>Gray</option>
                </select>
                <p class="text-gray-500 text-sm mt-1">Choose the primary color for your sidebar</p>
            </div>
            
            <div class="mb-4">
                <label for="sidebar_bg" class="block text-sm font-medium text-gray-700 mb-1">Sidebar Background Style</label>
                <select id="sidebar_bg" name="sidebar_bg" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                    <option value="default" {{ old('sidebar_bg', $organization?->sidebar_bg) == 'default' ? 'selected' : '' }}>Solid Color (Default)</option>
                    <option value="gradient" {{ old('sidebar_bg', $organization?->sidebar_bg) == 'gradient' ? 'selected' : '' }}>Gradient</option>
                    <option value="pattern-dots" {{ old('sidebar_bg', $organization?->sidebar_bg) == 'pattern-dots' ? 'selected' : '' }}>Pattern - Dots</option>
                    <option value="pattern-lines" {{ old('sidebar_bg', $organization?->sidebar_bg) == 'pattern-lines' ? 'selected' : '' }}>Pattern - Lines</option>
                    <option value="pattern-grid" {{ old('sidebar_bg', $organization?->sidebar_bg) == 'pattern-grid' ? 'selected' : '' }}>Pattern - Grid</option>
                </select>
                <p class="text-gray-500 text-sm mt-1">Select a background style for your sidebar</p>
            </div>
            
            <div class="col-span-2 border p-4 rounded-md bg-gray-50">
                <h5 class="font-medium mb-2">Preview:</h5>
                <div id="theme-preview" class="w-full h-20 rounded-md flex items-center justify-center text-white font-medium">
                    Your Sidebar Preview
                </div>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colorThemeSelect = document.getElementById('color_theme');
        const sidebarBgSelect = document.getElementById('sidebar_bg');
        const previewElement = document.getElementById('theme-preview');
        
        const colorMap = {
            'indigo': { base: '#4338ca', hover: '#3730a3' },
            'blue': { base: '#1d4ed8', hover: '#1e40af' },
            'green': { base: '#15803d', hover: '#166534' },
            'red': { base: '#b91c1c', hover: '#991b1b' },
            'purple': { base: '#7e22ce', hover: '#6b21a8' },
            'pink': { base: '#be185d', hover: '#9d174d' },
            'yellow': { base: '#a16207', hover: '#854d0e' },
            'gray': { base: '#374151', hover: '#1f2937' }
        };
        
        updateThemePreview();
        colorThemeSelect.addEventListener('change', updateThemePreview);
        sidebarBgSelect.addEventListener('change', updateThemePreview);
        
        function updateThemePreview() {
            const colorTheme = colorThemeSelect.value;
            const sidebarBg = sidebarBgSelect.value;
            const colors = colorMap[colorTheme] || colorMap['indigo'];
            previewElement.className = 'w-full h-20 rounded-md flex items-center justify-center text-white font-medium';
            previewElement.style = '';
            previewElement.style.backgroundColor = colors.base;
            
            if (sidebarBg === 'gradient') {
                previewElement.style.background = `linear-gradient(135deg, ${colors.base} 0%, ${colors.hover} 100%)`;
            } else if (sidebarBg === 'pattern-dots') {
                previewElement.style.backgroundImage = 'radial-gradient(rgba(255,255,255,0.15) 1px, transparent 1px)';
                previewElement.style.backgroundSize = '10px 10px';
            } else if (sidebarBg === 'pattern-lines') {
                previewElement.style.backgroundImage = 'linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.1) 75%, transparent 75%, transparent)';
                previewElement.style.backgroundSize = '20px 20px';
            } else if (sidebarBg === 'pattern-grid') {
                previewElement.style.backgroundImage = 'linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(to right, rgba(255,255,255,0.1) 1px, transparent 1px)';
                previewElement.style.backgroundSize = '20px 20px';
            }
        }
    });
</script>
@endpush
