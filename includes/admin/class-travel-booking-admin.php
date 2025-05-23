<?php
/**
 * Admin functionality
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Admin {
    /**
     * Plugin version
     */
    private $version;
    
    /**
     * Initialize the class and set its properties.
     */
    public function __construct($version) {
        $this->version = $version;
        
        // Include admin sub-pages
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/admin/class-travel-booking-admin-vehicles.php';
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/admin/class-travel-booking-admin-routes.php';
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/admin/class-travel-booking-admin-bookings.php';
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/admin/class-travel-booking-admin-settings.php';
    }
    
    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style('travel-booking-admin', TRAVEL_BOOKING_PLUGIN_URL . 'assets/css/travel-booking-admin.css', array(), $this->version, 'all');
    }
    
    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script('travel-booking-admin', TRAVEL_BOOKING_PLUGIN_URL . 'assets/js/travel-booking-admin.js', array('jquery'), $this->version, false);
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Travel Booking', 'travel-booking'),
            __('Travel Booking', 'travel-booking'),
            'manage_options',
            'travel-booking',
            array($this, 'display_dashboard_page'),
            'dashicons-car',
            30
        );
        
        // Dashboard
        add_submenu_page(
            'travel-booking',
            __('Dashboard', 'travel-booking'),
            __('Dashboard', 'travel-booking'),
            'manage_options',
            'travel-booking',
            array($this, 'display_dashboard_page')
        );
        
        // Vehicles
        $vehicles_page = add_submenu_page(
            'travel-booking',
            __('Vehicles', 'travel-booking'),
            __('Vehicles', 'travel-booking'),
            'manage_options',
            'travel-booking-vehicles',
            array('Travel_Booking_Admin_Vehicles', 'display_page')
        );
        
        // Add actions specific to the vehicles page
        add_action('load-' . $vehicles_page, array('Travel_Booking_Admin_Vehicles', 'page_actions'));
        
        // Routes
        $routes_page = add_submenu_page(
            'travel-booking',
            __('Routes', 'travel-booking'),
            __('Routes', 'travel-booking'),
            'manage_options',
            'travel-booking-routes',
            array('Travel_Booking_Admin_Routes', 'display_page')
        );
        
        // Add actions specific to the routes page
        add_action('load-' . $routes_page, array('Travel_Booking_Admin_Routes', 'page_actions'));
        
        // Bookings
        $bookings_page = add_submenu_page(
            'travel-booking',
            __('Bookings', 'travel-booking'),
            __('Bookings', 'travel-booking'),
            'manage_options',
            'travel-booking-bookings',
            array('Travel_Booking_Admin_Bookings', 'display_page')
        );
        
        // Add actions specific to the bookings page
        add_action('load-' . $bookings_page, array('Travel_Booking_Admin_Bookings', 'page_actions'));
        
        // Settings
        $settings_page = add_submenu_page(
            'travel-booking',
            __('Settings', 'travel-booking'),
            __('Settings', 'travel-booking'),
            'manage_options',
            'travel-booking-settings',
            array('Travel_Booking_Admin_Settings', 'display_page')
        );
        
        add_action('load-' . $settings_page, array('Travel_Booking_Admin_Settings', 'page_actions'));
        
        add_action('admin_init', array('Travel_Booking_Admin_Settings', 'page_actions'));
    }
    
    /**
     * Display the dashboard page
     */
    public function display_dashboard_page() {
        // Get counts
        $pending_count = Travel_Booking_Booking::count_by_status('pending');
        $confirmed_count = Travel_Booking_Booking::count_by_status('confirmed');
        $completed_count = Travel_Booking_Booking::count_by_status('completed');
        $cancelled_count = Travel_Booking_Booking::count_by_status('cancelled');
        
        // Get recent bookings
        $recent_bookings = Travel_Booking_Booking::get_all(array(
            'limit' => 5,
            'orderby' => 'id',
            'order' => 'DESC'
        ));
        
        // Include template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }
}