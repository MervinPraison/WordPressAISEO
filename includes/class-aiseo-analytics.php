<?php
/**
 * AISEO Google Analytics Integration
 *
 * Handles Google Analytics (GA4 and Universal Analytics) tracking code injection
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Analytics {
    
    /**
     * Option keys
     */
    const OPTION_TRACKING_ID = 'aiseo_ga_tracking_id';
    const OPTION_ENABLED = 'aiseo_ga_enabled';
    const OPTION_ANONYMIZE_IP = 'aiseo_ga_anonymize_ip';
    const OPTION_TRACK_ADMIN = 'aiseo_ga_track_admin';
    const OPTION_TRACK_LOGGED_IN = 'aiseo_ga_track_logged_in';
    
    /**
     * Initialize analytics
     */
    public function init() {
        // Output tracking code in head
        add_action('wp_head', array($this, 'output_tracking_code'), 1);
    }
    
    /**
     * Get analytics settings
     *
     * @return array Settings
     */
    public function get_settings() {
        return array(
            'tracking_id' => get_option(self::OPTION_TRACKING_ID, ''),
            'enabled' => get_option(self::OPTION_ENABLED, '0') === '1',
            'anonymize_ip' => get_option(self::OPTION_ANONYMIZE_IP, '1') === '1',
            'track_admin' => get_option(self::OPTION_TRACK_ADMIN, '0') === '1',
            'track_logged_in' => get_option(self::OPTION_TRACK_LOGGED_IN, '1') === '1',
        );
    }
    
    /**
     * Update analytics settings
     *
     * @param array $settings Settings to update
     * @return bool Success
     */
    public function update_settings($settings) {
        $updated = false;
        
        if (isset($settings['tracking_id'])) {
            $tracking_id = $this->sanitize_tracking_id($settings['tracking_id']);
            update_option(self::OPTION_TRACKING_ID, $tracking_id);
            $updated = true;
        }
        
        if (isset($settings['enabled'])) {
            update_option(self::OPTION_ENABLED, $settings['enabled'] ? '1' : '0');
            $updated = true;
        }
        
        if (isset($settings['anonymize_ip'])) {
            update_option(self::OPTION_ANONYMIZE_IP, $settings['anonymize_ip'] ? '1' : '0');
            $updated = true;
        }
        
        if (isset($settings['track_admin'])) {
            update_option(self::OPTION_TRACK_ADMIN, $settings['track_admin'] ? '1' : '0');
            $updated = true;
        }
        
        if (isset($settings['track_logged_in'])) {
            update_option(self::OPTION_TRACK_LOGGED_IN, $settings['track_logged_in'] ? '1' : '0');
            $updated = true;
        }
        
        return $updated;
    }
    
    /**
     * Sanitize tracking ID
     *
     * @param string $id Tracking ID
     * @return string Sanitized ID
     */
    private function sanitize_tracking_id($id) {
        $id = trim($id);
        $id = strtoupper($id);
        
        // Remove any spaces
        $id = preg_replace('/\s+/', '', $id);
        
        return sanitize_text_field($id);
    }
    
    /**
     * Check if tracking ID is GA4 format
     *
     * @param string $id Tracking ID
     * @return bool
     */
    private function is_ga4($id) {
        return preg_match('/^G-[A-Z0-9]+$/', $id);
    }
    
    /**
     * Check if tracking ID is Universal Analytics format
     *
     * @param string $id Tracking ID
     * @return bool
     */
    private function is_universal($id) {
        return preg_match('/^UA-\d+-\d+$/', $id);
    }
    
    /**
     * Should track current user
     *
     * @return bool
     */
    private function should_track() {
        $settings = $this->get_settings();
        
        // Check if enabled
        if (!$settings['enabled']) {
            return false;
        }
        
        // Check if tracking ID is set
        if (empty($settings['tracking_id'])) {
            return false;
        }
        
        // Check admin pages
        if (is_admin() && !$settings['track_admin']) {
            return false;
        }
        
        // Check logged in users
        if (is_user_logged_in() && !$settings['track_logged_in']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Output tracking code
     */
    public function output_tracking_code() {
        if (!$this->should_track()) {
            return;
        }
        
        $settings = $this->get_settings();
        $tracking_id = $settings['tracking_id'];
        
        if ($this->is_ga4($tracking_id)) {
            $this->output_ga4_code($tracking_id, $settings);
        } elseif ($this->is_universal($tracking_id)) {
            $this->output_universal_code($tracking_id, $settings);
        }
    }
    
    /**
     * Output GA4 tracking code
     *
     * @param string $tracking_id Tracking ID
     * @param array $settings Settings
     */
    private function output_ga4_code($tracking_id, $settings) {
        ?>
<!-- Google Analytics (GA4) - AISEO -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($tracking_id); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo esc_js($tracking_id); ?>'<?php 
    $config = array();
    if ($settings['anonymize_ip']) {
        $config['anonymize_ip'] = true;
    }
    if (!empty($config)) {
        echo ', ' . wp_json_encode($config);
    }
?>);
</script>
<!-- End Google Analytics -->
        <?php
    }
    
    /**
     * Output Universal Analytics tracking code
     *
     * @param string $tracking_id Tracking ID
     * @param array $settings Settings
     */
    private function output_universal_code($tracking_id, $settings) {
        ?>
<!-- Google Analytics (Universal) - AISEO -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
ga('create', '<?php echo esc_js($tracking_id); ?>', 'auto');
<?php if ($settings['anonymize_ip']) : ?>
ga('set', 'anonymizeIp', true);
<?php endif; ?>
ga('send', 'pageview');
</script>
<!-- End Google Analytics -->
        <?php
    }
}
