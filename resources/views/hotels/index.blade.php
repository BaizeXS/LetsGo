@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-2 py-3">
    <!-- Search and Weather Card -->
    <div class="flex flex-col md:flex-row gap-3">
        <!-- Search Card -->
        <div class="w-full md:w-3/4 bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-bold text-gray-800 mb-3">Book Hotels</h2>
            <div class="space-y-3" x-data="{ cityDropdownOpen: false, currentCity: '{{ $city }}' }">
                <!-- Row 1: Destination/Check-in/Check-out -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                    <!-- Destination/Hotel Name -->
                    <div class="relative">
                        <label class="block text-gray-700 text-sm font-medium mb-1">Destination/Hotel Name</label>
                        <input type="text" name="city"
                            x-model="currentCity"
                            @focus="cityDropdownOpen = true"
                            @keydown.escape.window="cityDropdownOpen = false"
                            placeholder="Enter city or hotel name" class="w-full px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 text-base">

                        <div x-show="cityDropdownOpen"
                            x-cloak
                            @click.away="cityDropdownOpen = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-95"
                            class="absolute z-10 mt-1 w-full max-h-96 overflow-y-auto bg-white rounded-md shadow-lg border border-gray-200"
                            style="display: none;">

                            <div class="px-4 py-2 border-b border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Recently Searched</span>
                                    <button class="text-xs text-gray-400 hover:text-gray-600">Clear History</button>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <button @click="currentCity = 'Shanghai'; cityDropdownOpen = false" class="block w-full text-left text-sm text-gray-700 hover:bg-gray-100 px-2 py-1 rounded">Shanghai</button>
                                    <button @click="currentCity = 'Beijing'; cityDropdownOpen = false" class="block w-full text-left text-sm text-gray-700 hover:bg-gray-100 px-2 py-1 rounded">Beijing</button>
                                </div>
                            </div>

                            <div class="px-4 pt-3 pb-2">
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Popular Domestic Cities</h3>
                                <div class="grid grid-cols-5 gap-x-4 gap-y-2">
                                    @foreach($popularCities['domestic'] as $popCity)
                                    <button @click="currentCity = '{{ $popCity }}'; cityDropdownOpen = false" class="text-sm text-gray-700 hover:text-red-500 text-left">
                                        {{ $popCity }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="px-4 pt-3 pb-2 bg-gray-50 rounded-b-md">
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Popular International Cities</h3>
                                <div class="grid grid-cols-5 gap-x-4 gap-y-2">
                                    @foreach($popularCities['international'] as $popCity)
                                    <button @click="currentCity = '{{ $popCity }}'; cityDropdownOpen = false" class="text-sm text-gray-700 hover:text-red-500 text-left">
                                        {{ $popCity }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Check-in/Check-out Date -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-gray-700 text-sm font-medium">Check-in</label>
                            <span class="text-gray-500 text-xs" id="durationDisplay"></span>
                            <label class="block text-gray-700 text-sm font-medium">Check-out</label>
                        </div>
                        <div class="flex">
                            <div class="w-1/2 pr-1">
                                <input type="date" id="checkinDate" name="checkin" value="{{ $checkin }}" class="w-full px-3 py-2 rounded-l-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 text-sm">
                            </div>
                            <div class="w-1/2 pl-1">
                                <input type="date" id="checkoutDate" name="checkout" value="{{ $checkout }}" class="w-full px-3 py-2 rounded-r-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Room/Level/Keywords/Search -->
                <div class="flex gap-4 items-end">
                    <!-- Left half: Room and Guests, Hotel Level -->
                    <div class="w-1/2 grid grid-cols-2 gap-4">
                        <!-- Room and Guests -->
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Rooms & Guests</label>
                            <div class="relative">
                                <select name="rooms_guests" class="w-full appearance-none px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 bg-white text-sm">
                                    <option value="1,1" {{ $rooms == 1 && $guests == 1 ? 'selected' : '' }}>1 Room, 1 Guest</option>
                                    <option value="1,2" {{ $rooms == 1 && $guests == 2 ? 'selected' : '' }}>1 Room, 2 Guests</option>
                                    <option value="2,2" {{ $rooms == 2 && $guests == 2 ? 'selected' : '' }}>2 Rooms, 2 Guests</option>
                                    <option value="2,4" {{ $rooms == 2 && $guests == 4 ? 'selected' : '' }}>2 Rooms, 4 Guests</option>
                                    <!-- 更多选项 -->
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Hotel Level -->
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Hotel Level</label>
                            <div class="relative">
                                <select name="hotel_class" class="w-full appearance-none px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 bg-white text-sm">
                                    <option value="">Any</option>
                                    <option value="5">5 Star / Luxury</option>
                                    <option value="4">4 Star / Upscale</option>
                                    <option value="3">3 Star / Comfort</option>
                                    <option value="2">Budget</option>
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
                    <div class="w-1/2 flex gap-2 items-end">
                        <!-- Keywords (Optional) -->
                        <div class="flex-grow">
                            <label class="block text-gray-700 text-sm font-medium mb-1">Keywords (Optional)</label>
                            <input type="text" name="keywords" placeholder="e.g., Airport, Station, Hotel Name..." class="w-full px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 text-sm">
                        </div>

                        <!-- Search Button -->
                        <button type="submit" class="flex-shrink-0 w-28 h-10 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg text-sm">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right half: Weather Card -->
        <div class="w-full md:w-1/4 md:aspect-square">
            <div class="h-full rounded-lg overflow-hidden shadow relative">
                <!-- Dynamic Weather Background -->
                <div class="absolute inset-0 bg-gradient-to-b from-yellow-400 to-orange-400"></div>

                <div class="relative p-4 text-white h-full flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center">
                            <h2 class="text-2xl font-bold">{{ $city }}</h2>

                            <!-- Weather Icon -->
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-xs opacity-80">Today</p>
                    </div>

                    <div class="mt-3">
                        <div class="text-5xl font-bold">{{ $weather['temp'] }}</div>
                        <div class="text-lg">{{ $weather['condition'] }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-3">
                        <div>
                            <p class="text-xs opacity-80">Humidity</p>
                            <p class="text-sm font-semibold">{{ $weather['humidity'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs opacity-80">Wind</p>
                            <p class="text-sm font-semibold">{{ $weather['wind'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hotel List -->
    <div class="bg-white rounded-lg shadow p-4 mt-3">
        <h2 class="text-xl font-bold mb-3">Hotels in {{ $city }}</h2>

        @if(count($hotels) > 0)
        <div class="space-y-3">
            @foreach($hotels as $hotel)
            <div class="flex flex-col md:flex-row border rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="w-full md:w-1/3 h-40 md:h-auto">
                    <img src="{{ $hotel['image'] }}" alt="{{ $hotel['name'] }}" class="w-full h-full object-cover">
                </div>
                <div class="w-full md:w-2/3 p-3 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg font-bold text-gray-800">{{ $hotel['name'] }}</h3>
                            <div class="bg-red-100 text-red-800 px-2 py-0.5 rounded text-sm font-semibold">
                                {{ $hotel['rating'] }} Rating
                            </div>
                        </div>
                        <div class="flex items-center text-yellow-500 mt-0.5">
                            @for($i = 0; $i < $hotel['stars']; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.799-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                @endfor
                        </div>
                        <p class="text-gray-600 text-sm mt-1">{{ $hotel['distance'] }}</p>
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach($hotel['tags'] as $tag)
                            <span class="bg-gray-100 text-gray-800 text-xs px-1.5 py-0.5 rounded">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-between items-end mt-3">
                        <div>
                            <span class="text-xl font-bold text-red-600">¥{{ $hotel['price'] }}</span>
                            <span class="text-gray-500 text-sm"> onwards</span>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="openSubscribeModal({{ $hotel['id'] }})" class="bg-white border border-red-500 text-red-500 px-3 py-1 text-sm rounded hover:bg-red-50">
                                Subscribe
                            </button>
                            <a href="{{ route('hotels.show', $hotel['id']) }}" class="bg-red-500 text-white px-3 py-1 text-sm rounded hover:bg-red-600">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="py-8 text-center text-gray-500">
            <p>No hotels found matching your criteria. Please try different search terms.</p>
        </div>
        @endif
    </div>
</div>

<!-- 订阅弹窗 -->
<div id="subscribeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg p-4 w-full max-w-sm mx-4">
        <h3 class="text-lg font-bold mb-3">Subscribe to Price Changes</h3>
        <p class="text-gray-600 text-sm mb-3">Enter your email, and we'll notify you when the price changes.</p>

        <form id="subscribeForm" class="space-y-3">
            <input type="hidden" id="hotelId" name="hotel_id">
            <div>
                <label class="block text-sm font-medium mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full px-3 py-1.5 text-sm border rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Price Below (Optional)</label>
                <input type="number" name="price_threshold" class="w-full px-3 py-1.5 text-sm border rounded-md">
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeSubscribeModal()" class="px-3 py-1.5 text-sm border rounded-md">
                    Cancel
                </button>
                <button type="submit" class="bg-red-500 text-white px-3 py-1.5 text-sm rounded-md hover:bg-red-600">
                    Subscribe
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* 雨天动画效果 */
    .rain {
        position: absolute;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.6) 100%);
        background-size: 20px 100%;
        animation: rain 0.5s linear infinite;
    }

    @keyframes rain {
        0% {
            background-position: 0% 0%;
        }

        100% {
            background-position: 20px 100%;
        }
    }

    /* x-cloak rule */
    [x-cloak] {
        display: none !important;
    }
</style>

<script>
    // Function to calculate and display duration
    function updateDuration() {
        const checkinInput = document.getElementById('checkinDate');
        const checkoutInput = document.getElementById('checkoutDate');
        const durationDisplay = document.getElementById('durationDisplay');

        const checkinDate = new Date(checkinInput.value);
        const checkoutDate = new Date(checkoutInput.value);

        if (!isNaN(checkinDate) && !isNaN(checkoutDate) && checkoutDate > checkinDate) {
            const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            durationDisplay.textContent = daysDiff + (daysDiff === 1 ? ' night' : ' nights');
        } else {
            durationDisplay.textContent = '';
        }
    }

    // Add event listeners to date inputs
    document.getElementById('checkinDate').addEventListener('change', updateDuration);
    document.getElementById('checkoutDate').addEventListener('change', updateDuration);

    // Initial calculation on page load
    document.addEventListener('DOMContentLoaded', updateDuration);

    function openSubscribeModal(hotelId) {
        document.getElementById('hotelId').value = hotelId;
        document.getElementById('subscribeModal').classList.remove('hidden');
    }

    function closeSubscribeModal() {
        document.getElementById('subscribeModal').classList.add('hidden');
    }

    document.getElementById('subscribeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const hotelId = document.getElementById('hotelId').value;
        const formData = new FormData(this);

        fetch(`/hotels/${hotelId}/subscribe`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: formData.get('email'),
                    price_threshold: formData.get('price_threshold')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeSubscribeModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
</script>
@endsection