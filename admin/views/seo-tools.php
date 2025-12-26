<?php
/**
 * SEO Tools Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get recent posts for selection
$aiseo_recent_posts = wp_get_recent_posts(array(
    'numberposts' => 20,
    'post_status' => 'publish,draft',
));
?>

<div class="aiseo-seo-tools">
    
    <!-- Meta Generation Section -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Meta Generation', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Generate SEO Metadata', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Select Post', 'aiseo'); ?></label>
                    <select id="aiseo-meta-post-select" class="regular-text">
                        <option value=""><?php esc_html_e('-- Select a post --', 'aiseo'); ?></option>
                        <?php foreach ($recent_posts as $post): ?>
                            <option value="<?php echo esc_attr($post['ID']); ?>">
                                <?php echo esc_html($post['post_title']); ?> (<?php echo esc_html($post['post_status']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-generate-meta" data-field="title">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Generate Title', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-primary aiseo-generate-meta" data-field="description">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Generate Description', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-secondary aiseo-generate-meta" data-field="keyword">
                        <span class="dashicons dashicons-tag"></span>
                        <?php esc_html_e('Generate Keyword', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-meta-results" class="aiseo-mt-20" style="display:none;">
                    <h4><?php esc_html_e('Generated Content:', 'aiseo'); ?></h4>
                    <div class="aiseo-alert aiseo-alert-success">
                        <div id="aiseo-meta-output"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Analysis Section -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Content Analysis', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Analyze Post SEO', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Select Post to Analyze', 'aiseo'); ?></label>
                    <select id="aiseo-analyze-post-select" class="regular-text">
                        <option value=""><?php esc_html_e('-- Select a post --', 'aiseo'); ?></option>
                        <?php foreach ($recent_posts as $post): ?>
                            <option value="<?php echo esc_attr($post['ID']); ?>">
                                <?php echo esc_html($post['post_title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="button" class="button button-primary aiseo-analyze-content">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php esc_html_e('Analyze Content', 'aiseo'); ?>
                </button>
                
                <div class="aiseo-analysis-results" style="display:none; margin-top:20px;"></div>
            </div>
        </div>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Analysis Features', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('11 SEO metrics analyzed', 'aiseo'); ?></li>
                    <li><?php esc_html_e('6 readability metrics (Flesch, Gunning Fog, SMOG, etc.)', 'aiseo'); ?></li>
                    <li><?php esc_html_e('40+ advanced SEO factors', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Keyword density analysis', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Content length optimization', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Heading structure analysis', 'aiseo'); ?></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Internal Linking Section -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Internal Linking', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php esc_html_e('Internal Linking Suggestions', 'aiseo'); ?>
                    <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
                </h3>
            </div>
            <div class="aiseo-card-body">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Select Post', 'aiseo'); ?></label>
                    <select id="aiseo-linking-post-select" class="regular-text">
                        <option value=""><?php esc_html_e('-- Select a post --', 'aiseo'); ?></option>
                        <?php foreach ($recent_posts as $post): ?>
                            <option value="<?php echo esc_attr($post['ID']); ?>"><?php echo esc_html($post['post_title']); ?> (<?php echo esc_html($post['post_status']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-get-linking">
                        <span class="dashicons dashicons-admin-links"></span>
                        <?php esc_html_e('Get Linking Suggestions', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-linking-results" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Suggested Internal Links:', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Meta Variations Section -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Meta Variations', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title">
                    <span class="dashicons dashicons-admin-page"></span>
                    <?php esc_html_e('Generate Multiple Variations', 'aiseo'); ?>
                    <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
                </h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Generate 5 variations with AI scoring to pick the best one', 'aiseo'); ?></p>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Select Post', 'aiseo'); ?></label>
                    <select id="aiseo-variations-post-select" class="regular-text">
                        <option value=""><?php esc_html_e('-- Select a post --', 'aiseo'); ?></option>
                        <?php foreach ($recent_posts as $post): ?>
                            <option value="<?php echo esc_attr($post['ID']); ?>"><?php echo esc_html($post['post_title']); ?> (<?php echo esc_html($post['post_status']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-get-title-variations">
                        <span class="dashicons dashicons-admin-page"></span>
                        <?php esc_html_e('Generate Title Variations', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button aiseo-get-desc-variations">
                        <span class="dashicons dashicons-admin-page"></span>
                        <?php esc_html_e('Generate Description Variations', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-variations-results" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Variations (with AI scores):', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Schema & Tags Section -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Schema & Social Tags', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Schema Markup', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Automatic JSON-LD schema markup for:', 'aiseo'); ?></p>
                <div class="aiseo-stat-grid">
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('Article', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('BlogPosting', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('WebPage', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('FAQ', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('HowTo', 'aiseo'); ?></div>
                    </div>
                </div>
                <p class="aiseo-mt-20">
                    <em><?php esc_html_e('Schema is automatically added to all posts and pages', 'aiseo'); ?></em>
                </p>
            </div>
        </div>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Social Media Tags', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Automatic Open Graph and Twitter Card tags:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('og:title, og:description, og:image', 'aiseo'); ?></li>
                    <li><?php esc_html_e('twitter:card, twitter:title, twitter:description', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Automatic image optimization', 'aiseo'); ?></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Internal Linking Section -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Internal Linking', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Link Suggestions', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('AI-powered internal linking suggestions:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('Related content detection', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Orphan page identification', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Link opportunity analysis', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Anchor text optimization', 'aiseo'); ?></li>
                </ul>
                <p class="aiseo-mt-20">
                    <strong><?php esc_html_e('Available via:', 'aiseo'); ?></strong><br>
                    <code>wp aiseo internal-linking suggestions &lt;post-id&gt;</code><br>
                    <code>wp aiseo internal-linking orphans</code>
                </p>
            </div>
        </div>
    </div>
    
</div>

<script>
// Ensure ajaxurl is defined
var ajaxurl = ajaxurl || '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';

jQuery(document).ready(function($) {
    // Clear refresh flag on page load (prevents infinite loops)
    sessionStorage.removeItem('aiseo_nonce_refresh_attempted');
    
    // Meta generation
    $('.aiseo-generate-meta').on('click', function() {
        var btn = $(this);
        var field = btn.data('field');
        var postId = $('#aiseo-meta-post-select').val();
        
        if (!postId) {
            $('#aiseo-meta-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> <?php esc_html_e('Please select a post first', 'aiseo'); ?></div>').show();
            return;
        }
        
        btn.prop('disabled', true).text('<?php esc_html_e('Generating...', 'aiseo'); ?>');
        
        // 🔴 COMPREHENSIVE DEBUG LOGGING
        console.log('========================================');
        console.log('🔴 SEO TOOLS - AJAX REQUEST STARTING');
        console.log('========================================');
        console.log('Timestamp:', new Date().toISOString());
        console.log('Page loaded at: <?php echo esc_js(current_time('Y-m-d H:i:s')); ?>');
        console.log('Field:', field);
        console.log('Post ID:', postId);
        console.log('Action:', 'aiseo_generate_' + field);
        console.log('AJAX URL:', ajaxurl);
        console.log('User ID (PHP):', '<?php echo esc_js(get_current_user_id()); ?>');
        console.log('---');
        console.log('aiseoAdmin object:', aiseoAdmin);
        console.log('aiseoAdmin.nonce:', aiseoAdmin ? aiseoAdmin.nonce : 'UNDEFINED');
        console.log('aiseoAdmin.ajaxUrl:', aiseoAdmin ? aiseoAdmin.ajaxUrl : 'UNDEFINED');
        console.log('Nonce being sent:', aiseoAdmin.nonce);
        console.log('========================================');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_' + field,
                post_id: postId,
                nonce: aiseoAdmin.nonce  // Use localized nonce instead of hardcoded
            },
            success: function(response) {
                console.log('=== AJAX SUCCESS ===');
                console.log('Response:', response);
                console.log('Response Type:', typeof response);
                console.log('Response Success:', response.success);
                console.log('Response Data:', response.data);
                
                if (response.success) {
                    $('#aiseo-meta-output').html('<strong>' + field.toUpperCase() + ':</strong> ' + response.data);
                    $('#aiseo-meta-results').show();
                } else {
                    console.error('Response indicates failure:', response.data);
                    $('#aiseo-meta-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + (response.data || 'Failed to generate') + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.log('=== AJAX ERROR ===');
                console.error('XHR Status:', xhr.status);
                console.error('XHR Status Text:', xhr.statusText);
                console.error('XHR Response Text:', xhr.responseText);
                console.error('XHR Response (parsed):', xhr.responseJSON);
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Full XHR Object:', xhr);
                
                // DISABLED AUTO-REFRESH FOR DEBUGGING
                // Show detailed error instead
                var errorDetails = '<div class="notice notice-error" style="padding:10px;">';
                errorDetails += '<strong>AJAX Error Details:</strong><br>';
                errorDetails += 'Status Code: ' + xhr.status + '<br>';
                errorDetails += 'Status Text: ' + xhr.statusText + '<br>';
                errorDetails += 'Response: ' + (xhr.responseText || 'No response') + '<br>';
                errorDetails += 'Error: ' + error + '<br>';
                errorDetails += '<em>Check browser console for full details</em>';
                errorDetails += '</div>';
                
                $('#aiseo-meta-results').html(errorDetails).show();
                
                var errorMsg = '';
                if (xhr.status === 403) {
                    errorMsg = '<strong>Permission Error (403):</strong> You do not have permission to perform this action.';
                } else if (xhr.status === 500) {
                    errorMsg = '<strong>Server Error (500):</strong> An internal server error occurred. Check PHP error logs.';
                } else {
                    errorMsg = '<strong><?php esc_html_e('Connection error', 'aiseo'); ?>:</strong> ' + error + ' (Status: ' + xhr.status + ')';
                }
                $('#aiseo-meta-results').html('<div class="notice notice-error" style="padding:10px;">' + errorMsg + '</div>').show();
            },
            complete: function() {
                var buttonText = field === 'title' ? '<?php esc_html_e('Generate Title', 'aiseo'); ?>' : '<?php esc_html_e('Generate Description', 'aiseo'); ?>';
                btn.prop('disabled', false).html('<span class="dashicons dashicons-edit"></span> ' + buttonText);
            }
        });
    });
    
    // Content analysis
    $('.aiseo-analyze-content').on('click', function() {
        var btn = $(this);
        var postId = $('#aiseo-analyze-post-select').val();
        
        if (!postId) {
            $('.aiseo-analysis-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> <?php esc_html_e('Please select a post first', 'aiseo'); ?></div>').show();
            return;
        }
        
        btn.prop('disabled', true).text('<?php esc_html_e('Analyzing...', 'aiseo'); ?>');
        $('.aiseo-analysis-results').hide();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_analyze_content',
                post_id: postId,
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div class="aiseo-card"><h4><?php esc_html_e('Analysis Results', 'aiseo'); ?></h4>';
                    html += '<div class="aiseo-stat-grid">';
                    
                    if (response.data.overall_score) {
                        html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + response.data.overall_score + '</div><div class="aiseo-stat-label">SEO Score</div></div>';
                    }
                    
                    $.each(response.data, function(key, value) {
                        if (key !== 'overall_score' && typeof value === 'object' && value.score) {
                            html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + value.score + '</div><div class="aiseo-stat-label">' + key.replace(/_/g, ' ') + '</div></div>';
                        }
                    });
                    
                    html += '</div></div>';
                    $('.aiseo-analysis-results').html(html).show();
                } else {
                    $('.aiseo-analysis-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + (response.data || 'Failed to analyze') + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Analysis error:', xhr, status, error);
                
                // Auto-refresh on nonce failure (only once)
                if (xhr.status === 403 && xhr.responseText === '-1') {
                    // Check if we already tried refreshing
                    if (!sessionStorage.getItem('aiseo_nonce_refresh_attempted')) {
                        sessionStorage.setItem('aiseo_nonce_refresh_attempted', '1');
                        $('.aiseo-analysis-results').html('<div class="notice notice-warning" style="padding:10px;"><strong>Session expired.</strong> Refreshing page automatically...</div>').show();
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                        return;
                    } else {
                        // Already tried refreshing, show manual refresh message
                        sessionStorage.removeItem('aiseo_nonce_refresh_attempted');
                        $('.aiseo-analysis-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Session Error:</strong> Please log out and log back in to WordPress, then try again.</div>').show();
                        return;
                    }
                }
                
                var errorMsg = '';
                if (xhr.status === 403) {
                    errorMsg = '<strong>Permission Error (403):</strong> You do not have permission to perform this action.';
                } else if (xhr.status === 500) {
                    errorMsg = '<strong>Server Error (500):</strong> An internal server error occurred. Check PHP error logs.';
                } else {
                    errorMsg = '<strong><?php esc_html_e('Connection error', 'aiseo'); ?>:</strong> ' + error + ' (Status: ' + xhr.status + ')';
                }
                $('.aiseo-analysis-results').html('<div class="notice notice-error" style="padding:10px;">' + errorMsg + '</div>').show();
            },
            complete: function() {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e('Analyze Content', 'aiseo'); ?>');
            }
        });
    });
    
    // Internal Linking
    $('.aiseo-get-linking').on('click', function() {
        var btn = $(this);
        var postId = $('#aiseo-linking-post-select').val();
        
        if (!postId) {
            alert('<?php esc_html_e('Please select a post first', 'aiseo'); ?>');
            return;
        }
        
        btn.prop('disabled', true).text('<?php esc_html_e('Getting suggestions...', 'aiseo'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_internal_linking',
                post_id: postId,
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                console.log('Internal Linking Response:', response);
                if (response.success && response.data) {
                    var html = '<ul>';
                    var suggestions = [];
                    
                    // Extract suggestions from different response formats
                    if (Array.isArray(response.data)) {
                        suggestions = response.data;
                    } else if (response.data.suggestions && Array.isArray(response.data.suggestions)) {
                        suggestions = response.data.suggestions;
                    }
                    
                    if (suggestions.length > 0) {
                        $.each(suggestions, function(i, link) {
                            var title = link.post_title || link.title || 'Untitled';
                            var url = link.post_url || link.url || link.permalink || '#';
                            var reason = link.reason || link.description || 'Relevant content';
                            var score = link.relevance_score ? ' (Score: ' + Math.round(link.relevance_score * 100) + '%)' : '';
                            html += '<li><a href="' + url + '" target="_blank">' + title + '</a>' + score + ' - ' + reason + '</li>';
                        });
                    } else {
                        html += '<li>No suggestions found</li>';
                    }
                    html += '</ul>';
                    $('#aiseo-linking-results').show().find('.aiseo-result-content').html(html);
                } else {
                    $('#aiseo-linking-results').show().find('.aiseo-result-content').html('<p style="color:red;">Error: ' + (response.data || 'Failed to get suggestions') + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Internal Linking Error:', xhr, status, error);
                $('#aiseo-linking-results').show().find('.aiseo-result-content').html('<p style="color:red;">Connection error: ' + error + '</p>');
            },
            complete: function() {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-links"></span> <?php esc_html_e('Get Linking Suggestions', 'aiseo'); ?>');
            }
        });
    });
    
    // Meta Variations - Title
    $('.aiseo-get-title-variations').on('click', function() {
        var btn = $(this);
        var postId = $('#aiseo-variations-post-select').val();
        
        if (!postId) {
            $('#aiseo-variations-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> <?php esc_html_e('Please select a post first', 'aiseo'); ?></div>').show();
            return;
        }
        
        btn.prop('disabled', true).text('<?php esc_html_e('Generating...', 'aiseo'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_meta_variations',
                post_id: postId,
                type: 'title',
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                console.log('Title Variations Response:', response);
                if (response.success && response.data) {
                    if (Array.isArray(response.data) && response.data.length > 0) {
                        var html = '<ol>';
                        $.each(response.data, function(i, variation) {
                            var text = variation.text || variation.title || variation;
                            var score = variation.score || 0;
                            html += '<li><strong>' + text + '</strong> (Score: ' + score + ')</li>';
                        });
                        html += '</ol>';
                        $('#aiseo-variations-results').show().find('.aiseo-result-content').html(html);
                    } else {
                        $('#aiseo-variations-results').html('<div class="notice notice-warning" style="padding:10px;"><strong>Notice:</strong> No variations generated. Try a different post.</div>').show();
                    }
                } else {
                    var errorMsg = response.data || 'Failed to generate variations';
                    if (typeof errorMsg === 'object') {
                        errorMsg = JSON.stringify(errorMsg);
                    }
                    $('#aiseo-variations-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + errorMsg + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Variations error:', xhr, status, error);
                var errorMsg = 'Connection error';
                if (xhr.status === 500) {
                    errorMsg = 'Server error (500). The post may not exist or there was a backend error. Check PHP error logs.';
                } else if (xhr.status === 403) {
                    errorMsg = 'Security error (403). Please <a href="javascript:location.reload();">refresh the page</a> and try again.';
                }
                $('#aiseo-variations-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + errorMsg + '<br><small>Status: ' + xhr.status + '</small></div>').show();
            },
            complete: function() {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-page"></span> <?php esc_html_e('Generate Title Variations', 'aiseo'); ?>');
            }
        });
    });
    
    // Meta Variations - Description
    $('.aiseo-get-desc-variations').on('click', function() {
        var btn = $(this);
        var postId = $('#aiseo-variations-post-select').val();
        
        if (!postId) {
            $('#aiseo-variations-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> <?php esc_html_e('Please select a post first', 'aiseo'); ?></div>').show();
            return;
        }
        
        btn.prop('disabled', true).text('<?php esc_html_e('Generating...', 'aiseo'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_meta_variations',
                post_id: postId,
                type: 'description',
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                console.log('Description Variations Response:', response);
                if (response.success && response.data) {
                    if (Array.isArray(response.data) && response.data.length > 0) {
                        var html = '<ol>';
                        $.each(response.data, function(i, variation) {
                            var text = variation.text || variation.description || variation;
                            var score = variation.score || 0;
                            html += '<li><strong>' + text + '</strong> (Score: ' + score + ')</li>';
                        });
                        html += '</ol>';
                        $('#aiseo-variations-results').show().find('.aiseo-result-content').html(html);
                    } else {
                        $('#aiseo-variations-results').html('<div class="notice notice-warning" style="padding:10px;"><strong>Notice:</strong> No variations generated. Try a different post.</div>').show();
                    }
                } else {
                    var errorMsg = response.data || 'Failed to generate variations';
                    if (typeof errorMsg === 'object') {
                        errorMsg = JSON.stringify(errorMsg);
                    }
                    $('#aiseo-variations-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + errorMsg + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Variations error:', xhr, status, error);
                var errorMsg = 'Connection error';
                if (xhr.status === 500) {
                    errorMsg = 'Server error (500). The post may not exist or there was a backend error. Check PHP error logs.';
                } else if (xhr.status === 403) {
                    errorMsg = 'Security error (403). Please <a href="javascript:location.reload();">refresh the page</a> and try again.';
                }
                $('#aiseo-variations-results').html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + errorMsg + '<br><small>Status: ' + xhr.status + '</small></div>').show();
            },
            complete: function() {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-page"></span> <?php esc_html_e('Generate Description Variations', 'aiseo'); ?>');
            }
        });
    });
});
</script>
