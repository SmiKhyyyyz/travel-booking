<?php
/**
 * The main plugin class
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking {
    /**
     * The instance of this class
     */
    private static $instance;

    /**
     * Plugin loader
     */
    private $loader;

    /**
     * Plugin version
     */
    private $version;

    /**
     * Initialize the plugin
     */
    public function init() {
        $this->version = TRAVEL_BOOKING_VERSION;
        
        // Add webp and avif MIME type support
        add_filter('upload_mimes', array($this, 'add_webp_avif_mime_types'));
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcodes();
        $this->loader->run();
    }

    /**
     * Add WebP and AVIF MIME types support
     */
    public function add_webp_avif_mime_types($mimes) {
        $mimes['webp'] = 'image/webp';
        $mimes['avif'] = 'image/avif';
        return $mimes;
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Include classes
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-loader.php';
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-vehicle.php';
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-route.php';
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-booking.php';
        
        // Admin and frontend dependencies
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/admin/class-travel-booking-admin.php';
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/frontend/class-travel-booking-frontend.php';
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/frontend/class-travel-booking-shortcodes.php';
        
        // API integration
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-api.php';
        
        // WooCommerce integration
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-woocommerce.php';
        
        // Security
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-security.php';
        
        // Create the loader
        $this->loader = new Travel_Booking_Loader();
    }

    /**
     * Register admin hooks
     */
    private function define_admin_hooks() {
        $admin = new Travel_Booking_Admin($this->version);
        
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $admin, 'add_admin_menu');
    }

    /**
     * Register public hooks
     */
    private function define_public_hooks() {
        $frontend = new Travel_Booking_Frontend($this->version);
        
        $this->loader->add_action('wp_enqueue_scripts', $frontend, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $frontend, 'enqueue_scripts');
        
        // Initialize WooCommerce integration
        $woocommerce = new Travel_Booking_WooCommerce();
        $this->loader->add_action('init', $woocommerce, 'init');
        
        // Initialize API routes
        $api = new Travel_Booking_API();
        $this->loader->add_action('rest_api_init', $api, 'register_routes');
    }

    /**
     * Register shortcodes
     */
    private function define_shortcodes() {
        $shortcodes = new Travel_Booking_Shortcodes();
        $shortcodes->register_shortcodes();
    }
}