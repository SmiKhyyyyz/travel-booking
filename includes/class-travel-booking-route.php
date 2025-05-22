<?php
/**
 * Route management functionality
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Route {
    /**
     * Get predefined routes
     */
    public static function get_predefined_routes($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'orderby' => 'id',
            'order' => 'ASC',
            'vehicle_id' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'travel_booking_rates';
        
        $sql = "SELECT * FROM {$table_name}";
        $sql_args = array();
        
        // Filter by vehicle_id if provided
        if ($args['vehicle_id'] > 0) {
            $sql .= " WHERE vehicle_id = %d";
            $sql_args[] = $args['vehicle_id'];
        }
        
        // Validation des paramètres ORDER BY
        $allowed_orderby = ['id', 'name', 'created_at', 'price'];
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'id';
        $order = in_array(strtoupper($args['order']), ['ASC', 'DESC']) ? $args['order'] : 'DESC';
        $sql .= " ORDER BY {$orderby} {$order}";
        
        if (!empty($sql_args)) {
            $routes = $wpdb->get_results(
                $wpdb->prepare($sql, $sql_args)
            );
        } else {
            $routes = $wpdb->get_results($sql);
        }
        
        return $routes;
    }
    
    /**
     * Get a single predefined route
     */
    public static function get_predefined_route($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_rates';
        
        $route = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id)
        );
        
        return $route;
    }
    
    /**
     * Add a new predefined route
     */
    public static function add_predefined_route($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_rates';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'origin' => sanitize_text_field($data['origin']),
                'destination' => sanitize_text_field($data['destination']),
                'vehicle_id' => intval($data['vehicle_id']),
                'distance' => floatval($data['distance']),
                'duration' => floatval($data['duration']),
                'fixed_price' => floatval($data['fixed_price'])
            ),
            array('%s', '%s', '%d', '%f', '%f', '%f')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update a predefined route
     */
    public static function update_predefined_route($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_rates';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'origin' => sanitize_text_field($data['origin']),
                'destination' => sanitize_text_field($data['destination']),
                'vehicle_id' => intval($data['vehicle_id']),
                'distance' => floatval($data['distance']),
                'duration' => floatval($data['duration']),
                'fixed_price' => floatval($data['fixed_price'])
            ),
            array('id' => $id),
            array('%s', '%s', '%d', '%f', '%f', '%f'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete a predefined route
     */
    public static function delete_predefined_route($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_rates';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Check if a predefined route exists
     */
    public static function check_predefined_route($origin, $destination, $vehicle_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'travel_booking_rates';
        
        $route = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} 
                WHERE origin = %s AND destination = %s AND vehicle_id = %d",
                $origin,
                $destination,
                $vehicle_id
            )
        );
        
        if ($route) {
            return array(
                'price' => $route->fixed_price,
                'distance' => $route->distance,
                'duration' => $route->duration
            );
        }
        
        return false;
    }
}