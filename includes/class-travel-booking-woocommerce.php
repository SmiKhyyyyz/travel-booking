<?php
/**
 * WooCommerce integration functionality
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_WooCommerce {
    /**
     * Initialize WooCommerce integration
     */
    public function init() {
        // Add booking details to order emails
        add_action('woocommerce_email_order_details', array($this, 'add_booking_details_to_email'), 20, 4);
        
        // Add booking details to order admin page
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_booking_admin_order_meta'), 10, 1);
        
        // Add booking details to thank you page
        add_action('woocommerce_thankyou', array($this, 'thankyou_booking_details'), 10, 1);
        
        // Update booking status when order status changes
        add_action('woocommerce_order_status_changed', array($this, 'update_booking_status'), 10, 4);
    }
    
    /**
     * Add booking details to order emails
     */
    public function add_booking_details_to_email($order, $sent_to_admin, $plain_text, $email) {
        // Get booking token from order meta
        $booking_token = $order->get_meta('_travel_booking_token');
        
        if (!$booking_token) {
            return;
        }
        
        // Get booking details
        $booking = Travel_Booking_Booking::get_by_token($booking_token);
        
        if (!$booking) {
            return;
        }
        
        // Get vehicle details
        $vehicle = Travel_Booking_Vehicle::get($booking->vehicle_id);
        
        if (!$vehicle) {
            return;
        }
        
        // Display booking details
        if ($plain_text) {
            echo "\n\n==========\n\n";
            echo __('Booking Details', 'travel-booking') . "\n\n";
            echo __('From', 'travel-booking') . ': ' . $booking->departure . "\n";
            echo __('To', 'travel-booking') . ': ' . $booking->destination . "\n";
            echo __('Date', 'travel-booking') . ': ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->travel_date)) . "\n";
            echo __('Vehicle', 'travel-booking') . ': ' . $vehicle->name . "\n";
            echo __('Passengers', 'travel-booking') . ': ' . $booking->number_of_passengers . "\n";
            echo __('Round Trip', 'travel-booking') . ': ' . ($booking->round_trip ? __('Yes', 'travel-booking') : __('No', 'travel-booking')) . "\n";
            
            if (!empty($booking->flight_number)) {
                echo __('Flight Number', 'travel-booking') . ': ' . $booking->flight_number . "\n";
            }
            
            if (!empty($booking->notes)) {
                echo __('Notes', 'travel-booking') . ': ' . $booking->notes . "\n";
            }
            
            echo "\n==========\n\n";
        } else {
            echo '<h2>' . __('Booking Details', 'travel-booking') . '</h2>';
            echo '<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; margin-bottom: 20px;">';
            echo '<tr><th>' . __('From', 'travel-booking') . ':</th><td>' . $booking->departure . '</td></tr>';
            echo '<tr><th>' . __('To', 'travel-booking') . ':</th><td>' . $booking->destination . '</td></tr>';
            echo '<tr><th>' . __('Date', 'travel-booking') . ':</th><td>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->travel_date)) . '</td></tr>';
            echo '<tr><th>' . __('Vehicle', 'travel-booking') . ':</th><td>' . $vehicle->name . '</td></tr>';
            echo '<tr><th>' . __('Passengers', 'travel-booking') . ':</th><td>' . $booking->number_of_passengers . '</td></tr>';
            echo '<tr><th>' . __('Round Trip', 'travel-booking') . ':</th><td>' . ($booking->round_trip ? __('Yes', 'travel-booking') : __('No', 'travel-booking')) . '</td></tr>';
            
            if (!empty($booking->flight_number)) {
                echo '<tr><th>' . __('Flight Number', 'travel-booking') . ':</th><td>' . $booking->flight_number . '</td></tr>';
            }
            
            if (!empty($booking->notes)) {
                echo '<tr><th>' . __('Notes', 'travel-booking') . ':</th><td>' . $booking->notes . '</td></tr>';
            }
            
            echo '</table>';
        }
    }
    
    /**
     * Display booking details on order admin page
     */
    public function display_booking_admin_order_meta($order) {
        // Get booking token from order meta
        $booking_token = $order->get_meta('_travel_booking_token');
        
        if (!$booking_token) {
            return;
        }
        
        // Get booking details
        $booking = Travel_Booking_Booking::get_by_token($booking_token);
        
        if (!$booking) {
            return;
        }
        
        // Get vehicle details
        $vehicle = Travel_Booking_Vehicle::get($booking->vehicle_id);
        
        if (!$vehicle) {
            return;
        }
        
        // Display booking details
        echo '<h3>' . __('Transportation Booking Details', 'travel-booking') . '</h3>';
        echo '<p><strong>' . __('From', 'travel-booking') . ':</strong> ' . $booking->departure . '</p>';
        echo '<p><strong>' . __('To', 'travel-booking') . ':</strong> ' . $booking->destination . '</p>';
        echo '<p><strong>' . __('Date', 'travel-booking') . ':</strong> ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->travel_date)) . '</p>';
        echo '<p><strong>' . __('Vehicle', 'travel-booking') . ':</strong> ' . $vehicle->name . '</p>';
        echo '<p><strong>' . __('Passengers', 'travel-booking') . ':</strong> ' . $booking->number_of_passengers . '</p>';
        echo '<p><strong>' . __('Round Trip', 'travel-booking') . ':</strong> ' . ($booking->round_trip ? __('Yes', 'travel-booking') : __('No', 'travel-booking')) . '</p>';
        echo '<p><strong>' . __('Distance', 'travel-booking') . ':</strong> ' . $booking->distance . ' km</p>';
        echo '<p><strong>' . __('Duration', 'travel-booking') . ':</strong> ' . $booking->duration . ' ' . __('hours', 'travel-booking') . '</p>';
        
        if (!empty($booking->flight_number)) {
            echo '<p><strong>' . __('Flight Number', 'travel-booking') . ':</strong> ' . $booking->flight_number . '</p>';
        }
        
        if (!empty($booking->notes)) {
            echo '<p><strong>' . __('Notes', 'travel-booking') . ':</strong> ' . $booking->notes . '</p>';
        }
        
        if (!empty($booking->promo_code)) {
            echo '<p><strong>' . __('Promo Code', 'travel-booking') . ':</strong> ' . $booking->promo_code . '</p>';
        }
    }
    
    /**
     * Display booking details on the thank you page
     */
    public function thankyou_booking_details($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Get booking token from order meta
        $booking_token = $order->get_meta('_travel_booking_token');
        
        if (!$booking_token) {
            return;
        }
        
        // Get booking details
        $booking = Travel_Booking_Booking::get_by_token($booking_token);
        
        if (!$booking) {
            return;
        }
        
        // Get vehicle details
        $vehicle = Travel_Booking_Vehicle::get($booking->vehicle_id);
        
        if (!$vehicle) {
            return;
        }
        
        // Display booking details
        echo '<h2>' . __('Transportation Booking Details', 'travel-booking') . '</h2>';
        echo '<table class="woocommerce-table shop_table booking_details">';
        echo '<tr><th>' . __('From', 'travel-booking') . '</th><td>' . $booking->departure . '</td></tr>';
        echo '<tr><th>' . __('To', 'travel-booking') . '</th><td>' . $booking->destination . '</td></tr>';
        echo '<tr><th>' . __('Date', 'travel-booking') . '</th><td>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->travel_date)) . '</td></tr>';
        echo '<tr><th>' . __('Vehicle', 'travel-booking') . '</th><td>' . $vehicle->name . '</td></tr>';
        echo '<tr><th>' . __('Passengers', 'travel-booking') . '</th><td>' . $booking->number_of_passengers . '</td></tr>';
        echo '<tr><th>' . __('Round Trip', 'travel-booking') . '</th><td>' . ($booking->round_trip ? __('Yes', 'travel-booking') : __('No', 'travel-booking')) . '</td></tr>';
        
        if (!empty($booking->flight_number)) {
            echo '<tr><th>' . __('Flight Number', 'travel-booking') . '</th><td>' . $booking->flight_number . '</td></tr>';
        }
        
        echo '</table>';
    }
    
    /**
     * Update booking status when order status changes
     */
    public function update_booking_status($order_id, $status_from, $status_to, $order) {
        // Get booking token from order meta
        $booking_token = $order->get_meta('_travel_booking_token');
        
        if (!$booking_token) {
            return;
        }
        
        // Get booking
        $booking = Travel_Booking_Booking::get_by_token($booking_token);
        
        if (!$booking) {
            return;
        }
        
        // Map order status to booking status
        $status_map = array(
            'pending' => 'pending',
            'processing' => 'confirmed',
            'on-hold' => 'on-hold',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'failed' => 'failed'
        );
        
        if (isset($status_map[$status_to])) {
            Travel_Booking_Booking::update_status($booking->id, $status_map[$status_to]);
        }
    }
}