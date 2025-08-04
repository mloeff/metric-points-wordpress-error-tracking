## WordPress Plugin: Metric Points Error Tracking

### Quick Installation Guide

1. **Upload Plugin**
   - Upload the entire plugin folder to `/wp-content/plugins/`
   - Or install via WordPress admin: Plugins > Add New > Upload Plugin

2. **Activate Plugin**
   - Go to WordPress admin > Plugins
   - Find "Metric Points Error Tracking" and click "Activate"

3. **Configure Settings**
   - Go to Settings > Error Tracking
   - Enter your API Key and Endpoint URL
   - Enable error tracking
   - Test the connection

4. **Advanced Configuration (Optional)**
   - Set sample rate for high-traffic sites
   - Add error patterns to ignore
   - Enable debug mode during development

### Minimum Requirements
- WordPress 5.0 or higher
- PHP 7.0 or higher
- cURL support (for connection testing)

### Configuration Examples

**Basic Setup:**
- API Key: `your-api-key-here`
- Endpoint URL: `https://your-service.com/api/errors`
- Sample Rate: `100` (track all errors)

**High Traffic Site:**
- Sample Rate: `10` (track 10% of errors)
- Ignore Patterns:
  ```
  Script error\.
  Non-Error promise rejection
  ResizeObserver loop limit exceeded
  ```

**Development Site:**
- Debug Mode: Enabled
- Sample Rate: `100`

### Testing Your Setup

1. Save your configuration
2. Click "Test Connection" to verify API connectivity
3. Check browser console for debug messages (if debug mode enabled)
4. Trigger a test error to verify tracking works

### Need Help?

- Check the full README.md for detailed documentation
- Enable debug mode to troubleshoot issues
- Visit the GitHub repository for support
