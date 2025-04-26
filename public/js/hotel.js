// Calculate and display the number of nights between check-in and check-out
function updateDuration() {
    const checkinInput = document.getElementById('checkinDate');
    const checkoutInput = document.getElementById('checkoutDate');
    const durationDisplay = document.getElementById('durationDisplay');

    if (!checkinInput || !checkoutInput || !durationDisplay) {
        if (durationDisplay) durationDisplay.textContent = ''; // Clear if inputs missing
        return;
    }

    const checkinDate = new Date(checkinInput.value + 'T00:00:00');
    const checkoutDate = new Date(checkoutInput.value + 'T00:00:00');

    // Ensure dates are valid and checkout is after checkin
    if (!isNaN(checkinDate) && !isNaN(checkoutDate) && checkoutDate > checkinDate) {
        const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
        const daysDiff = Math.round(timeDiff / (1000 * 3600 * 24)); // Calculate nights
        durationDisplay.textContent = daysDiff + (daysDiff === 1 ? ' night' : ' nights');
    } else {
        durationDisplay.textContent = ''; // Clear if dates invalid
    }
}

// Set the minimum selectable checkout date to be the day after the check-in date
function setMinCheckoutDate() {
    const checkinInput = document.getElementById('checkinDate');
    const checkoutInput = document.getElementById('checkoutDate');

    if (!checkinInput || !checkoutInput) return;

    // Also set min check-in date to today
    const todayStr = new Date().toISOString().split('T')[0];
     if (!checkinInput.min || checkinInput.min < todayStr) {
        checkinInput.min = todayStr;
     }
     if(checkinInput.value < todayStr){ // Correct value if it's in the past
        checkinInput.value = todayStr;
     }


    if (checkinInput.value) {
        const checkinDate = new Date(checkinInput.value + 'T00:00:00');
        if (!isNaN(checkinDate)) {
            const nextDay = new Date(checkinDate);
            nextDay.setDate(checkinDate.getDate() + 1); // Day after check-in
            const minCheckoutValue = nextDay.toISOString().split('T')[0];
            checkoutInput.min = minCheckoutValue; // Set min attribute

            // If checkout date is invalid or earlier than check-in, adjust it
            const currentCheckoutDate = new Date(checkoutInput.value + 'T00:00:00');
            if (isNaN(currentCheckoutDate) || currentCheckoutDate <= checkinDate || !checkoutInput.value) {
                checkoutInput.value = minCheckoutValue;
                // Trigger change event to update duration display if value changed
                checkoutInput.dispatchEvent(new Event('change'));
            }
        } else {
             checkoutInput.min = ''; // Clear min if check-in is invalid
        }
    } else {
        checkoutInput.min = ''; // Clear min if check-in is empty
    }
}

// Add event listeners to update duration and minimum checkout on date changes
const checkinDateElem = document.getElementById('checkinDate');
const checkoutDateElem = document.getElementById('checkoutDate');

if (checkinDateElem) {
    checkinDateElem.addEventListener('change', () => {
        setMinCheckoutDate();
        updateDuration();
    });
}
if (checkoutDateElem) {
    checkoutDateElem.addEventListener('change', updateDuration);
}

// Initial setup for dates on page load
document.addEventListener('DOMContentLoaded', () => {
    if (checkinDateElem && checkoutDateElem) {
         setMinCheckoutDate();
         updateDuration();
    }
});

// --- Subscribe Modal Logic ---

function openSubscribeModal(hotelId) {
    const hotelIdInput = document.getElementById('hotelId');
    const subscribeModal = document.getElementById('subscribeModal');
    if (hotelIdInput && subscribeModal) {
        hotelIdInput.value = hotelId; // Set the hotel ID in the hidden form field
        subscribeModal.classList.remove('hidden');
        subscribeModal.classList.add('flex'); // Show the modal
    }
}

function closeSubscribeModal() {
    const subscribeModal = document.getElementById('subscribeModal');
    if (subscribeModal) {
        subscribeModal.classList.add('hidden');
        subscribeModal.classList.remove('flex'); // Hide the modal

        // Reset the form inside the modal
        const subscribeForm = document.getElementById('subscribeForm');
        if (subscribeForm) {
            subscribeForm.reset();
            // Hide any previous validation errors
            subscribeForm.querySelectorAll('.text-red-500').forEach(el => {
                 el.classList.add('hidden');
                 el.textContent = '';
            });
            // Re-enable submit button and hide spinner
            const submitButton = subscribeForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.querySelector('.button-text').classList.remove('hidden');
                submitButton.querySelector('svg.animate-spin').classList.add('hidden');
            }
        }
    }
}

// Handle the submission of the subscription form
const subscribeForm = document.getElementById('subscribeForm');
if (subscribeForm) {
    subscribeForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        const hotelId = document.getElementById('hotelId').value;
        const formData = new FormData(this);
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const submitButton = this.querySelector('button[type="submit"]');
        const buttonText = submitButton.querySelector('.button-text');
        const spinner = submitButton.querySelector('svg.animate-spin');

        // Clear previous validation errors
        this.querySelectorAll('.text-red-500').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });

        if (!csrfTokenMeta) {
             console.error('CSRF token meta tag not found');
             alert('Error: CSRF token missing. Cannot submit form.');
             return;
        }

        // Disable button and show spinner during submission
        submitButton.disabled = true;
        buttonText.classList.add('hidden');
        spinner.classList.remove('hidden');

        // Send subscription request to the backend
        fetch(`/hotels/${hotelId}/subscribe`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfTokenMeta.getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json' // Sending JSON
                },
                body: JSON.stringify({ // Construct JSON payload
                    // _token: csrfTokenMeta.getAttribute('content'), // Not needed in JSON body usually
                    email: formData.get('email'),
                    price_threshold: formData.get('price_threshold') || null // Send null if empty
                })
            })
            .then(async response => { // Handle response, check for errors
                 const data = await response.json(); // Always try to parse JSON
                 if (!response.ok) {
                    // Throw an error object for easier handling in catch block
                    throw { status: response.status, data: data };
                 }
                 return data; // Return parsed JSON data on success
            })
            .then(data => { // Process successful response
                if (data.success) {
                    alert(data.message || 'Subscription successful!');
                    closeSubscribeModal();
                } else {
                    // Handle backend indicating failure even with 2xx status
                    alert(data.message || 'Subscription failed. Please try again.');
                }
            })
            .catch(error => { // Handle fetch errors or non-ok responses
                let errorMessage = 'An error occurred during subscription.';

                // Handle validation errors (422)
                if (error && error.status === 422 && error.data && error.data.errors) {
                    errorMessage = 'Please correct the following errors:';
                    // Display errors next to the relevant form fields
                    Object.keys(error.data.errors).forEach(field => {
                        const inputElement = subscribeForm.querySelector(`[name="${field}"]`);
                        const errorElement = inputElement ? inputElement.closest('div').querySelector('.text-red-500') : null;
                        const messages = error.data.errors[field];
                        if (errorElement) {
                            errorElement.textContent = messages.join(' ');
                            errorElement.classList.remove('hidden');
                        }
                        errorMessage += `\n- ${messages.join(', ')}`; // Also add to alert for backup
                    });
                     console.warn('Validation Errors:', error.data.errors);
                } else if (error && error.data && error.data.message) {
                     // Use backend-provided error message if available
                     alert(error.data.message);
                } else {
                     // Generic error alert
                     alert(errorMessage);
                }
                 console.error('Subscription Error:', error);
            })
            .finally(() => { // Always re-enable button and hide spinner
                 if (submitButton) {
                     submitButton.disabled = false;
                     buttonText.classList.remove('hidden');
                     spinner.classList.add('hidden');
                 }
            });
    });
}

// Close subscribe modal if user clicks on the backdrop
const subscribeModal = document.getElementById('subscribeModal');
if (subscribeModal) {
    subscribeModal.addEventListener('click', function(event) {
        // Check if the click was directly on the modal background
        if (event.target === subscribeModal) {
            closeSubscribeModal();
        }
    });
}

// --- Details & Booking Modal Logic (Using Mock Data) ---

// Mock hotel data to avoid backend calls for demo purposes
const MOCK_HOTELS = {
    1: { id: 1, name: 'The Peninsula Hong Kong', rating: 4.9, stars: 5, distance: '1 km from Tsim Sha Tsui', price: 3500, image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', tags: ['Luxury', 'Harbour View', 'Spa'] },
    2: { id: 2, name: 'Four Seasons Hotel Hong Kong', rating: 4.8, stars: 5, distance: '0.5 km from IFC', price: 3200, image: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', tags: ['Michelin Stars', 'Pool', 'Central'] },
    3: { id: 3, name: 'Cordis, Hong Kong', rating: 4.5, stars: 5, distance: '0.2 km from Langham Place', price: 1800, image: 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', tags: ['Shopping', 'Mong Kok', 'Rooftop Pool'] },
    4: { id: 4, name: 'Hotel ICON', rating: 4.6, stars: 4, distance: '0.8 km from Tsim Sha Tsui East', price: 1500, image: 'https://images.unsplash.com/photo-1568084680786-a84f91d1153c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', tags: ['Design', 'University', 'Pool View'] },
    5: { id: 5, name: 'ibis Hong Kong Central & Sheung Wan', rating: 4.0, stars: 3, distance: '0.5 km from Sheung Wan MTR', price: 700, image: 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', tags: ['Budget', 'Value', 'Harbour View (Partial)'] },
    // Add Beijing hotels or others as needed
    101: { id: 101, name: 'Grand International Hotel Beijing', rating: 4.5, stars: 5, distance: '2.5 km from Tiananmen Square', price: 880, image: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', tags: ['Free Breakfast', 'Free WiFi', 'Fitness Center'] },
    102: { id: 102, name: 'The Ritz-Carlton, Beijing', rating: 4.8, stars: 5, distance: '3 km from The Palace Museum', price: 1680, image: 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', tags: ['Fine Dining', 'Swimming Pool', 'Spa Center'] },
    103: { id: 103, name: 'Park Hyatt Beijing', rating: 4.7, stars: 5, distance: '1 km from CCTV Headquarters', price: 1500, image: 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', tags: ['CBD', 'Modern', 'Sky Lobby'] },
    104: { id: 104, name: 'Hotel Éclat Beijing', rating: 4.6, stars: 4, distance: '2 km from Sanlitun', price: 1200, image: 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.0.3&q=80&w=400', tags: ['Art', 'Boutique', 'Unique Design'] }
};

// Opens the details modal and populates it using MOCK_HOTELS data
function openDetailsModal(hotelId) {
    const modal = document.getElementById('detailsBookingModal');
    // Get references to modal elements
    const hotelNameElem = document.getElementById('modalHotelName');
    const hotelImageElem = document.getElementById('modalHotelImage');
    const hotelStarsElem = document.getElementById('modalHotelStars');
    const hotelRatingElem = document.getElementById('modalHotelRating');
    const hotelDistanceElem = document.getElementById('modalHotelDistance');
    const hotelPriceElem = document.getElementById('modalHotelPrice');
    const hotelTagsElem = document.getElementById('modalHotelTags');
    // Get references to form elements
    const bookingForm = document.getElementById('bookingForm');
    const bookingHotelIdInput = document.getElementById('bookingHotelId');
    const bookingCheckin = document.getElementById('bookingCheckin');
    const bookingCheckout = document.getElementById('bookingCheckout');
    const bookingName = document.getElementById('bookingName'); // Assume exists in form
    const bookingEmail = document.getElementById('bookingEmail'); // Assume exists in form
    const bookingRoomsInput = document.getElementById('bookingRooms'); // Hidden input
    const bookingGuestsInput = document.getElementById('bookingGuests'); // Hidden input
    const bookingInfoDisplay = document.getElementById('bookingInfoDisplay'); // Display element
    const bookingDurationDisplay = document.getElementById('bookingDurationDisplay'); // Display element

    if (!modal || !hotelId) {
        console.error("Details modal element or hotelId is missing.");
        return;
    }

    // --- Reset modal to a loading state ---
    hotelNameElem.textContent = 'Loading...';
    hotelImageElem.src = 'https://via.placeholder.com/400x250?text=Loading...'; // Placeholder image
    hotelImageElem.alt = 'Loading hotel image...';
    hotelStarsElem.innerHTML = '<span class="text-sm text-gray-500">Loading...</span>';
    hotelRatingElem.textContent = 'N/A';
    hotelDistanceElem.textContent = 'N/A';
    hotelPriceElem.textContent = 'N/A';
    hotelTagsElem.innerHTML = '';
    bookingHotelIdInput.value = hotelId; // Set hotel ID in the form early
    bookingInfoDisplay.textContent = 'Loading...'; // Reset rooms/guests display
    bookingDurationDisplay.textContent = ''; // Reset duration display

    // --- Get current search context from the main form to pre-fill booking form ---
    const searchCheckin = document.getElementById('checkinDate')?.value || '';
    const searchCheckout = document.getElementById('checkoutDate')?.value || '';
    // Read values from the hidden inputs controlled by the Alpine roomGuestSelector
    const searchRooms = document.querySelector('input[name="rooms"]')?.value || '1';
    const searchGuests = document.querySelector('input[name="guests"]')?.value || '1';

    // Pre-fill booking form dates (use today/tomorrow as fallback)
    bookingCheckin.value = searchCheckin || new Date().toISOString().split('T')[0];
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    bookingCheckout.value = searchCheckout || tomorrow.toISOString().split('T')[0];
    setMinBookingCheckoutDate(); // Set min checkout based on check-in
    updateBookingDuration(); // Calculate initial duration for booking form

    // Store rooms/guests in hidden fields and update the display text
    bookingRoomsInput.value = searchRooms;
    bookingGuestsInput.value = searchGuests;
    bookingInfoDisplay.textContent = `${searchRooms} Room${searchRooms > 1 ? 's' : ''}, ${searchGuests} Guest${searchGuests > 1 ? 's' : ''}`;

    // Show the modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // Simulate fetching data (using MOCK_HOTELS)
    setTimeout(() => {
        try {
            const hotel = MOCK_HOTELS[hotelId]; // Get data from our mock object

            if (!hotel) {
                throw new Error(`Mock data not found for Hotel ID: ${hotelId}`);
            }

            // --- Populate Modal with mock data ---
            hotelNameElem.textContent = hotel.name;
            hotelImageElem.src = hotel.image; // Use image URL from mock data
            hotelImageElem.alt = hotel.name;

            // Populate stars
            hotelStarsElem.innerHTML = ''; // Clear loading/previous stars
            if (hotel.stars) {
                for (let i = 0; i < Math.floor(hotel.stars); i++) {
                    // Simple star SVG
                    const starSvg = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.799-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>`;
                    hotelStarsElem.innerHTML += starSvg;
                }
            }

            // Populate other details
            hotelRatingElem.textContent = hotel.rating ? `${Number(hotel.rating).toFixed(1)} Rating` : 'N/A';
            hotelDistanceElem.textContent = hotel.distance || 'N/A';
            hotelPriceElem.textContent = hotel.price ? `¥${Number(hotel.price).toFixed(2)}` : 'N/A'; // Assuming Yen currency symbol

            // Populate tags
            hotelTagsElem.innerHTML = ''; // Clear loading/previous tags
            if (hotel.tags && Array.isArray(hotel.tags)) {
                hotel.tags.forEach(tag => {
                    const tagSpan = `<span class="bg-gray-100 text-gray-800 text-xs px-1.5 py-0.5 rounded mr-1">${tag}</span>`;
                    hotelTagsElem.innerHTML += tagSpan;
                });
            }
        } catch (error) {
            // Handle errors (e.g., hotelId not found in MOCK_HOTELS)
            console.error('Error processing hotel details from mock data:', error);
            hotelNameElem.textContent = 'Error Loading Details';
            hotelImageElem.src = 'https://via.placeholder.com/400x250?text=Error'; // Error placeholder
            hotelImageElem.alt = 'Error loading image';
            hotelStarsElem.innerHTML = `<span class="text-sm text-red-500">${error.message || 'Could not load details.'}</span>`;
        }
    }, 300); // Short delay to simulate network request
}

// Closes the details/booking modal
function closeDetailsModal() {
    const modal = document.getElementById('detailsBookingModal');
    const bookingForm = document.getElementById('bookingForm');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    if (bookingForm) {
        bookingForm.reset(); // Reset form fields when closing
    }
}

// Updates the duration display (e.g., "(2 nights)") in the booking form
function updateBookingDuration() {
    const checkinInput = document.getElementById('bookingCheckin');
    const checkoutInput = document.getElementById('bookingCheckout');
    const durationDisplay = document.getElementById('bookingDurationDisplay');

    if (!checkinInput || !checkoutInput || !durationDisplay) {
        if (durationDisplay) durationDisplay.textContent = '';
        return;
    }

    const checkinDate = new Date(checkinInput.value + 'T00:00:00');
    const checkoutDate = new Date(checkoutInput.value + 'T00:00:00');

    // Calculate and display duration if dates are valid
    if (!isNaN(checkinDate) && !isNaN(checkoutDate) && checkoutDate > checkinDate) {
        const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
        const daysDiff = Math.round(timeDiff / (1000 * 3600 * 24));
        durationDisplay.textContent = `(${daysDiff} night${daysDiff === 1 ? '' : 's'})`; // Add parentheses for clarity
    } else {
        durationDisplay.textContent = '';
    }
}

// Sets the minimum checkout date in the booking form based on the check-in date
function setMinBookingCheckoutDate() {
    const bookingCheckinInput = document.getElementById('bookingCheckin');
    const bookingCheckoutInput = document.getElementById('bookingCheckout');
    if (!bookingCheckinInput || !bookingCheckoutInput) return;

    // Ensure check-in min date is today
    const todayStr = new Date().toISOString().split('T')[0];
    bookingCheckinInput.min = todayStr;
    if (bookingCheckinInput.value < todayStr) { // Adjust value if needed
        bookingCheckinInput.value = todayStr;
    }

    // Set checkout min date based on check-in
    if (bookingCheckinInput.value) {
        const checkinDate = new Date(bookingCheckinInput.value + 'T00:00:00');
        if (!isNaN(checkinDate)) {
            const nextDay = new Date(checkinDate);
            nextDay.setDate(checkinDate.getDate() + 1);
            const minCheckoutValue = nextDay.toISOString().split('T')[0];
            bookingCheckoutInput.min = minCheckoutValue;

            // Adjust checkout value if it's invalid or not set
            const currentCheckoutDate = new Date(bookingCheckoutInput.value + 'T00:00:00');
            if (isNaN(currentCheckoutDate) || currentCheckoutDate <= checkinDate || !bookingCheckoutInput.value) {
                bookingCheckoutInput.value = minCheckoutValue;
                // Trigger change explicitly if value was adjusted
                bookingCheckoutInput.dispatchEvent(new Event('change'));
            }
        }
    }
}

// Add event listeners for booking date changes (run after DOM is loaded)
document.addEventListener('DOMContentLoaded', function() {
    const bookingCheckinElem = document.getElementById('bookingCheckin');
    const bookingCheckoutElem = document.getElementById('bookingCheckout');

    if (bookingCheckinElem) {
        bookingCheckinElem.addEventListener('change', () => {
            setMinBookingCheckoutDate(); // Update min checkout when check-in changes
            updateBookingDuration(); // Update duration display
        });
    }

    if (bookingCheckoutElem) {
        bookingCheckoutElem.addEventListener('change', updateBookingDuration); // Update duration when check-out changes
    }

    // Handle the simulated booking form submission
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent actual submission

            // Get values from the form
            const hotelId = document.getElementById('bookingHotelId').value;
            const hotel = MOCK_HOTELS[hotelId]; // Get hotel details from mock data
            const hotelName = hotel ? hotel.name : 'Unknown Hotel'; // Fallback name

            const checkin = document.getElementById('bookingCheckin').value;
            const checkout = document.getElementById('bookingCheckout').value;
            const name = document.getElementById('bookingName').value;
            const email = document.getElementById('bookingEmail').value;
            // Read rooms/guests from hidden inputs
            const rooms = document.getElementById('bookingRooms').value || '1';
            const guests = document.getElementById('bookingGuests').value || '1';

            // Log the simulated booking details
            console.log('Simulated Booking Request Details:', {
                hotelName: hotelName,
                hotelId: hotelId,
                checkin: checkin,
                checkout: checkout,
                name: name,
                email: email,
                rooms: rooms,
                guests: guests
            });

            // Show a confirmation alert (demo only)
            alert(`Booking request sent! We will contact you shortly (Demo).
Hotel: ${hotelName}
Check-in: ${checkin}
Check-out: ${checkout}
Name: ${name}
Rooms: ${rooms}, Guests: ${guests}`);

            closeDetailsModal(); // Close the modal after submission
        });
    }

    // Close details modal if user clicks on the backdrop
    const detailsBookingModal = document.getElementById('detailsBookingModal');
    if (detailsBookingModal) {
        detailsBookingModal.addEventListener('click', function(event) {
            if (event.target === detailsBookingModal) {
                closeDetailsModal();
            }
        });
    }
});


// --- Alpine.js Components ---
document.addEventListener('alpine:init', () => {
    // City Dropdown Component for search form
    Alpine.data('cityDropdown', (initialCity, popularDomestic, popularInternational) => {
        const MAX_HISTORY = 5; // Max number of recent searches to store
        return {
            cityDropdownOpen: false,
            currentCity: initialCity || '', // Input field model
            recentSearches: [], // Array for recent searches
            popularDomesticCities: popularDomestic || [],
            popularInternationalCities: popularInternational || [],

            // Load search history from localStorage on initialization
            init() { this.loadHistory(); },

            // Load history from localStorage
            loadHistory() {
                const history = localStorage.getItem('hotelSearchHistory');
                if (history) {
                    try {
                        this.recentSearches = JSON.parse(history);
                    } catch (e) {
                        console.error("Error parsing search history from localStorage", e);
                        this.recentSearches = [];
                        localStorage.removeItem('hotelSearchHistory'); // Clear invalid data
                    }
                } else {
                    this.recentSearches = [];
                }
            },

            // Save current history to localStorage
            saveHistory() {
                try {
                    localStorage.setItem('hotelSearchHistory', JSON.stringify(this.recentSearches));
                } catch (e) {
                    console.error("Error saving search history to localStorage", e);
                }
            },

            // Add a city to the recent searches list
            addSearch(city) {
                if (!city || typeof city !== 'string' || city.trim() === '') return;
                const trimmedCity = city.trim();
                // Remove existing entry if present, then add to the beginning
                this.recentSearches = this.recentSearches.filter(item => item !== trimmedCity);
                this.recentSearches.unshift(trimmedCity);
                // Limit history size
                if (this.recentSearches.length > MAX_HISTORY) {
                    this.recentSearches = this.recentSearches.slice(0, MAX_HISTORY);
                }
                this.saveHistory(); // Persist changes
            },

            // Clear search history
            clearHistory() {
                this.recentSearches = [];
                localStorage.removeItem('hotelSearchHistory');
            },

            // Select a city from dropdown/history
            selectCity(city) {
                if (city && city.trim() !== '') {
                    const trimmedCity = city.trim();
                    this.addSearch(trimmedCity); // Add to history
                    this.currentCity = trimmedCity; // Update input field
                }
                this.cityDropdownOpen = false; // Close dropdown
            }
        };
    });

    // Room & Guest Selector Component for search form
    Alpine.data('roomGuestSelector', (initialRooms = 1, initialGuests = 1) => {
        // Ensure initial values are at least 1
        initialRooms = Math.max(1, initialRooms || 1);
        let initialAdults = Math.max(1, initialGuests || 1); // Assume initialGuests are adults
        let initialChildren = 0; // Start with 0 children

        return {
            open: false, // Dropdown state
            rooms: initialRooms,
            adults: initialAdults,
            children: initialChildren,

            // Increment count for rooms, adults, or children
            increment(type) {
                if (type === 'rooms') this.rooms++;
                if (type === 'adults') this.adults++;
                if (type === 'children') this.children++;
                this.validateCounts(); // Ensure counts remain valid
            },

            // Decrement count, ensuring minimums are met
            decrement(type) {
                if (type === 'rooms' && this.rooms > 1) this.rooms--;
                if (type === 'adults' && this.adults > 1) this.adults--;
                if (type === 'children' && this.children > 0) this.children--;
                this.validateCounts();
            },

            // Ensure counts don't go below minimums (1 room, 1 adult, 0 children)
            validateCounts() {
                if (this.rooms < 1) this.rooms = 1;
                if (this.adults < 1) this.adults = 1;
                if (this.children < 0) this.children = 0;
            },

            // Generate formatted text for the display button
            formattedText() {
                let text = `${this.rooms} Room${this.rooms > 1 ? 's' : ''}, ${this.adults} Adult${this.adults > 1 ? 's' : ''}`;
                if (this.children > 0) {
                    text += `, ${this.children} Child${this.children > 1 ? 'ren' : ''}`;
                }
                return text;
            },

            // Computed property for total guests (adults + children) for the hidden input
            get totalGuests() {
                return this.adults + this.children;
            }
        };
    });
});