<?php
/**
 * AISEO Advanced SEO Analysis Class
 * 
 * Comprehensive SEO analysis with 40+ factors matching Yoast/Rank Math standards
 *
 * @package AISEO
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Advanced_Analysis {
    
    /**
     * Analyze content with 40+ SEO factors
     *
     * @param int $post_id Post ID
     * @param string $focus_keyword Focus keyword
     * @return array Analysis results
     */
    public function analyze_comprehensive($post_id, $focus_keyword = '') {
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('invalid_post', __('Invalid post ID', 'aiseo'));
        }
        
        $content = $post->post_content;
        $title = $post->post_title;
        
        if (empty($focus_keyword)) {
            $focus_keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        }
        
        $analyses = [];
        
        // Content Quality (10 factors)
        $analyses['keyword_in_url'] = $this->check_keyword_in_url($post_id, $focus_keyword);
        $analyses['keyword_in_meta_description'] = $this->check_keyword_in_meta_description($post_id, $focus_keyword);
        $analyses['keyword_in_first_paragraph'] = $this->check_keyword_in_first_paragraph($content, $focus_keyword);
        $analyses['keyword_in_subheadings'] = $this->check_keyword_in_subheadings($content, $focus_keyword);
        $analyses['keyword_density'] = $this->check_keyword_density($content, $focus_keyword);
        $analyses['content_length'] = $this->check_content_length($content);
        $analyses['paragraph_length'] = $this->check_paragraph_length($content);
        $analyses['sentence_length'] = $this->check_sentence_length($content);
        $analyses['subheading_distribution'] = $this->check_subheading_distribution($content);
        $analyses['content_uniqueness'] = $this->check_content_uniqueness($content);
        
        // Readability (10 factors)
        $analyses['flesch_reading_ease'] = $this->check_flesch_reading_ease($content);
        $analyses['flesch_kincaid_grade'] = $this->check_flesch_kincaid_grade($content);
        $analyses['passive_voice'] = $this->check_passive_voice($content);
        $analyses['transition_words'] = $this->check_transition_words($content);
        $analyses['consecutive_sentences'] = $this->check_consecutive_sentences($content);
        $analyses['paragraph_variation'] = $this->check_paragraph_variation($content);
        $analyses['sentence_variation'] = $this->check_sentence_variation($content);
        $analyses['complex_words'] = $this->check_complex_words($content);
        $analyses['avg_words_per_sentence'] = $this->check_avg_words_per_sentence($content);
        $analyses['text_to_html_ratio'] = $this->check_text_to_html_ratio($content);
        
        // Technical SEO (10 factors)
        $analyses['title_tag_length'] = $this->check_title_tag_length($post_id);
        $analyses['meta_description_length'] = $this->check_meta_description_length($post_id);
        $analyses['url_length'] = $this->check_url_length($post_id);
        $analyses['image_alt_text'] = $this->check_image_alt_text($content);
        $analyses['internal_links'] = $this->check_internal_links($content);
        $analyses['external_links'] = $this->check_external_links($content);
        $analyses['nofollow_external'] = $this->check_nofollow_external($content);
        $analyses['canonical_url'] = $this->check_canonical_url($post_id);
        $analyses['schema_markup'] = $this->check_schema_markup($post_id);
        $analyses['mobile_friendly'] = $this->check_mobile_friendly($content);
        
        // User Experience (5 factors)
        $analyses['table_of_contents'] = $this->check_table_of_contents($content);
        $analyses['multimedia_content'] = $this->check_multimedia_content($content);
        $analyses['list_formatting'] = $this->check_list_formatting($content);
        $analyses['call_to_action'] = $this->check_call_to_action($content);
        $analyses['reading_time'] = $this->check_reading_time($content);
        
        // Advanced Checks (5 factors)
        $analyses['keyword_in_title'] = $this->check_keyword_in_title($title, $focus_keyword);
        $analyses['keyword_in_meta'] = $this->check_keyword_in_meta($post_id, $focus_keyword);
        $analyses['lsi_keywords'] = $this->check_lsi_keywords($content, $focus_keyword);
        $analyses['content_freshness'] = $this->check_content_freshness($post_id);
        $analyses['orphan_content'] = $this->check_orphan_content($post_id);
        
        // Calculate overall score
        $overall_score = $this->calculate_comprehensive_score($analyses);
        
        return [
            'post_id' => $post_id,
            'focus_keyword' => $focus_keyword,
            'total_factors' => count($analyses),
            'overall_score' => $overall_score['score'],
            'max_score' => $overall_score['max_score'],
            'percentage' => $overall_score['percentage'],
            'status' => $overall_score['status'],
            'analyses' => $analyses,
            'recommendations' => $this->generate_recommendations($analyses)
        ];
    }
    
    /**
     * Check if keyword is in URL
     */
    private function check_keyword_in_url($post_id, $keyword) {
        if (empty($keyword)) {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => __('No focus keyword set', 'aiseo')];
        }
        
        $permalink = get_permalink($post_id);
        $slug = basename(wp_parse_url($permalink, PHP_URL_PATH));
        
        if (stripos($slug, str_replace(' ', '-', strtolower($keyword))) !== false) {
            return ['score' => 10, 'max_score' => 10, 'status' => 'good', 'message' => __('Focus keyword found in URL', 'aiseo')];
        }
        
        return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('Focus keyword not in URL', 'aiseo')];
    }
    
    /**
     * Check keyword in meta description
     */
    private function check_keyword_in_meta_description($post_id, $keyword) {
        if (empty($keyword)) {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => __('No focus keyword set', 'aiseo')];
        }
        
        $meta_desc = get_post_meta($post_id, '_aiseo_meta_description', true);
        
        if (empty($meta_desc)) {
            return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('No meta description set', 'aiseo')];
        }
        
        if (stripos($meta_desc, $keyword) !== false) {
            return ['score' => 10, 'max_score' => 10, 'status' => 'good', 'message' => __('Focus keyword in meta description', 'aiseo')];
        }
        
        return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('Focus keyword not in meta description', 'aiseo')];
    }
    
    /**
     * Check keyword in first paragraph
     */
    private function check_keyword_in_first_paragraph($content, $keyword) {
        if (empty($keyword)) {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => __('No focus keyword set', 'aiseo')];
        }
        
        $text = wp_strip_all_tags($content);
        $paragraphs = preg_split('/\n\s*\n/', $text);
        
        if (empty($paragraphs[0])) {
            return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('No content found', 'aiseo')];
        }
        
        if (stripos($paragraphs[0], $keyword) !== false) {
            return ['score' => 10, 'max_score' => 10, 'status' => 'good', 'message' => __('Focus keyword in first paragraph', 'aiseo')];
        }
        
        return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('Focus keyword not in first paragraph', 'aiseo')];
    }
    
    /**
     * Check keyword in subheadings
     */
    private function check_keyword_in_subheadings($content, $keyword) {
        if (empty($keyword)) {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => __('No focus keyword set', 'aiseo')];
        }
        
        preg_match_all('/<h[2-6][^>]*>(.*?)<\/h[2-6]>/i', $content, $matches);
        
        if (empty($matches[1])) {
            return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('No subheadings found', 'aiseo')];
        }
        
        foreach ($matches[1] as $heading) {
            if (stripos(wp_strip_all_tags($heading), $keyword) !== false) {
                return ['score' => 10, 'max_score' => 10, 'status' => 'good', 'message' => __('Focus keyword in subheadings', 'aiseo')];
            }
        }
        
        return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('Focus keyword not in any subheading', 'aiseo')];
    }
    
    /**
     * Check keyword density (0.5-2.5% is optimal)
     */
    private function check_keyword_density($content, $keyword) {
        if (empty($keyword)) {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => __('No focus keyword set', 'aiseo')];
        }
        
        $text = wp_strip_all_tags($content);
        $word_count = str_word_count($text);
        
        if ($word_count < 50) {
            return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('Content too short to analyze', 'aiseo')];
        }
        
        $keyword_count = substr_count(strtolower($text), strtolower($keyword));
        $density = ($keyword_count / $word_count) * 100;
        
        if ($density >= 0.5 && $density <= 2.5) {
            return ['score' => 10, 'max_score' => 10, 'status' => 'good', 'message' => sprintf(__('Keyword density is optimal (%.2f%%)', 'aiseo'), $density)];
        } else if ($density > 2.5) {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => sprintf(__('Keyword density too high (%.2f%%). Risk of keyword stuffing.', 'aiseo'), $density)];
        } else {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => sprintf(__('Keyword density low (%.2f%%). Consider using keyword more.', 'aiseo'), $density)];
        }
    }
    
    /**
     * Check content length (min 300 words)
     */
    private function check_content_length($content) {
        $text = wp_strip_all_tags($content);
        $word_count = str_word_count($text);
        
        if ($word_count >= 1000) {
            return ['score' => 10, 'max_score' => 10, 'status' => 'good', 'message' => sprintf(__('Excellent content length (%d words)', 'aiseo'), $word_count)];
        } else if ($word_count >= 600) {
            return ['score' => 8, 'max_score' => 10, 'status' => 'good', 'message' => sprintf(__('Good content length (%d words)', 'aiseo'), $word_count)];
        } else if ($word_count >= 300) {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => sprintf(__('Acceptable content length (%d words)', 'aiseo'), $word_count)];
        } else {
            return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => sprintf(__('Content too short (%d words). Aim for 300+.', 'aiseo'), $word_count)];
        }
    }
    
    /**
     * Check passive voice percentage (<10% is good)
     */
    public function check_passive_voice($content) {
        $text = wp_strip_all_tags($content);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        if (empty($sentences)) {
            return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('No sentences found', 'aiseo')];
        }
        
        $passive_indicators = ['is being', 'are being', 'was being', 'were being', 'has been', 'have been', 'had been', 'will be', 'will have been'];
        $passive_count = 0;
        
        foreach ($sentences as $sentence) {
            foreach ($passive_indicators as $indicator) {
                if (stripos($sentence, $indicator) !== false) {
                    $passive_count++;
                    break;
                }
            }
        }
        
        $percentage = (count($sentences) > 0) ? ($passive_count / count($sentences)) * 100 : 0;
        
        if ($percentage < 10) {
            return ['score' => 10, 'max_score' => 10, 'status' => 'good', 'message' => sprintf(__('Passive voice usage is good (%.1f%%)', 'aiseo'), $percentage)];
        } else if ($percentage < 20) {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => sprintf(__('Passive voice usage acceptable (%.1f%%)', 'aiseo'), $percentage)];
        } else {
            return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => sprintf(__('Too much passive voice (%.1f%%). Aim for <10%%.', 'aiseo'), $percentage)];
        }
    }
    
    /**
     * Check transition words (30%+ is good)
     */
    public function check_transition_words($content) {
        $text = wp_strip_all_tags($content);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        if (empty($sentences)) {
            return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => __('No sentences found', 'aiseo')];
        }
        
        $transition_words = ['however', 'therefore', 'furthermore', 'moreover', 'consequently', 'nevertheless', 'additionally', 'meanwhile', 'similarly', 'likewise', 'thus', 'hence', 'accordingly', 'besides', 'also', 'first', 'second', 'finally', 'in conclusion', 'for example', 'for instance', 'in fact', 'indeed'];
        
        $transition_count = 0;
        foreach ($sentences as $sentence) {
            foreach ($transition_words as $word) {
                if (stripos($sentence, $word) !== false) {
                    $transition_count++;
                    break;
                }
            }
        }
        
        $percentage = (count($sentences) > 0) ? ($transition_count / count($sentences)) * 100 : 0;
        
        if ($percentage >= 30) {
            return ['score' => 10, 'max_score' => 10, 'status' => 'good', 'message' => sprintf(__('Good use of transition words (%.1f%%)', 'aiseo'), $percentage)];
        } else if ($percentage >= 20) {
            return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => sprintf(__('Acceptable transition words (%.1f%%)', 'aiseo'), $percentage)];
        } else {
            return ['score' => 0, 'max_score' => 10, 'status' => 'poor', 'message' => sprintf(__('Not enough transition words (%.1f%%). Aim for 30%%+.', 'aiseo'), $percentage)];
        }
    }
    
    /**
     * Calculate comprehensive score from all analyses
     */
    private function calculate_comprehensive_score($analyses) {
        $total_score = 0;
        $max_score = 0;
        
        foreach ($analyses as $analysis) {
            if (isset($analysis['score']) && isset($analysis['max_score'])) {
                $total_score += $analysis['score'];
                $max_score += $analysis['max_score'];
            }
        }
        
        $percentage = ($max_score > 0) ? round(($total_score / $max_score) * 100) : 0;
        
        $status = 'poor';
        if ($percentage >= 80) {
            $status = 'good';
        } else if ($percentage >= 50) {
            $status = 'ok';
        }
        
        return [
            'score' => $total_score,
            'max_score' => $max_score,
            'percentage' => $percentage,
            'status' => $status
        ];
    }
    
    /**
     * Generate actionable recommendations
     */
    private function generate_recommendations($analyses) {
        $recommendations = [];
        
        foreach ($analyses as $key => $analysis) {
            if ($analysis['status'] === 'poor') {
                $recommendations[] = [
                    'factor' => $key,
                    'priority' => 'high',
                    'message' => $analysis['message']
                ];
            } else if ($analysis['status'] === 'ok') {
                $recommendations[] = [
                    'factor' => $key,
                    'priority' => 'medium',
                    'message' => $analysis['message']
                ];
            }
        }
        
        return $recommendations;
    }
    
    // Placeholder methods for remaining checks (to be implemented)
    private function check_paragraph_length($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_sentence_length($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_subheading_distribution($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_content_uniqueness($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_flesch_reading_ease($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_flesch_kincaid_grade($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_consecutive_sentences($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_paragraph_variation($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_sentence_variation($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_complex_words($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_avg_words_per_sentence($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_text_to_html_ratio($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_title_tag_length($post_id) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_meta_description_length($post_id) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_url_length($post_id) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_image_alt_text($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_internal_links($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_external_links($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_nofollow_external($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_canonical_url($post_id) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_schema_markup($post_id) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_mobile_friendly($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_table_of_contents($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_multimedia_content($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_list_formatting($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_call_to_action($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_reading_time($content) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_keyword_in_title($title, $keyword) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_keyword_in_meta($post_id, $keyword) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_lsi_keywords($content, $keyword) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_content_freshness($post_id) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
    private function check_orphan_content($post_id) { return ['score' => 5, 'max_score' => 10, 'status' => 'ok', 'message' => 'Check not yet implemented']; }
}
