<?php
/**
 * Customer bookings list template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="travel-booking-customer-section">
    <div class="travel-booking-customer-header">
        <h2><?php _e('My Travel Bookings', 'travel-booking'); ?></h2>
        <p class="travel-booking-description"><?php _e('View and manage your travel bookings.', 'travel-booking'); ?></p>
    </div>

    <?php if (empty($bookings)) : ?>
        <div class="travel-booking-empty-state">
            <div class="travel-booking-empty-icon">🚗</div>
            <h3><?php _e('No bookings yet', 'travel-booking'); ?></h3>
            <p><?php _e('You haven\'t made any travel bookings yet.', 'travel-booking'); ?></p>
            <a href="<?php echo esc_url(get_permalink(get_option('travel_booking_booking_page_id'))); ?>" class="travel-booking-btn-primary">
                <?php _e('Book Your First Trip', 'travel-booking'); ?>
            </a>
        </div>
    <?php else : ?>
        <div class="travel-booking-stats-row">
            <?php
            $stats = array(
                'total' => count($bookings),
                'confirmed' => count(array_filter($bookings, function($b) { return $b->status === 'confirmed'; })),
                'completed' => count(array_filter($bookings, function($b) { return $b->status === 'completed'; })),
                'cancelled' => count(array_filter($bookings, function($b) { return $b->status === 'cancelled'; }))
            );
            ?>
            <div class="travel-booking-stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label"><?php _e('Total Bookings', 'travel-booking'); ?></div>
            </div>
            <div class="travel-booking-stat-card">
                <div class="stat-number confirmed"><?php echo $stats['confirmed']; ?></div>
                <div class="stat-label"><?php _e('Confirmed', 'travel-booking'); ?></div>
            </div>
            <div class="travel-booking-stat-card">
                <div class="stat-number completed"><?php echo $stats['completed']; ?></div>
                <div class="stat-label"><?php _e('Completed', 'travel-booking'); ?></div>
            </div>
            <div class="travel-booking-stat-card">
                <div class="stat-number cancelled"><?php echo $stats['cancelled']; ?></div>
                <div class="stat-label"><?php _e('Cancelled', 'travel-booking'); ?></div>
            </div>
        </div>

        <div class="travel-booking-filters">
            <select id="status-filter" class="travel-booking-filter-select">
                <option value=""><?php _e('All Statuses', 'travel-booking'); ?></option>
                <option value="pending"><?php _e('Pending', 'travel-booking'); ?></option>
                <option value="confirmed"><?php _e('Confirmed', 'travel-booking'); ?></option>
                <option value="completed"><?php _e('Completed', 'travel-booking'); ?></option>
                <option value="cancelled"><?php _e('Cancelled', 'travel-booking'); ?></option>
            </select>
            
            <input type="text" id="search-bookings" class="travel-booking-filter-input" placeholder="<?php _e('Search by destination...', 'travel-booking'); ?>">
        </div>

        <div class="travel-booking-bookings-grid">
            <?php foreach ($bookings as $booking) : ?>
                <div class="travel-booking-booking-card" data-status="<?php echo esc_attr($booking->status); ?>" data-destination="<?php echo esc_attr(strtolower($booking->destination)); ?>">
                    <div class="booking-card-header">
                        <div class="booking-info">
                            <h3 class="booking-title">
                                <?php echo esc_html($booking->departure); ?> → <?php echo esc_html($booking->destination); ?>
                            </h3>
                            <div class="booking-meta">
                                <span class="booking-id"><?php _e('Booking', 'travel-booking'); ?> #<?php echo esc_html($booking->id); ?></span>
                                <span class="booking-date">
                                    <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->travel_date)); ?>
                                </span>
                            </div>
                        </div>
                        <div class="booking-status">
                            <span class="status-badge <?php echo Travel_Booking_Customer_Area::get_status_class($booking->status); ?>">
                                <?php echo Travel_Booking_Customer_Area::get_status_label($booking->status); ?>
                            </span>
                        </div>
                    </div>

                    <div class="booking-card-body">
                        <div class="booking-details-grid">
                            <div class="booking-detail">
                                <div class="detail-icon">🚗</div>
                                <div class="detail-content">
                                    <div class="detail-label"><?php _e('Vehicle', 'travel-booking'); ?></div>
                                    <div class="detail-value"><?php echo esc_html($booking->vehicle_name ?: 'N/A'); ?></div>
                                </div>
                            </div>

                            <div class="booking-detail">
                                <div class="detail-icon">👥</div>
                                <div class="detail-content">
                                    <div class="detail-label"><?php _e('Passengers', 'travel-booking'); ?></div>
                                    <div class="detail-value"><?php echo esc_html($booking->number_of_passengers); ?></div>
                                </div>
                            </div>

                            <div class="booking-detail">
                                <div class="detail-icon">📏</div>
                                <div class="detail-content">
                                    <div class="detail-label"><?php _e('Distance', 'travel-booking'); ?></div>
                                    <div class="detail-value"><?php echo esc_html(number_format($booking->distance, 2)); ?> km</div>
                                </div>
                            </div>

                            <div class="booking-detail">
                                <div class="detail-icon">💰</div>
                                <div class="detail-content">
                                    <div class="detail-label"><?php _e('Price', 'travel-booking'); ?></div>
                                    <div class="detail-value"><?php echo esc_html(number_format($booking->price, 2)); ?> CHF</div>
                                </div>
                            </div>

                            <?php if ($booking->round_trip) : ?>
                            <div class="booking-detail">
                                <div class="detail-icon">↩️</div>
                                <div class="detail-content">
                                    <div class="detail-label"><?php _e('Type', 'travel-booking'); ?></div>
                                    <div class="detail-value"><?php _e('Round Trip', 'travel-booking'); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($booking->flight_number)) : ?>
                            <div class="booking-detail">
                                <div class="detail-icon">✈️</div>
                                <div class="detail-content">
                                    <div class="detail-label"><?php _e('Flight', 'travel-booking'); ?></div>
                                    <div class="detail-value"><?php echo esc_html($booking->flight_number); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($booking->notes)) : ?>
                        <div class="booking-notes">
                            <strong><?php _e('Notes:', 'travel-booking'); ?></strong>
                            <p><?php echo esc_html($booking->notes); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="booking-card-footer">
                        <div class="booking-actions">
                            <?php if ($booking->order_id) : ?>
                            <a href="<?php echo esc_url(wc_get_account_endpoint_url('view-order') . $booking->order_id); ?>" class="travel-booking-btn-secondary">
                                <?php _e('View Order', 'travel-booking'); ?>
                            </a>
                            <?php endif; ?>

                            <?php if (Travel_Booking_Customer_Area::can_cancel_booking($booking)) : ?>
                            <button type="button" class="travel-booking-btn-danger cancel-booking" data-booking-id="<?php echo esc_attr($booking->id); ?>">
                                <?php _e('Cancel Booking', 'travel-booking'); ?>
                            </button>
                            <?php endif; ?>

                            <?php if ($booking->status === 'confirmed') : ?>
                            <button type="button" class="travel-booking-btn-info" title="<?php _e('Our driver will contact you 30 minutes before pickup', 'travel-booking'); ?>">
                                <?php _e('Track Status', 'travel-booking'); ?>
                            </button>
                            <?php endif; ?>
                        </div>

                        <div class="booking-created">
                            <?php _e('Booked on', 'travel-booking'); ?> <?php echo date_i18n(get_option('date_format'), strtotime($booking->created_at)); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="travel-booking-quick-actions">
            <h3><?php _e('Quick Actions', 'travel-booking'); ?></h3>
            <div class="quick-actions-grid">
                <a href="<?php echo esc_url(get_permalink(get_option('travel_booking_booking_page_id'))); ?>" class="quick-action-card">
                    <div class="quick-action-icon">🚗</div>
                    <div class="quick-action-title"><?php _e('New Booking', 'travel-booking'); ?></div>
                    <div class="quick-action-desc"><?php _e('Book a new trip', 'travel-booking'); ?></div>
                </a>

                <a href="<?php echo esc_url(wc_get_account_endpoint_url('travel-favorites')); ?>" class="quick-action-card">
                    <div class="quick-action-icon">⭐</div>
                    <div class="quick-action-title"><?php _e('Favorites', 'travel-booking'); ?></div>
                    <div class="quick-action-desc"><?php _e('Manage favorite addresses', 'travel-booking'); ?></div>
                </a>

                <a href="<?php echo esc_url(wc_get_account_endpoint_url('travel-profile')); ?>" class="quick-action-card">
                    <div class="quick-action-icon">👤</div>
                    <div class="quick-action-title"><?php _e('Profile', 'travel-booking'); ?></div>
                    <div class="quick-action-desc"><?php _e('Update travel preferences', 'travel-booking'); ?></div>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>