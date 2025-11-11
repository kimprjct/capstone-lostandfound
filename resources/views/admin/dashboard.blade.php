@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden max-w-7xl mx-auto p-6">

    {{-- Success message --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-blue-50 text-blue-700 shadow rounded-lg p-6 text-center">
            <div class="flex justify-center mb-2">
                <i class="fas fa-building fa-2x"></i>
            </div>
            <h3 class="text-sm font-medium">Total Organizations</h3>
            <p class="text-3xl font-bold">{{ number_format($organizationCount ?? 0) }}</p>
        </div>

        <div class="bg-green-50 text-green-700 shadow rounded-lg p-6 text-center">
            <div class="flex justify-center mb-2">
                <i class="fas fa-users fa-2x"></i>
            </div>
            <h3 class="text-sm font-medium">Total Users (All Orgs)</h3>
            <p class="text-3xl font-bold">{{ number_format($userCount ?? 0) }}</p>
        </div>

        <div class="bg-indigo-50 text-indigo-700 shadow rounded-lg p-6 text-center">
            <div class="flex justify-center mb-2">
                <i class="fas fa-box fa-2x"></i>
            </div>
            <h3 class="text-sm font-medium">Total Lost/Found Items</h3>
            <p class="text-3xl font-bold">{{ number_format($lostItemsCount + $foundItemsCount ?? 0) }}</p>
        </div>

        <div class="bg-red-50 text-red-700 shadow rounded-lg p-6 text-center">
            <div class="flex justify-center mb-2">
                <i class="fas fa-clipboard-check fa-2x"></i>
            </div>
            <h3 class="text-sm font-medium">Total Claims Made</h3>
            <p class="text-3xl font-bold">{{ number_format($claimCount ?? 0) }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Items Reported Over Time</h3>
            <canvas id="itemsChart"></canvas>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Claims Made Over Time</h3>
            <canvas id="claimsChart"></canvas>
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const itemsCtx = document.getElementById('itemsChart').getContext('2d');
    new Chart(itemsCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyStats['months']),
            datasets: [
                {
                    label: 'Lost Items',
                    data: @json($monthlyStats['lostItems']),
                    borderColor: '#f87171',
                    backgroundColor: 'rgba(248,113,113,0.2)',
                    fill: true
                },
                {
                    label: 'Found Items',
                    data: @json($monthlyStats['foundItems']),
                    borderColor: '#60a5fa',
                    backgroundColor: 'rgba(96,165,250,0.2)',
                    fill: true
                },
                {
                    label: 'Returned Items',
                    data: @json($monthlyStats['returnedItems']),
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(52,211,153,0.2)',
                    fill: true
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0 // ✅ force whole numbers
                    }
                }
            }
        }
    });

    const claimsCtx = document.getElementById('claimsChart').getContext('2d');
    new Chart(claimsCtx, {
        type: 'bar',
        data: {
            labels: @json($claimStats['months']),
            datasets: [{
                label: 'Claims',
                data: @json($claimStats['claims']),
                backgroundColor: '#4ade80',
                borderColor: '#22c55e',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0 // ✅ force whole numbers
                    }
                }
            }
        }
    });
</script>
@endsection
