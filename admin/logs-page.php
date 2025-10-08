<?php
/**
 * Logs Page Template
 *
 * @package WPForms_Fingerprint_Protection
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wpfp-logs-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (isset($_GET['marked'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Status updated successfully!', 'wpforms-fingerprint-protection'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="wpfp-stats-grid">
        <div class="wpfp-stat-card wpfp-stat-total">
            <div class="wpfp-stat-icon">📊</div>
            <div class="wpfp-stat-content">
                <div class="wpfp-stat-label"><?php esc_html_e('Total Submissions', 'wpforms-fingerprint-protection'); ?></div>
                <div class="wpfp-stat-value"><?php echo esc_html($stats->total ?? 0); ?></div>
            </div>
        </div>
        
        <div class="wpfp-stat-card wpfp-stat-allowed">
            <div class="wpfp-stat-icon">✅</div>
            <div class="wpfp-stat-content">
                <div class="wpfp-stat-label"><?php esc_html_e('Allowed', 'wpforms-fingerprint-protection'); ?></div>
                <div class="wpfp-stat-value"><?php echo esc_html($stats->allowed ?? 0); ?></div>
            </div>
        </div>
        
        <div class="wpfp-stat-card wpfp-stat-blocked">
            <div class="wpfp-stat-icon">🚫</div>
            <div class="wpfp-stat-content">
                <div class="wpfp-stat-label"><?php esc_html_e('Blocked', 'wpforms-fingerprint-protection'); ?></div>
                <div class="wpfp-stat-value"><?php echo esc_html($stats->blocked ?? 0); ?></div>
            </div>
        </div>
        
        <div class="wpfp-stat-card wpfp-stat-spam">
            <div class="wpfp-stat-icon">⚠️</div>
            <div class="wpfp-stat-content">
                <div class="wpfp-stat-label"><?php esc_html_e('Spam', 'wpforms-fingerprint-protection'); ?></div>
                <div class="wpfp-stat-value"><?php echo esc_html($stats->spam ?? 0); ?></div>
            </div>
        </div>
        
        <div class="wpfp-stat-card wpfp-stat-suspicious">
            <div class="wpfp-stat-icon">🔍</div>
            <div class="wpfp-stat-content">
                <div class="wpfp-stat-label"><?php esc_html_e('Suspicious', 'wpforms-fingerprint-protection'); ?></div>
                <div class="wpfp-stat-value"><?php echo esc_html($stats->suspicious ?? 0); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <ul class="subsubsub">
        <li>
            <a href="?page=wpfp-logs&filter=all" <?php echo $filter === 'all' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('All', 'wpforms-fingerprint-protection'); ?>
            </a> |
        </li>
        <li>
            <a href="?page=wpfp-logs&filter=allowed" <?php echo $filter === 'allowed' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Allowed', 'wpforms-fingerprint-protection'); ?>
            </a> |
        </li>
        <li>
            <a href="?page=wpfp-logs&filter=blocked" <?php echo $filter === 'blocked' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Blocked', 'wpforms-fingerprint-protection'); ?>
            </a> |
        </li>
        <li>
            <a href="?page=wpfp-logs&filter=spam" <?php echo $filter === 'spam' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Spam', 'wpforms-fingerprint-protection'); ?>
            </a> |
        </li>
        <li>
            <a href="?page=wpfp-logs&filter=suspicious" <?php echo $filter === 'suspicious' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Suspicious', 'wpforms-fingerprint-protection'); ?>
            </a>
        </li>
    </ul>
    
    <!-- Logs Table -->
    <table class="wp-list-table widefat fixed striped wpfp-logs-table">
        <thead>
            <tr>
                <th style="width: 50px;"><?php esc_html_e('ID', 'wpforms-fingerprint-protection'); ?></th>
                <th><?php esc_html_e('Visitor ID', 'wpforms-fingerprint-protection'); ?></th>
                <th style="width: 100px;"><?php esc_html_e('Status', 'wpforms-fingerprint-protection'); ?></th>
                <th style="width: 80px;"><?php esc_html_e('Confidence', 'wpforms-fingerprint-protection'); ?></th>
                <th style="width: 120px;"><?php esc_html_e('IP Address', 'wpforms-fingerprint-protection'); ?></th>
                <th style="width: 150px;"><?php esc_html_e('Time', 'wpforms-fingerprint-protection'); ?></th>
                <th style="width: 120px;"><?php esc_html_e('Reason', 'wpforms-fingerprint-protection'); ?></th>
                <th style="width: 150px;"><?php esc_html_e('Action', 'wpforms-fingerprint-protection'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($results)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <div class="wpfp-no-results">
                            <span class="dashicons dashicons-info" style="font-size: 48px; opacity: 0.3;"></span>
                            <p><?php esc_html_e('No submissions found.', 'wpforms-fingerprint-protection'); ?></p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo esc_html($row->id); ?></td>
                    <td>
                        <code class="wpfp-fingerprint-id">
                            <?php echo esc_html(substr($row->visitor_id, 0, 20)); ?>...
                        </code>
                    </td>
                    <td>
                        <?php 
                        $status_class = 'wpfp-status-' . $row->status;
                        $status_label = ucfirst($row->status);
                        ?>
                        <span class="wpfp-status-badge <?php echo esc_attr($status_class); ?>">
                            <?php echo esc_html($status_label); ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        if ($row->confidence_score !== null) {
                            $confidence_percent = round($row->confidence_score * 100);
                            $confidence_class = $confidence_percent >= 70 ? 'good' : ($confidence_percent >= 50 ? 'medium' : 'low');
                            echo '<span class="wpfp-confidence wpfp-confidence-' . esc_attr($confidence_class) . '">' . 
                                 esc_html($confidence_percent) . '%</span>';
                        } else {
                            echo '<span class="wpfp-na">' . esc_html__('N/A', 'wpforms-fingerprint-protection') . '</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo esc_html($row->ip_address); ?></td>
                    <td>
                        <?php 
                        echo esc_html(
                            sprintf(
                                /* translators: %s: Human-readable time difference */
                                __('%s ago', 'wpforms-fingerprint-protection'),
                                human_time_diff(strtotime($row->submission_time), current_time('timestamp'))
                            )
                        );
                        ?>
                        <br>
                        <small><?php echo esc_html($row->submission_time); ?></small>
                    </td>
                    <td>
                        <?php 
                        if ($row->block_reason) {
                            echo '<span class="wpfp-reason">' . esc_html(str_replace('_', ' ', $row->block_reason)) . '</span>';
                        } else {
                            echo '<span class="wpfp-na">—</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($row->status === 'allowed' || $row->status === 'suspicious'): ?>
                            <a href="<?php echo esc_url(wp_nonce_url(
                                admin_url('admin.php?page=wpfp-logs&action=mark_spam&id=' . urlencode($row->visitor_id)),
                                'wpfp_mark_spam_' . $row->visitor_id
                            )); ?>" 
                               class="button button-small wpfp-btn-spam"
                               onclick="return confirm('<?php esc_attr_e('Mark this fingerprint as spam? Future submissions from this device will be blocked.', 'wpforms-fingerprint-protection'); ?>');">
                                <span class="dashicons dashicons-warning"></span>
                                <?php esc_html_e('Mark as Spam', 'wpforms-fingerprint-protection'); ?>
                            </a>
                        <?php elseif ($row->status === 'spam'): ?>
                            <a href="<?php echo esc_url(wp_nonce_url(
                                admin_url('admin.php?page=wpfp-logs&action=unmark_spam&id=' . urlencode($row->visitor_id)),
                                'wpfp_unmark_spam_' . $row->visitor_id
                            )); ?>"
                               class="button button-small wpfp-btn-unspam"
                               onclick="return confirm('<?php esc_attr_e('Remove spam marking? This device will be allowed to submit again.', 'wpforms-fingerprint-protection'); ?>');">
                                <span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e('Unmark Spam', 'wpforms-fingerprint-protection'); ?>
                            </a>
                        <?php else: ?>
                            <span class="wpfp-na">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if (!empty($results)): ?>
        <p class="wpfp-results-info">
            <?php 
            echo sprintf(
                /* translators: %d: Number of results shown */
                esc_html__('Showing the most recent %d submissions.', 'wpforms-fingerprint-protection'),
                count($results)
            );
            ?>
        </p>
    <?php endif; ?>
</div>