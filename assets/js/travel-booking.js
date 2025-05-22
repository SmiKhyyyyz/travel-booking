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
 * Get available vehicles - VERSION CORRIGÉE
 */
function getAvailableVehicles(departure, destination, passengers, distance, duration, roundTrip) {
    const travelDate = $('#travel-date').val();
    const travelTime = $('#travel-time').val();

    console.log('Données envoyées:', {
    action: 'get_available_vehicles',
    departure: departure,
    destination: destination,
    passengers: passengers,
    distance: distance,
    duration: duration,
    round_trip: roundTrip ? 1 : 0,
    nonce: travel_booking_params.nonce
});

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
 * Select a vehicle and create booking - VERSION CORRIGÉE
 */
function selectVehicle(vehicleData) {
    if (confirm(travel_booking_params.i18n.confirm_selection)) {
        // Show loading animation
        $('.travel-booking-loading-animation').show();
        
        // CORRECTION : Utiliser ajax_url pour create_booking
        $.ajax({
            url: travel_booking_params.ajax_url, // ← CHANGEMENT ICI
            type: 'POST',
            data: {
                action: 'create_booking', // ← Action WordPress AJAX
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
                        <span class="travel-booking-vehicle-card-value">${vehicle.price.toFixed(2)} ${travel_booking_params.currency_symbol || 'CHF'}</span>
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
 * Save client details - VERSION CORRIGÉE
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
    
    // CORRECTION : Utiliser ajax_url pour update_booking_client
    $.ajax({
        url: travel_booking_params.ajax_url, // ← CHANGEMENT ICI
        type: 'POST',
        data: {
            action: 'update_booking_client', // ← Action WordPress AJAX
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
 * Apply promo code - VERSION CORRIGÉE
 */
function applyPromoCode() {

    console.log('=== applyPromoCode appelée ===');
    console.log('Button clicked!');
    
    const promoCode = $('#promo-code').val().trim();
    console.log('Promo code value:', promoCode);

    
    // // DEBUG - Ajoute ces lignes
    // console.log('=== DEBUG applyPromoCode ===');
    // console.log('Promo code:', promoCode);
    // console.log('Token:', travel_booking_params.token);
    // console.log('REST URL:', travel_booking_params.rest_url);
    
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

/**
 * Initialiser les paramètres manquants
 */
function initTravelBookingParams() {
    // S'assurer que les paramètres REST sont définis
    if (typeof travel_booking_params === 'undefined') {
        travel_booking_params = {};
    }
    
    // Ajouter les URLs REST si manquantes
    if (!travel_booking_params.rest_url) {
        travel_booking_params.rest_url = wpApiSettings.root || '/wp-json/';
    }
    
    if (!travel_booking_params.rest_nonce) {
        travel_booking_params.rest_nonce = wpApiSettings.nonce || '';
    }
}
    
    /**
     * Create WooCommerce order and proceed to payment
     */
    function createOrder() {
        
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
        
        // Sauvegarder la fonction $.ajax originale
        const originalAjax = $.ajax;
        
        // Empêcher toute requête AJAX vers apply-promo pendant la création de commande
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
            beforeSend: function(xhr) {
                // Ajouter le nonce REST si disponible
                if (travel_booking_params.rest_nonce) {
                    xhr.setRequestHeader('X-WP-Nonce', travel_booking_params.rest_nonce);
                }
            },
            success: function(response) {
                
                // CORRECTION ICI :
                if (response.success && response.payment_url) {  // ← Supprimer .data
                    console.log('Redirection vers:', response.payment_url);
                    window.location.href = response.payment_url;  // ← Supprimer .data
                    
                } else {
                    console.error('Erreur: Pas d\'URL de paiement dans la réponse');
                    console.error('Réponse complète:', response);  // ← Debug
                    alert('Erreur lors de la création de la commande. Veuillez réessayer.');
                    
                    // Réactiver le bouton - CORRECTION ICI :
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
                // Restaurer la fonction $.ajax originale
                $.ajax = originalAjax;
                
                // Libérer le verrou
                window.createOrderInProgress = false;
                
                console.log('=== createOrder TERMINE ===');
            }
        });
    }
    
    // Make initTravelBooking function available globally
    window.initTravelBooking = initTravelBooking;
    
})(jQuery);