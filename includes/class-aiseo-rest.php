<?php
/**
 * AISEO REST API Handler
 *
 * Provides REST API endpoints for testing and integration
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_REST {
    
    /**
     * API namespace
     */
    const NAMESPACE = 'aiseo/v1';
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Status endpoint
        register_rest_route(self::NAMESPACE, '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_status'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Validate API key endpoint
        register_rest_route(self::NAMESPACE, '/validate-key', array(
            'methods' => 'GET',
            'callback' => array($this, 'validate_api_key'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Generate meta description endpoint
        register_rest_route(self::NAMESPACE, '/generate/description', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_description'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'content' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'keyword' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // Generate SEO title endpoint
        register_rest_route(self::NAMESPACE, '/generate/title', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_title'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'content' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'keyword' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // Analyze content endpoint
        register_rest_route(self::NAMESPACE, '/analyze', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_content'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_id' => array(
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'content' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
            ),
        ));
        
        // Generate metadata for post endpoint
        register_rest_route(self::NAMESPACE, '/generate/post/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_post_metadata'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'meta_types' => array(
                    'required' => false,
                    'type' => 'array',
                    'default' => array('title', 'description'),
                ),
            ),
        ));
        
        // Get schema markup for post endpoint
        register_rest_route(self::NAMESPACE, '/schema/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_schema'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'auto',
                    'enum' => array('auto', 'article', 'blogposting', 'webpage'),
                ),
            ),
        ));
        
        // Get meta tags for post endpoint
        register_rest_route(self::NAMESPACE, '/meta-tags/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_meta_tags'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Get social media tags for post endpoint
        register_rest_route(self::NAMESPACE, '/social-tags/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_social_tags'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Get sitemap statistics endpoint
        register_rest_route(self::NAMESPACE, '/sitemap/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_sitemap_stats'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Homepage SEO: Get settings
        register_rest_route(self::NAMESPACE, '/homepage-seo', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_homepage_seo'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Homepage SEO: Update settings
        register_rest_route(self::NAMESPACE, '/homepage-seo', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_homepage_seo'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'home_title' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'home_description' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'home_keywords' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'blog_title' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'blog_description' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'blog_keywords' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // Taxonomy SEO: Get term SEO settings
        register_rest_route(self::NAMESPACE, '/taxonomy-seo/(?P<taxonomy>[a-z0-9_-]+)/(?P<term_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_taxonomy_seo'),
            'permission_callback' => array($this, 'check_edit_term_permission'),
            'args' => array(
                'taxonomy' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_key',
                ),
                'term_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Taxonomy SEO: Update term SEO settings
        register_rest_route(self::NAMESPACE, '/taxonomy-seo/(?P<taxonomy>[a-z0-9_-]+)/(?P<term_id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_taxonomy_seo'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'taxonomy' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_key',
                ),
                'term_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'title' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'description' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'keywords' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'canonical' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'esc_url_raw',
                ),
                'noindex' => array(
                    'required' => false,
                    'type' => 'boolean',
                ),
                'nofollow' => array(
                    'required' => false,
                    'type' => 'boolean',
                ),
            ),
        ));
        
        // Taxonomy SEO: List all terms with SEO for a taxonomy
        register_rest_route(self::NAMESPACE, '/taxonomy-seo/(?P<taxonomy>[a-z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'list_taxonomy_seo'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'taxonomy' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_key',
                ),
            ),
        ));
        
        // Webmaster Verification: Get codes
        register_rest_route(self::NAMESPACE, '/webmaster-verification', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_webmaster_verification'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Webmaster Verification: Update codes
        register_rest_route(self::NAMESPACE, '/webmaster-verification', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_webmaster_verification'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'google' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'bing' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'yandex' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'pinterest' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'baidu' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // Analytics: Get settings
        register_rest_route(self::NAMESPACE, '/analytics', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_analytics_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Analytics: Update settings
        register_rest_route(self::NAMESPACE, '/analytics', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_analytics_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'tracking_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'enabled' => array(
                    'required' => false,
                    'type' => 'boolean',
                ),
                'anonymize_ip' => array(
                    'required' => false,
                    'type' => 'boolean',
                ),
                'track_admin' => array(
                    'required' => false,
                    'type' => 'boolean',
                ),
                'track_logged_in' => array(
                    'required' => false,
                    'type' => 'boolean',
                ),
            ),
        ));
        
        // Title Templates: Get templates
        register_rest_route(self::NAMESPACE, '/title-templates', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_title_templates'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Title Templates: Update templates
        register_rest_route(self::NAMESPACE, '/title-templates', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_title_templates'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Robots Settings: Get settings
        register_rest_route(self::NAMESPACE, '/robots-settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_robots_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Robots Settings: Update settings
        register_rest_route(self::NAMESPACE, '/robots-settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_robots_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Breadcrumbs: Get settings
        register_rest_route(self::NAMESPACE, '/breadcrumbs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_breadcrumbs_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Breadcrumbs: Update settings
        register_rest_route(self::NAMESPACE, '/breadcrumbs', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_breadcrumbs_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // RSS: Get settings
        register_rest_route(self::NAMESPACE, '/rss', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_rss_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // RSS: Update settings
        register_rest_route(self::NAMESPACE, '/rss', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_rss_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Importer: Check for old plugin data
        register_rest_route(self::NAMESPACE, '/import/check', array(
            'methods' => 'GET',
            'callback' => array($this, 'check_import_data'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Importer: Get preview
        register_rest_route(self::NAMESPACE, '/import/preview', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_import_preview'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Importer: Run import
        register_rest_route(self::NAMESPACE, '/import/run', array(
            'methods' => 'POST',
            'callback' => array($this, 'run_import'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Importer: Cleanup old data
        register_rest_route(self::NAMESPACE, '/import/cleanup', array(
            'methods' => 'POST',
            'callback' => array($this, 'cleanup_import_data'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Image SEO: Generate alt text for image
        register_rest_route(self::NAMESPACE, '/image/generate-alt/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_image_alt'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Image SEO: Get images missing alt text
        register_rest_route(self::NAMESPACE, '/image/missing-alt', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_missing_alt'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'per_page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 100,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Image SEO: Get image SEO score
        register_rest_route(self::NAMESPACE, '/image/seo-score/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_image_seo_score'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Advanced Analysis: Comprehensive SEO analysis (40+ factors)
        register_rest_route(self::NAMESPACE, '/analyze/advanced/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'analyze_advanced'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'keyword' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // Bulk Edit: Get posts for bulk editing
        register_rest_route(self::NAMESPACE, '/bulk/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_bulk_posts'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 50,
                ),
            ),
        ));
        
        // Bulk Edit: Update multiple posts
        register_rest_route(self::NAMESPACE, '/bulk/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_update_posts'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'updates' => array(
                    'required' => true,
                    'type' => 'array',
                ),
            ),
        ));
        
        // Bulk Edit: Generate metadata for multiple posts
        register_rest_route(self::NAMESPACE, '/bulk/generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_generate_metadata'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_ids' => array(
                    'required' => true,
                    'type' => 'array',
                ),
                'meta_types' => array(
                    'required' => false,
                    'type' => 'array',
                    'default' => array('title', 'description'),
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Bulk Edit: Preview changes
        register_rest_route(self::NAMESPACE, '/bulk/preview', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_preview_changes'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'updates' => array(
                    'required' => true,
                    'type' => 'array',
                ),
            ),
        ));
        
        // Post Creator: Create AI-generated post
        register_rest_route(self::NAMESPACE, '/post/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_ai_post'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'topic' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'title' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'keyword' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'post_status' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'draft',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'content_length' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'medium',
                    'enum' => array('short', 'medium', 'long'),
                ),
                'generate_seo' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => true,
                ),
                'category' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'tags' => array(
                    'required' => false,
                    'type' => 'array',
                ),
            ),
        ));
        
        // Post Creator: Bulk create posts
        register_rest_route(self::NAMESPACE, '/post/bulk-create', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_create_ai_posts'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'posts' => array(
                    'required' => true,
                    'type' => 'array',
                ),
            ),
        ));
        
        // Post Creator: Get statistics
        register_rest_route(self::NAMESPACE, '/post/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_post_creator_stats'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Import/Export: Export to JSON
        register_rest_route(self::NAMESPACE, '/export/json', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_json'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
            ),
        ));
        
        // Import/Export: Export to CSV
        register_rest_route(self::NAMESPACE, '/export/csv', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_csv'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
            ),
        ));
        
        // Import/Export: Import from JSON
        register_rest_route(self::NAMESPACE, '/import/json', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_json'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'object',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Import/Export: Import from Yoast
        register_rest_route(self::NAMESPACE, '/import/yoast', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_yoast'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Import/Export: Import from Rank Math
        register_rest_route(self::NAMESPACE, '/import/rankmath', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_rankmath'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Import/Export: Import from AIOSEO
        register_rest_route(self::NAMESPACE, '/import/aioseo', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_aioseo'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Multilingual: Get active plugin
        register_rest_route(self::NAMESPACE, '/multilingual/plugin', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_multilingual_plugin'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Multilingual: Get languages
        register_rest_route(self::NAMESPACE, '/multilingual/languages', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_multilingual_languages'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Multilingual: Get post translations
        register_rest_route(self::NAMESPACE, '/multilingual/translations/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_post_translations'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),
        ));
        
        // Multilingual: Get hreflang tags
        register_rest_route(self::NAMESPACE, '/multilingual/hreflang/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_hreflang_tags'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),
        ));
        
        // Multilingual: Sync metadata
        register_rest_route(self::NAMESPACE, '/multilingual/sync/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'sync_multilingual_metadata'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // CPT: Get all custom post types
        register_rest_route(self::NAMESPACE, '/cpt/list', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cpt_list'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // CPT: Get supported post types
        register_rest_route(self::NAMESPACE, '/cpt/supported', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_supported_cpt'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // CPT: Enable post type
        register_rest_route(self::NAMESPACE, '/cpt/enable', array(
            'methods' => 'POST',
            'callback' => array($this, 'enable_cpt'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // CPT: Disable post type
        register_rest_route(self::NAMESPACE, '/cpt/disable', array(
            'methods' => 'POST',
            'callback' => array($this, 'disable_cpt'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // CPT: Get posts by type
        register_rest_route(self::NAMESPACE, '/cpt/posts/(?P<post_type>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cpt_posts'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 20,
                ),
            ),
        ));
        
        // CPT: Get statistics
        register_rest_route(self::NAMESPACE, '/cpt/stats/(?P<post_type>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cpt_stats'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // CPT: Bulk generate
        register_rest_route(self::NAMESPACE, '/cpt/bulk-generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_generate_cpt'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => -1,
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Competitor: Get all competitors
        register_rest_route(self::NAMESPACE, '/competitor/list', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_competitors_list'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Competitor: Add competitor
        register_rest_route(self::NAMESPACE, '/competitor/add', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_competitor'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'url' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'name' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => '',
                ),
            ),
        ));
        
        // Competitor: Remove competitor
        register_rest_route(self::NAMESPACE, '/competitor/remove/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'remove_competitor'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Competitor: Analyze competitor
        register_rest_route(self::NAMESPACE, '/competitor/analyze/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_competitor'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Competitor: Get analysis
        register_rest_route(self::NAMESPACE, '/competitor/analysis/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_competitor_analysis'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Competitor: Compare with site
        register_rest_route(self::NAMESPACE, '/competitor/compare/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'compare_competitor'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'post_id' => array(
                    'required' => false,
                    'type' => 'integer',
                ),
            ),
        ));
        
        // Competitor: Get summary
        register_rest_route(self::NAMESPACE, '/competitor/summary', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_competitor_summary'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Keyword Research: Get suggestions
        register_rest_route(self::NAMESPACE, '/keyword/suggestions', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_keyword_suggestions'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'seed_keyword' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 20,
                ),
            ),
        ));
        
        // Keyword Research: Get related keywords
        register_rest_route(self::NAMESPACE, '/keyword/related', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_related_keywords'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'keyword' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10,
                ),
            ),
        ));
        
        // Keyword Research: Analyze difficulty
        register_rest_route(self::NAMESPACE, '/keyword/difficulty', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_keyword_difficulty'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'keyword' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Keyword Research: Get question keywords
        register_rest_route(self::NAMESPACE, '/keyword/questions', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_question_keywords'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'topic' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 15,
                ),
            ),
        ));
        
        // Keyword Research: Analyze trends
        register_rest_route(self::NAMESPACE, '/keyword/trends', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_keyword_trends'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'keyword' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Keyword Research: Get summary
        register_rest_route(self::NAMESPACE, '/keyword/summary', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_keyword_summary'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Keyword Research: Clear cache
        register_rest_route(self::NAMESPACE, '/keyword/clear-cache', array(
            'methods' => 'POST',
            'callback' => array($this, 'clear_keyword_cache'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Backlink Monitoring: List all backlinks
        register_rest_route(self::NAMESPACE, '/backlink/list', array(
            'methods' => 'GET',
            'callback' => array($this, 'list_backlinks'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'status' => array(
                    'required' => false,
                    'type' => 'string',
                ),
                'target_url' => array(
                    'required' => false,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Backlink Monitoring: Add backlink
        register_rest_route(self::NAMESPACE, '/backlink/add', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_backlink'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'source_url' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'target_url' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'anchor_text' => array(
                    'required' => false,
                    'type' => 'string',
                ),
                'follow' => array(
                    'required' => false,
                    'type' => 'boolean',
                ),
            ),
        ));
        
        // Backlink Monitoring: Remove backlink
        register_rest_route(self::NAMESPACE, '/backlink/remove/(?P<id>[a-zA-Z0-9_]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'remove_backlink'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Backlink Monitoring: Check backlink status
        register_rest_route(self::NAMESPACE, '/backlink/check/(?P<id>[a-zA-Z0-9_]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'check_backlink_status'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Backlink Monitoring: Analyze backlink quality
        register_rest_route(self::NAMESPACE, '/backlink/analyze/(?P<id>[a-zA-Z0-9_]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_backlink_quality'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Backlink Monitoring: Get new backlinks
        register_rest_route(self::NAMESPACE, '/backlink/new', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_new_backlinks'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'days' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 7,
                ),
            ),
        ));
        
        // Backlink Monitoring: Get lost backlinks
        register_rest_route(self::NAMESPACE, '/backlink/lost', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_lost_backlinks'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Backlink Monitoring: Generate disavow file
        register_rest_route(self::NAMESPACE, '/backlink/disavow', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_disavow_file'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'backlink_ids' => array(
                    'required' => true,
                    'type' => 'array',
                ),
            ),
        ));
        
        // Backlink Monitoring: Bulk check
        register_rest_route(self::NAMESPACE, '/backlink/bulk-check', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_check_backlinks'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        
        // Backlink Monitoring: Get summary
        register_rest_route(self::NAMESPACE, '/backlink/summary', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_backlink_summary'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Rank Tracking: Track keyword
        register_rest_route(self::NAMESPACE, '/rank/track', array(
            'methods' => 'POST',
            'callback' => array($this, 'track_keyword'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'keyword' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'post_id' => array(
                    'required' => false,
                    'type' => 'integer',
                ),
                'location' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'US',
                ),
            ),
        ));
        
        // Rank Tracking: Get position history
        register_rest_route(self::NAMESPACE, '/rank/history/(?P<keyword>[^/]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_rank_history'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'days' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 30,
                ),
            ),
        ));
        
        // Rank Tracking: Get ranking keywords for post
        register_rest_route(self::NAMESPACE, '/rank/keywords/(?P<post_id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_ranking_keywords'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
        ));
        
        // Rank Tracking: Compare with competitor
        register_rest_route(self::NAMESPACE, '/rank/compare', array(
            'methods' => 'POST',
            'callback' => array($this, 'compare_rank'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'keyword' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'competitor_url' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Rank Tracking: Detect SERP features
        register_rest_route(self::NAMESPACE, '/rank/serp-features', array(
            'methods' => 'POST',
            'callback' => array($this, 'detect_serp_features'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'keyword' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Rank Tracking: Get all keywords
        register_rest_route(self::NAMESPACE, '/rank/keywords', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_all_tracked_keywords'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'post_id' => array(
                    'required' => false,
                    'type' => 'integer',
                ),
                'location' => array(
                    'required' => false,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Rank Tracking: Delete keyword
        register_rest_route(self::NAMESPACE, '/rank/delete', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_tracked_keyword'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'keyword' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'post_id' => array(
                    'required' => false,
                    'type' => 'integer',
                ),
            ),
        ));
        
        // Rank Tracking: Get summary
        register_rest_route(self::NAMESPACE, '/rank/summary', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_rank_summary'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Internal Linking: Get suggestions
        register_rest_route(self::NAMESPACE, '/internal-linking/suggestions/(?P<post_id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_internal_linking_suggestions'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
        ));
        
        // Internal Linking: Detect orphans
        register_rest_route(self::NAMESPACE, '/internal-linking/orphans', array(
            'methods' => 'GET',
            'callback' => array($this, 'detect_orphan_pages'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Internal Linking: Analyze distribution
        register_rest_route(self::NAMESPACE, '/internal-linking/distribution/(?P<post_id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'analyze_link_distribution'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
        ));
        
        // Internal Linking: Get opportunities
        register_rest_route(self::NAMESPACE, '/internal-linking/opportunities/(?P<post_id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_link_opportunities'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
        ));
        
        // Content Suggestions: Get topic suggestions
        register_rest_route(self::NAMESPACE, '/content/topics', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_content_topics'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Content Suggestions: Get optimization tips
        register_rest_route(self::NAMESPACE, '/content/optimize/(?P<post_id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_optimization_tips'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
        ));
        
        // Content Suggestions: Get trending topics
        register_rest_route(self::NAMESPACE, '/content/trending', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_trending_topics'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Content Suggestions: Generate content brief
        register_rest_route(self::NAMESPACE, '/content/brief', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_content_brief'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Content Suggestions: Analyze content gaps
        register_rest_route(self::NAMESPACE, '/content/gaps', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_content_gaps'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // 404 Monitor: Get 404 errors
        register_rest_route(self::NAMESPACE, '/404/errors', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_404_errors'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // 404 Monitor: Suggest redirect
        register_rest_route(self::NAMESPACE, '/404/suggest', array(
            'methods' => 'POST',
            'callback' => array($this, 'suggest_redirect'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Redirects: Create redirect
        register_rest_route(self::NAMESPACE, '/redirects/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_redirect'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Redirects: Get all redirects
        register_rest_route(self::NAMESPACE, '/redirects/list', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_redirects'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Redirects: Delete redirect
        register_rest_route(self::NAMESPACE, '/redirects/delete/(?P<id>\\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_redirect'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Redirects: Bulk import
        register_rest_route(self::NAMESPACE, '/redirects/import', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_redirects'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Redirects: Export
        register_rest_route(self::NAMESPACE, '/redirects/export', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_redirects'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Redirects: Statistics
        register_rest_route(self::NAMESPACE, '/redirects/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_redirect_stats'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Permalink Optimization: Optimize permalink
        register_rest_route(self::NAMESPACE, '/permalink/optimize', array(
            'methods' => 'POST',
            'callback' => array($this, 'optimize_permalink'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'post_id' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
                'apply' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Enhanced Readability: Analyze readability
        register_rest_route(self::NAMESPACE, '/readability/analyze/(?P<post_id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'analyze_readability'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
        ));
        
        // FAQ Generator: Generate FAQs
        register_rest_route(self::NAMESPACE, '/faq/generate/(?P<post_id>\\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_faqs'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'count' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 5,
                ),
                'save' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // FAQ Generator: Get FAQs
        register_rest_route(self::NAMESPACE, '/faq/get/(?P<post_id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'generate_faqs'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
        ));
        
        // Content Outline: Generate outline
        register_rest_route(self::NAMESPACE, '/outline/generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_outline'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'topic' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'keyword' => array(
                    'required' => false,
                    'type' => 'string',
                ),
                'word_count' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 1500,
                ),
                'save' => array(
                    'required' => false,
                    'type' => 'boolean',
                ),
                'post_id' => array(
                    'required' => false,
                    'type' => 'integer',
                ),
            ),
        ));
        
        // Content Rewriter: Rewrite content
        register_rest_route(self::NAMESPACE, '/rewrite/content', array(
            'methods' => 'POST',
            'callback' => array($this, 'rewrite_content'),
            'permission_callback' => array($this, 'check_admin_permission'),
            'args' => array(
                'content' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'mode' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'improve',
                ),
                'keyword' => array(
                    'required' => false,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Meta Variations: Generate variations
        register_rest_route(self::NAMESPACE, '/meta/variations/(?P<post_id>\\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_meta_variations'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'count' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 5,
                ),
                'save' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Unified Report: Generate comprehensive SEO report
        register_rest_route(self::NAMESPACE, '/report/unified/(?P<id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_unified_report'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'force_refresh' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Unified Report: Get historical reports
        register_rest_route(self::NAMESPACE, '/report/history/(?P<id>\\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_report_history'),
            'permission_callback' => array($this, 'check_edit_post_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10,
                ),
            ),
        ));
    }
    
    /**
     * Get plugin status
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_status($request) {
        $api_key = AISEO_Helpers::get_api_key();
        
        return new WP_REST_Response(array(
            'success' => true,
            'version' => AISEO_VERSION,
            'api_key_configured' => !empty($api_key),
            'model' => get_option('aiseo_api_model', 'gpt-4o-mini'),
            'features' => array(
                'sitemap' => get_option('aiseo_enable_sitemap', true),
                'schema' => get_option('aiseo_enable_schema', true),
                'social_tags' => get_option('aiseo_enable_social_tags', true),
            ),
        ), 200);
    }
    
    /**
     * Validate API key
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function validate_api_key($request) {
        $api = new AISEO_API();
        $result = $api->check_api_key();
        
        if (is_wp_error($result)) {
            return new WP_REST_Response(array(
                'success' => false,
                'valid' => false,
                'message' => $result->get_error_message(),
            ), 200);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'valid' => true,
            'message' => 'API key is valid',
        ), 200);
    }
    
    /**
     * Generate meta description
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function generate_description($request) {
        $content = $request->get_param('content');
        $keyword = $request->get_param('keyword');
        
        $api = new AISEO_API();
        $description = $api->generate_meta_description($content, $keyword);
        
        if (is_wp_error($description)) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => $description->get_error_message(),
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'description' => $description,
            'length' => strlen($description),
        ), 200);
    }
    
    /**
     * Generate SEO title
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function generate_title($request) {
        $content = $request->get_param('content');
        $keyword = $request->get_param('keyword');
        
        $api = new AISEO_API();
        $title = $api->generate_title($content, $keyword);
        
        if (is_wp_error($title)) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => $title->get_error_message(),
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'title' => $title,
            'length' => strlen($title),
        ), 200);
    }
    
    /**
     * Analyze content
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function analyze_content($request) {
        $post_id = $request->get_param('post_id');
        $keyword = $request->get_param('keyword');
        
        if (!$post_id) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post ID is required',
            ), 400);
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        // Use comprehensive analysis engine
        $analyzer = new AISEO_Analysis();
        $analysis = $analyzer->analyze_post($post_id, $keyword);
        
        if (isset($analysis['error'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => $analysis['error'],
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'score' => $analysis['overall_score'],
            'status' => $analysis['status'],
            'analyses' => $analysis['analyses'],
            'timestamp' => $analysis['timestamp'],
        ), 200);
    }
    
    /**
     * Generate metadata for a post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function generate_post_metadata($request) {
        $post_id = $request->get_param('id');
        $meta_types = $request->get_param('meta_types');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        $api = new AISEO_API();
        $content = $post->post_content;
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        $results = array();
        
        if (in_array('title', $meta_types)) {
            $title = $api->generate_title($content, $keyword);
            if (!is_wp_error($title)) {
                update_post_meta($post_id, '_aiseo_meta_title', $title);
                $results['title'] = $title;
            }
        }
        
        if (in_array('description', $meta_types)) {
            $description = $api->generate_meta_description($content, $keyword);
            if (!is_wp_error($description)) {
                update_post_meta($post_id, '_aiseo_meta_description', $description);
                $results['description'] = $description;
            }
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'post_id' => $post_id,
            'generated' => $results,
        ), 200);
    }
    
    /**
     * Get sitemap statistics
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_sitemap_stats($request) {
        $sitemap_generator = new AISEO_Sitemap();
        $stats = $sitemap_generator->get_sitemap_stats();
        
        return new WP_REST_Response(array(
            'success' => true,
            'stats' => $stats,
            'sitemap_url' => home_url('/sitemap.xml'),
        ), 200);
    }
    
    /**
     * Get homepage SEO settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_homepage_seo($request) {
        $homepage_seo = new AISEO_Homepage_SEO();
        $settings = $homepage_seo->get_settings();
        
        return new WP_REST_Response(array(
            'success' => true,
            'settings' => $settings,
        ), 200);
    }
    
    /**
     * Update homepage SEO settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function update_homepage_seo($request) {
        $homepage_seo = new AISEO_Homepage_SEO();
        
        $settings = array();
        
        if ($request->has_param('home_title')) {
            $settings['home_title'] = $request->get_param('home_title');
        }
        if ($request->has_param('home_description')) {
            $settings['home_description'] = $request->get_param('home_description');
        }
        if ($request->has_param('home_keywords')) {
            $settings['home_keywords'] = $request->get_param('home_keywords');
        }
        if ($request->has_param('blog_title')) {
            $settings['blog_title'] = $request->get_param('blog_title');
        }
        if ($request->has_param('blog_description')) {
            $settings['blog_description'] = $request->get_param('blog_description');
        }
        if ($request->has_param('blog_keywords')) {
            $settings['blog_keywords'] = $request->get_param('blog_keywords');
        }
        
        $updated = $homepage_seo->update_settings($settings);
        
        return new WP_REST_Response(array(
            'success' => true,
            'updated' => $updated,
            'settings' => $homepage_seo->get_settings(),
        ), 200);
    }
    
    /**
     * Check admin permission
     *
     * @param WP_REST_Request $request Request object.
     * @return bool|WP_Error
     */
    public function check_admin_permission($request) {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'aiseo'),
                array('status' => 401)
            );
        }

        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to access this endpoint.', 'aiseo'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Check permission to access or modify a specific post.
     *
     * @param WP_REST_Request $request Request object.
     * @return bool|WP_Error
     */
    public function check_edit_post_permission($request) {
        $base = $this->check_permission($request);
        if (true !== $base) {
            return $base;
        }

        $post_id = absint($request->get_param('id') ?: $request->get_param('post_id'));
        if (!$post_id) {
            return new WP_Error(
                'rest_forbidden',
                __('A valid post ID is required.', 'aiseo'),
                array('status' => 403)
            );
        }

        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to access this post.', 'aiseo'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Check permission to access or modify taxonomy term SEO settings.
     *
     * @param WP_REST_Request $request Request object.
     * @return bool|WP_Error
     */
    public function check_edit_term_permission($request) {
        $base = $this->check_permission($request);
        if (true !== $base) {
            return $base;
        }

        $taxonomy = sanitize_key($request->get_param('taxonomy'));
        $term_id = absint($request->get_param('term_id'));

        if (!$taxonomy || !$term_id) {
            return new WP_Error(
                'rest_forbidden',
                __('A valid taxonomy and term ID are required.', 'aiseo'),
                array('status' => 403)
            );
        }

        if (!current_user_can('edit_term', $term_id)) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to access this term.', 'aiseo'),
                array('status' => 403)
            );
        }

        return true;
    }
    
    /**
     * Get taxonomy SEO settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_taxonomy_seo($request) {
        $taxonomy = $request->get_param('taxonomy');
        $term_id = $request->get_param('term_id');
        
        // Verify taxonomy exists
        if (!taxonomy_exists($taxonomy)) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Invalid taxonomy',
            ), 400);
        }
        
        // Verify term exists
        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Term not found',
            ), 404);
        }
        
        $taxonomy_seo = new AISEO_Taxonomy_SEO();
        $meta = $taxonomy_seo->get_term_meta($term_id, $taxonomy);
        
        return new WP_REST_Response(array(
            'success' => true,
            'term_id' => $term_id,
            'taxonomy' => $taxonomy,
            'term_name' => $term->name,
            'term_slug' => $term->slug,
            'seo' => $meta,
        ), 200);
    }
    
    /**
     * Update taxonomy SEO settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function update_taxonomy_seo($request) {
        $taxonomy = $request->get_param('taxonomy');
        $term_id = $request->get_param('term_id');
        
        // Verify taxonomy exists
        if (!taxonomy_exists($taxonomy)) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Invalid taxonomy',
            ), 400);
        }
        
        // Verify term exists
        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Term not found',
            ), 404);
        }
        
        $taxonomy_seo = new AISEO_Taxonomy_SEO();
        
        $meta = array();
        
        if ($request->has_param('title')) {
            $meta['title'] = $request->get_param('title');
        }
        if ($request->has_param('description')) {
            $meta['description'] = $request->get_param('description');
        }
        if ($request->has_param('keywords')) {
            $meta['keywords'] = $request->get_param('keywords');
        }
        if ($request->has_param('canonical')) {
            $meta['canonical'] = $request->get_param('canonical');
        }
        if ($request->has_param('noindex')) {
            $meta['noindex'] = $request->get_param('noindex');
        }
        if ($request->has_param('nofollow')) {
            $meta['nofollow'] = $request->get_param('nofollow');
        }
        
        $updated = $taxonomy_seo->update_term_meta($term_id, $taxonomy, $meta);
        
        return new WP_REST_Response(array(
            'success' => true,
            'updated' => $updated,
            'term_id' => $term_id,
            'taxonomy' => $taxonomy,
            'seo' => $taxonomy_seo->get_term_meta($term_id, $taxonomy),
        ), 200);
    }
    
    /**
     * List all terms with SEO for a taxonomy
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function list_taxonomy_seo($request) {
        $taxonomy = $request->get_param('taxonomy');
        
        // Verify taxonomy exists
        if (!taxonomy_exists($taxonomy)) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Invalid taxonomy',
            ), 400);
        }
        
        $taxonomy_seo = new AISEO_Taxonomy_SEO();
        $terms = $taxonomy_seo->get_taxonomy_terms_with_meta($taxonomy);
        
        return new WP_REST_Response(array(
            'success' => true,
            'taxonomy' => $taxonomy,
            'count' => count($terms),
            'terms' => $terms,
        ), 200);
    }
    
    /**
     * Get webmaster verification codes
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_webmaster_verification($request) {
        $webmaster = new AISEO_Webmaster();
        $codes = $webmaster->get_verification_codes();
        
        return new WP_REST_Response(array(
            'success' => true,
            'codes' => $codes,
        ), 200);
    }
    
    /**
     * Update webmaster verification codes
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function update_webmaster_verification($request) {
        $webmaster = new AISEO_Webmaster();
        
        $codes = array();
        
        if ($request->has_param('google')) {
            $codes['google'] = $request->get_param('google');
        }
        if ($request->has_param('bing')) {
            $codes['bing'] = $request->get_param('bing');
        }
        if ($request->has_param('yandex')) {
            $codes['yandex'] = $request->get_param('yandex');
        }
        if ($request->has_param('pinterest')) {
            $codes['pinterest'] = $request->get_param('pinterest');
        }
        if ($request->has_param('baidu')) {
            $codes['baidu'] = $request->get_param('baidu');
        }
        
        $updated = $webmaster->update_verification_codes($codes);
        
        return new WP_REST_Response(array(
            'success' => true,
            'updated' => $updated,
            'codes' => $webmaster->get_verification_codes(),
        ), 200);
    }
    
    /**
     * Get analytics settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_analytics_settings($request) {
        $analytics = new AISEO_Analytics();
        $settings = $analytics->get_settings();
        
        return new WP_REST_Response(array(
            'success' => true,
            'settings' => $settings,
        ), 200);
    }
    
    /**
     * Update analytics settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function update_analytics_settings($request) {
        $analytics = new AISEO_Analytics();
        
        $settings = array();
        
        if ($request->has_param('tracking_id')) {
            $settings['tracking_id'] = $request->get_param('tracking_id');
        }
        if ($request->has_param('enabled')) {
            $settings['enabled'] = $request->get_param('enabled');
        }
        if ($request->has_param('anonymize_ip')) {
            $settings['anonymize_ip'] = $request->get_param('anonymize_ip');
        }
        if ($request->has_param('track_admin')) {
            $settings['track_admin'] = $request->get_param('track_admin');
        }
        if ($request->has_param('track_logged_in')) {
            $settings['track_logged_in'] = $request->get_param('track_logged_in');
        }
        
        $updated = $analytics->update_settings($settings);
        
        return new WP_REST_Response(array(
            'success' => true,
            'updated' => $updated,
            'settings' => $analytics->get_settings(),
        ), 200);
    }
    
    /**
     * Get title templates
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_title_templates($request) {
        $title_templates = new AISEO_Title_Templates();
        
        return new WP_REST_Response(array(
            'success' => true,
            'templates' => $title_templates->get_templates(),
            'placeholders' => $title_templates->get_placeholders(),
        ), 200);
    }
    
    /**
     * Update title templates
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function update_title_templates($request) {
        $title_templates = new AISEO_Title_Templates();
        
        $templates = $request->get_json_params();
        if (empty($templates)) {
            $templates = $request->get_body_params();
        }
        
        $updated = $title_templates->update_templates($templates);
        
        return new WP_REST_Response(array(
            'success' => true,
            'updated' => $updated,
            'templates' => $title_templates->get_templates(),
        ), 200);
    }
    
    /**
     * Get robots settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_robots_settings($request) {
        $robots = new AISEO_Robots();
        
        return new WP_REST_Response(array(
            'success' => true,
            'settings' => $robots->get_settings(),
        ), 200);
    }
    
    /**
     * Update robots settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function update_robots_settings($request) {
        $robots = new AISEO_Robots();
        
        $settings = $request->get_json_params();
        if (empty($settings)) {
            $settings = $request->get_body_params();
        }
        
        $updated = $robots->update_settings($settings);
        
        return new WP_REST_Response(array(
            'success' => true,
            'updated' => $updated,
            'settings' => $robots->get_settings(),
        ), 200);
    }
    
    /**
     * Get breadcrumbs settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_breadcrumbs_settings($request) {
        $breadcrumbs = new AISEO_Breadcrumbs();
        
        return new WP_REST_Response(array(
            'success' => true,
            'settings' => $breadcrumbs->get_settings(),
        ), 200);
    }
    
    /**
     * Update breadcrumbs settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function update_breadcrumbs_settings($request) {
        $breadcrumbs = new AISEO_Breadcrumbs();
        
        $settings = $request->get_json_params();
        if (empty($settings)) {
            $settings = $request->get_body_params();
        }
        
        $updated = $breadcrumbs->update_settings($settings);
        
        return new WP_REST_Response(array(
            'success' => true,
            'updated' => $updated,
            'settings' => $breadcrumbs->get_settings(),
        ), 200);
    }
    
    /**
     * Get RSS settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_rss_settings($request) {
        $rss = new AISEO_RSS();
        
        return new WP_REST_Response(array(
            'success' => true,
            'settings' => $rss->get_settings(),
            'placeholders' => $rss->get_placeholders(),
        ), 200);
    }
    
    /**
     * Update RSS settings
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function update_rss_settings($request) {
        $rss = new AISEO_RSS();
        
        $settings = $request->get_json_params();
        if (empty($settings)) {
            $settings = $request->get_body_params();
        }
        
        $updated = $rss->update_settings($settings);
        
        return new WP_REST_Response(array(
            'success' => true,
            'updated' => $updated,
            'settings' => $rss->get_settings(),
        ), 200);
    }
    
    /**
     * Check for old plugin import data
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function check_import_data($request) {
        $importer = new AISEO_Importer();
        
        return new WP_REST_Response(array(
            'success' => true,
            'has_data' => $importer->has_old_plugin_data(),
        ), 200);
    }
    
    /**
     * Get import preview
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_import_preview($request) {
        $importer = new AISEO_Importer();
        
        return new WP_REST_Response(array(
            'success' => true,
            'preview' => $importer->get_import_preview(),
        ), 200);
    }
    
    /**
     * Run import from old plugin
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function run_import($request) {
        $importer = new AISEO_Importer();
        $overwrite = $request->get_param('overwrite');
        
        $results = $importer->import_all($overwrite);
        
        return new WP_REST_Response(array(
            'success' => true,
            'results' => $results,
        ), 200);
    }
    
    /**
     * Cleanup old plugin data
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function cleanup_import_data($request) {
        $importer = new AISEO_Importer();
        
        $results = $importer->cleanup_old_data();
        
        return new WP_REST_Response(array(
            'success' => true,
            'results' => $results,
        ), 200);
    }
    
    /**
     * Get social media tags for post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_social_tags($request) {
        $post_id = $request->get_param('id');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        $social_handler = new AISEO_Social();
        $social_tags = $social_handler->get_social_tags($post_id);
        
        return new WP_REST_Response(array(
            'success' => true,
            'post_id' => $post_id,
            'social_tags' => $social_tags,
        ), 200);
    }
    
    /**
     * Get meta tags for post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_meta_tags($request) {
        $post_id = $request->get_param('id');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        $meta_handler = new AISEO_Meta();
        $meta_tags = $meta_handler->get_meta_tags($post_id);
        
        return new WP_REST_Response(array(
            'success' => true,
            'post_id' => $post_id,
            'meta_tags' => $meta_tags,
        ), 200);
    }
    
    /**
     * Get schema markup for post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_schema($request) {
        $post_id = $request->get_param('id');
        $schema_type = $request->get_param('type');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        $schema_generator = new AISEO_Schema();
        $schema = $schema_generator->generate_schema($post_id, $schema_type);
        
        if (!$schema) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Failed to generate schema',
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'post_id' => $post_id,
            'schema_type' => $schema_type,
            'schema' => $schema,
        ), 200);
    }
    
    /**
     * Generate alt text for image (Image SEO)
     */
    public function generate_image_alt($request) {
        $image_id = $request->get_param('id');
        $overwrite = $request->get_param('overwrite');
        
        $image_seo = new AISEO_Image_SEO();
        $alt_text = $image_seo->generate_alt_text($image_id, ['overwrite' => $overwrite]);
        
        if (is_wp_error($alt_text)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $alt_text->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'image_id' => $image_id,
            'alt_text' => $alt_text
        ], 200);
    }
    
    /**
     * Get images missing alt text (Image SEO)
     */
    public function get_missing_alt($request) {
        $per_page = $request->get_param('per_page') ?: 100;
        
        $image_seo = new AISEO_Image_SEO();
        $images = $image_seo->detect_missing_alt_text(['posts_per_page' => $per_page]);
        
        return new WP_REST_Response([
            'success' => true,
            'count' => count($images),
            'images' => $images
        ], 200);
    }
    
    /**
     * Get image SEO score (Image SEO)
     */
    public function get_image_seo_score($request) {
        $image_id = $request->get_param('id');
        
        $image_seo = new AISEO_Image_SEO();
        $score_data = $image_seo->analyze_image_seo($image_id);
        
        return new WP_REST_Response([
            'success' => true,
            'image_id' => $image_id,
            'score_data' => $score_data
        ], 200);
    }
    
    /**
     * Advanced SEO analysis (40+ factors)
     */
    public function analyze_advanced($request) {
        $post_id = $request->get_param('id');
        $keyword = $request->get_param('keyword');
        
        $advanced_analysis = new AISEO_Advanced_Analysis();
        $result = $advanced_analysis->analyze_comprehensive($post_id, $keyword);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get posts for bulk editing
     */
    public function get_bulk_posts($request) {
        $post_type = $request->get_param('post_type');
        $limit = $request->get_param('limit');
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $posts = $bulk_edit->get_posts_for_editing([
            'post_type' => $post_type,
            'posts_per_page' => $limit
        ]);
        
        return new WP_REST_Response([
            'success' => true,
            'total' => count($posts),
            'posts' => $posts
        ], 200);
    }
    
    /**
     * Bulk update posts
     */
    public function bulk_update_posts($request) {
        $updates = $request->get_param('updates');
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $result = $bulk_edit->bulk_update($updates);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Bulk generate metadata
     */
    public function bulk_generate_metadata($request) {
        $post_ids = $request->get_param('post_ids');
        $meta_types = $request->get_param('meta_types');
        $overwrite = $request->get_param('overwrite');
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $result = $bulk_edit->bulk_generate($post_ids, [
            'meta_types' => $meta_types,
            'overwrite' => $overwrite
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Preview bulk changes
     */
    public function bulk_preview_changes($request) {
        $updates = $request->get_param('updates');
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $preview = $bulk_edit->preview_changes($updates);
        
        if (is_wp_error($preview)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $preview->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'preview' => $preview
        ], 200);
    }
    
    /**
     * Export to JSON
     */
    public function export_json($request) {
        $post_type = $request->get_param('post_type');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->export_to_json(['post_type' => $post_type]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Export to CSV
     */
    public function export_csv($request) {
        $post_type = $request->get_param('post_type');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->export_to_csv(['post_type' => $post_type]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result,
            'content_type' => 'text/csv'
        ], 200);
    }
    
    /**
     * Import from JSON
     */
    public function import_json($request) {
        $data = $request->get_param('data');
        $overwrite = $request->get_param('overwrite');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->import_from_json($data, ['overwrite' => $overwrite]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Import from Yoast SEO
     */
    public function import_yoast($request) {
        $post_type = $request->get_param('post_type');
        $overwrite = $request->get_param('overwrite');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->import_from_yoast([
            'post_type' => $post_type,
            'overwrite' => $overwrite
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Import from Rank Math
     */
    public function import_rankmath($request) {
        $post_type = $request->get_param('post_type');
        $overwrite = $request->get_param('overwrite');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->import_from_rankmath([
            'post_type' => $post_type,
            'overwrite' => $overwrite
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Import from AIOSEO
     */
    public function import_aioseo($request) {
        $post_type = $request->get_param('post_type');
        $overwrite = $request->get_param('overwrite');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->import_from_aioseo([
            'post_type' => $post_type,
            'overwrite' => $overwrite
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get active multilingual plugin
     */
    public function get_multilingual_plugin($request) {
        $multilingual = new AISEO_Multilingual();
        $plugin = $multilingual->get_active_plugin();
        
        return new WP_REST_Response([
            'success' => true,
            'plugin' => $plugin,
            'supported_plugins' => ['wpml', 'polylang', 'translatepress']
        ], 200);
    }
    
    /**
     * Get multilingual languages
     */
    public function get_multilingual_languages($request) {
        $multilingual = new AISEO_Multilingual();
        $languages = $multilingual->get_languages();
        
        return new WP_REST_Response([
            'success' => true,
            'plugin' => $multilingual->get_active_plugin(),
            'languages' => $languages,
            'count' => count($languages)
        ], 200);
    }
    
    /**
     * Get post translations
     */
    public function get_post_translations($request) {
        $post_id = $request->get_param('id');
        
        $multilingual = new AISEO_Multilingual();
        $translations = $multilingual->get_post_translations($post_id);
        
        return new WP_REST_Response([
            'success' => true,
            'post_id' => $post_id,
            'translations' => $translations,
            'count' => count($translations)
        ], 200);
    }
    
    /**
     * Get hreflang tags for a post
     */
    public function get_hreflang_tags($request) {
        $post_id = $request->get_param('id');
        
        $multilingual = new AISEO_Multilingual();
        $hreflang_tags = $multilingual->generate_hreflang_tags($post_id);
        
        return new WP_REST_Response([
            'success' => true,
            'post_id' => $post_id,
            'hreflang_tags' => $hreflang_tags,
            'count' => count($hreflang_tags)
        ], 200);
    }
    
    /**
     * Sync metadata across translations
     */
    public function sync_multilingual_metadata($request) {
        $post_id = $request->get_param('id');
        $overwrite = $request->get_param('overwrite');
        
        $multilingual = new AISEO_Multilingual();
        $result = $multilingual->sync_metadata_across_translations($post_id, $overwrite);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get custom post types list
     */
    public function get_cpt_list($request) {
        $cpt = new AISEO_CPT();
        $post_types = $cpt->get_custom_post_types();
        
        return new WP_REST_Response([
            'success' => true,
            'post_types' => $post_types,
            'count' => count($post_types)
        ], 200);
    }
    
    /**
     * Get supported custom post types
     */
    public function get_supported_cpt($request) {
        $cpt = new AISEO_CPT();
        $supported = $cpt->get_supported_post_types();
        
        return new WP_REST_Response([
            'success' => true,
            'supported_post_types' => $supported,
            'count' => count($supported)
        ], 200);
    }
    
    /**
     * Enable custom post type
     */
    public function enable_cpt($request) {
        $post_type = $request->get_param('post_type');
        
        $cpt = new AISEO_CPT();
        $result = $cpt->enable_post_type($post_type);
        
        if ($result) {
            return new WP_REST_Response([
                'success' => true,
                'message' => sprintf('SEO enabled for post type: %s', $post_type)
            ], 200);
        }
        
        return new WP_REST_Response([
            'success' => false,
            'message' => sprintf('Post type %s already enabled', $post_type)
        ], 400);
    }
    
    /**
     * Disable custom post type
     */
    public function disable_cpt($request) {
        $post_type = $request->get_param('post_type');
        
        $cpt = new AISEO_CPT();
        $result = $cpt->disable_post_type($post_type);
        
        if ($result) {
            return new WP_REST_Response([
                'success' => true,
                'message' => sprintf('SEO disabled for post type: %s', $post_type)
            ], 200);
        }
        
        return new WP_REST_Response([
            'success' => false,
            'message' => sprintf('Post type %s not found in supported list', $post_type)
        ], 400);
    }
    
    /**
     * Get posts by custom post type
     */
    public function get_cpt_posts($request) {
        $post_type = $request->get_param('post_type');
        $limit = $request->get_param('limit');
        
        $cpt = new AISEO_CPT();
        $posts = $cpt->get_posts_by_type($post_type, ['posts_per_page' => $limit]);
        
        return new WP_REST_Response([
            'success' => true,
            'post_type' => $post_type,
            'posts' => $posts,
            'count' => count($posts)
        ], 200);
    }
    
    /**
     * Get statistics for custom post type
     */
    public function get_cpt_stats($request) {
        $post_type = $request->get_param('post_type');
        
        $cpt = new AISEO_CPT();
        $stats = $cpt->get_post_type_stats($post_type);
        
        return new WP_REST_Response([
            'success' => true,
            'post_type' => $post_type,
            'stats' => $stats
        ], 200);
    }
    
    /**
     * Bulk generate metadata for custom post type
     */
    public function bulk_generate_cpt($request) {
        $post_type = $request->get_param('post_type');
        $limit = $request->get_param('limit');
        $overwrite = $request->get_param('overwrite');
        
        $cpt = new AISEO_CPT();
        $result = $cpt->bulk_generate_for_type($post_type, [
            'limit' => $limit,
            'overwrite' => $overwrite
        ]);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get competitors list
     */
    public function get_competitors_list($request) {
        $competitor = new AISEO_Competitor();
        $competitors = $competitor->get_competitors();
        
        return new WP_REST_Response([
            'success' => true,
            'competitors' => $competitors,
            'count' => count($competitors)
        ], 200);
    }
    
    /**
     * Add competitor
     */
    public function add_competitor($request) {
        $url = $request->get_param('url');
        $name = $request->get_param('name');
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->add_competitor($url, $name);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'competitor_id' => $result,
            'message' => 'Competitor added successfully'
        ], 200);
    }
    
    /**
     * Remove competitor
     */
    public function remove_competitor($request) {
        $id = $request->get_param('id');
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->remove_competitor($id);
        
        if ($result) {
            return new WP_REST_Response([
                'success' => true,
                'message' => 'Competitor removed successfully'
            ], 200);
        }
        
        return new WP_REST_Response([
            'success' => false,
            'error' => 'Competitor not found'
        ], 404);
    }
    
    /**
     * Analyze competitor
     */
    public function analyze_competitor($request) {
        $id = $request->get_param('id');
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->analyze_competitor($id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'analysis' => $result,
            'message' => 'Competitor analyzed successfully'
        ], 200);
    }
    
    /**
     * Get competitor analysis
     */
    public function get_competitor_analysis($request) {
        $id = $request->get_param('id');
        
        $competitor = new AISEO_Competitor();
        $analysis = $competitor->get_analysis($id);
        
        if (!$analysis) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'No analysis data found'
            ], 404);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'analysis' => $analysis
        ], 200);
    }
    
    /**
     * Compare with site
     */
    public function compare_competitor($request) {
        $id = $request->get_param('id');
        $post_id = $request->get_param('post_id');
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->compare_with_site($id, $post_id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'comparison' => $result
        ], 200);
    }
    
    /**
     * Get competitor summary
     */
    public function get_competitor_summary($request) {
        $competitor = new AISEO_Competitor();
        $summary = $competitor->get_summary();
        
        return new WP_REST_Response([
            'success' => true,
            'summary' => $summary
        ], 200);
    }
    
    /**
     * Get keyword suggestions
     */
    public function get_keyword_suggestions($request) {
        $seed_keyword = $request->get_param('seed_keyword');
        $limit = $request->get_param('limit') ?: 20;
        
        $keyword_research = new AISEO_Keyword_Research();
        $result = $keyword_research->get_keyword_suggestions($seed_keyword, $limit);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'keywords' => $result,
            'count' => count($result)
        ], 200);
    }
    
    /**
     * Get related keywords
     */
    public function get_related_keywords($request) {
        $keyword = $request->get_param('keyword');
        $limit = $request->get_param('limit') ?: 10;
        
        $keyword_research = new AISEO_Keyword_Research();
        $result = $keyword_research->get_related_keywords($keyword, $limit);
        
        return new WP_REST_Response([
            'success' => true,
            'related_keywords' => $result,
            'count' => count($result)
        ], 200);
    }
    
    /**
     * Analyze keyword difficulty
     */
    public function analyze_keyword_difficulty($request) {
        $keyword = $request->get_param('keyword');
        
        $keyword_research = new AISEO_Keyword_Research();
        $result = $keyword_research->analyze_keyword_difficulty($keyword);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'analysis' => $result
        ], 200);
    }
    
    /**
     * Get question keywords
     */
    public function get_question_keywords($request) {
        $topic = $request->get_param('topic');
        $limit = $request->get_param('limit') ?: 15;
        
        $keyword_research = new AISEO_Keyword_Research();
        $result = $keyword_research->get_question_keywords($topic, $limit);
        
        return new WP_REST_Response([
            'success' => true,
            'questions' => $result,
            'count' => count($result)
        ], 200);
    }
    
    /**
     * Analyze keyword trends
     */
    public function analyze_keyword_trends($request) {
        $keyword = $request->get_param('keyword');
        
        $keyword_research = new AISEO_Keyword_Research();
        $result = $keyword_research->analyze_keyword_trends($keyword);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'trends' => $result
        ], 200);
    }
    
    /**
     * Get keyword summary
     */
    public function get_keyword_summary($request) {
        $keyword_research = new AISEO_Keyword_Research();
        $summary = $keyword_research->get_summary();
        
        return new WP_REST_Response([
            'success' => true,
            'summary' => $summary
        ], 200);
    }
    
    /**
     * Clear keyword cache
     */
    public function clear_keyword_cache($request) {
        $keyword_research = new AISEO_Keyword_Research();
        $deleted = $keyword_research->clear_cache();
        
        return new WP_REST_Response([
            'success' => true,
            'deleted' => $deleted,
            'message' => sprintf('%d cache entries cleared', $deleted)
        ], 200);
    }
    
    /**
     * List backlinks
     */
    public function list_backlinks($request) {
        $status = $request->get_param('status');
        $target_url = $request->get_param('target_url');
        
        $filters = [];
        if ($status) {
            $filters['status'] = $status;
        }
        if ($target_url) {
            $filters['target_url'] = $target_url;
        }
        
        $backlink = new AISEO_Backlink();
        $backlinks = $backlink->get_backlinks($filters);
        
        return new WP_REST_Response([
            'success' => true,
            'backlinks' => $backlinks,
            'count' => count($backlinks)
        ], 200);
    }
    
    /**
     * Add backlink
     */
    public function add_backlink($request) {
        $source_url = $request->get_param('source_url');
        $target_url = $request->get_param('target_url');
        $anchor_text = $request->get_param('anchor_text');
        $follow = $request->get_param('follow');
        
        $options = [];
        if ($anchor_text) {
            $options['anchor_text'] = $anchor_text;
        }
        if ($follow !== null) {
            $options['follow'] = $follow;
        }
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->add_backlink($source_url, $target_url, $options);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'backlink' => $result
        ], 201);
    }
    
    /**
     * Remove backlink
     */
    public function remove_backlink($request) {
        $backlink_id = $request->get_param('id');
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->remove_backlink($backlink_id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 404);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Backlink removed successfully'
        ], 200);
    }
    
    /**
     * Check backlink status
     */
    public function check_backlink_status($request) {
        $backlink_id = $request->get_param('id');
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->check_backlink_status($backlink_id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'status' => $result
        ], 200);
    }
    
    /**
     * Analyze backlink quality
     */
    public function analyze_backlink_quality($request) {
        $backlink_id = $request->get_param('id');
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->analyze_backlink_quality($backlink_id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'analysis' => $result
        ], 200);
    }
    
    /**
     * Get new backlinks
     */
    public function get_new_backlinks($request) {
        $days = $request->get_param('days') ?: 7;
        
        $backlink = new AISEO_Backlink();
        $backlinks = $backlink->get_new_backlinks($days);
        
        return new WP_REST_Response([
            'success' => true,
            'backlinks' => $backlinks,
            'count' => count($backlinks),
            'days' => $days
        ], 200);
    }
    
    /**
     * Get lost backlinks
     */
    public function get_lost_backlinks($request) {
        $backlink = new AISEO_Backlink();
        $backlinks = $backlink->get_lost_backlinks();
        
        return new WP_REST_Response([
            'success' => true,
            'backlinks' => $backlinks,
            'count' => count($backlinks)
        ], 200);
    }
    
    /**
     * Generate disavow file
     */
    public function generate_disavow_file($request) {
        $backlink_ids = $request->get_param('backlink_ids');
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->generate_disavow_file($backlink_ids);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'disavow_file' => $result,
            'count' => count($backlink_ids)
        ], 200);
    }
    
    /**
     * Bulk check backlinks
     */
    public function bulk_check_backlinks($request) {
        $backlink = new AISEO_Backlink();
        $results = $backlink->bulk_check_backlinks();
        
        return new WP_REST_Response([
            'success' => true,
            'results' => $results
        ], 200);
    }
    
    /**
     * Get backlink summary
     */
    public function get_backlink_summary($request) {
        $backlink = new AISEO_Backlink();
        $summary = $backlink->get_summary();
        
        return new WP_REST_Response([
            'success' => true,
            'summary' => $summary
        ], 200);
    }
    
    /**
     * Track keyword rank
     */
    public function track_keyword($request) {
        $keyword = $request->get_param('keyword');
        $post_id = $request->get_param('post_id') ?: 0;
        $location = $request->get_param('location') ?: 'US';
        
        $tracker = new AISEO_Rank_Tracker();
        $result = $tracker->track_keyword($keyword, $post_id, $location);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'tracking' => $result
        ], 201);
    }
    
    /**
     * Get rank history
     */
    public function get_rank_history($request) {
        $keyword = urldecode($request->get_param('keyword'));
        $days = $request->get_param('days') ?: 30;
        
        $tracker = new AISEO_Rank_Tracker();
        $history = $tracker->get_position_history($keyword, $days);
        
        return new WP_REST_Response([
            'success' => true,
            'keyword' => $keyword,
            'days' => $days,
            'history' => $history,
            'count' => count($history)
        ], 200);
    }
    
    /**
     * Get ranking keywords for post
     */
    public function get_ranking_keywords($request) {
        $post_id = $request->get_param('post_id');
        
        $tracker = new AISEO_Rank_Tracker();
        $keywords = $tracker->get_ranking_keywords($post_id);
        
        return new WP_REST_Response([
            'success' => true,
            'post_id' => $post_id,
            'keywords' => $keywords,
            'count' => count($keywords)
        ], 200);
    }
    
    /**
     * Compare rank with competitor
     */
    public function compare_rank($request) {
        $keyword = $request->get_param('keyword');
        $competitor_url = $request->get_param('competitor_url');
        
        $tracker = new AISEO_Rank_Tracker();
        $result = $tracker->compare_with_competitor($keyword, $competitor_url);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'comparison' => $result
        ], 200);
    }
    
    /**
     * Detect SERP features
     */
    public function detect_serp_features($request) {
        $keyword = $request->get_param('keyword');
        
        $tracker = new AISEO_Rank_Tracker();
        $result = $tracker->detect_serp_features($keyword);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'keyword' => $keyword,
            'serp_features' => $result
        ], 200);
    }
    
    /**
     * Get all tracked keywords
     */
    public function get_all_tracked_keywords($request) {
        $post_id = $request->get_param('post_id');
        $location = $request->get_param('location');
        
        $filters = [];
        if ($post_id) {
            $filters['post_id'] = $post_id;
        }
        if ($location) {
            $filters['location'] = $location;
        }
        
        $tracker = new AISEO_Rank_Tracker();
        $keywords = $tracker->get_all_keywords($filters);
        
        return new WP_REST_Response([
            'success' => true,
            'keywords' => $keywords,
            'count' => count($keywords)
        ], 200);
    }
    
    /**
     * Delete tracked keyword
     */
    public function delete_tracked_keyword($request) {
        $keyword = $request->get_param('keyword');
        $post_id = $request->get_param('post_id') ?: 0;
        
        $tracker = new AISEO_Rank_Tracker();
        $result = $tracker->delete_keyword($keyword, $post_id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Keyword tracking deleted successfully'
        ], 200);
    }
    
    /**
     * Get rank tracking summary
     */
    public function get_rank_summary($request) {
        $tracker = new AISEO_Rank_Tracker();
        $summary = $tracker->get_summary();
        
        return new WP_REST_Response([
            'success' => true,
            'summary' => $summary
        ], 200);
    }
    
    /**
     * Get internal linking suggestions
     */
    public function get_internal_linking_suggestions($request) {
        $post_id = $request->get_param('post_id');
        $limit = $request->get_param('limit') ?: 5;
        $exclude_ids = $request->get_param('exclude_ids') ?: [];
        
        $linking = new AISEO_Internal_Linking();
        $result = $linking->get_suggestions($post_id, [
            'limit' => $limit,
            'exclude_ids' => $exclude_ids
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Detect orphan pages
     */
    public function detect_orphan_pages($request) {
        $post_type = $request->get_param('post_type') ?: 'post';
        $limit = $request->get_param('limit') ?: 50;
        
        $linking = new AISEO_Internal_Linking();
        $result = $linking->detect_orphans([
            'post_type' => $post_type,
            'limit' => $limit
        ]);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Analyze link distribution
     */
    public function analyze_link_distribution($request) {
        $post_id = $request->get_param('post_id');
        
        $linking = new AISEO_Internal_Linking();
        $result = $linking->analyze_distribution($post_id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get link opportunities
     */
    public function get_link_opportunities($request) {
        $post_id = $request->get_param('post_id');
        
        $linking = new AISEO_Internal_Linking();
        $result = $linking->get_opportunities($post_id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get content topic suggestions
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_content_topics($request) {
        $niche = $request->get_param('niche');
        $keywords = $request->get_param('keywords');
        $count = $request->get_param('count') ?: 10;
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->get_topic_suggestions([
            'niche' => $niche,
            'keywords' => is_array($keywords) ? $keywords : explode(',', $keywords),
            'count' => absint($count)
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get optimization tips for a post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_optimization_tips($request) {
        $post_id = absint($request->get_param('post_id'));
        $focus_keyword = $request->get_param('focus_keyword');
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->get_optimization_tips($post_id, [
            'focus_keyword' => $focus_keyword
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get trending topics
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_trending_topics($request) {
        $niche = $request->get_param('niche');
        $count = $request->get_param('count') ?: 10;
        $timeframe = $request->get_param('timeframe') ?: 'week';
        
        if (empty($niche)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'Niche parameter is required'
            ], 400);
        }
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->get_trending_topics($niche, [
            'count' => absint($count),
            'timeframe' => $timeframe
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Generate content brief
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function generate_content_brief($request) {
        $topic = $request->get_param('topic');
        $focus_keyword = $request->get_param('focus_keyword');
        $target_audience = $request->get_param('target_audience');
        $word_count = $request->get_param('word_count') ?: 1500;
        
        if (empty($topic)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'Topic parameter is required'
            ], 400);
        }
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->generate_content_brief($topic, [
            'focus_keyword' => $focus_keyword,
            'target_audience' => $target_audience,
            'word_count' => absint($word_count)
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Analyze content gaps
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function analyze_content_gaps($request) {
        $existing_topics = $request->get_param('existing_topics');
        $niche = $request->get_param('niche');
        
        if (empty($existing_topics) || empty($niche)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'Both existing_topics and niche parameters are required'
            ], 400);
        }
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->analyze_content_gaps(
            is_array($existing_topics) ? $existing_topics : explode(',', $existing_topics),
            $niche
        );
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get 404 errors
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_404_errors($request) {
        $limit = $request->get_param('limit') ?: 100;
        $offset = $request->get_param('offset') ?: 0;
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->get_404_errors([
            'limit' => absint($limit),
            'offset' => absint($offset)
        ]);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Suggest redirect for 404 URL
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function suggest_redirect($request) {
        $url = $request->get_param('url');
        
        if (empty($url)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'URL parameter is required'
            ], 400);
        }
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->suggest_redirect($url);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Create redirect
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function create_redirect($request) {
        $source = $request->get_param('source');
        $target = $request->get_param('target');
        $type = $request->get_param('type') ?: '301';
        $is_regex = $request->get_param('is_regex') ?: false;
        
        if (empty($source) || empty($target)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'Source and target URLs are required'
            ], 400);
        }
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->create_redirect($source, $target, $type, [
            'is_regex' => $is_regex
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'redirect_id' => $result
        ], 200);
    }
    
    /**
     * Get all redirects
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_redirects($request) {
        $limit = $request->get_param('limit') ?: 100;
        $offset = $request->get_param('offset') ?: 0;
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->get_redirects([
            'limit' => absint($limit),
            'offset' => absint($offset)
        ]);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Delete redirect
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function delete_redirect($request) {
        $redirect_id = absint($request->get_param('id'));
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->delete_redirect($redirect_id);
        
        if (!$result) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'Failed to delete redirect'
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Redirect deleted successfully'
        ], 200);
    }
    
    /**
     * Import redirects from CSV
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function import_redirects($request) {
        $csv_data = $request->get_param('csv_data');
        
        if (empty($csv_data)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'CSV data is required'
            ], 400);
        }
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->bulk_import_redirects($csv_data);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Export redirects to CSV
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function export_redirects($request) {
        $redirects = new AISEO_Redirects();
        $csv_data = $redirects->export_redirects();
        
        return new WP_REST_Response([
            'success' => true,
            'csv_data' => $csv_data
        ], 200);
    }
    
    /**
     * Get redirect statistics
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_redirect_stats($request) {
        $redirects = new AISEO_Redirects();
        $stats = $redirects->get_statistics();
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $stats
        ], 200);
    }
    
    /**
     * Optimize permalink
     */
    public function optimize_permalink($request) {
        $post_id = $request->get_param('post_id');
        $apply = $request->get_param('apply');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(['success' => false, 'error' => 'Post not found'], 404);
        }
        
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        $permalink = new AISEO_Permalink();
        $result = $permalink->optimize_permalink($post->post_name, $keyword);
        
        if ($apply && $result['optimized'] !== $result['original']) {
            wp_update_post(['ID' => $post_id, 'post_name' => $result['optimized']]);
            $result['applied'] = true;
        }
        
        return new WP_REST_Response(['success' => true, 'data' => $result], 200);
    }
    
    /**
     * Analyze readability
     */
    public function analyze_readability($request) {
        $post_id = $request->get_param('post_id');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(['success' => false, 'error' => 'Post not found'], 404);
        }
        
        $readability = new AISEO_Readability();
        $analysis = $readability->analyze($post->post_content);
        
        return new WP_REST_Response(['success' => true, 'data' => $analysis], 200);
    }
    
    /**
     * Generate FAQs
     */
    public function generate_faqs($request) {
        $post_id = $request->get_param('post_id');
        $count = $request->get_param('count') ?: 5;
        $save = $request->get_param('save');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(['success' => false, 'error' => 'Post not found'], 404);
        }
        
        $faq = new AISEO_FAQ();
        $result = $faq->generate($post->post_content, $count);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response(['success' => false, 'error' => $result->get_error_message()], 500);
        }
        
        if ($save) {
            $faq->save_to_post($post_id, $result['faqs']);
        }
        
        return new WP_REST_Response(['success' => true, 'data' => $result], 200);
    }
    
    /**
     * Generate content outline
     */
    public function generate_outline($request) {
        $topic = $request->get_param('topic');
        $keyword = $request->get_param('keyword') ?: '';
        $word_count = $request->get_param('word_count') ?: 1500;
        $save = $request->get_param('save');
        $post_id = $request->get_param('post_id');
        
        if (empty($topic)) {
            return new WP_REST_Response(['success' => false, 'error' => 'Topic is required'], 400);
        }
        
        $outline = new AISEO_Outline();
        $result = $outline->generate($topic, $keyword, ['word_count' => $word_count]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response(['success' => false, 'error' => $result->get_error_message()], 500);
        }
        
        if ($save && $post_id) {
            $outline->save_to_post($post_id, $result['outline']);
        }
        
        return new WP_REST_Response(['success' => true, 'data' => $result], 200);
    }
    
    /**
     * Rewrite content
     */
    public function rewrite_content($request) {
        $content = $request->get_param('content');
        $mode = $request->get_param('mode') ?: 'improve';
        $keyword = $request->get_param('keyword') ?: '';
        
        if (empty($content)) {
            return new WP_REST_Response(['success' => false, 'error' => 'Content is required'], 400);
        }
        
        $rewriter = new AISEO_Rewriter();
        $result = $rewriter->rewrite($content, ['mode' => $mode, 'keyword' => $keyword]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response(['success' => false, 'error' => $result->get_error_message()], 500);
        }
        
        return new WP_REST_Response(['success' => true, 'data' => $result], 200);
    }
    
    /**
     * Generate meta description variations
     */
    public function generate_meta_variations($request) {
        $post_id = $request->get_param('post_id');
        $count = $request->get_param('count') ?: 5;
        $save = $request->get_param('save');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(['success' => false, 'error' => 'Post not found'], 404);
        }
        
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        $meta_variations = new AISEO_Meta_Variations();
        $result = $meta_variations->generate($post->post_content, $keyword, $count);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response(['success' => false, 'error' => $result->get_error_message()], 500);
        }
        
        if ($save) {
            $meta_variations->save_to_post($post_id, $result['variations']);
        }
        
        return new WP_REST_Response(['success' => true, 'data' => $result], 200);
    }
    
    /**
     * Get unified SEO report for a post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_unified_report($request) {
        $post_id = $request->get_param('id');
        $force_refresh = $request->get_param('force_refresh') ?: false;
        
        if (!class_exists('AISEO_Unified_Report')) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Unified Report class not found'
            ], 500);
        }
        
        $options = [];
        if ($force_refresh) {
            $options['force_refresh'] = true;
        }
        
        $report = AISEO_Unified_Report::generate_report($post_id, $options);
        
        if (isset($report['error'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $report['error']
            ], 404);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'report' => $report
        ], 200);
    }
    
    /**
     * Get historical reports for a post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_report_history($request) {
        $post_id = $request->get_param('id');
        $limit = $request->get_param('limit') ?: 10;
        
        if (!class_exists('AISEO_Unified_Report')) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Unified Report class not found'
            ], 500);
        }
        
        $history = AISEO_Unified_Report::get_history($post_id, $limit);
        
        return new WP_REST_Response([
            'success' => true,
            'post_id' => $post_id,
            'history' => $history,
            'count' => count($history)
        ], 200);
    }
    
    /**
     * Create AI-generated post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function create_ai_post($request) {
        if (!class_exists('AISEO_Post_Creator')) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Post Creator class not found'
            ], 500);
        }
        
        $creator = new AISEO_Post_Creator();
        
        $args = array(
            'topic' => $request->get_param('topic'),
            'title' => $request->get_param('title'),
            'keyword' => $request->get_param('keyword'),
            'post_type' => $request->get_param('post_type'),
            'post_status' => $request->get_param('post_status'),
            'content_length' => $request->get_param('content_length'),
            'generate_seo' => $request->get_param('generate_seo'),
            'category' => $request->get_param('category'),
            'tags' => $request->get_param('tags'),
        );
        
        $result = $creator->create_post($args);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 201);
    }
    
    /**
     * Bulk create AI-generated posts
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function bulk_create_ai_posts($request) {
        if (!class_exists('AISEO_Post_Creator')) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Post Creator class not found'
            ], 500);
        }
        
        $creator = new AISEO_Post_Creator();
        $posts_data = $request->get_param('posts');
        
        if (empty($posts_data) || !is_array($posts_data)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Posts data is required and must be an array'
            ], 400);
        }
        
        $result = $creator->bulk_create_posts($posts_data);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get post creator statistics
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_post_creator_stats($request) {
        if (!class_exists('AISEO_Post_Creator')) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Post Creator class not found'
            ], 500);
        }
        
        $creator = new AISEO_Post_Creator();
        $stats = $creator->get_statistics();
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $stats
        ], 200);
    }
    
    /**
     * Check permission for protected endpoints
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error
     */
    public function check_permission($request) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'aiseo'),
                array('status' => 401)
            );
        }
        
        // Check if user has edit_posts capability (minimum requirement)
        if (!current_user_can('edit_posts')) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to access this endpoint.', 'aiseo'),
                array('status' => 403)
            );
        }
        
        return true;
    }
}
