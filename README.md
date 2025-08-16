# Metric Points WordPress Error Tracking Plugin

A comprehensive WordPress plugin that integrates your website with the Metric Points JavaScript Error Tracking service. This plugin automatically captures JavaScript errors, unhandled promise rejections, and other client-side issues, sending them to your Metric Points service for analysis and monitoring.

## Features

- **Easy Integration**: Simple admin interface for configuration
- **Real-time Error Tracking**: Captures JavaScript errors and unhandled promise rejections
- **Configurable Sampling**: Set sample rates to manage high-traffic sites
- **Error Filtering**: Ignore specific error patterns using regex
- **Debug Mode**: Enhanced logging for development and troubleshooting
- **Connection Testing**: Built-in connection test to verify your configuration
- **Internationalization**: Ready for translation into multiple languages
- **Secure**: Proper sanitization and security measures
- **WordPress Hooks**: Extensible with filters and actions

## Installation

### Manual Installation

1. Download or clone this repository
2. Upload the plugin folder to your WordPress `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the plugin through **Settings > Error Tracking**

### Via Git

```bash
cd /path/to/your/wordpress/wp-content/plugins/
git clone https://github.com/mloeff/metric-points-wordpress-error-tracking.git
```

## Configuration

After activation, go to **Settings > Error Tracking** in your WordPress admin panel.

### Required Settings

- **API Key**: Your Metric Points API authentication key (format: `err_xxxxxxxxx.xxxxx`)
- **Endpoint URL**: The base URL of your Metric Points error tracking service (e.g., `https://metricpoints.com/api/error-reports`)

**Important**: The plugin will automatically append your API key to the endpoint URL, so you only need to provide the base endpoint.

### Optional Settings

- **Enable Error Tracking**: Toggle to enable/disable the tracking (default: disabled)
- **Sample Rate**: Percentage of errors to track (0-100%, default: 100%)
- **Debug Mode**: Enable console logging for troubleshooting (default: disabled)
- **Ignore Error Patterns**: Regex patterns for errors to ignore (one per line)

## Getting Your API Key

1. **Sign up** for a Metric Points account at [metricpoints.com](https://metricpoints.com)
2. **Create an Error Tracking project** in your dashboard
3. **Copy the API key** (starts with `err_`) from your project settings
4. **Use the endpoint URL** shown in your project settings

## Usage

Once configured and enabled, the plugin will:

1. Automatically inject the error tracking script into all pages
2. Capture JavaScript errors and unhandled promise rejections
3. Send error data to your configured Metric Points endpoint
4. Include metadata such as:
   - WordPress version
   - Plugin version
   - Site URL
   - Theme information
   - User role (logged in vs anonymous)
   - User agent information
   - Timestamp
   - Error stack traces

## Error Data Structure

The plugin sends error data in the following JSON format:

```json
{
  "type": "javascript_error|unhandled_rejection",
  "message": "Error message",
  "source": "file.js",
  "line": 123,
  "column": 45,
  "stack": "Error stack trace",
  "timestamp": "2025-08-03T12:00:00.000Z",
  "url": "https://example.com/page",
  "userAgent": "Browser user agent string",
  "level": "error",
  "metadata": {
    "wordpress_version": "6.3",
    "plugin_version": "1.0.0",
    "site_url": "https://example.com",
    "theme": "twentytwentyfour",
    "user_role": "logged_in"
  }
}
```

## API Integration

### Authentication

The plugin sends requests to the endpoint with the API key in the URL path:
- **URL Format**: `https://yourdomain.com/api/error-reports/{api_key}`
- **Headers**: `Content-Type: application/json`

### Sample Rate

Use the sample rate setting to control the percentage of errors reported. This is useful for high-traffic sites where you want to monitor errors without overwhelming your service.

### Error Filtering

Add regex patterns to ignore specific errors:
```
Script error\.
Non-Error promise rejection captured
ResizeObserver loop limit exceeded
```

## WordPress Hooks

The plugin provides several WordPress hooks for customization:

### Actions
- `mpet_before_script_output` - Fired before the tracking script is output
- `mpet_after_script_output` - Fired after the tracking script is output

### Filters
- `mpet_script_config` - Filter the JavaScript configuration object
- `mpet_should_load_script` - Control whether the script should load

### Example Filter Usage

```php
// Customize the error tracking configuration
add_filter('mpet_script_config', function($config) {
    // Add custom metadata
    $config['metadata']['environment'] = wp_get_environment_type();
    $config['metadata']['custom_field'] = 'custom_value';
    
    // Add custom error data filter
    $config['onErrorDataFilter'] = 'my_custom_error_filter';
    
    return $config;
});

// Conditionally disable tracking
add_filter('mpet_should_load_script', function($should_load) {
    // Don't load for administrators
    if (current_user_can('administrator')) {
        return false;
    }
    
    // Don't load on admin pages
    if (is_admin()) {
        return false;
    }
    
    return $should_load;
});

// Custom error data processing
function my_custom_error_filter($errorData) {
    // Add current post ID if available
    if (is_singular()) {
        $errorData['metadata']['post_id'] = get_the_ID();
    }
    
    // Add user information (be careful with privacy)
    if (is_user_logged_in()) {
        $errorData['metadata']['user_id'] = get_current_user_id();
    }
    
    return $errorData;
}
```

## Development

### File Structure

```
metric-points-error-tracking/
├── metric-points-error-tracking.php  # Main plugin file
├── assets/
│   ├── admin.js                      # Admin panel JavaScript
│   └── admin.css                     # Admin panel styles
├── languages/
│   └── metric-points-error-tracking.pot  # Translation template
├── example-config.php                # Example configuration file
├── README.md
├── INSTALL.md
└── LICENSE
```

## Security

The plugin implements several security measures:

- Input sanitization for all settings
- Nonce verification for AJAX requests
- Capability checks for admin access
- Proper escaping of output data
- Validation of URLs and numeric inputs

## Browser Compatibility

The tracking script is compatible with:
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Internet Explorer 11+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- Minimal impact on page load times
- Asynchronous error reporting
- Configurable sample rates for high-traffic sites
- No external dependencies

## Troubleshooting

### Common Issues

1. **Errors not appearing**: Check that the plugin is enabled and properly configured
2. **Connection test fails**: Verify your API key and endpoint URL
3. **Too many errors**: Reduce the sample rate or add ignore patterns
4. **Script not loading**: Check for JavaScript conflicts or ad blockers

### Debug Mode

Enable debug mode to see console messages about:
- Plugin initialization
- Error capture and filtering
- API request success/failure

### Testing Your Setup

1. Save your configuration
2. Click "Test Connection" to verify API connectivity
3. Check browser console for debug messages (if debug mode enabled)
4. Trigger a test error to verify tracking works

## Support

For support, please:
1. Check the troubleshooting section above
2. Enable debug mode to gather more information
3. Open an issue on the [GitHub repository](https://github.com/mloeff/metric-points-wordpress-error-tracking/issues)

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later. See the [LICENSE](LICENSE) file for details.

## Changelog

### 1.0.0
- Initial release
- Basic error tracking functionality
- Admin interface for configuration
- Connection testing
- Internationalization support
- Error filtering and sampling
- WordPress hooks for extensibility
- Improved API compatibility with Metric Points web-app

