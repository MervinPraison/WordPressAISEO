<?php
/**
 * Bulk Operations Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

// Get all posts for bulk selection
$aiseo_all_posts = get_posts(array(
    'numberposts' => 100,
    'post_type' => array('post', 'page'),
    'post_status' => 'any',
));
?>

<div class="aiseo-bulk-operations">
    
    <!-- Bulk Editor Section -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Bulk Editor', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Select Posts for Bulk Editing', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <div class="aiseo-form-group">
                    <label>
                        <input type="checkbox" id="aiseo-select-all-posts"> 
                        <?php esc_html_e('Select All Posts', 'aiseo'); ?>
                    </label>
                </div>
                
                <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; margin: 15px 0;">
                    <?php foreach ($all_posts as $post): ?>
                        <label style="display: block; padding: 5px 0;">
                            <input type="checkbox" class="aiseo-bulk-post" value="<?php echo esc_attr($post->ID); ?>">
                            <?php echo esc_html($post->post_title); ?> 
                            <span style="color: #666;">(<?php echo esc_html($post->post_type); ?> - <?php echo esc_html($post->post_status); ?>)</span>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary" id="aiseo-bulk-generate-titles">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Generate Titles for Selected', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-primary" id="aiseo-bulk-generate-descriptions">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Generate Descriptions for Selected', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="aiseo-bulk-analyze">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php esc_html_e('Analyze Selected Posts', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-bulk-progress" style="display:none; margin-top: 20px;">
                    <h4><?php esc_html_e('Progress:', 'aiseo'); ?></h4>
                    <div class="aiseo-progress-bar">
                        <div class="aiseo-progress-fill" style="width: 0%;">0%</div>
                    </div>
                    <p id="aiseo-bulk-status"></p>
                </div>
                
                <div id="aiseo-bulk-results" style="display:none; margin-top: 30px;">
                    <h4><?php esc_html_e('Review Generated Content:', 'aiseo'); ?></h4>
                    <div class="aiseo-button-group" style="margin-bottom: 15px;">
                        <button type="button" class="button" id="aiseo-select-all-results"><?php esc_html_e('Select All', 'aiseo'); ?></button>
                        <button type="button" class="button" id="aiseo-clear-all-results"><?php esc_html_e('Clear All', 'aiseo'); ?></button>
                        <button type="button" class="button button-primary" id="aiseo-save-approved" style="margin-left: 20px;">
                            <span class="dashicons dashicons-yes"></span>
                            <?php esc_html_e('Save Approved Changes', 'aiseo'); ?>
                        </button>
                    </div>
                    <div id="aiseo-results-list" style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 5px; max-height: 500px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Import/Export Section -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Import / Export', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Import from Other Plugins', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Import SEO data from:', 'aiseo'); ?></p>
                <div class="aiseo-button-group">
                    <button type="button" class="button button-secondary aiseo-import-btn" data-source="yoast">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Import from Yoast SEO', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-secondary aiseo-import-btn" data-source="rankmath">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Import from Rank Math', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-secondary aiseo-import-btn" data-source="aioseo">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Import from All in One SEO', 'aiseo'); ?>
                    </button>
                </div>
                <div id="aiseo-import-result" class="aiseo-result-box" style="display:none; margin-top: 20px;">
                    <h4><?php esc_html_e('Import Results:', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                </div>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Command:', 'aiseo'); ?></strong><br>
                <code>wp aiseo import --from=yoast</code></p>
            </div>
        </div>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Export Data', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Export your SEO data:', 'aiseo'); ?></p>
                <div class="aiseo-button-group">
                    <button type="button" class="button button-secondary aiseo-export-btn" data-format="json">
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e('Export to JSON', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-secondary">
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e('Export to CSV', 'aiseo'); ?>
                    </button>
                </div>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo export --format=json > export.json</code><br>
                <code>wp aiseo export --format=csv > export.csv</code></p>
            </div>
        </div>
    </div>
    
    <!-- Bulk Statistics -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Bulk Statistics', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-stat-grid">
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value"><?php echo count($all_posts); ?></div>
                    <div class="aiseo-stat-label"><?php esc_html_e('Total Posts', 'aiseo'); ?></div>
                </div>
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value"><?php echo count(array_filter($all_posts, function($p) { return $p->post_status === 'publish'; })); ?></div>
                    <div class="aiseo-stat-label"><?php esc_html_e('Published', 'aiseo'); ?></div>
                </div>
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value"><?php echo count(array_filter($all_posts, function($p) { return $p->post_type === 'post'; })); ?></div>
                    <div class="aiseo-stat-label"><?php esc_html_e('Posts', 'aiseo'); ?></div>
                </div>
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value"><?php echo count(array_filter($all_posts, function($p) { return $p->post_type === 'page'; })); ?></div>
                    <div class="aiseo-stat-label"><?php esc_html_e('Pages', 'aiseo'); ?></div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
jQuery(document).ready(function($) {
    // Clear refresh flag on page load (prevents infinite loops)
    sessionStorage.removeItem('aiseo_nonce_refresh_attempted');
    
    // Global nonce variable - use localized nonce
    var aiseoBulkNonce = aiseoAdmin.nonce;
    
    // Function to refresh nonce
    function refreshNonce(callback) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_refresh_nonce'
            },
            success: function(response) {
                if (response.success && response.data.nonce) {
                    aiseoBulkNonce = response.data.nonce;
                    console.log('Nonce refreshed successfully');
                    if (callback) callback();
                }
            },
            error: function() {
                console.error('Failed to refresh nonce');
            }
        });
    }
    
    // Select all checkbox
    $('#aiseo-select-all-posts').on('change', function() {
        $('.aiseo-bulk-post').prop('checked', $(this).is(':checked'));
    });
    
    // Bulk operations
    var bulkResults = [];
    
    function bulkOperation(action, buttonText, fieldType) {
        var selectedPosts = [];
        $('.aiseo-bulk-post:checked').each(function() {
            selectedPosts.push({
                id: $(this).val(),
                title: $(this).parent().text().trim()
            });
        });
        
        if (selectedPosts.length === 0) {
            $('#aiseo-bulk-status').html('<span style="color: red;"><?php esc_html_e('Please select at least one post', 'aiseo'); ?></span>');
            $('#aiseo-bulk-progress').show();
            return;
        }
        
        // Disable all bulk operation buttons
        $('#aiseo-bulk-generate-titles, #aiseo-bulk-generate-descriptions, #aiseo-bulk-analyze').prop('disabled', true).addClass('disabled');
        
        bulkResults = [];
        var currentIndex = 0;
        
        $('#aiseo-bulk-progress').show();
        $('#aiseo-bulk-results').hide();
        $('.aiseo-progress-fill').css('width', '0%').text('0%');
        
        function processNext() {
            if (currentIndex >= selectedPosts.length) {
                displayResults(fieldType);
                return;
            }
            
            var post = selectedPosts[currentIndex];
            var progress = Math.round(((currentIndex + 1) / selectedPosts.length) * 100);
            
            $('.aiseo-progress-fill').css('width', progress + '%').text(progress + '%');
            $('#aiseo-bulk-status').text('<?php esc_html_e('Processing', 'aiseo'); ?> ' + (currentIndex + 1) + ' <?php esc_html_e('of', 'aiseo'); ?> ' + selectedPosts.length + ': ' + post.title);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: action,
                    post_id: post.id,
                    nonce: aiseoBulkNonce
                },
                success: function(response) {
                    console.log('Bulk AJAX response for post', post.id, ':', response);
                    // Clear refresh flag on success
                    sessionStorage.removeItem('aiseo_nonce_refresh_attempted');
                    
                    if (response.success) {
                        bulkResults.push({
                            postId: post.id,
                            postTitle: post.title,
                            success: true,
                            generated: response.data,
                            fieldType: fieldType
                        });
                    } else {
                        console.error('Bulk operation failed for post', post.id, ':', response.data);
                        bulkResults.push({
                            postId: post.id,
                            postTitle: post.title,
                            success: false,
                            error: response.data
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Bulk AJAX error for post', post.id, ':', xhr.status, error);
                    
                    // Auto-refresh nonce on 403 failure (no page reload!)
                    if (xhr.status === 403 && xhr.responseText === '-1') {
                        if (!sessionStorage.getItem('aiseo_nonce_refresh_attempted')) {
                            sessionStorage.setItem('aiseo_nonce_refresh_attempted', '1');
                            console.log('Nonce expired, refreshing nonce and retrying...');
                            
                            // Refresh nonce and retry this request
                            refreshNonce(function() {
                                // Retry the same post with new nonce
                                currentIndex--; // Go back one so processNext() will retry this post
                                processNext();
                            });
                            return;
                        } else {
                            sessionStorage.removeItem('aiseo_nonce_refresh_attempted');
                            bulkResults.push({
                                postId: post.id,
                                postTitle: post.title,
                                success: false,
                                error: 'Session Error: Please log out and log back in to WordPress'
                            });
                        }
                    } else {
                        bulkResults.push({
                            postId: post.id,
                            postTitle: post.title,
                            success: false,
                            error: 'Connection error: ' + error + ' (Status: ' + xhr.status + ')'
                        });
                    }
                },
                complete: function() {
                    currentIndex++;
                    processNext();
                }
            });
        }
        
        processNext();
    }
    
    function displayResults(fieldType) {
        var html = '';
        var fieldLabel = fieldType === 'title' ? 'Title' : (fieldType === 'description' ? 'Description' : 'Analysis');
        
        console.log('Displaying results:', bulkResults);
        
        $.each(bulkResults, function(i, result) {
            if (result.success) {
                // Extract the actual generated content
                var generatedText = '';
                if (typeof result.generated === 'string') {
                    generatedText = result.generated;
                } else if (result.generated && result.generated.title) {
                    generatedText = result.generated.title;
                } else if (result.generated && result.generated.description) {
                    generatedText = result.generated.description;
                } else if (result.generated && result.generated.content) {
                    generatedText = result.generated.content;
                } else if (result.generated && result.generated.text) {
                    generatedText = result.generated.text;
                } else if (typeof result.generated === 'object') {
                    generatedText = JSON.stringify(result.generated);
                }
                
                // Escape for HTML attribute
                var escapedValue = generatedText.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                
                html += '<div class="aiseo-result-item" style="margin-bottom: 15px; padding: 10px; background: white; border: 1px solid #ddd; border-radius: 3px;">';
                html += '<label style="display: flex; align-items: start; cursor: pointer;">';
                html += '<input type="checkbox" class="aiseo-approve-checkbox" data-post-id="' + result.postId + '" data-field="' + result.fieldType + '" data-value="' + escapedValue + '" checked style="margin-right: 10px; margin-top: 3px;">';
                html += '<div style="flex: 1;"><strong>' + result.postTitle + '</strong><br>';
                html += '<span style="color: #666; font-size: 12px;">New ' + fieldLabel + ':</span><br>';
                html += '<span style="color: #0073aa;">' + $('<div>').text(generatedText).html() + '</span></div>';
                html += '</label></div>';
            } else {
                html += '<div class="aiseo-result-item" style="margin-bottom: 15px; padding: 10px; background: #fee; border: 1px solid #fcc; border-radius: 3px;">';
                html += '<strong>' + result.postTitle + '</strong><br>';
                html += '<span style="color: red;">Error: ' + (result.error || 'Unknown error') + '</span>';
                html += '</div>';
            }
        });
        
        if (html === '') {
            html = '<div class="notice notice-warning" style="padding:10px;"><strong>Notice:</strong> No results to display. The operations may have failed.</div>';
        }
        
        $('#aiseo-results-list').html(html);
        $('#aiseo-bulk-results').show();
        $('#aiseo-bulk-status').html('<strong style="color: green;"><?php esc_html_e('Complete! Review and approve changes below.', 'aiseo'); ?></strong>');
        
        // Re-enable all bulk operation buttons
        $('#aiseo-bulk-generate-titles, #aiseo-bulk-generate-descriptions, #aiseo-bulk-analyze').prop('disabled', false).removeClass('disabled');
        
        $('html, body').animate({scrollTop: $('#aiseo-bulk-results').offset().top - 100}, 500);
    }
    
    $('#aiseo-bulk-generate-titles').on('click', function() {
        bulkOperation('aiseo_generate_title', '<?php esc_html_e('Generate Titles', 'aiseo'); ?>', 'title');
    });
    
    $('#aiseo-bulk-generate-descriptions').on('click', function() {
        bulkOperation('aiseo_generate_description', '<?php esc_html_e('Generate Descriptions', 'aiseo'); ?>', 'description');
    });
    
    $('#aiseo-bulk-analyze').on('click', function() {
        bulkOperation('aiseo_analyze_content', '<?php esc_html_e('Analyze Posts', 'aiseo'); ?>', 'analysis');
    });
    
    // Select/Clear all results
    $('#aiseo-select-all-results').on('click', function() {
        $('.aiseo-approve-checkbox').prop('checked', true);
    });
    
    $('#aiseo-clear-all-results').on('click', function() {
        $('.aiseo-approve-checkbox').prop('checked', false);
    });
    
    // Save approved changes
    $('#aiseo-save-approved').on('click', function() {
        var $btn = $(this);
        var approved = [];
        
        $('.aiseo-approve-checkbox:checked').each(function() {
            approved.push({
                post_id: $(this).data('post-id'),
                field: $(this).data('field'),
                value: $(this).data('value')
            });
        });
        
        if (approved.length === 0) {
            $('#aiseo-bulk-status').html('<span style="color: red;"><?php esc_html_e('Please select at least one item to save', 'aiseo'); ?></span>');
            return;
        }
        
        $btn.prop('disabled', true).text('<?php esc_html_e('Saving...', 'aiseo'); ?>');
        
        var saved = 0;
        var total = approved.length;
        
        function saveNext(index) {
            if (index >= total) {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> <?php esc_html_e('Save Approved Changes', 'aiseo'); ?>');
                $('#aiseo-bulk-status').html('<strong style="color: green;"><?php esc_html_e('Saved', 'aiseo'); ?> ' + saved + ' <?php esc_html_e('changes successfully!', 'aiseo'); ?></strong>');
                $('#aiseo-bulk-results').fadeOut(500);
                return;
            }
            
            var item = approved[index];
            var action = item.field === 'title' ? 'aiseo_save_title' : 'aiseo_save_description';
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: action,
                    post_id: item.post_id,
                    value: item.value,
                    nonce: aiseoAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        saved++;
                    }
                },
                complete: function() {
                    $btn.text('<?php esc_html_e('Saving', 'aiseo'); ?> ' + (index + 1) + '/' + total + '...');
                    saveNext(index + 1);
                }
            });
        }
        
        saveNext(0);
    });
    
    // Import from other plugins
    $('.aiseo-import-btn').on('click', function() {
        var btn = $(this);
        var source = btn.data('source');
        
        if (!confirm('Import SEO data from ' + source.toUpperCase() + '? This may take a few minutes.')) {
            return;
        }
        
        btn.prop('disabled', true).text('Importing...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_import_seo',
                source: source,
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#aiseo-import-result').show().find('.aiseo-result-content').html(
                        '<p><strong>Success!</strong> Imported ' + response.data.count + ' items from ' + source.toUpperCase() + '</p>'
                    );
                } else {
                    alert('Error: ' + (response.data || 'Import failed'));
                }
            },
            error: function() {
                alert('Connection error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Import from ' + source.charAt(0).toUpperCase() + source.slice(1));
            }
        });
    });
    
    // Export data
    $('.aiseo-export-btn').on('click', function() {
        var btn = $(this);
        var format = btn.data('format');
        var $resultDiv = $('#aiseo-export-result');
        
        // Create result div if doesn't exist
        if ($resultDiv.length === 0) {
            $resultDiv = $('<div id="aiseo-export-result" class="aiseo-result-box" style="margin-top:20px;"></div>').insertAfter(btn.parent());
        }
        
        btn.prop('disabled', true).text('Exporting...');
        $resultDiv.html('<p>Preparing export...</p>').show();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_export_seo',
                format: format,
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                console.log('Export Response:', response);
                console.log('Export format:', format);
                console.log('Response data type:', typeof response.data);
                console.log('Response data is array:', Array.isArray(response.data));
                console.log('Response data length:', response.data ? response.data.length : 0);
                
                if (response.success && response.data) {
                    var content, mimeType;
                    
                    // Handle different formats
                    if (format === 'csv') {
                        // Convert JSON to CSV
                        if (Array.isArray(response.data) && response.data.length > 0) {
                            var headers = Object.keys(response.data[0]);
                            var csv = headers.join(',') + '\n';
                            response.data.forEach(function(row) {
                                var values = headers.map(function(header) {
                                    var val = row[header] || '';
                                    // Escape commas and quotes
                                    return '"' + String(val).replace(/"/g, '""') + '"';
                                });
                                csv += values.join(',') + '\n';
                            });
                            content = csv;
                            mimeType = 'text/csv';
                        } else {
                            $resultDiv.html('<div class="notice notice-warning" style="padding:10px;"><strong>Notice:</strong> No data to export.</div>').show();
                            return;
                        }
                    } else {
                        // JSON format
                        content = JSON.stringify(response.data, null, 2);
                        mimeType = 'application/json';
                    }
                    
                    // Create download link
                    var blob = new Blob([content], {type: mimeType});
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'aiseo-export-' + Date.now() + '.' + format;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    var count = Array.isArray(response.data) ? response.data.length : 0;
                    $resultDiv.html('<div class="notice notice-success" style="padding:10px;"><strong>✓ Success!</strong> Exported ' + count + ' posts to ' + format.toUpperCase() + '.</div>').show();
                } else {
                    var errorMsg = response.data || 'Export failed';
                    if (typeof errorMsg === 'object') {
                        errorMsg = JSON.stringify(errorMsg);
                    }
                    $resultDiv.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + errorMsg + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Export error:', xhr, status, error);
                var errorMsg = 'Connection error';
                if (xhr.status === 500) {
                    errorMsg = 'Server error (500). Check PHP error logs or try refreshing the page.';
                } else if (xhr.status === 403) {
                    errorMsg = 'Security error (403). Please <a href="javascript:location.reload();">refresh the page</a> and try again.';
                }
                $resultDiv.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + errorMsg + '<br><small>Status: ' + xhr.status + '</small></div>').show();
            },
            complete: function() {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> Export to ' + format.toUpperCase());
            }
        });
    });
});
</script>
