<?php
/**
 * AISEO Image SEO Class
 * 
 * Handles AI-powered image optimization including alt text generation,
 * filename suggestions, and image SEO scoring.
 *
 * @package AISEO
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Image_SEO {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // AJAX handlers
        add_action('wp_ajax_aiseo_generate_single_alt', [$this, 'ajax_generate_single_alt']);
        add_action('wp_ajax_aiseo_bulk_generate_alt', [$this, 'ajax_bulk_generate_alt']);
        add_action('wp_ajax_aiseo_get_missing_alt', [$this, 'ajax_get_missing_alt']);
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'aiseo-settings',
            __('Image SEO', 'aiseo'),
            __('Image SEO', 'aiseo'),
            'upload_files',
            'aiseo-image-seo',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'aiseo_page_aiseo-image-seo') {
            return;
        }
        
        wp_enqueue_style('aiseo-image-seo', AISEO_PLUGIN_URL . 'css/aiseo-image-seo.css', [], AISEO_VERSION);
        wp_enqueue_script('aiseo-image-seo', AISEO_PLUGIN_URL . 'js/aiseo-image-seo.js', ['jquery'], AISEO_VERSION, true);
        
        wp_localize_script('aiseo-image-seo', 'aiseoImageSeo', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aiseo_image_seo'),
            'strings' => [
                'generating' => __('Generating alt text...', 'aiseo'),
                'success' => __('Alt text generated successfully', 'aiseo'),
                'error' => __('Failed to generate alt text', 'aiseo')
            ]
        ]);
    }
    
    /**
     * Generate AI-powered alt text for an image
     *
     * @param int $image_id Attachment ID
     * @param array $options Generation options
     * @return string|WP_Error Generated alt text or error
     */
    public function generate_alt_text($image_id, $options = []) {
        // Validate image ID
        if (!wp_attachment_is_image($image_id)) {
            return new WP_Error('invalid_image', __('Invalid image ID', 'aiseo'));
        }
        
        // Get image data
        $post_id = wp_get_post_parent_id($image_id);
        
        // Get context from parent post
        $context = $this->build_context($image_id, $post_id);
        
        // Check if alt text already exists
        $existing_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        if (!empty($existing_alt) && empty($options['overwrite'])) {
            return new WP_Error('alt_exists', __('Alt text already exists', 'aiseo'));
        }
        
        // Prepare context for AI with image URL
        $image_url = wp_get_attachment_url($image_id);
        $image_post = get_post($image_id);
        
        $image_context = [
            'image_url' => $image_url,
            'filename' => basename($image_url),
            'title' => $context['image_title'],
            'caption' => !empty($image_post->post_excerpt) ? $image_post->post_excerpt : '',
            'description' => !empty($image_post->post_content) ? $image_post->post_content : '',
            'parent_title' => $context['post_title'],
            'parent_content' => $context['surrounding_text']
        ];
        
        // Call OpenAI API
        $api = new AISEO_API();
        $response = $api->generate_alt_text($image_context);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Clean and validate alt text
        $alt_text = $this->sanitize_alt_text($response);
        
        // Save alt text
        update_post_meta($image_id, '_wp_attachment_image_alt', $alt_text);
        update_post_meta($image_id, '_aiseo_ai_generated_alt', true);
        update_post_meta($image_id, '_aiseo_alt_generated_at', current_time('mysql'));
        
        return $alt_text;
    }
    
    /**
     * Build context for AI alt text generation
     */
    private function build_context($image_id, $post_id) {
        $context = [
            'image_title' => get_the_title($image_id),
            'focus_keyword' => '',
            'post_title' => '',
            'surrounding_text' => ''
        ];
        
        if ($post_id > 0) {
            $context['focus_keyword'] = get_post_meta($post_id, '_aiseo_focus_keyword', true);
            $context['post_title'] = get_the_title($post_id);
            
            $post_content = get_post_field('post_content', $post_id);
            $context['surrounding_text'] = $this->extract_surrounding_text(
                wp_get_attachment_url($image_id),
                $post_content
            );
        }
        
        return $context;
    }
    
    /**
     * Extract text surrounding an image in content
     */
    private function extract_surrounding_text($image_url, $content) {
        $image_filename = basename($image_url);
        $position = strpos($content, $image_filename);
        
        if ($position === false) {
            return substr(wp_strip_all_tags($content), 0, 200);
        }
        
        $before = substr($content, max(0, $position - 200), 200);
        $after = substr($content, $position, 200);
        
        return wp_strip_all_tags($before . ' ' . $after);
    }
    
    /**
     * Build AI prompt for alt text generation
     */
    private function build_alt_text_prompt($context) {
        $prompt = "Generate SEO-optimized alt text for an image.\n\n";
        
        if (!empty($context['post_title'])) {
            $prompt .= "Post title: {$context['post_title']}\n";
        }
        
        if (!empty($context['focus_keyword'])) {
            $prompt .= "Focus keyword: {$context['focus_keyword']}\n";
        }
        
        if (!empty($context['surrounding_text'])) {
            $prompt .= "Context: {$context['surrounding_text']}\n";
        }
        
        $prompt .= "\nRequirements:\n";
        $prompt .= "- Maximum 125 characters\n";
        $prompt .= "- Include focus keyword naturally if provided\n";
        $prompt .= "- Be descriptive and specific\n";
        $prompt .= "- Avoid 'image of' or 'picture of'\n\n";
        $prompt .= "Alt text:";
        
        return $prompt;
    }
    
    /**
     * Sanitize and validate alt text
     */
    private function sanitize_alt_text($alt_text) {
        $alt_text = trim($alt_text, " \t\n\r\0\x0B\"'");
        $alt_text = sanitize_text_field($alt_text);
        
        if (strlen($alt_text) > 125) {
            $alt_text = substr($alt_text, 0, 122) . '...';
        }
        
        return $alt_text;
    }
    
    /**
     * Detect images missing alt text
     */
    public function detect_missing_alt_text($args = []) {
        global $wpdb;
        
        $defaults = [
            'posts_per_page' => 100,
            'offset' => 0
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $query = "
            SELECT p.ID, p.post_title, p.post_parent, p.guid, p.post_date
            FROM {$wpdb->posts} p
            WHERE p.post_type = 'attachment'
            AND p.post_mime_type LIKE 'image/%'
            AND p.post_status = 'inherit'
            AND p.ID NOT IN (
                SELECT post_id 
                FROM {$wpdb->postmeta} 
                WHERE meta_key = '_wp_attachment_image_alt' 
                AND meta_value != ''
            )
            ORDER BY p.post_date DESC
            LIMIT %d OFFSET %d
        ";
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Necessary for image metadata query
        $images = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Query is prepared with $wpdb->prepare()
            $wpdb->prepare($query, $args['posts_per_page'], $args['offset'])
        );
        
        foreach ($images as &$image) {
            $image->thumbnail = wp_get_attachment_image_url($image->ID, 'thumbnail');
            $image->filesize = size_format(filesize(get_attached_file($image->ID)));
            
            if ($image->post_parent > 0) {
                $image->parent_title = get_the_title($image->post_parent);
                $image->parent_url = get_edit_post_link($image->post_parent);
            }
        }
        
        return $images;
    }
    
    /**
     * Calculate image SEO score
     */
    public function analyze_image_seo($image_id) {
        $score = 0;
        $max_score = 100;
        $checks = [];
        
        // Check 1: Alt text present (30 points)
        $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        if (!empty($alt_text)) {
            $score += 30;
            $checks['alt_text_present'] = [
                'score' => 30,
                'status' => 'good',
                'message' => __('Alt text is present', 'aiseo')
            ];
        } else {
            $checks['alt_text_present'] = [
                'score' => 0,
                'status' => 'poor',
                'message' => __('Missing alt text', 'aiseo')
            ];
        }
        
        // Check 2: Alt text length (10 points)
        $alt_length = strlen($alt_text);
        if ($alt_length >= 50 && $alt_length <= 125) {
            $score += 10;
            $checks['alt_text_length'] = [
                'score' => 10,
                'status' => 'good',
                /* translators: %d: number of characters in alt text */
                'message' => sprintf(__('Alt text length is optimal (%d chars)', 'aiseo'), $alt_length)
            ];
        } else if ($alt_length > 0) {
            $score += 5;
            $checks['alt_text_length'] = [
                'score' => 5,
                'status' => 'ok',
                /* translators: %d: number of characters in alt text */
                'message' => sprintf(__('Alt text length could be improved (%d chars)', 'aiseo'), $alt_length)
            ];
        }
        
        // Check 3: Keyword in alt text (20 points)
        $post_id = wp_get_post_parent_id($image_id);
        $focus_keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        if (!empty($focus_keyword) && !empty($alt_text) && stripos($alt_text, $focus_keyword) !== false) {
            $score += 20;
            $checks['keyword_in_alt'] = [
                'score' => 20,
                'status' => 'good',
                /* translators: %s: focus keyword */
                'message' => sprintf(__('Focus keyword "%s" found in alt text', 'aiseo'), $focus_keyword)
            ];
        }
        
        // Check 4: Image title (10 points)
        $image_title = get_the_title($image_id);
        if (!empty($image_title) && $image_title !== 'Auto Draft') {
            $score += 10;
            $checks['image_title'] = [
                'score' => 10,
                'status' => 'good',
                'message' => __('Image has descriptive title', 'aiseo')
            ];
        }
        
        // Check 5: Filename (10 points)
        $filename = basename(get_attached_file($image_id));
        $filename_clean = preg_replace('/\.[^.]+$/', '', $filename);
        
        if (preg_match('/^[a-z0-9-]+$/', $filename_clean)) {
            $score += 10;
            $checks['filename'] = [
                'score' => 10,
                'status' => 'good',
                'message' => __('Filename is SEO-friendly', 'aiseo')
            ];
        }
        
        // Check 6: File size (20 points)
        $filesize = filesize(get_attached_file($image_id)) / 1024;
        
        if ($filesize < 100) {
            $score += 20;
            $checks['file_size'] = [
                'score' => 20,
                'status' => 'good',
                /* translators: %.1f: file size in kilobytes */
                'message' => sprintf(__('Image size is optimized (%.1f KB)', 'aiseo'), $filesize)
            ];
        } else if ($filesize < 200) {
            $score += 10;
            $checks['file_size'] = [
                'score' => 10,
                'status' => 'ok',
                /* translators: %.1f: file size in kilobytes */
                'message' => sprintf(__('Image size is acceptable (%.1f KB)', 'aiseo'), $filesize)
            ];
        }
        
        return [
            'score' => $score,
            'max_score' => $max_score,
            'percentage' => round(($score / $max_score) * 100),
            'status' => $score >= 80 ? 'good' : ($score >= 50 ? 'ok' : 'poor'),
            'checks' => $checks
        ];
    }
    
    /**
     * AJAX: Generate alt text for single image
     */
    public function ajax_generate_single_alt() {
        try {
            check_ajax_referer('aiseo_image_seo', 'nonce');
            
            if (!current_user_can('upload_files')) {
                wp_send_json_error(['message' => __('Permission denied', 'aiseo')]);
            }
            
            if (!isset($_POST['image_id'])) {
                wp_send_json_error(['message' => __('Image ID is required', 'aiseo')]);
            }
            
            $image_id = intval($_POST['image_id']);
            $overwrite = !empty($_POST['overwrite']);
            
            $alt_text = $this->generate_alt_text($image_id, ['overwrite' => $overwrite]);
            
            if (is_wp_error($alt_text)) {
                wp_send_json_error(['message' => $alt_text->get_error_message()]);
            }
            
            wp_send_json_success([
                'alt_text' => $alt_text,
                'message' => __('Alt text generated successfully', 'aiseo')
            ]);
        } catch (Exception $e) {
            // Error logged via AISEO_Helpers::log()
            wp_send_json_error(['message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * AJAX: Get images missing alt text
     */
    public function ajax_get_missing_alt() {
        check_ajax_referer('aiseo_image_seo', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error(['message' => __('Permission denied', 'aiseo')]);
        }
        
        $images = $this->detect_missing_alt_text();
        
        wp_send_json_success(['images' => $images]);
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        try {
            $images = $this->detect_missing_alt_text(['posts_per_page' => 20]);
            $total_images = wp_count_posts('attachment')->inherit;
        } catch (Exception $e) {
            // If there's an error, set defaults to prevent blank page
            // Error logged via AISEO_Helpers::log()
            $images = [];
            $total_images = 0;
        }
        
        include AISEO_PLUGIN_DIR . 'admin/views/image-seo-page.php';
    }
}
