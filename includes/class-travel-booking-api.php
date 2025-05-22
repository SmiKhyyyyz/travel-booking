<?php
/**
 * API routes functionality
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_API {
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('travel-booking/v1', '/vehicles', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_vehicles'),
            'permission_callback' => array($this, 'get_items_permissions_check')
        ));
        
        register_rest_route('travel-booking/v1', '/calculate-price', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_price'),
            'permission_callback' => array($this, 'get_items_permissions_check')
        ));

        register_rest_route('travel-booking/v1', '/apply-promo', array(
            'methods' => 'POST',
            'callback' => array($this, 'apply_promo'),
            'permission_callback' => array($this, 'get_items_permissions_check')
        ));
        
        register_rest_route('travel-booking/v1', '/create-booking', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_booking'),
            'permission_callback' => array($this, 'create_item_permissions_check')
        ));
        
        register_rest_route('travel-booking/v1', '/update-booking', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_booking'),
            'permission_callback' => array($this, 'create_item_permissions_check')
        ));
        
        register_rest_route('travel-booking/v1', '/apply-promo', array(
            'methods' => 'POST',
            'callback' => array($this, 'apply_promo'),
            'permission_callback' => array($this, 'get_items_permissions_check')
        ));
        
        register_rest_route('travel-booking/v1', '/create-order', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_order'),
            'permission_callback' => array($this, 'create_item_permissions_check')
        ));
    }
    
    /**
     * Check if a given request has permission to get items
     */
    public function get_items_permissions_check($request) {
        return true; // Public endpoint for now
    }
    
    /**
     * Check if a given request has permission to create items
     */
    public function create_item_permissions_check($request) {
        // Verify nonce for authenticated requests
        $nonce = $request->get_header('X-WP-Nonce');
        if ($nonce && !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', __('Invalid nonce.', 'travel-booking'), array('status' => 403));
        }
        
        return true;
    }
    
    /**
     * Get vehicles
     */
    public function get_vehicles($request) {
        $passengers = intval($request->get_param('passengers'));
        
        $args = array(
            'min_capacity' => $passengers > 0 ? $passengers : 0
        );
        
        $vehicles = Travel_Booking_Vehicle::get_all($args);
        
        return rest_ensure_response($vehicles);
    }
    
    /**
     * Calculate price
     */
    public function calculate_price($request) {
        $params = $request->get_json_params();
        
        $vehicle_id = intval($params['vehicle_id']);
        $departure = sanitize_text_field($params['departure']);
        $destination = sanitize_text_field($params['destination']);
        $distance = floatval($params['distance']);
        $round_trip = isset($params['round_trip']) && $params['round_trip'];
        
        // Check if there's a predefined route
        $route = Travel_Booking_Route::check_predefined_route($departure, $destination, $vehicle_id);
        
        if ($route) {
            $price = $route['price'];
            $distance = $route['distance'];
            $duration = $route['duration'];
            
            if ($round_trip) {
                $price *= 2;
            }
        } else {
            $options = array(
                'round_trip' => $round_trip,
                'origin' => $departure,
                'destination' => $destination
            );
            
            $price = Travel_Booking_Vehicle::calculate_price($vehicle_id, $distance, $options);
        }
        
        if ($price === false) {
            return new WP_Error('invalid_vehicle', __('Invalid vehicle ID.', 'travel-booking'), array('status' => 400));
        }
        
        return rest_ensure_response(array(
            'price' => $price,
            'distance' => $distance,
            'duration' => $duration ?? ($distance / 70) // Estimate duration if not available
        ));
    }
    
    /**
     * Create a new booking
     */
    public function create_booking($request) {
        $params = $request->get_json_params();
        
        // Validate required fields
        $required_fields = array('vehicle_id', 'departure', 'destination', 'distance', 'price', 'travel_date', 'travel_time', 'passengers');
        
        foreach ($required_fields as $field) {
            if (empty($params[$field])) {
                return new WP_Error('missing_field', sprintf(__('Missing required field: %s', 'travel-booking'), $field), array('status' => 400));
            }
        }
        
        $result = Travel_Booking_Booking::create($params);
        
        if (!$result) {
            return new WP_Error('booking_failed', __('Failed to create booking.', 'travel-booking'), array('status' => 500));
        }
        
        $booking_summary_page = get_option('travel_booking_summary_page');
        $redirect_url = add_query_arg('token', $result['session_token'], get_permalink($booking_summary_page));
        
        return rest_ensure_response(array(
            'success' => true,
            'booking_id' => $result['id'],
            'token' => $result['session_token'],
            'redirect_url' => $redirect_url
        ));
    }
    
    /**
     * Update a booking
     */
    public function update_booking($request) {
        $params = $request->get_json_params();
        
        // Validate required fields
        $required_fields = array('token', 'first_name', 'last_name', 'email', 'phone', 'address');
        
        foreach ($required_fields as $field) {
            if (empty($params[$field])) {
                return new WP_Error('missing_field', sprintf(__('Missing required field: %s', 'travel-booking'), $field), array('status' => 400));
            }
        }
        
        $result = Travel_Booking_Booking::update_client_details($params['token'], $params);
        
        if (!$result) {
            return new WP_Error('update_failed', __('Failed to update booking.', 'travel-booking'), array('status' => 500));
        }
        
        return rest_ensure_response(array(
            'success' => true
        ));
    }
    
    /**
     * Apply promo code
     */
    public function apply_promo($request) {
        // Journaliser le début de la fonction
        error_log('=== Début de apply_promo ===');
        
        $params = $request->get_json_params();
        error_log('Paramètres reçus: ' . print_r($params, true));
        
        $token = sanitize_text_field($params['token']);
        $promo_code = sanitize_text_field($params['code']);
        error_log('Token: ' . $token . ', Code promo: ' . $promo_code);
        
        // Get the booking
        $booking = Travel_Booking_Booking::get_by_token($token);
        error_log('Booking trouvé: ' . ($booking ? 'Oui' : 'Non'));
        
        if (!$booking) {
            error_log('Erreur: Token de réservation invalide');
            return new WP_Error('invalid_token', __('Invalid booking token.', 'travel-booking'), array('status' => 400));
        }
        
        // Vérifier si le même code promo est déjà appliqué
        if (isset($booking->promo_code) && $booking->promo_code === $promo_code) {
            error_log('Code promo déjà appliqué: ' . $promo_code);
            
            // Définir une valeur de réduction par défaut si elle n'est pas disponible
            $discount_percent = 10; // Valeur par défaut
            
            // Si vous avez stocké un type de réduction et un montant dans la réservation, vous pouvez les utiliser
            if (isset($booking->discount_type) && isset($booking->discount_amount)) {
                if ($booking->discount_type === 'percent') {
                    $discount_percent = $booking->discount_amount;
                } else {
                    // Calculer le pourcentage approximatif pour un montant fixe
                    $discount_percent = ($booking->discount_amount / $booking->price) * 100;
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'discount' => $discount_percent,
                'code' => $promo_code,
                'already_applied' => true
            ));
        }

        if (!class_exists('WC_Coupon')) {
            error_log('Erreur: Classe WC_Coupon non disponible');
            return new WP_Error('woocommerce_not_loaded', __('WooCommerce coupon functionality is not available.', 'travel-booking'), array('status' => 500));
        }

        // Vérifier l'existence du coupon
        try {
            $coupon = new WC_Coupon($promo_code);
            error_log('Coupon ID: ' . $coupon->get_id());
            
            if (!$coupon->get_id()) {
                error_log('Erreur: Code promo invalide (ID non trouvé)');
                return new WP_Error('invalid_promo', __('Invalid promo code.', 'travel-booking'), array('status' => 400));
            }
            
            // Vérifier la validité du coupon
            $is_valid = $coupon->is_valid();
            error_log('Le coupon est-il valide? ' . ($is_valid ? 'Oui' : 'Non'));
            
            if (!$is_valid) {
                // Récupérer les erreurs spécifiques
                $errors = '';
                if (method_exists($coupon, 'get_error_messages')) {
                    $error_messages = $coupon->get_error_messages();
                    $errors = implode(', ', $error_messages);
                }
                error_log('Erreurs de validité du coupon: ' . $errors);
                
                return new WP_Error('invalid_promo', __('This promo code is not valid anymore.', 'travel-booking'), array('status' => 400));
            }
            
            // Calculer la réduction
            $discount_type = $coupon->get_discount_type();
            $discount_amount = $coupon->get_amount();
            error_log('Type de réduction: ' . $discount_type . ', Montant: ' . $discount_amount);
            
            // Appliquer le code promo à la réservation
            $result = Travel_Booking_Booking::apply_promo_code($token, $promo_code);
            error_log('Résultat de l\'application: ' . ($result ? 'Succès' : 'Échec'));
            
            if (!$result) {
                error_log('Erreur: Échec de l\'application du code promo');
                return new WP_Error('apply_failed', __('Failed to apply promo code.', 'travel-booking'), array('status' => 500));
            }
            
            // Calcul du pourcentage de réduction
            if ($discount_type === 'percent') {
                $discount_percent = $discount_amount;
            } else {
                // Pour les montants fixes, calculer un pourcentage approximatif
                $discount_percent = ($discount_amount / $booking->price) * 100;
            }
            error_log('Pourcentage de réduction final: ' . $discount_percent);
            
            $response = array(
                'success' => true,
                'discount' => $discount_percent,
                'code' => $promo_code
            );
            error_log('Réponse finale: ' . print_r($response, true));
            
            return rest_ensure_response($response);
        } catch (Exception $e) {
            error_log('Exception lors du traitement du coupon: ' . $e->getMessage());
            return new WP_Error('coupon_error', $e->getMessage(), array('status' => 500));
        }
    }
    
/**
     * Create WooCommerce order
     */
    public function create_order($request) {
    $params = $request->get_json_params();
    
    $token = isset($params['token']) ? sanitize_text_field($params['token']) : '';
    
    // Vérifier le token
    $booking = Travel_Booking_Booking::get_by_token($token);
    
    if (!$booking) {
        return new WP_Error('invalid_token', __('Invalid booking token.', 'travel-booking'), array('status' => 400));
    }
    
    // Créer une commande WooCommerce
    $order = wc_create_order();
    
    // Ajouter le produit
    $product_id = get_option('travel_booking_product_id', 0);
    
    if (!$product_id) {
        // Chercher un produit ou en créer un
        $products = wc_get_products(array(
            'limit' => 1,
            'status' => 'publish',
            'type' => 'simple',
            'virtual' => true
        ));
        
        if (!empty($products)) {
            $product_id = $products[0]->get_id();
        } else {
            // Créer un produit par défaut
            $product = new WC_Product_Simple();
            $product->set_name('Réservation de Transport');
            $product->set_status('publish');
            $product->set_catalog_visibility('hidden');
            $product->set_virtual(true);
            $product->set_price(0);
            $product->set_regular_price(0);
            $product->save();
            
            $product_id = $product->get_id();
            update_option('travel_booking_product_id', $product_id);
        }
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return new WP_Error('product_not_found', __('Booking product not found.', 'travel-booking'), array('status' => 500));
    }
    
    // Ajouter le produit à la commande
    $order->add_product($product, 1, array(
        'subtotal' => $booking->price,
        'total' => $booking->price
    ));
    
    // Ajouter les infos client
    $order->set_address(array(
        'first_name' => $booking->client_first_name,
        'last_name' => $booking->client_last_name,
        'email' => $booking->client_email,
        'phone' => $booking->client_phone,
        'address_1' => $booking->client_address
    ), 'billing');
    
    // Ajouter métadonnées de réservation
    $order->update_meta_data('_travel_booking_token', $token);
    $order->update_meta_data('_travel_booking_departure', $booking->departure);
    $order->update_meta_data('_travel_booking_destination', $booking->destination);
    $order->update_meta_data('_travel_booking_date', $booking->travel_date);
    $order->update_meta_data('_travel_booking_vehicle_id', $booking->vehicle_id);
    
    // Appliquer le code promo si présent dans la réservation
    if (!empty($booking->promo_code)) {
        try {
            $order->apply_coupon($booking->promo_code);
        } catch (Exception $e) {
            // Journaliser l'erreur mais continuer
            error_log('Erreur lors de l\'application du coupon: ' . $e->getMessage());
        }
    }
    
    // Finaliser la commande
    $order->calculate_totals();
    $order->update_status('pending', __('Order created from Travel Booking plugin.', 'travel-booking'));
    
    // Mettre à jour la réservation avec l'ID de commande
    Travel_Booking_Booking::update_order_id($token, $order->get_id());
    
    // Générer l'URL de paiement
    $payment_url = $order->get_checkout_payment_url();
    
    return rest_ensure_response(array(
        'success' => true,
        'order_id' => $order->get_id(),
        'payment_url' => $payment_url
    ));
}
    
    /**
     * Get or create a WooCommerce product for bookings
     */
    private function get_or_create_booking_product() {
        // First try to find existing product
        $products = wc_get_products(array(
            'limit' => 1,
            'return' => 'ids',
            'status' => 'publish',
            'meta_key' => '_travel_booking_product',
            'meta_value' => 'yes'
        ));
        
        if (!empty($products)) {
            update_option('travel_booking_product_id', $products[0]);
            return $products[0];
        }
        
        // Create a new product
        $product = new WC_Product_Simple();
        
        $product->set_name(__('Transport Booking', 'travel-booking'));
        $product->set_description(__('Transportation service booking.', 'travel-booking'));
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_price(0); // Price will be set during checkout
        $product->set_regular_price(0);
        $product->set_sold_individually(true);
        $product->set_virtual(true); // No shipping needed
        
        // Add product meta
        $product->update_meta_data('_travel_booking_product', 'yes');
        
        $product_id = $product->save();
        
        // Save product ID in options
        update_option('travel_booking_product_id', $product_id);
        
        return $product_id;
    }
}