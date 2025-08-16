jQuery(document).ready(function($) {
    'use strict';
    
    // Test connection functionality
    $('#mpet-test-connection').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $status = $('#mpet-connection-status');
        
        // Disable button and show loading state
        $button.prop('disabled', true).text(mpet_ajax.testing_text);
        $status.html('<p><span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span>' + mpet_ajax.testing_text + '</p>');
        
        // Make AJAX request
        $.ajax({
            url: mpet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mpet_test_connection',
                nonce: mpet_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<div class="notice notice-success inline"><p><span class="dashicons dashicons-yes-alt"></span> ' + response.data + '</p></div>');
                } else {
                    $status.html('<div class="notice notice-error inline"><p><span class="dashicons dashicons-warning"></span> ' + response.data + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                $status.html('<div class="notice notice-error inline"><p><span class="dashicons dashicons-warning"></span> Connection test failed: ' + error + '</p></div>');
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).text(mpet_ajax.test_text);
            }
        });
    });
    
    // Enable/disable test connection button based on required fields
    function toggleTestButton() {
        var apiKey = $('#mpet_api_key').val().trim();
        var $button = $('#mpet-test-connection');
        
        if (apiKey) {
            $button.prop('disabled', false);
        } else {
            $button.prop('disabled', true);
        }
    }
    
    // Monitor required fields
    $('#mpet_api_key').on('input', toggleTestButton);
    
    // Form validation
    $('form').on('submit', function(e) {
        var apiKey = $('#mpet_api_key').val().trim();
        var enabled = $('#mpet_enabled').is(':checked');
        
        if (enabled && !apiKey) {
            e.preventDefault();
            alert('Please enter your API Key when error tracking is enabled.');
            return false;
        }
        
        // Validate sample rate
        var sampleRate = parseFloat($('#mpet_sample_rate').val());
        if (isNaN(sampleRate) || sampleRate < 0 || sampleRate > 100) {
            e.preventDefault();
            alert('Sample rate must be a number between 0 and 100.');
            return false;
        }
    });
    
    // Show/hide advanced options
    var $advancedToggle = $('<button type="button" class="button button-link mpet-advanced-toggle">Show Advanced Options</button>');
    var $advancedOptions = $('.form-table tr').slice(2); // Hide debug mode and ignore patterns by default
    
    $advancedOptions.hide();
    $('.form-table').after($advancedToggle);
    
    $advancedToggle.on('click', function() {
        if ($advancedOptions.is(':visible')) {
            $advancedOptions.hide();
            $(this).text('Show Advanced Options');
        } else {
            $advancedOptions.show();
            $(this).text('Hide Advanced Options');
        }
    });
    
    // Auto-save draft functionality for large configurations
    var autoSaveTimer;
    var hasUnsavedChanges = false;
    
    $('input, textarea, select').on('change input', function() {
        hasUnsavedChanges = true;
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Could implement auto-save here if needed
        }, 5000);
    });
    
    // Warn user about unsaved changes
    $(window).on('beforeunload', function() {
        if (hasUnsavedChanges) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
    // Clear unsaved changes flag on form submit
    $('form').on('submit', function() {
        hasUnsavedChanges = false;
    });
    
    // Initialize
    toggleTestButton();
});
