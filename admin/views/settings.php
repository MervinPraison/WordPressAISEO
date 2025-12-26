<?php
/**
 * Settings Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['aiseo_settings_submit']) && check_admin_referer('aiseo_settings_nonce', 'aiseo_settings_nonce')) {
    // API Settings
    if (isset($_POST['aiseo_api_key']) && !empty($_POST['aiseo_api_key'])) {
        $aiseo_api_key_input = sanitize_text_field(wp_unslash($_POST['aiseo_api_key']));
        // Only save if it's not the masked value
        if (strpos($aiseo_api_key_input, '*') === false && !empty($aiseo_api_key_input)) {
            AISEO_Helpers::save_api_key($aiseo_api_key_input);
        }
    }
    if (isset($_POST['aiseo_model'])) {
        update_option('aiseo_model', sanitize_text_field(wp_unslash($_POST['aiseo_model'])));
    }
    if (isset($_POST['aiseo_max_tokens'])) {
        update_option('aiseo_max_tokens', absint($_POST['aiseo_max_tokens']));
    }
    if (isset($_POST['aiseo_temperature'])) {
        update_option('aiseo_temperature', floatval($_POST['aiseo_temperature']));
    }
    
    // Performance Settings
    if (isset($_POST['aiseo_cache_ttl'])) {
        update_option('aiseo_cache_ttl', absint($_POST['aiseo_cache_ttl']));
    }
    if (isset($_POST['aiseo_rate_limit'])) {
        update_option('aiseo_rate_limit', absint($_POST['aiseo_rate_limit']));
    }
    
    // Feature Toggles
    update_option('aiseo_auto_generate_title', isset($_POST['aiseo_auto_generate_title']) ? '1' : '0');
    update_option('aiseo_auto_generate_description', isset($_POST['aiseo_auto_generate_description']) ? '1' : '0');
    update_option('aiseo_enable_schema', isset($_POST['aiseo_enable_schema']) ? '1' : '0');
    update_option('aiseo_enable_sitemap', isset($_POST['aiseo_enable_sitemap']) ? '1' : '0');
    
    // Logging
    update_option('aiseo_enable_logging', isset($_POST['aiseo_enable_logging']) ? '1' : '0');
    update_option('aiseo_log_level', isset($_POST['aiseo_log_level']) ? sanitize_text_field(wp_unslash($_POST['aiseo_log_level'])) : 'error');
    
    // Homepage SEO Settings
    $aiseo_homepage_seo_form = new AISEO_Homepage_SEO();
    $aiseo_homepage_settings = array();
    if (isset($_POST['aiseo_home_title'])) {
        $aiseo_homepage_settings['home_title'] = sanitize_text_field(wp_unslash($_POST['aiseo_home_title']));
    }
    if (isset($_POST['aiseo_home_description'])) {
        $aiseo_homepage_settings['home_description'] = sanitize_textarea_field(wp_unslash($_POST['aiseo_home_description']));
    }
    if (isset($_POST['aiseo_home_keywords'])) {
        $aiseo_homepage_settings['home_keywords'] = sanitize_text_field(wp_unslash($_POST['aiseo_home_keywords']));
    }
    if (isset($_POST['aiseo_blog_title'])) {
        $aiseo_homepage_settings['blog_title'] = sanitize_text_field(wp_unslash($_POST['aiseo_blog_title']));
    }
    if (isset($_POST['aiseo_blog_description'])) {
        $aiseo_homepage_settings['blog_description'] = sanitize_textarea_field(wp_unslash($_POST['aiseo_blog_description']));
    }
    if (isset($_POST['aiseo_blog_keywords'])) {
        $aiseo_homepage_settings['blog_keywords'] = sanitize_text_field(wp_unslash($_POST['aiseo_blog_keywords']));
    }
    $aiseo_homepage_seo_form->update_settings($aiseo_homepage_settings);
    
    // Webmaster Verification Codes
    $aiseo_webmaster_form = new AISEO_Webmaster();
    $aiseo_verification = array();
    if (isset($_POST['aiseo_verification_google'])) {
        $aiseo_verification['google'] = sanitize_text_field(wp_unslash($_POST['aiseo_verification_google']));
    }
    if (isset($_POST['aiseo_verification_bing'])) {
        $aiseo_verification['bing'] = sanitize_text_field(wp_unslash($_POST['aiseo_verification_bing']));
    }
    if (isset($_POST['aiseo_verification_yandex'])) {
        $aiseo_verification['yandex'] = sanitize_text_field(wp_unslash($_POST['aiseo_verification_yandex']));
    }
    if (isset($_POST['aiseo_verification_pinterest'])) {
        $aiseo_verification['pinterest'] = sanitize_text_field(wp_unslash($_POST['aiseo_verification_pinterest']));
    }
    if (isset($_POST['aiseo_verification_baidu'])) {
        $aiseo_verification['baidu'] = sanitize_text_field(wp_unslash($_POST['aiseo_verification_baidu']));
    }
    $aiseo_webmaster_form->update_verification_codes($aiseo_verification);
    
    // Analytics Settings
    $aiseo_analytics_form = new AISEO_Analytics();
    $aiseo_analytics_data = array();
    if (isset($_POST['aiseo_ga_tracking_id'])) {
        $aiseo_analytics_data['tracking_id'] = sanitize_text_field(wp_unslash($_POST['aiseo_ga_tracking_id']));
    }
    $aiseo_analytics_data['enabled'] = isset($_POST['aiseo_ga_enabled']);
    $aiseo_analytics_data['anonymize_ip'] = isset($_POST['aiseo_ga_anonymize_ip']);
    $aiseo_analytics_data['track_admin'] = isset($_POST['aiseo_ga_track_admin']);
    $aiseo_analytics_data['track_logged_in'] = isset($_POST['aiseo_ga_track_logged_in']);
    $aiseo_analytics_form->update_settings($aiseo_analytics_data);
    
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'aiseo') . '</p></div>';
}

// Get current settings
$aiseo_api_key = AISEO_Helpers::get_api_key();
$aiseo_model = get_option('aiseo_model', 'gpt-4o-mini');
$aiseo_max_tokens = get_option('aiseo_max_tokens', 1000);
$aiseo_temperature = get_option('aiseo_temperature', 0.7);
$aiseo_cache_ttl = get_option('aiseo_cache_ttl', 86400);
$aiseo_rate_limit = get_option('aiseo_rate_limit', 10);
$aiseo_auto_title = get_option('aiseo_auto_generate_title', '0');
$aiseo_auto_desc = get_option('aiseo_auto_generate_description', '0');
$aiseo_enable_schema = get_option('aiseo_enable_schema', '1');
$aiseo_enable_sitemap = get_option('aiseo_enable_sitemap', '1');
$aiseo_enable_logging = get_option('aiseo_enable_logging', '0');
$aiseo_log_level = get_option('aiseo_log_level', 'error');

// Get homepage SEO settings
$aiseo_homepage_seo = new AISEO_Homepage_SEO();
$aiseo_homepage_settings = $aiseo_homepage_seo->get_settings();

// Get webmaster verification codes
$aiseo_webmaster = new AISEO_Webmaster();
$aiseo_verification_codes = $aiseo_webmaster->get_verification_codes();

// Get analytics settings
$aiseo_analytics = new AISEO_Analytics();
$aiseo_analytics_settings = $aiseo_analytics->get_settings();
?>

<div class="aiseo-settings">
    <form method="post" action="">
        <?php wp_nonce_field('aiseo_settings_nonce', 'aiseo_settings_nonce'); ?>
        
        <!-- API Settings -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('API Settings', 'aiseo'); ?></h2>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label">
                    <?php esc_html_e('OpenAI API Key', 'aiseo'); ?>
                    <span class="required">*</span>
                </label>
                <input type="password" 
                       name="aiseo_api_key" 
                       value="<?php echo esc_attr($aiseo_api_key ? str_repeat('*', 20) : ''); ?>" 
                       class="large-text"
                       placeholder="sk-..." />
                <span class="aiseo-form-description">
                    <?php esc_html_e('Get your API key from', 'aiseo'); ?> 
                    <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                </span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('AI Model', 'aiseo'); ?></label>
                <select name="aiseo_model" class="regular-text">
                    <option value="gpt-4o-mini" <?php selected($aiseo_model, 'gpt-4o-mini'); ?>>GPT-4o Mini (Recommended)</option>
                    <option value="gpt-4o" <?php selected($aiseo_model, 'gpt-4o'); ?>>GPT-4o</option>
                    <option value="gpt-4" <?php selected($aiseo_model, 'gpt-4'); ?>>GPT-4</option>
                    <option value="gpt-3.5-turbo" <?php selected($aiseo_model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                </select>
                <span class="aiseo-form-description"><?php esc_html_e('GPT-4o-mini offers the best balance of speed and quality', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Max Tokens', 'aiseo'); ?></label>
                <input type="number" 
                       name="aiseo_max_tokens" 
                       value="<?php echo esc_attr($aiseo_max_tokens); ?>" 
                       class="small-text"
                       min="100" 
                       max="4000" />
                <span class="aiseo-form-description"><?php esc_html_e('Maximum tokens per API request (100-4000)', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Temperature', 'aiseo'); ?></label>
                <input type="number" 
                       name="aiseo_temperature" 
                       value="<?php echo esc_attr($aiseo_temperature); ?>" 
                       class="small-text"
                       min="0" 
                       max="2" 
                       step="0.1" />
                <span class="aiseo-form-description"><?php esc_html_e('Controls randomness: 0 = focused, 2 = creative (0-2)', 'aiseo'); ?></span>
            </div>
        </div>
        
        <!-- Performance Settings -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('Performance', 'aiseo'); ?></h2>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Cache TTL (seconds)', 'aiseo'); ?></label>
                <input type="number" 
                       name="aiseo_cache_ttl" 
                       value="<?php echo esc_attr($aiseo_cache_ttl); ?>" 
                       class="small-text"
                       min="3600" />
                <span class="aiseo-form-description"><?php esc_html_e('How long to cache AI responses (default: 86400 = 24 hours)', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Rate Limit (requests/minute)', 'aiseo'); ?></label>
                <input type="number" 
                       name="aiseo_rate_limit" 
                       value="<?php echo esc_attr($aiseo_rate_limit); ?>" 
                       class="small-text"
                       min="1" 
                       max="60" />
                <span class="aiseo-form-description"><?php esc_html_e('Maximum API requests per minute (default: 10)', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <button type="button" class="button button-secondary" onclick="if(confirm('Clear all AISEO caches?')) { location.href='<?php echo esc_url(admin_url('admin.php?page=aiseo&tab=settings&action=clear_cache')); ?>'; }">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e('Clear All Caches', 'aiseo'); ?>
                </button>
            </div>
        </div>
        
        <!-- Feature Toggles -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('Features', 'aiseo'); ?></h2>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_auto_generate_title" value="1" <?php checked($aiseo_auto_title, '1'); ?> />
                    <?php esc_html_e('Auto-generate SEO titles for new posts', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_auto_generate_description" value="1" <?php checked($aiseo_auto_desc, '1'); ?> />
                    <?php esc_html_e('Auto-generate meta descriptions for new posts', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_enable_schema" value="1" <?php checked($aiseo_enable_schema, '1'); ?> />
                    <?php esc_html_e('Enable Schema Markup (JSON-LD)', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_enable_sitemap" value="1" <?php checked($aiseo_enable_sitemap, '1'); ?> />
                    <?php esc_html_e('Enable XML Sitemap', 'aiseo'); ?>
                </label>
            </div>
        </div>
        
        <!-- Security & Logging -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('Security & Logging', 'aiseo'); ?></h2>
            
            <div class="aiseo-card" style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 15px; margin-bottom: 20px;">
                <p style="margin: 0;">
                    <strong><?php esc_html_e('Security Status:', 'aiseo'); ?></strong>
                    <?php esc_html_e('API keys are encrypted with AES-256-CBC', 'aiseo'); ?>
                </p>
            </div>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_enable_logging" value="1" <?php checked($aiseo_enable_logging, '1'); ?> />
                    <?php esc_html_e('Enable Error Logging', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Log Level', 'aiseo'); ?></label>
                <select name="aiseo_log_level" class="regular-text">
                    <option value="error" <?php selected($aiseo_log_level, 'error'); ?>>Error Only</option>
                    <option value="warning" <?php selected($aiseo_log_level, 'warning'); ?>>Warning & Error</option>
                    <option value="info" <?php selected($aiseo_log_level, 'info'); ?>>Info, Warning & Error</option>
                    <option value="debug" <?php selected($aiseo_log_level, 'debug'); ?>>Debug (All)</option>
                </select>
            </div>
        </div>
        
        <!-- Homepage SEO Settings -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('Homepage SEO', 'aiseo'); ?></h2>
            
            <?php
            $aiseo_frontpage_id = get_option('page_on_front');
            $aiseo_blog_id = get_option('page_for_posts');
            $aiseo_show_on_front = get_option('show_on_front');
            
            if ($aiseo_show_on_front === 'page' && ($aiseo_frontpage_id || $aiseo_blog_id)) {
                echo '<div class="aiseo-card" style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 15px; margin-bottom: 20px;">';
                echo '<p style="margin: 0;"><strong>' . esc_html__('Note:', 'aiseo') . '</strong> ';
                echo esc_html__('You are using a static front page. You can also edit SEO settings directly on:', 'aiseo') . ' ';
                if ($aiseo_frontpage_id) {
                    echo '<a href="' . esc_url(get_edit_post_link($aiseo_frontpage_id)) . '">' . esc_html__('Front Page', 'aiseo') . '</a>';
                }
                if ($aiseo_frontpage_id && $aiseo_blog_id) {
                    echo ' | ';
                }
                if ($aiseo_blog_id) {
                    echo '<a href="' . esc_url(get_edit_post_link($aiseo_blog_id)) . '">' . esc_html__('Blog Page', 'aiseo') . '</a>';
                }
                echo '</p></div>';
            }
            ?>
            
            <h3><?php esc_html_e('Homepage Settings', 'aiseo'); ?></h3>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Homepage Title', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_home_title" 
                       value="<?php echo esc_attr($aiseo_homepage_settings['home_title']); ?>" 
                       class="large-text"
                       placeholder="<?php echo esc_attr(get_bloginfo('name') . ' | ' . get_bloginfo('description')); ?>" />
                <span class="aiseo-form-description"><?php esc_html_e('Custom title for your homepage. Leave empty to use default.', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Homepage Meta Description', 'aiseo'); ?></label>
                <textarea name="aiseo_home_description" 
                          class="large-text" 
                          rows="3"
                          placeholder="<?php esc_attr_e('Enter a compelling description for your homepage (155-160 characters recommended)', 'aiseo'); ?>"><?php echo esc_textarea($aiseo_homepage_settings['home_description']); ?></textarea>
                <span class="aiseo-form-description">
                    <?php esc_html_e('Meta description for your homepage. Recommended: 155-160 characters.', 'aiseo'); ?>
                    <span class="aiseo-char-count" data-target="aiseo_home_description"><?php echo esc_html(strlen($aiseo_homepage_settings['home_description'])); ?>/160</span>
                </span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Homepage Meta Keywords', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_home_keywords" 
                       value="<?php echo esc_attr($aiseo_homepage_settings['home_keywords']); ?>" 
                       class="large-text"
                       placeholder="<?php esc_attr_e('keyword1, keyword2, keyword3', 'aiseo'); ?>" />
                <span class="aiseo-form-description"><?php esc_html_e('Comma-separated keywords for your homepage.', 'aiseo'); ?></span>
            </div>
            
            <h3><?php esc_html_e('Blog Page Settings', 'aiseo'); ?></h3>
            <p class="description"><?php esc_html_e('These settings apply when you have a separate blog page (Settings → Reading → Posts page).', 'aiseo'); ?></p>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Blog Page Title', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_blog_title" 
                       value="<?php echo esc_attr($aiseo_homepage_settings['blog_title']); ?>" 
                       class="large-text"
                       placeholder="<?php esc_attr_e('Blog | Your Site Name', 'aiseo'); ?>" />
                <span class="aiseo-form-description"><?php esc_html_e('Custom title for your blog page.', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Blog Page Meta Description', 'aiseo'); ?></label>
                <textarea name="aiseo_blog_description" 
                          class="large-text" 
                          rows="3"
                          placeholder="<?php esc_attr_e('Enter a description for your blog page', 'aiseo'); ?>"><?php echo esc_textarea($aiseo_homepage_settings['blog_description']); ?></textarea>
                <span class="aiseo-form-description"><?php esc_html_e('Meta description for your blog page.', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Blog Page Meta Keywords', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_blog_keywords" 
                       value="<?php echo esc_attr($aiseo_homepage_settings['blog_keywords']); ?>" 
                       class="large-text"
                       placeholder="<?php esc_attr_e('blog, articles, news', 'aiseo'); ?>" />
                <span class="aiseo-form-description"><?php esc_html_e('Comma-separated keywords for your blog page.', 'aiseo'); ?></span>
            </div>
        </div>
        
        <!-- Webmaster Verification -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('Webmaster Verification', 'aiseo'); ?></h2>
            <p class="description"><?php esc_html_e('Enter verification codes from search engines and webmaster tools. You can paste the full meta tag or just the verification code.', 'aiseo'); ?></p>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Google Search Console', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_verification_google" 
                       value="<?php echo esc_attr($aiseo_verification_codes['google']); ?>" 
                       class="large-text"
                       placeholder="<?php esc_attr_e('Enter Google verification code', 'aiseo'); ?>" />
                <span class="aiseo-form-description">
                    <?php esc_html_e('Get your code from', 'aiseo'); ?> 
                    <a href="https://search.google.com/search-console" target="_blank">Google Search Console</a>
                </span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Bing Webmaster Tools', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_verification_bing" 
                       value="<?php echo esc_attr($aiseo_verification_codes['bing']); ?>" 
                       class="large-text"
                       placeholder="<?php esc_attr_e('Enter Bing verification code', 'aiseo'); ?>" />
                <span class="aiseo-form-description">
                    <?php esc_html_e('Get your code from', 'aiseo'); ?> 
                    <a href="https://www.bing.com/webmasters" target="_blank">Bing Webmaster Tools</a>
                </span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Yandex Webmaster', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_verification_yandex" 
                       value="<?php echo esc_attr($aiseo_verification_codes['yandex']); ?>" 
                       class="large-text"
                       placeholder="<?php esc_attr_e('Enter Yandex verification code', 'aiseo'); ?>" />
                <span class="aiseo-form-description">
                    <?php esc_html_e('Get your code from', 'aiseo'); ?> 
                    <a href="https://webmaster.yandex.com" target="_blank">Yandex Webmaster</a>
                </span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Pinterest', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_verification_pinterest" 
                       value="<?php echo esc_attr($aiseo_verification_codes['pinterest']); ?>" 
                       class="large-text"
                       placeholder="<?php esc_attr_e('Enter Pinterest verification code', 'aiseo'); ?>" />
                <span class="aiseo-form-description">
                    <?php esc_html_e('Get your code from', 'aiseo'); ?> 
                    <a href="https://pinterest.com/settings/claim" target="_blank">Pinterest Settings</a>
                </span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Baidu Webmaster', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_verification_baidu" 
                       value="<?php echo esc_attr($aiseo_verification_codes['baidu']); ?>" 
                       class="large-text"
                       placeholder="<?php esc_attr_e('Enter Baidu verification code', 'aiseo'); ?>" />
                <span class="aiseo-form-description">
                    <?php esc_html_e('Get your code from', 'aiseo'); ?> 
                    <a href="https://ziyuan.baidu.com" target="_blank">Baidu Webmaster</a>
                </span>
            </div>
        </div>
        
        <!-- Google Analytics -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('Google Analytics', 'aiseo'); ?></h2>
            
            <div class="aiseo-form-group">
                <label class="aiseo-toggle">
                    <input type="checkbox" 
                           name="aiseo_ga_enabled" 
                           value="1" 
                           <?php checked($aiseo_analytics_settings['enabled'], true); ?> />
                    <span class="aiseo-toggle-slider"></span>
                    <?php esc_html_e('Enable Google Analytics', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Tracking ID', 'aiseo'); ?></label>
                <input type="text" 
                       name="aiseo_ga_tracking_id" 
                       value="<?php echo esc_attr($aiseo_analytics_settings['tracking_id']); ?>" 
                       class="regular-text"
                       placeholder="<?php esc_attr_e('G-XXXXXXXXXX or UA-XXXXX-X', 'aiseo'); ?>" />
                <span class="aiseo-form-description">
                    <?php esc_html_e('Enter your GA4 (G-XXXXXXXXXX) or Universal Analytics (UA-XXXXX-X) tracking ID.', 'aiseo'); ?>
                </span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-toggle">
                    <input type="checkbox" 
                           name="aiseo_ga_anonymize_ip" 
                           value="1" 
                           <?php checked($aiseo_analytics_settings['anonymize_ip'], true); ?> />
                    <span class="aiseo-toggle-slider"></span>
                    <?php esc_html_e('Anonymize IP addresses (recommended for GDPR compliance)', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-toggle">
                    <input type="checkbox" 
                           name="aiseo_ga_track_logged_in" 
                           value="1" 
                           <?php checked($aiseo_analytics_settings['track_logged_in'], true); ?> />
                    <span class="aiseo-toggle-slider"></span>
                    <?php esc_html_e('Track logged-in users', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-toggle">
                    <input type="checkbox" 
                           name="aiseo_ga_track_admin" 
                           value="1" 
                           <?php checked($aiseo_analytics_settings['track_admin'], true); ?> />
                    <span class="aiseo-toggle-slider"></span>
                    <?php esc_html_e('Track admin pages', 'aiseo'); ?>
                </label>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="aiseo-form-section">
            <button type="submit" name="aiseo_settings_submit" class="button button-primary button-large">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e('Save Settings', 'aiseo'); ?>
            </button>
        </div>
    </form>
</div>
