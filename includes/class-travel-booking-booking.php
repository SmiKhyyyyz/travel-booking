<?php
/**
 * Booking management functionality
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Booking {
    /**
     * Get all bookings
     */
    public static function get_all($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'orderby' => 'id',
            'order' => 'DESC',
            'limit' => -1,
            'offset' => 0,
            'status' => '',
            'vehicle_id' => 0,
            'search' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $sql = "SELECT * FROM {$table_name} WHERE 1=1";
        $sql_args = array();
        
        // Filter by status if provided
        if (!empty($args['status'])) {
            $sql .= " AND status = %s";
            $sql_args[] = $args['status'];
        }
        
        // Filter by vehicle_id if provided
        if ($args['vehicle_id'] > 0) {
            $sql .= " AND vehicle_id = %d";
            $sql_args[] = $args['vehicle_id'];
        }
        
        // Search in client name, email, or addresses
        if (!empty($args['search'])) {
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $sql .= " AND (
                client_first_name LIKE %s OR 
                client_last_name LIKE %s OR 
                client_email LIKE %s OR 
                departure LIKE %s OR 
                destination LIKE %s
            )";
            array_push($sql_args, $search_term, $search_term, $search_term, $search_term, $search_term);
        }
        
        // Add orderby
        $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        // Add limit and offset
        if ($args['limit'] > 0) {
            $sql .= " LIMIT %d OFFSET %d";
            array_push($sql_args, $args['limit'], $args['offset']);
        }
        
        if (!empty($sql_args)) {
            $bookings = $wpdb->get_results(
                $wpdb->prepare($sql, $sql_args)
            );
        } else {
            $bookings = $wpdb->get_results($sql);
        }
        
        return $bookings;
    }
    
    /**
     * Get a single booking by ID
     */
    public static function get($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $booking = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id)
        );
        
        return $booking;
    }
    
    /**
     * Get a booking by session token
     */
    public static function get_by_token($token) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $booking = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE session_token = %s", $token)
        );
        
        return $booking;
    }
    
    /**
     * Get a booking by order ID
     */
    public static function get_by_order_id($order_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $booking = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE order_id = %d", $order_id)
        );
        
        return $booking;
    }
    
    /**
     * Create a new booking
     */
    public static function create($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        // Generate a unique session token
        $session_token = self::generate_session_token();
        
        // Prepare travel date
        $travel_date = $data['travel_date'] . ' ' . $data['travel_time'];
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'vehicle_id' => intval($data['vehicle_id']),
                'session_token' => $session_token,
                'departure' => sanitize_text_field($data['departure']),
                'destination' => sanitize_text_field($data['destination']),
                'distance' => floatval($data['distance']),
                'duration' => floatval($data['duration']),
                'price' => floatval($data['price']),
                'travel_date' => $travel_date,
                'round_trip' => isset($data['round_trip']) ? 1 : 0,
                'number_of_passengers' => intval($data['passengers']),
                'status' => 'pending'
            ),
            array('%d', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%d', '%d', '%s')
        );
        
        if ($result) {
            return array(
                'id' => $wpdb->insert_id,
                'session_token' => $session_token
            );
        }
        
        return false;
    }
    
    /**
     * Update client details in a booking
     */
    public static function update_client_details($token, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'client_first_name' => sanitize_text_field($data['first_name']),
                'client_last_name' => sanitize_text_field($data['last_name']),
                'client_email' => sanitize_email($data['email']),
                'client_phone' => sanitize_text_field($data['phone']),
                'client_address' => sanitize_textarea_field($data['address']),
                'flight_number' => sanitize_text_field($data['flight_number'] ?? ''),
                'notes' => sanitize_textarea_field($data['notes'] ?? '')
            ),
            array('session_token' => $token),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Update a booking's status
     */
    public static function update_status($id, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Update order ID for a booking
     */
    public static function update_order_id($token, $order_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $result = $wpdb->update(
            $table_name,
            array('order_id' => $order_id),
            array('session_token' => $token),
            array('%d'),
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Apply promo code to a booking
     */
    public static function apply_promo_code($token, $promo_code) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $result = $wpdb->update(
            $table_name,
            array('promo_code' => sanitize_text_field($promo_code)),
            array('session_token' => $token),
            array('%s'),
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete a booking
     */
    public static function delete($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Generate a random session token
     */
    private static function generate_session_token() {
        return bin2hex(random_bytes(16)); // 32 characters
    }
    
    /**
     * Count bookings by status
     */
    public static function count_by_status($status = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_bookings';
        
        if (empty($status)) {
            return $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        }
        
        return $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE status = %s", $status)
        );
    }
}