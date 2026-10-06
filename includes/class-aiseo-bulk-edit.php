<?php
/**
 * AISEO Bulk Editing Class
 * 
 * Bulk edit SEO metadata for multiple posts at once
 *
 * @package AISEO
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Bulk_Edit {
    
    /**
     * Get posts for bulk editing
     *
     * @param array $args Query arguments
     * @return array Posts with metadata
     */
    public function get_posts_for_editing($args = []) {
        $defaults = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $posts = get_posts($args);
        $results = [];
        
        foreach ($posts as $post) {
            if (!current_user_can('edit_post', $post->ID)) {
                continue;
            }

            $results[] = [
                'ID' => $post->ID,
                'title' => $post->post_title,
                'post_type' => $post->post_type,
                'status' => $post->post_status,
                'date' => $post->post_date,
                'meta_title' => get_post_meta($post->ID, '_aiseo_meta_title', true),
                'meta_description' => get_post_meta($post->ID, '_aiseo_meta_description', true),
                'focus_keyword' => get_post_meta($post->ID, '_aiseo_focus_keyword', true),
                'canonical_url' => get_post_meta($post->ID, '_aiseo_canonical_url', true),
                'robots_index' => get_post_meta($post->ID, '_aiseo_robots_index', true) ?: 'index',
                'robots_follow' => get_post_meta($post->ID, '_aiseo_robots_follow', true) ?: 'follow',
            ];
        }
        
        return $results;
    }
    
    /**
     * Bulk update metadata for multiple posts
     *
     * @param array $updates Array of post IDs and their metadata updates
     * @return array Results with success/error for each post
     */
    public function bulk_update($updates) {
        if (empty($updates) || !is_array($updates)) {
            return new WP_Error('invalid_data', __('Invalid update data provided', 'aiseo'));
        }
        
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'updated' => []
        ];
        
        foreach ($updates as $update) {
            if (!isset($update['post_id'])) {
                $results['failed']++;
                $results['errors'][] = 'Missing post_id in update data';
                continue;
            }
            
            $post_id = absint($update['post_id']);
            $post = get_post($post_id);
            
            if (!$post) {
                $results['failed']++;
                $results['errors'][] = sprintf(
                    /* translators: %d: post ID */
                    __('Post %d not found', 'aiseo'),
                    $post_id
                );
                continue;
            }

            if (!current_user_can('edit_post', $post_id)) {
                $results['failed']++;
                $results['errors'][] = sprintf(
                    /* translators: %d: post ID */
                    __('You are not allowed to edit post %d.', 'aiseo'),
                    $post_id
                );
                continue;
            }
            
            // Update each metadata field if provided
            $updated_fields = [];
            
            if (isset($update['meta_title'])) {
                update_post_meta($post_id, '_aiseo_meta_title', sanitize_text_field($update['meta_title']));
                $updated_fields[] = 'meta_title';
            }
            
            if (isset($update['meta_description'])) {
                update_post_meta($post_id, '_aiseo_meta_description', sanitize_textarea_field($update['meta_description']));
                $updated_fields[] = 'meta_description';
            }
            
            if (isset($update['focus_keyword'])) {
                update_post_meta($post_id, '_aiseo_focus_keyword', sanitize_text_field($update['focus_keyword']));
                $updated_fields[] = 'focus_keyword';
            }
            
            if (isset($update['canonical_url'])) {
                update_post_meta($post_id, '_aiseo_canonical_url', esc_url_raw($update['canonical_url']));
                $updated_fields[] = 'canonical_url';
            }
            
            if (isset($update['robots_index'])) {
                $robots_index = in_array($update['robots_index'], ['index', 'noindex']) ? $update['robots_index'] : 'index';
                update_post_meta($post_id, '_aiseo_robots_index', $robots_index);
                $updated_fields[] = 'robots_index';
            }
            
            if (isset($update['robots_follow'])) {
                $robots_follow = in_array($update['robots_follow'], ['follow', 'nofollow']) ? $update['robots_follow'] : 'follow';
                update_post_meta($post_id, '_aiseo_robots_follow', $robots_follow);
                $updated_fields[] = 'robots_follow';
            }
            
            if (!empty($updated_fields)) {
                $results['success']++;
                $results['updated'][] = [
                    'post_id' => $post_id,
                    'fields' => $updated_fields
                ];
            } else {
                $results['failed']++;
                $results['errors'][] = sprintf('No valid fields to update for post %d', $post_id);
            }
        }
        
        return $results;
    }
    
    /**
     * Bulk generate metadata using AI
     *
     * @param array $post_ids Array of post IDs
     * @param array $options Generation options (meta_types, overwrite)
     * @return array Results
     */
    public function bulk_generate($post_ids, $options = []) {
        if (empty($post_ids) || !is_array($post_ids)) {
            return new WP_Error('invalid_data', __('Invalid post IDs provided', 'aiseo'));
        }
        
        $defaults = [
            'meta_types' => ['title', 'description'],
            'overwrite' => false
        ];
        
        $options = wp_parse_args($options, $defaults);
        
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
            'generated' => []
        ];
        
        $ai_generator = new AISEO_AI_Generator();
        
        foreach ($post_ids as $post_id) {
            $post_id = absint($post_id);
            $post = get_post($post_id);
            
            if (!$post) {
                $results['failed']++;
                $results['errors'][] = sprintf(
                    /* translators: %d: post ID */
                    __('Post %d not found', 'aiseo'),
                    $post_id
                );
                continue;
            }

            if (!current_user_can('edit_post', $post_id)) {
                $results['failed']++;
                $results['errors'][] = sprintf(
                    /* translators: %d: post ID */
                    __('You are not allowed to edit post %d.', 'aiseo'),
                    $post_id
                );
                continue;
            }
            
            $generated_fields = [];
            
            // Generate meta title
            if (in_array('title', $options['meta_types'])) {
                $existing_title = get_post_meta($post_id, '_aiseo_meta_title', true);
                
                if (empty($existing_title) || $options['overwrite']) {
                    $focus_keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
                    $title = $ai_generator->generate_title($post->post_content, $focus_keyword);
                    
                    if (!is_wp_error($title)) {
                        update_post_meta($post_id, '_aiseo_meta_title', $title);
                        $generated_fields[] = 'title';
                    }
                } else {
                    $results['skipped']++;
                }
            }
            
            // Generate meta description
            if (in_array('description', $options['meta_types'])) {
                $existing_desc = get_post_meta($post_id, '_aiseo_meta_description', true);
                
                if (empty($existing_desc) || $options['overwrite']) {
                    $focus_keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
                    $description = $ai_generator->generate_description($post->post_content, $focus_keyword);
                    
                    if (!is_wp_error($description)) {
                        update_post_meta($post_id, '_aiseo_meta_description', $description);
                        $generated_fields[] = 'description';
                    }
                } else {
                    $results['skipped']++;
                }
            }
            
            if (!empty($generated_fields)) {
                $results['success']++;
                $results['generated'][] = [
                    'post_id' => $post_id,
                    'fields' => $generated_fields
                ];
            }
            
            // Rate limiting - 2 second delay between posts
            sleep(2);
        }
        
        return $results;
    }
    
    /**
     * Bulk delete metadata
     *
     * @param array $post_ids Array of post IDs
     * @param array $meta_keys Metadata keys to delete
     * @return array Results
     */
    public function bulk_delete($post_ids, $meta_keys = []) {
        if (empty($post_ids) || !is_array($post_ids)) {
            return new WP_Error('invalid_data', __('Invalid post IDs provided', 'aiseo'));
        }
        
        $allowed_meta_keys = [
            '_aiseo_meta_title',
            '_aiseo_meta_description',
            '_aiseo_focus_keyword',
            '_aiseo_canonical_url',
            '_aiseo_robots_index',
            '_aiseo_robots_follow'
        ];

        if (empty($meta_keys)) {
            $meta_keys = $allowed_meta_keys;
        } else {
            $meta_keys = array_values(array_intersect($meta_keys, $allowed_meta_keys));
        }

        if (empty($meta_keys)) {
            return new WP_Error('invalid_data', __('No valid metadata keys to delete', 'aiseo'));
        }
        
        $results = [
            'success' => 0,
            'failed' => 0,
            'deleted' => []
        ];
        
        foreach ($post_ids as $post_id) {
            $post_id = absint($post_id);
            $post = get_post($post_id);
            
            if (!$post || !current_user_can('edit_post', $post_id)) {
                $results['failed']++;
                continue;
            }
            
            $deleted_fields = [];
            foreach ($meta_keys as $key) {
                delete_post_meta($post_id, $key);
                $deleted_fields[] = $key;
            }
            
            $results['success']++;
            $results['deleted'][] = [
                'post_id' => $post_id,
                'fields' => $deleted_fields
            ];
        }
        
        return $results;
    }
    
    /**
     * Preview bulk changes without applying them
     *
     * @param array $updates Array of post IDs and their metadata updates
     * @return array Preview of changes
     */
    public function preview_changes($updates) {
        if (empty($updates) || !is_array($updates)) {
            return new WP_Error('invalid_data', __('Invalid update data provided', 'aiseo'));
        }
        
        $preview = [];
        
        foreach ($updates as $update) {
            if (!isset($update['post_id'])) {
                continue;
            }
            
            $post_id = absint($update['post_id']);
            $post = get_post($post_id);
            
            if (!$post || !current_user_can('edit_post', $post_id)) {
                continue;
            }
            
            $changes = [
                'post_id' => $post_id,
                'post_title' => $post->post_title,
                'before' => [],
                'after' => []
            ];
            
            // Compare each field
            if (isset($update['meta_title'])) {
                $changes['before']['meta_title'] = get_post_meta($post_id, '_aiseo_meta_title', true);
                $changes['after']['meta_title'] = sanitize_text_field($update['meta_title']);
            }
            
            if (isset($update['meta_description'])) {
                $changes['before']['meta_description'] = get_post_meta($post_id, '_aiseo_meta_description', true);
                $changes['after']['meta_description'] = sanitize_textarea_field($update['meta_description']);
            }
            
            if (isset($update['focus_keyword'])) {
                $changes['before']['focus_keyword'] = get_post_meta($post_id, '_aiseo_focus_keyword', true);
                $changes['after']['focus_keyword'] = sanitize_text_field($update['focus_keyword']);
            }
            
            $preview[] = $changes;
        }
        
        return $preview;
    }
}
