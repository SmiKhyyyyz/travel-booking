<?php
/**
 * Customer favorite addresses template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="travel-booking-customer-section">
    <div class="travel-booking-customer-header">
        <h2><?php _e('Favorite Addresses', 'travel-booking'); ?></h2>
        <p class="travel-booking-description"><?php _e('Save your frequently used addresses for quick and easy booking.', 'travel-booking'); ?></p>
    </div>

    <!-- Add New Favorite Form -->
    <div class="add-favorite-section">
        <button type="button" id="toggle-add-form" class="travel-booking-btn-primary">
            <span class="btn-icon">➕</span>
            <?php _e('Add New Address', 'travel-booking'); ?>
        </button>

        <form id="add-favorite-form" class="add-favorite-form" style="display: none;">
            <?php wp_nonce_field('travel_booking_customer_nonce', 'nonce'); ?>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="favorite_name"><?php _e('Address Name', 'travel-booking'); ?> <span class="required">*</span></label>
                    <input type="text" id="favorite_name" name="name" 
                           placeholder="<?php _e('e.g., Home, Office, Airport...', 'travel-booking'); ?>" 
                           class="travel-booking-input" required>
                </div>

                <div class="form-group">
                    <label for="favorite_type"><?php _e('Address Type', 'travel-booking'); ?></label>
                    <select id="favorite_type" name="type" class="travel-booking-select">
                        <option value="home"><?php _e('Home', 'travel-booking'); ?></option>
                        <option value="work"><?php _e('Work', 'travel-booking'); ?></option>
                        <option value="airport"><?php _e('Airport', 'travel-booking'); ?></option>
                        <option value="hotel"><?php _e('Hotel', 'travel-booking'); ?></option>
                        <option value="restaurant"><?php _e('Restaurant', 'travel-booking'); ?></option>
                        <option value="other"><?php _e('Other', 'travel-booking'); ?></option>
                    </select>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="favorite_address"><?php _e('Full Address', 'travel-booking'); ?> <span class="required">*</span></label>
                <input type="text" id="favorite_address" name="address" 
                       placeholder="<?php _e('Enter complete address with street, city, postal code...', 'travel-booking'); ?>" 
                       class="travel-booking-input" required>
                <span class="field-description"><?php _e('Be as specific as possible for accurate pickup/dropoff.', 'travel-booking'); ?></span>
            </div>

            <div class="form-actions">
                <button type="submit" class="travel-booking-btn-primary">
                    <?php _e('Save Address', 'travel-booking'); ?>
                </button>
                <button type="button" id="cancel-add-form" class="travel-booking-btn-secondary">
                    <?php _e('Cancel', 'travel-booking'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Favorites List -->
    <div class="favorites-container">
        <?php if (empty($favorites)) : ?>
            <div class="travel-booking-empty-state">
                <div class="travel-booking-empty-icon">📍</div>
                <h3><?php _e('No favorite addresses yet', 'travel-booking'); ?></h3>
                <p><?php _e('Add your frequently used addresses to make booking faster and more convenient.', 'travel-booking'); ?></p>
            </div>
        <?php else : ?>
            <div class="favorites-stats">
                <p><?php printf(_n('You have %d favorite address', 'You have %d favorite addresses', count($favorites), 'travel-booking'), count($favorites)); ?></p>
            </div>

            <div class="favorites-grid" id="favorites-list">
                <?php foreach ($favorites as $favorite) : ?>
                    <div class="favorite-card" data-favorite-id="<?php echo esc_attr($favorite['id']); ?>">
                        <div class="favorite-header">
                            <div class="favorite-type-icon">
                                <?php
                                $type_icons = array(
                                    'home' => '🏠',
                                    'work' => '🏢',
                                    'airport' => '✈️',
                                    'hotel' => '🏨',
                                    'restaurant' => '🍴',
                                    'other' => '📍'
                                );
                                echo $type_icons[$favorite['type']] ?? '📍';
                                ?>
                            </div>
                            <div class="favorite-info">
                                <h3 class="favorite-name"><?php echo esc_html($favorite['name']); ?></h3>
                                <span class="favorite-type-label"><?php echo esc_html(ucfirst($favorite['type'])); ?></span>
                            </div>
                            <div class="favorite-actions">
                                <button type="button" class="action-btn edit-favorite" data-favorite-id="<?php echo esc_attr($favorite['id']); ?>" title="<?php _e('Edit', 'travel-booking'); ?>">
                                    ✏️
                                </button>
                                <button type="button" class="action-btn remove-favorite" data-favorite-id="<?php echo esc_attr($favorite['id']); ?>" title="<?php _e('Remove', 'travel-booking'); ?>">
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <div class="favorite-body">
                            <div class="favorite-address">
                                <span class="address-icon">📍</span>
                                <span class="address-text"><?php echo esc_html($favorite['address']); ?></span>
                            </div>
                            
                            <div class="favorite-meta">
                                <span class="added-date">
                                    <?php _e('Added', 'travel-booking'); ?> <?php echo date_i18n(get_option('date_format'), strtotime($favorite['created_at'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="favorite-footer">
                            <div class="quick-actions">
                                <button type="button" class="quick-action-btn use-as-pickup" data-address="<?php echo esc_attr($favorite['address']); ?>">
                                    <span class="action-icon">🚗</span>
                                    <?php _e('Use as Pickup', 'travel-booking'); ?>
                                </button>
                                <button type="button" class="quick-action-btn use-as-destination" data-address="<?php echo esc_attr($favorite['address']); ?>">
                                    <span class="action-icon">🏁</span>
                                    <?php _e('Use as Destination', 'travel-booking'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Bulk Actions -->
            <div class="favorites-bulk-actions">
                <h3><?php _e('Manage All Addresses', 'travel-booking'); ?></h3>
                <div class="bulk-actions-grid">
                    <button type="button" id="export-favorites" class="travel-booking-btn-outline">
                        <span class="btn-icon">📤</span>
                        <?php _e('Export Addresses', 'travel-booking'); ?>
                    </button>
                    <button type="button" id="import-favorites" class="travel-booking-btn-outline">
                        <span class="btn-icon">📥</span>
                        <?php _e('Import Addresses', 'travel-booking'); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Booking Section -->
    <div class="quick-booking-section">
        <h3><?php _e('Quick Booking', 'travel-booking'); ?></h3>
        <p class="section-description"><?php _e('Use your favorite addresses to quickly create a new booking.', 'travel-booking'); ?></p>
        
        <form id="quick-booking-form" class="quick-booking-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="quick_pickup"><?php _e('Pickup Address', 'travel-booking'); ?></label>
                    <select id="quick_pickup" name="pickup" class="travel-booking-select">
                        <option value=""><?php _e('Select pickup address...', 'travel-booking'); ?></option>
                        <?php foreach ($favorites as $favorite) : ?>
                            <option value="<?php echo esc_attr($favorite['address']); ?>">
                                <?php echo esc_html($favorite['name'] . ' - ' . $favorite['address']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quick_destination"><?php _e('Destination Address', 'travel-booking'); ?></label>
                    <select id="quick_destination" name="destination" class="travel-booking-select">
                        <option value=""><?php _e('Select destination address...', 'travel-booking'); ?></option>
                        <?php foreach ($favorites as $favorite) : ?>
                            <option value="<?php echo esc_attr($favorite['address']); ?>">
                                <?php echo esc_html($favorite['name'] . ' - ' . $favorite['address']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <button type="button" id="proceed-to-booking" class="travel-booking-btn-primary" disabled>
                        <?php _e('Book This Trip', 'travel-booking'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Messages -->
    <div id="favorites-messages" class="travel-booking-messages" style="display: none;"></div>
</div>

<!-- Import Modal (hidden) -->
<div id="import-modal" class="travel-booking-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Import Addresses', 'travel-booking'); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p><?php _e('Import addresses from a CSV file. Format: Name, Type, Address', 'travel-booking'); ?></p>
            <input type="file" id="import-file" accept=".csv" class="travel-booking-input">
            <div class="import-preview" id="import-preview" style="display: none;">
                <h4><?php _e('Preview:', 'travel-booking'); ?></h4>
                <div class="preview-content"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" id="confirm-import" class="travel-booking-btn-primary" disabled>
                <?php _e('Import Addresses', 'travel-booking'); ?>
            </button>
            <button type="button" class="travel-booking-btn-secondary modal-close">
                <?php _e('Cancel', 'travel-booking'); ?>
            </button>
        </div>
    </div>
</div>