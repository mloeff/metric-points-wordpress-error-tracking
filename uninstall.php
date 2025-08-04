<?php
/**
 * Uninstall script for Metric Points Error Tracking plugin
 * This file is executed when the plugin is deleted from WordPress admin
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all plugin options from the database
delete_option('mpet_api_key');
delete_option('mpet_endpoint_url');
delete_option('mpet_enabled');
delete_option('mpet_debug_mode');
delete_option('mpet_ignore_errors');
delete_option('mpet_sample_rate');

// Remove any cached data or transients
delete_transient('mpet_connection_test');

// Clean up any user meta data (if we stored any)
// delete_user_meta_by_key('mpet_user_setting');

// Note: We don't remove any log files or other data that might be useful
// for troubleshooting or migration to another error tracking solution
