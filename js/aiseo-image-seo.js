/**
 * AISEO Image SEO JavaScript
 * 
 * @package AISEO
 * @since 1.1.0
 */

(function($) {
    'use strict';
    
    // Check if required variables are defined
    if (typeof aiseoImageSeo === 'undefined') {
        console.error('AISEO Image SEO: aiseoImageSeo is not defined! Script localization failed.');
        return;
    }
    
    if (!aiseoImageSeo.ajaxUrl) {
        console.error('AISEO Image SEO: ajaxUrl is not defined!');
        return;
    }
    
    if (!aiseoImageSeo.nonce) {
        console.error('AISEO Image SEO: nonce is not defined!');
        return;
    }
    
    
    let cancelBulk = false;
    
    $(document).ready(function() {
        
        // Select all checkbox
        $('#select-all-images').on('change', function() {
            $('.image-checkbox').prop('checked', $(this).prop('checked'));
            updateBulkButton();
        });
        
        // Individual checkbox
        $('.image-checkbox').on('change', function() {
            updateBulkButton();
            
            // Update select all checkbox
            const total = $('.image-checkbox').length;
            const checked = $('.image-checkbox:checked').length;
            $('#select-all-images').prop('checked', total === checked);
        });
        
        // Generate single alt text
        $('.aiseo-generate-single-alt').on('click', function() {
            const button = $(this);
            const imageId = button.data('image-id');
            const spinner = button.siblings('.spinner');
            const result = button.siblings('.alt-text-result');
            
            button.prop('disabled', true);
            spinner.addClass('is-active');
            result.text('').removeClass('success error');
            
            $.ajax({
                url: aiseoImageSeo.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aiseo_generate_single_alt',
                    nonce: aiseoImageSeo.nonce,
                    image_id: imageId,
                    overwrite: false
                },
                success: function(response) {
                    if (response.success) {
                        result.text(response.data.alt_text).addClass('success');
                        
                        // Remove row after 2 seconds
                        setTimeout(function() {
                            button.closest('tr').fadeOut(function() {
                                $(this).remove();
                                updateStats();
                            });
                        }, 2000);
                    } else {
                        result.text(response.data.message).addClass('error');
                    }
                },
                error: function() {
                    result.text(aiseoImageSeo.strings.error).addClass('error');
                },
                complete: function() {
                    button.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        });
        
        // Bulk generate alt text
        $('#bulk-generate-alt').on('click', function() {
            const selectedIds = $('.image-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedIds.length === 0) {
                alert('Please select images to process.');
                return;
            }
            
            if (!confirm(`Generate alt text for ${selectedIds.length} images?`)) {
                return;
            }
            
            cancelBulk = false;
            processBulkGeneration(selectedIds);
        });
        
        // Cancel bulk operation
        $('#cancel-bulk').on('click', function() {
            cancelBulk = true;
            $(this).prop('disabled', true).text('Cancelling...');
        });
    });
    
    /**
     * Update bulk action button state
     */
    function updateBulkButton() {
        const checked = $('.image-checkbox:checked').length;
        $('#bulk-generate-alt').prop('disabled', checked === 0);
        $('.selected-count').text(checked + ' selected');
    }
    
    /**
     * Process bulk alt text generation
     */
    function processBulkGeneration(imageIds) {
        const total = imageIds.length;
        let processed = 0;
        let success = 0;
        let errors = 0;
        
        // Show progress bar
        $('.aiseo-progress').show();
        $('.progress-text .total').text(total);
        $('.progress-text .current').text(0);
        $('.progress-fill').css('width', '0%');
        $('#cancel-bulk').prop('disabled', false).text('Cancel');
        
        // Hide table
        $('.aiseo-missing-alt-section table').fadeTo(300, 0.3);
        $('.bulk-actions-bar').fadeTo(300, 0.3);
        
        function processNext() {
            if (cancelBulk || processed >= total) {
                finishBulk(success, errors, total);
                return;
            }
            
            const imageId = imageIds[processed];
            
            $.ajax({
                url: aiseoImageSeo.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aiseo_generate_single_alt',
                    nonce: aiseoImageSeo.nonce,
                    image_id: imageId,
                    overwrite: false
                },
                success: function(response) {
                    if (response.success) {
                        success++;
                        // Remove row
                        $('tr[data-image-id="' + imageId + '"]').fadeOut();
                    } else {
                        errors++;
                    }
                },
                error: function() {
                    errors++;
                },
                complete: function() {
                    processed++;
                    
                    // Update progress
                    const percentage = (processed / total) * 100;
                    $('.progress-fill').css('width', percentage + '%');
                    $('.progress-text .current').text(processed);
                    
                    // Continue after delay (rate limiting)
                    setTimeout(processNext, 2000);
                }
            });
        }
        
        processNext();
    }
    
    /**
     * Finish bulk operation
     */
    function finishBulk(success, errors, total) {
        $('.aiseo-progress').hide();
        $('.aiseo-missing-alt-section table').fadeTo(300, 1);
        $('.bulk-actions-bar').fadeTo(300, 1);
        
        let message = `Completed: ${success} successful`;
        if (errors > 0) {
            message += `, ${errors} errors`;
        }
        
        alert(message);
        
        // Reload page to update stats
        location.reload();
    }
    
    /**
     * Update statistics
     */
    function updateStats() {
        const remaining = $('.image-checkbox').length;
        $('.stat-number.warning').text(remaining);
    }
    
})(jQuery);
