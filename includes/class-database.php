<?php
/**
 * Database Handler Class
 *
 * Handles all database operations for fingerprint logs
 *
 * @package WPForms_Fingerprint_Protection
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WPFP_Database
 */
class WPFP_Database {
    
    /**
     * Table name
     *
     * @var string
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpforms_fingerprints';
    }
    
    /**
     * Create database table
     */
    public function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
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
    
    /**
     * Log submission
     *
     * @param string $visitor_id Visitor fingerprint ID
     * @param int    $entry_id   Form entry ID
     * @param float  $confidence Confidence score
     * @param string $ip_address IP address
     * @param string $user_agent User agent string
     * @return int|false Insert ID or false on failure
     */
    public function log_submission($visitor_id, $entry_id, $confidence, $ip_address, $user_agent) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'visitor_id' => $visitor_id,
                'entry_id' => $entry_id,
                'confidence_score' => $confidence,
                'ip_address' => $ip_address,
                'user_agent' => substr($user_agent, 0, 255),
                'status' => 'allowed',
                'submission_time' => current_time('mysql')
            ),
            array('%s', '%d', '%f', '%s', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Log blocked submission
     *
     * @param string $visitor_id Visitor fingerprint ID
     * @param string $reason     Block reason
     * @param string $ip_address IP address
     * @param string $user_agent User agent string
     * @return int|false Insert ID or false on failure
     */
    public function log_blocked_submission($visitor_id, $reason, $ip_address, $user_agent) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'visitor_id' => $visitor_id,
                'ip_address' => $ip_address,
                'user_agent' => substr($user_agent, 0, 255),
                'status' => 'blocked',
                'block_reason' => $reason,
                'submission_time' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Log suspicious submission
     *
     * @param string $visitor_id Visitor fingerprint ID
     * @param string $reason     Suspicious reason
     * @param float  $confidence Confidence score
     * @param string $ip_address IP address
     * @return int|false Insert ID or false on failure
     */
    public function log_suspicious_submission($visitor_id, $reason, $confidence, $ip_address) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'visitor_id' => $visitor_id,
                'confidence_score' => $confidence,
                'ip_address' => $ip_address,
                'status' => 'suspicious',
                'block_reason' => $reason,
                'submission_time' => current_time('mysql')
            ),
            array('%s', '%f', '%s', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Get recent submissions count for a visitor
     *
     * @param string $visitor_id  Visitor fingerprint ID
     * @param int    $time_window Time window in hours
     * @return int Submission count
     */
    public function get_recent_submissions($visitor_id, $time_window = 1) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
            WHERE visitor_id = %s 
            AND submission_time > DATE_SUB(NOW(), INTERVAL %d HOUR)
            AND status = 'allowed'",
            $visitor_id,
            $time_window
        ));
        
        return intval($count);
    }
    
    /**
     * Check if visitor is a known spammer
     *
     * @param string $visitor_id      Visitor fingerprint ID
     * @param int    $spam_threshold  Number of spam marks to consider as spammer
     * @return bool True if known spammer
     */
    public function is_known_spammer($visitor_id, $spam_threshold = 2) {
        global $wpdb;
        
        $spam_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
            WHERE visitor_id = %s 
            AND status = 'spam'
            AND submission_time > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            $visitor_id
        ));
        
        return intval($spam_count) >= $spam_threshold;
    }
    
    /**
     * Mark visitor as spam
     *
     * @param string $visitor_id Visitor fingerprint ID
     * @return int|false Number of rows updated or false on failure
     */
    public function mark_as_spam($visitor_id) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            array('status' => 'spam'),
            array('visitor_id' => $visitor_id),
            array('%s'),
            array('%s')
        );
    }
    
    /**
     * Unmark visitor as spam
     *
     * @param string $visitor_id Visitor fingerprint ID
     * @return int|false Number of rows updated or false on failure
     */
    public function unmark_as_spam($visitor_id) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            array('status' => 'allowed'),
            array('visitor_id' => $visitor_id),
            array('%s'),
            array('%s')
        );
    }
    
    /**
     * Get logs with optional filtering
     *
     * @param string $status Status filter (all, allowed, blocked, spam, suspicious)
     * @param int    $limit  Number of records to retrieve
     * @return array Array of log records
     */
    public function get_logs($status = 'all', $limit = 100) {
        global $wpdb;
        
        $where = '';
        if ($status !== 'all') {
            $where = $wpdb->prepare("WHERE status = %s", $status);
        }
        
        $query = "SELECT * FROM {$this->table_name} 
                  {$where}
                  ORDER BY submission_time DESC 
                  LIMIT %d";
        
        return $wpdb->get_results($wpdb->prepare($query, $limit));
    }
    
    /**
     * Get statistics
     *
     * @return object Statistics object
     */
    public function get_statistics() {
        global $wpdb;
        
        return $wpdb->get_row("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'allowed' THEN 1 ELSE 0 END) as allowed,
                SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
                SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) as spam,
                SUM(CASE WHEN status = 'suspicious' THEN 1 ELSE 0 END) as suspicious
            FROM {$this->table_name}
        ");
    }
    
    /**
     * Get statistics for a time period
     *
     * @param int $days Number of days to look back
     * @return object Statistics object
     */
    public function get_statistics_for_period($days = 7) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'allowed' THEN 1 ELSE 0 END) as allowed,
                SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
                SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) as spam,
                SUM(CASE WHEN status = 'suspicious' THEN 1 ELSE 0 END) as suspicious
            FROM {$this->table_name}
            WHERE submission_time > DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $days));
    }
    
    /**
     * Delete old logs
     *
     * @param int $days Delete logs older than this many days
     * @return int|false Number of rows deleted or false on failure
     */
    public function delete_old_logs($days = 90) {
        global $wpdb;
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} 
            WHERE submission_time < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }
    
    /**
     * Get table name
     *
     * @return string Table name
     */
    public function get_table_name() {
        return $this->table_name;
    }
}