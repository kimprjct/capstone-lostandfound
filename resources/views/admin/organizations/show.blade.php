@extends('layouts.app')

@section('title', $organization->name)

@section('content')
<div class="space-y-8">

    <h2 class="text-2xl font-bold mb-6">{{ $organization->name }} - Items</h2>

    {{-- Lost Items --}}
    <section>
        <h3 class="text-xl font-semibold mb-4">Lost Items</h3>
        @if($lostItems->isEmpty())
            <p class="text-gray-500 mb-6">No lost items.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach ($lostItems as $item)
                <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
                    <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if($item->photos && $item->photos->count() > 0)
                            <div class="relative w-full h-full">
                                <div id="carousel-{{ $item->id }}" class="carousel slide w-full h-full" data-bs-ride="carousel">
                                    <div class="carousel-inner w-full h-full">
                                        @foreach($item->photos as $index => $photo)
                                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }} w-full h-full">
                                                <img src="{{ $photo->image_url }}" alt="{{ $item->title }}" class="object-cover w-full h-full" />
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($item->photos->count() > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{{ $item->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-{{ $item->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @elseif($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="object-cover w-full h-full" />
                        @else
                            <span class="text-gray-400">No image</span>
                        @endif
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h4 class="font-semibold text-lg mb-1">{{ $item->title }}</h4>
                        <p class="text-sm text-gray-600 mb-1">
                            Reported by: {{ $item->user ? trim($item->user->first_name . ' ' . $item->user->last_name) : 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-500 mb-1">
                            Date Reported: {{ $item->created_at->format('M d, Y') }}
                        </p>
                        <p class="text-sm text-gray-600 mt-auto">
                            Status: 
                            @if($item->status === 'claimed')
                                Claimed
                            @else
                                Not Yet Claimed
                            @endif
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Found Items --}}
    <section>
        <h3 class="text-xl font-semibold mb-4">Found Items</h3>
        @if($foundItems->isEmpty())
            <p class="text-gray-500 mb-6">No found items.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach ($foundItems as $item)
                <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
                    <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if($item->photos && $item->photos->count() > 0)
                            <div class="relative w-full h-full">
                                <div id="carousel-{{ $item->id }}" class="carousel slide w-full h-full" data-bs-ride="carousel">
                                    <div class="carousel-inner w-full h-full">
                                        @foreach($item->photos as $index => $photo)
                                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }} w-full h-full">
                                                <img src="{{ $photo->image_url }}" alt="{{ $item->title }}" class="object-cover w-full h-full" />
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($item->photos->count() > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{{ $item->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-{{ $item->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @elseif($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="object-cover w-full h-full" />
                        @else
                            <span class="text-gray-400">No image</span>
                        @endif
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h4 class="font-semibold text-lg mb-1">{{ $item->title }}</h4>
                        <p class="text-sm text-gray-600 mb-1">
                            Reported by: {{ $item->user ? trim($item->user->first_name . ' ' . $item->user->last_name) : 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-500 mb-1">
                            Date Reported: {{ $item->created_at->format('M d, Y') }}
                        </p>
                        <p class="text-sm text-gray-600 mt-auto">
                            Status: 
                            @if($item->status === 'claimed')
                                Claimed
                            @else
                                Not Yet Claimed
                            @endif
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </section>

</div>
@endsection
