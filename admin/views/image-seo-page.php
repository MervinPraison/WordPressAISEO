<?php
/**
 * Image SEO Admin Page
 *
 * @package AISEO
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap aiseo-image-seo-page">
    <h1><?php esc_html_e('Image SEO & Alt Text Optimization', 'aiseo'); ?></h1>
    
    <!-- Statistics Dashboard -->
    <div class="aiseo-image-stats">
        <div class="stat-box">
            <h3><?php esc_html_e('Total Images', 'aiseo'); ?></h3>
            <p class="stat-number"><?php echo number_format($total_images); ?></p>
        </div>
        <div class="stat-box">
            <h3><?php esc_html_e('Missing Alt Text', 'aiseo'); ?></h3>
            <p class="stat-number warning"><?php echo number_format(count($images)); ?></p>
        </div>
        <div class="stat-box">
            <h3><?php esc_html_e('AI Generated', 'aiseo'); ?></h3>
            <p class="stat-number success">
                <?php
                global $wpdb;
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Counting AI-generated alt texts
                $ai_generated = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_aiseo_ai_generated_alt' AND meta_value = '1'");
                echo number_format($ai_generated);
                ?>
            </p>
        </div>
    </div>
    
    <!-- Missing Alt Text Table -->
    <div class="aiseo-missing-alt-section">
        <h2><?php esc_html_e('Images Missing Alt Text', 'aiseo'); ?></h2>
        
        <?php if (empty($images)): ?>
            <div class="notice notice-success">
                <p><?php esc_html_e('Great! All images have alt text.', 'aiseo'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="select-all-images"></th>
                        <th style="width: 80px;"><?php esc_html_e('Image', 'aiseo'); ?></th>
                        <th><?php esc_html_e('Filename', 'aiseo'); ?></th>
                        <th><?php esc_html_e('Used In', 'aiseo'); ?></th>
                        <th><?php esc_html_e('Size', 'aiseo'); ?></th>
                        <th><?php esc_html_e('Actions', 'aiseo'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($images as $image): ?>
                    <tr data-image-id="<?php echo esc_attr($image->ID); ?>">
                        <td>
                            <input type="checkbox" class="image-checkbox" value="<?php echo esc_attr($image->ID); ?>">
                        </td>
                        <td>
                            <?php if ($image->thumbnail): ?>
                                <img src="<?php echo esc_url($image->thumbnail); ?>" alt="" style="width: 60px; height: 60px; object-fit: cover;">
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html(basename($image->guid)); ?></strong>
                        </td>
                        <td>
                            <?php if ($image->post_parent > 0): ?>
                                <a href="<?php echo esc_url($image->parent_url); ?>" target="_blank">
                                    <?php echo esc_html($image->parent_title); ?>
                                </a>
                            <?php else: ?>
                                <em><?php esc_html_e('Not attached', 'aiseo'); ?></em>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($image->filesize); ?></td>
                        <td>
                            <button class="button aiseo-generate-single-alt" data-image-id="<?php echo esc_attr($image->ID); ?>">
                                <?php esc_html_e('Generate Alt Text', 'aiseo'); ?>
                            </button>
                            <span class="spinner"></span>
                            <span class="alt-text-result"></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="bulk-actions-bar">
                <button class="button button-primary" id="bulk-generate-alt" disabled>
                    <?php esc_html_e('Generate Alt Text for Selected', 'aiseo'); ?>
                </button>
                <span class="selected-count">0 <?php esc_html_e('selected', 'aiseo'); ?></span>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Progress Bar -->
    <div class="aiseo-progress" style="display:none;">
        <h3><?php esc_html_e('Generating Alt Text...', 'aiseo'); ?></h3>
        <div class="progress-bar">
            <div class="progress-fill" style="width:0%"></div>
        </div>
        <p class="progress-text">
            <span class="current">0</span> / <span class="total">0</span> <?php esc_html_e('completed', 'aiseo'); ?>
        </p>
        <button class="button" id="cancel-bulk"><?php esc_html_e('Cancel', 'aiseo'); ?></button>
    </div>
</div>
