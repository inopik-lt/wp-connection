<?php
/**
 * Sync functionality for WP Site Connector.
 *
 * @package WP_Site_Connector
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sync class.
 */
class WP_Site_Connector_Sync {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Initialize sync functionality.
     */
    private function __construct() {
        // Hook into post actions.
        add_action( 'save_post', array( $this, 'sync_on_save' ), 10, 3 );
        add_action( 'before_delete_post', array( $this, 'sync_on_delete' ), 10, 2 );
    }

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sync post on save.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post Post object.
     * @param bool    $update Whether this is an update.
     */
    public function sync_on_save( $post_id, $post, $update ) {
        // Check if sync is enabled.
        if ( ! get_option( 'wp_site_connector_enabled', '0' ) ) {
            return;
        }

        // Skip if this is an autosave or revision.
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Check if we should sync this post type.
        if ( 'post' === $post->post_type && ! get_option( 'wp_site_connector_sync_posts', '1' ) ) {
            return;
        }

        if ( 'page' === $post->post_type && ! get_option( 'wp_site_connector_sync_pages', '1' ) ) {
            return;
        }

        // Skip if this post came from remote (to prevent infinite loop).
        if ( get_post_meta( $post_id, '_wp_site_connector_remote_id', true ) ) {
            return;
        }

        // Only sync posts and pages.
        if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
            return;
        }

        // Sync the post.
        $this->push_to_remote( $post_id, $post, $update ? 'update' : 'create' );
    }

    /**
     * Sync post on delete.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post Post object.
     */
    public function sync_on_delete( $post_id, $post ) {
        // Check if sync is enabled.
        if ( ! get_option( 'wp_site_connector_enabled', '0' ) ) {
            return;
        }

        // Skip if this post came from remote.
        if ( get_post_meta( $post_id, '_wp_site_connector_remote_id', true ) ) {
            return;
        }

        // Check if we should sync this post type.
        if ( 'post' === $post->post_type && ! get_option( 'wp_site_connector_sync_posts', '1' ) ) {
            return;
        }

        if ( 'page' === $post->post_type && ! get_option( 'wp_site_connector_sync_pages', '1' ) ) {
            return;
        }

        // Only sync posts and pages.
        if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
            return;
        }

        // Send delete request.
        $this->delete_from_remote( $post_id, $post );
    }

    /**
     * Push post to remote site.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post Post object.
     * @param string  $event_type Event type (create/update).
     */
    private function push_to_remote( $post_id, $post, $event_type ) {
        $remote_url = get_option( 'wp_site_connector_remote_url', '' );
        $remote_api_key = get_option( 'wp_site_connector_remote_api_key', '' );

        if ( empty( $remote_url ) || empty( $remote_api_key ) ) {
            WP_Site_Connector_Logger::log(
                $event_type,
                $post->post_type,
                $post_id,
                $remote_url,
                'failed',
                __( 'Remote URL or API key not configured.', 'wp-site-connector' )
            );
            return;
        }

        // Prepare post data.
        $data = array(
            'object_type' => $post->post_type,
            'remote_id'   => $post_id,
            'data'        => array(
                'title'   => $post->post_title,
                'content' => $post->post_content,
                'excerpt' => $post->post_excerpt,
                'status'  => $post->post_status,
            ),
        );

        // Add featured image if available.
        $thumbnail_id = get_post_thumbnail_id( $post_id );
        if ( $thumbnail_id ) {
            $image_url = wp_get_attachment_url( $thumbnail_id );
            if ( $image_url ) {
                $data['data']['featured_image_url'] = $image_url;
            }
        }

        // Add taxonomies.
        $taxonomies = get_object_taxonomies( $post->post_type );
        if ( ! empty( $taxonomies ) ) {
            $terms = array();
            foreach ( $taxonomies as $taxonomy ) {
                $post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'names' ) );
                if ( ! is_wp_error( $post_terms ) && ! empty( $post_terms ) ) {
                    $terms[ $taxonomy ] = $post_terms;
                }
            }
            if ( ! empty( $terms ) ) {
                $data['data']['terms'] = $terms;
            }
        }

        // Send request to remote site.
        $response = wp_remote_post(
            trailingslashit( $remote_url ) . 'wp-json/wp-site-connector/v1/receive',
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'X-API-Key'    => $remote_api_key,
                ),
                'body'    => wp_json_encode( $data ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            WP_Site_Connector_Logger::log(
                $event_type,
                $post->post_type,
                $post_id,
                $remote_url,
                'failed',
                $response->get_error_message()
            );
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 === $code ) {
            WP_Site_Connector_Logger::log(
                $event_type,
                $post->post_type,
                $post_id,
                $remote_url,
                'success',
                isset( $body['message'] ) ? $body['message'] : __( 'Synced successfully.', 'wp-site-connector' )
            );
        } else {
            WP_Site_Connector_Logger::log(
                $event_type,
                $post->post_type,
                $post_id,
                $remote_url,
                'failed',
                isset( $body['message'] ) ? $body['message'] : sprintf( __( 'HTTP error: %d', 'wp-site-connector' ), $code )
            );
        }
    }

    /**
     * Delete post from remote site.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post Post object.
     */
    private function delete_from_remote( $post_id, $post ) {
        $remote_url = get_option( 'wp_site_connector_remote_url', '' );
        $remote_api_key = get_option( 'wp_site_connector_remote_api_key', '' );

        if ( empty( $remote_url ) || empty( $remote_api_key ) ) {
            WP_Site_Connector_Logger::log(
                'delete',
                $post->post_type,
                $post_id,
                $remote_url,
                'failed',
                __( 'Remote URL or API key not configured.', 'wp-site-connector' )
            );
            return;
        }

        // Prepare delete data.
        $data = array(
            'object_type' => $post->post_type,
            'remote_id'   => $post_id,
        );

        // Send delete request to remote site.
        $response = wp_remote_post(
            trailingslashit( $remote_url ) . 'wp-json/wp-site-connector/v1/delete',
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'X-API-Key'    => $remote_api_key,
                ),
                'body'    => wp_json_encode( $data ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            WP_Site_Connector_Logger::log(
                'delete',
                $post->post_type,
                $post_id,
                $remote_url,
                'failed',
                $response->get_error_message()
            );
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 === $code ) {
            WP_Site_Connector_Logger::log(
                'delete',
                $post->post_type,
                $post_id,
                $remote_url,
                'success',
                isset( $body['message'] ) ? $body['message'] : __( 'Deleted successfully.', 'wp-site-connector' )
            );
        } else {
            WP_Site_Connector_Logger::log(
                'delete',
                $post->post_type,
                $post_id,
                $remote_url,
                'failed',
                isset( $body['message'] ) ? $body['message'] : sprintf( __( 'HTTP error: %d', 'wp-site-connector' ), $code )
            );
        }
    }
}
