<?php
/**
 * Plugin Name: Travel Booking System
 * Description: A comprehensive travel booking system for transportation services with WooCommerce integration.
 * Version: 1.0.0
 * Author: Pixel Agency
 * Text Domain: travel-booking
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TRAVEL_BOOKING_VERSION', '1.0.0');
define('TRAVEL_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TRAVEL_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TRAVEL_BOOKING_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if WooCommerce is active
function travel_booking_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>';
            echo __('Travel Booking requires WooCommerce to be installed and activated.', 'travel-booking');
            echo '</p></div>';
        });
        return false;
    }
    return true;
}

// Plugin Initialization
function travel_booking_init() {
    // Check requirements
    if (!travel_booking_check_woocommerce()) {
        return;
    }
    
    // Load text domain for translation
    load_plugin_textdomain('travel-booking', false, dirname(TRAVEL_BOOKING_PLUGIN_BASENAME) . '/languages');
    
    // Include required files
    require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking.php';
    
    // Initialize main plugin class
    $travel_booking = new Travel_Booking();
    $travel_booking->init();
}
add_action('plugins_loaded', 'travel_booking_init');

// Activation Hook
function travel_booking_activate() {
    require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-activator.php';
    Travel_Booking_Activator::activate();
}
register_activation_hook(__FILE__, 'travel_booking_activate');

// Deactivation Hook
function travel_booking_deactivate() {
    require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-deactivator.php';
    Travel_Booking_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'travel_booking_deactivate');