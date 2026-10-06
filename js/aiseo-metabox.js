/**
 * AISEO Metabox JavaScript
 * Handles AI generation buttons and content analysis
 */

(function($) {
    'use strict';
    
    // Wait for DOM ready
    $(document).ready(function() {
        
        // Check if we're on the post editor
        if (!$('.aiseo-metabox').length) {
            return;
        }
        
        // Character counter
        function updateCharCount(input, counter) {
            var count = input.val().length;
            counter.find('.aiseo-current-count').text(count);
            
            var maxChars = input.attr('maxlength') || 160;
            if (count > maxChars * 0.9) {
                counter.addClass('warning');
            } else {
                counter.removeClass('warning');
            }
        }
        
        // Initialize character counters
        $('.aiseo-title-input').on('input', function() {
            updateCharCount($(this), $(this).parent().parent().find('.aiseo-char-count'));
        }).trigger('input');
        
        $('.aiseo-description-input').on('input', function() {
            updateCharCount($(this), $(this).parent().find('.aiseo-char-count'));
        }).trigger('input');
        
        // Advanced settings - native details/summary element handles toggle automatically
        
        // Generate with AI button
        $('.aiseo-generate-btn').on('click', function(e) {
            e.preventDefault();
            
            var btn = $(this);
            var field = btn.data('field');
            var input;
            
            // Determine which input field to update
            if (field === 'keyword') {
                input = $('#aiseo_focus_keyword');
            } else if (field === 'title') {
                input = $('#aiseo_meta_title');
            } else {
                input = $('#aiseo_meta_description');
            }
            
            var postId = $('#post_ID').val();
            
            if (!postId) {
                alert('Please save the post first before generating AI content.');
                return;
            }
            
            btn.prop('disabled', true).text('Generating...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiseo_metabox_generate_' + field,
                    post_id: postId,
                    nonce: $('#aiseo_metabox_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        input.val(response.data).trigger('input');
                    } else {
                        alert('Error: ' + (response.data || 'Failed to generate'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AISEO Generate Error:', error);
                    alert('Error: Failed to connect to server');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-generic"></span> Generate with AI');
                }
            });
        });
        
        // Analyze content button
        $('.aiseo-analyze-btn').on('click', function(e) {
            e.preventDefault();
            
            var btn = $(this);
            var postId = $('#post_ID').val();
            var resultsDiv = $('.aiseo-analysis-results');
            
            if (!postId) {
                alert('Please save the post first before analyzing content.');
                return;
            }
            
            btn.prop('disabled', true).text('Analyzing...');
            resultsDiv.hide();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiseo_metabox_analyze_content',
                    post_id: postId,
                    nonce: $('#aiseo_metabox_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        var html = '<ul>';
                        
                        // Map of analysis keys to readable labels
                        var labels = {
                            'keyword_density': 'Keyword Density',
                            'readability': 'Readability',
                            'paragraph_structure': 'Paragraph Structure',
                            'sentence_length': 'Sentence Length',
                            'content_length': 'Content Length',
                            'keyword_in_title': 'Keyword in Title',
                            'keyword_in_headings': 'Keyword in Headings',
                            'keyword_in_intro': 'Keyword in Introduction',
                            'internal_links': 'Internal Links',
                            'external_links': 'External Links',
                            'images': 'Images'
                        };
                        
                        $.each(response.data.analyses, function(key, analysis) {
                            var label = labels[key] || key;
                            var statusClass = analysis.status === 'good' ? 'green' : (analysis.status === 'warning' ? 'orange' : 'red');
                            html += '<li><strong>' + label + ':</strong> <span style="color:' + statusClass + '">' + analysis.score + '/100</span> - ' + analysis.message + '</li>';
                        });
                        html += '</ul>';
                        html += '<p><strong>Overall Score: ' + response.data.overall_score + '/100</strong></p>';
                        
                        resultsDiv.find('.aiseo-analysis-content').html(html);
                        resultsDiv.slideDown();
                    } else {
                        alert('Error: ' + (response.data || 'Failed to analyze'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AISEO Analyze Error:', error);
                    alert('Error: Failed to connect to server');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<span class="dashicons dashicons-chart-bar"></span> Analyze Content');
                }
            });
        });
        
        // Preview SEO button
        $('.aiseo-preview-btn').on('click', function(e) {
            e.preventDefault();
            
            var resultsDiv = $('.aiseo-preview-results');
            
            // Get values
            var title = $('#aiseo_meta_title').val() || $('.editor-post-title__input').val() || $('input[name="post_title"]').val() || 'Your Page Title';
            var description = $('#aiseo_meta_description').val() || 'Your meta description will appear here. Add a compelling description to improve click-through rates.';
            var url = $('#aiseo_canonical_url').val() || window.location.href;
            
            // Extract domain from URL
            var domain = url.replace(/^https?:\/\//, '').split('/')[0];
            
            // Build preview HTML
            var previewHtml = '';
            previewHtml += '<div style="color: #1a0dab; font-size: 20px; font-family: arial, sans-serif; line-height: 1.3; margin-bottom: 5px; cursor: pointer;">' + title + '</div>';
            previewHtml += '<div style="color: #006621; font-size: 14px; font-family: arial, sans-serif; margin-bottom: 5px;">' + domain + '</div>';
            previewHtml += '<div style="color: #545454; font-size: 14px; font-family: arial, sans-serif; line-height: 1.5;">' + description + '</div>';
            
            // Show preview
            resultsDiv.find('.aiseo-preview-content').html(previewHtml);
            resultsDiv.slideDown();
            
            // Scroll to preview
            $('html, body').animate({
                scrollTop: resultsDiv.offset().top - 100
            }, 500);
        });
        
    });
    
})(jQuery);
