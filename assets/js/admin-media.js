/**
 * Admin media uploader script
 */
jQuery(document).ready(function($) {
    // Initialize media uploader
    var mediaUploader;
    
    // Handle media upload button click
    $('.travel-booking-upload-button').on('click', function(e) {
        e.preventDefault();
        
        // Get target input field
        var targetField = $(this).data('target');
        
        // Create media uploader instance if not exists
        if (!mediaUploader) {
            mediaUploader = wp.media({
                title: travelBookingAdminMedia.title || 'Select Image',
                button: {
                    text: travelBookingAdminMedia.button || 'Use this image'
                },
                multiple: false
            });
            
            // When an image is selected, run a callback
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Set image URL to input field
                $('#' + targetField).val(attachment.url);
                
                // Update image preview
                $('#' + targetField + '-preview').attr('src', attachment.url).show();
                
                // Show remove button
                $('#' + targetField + '-remove').show();
            });
        }
        
        // Open media uploader
        mediaUploader.open();
    });
    
    // Handle remove button click
    $('.travel-booking-remove-button').on('click', function(e) {
        e.preventDefault();
        
        // Get target input field
        var targetField = $(this).data('target');
        
        // Clear input field
        $('#' + targetField).val('');
        
        // Hide image preview
        $('#' + targetField + '-preview').attr('src', '').hide();
        
        // Hide remove button
        $(this).hide();
    });
});