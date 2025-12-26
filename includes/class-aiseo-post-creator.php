<?php
/**
 * AISEO AI-Powered Post Creator
 *
 * Creates complete WordPress posts with AI-generated content and SEO metadata
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Post_Creator {
    
    /**
     * AISEO API instance
     */
    private $api;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api = new AISEO_API();
    }
    
    /**
     * Create a new post with AI-generated content
     *
     * @param array $args Post creation arguments
     * @return array|WP_Error Post data or error
     */
    public function create_post($args) {
        // Validate required arguments
        if (empty($args['topic']) && empty($args['title'])) {
            return new WP_Error('missing_topic', __('Topic or title is required', 'aiseo'));
        }
        
        // Parse arguments with defaults
        $defaults = array(
            'topic' => '',
            'title' => '',
            'keyword' => '',
            'post_type' => 'post',
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
            'content_length' => 'medium', // short, medium, long
            'generate_image' => false,
            'generate_seo' => true,
            'category' => '',
            'tags' => array(),
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Use structured output to generate both title and content together
        $structured_result = $this->generate_post_with_structured_output(
            $args['topic'],
            $args['keyword'],
            $args['content_length']
        );
        
        if (is_wp_error($structured_result)) {
            return $structured_result;
        }
        
        // Use structured title and content
        $args['title'] = $structured_result['title'];
        $content_result = array(
            'content' => $structured_result['content'],
            'keyword' => !empty($args['keyword']) ? $args['keyword'] : $structured_result['keyword'],
            'word_count' => str_word_count(wp_strip_all_tags($structured_result['content'])),
        );
        
        // Create the post
        $post_data = array(
            'post_title' => sanitize_text_field($args['title']),
            'post_content' => wp_kses_post($content_result['content']),
            'post_status' => sanitize_text_field($args['post_status']),
            'post_type' => sanitize_text_field($args['post_type']),
            'post_author' => absint($args['post_author']),
        );
        
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Store focus keyword
        if (!empty($args['keyword'])) {
            update_post_meta($post_id, '_aiseo_focus_keyword', sanitize_text_field($args['keyword']));
        } elseif (!empty($content_result['keyword'])) {
            update_post_meta($post_id, '_aiseo_focus_keyword', sanitize_text_field($content_result['keyword']));
        }
        
        // Add categories
        if (!empty($args['category'])) {
            $category_ids = $this->process_categories($args['category'], $args['post_type']);
            if (!empty($category_ids)) {
                wp_set_post_terms($post_id, $category_ids, $args['post_type'] === 'post' ? 'category' : 'category');
            }
        }
        
        // Add tags
        if (!empty($args['tags'])) {
            $tags = is_array($args['tags']) ? $args['tags'] : explode(',', $args['tags']);
            wp_set_post_terms($post_id, array_map('trim', $tags), 'post_tag');
        }
        
        // Generate SEO metadata
        if ($args['generate_seo']) {
            $seo_result = $this->generate_seo_metadata($post_id, $content_result['content'], $args['keyword']);
            if (is_wp_error($seo_result)) {
                AISEO_Helpers::log('WARNING', 'post_creator', 'SEO generation failed', array(
                    'post_id' => $post_id,
                    'error' => $seo_result->get_error_message(),
                ));
            }
        }
        
        // Mark as AI-generated
        update_post_meta($post_id, '_aiseo_ai_generated_post', true);
        update_post_meta($post_id, '_aiseo_generation_timestamp', current_time('mysql'));
        
        AISEO_Helpers::log('INFO', 'post_creator', 'Post created successfully', array(
            'post_id' => $post_id,
            'title' => $args['title'],
        ));
        
        return array(
            'post_id' => $post_id,
            'title' => $args['title'],
            'content' => $content_result['content'],
            'keyword' => $args['keyword'] ?: $content_result['keyword'],
            'url' => get_permalink($post_id),
            'edit_url' => get_edit_post_link($post_id, 'raw'),
        );
    }
    
    /**
     * Generate post title using AI
     *
     * @param string $topic Post topic
     * @param string $keyword Focus keyword
     * @return string|WP_Error Generated title or error
     */
    private function generate_post_title($topic, $keyword = '') {
        $prompt = "Generate a compelling, SEO-optimized blog post title";
        
        if (!empty($keyword)) {
            $prompt .= " that includes the keyword '{$keyword}'";
        }
        
        $prompt .= " for the following topic:\n\n{$topic}";
        $prompt .= "\n\nProvide ONLY the title text (50-60 characters), nothing else. Make it engaging and click-worthy.";
        
        $result = $this->api->make_request($prompt, array(
            'max_tokens' => 50,
            'temperature' => 0.8,
        ));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $title = trim($result, " \t\n\r\0\x0B\"'");
        
        // Ensure reasonable length
        if (strlen($title) > 100) {
            $title = substr($title, 0, 97) . '...';
        }
        
        return $title;
    }
    
    /**
     * Generate post content using AI
     *
     * @param string $title Post title
     * @param string $topic Post topic
     * @param string $keyword Focus keyword
     * @param string $length Content length (short, medium, long)
     * @return array|WP_Error Content data or error
     */
    private function generate_post_content($title, $topic, $keyword = '', $length = 'medium') {
        // Determine word count based on length
        $word_counts = array(
            'short' => 500,
            'medium' => 1000,
            'long' => 2000,
        );
        
        $target_words = isset($word_counts[$length]) ? $word_counts[$length] : $word_counts['medium'];
        
        $prompt = "Write a comprehensive, SEO-optimized blog post about: \"{$title}\"\n\n";
        
        if (!empty($topic)) {
            $prompt .= "Topic: {$topic}\n\n";
        }
        
        if (!empty($keyword)) {
            $prompt .= "Focus keyword: {$keyword}\n";
            $prompt .= "Include the keyword naturally throughout the content.\n\n";
        }
        
        $prompt .= "Requirements:\n";
        $prompt .= "- Target length: approximately {$target_words} words\n";
        $prompt .= "- Use proper HTML formatting with <h2>, <h3>, <p>, <ul>, <li> tags\n";
        $prompt .= "- Include an engaging introduction\n";
        $prompt .= "- Use subheadings to organize content\n";
        $prompt .= "- Include bullet points or lists where appropriate\n";
        $prompt .= "- Write in a clear, engaging style\n";
        $prompt .= "- Include a conclusion with a call-to-action\n";
        $prompt .= "- Make it SEO-friendly and valuable to readers\n\n";
        $prompt .= "IMPORTANT: \n";
        $prompt .= "- Do NOT include the title as an <h1> or heading in the content (WordPress will add it automatically)\n";
        $prompt .= "- Start directly with the introduction paragraph\n";
        $prompt .= "- Use <h2> for main sections, <h3> for subsections\n";
        $prompt .= "- Provide ONLY the HTML content body\n";
        $prompt .= "- Do NOT wrap it in markdown code blocks (```html)\n";
        $prompt .= "- Do NOT add any commentary or explanations\n";
        $prompt .= "- Just the raw HTML content starting with the first paragraph";
        
        // Adjust max_tokens based on length
        $max_tokens = array(
            'short' => 1000,
            'medium' => 2000,
            'long' => 4000,
        );
        
        $result = $this->api->make_request($prompt, array(
            'max_tokens' => isset($max_tokens[$length]) ? $max_tokens[$length] : $max_tokens['medium'],
            'temperature' => 0.7,
        ));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Clean up the content
        $content = $this->cleanup_generated_content($result);
        
        // Extract keyword if not provided
        $extracted_keyword = $keyword;
        if (empty($extracted_keyword)) {
            $extracted_keyword = $this->extract_keyword_from_content($title, $content);
        }
        
        return array(
            'content' => $content,
            'keyword' => $extracted_keyword,
            'word_count' => str_word_count(wp_strip_all_tags($content)),
        );
    }
    
    /**
     * Generate post with structured output (title + content)
     *
     * @param string $topic Topic or subject
     * @param string $keyword Focus keyword
     * @param string $length Content length
     * @return array|WP_Error Structured data with title and content, or error
     */
    private function generate_post_with_structured_output($topic, $keyword = '', $length = 'medium') {
        // Determine word count based on length
        $word_counts = array(
            'tiny' => 100,
            'brief' => 200,
            'short' => 500,
            'medium' => 1000,
            'long' => 2000,
        );
        
        $target_words = isset($word_counts[$length]) ? $word_counts[$length] : $word_counts['medium'];
        
        // Build prompt
        $prompt = "Create a comprehensive, SEO-optimized blog post about: \"{$topic}\"\n\n";
        
        if (!empty($keyword)) {
            $prompt .= "Focus keyword: {$keyword}\n";
            $prompt .= "Include the keyword naturally throughout the content.\n\n";
        }
        
        $prompt .= "Requirements:\n";
        $prompt .= "- Target length: approximately {$target_words} words\n";
        $prompt .= "- Use proper HTML formatting with <h2>, <h3>, <p>, <ul>, <li> tags\n";
        $prompt .= "- Include an engaging introduction\n";
        $prompt .= "- Use subheadings to organize content\n";
        $prompt .= "- Include bullet points or lists where appropriate\n";
        $prompt .= "- Write in a clear, engaging style\n";
        $prompt .= "- Include a conclusion with a call-to-action\n";
        $prompt .= "- Make it SEO-friendly and valuable to readers\n\n";
        $prompt .= "IMPORTANT: \n";
        $prompt .= "- Do NOT include the title as an <h1> or heading in the content (WordPress will add it automatically)\n";
        $prompt .= "- Start the content directly with the introduction paragraph\n";
        $prompt .= "- Use <h2> for main sections, <h3> for subsections\n";
        $prompt .= "- Provide ONLY the HTML content body in the 'content' field\n";
        $prompt .= "- Do NOT wrap content in markdown code blocks\n";
        $prompt .= "- Extract a focus keyword if not provided";
        
        // Define JSON schema for structured output
        $schema = array(
            'name' => 'blog_post',
            'schema' => array(
                'type' => 'object',
                'properties' => array(
                    'title' => array(
                        'type' => 'string',
                        'description' => 'SEO-optimized post title (50-60 characters)',
                    ),
                    'content' => array(
                        'type' => 'string',
                        'description' => 'HTML formatted post content without the title',
                    ),
                    'keyword' => array(
                        'type' => 'string',
                        'description' => 'Primary focus keyword for SEO',
                    ),
                ),
                'required' => array('title', 'content', 'keyword'),
                'additionalProperties' => false,
            ),
        );
        
        // Adjust max_tokens based on length
        $max_tokens = array(
            'tiny' => 500,
            'brief' => 800,
            'short' => 1000,
            'medium' => 2000,
            'long' => 4000,
        );
        
        // Make structured request with retries
        $result = $this->api->make_structured_request($prompt, $schema, array(
            'max_tokens' => isset($max_tokens[$length]) ? $max_tokens[$length] : $max_tokens['medium'],
            'temperature' => 0.7,
            'max_retries' => 3,
        ));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Clean up the content
        $result['content'] = $this->cleanup_generated_content($result['content']);
        
        // Use provided keyword if available
        if (!empty($keyword)) {
            $result['keyword'] = $keyword;
        }
        
        return $result;
    }
    
    /**
     * Generate SEO metadata for post
     *
     * @param int $post_id Post ID
     * @param string $content Post content
     * @param string $keyword Focus keyword
     * @return bool|WP_Error True on success, error on failure
     */
    private function generate_seo_metadata($post_id, $content, $keyword = '') {
        // Generate meta title
        $meta_title = $this->api->generate_title($content, $keyword);
        if (!is_wp_error($meta_title) && !empty($meta_title)) {
            update_post_meta($post_id, '_aiseo_meta_title', $meta_title);
            update_post_meta($post_id, '_aiseo_ai_generated_title', true);
        }
        
        // Generate meta description
        $meta_description = $this->api->generate_meta_description($content, $keyword);
        if (!is_wp_error($meta_description) && !empty($meta_description)) {
            update_post_meta($post_id, '_aiseo_meta_description', $meta_description);
            update_post_meta($post_id, '_aiseo_ai_generated_desc', true);
        }
        
        // Generate social media tags
        if (!is_wp_error($meta_title) && !is_wp_error($meta_description)) {
            update_post_meta($post_id, '_aiseo_og_title', $meta_title);
            update_post_meta($post_id, '_aiseo_og_description', $meta_description);
            update_post_meta($post_id, '_aiseo_twitter_title', $meta_title);
            update_post_meta($post_id, '_aiseo_twitter_description', $meta_description);
        }
        
        // Set schema type
        $post = get_post($post_id);
        $schema_type = $post->post_type === 'post' ? 'Article' : 'WebPage';
        update_post_meta($post_id, '_aiseo_schema_type', $schema_type);
        
        update_post_meta($post_id, '_aiseo_generation_timestamp', current_time('mysql'));
        
        return true;
    }
    
    /**
     * Extract keyword from content
     *
     * @param string $title Post title
     * @param string $content Post content
     * @return string Extracted keyword
     */
    private function extract_keyword_from_content($title, $content) {
        // Simple extraction from title
        $stop_words = array('the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with');
        $words = explode(' ', strtolower($title));
        $words = array_diff($words, $stop_words);
        
        return implode(' ', array_slice($words, 0, 3));
    }
    
    /**
     * Process categories
     *
     * @param string|array $categories Category names or IDs
     * @param string $post_type Post type
     * @return array Category IDs
     */
    private function process_categories($categories, $post_type) {
        $category_ids = array();
        
        if (!is_array($categories)) {
            $categories = explode(',', $categories);
        }
        
        $taxonomy = $post_type === 'post' ? 'category' : 'category';
        
        foreach ($categories as $category) {
            $category = trim($category);
            
            // Check if it's an ID
            if (is_numeric($category)) {
                $term = get_term($category, $taxonomy);
                if ($term && !is_wp_error($term)) {
                    $category_ids[] = (int) $category;
                }
            } else {
                // Try to find by name, create if doesn't exist
                $term = get_term_by('name', $category, $taxonomy);
                if (!$term) {
                    $term = wp_insert_term($category, $taxonomy);
                    if (!is_wp_error($term)) {
                        $category_ids[] = $term['term_id'];
                    }
                } else {
                    $category_ids[] = $term->term_id;
                }
            }
        }
        
        return $category_ids;
    }
    
    /**
     * Bulk create posts
     *
     * @param array $posts_data Array of post data
     * @return array Results with success/error counts
     */
    public function bulk_create_posts($posts_data) {
        $results = array(
            'total' => count($posts_data),
            'success' => 0,
            'failed' => 0,
            'posts' => array(),
            'errors' => array(),
        );
        
        foreach ($posts_data as $index => $post_args) {
            $result = $this->create_post($post_args);
            
            if (is_wp_error($result)) {
                $results['failed']++;
                $results['errors'][] = array(
                    'index' => $index,
                    'topic' => $post_args['topic'] ?? $post_args['title'] ?? 'Unknown',
                    'error' => $result->get_error_message(),
                );
            } else {
                $results['success']++;
                $results['posts'][] = $result;
            }
            
            // Small delay to avoid rate limiting
            if ($index < count($posts_data) - 1) {
                sleep(2);
            }
        }
        
        return $results;
    }
    
    /**
     * Get post creation statistics
     *
     * @return array Statistics
     */
    public function get_statistics() {
        global $wpdb;
        
        $stats = array(
            'total_ai_posts' => 0,
            'posts_by_status' => array(),
            'posts_by_type' => array(),
            'recent_posts' => array(),
        );
        
        // Get total AI-generated posts
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Statistics query
        $stats['total_ai_posts'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_aiseo_ai_generated_post' AND meta_value = '1'"
        );
        
        // Get posts by status
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Statistics query
        $status_query = $wpdb->get_results(
            "SELECT p.post_status, COUNT(*) as count 
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE pm.meta_key = '_aiseo_ai_generated_post' AND pm.meta_value = '1'
             GROUP BY p.post_status"
        );
        
        foreach ($status_query as $row) {
            $stats['posts_by_status'][$row->post_status] = (int) $row->count;
        }
        
        // Get posts by type
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Statistics query
        $type_query = $wpdb->get_results(
            "SELECT p.post_type, COUNT(*) as count 
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE pm.meta_key = '_aiseo_ai_generated_post' AND pm.meta_value = '1'
             GROUP BY p.post_type"
        );
        
        foreach ($type_query as $row) {
            $stats['posts_by_type'][$row->post_type] = (int) $row->count;
        }
        
        // Get recent posts
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Statistics query
        $recent_posts = $wpdb->get_results(
            "SELECT p.ID, p.post_title, p.post_status, p.post_type, pm.meta_value as created_at
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE pm.meta_key = '_aiseo_generation_timestamp'
             ORDER BY pm.meta_value DESC
             LIMIT 10"
        );
        
        foreach ($recent_posts as $post) {
            $stats['recent_posts'][] = array(
                'id' => (int) $post->ID,
                'title' => $post->post_title,
                'status' => $post->post_status,
                'type' => $post->post_type,
                'created_at' => $post->created_at,
                'url' => get_permalink($post->ID),
            );
        }
        
        return $stats;
    }
    
    /**
     * Clean up AI-generated content
     * Removes markdown code blocks, extra whitespace, and formatting artifacts
     *
     * @param string $content Raw content from AI
     * @return string Cleaned content
     */
    private function cleanup_generated_content($content) {
        // Remove markdown code blocks (```html, ```php, etc.)
        $content = preg_replace('/^```[a-z]*\s*/m', '', $content);
        $content = preg_replace('/\s*```$/m', '', $content);
        
        // Remove any remaining ``` markers
        $content = str_replace('```', '', $content);
        
        // Trim whitespace from start and end
        $content = trim($content);
        
        // Remove excessive blank lines (more than 2 consecutive newlines)
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        // Remove leading/trailing whitespace from each line while preserving HTML structure
        $lines = explode("\n", $content);
        $lines = array_map('trim', $lines);
        $content = implode("\n", $lines);
        
        // Remove empty lines at the beginning
        $content = ltrim($content, "\n");
        
        return $content;
    }
}
