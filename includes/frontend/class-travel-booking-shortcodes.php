<?php
/**
 * Frontend shortcodes
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Shortcodes {
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('travel_booking_form', array($this, 'booking_form_shortcode'));
        add_shortcode('travel_booking_summary', array($this, 'booking_summary_shortcode'));
    }
    
    /**
     * Booking form shortcode
     */
    public function booking_form_shortcode($atts) {
        // Extract attributes
        $atts = shortcode_atts(array(
            'title' => __('Book Your Transport', 'travel-booking'),
        ), $atts);
        
        // Enqueue required scripts and styles
        wp_enqueue_style('travel-booking');
        wp_enqueue_script('travel-booking');
        
        // Get Google Maps API key
        $api_key = get_option('travel_booking_google_maps_api_key', '');
        
        if (empty($api_key)) {
            return '<div class="travel-booking-notice travel-booking-error">' . __('Google Maps API key is not configured. Please configure it in the plugin settings.', 'travel-booking') . '</div>';
        }
        
        // Enqueue Google Maps script
        wp_enqueue_script(
            'google-maps',
            'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&callback=initTravelBooking',
            array('travel-booking'),
            null,
            true
        );
        
        // Localize script
        wp_localize_script('travel-booking', 'travel_booking_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('travel_booking_nonce'),
            'default_location' => get_option('travel_booking_default_location', 'Geneva, Switzerland'),
            'i18n' => array(
                'select_vehicle' => __('Select Vehicle', 'travel-booking'),
                'loading' => __('Loading...', 'travel-booking'),
                'error' => __('Error', 'travel-booking'),
                'no_vehicles' => __('No vehicles available for the selected criteria.', 'travel-booking'),
                'calculate_first' => __('Please calculate the route first.', 'travel-booking'),
                'fill_required' => __('Please fill in all required fields.', 'travel-booking'),
                'confirm_selection' => __('Are you sure you want to select this vehicle?', 'travel-booking')
            )
        ));
        
        // Start output buffering
        ob_start();
        
        // Include template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/frontend/booking-form.php';
        
        // Return buffered content
        return ob_get_clean();
    }
    
    /**
     * Booking summary shortcode
     */
    public function booking_summary_shortcode($atts) {
        // Extract attributes
        $atts = shortcode_atts(array(
            'title' => __('Booking Summary', 'travel-booking'),
        ), $atts);
        
        // Enqueue required scripts and styles
        wp_enqueue_style('travel-booking');
        wp_enqueue_script('travel-booking');
        
        // Get token from URL
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
        
        if (empty($token)) {
            return '<div class="travel-booking-notice travel-booking-error">' . __('Invalid booking session. Please start a new booking.', 'travel-booking') . '</div>';
        }
        
        // Get booking
        $booking = Travel_Booking_Booking::get_by_token($token);
        
        if (!$booking) {
            return '<div class="travel-booking-notice travel-booking-error">' . __('Booking not found. Please start a new booking.', 'travel-booking') . '</div>';
        }
        
        // Get vehicle
        $vehicle = Travel_Booking_Vehicle::get($booking->vehicle_id);
        
        if (!$vehicle) {
            return '<div class="travel-booking-notice travel-booking-error">' . __('Vehicle not found. Please start a new booking.', 'travel-booking') . '</div>';
        }
        
        // Localize script
        wp_localize_script('travel-booking', 'travel_booking_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('travel_booking_nonce'),
            'token' => $token,
            'i18n' => array(
                'confirm' => __('Are you sure?', 'travel-booking'),
                'processing' => __('Processing...', 'travel-booking'),
                'error' => __('Error', 'travel-booking'),
                'fill_required' => __('Please fill in all required fields.', 'travel-booking')
            )
        ));
        
        // Start output buffering
        ob_start();
        
        // Include template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/frontend/booking-summary.php';
        
        // Return buffered content
        return ob_get_clean();
    }
}