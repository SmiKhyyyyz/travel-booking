<?php
/**
 * Admin booking view template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('View Booking', 'travel-booking'); ?> #<?php echo $booking->id; ?></h1>
    
    <div class="travel-booking-booking-details">
        <div class="card">
            <h2><?php _e('Booking Information', 'travel-booking'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Booking ID', 'travel-booking'); ?></th>
                    <td>#<?php echo esc_html($booking->id); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Status', 'travel-booking'); ?></th>
                    <td>
                        <span class="status-indicator status-<?php echo esc_attr($booking->status); ?>">
                            <?php echo esc_html($booking->status); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('From', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->departure); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('To', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->destination); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Travel Date', 'travel-booking'); ?></th>
                    <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->travel_date)); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Passengers', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->number_of_passengers); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Distance', 'travel-booking'); ?></th>
                    <td><?php echo esc_html(number_format($booking->distance, 2)); ?> km</td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Duration', 'travel-booking'); ?></th>
                    <td><?php echo esc_html(number_format($booking->duration, 2)); ?> <?php _e('hours', 'travel-booking'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Price', 'travel-booking'); ?></th>
                    <td><?php echo esc_html(number_format($booking->price, 2)); ?> CHF</td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Round Trip', 'travel-booking'); ?></th>
                    <td><?php echo $booking->round_trip ? __('Yes', 'travel-booking') : __('No', 'travel-booking'); ?></td>
                </tr>
                <?php if (!empty($booking->promo_code)) : ?>
                <tr>
                    <th scope="row"><?php _e('Promo Code', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->promo_code); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th scope="row"><?php _e('Created', 'travel-booking'); ?></th>
                    <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->created_at)); ?></td>
                </tr>
            </table>
        </div>

        <?php if (!empty($booking->client_first_name)) : ?>
        <div class="card">
            <h2><?php _e('Client Information', 'travel-booking'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Name', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->client_first_name . ' ' . $booking->client_last_name); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Email', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->client_email); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Phone', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->client_phone); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Address', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->client_address); ?></td>
                </tr>
                <?php if (!empty($booking->flight_number)) : ?>
                <tr>
                    <th scope="row"><?php _e('Flight Number', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->flight_number); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($booking->notes)) : ?>
                <tr>
                    <th scope="row"><?php _e('Notes', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($booking->notes); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php endif; ?>

        <?php if ($vehicle) : ?>
        <div class="card">
            <h2><?php _e('Vehicle Information', 'travel-booking'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Vehicle', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($vehicle->name); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Capacity', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($vehicle->capacity); ?> <?php _e('passengers', 'travel-booking'); ?></td>
                </tr>
                <?php if (!empty($vehicle->description)) : ?>
                <tr>
                    <th scope="row"><?php _e('Description', 'travel-booking'); ?></th>
                    <td><?php echo esc_html($vehicle->description); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($vehicle->image_url)) : ?>
                <tr>
                    <th scope="row"><?php _e('Image', 'travel-booking'); ?></th>
                    <td><img src="<?php echo esc_url($vehicle->image_url); ?>" alt="<?php echo esc_attr($vehicle->name); ?>" class="vehicle-image-preview" style="max-width: 200px;"></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($booking->order_id)) : ?>
        <div class="card">
            <h2><?php _e('WooCommerce Order', 'travel-booking'); ?></h2>
            
            <p>
                <a href="<?php echo admin_url('post.php?post=' . $booking->order_id . '&action=edit'); ?>" class="button button-primary">
                    <?php _e('View Order', 'travel-booking'); ?> #<?php echo $booking->order_id; ?>
                </a>
            </p>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2><?php _e('Actions', 'travel-booking'); ?></h2>
            
            <p>
                <a href="<?php echo admin_url('admin.php?page=travel-booking-bookings'); ?>" class="button">
                    &larr; <?php _e('Back to Bookings', 'travel-booking'); ?>
                </a>
                
                <?php if ($booking->status !== 'confirmed') : ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travel-booking-bookings&action=update_status&id=' . $booking->id . '&status=confirmed'), 'travel_booking_update_status_' . $booking->id); ?>" class="button button-primary">
                    <?php _e('Confirm Booking', 'travel-booking'); ?>
                </a>
                <?php endif; ?>
                
                <?php if ($booking->status !== 'cancelled') : ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travel-booking-bookings&action=update_status&id=' . $booking->id . '&status=cancelled'), 'travel_booking_update_status_' . $booking->id); ?>" class="button" onclick="return confirm('<?php _e('Are you sure you want to cancel this booking?', 'travel-booking'); ?>')">
                    <?php _e('Cancel Booking', 'travel-booking'); ?>
                </a>
                <?php endif; ?>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travel-booking-bookings&action=delete&id=' . $booking->id), 'travel_booking_delete_booking_' . $booking->id); ?>" class="button button-link-delete" onclick="return confirm('<?php _e('Are you sure you want to delete this booking? This action cannot be undone.', 'travel-booking'); ?>')">
                    <?php _e('Delete Booking', 'travel-booking'); ?>
                </a>
            </p>
        </div>
    </div>
</div>