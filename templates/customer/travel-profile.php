<?php
/**
 * Customer travel profile template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Get available vehicles for preferred vehicle selection
$vehicles = Travel_Booking_Vehicle::get_all();
?>

<div class="travel-booking-customer-section">
    <div class="travel-booking-customer-header">
        <h2><?php _e('Travel Profile', 'travel-booking'); ?></h2>
        <p class="travel-booking-description"><?php _e('Set your travel preferences to make booking faster and more convenient.', 'travel-booking'); ?></p>
    </div>

    <form id="travel-profile-form" class="travel-booking-profile-form">
        <?php wp_nonce_field('travel_booking_customer_nonce', 'nonce'); ?>
        
        <div class="profile-sections">
            <!-- Default Locations -->
            <div class="profile-section">
                <div class="section-header">
                    <h3><?php _e('Default Locations', 'travel-booking'); ?></h3>
                    <p class="section-description"><?php _e('Set your most frequently used pickup and drop-off locations.', 'travel-booking'); ?></p>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="default_pickup"><?php _e('Default Pickup Location', 'travel-booking'); ?></label>
                        <input type="text" id="default_pickup" name="default_pickup" 
                               value="<?php echo esc_attr($profile['default_pickup']); ?>" 
                               placeholder="<?php _e('e.g., Home address, Office, Airport...', 'travel-booking'); ?>"
                               class="travel-booking-input">
                        <span class="field-description"><?php _e('This will be pre-filled when making new bookings.', 'travel-booking'); ?></span>
                    </div>

                    <div class="form-group">
                        <label for="default_dropoff"><?php _e('Default Drop-off Location', 'travel-booking'); ?></label>
                        <input type="text" id="default_dropoff" name="default_dropoff" 
                               value="<?php echo esc_attr($profile['default_dropoff']); ?>" 
                               placeholder="<?php _e('e.g., Work address, Train station...', 'travel-booking'); ?>"
                               class="travel-booking-input">
                        <span class="field-description"><?php _e('This will be pre-filled when making new bookings.', 'travel-booking'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Vehicle Preferences -->
            <div class="profile-section">
                <div class="section-header">
                    <h3><?php _e('Vehicle Preferences', 'travel-booking'); ?></h3>
                    <p class="section-description"><?php _e('Choose your preferred vehicle type for faster booking.', 'travel-booking'); ?></p>
                </div>
                
                <div class="form-group">
                    <label for="preferred_vehicle"><?php _e('Preferred Vehicle', 'travel-booking'); ?></label>
                    <select id="preferred_vehicle" name="preferred_vehicle" class="travel-booking-select">
                        <option value=""><?php _e('No preference', 'travel-booking'); ?></option>
                        <?php foreach ($vehicles as $vehicle) : ?>
                            <option value="<?php echo esc_attr($vehicle->id); ?>" <?php selected($profile['preferred_vehicle'], $vehicle->id); ?>>
                                <?php echo esc_html($vehicle->name); ?> (<?php echo esc_html($vehicle->capacity); ?> <?php _e('passengers', 'travel-booking'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-description"><?php _e('Your preferred vehicle will be pre-selected when available.', 'travel-booking'); ?></span>
                </div>

                <div class="form-group">
                    <label for="special_requests"><?php _e('Special Requests', 'travel-booking'); ?></label>
                    <textarea id="special_requests" name="special_requests" 
                              placeholder="<?php _e('e.g., Child seat required, wheelchair accessible, air conditioning...', 'travel-booking'); ?>"
                              class="travel-booking-textarea" rows="3"><?php echo esc_textarea($profile['special_requests']); ?></textarea>
                    <span class="field-description"><?php _e('These requests will be included in all your bookings.', 'travel-booking'); ?></span>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="profile-section">
                <div class="section-header">
                    <h3><?php _e('Emergency Contact', 'travel-booking'); ?></h3>
                    <p class="section-description"><?php _e('Provide an emergency contact for safety purposes.', 'travel-booking'); ?></p>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="emergency_contact"><?php _e('Emergency Contact Name', 'travel-booking'); ?></label>
                        <input type="text" id="emergency_contact" name="emergency_contact" 
                               value="<?php echo esc_attr($profile['emergency_contact']); ?>" 
                               placeholder="<?php _e('Full name', 'travel-booking'); ?>"
                               class="travel-booking-input">
                    </div>

                    <div class="form-group">
                        <label for="emergency_phone"><?php _e('Emergency Contact Phone', 'travel-booking'); ?></label>
                        <input type="tel" id="emergency_phone" name="emergency_phone" 
                               value="<?php echo esc_attr($profile['emergency_phone']); ?>" 
                               placeholder="<?php _e('+41 XX XXX XX XX', 'travel-booking'); ?>"
                               class="travel-booking-input">
                    </div>
                </div>
            </div>

            <!-- Communication Preferences -->
            <div class="profile-section">
                <div class="section-header">
                    <h3><?php _e('Communication Preferences', 'travel-booking'); ?></h3>
                    <p class="section-description"><?php _e('Choose how you\'d like to receive updates and notifications.', 'travel-booking'); ?></p>
                </div>
                
                <div class="form-checkboxes">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="newsletter" <?php checked($profile['newsletter'], 1); ?>>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">
                                <strong><?php _e('Email Newsletter', 'travel-booking'); ?></strong>
                                <small><?php _e('Receive updates about new services and special offers.', 'travel-booking'); ?></small>
                            </span>
                        </label>
                    </div>

                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="sms_notifications" <?php checked($profile['sms_notifications'], 1); ?>>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">
                                <strong><?php _e('SMS Notifications', 'travel-booking'); ?></strong>
                                <small><?php _e('Receive booking confirmations and driver updates via SMS.', 'travel-booking'); ?></small>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Profile Statistics -->
            <div class="profile-section">
                <div class="section-header">
                    <h3><?php _e('Your Travel Statistics', 'travel-booking'); ?></h3>
                    <p class="section-description"><?php _e('Overview of your travel activity with us.', 'travel-booking'); ?></p>
                </div>
                
                <?php
                $user_id = get_current_user_id();
                $user = get_userdata($user_id);
                $user_bookings = Travel_Booking_Customer_Area::get_user_bookings($user_id);
                
                $total_bookings = count($user_bookings);
                $total_distance = array_sum(array_column($user_bookings, 'distance'));
                $total_spent = array_sum(array_column($user_bookings, 'price'));
                $member_since = get_userdata($user_id)->user_registered;
                ?>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon">🚗</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($total_bookings); ?></div>
                            <div class="stat-label"><?php _e('Total Trips', 'travel-booking'); ?></div>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon">📏</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($total_distance, 0); ?> km</div>
                            <div class="stat-label"><?php _e('Distance Traveled', 'travel-booking'); ?></div>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon">💰</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($total_spent, 2); ?> CHF</div>
                            <div class="stat-label"><?php _e('Total Spent', 'travel-booking'); ?></div>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon">📅</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo date_i18n('M Y', strtotime($member_since)); ?></div>
                            <div class="stat-label"><?php _e('Member Since', 'travel-booking'); ?></div>
                        </div>
                    </div>
                </div>

                <?php if ($total_bookings >= 5) : ?>
                <div class="loyalty-badge">
                    <div class="badge-icon">⭐</div>
                    <div class="badge-content">
                        <h4><?php _e('Loyal Customer', 'travel-booking'); ?></h4>
                        <p><?php _e('Thank you for being a valued customer! You may be eligible for special discounts.', 'travel-booking'); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="travel-booking-btn-primary" id="save-profile">
                <?php _e('Save Profile', 'travel-booking'); ?>
            </button>
            <button type="button" class="travel-booking-btn-secondary" id="reset-profile">
                <?php _e('Reset to Default', 'travel-booking'); ?>
            </button>
        </div>
    </form>

    <!-- Success/Error Messages -->
    <div id="profile-messages" class="travel-booking-messages" style="display: none;"></div>
</div>