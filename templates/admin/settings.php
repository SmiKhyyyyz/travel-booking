<?php
/**
 * Admin settings template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Travel Booking Settings', 'travel-booking'); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=travel-booking-settings&tab=general'); ?>" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'travel-booking'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=travel-booking-settings&tab=pages'); ?>" class="nav-tab <?php echo $current_tab === 'pages' ? 'nav-tab-active' : ''; ?>"><?php _e('Pages', 'travel-booking'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=travel-booking-settings&tab=woocommerce'); ?>" class="nav-tab <?php echo $current_tab === 'woocommerce' ? 'nav-tab-active' : ''; ?>"><?php _e('WooCommerce', 'travel-booking'); ?></a>
    </h2>
    
    <form method="post" action="options.php" class="travel-booking-form">
        <?php
        // Output security fields
        if ($current_tab === 'general') {
            settings_fields('travel_booking_general');
            do_settings_sections('travel_booking_general');
        } elseif ($current_tab === 'pages') {
            settings_fields('travel_booking_pages');
            do_settings_sections('travel_booking_pages');
        } elseif ($current_tab === 'woocommerce') {
            settings_fields('travel_booking_woocommerce');
            do_settings_sections('travel_booking_woocommerce');
        }
        
        // Output save button
        submit_button();
        ?>
    </form>
</div>