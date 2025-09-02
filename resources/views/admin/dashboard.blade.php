<x-app-layout>
    <div class="relative">
        @include('admin.components.sidebar')

        <div class="ml-64">
            <div class="py-6 px-4">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                    {{ __('Admin Dashboard') }}
                </h2>

                {{-- Stats Overview --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white shadow rounded-lg p-6 text-center">
                        <h3 class="text-gray-500 text-sm">Total Organizations Registered</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($tenantCount ?? 0) }}</p>
                    </div>

                    <div class="bg-white shadow rounded-lg p-6 text-center">
                        <h3 class="text-gray-500 text-sm">Active Users (All Orgs)</h3>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($userCount ?? 0) }}</p>
                    </div>

                    <div class="bg-white shadow rounded-lg p-6 text-center">
                        <h3 class="text-gray-500 text-sm">Total Lost/Found Items</h3>
                        <p class="text-2xl font-bold text-indigo-600">{{ number_format($itemCount ?? 0) }}</p>
                    </div>

                    <div class="bg-white shadow rounded-lg p-6 text-center">
                        <h3 class="text-gray-500 text-sm">Pending Organization Approvals</h3>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($pendingRequestsCount ?? 0) }}</p>
                    </div>
                </div>

                {{-- Charts --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Items Reported Over Time</h3>
                        <svg viewBox="0 0 600 260" width="100%" height="260" role="img" aria-label="Items over time area chart">
                            <rect x="0" y="0" width="600" height="260" fill="#ffffff" />
                            <g stroke="#e2e8f0">
                                <line x1="50" y1="220" x2="580" y2="220" />
                                <line x1="50" y1="180" x2="580" y2="180" />
                                <line x1="50" y1="140" x2="580" y2="140" />
                                <line x1="50" y1="100" x2="580" y2="100" />
                                <line x1="50" y1="60" x2="580" y2="60" />
                            </g>
                            <path d="M50 220 L90 200 L130 210 L170 180 L210 170 L250 150 L290 140 L330 120 L370 110 L410 95 L450 80 L490 70 L530 60 L580 50 L580 220 L50 220 Z" fill="rgba(49,130,206,0.25)" stroke="rgba(49,130,206,0.6)" stroke-width="2" />
                            <g fill="#718096" font-size="10">
                                <text x="70" y="238">Jan</text>
                                <text x="130" y="238">Feb</text>
                                <text x="190" y="238">Mar</text>
                                <text x="250" y="238">Apr</text>
                                <text x="310" y="238">May</text>
                                <text x="370" y="238">Jun</text>
                                <text x="430" y="238">Jul</text>
                                <text x="490" y="238">Aug</text>
                                <text x="550" y="238">Sep</text>
                            </g>
                        </svg>
                    </div>

                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Claims Made Over Time</h3>
                        <svg viewBox="0 0 600 260" width="100%" height="260" role="img" aria-label="Claims over time area chart">
                            <rect x="0" y="0" width="600" height="260" fill="#ffffff" />
                            <g stroke="#e2e8f0">
                                <line x1="50" y1="220" x2="580" y2="220" />
                                <line x1="50" y1="180" x2="580" y2="180" />
                                <line x1="50" y1="140" x2="580" y2="140" />
                                <line x1="50" y1="100" x2="580" y2="100" />
                                <line x1="50" y1="60" x2="580" y2="60" />
                            </g>
                            <path d="M50 220 L90 210 L130 205 L170 190 L210 175 L250 160 L290 150 L330 135 L370 120 L410 110 L450 95 L490 80 L530 70 L580 55 L580 220 L50 220 Z" fill="rgba(56,161,105,0.25)" stroke="rgba(56,161,105,0.6)" stroke-width="2" />
                            <g fill="#718096" font-size="10">
                                <text x="70" y="238">Jan</text>
                                <text x="130" y="238">Feb</text>
                                <text x="190" y="238">Mar</text>
                                <text x="250" y="238">Apr</text>
                                <text x="310" y="238">May</text>
                                <text x="370" y="238">Jun</text>
                                <text x="430" y="238">Jul</text>
                                <text x="490" y="238">Aug</text>
                                <text x="550" y="238">Sep</text>
                            </g>
                        </svg>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div class="font-semibold text-gray-800">Quick Links</div>
                        <div class="text-sm text-gray-500">Shortcuts</div>
                    </div>
                    {{-- Add your quick link buttons here --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
