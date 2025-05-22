<?php
/**
 * Frontend functionality
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Frontend {
    /**
     * Plugin version
     */
    private $version;
    
    /**
     * Initialize the class and set its properties.
     */
    public function __construct($version) {
        $this->version = $version;
        
        // Register AJAX handlers
        add_action('wp_ajax_get_available_vehicles', array($this, 'get_available_vehicles'));
        add_action('wp_ajax_nopriv_get_available_vehicles', array($this, 'get_available_vehicles'));
        
        add_action('wp_ajax_create_booking', array($this, 'create_booking'));
        add_action('wp_ajax_nopriv_create_booking', array($this, 'create_booking'));
        
        add_action('wp_ajax_update_booking_client', array($this, 'update_booking_client'));
        add_action('wp_ajax_nopriv_update_booking_client', array($this, 'update_booking_client'));
        
        add_action('wp_ajax_apply_promo_code', array($this, 'apply_promo_code'));
        add_action('wp_ajax_nopriv_apply_promo_code', array($this, 'apply_promo_code'));
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'travel-booking',
            TRAVEL_BOOKING_PLUGIN_URL . 'assets/css/travel-booking.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the public-facing side - VERSION CORRIGÉE
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'travel-booking',
            TRAVEL_BOOKING_PLUGIN_URL . 'assets/js/travel-booking.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // Récupérer le token depuis l'URL si disponible
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
        
        // Paramètres de localisation améliorés
        wp_localize_script('travel-booking', 'travel_booking_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url(), // AJOUT IMPORTANT
            'nonce' => wp_create_nonce('travel_booking_nonce'),
            'rest_nonce' => wp_create_nonce('wp_rest'), // AJOUT IMPORTANT
            'token' => $token, // AJOUT IMPORTANT
            'default_location' => get_option('travel_booking_default_location', 'Geneva, Switzerland'),
            'currency_symbol' => function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : 'CHF',
            'i18n' => array(
                'select_vehicle' => __('Select Vehicle', 'travel-booking'),
                'loading' => __('Loading...', 'travel-booking'),
                'error' => __('Error', 'travel-booking'),
                'no_vehicles' => __('No vehicles available for the selected criteria.', 'travel-booking'),
                'calculate_first' => __('Please calculate the route first.', 'travel-booking'),
                'fill_required' => __('Please fill in all required fields.', 'travel-booking'),
                'confirm_selection' => __('Are you sure you want to select this vehicle?', 'travel-booking'),
                'proceed_payment' => __('Proceed to Payment', 'travel-booking'),
                'processing' => __('Processing...', 'travel-booking')
            )
        ));
        
        // Enregistrer le script API REST de WordPress
        wp_enqueue_script('wp-api');
    }
    
    /**
     * AJAX handler for getting available vehicles
     */
    public function get_available_vehicles() {
        error_log('=== get_available_vehicles appelée ===');
        error_log('GET params: ' . print_r($_GET, true));
    
        // Check nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'travel_booking_nonce')) {
            error_log('Nonce check failed');
            wp_send_json_error(array('message' => __('Security check failed.', 'travel-booking')));
            exit;
        }
        
        // Get parameters
        $departure = isset($_GET['departure']) ? sanitize_text_field($_GET['departure']) : '';
        $destination = isset($_GET['destination']) ? sanitize_text_field($_GET['destination']) : '';
        $passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 1;
        $distance = isset($_GET['distance']) ? floatval($_GET['distance']) : 0;
        $duration = isset($_GET['duration']) ? floatval($_GET['duration']) : 0;
        $round_trip = isset($_GET['round_trip']) && $_GET['round_trip'] == '1';
        
        // Validate required fields
        if (empty($departure) || empty($destination) || $distance <= 0) {
            wp_send_json_error(array('message' => __('Please provide valid departure, destination and distance.', 'travel-booking')));
            exit;
        }
        
        // Get vehicles that match passenger count
        $vehicles = Travel_Booking_Vehicle::get_all(array(
            'min_capacity' => $passengers
        ));
        
        // Calculate prices for each vehicle
        $vehicles_data = array();
        
        foreach ($vehicles as $vehicle) {
            $price_options = array(
                'round_trip' => $round_trip,
                'origin' => $departure,
                'destination' => $destination
            );
            
            $price = Travel_Booking_Vehicle::calculate_price($vehicle->id, $distance, $price_options);
            
            if ($price !== false) {
                $vehicles_data[] = array(
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'capacity' => $vehicle->capacity,
                    'description' => $vehicle->description,
                    'image_url' => $vehicle->image_url,
                    'price' => $price
                );
            }
        }
        
        wp_send_json_success($vehicles_data);
    }
    
    /**
     * AJAX handler for creating a booking
     */
    public function create_booking() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'travel_booking_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'travel-booking')));
            exit;
        }
        
        // Get parameters
        $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
        $departure = isset($_POST['departure']) ? sanitize_text_field($_POST['departure']) : '';
        $destination = isset($_POST['destination']) ? sanitize_text_field($_POST['destination']) : '';
        $travel_date = isset($_POST['travel_date']) ? sanitize_text_field($_POST['travel_date']) : '';
        $travel_time = isset($_POST['travel_time']) ? sanitize_text_field($_POST['travel_time']) : '';
        $passengers = isset($_POST['passengers']) ? intval($_POST['passengers']) : 1;
        $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
        $duration = isset($_POST['duration']) ? floatval($_POST['duration']) : 0;
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $round_trip = isset($_POST['round_trip']) && $_POST['round_trip'] == '1';
        
        // Validate required fields
        if ($vehicle_id <= 0 || empty($departure) || empty($destination) || empty($travel_date) || empty($travel_time) || $distance <= 0 || $price <= 0) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'travel-booking')));
            exit;
        }
        
        // Create booking
        $booking_data = array(
            'vehicle_id' => $vehicle_id,
            'departure' => $departure,
            'destination' => $destination,
            'travel_date' => $travel_date,
            'travel_time' => $travel_time,
            'passengers' => $passengers,
            'distance' => $distance,
            'duration' => $duration,
            'price' => $price,
            'round_trip' => $round_trip
        );
        
        $result = Travel_Booking_Booking::create($booking_data);
        
        if ($result) {
            // Get summary page URL
            $summary_page_id = get_option('travel_booking_summary_page_id', 0);
            
            if ($summary_page_id > 0) {
                $redirect_url = add_query_arg('token', $result['session_token'], get_permalink($summary_page_id));
            } else {
                $redirect_url = add_query_arg('token', $result['session_token'], home_url());
            }
            
            wp_send_json_success(array(
                'booking_id' => $result['id'],
                'token' => $result['session_token'],
                'redirect_url' => $redirect_url
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to create booking. Please try again.', 'travel-booking')));
        }
    }
    
    /**
     * AJAX handler for updating booking client details
     */
    public function update_booking_client() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'travel_booking_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'travel-booking')));
            exit;
        }
        
        // Get parameters
        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $address = isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '';
        $flight_number = isset($_POST['flight_number']) ? sanitize_text_field($_POST['flight_number']) : '';
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        // Validate required fields
        if (empty($token) || empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'travel-booking')));
            exit;
        }
        
        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(array('message' => __('Please enter a valid email address.', 'travel-booking')));
            exit;
        }
        
        // Update booking client details
        $client_data = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'flight_number' => $flight_number,
            'notes' => $notes
        );
        
        $result = Travel_Booking_Booking::update_client_details($token, $client_data);
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error(array('message' => __('Failed to update booking. Please try again.', 'travel-booking')));
        }
    }
    
    /**
     * AJAX handler for applying promo code
     */
    public function apply_promo_code() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'travel_booking_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'travel-booking')));
            exit;
        }
        
        // Get parameters
        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        $promo_code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        
        // Validate required fields
        if (empty($token) || empty($promo_code)) {
            wp_send_json_error(array('message' => __('Please provide a valid promo code.', 'travel-booking')));
            exit;
        }
        
        // Get booking
        $booking = Travel_Booking_Booking::get_by_token($token);
        
        if (!$booking) {
            wp_send_json_error(array('message' => __('Invalid booking session. Please start a new booking.', 'travel-booking')));
            exit;
        }
        
        // Verify promo code
        $coupon = new WC_Coupon($promo_code);
        
        if (!$coupon->get_id()) {
            wp_send_json_error(array('message' => __('Invalid promo code.', 'travel-booking')));
            exit;
        }
        
        if (!$coupon->is_valid()) {
            wp_send_json_error(array('message' => __('This promo code is not valid or has expired.', 'travel-booking')));
            exit;
        }
        
        // Apply promo code to booking
        $result = Travel_Booking_Booking::apply_promo_code($token, $promo_code);
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to apply promo code. Please try again.', 'travel-booking')));
            exit;
        }
        
        // Calculate discount
        $discount_type = $coupon->get_discount_type();
        $discount_amount = $coupon->get_amount();
        
        if ($discount_type === 'percent') {
            $discount_percent = $discount_amount;
            $discounted_price = $booking->price * (1 - ($discount_percent / 100));
        } else {
            // Fixed discount
            $discounted_price = $booking->price - $discount_amount;
            if ($discounted_price < 0) {
                $discounted_price = 0;
            }
            $discount_percent = ($discount_amount / $booking->price) * 100;
        }
        
        wp_send_json_success(array(
            'original_price' => $booking->price,
            'discounted_price' => $discounted_price,
            'discount_amount' => $discount_amount,
            'discount_percent' => $discount_percent,
            'discount_type' => $discount_type
        ));
    }
}