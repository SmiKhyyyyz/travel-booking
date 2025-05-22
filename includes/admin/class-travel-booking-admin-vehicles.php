<?php
/**
 * Admin vehicles management
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Admin_Vehicles {
    /**
     * Handle vehicle page actions
     */
    public static function page_actions() {
        // Handle form submissions
        if (isset($_POST['travel_booking_vehicle_action']) && isset($_POST['_wpnonce'])) {
            $action = sanitize_text_field($_POST['travel_booking_vehicle_action']);
            
            // Verify nonce
            if (!wp_verify_nonce($_POST['_wpnonce'], 'travel_booking_vehicle_' . $action)) {
                wp_die(__('Security check failed.', 'travel-booking'));
            }
            
            // Handle different actions
            switch ($action) {
                case 'create':
                    self::handle_create_vehicle();
                    break;
                    
                case 'update':
                    self::handle_update_vehicle();
                    break;
            }
        }
        
        // Handle delete action via GET request
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
            // Verify nonce
            if (!wp_verify_nonce($_GET['_wpnonce'], 'travel_booking_delete_vehicle_' . $_GET['id'])) {
                wp_die(__('Security check failed.', 'travel-booking'));
            }
            
            self::handle_delete_vehicle(intval($_GET['id']));
        }
        
        // Add scripts for media uploader
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_media_scripts'));
    }
    
    /**
     * Enqueue media scripts
     */
    public static function enqueue_media_scripts() {
        wp_enqueue_media();
        
        wp_enqueue_script(
            'travel-booking-admin-media',
            TRAVEL_BOOKING_PLUGIN_URL . 'assets/js/admin-media.js',
            array('jquery'),
            TRAVEL_BOOKING_VERSION,
            true
        );
    }
    
    /**
     * Handle create vehicle action
     */
    private static function handle_create_vehicle() {
        // Validate and sanitize input fields
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
        $price_per_km = isset($_POST['price_per_km']) ? floatval($_POST['price_per_km']) : 0;
        $base_price = isset($_POST['base_price']) ? floatval($_POST['base_price']) : 0;
        
        // Validate required fields
        if (empty($name) || $capacity <= 0 || empty($image_url) || $price_per_km <= 0 || $base_price <= 0) {
            add_settings_error(
                'travel_booking_vehicle',
                'required-fields',
                __('Please fill in all required fields with valid values.', 'travel-booking'),
                'error'
            );
            return;
        }
        
        // Create vehicle
        $vehicle_data = array(
            'name' => $name,
            'capacity' => $capacity,
            'description' => $description,
            'image_url' => $image_url,
            'price_per_km' => $price_per_km,
            'base_price' => $base_price
        );
        
        $result = Travel_Booking_Vehicle::add($vehicle_data);
        
        if ($result) {
            // Redirect to vehicles list with success message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-vehicles', 'message' => 'created'),
                admin_url('admin.php')
            ));
            exit;
        } else {
            add_settings_error(
                'travel_booking_vehicle',
                'create-failed',
                __('Failed to create vehicle. Please try again.', 'travel-booking'),
                'error'
            );
        }
    }
    
    /**
     * Handle update vehicle action
     */
    private static function handle_update_vehicle() {
        // Get vehicle ID
        $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
        
        if ($vehicle_id <= 0) {
            add_settings_error(
                'travel_booking_vehicle',
                'invalid-id',
                __('Invalid vehicle ID.', 'travel-booking'),
                'error'
            );
            return;
        }
        
        // Validate and sanitize input fields
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
        $price_per_km = isset($_POST['price_per_km']) ? floatval($_POST['price_per_km']) : 0;
        $base_price = isset($_POST['base_price']) ? floatval($_POST['base_price']) : 0;
        
        // Validate required fields
        if (empty($name) || $capacity <= 0 || empty($image_url) || $price_per_km <= 0 || $base_price <= 0) {
            add_settings_error(
                'travel_booking_vehicle',
                'required-fields',
                __('Please fill in all required fields with valid values.', 'travel-booking'),
                'error'
            );
            return;
        }
        
        // Update vehicle
        $vehicle_data = array(
            'name' => $name,
            'capacity' => $capacity,
            'description' => $description,
            'image_url' => $image_url,
            'price_per_km' => $price_per_km,
            'base_price' => $base_price
        );
        
        $result = Travel_Booking_Vehicle::update($vehicle_id, $vehicle_data);
        
        if ($result) {
            // Redirect to vehicles list with success message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-vehicles', 'message' => 'updated'),
                admin_url('admin.php')
            ));
            exit;
        } else {
            add_settings_error(
                'travel_booking_vehicle',
                'update-failed',
                __('Failed to update vehicle. Please try again.', 'travel-booking'),
                'error'
            );
        }
    }
    
    /**
     * Handle delete vehicle action
     */
    private static function handle_delete_vehicle($vehicle_id) {
        $result = Travel_Booking_Vehicle::delete($vehicle_id);
        
        if ($result) {
            // Redirect to vehicles list with success message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-vehicles', 'message' => 'deleted'),
                admin_url('admin.php')
            ));
            exit;
        } else {
            add_settings_error(
                'travel_booking_vehicle',
                'delete-failed',
                __('Failed to delete vehicle. Please try again.', 'travel-booking'),
                'error'
            );
            
            // Redirect to vehicles list with error message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-vehicles', 'error' => 'delete-failed'),
                admin_url('admin.php')
            ));
            exit;
        }
    }
    
    /**
     * Display vehicles page
     */
    public static function display_page() {
        // Get current action
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        // Display appropriate page based on action
        switch ($action) {
            case 'new':
                self::display_new_vehicle_page();
                break;
                
            case 'edit':
                self::display_edit_vehicle_page();
                break;
                
            case 'list':
            default:
                self::display_vehicles_list_page();
                break;
        }
    }
    
    /**
     * Display vehicles list page
     */
    private static function display_vehicles_list_page() {
        // Get all vehicles
        $vehicles = Travel_Booking_Vehicle::get_all();
        
        // Display admin notices
        settings_errors('travel_booking_vehicle');
        
        // Success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            
            switch ($message) {
                case 'created':
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Vehicle created successfully.', 'travel-booking') . '</p></div>';
                    break;
                    
                case 'updated':
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Vehicle updated successfully.', 'travel-booking') . '</p></div>';
                    break;
                    
                case 'deleted':
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Vehicle deleted successfully.', 'travel-booking') . '</p></div>';
                    break;
            }
        }
        
        // Display vehicles list template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/vehicles-list.php';
    }
    
    /**
     * Display new vehicle page
     */
    private static function display_new_vehicle_page() {
        // Display admin notices
        settings_errors('travel_booking_vehicle');
        
        // Display new vehicle form template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/vehicle-new.php';
    }
    
    /**
     * Display edit vehicle page
     */
    private static function display_edit_vehicle_page() {
        // Get vehicle ID
        $vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($vehicle_id <= 0) {
            echo '<div class="notice notice-error"><p>' . __('Invalid vehicle ID.', 'travel-booking') . '</p></div>';
            return;
        }
        
        // Get vehicle data
        $vehicle = Travel_Booking_Vehicle::get($vehicle_id);
        
        if (!$vehicle) {
            echo '<div class="notice notice-error"><p>' . __('Vehicle not found.', 'travel-booking') . '</p></div>';
            return;
        }
        
        // Display admin notices
        settings_errors('travel_booking_vehicle');
        
        // Display edit vehicle form template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/vehicle-edit.php';
    }
}