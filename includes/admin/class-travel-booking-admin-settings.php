<?php
/**
 * Admin settings management - VERSION CORRIGÉE
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Admin_Settings {
    /**
     * Handle settings page actions
     */
    public static function page_actions() {
        // Register settings - GÉNÉRAL
        register_setting('travel_booking_general', 'travel_booking_google_maps_api_key');
        register_setting('travel_booking_general', 'travel_booking_default_location');
        
        // Register settings sections - GÉNÉRAL
        add_settings_section(
            'travel_booking_general_section',
            __('General Settings', 'travel-booking'),
            array(__CLASS__, 'general_section_callback'),
            'travel_booking_general'
        );
        
        // Register settings fields - GÉNÉRAL
        add_settings_field(
            'travel_booking_google_maps_api_key',
            __('Google Maps API Key', 'travel-booking'),
            array(__CLASS__, 'google_maps_api_key_callback'),
            'travel_booking_general',
            'travel_booking_general_section'
        );
        
        add_settings_field(
            'travel_booking_default_location',
            __('Default Location', 'travel-booking'),
            array(__CLASS__, 'default_location_callback'),
            'travel_booking_general',
            'travel_booking_general_section'
        );
        
        // Page selection settings - PAGES
        register_setting('travel_booking_pages', 'travel_booking_booking_page_id');
        register_setting('travel_booking_pages', 'travel_booking_summary_page_id');
        
        add_settings_section(
            'travel_booking_pages_section',
            __('Page Settings', 'travel-booking'),
            array(__CLASS__, 'pages_section_callback'),
            'travel_booking_pages'
        );
        
        add_settings_field(
            'travel_booking_booking_page_id',
            __('Booking Form Page', 'travel-booking'),
            array(__CLASS__, 'booking_page_callback'),
            'travel_booking_pages',
            'travel_booking_pages_section'
        );
        
        add_settings_field(
            'travel_booking_summary_page_id',
            __('Booking Summary Page', 'travel-booking'),
            array(__CLASS__, 'summary_page_callback'),
            'travel_booking_pages',
            'travel_booking_pages_section'
        );
        
        // WooCommerce settings - WOOCOMMERCE
        register_setting('travel_booking_woocommerce', 'travel_booking_product_id');
        
        add_settings_section(
            'travel_booking_woocommerce_section',
            __('WooCommerce Settings', 'travel-booking'),
            array(__CLASS__, 'woocommerce_section_callback'),
            'travel_booking_woocommerce'
        );
        
        add_settings_field(
            'travel_booking_product_id',
            __('Booking Product', 'travel-booking'),
            array(__CLASS__, 'product_id_callback'),
            'travel_booking_woocommerce',
            'travel_booking_woocommerce_section'
        );
        
        // ===== AJOUT DE LA SECTION EMAILS =====
        // Register settings - EMAILS
        register_setting('travel_booking_emails', 'travel_booking_email_logo');
        register_setting('travel_booking_emails', 'travel_booking_email_from_name');
        register_setting('travel_booking_emails', 'travel_booking_email_from_email');
        register_setting('travel_booking_emails', 'travel_booking_email_footer_text');
        
        add_settings_section(
            'travel_booking_emails_section',
            __('Email Settings', 'travel-booking'),
            array(__CLASS__, 'emails_section_callback'),
            'travel_booking_emails'
        );

        // Champs pour les emails
        add_settings_field(
            'travel_booking_email_logo',
            __('Email Logo URL', 'travel-booking'),
            array(__CLASS__, 'email_logo_callback'),
            'travel_booking_emails',
            'travel_booking_emails_section'
        );
        
        add_settings_field(
            'travel_booking_email_from_name',
            __('From Name', 'travel-booking'),
            array(__CLASS__, 'email_from_name_callback'),
            'travel_booking_emails',
            'travel_booking_emails_section'
        );
        
        add_settings_field(
            'travel_booking_email_from_email',
            __('From Email', 'travel-booking'),
            array(__CLASS__, 'email_from_email_callback'),
            'travel_booking_emails',
            'travel_booking_emails_section'
        );
        
        add_settings_field(
            'travel_booking_email_footer_text',
            __('Footer Text', 'travel-booking'),
            array(__CLASS__, 'email_footer_text_callback'),
            'travel_booking_emails',
            'travel_booking_emails_section'
        );
    }
    
    /**
     * Display the settings page
     */
    public static function display_page() {
        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        // Display settings page template
        include TRAVEL_BOOKING_PLUGIN_DIR . 'templates/admin/settings.php';
    }
    
    /**
     * General section callback
     */
    public static function general_section_callback() {
        echo '<p>' . __('Configure general settings for the travel booking system.', 'travel-booking') . '</p>';
    }
    
    /**
     * Pages section callback
     */
    public static function pages_section_callback() {
        echo '<p>' . __('Select the pages where the booking form and summary will be displayed.', 'travel-booking') . '</p>';
    }
    
    /**
     * WooCommerce section callback
     */
    public static function woocommerce_section_callback() {
        echo '<p>' . __('Configure WooCommerce integration settings.', 'travel-booking') . '</p>';
    }
    
    /**
     * Emails section callback
     */
    public static function emails_section_callback() {
        echo '<p>' . __('Configure email settings for booking confirmations and notifications.', 'travel-booking') . '</p>';
    }
    
    /**
     * Google Maps API Key field callback
     */
    public static function google_maps_api_key_callback() {
        $api_key = get_option('travel_booking_google_maps_api_key', '');
        
        echo '<input type="text" name="travel_booking_google_maps_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
        echo '<p class="description">' . __('Enter your Google Maps API key. This is required for map and route functionality.', 'travel-booking') . '</p>';
        echo '<p class="description"><a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">' . __('How to get a Google Maps API key', 'travel-booking') . '</a></p>';
    }
    
    /**
     * Default Location field callback
     */
    public static function default_location_callback() {
        $default_location = get_option('travel_booking_default_location', 'Geneva, Switzerland');
        
        echo '<input type="text" name="travel_booking_default_location" value="' . esc_attr($default_location) . '" class="regular-text">';
        echo '<p class="description">' . __('Enter the default location to center the map on.', 'travel-booking') . '</p>';
    }
    
    /**
     * Booking Page field callback
     */
    public static function booking_page_callback() {
        $booking_page_id = get_option('travel_booking_booking_page_id', 0);
        
        wp_dropdown_pages(array(
            'name' => 'travel_booking_booking_page_id',
            'selected' => $booking_page_id,
            'show_option_none' => __('-- Select a page --', 'travel-booking')
        ));
        
        echo '<p class="description">' . __('Select the page where the booking form will be displayed. Add the [travel_booking_form] shortcode to this page.', 'travel-booking') . '</p>';
        
        if ($booking_page_id > 0) {
            echo '<p><a href="' . get_permalink($booking_page_id) . '" target="_blank">' . __('View Page', 'travel-booking') . '</a> | ';
            echo '<a href="' . admin_url('post.php?post=' . $booking_page_id . '&action=edit') . '">' . __('Edit Page', 'travel-booking') . '</a></p>';
        } else {
            echo '<p><a href="' . admin_url('post-new.php?post_type=page') . '" class="button">' . __('Create New Page', 'travel-booking') . '</a></p>';
        }
    }
    
    /**
     * Summary Page field callback
     */
    public static function summary_page_callback() {
        $summary_page_id = get_option('travel_booking_summary_page_id', 0);
        
        wp_dropdown_pages(array(
            'name' => 'travel_booking_summary_page_id',
            'selected' => $summary_page_id,
            'show_option_none' => __('-- Select a page --', 'travel-booking')
        ));
        
        echo '<p class="description">' . __('Select the page where the booking summary will be displayed. Add the [travel_booking_summary] shortcode to this page.', 'travel-booking') . '</p>';
        
        if ($summary_page_id > 0) {
            echo '<p><a href="' . get_permalink($summary_page_id) . '" target="_blank">' . __('View Page', 'travel-booking') . '</a> | ';
            echo '<a href="' . admin_url('post.php?post=' . $summary_page_id . '&action=edit') . '">' . __('Edit Page', 'travel-booking') . '</a></p>';
        } else {
            echo '<p><a href="' . admin_url('post-new.php?post_type=page') . '" class="button">' . __('Create New Page', 'travel-booking') . '</a></p>';
        }
    }

    /**
     * Email logo callback
     */
    public static function email_logo_callback() {
        $logo_url = get_option('travel_booking_email_logo', '');
        
        echo '<input type="url" name="travel_booking_email_logo" value="' . esc_attr($logo_url) . '" class="regular-text">';
        echo '<p class="description">' . __('URL of your company logo for emails (optional).', 'travel-booking') . '</p>';
    }
    
    /**
     * Email from name callback
     */
    public static function email_from_name_callback() {
        $from_name = get_option('travel_booking_email_from_name', get_bloginfo('name'));
        
        echo '<input type="text" name="travel_booking_email_from_name" value="' . esc_attr($from_name) . '" class="regular-text">';
        echo '<p class="description">' . __('The name that will appear as sender in emails.', 'travel-booking') . '</p>';
    }
    
    /**
     * Email from email callback
     */
    public static function email_from_email_callback() {
        $from_email = get_option('travel_booking_email_from_email', get_option('admin_email'));
        
        echo '<input type="email" name="travel_booking_email_from_email" value="' . esc_attr($from_email) . '" class="regular-text">';
        echo '<p class="description">' . __('The email address that will appear as sender.', 'travel-booking') . '</p>';
    }
    
    /**
     * Email footer text callback
     */
    public static function email_footer_text_callback() {
        $footer_text = get_option('travel_booking_email_footer_text', '');
        
        echo '<textarea name="travel_booking_email_footer_text" rows="3" class="large-text">' . esc_textarea($footer_text) . '</textarea>';
        echo '<p class="description">' . __('Additional text to display in email footer (optional).', 'travel-booking') . '</p>';
    }
    
    /**
     * Product ID field callback
     */
    public static function product_id_callback() {
        $product_id = get_option('travel_booking_product_id', 0);
        
        // Get WooCommerce products
        $products = array();
        
        if (class_exists('WooCommerce')) {
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            );
            
            $products_query = new WP_Query($args);
            
            if ($products_query->have_posts()) {
                while ($products_query->have_posts()) {
                    $products_query->the_post();
                    $products[get_the_ID()] = get_the_title();
                }
                
                wp_reset_postdata();
            }
        }
        
        if (!empty($products)) {
            echo '<select name="travel_booking_product_id">';
            echo '<option value="">' . __('-- Select a product --', 'travel-booking') . '</option>';
            
            foreach ($products as $id => $name) {
                echo '<option value="' . esc_attr($id) . '" ' . selected($product_id, $id, false) . '>' . esc_html($name) . '</option>';
            }
            
            echo '</select>';
            
            if ($product_id > 0) {
                echo '<p><a href="' . get_permalink($product_id) . '" target="_blank">' . __('View Product', 'travel-booking') . '</a> | ';
                echo '<a href="' . admin_url('post.php?post=' . $product_id . '&action=edit') . '">' . __('Edit Product', 'travel-booking') . '</a></p>';
            }
        } else {
            echo '<p>' . __('No products found. Please create a product in WooCommerce first.', 'travel-booking') . '</p>';
            echo '<p><a href="' . admin_url('post-new.php?post_type=product') . '" class="button">' . __('Create New Product', 'travel-booking') . '</a></p>';
        }
        
        echo '<p class="description">' . __('Select the WooCommerce product to use for bookings. If none is selected, a product will be created automatically.', 'travel-booking') . '</p>';
    }
}