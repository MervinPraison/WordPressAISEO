<?php
/**
 * AISEO XML Sitemap Generator
 *
 * Generates and manages XML sitemaps for search engines
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Sitemap {
    
    /**
     * Sitemap cache key
     */
    const CACHE_KEY = 'aiseo_sitemap_cache';
    
    /**
     * Cache duration (12 hours)
     */
    const CACHE_DURATION = 43200;
    
    /**
     * Initialize sitemap generator
     */
    public function init() {
        // Disable WordPress core sitemaps (we're replacing them)
        add_filter('wp_sitemaps_enabled', '__return_false');
        
        // Prevent trailing slash redirect for sitemap URLs
        add_filter('redirect_canonical', array($this, 'prevent_sitemap_redirect'), 10, 2);
        
        // Add rewrite rules for sitemap
        add_action('init', array($this, 'add_rewrite_rules'));
        
        // Handle sitemap requests
        add_action('template_redirect', array($this, 'handle_sitemap_request'));
        
        // Clear cache on post save/delete
        add_action('save_post', array($this, 'clear_cache'));
        add_action('delete_post', array($this, 'clear_cache'));
        
        // Add sitemap to robots.txt
        add_filter('robots_txt', array($this, 'add_sitemap_to_robots'), 10, 2);
    }
    
    /**
     * Add rewrite rules for sitemap
     */
    public function add_rewrite_rules() {
        // Add query vars
        add_filter('query_vars', function($vars) {
            $vars[] = 'aiseo_sitemap';
            $vars[] = 'aiseo_sitemap_type';
            return $vars;
        });
        
        // Use rewrite_rules_array filter to ensure our rules come first
        add_filter('rewrite_rules_array', function($rules) {
            $new_rules = array(
                // OLD-STYLE URLs (PRIMARY - for migration compatibility)
                // Main sitemap index (old Yoast-style)
                '^sitemap_index\.xml$' => 'index.php?aiseo_sitemap=index',
                // Old-style post type sitemaps
                '^post-sitemap\.xml$' => 'index.php?aiseo_sitemap=posts-post-1',
                '^page-sitemap\.xml$' => 'index.php?aiseo_sitemap=posts-page-1',
                '^([a-z0-9_-]+)-sitemap\.xml$' => 'index.php?aiseo_sitemap=posts-$matches[1]-1',
                // Old-style taxonomy sitemaps
                '^category-sitemap\.xml$' => 'index.php?aiseo_sitemap=taxonomies-category-1',
                '^post_tag-sitemap\.xml$' => 'index.php?aiseo_sitemap=taxonomies-post_tag-1',
                
                // NEW-STYLE URLs (BACKUP - WordPress standard)
                // Primary WordPress standard path
                '^wp-sitemap\.xml$' => 'index.php?aiseo_sitemap=index',
                // User-friendly alias
                '^sitemap\.xml$' => 'index.php?aiseo_sitemap=index',
                // Sub-sitemaps (WordPress standard format)
                '^wp-sitemap-posts-([^-]+)-([0-9]+)\.xml$' => 'index.php?aiseo_sitemap=posts-$matches[1]-$matches[2]',
                '^wp-sitemap-taxonomies-([^-]+)-([0-9]+)\.xml$' => 'index.php?aiseo_sitemap=taxonomies-$matches[1]-$matches[2]',
                '^wp-sitemap-users-([0-9]+)\.xml$' => 'index.php?aiseo_sitemap=users-$matches[1]',
            );
            return $new_rules + $rules;
        });
    }
    
    /**
     * Handle sitemap requests
     */
    public function handle_sitemap_request() {
        $sitemap = get_query_var('aiseo_sitemap');
        
        if (empty($sitemap)) {
            return;
        }
        
        // Generate sitemap
        if ($sitemap === '1' || $sitemap === 'index') {
            $this->output_sitemap_index();
        } else {
            // Parse WordPress standard format: posts-{type}-{page} or taxonomies-{type}-{page} or users-{page}
            $parts = explode('-', $sitemap);
            if (count($parts) >= 3 && $parts[0] === 'posts') {
                // posts-post-1 format
                $post_type = $parts[1];
                $page = isset($parts[2]) ? intval($parts[2]) : 1;
                $this->output_sitemap($post_type, $page);
            } elseif (count($parts) >= 3 && $parts[0] === 'taxonomies') {
                // taxonomies-category-1 format
                $taxonomy = $parts[1];
                $page = isset($parts[2]) ? intval($parts[2]) : 1;
                $this->output_taxonomy_sitemap($taxonomy, $page);
            } elseif (count($parts) >= 2 && $parts[0] === 'users') {
                // users-1 format
                $page = isset($parts[1]) ? intval($parts[1]) : 1;
                $this->output_users_sitemap($page);
            } else {
                // Legacy format support
                $this->output_sitemap($sitemap);
            }
        }
        
        exit;
    }
    
    /**
     * Output sitemap index
     */
    private function output_sitemap_index() {
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?xml-stylesheet type="text/xsl" href="' . esc_url(plugins_url('assets/sitemap.xsl', dirname(__FILE__))) . '"?>' . "\n";
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Get enabled post types
        $post_types = $this->get_enabled_post_types();
        
        foreach ($post_types as $post_type) {
            $lastmod = $this->get_post_type_lastmod($post_type);
            
            // Use old-style URLs as primary (e.g., post-sitemap.xml, page-sitemap.xml)
            $sitemap_url = home_url("/{$post_type}-sitemap.xml");
            
            echo "\t<sitemap>\n";
            echo "\t\t<loc>" . esc_url($sitemap_url) . "</loc>\n";
            if ($lastmod) {
                echo "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
            }
            echo "\t</sitemap>\n";
        }
        
        // Add taxonomy sitemaps
        $taxonomies = $this->get_enabled_taxonomies();
        
        foreach ($taxonomies as $taxonomy) {
            $lastmod = $this->get_taxonomy_lastmod($taxonomy);
            
            // Use old-style URLs (e.g., category-sitemap.xml, post_tag-sitemap.xml)
            $sitemap_url = home_url("/{$taxonomy}-sitemap.xml");
            
            echo "\t<sitemap>\n";
            echo "\t\t<loc>" . esc_url($sitemap_url) . "</loc>\n";
            if ($lastmod) {
                echo "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
            }
            echo "\t</sitemap>\n";
        }
        
        echo '</sitemapindex>';
    }
    
    /**
     * Get enabled taxonomies for sitemap
     *
     * @return array Taxonomy names
     */
    private function get_enabled_taxonomies() {
        $taxonomies = get_taxonomies(array(
            'public' => true,
            'publicly_queryable' => true,
        ), 'names');
        
        // Filter out unwanted taxonomies
        $excluded = array('post_format');
        $taxonomies = array_diff($taxonomies, $excluded);
        
        return apply_filters('aiseo_sitemap_taxonomies', $taxonomies);
    }
    
    /**
     * Get taxonomy last modified date
     *
     * @param string $taxonomy Taxonomy name
     * @return string|null ISO 8601 date or null
     */
    private function get_taxonomy_lastmod($taxonomy) {
        global $wpdb;
        
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'number' => 1,
            'orderby' => 'count',
            'order' => 'DESC',
        ));
        
        if (empty($terms) || is_wp_error($terms)) {
            return null;
        }
        
        // Get most recent post in this taxonomy
        $term = $terms[0];
        $posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => 1,
            'orderby' => 'modified',
            'order' => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ),
            ),
        ));
        
        if (!empty($posts)) {
            return gmdate('c', strtotime($posts[0]->post_modified_gmt));
        }
        
        return null;
    }
    
    /**
     * Output sitemap for specific post type
     *
     * @param string $post_type Post type
     * @param int $page Page number (default 1)
     */
    private function output_sitemap($post_type, $page = 1) {
        // Check cache
        $cache_key = self::CACHE_KEY . '_' . $post_type . '_' . $page;
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            header('Content-Type: application/xml; charset=utf-8');
            header('X-Robots-Tag: noindex, follow');
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML sitemap output with proper content-type header
            echo $cached;
            return;
        }
        
        // Generate sitemap
        $sitemap = $this->generate_sitemap($post_type);
        
        // Cache it
        set_transient($cache_key, $sitemap, self::CACHE_DURATION);
        
        // Output
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML sitemap output with proper content-type header
        echo $sitemap;
    }
    
    /**
     * Generate sitemap XML for post type
     *
     * @param string $post_type Post type
     * @return string Sitemap XML
     */
    public function generate_sitemap($post_type) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(plugins_url('assets/sitemap.xsl', dirname(__FILE__))) . '"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        
        // Get posts
        $posts = $this->get_posts_for_sitemap($post_type);
        
        foreach ($posts as $post) {
            $xml .= $this->generate_url_entry($post);
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Generate URL entry for post
     *
     * @param WP_Post $post Post object
     * @return string URL entry XML
     */
    private function generate_url_entry($post) {
        $xml = "\t<url>\n";
        
        // Location
        $xml .= "\t\t<loc>" . esc_url(get_permalink($post->ID)) . "</loc>\n";
        
        // Last modified
        $lastmod = get_post_modified_time('c', false, $post);
        $xml .= "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
        
        // Change frequency
        $changefreq = $this->get_changefreq($post);
        $xml .= "\t\t<changefreq>" . esc_html($changefreq) . "</changefreq>\n";
        
        // Priority
        $priority = $this->get_priority($post);
        $xml .= "\t\t<priority>" . esc_html($priority) . "</priority>\n";
        
        // Images
        $images = $this->get_post_images($post);
        foreach ($images as $image) {
            $xml .= "\t\t<image:image>\n";
            $xml .= "\t\t\t<image:loc>" . esc_url($image['url']) . "</image:loc>\n";
            if (!empty($image['title'])) {
                $xml .= "\t\t\t<image:title>" . esc_html($image['title']) . "</image:title>\n";
            }
            if (!empty($image['caption'])) {
                $xml .= "\t\t\t<image:caption>" . esc_html($image['caption']) . "</image:caption>\n";
            }
            $xml .= "\t\t</image:image>\n";
        }
        
        $xml .= "\t</url>\n";
        
        return $xml;
    }
    
    /**
     * Get posts for sitemap
     *
     * @param string $post_type Post type
     * @return array Posts
     */
    private function get_posts_for_sitemap($post_type) {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        );
        
        // Exclude noindex posts
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for sitemap generation, results are cached
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key' => '_aiseo_noindex',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key' => '_aiseo_noindex',
                'value' => '0',
            ),
        );
        
        $query = new WP_Query($args);
        
        return $query->posts;
    }
    
    /**
     * Get change frequency for post
     *
     * @param WP_Post $post Post object
     * @return string Change frequency
     */
    private function get_changefreq($post) {
        // Check for custom changefreq
        $custom = get_post_meta($post->ID, '_aiseo_sitemap_changefreq', true);
        
        if (!empty($custom)) {
            return $custom;
        }
        
        // Calculate based on post age
        $post_age_days = (time() - strtotime($post->post_date)) / DAY_IN_SECONDS;
        
        if ($post_age_days < 7) {
            return 'daily';
        } elseif ($post_age_days < 30) {
            return 'weekly';
        } elseif ($post_age_days < 365) {
            return 'monthly';
        } else {
            return 'yearly';
        }
    }
    
    /**
     * Get priority for post
     *
     * @param WP_Post $post Post object
     * @return string Priority (0.0 - 1.0)
     */
    private function get_priority($post) {
        // Check for custom priority
        $custom = get_post_meta($post->ID, '_aiseo_sitemap_priority', true);
        
        if (!empty($custom)) {
            return $custom;
        }
        
        // Calculate based on post type and homepage
        if ($post->post_type === 'page') {
            // Homepage gets highest priority
            if (get_option('page_on_front') == $post->ID) {
                return '1.0';
            }
            return '0.8';
        }
        
        // Posts get medium-high priority
        if ($post->post_type === 'post') {
            return '0.6';
        }
        
        // Other post types get medium priority
        return '0.5';
    }
    
    /**
     * Get images from post
     *
     * @param WP_Post $post Post object
     * @return array Images
     */
    private function get_post_images($post) {
        $images = array();
        
        // Featured image
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');
            $image_title = get_the_title($thumbnail_id);
            $image_caption = wp_get_attachment_caption($thumbnail_id);
            
            if ($image_url) {
                $images[] = array(
                    'url' => $image_url,
                    'title' => $image_title,
                    'caption' => $image_caption,
                );
            }
        }
        
        // Images from content (limit to 5 total)
        if (count($images) < 5) {
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches);
            
            if (!empty($matches[1])) {
                foreach (array_slice($matches[1], 0, 5 - count($images)) as $img_url) {
                    $images[] = array(
                        'url' => $img_url,
                        'title' => '',
                        'caption' => '',
                    );
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Get enabled post types for sitemap
     *
     * @return array Post types
     */
    private function get_enabled_post_types() {
        $default_types = array('post', 'page');
        $enabled_types = get_option('aiseo_sitemap_post_types', $default_types);
        
        // Filter out non-public post types
        $public_types = get_post_types(array('public' => true));
        
        return array_intersect($enabled_types, $public_types);
    }
    
    /**
     * Get last modified date for post type
     *
     * @param string $post_type Post type
     * @return string|false Last modified date or false
     */
    private function get_post_type_lastmod($post_type) {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'modified',
            'order' => 'DESC',
            'fields' => 'ids',
        );
        
        $query = new WP_Query($args);
        
        if (!empty($query->posts)) {
            $post = get_post($query->posts[0]);
            return get_post_modified_time('c', false, $post);
        }
        
        return false;
    }
    
    /**
     * Clear sitemap cache
     */
    public function clear_cache() {
        $post_types = $this->get_enabled_post_types();
        
        foreach ($post_types as $post_type) {
            delete_transient(self::CACHE_KEY . '_' . $post_type);
        }
    }
    
    /**
     * Prevent trailing slash redirect for sitemap URLs
     *
     * @param string $redirect_url The redirect URL
     * @param string $requested_url The requested URL
     * @return string|false Modified redirect URL or false to prevent redirect
     */
    public function prevent_sitemap_redirect($redirect_url, $requested_url) {
        // Check if this is a sitemap request
        if (strpos($requested_url, 'wp-sitemap') !== false || 
            strpos($requested_url, 'sitemap') !== false && strpos($requested_url, '.xml') !== false) {
            // Prevent redirect
            return false;
        }
        
        return $redirect_url;
    }
    
    /**
     * Add sitemap to robots.txt
     *
     * @param string $output Robots.txt output
     * @param bool $public Whether site is public
     * @return string Modified output
     */
    public function add_sitemap_to_robots($output, $public) {
        if ($public) {
            $output .= "\n# AISEO Sitemap\n";
            // Use old-style URL as primary for migration compatibility
            $output .= "Sitemap: " . home_url('/sitemap_index.xml') . "\n";
            $output .= "# Also available at:\n";
            $output .= "# " . home_url('/sitemap.xml') . "\n";
            $output .= "# " . home_url('/wp-sitemap.xml') . "\n";
        }
        
        return $output;
    }
    
    /**
     * Ping search engines about sitemap update
     */
    public function ping_search_engines() {
        // Use old-style URL as primary
        $sitemap_url = urlencode(home_url('/sitemap_index.xml'));
        
        // Google
        wp_remote_get("https://www.google.com/ping?sitemap={$sitemap_url}");
        
        // Bing
        wp_remote_get("https://www.bing.com/ping?sitemap={$sitemap_url}");
    }
    
    /**
     * Get sitemap statistics
     *
     * @return array Statistics
     */
    public function get_sitemap_stats() {
        $stats = array(
            'post_types' => array(),
            'total_urls' => 0,
            'last_generated' => '',
        );
        
        $post_types = $this->get_enabled_post_types();
        
        foreach ($post_types as $post_type) {
            $posts = $this->get_posts_for_sitemap($post_type);
            $count = count($posts);
            
            $stats['post_types'][$post_type] = $count;
            $stats['total_urls'] += $count;
        }
        
        // Check if cached
        $cache_key = self::CACHE_KEY . '_' . $post_types[0];
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            $stats['last_generated'] = 'Cached';
        } else {
            $stats['last_generated'] = 'Not cached';
        }
        
        return $stats;
    }
}
