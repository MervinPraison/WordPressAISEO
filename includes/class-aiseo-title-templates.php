<?php
/**
 * AISEO Global Title Templates
 *
 * Handles global title templates and suffixes for different page types
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Title_Templates {
    
    /**
     * Option key for title templates
     */
    const OPTION_KEY = 'aiseo_title_templates';
    
    /**
     * Default templates
     */
    private $defaults = array(
        'separator' => '|',
        'separator_position' => 'right', // left or right
        'post_title' => '%title% %sep% %sitename%',
        'page_title' => '%title% %sep% %sitename%',
        'category_title' => '%term% %sep% %sitename%',
        'tag_title' => '%term% %sep% %sitename%',
        'archive_title' => '%archive_type% %sep% %sitename%',
        'author_title' => '%author% %sep% %sitename%',
        'search_title' => 'Search Results for "%search%" %sep% %sitename%',
        '404_title' => 'Page Not Found %sep% %sitename%',
        'date_title' => '%date% %sep% %sitename%',
    );
    
    /**
     * Available placeholders
     */
    private $placeholders = array(
        '%title%' => 'Post/Page title',
        '%sitename%' => 'Site name',
        '%tagline%' => 'Site tagline',
        '%sep%' => 'Separator',
        '%term%' => 'Category/Tag name',
        '%author%' => 'Author name',
        '%search%' => 'Search query',
        '%date%' => 'Archive date',
        '%archive_type%' => 'Archive type (e.g., "Archives")',
        '%page%' => 'Page number',
    );
    
    /**
     * Initialize title templates
     */
    public function init() {
        // Filter document title parts
        add_filter('document_title_parts', array($this, 'filter_title_parts'), 15);
        add_filter('document_title_separator', array($this, 'filter_separator'), 10);
    }
    
    /**
     * Get all templates
     *
     * @return array Templates
     */
    public function get_templates() {
        $templates = get_option(self::OPTION_KEY, array());
        return wp_parse_args($templates, $this->defaults);
    }
    
    /**
     * Update templates
     *
     * @param array $templates Templates to update
     * @return bool Success
     */
    public function update_templates($templates) {
        $current = $this->get_templates();
        
        // Sanitize and merge
        foreach ($templates as $key => $value) {
            if (array_key_exists($key, $this->defaults)) {
                $current[$key] = sanitize_text_field($value);
            }
        }
        
        return update_option(self::OPTION_KEY, $current);
    }
    
    /**
     * Get available placeholders
     *
     * @return array Placeholders
     */
    public function get_placeholders() {
        return $this->placeholders;
    }
    
    /**
     * Filter document title separator
     *
     * @param string $sep Current separator
     * @return string Modified separator
     */
    public function filter_separator($sep) {
        $templates = $this->get_templates();
        return !empty($templates['separator']) ? $templates['separator'] : $sep;
    }
    
    /**
     * Filter document title parts
     *
     * @param array $title_parts Title parts
     * @return array Modified title parts
     */
    public function filter_title_parts($title_parts) {
        $templates = $this->get_templates();
        
        // Determine page type and get appropriate template
        $template = $this->get_current_template($templates);
        
        if (empty($template)) {
            return $title_parts;
        }
        
        // Parse template and build title
        $parsed_title = $this->parse_template($template, $title_parts, $templates);
        
        if (!empty($parsed_title)) {
            // Return as single title to override default behavior
            return array('title' => $parsed_title);
        }
        
        return $title_parts;
    }
    
    /**
     * Get template for current page type
     *
     * @param array $templates All templates
     * @return string Template string
     */
    private function get_current_template($templates) {
        if (is_singular('post')) {
            return $templates['post_title'];
        }
        
        if (is_page()) {
            return $templates['page_title'];
        }
        
        if (is_category()) {
            return $templates['category_title'];
        }
        
        if (is_tag()) {
            return $templates['tag_title'];
        }
        
        if (is_author()) {
            return $templates['author_title'];
        }
        
        if (is_search()) {
            return $templates['search_title'];
        }
        
        if (is_404()) {
            return $templates['404_title'];
        }
        
        if (is_date()) {
            return $templates['date_title'];
        }
        
        if (is_archive()) {
            return $templates['archive_title'];
        }
        
        return '';
    }
    
    /**
     * Parse template and replace placeholders
     *
     * @param string $template Template string
     * @param array $title_parts Original title parts
     * @param array $templates All templates
     * @return string Parsed title
     */
    private function parse_template($template, $title_parts, $templates) {
        $replacements = array(
            '%sitename%' => get_bloginfo('name'),
            '%tagline%' => get_bloginfo('description'),
            '%sep%' => $templates['separator'],
        );
        
        // Get title based on context
        if (isset($title_parts['title'])) {
            $replacements['%title%'] = $title_parts['title'];
        }
        
        // Term name for taxonomies
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            if ($term) {
                $replacements['%term%'] = $term->name;
            }
        }
        
        // Author name
        if (is_author()) {
            $author = get_queried_object();
            if ($author) {
                $replacements['%author%'] = $author->display_name;
            }
        }
        
        // Search query
        if (is_search()) {
            $replacements['%search%'] = get_search_query();
        }
        
        // Date archives
        if (is_date()) {
            if (is_year()) {
                $replacements['%date%'] = get_the_date('Y');
            } elseif (is_month()) {
                $replacements['%date%'] = get_the_date('F Y');
            } elseif (is_day()) {
                $replacements['%date%'] = get_the_date();
            }
        }
        
        // Archive type
        if (is_archive()) {
            $replacements['%archive_type%'] = post_type_archive_title('', false) ?: __('Archives', 'aiseo');
        }
        
        // Page number
        $page = get_query_var('paged', 0);
        if ($page > 1) {
            $replacements['%page%'] = sprintf(__('Page %d', 'aiseo'), $page);
        } else {
            $replacements['%page%'] = '';
        }
        
        // Replace placeholders
        $title = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
        
        // Clean up multiple separators and spaces
        $sep = preg_quote($templates['separator'], '/');
        $title = preg_replace('/\s*' . $sep . '\s*' . $sep . '\s*/', ' ' . $templates['separator'] . ' ', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title, ' ' . $templates['separator']);
        
        return $title;
    }
}
