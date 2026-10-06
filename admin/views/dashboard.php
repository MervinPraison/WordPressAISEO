<?php
/**
 * Dashboard Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get statistics
$api_key = AISEO_Helpers::get_api_key();
$posts_analyzed = get_option('aiseo_posts_analyzed_count', 0);
$metadata_generated = get_option('aiseo_metadata_generated_count', 0);
$ai_posts_created = 0;

if (class_exists('AISEO_Post_Creator')) {
    $creator = new AISEO_Post_Creator();
    $stats = $creator->get_statistics();
    $ai_posts_created = isset($stats['total_ai_posts']) ? $stats['total_ai_posts'] : 0;
}

// Get recent activity
$recent_posts = wp_get_recent_posts(array(
    'numberposts' => 5,
    'post_status' => 'any',
));
?>

<div class="aiseo-dashboard">
    
    <!-- Quick Stats -->
    <div class="aiseo-dashboard-grid">
        
        <!-- API Status Widget -->
        <div class="aiseo-widget">
            <h3 class="aiseo-widget-title">
                <span class="dashicons dashicons-admin-plugins"></span>
                <?php esc_html_e('API Status', 'aiseo'); ?>
            </h3>
            <div class="aiseo-widget-body">
                <?php if (!empty($api_key)): ?>
                    <p class="aiseo-alert aiseo-alert-success">
                        <strong><?php esc_html_e('API Key Configured', 'aiseo'); ?></strong><br>
                        <?php esc_html_e('Model:', 'aiseo'); ?> <code><?php echo esc_html(get_option('aiseo_model', 'gpt-4o-mini')); ?></code>
                    </p>
                <?php else: ?>
                    <p class="aiseo-alert aiseo-alert-warning">
                        <strong><?php esc_html_e('No API Key', 'aiseo'); ?></strong><br>
                        <?php esc_html_e('Please configure your OpenAI API key in Settings.', 'aiseo'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Stats Widget -->
        <div class="aiseo-widget">
            <h3 class="aiseo-widget-title">
                <span class="dashicons dashicons-chart-bar"></span>
                <?php esc_html_e('Quick Stats', 'aiseo'); ?>
            </h3>
            <div class="aiseo-stat-grid">
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value"><?php echo esc_html($posts_analyzed); ?></div>
                    <div class="aiseo-stat-label"><?php esc_html_e('Posts Analyzed', 'aiseo'); ?></div>
                </div>
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value"><?php echo esc_html($metadata_generated); ?></div>
                    <div class="aiseo-stat-label"><?php esc_html_e('Metadata Generated', 'aiseo'); ?></div>
                </div>
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value"><?php echo esc_html($ai_posts_created); ?></div>
                    <div class="aiseo-stat-label"><?php esc_html_e('AI Posts Created', 'aiseo'); ?></div>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Quick Actions -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php esc_html_e('Quick Actions', 'aiseo'); ?>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <div class="aiseo-button-group">
                <a href="<?php echo esc_url(admin_url('admin.php?page=aiseo&tab=ai-content')); ?>" class="aiseo-btn aiseo-btn-primary aiseo-btn-large">
                    <span class="dashicons dashicons-edit"></span>
                    <?php esc_html_e('Create AI Post', 'aiseo'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=aiseo&tab=seo-tools')); ?>" class="aiseo-btn aiseo-btn-secondary aiseo-btn-large">
                    <span class="dashicons dashicons-search"></span>
                    <?php esc_html_e('SEO Tools', 'aiseo'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=aiseo&tab=bulk-operations')); ?>" class="aiseo-btn aiseo-btn-secondary aiseo-btn-large">
                    <span class="dashicons dashicons-admin-page"></span>
                    <?php esc_html_e('Bulk Operations', 'aiseo'); ?>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-clock"></span>
                <?php esc_html_e('Recent Posts', 'aiseo'); ?>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <?php if (!empty($recent_posts)): ?>
                <table class="aiseo-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Title', 'aiseo'); ?></th>
                            <th><?php esc_html_e('Status', 'aiseo'); ?></th>
                            <th><?php esc_html_e('Date', 'aiseo'); ?></th>
                            <th><?php esc_html_e('Actions', 'aiseo'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_posts as $post): ?>
                            <tr>
                                <td><strong><?php echo esc_html($post['post_title']); ?></strong></td>
                                <td>
                                    <span class="aiseo-badge aiseo-badge-<?php echo esc_attr($post['post_status'] === 'publish' ? 'success' : 'warning'); ?>">
                                        <?php echo esc_html(ucfirst($post['post_status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($post['post_date']))); ?></td>
                                <td class="aiseo-table-actions">
                                    <a href="<?php echo esc_url(get_edit_post_link($post['ID'])); ?>" class="button button-small">
                                        <?php esc_html_e('Edit', 'aiseo'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="aiseo-empty-state">
                    <span class="dashicons dashicons-admin-post"></span>
                    <h3><?php esc_html_e('No Recent Posts', 'aiseo'); ?></h3>
                    <p><?php esc_html_e('Create your first post to see it here.', 'aiseo'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>
