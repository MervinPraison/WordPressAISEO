<?php
/**
 * AISEO 404 Monitor & Redirection Manager
 *
 * @package AISEO
 * @since 1.9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 404 Monitor & Redirection Manager Class
 */
class AISEO_Redirects {
    
    /**
     * Log 404 error
     *
     * @param string $url Requested URL
     * @param array $options Additional options
     * @return bool Success status
     */
    public function log_404_error($url, $options = []) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aiseo_404_log';
        
        // Get request details
        $referrer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        $ip_address = $this->get_client_ip();
        
        // Insert log entry
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared , WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table, no WP equivalent
        $result = $wpdb->insert(
            $table_name,
            [
                'url' => esc_url_raw($url),
                'referrer' => $referrer,
                'user_agent' => $user_agent,
                'ip_address' => $ip_address,
                'timestamp' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Get client IP address
     *
     * @return string IP address
     */
    private function get_client_ip() {
        $ip = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        
        return $ip;
    }
    
    /**
     * Get 404 errors
     *
     * @param array $args Query arguments
     * @return array 404 errors
     */
    public function get_404_errors($args = []) {
        global $wpdb;
        
        $defaults = [
            'limit' => 100,
            'offset' => 0,
            'orderby' => 'timestamp',
            'order' => 'DESC',
            'date_from' => '',
            'date_to' => ''
        ];
        
        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . 'aiseo_404_log';
        
        // Build query
        $where = '1=1';
        
        if (!empty($args['date_from'])) {
            $where .= $wpdb->prepare(' AND timestamp >= %s', $args['date_from']);
        }
        
        if (!empty($args['date_to'])) {
            $where .= $wpdb->prepare(' AND timestamp <= %s', $args['date_to']);
        }
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name and dynamic WHERE/ORDER BY are safe, values are prepared
        $query = $wpdb->prepare(
            "SELECT url, COUNT(*) as hits, MAX(timestamp) as last_hit, referrer
             FROM {$table_name}
             WHERE {$where}
             GROUP BY url
             ORDER BY {$args['orderby']} {$args['order']}
             LIMIT %d OFFSET %d",
            $args['limit'],
            $args['offset']
        );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared , WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Query is prepared above with $wpdb->prepare()
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return [
            'errors' => $results,
            'total' => count($results)
        ];
    }
    
    /**
     * Suggest redirect based on URL similarity
     *
     * @param string $url 404 URL
     * @return array|WP_Error Redirect suggestions
     */
    public function suggest_redirect($url) {
        // Get existing posts/pages
        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (empty($posts)) {
            return new WP_Error('no_posts', 'No published posts or pages found');
        }
        
        // Extract slug from URL
        $url_slug = basename(wp_parse_url($url, PHP_URL_PATH));
        
        // Find similar URLs based on slug matching
        $matches = [];
        foreach ($posts as $post) {
            $post_slug = $post->post_name;
            $post_url = get_permalink($post->ID);
            
            // Calculate similarity
            similar_text($url_slug, $post_slug, $percent);
            
            if ($percent > 30) { // At least 30% similar
                $matches[] = [
                    'url' => $post_url,
                    'title' => $post->post_title,
                    'slug' => $post_slug,
                    'similarity' => $percent
                ];
            }
        }
        
        // Sort by similarity
        usort($matches, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        if (empty($matches)) {
            return new WP_Error('no_matches', 'No similar URLs found');
        }
        
        // Build suggestion response
        $best_match = $matches[0];
        $alternatives = array_slice(array_column($matches, 'url'), 1, 3);
        
        return [
            'suggested_url' => $best_match['url'],
            'confidence' => $best_match['similarity'] > 70 ? 'high' : ($best_match['similarity'] > 50 ? 'medium' : 'low'),
            'reason' => sprintf(
                'URL slug "%s" is %.1f%% similar to "%s"',
                $url_slug,
                $best_match['similarity'],
                $best_match['slug']
            ),
            'alternatives' => $alternatives
        ];
    }
    
    /**
     * Parse AI response
     *
     * @param string $response AI response
     * @return array|false Parsed suggestion
     */
    private function parse_ai_response($response) {
        // Try to extract JSON from response
        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }
        
        return false;
    }
    
    /**
     * Create redirect
     *
     * @param string $source Source URL
     * @param string $target Target URL
     * @param string $type Redirect type (301, 302, 307)
     * @param array $options Additional options
     * @return int|WP_Error Redirect ID or error
     */
    public function create_redirect($source, $target, $type = '301', $options = []) {
        global $wpdb;
        
        // Validate redirect type
        if (!in_array($type, ['301', '302', '307'])) {
            return new WP_Error('invalid_type', 'Invalid redirect type');
        }
        
        // Validate URLs
        if (empty($source) || empty($target)) {
            return new WP_Error('invalid_urls', 'Source and target URLs are required');
        }
        
        $table_name = $wpdb->prefix . 'aiseo_redirects';
        
        // Check if redirect already exists
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table query
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE source_url = %s",
            $source
        ));
        
        if ($existing) {
            return new WP_Error('redirect_exists', 'Redirect already exists for this URL');
        }
        
        // Insert redirect
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared , WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table, no WP equivalent
        $result = $wpdb->insert(
            $table_name,
            [
                'source_url' => esc_url_raw($source),
                'target_url' => esc_url_raw($target),
                'redirect_type' => $type,
                'is_regex' => isset($options['is_regex']) ? (int) $options['is_regex'] : 0,
                'hits' => 0,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%d', '%d', '%s']
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create redirect');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get redirects
     *
     * @param array $args Query arguments
     * @return array Redirects
     */
    public function get_redirects($args = []) {
        global $wpdb;
        
        $defaults = [
            'limit' => 100,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . 'aiseo_redirects';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name and ORDER BY are safe, values are prepared
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name}
             ORDER BY {$args['orderby']} {$args['order']}
             LIMIT %d OFFSET %d",
            $args['limit'],
            $args['offset']
        );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared , WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Query is prepared above with $wpdb->prepare()
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return [
            'redirects' => $results,
            'total' => count($results)
        ];
    }
    
    /**
     * Delete redirect
     *
     * @param int $redirect_id Redirect ID
     * @return bool Success status
     */
    public function delete_redirect($redirect_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aiseo_redirects';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared , WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table, no WP equivalent
        $result = $wpdb->delete(
            $table_name,
            ['id' => absint($redirect_id)],
            ['%d']
        );
        
        if ($result !== false) {
            flush_rewrite_rules();
        }
        
        return $result !== false;
    }
    
    /**
     * Bulk import redirects from CSV
     *
     * @param string $csv_data CSV data
     * @return array Import results
     */
    public function bulk_import_redirects($csv_data) {
        $lines = explode("\n", $csv_data);
        $imported = 0;
        $errors = [];
        
        foreach ($lines as $index => $line) {
            // Skip header row
            if ($index === 0) {
                continue;
            }
            
            $data = str_getcsv($line);
            
            if (count($data) < 2) {
                continue;
            }
            
            $source = trim($data[0]);
            $target = trim($data[1]);
            $type = isset($data[2]) ? trim($data[2]) : '301';
            
            $result = $this->create_redirect($source, $target, $type);
            
            if (is_wp_error($result)) {
                $errors[] = "Line {$index}: " . $result->get_error_message();
            } else {
                $imported++;
            }
        }
        
        return [
            'imported' => $imported,
            'errors' => $errors,
            'total_lines' => count($lines) - 1
        ];
    }
    
    /**
     * Export redirects to CSV
     *
     * @return string CSV data
     */
    public function export_redirects() {
        $redirects = $this->get_redirects(['limit' => 9999]);
        
        $csv = "Source URL,Target URL,Type,Hits,Created\n";
        
        foreach ($redirects['redirects'] as $redirect) {
            $csv .= sprintf(
                '"%s","%s","%s",%d,"%s"' . "\n",
                $redirect['source_url'],
                $redirect['target_url'],
                $redirect['redirect_type'],
                $redirect['hits'],
                $redirect['created_at']
            );
        }
        
        return $csv;
    }
    
    /**
     * Process redirect
     *
     * @param string $requested_url Requested URL
     * @return bool Whether redirect was processed
     */
    public function process_redirect($requested_url) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aiseo_redirects';
        
        // Check for exact match
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table query
        $redirect = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE source_url = %s AND is_regex = 0",
            $requested_url
        ), ARRAY_A);
        
        // Check for regex match if no exact match
        if (!$redirect) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table query
            $regex_redirects = $wpdb->get_results(
                "SELECT * FROM {$table_name} WHERE is_regex = 1",
                ARRAY_A
            );
            
            foreach ($regex_redirects as $regex_redirect) {
                if (preg_match('#' . $regex_redirect['source_url'] . '#', $requested_url)) {
                    $redirect = $regex_redirect;
                    break;
                }
            }
        }
        
        if ($redirect) {
            // Increment hit counter
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table update
            $wpdb->update(
                $table_name,
                ['hits' => $redirect['hits'] + 1],
                ['id' => $redirect['id']],
                ['%d'],
                ['%d']
            );
            
            // Perform redirect
            wp_redirect($redirect['target_url'], (int) $redirect['redirect_type']);
            exit;
        }
        
        return false;
    }
    
    /**
     * Get redirect statistics
     *
     * @return array Statistics
     */
    public function get_statistics() {
        global $wpdb;
        
        $redirects_table = $wpdb->prefix . 'aiseo_redirects';
        $errors_table = $wpdb->prefix . 'aiseo_404_log';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared , WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table, no WP equivalent
        $total_redirects = $wpdb->get_var("SELECT COUNT(*) FROM {$redirects_table}");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared , WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table, no WP equivalent
        $total_404s = $wpdb->get_var("SELECT COUNT(*) FROM {$errors_table}");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table statistics
        $total_hits = $wpdb->get_var("SELECT SUM(hits) FROM {$redirects_table}");
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table statistics
        $top_404s = $wpdb->get_results(
            "SELECT url, COUNT(*) as hits 
             FROM {$errors_table} 
             GROUP BY url 
             ORDER BY hits DESC 
             LIMIT 10",
            ARRAY_A
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table statistics
        $top_redirects = $wpdb->get_results(
            "SELECT source_url, target_url, hits 
             FROM {$redirects_table} 
             ORDER BY hits DESC 
             LIMIT 10",
            ARRAY_A
        );
        
        return [
            'total_redirects' => (int) $total_redirects,
            'total_404_errors' => (int) $total_404s,
            'total_redirect_hits' => (int) $total_hits,
            'top_404s' => $top_404s,
            'top_redirects' => $top_redirects
        ];
    }
    
    /**
     * Clear cache
     *
     * @param string $type Cache type to clear
     * @return bool Success status
     */
    public function clear_cache($type = 'all') {
        delete_transient('aiseo_redirect_stats');
        delete_transient('aiseo_404_summary');
        
        return true;
    }
}
