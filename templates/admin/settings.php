<?php
/**
 * Admin settings template - VERSION CORRIGÉE AVEC ONGLET EMAILS
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Travel Booking Settings', 'travel-booking'); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=travel-booking-settings&tab=general'); ?>" 
           class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('General', 'travel-booking'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=travel-booking-settings&tab=pages'); ?>" 
           class="nav-tab <?php echo $current_tab === 'pages' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Pages', 'travel-booking'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=travel-booking-settings&tab=emails'); ?>" 
           class="nav-tab <?php echo $current_tab === 'emails' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Emails', 'travel-booking'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=travel-booking-settings&tab=woocommerce'); ?>" 
           class="nav-tab <?php echo $current_tab === 'woocommerce' ? 'nav-tab-active' : ''; ?>">
            <?php _e('WooCommerce', 'travel-booking'); ?>
        </a>
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
        } elseif ($current_tab === 'emails') {
            settings_fields('travel_booking_emails');
            do_settings_sections('travel_booking_emails');
        } elseif ($current_tab === 'woocommerce') {
            settings_fields('travel_booking_woocommerce');
            do_settings_sections('travel_booking_woocommerce');
        }
        
        // Output save button
        submit_button();
        ?>
    </form>
    
    <?php if ($current_tab === 'emails') : ?>
    <div class="card" style="margin-top: 20px;">
        <h2><?php _e('Email Preview', 'travel-booking'); ?></h2>
        <p><?php _e('Here\'s how your emails will look with the current settings:', 'travel-booking'); ?></p>
        
        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 10px 0;">
            <h4><?php _e('Booking Confirmation Email', 'travel-booking'); ?></h4>
            <p><strong><?php _e('From:', 'travel-booking'); ?></strong> <?php echo esc_html(get_option('travel_booking_email_from_name', get_bloginfo('name'))); ?> &lt;<?php echo esc_html(get_option('travel_booking_email_from_email', get_option('admin_email'))); ?>&gt;</p>
            <p><strong><?php _e('Subject:', 'travel-booking'); ?></strong> <?php _e('Confirmation of your transport booking', 'travel-booking'); ?></p>
            
            <?php if (get_option('travel_booking_email_logo')) : ?>
            <p><strong><?php _e('Logo:', 'travel-booking'); ?></strong> <img src="<?php echo esc_url(get_option('travel_booking_email_logo')); ?>" alt="Logo" style="max-height: 40px;"></p>
            <?php endif; ?>
            
            <?php if (get_option('travel_booking_email_footer_text')) : ?>
            <p><strong><?php _e('Footer:', 'travel-booking'); ?></strong> <?php echo esc_html(get_option('travel_booking_email_footer_text')); ?></p>
            <?php endif; ?>
        </div>
        
        <p class="description">
            <?php _e('Emails are automatically sent when:', 'travel-booking'); ?>
        </p>
        <ul style="margin-left: 20px;">
            <li>✅ <?php _e('A booking is confirmed (order status: processing)', 'travel-booking'); ?></li>
            <li>✅ <?php _e('A booking is completed (order status: completed)', 'travel-booking'); ?></li>
            <li>✅ <?php _e('A booking is cancelled (order status: cancelled)', 'travel-booking'); ?></li>
        </ul>
    </div>
    <?php endif; ?>
</div>