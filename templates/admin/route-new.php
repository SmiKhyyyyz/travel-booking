<?php
/**
 * Admin new route template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Add New Route', 'travel-booking'); ?></h1>
    
    <form method="post" action="" class="travel-booking-form">
        <?php wp_nonce_field('travel_booking_route_create'); ?>
        <input type="hidden" name="travel_booking_route_action" value="create">
        
        <div class="form-row">
            <label for="origin"><?php _e('Origin', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="text" name="origin" id="origin" value="" required placeholder="<?php _e('e.g. Geneva, Switzerland', 'travel-booking'); ?>">
            <p class="description"><?php _e('Enter the departure location.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="destination"><?php _e('Destination', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="text" name="destination" id="destination" value="" required placeholder="<?php _e('e.g. Zurich, Switzerland', 'travel-booking'); ?>">
            <p class="description"><?php _e('Enter the destination location.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="vehicle_id"><?php _e('Vehicle', 'travel-booking'); ?> <span class="required">*</span></label>
            <select name="vehicle_id" id="vehicle_id" required>
                <option value=""><?php _e('-- Select a vehicle --', 'travel-booking'); ?></option>
                <?php foreach ($vehicles as $vehicle) : ?>
                    <option value="<?php echo esc_attr($vehicle->id); ?>">
                        <?php echo esc_html($vehicle->name); ?> (<?php echo esc_html($vehicle->capacity); ?> <?php _e('passengers', 'travel-booking'); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e('Select the vehicle for this route.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="distance"><?php _e('Distance (km)', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="number" name="distance" id="distance" value="" min="0" step="0.01" required placeholder="0.00">
            <p class="description"><?php _e('Enter the distance in kilometers.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="duration"><?php _e('Duration (hours)', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="number" name="duration" id="duration" value="" min="0" step="0.01" required placeholder="0.00">
            <p class="description"><?php _e('Enter the estimated duration in hours.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="fixed_price"><?php _e('Fixed Price (CHF)', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="number" name="fixed_price" id="fixed_price" value="" min="0" step="0.01" required placeholder="0.00">
            <p class="description"><?php _e('Enter the fixed price for this route. This will override the vehicle\'s per-km pricing.', 'travel-booking'); ?></p>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Add Route', 'travel-booking'); ?>">
            <a href="<?php echo admin_url('admin.php?page=travel-booking-routes'); ?>" class="button"><?php _e('Cancel', 'travel-booking'); ?></a>
        </p>
    </form>
    
    <div class="card" style="margin-top: 30px;">
        <h2><?php _e('Tips for Adding Routes', 'travel-booking'); ?></h2>
        <ul>
            <li><?php _e('Use specific location names (e.g. "Geneva Airport" instead of just "Geneva").', 'travel-booking'); ?></li>
            <li><?php _e('The fixed price will be used instead of the vehicle\'s per-km calculation.', 'travel-booking'); ?></li>
            <li><?php _e('Distance and duration are used for display purposes and route optimization.', 'travel-booking'); ?></li>
            <li><?php _e('You can create multiple routes for the same locations with different vehicles.', 'travel-booking'); ?></li>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-calculate price suggestion based on vehicle and distance
    $('#vehicle_id, #distance').on('change', function() {
        var vehicleId = $('#vehicle_id').val();
        var distance = parseFloat($('#distance').val());
        
        if (vehicleId && distance > 0) {
            // This would ideally make an AJAX call to get vehicle pricing
            // For now, just show a helpful message
            console.log('Vehicle:', vehicleId, 'Distance:', distance);
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        var origin = $('#origin').val().trim();
        var destination = $('#destination').val().trim();
        var distance = parseFloat($('#distance').val());
        var duration = parseFloat($('#duration').val());
        var price = parseFloat($('#fixed_price').val());
        
        if (!origin || !destination) {
            alert('<?php _e('Please enter both origin and destination.', 'travel-booking'); ?>');
            e.preventDefault();
            return false;
        }
        
        if (distance <= 0) {
            alert('<?php _e('Distance must be greater than 0.', 'travel-booking'); ?>');
            e.preventDefault();
            return false;
        }
        
        if (duration <= 0) {
            alert('<?php _e('Duration must be greater than 0.', 'travel-booking'); ?>');
            e.preventDefault();
            return false;
        }
        
        if (price <= 0) {
            alert('<?php _e('Price must be greater than 0.', 'travel-booking'); ?>');
            e.preventDefault();
            return false;
        }
        
        if (origin.toLowerCase() === destination.toLowerCase()) {
            alert('<?php _e('Origin and destination cannot be the same.', 'travel-booking'); ?>');
            e.preventDefault();
            return false;
        }
    });
});
</script>