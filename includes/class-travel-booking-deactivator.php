<?php
/**
 * Plugin deactivation functionality
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Deactivator {
    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Additional cleanup if needed
        // Note: We won't delete tables here to preserve data
    }
}