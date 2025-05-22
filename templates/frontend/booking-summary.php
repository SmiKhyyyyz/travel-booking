<?php
/**
 * Frontend booking summary template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="travel-booking-container">
    <h2 class="travel-booking-title"><?php echo esc_html($atts['title']); ?></h2>
    
    <div class="travel-booking-summary">
        <div class="travel-booking-summary-section">
            <h3><?php _e('Booking Details', 'travel-booking'); ?></h3>
            
            <div class="travel-booking-summary-item">
                <span class="travel-booking-summary-label"><?php _e('From:', 'travel-booking'); ?></span>
                <span class="travel-booking-summary-value"><?php echo esc_html($booking->departure); ?></span>
            </div>
            
            <div class="travel-booking-summary-item">
                <span class="travel-booking-summary-label"><?php _e('To:', 'travel-booking'); ?></span>
                <span class="travel-booking-summary-value"><?php echo esc_html($booking->destination); ?></span>
            </div>
            
            <div class="travel-booking-summary-item">
                <span class="travel-booking-summary-label"><?php _e('Date:', 'travel-booking'); ?></span>
                <span class="travel-booking-summary-value"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->travel_date)); ?></span>
            </div>
            
            <div class="travel-booking-summary-item">
                <span class="travel-booking-summary-label"><?php _e('Passengers:', 'travel-booking'); ?></span>
                <span class="travel-booking-summary-value"><?php echo esc_html($booking->number_of_passengers); ?></span>
            </div>
            
            <div class="travel-booking-summary-item">
                <span class="travel-booking-summary-label"><?php _e('Round Trip:', 'travel-booking'); ?></span>
                <span class="travel-booking-summary-value"><?php echo $booking->round_trip ? __('Yes', 'travel-booking') : __('No', 'travel-booking'); ?></span>
            </div>
            
            <div class="travel-booking-summary-item">
                <span class="travel-booking-summary-label"><?php _e('Distance:', 'travel-booking'); ?></span>
                <span class="travel-booking-summary-value"><?php echo esc_html(number_format($booking->distance, 2)); ?> km</span>
            </div>
            
            <div class="travel-booking-summary-item">
                <span class="travel-booking-summary-label"><?php _e('Duration:', 'travel-booking'); ?></span>
                <span class="travel-booking-summary-value"><?php echo esc_html(number_format($booking->duration, 2)); ?> <?php _e('hours', 'travel-booking'); ?></span>
            </div>
        </div>
        
        <div class="travel-booking-summary-section">
            <h3><?php _e('Vehicle', 'travel-booking'); ?></h3>
            
            <div class="travel-booking-vehicle-details">
                <?php if (!empty($vehicle->image_url)) : ?>
                    <div class="travel-booking-vehicle-image">
                        <img src="<?php echo esc_url($vehicle->image_url); ?>" alt="<?php echo esc_attr($vehicle->name); ?>">
                    </div>
                <?php endif; ?>
                
                <div class="travel-booking-vehicle-info">
                    <h4><?php echo esc_html($vehicle->name); ?></h4>
                    
                    <?php if (!empty($vehicle->description)) : ?>
                        <p><?php echo esc_html($vehicle->description); ?></p>
                    <?php endif; ?>
                    
                    <div class="travel-booking-vehicle-details-item">
                        <span class="travel-booking-vehicle-details-label"><?php _e('Capacity:', 'travel-booking'); ?></span>
                        <span class="travel-booking-vehicle-details-value"><?php echo esc_html($vehicle->capacity); ?> <?php _e('passengers', 'travel-booking'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="travel-booking-summary-section">
            <h3><?php _e('Price', 'travel-booking'); ?></h3>
            
            <div class="travel-booking-summary-item travel-booking-price">
                <span class="travel-booking-summary-label"><?php _e('Total:', 'travel-booking'); ?></span>
                <span id="travel-booking-price" class="travel-booking-summary-value travel-booking-price-value"><?php echo esc_html(number_format($booking->price, 2)); ?> <?php echo get_woocommerce_currency_symbol(); ?></span>
            </div>
            
            <div class="travel-booking-promo-code">
                <div class="travel-booking-form-group">
                    <label for="promo-code"><?php _e('Promo Code', 'travel-booking'); ?></label>
                    <div class="travel-booking-promo-code-input">
                        <input type="text" id="promo-code" name="promo-code" placeholder="<?php _e('Enter promo code', 'travel-booking'); ?>">
                        <button type="button" id="apply-promo-code" class="travel-booking-button"><?php _e('Apply', 'travel-booking'); ?></button>
                    </div>
                    <div id="promo-code-message" class="travel-booking-promo-code-message"></div>
                </div>
            </div>
        </div>
        
        <?php if (empty($booking->client_first_name)) : ?>
            <div class="travel-booking-summary-section">
                <h3><?php _e('Your Information', 'travel-booking'); ?></h3>
                
                <form id="travel-booking-client-form" class="travel-booking-form">
                    <div class="travel-booking-form-row">
                        <div class="travel-booking-form-group">
                            <label for="first-name"><?php _e('First Name', 'travel-booking'); ?> <span class="required">*</span></label>
                            <input type="text" id="first-name" name="first-name" required>
                        </div>
                        
                        <div class="travel-booking-form-group">
                            <label for="last-name"><?php _e('Last Name', 'travel-booking'); ?> <span class="required">*</span></label>
                            <input type="text" id="last-name" name="last-name" required>
                        </div>
                    </div>
                    
                    <div class="travel-booking-form-row">
                        <div class="travel-booking-form-group">
                            <label for="email"><?php _e('Email', 'travel-booking'); ?> <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="travel-booking-form-group">
                            <label for="phone"><?php _e('Phone', 'travel-booking'); ?> <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="travel-booking-form-row">
                        <div class="travel-booking-form-group">
                            <label for="address"><?php _e('Address', 'travel-booking'); ?> <span class="required">*</span></label>
                            <input type="text" id="address" name="address" required>
                        </div>
                    </div>
                    
                    <div class="travel-booking-form-row">
                        <div class="travel-booking-form-group">
                            <label for="flight-number"><?php _e('Flight Number', 'travel-booking'); ?></label>
                            <input type="text" id="flight-number" name="flight-number">
                        </div>
                    </div>
                    
                    <div class="travel-booking-form-row">
                        <div class="travel-booking-form-group">
                            <label for="notes"><?php _e('Notes', 'travel-booking'); ?></label>
                            <textarea id="notes" name="notes"></textarea>
                        </div>
                    </div>
                    
                    <div class="travel-booking-form-row">
                        <button type="submit" id="proceed-to-payment" class="travel-booking-button travel-booking-button-primary"><?php _e('Proceed to Payment', 'travel-booking'); ?></button>
                    </div>
                </form>
            </div>
        <?php else : ?>
            <div class="travel-booking-summary-section">
                <h3><?php _e('Client Information', 'travel-booking'); ?></h3>
                
                <div class="travel-booking-summary-item">
                    <span class="travel-booking-summary-label"><?php _e('Name:', 'travel-booking'); ?></span>
                    <span class="travel-booking-summary-value"><?php echo esc_html($booking->client_first_name . ' ' . $booking->client_last_name); ?></span>
                </div>
                
                <div class="travel-booking-summary-item">
                    <span class="travel-booking-summary-label"><?php _e('Email:', 'travel-booking'); ?></span>
                    <span class="travel-booking-summary-value"><?php echo esc_html($booking->client_email); ?></span>
                </div>
                
                <div class="travel-booking-summary-item">
                    <span class="travel-booking-summary-label"><?php _e('Phone:', 'travel-booking'); ?></span>
                    <span class="travel-booking-summary-value"><?php echo esc_html($booking->client_phone); ?></span>
                </div>
                
                <div class="travel-booking-summary-item">
                    <span class="travel-booking-summary-label"><?php _e('Address:', 'travel-booking'); ?></span>
                    <span class="travel-booking-summary-value"><?php echo esc_html($booking->client_address); ?></span>
                </div>
                
                <?php if (!empty($booking->flight_number)) : ?>
                    <div class="travel-booking-summary-item">
                        <span class="travel-booking-summary-label"><?php _e('Flight Number:', 'travel-booking'); ?></span>
                        <span class="travel-booking-summary-value"><?php echo esc_html($booking->flight_number); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($booking->notes)) : ?>
                    <div class="travel-booking-summary-item">
                        <span class="travel-booking-summary-label"><?php _e('Notes:', 'travel-booking'); ?></span>
                        <span class="travel-booking-summary-value"><?php echo esc_html($booking->notes); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- AJOUTER CETTE SECTION PROMO CODE -->
                <div class="travel-booking-promo-code" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <div class="travel-booking-form-group">
                        <label for="promo-code-final"><?php _e('Promo Code', 'travel-booking'); ?></label>
                        <div class="travel-booking-promo-code-input">
                            <input type="text" id="promo-code-final" name="promo-code" placeholder="<?php _e('Enter promo code', 'travel-booking'); ?>" value="<?php echo esc_attr($booking->promo_code ?? ''); ?>">
                            <button type="button" id="apply-promo-code-final" class="travel-booking-button"><?php _e('Apply', 'travel-booking'); ?></button>
                        </div>
                        <div id="promo-code-message-final" class="travel-booking-promo-code-message"></div>
                    </div>
                </div>
                
                <div class="travel-booking-form-row">
                    <button type="button" id="create-order" class="travel-booking-button travel-booking-button-primary"><?php _e('Proceed to Payment', 'travel-booking'); ?></button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>