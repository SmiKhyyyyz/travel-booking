<?php
/**
 * Vehicle management functionality
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Vehicle {
    /**
     * Get all vehicles
     */
    public static function get_all($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'orderby' => 'id',
            'order' => 'ASC',
            'limit' => -1,
            'offset' => 0,
            'min_capacity' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'travel_booking_vehicles';
        
        $sql = "SELECT * FROM {$table_name} WHERE capacity >= %d";
        $sql_args = array($args['min_capacity']);
        
        // Validation des paramètres ORDER BY
        $allowed_orderby = ['id', 'name', 'created_at', 'price'];
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'id';
        $order = in_array(strtoupper($args['order']), ['ASC', 'DESC']) ? $args['order'] : 'DESC';
        $sql .= " ORDER BY {$orderby} {$order}";
        
        // Add limit and offset
        if ($args['limit'] > 0) {
            $sql .= " LIMIT %d OFFSET %d";
            array_push($sql_args, $args['limit'], $args['offset']);
        }
        
        if ($args['limit'] > 0) {
            $vehicles = $wpdb->get_results(
                $wpdb->prepare($sql, $sql_args)
            );
        } else {
            $vehicles = $wpdb->get_results(
                $wpdb->prepare($sql, $args['min_capacity'])
            );
        }
        
        return $vehicles;
    }
    
    /**
     * Get a single vehicle by ID
     */
    public static function get($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_vehicles';
        
        $vehicle = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id)
        );
        
        return $vehicle;
    }
    
    /**
     * Add a new vehicle
     */
    public static function add($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_vehicles';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'capacity' => intval($data['capacity']),
                'description' => sanitize_textarea_field($data['description']),
                'image_url' => esc_url_raw($data['image_url']),
                'price_per_km' => floatval($data['price_per_km']),
                'base_price' => floatval($data['base_price'])
            ),
            array('%s', '%d', '%s', '%s', '%f', '%f')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update a vehicle
     */
    public static function update($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_vehicles';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'capacity' => intval($data['capacity']),
                'description' => sanitize_textarea_field($data['description']),
                'image_url' => esc_url_raw($data['image_url']),
                'price_per_km' => floatval($data['price_per_km']),
                'base_price' => floatval($data['base_price'])
            ),
            array('id' => $id),
            array('%s', '%d', '%s', '%s', '%f', '%f'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete a vehicle
     */
    public static function delete($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_vehicles';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Calculate price for a specific vehicle and route
     */
    public static function calculate_price($vehicle_id, $distance, $options = array()) {
        global $wpdb;
        
        $defaults = array(
            'round_trip' => false,
            'origin' => '',
            'destination' => ''
        );
        
        $options = wp_parse_args($options, $defaults);
        
        // Get the vehicle
        $vehicle = self::get($vehicle_id);
        
        if (!$vehicle) {
            return false;
        }
        
        // Check if there's a predefined rate for this route
        if (!empty($options['origin']) && !empty($options['destination'])) {
            $rates_table = $wpdb->prefix . 'travel_booking_rates';
            
            $rate = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$rates_table} 
                    WHERE origin = %s AND destination = %s AND vehicle_id = %d",
                    $options['origin'],
                    $options['destination'],
                    $vehicle_id
                )
            );
            
            if ($rate && $rate->fixed_price > 0) {
                $price = $rate->fixed_price;
                
                if ($options['round_trip']) {
                    $price *= 2;
                }
                
                return $price;
            }
        }
        
        // Calculate based on distance and vehicle pricing
        $price = $vehicle->price_per_km * $distance;
        
        // Apply minimum price if calculated price is lower
        if ($price < $vehicle->base_price) {
            $price = $vehicle->base_price;
        }
        
        // Double the price for round trip
        if ($options['round_trip']) {
            $price *= 2;
        }
        
        // Allow price modification via filters
        $price = apply_filters('travel_booking_calculated_price', $price, $vehicle_id, $distance, $options);
        
        return $price;
    }
}