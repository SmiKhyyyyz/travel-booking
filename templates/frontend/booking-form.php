<?php
/**
 * Frontend booking form template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="travel-booking-container">
    <h2 class="travel-booking-title"><?php echo esc_html($atts['title']); ?></h2>
    
    <form id="travel-booking-form" class="travel-booking-form">
        <div class="travel-booking-form-row">
            <div class="travel-booking-form-group">
                <label for="departure"><?php _e('From', 'travel-booking'); ?> <span class="required">*</span></label>
                <input type="text" id="departure" name="departure" placeholder="<?php _e('Enter departure location', 'travel-booking'); ?>" required>
            </div>
            
            <div class="travel-booking-form-group">
                <label for="destination"><?php _e('To', 'travel-booking'); ?> <span class="required">*</span></label>
                <input type="text" id="destination" name="destination" placeholder="<?php _e('Enter destination', 'travel-booking'); ?>" required>
            </div>
        </div>
        
        <div class="travel-booking-form-row">
            <div class="travel-booking-form-group">
                <label for="travel-date"><?php _e('Date', 'travel-booking'); ?> <span class="required">*</span></label>
                <input type="date" id="travel-date" name="travel-date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="travel-booking-form-group">
                <label for="travel-time"><?php _e('Time', 'travel-booking'); ?> <span class="required">*</span></label>
                <input type="time" id="travel-time" name="travel-time" required>
            </div>
        </div>
        
        <div class="travel-booking-form-row">
            <div class="travel-booking-form-group">
                <label for="passengers"><?php _e('Passengers', 'travel-booking'); ?> <span class="required">*</span></label>
                <select id="passengers" name="passengers" required>
                    <?php for ($i = 1; $i <= 10; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i === 1 ? __('passenger', 'travel-booking') : __('passengers', 'travel-booking'); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="travel-booking-form-group travel-booking-checkbox-group">
                <label for="round-trip">
                    <input type="checkbox" id="round-trip" name="round-trip">
                    <?php _e('Round Trip', 'travel-booking'); ?>
                </label>
            </div>
        </div>
        
        <div class="travel-booking-form-row">
            <button type="submit" class="travel-booking-button travel-booking-button-primary"><?php _e('Calculate', 'travel-booking'); ?></button>
        </div>
    </form>
    
    <div class="travel-booking-loading-animation" style="display: none;">
        <div class="travel-booking-spinner"></div>
        <p><?php _e('Calculating the best route...', 'travel-booking'); ?></p>
    </div>
    
    <div id="travel-booking-map" class="travel-booking-map"></div>
    
    <div id="travel-booking-results" class="travel-booking-results" style="display: none;">
        <h3><?php _e('Route Information', 'travel-booking'); ?></h3>
        <div class="travel-booking-route-info">
            <div class="travel-booking-route-info-item">
                <span class="travel-booking-route-info-label"><?php _e('Distance:', 'travel-booking'); ?></span>
                <span id="travel-booking-distance" class="travel-booking-route-info-value">0 km</span>
            </div>
            <div class="travel-booking-route-info-item">
                <span class="travel-booking-route-info-label"><?php _e('Duration:', 'travel-booking'); ?></span>
                <span id="travel-booking-duration" class="travel-booking-route-info-value">0 hours</span>
            </div>
        </div>
    </div>
    
    <div id="travel-booking-vehicles" class="travel-booking-vehicles"></div>
</div>