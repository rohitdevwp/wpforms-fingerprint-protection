<?php
/**
 * Plugin Name: WPForms Fingerprint Protection
 * Plugin URI: https://github.com/rohitdevwp/wpforms-fingerprint-protection
 * Description: Prevent spam and fake form submissions using FingerprintJS device fingerprinting technology. Includes rate limiting, spam detection, and admin dashboard.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Rohit Dev
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpforms-fingerprint-protection
 * Domain Path: /languages
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
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Database instance
     *
     * @var WPFP_Database
     */
    private $database;
    
    /**
     * Validator instance
     *
     * @var WPFP_Validator
     */
    private $validator;
    
    /**
     * Logger instance
     *
     * @var WPFP_Logger
     */
    private $logger;
    
    /**
     * Get single instance
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
        // Load dependencies
        $this->load_dependencies();
        
        // Load plugin textdomain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Check if WPForms is active
        if (!$this->is_wpforms_active()) {
            add_action('admin_notices', array($this, 'wpforms_missing_notice'));
            return;
        }
        
        // Initialize plugin
        $this->init();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once WPFP_PLUGIN_DIR . 'includes/class-database.php';
        require_once WPFP_PLUGIN_DIR . 'includes/class-logger.php';
        require_once WPFP_PLUGIN_DIR . 'includes/class-validator.php';
        
        // Initialize classes
        $this->database = new WPFP_Database();
        $this->logger = new WPFP_Logger($this->database);
        $this->validator = new WPFP_Validator($this->database, $this->logger);
    }
    
    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wpforms-fingerprint-protection',
            false,
            dirname(WPFP_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Check if WPForms is active
     */
    private function is_wpforms_active() {
        return class_exists('WPForms');
    }
    
    /**
     * Display notice if WPForms is not active
     */
    public function wpforms_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php 
                echo sprintf(
                    /* translators: %s: WPForms plugin name */
                    esc_html__('%s requires WPForms plugin to be installed and activated.', 'wpforms-fingerprint-protection'),
                    '<strong>' . esc_html__('WPForms Fingerprint Protection', 'wpforms-fingerprint-protection') . '</strong>'
                );
                ?>
                <a href="<?php echo esc_url(admin_url('plugin-install.php?s=wpforms&tab=search&type=term')); ?>">
                    <?php esc_html_e('Install WPForms Now', 'wpforms-fingerprint-protection'); ?>
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Add fingerprint script to forms
        add_action('wpforms_wp_footer', array($this, 'add_fingerprint_script'));
        
        // Validate submissions
        add_action('wpforms_process_before', array($this, 'validate_fingerprint'), 10, 2);
        
        // Admin menu and settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
        
        // Add settings link on plugins page
        add_filter('plugin_action_links_' . WPFP_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        
        // Enqueue admin styles and scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->database->create_table();
        
        // Set default options
        $default_options = array(
            'wpfp_api_key' => '',
            'wpfp_rate_limit' => '5',
            'wpfp_time_window' => '1',
            'wpfp_confidence_threshold' => '0.5',
            'wpfp_spam_threshold' => '2',
            'wpfp_block_no_fingerprint' => 'no'
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed (don't delete data by default)
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        $settings = array(
            'wpfp_api_key' => array('type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => ''),
            'wpfp_rate_limit' => array('type' => 'integer', 'sanitize' => 'absint', 'default' => 5),
            'wpfp_time_window' => array('type' => 'integer', 'sanitize' => 'absint', 'default' => 1),
            'wpfp_confidence_threshold' => array('type' => 'string', 'sanitize' => array($this, 'sanitize_decimal'), 'default' => '0.5'),
            'wpfp_spam_threshold' => array('type' => 'integer', 'sanitize' => 'absint', 'default' => 2),
            'wpfp_block_no_fingerprint' => array('type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => 'no')
        );
        
        foreach ($settings as $setting => $args) {
            register_setting('wpfp_settings', $setting, array(
                'type' => $args['type'],
                'sanitize_callback' => $args['sanitize'],
                'default' => $args['default']
            ));
        }
    }
    
    /**
     * Sanitize decimal values
     */
    public function sanitize_decimal($value) {
        $value = floatval($value);
        return max(0, min(1, $value));
    }
    
    /**
     * Add settings link on plugins page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wpfp-settings') . '">' . 
                        esc_html__('Settings', 'wpforms-fingerprint-protection') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wpfp') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wpfp-admin-style',
            WPFP_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            WPFP_VERSION
        );
        
        wp_enqueue_script(
            'wpfp-admin-script',
            WPFP_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery'),
            WPFP_VERSION,
            true
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Settings page
        add_submenu_page(
            'wpforms-overview',
            __('Fingerprint Settings', 'wpforms-fingerprint-protection'),
            __('Fingerprint Protection', 'wpforms-fingerprint-protection'),
            'manage_options',
            'wpfp-settings',
            array($this, 'render_settings_page')
        );
        
        // Logs page
        add_submenu_page(
            'wpforms-overview',
            __('Fingerprint Logs', 'wpforms-fingerprint-protection'),
            __('Fingerprint Logs', 'wpforms-fingerprint-protection'),
            'manage_options',
            'wpfp-logs',
            array($this, 'render_logs_page')
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings
        if (isset($_POST['wpfp_settings_submit'])) {
            check_admin_referer('wpfp_settings_action', 'wpfp_settings_nonce');
            
            update_option('wpfp_api_key', sanitize_text_field($_POST['wpfp_api_key']));
            update_option('wpfp_rate_limit', absint($_POST['wpfp_rate_limit']));
            update_option('wpfp_time_window', absint($_POST['wpfp_time_window']));
            update_option('wpfp_confidence_threshold', $this->sanitize_decimal($_POST['wpfp_confidence_threshold']));
            update_option('wpfp_spam_threshold', absint($_POST['wpfp_spam_threshold']));
            update_option('wpfp_block_no_fingerprint', sanitize_text_field($_POST['wpfp_block_no_fingerprint']));
            
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 esc_html__('Settings saved successfully!', 'wpforms-fingerprint-protection') . '</p></div>';
        }
        
        $api_key = get_option('wpfp_api_key', '');
        $rate_limit = get_option('wpfp_rate_limit', 5);
        $time_window = get_option('wpfp_time_window', 1);
        $confidence_threshold = get_option('wpfp_confidence_threshold', 0.5);
        $spam_threshold = get_option('wpfp_spam_threshold', 2);
        $block_no_fingerprint = get_option('wpfp_block_no_fingerprint', 'no');
        
        include WPFP_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    /**
     * Render logs page
     */
    public function render_logs_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
        $results = $this->database->get_logs($filter, 100);
        $stats = $this->database->get_statistics();
        
        include WPFP_PLUGIN_DIR . 'admin/logs-page.php';
    }
    
    /**
     * Handle admin actions (mark spam, etc.)
     */
    public function handle_admin_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wpfp-logs') {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Mark as spam
        if (isset($_GET['action']) && $_GET['action'] === 'mark_spam' && isset($_GET['id'])) {
            check_admin_referer('wpfp_mark_spam_' . $_GET['id']);
            
            $visitor_id = sanitize_text_field($_GET['id']);
            $this->database->mark_as_spam($visitor_id);
            
            wp_redirect(admin_url('admin.php?page=wpfp-logs&marked=spam'));
            exit;
        }
        
        // Unmark spam
        if (isset($_GET['action']) && $_GET['action'] === 'unmark_spam' && isset($_GET['id'])) {
            check_admin_referer('wpfp_unmark_spam_' . $_GET['id']);
            
            $visitor_id = sanitize_text_field($_GET['id']);
            $this->database->unmark_as_spam($visitor_id);
            
            wp_redirect(admin_url('admin.php?page=wpfp-logs&marked=allowed'));
            exit;
        }
    }
    
    /**
     * Add fingerprint script to footer
     */
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
                    .catch(error => {
                        console.error('FingerprintJS load error:', error);
                        return null;
                    });
                
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initFingerprint);
                } else {
                    initFingerprint();
                }
                
                function initFingerprint() {
                    const wpForms = document.querySelectorAll('.wpforms-form');
                    
                    wpForms.forEach(function(form) {
                        if (form.dataset.fingerprintInit) {
                            return;
                        }
                        form.dataset.fingerprintInit = 'true';
                        
                        form.addEventListener('submit', async function(e) {
                            const existingFingerprint = form.querySelector('input[name="visitor_fingerprint"]');
                            if (existingFingerprint) {
                                return;
                            }
                            
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
                            
                            const submitEvent = new Event('submit', {
                                bubbles: true,
                                cancelable: true
                            });
                            
                            form.dispatchEvent(submitEvent);
                        }, false);
                    });
                }
            })();
        </script>
        <?php
    }
    
    /**
     * Validate fingerprint on form submission
     */
    public function validate_fingerprint($fields, $entry) {
        $this->validator->validate_fingerprint($fields, $entry);
    }
}

// Initialize plugin
function wpfp_init() {
    return WPForms_Fingerprint_Protection::get_instance();
}

// Start the plugin
wpfp_init();