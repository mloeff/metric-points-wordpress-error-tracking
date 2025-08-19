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
            'Metric Points Error Tracking',
            'Error Tracking',
            'manage_options',
            'metric-points-error-tracking',
            array($this, 'admin_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('mpet_options', 'mpet_api_key');
        register_setting('mpet_options', 'mpet_sample_rate');
        register_setting('mpet_options', 'mpet_debug_mode');
        register_setting('mpet_options', 'mpet_ignore_patterns');
        
        // Enhanced features settings
        register_setting('mpet_options', 'mpet_session_replay_enabled');
        register_setting('mpet_options', 'mpet_session_replay_max_events');
        register_setting('mpet_options', 'mpet_mouse_tracking');
        register_setting('mpet_options', 'mpet_click_tracking');
        register_setting('mpet_options', 'mpet_scroll_tracking');
        register_setting('mpet_options', 'mpet_keypress_tracking');
        register_setting('mpet_options', 'mpet_focus_tracking');
        register_setting('mpet_options', 'mpet_url_tracking');
        
        register_setting('mpet_options', 'mpet_performance_enabled');
        register_setting('mpet_options', 'mpet_memory_monitoring');
        
        register_setting('mpet_options', 'mpet_privacy_mask_input');
        register_setting('mpet_options', 'mpet_privacy_exclude_fields');
        register_setting('mpet_options', 'mpet_user_consent');
        
        add_settings_section(
            'mpet_main_section',
            'Required Settings',
            array($this, 'main_section_callback'),
            'mpet_options'
        );
        
        add_settings_section(
            'mpet_enhanced_section',
            'Enhanced Features',
            array($this, 'enhanced_section_callback'),
            'mpet_options'
        );
        
        add_settings_section(
            'mpet_privacy_section',
            'Privacy & Security',
            array($this, 'privacy_section_callback'),
            'mpet_options'
        );
        
        add_settings_field(
            'mpet_api_key',
            'API Key',
            array($this, 'api_key_callback'),
            'mpet_options',
            'mpet_main_section'
        );
        
        add_settings_field(
            'mpet_sample_rate',
            'Sample Rate',
            array($this, 'sample_rate_callback'),
            'mpet_options',
            'mpet_main_section'
        );
        
        add_settings_field(
            'mpet_debug_mode',
            'Debug Mode',
            array($this, 'debug_mode_callback'),
            'mpet_options',
            'mpet_main_section'
        );
        
        add_settings_field(
            'mpet_ignore_patterns',
            'Ignore Patterns',
            array($this, 'ignore_patterns_callback'),
            'mpet_options',
            'mpet_main_section'
        );
        
        // Enhanced features fields
        add_settings_field(
            'mpet_session_replay_enabled',
            'Enable Session Replay',
            array($this, 'session_replay_enabled_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        add_settings_field(
            'mpet_session_replay_max_events',
            'Max Events to Track',
            array($this, 'max_events_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        add_settings_field(
            'mpet_mouse_tracking',
            'Mouse Movement Tracking',
            array($this, 'mouse_tracking_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        add_settings_field(
            'mpet_click_tracking',
            'Click Tracking',
            array($this, 'click_tracking_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        add_settings_field(
            'mpet_scroll_tracking',
            'Scroll Tracking',
            array($this, 'scroll_tracking_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        add_settings_field(
            'mpet_keypress_tracking',
            'Keypress Tracking',
            array($this, 'keypress_tracking_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        add_settings_field(
            'mpet_focus_tracking',
            'Focus Tracking',
            array($this, 'focus_tracking_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        add_settings_field(
            'mpet_url_tracking',
            'URL Change Tracking',
            array($this, 'url_tracking_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        add_settings_field(
            'mpet_performance_enabled',
            'Performance Monitoring',
            array($this, 'performance_enabled_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        add_settings_field(
            'mpet_memory_monitoring',
            'Memory Usage Monitoring',
            array($this, 'memory_monitoring_callback'),
            'mpet_options',
            'mpet_enhanced_section'
        );
        
        // Privacy fields
        add_settings_field(
            'mpet_privacy_mask_input',
            'Mask User Input',
            array($this, 'privacy_mask_input_callback'),
            'mpet_options',
            'mpet_privacy_section'
        );
        
        add_settings_field(
            'mpet_privacy_exclude_fields',
            'Exclude Sensitive Fields',
            array($this, 'privacy_exclude_fields_callback'),
            'mpet_options',
            'mpet_privacy_section'
        );
        
        add_settings_field(
            'mpet_user_consent',
            'Require User Consent',
            array($this, 'user_consent_callback'),
            'mpet_options',
            'mpet_options'
        );
    }
    
    /**
     * Sanitize sample rate (0-100)
     */
    public function sanitize_sample_rate($value) {
        $value = floatval($value);
        return max(0, min(100, $value));
    }
    
    /**
     * Section callbacks
     */
    public function main_section_callback() {
        echo '<p>Configure the basic error tracking settings. Only the API Key is required.</p>';
    }
    
    public function enhanced_section_callback() {
        echo '<p>Enable advanced features like session replay, performance monitoring, and detailed user tracking.</p>';
    }
    
    public function privacy_section_callback() {
        echo '<p>Configure privacy and security settings to protect user data and comply with regulations.</p>';
    }
    
    /**
     * Field callbacks
     */
    public function api_key_callback() {
        $api_key = get_option('mpet_api_key');
        echo '<input type="text" id="mpet_api_key" name="mpet_api_key" value="' . esc_attr($api_key) . '" class="regular-text" required />';
        echo '<p class="description">Your Metric Points API key for error tracking.</p>';
    }
    
    public function sample_rate_callback() {
        $sample_rate = get_option('mpet_sample_rate', 1.0);
        echo '<input type="number" id="mpet_sample_rate" name="mpet_sample_rate" value="' . esc_attr($sample_rate) . '" min="0.1" max="1.0" step="0.1" class="small-text" />';
        echo '<p class="description">Percentage of errors to track (0.1 = 10%, 1.0 = 100%).</p>';
    }
    
    public function debug_mode_callback() {
        $debug_mode = get_option('mpet_debug_mode', false);
        echo '<input type="checkbox" id="mpet_debug_mode" name="mpet_debug_mode" value="1" ' . checked(1, $debug_mode, false) . ' />';
        echo '<label for="mpet_debug_mode">Enable debug logging in browser console</label>';
    }
    
    public function ignore_patterns_callback() {
        $ignore_patterns = get_option('mpet_ignore_patterns', '');
        echo '<textarea id="mpet_ignore_patterns" name="mpet_ignore_patterns" rows="4" class="large-text">' . esc_textarea($ignore_patterns) . '</textarea>';
        echo '<p class="description">Enter error message patterns to ignore (one per line). Supports regex patterns.</p>';
    }
    
    public function session_replay_enabled_callback() {
        $enabled = get_option('mpet_session_replay_enabled', true);
        echo '<input type="checkbox" id="mpet_session_replay_enabled" name="mpet_session_replay_enabled" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_session_replay_enabled">Enable session replay functionality</label>';
    }
    
    public function max_events_callback() {
        $max_events = get_option('mpet_session_replay_max_events', 1000);
        echo '<input type="number" id="mpet_session_replay_max_events" name="mpet_session_replay_max_events" value="' . esc_attr($max_events) . '" min="100" max="10000" step="100" class="small-text" />';
        echo '<p class="description">Maximum number of events to track per session.</p>';
    }
    
    public function mouse_tracking_callback() {
        $enabled = get_option('mpet_mouse_tracking', true);
        echo '<input type="checkbox" id="mpet_mouse_tracking" name="mpet_mouse_tracking" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_mouse_tracking">Track mouse movements</label>';
    }
    
    public function click_tracking_callback() {
        $enabled = get_option('mpet_click_tracking', true);
        echo '<input type="checkbox" id="mpet_click_tracking" name="mpet_click_tracking" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_click_tracking">Track mouse clicks</label>';
    }
    
    public function scroll_tracking_callback() {
        $enabled = get_option('mpet_scroll_tracking', true);
        echo '<input type="checkbox" id="mpet_scroll_tracking" name="mpet_scroll_tracking" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_scroll_tracking">Track scroll positions</label>';
    }
    
    public function keypress_tracking_callback() {
        $enabled = get_option('mpet_keypress_tracking', true);
        echo '<input type="checkbox" id="mpet_keypress_tracking" name="mpet_keypress_tracking" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_keypress_tracking">Track keyboard input</label>';
    }
    
    public function focus_tracking_callback() {
        $enabled = get_option('mpet_focus_tracking', true);
        echo '<input type="checkbox" id="mpet_focus_tracking" name="mpet_focus_tracking" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_focus_tracking">Track focus changes</label>';
    }
    
    public function url_tracking_callback() {
        $enabled = get_option('mpet_url_tracking', true);
        echo '<input type="checkbox" id="mpet_url_tracking" name="mpet_url_tracking" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_url_tracking">Track URL changes</label>';
    }
    
    public function performance_enabled_callback() {
        $enabled = get_option('mpet_performance_enabled', true);
        echo '<input type="checkbox" id="mpet_performance_enabled" name="mpet_performance_enabled" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_performance_enabled">Monitor page performance metrics</label>';
    }
    
    public function memory_monitoring_callback() {
        $enabled = get_option('mpet_memory_monitoring', true);
        echo '<input type="checkbox" id="mpet_memory_monitoring" name="mpet_memory_monitoring" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_memory_monitoring">Monitor memory usage</label>';
    }
    
    public function privacy_mask_input_callback() {
        $enabled = get_option('mpet_privacy_mask_input', true);
        echo '<input type="checkbox" id="mpet_privacy_mask_input" name="mpet_privacy_mask_input" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_privacy_mask_input">Mask sensitive user input data</label>';
    }
    
    public function privacy_exclude_fields_callback() {
        $exclude_fields = get_option('mpet_privacy_exclude_fields', 'password,credit-card,ssn,passwd');
        echo '<input type="text" id="mpet_privacy_exclude_fields" name="mpet_privacy_exclude_fields" value="' . esc_attr($exclude_fields) . '" class="regular-text" />';
        echo '<p class="description">Comma-separated list of field names to exclude from tracking.</p>';
    }
    
    public function user_consent_callback() {
        $enabled = get_option('mpet_user_consent', false);
        echo '<input type="checkbox" id="mpet_user_consent" name="mpet_user_consent" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="mpet_user_consent">Require explicit user consent before tracking</label>';
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
                settings_fields('mpet_options');
                do_settings_sections('mpet_options');
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
                    <button type="button" id="mpet-test-connection" class="button button-secondary" <?php echo (!get_option('mpet_api_key')) ? 'disabled' : ''; ?>>
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
     * Add the tracking script to the page
     */
    public function add_tracking_script() {
        $api_key = get_option('mpet_api_key');
        $sample_rate = get_option('mpet_sample_rate', 1.0);
        $debug_mode = get_option('mpet_debug_mode', false);
        $ignore_patterns = get_option('mpet_ignore_patterns', '');
        
        if (empty($api_key)) {
            return;
        }
        
        // Convert ignore patterns to array
        $ignore_array = array();
        if (!empty($ignore_patterns)) {
            $ignore_array = array_filter(array_map('trim', explode("\n", $ignore_patterns)));
        }
        
        // Check if script should be loaded
        if (!apply_filters('mpet_should_load_script', true)) {
            return;
        }
        
        // Get enhanced features settings
        $session_replay_enabled = get_option('mpet_session_replay_enabled', true);
        $max_events = get_option('mpet_session_replay_max_events', 1000);
        $mouse_tracking = get_option('mpet_mouse_tracking', true);
        $click_tracking = get_option('mpet_click_tracking', true);
        $scroll_tracking = get_option('mpet_scroll_tracking', true);
        $keypress_tracking = get_option('mpet_keypress_tracking', true);
        $focus_tracking = get_option('mpet_focus_tracking', true);
        $url_tracking = get_option('mpet_url_tracking', true);
        
        $performance_enabled = get_option('mpet_performance_enabled', true);
        $memory_monitoring = get_option('mpet_memory_monitoring', true);
        
        $privacy_mask_input = get_option('mpet_privacy_mask_input', true);
        $privacy_exclude_fields = get_option('mpet_privacy_exclude_fields', 'password,credit-card,ssn,passwd');
        $user_consent = get_option('mpet_user_consent', false);
        
        // Convert exclude fields to array
        $exclude_fields_array = array();
        if (!empty($privacy_exclude_fields)) {
            $exclude_fields_array = array_filter(array_map('trim', explode(',', $privacy_exclude_fields)));
        }
        
        // Build base configuration
        $config = array(
            'apiKey' => $api_key,
            'endpoint' => 'https://metricpoints.com/api/error-reports', // Fixed base URL
            'sampleRate' => floatval($sample_rate),
            'debug' => $debug_mode ? true : false,
            'ignorePatterns' => $ignore_array,
            'sessionReplay' => array(
                'enabled' => $session_replay_enabled ? true : false,
                'maxEvents' => intval($max_events),
                'mouseTracking' => $mouse_tracking ? true : false,
                'clickTracking' => $click_tracking ? true : false,
                'scrollTracking' => $scroll_tracking ? true : false,
                'keypressTracking' => $keypress_tracking ? true : false,
                'focusTracking' => $focus_tracking ? true : false,
                'urlTracking' => $url_tracking ? true : false
            ),
            'performance' => array(
                'enabled' => $performance_enabled ? true : false,
                'captureMetrics' => $performance_enabled ? true : false,
                'memoryMonitoring' => $memory_monitoring ? true : false
            ),
            'privacy' => array(
                'maskUserInput' => $privacy_mask_input ? true : false,
                'excludeSensitiveFields' => $exclude_fields_array,
                'userConsent' => $user_consent ? true : false
            ),
            'metadata' => array(
                'wordpress_version' => get_bloginfo('version'),
                'plugin_version' => MPET_VERSION,
                'site_url' => get_site_url(),
                'theme' => get_stylesheet(),
                'user_role' => is_user_logged_in() ? 'logged_in' : 'anonymous',
                'plugin_name' => 'metric-points-error-tracking',
                'environment' => defined('WP_DEBUG') && WP_DEBUG ? 'development' : 'production'
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
            
            // Load the enhanced error tracker script
            var script = document.createElement('script');
            script.src = '<?php echo plugin_dir_url(__FILE__); ?>js/metric-points-error-tracker.js';
            script.async = true;
            script.onload = function() {
                console.log('Metric Points Error Tracker loaded successfully');
            };
            script.onerror = function() {
                console.error('Failed to load Metric Points Error Tracker');
            };
            document.head.appendChild(script);
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
    
    if (!$api_key) {
        wp_send_json_error(__('API Key is required.', 'metric-points-error-tracking'));
        return;
    }
    
    // Build the full endpoint URL with API key
    $test_url = 'https://metricpoints.com/api/error-reports/' . $api_key;
    
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
