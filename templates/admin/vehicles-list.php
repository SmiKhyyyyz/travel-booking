<?php
/**
 * Admin vehicles list template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Vehicles', 'travel-booking'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=travel-booking-vehicles&action=new'); ?>" class="page-title-action"><?php _e('Add New', 'travel-booking'); ?></a>
    <hr class="wp-header-end">
    
    <?php if (empty($vehicles)) : ?>
        <div class="notice notice-info">
            <p><?php _e('No vehicles found. Click the "Add New" button to create one.', 'travel-booking'); ?></p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-image"><?php _e('Image', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-name"><?php _e('Name', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-capacity"><?php _e('Capacity', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-price"><?php _e('Price per km', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-base-price"><?php _e('Base Price', 'travel-booking'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'travel-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $vehicle) : ?>
                    <tr>
                        <td class="column-image">
                            <?php if (!empty($vehicle->image_url)) : ?>
                                <img src="<?php echo esc_url($vehicle->image_url); ?>" alt="<?php echo esc_attr($vehicle->name); ?>" width="60" height="60">
                            <?php else : ?>
                                <span class="dashicons dashicons-car"></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-name">
                            <strong><?php echo esc_html($vehicle->name); ?></strong>
                            <?php if (!empty($vehicle->description)) : ?>
                                <p class="description"><?php echo esc_html(wp_trim_words($vehicle->description, 10)); ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="column-capacity">
                            <?php echo esc_html($vehicle->capacity); ?> <?php _e('passengers', 'travel-booking'); ?>
                        </td>
                        <td class="column-price">
                            <?php echo esc_html(number_format($vehicle->price_per_km, 2)); ?> <?php _e('per km', 'travel-booking'); ?>
                        </td>
                        <td class="column-base-price">
                            <?php echo esc_html(number_format($vehicle->base_price, 2)); ?>
                        </td>
                        <td class="column-actions">
                            <a href="<?php echo admin_url('admin.php?page=travel-booking-vehicles&action=edit&id=' . $vehicle->id); ?>" class="button"><?php _e('Edit', 'travel-booking'); ?></a>
                            
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travel-booking-vehicles&action=delete&id=' . $vehicle->id), 'travel_booking_delete_vehicle_' . $vehicle->id); ?>" class="button" onclick="return confirm('<?php _e('Are you sure you want to delete this vehicle?', 'travel-booking'); ?>');"><?php _e('Delete', 'travel-booking'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>