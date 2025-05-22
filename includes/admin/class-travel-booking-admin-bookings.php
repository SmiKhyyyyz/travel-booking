<?php
/**
 * Admin bookings management
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Admin_Bookings {
    /**
     * Handle bookings page actions
     */
    public static function page_actions() {
        // Handle bulk actions FIRST
        if (isset($_POST['action']) || isset($_POST['action2'])) {
            $action = $_POST['action'] !== '-1' ? $_POST['action'] : $_POST['action2'];
            
            if ($action !== '-1' && !empty($_POST['booking_ids']) && wp_verify_nonce($_POST['travel_booking_bulk_nonce'], 'travel_booking_bulk_action')) {
                self::handle_bulk_action($action, $_POST['booking_ids']);
            }
        }
        
        // Handle status update action
        if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['status']) && isset($_GET['_wpnonce'])) {
            // Verify nonce
            if (!wp_verify_nonce($_GET['_wpnonce'], 'travel_booking_update_status_' . $_GET['id'])) {
                wp_die(__('Security check failed.', 'travel-booking'));
            }
            
            self::handle_update_status(intval($_GET['id']), sanitize_text_field($_GET['status']));
        }
        
        // Handle delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
            // Verify nonce
            if (!wp_verify_nonce($_GET['_wpnonce'], 'travel_booking_delete_booking_' . $_GET['id'])) {
                wp_die(__('Security check failed.', 'travel-booking'));
            }
            
            self::handle_delete_booking(intval($_GET['id']));
        }
    }
    
    /**
     * Handle bulk actions
     */
    private static function handle_bulk_action($action, $booking_ids) {
        $booking_ids = array_map('intval', $booking_ids);
        $success_count = 0;
        
        foreach ($booking_ids as $booking_id) {
            switch ($action) {
                case 'delete':
                    if (Travel_Booking_Booking::delete($booking_id)) {
                        $success_count++;
                    }
                    break;
                    
                case 'confirm':
                    if (Travel_Booking_Booking::update_status($booking_id, 'confirmed')) {
                        $success_count++;
                    }
                    break;
                    
                case 'cancel':
                    if (Travel_Booking_Booking::update_status($booking_id, 'cancelled')) {
                        $success_count++;
                    }
                    break;
            }
        }
        
        // Redirect with success message
        $message = '';
        switch ($action) {
            case 'delete':
                $message = sprintf(_n('%d booking deleted.', '%d bookings deleted.', $success_count, 'travel-booking'), $success_count);
                break;
            case 'confirm':
                $message = sprintf(_n('%d booking confirmed.', '%d bookings confirmed.', $success_count, 'travel-booking'), $success_count);
                break;
            case 'cancel':
                $message = sprintf(_n('%d booking cancelled.', '%d bookings cancelled.', $success_count, 'travel-booking'), $success_count);
                break;
        }
        
        wp_redirect(add_query_arg(
            array('page' => 'travel-booking-bookings', 'bulk_message' => urlencode($message)),
            admin_url('admin.php')
        ));
        exit;
    }
    
    /**
     * Handle update status action
     */
    private static function handle_update_status($booking_id, $status) {
        $valid_statuses = array('pending', 'confirmed', 'cancelled', 'completed');
        
        if (!in_array($status, $valid_statuses)) {
            add_settings_error(
                'travel_booking_booking',
                'invalid-status',
                __('Invalid status.', 'travel-booking'),
                'error'
            );
            return;
        }
        
        $result = Travel_Booking_Booking::update_status($booking_id, $status);
        
        if ($result) {
            // Redirect to bookings list with success message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-bookings', 'message' => 'status-updated'),
                admin_url('admin.php')
            ));
            exit;
        } else {
            add_settings_error(
                'travel_booking_booking',
                'update-status-failed',
                __('Failed to update booking status. Please try again.', 'travel-booking'),
                'error'
            );
            
            // Redirect to bookings list with error message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-bookings', 'error' => 'update-status-failed'),
                admin_url('admin.php')
            ));
            exit;
        }
    }
    
    /**
     * Handle delete booking action
     */
    private static function handle_delete_booking($booking_id) {
        $result = Travel_Booking_Booking::delete($booking_id);
        
        if ($result) {
            // Redirect to bookings list with success message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-bookings', 'message' => 'deleted'),
                admin_url('admin.php')
            ));
            exit;
        } else {
            add_settings_error(
                'travel_booking_booking',
                'delete-failed',
                __('Failed to delete booking. Please try again.', 'travel-booking'),
                'error'
            );
            
            // Redirect to bookings list with error message
            wp_redirect(add_query_arg(
                array('page' => 'travel-booking-bookings', 'error' => 'delete-failed'),
                admin_url('admin.php')
            ));
            exit;
        }
    }
    
    /**
     * Display bookings page
     */
    public static function display_page() {
        // Get current action
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        // Display appropriate page based on action
        switch ($action) {
            case 'view':
                self::display_view_booking_page();
                break;
                
            case 'list':
            default:
                self::display_bookings_list_page();
                break;
        }
    }
    
    /**
     * Display bookings list page
     */
    private static function display_bookings_list_page() {
        // Get filter parameters
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // Get bookings
        $args = array(
            'status' => $status,
            'search' => $search
        );
        
        $bookings = Travel_Booking_Booking::get_all($args);
        
        // Get vehicles for reference
        $vehicles = array();
        $all_vehicles = Travel_Booking_Vehicle::get_all();
        
        foreach ($all_vehicles as $vehicle) {
            $vehicles[$vehicle->id] = $vehicle->name;
        }
        
        // Display admin notices
        settings_errors('travel_booking_booking');
        
        // Success messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            
            switch ($message) {
                case 'status-updated':
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Booking status updated successfully.', 'travel-booking') . '</p></div>';
                    break;
                    
                case 'deleted':
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Booking deleted successfully.', 'travel-booking') . '</p></div>';
                    break;
            }
        }
        
        // Bulk action messages
        if (isset($_GET['bulk_message'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(urldecode($_GET['bulk_message'])) . '</p></div>';
        }
        
        // Display bookings list template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/bookings-list.php';
    }
    
    /**
     * Display view booking page
     */
    private static function display_view_booking_page() {
        // Get booking ID
        $booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($booking_id <= 0) {
            echo '<div class="notice notice-error"><p>' . __('Invalid booking ID.', 'travel-booking') . '</p></div>';
            return;
        }
        
        // Get booking data
        $booking = Travel_Booking_Booking::get($booking_id);
        
        if (!$booking) {
            echo '<div class="notice notice-error"><p>' . __('Booking not found.', 'travel-booking') . '</p></div>';
            return;
        }
        
        // Get vehicle data
        $vehicle = Travel_Booking_Vehicle::get($booking->vehicle_id);
        
        // Display admin notices
        settings_errors('travel_booking_booking');
        
        // Display view booking template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/booking-view.php';
    }
}