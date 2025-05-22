/**
 * Travel Booking System JavaScript
 */

if (typeof travel_booking_params === 'undefined') {
    var travel_booking_params = {
        ajax_url: '/wp-admin/admin-ajax.php',
        nonce: '',
        default_location: 'Geneva, Switzerland',
        currency_symbol: 'CHF',
        i18n: {
            select_vehicle: 'Select Vehicle',
            loading: 'Loading...',
            error: 'Error',
            no_vehicles: 'No vehicles available for the selected criteria.',
            calculate_first: 'Please calculate the route first.',
            fill_required: 'Please fill in all required fields.',
            confirm_selection: 'Are you sure you want to select this vehicle?',
            proceed_payment: 'Proceed to Payment',
            processing: 'Processing...'
        }
    };
}

(function($) {
    'use strict';
    
    // Initialize when the document is ready
    $(document).ready(function() {
        initTravelBooking();
    });
    
    /**
     * Initialize the travel booking functionality
     */
    function initTravelBooking() {
        // Initialize booking form
        initBookingForm();
        
        // Initialize booking summary
        initBookingSummary();
    }
    
    /**
     * Initialize booking form
     */
    function initBookingForm() {
        const form = $('#travel-booking-form');
        
        if (form.length === 0) {
            return;
        }
        
        // Initialize Google Maps autocomplete if available
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            initAutocomplete();
        }
        
        // Handle form submission
        form.on('submit', function(e) {
            e.preventDefault();
            calculateRoute();
        });
        
        // Handle vehicle selection
        $(document).on('click', '.travel-booking-select-vehicle', function() {
            const vehicleData = $(this).data();
            selectVehicle(vehicleData);
        });
    }
    
    /**
     * Initialize booking summary
     */

    function initBookingSummary() {
    // Récupérer le token de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    
    // Si le travel_booking_params n'existe pas encore, le créer
    if (typeof travel_booking_params === 'undefined') {
        window.travel_booking_params = {
            // IMPORTANT : Utilisez l'URL correcte de l'API REST
            ajax_url: '/wp-json/travel-booking/v1/apply-promo',
            token: token
        };
    } else {
        // Sinon, simplement ajouter le token
        travel_booking_params.token = token;
        // Assurez-vous que l'URL est correcte
        travel_booking_params.ajax_url = '/wp-json/travel-booking/v1/apply-promo';
    }

        const clientForm = $('#travel-booking-client-form');
        
        if (clientForm.length === 0) {
            return;
        }
        
        // Handle client form submission
        clientForm.on('submit', function(e) {
            e.preventDefault();
            saveClientDetails();
        });
        
        // Handle promo code application
        $('#apply-promo-code').on('click', function() {
            applyPromoCode();
        });
        
        // Handle create order button
        $('#create-order').on('click', function() {
            createOrder();
        });
    }
    
    /**
     * Initialize Google Maps autocomplete
     */
    function initAutocomplete() {
        const departureInput = document.getElementById('departure');
        const destinationInput = document.getElementById('destination');
        
        if (departureInput && destinationInput) {
            const departureAutocomplete = new google.maps.places.Autocomplete(departureInput);
            const destinationAutocomplete = new google.maps.places.Autocomplete(destinationInput);
        }
    }
    
    /**
     * Initialize Google Maps
     */
    function initMap() {
        if (!window.travelBookingMap) {
            const mapElement = document.getElementById('travel-booking-map');
            
            if (mapElement) {
                // Get default location
                const defaultLocation = travel_booking_params.default_location || 'Geneva, Switzerland';
                const geocoder = new google.maps.Geocoder();
                
                // Create map with default center
                window.travelBookingMap = new google.maps.Map(mapElement, {
                    center: { lat: 46.2044, lng: 6.1432 }, // Geneva by default
                    zoom: 10
                });
                
                // Try to center map on default location
                geocoder.geocode({ address: defaultLocation }, function(results, status) {
                    if (status === google.maps.GeocoderStatus.OK && results[0]) {
                        window.travelBookingMap.setCenter(results[0].geometry.location);
                    }
                });
            }
        }
    }
    
    /**
     * Calculate route
     */
    function calculateRoute() {
        const departure = $('#departure').val();
        const destination = $('#destination').val();
        const travelDate = $('#travel-date').val();
        const travelTime = $('#travel-time').val();
        const passengers = $('#passengers').val();
        const roundTrip = $('#round-trip').is(':checked');
        
        if (!departure || !destination || !travelDate || !travelTime || !passengers) {
            alert(travel_booking_params.i18n.fill_required);
            return;
        }
        
        // Show loading animation
        $('.travel-booking-loading-animation').show();
        $('#travel-booking-vehicles').empty();
        $('#travel-booking-results').hide();
        
        // Initialize map
        initMap();
        
        // Calculate route with Google Maps
        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer();
        directionsRenderer.setMap(window.travelBookingMap);
        
        const request = {
            origin: departure,
            destination: destination,
            travelMode: google.maps.TravelMode.DRIVING
        };
        
        directionsService.route(request, function(result, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                directionsRenderer.setDirections(result);
                
                // Get distance and duration
                const route = result.routes[0];
                const leg = route.legs[0];
                const distance = leg.distance.value / 1000; // Convert to km
                const duration = leg.duration.value / 3600; // Convert to hours
                
                // Update results
                $('#travel-booking-distance').text(distance.toFixed(2) + ' km');
                $('#travel-booking-duration').text(duration.toFixed(2) + ' ' + (duration <= 1 ? 'hour' : 'hours'));
                $('#travel-booking-results').show();
                
                // Get available vehicles
                getAvailableVehicles(departure, destination, passengers, distance, duration, roundTrip);
            } else {
                $('.travel-booking-loading-animation').hide();
                alert('Could not calculate the route. Please try again.');
            }
        });
    }
    
    /**
     * Get available vehicles
     */
    function getAvailableVehicles(departure, destination, passengers, distance, duration, roundTrip) {
        const travelDate = $('#travel-date').val();
        const travelTime = $('#travel-time').val();

        $.ajax({
            url: travel_booking_params.ajax_url,
            type: 'GET',
            data: {
                action: 'get_available_vehicles',
                departure: departure,
                destination: destination,
                passengers: passengers,
                distance: distance,
                duration: duration,
                round_trip: roundTrip ? 1 : 0,
                nonce: travel_booking_params.nonce
            },
            success: function(response) {
                $('.travel-booking-loading-animation').hide();
                
                if (response.success) {
                    displayVehicles(response.data, departure, destination, distance, duration, travelDate, travelTime, passengers, roundTrip);
                } else {
                    alert(response.data.message || travel_booking_params.i18n.error);
                }
            },
            error: function() {
                $('.travel-booking-loading-animation').hide();
                alert(travel_booking_params.i18n.error);
            }
        });
    }
    
    /**
     * Display available vehicles
     */
    function displayVehicles(vehicles, departure, destination, distance, duration, travelDate, travelTime, passengers, roundTrip) {
        const vehiclesContainer = $('#travel-booking-vehicles');
        vehiclesContainer.empty();
        
        if (vehicles.length === 0) {
            vehiclesContainer.html('<p class="travel-booking-no-vehicles">' + travel_booking_params.i18n.no_vehicles + '</p>');
            return;
        }
        
        // Create vehicles container
        const vehiclesGrid = $('<div class="travel-booking-vehicles-grid"></div>');
        
        // Add vehicles
        $.each(vehicles, function(index, vehicle) {
            const vehicleCard = $('<div class="travel-booking-vehicle-card"></div>');
            
            vehicleCard.html(`
                <div class="travel-booking-vehicle-card-image">
                    <img src="${vehicle.image_url}" alt="${vehicle.name}">
                </div>
                <div class="travel-booking-vehicle-card-content">
                    <h3 class="travel-booking-vehicle-card-title">${vehicle.name}</h3>
                    <p class="travel-booking-vehicle-card-description">${vehicle.description || ''}</p>
                    <div class="travel-booking-vehicle-card-details">
                        <div class="travel-booking-vehicle-card-capacity">
                            <span class="travel-booking-vehicle-card-label">Capacity:</span>
                            <span class="travel-booking-vehicle-card-value">${vehicle.capacity} passengers</span>
                        </div>
                        <div class="travel-booking-vehicle-card-price">
                            <span class="travel-booking-vehicle-card-label">Price:</span>
                            <span class="travel-booking-vehicle-card-value">${vehicle.price.toFixed(2)} ${travel_booking_params.currency_symbol || '$'}</span>
                        </div>
                    </div>
                    <button type="button" class="travel-booking-button travel-booking-select-vehicle"
                        data-vehicle-id="${vehicle.id}"
                        data-name="${vehicle.name}"
                        data-price="${vehicle.price}"
                        data-capacity="${vehicle.capacity}"
                        data-distance="${distance}"
                        data-duration="${duration}"
                        data-departure="${departure}"
                        data-destination="${destination}"
                        data-travel-date="${travelDate}"
                        data-travel-time="${travelTime}"
                        data-passengers="${passengers}"
                        data-round-trip="${roundTrip ? 1 : 0}">
                        Select
                    </button>
                </div>
            `);
            
            vehiclesGrid.append(vehicleCard);
        });
        
        vehiclesContainer.append('<h3>' + travel_booking_params.i18n.select_vehicle + '</h3>');
        vehiclesContainer.append(vehiclesGrid);
        
        // Scroll to vehicles
        $('html, body').animate({
            scrollTop: vehiclesContainer.offset().top - 50
        }, 500);
    }
    
    /**
     * Select a vehicle and create booking
     */
    function selectVehicle(vehicleData) {
        if (confirm(travel_booking_params.i18n.confirm_selection)) {
            // Show loading animation
            $('.travel-booking-loading-animation').show();
            
            $.ajax({
                url: travel_booking_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'create_booking',
                    vehicle_id: vehicleData.vehicleId,
                    departure: vehicleData.departure,
                    destination: vehicleData.destination,
                    travel_date: vehicleData.travelDate,
                    travel_time: vehicleData.travelTime,
                    passengers: vehicleData.passengers,
                    distance: vehicleData.distance,
                    duration: vehicleData.duration,
                    price: vehicleData.price,
                    round_trip: vehicleData.roundTrip,
                    nonce: travel_booking_params.nonce
                },
                success: function(response) {
                    $('.travel-booking-loading-animation').hide();
                    
                    if (response.success) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        alert(response.data.message || travel_booking_params.i18n.error);
                    }
                },
                error: function() {
                    $('.travel-booking-loading-animation').hide();
                    alert(travel_booking_params.i18n.error);
                }
            });
        }
    }
    
/**
     * Save client details
     */
    function saveClientDetails() {
        const firstName = $('#first-name').val();
        const lastName = $('#last-name').val();
        const email = $('#email').val();
        const phone = $('#phone').val();
        const address = $('#address').val();
        const flightNumber = $('#flight-number').val();
        const notes = $('#notes').val();
        
        if (!firstName || !lastName || !email || !phone || !address) {
            alert(travel_booking_params.i18n.fill_required);
            return;
        }
        
        // Show loading animation
        $('#proceed-to-payment').text(travel_booking_params.i18n.processing).prop('disabled', true);
        
        $.ajax({
            url: travel_booking_params.ajax_url,
            type: 'POST',
            data: {
                action: 'update_booking_client',
                token: travel_booking_params.token,
                first_name: firstName,
                last_name: lastName,
                email: email,
                phone: phone,
                address: address,
                flight_number: flightNumber,
                notes: notes,
                nonce: travel_booking_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Create order after saving client details
                    createOrder();
                } else {
                    $('#proceed-to-payment').text(travel_booking_params.i18n.proceed_payment).prop('disabled', false);
                    alert(response.data.message || travel_booking_params.i18n.error);
                }
            },
            error: function() {
                $('#proceed-to-payment').text(travel_booking_params.i18n.proceed_payment).prop('disabled', false);
                alert(travel_booking_params.i18n.error);
            }
        });
    }
    
    /**
     * Apply promo code
     */
    /**
 * Apply promo code
 */
function applyPromoCode() {
    const promoCode = $('#promo-code').val();
    
    if (!promoCode) {
        $('#promo-code-message').text('Veuillez entrer un code promo').addClass('travel-booking-promo-code-error').removeClass('travel-booking-promo-code-success');
        return;
    }
    
    // Log pour le débogage
    console.log('Tentative d\'application du code promo:', promoCode);
    console.log('Token utilisé:', travel_booking_params.token);
    
    // Désactiver le bouton pendant la requête
    $('#apply-promo-code').prop('disabled', true).text('Application...');
    
    // IMPORTANT : Utilisez l'URL correcte de l'API REST
    const apiUrl = '/wp-json/travel-booking/v1/apply-promo';
    
    $.ajax({
        url: apiUrl,
        type: 'POST',
        data: JSON.stringify({
            token: travel_booking_params.token,
            code: promoCode
        }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            console.log('Réponse du serveur:', response);
            
            if (response.success) {
                $('#promo-code-message')
                    .text('Code promo appliqué: ' + response.discount + '% de réduction')
                    .removeClass('travel-booking-promo-code-error')
                    .addClass('travel-booking-promo-code-success');
                
                // Mettre à jour le prix affiché
                const priceElement = $('#travel-booking-price');
                const originalText = priceElement.text();
                const originalPrice = parseFloat(originalText.replace(/[^\d.]/g, ''));
                
                if (!priceElement.attr('data-original-price')) {
                    priceElement.attr('data-original-price', originalPrice);
                }
                
                const discountAmount = originalPrice * (response.discount / 100);
                const discountedPrice = originalPrice - discountAmount;
                
                priceElement.html(
                    '<span class="original-price" style="text-decoration: line-through;">' + 
                    originalPrice.toFixed(2) + ' CHF</span> ' +
                    '<span class="discounted-price">' + discountedPrice.toFixed(2) + ' CHF</span>'
                );
            } else {
                $('#promo-code-message')
                    .text(response.data && response.data.message || 'Code promo invalide')
                    .addClass('travel-booking-promo-code-error')
                    .removeClass('travel-booking-promo-code-success');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur AJAX:', xhr.responseText);
            
            let errorMessage = 'Erreur lors de l\'application du code promo';
            
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message || (response.data && response.data.message)) {
                    errorMessage = response.message || response.data.message;
                }
            } catch (e) {
                console.error('Erreur lors de l\'analyse de la réponse JSON:', e);
            }
            
            $('#promo-code-message')
                .text(errorMessage)
                .addClass('travel-booking-promo-code-error')
                .removeClass('travel-booking-promo-code-success');
        },
        complete: function() {
            // Réactiver le bouton
            $('#apply-promo-code').prop('disabled', false).text('Appliquer');
        }
    });
}
    
    /**
     * Create WooCommerce order and proceed to payment
     */
function createOrder() {
    // Afficher l'indicateur de chargement
    const buttonText = $('#create-order').length ? $('#create-order').text() : $('#proceed-to-payment').text();
    
    if ($('#create-order').length) {
        $('#create-order').text('Traitement en cours...').prop('disabled', true);
    } else {
        $('#proceed-to-payment').text('Traitement en cours...').prop('disabled', true);
    }
    
    // Utiliser l'URL correcte pour créer la commande
    const createOrderUrl = '/wp-json/travel-booking/v1/create-order';
    
    // Empêcher toute requête AJAX vers apply-promo pendant la création de commande
    const originalAjax = $.ajax;
    $.ajax = function(settings) {
        if (settings.url && settings.url.includes('apply-promo')) {
            // Ignorer silencieusement les requêtes vers apply-promo
            console.log('Requête vers apply-promo ignorée pendant la création de commande');
            return { abort: function() {} };
        }
        return originalAjax.apply(this, arguments);
    };
    
    // Faire la requête de création de commande
    originalAjax({
        url: createOrderUrl,
        type: 'POST',
        data: JSON.stringify({
            token: travel_booking_params.token
        }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            console.log('Réponse du serveur (création commande):', response);
            
            if (response.success && response.data && response.data.payment_url) {
                // Rediriger vers la page de paiement
                window.location.href = response.data.payment_url;
            } else {
                // Gérer l'erreur
                alert('Erreur lors de la création de la commande. Veuillez réessayer.');
                
                // Réactiver le bouton
                if ($('#create-order').length) {
                    $('#create-order').text(buttonText).prop('disabled', false);
                } else {
                    $('#proceed-to-payment').text(buttonText).prop('disabled', false);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur lors de la création de la commande:', xhr.responseText);
            
            // Essayer d'extraire un message d'erreur
            let errorMessage = 'Erreur lors de la création de la commande. Veuillez réessayer.';
            
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message || (response.data && response.data.message)) {
                    errorMessage = response.message || response.data.message;
                }
            } catch (e) {
                console.error('Erreur lors de l\'analyse de la réponse JSON:', e);
            }
            
            alert(errorMessage);
            
            // Réactiver le bouton
            if ($('#create-order').length) {
                $('#create-order').text(buttonText).prop('disabled', false);
            } else {
                $('#proceed-to-payment').text(buttonText).prop('disabled', false);
            }
        },
        complete: function() {
            // Restaurer la fonction $.ajax originale
            $.ajax = originalAjax;
        }
    });
}
    
    // Make initTravelBooking function available globally
    window.initTravelBooking = initTravelBooking;
    
})(jQuery);