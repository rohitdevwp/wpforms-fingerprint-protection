<?php
/**
 * Plugin Name: WPForms Fingerprint Protection
 * Plugin URI: https://github.com/rohitdevwp/wpforms-fingerprint-protection
 * Description: Prevent spam and fake form submissions using FingerprintJS device fingerprinting technology.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Rohit Dev
 * Author URI: https://kovisys.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpforms-fingerprint-protection
 *
 * @package WPForms_Fingerprint_Protection
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPFP_VERSION', '1.0.0');
define('WPFP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPFP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPFP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class WPForms_Fingerprint_Protection {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        if (!$this->is_wpforms_active()) {
            add_action('admin_notices', array($this, 'wpforms_missing_notice'));
            return;
        }
        
        $this->init();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('wpforms-fingerprint-protection', false, dirname(WPFP_PLUGIN_BASENAME) . '/languages');
    }
    
    private function is_wpforms_active() {
        return class_exists('WPForms');
    }
    
    public function wpforms_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong>WPForms Fingerprint Protection</strong> requires WPForms plugin to be installed and activated. (If Not Installed)
                <a href="<?php echo esc_url(admin_url('plugin-install.php?s=wpforms&tab=search&type=term')); ?>">Install WPForms Now</a>
            </p>
        </div>
        <?php
    }
    
    private function init() {
        add_action('wpforms_wp_footer', array($this, 'add_fingerprint_script'));
        add_action('wpforms_process_before', array($this, 'validate_fingerprint'), 10, 2);
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
        add_filter('plugin_action_links_' . WPFP_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    public function activate() {
        $this->create_database_table();
        
        $defaults = array(
            'wpfp_api_key' => '',
            'wpfp_rate_limit' => '5',
            'wpfp_time_window' => '1',
            'wpfp_confidence_threshold' => '0.5',
            'wpfp_spam_threshold' => '2',
            'wpfp_block_no_fingerprint' => 'no'
        );
        
        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    public function deactivate() {
        // Cleanup if needed
    }
    
    private function create_database_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_fingerprints';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            visitor_id varchar(255) NOT NULL,
            entry_id bigint(20) DEFAULT NULL,
            confidence_score decimal(3,2) DEFAULT NULL,
            ip_address varchar(100) DEFAULT NULL,
            user_agent varchar(255) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'allowed',
            block_reason varchar(50) DEFAULT NULL,
            submission_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY visitor_id (visitor_id),
            KEY status (status),
            KEY submission_time (submission_time)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function register_settings() {
        register_setting('wpfp_settings', 'wpfp_api_key', 'sanitize_text_field');
        register_setting('wpfp_settings', 'wpfp_rate_limit', 'absint');
        register_setting('wpfp_settings', 'wpfp_time_window', 'absint');
        register_setting('wpfp_settings', 'wpfp_confidence_threshold', array($this, 'sanitize_decimal'));
        register_setting('wpfp_settings', 'wpfp_spam_threshold', 'absint');
        register_setting('wpfp_settings', 'wpfp_block_no_fingerprint', 'sanitize_text_field');
    }
    
    public function sanitize_decimal($value) {
        $value = floatval($value);
        return max(0, min(1, $value));
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wpfp-settings') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wpfp') === false) {
            return;
        }
        
        if (file_exists(WPFP_PLUGIN_DIR . 'assets/css/admin-style.css')) {
            wp_enqueue_style('wpfp-admin-style', WPFP_PLUGIN_URL . 'assets/css/admin-style.css', array(), WPFP_VERSION);
        }
        
        if (file_exists(WPFP_PLUGIN_DIR . 'assets/js/admin-script.js')) {
            wp_enqueue_script('wpfp-admin-script', WPFP_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), WPFP_VERSION, true);
        }
    }
    
   public function add_admin_menu() {
    // Create standalone menu (not under WPForms)
    add_menu_page(
        'Fingerprint Protection',           // Page title
        'Fingerprint Protection',           // Menu title
        'manage_options',                   // Capability
        'wpfp-settings',                    // Menu slug
        array($this, 'render_settings_page'), // Callback
        'dashicons-shield',                 // Icon
        30                                  // Position
    );
    
    add_submenu_page(
        'wpfp-settings',                    // Parent slug
        'Settings',                         // Page title
        'Settings',                         // Menu title
        'manage_options',                   // Capability
        'wpfp-settings',                    // Menu slug (same as parent)
        array($this, 'render_settings_page') // Callback
    );
    
    add_submenu_page(
        'wpfp-settings',                    // Parent slug
        'Logs',                             // Page title  
        'Logs',                             // Menu title
        'manage_options',                   // Capability
        'wpfp-logs',                        // Menu slug
        array($this, 'render_logs_page')    // Callback
    );
}
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_POST['wpfp_settings_submit'])) {
            check_admin_referer('wpfp_settings_action', 'wpfp_settings_nonce');
            
            update_option('wpfp_api_key', sanitize_text_field($_POST['wpfp_api_key']));
            update_option('wpfp_rate_limit', absint($_POST['wpfp_rate_limit']));
            update_option('wpfp_time_window', absint($_POST['wpfp_time_window']));
            update_option('wpfp_confidence_threshold', $this->sanitize_decimal($_POST['wpfp_confidence_threshold']));
            update_option('wpfp_spam_threshold', absint($_POST['wpfp_spam_threshold']));
            update_option('wpfp_block_no_fingerprint', isset($_POST['wpfp_block_no_fingerprint']) ? 'yes' : 'no');
            
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
        }
        
        $api_key = get_option('wpfp_api_key', '');
        $rate_limit = get_option('wpfp_rate_limit', 5);
        $time_window = get_option('wpfp_time_window', 1);
        $confidence_threshold = get_option('wpfp_confidence_threshold', 0.5);
        $spam_threshold = get_option('wpfp_spam_threshold', 2);
        $block_no_fingerprint = get_option('wpfp_block_no_fingerprint', 'no');
        
        if (file_exists(WPFP_PLUGIN_DIR . 'admin/settings-page.php')) {
            include WPFP_PLUGIN_DIR . 'admin/settings-page.php';
        } else {
            $this->render_simple_settings_page($api_key, $rate_limit, $time_window, $confidence_threshold, $spam_threshold, $block_no_fingerprint);
        }
    }
    
    private function render_simple_settings_page($api_key, $rate_limit, $time_window, $confidence_threshold, $spam_threshold, $block_no_fingerprint) {
        ?>
        <div class="wrap">
            <h1>WPForms Fingerprint Protection Settings</h1>
            <form method="post">
                <?php wp_nonce_field('wpfp_settings_action', 'wpfp_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="wpfp_api_key">FingerprintJS API Key *</label></th>
                        <td>
                            <input type="text" id="wpfp_api_key" name="wpfp_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" required>
                            <p class="description">Get your free API key from <a href="https://fingerprint.com" target="_blank">fingerprint.com</a></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wpfp_rate_limit">Max Submissions</label></th>
                        <td>
                            <input type="number" id="wpfp_rate_limit" name="wpfp_rate_limit" value="<?php echo esc_attr($rate_limit); ?>" min="1" max="100">
                            <p class="description">Maximum submissions per device (default: 5)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wpfp_time_window">Time Window (Hours)</label></th>
                        <td>
                            <input type="number" id="wpfp_time_window" name="wpfp_time_window" value="<?php echo esc_attr($time_window); ?>" min="1" max="24">
                            <p class="description">Time window for rate limiting (default: 1 hour)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wpfp_confidence_threshold">Confidence Threshold</label></th>
                        <td>
                            <input type="number" id="wpfp_confidence_threshold" name="wpfp_confidence_threshold" value="<?php echo esc_attr($confidence_threshold); ?>" min="0" max="1" step="0.1">
                            <p class="description">Minimum confidence score 0.0-1.0 (default: 0.5)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wpfp_spam_threshold">Spam Threshold</label></th>
                        <td>
                            <input type="number" id="wpfp_spam_threshold" name="wpfp_spam_threshold" value="<?php echo esc_attr($spam_threshold); ?>" min="1" max="10">
                            <p class="description">Spam marks before blocking (default: 2)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wpfp_block_no_fingerprint">Block Missing Fingerprints</label></th>
                        <td>
                            <input type="checkbox" id="wpfp_block_no_fingerprint" name="wpfp_block_no_fingerprint" value="yes" <?php checked($block_no_fingerprint, 'yes'); ?>>
                            <label for="wpfp_block_no_fingerprint">Block submissions without fingerprint</label>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" name="wpfp_settings_submit" class="button button-primary">Save Settings</button>
                </p>
            </form>
        </div>
        <?php
    }
    
    public function render_logs_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_fingerprints';
        
        $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
        
        $where = '';
        if ($filter !== 'all') {
            $where = $wpdb->prepare("WHERE status = %s", $filter);
        }
        
        $results = $wpdb->get_results("SELECT * FROM $table_name $where ORDER BY submission_time DESC LIMIT 100");
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'allowed' THEN 1 ELSE 0 END) as allowed,
                SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
                SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) as spam,
                SUM(CASE WHEN status = 'suspicious' THEN 1 ELSE 0 END) as suspicious
            FROM $table_name
        ");
        
        if (file_exists(WPFP_PLUGIN_DIR . 'admin/logs-page.php')) {
            include WPFP_PLUGIN_DIR . 'admin/logs-page.php';
        } else {
            $this->render_simple_logs_page($results, $stats, $filter);
        }
    }
    
    private function render_simple_logs_page($results, $stats, $filter) {
        ?>
        <div class="wrap">
            <h1>Fingerprint Submission Logs</h1>
            <?php if (isset($_GET['marked'])): ?>
                <div class="notice notice-success"><p>Status updated successfully!</p></div>
            <?php endif; ?>
            <p>
                <strong>Total:</strong> <?php echo esc_html($stats->total ?? 0); ?> |
                <strong>Allowed:</strong> <?php echo esc_html($stats->allowed ?? 0); ?> |
                <strong>Blocked:</strong> <?php echo esc_html($stats->blocked ?? 0); ?> |
                <strong>Spam:</strong> <?php echo esc_html($stats->spam ?? 0); ?> |
                <strong>Suspicious:</strong> <?php echo esc_html($stats->suspicious ?? 0); ?>
            </p>
            <ul class="subsubsub">
                <li><a href="?page=wpfp-logs&filter=all" <?php echo $filter === 'all' ? 'class="current"' : ''; ?>>All</a> | </li>
                <li><a href="?page=wpfp-logs&filter=allowed" <?php echo $filter === 'allowed' ? 'class="current"' : ''; ?>>Allowed</a> | </li>
                <li><a href="?page=wpfp-logs&filter=blocked" <?php echo $filter === 'blocked' ? 'class="current"' : ''; ?>>Blocked</a> | </li>
                <li><a href="?page=wpfp-logs&filter=spam" <?php echo $filter === 'spam' ? 'class="current"' : ''; ?>>Spam</a> | </li>
                <li><a href="?page=wpfp-logs&filter=suspicious" <?php echo $filter === 'suspicious' ? 'class="current"' : ''; ?>>Suspicious</a></li>
            </ul>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Visitor ID</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="6" style="text-align: center;">No submissions found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->id); ?></td>
                            <td><?php echo esc_html(substr($row->visitor_id, 0, 20)); ?>...</td>
                            <td><?php echo esc_html(ucfirst($row->status)); ?></td>
                            <td><?php echo esc_html($row->ip_address); ?></td>
                            <td><?php echo esc_html($row->submission_time); ?></td>
                            <td>
                                <?php if ($row->status === 'allowed' || $row->status === 'suspicious'): ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpfp-logs&action=mark_spam&id=' . urlencode($row->visitor_id)), 'wpfp_mark_spam_' . $row->visitor_id)); ?>">Mark as Spam</a>
                                <?php elseif ($row->status === 'spam'): ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpfp-logs&action=unmark_spam&id=' . urlencode($row->visitor_id)), 'wpfp_unmark_spam_' . $row->visitor_id)); ?>">Unmark Spam</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    public function handle_admin_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wpfp-logs' || !current_user_can('manage_options')) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_fingerprints';
        
        if (isset($_GET['action']) && $_GET['action'] === 'mark_spam' && isset($_GET['id'])) {
            check_admin_referer('wpfp_mark_spam_' . $_GET['id']);
            $wpdb->update($table_name, array('status' => 'spam'), array('visitor_id' => sanitize_text_field($_GET['id'])), array('%s'), array('%s'));
            wp_redirect(admin_url('admin.php?page=wpfp-logs&marked=spam'));
            exit;
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'unmark_spam' && isset($_GET['id'])) {
            check_admin_referer('wpfp_unmark_spam_' . $_GET['id']);
            $wpdb->update($table_name, array('status' => 'allowed'), array('visitor_id' => sanitize_text_field($_GET['id'])), array('%s'), array('%s'));
            wp_redirect(admin_url('admin.php?page=wpfp-logs&marked=allowed'));
            exit;
        }
    }
    
    public function add_fingerprint_script() {
        $api_key = get_option('wpfp_api_key', '');
        if (empty($api_key)) {
            return;
        }
        ?>
        <script>
            (function() {
                'use strict';
                const fpPromise = import('https://fpjscdn.net/v3/<?php echo esc_js($api_key); ?>')
                    .then(FingerprintJS => FingerprintJS.load())
                    .catch(error => { console.error('FingerprintJS load error:', error); return null; });
                
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initFingerprint);
                } else {
                    initFingerprint();
                }
                
                function initFingerprint() {
                    const wpForms = document.querySelectorAll('.wpforms-form');
                    wpForms.forEach(function(form) {
                        if (form.dataset.fingerprintInit) return;
                        form.dataset.fingerprintInit = 'true';
                        
                        form.addEventListener('submit', async function(e) {
                            const existingFingerprint = form.querySelector('input[name="visitor_fingerprint"]');
                            if (existingFingerprint) return;
                            
                            e.preventDefault();
                            
                            try {
                                const fp = await fpPromise;
                                if (fp) {
                                    const result = await fp.get();
                                    const fpInput = document.createElement('input');
                                    fpInput.type = 'hidden';
                                    fpInput.name = 'visitor_fingerprint';
                                    fpInput.value = result.visitorId;
                                    form.appendChild(fpInput);
                                    
                                    const confidenceInput = document.createElement('input');
                                    confidenceInput.type = 'hidden';
                                    confidenceInput.name = 'visitor_confidence';
                                    confidenceInput.value = result.confidence.score;
                                    form.appendChild(confidenceInput);
                                } else {
                                    const fpInput = document.createElement('input');
                                    fpInput.type = 'hidden';
                                    fpInput.name = 'visitor_fingerprint';
                                    fpInput.value = 'fingerprint_unavailable';
                                    form.appendChild(fpInput);
                                }
                            } catch (error) {
                                console.error('Fingerprint error:', error);
                                const fpInput = document.createElement('input');
                                fpInput.type = 'hidden';
                                fpInput.name = 'visitor_fingerprint';
                                fpInput.value = 'fingerprint_error';
                                form.appendChild(fpInput);
                            }
                            
                            const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                            form.dispatchEvent(submitEvent);
                        }, false);
                    });
                }
            })();
        </script>
        <?php
    }
    
    public function validate_fingerprint($fields, $entry) {
        $visitor_id = isset($_POST['visitor_fingerprint']) ? sanitize_text_field($_POST['visitor_fingerprint']) : '';
        $confidence = isset($_POST['visitor_confidence']) ? floatval($_POST['visitor_confidence']) : 0;
        
        if (empty($visitor_id)) {
            if (get_option('wpfp_block_no_fingerprint', 'no') === 'yes') {
                wpforms()->process->errors[$entry['id']]['header'] = 'Security validation failed. Please try again.';
            }
            $this->log_submission($visitor_id, null, $confidence, 'suspicious', 'missing_fingerprint');
            return;
        }
        
        if ($visitor_id === 'fingerprint_unavailable' || $visitor_id === 'fingerprint_error') {
            $this->log_submission($visitor_id, null, $confidence, 'suspicious', 'fingerprint_load_failed');
            return;
        }
        
        $confidence_threshold = floatval(get_option('wpfp_confidence_threshold', 0.5));
        if ($confidence > 0 && $confidence < $confidence_threshold) {
            $this->log_submission($visitor_id, null, $confidence, 'suspicious', 'low_confidence');
        }
        
        $rate_limit = intval(get_option('wpfp_rate_limit', 5));
        $time_window = intval(get_option('wpfp_time_window', 1));
        $submission_count = $this->get_recent_submissions($visitor_id, $time_window);
        
        if ($submission_count >= $rate_limit) {
            wpforms()->process->errors[$entry['id']]['header'] = 'Too many submissions detected. Please try again later.';
            $this->log_submission($visitor_id, null, $confidence, 'blocked', 'rate_limit');
            return;
        }
        
        $spam_threshold = intval(get_option('wpfp_spam_threshold', 2));
        if ($this->is_known_spammer($visitor_id, $spam_threshold)) {
            wpforms()->process->errors[$entry['id']]['header'] = 'Your submission could not be processed.';
            $this->log_submission($visitor_id, null, $confidence, 'blocked', 'known_spammer');
            return;
        }
        
        $this->log_submission($visitor_id, $entry['id'], $confidence, 'allowed', null);
    }
    
    private function get_recent_submissions($visitor_id, $time_window) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_fingerprints';
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE visitor_id = %s AND submission_time > DATE_SUB(NOW(), INTERVAL %d HOUR) AND status = 'allowed'",
            $visitor_id, $time_window
        ));
        return intval($count);
    }
    
    private function is_known_spammer($visitor_id, $spam_threshold) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_fingerprints';
        $spam_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE visitor_id = %s AND status = 'spam' AND submission_time > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            $visitor_id
        ));
        return intval($spam_count) >= $spam_threshold;
    }
    
    private function log_submission($visitor_id, $entry_id, $confidence, $status, $reason) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_fingerprints';
        $wpdb->insert($table_name, array(
            'visitor_id' => $visitor_id,
            'entry_id' => $entry_id,
            'confidence_score' => $confidence,
            'ip_address' => $this->get_user_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '',
            'status' => $status,
            'block_reason' => $reason,
            'submission_time' => current_time('mysql')
        ));
    }
    
    private function get_user_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return 'Unknown';
    }
}

// Initialize plugin
function wpfp_init() {
    return WPForms_Fingerprint_Protection::get_instance();
}
wpfp_init();
