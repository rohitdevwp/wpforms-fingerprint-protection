/**
 * Admin JavaScript for WPForms Fingerprint Protection
 *
 * @package WPForms_Fingerprint_Protection
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Confirm before marking as spam
        $('.wpfp-btn-spam').on('click', function(e) {
            if (!confirm('Mark this fingerprint as spam? Future submissions from this device will be blocked.')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Confirm before unmarking spam
        $('.wpfp-btn-unspam').on('click', function(e) {
            if (!confirm('Remove spam marking? This device will be allowed to submit again.')) {
                e.preventDefault();
                return false;
            }
        });
        
        // API Key validation
        $('#wpfp_api_key').on('blur', function() {
            var apiKey = $(this).val().trim();
            
            if (apiKey.length > 0 && apiKey.length < 10) {
                alert('API key seems too short. Please verify your key from fingerprint.com');
            }
        });
        
        // Settings form validation
        $('form[action=""]').on('submit', function(e) {
            var apiKey = $('#wpfp_api_key').val().trim();
            var rateLimit = parseInt($('#wpfp_rate_limit').val());
            var timeWindow = parseInt($('#wpfp_time_window').val());
            
            if (apiKey === '') {
                alert('Please enter your FingerprintJS API key.');
                $('#wpfp_api_key').focus();
                e.preventDefault();
                return false;
            }
            
            if (rateLimit < 1 || rateLimit > 100) {
                alert('Rate limit must be between 1 and 100.');
                $('#wpfp_rate_limit').focus();
                e.preventDefault();
                return false;
            }
            
            if (timeWindow < 1 || timeWindow > 24) {
                alert('Time window must be between 1 and 24 hours.');
                $('#wpfp_time_window').focus();
                e.preventDefault();
                return false;
            }
        });
        
        // Auto-dismiss success notices after 5 seconds
        setTimeout(function() {
            $('.notice-success.is-dismissible').fadeOut();
        }, 5000);
        
    });
    
})(jQuery);