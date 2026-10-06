<?php
/**
 * AISEO Breadcrumbs
 *
 * Provides visual breadcrumb navigation with schema markup
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Breadcrumbs {
    
    /**
     * Option key for breadcrumbs settings
     */
    const OPTION_KEY = 'aiseo_breadcrumbs_settings';
    
    /**
     * Default settings
     */
    private $defaults = array(
        'enabled' => true,
        'separator' => '»',
        'home_text' => 'Home',
        'show_home' => true,
        'show_current' => true,
        'bold_current' => true,
        'show_on_home' => false,
        'wrapper_class' => 'aiseo-breadcrumbs',
        'schema_enabled' => true,
    );
    
    /**
     * Initialize breadcrumbs
     */
    public function init() {
        // Register shortcode
        add_shortcode('aiseo_breadcrumbs', array($this, 'shortcode'));
        
        // Add action hook for theme integration
        add_action('aiseo_breadcrumbs', array($this, 'display'));
    }
    
    /**
     * Get settings
     *
     * @return array Settings
     */
    public function get_settings() {
        $settings = get_option(self::OPTION_KEY, array());
        return wp_parse_args($settings, $this->defaults);
    }
    
    /**
     * Update settings
     *
     * @param array $settings Settings to update
     * @return bool Success
     */
    public function update_settings($settings) {
        $current = $this->get_settings();
        
        foreach ($settings as $key => $value) {
            if (array_key_exists($key, $this->defaults)) {
                if (is_bool($this->defaults[$key])) {
                    $current[$key] = (bool) $value;
                } else {
                    $current[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return update_option(self::OPTION_KEY, $current);
    }
    
    /**
     * Shortcode handler
     *
     * @param array $atts Shortcode attributes
     * @return string Breadcrumbs HTML
     */
    public function shortcode($atts = array()) {
        $atts = shortcode_atts(array(
            'separator' => null,
            'home_text' => null,
            'wrapper_class' => null,
        ), $atts, 'aiseo_breadcrumbs');
        
        return $this->render($atts);
    }
    
    /**
     * Display breadcrumbs (for action hook)
     *
     * @param array $args Optional arguments
     */
    public function display($args = array()) {
        echo $this->render($args);
    }
    
    /**
     * Render breadcrumbs
     *
     * @param array $args Optional arguments to override settings
     * @return string Breadcrumbs HTML
     */
    public function render($args = array()) {
        $settings = $this->get_settings();
        
        if (!$settings['enabled']) {
            return '';
        }
        
        // Don't show on homepage if disabled
        if (is_front_page() && !$settings['show_on_home']) {
            return '';
        }
        
        // Merge args with settings
        $separator = isset($args['separator']) && $args['separator'] !== null ? $args['separator'] : $settings['separator'];
        $home_text = isset($args['home_text']) && $args['home_text'] !== null ? $args['home_text'] : $settings['home_text'];
        $wrapper_class = isset($args['wrapper_class']) && $args['wrapper_class'] !== null ? $args['wrapper_class'] : $settings['wrapper_class'];
        
        // Build breadcrumbs
        $breadcrumbs = $this->build_breadcrumbs($home_text);
        
        if (empty($breadcrumbs)) {
            return '';
        }
        
        // Generate HTML
        $html = $this->generate_html($breadcrumbs, $separator, $wrapper_class, $settings);
        
        return $html;
    }
    
    /**
     * Build breadcrumbs array
     *
     * @param string $home_text Home link text
     * @return array Breadcrumbs
     */
    private function build_breadcrumbs($home_text) {
        $settings = $this->get_settings();
        $breadcrumbs = array();
        
        // Home
        if ($settings['show_home']) {
            $breadcrumbs[] = array(
                'title' => $home_text,
                'url' => home_url('/'),
                'current' => is_front_page(),
            );
        }
        
        // Front page - just home
        if (is_front_page()) {
            return $breadcrumbs;
        }
        
        // Blog page
        if (is_home()) {
            $blog_page_id = get_option('page_for_posts');
            if ($blog_page_id) {
                $breadcrumbs[] = array(
                    'title' => get_the_title($blog_page_id),
                    'url' => get_permalink($blog_page_id),
                    'current' => true,
                );
            } else {
                $breadcrumbs[] = array(
                    'title' => __('Blog', 'aiseo'),
                    'url' => '',
                    'current' => true,
                );
            }
            return $breadcrumbs;
        }
        
        // Single post
        if (is_singular('post')) {
            // Categories
            $categories = get_the_category();
            if (!empty($categories)) {
                $category = $categories[0];
                
                // Parent categories
                $parents = get_ancestors($category->term_id, 'category');
                $parents = array_reverse($parents);
                
                foreach ($parents as $parent_id) {
                    $parent = get_term($parent_id, 'category');
                    $breadcrumbs[] = array(
                        'title' => $parent->name,
                        'url' => get_term_link($parent),
                        'current' => false,
                    );
                }
                
                $breadcrumbs[] = array(
                    'title' => $category->name,
                    'url' => get_term_link($category),
                    'current' => false,
                );
            }
            
            // Current post
            $breadcrumbs[] = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        // Page
        if (is_page()) {
            global $post;
            
            // Parent pages
            if ($post->post_parent) {
                $parents = get_ancestors($post->ID, 'page');
                $parents = array_reverse($parents);
                
                foreach ($parents as $parent_id) {
                    $breadcrumbs[] = array(
                        'title' => get_the_title($parent_id),
                        'url' => get_permalink($parent_id),
                        'current' => false,
                    );
                }
            }
            
            // Current page
            $breadcrumbs[] = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        // Category
        if (is_category()) {
            $category = get_queried_object();
            
            // Parent categories
            $parents = get_ancestors($category->term_id, 'category');
            $parents = array_reverse($parents);
            
            foreach ($parents as $parent_id) {
                $parent = get_term($parent_id, 'category');
                $breadcrumbs[] = array(
                    'title' => $parent->name,
                    'url' => get_term_link($parent),
                    'current' => false,
                );
            }
            
            $breadcrumbs[] = array(
                'title' => $category->name,
                'url' => get_term_link($category),
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        // Tag
        if (is_tag()) {
            $tag = get_queried_object();
            $breadcrumbs[] = array(
                'title' => $tag->name,
                'url' => get_term_link($tag),
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        // Author
        if (is_author()) {
            $author = get_queried_object();
            $breadcrumbs[] = array(
                'title' => $author->display_name,
                'url' => get_author_posts_url($author->ID),
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        // Date archives
        if (is_date()) {
            if (is_year()) {
                $breadcrumbs[] = array(
                    'title' => get_the_date('Y'),
                    'url' => get_year_link(get_the_date('Y')),
                    'current' => true,
                );
            } elseif (is_month()) {
                $breadcrumbs[] = array(
                    'title' => get_the_date('Y'),
                    'url' => get_year_link(get_the_date('Y')),
                    'current' => false,
                );
                $breadcrumbs[] = array(
                    'title' => get_the_date('F'),
                    'url' => get_month_link(get_the_date('Y'), get_the_date('m')),
                    'current' => true,
                );
            } elseif (is_day()) {
                $breadcrumbs[] = array(
                    'title' => get_the_date('Y'),
                    'url' => get_year_link(get_the_date('Y')),
                    'current' => false,
                );
                $breadcrumbs[] = array(
                    'title' => get_the_date('F'),
                    'url' => get_month_link(get_the_date('Y'), get_the_date('m')),
                    'current' => false,
                );
                $breadcrumbs[] = array(
                    'title' => get_the_date('j'),
                    'url' => get_day_link(get_the_date('Y'), get_the_date('m'), get_the_date('d')),
                    'current' => true,
                );
            }
            
            return $breadcrumbs;
        }
        
        // Search
        if (is_search()) {
            $breadcrumbs[] = array(
                'title' => sprintf(__('Search: %s', 'aiseo'), get_search_query()),
                'url' => '',
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        // 404
        if (is_404()) {
            $breadcrumbs[] = array(
                'title' => __('Page Not Found', 'aiseo'),
                'url' => '',
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        // Custom post type
        if (is_singular()) {
            $post_type = get_post_type();
            $post_type_obj = get_post_type_object($post_type);
            
            if ($post_type_obj && $post_type_obj->has_archive) {
                $breadcrumbs[] = array(
                    'title' => $post_type_obj->labels->name,
                    'url' => get_post_type_archive_link($post_type),
                    'current' => false,
                );
            }
            
            $breadcrumbs[] = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        // Post type archive
        if (is_post_type_archive()) {
            $post_type_obj = get_queried_object();
            $breadcrumbs[] = array(
                'title' => $post_type_obj->labels->name,
                'url' => get_post_type_archive_link($post_type_obj->name),
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        // Taxonomy
        if (is_tax()) {
            $term = get_queried_object();
            $taxonomy = get_taxonomy($term->taxonomy);
            
            // Parent terms
            $parents = get_ancestors($term->term_id, $term->taxonomy);
            $parents = array_reverse($parents);
            
            foreach ($parents as $parent_id) {
                $parent = get_term($parent_id, $term->taxonomy);
                $breadcrumbs[] = array(
                    'title' => $parent->name,
                    'url' => get_term_link($parent),
                    'current' => false,
                );
            }
            
            $breadcrumbs[] = array(
                'title' => $term->name,
                'url' => get_term_link($term),
                'current' => true,
            );
            
            return $breadcrumbs;
        }
        
        return $breadcrumbs;
    }
    
    /**
     * Generate HTML output
     *
     * @param array $breadcrumbs Breadcrumbs array
     * @param string $separator Separator
     * @param string $wrapper_class Wrapper class
     * @param array $settings Settings
     * @return string HTML
     */
    private function generate_html($breadcrumbs, $separator, $wrapper_class, $settings) {
        $html = '<nav class="' . esc_attr($wrapper_class) . '" aria-label="' . esc_attr__('Breadcrumb', 'aiseo') . '">';
        
        // Schema markup
        if ($settings['schema_enabled']) {
            $html .= '<ol itemscope itemtype="https://schema.org/BreadcrumbList">';
        } else {
            $html .= '<ol>';
        }
        
        $count = count($breadcrumbs);
        $position = 1;
        
        foreach ($breadcrumbs as $index => $crumb) {
            $is_last = ($index === $count - 1);
            
            // Skip current item if disabled
            if ($is_last && !$settings['show_current']) {
                continue;
            }
            
            if ($settings['schema_enabled']) {
                $html .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            } else {
                $html .= '<li>';
            }
            
            // Link or span
            if (!empty($crumb['url']) && !$crumb['current']) {
                if ($settings['schema_enabled']) {
                    $html .= '<a itemprop="item" href="' . esc_url($crumb['url']) . '">';
                    $html .= '<span itemprop="name">' . esc_html($crumb['title']) . '</span>';
                    $html .= '</a>';
                } else {
                    $html .= '<a href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['title']) . '</a>';
                }
            } else {
                $title = esc_html($crumb['title']);
                if ($settings['bold_current'] && $crumb['current']) {
                    $title = '<strong>' . $title . '</strong>';
                }
                
                if ($settings['schema_enabled']) {
                    $html .= '<span itemprop="name">' . $title . '</span>';
                } else {
                    $html .= '<span>' . $title . '</span>';
                }
            }
            
            // Schema position
            if ($settings['schema_enabled']) {
                $html .= '<meta itemprop="position" content="' . $position . '">';
            }
            
            $html .= '</li>';
            
            // Separator
            if (!$is_last) {
                $html .= '<li class="separator" aria-hidden="true">' . esc_html($separator) . '</li>';
            }
            
            $position++;
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Get breadcrumbs as array (for REST API)
     *
     * @return array Breadcrumbs data
     */
    public function get_breadcrumbs_array() {
        $settings = $this->get_settings();
        return $this->build_breadcrumbs($settings['home_text']);
    }
}

/**
 * Template function for displaying breadcrumbs
 *
 * @param array $args Optional arguments
 */
function aiseo_breadcrumbs($args = array()) {
    $breadcrumbs = new AISEO_Breadcrumbs();
    $breadcrumbs->display($args);
}
