/**
 * Travel Booking System JavaScript - Version Complète et Corrigée
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
        console.log('Initializing Travel Booking...');
        
        // S'assurer que les animations de chargement sont cachées au début
        $('.travel-booking-loading-animation').removeClass('show').css('visibility', 'hidden');
        $('#car-animation-container').removeClass('show').css('visibility', 'hidden');
        
        // Initialize booking form
        initBookingForm();
        
        // Initialize booking summary
        initBookingSummary();
        
        // Initialize Google Maps if available
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            console.log('Google Maps API loaded, initializing...');
            initAutocomplete();
            initMapWithStyle();
        } else {
            console.log('Google Maps API not loaded yet, waiting...');
        }
    }
    
    /**
     * Initialize booking form
     */
    function initBookingForm() {
        const form = $('#travel-booking-form');
        
        if (form.length === 0) {
            console.log('Booking form not found');
            return;
        }
        
        console.log('Booking form found, setting up handlers...');
        
        // Handle form submission
        form.on('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            calculateRouteStyled();
        });
        
        // Handle vehicle selection
        $(document).on('click', '.travel-booking-select-vehicle', function() {
            console.log('Vehicle selected');
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
                ajax_url: '/wp-admin/admin-ajax.php',
                token: token
            };
        } else {
            // Sinon, simplement ajouter le token
            travel_booking_params.token = token;
            // Assurez-vous que l'URL est correcte
            if (!travel_booking_params.ajax_url) {
                travel_booking_params.ajax_url = '/wp-admin/admin-ajax.php';
            }
        }

        // TOUJOURS attacher les événements - PAS de return prématuré !
        
        // Handle promo code application - POUR TOUS LES CAS
        $(document).on('click', '#apply-promo-code', function() {
            console.log('Click sur apply-promo-code détecté!');
            applyPromoCode();
        });
        
        // Handle promo code final (après infos client)
        $(document).on('click', '#apply-promo-code-final', function() {
            console.log('Click sur apply-promo-code-final détecté!');
            applyPromoCodeFinal();
        });
        
        // Handle create order button
        $('#create-order').on('click', function() {
            createOrder();
        });
        
        // Handle client form submission SEULEMENT s'il existe
        const clientForm = $('#travel-booking-client-form');
        if (clientForm.length > 0) {
            console.log('Formulaire client trouvé, ajout événement submit');
            clientForm.on('submit', function(e) {
                e.preventDefault();
                saveClientDetails();
            });
        } else {
            console.log('Formulaire client non trouvé (infos déjà saisies)');
        }
    }
    
    /**
     * Initialize Google Maps autocomplete
     */
    function initAutocomplete() {
        console.log('Initializing autocomplete...');
        const departureInput = document.getElementById('departure');
        const destinationInput = document.getElementById('destination');
        
        if (departureInput && destinationInput) {
            console.log('Setting up autocomplete for departure and destination');
            const departureAutocomplete = new google.maps.places.Autocomplete(departureInput);
            const destinationAutocomplete = new google.maps.places.Autocomplete(destinationInput);
            
            // Ajouter des listeners pour debug
            departureAutocomplete.addListener('place_changed', function() {
                console.log('Departure place changed:', departureAutocomplete.getPlace());
            });
            
            destinationAutocomplete.addListener('place_changed', function() {
                console.log('Destination place changed:', destinationAutocomplete.getPlace());
            });
        } else {
            console.log('Departure or destination input not found');
        }
    }
    
    /**
     * Initialize Google Maps with style
     */
    function initMapWithStyle() {
        console.log('Initializing styled map...');
        const mapElement = document.getElementById('travel-booking-map');
        
        if (!mapElement) {
            console.log('Map element not found');
            return;
        }
        
        if (!window.travelBookingMap) {
            const defaultLocation = { lat: 46.2044, lng: 6.1432 }; // Geneva
            
            // Style de carte sombre
            const darkMapStyle = [
                {
                    "elementType": "geometry",
                    "stylers": [{"color": "#212121"}]
                },
                {
                    "elementType": "labels.icon",
                    "stylers": [{"visibility": "off"}]
                },
                {
                    "elementType": "labels.text.fill",
                    "stylers": [{"color": "#757575"}]
                },
                {
                    "elementType": "labels.text.stroke",
                    "stylers": [{"color": "#212121"}]
                },
                {
                    "featureType": "administrative",
                    "elementType": "geometry",
                    "stylers": [{"color": "#757575"}]
                },
                {
                    "featureType": "administrative.country",
                    "elementType": "labels.text.fill",
                    "stylers": [{"color": "#9e9e9e"}]
                },
                {
                    "featureType": "administrative.locality",
                    "elementType": "labels.text.fill",
                    "stylers": [{"color": "#bdbdbd"}]
                },
                {
                    "featureType": "poi",
                    "elementType": "labels.text.fill",
                    "stylers": [{"color": "#757575"}]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "geometry",
                    "stylers": [{"color": "#181818"}]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry.fill",
                    "stylers": [{"color": "#2c2c2c"}]
                },
                {
                    "featureType": "road",
                    "elementType": "labels.text.fill",
                    "stylers": [{"color": "#8a8a8a"}]
                },
                {
                    "featureType": "road.arterial",
                    "elementType": "geometry",
                    "stylers": [{"color": "#373737"}]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "geometry",
                    "stylers": [{"color": "#3c3c3c"}]
                },
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [{"color": "#000000"}]
                },
                {
                    "featureType": "water",
                    "elementType": "labels.text.fill",
                    "stylers": [{"color": "#3d3d3d"}]
                }
            ];
            
            window.travelBookingMap = new google.maps.Map(mapElement, {
                center: defaultLocation,
                zoom: 10,
                styles: darkMapStyle,
                disableDefaultUI: false,
                zoomControl: true,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true
            });
            
            // Initialiser les services de directions
            window.directionsService = new google.maps.DirectionsService();
            window.directionsRenderer = new google.maps.DirectionsRenderer({
                suppressMarkers: false,
                polylineOptions: {
                    strokeColor: '#d3b27f',
                    strokeWeight: 4,
                    strokeOpacity: 0.8
                },
                markerOptions: {
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 8,
                        fillColor: '#d3b27f',
                        fillOpacity: 1,
                        strokeColor: '#ffffff',
                        strokeWeight: 2
                    }
                }
            });
            window.directionsRenderer.setMap(window.travelBookingMap);
            
            console.log('Map initialized successfully');
        }
    }
    
    /**
     * Calculate route with new styling
     */
    function calculateRouteStyled() {
        console.log('=== calculateRouteStyled called ===');
        
        const departure = $('#departure').val();
        const destination = $('#destination').val();
        const travelDate = $('#travel-date').val();
        const travelTime = $('#travel-time').val();
        const passengers = $('#passengers').val();
        const roundTrip = $('#round-trip').is(':checked');
        
        console.log('Form data:', {
            departure, destination, travelDate, travelTime, passengers, roundTrip
        });
        
        if (!departure || !destination || !travelDate || !travelTime || !passengers) {
            alert(travel_booking_params.i18n.fill_required);
            return;
        }
        
        // Afficher l'animation de chargement
        $('#car-animation-container').addClass('show').css('visibility', 'visible');
        $('.travel-booking-loading-animation').addClass('show').css('visibility', 'visible');
        $('#travel-booking-vehicles').empty();
        $('#travel-booking-results').hide();
        
        // Initialiser la carte si pas encore fait
        if (!window.travelBookingMap) {
            console.log('Map not initialized, initializing now...');
            initMapWithStyle();
        }
        
        // Vérifier que Google Maps est disponible
        if (typeof google === 'undefined' || !google.maps) {
            console.error('Google Maps API not loaded');
            $('.travel-booking-loading-animation').hide();
            $('#car-animation-container').hide();
            alert('Google Maps API not loaded. Please check the API key configuration.');
            return;
        }
        
        // Calculer le trajet avec Google Maps
        const directionsService = new google.maps.DirectionsService();
        
        const request = {
            origin: departure,
            destination: destination,
            travelMode: google.maps.TravelMode.DRIVING,
            provideRouteAlternatives: true,
            optimizeWaypoints: true
        };
        
        console.log('Calculating route with request:', request);
        
        directionsService.route(request, function(result, status) {
            console.log('Route calculation result:', status, result);
            
            if (status === google.maps.DirectionsStatus.OK) {
                // Afficher le trajet sur la carte
                if (window.directionsRenderer) {
                    window.directionsRenderer.setDirections(result);
                }
                
                // Trouver la route la plus courte
                let shortestRoute = result.routes[0];
                let shortestDistance = shortestRoute.legs[0].distance.value;
                
                result.routes.forEach(route => {
                    const distance = route.legs[0].distance.value;
                    if (distance < shortestDistance) {
                        shortestDistance = distance;
                        shortestRoute = route;
                    }
                });
                
                const leg = shortestRoute.legs[0];
                const distance = leg.distance.value / 1000; // en km
                const duration = leg.duration.value / 3600; // en heures
                
                console.log('Route calculated:', { distance, duration });
                
                // Mettre à jour les résultats
                $('#travel-booking-distance').text(distance.toFixed(2) + ' km');
                $('#travel-booking-duration').text(duration.toFixed(2) + ' hours');
                $('#travel-booking-results').show();
                
                // Obtenir les véhicules disponibles
                getAvailableVehicles(departure, destination, passengers, distance, duration, roundTrip);
                
            } else {
                console.error('Route calculation failed:', status);
                $('.travel-booking-loading-animation').removeClass('show');
                $('#car-animation-container').removeClass('show');
                alert('Could not calculate the route. Please try again. Status: ' + status);
            }
        });
    }
    
    /**
     * Get available vehicles
     */
    function getAvailableVehicles(departure, destination, passengers, distance, duration, roundTrip) {
        console.log('=== getAvailableVehicles called ===');
        console.log('Parameters:', {
            departure, destination, passengers, distance, duration, roundTrip
        });

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
                console.log('Vehicles response:', response);
                $('.travel-booking-loading-animation').removeClass('show');
                $('#car-animation-container').removeClass('show');
                
                if (response.success) {
                    displayVehiclesStyled(response.data, departure, destination, distance, duration, travelDate, travelTime, passengers, roundTrip);
                } else {
                    alert(response.data.message || travel_booking_params.i18n.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', xhr, status, error);
                $('.travel-booking-loading-animation').removeClass('show');
                $('#car-animation-container').removeClass('show');
                alert(travel_booking_params.i18n.error);
            }
        });
    }

    /**
     * Display vehicles with new styling
     */
    function displayVehiclesStyled(vehicles, departure, destination, distance, duration, travelDate, travelTime, passengers, roundTrip) {
        console.log('=== displayVehiclesStyled called ===');
        console.log('Vehicles:', vehicles);
        
        const vehiclesContainer = $('#travel-booking-vehicles');
        vehiclesContainer.empty();
        
        if (vehicles.length === 0) {
            vehiclesContainer.html('<p class="travel-booking-no-vehicles">' + travel_booking_params.i18n.no_vehicles + '</p>');
            return;
        }
        
        // Créer le titre
        vehiclesContainer.append('<h3 style="text-align: center; color: #354747; margin-bottom: 30px;">' + travel_booking_params.i18n.select_vehicle + '</h3>');
        
        // Créer la grille de véhicules
        const vehiclesGrid = $('<div class="travel-booking-vehicles-grid"></div>');
        
        // Ajouter chaque véhicule
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
                            <span class="travel-booking-vehicle-card-value">${vehicle.price.toFixed(2)} ${travel_booking_params.currency_symbol || 'CHF'}</span>
                        </div>
                    </div>
                    <button type="button" class="travel-booking-select-vehicle"
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
                        Choose this vehicle
                    </button>
                </div>
            `);
            
            vehiclesGrid.append(vehicleCard);
        });
        
        vehiclesContainer.append(vehiclesGrid);
        
        // Animation d'apparition des cartes
        setTimeout(() => {
            $('.travel-booking-vehicle-card').each(function(index) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                }).delay(index * 100).animate({
                    'opacity': '1'
                }, 500).css('transform', 'translateY(0px)');
            });
        }, 100);
        
        // Scroll vers les véhicules
        $('html, body').animate({
            scrollTop: vehiclesContainer.offset().top - 100
        }, 800);
    }

    /**
     * Select a vehicle and create booking
     */
    function selectVehicle(vehicleData) {
        console.log('=== selectVehicle called ===');
        console.log('Vehicle data:', vehicleData);
        
        if (confirm(travel_booking_params.i18n.confirm_selection)) {
            // Show loading animation
            $('.travel-booking-loading-animation').addClass('show');
            
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
                    $('.travel-booking-loading-animation').removeClass('show');
                    
                    if (response.success) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        alert(response.data.message || travel_booking_params.i18n.error);
                    }
                },
                error: function() {
                    $('.travel-booking-loading-animation').removeClass('show');
                    alert(travel_booking_params.i18n.error);
                }
            });
        }
    }

    /**
     * Apply promo code
     */
    function applyPromoCode() {
        console.log('=== applyPromoCode called ===');
        
        const promoCode = $('#promo-code').val().trim();
        console.log('Promo code value:', promoCode);
        
        if (!promoCode) {
            $('#promo-code-message')
                .text('Veuillez entrer un code promo')
                .removeClass('travel-booking-promo-code-success')
                .addClass('travel-booking-promo-code-error');
            return;
        }
        
        // Vérifier si le token existe
        if (!travel_booking_params.token) {
            $('#promo-code-message')
                .text('Session invalide. Veuillez rafraîchir la page.')
                .removeClass('travel-booking-promo-code-success')
                .addClass('travel-booking-promo-code-error');
            return;
        }
        
        // Désactiver le bouton pendant la requête
        const $button = $('#apply-promo-code');
        const originalText = $button.text();
        $button.prop('disabled', true).text('Application...');
        
        // Préparer les données
        const requestData = {
            token: travel_booking_params.token,
            code: promoCode
        };
        
        // Utiliser l'API REST de WordPress
        $.ajax({
            url: travel_booking_params.rest_url + 'travel-booking/v1/apply-promo',
            type: 'POST',
            data: JSON.stringify(requestData),
            contentType: 'application/json',
            dataType: 'json',
            beforeSend: function(xhr) {
                // Ajouter le nonce dans les headers
                xhr.setRequestHeader('X-WP-Nonce', travel_booking_params.rest_nonce);
            },
            success: function(response) {
                if (response.success) {
                    $('#promo-code-message')
                        .text(`Code promo appliqué: ${response.discount}% de réduction`)
                        .removeClass('travel-booking-promo-code-error')
                        .addClass('travel-booking-promo-code-success');
                    
                    // Mettre à jour le prix affiché
                    updatePriceDisplay(response.discount);
                    
                } else {
                    const errorMessage = response.data && response.data.message 
                        ? response.data.message 
                        : 'Code promo invalide';
                        
                    $('#promo-code-message')
                        .text(errorMessage)
                        .removeClass('travel-booking-promo-code-success')
                        .addClass('travel-booking-promo-code-error');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Erreur lors de l\'application du code promo';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Erreur parsing JSON:', e);
                }
                
                $('#promo-code-message')
                    .text(errorMessage)
                    .removeClass('travel-booking-promo-code-success')
                    .addClass('travel-booking-promo-code-error');
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Apply promo code - Version pour les infos client finales
     */
    function applyPromoCodeFinal() {
        const promoCode = $('#promo-code-final').val().trim();
        
        if (!promoCode) {
            $('#promo-code-message-final')
                .text('Veuillez entrer un code promo')
                .removeClass('travel-booking-promo-code-success')
                .addClass('travel-booking-promo-code-error');
            return;
        }
        
        // Utiliser le même code que applyPromoCode mais avec les bons IDs
        const $button = $('#apply-promo-code-final');
        const originalText = $button.text();
        $button.prop('disabled', true).text('Application...');
        
        const requestData = {
            token: travel_booking_params.token,
            code: promoCode
        };
        
        $.ajax({
            url: '/wp-json/travel-booking/v1/apply-promo',
            type: 'POST',
            data: JSON.stringify(requestData),
            contentType: 'application/json',
            dataType: 'json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', travel_booking_params.rest_nonce);
            },
            success: function(response) {
                if (response.success) {
                    $('#promo-code-message-final')
                        .text(`Code promo appliqué: ${response.discount}% de réduction`)
                        .removeClass('travel-booking-promo-code-error')
                        .addClass('travel-booking-promo-code-success');
                    
                    updatePriceDisplay(response.discount);
                } else {
                    const errorMessage = response.data && response.data.message 
                        ? response.data.message 
                        : 'Code promo invalide';
                        
                    $('#promo-code-message-final')
                        .text(errorMessage)
                        .removeClass('travel-booking-promo-code-success')
                        .addClass('travel-booking-promo-code-error');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Erreur lors de l\'application du code promo';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Erreur parsing JSON:', e);
                }
                
                $('#promo-code-message-final')
                    .text(errorMessage)
                    .removeClass('travel-booking-promo-code-success')
                    .addClass('travel-booking-promo-code-error');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Save client details
     */
    function saveClientDetails() {
        console.log('=== saveClientDetails called ===');
        
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
     * Create WooCommerce order and proceed to payment
     */
    function createOrder() {
        console.log('=== createOrder called ===');
        
        // Empêcher les double-clics
        if (window.createOrderInProgress) {
            console.log('Création déjà en cours, abandon');
            return;
        }
        window.createOrderInProgress = true;
        
        // Afficher l'indicateur de chargement
        const $createButton = $('#create-order');
        const $proceedButton = $('#proceed-to-payment');
        let buttonText = '';
        
        if ($createButton.length) {
            buttonText = $createButton.text();
            $createButton.text('Traitement en cours...').prop('disabled', true);
        } else if ($proceedButton.length) {
            buttonText = $proceedButton.text();
            $proceedButton.text('Traitement en cours...').prop('disabled', true);
        }
        
        // Utiliser l'URL correcte pour créer la commande
        const createOrderUrl = '/wp-json/travel-booking/v1/create-order';
        
        $.ajax({
            url: createOrderUrl,
            type: 'POST',
            data: JSON.stringify({
                token: travel_booking_params.token
            }),
            contentType: 'application/json',
            dataType: 'json',
            beforeSend: function(xhr) {
                // Ajouter le nonce REST si disponible
                if (travel_booking_params.rest_nonce) {
                    xhr.setRequestHeader('X-WP-Nonce', travel_booking_params.rest_nonce);
                }
            },
            success: function(response) {
                console.log('Create order response:', response);
                
                if (response.success && response.payment_url) {
                    console.log('Redirection vers:', response.payment_url);
                    window.location.href = response.payment_url;
                    
                } else {
                    console.error('Erreur: Pas d\'URL de paiement dans la réponse');
                    console.error('Réponse complète:', response);
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
                console.error('ERROR - Erreur lors de la création de la commande');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response Text:', xhr.responseText);
                
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
                if ($createButton.length) {
                    $createButton.text(buttonText).prop('disabled', false);
                } else if ($proceedButton.length) {
                    $proceedButton.text(buttonText).prop('disabled', false);
                }
            },
            complete: function() {
                // Libérer le verrou
                window.createOrderInProgress = false;
                console.log('=== createOrder TERMINE ===');
            }
        });
    }

    /**
     * Mettre à jour l'affichage du prix avec la réduction
     */
    function updatePriceDisplay(discountPercent) {
        const priceElement = $('#travel-booking-price');
        const originalPrice = parseFloat(priceElement.attr('data-original-price') || priceElement.text().replace(/[^\d.]/g, ''));
        
        // Sauvegarder le prix original si pas déjà fait
        if (!priceElement.attr('data-original-price')) {
            priceElement.attr('data-original-price', originalPrice);
        }
        
        const discountAmount = originalPrice * (discountPercent / 100);
        const discountedPrice = originalPrice - discountAmount;
        
        priceElement.html(`
            <span class="original-price" style="text-decoration: line-through; color: #999;">${originalPrice.toFixed(2)} CHF</span><br>
            <span class="discounted-price" style="font-weight: bold; color: #27ae60;">${discountedPrice.toFixed(2)} CHF</span>
        `);
    }
    
    // Make functions available globally for callback
    window.initTravelBooking = initTravelBooking;
    window.calculateRouteStyled = calculateRouteStyled;
    
})(jQuery);

// Callback function for Google Maps API
window.initTravelBookingCallback = function() {
    console.log('Google Maps callback fired');
    jQuery(document).ready(function() {
        // Attendre un petit délai pour s'assurer que tout est chargé
        setTimeout(function() {
            if (typeof initTravelBooking === 'function') {
                initTravelBooking();
            } else {
                console.error('initTravelBooking function not found');
            }
        }, 100);
    });
};

// Fonction globale pour compatibilité
window.initTravelBooking = window.initTravelBookingCallback;