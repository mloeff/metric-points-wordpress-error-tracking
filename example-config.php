<?php
/**
 * Example configuration for Metric Points Error Tracking
 * Copy and customize this file as needed for your specific setup
 */

// Example 1: Basic setup with custom error filtering
function mpet_custom_config($config) {
    // Add custom metadata
    $config['metadata']['theme'] = get_stylesheet();
    $config['metadata']['user_role'] = is_user_logged_in() ? 'logged_in' : 'anonymous';
    
    return $config;
}
add_filter('mpet_script_config', 'mpet_custom_config');

// Example 2: Conditional loading based on user role
function mpet_conditional_loading($should_load) {
    // Don't track errors for administrators
    if (current_user_can('administrator')) {
        return false;
    }
    
    // Don't track on admin pages
    if (is_admin()) {
        return false;
    }
    
    return $should_load;
}
add_filter('mpet_should_load_script', 'mpet_conditional_loading');

// Example 3: Custom error data processing
function mpet_custom_error_data($error_data) {
    // Add current post ID if available
    if (is_singular()) {
        $error_data['metadata']['post_id'] = get_the_ID();
    }
    
    // Add user information (be careful with privacy)
    if (is_user_logged_in()) {
        $error_data['metadata']['user_id'] = get_current_user_id();
    }
    
    return $error_data;
}
add_filter('mpet_error_data', 'mpet_custom_error_data');

// Example 4: Custom ignore patterns for specific themes/plugins
function mpet_theme_specific_ignores($config) {
    // Add theme-specific error patterns to ignore
    $theme_ignores = array(
        'jquery.*is not defined',
        'Uncaught TypeError.*slider',
        'ResizeObserver loop limit exceeded'
    );
    
    if (!isset($config['ignorePatterns'])) {
        $config['ignorePatterns'] = array();
    }
    
    $config['ignorePatterns'] = array_merge($config['ignorePatterns'], $theme_ignores);
    
    return $config;
}
add_filter('mpet_script_config', 'mpet_theme_specific_ignores');

// Example 5: Environment-based configuration
function mpet_environment_config($config) {
    // Different settings for different environments
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Development environment
        $config['debug'] = true;
        $config['sampleRate'] = 100;
    } elseif (wp_get_environment_type() === 'staging') {
        // Staging environment
        $config['sampleRate'] = 50;
    } else {
        // Production environment
        $config['sampleRate'] = 10; // Lower sample rate for production
    }
    
    return $config;
}
add_filter('mpet_script_config', 'mpet_environment_config');

// Example 6: Custom endpoint based on environment
function mpet_dynamic_endpoint($config) {
    $environment = wp_get_environment_type();
    
    // Use different endpoints for different environments
    switch ($environment) {
        case 'development':
            $config['endpoint'] = 'http://localhost:3000/api/errors';
            break;
        case 'staging':
            $config['endpoint'] = 'https://staging-api.example.com/errors';
            break;
        default:
            // Use the configured endpoint from admin
            break;
    }
    
    return $config;
}
// add_filter('mpet_script_config', 'mpet_dynamic_endpoint');

// Example 7: Add custom CSS for admin interface
function mpet_custom_admin_styles() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'settings_page_metric-points-error-tracking') {
        ?>
        <style>
        .mpet-status-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .mpet-info-section {
            border-left: 4px solid #0073aa;
        }
        </style>
        <?php
    }
}
add_action('admin_head', 'mpet_custom_admin_styles');

// Example 8: Log critical errors to WordPress error log
function mpet_log_critical_errors($config) {
    // Add a custom error handler that also logs to WordPress
    ?>
    <script>
    (function() {
        const originalReportError = window.MetricPointsErrorTracker.reportError;
        window.MetricPointsErrorTracker.reportError = function(errorData) {
            // Log critical errors to console for WordPress debugging
            if (errorData.message && errorData.message.includes('Critical')) {
                console.error('Critical Error Detected:', errorData);
            }
            
            // Call original function
            return originalReportError.call(this, errorData);
        };
    })();
    </script>
    <?php
}
add_action('mpet_after_script_output', 'mpet_log_critical_errors');
