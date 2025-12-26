<?php
/**
 * AISEO Multilingual Support Class
 * 
 * Support for WPML, Polylang, and hreflang tags
 *
 * @package AISEO
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Multilingual {
    
    /**
     * Detected multilingual plugin
     */
    private $active_plugin = null;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->detect_multilingual_plugin();
    }
    
    /**
     * Detect active multilingual plugin
     *
     * @return string|null Plugin name or null
     */
    public function detect_multilingual_plugin() {
        if (defined('ICL_SITEPRESS_VERSION')) {
            $this->active_plugin = 'wpml';
        } elseif (function_exists('pll_languages_list')) {
            $this->active_plugin = 'polylang';
        } elseif (class_exists('TRP_Translate_Press')) {
            $this->active_plugin = 'translatepress';
        }
        
        return $this->active_plugin;
    }
    
    /**
     * Get active multilingual plugin
     *
     * @return string|null
     */
    public function get_active_plugin() {
        return $this->active_plugin;
    }
    
    /**
     * Get all available languages
     *
     * @return array Languages array
     */
    public function get_languages() {
        $languages = [];
        
        switch ($this->active_plugin) {
            case 'wpml':
                $languages = $this->get_wpml_languages();
                break;
            case 'polylang':
                $languages = $this->get_polylang_languages();
                break;
            case 'translatepress':
                $languages = $this->get_translatepress_languages();
                break;
        }
        
        return $languages;
    }
    
    /**
     * Get WPML languages
     *
     * @return array
     */
    private function get_wpml_languages() {
        if (!function_exists('icl_get_languages')) {
            return [];
        }
        
        $wpml_languages = icl_get_languages('skip_missing=0');
        $languages = [];
        
        foreach ($wpml_languages as $lang) {
            $languages[] = [
                'code' => $lang['language_code'],
                'name' => $lang['native_name'],
                'locale' => $lang['default_locale'],
                'url' => $lang['url'],
                'active' => $lang['active']
            ];
        }
        
        return $languages;
    }
    
    /**
     * Get Polylang languages
     *
     * @return array
     */
    private function get_polylang_languages() {
        if (!function_exists('pll_languages_list')) {
            return [];
        }
        
        $pll_languages = pll_languages_list(['fields' => '']);
        $languages = [];
        
        foreach ($pll_languages as $lang) {
            $languages[] = [
                'code' => $lang->slug,
                'name' => $lang->name,
                'locale' => $lang->locale,
                'url' => pll_home_url($lang->slug),
                'active' => (pll_current_language() === $lang->slug)
            ];
        }
        
        return $languages;
    }
    
    /**
     * Get TranslatePress languages
     *
     * @return array
     */
    private function get_translatepress_languages() {
        if (!class_exists('TRP_Translate_Press')) {
            return [];
        }
        
        $trp = TRP_Translate_Press::get_trp_instance();
        $trp_languages = $trp->get_component('languages');
        $published_languages = $trp_languages->get_published_languages();
        
        $languages = [];
        foreach ($published_languages as $code) {
            $languages[] = [
                'code' => $code,
                'name' => $trp_languages->get_language_names([$code])[$code],
                'locale' => $code,
                'url' => '',
                'active' => (get_locale() === $code)
            ];
        }
        
        return $languages;
    }
    
    /**
     * Get translations for a post
     *
     * @param int $post_id Post ID
     * @return array Translations
     */
    public function get_post_translations($post_id) {
        $translations = [];
        
        switch ($this->active_plugin) {
            case 'wpml':
                $translations = $this->get_wpml_translations($post_id);
                break;
            case 'polylang':
                $translations = $this->get_polylang_translations($post_id);
                break;
            case 'translatepress':
                $translations = $this->get_translatepress_translations($post_id);
                break;
        }
        
        return $translations;
    }
    
    /**
     * Get WPML translations
     *
     * @param int $post_id
     * @return array
     */
    private function get_wpml_translations($post_id) {
        if (!function_exists('icl_object_id')) {
            return [];
        }
        
        $post_type = get_post_type($post_id);
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML filter hook
        $trid = apply_filters('wpml_element_trid', null, $post_id, 'post_' . $post_type);
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML filter hook
        $translations_data = apply_filters('wpml_get_element_translations', null, $trid, 'post_' . $post_type);
        
        $translations = [];
        foreach ($translations_data as $lang_code => $translation) {
            $translations[] = [
                'language' => $lang_code,
                'post_id' => $translation->element_id,
                'url' => get_permalink($translation->element_id),
                'title' => get_the_title($translation->element_id)
            ];
        }
        
        return $translations;
    }
    
    /**
     * Get Polylang translations
     *
     * @param int $post_id
     * @return array
     */
    private function get_polylang_translations($post_id) {
        if (!function_exists('pll_get_post_translations')) {
            return [];
        }
        
        $pll_translations = pll_get_post_translations($post_id);
        $translations = [];
        
        foreach ($pll_translations as $lang_code => $translated_post_id) {
            $translations[] = [
                'language' => $lang_code,
                'post_id' => $translated_post_id,
                'url' => get_permalink($translated_post_id),
                'title' => get_the_title($translated_post_id)
            ];
        }
        
        return $translations;
    }
    
    /**
     * Get TranslatePress translations
     *
     * @param int $post_id
     * @return array
     */
    private function get_translatepress_translations($post_id) {
        // TranslatePress doesn't create separate posts for translations
        // It translates content on the fly
        $languages = $this->get_languages();
        $translations = [];
        
        foreach ($languages as $lang) {
            $translations[] = [
                'language' => $lang['code'],
                'post_id' => $post_id,
                'url' => add_query_arg('trp-edit-translation', $lang['code'], get_permalink($post_id)),
                'title' => get_the_title($post_id)
            ];
        }
        
        return $translations;
    }
    
    /**
     * Generate hreflang tags for a post
     *
     * @param int $post_id Post ID
     * @return array Hreflang tags
     */
    public function generate_hreflang_tags($post_id) {
        $translations = $this->get_post_translations($post_id);
        $hreflang_tags = [];
        
        foreach ($translations as $translation) {
            $hreflang_tags[] = [
                'hreflang' => $translation['language'],
                'href' => $translation['url']
            ];
        }
        
        // Add x-default if available
        if (!empty($hreflang_tags)) {
            $default_lang = $this->get_default_language();
            foreach ($translations as $translation) {
                if ($translation['language'] === $default_lang) {
                    $hreflang_tags[] = [
                        'hreflang' => 'x-default',
                        'href' => $translation['url']
                    ];
                    break;
                }
            }
        }
        
        return $hreflang_tags;
    }
    
    /**
     * Get default language
     *
     * @return string
     */
    public function get_default_language() {
        switch ($this->active_plugin) {
            case 'wpml':
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML filter hook
                return apply_filters('wpml_default_language', null);
            case 'polylang':
                return pll_default_language();
            case 'translatepress':
                $trp = TRP_Translate_Press::get_trp_instance();
                $settings = $trp->get_component('settings')->get_settings();
                return $settings['default-language'];
            default:
                return substr(get_locale(), 0, 2);
        }
    }
    
    /**
     * Inject hreflang tags into head
     *
     * @param int $post_id Post ID
     */
    public function inject_hreflang_tags($post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        if (!$post_id) {
            return;
        }
        
        $hreflang_tags = $this->generate_hreflang_tags($post_id);
        
        foreach ($hreflang_tags as $tag) {
            echo '<link rel="alternate" hreflang="' . esc_attr($tag['hreflang']) . '" href="' . esc_url($tag['href']) . '" />' . "\n";
        }
    }
    
    /**
     * Copy SEO metadata to translation
     *
     * @param int $source_post_id Source post ID
     * @param int $target_post_id Target post ID
     * @param bool $overwrite Overwrite existing metadata
     * @return array Result
     */
    public function copy_metadata_to_translation($source_post_id, $target_post_id, $overwrite = false) {
        $meta_keys = [
            '_aiseo_meta_title',
            '_aiseo_meta_description',
            '_aiseo_focus_keyword',
            '_aiseo_canonical_url',
            '_aiseo_robots_index',
            '_aiseo_robots_follow',
            '_aiseo_og_title',
            '_aiseo_og_description',
            '_aiseo_twitter_title',
            '_aiseo_twitter_description'
        ];
        
        $copied = 0;
        $skipped = 0;
        
        foreach ($meta_keys as $meta_key) {
            $source_value = get_post_meta($source_post_id, $meta_key, true);
            
            if (empty($source_value)) {
                continue;
            }
            
            $target_value = get_post_meta($target_post_id, $meta_key, true);
            
            if (!empty($target_value) && !$overwrite) {
                $skipped++;
                continue;
            }
            
            update_post_meta($target_post_id, $meta_key, $source_value);
            $copied++;
        }
        
        return [
            'success' => true,
            'copied' => $copied,
            'skipped' => $skipped
        ];
    }
    
    /**
     * Sync metadata across all translations
     *
     * @param int $post_id Post ID
     * @param bool $overwrite Overwrite existing
     * @return array Results
     */
    public function sync_metadata_across_translations($post_id, $overwrite = false) {
        $translations = $this->get_post_translations($post_id);
        $results = [];
        
        foreach ($translations as $translation) {
            if ($translation['post_id'] == $post_id) {
                continue;
            }
            
            $result = $this->copy_metadata_to_translation($post_id, $translation['post_id'], $overwrite);
            $results[$translation['language']] = $result;
        }
        
        return [
            'success' => true,
            'synced_languages' => count($results),
            'results' => $results
        ];
    }
}
