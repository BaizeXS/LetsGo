<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LetsGO - Travel Notes Sharing Platform</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- External CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">

  <!-- External JavaScript -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
  <script charset="utf-8" src="https://map.qq.com/api/js?v=2.exp&key={{ env('TENCENT_MAP_KEY', '') }}"></script>

  @yield('styles')
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">
  <!-- Top navigation bar -->
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3">
      <div class="flex justify-between items-center">
        <!-- Search bar -->
        <div class="relative w-full max-w-md" id="search-container">
          <form action="{{ route('search') }}" method="GET" id="search-form">
            <div class="flex items-center border border-gray-300 rounded-full overflow-hidden">
              <button type="submit" class="px-3">
                <i class="fas fa-search text-gray-400"></i>
              </button>
              <input type="text" name="query" placeholder="Search destinations or keywords" class="w-full py-2 px-1 outline-none" value="{{ request('query') }}" autocomplete="off">
            </div>
          </form>
        </div>

        <!-- Right side icons -->
        <div class="flex space-x-4 items-center">
          <button id="open-map-btn" class="text-yellow-500"><i class="fas fa-compass text-xl"></i></button>

          @if(isset($isAuthenticated) && $isAuthenticated)
          <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center focus:outline-none">
              <div class="flex items-center gap-2">
                <div class="text-sm text-gray-800">{{ $authUser['email'] }}</div>
                <i class="fas fa-user-circle text-xl"></i>
              </div>
            </button>

            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
              <a href="{{ url('/favorites') }}" class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 border-b border-gray-100">
                My
              </a>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left px-4 py-3 text-sm text-gray-800 hover:bg-gray-100">
                  Log Out
                </button>
              </form>
            </div>
          </div>
          @else
          <a href="{{ route('login') }}" class="text-gray-700 text-sm hover:text-red-500">Log In</a>
          <a href="{{ route('register') }}" class="bg-red-500 text-white text-sm px-4 py-2 rounded-full hover:bg-red-600">Sign Up</a>
          @endif
        </div>
      </div>
    </div>
  </header>

  <!-- Map Modal -->
  <div id="map-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl h-3/4 flex flex-col">
      <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-xl font-semibold text-gray-800">Explore Destinations</h2>
        <button id="close-map-btn" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>
      <div class="p-4 flex-grow flex flex-col md:flex-row">
        <div class="flex flex-col w-full md:w-3/5 h-full">
          <div class="flex mb-4">
            <div class="relative w-full">
              <input id="map-search" type="text" placeholder="Search for a destination" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
              <button id="search-map-btn" class="absolute right-2 top-2 text-gray-500">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
          <div id="tencent-map" class="w-full flex-grow rounded-lg border border-gray-300"></div>
        </div>

        <!-- Posts display panel -->
        <div id="map-posts-panel" class="w-full md:w-2/5 h-full md:ml-4 mt-4 md:mt-0 overflow-hidden flex flex-col">
          <div class="flex justify-between items-center mb-4">
            <h3 id="map-posts-title" class="text-lg font-semibold text-gray-800">Travel Posts</h3>
            <span id="map-posts-count" class="text-sm text-gray-500">0 posts found</span>
          </div>
          <div id="map-posts-container" class="flex-grow overflow-y-auto border border-gray-200 rounded-lg p-2">
            <div id="map-posts-placeholder" class="flex flex-col items-center justify-center h-full text-gray-500">
              <i class="fas fa-map-marker-alt text-4xl mb-2"></i>
              <p>Click on a marker and select "Show travel posts" to view related travel notes</p>
            </div>
            <div id="map-posts-list" class="hidden space-y-3"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <main class="flex-grow container mx-auto px-4 py-6">
    @yield('content')
  </main>

  <!-- @if(config('app.env') === 'local') -->
  <!-- Debug information area - only shown in development environment -->
  <!-- <div class="bg-gray-100 border-t border-gray-200 py-2 text-xs text-gray-500 text-center"> -->
  <!-- Authentication status: @if(isset($isAuthenticated) && $isAuthenticated) Logged in ({{ $authUser['email'] }}) @else Not logged in @endif | -->
  <!-- Current route: {{ Route::currentRouteName() }} -->
  <!-- </div> -->
  <!-- @endif -->

  <!-- Bottom navigation bar -->
  <footer class="bg-white border-t border-gray-200 sticky bottom-0 z-50">
    <div class="container mx-auto">
      <div class="flex justify-around items-center py-3">
        <a href="{{ route('home') }}" class="flex flex-col items-center {{ Route::currentRouteName() == 'home' ? 'text-red-500' : 'text-gray-500' }}">
          <i class="fas fa-home text-xl"></i>
          <span class="text-xs mt-1">Home</span>
        </a>

        <a href="{{ route('chat.index') }}" class="flex flex-col items-center {{ Request::is('chat*') ? 'text-red-500' : 'text-gray-500' }}">
          <i class="fas fa-robot text-xl"></i>
          <span class="text-xs mt-1">AI Assistant</span>
        </a>

        @if(isset($isAuthenticated) && $isAuthenticated)
        <a href="{{ route('posts.create') }}" class="flex flex-col items-center text-gray-500 relative">
          <div class="bg-red-500 rounded-full p-3 -mt-5 border-4 border-white">
            <i class="fas fa-plus text-white text-xl"></i>
          </div>
          <span class="text-xs mt-1">Publish</span>
        </a>
        @else
        <a href="{{ route('login') }}" class="flex flex-col items-center text-gray-500 relative">
          <div class="bg-red-500 rounded-full p-3 -mt-5 border-4 border-white">
            <i class="fas fa-plus text-white text-xl"></i>
          </div>
          <span class="text-xs mt-1">Publish</span>
        </a>
        @endif

        <a href="{{ route('hotels.subscription') }}" class="flex flex-col items-center {{ Route::currentRouteName() == 'hotels.subscription' ? 'text-red-500' : 'text-gray-500' }}">
          <i class="fas fa-hotel text-xl"></i>
          <span class="text-xs mt-1">Hotels</span>
        </a>

        <a href="{{ $isAuthenticated ? url('/favorites') : route('login') }}" class="flex flex-col items-center {{ Route::currentRouteName() == 'user.favorites' ? 'text-red-500' : 'text-gray-500' }}">
          <i class="fas fa-user text-xl"></i>
          <span class="text-xs mt-1">{{ $isAuthenticated ? 'My' : 'Login' }}</span>
        </a>
      </div>
    </div>
  </footer>

  <!-- Load app.js -->
  <script src="{{ asset('js/app.js') }}"></script>

  <!-- Map functionality -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // DOM elements
      const elements = {
        mapModal: document.getElementById('map-modal'),
        openMapBtn: document.getElementById('open-map-btn'),
        closeMapBtn: document.getElementById('close-map-btn'),
        mapElement: document.getElementById('tencent-map'),
        searchInput: document.getElementById('map-search'),
        searchBtn: document.getElementById('search-map-btn'),
        mapPostsTitle: document.getElementById('map-posts-title'),
        mapPostsCount: document.getElementById('map-posts-count'),
        mapPostsPlaceholder: document.getElementById('map-posts-placeholder'),
        mapPostsList: document.getElementById('map-posts-list')
      };

      // Map variables
      let map;
      let markers = [];
      let searchService;
      let geocoder;

      // Popular destinations data
      const popularDestinations = [{
          name: "Beijing Forbidden City",
          lat: 39.9163,
          lng: 116.3972
        },
        {
          name: "Shanghai Bund",
          lat: 31.2304,
          lng: 121.4912
        },
        {
          name: "Xi'an Terracotta Army",
          lat: 34.3841,
          lng: 109.2785
        },
        {
          name: "Guangzhou Tower",
          lat: 23.1066,
          lng: 113.3214
        },
        {
          name: "Shenzhen Window of the World",
          lat: 22.5347,
          lng: 113.9740
        },
        {
          name: "Hangzhou West Lake",
          lat: 30.2590,
          lng: 120.1388
        }
      ];

      // Initialize map
      function initMap() {
        // Default center position - Beijing
        const defaultCenter = new qq.maps.LatLng(39.9042, 116.4074);

        // Create map instance
        map = new qq.maps.Map(elements.mapElement, {
          center: defaultCenter,
          zoom: 11,
          mapTypeControl: true,
          panControl: true,
          zoomControl: true,
          scaleControl: true
        });

        // Initialize services
        initServices();

        // Add default markers
        addDefaultMarkers();
      }

      // Initialize map services
      function initServices() {
        // Create place search service
        searchService = new qq.maps.SearchService({
          complete: handleSearchComplete
        });

        // Create geocoding service
        geocoder = new qq.maps.Geocoder();
      }

      // Handle search completion
      function handleSearchComplete(results) {
        if (results && results.detail.pois.length > 0) {
          const place = results.detail.pois[0];
          const location = {
            name: place.name,
            lat: place.latLng.lat,
            lng: place.latLng.lng
          };

          // Clear previous markers
          clearMarkers();

          // Move map to search result location
          map.setCenter(new qq.maps.LatLng(location.lat, location.lng));
          map.setZoom(14);

          // Add new marker
          addMarker(location);
        }
      }

      // Add default markers
      function addDefaultMarkers() {
        popularDestinations.forEach(destination => {
          addMarker(destination);
        });
      }

      // Add marker to map
      function addMarker(location) {
        const position = new qq.maps.LatLng(location.lat, location.lng);

        // Create marker
        const marker = new qq.maps.Marker({
          position: position,
          map: map,
          title: location.name
        });

        // Create info window
        const info = new qq.maps.InfoWindow({
          map: map
        });

        // Set info window content
        const infoContent = createInfoContent(location);

        // Add marker click event
        qq.maps.event.addListener(marker, 'click', function() {
          info.open();
          info.setContent(infoContent);
          info.setPosition(position);

          // Attach button event listeners
          setTimeout(() => {
            const loadPostsBtn = document.getElementById(`load-posts-${location.lat}-${location.lng}`);
            if (loadPostsBtn) {
              loadPostsBtn.addEventListener('click', () => {
                loadPostsForLocation(location.name);
              });
            }
          }, 100);
        });

        markers.push(marker);
        return marker;
      }

      // Create info window content
      function createInfoContent(location) {
        const infoContent = document.createElement('div');
        infoContent.innerHTML = `
                    <div class="p-2" style="width: 200px;">
                        <h3 style="font-weight: bold; margin-bottom: 5px;">${location.name}</h3>
                        <p style="font-size: 12px; margin-bottom: 5px;">Explore travel notes about this destination</p>
                        <a href="/search?query=${encodeURIComponent(location.name)}" style="color: #3b82f6; font-size: 12px; display: block; margin-top: 5px;">View related posts</a>
                        <button id="load-posts-${location.lat}-${location.lng}" style="color: #eab308; font-size: 12px; display: block; margin-top: 5px; cursor: pointer; background: none; border: none; padding: 0; text-align: left;">Show travel posts</button>
                    </div>
                `;
        return infoContent;
      }

      // Clear all markers
      function clearMarkers() {
        markers.forEach(marker => {
          marker.setMap(null);
        });
        markers = [];
      }

      // Search place
      function searchPlace(query) {
        if (query.trim()) {
          searchService.search(query);
        }
      }

      // Load travel notes for specified location
      function loadPostsForLocation(locationName) {
        // Update title
        elements.mapPostsTitle.textContent = `Travel Posts for ${locationName}`;

        // Show loading status
        showLoadingState(locationName);

        // Get travel notes for this location
        fetch(`/api/posts/location?location=${encodeURIComponent(locationName)}`)
          .then(response => response.json())
          .then(posts => {
            // Update note count
            elements.mapPostsCount.textContent = `${posts.length} posts found`;

            if (posts.length === 0) {
              showEmptyState(locationName);
            } else {
              showPosts(posts);
            }
          })
          .catch(error => {
            console.error('Error fetching posts:', error);
            showErrorState();
          });
      }

      // Show loading state
      function showLoadingState(locationName) {
        elements.mapPostsPlaceholder.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full">
                        <i class="fas fa-spinner fa-spin text-yellow-500 text-4xl mb-2"></i>
                        <p>Loading travel posts for ${locationName}...</p>
                    </div>
                `;
        elements.mapPostsPlaceholder.classList.remove('hidden');
        elements.mapPostsList.classList.add('hidden');
      }

      // Show empty state
      function showEmptyState(locationName) {
        elements.mapPostsPlaceholder.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-gray-500">
                        <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                        <p>No travel posts found for ${locationName}</p>
                        <a href="/search?query=${encodeURIComponent(locationName)}" class="text-yellow-500 mt-2">Search all posts</a>
                    </div>
                `;
        elements.mapPostsPlaceholder.classList.remove('hidden');
        elements.mapPostsList.classList.add('hidden');
      }

      // Show error state
      function showErrorState() {
        elements.mapPostsPlaceholder.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-gray-500">
                        <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-2"></i>
                        <p>Error loading posts. Please try again later.</p>
                    </div>
                `;
        elements.mapPostsPlaceholder.classList.remove('hidden');
        elements.mapPostsList.classList.add('hidden');
      }

      // Show posts
      function showPosts(posts) {
        elements.mapPostsList.innerHTML = posts.map(post => `
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <a href="/posts/${post.id}" class="block">
                            <div class="relative pb-48">
                                <img src="${post.cover_image}" class="absolute inset-0 h-full w-full object-cover" alt="${post.title}">
                            </div>
                            <div class="p-3">
                                <h4 class="font-medium text-gray-900 mb-1 truncate">${post.title}</h4>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span><i class="far fa-eye mr-1"></i>${post.views}</span>
                                    <span><i class="far fa-heart mr-1"></i>${post.likes}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                `).join('');

        elements.mapPostsPlaceholder.classList.add('hidden');
        elements.mapPostsList.classList.remove('hidden');
      }

      // Event listeners
      function setupEventListeners() {
        // Open map modal
        elements.openMapBtn.addEventListener('click', function() {
          elements.mapModal.classList.remove('hidden');
          // Initialize map if needed
          if (!map) {
            initMap();
          }
        });

        // Close map modal
        elements.closeMapBtn.addEventListener('click', function() {
          elements.mapModal.classList.add('hidden');
        });

        // Click outside modal area to close
        elements.mapModal.addEventListener('click', function(e) {
          if (e.target === elements.mapModal) {
            elements.mapModal.classList.add('hidden');
          }
        });

        // Handle search button click
        elements.searchBtn.addEventListener('click', function() {
          searchPlace(elements.searchInput.value);
        });

        // Handle search box enter key
        elements.searchInput.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            searchPlace(elements.searchInput.value);
          }
        });
      }

      // Initialize map functionality
      setupEventListeners();
    });
  </script>

  @yield('scripts')
</body>

</html>