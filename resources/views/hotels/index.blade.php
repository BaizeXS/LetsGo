@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/hotel.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-2 py-3">
  <!-- Search and Weather Card -->
  <div class="flex flex-col md:flex-row gap-3">
    <!-- Search Card -->
    <div class="w-full md:w-3/4 bg-white rounded-lg shadow p-4">
      <h2 class="text-lg font-bold text-gray-800 mb-3">Book Hotels</h2>
      <form method="GET" action="{{ route('hotels.index') }}" class="space-y-3">
        <!-- Row 1: Destination/Check-in/Check-out -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
          <!-- Destination/Hotel Name -->
          <div x-data="cityDropdown('{{ $city }}', {{ json_encode($popularCities['domestic'] ?? []) }}, {{ json_encode($popularCities['international'] ?? []) }})"
            class="relative"
            @click.away="cityDropdownOpen = false">
            <label for="cityInput" class="block text-gray-700 text-sm font-medium mb-1">Destination/Hotel Name</label>
            <input type="text" id="cityInput" name="city"
              x-model="currentCity"
              @focus="cityDropdownOpen = true"
              @keydown.escape.window="cityDropdownOpen = false"
              @input.debounce.300ms="cityDropdownOpen = true"
              placeholder="Enter city or hotel name" class="w-full px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 text-base">

            <div x-show="cityDropdownOpen"
              x-cloak
              x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="opacity-0 transform scale-95"
              x-transition:enter-end="opacity-100 transform scale-100"
              x-transition:leave="transition ease-in duration-75"
              x-transition:leave-start="opacity-100 transform scale-100"
              x-transition:leave-end="opacity-0 transform scale-95"
              class="absolute z-10 mt-1 w-full max-h-96 overflow-y-auto bg-white rounded-md shadow-lg border border-gray-200">

              <!-- Recently Searched Section -->
              <template x-if="recentSearches.length > 0">
                <div class="p-3 border-b border-gray-100">
                  <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Recently Searched</span>
                    <button type="button" @click.prevent.stop="clearHistory()" class="text-xs text-gray-400 hover:text-gray-600 hover:underline">Clear History</button>
                  </div>
                  <div class="mt-2 grid grid-cols-3 md:grid-cols-4 gap-y-2 gap-x-3">
                    <template x-for="(search, index) in recentSearches" :key="index">
                      <button type="button" @click="selectCity(search)" class="text-left text-sm text-gray-700 hover:text-red-500 truncate" x-text="search"></button>
                    </template>
                  </div>
                </div>
              </template>

              <!-- Popular Domestic Cities Section -->
              <template x-if="popularDomesticCities.length > 0">
                <div class="p-3 border-b border-gray-100">
                  <h3 class="text-sm font-semibold text-gray-500 mb-2">Popular Domestic Cities</h3>
                  <div class="grid grid-cols-3 md:grid-cols-4 gap-y-2 gap-x-3">
                    <template x-for="(popCity, index) in popularDomesticCities" :key="'dom-'+index">
                      <button type="button" @click="selectCity(popCity)" class="text-left text-sm text-gray-700 hover:text-red-500 truncate" x-text="popCity"></button>
                    </template>
                  </div>
                </div>
              </template>

              <!-- Popular International Cities Section -->
              <template x-if="popularInternationalCities.length > 0">
                <div class="p-3 bg-gray-50 rounded-b-md">
                  <h3 class="text-sm font-semibold text-gray-500 mb-2">Popular International Cities</h3>
                  <div class="grid grid-cols-3 md:grid-cols-4 gap-y-2 gap-x-3">
                    <template x-for="(popCity, index) in popularInternationalCities" :key="'intl-'+index">
                      <button type="button" @click="selectCity(popCity)" class="text-left text-sm text-gray-700 hover:text-red-500 truncate" x-text="popCity"></button>
                    </template>
                  </div>
                </div>
              </template>
            </div>
          </div>
          <!-- Check-in/Check-out Date -->
          <div>
            <div class="flex justify-between items-center mb-1">
              <label for="checkinDate" class="block text-gray-700 text-sm font-medium">Check-in</label>
              <span class="text-gray-500 text-xs" id="durationDisplay"></span>
              <label for="checkoutDate" class="block text-gray-700 text-sm font-medium">Check-out</label>
            </div>
            <div class="flex">
              <div class="w-1/2 pr-1">
                <input type="date" id="checkinDate" name="checkin" value="{{ $checkin }}" min="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-l-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 text-sm">
              </div>
              <div class="w-1/2 pl-1">
                <input type="date" id="checkoutDate" name="checkout" value="{{ $checkout }}" class="w-full px-3 py-2 rounded-r-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 text-sm">
              </div>
            </div>
          </div>
        </div>

        <!-- Row 2: Room/Level/Keywords/Search -->
        <div class="flex flex-col sm:flex-row gap-4 items-end">
          <!-- Left half: Room and Guests, Hotel Level -->
          <div class="w-full sm:w-1/2 flex flex-col sm:flex-row gap-4">
            <div class="w-full sm:w-1/2">
              <label class="block text-gray-700 text-sm font-medium mb-1">Rooms & Guests</label>
              <div x-data="roomGuestSelector({{ $rooms ?? 1 }}, {{ $guests ?? 1 }})" class="relative">
                <input type="hidden" name="rooms" x-model="rooms">
                <input type="hidden" name="guests" :value="adults + children">

                <button type="button"
                  @click="open = !open"
                  class="w-full flex justify-between items-center px-3 py-2 border border-gray-300 rounded-md bg-white text-sm text-left focus:outline-none focus:ring-1 focus:ring-red-500 overflow-hidden whitespace-nowrap text-ellipsis">
                  <span x-text="formattedText()" class="truncate">1 Room, 1 Adult</span>
                  <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0 ml-1" :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>

                <div x-show="open"
                  x-cloak
                  @click.away="open = false"
                  x-transition:enter="transition ease-out duration-100"
                  x-transition:enter-start="opacity-0 transform scale-95"
                  x-transition:enter-end="opacity-100 transform scale-100"
                  x-transition:leave="transition ease-in duration-75"
                  x-transition:leave-start="opacity-100 transform scale-100"
                  x-transition:leave-end="opacity-0 transform scale-95"
                  class="absolute left-0 z-10 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200">

                  <div class="p-4 space-y-4">
                    <div class="flex items-center justify-between">
                      <span class="text-gray-700 font-medium">Rooms</span>
                      <div class="flex items-center space-x-3">
                        <button type="button"
                          @click="decrement('rooms')"
                          :disabled="rooms <= 1"
                          class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-full border border-gray-300">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                          </svg>
                        </button>
                        <span class="w-6 text-center text-sm font-semibold tabular-nums" x-text="rooms">1</span>
                        <button type="button"
                          @click="increment('rooms')"
                          class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 rounded-full border border-gray-300">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                          </svg>
                        </button>
                      </div>
                    </div>

                    <div class="flex items-center justify-between">
                      <div>
                        <span class="text-gray-700 font-medium">Adults</span>
                        <span class="text-xs text-gray-500 block">18+ years</span>
                      </div>
                      <div class="flex items-center space-x-3">
                        <button type="button"
                          @click="decrement('adults')"
                          :disabled="adults <= 1"
                          class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-full border border-gray-300">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                          </svg>
                        </button>
                        <span class="w-6 text-center text-sm font-semibold tabular-nums" x-text="adults">1</span>
                        <button type="button"
                          @click="increment('adults')"
                          class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 rounded-full border border-gray-300">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                          </svg>
                        </button>
                      </div>
                    </div>

                    <div class="flex items-center justify-between">
                      <div>
                        <span class="text-gray-700 font-medium">Children</span>
                        <span class="text-xs text-gray-500 block">0-17 years</span>
                      </div>
                      <div class="flex items-center space-x-3">
                        <button type="button"
                          @click="decrement('children')"
                          :disabled="children <= 0"
                          class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-full border border-gray-300">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                          </svg>
                        </button>
                        <span class="w-6 text-center text-sm font-semibold tabular-nums" x-text="children">0</span>
                        <button type="button"
                          @click="increment('children')"
                          class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 rounded-full border border-gray-300">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="w-full sm:w-1/2">
              <label for="hotelClassSelect" class="block text-gray-700 text-sm font-medium mb-1">Hotel Level</label>
              <div class="relative">
                <select id="hotelClassSelect" name="hotel_class" class="w-full appearance-none px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 bg-white text-sm">
                  <option value="">Any</option>
                  <option value="5" {{ request('hotel_class') == '5' ? 'selected' : '' }}>5 Star / Luxury</option>
                  <option value="4" {{ request('hotel_class') == '4' ? 'selected' : '' }}>4 Star / Upscale</option>
                  <option value="3" {{ request('hotel_class') == '3' ? 'selected' : '' }}>3 Star / Comfort</option>
                  <option value="2" {{ request('hotel_class') == '2' ? 'selected' : '' }}>Budget</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                  <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Right half: Keywords, Search Button -->
          <div class="w-full sm:w-1/2 flex flex-col sm:flex-row gap-2 items-end mt-4 sm:mt-0">
            <div class="flex-grow w-full">
              <label for="keywordsInput" class="block text-gray-700 text-sm font-medium mb-1">Keywords (Optional)</label>
              <input type="text" id="keywordsInput" name="keywords" value="{{ request('keywords') }}" placeholder="e.g., Airport, Station, Hotel Name..." class="w-full px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 text-sm">
            </div>

            <button type="submit" class="flex-shrink-0 w-full sm:w-28 h-10 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg text-sm mt-2 sm:mt-0">
              <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              Search
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Right half: Weather Card -->
    <div class="w-full md:w-1/4 ">
      @php
      $weatherClass = 'bg-gradient-to-b from-blue-400 to-blue-600'; // Default
      $conditionText = $weather['condition'] ?? null;
      if ($conditionText) {
      if (stripos($conditionText, 'sunny') !== false || stripos($conditionText, 'clear') !== false) {
      $weatherClass = 'bg-gradient-to-b from-yellow-300 to-orange-400';
      } elseif (stripos($conditionText, 'rain') !== false || stripos($conditionText, 'drizzle') !== false) {
      $weatherClass = 'bg-gradient-to-b from-gray-400 to-blue-500'; // Add 'rain' class for animation if desired
      } elseif (stripos($conditionText, 'cloud') !== false || stripos($conditionText, 'overcast') !== false) {
      $weatherClass = 'bg-gradient-to-b from-gray-300 to-gray-500';
      } elseif (stripos($conditionText, 'snow') !== false || stripos($conditionText, 'sleet') !== false) {
      $weatherClass = 'bg-gradient-to-b from-blue-100 to-blue-300';
      }
      } elseif (!$weather) { // Handle case where weather data fetch failed completely
      $weatherClass = 'bg-gradient-to-b from-gray-400 to-gray-600'; // Indicate error state
      }
      @endphp
      <div class="h-full rounded-lg overflow-hidden shadow relative {{ $weatherClass }}">
        <div class="relative p-4 text-white h-full flex flex-col justify-between">
          @if($weather)
          <div>
            <div class="flex justify-between items-center">
              <h2 class="text-2xl font-bold">{{ $city }}</h2>
              @if($weather['icon'])
              <img src="{{ $weather['icon'] }}" alt="{{ $weather['condition'] }}" class="w-10 h-10">
              @else
              <svg class="w-10 h-10 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              @endif
            </div>
            <p class="text-xs opacity-80">Today</p>
          </div>

          <div class="mt-3 text-center">
            <div class="text-5xl font-bold">{{ $weather['temp'] }}&deg;C</div>
            <div class="text-lg capitalize">{{ $weather['condition'] }}</div>
          </div>

          <div class="grid grid-cols-2 gap-2 mt-3 text-center">
            <div>
              <p class="text-xs opacity-80">Humidity</p>
              <p class="text-sm font-semibold">{{ $weather['humidity'] }}</p>
            </div>
            <div>
              <p class="text-xs opacity-80">Wind</p>
              <p class="text-sm font-semibold">{{ $weather['wind'] }}</p>
            </div>
          </div>
          @else
          <div class="flex flex-col items-center justify-center h-full">
            <svg class="w-12 h-12 text-white opacity-50 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 class="text-xl font-bold">{{ $city }}</h2>
            <p class="text-sm opacity-80">Weather data unavailable</p>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Hotel List -->
  <div class="bg-white rounded-lg shadow p-4 mt-3">
    <h2 class="text-xl font-bold mb-3">Hotels in {{ $city }}</h2>

    @forelse($hotels as $hotel)
    <div class="flex flex-col md:flex-row border rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 mb-3">
      <div class="w-full md:w-1/3 h-48 md:h-auto flex-shrink-0">
        <img src="{{ $hotel['image'] ?? asset('images/placeholder-hotel.png') }}"
          alt="{{ $hotel['name'] }}"
          class="w-full h-full object-cover">
      </div>
      <div class="w-full md:w-2/3 p-3 flex flex-col justify-between">
        <div>
          <div class="flex justify-between items-start gap-2">
            <h3 class="text-lg font-bold text-gray-800 hover:text-red-600">
              <a href="{{ route('hotels.show', $hotel['id']) }}">{{ $hotel['name'] }}</a>
            </h3>
            @if(isset($hotel['rating']))
            <div class="flex-shrink-0 bg-red-100 text-red-800 px-2 py-0.5 rounded text-sm font-semibold whitespace-nowrap">
              {{ number_format($hotel['rating'], 1) }} Rating
            </div>
            @endif
          </div>
          @if(isset($hotel['stars']))
          <div class="flex items-center text-yellow-500 mt-0.5">
            @for($i = 0; $i < floor($hotel['stars']); $i++)
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.799-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              @endfor
          </div>
          @endif
          @if(isset($hotel['distance']))
          <p class="text-gray-600 text-sm mt-1">{{ $hotel['distance'] }}</p>
          @endif
          @if(!empty($hotel['tags']))
          <div class="mt-2 flex flex-wrap gap-1">
            @foreach($hotel['tags'] as $tag)
            <span class="bg-gray-100 text-gray-800 text-xs px-1.5 py-0.5 rounded">{{ $tag }}</span>
            @endforeach
          </div>
          @endif
        </div>
        <div class="flex justify-between items-end mt-3">
          <div>
            <span class="text-gray-500 text-sm">Starts from</span><br />
            <span class="text-xl font-bold text-red-600">¥{{ number_format($hotel['price'], 2) }}</span>
          </div>
          <div class="flex space-x-2">
            <button type="button" onclick="openSubscribeModal({{ $hotel['id'] }})" class="bg-white border border-red-500 text-red-500 px-3 py-1 text-sm rounded hover:bg-red-50 transition duration-150 ease-in-out">
              Subscribe
            </button>
            <a href="{{ route('hotels.show', $hotel['id']) }}" class="bg-red-500 text-white px-3 py-1 text-sm rounded hover:bg-red-600 transition duration-150 ease-in-out">
              View Details
            </a>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class="py-8 text-center text-gray-500">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No hotels found</h3>
      <p class="mt-1 text-sm text-gray-500">No hotels found matching your criteria. Please try different search terms or filters.</p>
    </div>
    @endforelse

    @if ($hotels instanceof \Illuminate\Pagination\LengthAwarePaginator && $hotels->hasPages())
    <div class="mt-4">
      {{ $hotels->links() }}
    </div>
    @endif
  </div>
</div>

<!-- Subscribe -->
<div id="subscribeModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 hidden items-center justify-center p-4" x-cloak>
  <div class="bg-white rounded-lg p-6 w-full max-w-sm shadow-xl" @click.away="closeSubscribeModal()">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-bold text-gray-800">Subscribe to Price Changes</h3>
      <button type="button" onclick="closeSubscribeModal()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <p class="text-gray-600 text-sm mb-4">Enter your email, and we'll notify you of significant price drops for this hotel.</p>

    <form id="subscribeForm" class="space-y-4">
      @csrf
      <input type="hidden" id="hotelId" name="hotel_id">
      <div>
        <label for="subscribeEmail" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input type="email" id="subscribeEmail" name="email" required class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500" placeholder="you@example.com">
        <span class="text-xs text-red-500 mt-1 hidden"></span>
      </div>
      <div>
        <label for="subscribePriceThreshold" class="block text-sm font-medium text-gray-700 mb-1">Notify Below Price (Optional)</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">¥</span>
          <input type="number" id="subscribePriceThreshold" name="price_threshold" min="0" step="any" class="w-full pl-7 pr-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500" placeholder="e.g., 500">
        </div>
        <span class="text-xs text-red-500 mt-1 hidden"></span>
      </div>
      <div class="flex justify-end space-x-3 pt-3">
        <button type="button" onclick="closeSubscribeModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
          Cancel
        </button>
        <button type="submit" class="bg-red-500 text-white px-4 py-2 text-sm rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center justify-center">
          <span class="button-text">Subscribe</span>
          <svg class="animate-spin ml-2 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/hotel.js') }}"></script>
@endpush