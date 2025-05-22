<?php
/**
 * Admin routes management
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Admin_Routes {
    /**
     * Handle route page actions
     */
    public static function page_actions() {
        // Handle form submissions
        if (isset($_POST['travel_booking_route_action']) && isset($_POST['_wpnonce'])) {
            $action = sanitize_text_field($_POST['travel_booking_route_action']);
            
            // Verify nonce
            if (!wp_verify_nonce($_POST['_wpnonce'], 'travel_booking_route_' . $action)) {
                wp_die(__('Security check failed.', 'travel-booking'));
            }
            
            // Handle different actions
            switch ($action) {
                case 'create':
                    self::handle_create_route();
                    break;
                    
                case 'update':
                    self::handle_update_route();
                    break;
            }
        }
        
        // Handle delete action via GET request
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
            // Verify nonce
            if (!wp_verify_nonce($_GET['_wpnonce'], 'travel_booking_delete_route_' . $_GET['id'])) {
                wp_die(__('Security check failed.', 'travel-booking'));
            }
            
            self::handle_delete_route(intval($_GET['id']));
        }
    }
    
    /**
     * Handle create route action
     */
    private static function handle_create_route() {
        // Validate and sanitize input fields
        $origin = isset($_POST['origin']) ? sanitize_text_field($_POST['origin']) : '';
        $destination = isset($_POST['destination']) ? sanitize_text_field($_POST['destination']) : '';
        $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
        $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
        $duration = isset($_POST['duration']) ? floatval($_POST['duration']) : 0;
        $fixed_price = isset($_POST['fixed_price']) ? floatval($_POST['fixed_price']) : 0;
        
        // Validate required fields
        if (empty($origin) || empty($destination) || $vehicle_id <= 0 || $distance <= 0 || $duration <= 0 || $fixed_price <= 0) {
            add_settings_error(
                'travel_booking_route',
                'required-fields',
                __('Please fill in all required fields with valid values.', 'travel-booking'),
                'error'
            );
            return;
        }
        
        // Create route
        $route_data = array(
            'origin' => $origin,
            'destination' => $destination,
            'vehicle_id' => $vehicle_id,
            'distance' => $distance,
            'duration' => $duration,
            'fixed_price' => $fixed_price
        );
        
        $result = Travel_Booking_Route::add_predefined_route($route_data);
        
        if ($result) {
            // Redirect to routes list with success message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-routes', 'message' => 'created'),
                admin_url('admin.php')
            ));
            exit;
        } else {
            add_settings_error(
                'travel_booking_route',
                'create-failed',
                __('Failed to create route. Please try again.', 'travel-booking'),
                'error'
            );
        }
    }
    
    /**
     * Handle update route action
     */
    private static function handle_update_route() {
        // Get route ID
        $route_id = isset($_POST['route_id']) ? intval($_POST['route_id']) : 0;
        
        if ($route_id <= 0) {
            add_settings_error(
                'travel_booking_route',
                'invalid-id',
                __('Invalid route ID.', 'travel-booking'),
                'error'
            );
            return;
        }
        
        // Validate and sanitize input fields
        $origin = isset($_POST['origin']) ? sanitize_text_field($_POST['origin']) : '';
        $destination = isset($_POST['destination']) ? sanitize_text_field($_POST['destination']) : '';
        $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
        $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
        $duration = isset($_POST['duration']) ? floatval($_POST['duration']) : 0;
        $fixed_price = isset($_POST['fixed_price']) ? floatval($_POST['fixed_price']) : 0;
        
        // Validate required fields
        if (empty($origin) || empty($destination) || $vehicle_id <= 0 || $distance <= 0 || $duration <= 0 || $fixed_price <= 0) {
            add_settings_error(
                'travel_booking_route',
                'required-fields',
                __('Please fill in all required fields with valid values.', 'travel-booking'),
                'error'
            );
            return;
        }
        
        // Update route
        $route_data = array(
            'origin' => $origin,
            'destination' => $destination,
            'vehicle_id' => $vehicle_id,
            'distance' => $distance,
            'duration' => $duration,
            'fixed_price' => $fixed_price
        );
        
        $result = Travel_Booking_Route::update_predefined_route($route_id, $route_data);
        
        if ($result) {
            // Redirect to routes list with success message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-routes', 'message' => 'updated'),
                admin_url('admin.php')
            ));
            exit;
        } else {
            add_settings_error(
                'travel_booking_route',
                'update-failed',
                __('Failed to update route. Please try again.', 'travel-booking'),
                'error'
            );
        }
    }
    
    /**
     * Handle delete route action
     */
    private static function handle_delete_route($route_id) {
        $result = Travel_Booking_Route::delete_predefined_route($route_id);
        
        if ($result) {
            // Redirect to routes list with success message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-routes', 'message' => 'deleted'),
                admin_url('admin.php')
            ));
            exit;
        } else {
            add_settings_error(
                'travel_booking_route',
                'delete-failed',
                __('Failed to delete route. Please try again.', 'travel-booking'),
                'error'
            );
            
            // Redirect to routes list with error message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-routes', 'error' => 'delete-failed'),
                admin_url('admin.php')
            ));
            exit;
        }
    }
    
    /**
     * Display routes page
     */
    public static function display_page() {
        // Get current action
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        // Display appropriate page based on action
        switch ($action) {
            case 'new':
                self::display_new_route_page();
                break;
                
            case 'edit':
                self::display_edit_route_page();
                break;
                
            case 'list':
            default:
                self::display_routes_list_page();
                break;
        }
    }
    
    /**
     * Display routes list page
     */
    private static function display_routes_list_page() {
        // Get all routes
        $routes = Travel_Booking_Route::get_predefined_routes();
        
        // Get vehicles for reference
        $vehicles = array();
        $all_vehicles = Travel_Booking_Vehicle::get_all();
        
        foreach ($all_vehicles as $vehicle) {
            $vehicles[$vehicle->id] = $vehicle->name;
        }
        
        // Display admin notices
        settings_errors('travel_booking_route');
        
        // Success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            
            switch ($message) {
                case 'created':
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Route created successfully.', 'travel-booking') . '</p></div>';
                    break;
                    
                case 'updated':
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Route updated successfully.', 'travel-booking') . '</p></div>';
                    break;
                    
                case 'deleted':
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Route deleted successfully.', 'travel-booking') . '</p></div>';
                    break;
            }
        }
        
        // Display routes list template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/routes-list.php';
    }
    
    /**
     * Display new route page
     */
    private static function display_new_route_page() {
        // Get vehicles for dropdown
        $vehicles = Travel_Booking_Vehicle::get_all();
        
        // Display admin notices
        settings_errors('travel_booking_route');
        
        // Display new route form template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/route-new.php';
    }
    
    /**
     * Display edit route page
     */
    private static function display_edit_route_page() {
        // Get route ID
        $route_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($route_id <= 0) {
            echo '<div class="notice notice-error"><p>' . __('Invalid route ID.', 'travel-booking') . '</p></div>';
            return;
        }
        
        // Get route data
        $route = Travel_Booking_Route::get_predefined_route($route_id);
        
        if (!$route) {
            echo '<div class="notice notice-error"><p>' . __('Route not found.', 'travel-booking') . '</p></div>';
            return;
        }
        
        // Get vehicles for dropdown
        $vehicles = Travel_Booking_Vehicle::get_all();
        
        // Display admin notices
        settings_errors('travel_booking_route');
        
        // Display edit route form template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/route-edit.php';
    }
}