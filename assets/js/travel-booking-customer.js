/**
 * Travel Booking Customer Area JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initCustomerArea();
    });
    
    function initCustomerArea() {
        // Initialize all customer area functionalities
        initBookingsFilter();
        initProfileForm();
        initFavoritesManagement();
        initQuickBooking();
        initModalHandlers();
    }
    
    /**
     * Initialize bookings filter functionality
     */
    function initBookingsFilter() {
        // Status filter
        $('#status-filter').on('change', function() {
            const selectedStatus = $(this).val();
            filterBookings();
        });
        
        // Search filter
        $('#search-bookings').on('input', function() {
            filterBookings();
        });
        
        // Cancel booking
        $(document).on('click', '.cancel-booking', function() {
            const bookingId = $(this).data('booking-id');
            
            if (confirm(travelBookingCustomer.i18n.confirm_cancel)) {
                cancelBooking(bookingId, $(this));
            }
        });
        
        function filterBookings() {
            const statusFilter = $('#status-filter').val().toLowerCase();
            const searchTerm = $('#search-bookings').val().toLowerCase();
            
            $('.travel-booking-booking-card').each(function() {
                const $card = $(this);
                const cardStatus = $card.data('status');
                const cardDestination = $card.data('destination');
                
                let showCard = true;
                
                // Status filter
                if (statusFilter && cardStatus !== statusFilter) {
                    showCard = false;
                }
                
                // Search filter
                if (searchTerm && cardDestination.indexOf(searchTerm) === -1) {
                    showCard = false;
                }
                
                if (showCard) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });
        }
        
        function cancelBooking(bookingId, $button) {
            const originalText = $button.text();
            $button.text('Cancelling...').prop('disabled', true);
            
            $.ajax({
                url: travelBookingCustomer.ajax_url,
                type: 'POST',
                data: {
                    action: 'cancel_booking',
                    booking_id: bookingId,
                    nonce: travelBookingCustomer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data);
                        // Update the card status
                        const $card = $button.closest('.travel-booking-booking-card');
                        $card.find('.status-badge')
                            .removeClass('status-pending status-confirmed')
                            .addClass('status-cancelled')
                            .text('Cancelled');
                        $button.remove();
                    } else {
                        showMessage('error', response.data);
                        $button.text(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    showMessage('error', travelBookingCustomer.i18n.error);
                    $button.text(originalText).prop('disabled', false);
                }
            });
        }
    }
    
    /**
     * Initialize profile form functionality
     */
    function initProfileForm() {
        $('#travel-profile-form').on('submit', function(e) {
            e.preventDefault();
            saveProfile();
        });
        
        $('#reset-profile').on('click', function() {
            if (confirm('Are you sure you want to reset all profile settings to default?')) {
                resetProfile();
            }
        });
        
        function saveProfile() {
            const $form = $('#travel-profile-form');
            const $button = $('#save-profile');
            const originalText = $button.text();
            
            $button.text('Saving...').prop('disabled', true);
            
            const formData = $form.serialize();
            
            $.ajax({
                url: travelBookingCustomer.ajax_url,
                type: 'POST',
                data: formData + '&action=save_travel_profile',
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data, '#profile-messages');
                        // Scroll to message
                        $('html, body').animate({
                            scrollTop: $('#profile-messages').offset().top - 100
                        }, 500);
                    } else {
                        showMessage('error', response.data, '#profile-messages');
                    }
                },
                error: function() {
                    showMessage('error', travelBookingCustomer.i18n.error, '#profile-messages');
                },
                complete: function() {
                    $button.text(originalText).prop('disabled', false);
                }
            });
        }
        
        function resetProfile() {
            $('#travel-profile-form')[0].reset();
            showMessage('success', 'Profile reset to default values.', '#profile-messages');
        }
    }
    
    /**
     * Initialize favorites management
     */
    function initFavoritesManagement() {
        // Toggle add form
        $('#toggle-add-form').on('click', function() {
            $('#add-favorite-form').slideToggle();
            $(this).text($(this).text() === '➕ Add New Address' ? '➖ Cancel' : '➕ Add New Address');
        });
        
        $('#cancel-add-form').on('click', function() {
            $('#add-favorite-form').slideUp();
            $('#toggle-add-form').text('➕ Add New Address');
        });
        
        // Add favorite form
        $('#add-favorite-form').on('submit', function(e) {
            e.preventDefault();
            addFavoriteAddress();
        });
        
        // Remove favorite
        $(document).on('click', '.remove-favorite', function() {
            const favoriteId = $(this).data('favorite-id');
            
            if (confirm('Are you sure you want to remove this address?')) {
                removeFavoriteAddress(favoriteId, $(this));
            }
        });
        
        // Use as pickup/destination
        $(document).on('click', '.use-as-pickup, .use-as-destination', function() {
            const address = $(this).data('address');
            const isPickup = $(this).hasClass('use-as-pickup');
            
            useAddressForBooking(address, isPickup);
        });
        
        // Export favorites
        $('#export-favorites').on('click', function() {
            exportFavorites();
        });
        
        // Import favorites
        $('#import-favorites').on('click', function() {
            $('#import-modal').show();
        });
        
        function addFavoriteAddress() {
            const $form = $('#add-favorite-form');
            const formData = $form.serialize();
            
            $.ajax({
                url: travelBookingCustomer.ajax_url,
                type: 'POST',
                data: formData + '&action=add_favorite_address',
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message, '#favorites-messages');
                        addFavoriteToList(response.data.favorite);
                        $form[0].reset();
                        $('#add-favorite-form').slideUp();
                        $('#toggle-add-form').text('➕ Add New Address');
                    } else {
                        showMessage('error', response.data, '#favorites-messages');
                    }
                },
                error: function() {
                    showMessage('error', travelBookingCustomer.i18n.error, '#favorites-messages');
                }
            });
        }
        
        function removeFavoriteAddress(favoriteId, $button) {
            $.ajax({
                url: travelBookingCustomer.ajax_url,
                type: 'POST',
                data: {
                    action: 'remove_favorite_address',
                    favorite_id: favoriteId,
                    nonce: travelBookingCustomer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data, '#favorites-messages');
                        $button.closest('.favorite-card').fadeOut(300, function() {
                            $(this).remove();
                            updateFavoritesCount();
                        });
                    } else {
                        showMessage('error', response.data, '#favorites-messages');
                    }
                },
                error: function() {
                    showMessage('error', travelBookingCustomer.i18n.error, '#favorites-messages');
                }
            });
        }
        
        function addFavoriteToList(favorite) {
            const typeIcons = {
                'home': '🏠',
                'work': '🏢',
                'airport': '✈️',
                'hotel': '🏨',
                'restaurant': '🍴',
                'other': '📍'
            };
            
            const favoriteHtml = `
                <div class="favorite-card" data-favorite-id="${favorite.id}">
                    <div class="favorite-header">
                        <div class="favorite-type-icon">${typeIcons[favorite.type] || '📍'}</div>
                        <div class="favorite-info">
                            <h3 class="favorite-name">${favorite.name}</h3>
                            <span class="favorite-type-label">${favorite.type.charAt(0).toUpperCase() + favorite.type.slice(1)}</span>
                        </div>
                        <div class="favorite-actions">
                            <button type="button" class="action-btn edit-favorite" data-favorite-id="${favorite.id}" title="Edit">✏️</button>
                            <button type="button" class="action-btn remove-favorite" data-favorite-id="${favorite.id}" title="Remove">🗑️</button>
                        </div>
                    </div>
                    <div class="favorite-body">
                        <div class="favorite-address">
                            <span class="address-icon">📍</span>
                            <span class="address-text">${favorite.address}</span>
                        </div>
                        <div class="favorite-meta">
                            <span class="added-date">Added just now</span>
                        </div>
                    </div>
                    <div class="favorite-footer">
                        <div class="quick-actions">
                            <button type="button" class="quick-action-btn use-as-pickup" data-address="${favorite.address}">
                                <span class="action-icon">🚗</span> Use as Pickup
                            </button>
                            <button type="button" class="quick-action-btn use-as-destination" data-address="${favorite.address}">
                                <span class="action-icon">🏁</span> Use as Destination
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('#favorites-list').append(favoriteHtml);
            updateFavoritesCount();
            
            // Add to quick booking selects
            const optionHtml = `<option value="${favorite.address}">${favorite.name} - ${favorite.address}</option>`;
            $('#quick_pickup, #quick_destination').append(optionHtml);
        }
        
        function updateFavoritesCount() {
            const count = $('.favorite-card').length;
            $('.favorites-stats p').text(`You have ${count} favorite address${count !== 1 ? 'es' : ''}`);
        }
        
        function useAddressForBooking(address, isPickup) {
            // Store in session storage to pre-fill booking form
            if (isPickup) {
                sessionStorage.setItem('travel_booking_departure', address);
            } else {
                sessionStorage.setItem('travel_booking_destination', address);
            }
            
            // Get booking page URL and redirect
            const bookingPageUrl = '/booking/'; // You might want to pass this as a parameter
            window.location.href = bookingPageUrl;
        }
        
        function exportFavorites() {
            const favorites = [];
            $('.favorite-card').each(function() {
                const $card = $(this);
                favorites.push({
                    name: $card.find('.favorite-name').text(),
                    type: $card.find('.favorite-type-label').text(),
                    address: $card.find('.address-text').text()
                });
            });
            
            const csvContent = "data:text/csv;charset=utf-8," 
                + "Name,Type,Address\n"
                + favorites.map(f => `"${f.name}","${f.type}","${f.address}"`).join("\n");
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "favorite_addresses.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
    
    /**
     * Initialize quick booking functionality
     */
    function initQuickBooking() {
        $('#quick_pickup, #quick_destination').on('change', function() {
            checkQuickBookingForm();
        });
        
        $('#proceed-to-booking').on('click', function() {
            const pickup = $('#quick_pickup').val();
            const destination = $('#quick_destination').val();
            
            if (pickup && destination) {
                // Store addresses in session storage
                sessionStorage.setItem('travel_booking_departure', pickup);
                sessionStorage.setItem('travel_booking_destination', destination);
                
                // Redirect to booking page
                window.location.href = '/booking/'; // You might want to pass this as a parameter
            }
        });
        
        function checkQuickBookingForm() {
            const pickup = $('#quick_pickup').val();
            const destination = $('#quick_destination').val();
            
            $('#proceed-to-booking').prop('disabled', !pickup || !destination || pickup === destination);
        }
    }
    
    /**
     * Initialize modal handlers
     */
    function initModalHandlers() {
        // Close modal handlers
        $('.modal-close').on('click', function() {
            $(this).closest('.travel-booking-modal').hide();
        });
        
        // Close modal on backdrop click
        $('.travel-booking-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
        
        // Import file handling
        $('#import-file').on('change', function() {
            const file = this.files[0];
            if (file && file.type === 'text/csv') {
                previewImportFile(file);
            } else {
                alert('Please select a valid CSV file.');
                $(this).val('');
            }
        });
        
        $('#confirm-import').on('click', function() {
            processImportFile();
        });
        
        function previewImportFile(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const csv = e.target.result;
                const lines = csv.split('\n');
                const preview = lines.slice(0, 6); // Show first 5 data rows + header
                
                let previewHtml = '<table class="import-preview-table"><thead><tr>';
                const headers = preview[0].split(',');
                headers.forEach(header => {
                    previewHtml += `<th>${header.trim().replace(/"/g, '')}</th>`;
                });
                previewHtml += '</tr></thead><tbody>';
                
                for (let i = 1; i < preview.length && i < 6; i++) {
                    if (preview[i].trim()) {
                        previewHtml += '<tr>';
                        const cells = preview[i].split(',');
                        cells.forEach(cell => {
                            previewHtml += `<td>${cell.trim().replace(/"/g, '')}</td>`;
                        });
                        previewHtml += '</tr>';
                    }
                }
                
                previewHtml += '</tbody></table>';
                $('#import-preview .preview-content').html(previewHtml);
                $('#import-preview').show();
                $('#confirm-import').prop('disabled', false);
            };
            reader.readAsText(file);
        }
        
        function processImportFile() {
            const file = $('#import-file')[0].files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const csv = e.target.result;
                const lines = csv.split('\n');
                const addresses = [];
                
                // Skip header and process data
                for (let i = 1; i < lines.length; i++) {
                    const line = lines[i].trim();
                    if (line) {
                        const cells = line.split(',').map(cell => cell.trim().replace(/"/g, ''));
                        if (cells.length >= 3) {
                            addresses.push({
                                name: cells[0],
                                type: cells[1].toLowerCase(),
                                address: cells[2]
                            });
                        }
                    }
                }
                
                // Import addresses one by one
                importAddressesBatch(addresses);
            };
            reader.readAsText(file);
        }
        
        function importAddressesBatch(addresses) {
            let imported = 0;
            const total = addresses.length;
            
            function importNext() {
                if (imported >= total) {
                    $('#import-modal').hide();
                    showMessage('success', `Successfully imported ${imported} addresses.`, '#favorites-messages');
                    return;
                }
                
                const address = addresses[imported];
                
                $.ajax({
                    url: travelBookingCustomer.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'add_favorite_address',
                        name: address.name,
                        type: address.type,
                        address: address.address,
                        nonce: travelBookingCustomer.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            addFavoriteToList(response.data.favorite);
                        }
                        imported++;
                        setTimeout(importNext, 100); // Small delay to avoid overwhelming the server
                    },
                    error: function() {
                        imported++;
                        setTimeout(importNext, 100);
                    }
                });
            }
            
            importNext();
        }
    }
    
    /**
     * Show message to user
     */
    function showMessage(type, message, container = '#favorites-messages') {
        const $container = $(container);
        $container.removeClass('success error').addClass(type);
        $container.text(message).show();
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            $container.fadeOut();
        }, 5000);
        
        // Scroll to message if it's not visible
        if ($container.offset().top < $(window).scrollTop() || 
            $container.offset().top > $(window).scrollTop() + $(window).height()) {
            $('html, body').animate({
                scrollTop: $container.offset().top - 100
            }, 500);
        }
    }
    
    /**
     * Utility function to format date
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }
    
    /**
     * Utility function to format currency
     */
    function formatCurrency(amount, currency = 'CHF') {
        return parseFloat(amount).toFixed(2) + ' ' + currency;
    }
    
    /**
     * Initialize tooltips if available
     */
    function initTooltips() {
        if (typeof $.fn.tooltip !== 'undefined') {
            $('[title]').tooltip();
        }
    }
    
    // Initialize tooltips
    initTooltips();
    
    // Handle loading states
    $(document).ajaxStart(function() {
        $('body').addClass('ajax-loading');
    }).ajaxStop(function() {
        $('body').removeClass('ajax-loading');
    });
    
    // Auto-fill booking form if coming from favorites
    $(window).on('load', function() {
        if (window.location.pathname.includes('booking')) {
            const departure = sessionStorage.getItem('travel_booking_departure');
            const destination = sessionStorage.getItem('travel_booking_destination');
            
            if (departure) {
                $('#departure').val(departure);
                sessionStorage.removeItem('travel_booking_departure');
            }
            
            if (destination) {
                $('#destination').val(destination);
                sessionStorage.removeItem('travel_booking_destination');
            }
        }
    });
    
    // Enhanced search functionality
    function enhancedSearch() {
        let searchTimeout;
        
        $('#search-bookings').on('input', function() {
            const searchTerm = $(this).val();
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch(searchTerm);
            }, 300);
        });
        
        function performSearch(term) {
            if (!term) {
                $('.travel-booking-booking-card').show();
                return;
            }
            
            term = term.toLowerCase();
            
            $('.travel-booking-booking-card').each(function() {
                const $card = $(this);
                const searchableText = [
                    $card.find('.booking-title').text(),
                    $card.find('.booking-id').text(),
                    $card.find('.detail-value').text()
                ].join(' ').toLowerCase();
                
                if (searchableText.includes(term)) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });
        }
    }
    
    // Initialize enhanced search
    enhancedSearch();
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Escape key closes modals
        if (e.key === 'Escape') {
            $('.travel-booking-modal:visible').hide();
        }
        
        // Ctrl/Cmd + K opens search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            $('#search-bookings').focus();
        }
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Print functionality for bookings
    function initPrintFunctionality() {
        $(document).on('click', '.print-booking', function() {
            const $card = $(this).closest('.travel-booking-booking-card');
            const printContent = $card.html();
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Booking Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .booking-card-header { border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 15px; }
                        .booking-title { font-size: 1.5em; font-weight: bold; margin-bottom: 10px; }
                        .booking-details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
                        .booking-detail { display: flex; align-items: center; gap: 10px; }
                        .detail-label { font-weight: bold; }
                        .status-badge { padding: 5px 10px; border-radius: 15px; font-weight: bold; }
                        .status-confirmed { background: #d4edda; color: #155724; }
                        .status-pending { background: #fff3cd; color: #856404; }
                        .status-completed { background: #cce7ff; color: #004085; }
                        .status-cancelled { background: #f8d7da; color: #721c24; }
                    </style>
                </head>
                <body>
                    <h1>Travel Booking Details</h1>
                    ${printContent}
                    <script>window.print(); window.close();</script>
                </body>
                </html>
            `);
            printWindow.document.close();
        });
    }
    
    // Initialize print functionality
    initPrintFunctionality();
    
})(jQuery);

// Global utility functions
window.TravelBookingCustomer = {
    showMessage: function(type, message, container) {
        const $container = jQuery(container || '#favorites-messages');
        $container.removeClass('success error').addClass(type);
        $container.text(message).show();
        
        setTimeout(function() {
            $container.fadeOut();
        }, 5000);
    },
    
    refreshBookings: function() {
        location.reload();
    },
    
    exportData: function(data, filename, type = 'csv') {
        let content, mimeType;
        
        if (type === 'csv') {
            const headers = Object.keys(data[0]);
            content = headers.join(',') + '\n';
            content += data.map(row => headers.map(header => `"${row[header]}"`).join(',')).join('\n');
            mimeType = 'text/csv';
        } else if (type === 'json') {
            content = JSON.stringify(data, null, 2);
            mimeType = 'application/json';
        }
        
        const blob = new Blob([content], { type: mimeType });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    }
};