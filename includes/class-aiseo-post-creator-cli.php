<?php
/**
 * AISEO Post Creator WP-CLI Commands
 *
 * @package AISEO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * Post Creator WP-CLI Commands
 */
class AISEO_Post_Creator_CLI {
    
    /**
     * Create an AI-generated post
     *
     * ## OPTIONS
     *
     * [--topic=<topic>]
     * : Topic for the post
     *
     * [--title=<title>]
     * : Post title (if not provided, will be generated from topic)
     *
     * [--keyword=<keyword>]
     * : Focus keyword for SEO
     *
     * [--post-type=<post-type>]
     * : Post type
     * ---
     * default: post
     * ---
     *
     * [--post-status=<status>]
     * : Post status
     * ---
     * default: draft
     * options:
     *   - draft
     *   - publish
     *   - pending
     * ---
     *
     * [--content-length=<length>]
     * : Content length
     * ---
     * default: medium
     * options:
     *   - short
     *   - medium
     *   - long
     * ---
     *
     * [--category=<category>]
     * : Category name or ID (comma-separated for multiple)
     *
     * [--tags=<tags>]
     * : Tags (comma-separated)
     *
     * [--no-seo]
     * : Skip SEO metadata generation
     *
     * ## EXAMPLES
     *
     *     # Create a post with a topic
     *     wp aiseo post create --topic="10 Best SEO Practices for 2024"
     *
     *     # Create a post with title and keyword
     *     wp aiseo post create --title="Ultimate SEO Guide" --keyword="SEO tips"
     *
     *     # Create a long-form published post
     *     wp aiseo post create --topic="WordPress Performance" --content-length=long --post-status=publish
     *
     *     # Create with categories and tags
     *     wp aiseo post create --topic="AI Content" --category="Technology,AI" --tags="ai,content,automation"
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function create($args, $assoc_args) {
        $topic = WP_CLI\Utils\get_flag_value($assoc_args, 'topic', '');
        $title = WP_CLI\Utils\get_flag_value($assoc_args, 'title', '');
        
        if (empty($topic) && empty($title)) {
            WP_CLI::error('Either --topic or --title is required');
        }
        
        $creator = new AISEO_Post_Creator();
        
        $post_args = array(
            'topic' => $topic,
            'title' => $title,
            'keyword' => WP_CLI\Utils\get_flag_value($assoc_args, 'keyword', ''),
            'post_type' => WP_CLI\Utils\get_flag_value($assoc_args, 'post-type', 'post'),
            'post_status' => WP_CLI\Utils\get_flag_value($assoc_args, 'post-status', 'draft'),
            'content_length' => WP_CLI\Utils\get_flag_value($assoc_args, 'content-length', 'medium'),
            'generate_seo' => !WP_CLI\Utils\get_flag_value($assoc_args, 'no-seo', false),
            'category' => WP_CLI\Utils\get_flag_value($assoc_args, 'category', ''),
            'tags' => WP_CLI\Utils\get_flag_value($assoc_args, 'tags', ''),
        );
        
        WP_CLI::log('Creating AI-generated post...');
        WP_CLI::log('');
        
        $result = $creator->create_post($post_args);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::log('');
        WP_CLI::success('Post created successfully!');
        WP_CLI::log('');
        WP_CLI::log('Post ID: ' . $result['post_id']);
        WP_CLI::log('Title: ' . $result['title']);
        WP_CLI::log('Keyword: ' . $result['keyword']);
        WP_CLI::log('URL: ' . $result['url']);
        WP_CLI::log('Edit URL: ' . $result['edit_url']);
    }
    
    /**
     * Bulk create AI-generated posts from a CSV or JSON file
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to CSV or JSON file containing post data
     *
     * [--format=<format>]
     * : File format
     * ---
     * default: csv
     * options:
     *   - csv
     *   - json
     * ---
     *
     * [--post-status=<status>]
     * : Default post status for all posts
     * ---
     * default: draft
     * ---
     *
     * [--content-length=<length>]
     * : Default content length
     * ---
     * default: medium
     * ---
     *
     * ## EXAMPLES
     *
     *     # Bulk create from CSV
     *     wp aiseo post bulk-create posts.csv
     *
     *     # Bulk create from JSON
     *     wp aiseo post bulk-create posts.json --format=json
     *
     *     # Create as published posts
     *     wp aiseo post bulk-create posts.csv --post-status=publish
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk_create($args, $assoc_args) {
        $file = $args[0];
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'csv');
        $default_status = WP_CLI\Utils\get_flag_value($assoc_args, 'post-status', 'draft');
        $default_length = WP_CLI\Utils\get_flag_value($assoc_args, 'content-length', 'medium');
        
        if (!file_exists($file)) {
            WP_CLI::error("File not found: {$file}");
        }
        
        // Parse file
        if ($format === 'json') {
            $content = file_get_contents($file);
            $posts_data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                WP_CLI::error('Invalid JSON file');
            }
        } else {
            // Parse CSV
            $posts_data = array();
            $handle = fopen($file, 'r');
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                $post = array();
                foreach ($headers as $index => $header) {
                    $post[$header] = isset($row[$index]) ? $row[$index] : '';
                }
                $posts_data[] = $post;
            }
            fclose($handle);
        }
        
        if (empty($posts_data)) {
            WP_CLI::error('No posts found in file');
        }
        
        // Add defaults
        foreach ($posts_data as &$post) {
            if (empty($post['post_status'])) {
                $post['post_status'] = $default_status;
            }
            if (empty($post['content_length'])) {
                $post['content_length'] = $default_length;
            }
        }
        
        WP_CLI::log(sprintf('Found %d posts to create', count($posts_data)));
        WP_CLI::log('');
        
        $creator = new AISEO_Post_Creator();
        $result = $creator->bulk_create_posts($posts_data);
        
        WP_CLI::log('');
        WP_CLI::success(sprintf(
            'Bulk creation complete: %d successful, %d failed',
            $result['success'],
            $result['failed']
        ));
        
        if (!empty($result['errors'])) {
            WP_CLI::log('');
            WP_CLI::log('Errors:');
            foreach ($result['errors'] as $error) {
                WP_CLI::warning(sprintf(
                    'Index %d (%s): %s',
                    $error['index'],
                    $error['topic'],
                    $error['error']
                ));
            }
        }
    }
    
    /**
     * Get statistics about AI-generated posts
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *     # Get statistics
     *     wp aiseo post stats
     *
     *     # Get statistics as JSON
     *     wp aiseo post stats --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function stats($args, $assoc_args) {
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');
        
        $creator = new AISEO_Post_Creator();
        $stats = $creator->get_statistics();
        
        WP_CLI::log('AI-Generated Posts Statistics');
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($stats, JSON_PRETTY_PRINT));
            return;
        }
        
        if ($format === 'yaml') {
            WP_CLI::log(yaml_emit($stats));
            return;
        }
        
        // Table format
        WP_CLI::log('Total AI Posts: ' . $stats['total_ai_posts']);
        WP_CLI::log('');
        
        if (!empty($stats['posts_by_status'])) {
            WP_CLI::log('Posts by Status:');
            $status_table = array();
            foreach ($stats['posts_by_status'] as $status => $count) {
                $status_table[] = array(
                    'Status' => ucfirst($status),
                    'Count' => $count
                );
            }
            WP_CLI\Utils\format_items('table', $status_table, array('Status', 'Count'));
            WP_CLI::log('');
        }
        
        if (!empty($stats['posts_by_type'])) {
            WP_CLI::log('Posts by Type:');
            $type_table = array();
            foreach ($stats['posts_by_type'] as $type => $count) {
                $type_table[] = array(
                    'Type' => $type,
                    'Count' => $count
                );
            }
            WP_CLI\Utils\format_items('table', $type_table, array('Type', 'Count'));
            WP_CLI::log('');
        }
        
        if (!empty($stats['recent_posts'])) {
            WP_CLI::log('Recent Posts:');
            WP_CLI\Utils\format_items('table', $stats['recent_posts'], array('id', 'title', 'status', 'type', 'created_at'));
        }
    }
    
    /**
     * List AI-generated posts
     *
     * ## OPTIONS
     *
     * [--post-type=<post-type>]
     * : Filter by post type
     * ---
     * default: post
     * ---
     *
     * [--post-status=<status>]
     * : Filter by post status
     * ---
     * default: any
     * ---
     *
     * [--limit=<number>]
     * : Number of posts to show
     * ---
     * default: 20
     * ---
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     *   - ids
     * ---
     *
     * ## EXAMPLES
     *
     *     # List all AI-generated posts
     *     wp aiseo post list
     *
     *     # List published posts only
     *     wp aiseo post list --post-status=publish
     *
     *     # Get post IDs
     *     wp aiseo post list --format=ids
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function list($args, $assoc_args) {
        $post_type = WP_CLI\Utils\get_flag_value($assoc_args, 'post-type', 'post');
        $post_status = WP_CLI\Utils\get_flag_value($assoc_args, 'post-status', 'any');
        $limit = WP_CLI\Utils\get_flag_value($assoc_args, 'limit', 20);
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');
        
        $query_args = array(
            'post_type' => $post_type,
            'post_status' => $post_status,
            'posts_per_page' => $limit,
            'meta_key' => '_aiseo_ai_generated_post',
            'meta_value' => '1',
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $posts = get_posts($query_args);
        
        if (empty($posts)) {
            WP_CLI::warning('No AI-generated posts found');
            return;
        }
        
        if ($format === 'ids') {
            $ids = array_map(function($post) {
                return $post->ID;
            }, $posts);
            WP_CLI::log(implode(' ', $ids));
            return;
        }
        
        $items = array();
        foreach ($posts as $post) {
            $items[] = array(
                'ID' => $post->ID,
                'Title' => $post->post_title,
                'Status' => $post->post_status,
                'Type' => $post->post_type,
                'Date' => $post->post_date,
                'Keyword' => get_post_meta($post->ID, '_aiseo_focus_keyword', true),
            );
        }
        
        WP_CLI\Utils\format_items($format, $items, array('ID', 'Title', 'Status', 'Type', 'Date', 'Keyword'));
    }
}

// Register WP-CLI commands
WP_CLI::add_command('aiseo post create', array('AISEO_Post_Creator_CLI', 'create'));
WP_CLI::add_command('aiseo post bulk-create', array('AISEO_Post_Creator_CLI', 'bulk_create'));
WP_CLI::add_command('aiseo post stats', array('AISEO_Post_Creator_CLI', 'stats'));
WP_CLI::add_command('aiseo post list', array('AISEO_Post_Creator_CLI', 'list'));
