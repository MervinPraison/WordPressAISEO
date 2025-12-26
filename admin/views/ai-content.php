<?php
/**
 * AI Content Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$aiseo_api_key = '';
if (class_exists('AISEO_Helpers')) {
    $aiseo_api_key = AISEO_Helpers::get_api_key();
}
?>

<div class="aiseo-ai-content">
    
    <?php if (empty($aiseo_api_key)): ?>
        <div class="aiseo-alert aiseo-alert-warning">
            <strong><?php esc_html_e('API Key Required', 'aiseo'); ?></strong>
            <?php esc_html_e('Please configure your OpenAI API key in Settings to use AI features.', 'aiseo'); ?>
        </div>
    <?php endif; ?>
    
    <!-- AI Post Creator -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-edit"></span>
                <?php esc_html_e('AI Post Creator', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <form id="aiseo-create-post-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Topic or Subject', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <textarea name="topic" rows="3" class="large-text" placeholder="<?php esc_attr_e('e.g., 10 Best SEO Practices for 2024', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>></textarea>
                    <span class="aiseo-form-description"><?php esc_html_e('Describe what you want the post to be about', 'aiseo'); ?></span>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Focus Keyword (Optional)', 'aiseo'); ?></label>
                    <input type="text" name="keyword" class="regular-text" placeholder="<?php esc_attr_e('e.g., SEO best practices', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                    <span class="aiseo-form-description"><?php esc_html_e('Main keyword to optimize for', 'aiseo'); ?></span>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Post Type', 'aiseo'); ?></label>
                    <select name="post_type" class="regular-text" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <option value="post" selected><?php esc_html_e('Post', 'aiseo'); ?></option>
                        <option value="page"><?php esc_html_e('Page', 'aiseo'); ?></option>
                    </select>
                    <span class="aiseo-form-description"><?php esc_html_e('Select the type of content to create', 'aiseo'); ?></span>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Content Length', 'aiseo'); ?></label>
                    <select name="content_length" class="regular-text" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <option value="tiny"><?php esc_html_e('Tiny (~100 words)', 'aiseo'); ?></option>
                        <option value="brief"><?php esc_html_e('Brief (~200 words)', 'aiseo'); ?></option>
                        <option value="short"><?php esc_html_e('Short (~500 words)', 'aiseo'); ?></option>
                        <option value="medium" selected><?php esc_html_e('Medium (~1000 words)', 'aiseo'); ?></option>
                        <option value="long"><?php esc_html_e('Long (~2000 words)', 'aiseo'); ?></option>
                    </select>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary button-large aiseo-generate-post" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Generate Post', 'aiseo'); ?>
                    </button>
                </div>
            </form>
            
            <div id="aiseo-post-creator-results" class="aiseo-result-box" style="display:none; margin-top:20px;">
                <h4><?php esc_html_e('Generated Content:', 'aiseo'); ?></h4>
                <div class="aiseo-result-content" style="background:#f9f9f9;padding:20px;border-radius:5px;max-height:400px;overflow-y:auto;"></div>
                <div class="aiseo-button-group" style="margin-top:15px;">
                    <select class="aiseo-post-type-select">
                        <option value="post"><?php esc_html_e('Post', 'aiseo'); ?></option>
                        <option value="page"><?php esc_html_e('Page', 'aiseo'); ?></option>
                    </select>
                    <button type="button" class="button button-primary aiseo-create-post-from-generator">
                        <span class="dashicons dashicons-plus"></span>
                        <?php esc_html_e('Create Post', 'aiseo'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Rewriter -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e('Content Rewriter', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <form id="aiseo-rewrite-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Content to Rewrite', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <textarea name="content" rows="6" class="large-text" placeholder="<?php esc_attr_e('Paste your content here...', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>></textarea>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Rewrite Mode', 'aiseo'); ?></label>
                    <select name="mode" class="regular-text" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <option value="improve"><?php esc_html_e('Improve - Enhance quality and clarity', 'aiseo'); ?></option>
                        <option value="simplify"><?php esc_html_e('Simplify - Make it easier to understand', 'aiseo'); ?></option>
                        <option value="expand"><?php esc_html_e('Expand - Add more details and depth', 'aiseo'); ?></option>
                        <option value="shorten"><?php esc_html_e('Shorten - Make it more concise', 'aiseo'); ?></option>
                        <option value="professional"><?php esc_html_e('Professional - Formal business tone', 'aiseo'); ?></option>
                        <option value="casual"><?php esc_html_e('Casual - Friendly conversational tone', 'aiseo'); ?></option>
                    </select>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-rewrite-btn" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e('Rewrite Content', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-rewrite-result" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Rewritten Content:', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                    <div class="aiseo-button-group aiseo-mt-20">
                        <button type="button" class="button aiseo-copy-result"><?php esc_html_e('Copy to Clipboard', 'aiseo'); ?></button>
                        <select class="aiseo-post-type-select">
                            <option value="post"><?php esc_html_e('Post', 'aiseo'); ?></option>
                            <option value="page"><?php esc_html_e('Page', 'aiseo'); ?></option>
                        </select>
                        <button type="button" class="button button-primary aiseo-create-post-from-result" data-source="rewrite">
                            <span class="dashicons dashicons-plus"></span>
                            <?php esc_html_e('Create Post', 'aiseo'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Content Suggestions -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-lightbulb"></span>
                <?php esc_html_e('Content Suggestions', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <form id="aiseo-suggestions-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Topic or Niche', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" name="topic" class="regular-text" placeholder="<?php esc_attr_e('e.g., Digital Marketing', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                    <span class="aiseo-form-description"><?php esc_html_e('Get 5 AI-powered content ideas', 'aiseo'); ?></span>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-suggestions-btn" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-lightbulb"></span>
                        <?php esc_html_e('Get Suggestions', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-suggestions-result" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Content Ideas:', 'aiseo'); ?></h4>
                    <ul class="aiseo-result-list"></ul>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Outline Generator -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('Content Outline Generator', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <form id="aiseo-outline-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Topic', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" name="topic" class="regular-text" placeholder="<?php esc_attr_e('e.g., Complete Guide to WordPress SEO', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Focus Keyword (Optional)', 'aiseo'); ?></label>
                    <input type="text" name="keyword" class="regular-text" placeholder="<?php esc_attr_e('e.g., WordPress SEO', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-outline-btn" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e('Generate Outline', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-outline-result" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Content Outline:', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                    <div class="aiseo-button-group aiseo-mt-20">
                        <button type="button" class="button aiseo-copy-result"><?php esc_html_e('Copy to Clipboard', 'aiseo'); ?></button>
                        <select class="aiseo-post-type-select">
                            <option value="post"><?php esc_html_e('Post', 'aiseo'); ?></option>
                            <option value="page"><?php esc_html_e('Page', 'aiseo'); ?></option>
                        </select>
                        <button type="button" class="button button-primary aiseo-create-post-from-result" data-source="outline">
                            <span class="dashicons dashicons-plus"></span>
                            <?php esc_html_e('Create Post', 'aiseo'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- FAQ Generator -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-editor-help"></span>
                <?php esc_html_e('FAQ Generator', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <form id="aiseo-faq-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Content', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <textarea name="content" rows="6" class="large-text" placeholder="<?php esc_attr_e('Paste your content to generate FAQs from...', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>></textarea>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Number of FAQs', 'aiseo'); ?></label>
                    <select name="count" class="regular-text" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <option value="3">3</option>
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                    </select>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-faq-btn" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-editor-help"></span>
                        <?php esc_html_e('Generate FAQs', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-faq-result" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Generated FAQs:', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                    <div class="aiseo-button-group aiseo-mt-20">
                        <button type="button" class="button aiseo-copy-result"><?php esc_html_e('Copy to Clipboard', 'aiseo'); ?></button>
                        <select class="aiseo-post-type-select">
                            <option value="post"><?php esc_html_e('Post', 'aiseo'); ?></option>
                            <option value="page"><?php esc_html_e('Page', 'aiseo'); ?></option>
                        </select>
                        <button type="button" class="button button-primary aiseo-create-post-from-result" data-source="faq">
                            <span class="dashicons dashicons-plus"></span>
                            <?php esc_html_e('Create Post', 'aiseo'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
</div>

<script>
jQuery(document).ready(function($) {
    // AI Post Creator - Generate Content
    $('.aiseo-generate-post').on('click', function() {
        var $btn = $(this);
        var $form = $('#aiseo-create-post-form');
        var topic = $form.find('[name="topic"]').val().trim();
        var keyword = $form.find('[name="keyword"]').val();
        var postType = $form.find('[name="post_type"]').val();
        var length = $form.find('[name="content_length"]').val();
        
        if (!topic) {
            alert('Please enter a topic');
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update dashicons-spin"></span> Generating Content...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 60000,
            data: {
                action: 'aiseo_generate_content',
                nonce: aiseoAdmin.nonce,
                topic: topic,
                keyword: keyword,
                post_type: postType,
                length: length
            },
            success: function(response) {
                console.log('Generate Content Response:', response);
                if (response.success && response.data) {
                    var content = parseMarkdown(response.data);
                    $('#aiseo-post-creator-results .aiseo-result-content').html(content);
                    $('#aiseo-post-creator-results').show();
                } else {
                    var errorMsg = response.data || 'Failed to generate content';
                    $('#aiseo-post-creator-results .aiseo-result-content').html('<p style="color:red;">Error: ' + errorMsg + '</p>');
                    $('#aiseo-post-creator-results').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Generate Content Error:', xhr, status, error);
                var errorMsg = 'Connection error';
                if (status === 'timeout') {
                    errorMsg = 'Request timed out (60s). Please try again with shorter content.';
                }
                $('#aiseo-post-creator-results .aiseo-result-content').html('<p style="color:red;">Error: ' + errorMsg + '</p>');
                $('#aiseo-post-creator-results').show();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-edit"></span> <?php esc_html_e('Generate Post', 'aiseo'); ?>');
            }
        });
    });
    
    // Create Post from Generated Content
    $('.aiseo-create-post-from-generator').on('click', function() {
        var $btn = $(this);
        var content = $('#aiseo-post-creator-results .aiseo-result-content').html();
        var topic = $('#aiseo-post-creator-form [name="topic"]').val();
        var postType = $('#aiseo-post-creator-results .aiseo-post-type-select').val();
        
        if (!content) {
            alert('No content to create post from');
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
                topic: topic,
                content: content,
                post_type: postType,
                length: 'medium'
            },
            success: function(response) {
                console.log('Create Post Response:', response);
                // Clear refresh flag on success
                sessionStorage.removeItem('aiseo_nonce_refresh_attempted');
                
                if (response.success) {
                    $btn.after('<div class="notice notice-success is-dismissible" style="margin:10px 0;padding:10px;"><strong>Success!</strong> Post created! <a href="' + response.data.edit_url + '" target="_blank">Edit post</a></div>');
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
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> <?php esc_html_e('Create Post', 'aiseo'); ?>');
            }
        });
    });
    
    // Simple Markdown to HTML parser
    function parseMarkdown(text) {
        if (!text) return '';
        
        // Convert markdown to HTML
        var html = text
            // Headers
            .replace(/^### (.*$)/gim, '<h3>$1</h3>')
            .replace(/^## (.*$)/gim, '<h2>$1</h2>')
            .replace(/^# (.*$)/gim, '<h1>$1</h1>')
            // Bold
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/__(.+?)__/g, '<strong>$1</strong>')
            // Italic
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/_(.+?)_/g, '<em>$1</em>')
            // Links
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>')
            // Line breaks
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>');
        
        // Wrap in paragraph if not already wrapped
        if (!html.startsWith('<h') && !html.startsWith('<p')) {
            html = '<p>' + html + '</p>';
        }
        
        return html;
    }
    
    // Content Rewriter
    $('.aiseo-rewrite-btn').on('click', function() {
        var $btn = $(this);
        var $form = $('#aiseo-rewrite-form');
        var content = $form.find('[name="content"]').val();
        var mode = $form.find('[name="mode"]').val();
        
        if (!content) {
            $('#aiseo-rewrite-result').show().find('.aiseo-result-content').html('<p style="color:red;">Please enter content to rewrite</p>');
            return;
        }
        
        $btn.prop('disabled', true).text('Rewriting...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_rewrite_content',
                nonce: aiseoAdmin.nonce,
                content: content,
                mode: mode
            },
            success: function(response) {
                console.log('Rewrite Response:', response);
                if (response.success && response.data) {
                    var content = '';
                    // Handle different response formats
                    if (typeof response.data === 'string') {
                        content = response.data;
                    } else if (response.data.rewritten) {
                        content = response.data.rewritten;
                    } else if (response.data.content) {
                        content = response.data.content;
                    } else if (response.data.rewritten_content) {
                        content = response.data.rewritten_content;
                    } else if (response.data.text) {
                        content = response.data.text;
                    } else {
                        content = 'Rewrite completed but content format is unexpected. Check console.';
                    }
                    // Parse markdown to HTML for proper display
                    var htmlContent = parseMarkdown(content);
                    $('#aiseo-rewrite-result').show().find('.aiseo-result-content').html('<div style="background:#f0f0f0;padding:15px;border-radius:5px;">' + htmlContent + '</div>');
                } else {
                    var errorMsg = response.data || 'Rewrite failed';
                    if (typeof errorMsg === 'object') {
                        errorMsg = JSON.stringify(errorMsg);
                    }
                    $('#aiseo-rewrite-result').show().find('.aiseo-result-content').html('<p style="color:red;">Error: ' + errorMsg + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Rewrite Error:', xhr, status, error);
                $('#aiseo-rewrite-result').show().find('.aiseo-result-content').html('<p style="color:red;">Connection error: ' + error + '</p>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php esc_html_e('Rewrite Content', 'aiseo'); ?>');
            }
        });
    });
    
    // Content Suggestions
    $('.aiseo-suggestions-btn').on('click', function() {
        console.log('Content Suggestions button clicked');
        var $btn = $(this);
        var $form = $('#aiseo-suggestions-form');
        var topic = $form.find('[name="topic"]').val();
        
        console.log('Topic:', topic);
        
        if (!topic) {
            $('#aiseo-suggestions-result').show().find('.aiseo-result-list').html('<li style="color:red;">Please enter a topic</li>');
            return;
        }
        
        $btn.prop('disabled', true).text('Getting suggestions...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_content_suggestions',
                nonce: aiseoAdmin.nonce,
                topic: topic
            },
            success: function(response) {
                console.log('Content Suggestions Response:', response);
                if (response.success && response.data) {
                    var html = '';
                    if (Array.isArray(response.data) && response.data.length > 0) {
                        $.each(response.data, function(i, suggestion) {
                            var text = typeof suggestion === 'string' ? suggestion : (suggestion.title || suggestion.text || JSON.stringify(suggestion));
                            html += '<li>' + text + '</li>';
                        });
                    } else {
                        html = '<li style="color:orange;">No suggestions generated. Try a different topic.</li>';
                    }
                    $('#aiseo-suggestions-result').show().find('.aiseo-result-list').html(html);
                } else {
                    var errorMsg = response.data || 'Failed to get suggestions';
                    if (typeof errorMsg === 'object') {
                        errorMsg = JSON.stringify(errorMsg);
                    }
                    $('#aiseo-suggestions-result').show().find('.aiseo-result-list').html('<li style="color:red;">Error: ' + errorMsg + '</li>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Content Suggestions Error:', xhr, status, error);
                $('#aiseo-suggestions-result').show().find('.aiseo-result-list').html('<li style="color:red;">Connection error: ' + error + '</li>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e('Get Suggestions', 'aiseo'); ?>');
            }
        });
    });
    
    // Outline Generator
    $('.aiseo-outline-btn').on('click', function() {
        var $btn = $(this);
        var $form = $('#aiseo-outline-form');
        var topic = $form.find('[name="topic"]').val();
        var keyword = $form.find('[name="keyword"]').val();
        
        if (!topic) {
            $('#aiseo-outline-result').show().find('.aiseo-result-content').html('<p style="color:red;">Please enter a topic</p>');
            return;
        }
        
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_outline',
                nonce: aiseoAdmin.nonce,
                topic: topic,
                keyword: keyword
            },
            success: function(response) {
                console.log('Outline Response:', response);
                if (response.success && response.data) {
                    var html = '';
                    
                    // Handle string response
                    if (typeof response.data === 'string') {
                        html = response.data.replace(/\n/g, '<br>');
                    } 
                    // Handle object with outline property (nested structure)
                    else if (response.data.outline) {
                        var outline = response.data.outline;
                        
                        // Check if outline has nested structure (introduction, sections, conclusion)
                        if (outline.introduction || outline.sections || outline.conclusion) {
                            html = '<div class="outline-structured">';
                            
                            // Introduction
                            if (outline.introduction && outline.introduction.length > 0) {
                                html += '<h4>Introduction</h4><ul>';
                                $.each(outline.introduction, function(i, item) {
                                    html += '<li>' + (item.text || item.title || item) + '</li>';
                                });
                                html += '</ul>';
                            }
                            
                            // Sections
                            if (outline.sections && outline.sections.length > 0) {
                                html += '<h4>Main Sections</h4><ul>';
                                $.each(outline.sections, function(i, section) {
                                    html += '<li><strong>' + (section.title || section.heading || 'Section ' + (i+1)) + '</strong>';
                                    if (section.subsections && section.subsections.length > 0) {
                                        html += '<ul>';
                                        $.each(section.subsections, function(j, sub) {
                                            html += '<li>' + (sub.title || sub.text || sub) + '</li>';
                                        });
                                        html += '</ul>';
                                    }
                                    html += '</li>';
                                });
                                html += '</ul>';
                            }
                            
                            // Conclusion
                            if (outline.conclusion && outline.conclusion.length > 0) {
                                html += '<h4>Conclusion</h4><ul>';
                                $.each(outline.conclusion, function(i, item) {
                                    html += '<li>' + (item.text || item.title || item) + '</li>';
                                });
                                html += '</ul>';
                            }
                            
                            // CTA
                            if (outline.cta && outline.cta.length > 0) {
                                html += '<h4>Call to Action</h4><ul>';
                                $.each(outline.cta, function(i, item) {
                                    html += '<li>' + (item.text || item.title || item) + '</li>';
                                });
                                html += '</ul>';
                            }
                            
                            html += '</div>';
                        }
                        // Simple string outline
                        else if (typeof outline === 'string') {
                            html = outline.replace(/\n/g, '<br>');
                        }
                        // Array outline
                        else if (Array.isArray(outline)) {
                            html = '<ul>';
                            $.each(outline, function(i, item) {
                                html += '<li>' + (item.title || item.heading || item.text || item) + '</li>';
                            });
                            html += '</ul>';
                        }
                    }
                    // Handle object with content property
                    else if (response.data.content) {
                        html = response.data.content.replace(/\n/g, '<br>');
                    }
                    // Handle array response
                    else if (Array.isArray(response.data)) {
                        html = '<ul>';
                        $.each(response.data, function(i, item) {
                            if (typeof item === 'string') {
                                html += '<li>' + item + '</li>';
                            } else {
                                html += '<li>' + (item.title || item.heading || item.text || JSON.stringify(item)) + '</li>';
                            }
                        });
                        html += '</ul>';
                    }
                    
                    // Catch-all: If html is still empty, try to display raw data
                    if (!html && response.data) {
                        if (typeof response.data === 'object') {
                            html = '<pre style="background:white;padding:10px;border-radius:5px;overflow:auto;">' + JSON.stringify(response.data, null, 2) + '</pre>';
                        } else {
                            html = '<div>' + response.data + '</div>';
                        }
                    }
                    
                    if (html) {
                        $('#aiseo-outline-result').show().find('.aiseo-result-content').html('<div style="background:#f0f0f0;padding:15px;border-radius:5px;">' + html + '</div>');
                    } else {
                        $('#aiseo-outline-result').show().find('.aiseo-result-content').html('<p style="color:orange;">⚠️ No outline generated. The API returned an empty result. Try with a different topic or check your API key.</p>');
                    }
                } else {
                    var errorMsg = response.data || 'Failed to generate outline';
                    if (typeof errorMsg === 'object') {
                        errorMsg = JSON.stringify(errorMsg);
                    }
                    $('#aiseo-outline-result').show().find('.aiseo-result-content').html('<p style="color:red;">Error: ' + errorMsg + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Outline Error:', xhr, status, error);
                $('#aiseo-outline-result').show().find('.aiseo-result-content').html('<p style="color:red;">Connection error: ' + error + '</p>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-list-view"></span> <?php esc_html_e('Generate Outline', 'aiseo'); ?>');
            }
        });
    });
    
    // FAQ Generator
    $('.aiseo-faq-btn').on('click', function() {
        var $btn = $(this);
        var $form = $('#aiseo-faq-form');
        var content = $form.find('[name="content"]').val();
        var count = $form.find('[name="count"]').val();
        
        if (!content) {
            $('#aiseo-faq-result').show().find('.aiseo-result-content').html('<p style="color:red;">Please enter content</p>');
            return;
        }
        
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_faq',
                nonce: aiseoAdmin.nonce,
                content: content,
                count: count
            },
            success: function(response) {
                console.log('FAQ Response:', response);
                if (response.success && response.data) {
                    var html = '';
                    var faqs = [];
                    
                    // Extract FAQs from different response formats
                    if (Array.isArray(response.data)) {
                        faqs = response.data;
                    } else if (response.data.faqs && Array.isArray(response.data.faqs)) {
                        faqs = response.data.faqs;
                    } else if (response.data.html && response.data.html !== '<div class="aiseo-faq"></div>') {
                        // Use the HTML if provided and not empty
                        html = response.data.html;
                    }
                    
                    // Build HTML from FAQs array
                    if (faqs.length > 0) {
                        $.each(faqs, function(i, faq) {
                            var question = faq.question || faq.q || 'No question';
                            var answer = faq.answer || faq.a || 'No answer';
                            html += '<div class="faq-item" style="margin-bottom:15px;padding:10px;background:white;border-left:3px solid #0073aa;">';
                            html += '<strong>Q: ' + question + '</strong>';
                            html += '<p style="margin:5px 0 0 0;">A: ' + answer + '</p></div>';
                        });
                    }
                    
                    if (html) {
                        $('#aiseo-faq-result').show().find('.aiseo-result-content').html('<div style="background:#f0f0f0;padding:15px;border-radius:5px;">' + html + '</div>');
                    } else {
                        $('#aiseo-faq-result').show().find('.aiseo-result-content').html('<p style="color:orange;">⚠️ No FAQs generated. The API returned an empty result. Try with different content or check your API key.</p>');
                    }
                } else {
                    var errorMsg = response.data || 'Failed to generate FAQs';
                    if (typeof errorMsg === 'object') {
                        errorMsg = JSON.stringify(errorMsg);
                    }
                    $('#aiseo-faq-result').show().find('.aiseo-result-content').html('<p style="color:red;">Error: ' + errorMsg + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('FAQ Error:', xhr, status, error);
                $('#aiseo-faq-result').show().find('.aiseo-result-content').html('<p style="color:red;">Connection error: ' + error + '</p>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-editor-help"></span> <?php esc_html_e('Generate FAQs', 'aiseo'); ?>');
            }
        });
    });
    
    // Copy to clipboard
    $(document).on('click', '.aiseo-copy-result', function() {
        var $btn = $(this);
        var text = $btn.closest('.aiseo-result-box').find('.aiseo-result-content').text();
        navigator.clipboard.writeText(text).then(function() {
            var originalText = $btn.text();
            $btn.text('✓ Copied!').css('color', 'green');
            setTimeout(function() {
                $btn.text(originalText).css('color', '');
            }, 2000);
        });
    });
    
    // Create post from result
    $(document).on('click', '.aiseo-create-post-from-result', function() {
        var $btn = $(this);
        var $resultBox = $btn.closest('.aiseo-result-box');
        var content = $resultBox.find('.aiseo-result-content').html();
        var postType = $resultBox.find('.aiseo-post-type-select').val();
        var source = $btn.data('source');
        
        $btn.prop('disabled', true).text('Creating...');
        
        // Extract title from content
        var tempDiv = $('<div>').html(content);
        var title = '';
        
        // For FAQ content, extract first question
        if (source === 'faq') {
            var textContent = tempDiv.text().trim();
            // Look for "Q:" pattern
            var qMatch = textContent.match(/Q:\s*([^?]+\?)/i);
            if (qMatch) {
                title = qMatch[1].trim();
            }
        }
        
        // For other content, try headings first
        if (!title) {
            title = tempDiv.find('h1, h2, h3').first().text();
        }
        
        // Fallback to first sentence
        if (!title) {
            var textContent = tempDiv.text().trim();
            title = textContent.split(/[.!?]/)[0].substring(0, 100);
        }
        
        // Final fallback
        if (!title) {
            title = 'Generated from ' + source;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 30000, // 30 second timeout
            data: {
                action: 'aiseo_create_post',
                nonce: aiseoAdmin.nonce,
                topic: title,
                content: content,
                post_type: postType,
                length: 'medium'
            },
            success: function(response) {
                console.log('Create Post Response:', response);
                if (response.success) {
                    $resultBox.prepend('<div class="notice notice-success is-dismissible" style="margin:10px 0;padding:10px;"><strong>Success!</strong> Post created! <a href="' + response.data.edit_url + '" target="_blank">Edit post</a></div>');
                    // Don't auto-hide - let user manually dismiss if needed
                } else {
                    $resultBox.prepend('<div class="notice notice-error" style="margin:10px 0;padding:10px;"><strong>Error:</strong> ' + response.data + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Create Post Error:', xhr, status, error);
                var errorMsg = 'Connection error';
                if (status === 'timeout') {
                    errorMsg = 'Request timed out (30s). The post may still be creating in the background.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error (500). Check if the post was created anyway.';
                }
                $resultBox.prepend('<div class="notice notice-error" style="margin:10px 0;padding:10px;"><strong>Error:</strong> ' + errorMsg + '</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> <?php esc_html_e('Create Post', 'aiseo'); ?>');
            }
        });
    });
});
</script>
