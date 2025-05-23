<?php
/**
 * Plugin activation functionality
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Activator {
    /**
     * Activate the plugin
     */
    public static function activate() {
        global $wpdb;
        
        // Get WordPress database charset
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table names
        $vehicles_table = $wpdb->prefix . 'travel_booking_vehicles';
        $rates_table = $wpdb->prefix . 'travel_booking_rates';
        $bookings_table = $wpdb->prefix . 'travel_booking_bookings';
        
        // SQL to create vehicles table
        $vehicles_sql = "CREATE TABLE $vehicles_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            capacity int(11) NOT NULL,
            description text,
            image_url varchar(255),
            price_per_km decimal(10,2) NOT NULL,
            base_price decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // SQL to create rates table
        $rates_sql = "CREATE TABLE $rates_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            origin varchar(255) NOT NULL,
            destination varchar(255) NOT NULL,
            vehicle_id bigint(20) NOT NULL,
            distance decimal(10,2) NOT NULL,
            duration decimal(10,2) NOT NULL,
            fixed_price decimal(10,2),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY vehicle_id (vehicle_id)
        ) $charset_collate;";
        
        // SQL to create bookings table
        $bookings_sql = "CREATE TABLE $bookings_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vehicle_id bigint(20) NOT NULL,
            order_id bigint(20),
            session_token varchar(64) NOT NULL,
            client_first_name varchar(255),
            client_last_name varchar(255),
            client_email varchar(255),
            client_phone varchar(50),
            client_address text,
            departure varchar(255) NOT NULL,
            destination varchar(255) NOT NULL,
            distance decimal(10,2) NOT NULL,
            duration decimal(10,2) NOT NULL,
            price decimal(10,2) NOT NULL,
            travel_date datetime NOT NULL,
            round_trip tinyint(1) DEFAULT 0,
            number_of_passengers int(11) DEFAULT 1,
            flight_number varchar(50),
            notes text,
            promo_code varchar(50),
            status varchar(50) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY vehicle_id (vehicle_id),
            KEY order_id (order_id),
            KEY session_token (session_token)
        ) $charset_collate;";
        
        // Execute the SQL to create tables
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($vehicles_sql);
        dbDelta($rates_sql);
        dbDelta($bookings_sql);
        
        // Add plugin version to options
        add_option('travel_booking_version', TRAVEL_BOOKING_VERSION);
        
        // Create default options
        add_option('travel_booking_google_maps_api_key', '');
        add_option('travel_booking_default_location', 'Geneva, Switzerland');
        add_option('travel_booking_booking_page_id', 0);
        
        // Insert sample vehicle data
        self::insert_sample_data();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create default pages
     */
    private static function create_default_pages() {
        // Create booking form page
        $booking_page = array(
            'post_title'    => __('Book a Trip', 'travel-booking'),
            'post_content'  => '[travel_booking_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'book-trip'
        );
        
        $booking_page_id = wp_insert_post($booking_page);
        if ($booking_page_id) {
            update_option('travel_booking_booking_page_id', $booking_page_id);
        }
        
        // Create booking summary page
        $summary_page = array(
            'post_title'    => __('Booking Summary', 'travel-booking'),
            'post_content'  => '[travel_booking_summary]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'booking-summary'
        );
        
        $summary_page_id = wp_insert_post($summary_page);
        if ($summary_page_id) {
            update_option('travel_booking_summary_page_id', $summary_page_id);
        }
    }

    
    /**
    * Insert sample vehicle data
    */
    private static function insert_sample_data() {
        global $wpdb;
        $vehicles_table = $wpdb->prefix . 'travel_booking_vehicles';
        
        // Check if the table is empty
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $vehicles_table");
        
        if ($count == 0) {
            // Insert sample vehicles with your specific images
            $sample_vehicles = [
                [
                    'name' => 'S-Class',
                    'capacity' => 3,
                    'description' => 'Comfortable S-Class luxury sedan for up to 3 passengers',
                    'image_url' => TRAVEL_BOOKING_PLUGIN_URL . 'assets/images/s-class.webp',
                    'price_per_km' => 6.67,
                    'base_price' => 420.00
                ],
                [
                    'name' => 'Maybach',
                    'capacity' => 3,
                    'description' => 'Ultimate luxury Maybach for up to 3 passengers',
                    'image_url' => TRAVEL_BOOKING_PLUGIN_URL . 'assets/images/maybach.webp',
                    'price_per_km' => 11.35,
                    'base_price' => 620.00
                ],
                [
                    'name' => 'V-Class',
                    'capacity' => 6,
                    'description' => 'Spacious V-Class for up to 6 passengers',
                    'image_url' => TRAVEL_BOOKING_PLUGIN_URL . 'assets/images/v-class.avif',
                    'price_per_km' => 6.00,
                    'base_price' => 378.00
                ],
                [
                    'name' => 'Sprinter',
                    'capacity' => 8,
                    'description' => 'Mercedes Sprinter for up to 8 passengers',
                    'image_url' => TRAVEL_BOOKING_PLUGIN_URL . 'assets/images/sprinter.png',
                    'price_per_km' => 11.13,
                    'base_price' => 450.00
                ],
                [
                    'name' => 'Rolls-Royce',
                    'capacity' => 4,
                    'description' => 'Exclusive Rolls-Royce for up to 4 passengers',
                    'image_url' => TRAVEL_BOOKING_PLUGIN_URL . 'assets/images/rolls.png',
                    'price_per_km' => 17.84,
                    'base_price' => 950.00
                ]
            ];
            
            foreach ($sample_vehicles as $vehicle) {
                $wpdb->insert($vehicles_table, $vehicle);
            }
        }
    }
}