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

- **API Key**: Your Metric Points API authentication key
- **Endpoint URL**: The URL of your Metric Points error tracking service endpoint

### Optional Settings

- **Enable Error Tracking**: Toggle to enable/disable the tracking (default: disabled)
- **Sample Rate**: Percentage of errors to track (0-100%, default: 100%)
- **Debug Mode**: Enable console logging for troubleshooting (default: disabled)
- **Ignore Error Patterns**: Regex patterns for errors to ignore (one per line)

## Usage

Once configured and enabled, the plugin will:

1. Automatically inject the error tracking script into all pages
2. Capture JavaScript errors and unhandled promise rejections
3. Send error data to your configured Metric Points endpoint
4. Include metadata such as:
   - WordPress version
   - Plugin version
   - Site URL
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
  "metadata": {
    "wordpress_version": "6.3",
    "plugin_version": "1.0.0",
    "site_url": "https://example.com"
  }
}
```

## API Integration

### Authentication

The plugin sends requests with the following headers:
- `Authorization: Bearer YOUR_API_KEY`
- `Content-Type: application/json`

### Sample Rate

Use the sample rate setting to control the percentage of errors reported. This is useful for high-traffic sites where you want to monitor errors without overwhelming your service.

### Error Filtering

Add regex patterns to ignore specific errors:
```
Script error\.
Non-Error promise rejection captured
ResizeObserver loop limit exceeded
```

## Development

### File Structure

```
metric-points-wordpress-error-tracking/
├── metric-points-error-tracking.php  # Main plugin file
├── assets/
│   ├── admin.js                      # Admin panel JavaScript
│   └── admin.css                     # Admin panel styles
├── languages/
│   └── metric-points-error-tracking.pot  # Translation template
├── README.md
└── LICENSE
```

### Hooks and Filters

The plugin provides several WordPress hooks for customization:

#### Actions
- `mpet_before_script_output` - Fired before the tracking script is output
- `mpet_after_script_output` - Fired after the tracking script is output

#### Filters
- `mpet_script_config` - Filter the JavaScript configuration object
- `mpet_error_data` - Filter error data before sending (server-side)
- `mpet_should_load_script` - Control whether the script should load

### Example Filter Usage

```php
// Customize the error tracking configuration
add_filter('mpet_script_config', function($config) {
    $config['customField'] = 'custom_value';
    return $config;
});

// Conditionally disable tracking
add_filter('mpet_should_load_script', function($should_load) {
    // Don't load for admin users
    return !current_user_can('administrator');
});
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

