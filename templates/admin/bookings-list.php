<?php
/**
 * Admin bookings list template
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Bookings', 'travel-booking'); ?></h1>
    <hr class="wp-header-end">
    
    <!-- Filtres -->
    <div class="travel-booking-filters" style="margin: 20px 0;">
        <form method="get">
            <input type="hidden" name="page" value="travel-booking-bookings">
            
            <select name="status">
                <option value=""><?php _e('All statuses', 'travel-booking'); ?></option>
                <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'travel-booking'); ?></option>
                <option value="confirmed" <?php selected($status, 'confirmed'); ?>><?php _e('Confirmed', 'travel-booking'); ?></option>
                <option value="completed" <?php selected($status, 'completed'); ?>><?php _e('Completed', 'travel-booking'); ?></option>
                <option value="cancelled" <?php selected($status, 'cancelled'); ?>><?php _e('Cancelled', 'travel-booking'); ?></option>
            </select>
            
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search bookings...', 'travel-booking'); ?>">
            
            <input type="submit" class="button" value="<?php _e('Filter', 'travel-booking'); ?>">
            
            <?php if (!empty($status) || !empty($search)) : ?>
                <a href="<?php echo admin_url('admin.php?page=travel-booking-bookings'); ?>" class="button"><?php _e('Clear', 'travel-booking'); ?></a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (empty($bookings)) : ?>
        <div class="notice notice-info">
            <p><?php _e('No bookings found.', 'travel-booking'); ?></p>
        </div>
    <?php else : ?>
        <!-- Actions en masse -->
        <form id="bookings-filter" method="post">
            <?php wp_nonce_field('travel_booking_bulk_action', 'travel_booking_bulk_nonce'); ?>
            
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="action" id="bulk-action-selector-top">
                        <option value="-1"><?php _e('Bulk Actions', 'travel-booking'); ?></option>
                        <option value="delete"><?php _e('Delete', 'travel-booking'); ?></option>
                        <option value="confirm"><?php _e('Confirm', 'travel-booking'); ?></option>
                        <option value="cancel"><?php _e('Cancel', 'travel-booking'); ?></option>
                    </select>
                    <input type="submit" id="doaction" class="button action" value="<?php _e('Apply', 'travel-booking'); ?>">
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped table-view-list travel-booking-table">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <input id="cb-select-all-1" type="checkbox">
                        </td>
                        <th scope="col" class="manage-column column-id"><?php _e('ID', 'travel-booking'); ?></th>
                        <th scope="col" class="manage-column column-customer"><?php _e('Customer', 'travel-booking'); ?></th>
                        <th scope="col" class="manage-column column-from"><?php _e('From', 'travel-booking'); ?></th>
                        <th scope="col" class="manage-column column-to"><?php _e('To', 'travel-booking'); ?></th>
                        <th scope="col" class="manage-column column-date"><?php _e('Date', 'travel-booking'); ?></th>
                        <th scope="col" class="manage-column column-status"><?php _e('Status', 'travel-booking'); ?></th>
                        <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'travel-booking'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking) : ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="booking_ids[]" value="<?php echo $booking->id; ?>">
                            </th>
                            <td class="column-id">
                                <strong>#<?php echo $booking->id; ?></strong>
                            </td>
                            <td class="column-customer">
                                <?php
                                $customer_name = trim($booking->client_first_name . ' ' . $booking->client_last_name);
                                echo !empty($customer_name) ? esc_html($customer_name) : '—';
                                
                                if (!empty($booking->client_email)) {
                                    echo '<br><small>' . esc_html($booking->client_email) . '</small>';
                                }
                                ?>
                            </td>
                            <td class="column-from" title="<?php echo esc_attr($booking->departure); ?>">
                                <?php echo esc_html(wp_trim_words($booking->departure, 3)); ?>
                            </td>
                            <td class="column-to" title="<?php echo esc_attr($booking->destination); ?>">
                                <?php echo esc_html(wp_trim_words($booking->destination, 3)); ?>
                            </td>
                            <td class="column-date">
                                <?php echo date_i18n('d/m/Y H:i', strtotime($booking->travel_date)); ?>
                            </td>
                            <td class="column-status">
                                <span class="status-indicator status-<?php echo esc_attr($booking->status); ?>">
                                    <?php echo esc_html($booking->status); ?>
                                </span>
                            </td>
                            <td class="column-actions">
                                <div class="row-actions">
                                    <span class="view">
                                        <a href="<?php echo admin_url('admin.php?page=travel-booking-bookings&action=view&id=' . $booking->id); ?>">
                                            <?php _e('View', 'travel-booking'); ?>
                                        </a>
                                    </span>
                                    
                                    <?php if ($booking->status !== 'confirmed') : ?>
                                    | <span class="confirm">
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travel-booking-bookings&action=update_status&id=' . $booking->id . '&status=confirmed'), 'travel_booking_update_status_' . $booking->id); ?>">
                                            <?php _e('Confirm', 'travel-booking'); ?>
                                        </a>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking->status !== 'cancelled') : ?>
                                    | <span class="cancel">
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travel-booking-bookings&action=update_status&id=' . $booking->id . '&status=cancelled'), 'travel_booking_update_status_' . $booking->id); ?>">
                                            <?php _e('Cancel', 'travel-booking'); ?>
                                        </a>
                                    </span>
                                    <?php endif; ?>
                                    
                                    | <span class="delete">
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travel-booking-bookings&action=delete&id=' . $booking->id), 'travel_booking_delete_booking_' . $booking->id); ?>" 
                                           onclick="return confirm('<?php _e('Are you sure you want to delete this booking?', 'travel-booking'); ?>')"
                                           style="color: #a00;">
                                            <?php _e('Delete', 'travel-booking'); ?>
                                        </a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <select name="action2" id="bulk-action-selector-bottom">
                        <option value="-1"><?php _e('Bulk Actions', 'travel-booking'); ?></option>
                        <option value="delete"><?php _e('Delete', 'travel-booking'); ?></option>
                        <option value="confirm"><?php _e('Confirm', 'travel-booking'); ?></option>
                        <option value="cancel"><?php _e('Cancel', 'travel-booking'); ?></option>
                    </select>
                    <input type="submit" id="doaction2" class="button action" value="<?php _e('Apply', 'travel-booking'); ?>">
                </div>
            </div>
        </form>
        
        <script>
        // Gestion des cases à cocher
        jQuery(document).ready(function($) {
            $('#cb-select-all-1').on('click', function() {
                $('input[name="booking_ids[]"]').prop('checked', this.checked);
            });
            
            // Confirmation pour suppression en masse
            $('#bookings-filter').on('submit', function(e) {
                const action = $('#bulk-action-selector-top').val() || $('#bulk-action-selector-bottom').val();
                const checkedBoxes = $('input[name="booking_ids[]"]:checked');
                
                if (action === '-1') {
                    e.preventDefault();
                    alert('<?php _e('Please select an action.', 'travel-booking'); ?>');
                    return false;
                }
                
                if (checkedBoxes.length === 0) {
                    e.preventDefault();
                    alert('<?php _e('Please select at least one booking.', 'travel-booking'); ?>');
                    return false;
                }
                
                if (action === 'delete') {
                    const confirmMessage = '<?php _e('Are you sure you want to delete the selected bookings? This action cannot be undone.', 'travel-booking'); ?>';
                    if (!confirm(confirmMessage)) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
        </script>
    <?php endif; ?>
</div>