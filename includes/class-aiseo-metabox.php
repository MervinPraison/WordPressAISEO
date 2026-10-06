<?php
/**
 * AISEO Post Editor Metabox
 *
 * Adds SEO controls to the post editor
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Metabox {
    
    /**
     * Initialize metabox
     */
    public function init() {
        // Add metabox
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        
        // Save metabox data
        add_action('save_post', array($this, 'save_metabox'), 10, 2);
        
        // Enqueue metabox scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_metabox_scripts'));
        
        // AJAX handlers for AI generation.
        // These use metabox-specific action names: AISEO_Admin registers its own
        // handlers for the unprefixed `aiseo_generate_*` / `aiseo_analyze_content`
        // actions, and loads first, so sharing names meant the admin handlers
        // silently shadowed these ones (and rejected this metabox's nonce).
        add_action('wp_ajax_aiseo_metabox_generate_keyword', array($this, 'ajax_generate_keyword'));
        add_action('wp_ajax_aiseo_metabox_generate_title', array($this, 'ajax_generate_title'));
        add_action('wp_ajax_aiseo_metabox_generate_description', array($this, 'ajax_generate_description'));
        add_action('wp_ajax_aiseo_metabox_analyze_content', array($this, 'ajax_analyze_content'));
    }
    
    /**
     * Add metabox to post editor
     */
    public function add_metabox() {
        $post_types = array('post', 'page');
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'aiseo_metabox',
                'AISEO - AI SEO Optimization',
                array($this, 'render_metabox'),
                $post_type,
                'normal',
                'high'
            );
        }
    }
    
    /**
     * Render metabox content
     *
     * @param WP_Post $post Post object
     */
    public function render_metabox($post) {
        // Add nonce for security
        wp_nonce_field('aiseo_metabox_nonce', 'aiseo_metabox_nonce');
        
        // Get current values
        $meta_title = get_post_meta($post->ID, '_aiseo_meta_title', true);
        $meta_description = get_post_meta($post->ID, '_aiseo_meta_description', true);
        $focus_keyword = get_post_meta($post->ID, '_aiseo_focus_keyword', true);
        $canonical_url = get_post_meta($post->ID, '_aiseo_canonical_url', true);
        $noindex = get_post_meta($post->ID, '_aiseo_noindex', true);
        $nofollow = get_post_meta($post->ID, '_aiseo_nofollow', true);
        
        // Use post title as default if meta title is empty
        if (empty($meta_title)) {
            $meta_title = get_the_title($post->ID);
        }
        
        // Use post excerpt as default if meta description is empty
        if (empty($meta_description)) {
            $meta_description = $post->post_excerpt;
            // If no excerpt, use first 160 characters of content
            if (empty($meta_description)) {
                $content = wp_strip_all_tags($post->post_content);
                $meta_description = wp_trim_words($content, 25, '...');
            }
        }
        
        // Get SEO score if available
        $analysis = new AISEO_Analysis();
        $score_data = $analysis->analyze_post($post->ID, $focus_keyword);
        $seo_score = isset($score_data['overall_score']) ? $score_data['overall_score'] : 0;
        $seo_status = isset($score_data['status']) ? $score_data['status'] : 'poor';
        
        ?>
        <div class="aiseo-metabox">
            <!-- SEO Score -->
            <div style="background: #f6f7f7; padding: 15px; margin: 0 0 20px 0; border-radius: 3px;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="position: relative; width: 70px; height: 70px;">
                        <svg width="70" height="70" style="transform: rotate(-90deg);">
                            <circle cx="35" cy="35" r="30" fill="none" stroke="#ddd" stroke-width="6"/>
                            <circle cx="35" cy="35" r="30" fill="none" stroke="<?php echo $seo_status === 'good' ? '#46b450' : ($seo_status === 'ok' ? '#ffb900' : '#dc3232'); ?>" stroke-width="6" 
                                    stroke-dasharray="<?php echo esc_attr(($seo_score / 100) * 188); ?> 188" stroke-linecap="round"/>
                        </svg>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <div style="font-size: 20px; font-weight: 600; line-height: 1;"><?php echo esc_html($seo_score); ?></div>
                            <div style="font-size: 10px; color: #666;">/100</div>
                        </div>
                    </div>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 3px;">SEO Score</div>
                        <div style="color: #666; font-size: 13px;">Status: <?php echo esc_html(ucfirst($seo_status)); ?></div>
                    </div>
                </div>
            </div>
            
            <div style="padding: 0;">
                <!-- Focus Keyword -->
                <div style="margin-bottom: 20px;">
                    <label for="aiseo_focus_keyword" style="display: block; font-weight: 600; margin-bottom: 8px;">
                        Focus Keyword
                    </label>
                    <div style="display: flex; gap: 8px; align-items: flex-start; flex-wrap: wrap;">
                        <input type="text" 
                               id="aiseo_focus_keyword" 
                               name="aiseo_focus_keyword" 
                               value="<?php echo esc_attr($focus_keyword); ?>" 
                               class="regular-text" 
                               placeholder="e.g., wordpress seo"
                               style="flex: 1; min-width: 300px; max-width: 400px;" />
                        <button type="button" class="button button-secondary aiseo-generate-btn" data-field="keyword">
                            <span class="dashicons dashicons-admin-generic"></span> Generate with AI
                        </button>
                    </div>
                    <p class="description" style="margin: 5px 0 0 0;">Main keyword to optimize for</p>
                </div>
                
                <!-- Meta Title -->
                <div style="margin-bottom: 20px;">
                    <label for="aiseo_meta_title" style="display: block; font-weight: 600; margin-bottom: 8px;">
                        SEO Title
                    </label>
                    <div style="display: flex; gap: 8px; align-items: flex-start; flex-wrap: wrap;">
                        <input type="text" 
                               id="aiseo_meta_title" 
                               name="aiseo_meta_title" 
                               value="<?php echo esc_attr($meta_title); ?>" 
                               class="aiseo-title-input" 
                               placeholder="Auto-generated if empty"
                               maxlength="70"
                               style="flex: 1; min-width: 300px;" />
                        <button type="button" class="button button-secondary aiseo-generate-btn" data-field="title">
                            <span class="dashicons dashicons-admin-generic"></span> Generate with AI
                        </button>
                    </div>
                    <p class="description" style="margin: 5px 0 0 0;">
                        <span class="aiseo-char-count"><span class="aiseo-current-count">0</span> / 60 characters</span>
                        <span style="color: #666;"> • Recommended: 50-60 characters</span>
                    </p>
                </div>
                
                <!-- Meta Description -->
                <div style="margin-bottom: 20px;">
                    <label for="aiseo_meta_description" style="display: block; font-weight: 600; margin-bottom: 8px;">
                        Meta Description
                    </label>
                    <textarea id="aiseo_meta_description" 
                              name="aiseo_meta_description" 
                              class="aiseo-description-input" 
                              rows="3" 
                              placeholder="Auto-generated if empty"
                              maxlength="200"
                              style="width: 100%; max-width: 600px; padding: 8px;"><?php echo esc_textarea($meta_description); ?></textarea>
                    <button type="button" class="button button-secondary aiseo-generate-btn" data-field="description" style="margin-top: 8px;">
                        <span class="dashicons dashicons-admin-generic"></span> Generate with AI
                    </button>
                    <p class="description" style="margin: 5px 0 0 0;">
                        <span class="aiseo-char-count"><span class="aiseo-current-count">0</span> / 160 characters</span>
                        <span style="color: #666;"> • Recommended: 150-160 characters</span>
                    </p>
                </div>
            </div>
            
            <!-- Advanced Settings -->
            <details class="aiseo-advanced-section" style="margin: 20px 0;">
                <summary style="cursor: pointer; padding: 10px 0; font-weight: 600; color: #2271b1; user-select: none; list-style: none;">
                    ▸ Advanced Settings
                </summary>
                <div style="margin-top: 15px;">
                    <table class="form-table" role="presentation">
                        <tbody>
                            <!-- Canonical URL -->
                            <tr>
                                <th scope="row">
                                    <label for="aiseo_canonical_url">Canonical URL</label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="aiseo_canonical_url" 
                                           name="aiseo_canonical_url" 
                                           value="<?php echo esc_attr($canonical_url); ?>" 
                                           class="large-text" 
                                           placeholder="<?php echo esc_attr(get_permalink($post->ID)); ?>" />
                                    <p class="description">Leave empty to use default permalink</p>
                                </td>
                            </tr>
                            
                            <!-- Robots Meta -->
                            <tr>
                                <th scope="row">Robots Meta</th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" 
                                                   name="aiseo_noindex" 
                                                   value="1" 
                                                   <?php checked($noindex, '1'); ?> />
                                            No Index
                                        </label>
                                        <p class="description">Prevent search engines from indexing this page</p>
                                        <br>
                                        <label>
                                            <input type="checkbox" 
                                                   name="aiseo_nofollow" 
                                                   value="1" 
                                                   <?php checked($nofollow, '1'); ?> />
                                            No Follow
                                        </label>
                                        <p class="description">Prevent search engines from following links on this page</p>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </details>
            
            <!-- Quick Actions -->
            <div style="margin: 20px 0; padding: 15px 0; display: flex; gap: 10px;">
                <button type="button" class="button button-secondary aiseo-analyze-btn">
                    <span class="dashicons dashicons-chart-bar"></span> Analyze Content
                </button>
                <button type="button" class="button button-secondary aiseo-preview-btn">
                    <span class="dashicons dashicons-visibility"></span> Preview SEO
                </button>
            </div>
            
            <!-- Analysis Results -->
            <div class="aiseo-analysis-results" style="display: none; margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #2271b1; border-radius: 3px;">
                <h3 style="margin-top: 0;">Content Analysis</h3>
                <div class="aiseo-analysis-content"></div>
            </div>
            
            <!-- Preview SEO Results -->
            <div class="aiseo-preview-results" style="display: none; margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #2271b1; border-radius: 3px;">
                <h3 style="margin-top: 0;">SEO Preview - Google Search Result</h3>
                <div class="aiseo-preview-content" style="background: white; padding: 20px; border-radius: 4px; border: 1px solid #ddd;"></div>
            </div>
        </div>
        
        <style>
            .aiseo-metabox {
                padding: 10px 0;
            }
            .aiseo-score-section {
                background: #f0f0f1;
                padding: 20px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .aiseo-score-display {
                display: flex;
                align-items: center;
                gap: 20px;
            }
            .aiseo-score-circle {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                border: 4px solid;
            }
            .aiseo-score-good {
                background: #d4edda;
                border-color: #28a745;
                color: #28a745;
            }
            .aiseo-score-ok {
                background: #fff3cd;
                border-color: #ffc107;
                color: #856404;
            }
            .aiseo-score-poor {
                background: #f8d7da;
                border-color: #dc3545;
                color: #dc3545;
            }
            .aiseo-score-number {
                font-size: 24px;
            }
            .aiseo-score-label {
                font-size: 12px;
            }
            .aiseo-field {
                margin-bottom: 20px;
            }
            .aiseo-field label {
                display: block;
                margin-bottom: 5px;
            }
            .aiseo-field .description {
                display: block;
                font-size: 12px;
                color: #646970;
                font-weight: normal;
            }
            .aiseo-input-group {
                display: flex;
                gap: 10px;
                align-items: flex-start;
            }
            .aiseo-input-group input,
            .aiseo-input-group textarea {
                flex: 1;
            }
            .aiseo-generate-btn {
                white-space: nowrap;
            }
            .aiseo-char-count {
                font-size: 12px;
                color: #646970;
                margin-top: 5px;
            }
            .aiseo-advanced-section {
                margin-top: 20px;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            .aiseo-toggle-header {
                cursor: pointer;
                user-select: none;
            }
            .aiseo-toggle-header:hover {
                color: #2271b1;
            }
            .aiseo-checkbox-group label {
                display: block;
                margin: 5px 0;
            }
            .aiseo-actions {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
            }
            .aiseo-actions button {
                margin-right: 10px;
            }
            .aiseo-analysis-results {
                margin-top: 20px;
                padding: 15px;
                background: #f0f0f1;
                border-radius: 4px;
            }
        </style>
        <?php
    }
    
    /**
     * Save metabox data
     *
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     */
    public function save_metabox($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['aiseo_metabox_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aiseo_metabox_nonce'])), 'aiseo_metabox_nonce')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save focus keyword
        if (isset($_POST['aiseo_focus_keyword'])) {
            update_post_meta($post_id, '_aiseo_focus_keyword', sanitize_text_field(wp_unslash($_POST['aiseo_focus_keyword'])));
        }
        
        // Save meta title
        if (isset($_POST['aiseo_meta_title'])) {
            update_post_meta($post_id, '_aiseo_meta_title', sanitize_text_field(wp_unslash($_POST['aiseo_meta_title'])));
        }
        
        // Save meta description
        if (isset($_POST['aiseo_meta_description'])) {
            update_post_meta($post_id, '_aiseo_meta_description', sanitize_textarea_field(wp_unslash($_POST['aiseo_meta_description'])));
        }
        
        // Save canonical URL
        if (isset($_POST['aiseo_canonical_url'])) {
            update_post_meta($post_id, '_aiseo_canonical_url', esc_url_raw(wp_unslash($_POST['aiseo_canonical_url'])));
        }
        
        // Save robots meta
        update_post_meta($post_id, '_aiseo_noindex', isset($_POST['aiseo_noindex']) ? '1' : '0');
        update_post_meta($post_id, '_aiseo_nofollow', isset($_POST['aiseo_nofollow']) ? '1' : '0');
    }
    
    /**
     * Enqueue metabox scripts
     */
    public function enqueue_metabox_scripts($hook) {
        // Only load on post editor
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        // Enqueue WordPress media library
        wp_enqueue_media();
        
        // Enqueue metabox JavaScript (external file instead of inline)
        wp_enqueue_script(
            'aiseo-metabox',
            AISEO_PLUGIN_URL . 'js/aiseo-metabox.js',
            array('jquery'),
            AISEO_VERSION,
            true
        );
    }
    
    /**
     * AJAX handler for generating keyword
     */
    public function ajax_generate_keyword() {
        check_ajax_referer('aiseo_metabox_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        $post = get_post($post_id);
        
        if (!$post || empty($post->post_content)) {
            wp_send_json_error('Post content is required to generate keywords');
        }
        
        // Use AI to extract main keyword from content
        $api = new AISEO_API();
        
        $prompt = "Analyze this content and suggest the single most important SEO keyword or key phrase (2-4 words maximum) that best represents the main topic. Return only the keyword phrase, nothing else.\n\nContent:\n" . wp_strip_all_tags($post->post_content);
        
        $keyword = $api->make_request($prompt, array(
            'max_tokens' => 20,
            'temperature' => 0.3,
        ));
        
        if (is_wp_error($keyword)) {
            wp_send_json_error($keyword->get_error_message());
        }
        
        // Clean up the keyword
        $keyword = trim($keyword);
        $keyword = strtolower($keyword);
        
        wp_send_json_success($keyword);
    }
    
    /**
     * AJAX handler for generating title
     */
    public function ajax_generate_title() {
        check_ajax_referer('aiseo_metabox_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        $post = get_post($post_id);
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        $api = new AISEO_API();
        $title = $api->generate_title($post->post_content, $keyword);
        
        if (is_wp_error($title)) {
            wp_send_json_error($title->get_error_message());
        }
        
        wp_send_json_success($title);
    }
    
    /**
     * AJAX handler for generating description
     */
    public function ajax_generate_description() {
        check_ajax_referer('aiseo_metabox_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        $post = get_post($post_id);
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        $api = new AISEO_API();
        $description = $api->generate_meta_description($post->post_content, $keyword);
        
        if (is_wp_error($description)) {
            wp_send_json_error($description->get_error_message());
        }
        
        wp_send_json_success($description);
    }
    
    /**
     * AJAX handler for analyzing content
     */
    public function ajax_analyze_content() {
        check_ajax_referer('aiseo_metabox_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        $analysis = new AISEO_Analysis();
        $results = $analysis->analyze_post($post_id, $keyword);
        
        if (is_wp_error($results)) {
            wp_send_json_error($results->get_error_message());
        }
        
        wp_send_json_success($results);
    }
}
