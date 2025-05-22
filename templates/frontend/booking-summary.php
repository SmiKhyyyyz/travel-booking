<?php
/**
 * Frontend booking summary template - Design moderne
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="travel-booking-container">
    <div class="travel-booking-summary-modern">
        <div class="travel-booking-summary">
            
            <!-- Colonne de gauche - Résumé du voyage -->
            <div class="travel-booking-summary-left">
                <h2><?php _e('SUMMARY OF THE TRIP', 'travel-booking'); ?></h2>
                
                <?php if (!empty($vehicle->image_url)) : ?>
                <div class="travel-booking-vehicle-showcase">
                    <img src="<?php echo esc_url($vehicle->image_url); ?>" alt="<?php echo esc_attr($vehicle->name); ?>">
                </div>
                <?php endif; ?>
                
                <div class="travel-booking-trip-details">
                    <div class="travel-booking-trip-detail-item">
                        <div class="travel-booking-trip-detail-label"><?php _e('Vehicle', 'travel-booking'); ?></div>
                        <div class="travel-booking-trip-detail-value"><?php echo esc_html($vehicle->name); ?></div>
                    </div>
                    
                    <div class="travel-booking-trip-detail-item">
                        <div class="travel-booking-trip-detail-label"><?php _e('Departure', 'travel-booking'); ?></div>
                        <div class="travel-booking-trip-detail-value"><?php echo esc_html($booking->departure); ?></div>
                    </div>
                    
                    <div class="travel-booking-trip-detail-item">
                        <div class="travel-booking-trip-detail-label"><?php _e('Destination', 'travel-booking'); ?></div>
                        <div class="travel-booking-trip-detail-value"><?php echo esc_html($booking->destination); ?></div>
                    </div>
                    
                    <div class="travel-booking-trip-detail-item">
                        <div class="travel-booking-trip-detail-label"><?php _e('Date & Time', 'travel-booking'); ?></div>
                        <div class="travel-booking-trip-detail-value"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->travel_date)); ?></div>
                    </div>
                    
                    <div class="travel-booking-trip-detail-item">
                        <div class="travel-booking-trip-detail-label"><?php _e('Passengers', 'travel-booking'); ?></div>
                        <div class="travel-booking-trip-detail-value"><?php echo esc_html($booking->number_of_passengers); ?> <?php echo $booking->number_of_passengers == 1 ? __('passenger', 'travel-booking') : __('passengers', 'travel-booking'); ?></div>
                    </div>
                    
                    <div class="travel-booking-trip-detail-item">
                        <div class="travel-booking-trip-detail-label"><?php _e('Distance', 'travel-booking'); ?></div>
                        <div class="travel-booking-trip-detail-value"><?php echo esc_html(number_format($booking->distance, 2)); ?> km</div>
                    </div>
                    
                    <div class="travel-booking-trip-detail-item">
                        <div class="travel-booking-trip-detail-label"><?php _e('Travel time', 'travel-booking'); ?></div>
                        <div class="travel-booking-trip-detail-value"><?php echo esc_html(number_format($booking->duration, 2)); ?> <?php _e('hours', 'travel-booking'); ?></div>
                    </div>
                    
                    <div class="travel-booking-trip-detail-item">
                        <div class="travel-booking-trip-detail-label"><?php _e('Round trip', 'travel-booking'); ?></div>
                        <div class="travel-booking-trip-detail-value"><?php echo $booking->round_trip ? __('Yes', 'travel-booking') : __('No', 'travel-booking'); ?></div>
                    </div>
                </div>
                
                <div class="travel-booking-price-highlight">
                    <div class="travel-booking-trip-detail-label"><?php _e('Price', 'travel-booking'); ?></div>
                    <div class="travel-booking-trip-detail-value" id="travel-booking-price" data-original-price="<?php echo esc_attr($booking->price); ?>">
                        <?php echo esc_html(number_format($booking->price, 2)); ?> CHF
                    </div>
                </div>
            </div>
            
            <!-- Colonne de droite - Détails client -->
            <div class="travel-booking-summary-right">
                <h2><?php _e('CUSTOMER DETAILS', 'travel-booking'); ?></h2>
                
                <?php if (empty($booking->client_first_name)) : ?>
                    <!-- Formulaire pour saisir les infos client -->
                    <form id="travel-booking-client-form" class="travel-booking-form">
                        <div class="travel-booking-form-row">
                            <div class="travel-booking-form-group">
                                <label for="first-name"><?php _e('First Name', 'travel-booking'); ?> <span style="color: #e74c3c;">*</span></label>
                                <input type="text" id="first-name" name="first-name" required>
                            </div>
                        </div>
                        
                        <div class="travel-booking-form-row">
                            <div class="travel-booking-form-group">
                                <label for="last-name"><?php _e('Last Name', 'travel-booking'); ?> <span style="color: #e74c3c;">*</span></label>
                                <input type="text" id="last-name" name="last-name" required>
                            </div>
                        </div>
                        
                        <div class="travel-booking-form-row">
                            <div class="travel-booking-form-group">
                                <label for="email"><?php _e('Email', 'travel-booking'); ?> <span style="color: #e74c3c;">*</span></label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="travel-booking-form-row">
                            <div class="travel-booking-form-group">
                                <label for="phone"><?php _e('Phone', 'travel-booking'); ?> <span style="color: #e74c3c;">*</span></label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                        </div>
                        
                        <div class="travel-booking-form-row">
                            <div class="travel-booking-form-group">
                                <label for="address"><?php _e('Full address', 'travel-booking'); ?> <span style="color: #e74c3c;">*</span></label>
                                <input type="text" id="address" name="address" required>
                            </div>
                        </div>
                        
                        <div class="travel-booking-form-row">
                            <div class="travel-booking-form-group">
                                <label for="flight-number"><?php _e('Flight Number', 'travel-booking'); ?></label>
                                <input type="text" id="flight-number" name="flight-number" placeholder="<?php _e('Add a flight number', 'travel-booking'); ?>">
                            </div>
                        </div>
                        
                        <div class="travel-booking-form-row">
                            <div class="travel-booking-form-group">
                                <label for="notes"><?php _e('Notes', 'travel-booking'); ?></label>
                                <textarea id="notes" name="notes" placeholder="<?php _e('Additional information...', 'travel-booking'); ?>"></textarea>
                            </div>
                        </div>
                        
                        <!-- Section code promo -->
                        <div class="travel-booking-promo-section">
                            <label for="promo-code"><?php _e('Promo Code', 'travel-booking'); ?></label>
                            <div class="travel-booking-promo-code-input">
                                <input type="text" id="promo-code" name="promo-code" placeholder="<?php _e('Enter your code', 'travel-booking'); ?>">
                                <button type="button" id="apply-promo-code"><?php _e('APPLY', 'travel-booking'); ?></button>
                            </div>
                            <div id="promo-code-message" class="travel-booking-promo-code-message" style="display: none;"></div>
                        </div>
                        
                        <button type="submit" id="proceed-to-payment" class="travel-booking-button-primary">
                            <?php _e('PAY NOW', 'travel-booking'); ?>
                        </button>
                    </form>
                    
                <?php else : ?>
                    <!-- Affichage des infos client déjà saisies -->
                    <div class="travel-booking-client-info">
                        <div class="travel-booking-trip-detail-item">
                            <div class="travel-booking-trip-detail-label"><?php _e('Name', 'travel-booking'); ?></div>
                            <div class="travel-booking-trip-detail-value"><?php echo esc_html($booking->client_first_name . ' ' . $booking->client_last_name); ?></div>
                        </div>
                        
                        <div class="travel-booking-trip-detail-item">
                            <div class="travel-booking-trip-detail-label"><?php _e('Email', 'travel-booking'); ?></div>
                            <div class="travel-booking-trip-detail-value"><?php echo esc_html($booking->client_email); ?></div>
                        </div>
                        
                        <div class="travel-booking-trip-detail-item">
                            <div class="travel-booking-trip-detail-label"><?php _e('Phone', 'travel-booking'); ?></div>
                            <div class="travel-booking-trip-detail-value"><?php echo esc_html($booking->client_phone); ?></div>
                        </div>
                        
                        <div class="travel-booking-trip-detail-item">
                            <div class="travel-booking-trip-detail-label"><?php _e('Address', 'travel-booking'); ?></div>
                            <div class="travel-booking-trip-detail-value"><?php echo esc_html($booking->client_address); ?></div>
                        </div>
                        
                        <?php if (!empty($booking->flight_number)) : ?>
                        <div class="travel-booking-trip-detail-item">
                            <div class="travel-booking-trip-detail-label"><?php _e('Flight Number', 'travel-booking'); ?></div>
                            <div class="travel-booking-trip-detail-value"><?php echo esc_html($booking->flight_number); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Section code promo pour client existant -->
                        <div class="travel-booking-promo-section">
                            <label for="promo-code-final"><?php _e('Promo Code', 'travel-booking'); ?></label>
                            <div class="travel-booking-promo-code-input">
                                <input type="text" id="promo-code-final" name="promo-code" placeholder="<?php _e('Enter your code', 'travel-booking'); ?>" value="<?php echo esc_attr($booking->promo_code ?? ''); ?>">
                                <button type="button" id="apply-promo-code-final"><?php _e('APPLY', 'travel-booking'); ?></button>
                            </div>
                            <div id="promo-code-message-final" class="travel-booking-promo-code-message" style="display: none;"></div>
                        </div>
                        
                        <button type="button" id="create-order" class="travel-booking-button-primary">
                            <?php _e('PAY NOW', 'travel-booking'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>