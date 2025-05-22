<?php
/**
 * Admin routes list template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Predefined Routes', 'travel-booking'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=travel-booking-routes&action=new'); ?>" class="page-title-action"><?php _e('Add New', 'travel-booking'); ?></a>
    <hr class="wp-header-end">
    
    <?php if (empty($routes)) : ?>
        <div class="notice notice-info">
            <p><?php _e('No predefined routes found. Click the "Add New" button to create one.', 'travel-booking'); ?></p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-origin"><?php _e('Origin', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-destination"><?php _e('Destination', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-vehicle"><?php _e('Vehicle', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-distance"><?php _e('Distance', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-duration"><?php _e('Duration', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-price"><?php _e('Fixed Price', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'travel-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routes as $route) : ?>
                    <tr>
                        <td class="column-origin">
                            <?php echo esc_html($route->origin); ?>
                        </td>
                        <td class="column-destination">
                            <?php echo esc_html($route->destination); ?>
                        </td>
                        <td class="column-vehicle">
                            <?php echo isset($vehicles[$route->vehicle_id]) ? esc_html($vehicles[$route->vehicle_id]) : __('Unknown', 'travel-booking'); ?>
                        </td>
                        <td class="column-distance">
                            <?php echo esc_html(number_format($route->distance, 2)); ?> km
                        </td>
                        <td class="column-duration">
                            <?php echo esc_html(number_format($route->duration, 2)); ?> <?php _e('hours', 'travel-booking'); ?>
                        </td>
                        <td class="column-price">
                            <?php echo esc_html(number_format($route->fixed_price, 2)); ?>
                        </td>
                        <td class="column-actions">
                            <a href="<?php echo admin_url('admin.php?page=travel-booking-routes&action=edit&id=' . $route->id); ?>" class="button"><?php _e('Edit', 'travel-booking'); ?></a>
                            
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travel-booking-routes&action=delete&id=' . $route->id), 'travel_booking_delete_route_' . $route->id); ?>" class="button" onclick="return confirm('<?php _e('Are you sure you want to delete this route?', 'travel-booking'); ?>');"><?php _e('Delete', 'travel-booking'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>