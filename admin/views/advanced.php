<?php
/**
 * Advanced Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

$aiseo_post_types = get_post_types(array('public' => true), 'objects');
?>

<div class="aiseo-advanced">
    
    <!-- Custom Post Types -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Custom Post Types', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Enable SEO for Custom Post Types', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Select which post types should have SEO features:', 'aiseo'); ?></p>
                <form method="post">
                    <?php wp_nonce_field('aiseo_cpt_settings'); ?>
                    <?php foreach ($post_types as $post_type): ?>
                        <div class="aiseo-form-group">
                            <label>
                                <input type="checkbox" name="aiseo_cpt[]" value="<?php echo esc_attr($post_type->name); ?>" checked>
                                <strong><?php echo esc_html($post_type->label); ?></strong>
                                <span style="color: #666;">(<?php echo esc_html($post_type->name); ?>)</span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="button button-primary aiseo-mt-20">
                        <?php esc_html_e('Save Post Type Settings', 'aiseo'); ?>
                    </button>
                </form>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo cpt list</code><br>
                <code>wp aiseo cpt generate --post-type=product --all</code></p>
            </div>
        </div>
    </div>
    
    <!-- Multilingual Support -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Multilingual SEO', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Multilingual Plugin Support', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('AISEO automatically detects and supports:', 'aiseo'); ?></p>
                <div class="aiseo-stat-grid">
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('WPML', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('Polylang', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('TranslatePress', 'aiseo'); ?></div>
                    </div>
                </div>
                <ul style="list-style: disc; padding-left: 20px; margin-top: 20px;">
                    <li><?php esc_html_e('Automatic language detection', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Per-language meta tags', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Hreflang tag generation', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Language-specific sitemaps', 'aiseo'); ?></li>
                </ul>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo multilingual status</code><br>
                <code>wp aiseo multilingual sync</code></p>
            </div>
        </div>
    </div>
    
    <!-- Unified Reports -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Unified Reports', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Generate SEO Reports', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Generate comprehensive SEO reports combining:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('Content analysis results', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Readability scores', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Advanced SEO factors', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Historical data', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Recommendations', 'aiseo'); ?></li>
                </ul>
                <div class="aiseo-button-group aiseo-mt-20">
                    <button type="button" class="button button-primary" id="aiseo-generate-report">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php esc_html_e('Generate Full Report', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="aiseo-download-pdf">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Export Report (HTML)', 'aiseo'); ?>
                    </button>
                </div>
                <p style="margin-top:10px;color:#666;font-size:12px;">
                    <span class="dashicons dashicons-info" style="font-size:14px;"></span>
                    <?php esc_html_e('Note: HTML export is available. For PDF export, use WP-CLI:', 'aiseo'); ?> <code>wp aiseo report export report.pdf</code>
                </p>
                <div id="aiseo-report-results" style="margin-top:20px;"></div>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo report generate --format=json</code><br>
                <code>wp aiseo report export report.pdf</code></p>
            </div>
        </div>
    </div>
    
    <!-- Keyword Research -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Keyword Research', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Research Keywords', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('AI-powered keyword research and suggestions:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('Keyword suggestions', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Search volume estimates', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Difficulty scores', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Related keywords', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Long-tail variations', 'aiseo'); ?></li>
                </ul>
                <div class="aiseo-form-group aiseo-mt-20">
                    <input type="text" id="aiseo-keyword-input" class="large-text" placeholder="<?php esc_attr_e('Enter a keyword...', 'aiseo'); ?>">
                    <button type="button" class="button button-primary" style="margin-top: 10px;">
                        <span class="dashicons dashicons-search"></span>
                        <?php esc_html_e('Research Keyword', 'aiseo'); ?>
                    </button>
                </div>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo keyword research "wordpress seo"</code><br>
                <code>wp aiseo keyword suggestions "content marketing"</code></p>
                <div class="aiseo-alert aiseo-alert-info aiseo-mt-20">
                    <?php esc_html_e('Note: Requires third-party API integration for volume/difficulty data', 'aiseo'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Briefs -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Content Briefs', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('AI Content Briefs', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Generate detailed content briefs with AI including target keywords, word count, structure, and key topics.', 'aiseo'); ?></p>
                
                <form id="aiseo-brief-form" class="aiseo-form" style="margin-top:20px;">
                    <div class="aiseo-form-group">
                        <label class="aiseo-form-label">
                            <?php esc_html_e('Topic or Subject', 'aiseo'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="topic" class="regular-text" placeholder="<?php esc_attr_e('e.g., WordPress SEO Best Practices', 'aiseo'); ?>" required>
                        <span class="aiseo-form-description"><?php esc_html_e('Enter the main topic for your content brief', 'aiseo'); ?></span>
                    </div>
                    
                    <div class="aiseo-form-group">
                        <label class="aiseo-form-label"><?php esc_html_e('Target Keyword (Optional)', 'aiseo'); ?></label>
                        <input type="text" name="keyword" class="regular-text" placeholder="<?php esc_attr_e('e.g., wordpress seo', 'aiseo'); ?>">
                        <span class="aiseo-form-description"><?php esc_html_e('Primary keyword to optimize for', 'aiseo'); ?></span>
                    </div>
                    
                    <div class="aiseo-button-group">
                        <button type="button" class="button button-primary" id="aiseo-generate-brief">
                            <span class="dashicons dashicons-edit-page"></span>
                            <?php esc_html_e('Generate Content Brief', 'aiseo'); ?>
                        </button>
                    </div>
                </form>
                
                <div id="aiseo-brief-results" class="aiseo-result-box" style="display:none; margin-top:20px;">
                    <h4><?php esc_html_e('Generated Content Brief:', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content" style="background:#f9f9f9;padding:20px;border-radius:5px;"></div>
                    <div class="aiseo-button-group" style="margin-top:15px;">
                        <button type="button" class="button button-secondary aiseo-create-post-from-brief" style="display:none;">
                            <span class="dashicons dashicons-plus"></span>
                            <?php esc_html_e('Create Post from Brief', 'aiseo'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
jQuery(document).ready(function($) {
    // CPT settings form
    $('.aiseo-advanced form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        
        $btn.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serialize() + '&action=aiseo_save_cpt_settings&nonce=' + aiseoAdmin.nonce,
            success: function(response) {
                if (response.success) {
                    alert('Settings saved successfully!');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('<?php esc_html_e('Save Post Type Settings', 'aiseo'); ?>');
            }
        });
    });
    
    // Generate report
    $('.aiseo-advanced button:contains("Generate Full Report")').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_report',
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var report = response.data;
                    var html = '<div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:5px;">';
                    html += '<h3 style="margin-top:0;">SEO Report</h3>';
                    html += '<div class="aiseo-stat-grid">';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.generated_at + '</div><div class="aiseo-stat-label">Generated</div></div>';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.posts_analyzed + '</div><div class="aiseo-stat-label">Posts Analyzed</div></div>';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.metadata_generated + '</div><div class="aiseo-stat-label">Metadata Generated</div></div>';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.ai_posts_created + '</div><div class="aiseo-stat-label">AI Posts Created</div></div>';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.api_requests + '</div><div class="aiseo-stat-label">API Requests</div></div>';
                    html += '</div>';
                    
                    if (report.recent_scores) {
                        html += '<h4>Recent Post Scores:</h4><table class="wp-list-table widefat fixed striped"><thead><tr><th>Post Title</th><th>SEO Score</th></tr></thead><tbody>';
                        $.each(report.recent_scores, function(i, item) {
                            html += '<tr><td>' + item.title + '</td><td>' + item.score + '</td></tr>';
                        });
                        html += '</tbody></table>';
                    }
                    html += '</div>';
                    
                    $('#aiseo-report-content').html(html);
                    $('#aiseo-report-section').show();
                    $('html, body').animate({scrollTop: $('#aiseo-report-section').offset().top - 100}, 500);
                    
                    // Store report data for PDF
                    $('#aiseo-download-pdf').data('report', report);
                } else {
                    $('#aiseo-report-content').html('<p style="color:red;">Error: ' + response.data + '</p>');
                    $('#aiseo-report-section').show();
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e('Generate Full Report', 'aiseo'); ?>');
            }
        });
    });
    // Keyword research
    $('.aiseo-advanced button:contains("Research Keyword")').on('click', function() {
        var $btn = $(this);
        var keyword = $('#aiseo-keyword-input').val();
        var $resultDiv = $('<div class="aiseo-result-box" style="margin-top:20px;"></div>').insertAfter($btn.parent());
        
        if (!keyword) {
            $resultDiv.html('<p style="color:red;">Please enter a keyword</p>').show();
            return;
        }
        
        $btn.prop('disabled', true).text('Researching...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_keyword_research',
                nonce: aiseoAdmin.nonce,
                keyword: keyword
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div style="background:#f0f0f0;padding:15px;border-radius:5px;"><h4>Keyword Suggestions for: ' + response.data.keyword + '</h4><ul style="list-style:disc;padding-left:20px;">';
                    $.each(response.data.suggestions, function(i, kw) {
                        html += '<li>' + kw + '</li>';
                    });
                    html += '</ul></div>';
                    $resultDiv.html(html).show();
                } else {
                    $resultDiv.html('<p style="color:red;">Error: ' + response.data + '</p>').show();
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-search"></span> <?php esc_html_e('Research Keyword', 'aiseo'); ?>');
            }
        });
    });
    
    // Generate report
    $('#aiseo-generate-report').on('click', function() {
        var $btn = $(this);
        var $resultDiv = $('#aiseo-report-results');
        
        $btn.prop('disabled', true).text('Generating...');
        $resultDiv.html('');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_report',
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div style="background:#f0f0f0;padding:20px;border-radius:5px;">';
                    html += '<h3>SEO Report Generated</h3>';
                    html += '<table class="wp-list-table widefat"><tr><th>Metric</th><th>Value</th></tr>';
                    html += '<tr><td>Posts Analyzed</td><td>' + response.data.posts_analyzed + '</td></tr>';
                    html += '<tr><td>Metadata Generated</td><td>' + response.data.metadata_generated + '</td></tr>';
                    html += '<tr><td>AI Posts Created</td><td>' + response.data.ai_posts_created + '</td></tr>';
                    html += '<tr><td>API Requests</td><td>' + response.data.api_requests + '</td></tr></table>';
                    html += '</div>';
                    $resultDiv.html(html).show();
                    
                    // Store report data for PDF download
                    $('#aiseo-download-pdf').data('report', response.data);
                    $('#aiseo-download-pdf').prop('disabled', false);
                } else {
                    $resultDiv.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + response.data + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Report generation error:', xhr, status, error);
                $resultDiv.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + error + '</div>').show();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e('Generate Full Report', 'aiseo'); ?>');
            }
        });
    });
    
    // PDF Download
    $('#aiseo-download-pdf').on('click', function() {
        var report = $(this).data('report');
        if (!report) {
            $(this).after('<p style="color:red;margin-top:10px;">No report data available</p>');
            return;
        }
        
        // Create PDF-style HTML content
        var pdfContent = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>AISEO Report</title>';
        pdfContent += '<style>body{font-family:Arial,sans-serif;padding:40px;}h1{color:#0073aa;}table{width:100%;border-collapse:collapse;margin:20px 0;}th,td{padding:10px;border:1px solid #ddd;text-align:left;}th{background:#0073aa;color:white;}</style>';
        pdfContent += '</head><body>';
        pdfContent += '<h1>AISEO SEO Report</h1>';
        pdfContent += '<p><strong>Generated:</strong> ' + report.generated_at + '</p>';
        pdfContent += '<table><tr><th>Metric</th><th>Value</th></tr>';
        pdfContent += '<tr><td>Posts Analyzed</td><td>' + report.posts_analyzed + '</td></tr>';
        pdfContent += '<tr><td>Metadata Generated</td><td>' + report.metadata_generated + '</td></tr>';
        pdfContent += '<tr><td>AI Posts Created</td><td>' + report.ai_posts_created + '</td></tr>';
        pdfContent += '<tr><td>API Requests</td><td>' + report.api_requests + '</td></tr></table>';
        
        if (report.recent_scores) {
            pdfContent += '<h2>Recent Post Scores</h2><table><tr><th>Post Title</th><th>SEO Score</th></tr>';
            $.each(report.recent_scores, function(i, item) {
                pdfContent += '<tr><td>' + item.title + '</td><td>' + item.score + '</td></tr>';
            });
            pdfContent += '</table>';
        }
        
        pdfContent += '</body></html>';
        
        // Create blob and download
        var blob = new Blob([pdfContent], {type: 'text/html'});
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'aiseo-report-' + Date.now() + '.html';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        $(this).after('<p style="color:green;margin-top:10px;">✓ Report downloaded! (Open in browser and print to PDF)</p>').next().delay(3000).fadeOut();
    });
    
    // Content Brief Generator
    $('#aiseo-brief-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#aiseo-generate-brief');
        var topic = $form.find('[name="topic"]').val();
        var keyword = $form.find('[name="keyword"]').val();
        
        if (!topic) {
            alert('<?php esc_html_e('Please enter a topic', 'aiseo'); ?>');
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update dashicons-spin"></span> <?php esc_html_e('Generating Brief...', 'aiseo'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_brief',
                topic: topic,
                keyword: keyword,
                nonce: aiseoAdmin.nonce
            },
            success: function(response) {
                console.log('Brief Response:', response);
                if (response.success && response.data) {
                    var brief = response.data;
                    var html = '<div class="content-brief">';
                    
                    // Title
                    if (brief.title) {
                        html += '<h3 style="margin-top:0;color:#0073aa;">' + brief.title + '</h3>';
                    }
                    
                    // Target Keywords
                    if (brief.keywords && brief.keywords.length > 0) {
                        html += '<div style="margin-bottom:15px;"><strong>🎯 Target Keywords:</strong><br>';
                        html += '<span style="background:#e3f2fd;padding:5px 10px;border-radius:3px;display:inline-block;margin:5px 5px 0 0;">' + brief.keywords.join('</span> <span style="background:#e3f2fd;padding:5px 10px;border-radius:3px;display:inline-block;margin:5px 5px 0 0;">') + '</span></div>';
                    }
                    
                    // Word Count
                    if (brief.word_count) {
                        html += '<div style="margin-bottom:15px;"><strong>📝 Recommended Word Count:</strong> ' + brief.word_count + ' words</div>';
                    }
                    
                    // Content Structure
                    if (brief.structure && brief.structure.length > 0) {
                        html += '<div style="margin-bottom:15px;"><strong>📋 Content Structure:</strong><ol style="margin:10px 0 0 20px;">';
                        $.each(brief.structure, function(i, section) {
                            html += '<li style="margin-bottom:5px;">' + section + '</li>';
                        });
                        html += '</ol></div>';
                    }
                    
                    // Key Topics
                    if (brief.key_topics && brief.key_topics.length > 0) {
                        html += '<div style="margin-bottom:15px;"><strong>💡 Key Topics to Cover:</strong><ul style="margin:10px 0 0 20px;">';
                        $.each(brief.key_topics, function(i, topic) {
                            html += '<li style="margin-bottom:5px;">' + topic + '</li>';
                        });
                        html += '</ul></div>';
                    }
                    
                    // SEO Tips
                    if (brief.seo_tips) {
                        html += '<div style="margin-bottom:15px;"><strong>🔍 SEO Tips:</strong><p style="margin:5px 0;">' + brief.seo_tips + '</p></div>';
                    }
                    
                    html += '</div>';
                    
                    $('#aiseo-brief-results').show().find('.aiseo-result-content').html(html);
                    // Show Create Post button
                    $('.aiseo-create-post-from-brief').show();
                } else {
                    var errorMsg = response.data || 'Failed to generate brief';
                    $('#aiseo-brief-results').show().find('.aiseo-result-content').html('<p style="color:red;">Error: ' + errorMsg + '</p>');
                    $('.aiseo-create-post-from-brief').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Brief Error:', xhr, status, error);
                $('#aiseo-brief-results').show().find('.aiseo-result-content').html('<p style="color:red;">Connection error: ' + error + '</p>');
                $('.aiseo-create-post-from-brief').hide();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-edit-page"></span> <?php esc_html_e('Generate Content Brief', 'aiseo'); ?>');
            }
        });
    });
    
    // Create Post from Brief
    $('.aiseo-create-post-from-brief').on('click', function() {
        var $btn = $(this);
        var briefContent = $('#aiseo-brief-results .aiseo-result-content').html();
        var topic = $('#aiseo-brief-form [name="topic"]').val();
        
        if (!briefContent) {
            alert('No brief content to create post from');
            return;
        }
        
        $btn.prop('disabled', true).text('Creating Post...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 30000,
            data: {
                action: 'aiseo_create_post',
                nonce: aiseoAdmin.nonce,
                topic: topic || 'Generated from Content Brief',
                content: briefContent,
                post_type: 'post',
                length: 'medium'
            },
            success: function(response) {
                console.log('Create Post from Brief Response:', response);
                if (response.success) {
                    $btn.after('<div class="notice notice-success" style="margin:10px 0;padding:10px;"><strong>Success!</strong> Post created! <a href="' + response.data.edit_url + '" target="_blank">Edit post</a></div>');
                } else {
                    $btn.after('<div class="notice notice-error" style="margin:10px 0;padding:10px;"><strong>Error:</strong> ' + response.data + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Create Post Error:', xhr, status, error);
                var errorMsg = 'Connection error';
                if (status === 'timeout') {
                    errorMsg = 'Request timed out (30s). The post may still be creating in the background.';
                }
                $btn.after('<div class="notice notice-error" style="margin:10px 0;padding:10px;"><strong>Error:</strong> ' + errorMsg + '</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> <?php esc_html_e('Create Post from Brief', 'aiseo'); ?>');
            }
        });
    });
});
</script>
