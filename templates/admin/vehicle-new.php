<?php
/**
 * Admin new vehicle template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Localize media uploader script
wp_localize_script('travel-booking-admin-media', 'travelBookingAdminMedia', array(
    'title' => __('Select Vehicle Image', 'travel-booking'),
    'button' => __('Use this image', 'travel-booking')
));
?>

<div class="wrap">
    <h1><?php _e('Add New Vehicle', 'travel-booking'); ?></h1>
    
    <form method="post" action="" class="travel-booking-form">
        <?php wp_nonce_field('travel_booking_vehicle_create'); ?>
        <input type="hidden" name="travel_booking_vehicle_action" value="create">
        
        <div class="form-row">
            <label for="name"><?php _e('Name', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="text" name="name" id="name" value="" required>
            <p class="description"><?php _e('Enter the vehicle name (e.g. Sedan, SUV, etc.).', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="capacity"><?php _e('Capacity', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="number" name="capacity" id="capacity" value="1" min="1" required>
            <p class="description"><?php _e('Maximum number of passengers.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="description"><?php _e('Description', 'travel-booking'); ?></label>
            <textarea name="description" id="description"></textarea>
            <p class="description"><?php _e('Enter a description of the vehicle.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="image_url"><?php _e('Vehicle Image', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="url" name="image_url" id="image_url" value="" required>
            <button class="button travel-booking-upload-button" data-target="image_url"><?php _e('Upload Image', 'travel-booking'); ?></button>
            <button class="button travel-booking-remove-button" data-target="image_url" style="display:none;"><?php _e('Remove Image', 'travel-booking'); ?></button>
            <p class="description"><?php _e('Upload or select an image for this vehicle.', 'travel-booking'); ?></p>
            <img id="image_url-preview" src="" alt="" class="vehicle-image-preview" style="display:none;">
        </div>
        
        <div class="form-row">
            <label for="price_per_km"><?php _e('Price per km', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="number" name="price_per_km" id="price_per_km" value="0" min="0" step="0.01" required>
            <p class="description"><?php _e('Enter the price per kilometer.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="base_price"><?php _e('Base Price', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="number" name="base_price" id="base_price" value="0" min="0" step="0.01" required>
            <p class="description"><?php _e('Enter the minimum price for any booking (will be used if calculated price is lower).', 'travel-booking'); ?></p>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Add Vehicle', 'travel-booking'); ?>">
            <a href="<?php echo admin_url('admin.php?page=travel-booking-vehicles'); ?>" class="button"><?php _e('Cancel', 'travel-booking'); ?></a>
        </p>
    </form>
</div>