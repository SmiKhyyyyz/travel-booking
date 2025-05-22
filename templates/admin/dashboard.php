<?php
/**
 * Admin dashboard template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Travel Booking Dashboard', 'travel-booking'); ?></h1>
    
    <div class="travel-booking-stats">
        <div class="travel-booking-stat-box pending">
            <h3><?php _e('Pending Bookings', 'travel-booking'); ?></h3>
            <div class="stat-value"><?php echo $pending_count; ?></div>
            <a href="<?php echo admin_url('admin.php?page=travel-booking-bookings&status=pending'); ?>"><?php _e('View all', 'travel-booking'); ?></a>
        </div>
        
        <div class="travel-booking-stat-box confirmed">
            <h3><?php _e('Confirmed Bookings', 'travel-booking'); ?></h3>
            <div class="stat-value"><?php echo $confirmed_count; ?></div>
            <a href="<?php echo admin_url('admin.php?page=travel-booking-bookings&status=confirmed'); ?>"><?php _e('View all', 'travel-booking'); ?></a>
        </div>
        
        <div class="travel-booking-stat-box completed">
            <h3><?php _e('Completed Bookings', 'travel-booking'); ?></h3>
            <div class="stat-value"><?php echo $completed_count; ?></div>
            <a href="<?php echo admin_url('admin.php?page=travel-booking-bookings&status=completed'); ?>"><?php _e('View all', 'travel-booking'); ?></a>
        </div>
        
        <div class="travel-booking-stat-box cancelled">
        <h3><?php _e('Cancelled Bookings', 'travel-booking'); ?></h3>
            <div class="stat-value"><?php echo $cancelled_count; ?></div>
            <a href="<?php echo admin_url('admin.php?page=travel-booking-bookings&status=cancelled'); ?>"><?php _e('View all', 'travel-booking'); ?></a>
        </div>
    </div>
    
    <div class="card">
        <h2><?php _e('Recent Bookings', 'travel-booking'); ?></h2>
        
        <?php if (empty($recent_bookings)) : ?>
            <p><?php _e('No bookings found.', 'travel-booking'); ?></p>
        <?php else : ?>
            <table class="travel-booking-table">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'travel-booking'); ?></th>
                        <th><?php _e('Customer', 'travel-booking'); ?></th>
                        <th><?php _e('From', 'travel-booking'); ?></th>
                        <th><?php _e('To', 'travel-booking'); ?></th>
                        <th><?php _e('Date', 'travel-booking'); ?></th>
                        <th><?php _e('Status', 'travel-booking'); ?></th>
                        <th><?php _e('Actions', 'travel-booking'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_bookings as $booking) : ?>
                        <tr>
                            <td>#<?php echo $booking->id; ?></td>
                            <td>
                                <?php
                                $customer_name = trim($booking->client_first_name . ' ' . $booking->client_last_name);
                                echo !empty($customer_name) ? esc_html($customer_name) : '—';
                                
                                if (!empty($booking->client_email)) {
                                    echo '<br><small>' . esc_html($booking->client_email) . '</small>';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html($booking->departure); ?></td>
                            <td><?php echo esc_html($booking->destination); ?></td>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->travel_date)); ?></td>
                            <td>
                                <span class="status-indicator status-<?php echo esc_attr($booking->status); ?>">
                                    <?php echo esc_html($booking->status); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=travel-booking-bookings&action=view&id=' . $booking->id); ?>" class="button">
                                    <?php _e('View', 'travel-booking'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p>
                <a href="<?php echo admin_url('admin.php?page=travel-booking-bookings'); ?>" class="button">
                    <?php _e('View All Bookings', 'travel-booking'); ?>
                </a>
            </p>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h2><?php _e('Quick Links', 'travel-booking'); ?></h2>
        
        <p>
            <a href="<?php echo admin_url('admin.php?page=travel-booking-vehicles&action=new'); ?>" class="button">
                <?php _e('Add New Vehicle', 'travel-booking'); ?>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=travel-booking-routes&action=new'); ?>" class="button">
                <?php _e('Add New Route', 'travel-booking'); ?>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=travel-booking-settings'); ?>" class="button">
                <?php _e('Configure Settings', 'travel-booking'); ?>
            </a>
        </p>
    </div>
</div>