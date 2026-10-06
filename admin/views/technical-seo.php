<?php
/**
 * Technical SEO Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;
?>

<div class="aiseo-technical-seo">
    
    <!-- Redirects Manager -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Redirects Manager', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Create New Redirect', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <form id="aiseo-redirect-form">
                    <div class="aiseo-form-group">
                        <label class="aiseo-form-label"><?php esc_html_e('From URL', 'aiseo'); ?></label>
                        <input type="text" name="from_url" class="large-text" placeholder="/old-page" required>
                    </div>
                    <div class="aiseo-form-group">
                        <label class="aiseo-form-label"><?php esc_html_e('To URL', 'aiseo'); ?></label>
                        <input type="text" name="to_url" class="large-text" placeholder="/new-page" required>
                    </div>
                    <div class="aiseo-form-group">
                        <label class="aiseo-form-label"><?php esc_html_e('Type', 'aiseo'); ?></label>
                        <select name="redirect_type" class="regular-text">
                            <option value="301">301 (Permanent)</option>
                            <option value="302">302 (Temporary)</option>
                            <option value="307">307 (Temporary Redirect)</option>
                        </select>
                    </div>
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-plus"></span>
                        <?php esc_html_e('Add Redirect', 'aiseo'); ?>
                    </button>
                </form>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo redirects add /old-page /new-page --type=301</code><br>
                <code>wp aiseo redirects list</code></p>
            </div>
        </div>
        
        <!-- Existing Redirects List -->
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title">
                    <?php esc_html_e('Existing Redirects', 'aiseo'); ?>
                    <button type="button" class="button button-small" id="aiseo-refresh-redirects" style="margin-left: 10px;">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e('Refresh', 'aiseo'); ?>
                    </button>
                </h3>
            </div>
            <div class="aiseo-card-body">
                <div id="aiseo-redirects-list">
                    <p><?php esc_html_e('Loading redirects...', 'aiseo'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- XML Sitemap -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('XML Sitemap', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Sitemap Settings', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Your sitemap is automatically generated at:', 'aiseo'); ?></p>
                <p><a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank">
                    <?php echo esc_url(home_url('/sitemap.xml')); ?>
                </a></p>
                
                <div class="aiseo-stat-grid aiseo-mt-20">
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('Auto-generated', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('Cached 24hrs', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('All post types', 'aiseo'); ?></div>
                    </div>
                </div>
                
                <div class="aiseo-mt-20">
                    <button type="button" class="button button-secondary">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e('Regenerate Sitemap', 'aiseo'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Image SEO -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Image SEO', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Bulk Alt Text Generation', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Generate AI-powered alt text for all images:', 'aiseo'); ?></p>
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary" id="aiseo-generate-all-alt">
                        <span class="dashicons dashicons-images-alt2"></span>
                        <?php esc_html_e('Generate Alt Text for All Images', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="aiseo-find-missing-alt">
                        <span class="dashicons dashicons-search"></span>
                        <?php esc_html_e('Find Images Missing Alt Text', 'aiseo'); ?>
                    </button>
                </div>
                <div id="aiseo-image-results" style="margin-top:20px;"></div>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo image generate-alt --all</code><br>
                <code>wp aiseo image missing-alt</code></p>
            </div>
        </div>
    </div>
    
    <!-- Canonical URLs -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Canonical URLs', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Canonical URLs are automatically added to all posts and pages to prevent duplicate content issues.', 'aiseo'); ?></p>
                <div class="aiseo-alert aiseo-alert-success aiseo-mt-20">
                    <strong><?php esc_html_e('Status:', 'aiseo'); ?></strong> <?php esc_html_e('Enabled and working automatically', 'aiseo'); ?>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
// Ensure ajaxurl is defined
var ajaxurl = ajaxurl || '<?php echo admin_url('admin-ajax.php'); ?>';

jQuery(document).ready(function($) {
    // Clear refresh flag on page load (prevents infinite loops)
    sessionStorage.removeItem('aiseo_nonce_refresh_attempted');
    
    // Redirect form submission
    $('#aiseo-redirect-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        
        $btn.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_add_redirect',
                nonce: aiseoAdmin.nonce,
                from_url: $form.find('[name="from_url"]').val(),
                to_url: $form.find('[name="to_url"]').val(),
                redirect_type: $form.find('[name="redirect_type"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $form.before('<div class="notice notice-success" style="margin:10px 0;padding:10px;"><strong>Success!</strong> Redirect added.</div>');
                    $form[0].reset();
                    loadRedirects(); // Refresh the list
                    setTimeout(function() { $form.prev('.notice').fadeOut(); }, 3000);
                } else {
                    $form.before('<div class="notice notice-error" style="margin:10px 0;padding:10px;"><strong>Error:</strong> ' + response.data + '</div>');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> <?php esc_html_e('Add Redirect', 'aiseo'); ?>');
            }
        });
    });
    
    // Load redirects list with pagination
    var currentPage = 1;
    var perPage = 10;
    
    function loadRedirects(page) {
        page = page || 1;
        currentPage = page;
        $('#aiseo-redirects-list').html('<p><?php esc_html_e('Loading redirects...', 'aiseo'); ?></p>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_list_redirects',
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    var redirects = response.data;
                    var totalPages = Math.ceil(redirects.length / perPage);
                    var start = (page - 1) * perPage;
                    var end = start + perPage;
                    var pageRedirects = redirects.slice(start, end);
                    
                    var html = '<table class="wp-list-table widefat fixed striped">';
                    html += '<thead><tr>';
                    html += '<th><?php esc_html_e('From URL', 'aiseo'); ?></th>';
                    html += '<th><?php esc_html_e('To URL', 'aiseo'); ?></th>';
                    html += '<th><?php esc_html_e('Type', 'aiseo'); ?></th>';
                    html += '<th><?php esc_html_e('Hits', 'aiseo'); ?></th>';
                    html += '<th><?php esc_html_e('Actions', 'aiseo'); ?></th>';
                    html += '</tr></thead><tbody>';
                    
                    $.each(pageRedirects, function(i, redirect) {
                        var fromUrl = redirect.from_url || redirect.from || 'undefined';
                        var toUrl = redirect.to_url || redirect.to || 'undefined';
                        html += '<tr>';
                        html += '<td><code>' + fromUrl + '</code></td>';
                        html += '<td><code>' + toUrl + '</code></td>';
                        html += '<td><span class="aiseo-badge">' + redirect.type + '</span></td>';
                        html += '<td>' + (redirect.hits || 0) + '</td>';
                        html += '<td><button class="button button-small aiseo-delete-redirect" data-id="' + redirect.id + '"><span class="dashicons dashicons-trash"></span> <?php esc_html_e('Delete', 'aiseo'); ?></button></td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    
                    // Add pagination if more than 10 redirects
                    if (totalPages > 1) {
                        html += '<div class="tablenav" style="margin-top:15px;"><div class="tablenav-pages">';
                        html += '<span class="displaying-num">' + redirects.length + ' items</span>';
                        html += '<span class="pagination-links">';
                        
                        if (page > 1) {
                            html += '<a class="button aiseo-redirect-page" data-page="' + (page - 1) + '">‹ <?php esc_html_e('Previous', 'aiseo'); ?></a> ';
                        }
                        
                        html += '<span class="paging-input">';
                        html += '<span class="tablenav-paging-text">' + page + ' of <span class="total-pages">' + totalPages + '</span></span>';
                        html += '</span>';
                        
                        if (page < totalPages) {
                            html += ' <a class="button aiseo-redirect-page" data-page="' + (page + 1) + '"><?php esc_html_e('Next', 'aiseo'); ?> ›</a>';
                        }
                        
                        html += '</span></div></div>';
                    }
                    
                    $('#aiseo-redirects-list').html(html);
                } else {
                    $('#aiseo-redirects-list').html('<p style="color:#666;"><?php esc_html_e('No redirects found. Create one above to get started.', 'aiseo'); ?></p>');
                }
            },
            error: function() {
                $('#aiseo-redirects-list').html('<p style="color:red;"><?php esc_html_e('Error loading redirects.', 'aiseo'); ?></p>');
            }
        });
    }
    
    // Pagination click handler
    $(document).on('click', '.aiseo-redirect-page', function(e) {
        e.preventDefault();
        var page = parseInt($(this).data('page'));
        loadRedirects(page);
    });
    
    // Refresh button
    $('#aiseo-refresh-redirects').on('click', function() {
        loadRedirects();
    });
    
    // Delete redirect
    $(document).on('click', '.aiseo-delete-redirect', function() {
        if (!confirm('<?php esc_html_e('Are you sure you want to delete this redirect?', 'aiseo'); ?>')) {
            return;
        }
        
        var $btn = $(this);
        var redirectId = $btn.data('id');
        
        $btn.prop('disabled', true).text('<?php esc_html_e('Deleting...', 'aiseo'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_delete_redirect',
                nonce: aiseoAdmin.nonce,
                redirect_id: redirectId
            },
            success: function(response) {
                if (response.success) {
                    loadRedirects(); // Refresh the list
                } else {
                    alert('Error: ' + response.data);
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> <?php esc_html_e('Delete', 'aiseo'); ?>');
                }
            }
        });
    });
    
    // Load redirects on page load
    loadRedirects();
    
    // Regenerate sitemap
    $('.aiseo-technical-seo button:contains("Regenerate Sitemap")').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Regenerating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_regenerate_sitemap',
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Sitemap regenerated successfully!');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php esc_html_e('Regenerate Sitemap', 'aiseo'); ?>');
            }
        });
    });
    
    // Find images missing alt text
    $('#aiseo-find-missing-alt').on('click', function() {
        var $btn = $(this);
        var $results = $('#aiseo-image-results');
        
        $btn.prop('disabled', true).text('Searching...');
        $results.html('<p>Searching for images without alt text...</p>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_find_missing_alt',
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    var html = '<div class="aiseo-card" style="background:#fff;padding:15px;"><h4>Found ' + response.data.length + ' images without alt text:</h4>';
                    html += '<table class="wp-list-table widefat fixed striped"><thead><tr>';
                    html += '<th>Image</th><th>Post</th><th>Actions</th></tr></thead><tbody>';
                    
                    $.each(response.data, function(i, img) {
                        html += '<tr data-post-id="' + img.post_id + '" data-image-url="' + img.url + '">';
                        html += '<td><img src="' + img.url + '" style="max-width:100px;height:auto;"></td>';
                        html += '<td>' + img.post_title + '</td>';
                        html += '<td>';
                        html += '<button class="button button-primary button-small aiseo-generate-single-alt" style="margin-right:5px;"><span class="dashicons dashicons-images-alt2"></span> Generate Alt</button>';
                        html += '<a href="' + img.edit_url + '" class="button button-small" target="_blank">Edit Post</a>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table></div>';
                    $results.html(html);
                } else if (response.success && response.data.length === 0) {
                    $results.html('<div class="notice notice-success" style="padding:10px;"><strong>Great!</strong> All images have alt text.</div>');
                } else {
                    $results.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + response.data + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Find missing alt error:', xhr, status, error);
                $results.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + error + '</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-search"></span> <?php esc_html_e('Find Images Missing Alt Text', 'aiseo'); ?>');
            }
        });
    });
    
    // Generate alt text for all images
    $('#aiseo-generate-all-alt').on('click', function() {
        var $btn = $(this);
        var $results = $('#aiseo-image-results');
        
        if (!confirm('Generate alt text for all images? This may take a while.')) return;
        
        $btn.prop('disabled', true).text('Generating...');
        
        // Create progress container
        var progressHtml = '<div class="aiseo-card" style="background:#fff;padding:15px;">';
        progressHtml += '<h4>Generating Alt Text Progress</h4>';
        progressHtml += '<div id="aiseo-alt-progress" style="margin-top:10px;"></div>';
        progressHtml += '<div id="aiseo-alt-summary" style="margin-top:15px;font-weight:bold;"></div>';
        progressHtml += '</div>';
        $results.html(progressHtml);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_image_alt',
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var progressDiv = $('#aiseo-alt-progress');
                    var summaryDiv = $('#aiseo-alt-summary');
                    
                    if (data.images && data.images.length > 0) {
                        var html = '<table class="wp-list-table widefat fixed striped" style="margin-top:10px;"><thead><tr>';
                        html += '<th>Post</th><th>Image</th><th>Status</th></tr></thead><tbody>';
                        
                        $.each(data.images, function(i, img) {
                            var statusClass = img.success ? 'notice-success' : 'notice-error';
                            var statusIcon = img.success ? '✓' : '✗';
                            html += '<tr>';
                            html += '<td>' + img.post_title + '</td>';
                            html += '<td><img src="' + img.url + '" style="max-width:50px;height:auto;"></td>';
                            html += '<td><span class="' + statusClass + '" style="padding:2px 8px;display:inline-block;">' + statusIcon + ' ' + img.message + '</span></td>';
                            html += '</tr>';
                        });
                        
                        html += '</tbody></table>';
                        progressDiv.html(html);
                        
                        summaryDiv.html('<span style="color:green;">✓ Completed: ' + data.success_count + ' images</span> | <span style="color:red;">✗ Failed: ' + data.failed_count + ' images</span>');
                    } else {
                        progressDiv.html('<p>No images found that need alt text.</p>');
                    }
                } else {
                    $results.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + response.data + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Generate alt text error:', xhr, status, error);
                $results.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + error + '</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e('Generate Alt Text for All Images', 'aiseo'); ?>');
            }
        });
    });
    
    // Generate alt text
    $('.aiseo-technical-seo button:contains("Generate Alt Text")').on('click', function() {
        var $btn = $(this);
        if (!confirm('Generate alt text for images missing alt text?')) return;
        
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_image_alt',
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e('Generate Alt Text for All Images', 'aiseo'); ?>');
            }
        });
    });
    
    // Generate alt text for single image
    $(document).on('click', '.aiseo-generate-single-alt', function() {
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var postId = $row.data('post-id');
        var imageUrl = $row.data('image-url');
        
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_single_alt',
                nonce: aiseoAdmin.nonce,
                post_id: postId,
                image_url: imageUrl
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        // Check if any rows left
                        var remainingRows = $('#aiseo-image-results tbody tr').length;
                        if (remainingRows === 0) {
                            $('#aiseo-image-results').html('<div class="notice notice-success" style="padding:10px;"><strong>Success!</strong> All images now have alt text!</div>');
                        }
                    });
                    // Show success message
                    $('#aiseo-image-results').prepend('<div class="notice notice-success is-dismissible" style="margin:10px 0;padding:10px;"><strong>Success!</strong> Alt text generated: "' + response.data.alt_text + '"</div>');
                    setTimeout(function() {
                        $('.notice.is-dismissible').fadeOut();
                    }, 5000);
                } else {
                    $btn.after('<span style="color:red;margin-left:5px;">Error: ' + response.data + '</span>');
                    setTimeout(function() {
                        $btn.next('span').fadeOut();
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Generate single alt error:', xhr, status, error);
                
                // Auto-refresh on nonce failure (only once)
                if (xhr.status === 403 && xhr.responseText === '-1') {
                    if (!sessionStorage.getItem('aiseo_nonce_refresh_attempted')) {
                        sessionStorage.setItem('aiseo_nonce_refresh_attempted', '1');
                        alert('Session expired. Refreshing page automatically...');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                        return;
                    } else {
                        sessionStorage.removeItem('aiseo_nonce_refresh_attempted');
                        $btn.after('<span style="color:red;margin-left:5px;">Session Error: Please log out and log back in</span>');
                        return;
                    }
                }
                
                $btn.after('<span style="color:red;margin-left:5px;">Error: ' + error + '</span>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-images-alt2"></span> Generate Alt');
            }
        });
    });
});
</script>
