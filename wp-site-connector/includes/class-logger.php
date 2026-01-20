<?php
/**
 * Logger functionality for WP Site Connector.
 *
 * @package WP_Site_Connector
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Logger class.
 */
class WP_Site_Connector_Logger {

    /**
     * Log a sync event.
     *
     * @param string $event_type Event type (create/update/delete).
     * @param string $object_type Object type (post/page).
     * @param int    $object_id Object ID.
     * @param string $remote_url Remote URL.
     * @param string $status Status (success/failed).
     * @param string $message Message.
     */
    public static function log( $event_type, $object_type, $object_id, $remote_url, $status, $message ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'site_connector_logs';

        $wpdb->insert(
            $table_name,
            array(
                'event_type'  => sanitize_text_field( $event_type ),
                'object_type' => sanitize_text_field( $object_type ),
                'object_id'   => absint( $object_id ),
                'remote_url'  => esc_url_raw( $remote_url ),
                'status'      => sanitize_text_field( $status ),
                'message'     => sanitize_text_field( $message ),
            ),
            array( '%s', '%s', '%d', '%s', '%s', '%s' )
        );
    }

    /**
     * Get logs.
     *
     * @param int $limit Number of logs to retrieve.
     * @return array Array of log entries.
     */
    public static function get_logs( $limit = 50 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'site_connector_logs';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Clear old logs.
     *
     * @param int $days Number of days to keep.
     */
    public static function clear_old_logs( $days = 30 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'site_connector_logs';

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
    }
}
