<?php
/**
 * Travel Booking Customer Area
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Customer_Area {
    
    /**
     * Initialize customer area
     */
    public static function init() {
        // Add customer area menu to WooCommerce My Account
        add_filter('woocommerce_account_menu_items', array(__CLASS__, 'add_account_menu_items'));
        
        // Add endpoints
        add_action('init', array(__CLASS__, 'add_endpoints'));
        
        // Add content to the endpoints
        add_action('woocommerce_account_travel-bookings_endpoint', array(__CLASS__, 'travel_bookings_content'));
        add_action('woocommerce_account_travel-profile_endpoint', array(__CLASS__, 'travel_profile_content'));
        add_action('woocommerce_account_travel-favorites_endpoint', array(__CLASS__, 'travel_favorites_content'));
        
        // Handle AJAX requests
        add_action('wp_ajax_save_travel_profile', array(__CLASS__, 'save_travel_profile'));
        add_action('wp_ajax_add_favorite_address', array(__CLASS__, 'add_favorite_address'));
        add_action('wp_ajax_remove_favorite_address', array(__CLASS__, 'remove_favorite_address'));
        add_action('wp_ajax_cancel_booking', array(__CLASS__, 'cancel_booking'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
    }
    
    /**
     * Add menu items to My Account
     */
    public static function add_account_menu_items($items) {
        // Insert after dashboard
        $new_items = array();
        
        foreach ($items as $key => $item) {
            $new_items[$key] = $item;
            
            if ($key === 'dashboard') {
                $new_items['travel-bookings'] = __('My Bookings', 'travel-booking');
                $new_items['travel-profile'] = __('Travel Profile', 'travel-booking');
                $new_items['travel-favorites'] = __('Favorite Addresses', 'travel-booking');
            }
        }
        
        return $new_items;
    }
    
    /**
     * Add endpoints
     */
    public static function add_endpoints() {
        add_rewrite_endpoint('travel-bookings', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('travel-profile', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('travel-favorites', EP_ROOT | EP_PAGES);
    }
    
    /**
     * Enqueue scripts and styles
     */
    public static function enqueue_scripts() {
        if (is_account_page()) {
            wp_enqueue_style(
                'travel-booking-customer',
                TRAVEL_BOOKING_PLUGIN_URL . 'assets/css/travel-booking-customer.css',
                array(),
                TRAVEL_BOOKING_VERSION
            );
            
            wp_enqueue_script(
                'travel-booking-customer',
                TRAVEL_BOOKING_PLUGIN_URL . 'assets/js/travel-booking-customer.js',
                array('jquery'),
                TRAVEL_BOOKING_VERSION,
                true
            );
            
            wp_localize_script('travel-booking-customer', 'travelBookingCustomer', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('travel_booking_customer_nonce'),
                'i18n' => array(
                    'confirm_cancel' => __('Are you sure you want to cancel this booking?', 'travel-booking'),
                    'success' => __('Success!', 'travel-booking'),
                    'error' => __('Error!', 'travel-booking'),
                    'saved' => __('Saved successfully!', 'travel-booking'),
                    'removed' => __('Removed successfully!', 'travel-booking')
                )
            ));
        }
    }
    
    /**
     * Travel bookings content
     */
    public static function travel_bookings_content() {
        $user_id = get_current_user_id();
        
        // Get user's bookings
        $bookings = self::get_user_bookings($user_id);
        
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/customer/bookings-list.php';
    }
    
    /**
     * Travel profile content
     */
    public static function travel_profile_content() {
        $user_id = get_current_user_id();
        
        // Get user's travel profile
        $profile = self::get_user_travel_profile($user_id);
        
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/customer/travel-profile.php';
    }
    
    /**
     * Favorite addresses content
     */
    public static function travel_favorites_content() {
        $user_id = get_current_user_id();
        
        // Get user's favorite addresses
        $favorites = self::get_user_favorite_addresses($user_id);
        
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/customer/favorite-addresses.php';
    }
    
    /**
     * Get user bookings
     */
    public static function get_user_bookings($user_id) {
        global $wpdb;
        
        $user = get_userdata($user_id);
        if (!$user) return array();
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $bookings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT b.*, v.name as vehicle_name, v.image_url as vehicle_image 
                 FROM {$table_name} b 
                 LEFT JOIN {$wpdb->prefix}travel_booking_vehicles v ON b.vehicle_id = v.id
                 WHERE b.client_email = %s 
                 ORDER BY b.created_at DESC",
                $user->user_email
            )
        );
        
        return $bookings;
    }
    
    /**
     * Get user travel profile
     */
    public static function get_user_travel_profile($user_id) {
        return array(
            'default_pickup' => get_user_meta($user_id, 'travel_default_pickup', true),
            'default_dropoff' => get_user_meta($user_id, 'travel_default_dropoff', true),
            'preferred_vehicle' => get_user_meta($user_id, 'travel_preferred_vehicle', true),
            'special_requests' => get_user_meta($user_id, 'travel_special_requests', true),
            'emergency_contact' => get_user_meta($user_id, 'travel_emergency_contact', true),
            'emergency_phone' => get_user_meta($user_id, 'travel_emergency_phone', true),
            'newsletter' => get_user_meta($user_id, 'travel_newsletter', true),
            'sms_notifications' => get_user_meta($user_id, 'travel_sms_notifications', true)
        );
    }
    
    /**
     * Get user favorite addresses
     */
    public static function get_user_favorite_addresses($user_id) {
        $favorites = get_user_meta($user_id, 'travel_favorite_addresses', true);
        return is_array($favorites) ? $favorites : array();
    }
    
    /**
     * Save travel profile
     */
    public static function save_travel_profile() {
        check_ajax_referer('travel_booking_customer_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(__('User not logged in.', 'travel-booking'));
        }
        
        $fields = array(
            'default_pickup' => sanitize_text_field($_POST['default_pickup'] ?? ''),
            'default_dropoff' => sanitize_text_field($_POST['default_dropoff'] ?? ''),
            'preferred_vehicle' => intval($_POST['preferred_vehicle'] ?? 0),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'] ?? ''),
            'emergency_contact' => sanitize_text_field($_POST['emergency_contact'] ?? ''),
            'emergency_phone' => sanitize_text_field($_POST['emergency_phone'] ?? ''),
            'newsletter' => isset($_POST['newsletter']) ? 1 : 0,
            'sms_notifications' => isset($_POST['sms_notifications']) ? 1 : 0
        );
        
        foreach ($fields as $key => $value) {
            update_user_meta($user_id, 'travel_' . $key, $value);
        }
        
        wp_send_json_success(__('Profile saved successfully!', 'travel-booking'));
    }
    
    /**
     * Add favorite address
     */
    public static function add_favorite_address() {
        check_ajax_referer('travel_booking_customer_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(__('User not logged in.', 'travel-booking'));
        }
        
        $name = sanitize_text_field($_POST['name'] ?? '');
        $address = sanitize_text_field($_POST['address'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'other');
        
        if (empty($name) || empty($address)) {
            wp_send_json_error(__('Name and address are required.', 'travel-booking'));
        }
        
        $favorites = self::get_user_favorite_addresses($user_id);
        
        $new_favorite = array(
            'id' => uniqid(),
            'name' => $name,
            'address' => $address,
            'type' => $type,
            'created_at' => current_time('mysql')
        );
        
        $favorites[] = $new_favorite;
        
        update_user_meta($user_id, 'travel_favorite_addresses', $favorites);
        
        wp_send_json_success(array(
            'message' => __('Address added successfully!', 'travel-booking'),
            'favorite' => $new_favorite
        ));
    }
    
    /**
     * Remove favorite address
     */
    public static function remove_favorite_address() {
        check_ajax_referer('travel_booking_customer_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(__('User not logged in.', 'travel-booking'));
        }
        
        $favorite_id = sanitize_text_field($_POST['favorite_id'] ?? '');
        
        if (empty($favorite_id)) {
            wp_send_json_error(__('Invalid favorite ID.', 'travel-booking'));
        }
        
        $favorites = self::get_user_favorite_addresses($user_id);
        
        $favorites = array_filter($favorites, function($favorite) use ($favorite_id) {
            return $favorite['id'] !== $favorite_id;
        });
        
        update_user_meta($user_id, 'travel_favorite_addresses', array_values($favorites));
        
        wp_send_json_success(__('Address removed successfully!', 'travel-booking'));
    }
    
    /**
     * Cancel booking
     */
    public static function cancel_booking() {
        check_ajax_referer('travel_booking_customer_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(__('User not logged in.', 'travel-booking'));
        }
        
        $booking_id = intval($_POST['booking_id'] ?? 0);
        
        if (!$booking_id) {
            wp_send_json_error(__('Invalid booking ID.', 'travel-booking'));
        }
        
        // Get booking
        $booking = Travel_Booking_Booking::get($booking_id);
        if (!$booking) {
            wp_send_json_error(__('Booking not found.', 'travel-booking'));
        }
        
        // Check if user owns this booking
        $user = get_userdata($user_id);
        if ($booking->client_email !== $user->user_email) {
            wp_send_json_error(__('You do not have permission to cancel this booking.', 'travel-booking'));
        }
        
        // Check if booking can be cancelled
        if (in_array($booking->status, array('cancelled', 'completed'))) {
            wp_send_json_error(__('This booking cannot be cancelled.', 'travel-booking'));
        }
        
        // Check cancellation policy (24h before travel)
        $travel_time = strtotime($booking->travel_date);
        $now = current_time('timestamp');
        $hours_until_travel = ($travel_time - $now) / 3600;
        
        if ($hours_until_travel < 24) {
            wp_send_json_error(__('Bookings can only be cancelled at least 24 hours before travel time.', 'travel-booking'));
        }
        
        // Update booking status
        $result = Travel_Booking_Booking::update_status($booking_id, 'cancelled');
        
        if (!$result) {
            wp_send_json_error(__('Failed to cancel booking.', 'travel-booking'));
        }
        
        // Cancel WooCommerce order if exists
        if ($booking->order_id) {
            $order = wc_get_order($booking->order_id);
            if ($order) {
                $order->update_status('cancelled', __('Cancelled by customer from travel booking area.', 'travel-booking'));
            }
        }
        
        wp_send_json_success(__('Booking cancelled successfully!', 'travel-booking'));
    }
    
    /**
     * Get booking status label
     */
    public static function get_status_label($status) {
        $labels = array(
            'pending' => __('Pending', 'travel-booking'),
            'confirmed' => __('Confirmed', 'travel-booking'),
            'completed' => __('Completed', 'travel-booking'),
            'cancelled' => __('Cancelled', 'travel-booking'),
            'on-hold' => __('On Hold', 'travel-booking'),
            'refunded' => __('Refunded', 'travel-booking'),
            'failed' => __('Failed', 'travel-booking')
        );
        
        return $labels[$status] ?? ucfirst($status);
    }
    
    /**
     * Get status CSS class
     */
    public static function get_status_class($status) {
        $classes = array(
            'pending' => 'status-pending',
            'confirmed' => 'status-confirmed',
            'completed' => 'status-completed',
            'cancelled' => 'status-cancelled',
            'on-hold' => 'status-on-hold',
            'refunded' => 'status-refunded',
            'failed' => 'status-failed'
        );
        
        return $classes[$status] ?? 'status-default';
    }
    
    /**
     * Can booking be cancelled
     */
    public static function can_cancel_booking($booking) {
        if (in_array($booking->status, array('cancelled', 'completed'))) {
            return false;
        }
        
        // Check 24h policy
        $travel_time = strtotime($booking->travel_date);
        $now = current_time('timestamp');
        $hours_until_travel = ($travel_time - $now) / 3600;
        
        return $hours_until_travel >= 24;
    }
}

// Initialize customer area
Travel_Booking_Customer_Area::init();