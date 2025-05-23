<?php
/**
 * Frontend booking form template - Nouveau Design UNIQUEMENT
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="travel-booking-container">
    <form id="travel-booking-form" class="travel-form">
        <div class="form-bg">
            <div class="form-group form-group--dep">
                <input type="text" id="departure" name="departure" class="form-input form-input--1" placeholder="<?php _e('Departure', 'travel-booking'); ?>" required>
            </div>
            <div class="barre"></div>
            <div class="form-group form-group--des">
                <input type="text" id="destination" name="destination" class="form-input" placeholder="<?php _e('Destination', 'travel-booking'); ?>" required>
            </div>
            <div class="barre"></div>
            <div class="form-group form-group--date">
                <input type="date" id="travel-date" name="travel-date" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="barre"></div>
            <div class="form-group form-group--time">
                <input type="time" id="travel-time" name="travel-time" class="form-input" required>
            </div>
            <div class="barre"></div>
            <div class="form-group form-group--pas">
                <select id="passengers" name="passengers" class="form-select" required>
                    <?php for ($i = 1; $i <= 10; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i === 1 ? __('passenger', 'travel-booking') : __('passengers', 'travel-booking'); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="barre"></div>
            <div class="form-group form-group-checkbox">
                <label for="round-trip"><?php _e('Round-trip', 'travel-booking'); ?></label>
                <input type="checkbox" id="round-trip" name="round-trip" class="form-checkbox">
            </div>
        </div>
        <button type="submit" class="form-button"><?php _e('Calculate', 'travel-booking'); ?></button>
    </form>

    <!-- Animation de chargement -->
    <div id="car-animation-container" style="display: none; visibility: hidden;">
        <div class="car-moving"></div>
    </div>

    <!-- Carte Google Maps -->
    <div id="travel-booking-map" class="map-container"></div>

    <!-- Résultats du trajet -->
    <div id="travel-booking-results" class="travel-results" style="display: none;">
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

    <!-- Options de véhicules -->
    <div id="travel-booking-vehicles" class="vehicle-options"></div>

    <!-- Loading animation -->
    <div class="travel-booking-loading-animation" style="display: none; visibility: hidden;">
        <div class="travel-booking-spinner"></div>
        <p><?php _e('Calculating the best route...', 'travel-booking'); ?></p>
    </div>
</div>