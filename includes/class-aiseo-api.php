<?php
/**
 * AISEO OpenAI API Handler
 *
 * Manages all OpenAI API communications
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_API {
    
    /**
     * OpenAI API endpoint
     */
    const API_ENDPOINT = 'https://api.openai.com/v1/chat/completions';
    
    /**
     * API key
     */
    private $api_key;
    
    /**
     * API model
     */
    private $model;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table for API tracking
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table for API usage tracking
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = AISEO_Helpers::get_api_key();
        $this->model = get_option('aiseo_api_model', 'gpt-4o-mini');
    }
    
    /**
     * Generate meta description
     *
     * @param string $content Post content
     * @param string $keyword Focus keyword
     * @return string|WP_Error Generated description or error
     */
    public function generate_meta_description($content, $keyword = '') {
        $content = AISEO_Helpers::strip_shortcodes_and_tags($content);
        $content = AISEO_Helpers::truncate_text($content, 500);
        
        $prompt = "Generate a compelling SEO meta description (155-160 characters) for the following content";
        if (!empty($keyword)) {
            $prompt .= " with focus keyword '$keyword'";
        }
        $prompt .= ":\n\n" . $content;
        $prompt .= "\n\nProvide ONLY the meta description text, nothing else. Make it engaging and include a call-to-action if appropriate.";
        
        $response = $this->make_request($prompt, array(
            'max_tokens' => 100,
            'temperature' => 0.7,
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $description = trim($response);
        
        // Remove surrounding quotes if present (OpenAI sometimes adds them)
        $description = trim($description, " \t\n\r\0\x0B\"'");
        
        // Ensure it's within character limit
        if (strlen($description) > 160) {
            $description = AISEO_Helpers::truncate_text($description, 160, '');
        }
        
        return $description;
    }
    
    /**
     * Generate SEO title
     *
     * @param string $content Post content
     * @param string $keyword Focus keyword
     * @return string|WP_Error Generated title or error
     */
    public function generate_title($content, $keyword = '') {
        $content = AISEO_Helpers::strip_shortcodes_and_tags($content);
        $content = AISEO_Helpers::truncate_text($content, 500);
        
        $prompt = "Generate an SEO-optimized title (50-60 characters) for the following content";
        if (!empty($keyword)) {
            $prompt .= " with focus keyword '$keyword'";
        }
        $prompt .= ":\n\n" . $content;
        $prompt .= "\n\nProvide ONLY the title text, nothing else. Make it compelling and click-worthy while being accurate.";
        
        $response = $this->make_request($prompt, array(
            'max_tokens' => 50,
            'temperature' => 0.7,
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $title = trim($response);
        
        // Remove surrounding quotes if present (OpenAI sometimes adds them)
        $title = trim($title, " \t\n\r\0\x0B\"'");
        
        // Ensure it's within character limit
        if (strlen($title) > 60) {
            $title = AISEO_Helpers::truncate_text($title, 60, '');
        }
        
        return $title;
    }
    
    /**
     * Generate multiple suggestions
     *
     * @param string $content Post content
     * @param string $type Type (title or description)
     * @param int $count Number of suggestions
     * @return array|WP_Error Array of suggestions or error
     */
    public function generate_multiple_suggestions($content, $type = 'description', $count = 3) {
        $suggestions = array();
        
        for ($i = 0; $i < $count; $i++) {
            if ($type === 'title') {
                $result = $this->generate_title($content);
            } else {
                $result = $this->generate_meta_description($content);
            }
            
            if (is_wp_error($result)) {
                return $result;
            }
            
            $suggestions[] = $result;
            
            // Small delay to avoid rate limiting
            if ($i < $count - 1) {
                sleep(1);
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Analyze content with AI
     *
     * @param string $content Content to analyze
     * @return array|WP_Error Analysis results or error
     */
    public function analyze_content($content) {
        $content = AISEO_Helpers::strip_shortcodes_and_tags($content);
        $content = AISEO_Helpers::truncate_text($content, 1000);
        
        $prompt = "Analyze the following content for SEO quality. Provide a brief assessment of:\n";
        $prompt .= "1. Content quality\n";
        $prompt .= "2. Keyword usage\n";
        $prompt .= "3. Readability\n";
        $prompt .= "4. Suggestions for improvement\n\n";
        $prompt .= "Content:\n" . $content;
        $prompt .= "\n\nProvide a concise analysis in 3-4 sentences.";
        
        $response = $this->make_request($prompt, array(
            'max_tokens' => 200,
            'temperature' => 0.5,
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'analysis' => trim($response),
            'timestamp' => current_time('mysql'),
        );
    }
    
    /**
     * Generate alt text for image using Vision API
     *
     * @param array $image_context Context array with image_url and page content
     * @return string|WP_Error Alt text or error
     */
    public function generate_alt_text($image_context) {
        $api_key = AISEO_Helpers::get_api_key();
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('OpenAI API key not configured', 'aiseo'));
        }
        
        // Build context prompt
        $context_text = "";
        if (!empty($image_context['parent_title'])) {
            $context_text .= "Page Title: " . $image_context['parent_title'] . "\n";
        }
        if (!empty($image_context['parent_content'])) {
            $context_text .= "Page Content: " . substr($image_context['parent_content'], 0, 500) . "\n";
        }
        if (!empty($image_context['filename'])) {
            $context_text .= "Filename: " . $image_context['filename'] . "\n";
        }
        
        $prompt = "Analyze this image and generate descriptive alt text (max 125 characters).\n\n";
        if (!empty($context_text)) {
            $prompt .= "Context:\n" . $context_text . "\n\n";
        }
        $prompt .= "Provide ONLY the alt text, nothing else. Make it descriptive, accurate, and SEO-friendly.";
        
        // Check if we have an image URL
        if (!empty($image_context['image_url'])) {
            // Use Vision API (GPT-4 Vision)
            $response = $this->make_vision_request($image_context['image_url'], $prompt);
        } else {
            // Fallback to text-only if no image URL
            $response = $this->make_request($prompt, array(
                'max_tokens' => 50,
                'temperature' => 0.6,
            ));
        }
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return AISEO_Helpers::truncate_text(trim($response), 125, '');
    }
    
    /**
     * Make API request to OpenAI
     *
     * @param string $prompt Prompt text
     * @param array $options Additional options
     * @return string|WP_Error Response text or error
     */
    public function make_request($prompt, $options = array()) {
        // Check API key
        if (empty($this->api_key)) {
            AISEO_Helpers::log('ERROR', 'api_request', 'API key not configured');
            return new WP_Error('no_api_key', __('OpenAI API key not configured', 'aiseo'));
        }
        
        // Check rate limiting
        $rate_limit_check = $this->check_rate_limit();
        if (is_wp_error($rate_limit_check)) {
            return $rate_limit_check;
        }
        
        // Check circuit breaker
        $circuit_check = $this->check_circuit_breaker();
        if (is_wp_error($circuit_check)) {
            return $circuit_check;
        }
        
        // Prepare request
        $defaults = array(
            'max_tokens' => (int) get_option('aiseo_api_max_tokens', 1000),
            'temperature' => (float) get_option('aiseo_api_temperature', 0.7),
        );
        
        $options = wp_parse_args($options, $defaults);
        
        $body = array(
            'model' => $this->model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are an SEO expert assistant. Provide concise, accurate, and helpful SEO content.',
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt,
                ),
            ),
            'max_tokens' => $options['max_tokens'],
            'temperature' => $options['temperature'],
        );
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => get_option('aiseo_api_timeout', 45),
            'sslverify' => true,
        );
        
        // Track start time
        $start_time = microtime(true);
        
        // Make request with retry logic
        $response = $this->make_request_with_retry($args);
        
        // Track end time
        $duration = (microtime(true) - $start_time) * 1000; // milliseconds
        
        if (is_wp_error($response)) {
            $this->record_failure();
            $this->log_usage(false, 0, $duration);
            
            AISEO_Helpers::log('ERROR', 'api_request', $response->get_error_message(), array(
                'duration_ms' => $duration,
            ));
            
            return $response;
        }
        
        // Parse response
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            $error_message = $this->parse_error_response($response_body, $response_code);
            
            $this->record_failure();
            $this->log_usage(false, 0, $duration);
            
            AISEO_Helpers::log('ERROR', 'api_request', $error_message, array(
                'status_code' => $response_code,
                'duration_ms' => $duration,
            ));
            
            return new WP_Error('api_error', $error_message);
        }
        
        $data = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            AISEO_Helpers::log('ERROR', 'api_request', 'Invalid JSON response');
            return new WP_Error('invalid_response', __('Invalid API response', 'aiseo'));
        }
        
        if (!isset($data['choices'][0]['message']['content'])) {
            AISEO_Helpers::log('ERROR', 'api_request', 'Unexpected response format', array('data' => $data));
            return new WP_Error('invalid_response', __('Unexpected API response format', 'aiseo'));
        }
        
        $content = $data['choices'][0]['message']['content'];
        
        // Track token usage
        $tokens_used = isset($data['usage']['total_tokens']) ? $data['usage']['total_tokens'] : 0;
        
        // Reset failure count on success
        delete_transient('aiseo_api_failures');
        
        // Log usage
        $this->log_usage(true, $tokens_used, $duration);
        
        AISEO_Helpers::log('INFO', 'api_request', 'API request successful', array(
            'tokens_used' => $tokens_used,
            'duration_ms' => $duration,
            'model' => $this->model,
        ));
        
        return $content;
    }
    
    /**
     * Make structured request with JSON schema (for title + content separation)
     *
     * @param string $prompt User prompt
     * @param array $schema JSON schema definition
     * @param array $options Additional options
     * @return array|WP_Error Structured response or error
     */
    public function make_structured_request($prompt, $schema, $options = array()) {
        // Check API key
        if (empty($this->api_key)) {
            AISEO_Helpers::log('ERROR', 'api_request', 'API key not configured');
            return new WP_Error('no_api_key', __('OpenAI API key not configured', 'aiseo'));
        }
        
        // Check rate limiting
        $rate_limit_check = $this->check_rate_limit();
        if (is_wp_error($rate_limit_check)) {
            return $rate_limit_check;
        }
        
        // Check circuit breaker
        $circuit_check = $this->check_circuit_breaker();
        if (is_wp_error($circuit_check)) {
            return $circuit_check;
        }
        
        // Prepare request
        $defaults = array(
            'max_tokens' => (int) get_option('aiseo_api_max_tokens', 2000),
            'temperature' => (float) get_option('aiseo_api_temperature', 0.7),
            'max_retries' => 3,
        );
        
        $options = wp_parse_args($options, $defaults);
        $max_retries = $options['max_retries'];
        
        // Retry loop for structured output
        for ($retry = 0; $retry < $max_retries; $retry++) {
            $body = array(
                'model' => $this->model,
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are an SEO expert assistant. Provide structured, accurate content.',
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt,
                    ),
                ),
                'max_tokens' => $options['max_tokens'],
                'temperature' => $options['temperature'],
                'response_format' => array(
                    'type' => 'json_schema',
                    'json_schema' => array(
                        'name' => $schema['name'],
                        'strict' => true,
                        'schema' => $schema['schema'],
                    ),
                ),
            );
            
            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode($body),
                'timeout' => get_option('aiseo_api_timeout', 60),
                'sslverify' => true,
            );
            
            // Track start time
            $start_time = microtime(true);
            
            // Make request
            $response = wp_remote_post(self::API_ENDPOINT, $args);
            
            // Track end time
            $duration = (microtime(true) - $start_time) * 1000; // milliseconds
            
            if (is_wp_error($response)) {
                if ($retry < $max_retries - 1) {
                    AISEO_Helpers::log('WARNING', 'api_structured_request', 'Retry ' . ($retry + 1) . ' after error: ' . $response->get_error_message());
                    sleep(1); // Wait 1 second before retry
                    continue;
                }
                
                $this->record_failure();
                $this->log_usage(false, 0, $duration);
                return $response;
            }
            
            // Parse response
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            
            if ($response_code !== 200) {
                if ($retry < $max_retries - 1) {
                    AISEO_Helpers::log('WARNING', 'api_structured_request', 'Retry ' . ($retry + 1) . ' after HTTP ' . $response_code);
                    sleep(1);
                    continue;
                }
                
                $error_message = $this->parse_error_response($response_body, $response_code);
                $this->record_failure();
                $this->log_usage(false, 0, $duration);
                return new WP_Error('api_error', $error_message);
            }
            
            $data = json_decode($response_body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($retry < $max_retries - 1) {
                    AISEO_Helpers::log('WARNING', 'api_structured_request', 'Retry ' . ($retry + 1) . ' after JSON parse error');
                    sleep(1);
                    continue;
                }
                
                return new WP_Error('invalid_response', __('Invalid API response', 'aiseo'));
            }
            
            if (!isset($data['choices'][0]['message']['content'])) {
                if ($retry < $max_retries - 1) {
                    AISEO_Helpers::log('WARNING', 'api_structured_request', 'Retry ' . ($retry + 1) . ' after missing content');
                    sleep(1);
                    continue;
                }
                
                return new WP_Error('invalid_response', __('Unexpected API response format', 'aiseo'));
            }
            
            $content = $data['choices'][0]['message']['content'];
            $structured_data = json_decode($content, true);
            
            // Validate structured data matches schema
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($structured_data)) {
                if ($retry < $max_retries - 1) {
                    AISEO_Helpers::log('WARNING', 'api_structured_request', 'Retry ' . ($retry + 1) . ' after invalid structured data');
                    sleep(1);
                    continue;
                }
                
                return new WP_Error('invalid_structure', __('API returned invalid structured data', 'aiseo'));
            }
            
            // Validate required fields exist
            $required_fields = isset($schema['schema']['required']) ? $schema['schema']['required'] : array();
            foreach ($required_fields as $field) {
                if (!isset($structured_data[$field]) || empty($structured_data[$field])) {
                    if ($retry < $max_retries - 1) {
                        AISEO_Helpers::log('WARNING', 'api_structured_request', 'Retry ' . ($retry + 1) . ' after missing field: ' . $field);
                        sleep(1);
                        continue 2; // Continue outer loop
                    }
                    
                    return new WP_Error('missing_field', sprintf(__('Required field "%s" is missing', 'aiseo'), $field));
                }
            }
            
            // Success! Track token usage
            $tokens_used = isset($data['usage']['total_tokens']) ? $data['usage']['total_tokens'] : 0;
            delete_transient('aiseo_api_failures');
            $this->log_usage(true, $tokens_used, $duration);
            
            AISEO_Helpers::log('INFO', 'api_structured_request', 'Structured API request successful', array(
                'tokens_used' => $tokens_used,
                'duration_ms' => $duration,
                'model' => $this->model,
                'retries' => $retry,
            ));
            
            return $structured_data;
        }
        
        // If we get here, all retries failed
        return new WP_Error('max_retries_exceeded', __('Failed to get valid structured response after maximum retries', 'aiseo'));
    }
    
    /**
     * Generate content outline with structured output
     *
     * @param string $topic Topic for the outline
     * @param string $keyword Focus keyword (optional)
     * @return array|WP_Error Structured outline or error
     */
    public function generate_outline($topic, $keyword = '') {
        $prompt = "Create a comprehensive content outline for: \"{$topic}\"\n\n";
        
        if (!empty($keyword)) {
            $prompt .= "Focus keyword: {$keyword}\n\n";
        }
        
        $prompt .= "Create a well-structured outline with:\n";
        $prompt .= "- An engaging title\n";
        $prompt .= "- Introduction points (2-3 items)\n";
        $prompt .= "- Main sections with subsections (3-5 sections)\n";
        $prompt .= "- Conclusion points (2-3 items)\n";
        $prompt .= "- Make it SEO-friendly and comprehensive";
        
        $schema = array(
            'name' => 'content_outline',
            'schema' => array(
                'type' => 'object',
                'properties' => array(
                    'title' => array(
                        'type' => 'string',
                        'description' => 'Engaging title for the content',
                    ),
                    'introduction' => array(
                        'type' => 'array',
                        'description' => 'Introduction points',
                        'items' => array(
                            'type' => 'string',
                        ),
                    ),
                    'sections' => array(
                        'type' => 'array',
                        'description' => 'Main content sections',
                        'items' => array(
                            'type' => 'object',
                            'properties' => array(
                                'title' => array('type' => 'string'),
                                'subsections' => array(
                                    'type' => 'array',
                                    'items' => array('type' => 'string'),
                                ),
                            ),
                            'required' => array('title', 'subsections'),
                            'additionalProperties' => false,
                        ),
                    ),
                    'conclusion' => array(
                        'type' => 'array',
                        'description' => 'Conclusion points',
                        'items' => array(
                            'type' => 'string',
                        ),
                    ),
                ),
                'required' => array('title', 'introduction', 'sections', 'conclusion'),
                'additionalProperties' => false,
            ),
        );
        
        return $this->make_structured_request($prompt, $schema, array(
            'max_tokens' => 1500,
            'temperature' => 0.7,
            'max_retries' => 3,
        ));
    }
    
    /**
     * Generate FAQ with structured output
     *
     * @param string $content Content to generate FAQs from
     * @param int $count Number of FAQs to generate
     * @return array|WP_Error Structured FAQs or error
     */
    public function generate_faq($content, $count = 3) {
        $content = AISEO_Helpers::strip_shortcodes_and_tags($content);
        $content = AISEO_Helpers::truncate_text($content, 1000);
        
        $prompt = "Generate {$count} frequently asked questions and answers based on this content:\n\n{$content}\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- Questions should be clear and relevant\n";
        $prompt .= "- Answers should be concise but informative\n";
        $prompt .= "- Cover the most important aspects of the content";
        
        $schema = array(
            'name' => 'faq_list',
            'schema' => array(
                'type' => 'object',
                'properties' => array(
                    'faqs' => array(
                        'type' => 'array',
                        'description' => 'List of FAQ items',
                        'items' => array(
                            'type' => 'object',
                            'properties' => array(
                                'question' => array(
                                    'type' => 'string',
                                    'description' => 'The question',
                                ),
                                'answer' => array(
                                    'type' => 'string',
                                    'description' => 'The answer',
                                ),
                            ),
                            'required' => array('question', 'answer'),
                            'additionalProperties' => false,
                        ),
                    ),
                ),
                'required' => array('faqs'),
                'additionalProperties' => false,
            ),
        );
        
        return $this->make_structured_request($prompt, $schema, array(
            'max_tokens' => 1500,
            'temperature' => 0.7,
            'max_retries' => 3,
        ));
    }
    
    /**
     * Generate content brief with structured output
     *
     * @param string $topic Topic for the brief
     * @param string $keyword Focus keyword (optional)
     * @return array|WP_Error Structured brief or error
     */
    public function generate_content_brief($topic, $keyword = '') {
        $prompt = "Create a detailed content brief for the topic: '$topic'";
        if (!empty($keyword)) {
            $prompt .= " with focus keyword: '$keyword'";
        }
        $prompt .= ".\n\nProvide a comprehensive content brief with all necessary details for creating high-quality SEO content.";
        
        $schema = array(
            'name' => 'content_brief',
            'schema' => array(
                'type' => 'object',
                'properties' => array(
                    'title' => array(
                        'type' => 'string',
                        'description' => 'Compelling title for the content',
                    ),
                    'keywords' => array(
                        'type' => 'array',
                        'description' => 'Target keywords (5-7 keywords)',
                        'items' => array('type' => 'string'),
                    ),
                    'word_count' => array(
                        'type' => 'string',
                        'description' => 'Recommended word count range (e.g., "1500-2000")',
                    ),
                    'structure' => array(
                        'type' => 'array',
                        'description' => 'Main sections/headings (5-7 items)',
                        'items' => array('type' => 'string'),
                    ),
                    'key_topics' => array(
                        'type' => 'array',
                        'description' => 'Key topics to cover (5-7 items)',
                        'items' => array('type' => 'string'),
                    ),
                    'seo_tips' => array(
                        'type' => 'string',
                        'description' => 'Brief SEO recommendations',
                    ),
                ),
                'required' => array('title', 'keywords', 'word_count', 'structure', 'key_topics', 'seo_tips'),
                'additionalProperties' => false,
            ),
        );
        
        return $this->make_structured_request($prompt, $schema, array(
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'max_retries' => 3,
        ));
    }
    
    /**
     * Make Vision API request to OpenAI
     *
     * @param string $image_url URL of the image to analyze
     * @param string $prompt Prompt text
     * @return string|WP_Error Response text or error
     */
    public function make_vision_request($image_url, $prompt) {
        // Check API key
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('OpenAI API key not configured', 'aiseo'));
        }
        
        // Use current model (gpt-4o-mini supports vision natively)
        $vision_model = $this->model;
        
        // Check if URL is local/inaccessible and convert to base64
        $image_data = $this->prepare_image_for_vision($image_url);
        if (is_wp_error($image_data)) {
            return $image_data;
        }
        
        $body = array(
            'model' => $vision_model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => array(
                        array(
                            'type' => 'text',
                            'text' => $prompt
                        ),
                        array(
                            'type' => 'image_url',
                            'image_url' => array(
                                'url' => $image_data
                            )
                        )
                    )
                )
            ),
            'max_tokens' => 100
        );
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => 60, // Vision API may take longer
            'sslverify' => true,
        );
        
        $start_time = microtime(true);
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);
        $duration = (microtime(true) - $start_time) * 1000;
        
        if (is_wp_error($response)) {
            $status_code = wp_remote_retrieve_response_code($response);
            AISEO_Helpers::log('ERROR', 'vision_api_request', 'Vision API request failed', array(
                'error' => $response->get_error_message(),
                'status_code' => $status_code,
            ));
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            $error_message = $this->parse_error_response($response_body, $response_code);
            AISEO_Helpers::log('ERROR', 'vision_api_request', 'Vision API error', array(
                'status_code' => $response_code,
                'error' => $error_message,
            ));
            return new WP_Error('api_error', $error_message);
        }
        
        $data = json_decode($response_body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', __('Invalid API response', 'aiseo'));
        }
        
        $content = $data['choices'][0]['message']['content'];
        $tokens_used = isset($data['usage']['total_tokens']) ? $data['usage']['total_tokens'] : 0;
        
        $this->log_usage(true, $tokens_used, $duration);
        
        AISEO_Helpers::log('INFO', 'vision_api_request', 'Vision API request successful', array(
            'tokens_used' => $tokens_used,
            'duration_ms' => $duration,
        ));
        
        return $content;
    }
    
    /**
     * Prepare image for Vision API
     * Converts local/inaccessible URLs to base64 data URLs
     *
     * @param string $image_url Image URL
     * @return string|WP_Error Image URL or base64 data URL, or error
     */
    private function prepare_image_for_vision($image_url) {
        // Check if URL is local or inaccessible
        $parsed_url = parse_url($image_url);
        $is_local = false;
        
        if (isset($parsed_url['host'])) {
            $local_domains = array('localhost', '127.0.0.1', '::1');
            $is_local = in_array($parsed_url['host'], $local_domains) || 
                       strpos($parsed_url['host'], '.local') !== false ||
                       strpos($parsed_url['host'], '.test') !== false ||
                       strpos($parsed_url['host'], '.dev') !== false;
        }
        
        // If it's a local URL, convert to base64
        if ($is_local) {
            // Get the file path from the URL
            $upload_dir = wp_upload_dir();
            $base_url = $upload_dir['baseurl'];
            
            // Replace the base URL with the base path
            $file_path = str_replace($base_url, $upload_dir['basedir'], $image_url);
            
            // Check if file exists
            if (!file_exists($file_path)) {
                AISEO_Helpers::log('ERROR', 'vision_api_request', 'Image file not found', array(
                    'image_url' => $image_url,
                    'file_path' => $file_path,
                ));
                return new WP_Error('file_not_found', __('Image file not found', 'aiseo'));
            }
            
            // Read file and encode to base64
            $image_data = file_get_contents($file_path);
            if ($image_data === false) {
                return new WP_Error('file_read_error', __('Failed to read image file', 'aiseo'));
            }
            
            // Get mime type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_path);
            finfo_close($finfo);
            
            // Create base64 data URL
            $base64 = base64_encode($image_data);
            $data_url = "data:{$mime_type};base64,{$base64}";
            
            AISEO_Helpers::log('INFO', 'vision_api_request', 'Converted local image to base64', array(
                'original_url' => $image_url,
                'file_size' => strlen($image_data),
                'mime_type' => $mime_type,
            ));
            
            return $data_url;
        }
        
        // For public URLs, return as-is
        return $image_url;
    }
    
    /**
     * Make request with retry logic
     *
     * @param array $args Request arguments
     * @return array|WP_Error Response or error
     */
    private function make_request_with_retry($args) {
        $max_retries = 3;
        $retry_count = 0;
        
        while ($retry_count < $max_retries) {
            $response = wp_remote_post(self::API_ENDPOINT, $args);
            
            if (!is_wp_error($response)) {
                $status_code = wp_remote_retrieve_response_code($response);
                
                // Success
                if ($status_code === 200) {
                    return $response;
                }
                
                // Retry on specific errors
                if (in_array($status_code, array(429, 500, 502, 503, 504))) {
                    $retry_count++;
                    $wait_time = pow(2, $retry_count); // Exponential backoff: 2, 4, 8 seconds
                    
                    AISEO_Helpers::log('WARNING', 'api_request', "Retrying request (attempt $retry_count)", array(
                        'status_code' => $status_code,
                        'wait_time' => $wait_time,
                    ));
                    
                    sleep($wait_time);
                    continue;
                }
                
                // Don't retry on client errors
                if ($status_code >= 400 && $status_code < 500) {
                    return $response;
                }
            }
            
            $retry_count++;
            
            if ($retry_count < $max_retries) {
                sleep(pow(2, $retry_count));
            }
        }
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return new WP_Error('max_retries', __('Maximum retry attempts exceeded', 'aiseo'));
    }
    
    /**
     * Check rate limiting
     *
     * @return true|WP_Error True if OK, error if rate limited
     */
    private function check_rate_limit() {
        $user_id = get_current_user_id();
        
        // Per-user rate limiting
        $user_requests = get_transient('aiseo_user_requests_' . $user_id);
        if ($user_requests === false) {
            $user_requests = 0;
        }
        
        $rate_limit_per_minute = get_option('aiseo_rate_limit_per_minute', 60);
        
        if ($user_requests >= $rate_limit_per_minute) {
            $wait_seconds = 60 - (time() - (int)get_transient('aiseo_user_requests_start_' . $user_id));
            $wait_seconds = max(1, $wait_seconds);
            return new WP_Error('rate_limit', sprintf(__('Rate limit exceeded. Please wait %d seconds and try again.', 'aiseo'), $wait_seconds));
        }
        
        // Track start time for better wait time calculation
        if ($user_requests === 0) {
            set_transient('aiseo_user_requests_start_' . $user_id, time(), 60);
        }
        
        set_transient('aiseo_user_requests_' . $user_id, $user_requests + 1, 60);
        
        return true;
    }
    
    /**
     * Check circuit breaker
     *
     * @return true|WP_Error True if OK, error if circuit open
     */
    private function check_circuit_breaker() {
        $failure_count = get_transient('aiseo_api_failures');
        
        if ($failure_count >= 5) {
            $cooldown = get_transient('aiseo_api_cooldown');
            
            if ($cooldown) {
                return new WP_Error('circuit_breaker', __('API temporarily unavailable. Please try again later.', 'aiseo'));
            }
            
            // Reset after cooldown
            delete_transient('aiseo_api_failures');
        }
        
        return true;
    }
    
    /**
     * Record API failure
     */
    private function record_failure() {
        $failure_count = get_transient('aiseo_api_failures');
        if ($failure_count === false) {
            $failure_count = 0;
        }
        
        $failure_count++;
        set_transient('aiseo_api_failures', $failure_count, 300); // 5 minutes
        
        if ($failure_count >= 5) {
            set_transient('aiseo_api_cooldown', true, 300); // 5 minute cooldown
        }
    }
    
    /**
     * Parse error response
     *
     * @param string $response_body Response body
     * @param int $status_code HTTP status code
     * @return string Error message
     */
    private function parse_error_response($response_body, $status_code) {
        $data = json_decode($response_body, true);
        
        if (isset($data['error']['message'])) {
            return $data['error']['message'];
        }
        
        switch ($status_code) {
            case 401:
                return __('Invalid API key', 'aiseo');
            case 429:
                return __('Rate limit exceeded', 'aiseo');
            case 500:
            case 502:
            case 503:
            case 504:
                return __('OpenAI service temporarily unavailable', 'aiseo');
            default:
                return sprintf(__('API error: %d', 'aiseo'), $status_code);
        }
    }
    
    /**
     * Log API usage statistics
     *
     * @param bool $success Success status
     * @param int $tokens_used Tokens used
     * @param float $duration Duration in milliseconds
     */
    private function log_usage($success, $tokens_used, $duration) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aiseo_usage_stats';
        $date = current_time('Y-m-d');
        $request_type = 'generate_content';
        
        // Check if record exists for today
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table query
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE date = %s AND request_type = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $date,
            $request_type
        ));
        
        if ($existing) {
            // Update existing record
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, no WP equivalent
            $wpdb->update(
                $table_name,
                array(
                    'requests_count' => $existing->requests_count + 1,
                    'tokens_used' => $existing->tokens_used + $tokens_used,
                    'avg_response_time' => (($existing->avg_response_time * $existing->requests_count) + $duration) / ($existing->requests_count + 1),
                    'success_count' => $success ? $existing->success_count + 1 : $existing->success_count,
                    'error_count' => $success ? $existing->error_count : $existing->error_count + 1,
                ),
                array('id' => $existing->id),
                array('%d', '%d', '%d', '%d', '%d'),
                array('%d')
            );
        } else {
            // Insert new record
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, no WP equivalent
            $wpdb->insert(
                $table_name,
                array(
                    'date' => $date,
                    'request_type' => $request_type,
                    'requests_count' => 1,
                    'tokens_used' => $tokens_used,
                    'avg_response_time' => $duration,
                    'success_count' => $success ? 1 : 0,
                    'error_count' => $success ? 0 : 1,
                ),
                array('%s', '%s', '%d', '%d', '%d', '%d', '%d')
            );
        }
        
        // Update monthly token usage
        $current_month_tokens = get_option('aiseo_token_usage_month', 0);
        update_option('aiseo_token_usage_month', $current_month_tokens + $tokens_used);
        
        $total_tokens = get_option('aiseo_token_usage_total', 0);
        update_option('aiseo_token_usage_total', $total_tokens + $tokens_used);
    }
    
    /**
     * Validate API key
     *
     * @return bool|WP_Error True if valid, error otherwise
     */
    public function check_api_key() {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('API key not configured', 'aiseo'));
        }
        
        // Make a minimal request to test the key
        $response = $this->make_request('Test', array('max_tokens' => 5));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Update validation status
        update_option('aiseo_api_validation_status', 'valid');
        update_option('aiseo_last_api_validation', current_time('mysql'));
        
        return true;
    }
}
