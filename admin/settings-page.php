<?php
/**
 * Settings Page Template
 *
 * @package WPForms_Fingerprint_Protection
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wpfp-settings-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wpfp-intro-box">
        <h2><?php esc_html_e('Welcome to WPForms Fingerprint Protection!', 'wpforms-fingerprint-protection'); ?></h2>
        <p><?php esc_html_e('Protect your forms from spam and fake submissions using advanced device fingerprinting technology.', 'wpforms-fingerprint-protection'); ?></p>
    </div>

    <div class="wpfp-settings-container">
        <div class="wpfp-settings-main">
            <form method="post" action="">
                <?php wp_nonce_field('wpfp_settings_action', 'wpfp_settings_nonce'); ?>
                
                <!-- API Key Section -->
                <div class="wpfp-card">
                    <h2><?php esc_html_e('FingerprintJS API Configuration', 'wpforms-fingerprint-protection'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wpfp_api_key">
                                    <?php esc_html_e('API Key', 'wpforms-fingerprint-protection'); ?>
                                    <span class="required">*</span>
                                </label>
                            </th>
                            <td>
                                <input 
                                    type="text" 
                                    id="wpfp_api_key" 
                                    name="wpfp_api_key" 
                                    value="<?php echo esc_attr($api_key); ?>" 
                                    class="regular-text"
                                    placeholder="<?php esc_attr_e('Enter your FingerprintJS API key', 'wpforms-fingerprint-protection'); ?>"
                                >
                                <p class="description">
                                    <?php 
                                    echo sprintf(
                                        /* translators: %s: URL to FingerprintJS */
                                        esc_html__('Get your free API key from %s (20,000 requests/month included)', 'wpforms-fingerprint-protection'),
                                        '<a href="https://fingerprint.com" target="_blank">fingerprint.com</a>'
                                    );
                                    ?>
                                </p>
                                <?php if (empty($api_key)): ?>
                                    <p class="wpfp-notice wpfp-notice-warning">
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php esc_html_e('Plugin will not work without an API key. Please add your key above.', 'wpforms-fingerprint-protection'); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="wpfp-notice wpfp-notice-success">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php esc_html_e('API key configured successfully!', 'wpforms-fingerprint-protection'); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Rate Limiting Section -->
                <div class="wpfp-card">
                    <h2><?php esc_html_e('Rate Limiting Settings', 'wpforms-fingerprint-protection'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wpfp_rate_limit">
                                    <?php esc_html_e('Max Submissions', 'wpforms-fingerprint-protection'); ?>
                                </label>
                            </th>
                            <td>
                                <input 
                                    type="number" 
                                    id="wpfp_rate_limit" 
                                    name="wpfp_rate_limit" 
                                    value="<?php echo esc_attr($rate_limit); ?>" 
                                    min="1" 
                                    max="100"
                                    class="small-text"
                                >
                                <p class="description">
                                    <?php esc_html_e('Maximum number of submissions allowed per device. Default: 5', 'wpforms-fingerprint-protection'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="wpfp_time_window">
                                    <?php esc_html_e('Time Window (Hours)', 'wpforms-fingerprint-protection'); ?>
                                </label>
                            </th>
                            <td>
                                <input 
                                    type="number" 
                                    id="wpfp_time_window" 
                                    name="wpfp_time_window" 
                                    value="<?php echo esc_attr($time_window); ?>" 
                                    min="1" 
                                    max="24"
                                    class="small-text"
                                >
                                <p class="description">
                                    <?php esc_html_e('Time window for rate limiting in hours. Default: 1 hour', 'wpforms-fingerprint-protection'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Spam Detection Section -->
                <div class="wpfp-card">
                    <h2><?php esc_html_e('Spam Detection Settings', 'wpforms-fingerprint-protection'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wpfp_confidence_threshold">
                                    <?php esc_html_e('Confidence Threshold', 'wpforms-fingerprint-protection'); ?>
                                </label>
                            </th>
                            <td>
                                <input 
                                    type="number" 
                                    id="wpfp_confidence_threshold" 
                                    name="wpfp_confidence_threshold" 
                                    value="<?php echo esc_attr($confidence_threshold); ?>" 
                                    min="0" 
                                    max="1" 
                                    step="0.1"
                                    class="small-text"
                                >
                                <p class="description">
                                    <?php esc_html_e('Minimum confidence score (0.0 - 1.0). Submissions below this are marked as suspicious. Default: 0.5', 'wpforms-fingerprint-protection'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="wpfp_spam_threshold">
                                    <?php esc_html_e('Spam Threshold', 'wpforms-fingerprint-protection'); ?>
                                </label>
                            </th>
                            <td>
                                <input 
                                    type="number" 
                                    id="wpfp_spam_threshold" 
                                    name="wpfp_spam_threshold" 
                                    value="<?php echo esc_attr($spam_threshold); ?>" 
                                    min="1" 
                                    max="10"
                                    class="small-text"
                                >
                                <p class="description">
                                    <?php esc_html_e('Number of spam marks in 7 days before blocking device. Default: 2', 'wpforms-fingerprint-protection'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="wpfp_block_no_fingerprint">
                                    <?php esc_html_e('Block Missing Fingerprints', 'wpforms-fingerprint-protection'); ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input 
                                        type="checkbox" 
                                        id="wpfp_block_no_fingerprint" 
                                        name="wpfp_block_no_fingerprint" 
                                        value="yes"
                                        <?php checked($block_no_fingerprint, 'yes'); ?>
                                    >
                                    <?php esc_html_e('Block submissions without fingerprint', 'wpforms-fingerprint-protection'); ?>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('Enable this to block submissions that have no fingerprint (not recommended - may block legitimate users).', 'wpforms-fingerprint-protection'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <p class="submit">
                    <button type="submit" name="wpfp_settings_submit" class="button button-primary button-hero">
                        <?php esc_html_e('Save Settings', 'wpforms-fingerprint-protection'); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="wpfp-settings-sidebar">
            <div class="wpfp-card wpfp-card-info">
                <h3><?php esc_html_e('Quick Start Guide', 'wpforms-fingerprint-protection'); ?></h3>
                <ol>
                    <li><?php esc_html_e('Sign up at fingerprint.com', 'wpforms-fingerprint-protection'); ?></li>
                    <li><?php esc_html_e('Copy your Public API Key', 'wpforms-fingerprint-protection'); ?></li>
                    <li><?php esc_html_e('Paste it in the API Key field above', 'wpforms-fingerprint-protection'); ?></li>
                    <li><?php esc_html_e('Click "Save Settings"', 'wpforms-fingerprint-protection'); ?></li>
                    <li><?php esc_html_e('Test your forms!', 'wpforms-fingerprint-protection'); ?></li>
                </ol>
            </div>

            <div class="wpfp-card wpfp-card-stats">
                <h3><?php esc_html_e('Protection Status', 'wpforms-fingerprint-protection'); ?></h3>
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'wpforms_fingerprints';
                $stats = $wpdb->get_row("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
                        SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) as spam
                    FROM $table_name
                    WHERE submission_time > DATE_SUB(NOW(), INTERVAL 7 DAY)
                ");
                ?>
                <div class="wpfp-stat">
                    <div class="wpfp-stat-label"><?php esc_html_e('Total Submissions (7 days)', 'wpforms-fingerprint-protection'); ?></div>
                    <div class="wpfp-stat-value"><?php echo esc_html($stats->total ?? 0); ?></div>
                </div>
                <div class="wpfp-stat">
                    <div class="wpfp-stat-label"><?php esc_html_e('Blocked Submissions', 'wpforms-fingerprint-protection'); ?></div>
                    <div class="wpfp-stat-value wpfp-stat-danger"><?php echo esc_html($stats->blocked ?? 0); ?></div>
                </div>
                <div class="wpfp-stat">
                    <div class="wpfp-stat-label"><?php esc_html_e('Spam Detected', 'wpforms-fingerprint-protection'); ?></div>
                    <div class="wpfp-stat-value wpfp-stat-warning"><?php echo esc_html($stats->spam ?? 0); ?></div>
                </div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpfp-logs')); ?>" class="button button-secondary" style="width: 100%; margin-top: 10px; text-align: center;">
                    <?php esc_html_e('View Full Logs', 'wpforms-fingerprint-protection'); ?>
                </a>
            </div>

            <div class="wpfp-card wpfp-card-help">
                <h3><?php esc_html_e('Need Help?', 'wpforms-fingerprint-protection'); ?></h3>
                <ul>
                    <li><a href="https://github.com/rohitdevwp/wpforms-fingerprint-protection/wiki" target="_blank"><?php esc_html_e('Documentation', 'wpforms-fingerprint-protection'); ?></a></li>
                    <li><a href="https://github.com/rohitdevwp/wpforms-fingerprint-protection/issues" target="_blank"><?php esc_html_e('Report a Bug', 'wpforms-fingerprint-protection'); ?></a></li>
                    <li><a href="https://wordpress.org/support/plugin/wpforms-fingerprint-protection/" target="_blank"><?php esc_html_e('Support Forum', 'wpforms-fingerprint-protection'); ?></a></li>
                </ul>
            </div>

            <div class="wpfp-card wpfp-card-donate">
                <h3><?php esc_html_e('Love this plugin?', 'wpforms-fingerprint-protection'); ?></h3>
                <p><?php esc_html_e('Consider leaving a 5-star review on WordPress.org!', 'wpforms-fingerprint-protection'); ?></p>
                <a href="https://wordpress.org/support/plugin/wpforms-fingerprint-protection/reviews/#new-post" target="_blank" class="button button-primary" style="width: 100%; text-align: center;">
                    <?php esc_html_e('Leave a Review ★★★★★', 'wpforms-fingerprint-protection'); ?>
                </a>
            </div>
        </div>
    </div>
</div>