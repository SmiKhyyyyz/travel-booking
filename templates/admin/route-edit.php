<?php
/**
 * Admin edit route template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Edit Route', 'travel-booking'); ?> #<?php echo $route->id; ?></h1>
    
    <form method="post" action="" class="travel-booking-form">
        <?php wp_nonce_field('travel_booking_route_update'); ?>
        <input type="hidden" name="travel_booking_route_action" value="update">
        <input type="hidden" name="route_id" value="<?php echo esc_attr($route->id); ?>">
        
        <div class="form-row">
            <label for="origin"><?php _e('Origin', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="text" name="origin" id="origin" value="<?php echo esc_attr($route->origin); ?>" required placeholder="<?php _e('e.g. Geneva, Switzerland', 'travel-booking'); ?>">
            <p class="description"><?php _e('Enter the departure location.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="destination"><?php _e('Destination', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="text" name="destination" id="destination" value="<?php echo esc_attr($route->destination); ?>" required placeholder="<?php _e('e.g. Zurich, Switzerland', 'travel-booking'); ?>">
            <p class="description"><?php _e('Enter the destination location.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="vehicle_id"><?php _e('Vehicle', 'travel-booking'); ?> <span class="required">*</span></label>
            <select name="vehicle_id" id="vehicle_id" required>
                <option value=""><?php _e('-- Select a vehicle --', 'travel-booking'); ?></option>
                <?php foreach ($vehicles as $vehicle) : ?>
                    <option value="<?php echo esc_attr($vehicle->id); ?>" <?php selected($route->vehicle_id, $vehicle->id); ?>>
                        <?php echo esc_html($vehicle->name); ?> (<?php echo esc_html($vehicle->capacity); ?> <?php _e('passengers', 'travel-booking'); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e('Select the vehicle for this route.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="distance"><?php _e('Distance (km)', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="number" name="distance" id="distance" value="<?php echo esc_attr($route->distance); ?>" min="0" step="0.01" required placeholder="0.00">
            <p class="description"><?php _e('Enter the distance in kilometers.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="duration"><?php _e('Duration (hours)', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="number" name="duration" id="duration" value="<?php echo esc_attr($route->duration); ?>" min="0" step="0.01" required placeholder="0.00">
            <p class="description"><?php _e('Enter the estimated duration in hours.', 'travel-booking'); ?></p>
        </div>
        
        <div class="form-row">
            <label for="fixed_price"><?php _e('Fixed Price (CHF)', 'travel-booking'); ?> <span class="required">*</span></label>
            <input type="number" name="fixed_price" id="fixed_price" value="<?php echo esc_attr($route->fixed_price); ?>" min="0" step="0.01" required placeholder="0.00">
            <p class="description"><?php _e('Enter the fixed price for this route. This will override the vehicle\'s per-km pricing.', 'travel-booking'); ?></p>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Update Route', 'travel-booking'); ?>">
            <a href="<?php echo admin_url('admin.php?page=travel-booking-routes'); ?>" class="button"><?php _e('Cancel', 'travel-booking'); ?></a>
        </p>
    </form>
    
    <div class="card" style="margin-top: 30px;">
        <h2><?php _e('Route Information', 'travel-booking'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Route ID', 'travel-booking'); ?></th>
                <td>#<?php echo esc_html($route->id); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Created', 'travel-booking'); ?></th>
                <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($route->created_at)); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Current Price per km', 'travel-booking'); ?></th>
                <td>
                    <?php 
                    $current_vehicle = null;
                    foreach ($vehicles as $v) {
                        if ($v->id == $route->vehicle_id) {
                            $current_vehicle = $v;
                            break;
                        }
                    }
                    if ($current_vehicle) {
                        echo esc_html(number_format($current_vehicle->price_per_km, 2)) . ' CHF/km';
                        $calculated_price = $current_vehicle->price_per_km * $route->distance;
                        if ($calculated_price < $current_vehicle->base_price) {
                            $calculated_price = $current_vehicle->base_price;
                        }
                        echo '<br><small>' . sprintf(__('Calculated price would be: %s CHF', 'travel-booking'), number_format($calculated_price, 2)) . '</small>';
                    } else {
                        echo '—';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Fixed Price Advantage', 'travel-booking'); ?></th>
                <td>
                    <?php 
                    if ($current_vehicle) {
                        $calculated_price = max($current_vehicle->price_per_km * $route->distance, $current_vehicle->base_price);
                        $difference = $route->fixed_price - $calculated_price;
                        if ($difference > 0) {
                            echo '<span style="color: #d63384;">+' . number_format($difference, 2) . ' CHF (' . __('more expensive', 'travel-booking') . ')</span>';
                        } elseif ($difference < 0) {
                            echo '<span style="color: #198754;">' . number_format($difference, 2) . ' CHF (' . __('cheaper', 'travel-booking') . ')</span>';
                        } else {
                            echo '<span style="color: #6c757d;">' . __('Same price', 'travel-booking') . '</span>';
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="card">
        <h2><?php _e('Actions', 'travel-booking'); ?></h2>
        <p>
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travel-booking-routes&action=delete&id=' . $route->id), 'travel_booking_delete_route_' . $route->id); ?>" 
                class="button button-link-delete" 
                onclick="return confirm('<?php _e('Are you sure you want to delete this route? This action cannot be undone.', 'travel-booking'); ?>')">
                <?php _e('Delete Route', 'travel-booking'); ?>
            </a>
        </p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update price comparison when vehicle or distance changes
    $('#vehicle_id, #distance, #fixed_price').on('change', function() {
        updatePriceComparison();
    });
    
    function updatePriceComparison() {
        var vehicleId = $('#vehicle_id').val();
        var distance = parseFloat($('#distance').val()) || 0;
        var fixedPrice = parseFloat($('#fixed_price').val()) || 0;
        
        if (vehicleId && distance > 0 && fixedPrice > 0) {
            // In a real implementation, you'd make an AJAX call to get vehicle pricing
            console.log('Vehicle:', vehicleId, 'Distance:', distance, 'Fixed Price:', fixedPrice);
        }
    }
    
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