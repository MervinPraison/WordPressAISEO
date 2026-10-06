<?php
/**
 * AISEO Plugin Importer
 *
 * Imports settings and data from the old seo-wordpress plugin
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Importer {
    
    /**
     * Old plugin option keys mapping to new plugin
     */
    private $option_mapping = array(
        // Homepage SEO
        'praison_seo_home_title' => 'aiseo_home_title',
        'praison_seo_home_description' => 'aiseo_home_description',
        'praison_seo_home_keywords' => 'aiseo_home_keywords',
        
        // Webmaster Verification
        'praison_seo_google_verify' => 'aiseo_verification_google',
        'praison_seo_bing_verify' => 'aiseo_verification_bing',
        
        // Google Analytics
        'praison_seo_ga_id' => 'aiseo_ga_tracking_id',
        
        // RSS Settings
        'praison_seo_rss_before' => 'aiseo_rss_before_content',
        'praison_seo_rss_after' => 'aiseo_rss_after_content',
    );
    
    /**
     * Old plugin post meta keys mapping
     */
    private $meta_mapping = array(
        '_praison_seo_title' => '_aiseo_meta_title',
        '_praison_seo_description' => '_aiseo_meta_description',
        '_praison_seo_keywords' => '_aiseo_keywords',
        '_praison_seo_canonical' => '_aiseo_canonical',
        '_praison_seo_noindex' => '_aiseo_noindex',
        '_praison_seo_nofollow' => '_aiseo_nofollow',
    );
    
    /**
     * Check if old plugin data exists
     *
     * @return bool
     */
    public function has_old_plugin_data() {
        // Check for old plugin options
        foreach (array_keys($this->option_mapping) as $old_key) {
            if (get_option($old_key) !== false) {
                return true;
            }
        }
        
        // Check for old plugin post meta
        global $wpdb;
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_praison_seo_%'"
        );
        
        return $count > 0;
    }
    
    /**
     * Get import preview (what will be imported)
     *
     * @return array Preview data
     */
    public function get_import_preview() {
        $preview = array(
            'options' => array(),
            'post_meta_count' => 0,
            'taxonomy_meta_count' => 0,
        );
        
        // Check options
        foreach ($this->option_mapping as $old_key => $new_key) {
            $value = get_option($old_key);
            if ($value !== false && !empty($value)) {
                $preview['options'][$old_key] = array(
                    'old_key' => $old_key,
                    'new_key' => $new_key,
                    'value' => is_string($value) ? substr($value, 0, 100) : $value,
                );
            }
        }
        
        // Count post meta
        global $wpdb;
        $preview['post_meta_count'] = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_praison_seo_%'"
        );
        
        // Count taxonomy meta (if stored in options)
        $taxonomy_meta = get_option('praison_seo_taxonomy_meta', array());
        if (!empty($taxonomy_meta)) {
            $preview['taxonomy_meta_count'] = count($taxonomy_meta, COUNT_RECURSIVE);
        }
        
        return $preview;
    }
    
    /**
     * Import all data from old plugin
     *
     * @param bool $overwrite Whether to overwrite existing data
     * @return array Import results
     */
    public function import_all($overwrite = false) {
        $results = array(
            'options_imported' => 0,
            'posts_imported' => 0,
            'taxonomies_imported' => 0,
            'errors' => array(),
        );
        
        // Import options
        $options_result = $this->import_options($overwrite);
        $results['options_imported'] = $options_result['imported'];
        $results['errors'] = array_merge($results['errors'], $options_result['errors']);
        
        // Import post meta
        $posts_result = $this->import_post_meta($overwrite);
        $results['posts_imported'] = $posts_result['imported'];
        $results['errors'] = array_merge($results['errors'], $posts_result['errors']);
        
        // Import taxonomy meta
        $taxonomy_result = $this->import_taxonomy_meta($overwrite);
        $results['taxonomies_imported'] = $taxonomy_result['imported'];
        $results['errors'] = array_merge($results['errors'], $taxonomy_result['errors']);
        
        return $results;
    }
    
    /**
     * Import options from old plugin
     *
     * @param bool $overwrite Whether to overwrite existing
     * @return array Results
     */
    public function import_options($overwrite = false) {
        $results = array(
            'imported' => 0,
            'errors' => array(),
        );
        
        foreach ($this->option_mapping as $old_key => $new_key) {
            $old_value = get_option($old_key);
            
            if ($old_value === false || empty($old_value)) {
                continue;
            }
            
            // Check if new option exists
            $new_value = get_option($new_key);
            
            if ($new_value !== false && !empty($new_value) && !$overwrite) {
                continue; // Skip if exists and not overwriting
            }
            
            // Import the option
            update_option($new_key, $old_value);
            $results['imported']++;
        }
        
        // Handle special cases
        $this->import_homepage_seo();
        $this->import_analytics_settings();
        $this->import_rss_settings();
        
        return $results;
    }
    
    /**
     * Import homepage SEO settings
     */
    private function import_homepage_seo() {
        $homepage_seo = new AISEO_Homepage_SEO();
        $settings = array();
        
        $home_title = get_option('praison_seo_home_title');
        if ($home_title) {
            $settings['home_title'] = $home_title;
        }
        
        $home_desc = get_option('praison_seo_home_description');
        if ($home_desc) {
            $settings['home_description'] = $home_desc;
        }
        
        $home_keywords = get_option('praison_seo_home_keywords');
        if ($home_keywords) {
            $settings['home_keywords'] = $home_keywords;
        }
        
        if (!empty($settings)) {
            $homepage_seo->update_settings($settings);
        }
    }
    
    /**
     * Import analytics settings
     */
    private function import_analytics_settings() {
        $ga_id = get_option('praison_seo_ga_id');
        
        if ($ga_id) {
            $analytics = new AISEO_Analytics();
            $analytics->update_settings(array(
                'tracking_id' => $ga_id,
                'enabled' => true,
            ));
        }
    }
    
    /**
     * Import RSS settings
     */
    private function import_rss_settings() {
        $rss = new AISEO_RSS();
        $settings = array();
        
        $before = get_option('praison_seo_rss_before');
        if ($before) {
            $settings['before_content'] = $before;
        }
        
        $after = get_option('praison_seo_rss_after');
        if ($after) {
            $settings['after_content'] = $after;
        }
        
        if (!empty($settings)) {
            $settings['enabled'] = true;
            $rss->update_settings($settings);
        }
    }
    
    /**
     * Import post meta from old plugin
     *
     * @param bool $overwrite Whether to overwrite existing
     * @return array Results
     */
    public function import_post_meta($overwrite = false) {
        global $wpdb;
        
        $results = array(
            'imported' => 0,
            'errors' => array(),
        );
        
        // Get all posts with old meta
        $post_ids = $wpdb->get_col(
            "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE '_praison_seo_%'"
        );
        
        foreach ($post_ids as $post_id) {
            $imported_for_post = false;
            
            foreach ($this->meta_mapping as $old_key => $new_key) {
                $old_value = get_post_meta($post_id, $old_key, true);
                
                if (empty($old_value)) {
                    continue;
                }
                
                // Check if new meta exists
                $new_value = get_post_meta($post_id, $new_key, true);
                
                if (!empty($new_value) && !$overwrite) {
                    continue;
                }
                
                // Import the meta
                update_post_meta($post_id, $new_key, $old_value);
                $imported_for_post = true;
            }
            
            if ($imported_for_post) {
                $results['imported']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Import taxonomy meta from old plugin
     *
     * @param bool $overwrite Whether to overwrite existing
     * @return array Results
     */
    public function import_taxonomy_meta($overwrite = false) {
        $results = array(
            'imported' => 0,
            'errors' => array(),
        );
        
        // Old plugin stored taxonomy meta in a single option
        $old_taxonomy_meta = get_option('praison_seo_taxonomy_meta', array());
        
        if (empty($old_taxonomy_meta)) {
            return $results;
        }
        
        $taxonomy_seo = new AISEO_Taxonomy_SEO();
        
        foreach ($old_taxonomy_meta as $taxonomy => $terms) {
            if (!is_array($terms)) {
                continue;
            }
            
            foreach ($terms as $term_id => $meta) {
                if (!is_array($meta)) {
                    continue;
                }
                
                // Map old keys to new keys
                $new_meta = array();
                
                if (isset($meta['title'])) {
                    $new_meta['title'] = $meta['title'];
                }
                if (isset($meta['description'])) {
                    $new_meta['description'] = $meta['description'];
                }
                if (isset($meta['keywords'])) {
                    $new_meta['keywords'] = $meta['keywords'];
                }
                if (isset($meta['canonical'])) {
                    $new_meta['canonical'] = $meta['canonical'];
                }
                if (isset($meta['noindex'])) {
                    $new_meta['noindex'] = (bool) $meta['noindex'];
                }
                if (isset($meta['nofollow'])) {
                    $new_meta['nofollow'] = (bool) $meta['nofollow'];
                }
                
                if (!empty($new_meta)) {
                    $taxonomy_seo->update_term_meta($term_id, $taxonomy, $new_meta);
                    $results['imported']++;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Clean up old plugin data after import
     *
     * @return array Cleanup results
     */
    public function cleanup_old_data() {
        global $wpdb;
        
        $results = array(
            'options_deleted' => 0,
            'meta_deleted' => 0,
        );
        
        // Delete old options
        foreach (array_keys($this->option_mapping) as $old_key) {
            if (delete_option($old_key)) {
                $results['options_deleted']++;
            }
        }
        
        // Delete old post meta
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_praison_seo_%'"
        );
        $results['meta_deleted'] = $deleted;
        
        // Delete old taxonomy meta option
        delete_option('praison_seo_taxonomy_meta');
        
        return $results;
    }
}
