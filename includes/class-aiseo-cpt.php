<?php
/**
 * AISEO Custom Post Type Support Class
 * 
 * Extends SEO functionality to custom post types
 *
 * @package AISEO
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AISEO_CPT {
    
    /**
     * Get all registered custom post types
     *
     * @param array $args Optional arguments
     * @return array Custom post types
     */
    public function get_custom_post_types($args = []) {
        $defaults = [
            'public' => true,
            '_builtin' => false
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $post_types = get_post_types($args, 'objects');
        
        $cpt_list = [];
        foreach ($post_types as $post_type) {
            $cpt_list[] = [
                'name' => $post_type->name,
                'label' => $post_type->label,
                'singular_name' => $post_type->labels->singular_name,
                'description' => $post_type->description,
                'public' => $post_type->public,
                'hierarchical' => $post_type->hierarchical,
                'has_archive' => $post_type->has_archive,
                'menu_icon' => $post_type->menu_icon,
                'supports' => get_all_post_type_supports($post_type->name),
                'count' => wp_count_posts($post_type->name)->publish
            ];
        }
        
        return $cpt_list;
    }
    
    /**
     * Check if post type is supported
     *
     * @param string $post_type Post type name
     * @return bool
     */
    public function is_post_type_supported($post_type) {
        // Get supported post types from settings
        $supported_types = get_option('aiseo_supported_post_types', ['post', 'page']);
        
        return in_array($post_type, $supported_types);
    }
    
    /**
     * Enable SEO for a custom post type
     *
     * @param string $post_type Post type name
     * @return bool Success
     */
    public function enable_post_type($post_type) {
        $supported_types = get_option('aiseo_supported_post_types', ['post', 'page']);
        
        if (!in_array($post_type, $supported_types)) {
            $supported_types[] = $post_type;
            update_option('aiseo_supported_post_types', $supported_types);
            return true;
        }
        
        return false;
    }
    
    /**
     * Disable SEO for a custom post type
     *
     * @param string $post_type Post type name
     * @return bool Success
     */
    public function disable_post_type($post_type) {
        $supported_types = get_option('aiseo_supported_post_types', ['post', 'page']);
        
        $key = array_search($post_type, $supported_types);
        if ($key !== false) {
            unset($supported_types[$key]);
            update_option('aiseo_supported_post_types', array_values($supported_types));
            return true;
        }
        
        return false;
    }
    
    /**
     * Get supported post types
     *
     * @return array
     */
    public function get_supported_post_types() {
        return get_option('aiseo_supported_post_types', ['post', 'page']);
    }
    
    /**
     * Get posts from a custom post type
     *
     * @param string $post_type Post type name
     * @param array $args Optional arguments
     * @return array Posts
     */
    public function get_posts_by_type($post_type, $args = []) {
        $defaults = [
            'post_type' => $post_type,
            'posts_per_page' => 20,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $posts = get_posts($args);
        $result = [];
        
        foreach ($posts as $post) {
            $result[] = [
                'ID' => $post->ID,
                'title' => $post->post_title,
                'post_type' => $post->post_type,
                'status' => $post->post_status,
                'date' => $post->post_date,
                'url' => get_permalink($post->ID),
                'meta_title' => get_post_meta($post->ID, '_aiseo_meta_title', true),
                'meta_description' => get_post_meta($post->ID, '_aiseo_meta_description', true),
                'focus_keyword' => get_post_meta($post->ID, '_aiseo_focus_keyword', true),
                'seo_score' => get_post_meta($post->ID, '_aiseo_seo_score', true)
            ];
        }
        
        return $result;
    }
    
    /**
     * Get SEO statistics for a custom post type
     *
     * @param string $post_type Post type name
     * @return array Statistics
     */
    public function get_post_type_stats($post_type) {
        $args = [
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        ];
        
        $post_ids = get_posts($args);
        
        $stats = [
            'total_posts' => count($post_ids),
            'with_meta_title' => 0,
            'with_meta_description' => 0,
            'with_focus_keyword' => 0,
            'with_seo_score' => 0,
            'average_score' => 0,
            'needs_optimization' => 0
        ];
        
        $total_score = 0;
        $score_count = 0;
        
        foreach ($post_ids as $post_id) {
            if (get_post_meta($post_id, '_aiseo_meta_title', true)) {
                $stats['with_meta_title']++;
            }
            if (get_post_meta($post_id, '_aiseo_meta_description', true)) {
                $stats['with_meta_description']++;
            }
            if (get_post_meta($post_id, '_aiseo_focus_keyword', true)) {
                $stats['with_focus_keyword']++;
            }
            
            $score = get_post_meta($post_id, '_aiseo_seo_score', true);
            if ($score) {
                $stats['with_seo_score']++;
                $total_score += floatval($score);
                $score_count++;
                
                if ($score < 70) {
                    $stats['needs_optimization']++;
                }
            }
        }
        
        if ($score_count > 0) {
            $stats['average_score'] = round($total_score / $score_count, 2);
        }
        
        // Calculate completion percentage
        $stats['completion_percentage'] = 0;
        if ($stats['total_posts'] > 0) {
            $completed = ($stats['with_meta_title'] + $stats['with_meta_description'] + $stats['with_focus_keyword']) / 3;
            $stats['completion_percentage'] = round(($completed / $stats['total_posts']) * 100, 2);
        }
        
        return $stats;
    }
    
    /**
     * Bulk generate SEO metadata for custom post type
     *
     * @param string $post_type Post type name
     * @param array $options Options
     * @return array Results
     */
    public function bulk_generate_for_type($post_type, $options = []) {
        $defaults = [
            'limit' => -1,
            'overwrite' => false,
            'meta_types' => ['title', 'description']
        ];
        
        $options = wp_parse_args($options, $defaults);
        
        $args = [
            'post_type' => $post_type,
            'posts_per_page' => $options['limit'],
            'post_status' => 'publish'
        ];
        
        $posts = get_posts($args);
        
        $results = [
            'success' => true,
            'total' => count($posts),
            'generated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'posts' => []
        ];
        
        foreach ($posts as $post) {
            if (!current_user_can('edit_post', $post->ID)) {
                $results['failed']++;
                continue;
            }

            $post_result = [
                'post_id' => $post->ID,
                'title' => $post->post_title,
                'status' => 'skipped',
                'message' => ''
            ];
            
            // Check if already has metadata
            $has_title = get_post_meta($post->ID, '_aiseo_meta_title', true);
            $has_description = get_post_meta($post->ID, '_aiseo_meta_description', true);
            
            if (($has_title && $has_description) && !$options['overwrite']) {
                $post_result['message'] = 'Already has metadata';
                $results['skipped']++;
            } else {
                // Generate metadata (simplified - in real implementation would use AI)
                if (in_array('title', $options['meta_types'])) {
                    $meta_title = $post->post_title . ' | SEO Optimized';
                    update_post_meta($post->ID, '_aiseo_meta_title', $meta_title);
                }
                
                if (in_array('description', $options['meta_types'])) {
                    $meta_description = wp_trim_words($post->post_content, 20, '...');
                    update_post_meta($post->ID, '_aiseo_meta_description', $meta_description);
                }
                
                $post_result['status'] = 'generated';
                $post_result['message'] = 'Metadata generated successfully';
                $results['generated']++;
            }
            
            $results['posts'][] = $post_result;
        }
        
        return $results;
    }
    
    /**
     * Export SEO data for custom post type
     *
     * @param string $post_type Post type name
     * @param string $format Export format (json|csv)
     * @return mixed Export data
     */
    public function export_post_type_data($post_type, $format = 'json') {
        $posts = $this->get_posts_by_type($post_type, ['posts_per_page' => -1]);
        
        if ($format === 'csv') {
            $csv = "ID,Title,Meta Title,Meta Description,Focus Keyword,SEO Score,URL\n";
            foreach ($posts as $post) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $post['ID'],
                    str_replace('"', '""', $post['title']),
                    str_replace('"', '""', $post['meta_title']),
                    str_replace('"', '""', $post['meta_description']),
                    str_replace('"', '""', $post['focus_keyword']),
                    $post['seo_score'],
                    $post['url']
                );
            }
            return $csv;
        }
        
        // JSON format
        return [
            'post_type' => $post_type,
            'export_date' => current_time('mysql'),
            'total_posts' => count($posts),
            'posts' => $posts
        ];
    }
    
    /**
     * Get post type archive SEO settings
     *
     * @param string $post_type Post type name
     * @return array Archive SEO settings
     */
    public function get_archive_seo($post_type) {
        $archive_seo = get_option('aiseo_archive_' . $post_type, []);
        
        $defaults = [
            'meta_title' => '',
            'meta_description' => '',
            'robots_index' => 'index',
            'robots_follow' => 'follow',
            'show_in_sitemap' => true
        ];
        
        return wp_parse_args($archive_seo, $defaults);
    }
    
    /**
     * Update post type archive SEO settings
     *
     * @param string $post_type Post type name
     * @param array $settings SEO settings
     * @return bool Success
     */
    public function update_archive_seo($post_type, $settings) {
        return update_option('aiseo_archive_' . $post_type, $settings);
    }
}
