/**
 * AISEO Admin JavaScript
 *
 * @package AISEO
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Global nonce management
    window.aiseoNonce = {
        current: aiseoAdmin.nonce,
        refreshing: false,
        
        /**
         * Get current nonce
         */
        get: function() {
            return this.current;
        },
        
        /**
         * Refresh nonce
         */
        refresh: function(callback) {
            if (this.refreshing) {
                // Already refreshing, wait for it
                setTimeout(() => {
                    if (callback) callback(this.current);
                }, 100);
                return;
            }
            
            this.refreshing = true;
            
            $.ajax({
                url: aiseoAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: aiseoAdmin.nonceRefreshAction
                },
                success: (response) => {
                    this.refreshing = false;
                    if (response.success && response.data.nonce) {
                        this.current = response.data.nonce;
                        aiseoAdmin.nonce = response.data.nonce;
                        if (callback) callback(this.current);
                    } else {
                        console.error('AISEO: Failed to refresh nonce');
                        if (callback) callback(null);
                    }
                },
                error: () => {
                    this.refreshing = false;
                    console.error('AISEO: Nonce refresh request failed');
                    if (callback) callback(null);
                }
            });
        }
    };
    
    /**
     * Enhanced AJAX wrapper with automatic nonce refresh
     */
    window.aiseoAjax = function(options) {
        // Add nonce to data
        if (!options.data) {
            options.data = {};
        }
        options.data.nonce = window.aiseoNonce.get();
        
        // Store original error handler
        const originalError = options.error;
        const originalSuccess = options.success;
        
        // Wrap success handler to detect nonce errors
        options.success = function(response) {
            // Check if response indicates nonce failure
            if (!response.success && response.data && 
                (response.data.indexOf('nonce') !== -1 || 
                 response.data.indexOf('expired') !== -1 ||
                 response.data.indexOf('invalid') !== -1)) {
                
                console.warn('AISEO: Nonce expired, refreshing and retrying...');
                
                // Refresh nonce and retry
                window.aiseoNonce.refresh((newNonce) => {
                    if (newNonce) {
                        // Update nonce and retry
                        options.data.nonce = newNonce;
                        $.ajax(options);
                    } else {
                        // Refresh failed, show error
                        alert(aiseoAdmin.strings.sessionExpired || 'Session expired. Please refresh the page.');
                        if (originalError) {
                            originalError(null, 'error', 'Nonce refresh failed');
                        }
                    }
                });
                return;
            }
            
            // Call original success handler
            if (originalSuccess) {
                originalSuccess(response);
            }
        };
        
        // Wrap error handler
        options.error = function(xhr, status, error) {
            // Check if it's a 403 (forbidden) which often indicates nonce failure
            if (xhr.status === 403) {
                console.warn('AISEO: 403 error, likely nonce issue. Refreshing and retrying...');
                
                window.aiseoNonce.refresh((newNonce) => {
                    if (newNonce) {
                        options.data.nonce = newNonce;
                        $.ajax(options);
                    } else {
                        alert(aiseoAdmin.strings.sessionExpired || 'Session expired. Please refresh the page.');
                        if (originalError) {
                            originalError(xhr, status, error);
                        }
                    }
                });
                return;
            }
            
            // Call original error handler
            if (originalError) {
                originalError(xhr, status, error);
            }
        };
        
        // Make the AJAX request
        return $.ajax(options);
    };

    const AISEO_Admin = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.setupNonceRefresh();
        },
        
        /**
         * Setup automatic nonce refresh every 10 minutes
         */
        setupNonceRefresh: function() {
            // Refresh nonce every 10 minutes to prevent expiration
            setInterval(() => {
                window.aiseoNonce.refresh();
            }, 10 * 60 * 1000); // 10 minutes
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Generate post
            $(document).on('click', '.aiseo-generate-post', this.generatePost);
            
            // Generate meta
            $(document).on('click', '.aiseo-generate-meta', this.generateMeta);
            
            // Analyze content
            $(document).on('click', '.aiseo-analyze-content', this.analyzeContent);
            
            // Load stats
            $(document).on('click', '.aiseo-load-stats', this.loadStats);
            
            // Form submissions
            $(document).on('submit', '.aiseo-ajax-form', this.handleFormSubmit);
        },
        
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            if (typeof $.fn.tooltip !== 'undefined') {
                $('[data-tooltip]').tooltip();
            }
        },
        
        /**
         * Generate AI post
         */
        generatePost: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $form = $btn.closest('form');
            const topic = $form.find('[name="topic"]').val();
            const keyword = $form.find('[name="keyword"]').val();
            const length = $form.find('[name="content_length"]').val();
            
            if (!topic) {
                AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, 'Topic is required');
                return;
            }
            
            AISEO_Admin.setLoading($btn, true);
            
            $.ajax({
                url: aiseoAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aiseo_create_post',
                    nonce: aiseoAdmin.nonce,
                    topic: topic,
                    keyword: keyword,
                    length: length
                },
                success: function(response) {
                    if (response.success) {
                        AISEO_Admin.showNotice('success', aiseoAdmin.strings.success, 
                            'Post created! <a href="' + response.data.edit_url + '">Edit post</a>');
                        $form[0].reset();
                    } else {
                        AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, response.data.message);
                    }
                },
                error: function() {
                    AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, 'AJAX request failed');
                },
                complete: function() {
                    AISEO_Admin.setLoading($btn, false);
                }
            });
        },
        
        /**
         * Generate meta
         */
        generateMeta: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const postId = $btn.data('post-id');
            const metaType = $btn.data('meta-type');
            
            if (!postId) {
                AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, 'Post ID is required');
                return;
            }
            
            AISEO_Admin.setLoading($btn, true);
            
            $.ajax({
                url: aiseoAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aiseo_admin_action',
                    action_type: 'generate_meta',
                    nonce: aiseoAdmin.nonce,
                    post_id: postId,
                    meta_type: metaType
                },
                success: function(response) {
                    if (response.success) {
                        const $output = $btn.siblings('.aiseo-meta-output');
                        $output.val(response.data.content).show();
                        AISEO_Admin.showNotice('success', aiseoAdmin.strings.success, 'Meta generated successfully');
                    } else {
                        AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, response.data.message);
                    }
                },
                error: function() {
                    AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, 'AJAX request failed');
                },
                complete: function() {
                    AISEO_Admin.setLoading($btn, false);
                }
            });
        },
        
        /**
         * Analyze content
         */
        analyzeContent: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const postId = $btn.data('post-id');
            
            if (!postId) {
                AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, 'Post ID is required');
                return;
            }
            
            AISEO_Admin.setLoading($btn, true);
            
            $.ajax({
                url: aiseoAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aiseo_admin_action',
                    action_type: 'analyze_content',
                    nonce: aiseoAdmin.nonce,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        AISEO_Admin.displayAnalysisResults(response.data);
                        AISEO_Admin.showNotice('success', aiseoAdmin.strings.success, 'Analysis complete');
                    } else {
                        AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, response.data.message);
                    }
                },
                error: function() {
                    AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, 'AJAX request failed');
                },
                complete: function() {
                    AISEO_Admin.setLoading($btn, false);
                }
            });
        },
        
        /**
         * Load statistics
         */
        loadStats: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const statType = $btn.data('stat-type');
            const $container = $btn.closest('.aiseo-widget').find('.aiseo-stat-container');
            
            AISEO_Admin.setLoading($btn, true);
            
            $.ajax({
                url: aiseoAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aiseo_admin_action',
                    action_type: 'get_stats',
                    nonce: aiseoAdmin.nonce,
                    stat_type: statType
                },
                success: function(response) {
                    if (response.success) {
                        AISEO_Admin.displayStats($container, response.data);
                    } else {
                        AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, response.data.message);
                    }
                },
                error: function() {
                    AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, 'AJAX request failed');
                },
                complete: function() {
                    AISEO_Admin.setLoading($btn, false);
                }
            });
        },
        
        /**
         * Handle form submit
         */
        handleFormSubmit: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('[type="submit"]');
            const formData = $form.serialize();
            
            AISEO_Admin.setLoading($submitBtn, true);
            
            $.ajax({
                url: aiseoAdmin.ajaxUrl,
                type: 'POST',
                data: formData + '&nonce=' + aiseoAdmin.nonce,
                success: function(response) {
                    if (response.success) {
                        AISEO_Admin.showNotice('success', aiseoAdmin.strings.success, response.data.message);
                        if (response.data.reload) {
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, response.data.message);
                    }
                },
                error: function() {
                    AISEO_Admin.showNotice('error', aiseoAdmin.strings.error, 'AJAX request failed');
                },
                complete: function() {
                    AISEO_Admin.setLoading($submitBtn, false);
                }
            });
        },
        
        /**
         * Display analysis results
         */
        displayAnalysisResults: function(data) {
            const $container = $('.aiseo-analysis-results');
            
            if (!$container.length) {
                return;
            }
            
            let html = '<div class="aiseo-card">';
            html += '<div class="aiseo-card-header">';
            html += '<h3 class="aiseo-card-title">Analysis Results</h3>';
            html += '<span class="aiseo-badge aiseo-badge-' + AISEO_Admin.getScoreBadge(data.overall_score) + '">';
            html += 'Score: ' + data.overall_score + '/100</span>';
            html += '</div>';
            html += '<div class="aiseo-card-body">';
            
            // Display metrics
            $.each(data, function(key, value) {
                if (key !== 'overall_score' && typeof value === 'object') {
                    html += '<div class="aiseo-metric">';
                    html += '<strong>' + AISEO_Admin.formatKey(key) + ':</strong> ';
                    html += '<span>' + JSON.stringify(value) + '</span>';
                    html += '</div>';
                }
            });
            
            html += '</div></div>';
            
            $container.html(html).show();
        },
        
        /**
         * Display statistics
         */
        displayStats: function($container, data) {
            let html = '<div class="aiseo-stat-grid">';
            
            $.each(data, function(key, value) {
                html += '<div class="aiseo-stat-item">';
                html += '<div class="aiseo-stat-value">' + value + '</div>';
                html += '<div class="aiseo-stat-label">' + AISEO_Admin.formatKey(key) + '</div>';
                html += '</div>';
            });
            
            html += '</div>';
            
            $container.html(html);
        },
        
        /**
         * Set loading state
         */
        setLoading: function($element, loading) {
            if (loading) {
                $element.prop('disabled', true)
                    .addClass('aiseo-loading')
                    .data('original-text', $element.html())
                    .html('<span class="aiseo-spinner"></span> ' + aiseoAdmin.strings.generating);
            } else {
                $element.prop('disabled', false)
                    .removeClass('aiseo-loading')
                    .html($element.data('original-text'));
            }
        },
        
        /**
         * Show notice
         */
        showNotice: function(type, title, message) {
            const $notice = $('<div class="aiseo-alert aiseo-alert-' + type + '"></div>');
            $notice.html('<strong>' + title + '</strong> ' + message);
            
            $('.aiseo-tab-content').prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        /**
         * Get score badge type
         */
        getScoreBadge: function(score) {
            if (score >= 80) return 'success';
            if (score >= 60) return 'warning';
            return 'error';
        },
        
        /**
         * Format key for display
         */
        formatKey: function(key) {
            return key.replace(/_/g, ' ')
                .replace(/\b\w/g, function(l) { return l.toUpperCase(); });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        AISEO_Admin.init();
    });
    
})(jQuery);
