<?php
/**
 * The main plugin class - MISE À JOUR AVEC ESPACE CLIENT
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
    
    // Flush rewrite rules if needed
    add_action('init', array($this, 'flush_rewrite_rules_maybe'), 20);
    
    add_action('send_headers', array($this, 'add_security_headers'));
    
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
     * Maybe flush rewrite rules for customer area endpoints
     */
    public function maybe_flush_rewrite_rules() {
        if (get_option('travel_booking_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
            delete_option('travel_booking_flush_rewrite_rules');
        }
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
        
        // Customer area
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/frontend/class-travel-booking-customer-area.php';
        
        // API integration
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-api.php';
        
        // WooCommerce integration
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-woocommerce.php';
        
        // Security
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-security.php';

        // Mail
        require_once TRAVEL_BOOKING_PLUGIN_DIR . 'includes/class-travel-booking-emails.php';
        
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
        $this->loader->add_action('admin_init', 'Travel_Booking_Admin_Settings', 'page_actions');

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
    
    // ✅ CORRECTION : Utilisez admin_init au lieu de wp_loaded
    $this->loader->add_action('admin_init', 'Travel_Booking_Admin_Settings', 'page_actions');
    
    // Support thème automatique (sans les réglages admin)
    $this->loader->add_action('after_setup_theme', $this, 'add_theme_support');
    $this->loader->add_action('after_switch_theme', $this, 'after_theme_switch');
    
    // Endpoints WooCommerce
    $this->loader->add_action('init', $this, 'add_woocommerce_endpoints', 0);
    
    // Body classes et assets conditionnels
    $this->loader->add_filter('body_class', $this, 'add_booking_body_classes');
    $this->loader->add_action('wp_enqueue_scripts', $this, 'conditional_enqueue', 15);
}

    /**
     * Flush rewrite rules on init if needed
     */
    public function flush_rewrite_rules_maybe() {
        if (get_option('travel_booking_needs_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('travel_booking_needs_flush_rewrite_rules');
        }
    }

    /**
     * Register shortcodes
     */
    private function define_shortcodes() {
        $shortcodes = new Travel_Booking_Shortcodes();
        $shortcodes->register_shortcodes();
    }

    public function security_audit() {
        $issues = array();
        
        // Vérifier les permissions des fichiers
        if (is_writable(TRAVEL_BOOKING_PLUGIN_DIR)) {
            $issues[] = 'Plugin directory is writable';
        }
        
        // Vérifier les clés API exposées
        $api_key = get_option('travel_booking_google_maps_api_key');
        if (!empty($api_key) && strlen($api_key) > 20) {
            // Tester les restrictions
            $test_url = "https://maps.googleapis.com/maps/api/geocode/json?address=test&key=" . $api_key;
            $response = wp_remote_get($test_url, array('headers' => array('Referer' => 'https://malicious-site.com')));
            
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($data['status']) && $data['status'] === 'OK') {
                    $issues[] = 'Google Maps API key is not properly restricted';
                }
            }
        }
        
        return $issues;
    }

    public function add_security_headers() {
        if (!is_admin()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }

    public function add_theme_support() {
    add_theme_support('woocommerce');
    add_theme_support('travel-booking');
    add_filter('widget_text', 'do_shortcode');
}

public function after_theme_switch() {
    flush_rewrite_rules();
}

public function add_woocommerce_endpoints() {
    add_rewrite_endpoint('travel-bookings', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('travel-profile', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('travel-favorites', EP_ROOT | EP_PAGES);
}

public function add_booking_body_classes($classes) {
    if (is_page()) {
        global $post;
        if (is_a($post, 'WP_Post')) {
            if (has_shortcode($post->post_content, 'travel_booking_form')) {
                $classes[] = 'travel-booking-form-page';
            }
            if (has_shortcode($post->post_content, 'travel_booking_summary')) {
                $classes[] = 'travel-booking-summary-page';
            }
        }
    }
    return $classes;
}

public function conditional_enqueue() {
    global $post;
    
    if (is_a($post, 'WP_Post') && (
        has_shortcode($post->post_content, 'travel_booking_form') || 
        has_shortcode($post->post_content, 'travel_booking_summary')
    )) {
        wp_enqueue_style('travel-booking');
        wp_enqueue_script('travel-booking');
    }
    
    if (is_account_page()) {
        wp_enqueue_style('travel-booking-customer');
        wp_enqueue_script('travel-booking-customer');
    }
}
}