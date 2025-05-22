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
    $nonce = $request->get_header('X-WP-Nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('rest_forbidden', 'Invalid nonce', array('status' => 403));
    }
    return true;
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
        
        // Validation renforcée
        if (!isset($params['vehicle_id']) || !is_numeric($params['vehicle_id']) || $params['vehicle_id'] <= 0) {
            return new WP_Error('invalid_vehicle_id', 'Vehicle ID must be a valid positive integer');
        }

        if (!isset($params['departure']) || empty(trim($params['departure']))) {
            return new WP_Error('invalid_departure', 'Departure location is required');
        }
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
 * Apply promo code - VERSION CORRIGÉE
 */
public function apply_promo($request) {
    // Log pour debug
    error_log('=== Apply Promo Called ===');
    
    try {
        // Récupérer les paramètres
        $params = $request->get_json_params();
        if (!$params) {
            $params = $request->get_params();
        }
        
        // Validation des paramètres
        if (empty($params['token'])) {
            return new WP_Error('missing_token', 'Token manquant', array('status' => 400));
        }
        
        if (empty($params['code'])) {
            return new WP_Error('missing_code', 'Code promo manquant', array('status' => 400));
        }
        
        $token = sanitize_text_field($params['token']);
        $promo_code = sanitize_text_field($params['code']);
        
        // Vérifier que WooCommerce est actif
        if (!class_exists('WC_Coupon')) {
            return new WP_Error('woocommerce_not_loaded', 'WooCommerce non disponible', array('status' => 500));
        }
        
        // Get the booking
        $booking = Travel_Booking_Booking::get_by_token($token);
        
        if (!$booking) {
            return new WP_Error('invalid_token', 'Token de réservation invalide', array('status' => 400));
        }
        
        // Vérifier si le même code promo est déjà appliqué
        if (!empty($booking->promo_code) && $booking->promo_code === $promo_code) {
            // Récupérer les détails du coupon pour retourner la réduction
            $coupon = new WC_Coupon($promo_code);
            if ($coupon->get_id()) {
                $discount_type = $coupon->get_discount_type();
                $discount_amount = $coupon->get_amount();
                
                $discount_percent = ($discount_type === 'percent') 
                    ? $discount_amount 
                    : ($discount_amount / $booking->price) * 100;
                
                return rest_ensure_response(array(
                    'success' => true,
                    'discount' => round($discount_percent, 2),
                    'code' => $promo_code,
                    'already_applied' => true
                ));
            }
        }
        
        // Créer et valider le coupon
        $coupon = new WC_Coupon($promo_code);
        
        if (!$coupon->get_id()) {
            return new WP_Error('invalid_promo', 'Code promo invalide', array('status' => 400));
        }
        
        // Vérifier la validité du coupon
        $validation_errors = array();
        
        // Vérifier si le coupon est actif
        if ($coupon->get_status() !== 'publish') {
            $validation_errors[] = 'Ce code promo n\'est pas actif';
        }
        
        // Vérifier les dates de validité
        $now = current_time('timestamp');
        
        if ($coupon->get_date_expires() && $coupon->get_date_expires()->getTimestamp() < $now) {
            $validation_errors[] = 'Ce code promo a expiré';
        }
        
        if ($coupon->get_date_created() && $coupon->get_date_created()->getTimestamp() > $now) {
            $validation_errors[] = 'Ce code promo n\'est pas encore valide';
        }
        
        // Vérifier les limites d'utilisation
        if ($coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit()) {
            $validation_errors[] = 'Ce code promo a atteint sa limite d\'utilisation';
        }
        
        // Vérifier le montant minimum
        if ($coupon->get_minimum_amount() > 0 && $booking->price < $coupon->get_minimum_amount()) {
            $validation_errors[] = sprintf(
                'Montant minimum requis: %s CHF',
                number_format($coupon->get_minimum_amount(), 2)
            );
        }
        
        // Vérifier le montant maximum
        if ($coupon->get_maximum_amount() > 0 && $booking->price > $coupon->get_maximum_amount()) {
            $validation_errors[] = sprintf(
                'Montant maximum autorisé: %s CHF',
                number_format($coupon->get_maximum_amount(), 2)
            );
        }
        
        if (!empty($validation_errors)) {
            return new WP_Error('invalid_promo', implode('. ', $validation_errors), array('status' => 400));
        }
        
        // Appliquer le code promo à la réservation
        $result = Travel_Booking_Booking::apply_promo_code($token, $promo_code);
        
        if (!$result) {
            return new WP_Error('apply_failed', 'Échec de l\'application du code promo', array('status' => 500));
        }
        
        // Calculer la réduction
        $discount_type = $coupon->get_discount_type();
        $discount_amount = $coupon->get_amount();
        
        if ($discount_type === 'percent') {
            $discount_percent = $discount_amount;
        } else {
            // Pour les montants fixes, calculer un pourcentage
            $discount_percent = min(($discount_amount / $booking->price) * 100, 100);
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'discount' => round($discount_percent, 2),
            'code' => $promo_code,
            'discount_type' => $discount_type,
            'discount_amount' => $discount_amount
        ));
        
    } catch (Exception $e) {
        error_log('Exception dans apply_promo: ' . $e->getMessage());
        return new WP_Error('server_error', 'Erreur serveur: ' . $e->getMessage(), array('status' => 500));
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