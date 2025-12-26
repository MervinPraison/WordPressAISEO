<?php
/**
 * Plugin Name: AI SEO
 * Plugin URI: https://github.com/MervinPraison/WordPressAISEO
 * Description: AI-powered SEO optimization for WordPress. Generate meta descriptions, titles, schema markup, and comprehensive SEO analysis using OpenAI.
 * Version: 5.0.6
 * Author: MervinPraison
 * Author URI: https://mer.vin
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: aiseo
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AISEO_VERSION', '5.0.6');

// CRITICAL FIX: Register AJAX actions IMMEDIATELY, before any hooks
if (is_admin() && defined('DOING_AJAX') && DOING_AJAX) {
    require_once dirname(__FILE__) . '/admin/class-aiseo-admin.php';
    if (class_exists('AISEO_Admin')) {
        new AISEO_Admin();
    }
}
define('AISEO_PLUGIN_FILE', __FILE__);
define('AISEO_PLUGIN_DIR', dirname(__FILE__) . '/');
define('AISEO_PLUGIN_URL', function_exists('plugin_dir_url') ? plugin_dir_url(__FILE__) : '');

// Load .env file if it exists
if (file_exists(AISEO_PLUGIN_DIR . '.env')) {
    $aiseo_env_file = file_get_contents(AISEO_PLUGIN_DIR . '.env');
    $aiseo_env_lines = explode("\n", $aiseo_env_file);
    
    foreach ($aiseo_env_lines as $aiseo_line) {
        $aiseo_line = trim($aiseo_line);
        if (empty($aiseo_line) || strpos($aiseo_line, '#') === 0) {
            continue;
        }
        
        if (strpos($aiseo_line, '=') !== false) {
            list($aiseo_key, $aiseo_value) = explode('=', $aiseo_line, 2);
            $aiseo_key = trim($aiseo_key);
            $aiseo_value = trim($aiseo_value);
            
            // Remove quotes if present
            $aiseo_value = trim($aiseo_value, '"\'');
            
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound -- Dynamic constant from .env file
            if (!defined($aiseo_key)) {
                define($aiseo_key, $aiseo_value);
            }
            
            // Also set as environment variable
            putenv("$aiseo_key=$aiseo_value");
        }
    }
}

/**
 * Autoloader for plugin classes
 */
spl_autoload_register(function ($class) {
    // Only autoload our classes
    if (strpos($class, 'AISEO_') !== 0) {
        return;
    }
    
    // Convert class name to file name
    $class_file = 'class-' . strtolower(str_replace('_', '-', $class)) . '.php';
    $file_path = AISEO_PLUGIN_DIR . 'includes/' . $class_file;
    
    if (file_exists($file_path)) {
        require_once $file_path;
    }
});

/**
 * Plugin activation hook
 */
function aiseo_activate() {
    // Load required classes
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-helpers.php';
    
    // Set default options
    $defaults = array(
        'aiseo_api_model' => 'gpt-4o-mini',
        'aiseo_api_timeout' => 45,
        'aiseo_api_max_tokens' => 1000,
        'aiseo_api_temperature' => '0.7',  // Store as string, convert to float when using
        'aiseo_enable_sitemap' => true,
        'aiseo_enable_schema' => true,
        'aiseo_enable_social_tags' => true,
        'aiseo_auto_generate' => false,
        'aiseo_enable_image_alt' => true,
        'aiseo_rate_limit_per_minute' => 10,
        'aiseo_rate_limit_per_hour' => 60,
        'aiseo_monthly_token_limit' => 0,
        'aiseo_version' => AISEO_VERSION,
        'aiseo_install_date' => current_time('mysql'),
    );
    
    foreach ($defaults as $key => $value) {
        if (get_option($key) === false) {
            add_option($key, $value);
        }
    }
    
    // Generate encryption keys if not exists
    if (!defined('AISEO_ENCRYPTION_KEY')) {
        AISEO_Helpers::generate_encryption_keys();
    }
    
    // Create custom tables
    aiseo_create_tables();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'aiseo_activate');

/**
 * Plugin deactivation hook
 */
function aiseo_deactivate() {
    // Clear scheduled cron jobs
    wp_clear_scheduled_hook('aiseo_cache_warming');
    wp_clear_scheduled_hook('aiseo_process_queue');
    wp_clear_scheduled_hook('aiseo_cleanup_logs');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'aiseo_deactivate');

/**
 * Create custom database tables
 */
function aiseo_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Logs table
    $table_name = $wpdb->prefix . 'aiseo_logs';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        timestamp DATETIME NOT NULL,
        level VARCHAR(20) NOT NULL,
        category VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        context LONGTEXT,
        user_id BIGINT(20) UNSIGNED,
        post_id BIGINT(20) UNSIGNED,
        trace_id VARCHAR(36),
        PRIMARY KEY (id),
        KEY timestamp (timestamp),
        KEY level (level),
        KEY category (category),
        KEY user_id (user_id),
        KEY trace_id (trace_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Failed requests table
    $table_name = $wpdb->prefix . 'aiseo_failed_requests';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        timestamp DATETIME NOT NULL,
        request_type VARCHAR(50) NOT NULL,
        post_id BIGINT(20) UNSIGNED,
        user_id BIGINT(20) UNSIGNED,
        content LONGTEXT,
        error_message TEXT,
        error_code VARCHAR(20),
        retry_count INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'pending',
        last_retry_at DATETIME,
        PRIMARY KEY (id),
        KEY timestamp (timestamp),
        KEY status (status),
        KEY post_id (post_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Usage statistics table
    $table_name = $wpdb->prefix . 'aiseo_usage_stats';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        date DATE NOT NULL,
        request_type VARCHAR(50) NOT NULL,
        requests_count INT DEFAULT 0,
        tokens_used INT DEFAULT 0,
        avg_response_time INT,
        success_count INT DEFAULT 0,
        error_count INT DEFAULT 0,
        cache_hits INT DEFAULT 0,
        cache_misses INT DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY date_type (date, request_type),
        KEY date (date)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Request queue table
    $table_name = $wpdb->prefix . 'aiseo_request_queue';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at DATETIME NOT NULL,
        scheduled_at DATETIME NOT NULL,
        priority VARCHAR(20) DEFAULT 'normal',
        request_type VARCHAR(50) NOT NULL,
        post_id BIGINT(20) UNSIGNED,
        user_id BIGINT(20) UNSIGNED,
        request_data LONGTEXT,
        status VARCHAR(20) DEFAULT 'queued',
        processed_at DATETIME,
        result LONGTEXT,
        PRIMARY KEY (id),
        KEY scheduled_at (scheduled_at),
        KEY status (status),
        KEY priority (priority)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Rank tracking table
    $table_name = $wpdb->prefix . 'aiseo_rank_tracking';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) UNSIGNED DEFAULT 0,
        keyword VARCHAR(255) NOT NULL,
        position INT NOT NULL,
        url VARCHAR(500) NOT NULL,
        date DATETIME NOT NULL,
        location VARCHAR(100) DEFAULT 'US',
        serp_features TEXT,
        PRIMARY KEY (id),
        KEY keyword_date (keyword(191), date),
        KEY post_id (post_id),
        KEY location (location)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 404 errors log table
    $table_name = $wpdb->prefix . 'aiseo_404_log';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        url VARCHAR(500) NOT NULL,
        referrer VARCHAR(500),
        user_agent VARCHAR(255),
        ip_address VARCHAR(45),
        timestamp DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY url (url(191)),
        KEY timestamp (timestamp)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Redirects table
    $table_name = $wpdb->prefix . 'aiseo_redirects';
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        source_url VARCHAR(500) NOT NULL,
        target_url VARCHAR(500) NOT NULL,
        redirect_type VARCHAR(10) DEFAULT '301',
        is_regex TINYINT(1) DEFAULT 0,
        hits INT DEFAULT 0,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY source_url (source_url(191))
    ) $charset_collate;";
    dbDelta($sql);
}

/**
 * Initialize the plugin
 */
function aiseo_init() {
    // Initialize core plugin class
    if (class_exists('AISEO_Core')) {
        $aiseo = new AISEO_Core();
        $aiseo->init();
    }
    
    // Initialize admin interface
    if (is_admin() && file_exists(AISEO_PLUGIN_DIR . 'admin/class-aiseo-admin.php')) {
        require_once AISEO_PLUGIN_DIR . 'admin/class-aiseo-admin.php';
        if (class_exists('AISEO_Admin')) {
            new AISEO_Admin();
        }
    }
}
add_action('init', 'aiseo_init', 1); // Priority 1 to run early

/**
 * Register WP-CLI commands
 */
if (defined('WP_CLI') && WP_CLI) {
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-image-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-advanced-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-bulk-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-import-export-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-multilingual-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-cpt-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-competitor-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-keyword-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-backlink-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-rank-tracker-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-internal-linking.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-internal-linking-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-content-suggestions.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-content-suggestions-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-redirects.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-redirects-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-permalink-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-readability-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-faq-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-outline-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-rewriter-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-meta-variations-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-post-creator.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-post-creator-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-homepage-seo.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-homepage-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-taxonomy-seo.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-taxonomy-cli.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-webmaster.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-analytics.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-title-templates.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-robots.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-breadcrumbs.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-rss.php';
    require_once AISEO_PLUGIN_DIR . 'includes/class-aiseo-importer.php';
}
