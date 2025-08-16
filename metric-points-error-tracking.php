<?php
/**
 * Plugin Name: Metric Points Error Tracking
 * Plugin URI: https://github.com/mloeff/metric-points-wordpress-error-tracking
 * Description: Integrates Metric Points JavaScript Error Tracking service with WordPress. Provides easy configuration through admin interface.
 * Version: 1.0.0
 * Author: Michael Loeff
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: metric-points-error-tracking
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MPET_VERSION', '1.0.0');
define('MPET_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MPET_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Main plugin class
 */
class MetricPointsErrorTracking {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('metric-points-error-tracking', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize admin interface
        if (is_admin()) {
            $this->init_admin();
        }
        
        // Add frontend scripts
        add_action('wp_head', array($this, 'add_tracking_script'));
    }
    
    /**
     * Initialize admin interface
     */
    private function init_admin() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Metric Points Error Tracking', 'metric-points-error-tracking'),
            __('Error Tracking', 'metric-points-error-tracking'),
            'manage_options',
            'metric-points-error-tracking',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('mpet_settings', 'mpet_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        register_setting('mpet_settings', 'mpet_endpoint_url', array(
            'sanitize_callback' => 'esc_url_raw'
        ));
        
        register_setting('mpet_settings', 'mpet_enabled', array(
            'sanitize_callback' => 'absint'
        ));
        
        register_setting('mpet_settings', 'mpet_debug_mode', array(
            'sanitize_callback' => 'absint'
        ));
        
        register_setting('mpet_settings', 'mpet_ignore_errors', array(
            'sanitize_callback' => 'sanitize_textarea_field'
        ));
        
        register_setting('mpet_settings', 'mpet_sample_rate', array(
            'sanitize_callback' => array($this, 'sanitize_sample_rate')
        ));
    }
    
    /**
     * Sanitize sample rate (0-100)
     */
    public function sanitize_sample_rate($value) {
        $value = floatval($value);
        return max(0, min(100, $value));
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Settings saved successfully!', 'metric-points-error-tracking'); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('mpet_settings');
                do_settings_sections('mpet_settings');
                ?>
                
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="mpet_enabled"><?php _e('Enable Error Tracking', 'metric-points-error-tracking'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="mpet_enabled" name="mpet_enabled" value="1" <?php checked(1, get_option('mpet_enabled', 0)); ?> />
                            <p class="description"><?php _e('Enable or disable error tracking on your site.', 'metric-points-error-tracking'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="mpet_api_key"><?php _e('API Key', 'metric-points-error-tracking'); ?> *</label>
                        </th>
                        <td>
                            <input type="text" id="mpet_api_key" name="mpet_api_key" value="<?php echo esc_attr(get_option('mpet_api_key')); ?>" class="regular-text" required />
                            <p class="description"><?php _e('Your Metric Points API key for authentication (format: err_xxxxxxxxx).', 'metric-points-error-tracking'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="mpet_endpoint_url"><?php _e('Endpoint URL', 'metric-points-error-tracking'); ?> *</label>
                        </th>
                        <td>
                            <input type="url" id="mpet_endpoint_url" name="mpet_endpoint_url" value="<?php echo esc_attr(get_option('mpet_endpoint_url')); ?>" class="regular-text" required />
                            <p class="description"><?php _e('The endpoint URL for your Metric Points error tracking service (e.g., https://metricpoints.com/api/error-reports).', 'metric-points-error-tracking'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="mpet_sample_rate"><?php _e('Sample Rate (%)', 'metric-points-error-tracking'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="mpet_sample_rate" name="mpet_sample_rate" value="<?php echo esc_attr(get_option('mpet_sample_rate', 100)); ?>" min="0" max="100" step="0.1" />
                            <p class="description"><?php _e('Percentage of errors to track (0-100). Use lower values for high-traffic sites.', 'metric-points-error-tracking'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="mpet_debug_mode"><?php _e('Debug Mode', 'metric-points-error-tracking'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="mpet_debug_mode" name="mpet_debug_mode" value="1" <?php checked(1, get_option('mpet_debug_mode', 0)); ?> />
                            <p class="description"><?php _e('Enable debug mode to log additional information to browser console.', 'metric-points-error-tracking'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="mpet_ignore_errors"><?php _e('Ignore Error Patterns', 'metric-points-error-tracking'); ?></label>
                        </th>
                        <td>
                            <textarea id="mpet_ignore_errors" name="mpet_ignore_errors" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('mpet_ignore_errors')); ?></textarea>
                            <p class="description"><?php _e('One error pattern per line. Supports regex patterns. Errors matching these patterns will be ignored.', 'metric-points-error-tracking'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div class="mpet-status-section">
                    <h2><?php _e('Connection Status', 'metric-points-error-tracking'); ?></h2>
                    <div id="mpet-connection-status">
                        <p><?php _e('Save settings to test connection...', 'metric-points-error-tracking'); ?></p>
                    </div>
                    <button type="button" id="mpet-test-connection" class="button button-secondary" <?php echo (!get_option('mpet_api_key') || !get_option('mpet_endpoint_url')) ? 'disabled' : ''; ?>>
                        <?php _e('Test Connection', 'metric-points-error-tracking'); ?>
                    </button>
                </div>
                
                <?php submit_button(); ?>
            </form>
            
            <div class="mpet-info-section">
                <h2><?php _e('Integration Information', 'metric-points-error-tracking'); ?></h2>
                <p><?php _e('This plugin automatically adds the Metric Points error tracking script to your website. The script will:', 'metric-points-error-tracking'); ?></p>
                <ul>
                    <li><?php _e('Capture JavaScript errors and exceptions', 'metric-points-error-tracking'); ?></li>
                    <li><?php _e('Track unhandled promise rejections', 'metric-points-error-tracking'); ?></li>
                    <li><?php _e('Collect browser and user agent information', 'metric-points-error-tracking'); ?></li>
                    <li><?php _e('Send error data to your Metric Points service', 'metric-points-error-tracking'); ?></li>
                </ul>
                
                <h3><?php _e('Plugin Version', 'metric-points-error-tracking'); ?></h3>
                <p><?php echo sprintf(__('Version: %s', 'metric-points-error-tracking'), MPET_VERSION); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_metric-points-error-tracking' !== $hook) {
            return;
        }
        
        wp_enqueue_script(
            'mpet-admin',
            MPET_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            MPET_VERSION,
            true
        );
        
        wp_enqueue_style(
            'mpet-admin',
            MPET_PLUGIN_URL . 'assets/admin.css',
            array(),
            MPET_VERSION
        );
        
        wp_localize_script('mpet-admin', 'mpet_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mpet_test_connection'),
            'testing_text' => __('Testing...', 'metric-points-error-tracking'),
            'test_text' => __('Test Connection', 'metric-points-error-tracking')
        ));
    }
    
    /**
     * Add tracking script to frontend
     */
    public function add_tracking_script() {
        // Check if tracking is enabled
        if (!get_option('mpet_enabled', 0)) {
            return;
        }
        
        $api_key = get_option('mpet_api_key');
        $endpoint_url = get_option('mpet_endpoint_url');
        
        // Don't load if required settings are missing
        if (!$api_key || !$endpoint_url) {
            return;
        }
        
        // Allow filtering of whether script should load
        $should_load = apply_filters('mpet_should_load_script', true);
        if (!$should_load) {
            return;
        }
        
        $sample_rate = get_option('mpet_sample_rate', 100);
        $debug_mode = get_option('mpet_debug_mode', 0);
        $ignore_patterns = get_option('mpet_ignore_errors', '');
        
        // Convert ignore patterns to JavaScript array
        $ignore_array = array();
        if (!empty($ignore_patterns)) {
            $patterns = explode("\n", $ignore_patterns);
            foreach ($patterns as $pattern) {
                $pattern = trim($pattern);
                if (!empty($pattern)) {
                    $ignore_array[] = $pattern;
                }
            }
        }
        
        // Build base configuration
        $config = array(
            'apiKey' => $api_key,
            'endpoint' => $endpoint_url,
            'sampleRate' => floatval($sample_rate),
            'debug' => $debug_mode ? true : false,
            'ignorePatterns' => $ignore_array,
            'metadata' => array(
                'wordpress_version' => get_bloginfo('version'),
                'plugin_version' => MPET_VERSION,
                'site_url' => get_site_url(),
                'theme' => get_stylesheet(),
                'user_role' => is_user_logged_in() ? 'logged_in' : 'anonymous'
            )
        );
        
        // Allow filtering of configuration
        $config = apply_filters('mpet_script_config', $config);
        
        // Fire action before script output
        do_action('mpet_before_script_output', $config);
        
        ?>
        <script type="text/javascript">
        (function() {
            // Metric Points Error Tracking Configuration
            window.MetricPointsConfig = <?php echo json_encode($config); ?>;
            
            // Initialize error tracking
            window.MetricPointsErrorTracker = {
                init: function() {
                    var self = this;
                    
                    // Handle JavaScript errors
                    window.onerror = function(message, source, lineno, colno, error) {
                        var errorData = {
                            type: 'javascript_error',
                            message: message,
                            source: source,
                            line: lineno,
                            column: colno,
                            stack: error ? error.stack : null,
                            timestamp: new Date().toISOString(),
                            url: window.location.href,
                            userAgent: navigator.userAgent,
                            level: 'error'
                        };
                        
                        // Allow filtering of error data
                        if (window.MetricPointsConfig.onErrorDataFilter) {
                            errorData = window.MetricPointsConfig.onErrorDataFilter(errorData);
                        }
                        
                        self.reportError(errorData);
                        return false;
                    };
                    
                    // Handle unhandled promise rejections
                    window.addEventListener('unhandledrejection', function(event) {
                        var errorData = {
                            type: 'unhandled_rejection',
                            message: event.reason ? event.reason.toString() : 'Unhandled Promise Rejection',
                            stack: event.reason && event.reason.stack ? event.reason.stack : null,
                            timestamp: new Date().toISOString(),
                            url: window.location.href,
                            userAgent: navigator.userAgent,
                            level: 'error'
                        };
                        
                        // Allow filtering of error data
                        if (window.MetricPointsConfig.onErrorDataFilter) {
                            errorData = window.MetricPointsConfig.onErrorDataFilter(errorData);
                        }
                        
                        self.reportError(errorData);
                    });
                    
                    if (window.MetricPointsConfig.debug) {
                        console.log('Metric Points Error Tracking initialized');
                    }
                },
                
                shouldIgnoreError: function(message) {
                    var patterns = window.MetricPointsConfig.ignorePatterns;
                    for (var i = 0; i < patterns.length; i++) {
                        try {
                            var regex = new RegExp(patterns[i], 'i');
                            if (regex.test(message)) {
                                return true;
                            }
                        } catch (e) {
                            // Invalid regex, try simple string match
                            if (message.toLowerCase().indexOf(patterns[i].toLowerCase()) !== -1) {
                                return true;
                            }
                        }
                    }
                    return false;
                },
                
                reportError: function(errorData) {
                    // Check sample rate
                    if (Math.random() * 100 > window.MetricPointsConfig.sampleRate) {
                        return;
                    }
                    
                    // Check ignore patterns
                    if (this.shouldIgnoreError(errorData.message)) {
                        if (window.MetricPointsConfig.debug) {
                            console.log('Ignoring error:', errorData.message);
                        }
                        return;
                    }
                    
                    // Add metadata
                    errorData.metadata = window.MetricPointsConfig.metadata;
                    
                    // Send to endpoint
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', window.MetricPointsConfig.endpoint + '/' + encodeURIComponent(window.MetricPointsConfig.apiKey), true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            if (window.MetricPointsConfig.debug) {
                                if (xhr.status === 200 || xhr.status === 204) {
                                    console.log('Error reported successfully');
                                } else {
                                    console.error('Failed to report error:', xhr.status);
                                }
                            }
                        }
                    };
                    
                    try {
                        xhr.send(JSON.stringify(errorData));
                    } catch (e) {
                        if (window.MetricPointsConfig.debug) {
                            console.error('Failed to send error report:', e);
                        }
                    }
                }
            };
            
            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    window.MetricPointsErrorTracker.init();
                });
            } else {
                window.MetricPointsErrorTracker.init();
            }
        })();
        </script>
        <?php
        
        // Fire action after script output
        do_action('mpet_after_script_output', $config);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        if (!get_option('mpet_enabled')) {
            add_option('mpet_enabled', 0);
        }
        if (!get_option('mpet_sample_rate')) {
            add_option('mpet_sample_rate', 100);
        }
        if (!get_option('mpet_debug_mode')) {
            add_option('mpet_debug_mode', 0);
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }
}

// AJAX handler for connection testing
add_action('wp_ajax_mpet_test_connection', 'mpet_test_connection_callback');

function mpet_test_connection_callback() {
    check_ajax_referer('mpet_test_connection', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    $api_key = get_option('mpet_api_key');
    $endpoint_url = get_option('mpet_endpoint_url');
    
    if (!$api_key || !$endpoint_url) {
        wp_send_json_error(__('API Key and Endpoint URL are required.', 'metric-points-error-tracking'));
        return;
    }
    
    // Build the full endpoint URL with API key
    $test_url = rtrim($endpoint_url, '/') . '/' . $api_key;
    
    // Test connection with a simple ping
    $response = wp_remote_post($test_url, array(
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode(array(
            'type' => 'connection_test',
            'message' => 'WordPress Plugin Connection Test',
            'timestamp' => current_time('c'),
            'source' => 'wordpress_plugin',
            'level' => 'info'
        )),
        'timeout' => 10
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error(sprintf(__('Connection failed: %s', 'metric-points-error-tracking'), $response->get_error_message()));
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code >= 200 && $status_code < 300) {
            wp_send_json_success(__('Connection successful!', 'metric-points-error-tracking'));
        } else {
            wp_send_json_error(sprintf(__('Connection failed with status code: %d', 'metric-points-error-tracking'), $status_code));
        }
    }
}

// Initialize the plugin
MetricPointsErrorTracking::get_instance();
