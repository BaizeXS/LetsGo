// Function to calculate and display duration
function updateDuration() {
    const checkinInput = document.getElementById('checkinDate');
    const checkoutInput = document.getElementById('checkoutDate');
    const durationDisplay = document.getElementById('durationDisplay');

    if (!checkinInput || !checkoutInput || !durationDisplay) {
        if (durationDisplay) durationDisplay.textContent = '';
        return;
    }

    const checkinDate = new Date(checkinInput.value + 'T00:00:00');
    const checkoutDate = new Date(checkoutInput.value + 'T00:00:00');

    if (!isNaN(checkinDate) && !isNaN(checkoutDate) && checkoutDate > checkinDate) {
        const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
        const daysDiff = Math.round(timeDiff / (1000 * 3600 * 24));
        durationDisplay.textContent = daysDiff + (daysDiff === 1 ? ' night' : ' nights');
    } else {
        durationDisplay.textContent = '';
    }
}

// Function to set minimum checkout date based on checkin date
function setMinCheckoutDate() {
    const checkinInput = document.getElementById('checkinDate');
    const checkoutInput = document.getElementById('checkoutDate');

    if (!checkinInput || !checkoutInput) {
        return;
    }

    if (checkinInput.value) {
        const checkinDate = new Date(checkinInput.value + 'T00:00:00');
        if (!isNaN(checkinDate)) {
            const nextDay = new Date(checkinDate);
            nextDay.setDate(checkinDate.getDate() + 1);
            const minCheckoutValue = nextDay.toISOString().split('T')[0];
            checkoutInput.min = minCheckoutValue;

            const currentCheckoutDate = new Date(checkoutInput.value + 'T00:00:00');
            if (isNaN(currentCheckoutDate) || currentCheckoutDate <= checkinDate) {
                checkoutInput.value = minCheckoutValue;
                checkoutInput.dispatchEvent(new Event('change'));
            }
        } else {
             checkoutInput.min = '';
        }
    } else {
        checkoutInput.min = '';
    }
}

// Add event listeners to date inputs
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

// Initial calculation and setup on page load for dates
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('checkinDate') && document.getElementById('checkoutDate')) {
         setMinCheckoutDate();
         updateDuration();
    } else {
    }
});


// Subscribe Modal Code
function openSubscribeModal(hotelId) {
    const hotelIdInput = document.getElementById('hotelId');
    const subscribeModal = document.getElementById('subscribeModal');
    if (hotelIdInput && subscribeModal) {
        hotelIdInput.value = hotelId;
        subscribeModal.classList.remove('hidden');
        subscribeModal.classList.add('flex');
    }
}

function closeSubscribeModal() {
    const subscribeModal = document.getElementById('subscribeModal');
    if (subscribeModal) {
        subscribeModal.classList.add('hidden');
        subscribeModal.classList.remove('flex');
        const subscribeForm = document.getElementById('subscribeForm');
        if (subscribeForm) {
            subscribeForm.reset();
            subscribeForm.querySelectorAll('.text-red-500').forEach(el => {
                 el.classList.add('hidden');
                 el.textContent = '';
            });
             const submitButton = subscribeForm.querySelector('button[type="submit"]');
             if (submitButton) {
                 submitButton.disabled = false;
                 submitButton.querySelector('.button-text').classList.remove('hidden');
                 submitButton.querySelector('svg.animate-spin').classList.add('hidden');
             }
        }
    }
}

const subscribeForm = document.getElementById('subscribeForm');
if (subscribeForm) {
    subscribeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const hotelId = document.getElementById('hotelId').value;
        const formData = new FormData(this);
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const submitButton = this.querySelector('button[type="submit"]');
        const buttonText = submitButton.querySelector('.button-text');
        const spinner = submitButton.querySelector('svg.animate-spin');

         this.querySelectorAll('.text-red-500').forEach(el => {
             el.classList.add('hidden');
             el.textContent = '';
         });

        if (!csrfTokenMeta) {
             console.error('CSRF token not found');
             alert('Error: CSRF token missing.');
             return;
        }

        submitButton.disabled = true;
        buttonText.classList.add('hidden');
        spinner.classList.remove('hidden');

        fetch(`/hotels/${hotelId}/subscribe`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfTokenMeta.getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                 body: JSON.stringify({
                     _token: csrfTokenMeta.getAttribute('content'),
                     email: formData.get('email'),
                     price_threshold: formData.get('price_threshold') || null
                 })
            })
            .then(async response => {
                 const data = await response.json();
                 if (!response.ok) {
                    throw { status: response.status, data: data };
                 }
                 return data;
            })
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Subscription successful!');
                    closeSubscribeModal();
                } else {
                    alert(data.message || 'Subscription failed. Please try again.');
                }
            })
            .catch(error => {
                let errorMessage = 'An error occurred during subscription.';

                if (error && error.status === 422 && error.data && error.data.errors) {
                    errorMessage = 'Please correct the following errors:';
                    Object.keys(error.data.errors).forEach(field => {
                        const inputElement = subscribeForm.querySelector(`[name="${field}"]`);
                        const errorElement = inputElement ? inputElement.closest('div').querySelector('.text-red-500') : null;
                        const messages = error.data.errors[field];
                        if (errorElement) {
                            errorElement.textContent = messages.join(' ');
                            errorElement.classList.remove('hidden');
                        }
                        errorMessage += `\n- ${messages.join(', ')}`;
                    });
                } else if (error && error.data && error.data.message) {
                     alert(error.data.message);
                } else {
                     alert(errorMessage);
                }
            })
            .finally(() => {
                 if (submitButton) {
                     submitButton.disabled = false;
                     buttonText.classList.remove('hidden');
                     spinner.classList.add('hidden');
                 }
            });
    });
}

// Ensure modal closes if backdrop is clicked
const subscribeModal = document.getElementById('subscribeModal');
if (subscribeModal) {
    subscribeModal.addEventListener('click', function(event) {
        if (event.target === subscribeModal) {
            closeSubscribeModal();
        }
    });
}

// Alpine.js Components
document.addEventListener('alpine:init', () => {
    // City Dropdown Component
    Alpine.data('cityDropdown', (initialCity, popularDomestic, popularInternational) => {
        const MAX_HISTORY = 5;
        return {
            cityDropdownOpen: false,
            currentCity: initialCity || '',
            recentSearches: [],
            popularDomesticCities: popularDomestic || [],
            popularInternationalCities: popularInternational || [],
            init() {
                this.loadHistory();
            },
            loadHistory() {
                const history = localStorage.getItem('hotelSearchHistory');
                if (history) {
                    try { this.recentSearches = JSON.parse(history); } catch (e) {
                        console.error("Error parsing search history", e); this.recentSearches = []; localStorage.removeItem('hotelSearchHistory');
                    }
                } else { this.recentSearches = []; }
            },
            saveHistory() {
                try { localStorage.setItem('hotelSearchHistory', JSON.stringify(this.recentSearches)); } catch (e) { console.error("Error saving search history", e); }
            },
            addSearch(city) {
                if (!city || typeof city !== 'string' || city.trim() === '') return;
                const trimmedCity = city.trim();
                this.recentSearches = this.recentSearches.filter(item => item !== trimmedCity);
                this.recentSearches.unshift(trimmedCity);
                if (this.recentSearches.length > MAX_HISTORY) { this.recentSearches = this.recentSearches.slice(0, MAX_HISTORY); }
                this.saveHistory();
            },
            clearHistory() {
                this.recentSearches = []; localStorage.removeItem('hotelSearchHistory');
            },
            selectCity(city) {
                if (city && city.trim() !== '') { this.addSearch(city); this.currentCity = city.trim(); }
                this.cityDropdownOpen = false;
            }
        };
    });

    // Room Guest Selector Component
    Alpine.data('roomGuestSelector', (initialRooms = 1, initialGuests = 1) => {
        initialRooms = Math.max(1, initialRooms || 1);
        let initialAdults = Math.max(1, initialGuests || 1);
        let initialChildren = 0;
        return {
            open: false, rooms: initialRooms, adults: initialAdults, children: initialChildren,
            increment(type) {
                if (type === 'rooms') this.rooms++; if (type === 'adults') this.adults++; if (type === 'children') this.children++;
                this.validateCounts();
            },
            decrement(type) {
                if (type === 'rooms' && this.rooms > 1) this.rooms--; if (type === 'adults' && this.adults > 1) this.adults--; if (type === 'children' && this.children > 0) this.children--;
                this.validateCounts();
            },
            validateCounts() {
                if (this.rooms < 1) this.rooms = 1; if (this.adults < 1) this.adults = 1; if (this.children < 0) this.children = 0;
            },
            formattedText() {
                return `${this.rooms} Room${this.rooms > 1 ? 's' : ''}, ${this.adults} Adult${this.adults > 1 ? 's' : ''}${this.children > 0 ? ', ' + this.children + ' Child' + (this.children > 1 ? 'ren' : '') : ''}`;
            },
        };
    });
});
