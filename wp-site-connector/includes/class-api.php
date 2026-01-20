<?php
/**
 * REST API endpoints for WP Site Connector.
 *
 * @package WP_Site_Connector
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * API class.
 */
class WP_Site_Connector_API {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Initialize API functionality.
     */
    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
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
     * Register REST API routes.
     */
    public function register_routes() {
        $namespace = 'wp-site-connector/v1';

        // Test endpoint.
        register_rest_route(
            $namespace,
            '/test',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'test_endpoint' ),
                'permission_callback' => array( $this, 'check_api_key' ),
            )
        );

        // Receive content endpoint.
        register_rest_route(
            $namespace,
            '/receive',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'receive_content' ),
                'permission_callback' => array( $this, 'check_api_key' ),
            )
        );

        // Delete content endpoint.
        register_rest_route(
            $namespace,
            '/delete',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'delete_content' ),
                'permission_callback' => array( $this, 'check_api_key' ),
            )
        );
    }

    /**
     * Check API key authentication.
     *
     * @param WP_REST_Request $request Request object.
     * @return bool True if authenticated.
     */
    public function check_api_key( $request ) {
        $api_key = $request->get_header( 'X-API-Key' );
        $local_api_key = get_option( 'wp_site_connector_api_key', '' );

        if ( empty( $api_key ) || empty( $local_api_key ) ) {
            return false;
        }

        return hash_equals( $local_api_key, $api_key );
    }

    /**
     * Test endpoint.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function test_endpoint( $request ) {
        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Connection successful!', 'wp-site-connector' ),
            ),
            200
        );
    }

    /**
     * Receive content from remote site.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function receive_content( $request ) {
        $params = $request->get_json_params();

        if ( empty( $params['object_type'] ) || empty( $params['data'] ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Missing required parameters.', 'wp-site-connector' ),
                ),
                400
            );
        }

        $object_type = sanitize_text_field( $params['object_type'] );
        $data = $params['data'];
        $remote_id = isset( $params['remote_id'] ) ? absint( $params['remote_id'] ) : 0;

        // Check if we should sync this content type.
        if ( 'post' === $object_type && ! get_option( 'wp_site_connector_sync_posts', '1' ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Post synchronization is disabled.', 'wp-site-connector' ),
                ),
                403
            );
        }

        if ( 'page' === $object_type && ! get_option( 'wp_site_connector_sync_pages', '1' ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Page synchronization is disabled.', 'wp-site-connector' ),
                ),
                403
            );
        }

        // Prepare post data.
        $post_data = array(
            'post_title'   => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '',
            'post_content' => isset( $data['content'] ) ? wp_kses_post( $data['content'] ) : '',
            'post_excerpt' => isset( $data['excerpt'] ) ? sanitize_textarea_field( $data['excerpt'] ) : '',
            'post_status'  => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'draft',
            'post_type'    => $object_type,
        );

        // Check if post already exists with this remote ID.
        $existing_id = $this->get_local_id_by_remote_id( $remote_id, $object_type );

        if ( $existing_id ) {
            // Update existing post.
            $post_data['ID'] = $existing_id;
            $result = wp_update_post( $post_data, true );
        } else {
            // Create new post.
            $result = wp_insert_post( $post_data, true );

            if ( ! is_wp_error( $result ) ) {
                // Store remote ID mapping.
                update_post_meta( $result, '_wp_site_connector_remote_id', $remote_id );
            }
        }

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => $result->get_error_message(),
                ),
                500
            );
        }

        // Handle featured image if provided.
        if ( ! empty( $data['featured_image_url'] ) ) {
            $this->set_featured_image_from_url( $result, $data['featured_image_url'] );
        }

        // Handle taxonomies if provided.
        if ( ! empty( $data['terms'] ) && is_array( $data['terms'] ) ) {
            foreach ( $data['terms'] as $taxonomy => $terms ) {
                wp_set_object_terms( $result, $terms, $taxonomy );
            }
        }

        return new WP_REST_Response(
            array(
                'success'  => true,
                'message'  => __( 'Content synchronized successfully.', 'wp-site-connector' ),
                'local_id' => $result,
            ),
            200
        );
    }

    /**
     * Delete content.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function delete_content( $request ) {
        $params = $request->get_json_params();

        if ( empty( $params['object_type'] ) || empty( $params['remote_id'] ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Missing required parameters.', 'wp-site-connector' ),
                ),
                400
            );
        }

        $object_type = sanitize_text_field( $params['object_type'] );
        $remote_id = absint( $params['remote_id'] );

        // Find local post by remote ID.
        $local_id = $this->get_local_id_by_remote_id( $remote_id, $object_type );

        if ( ! $local_id ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Content not found.', 'wp-site-connector' ),
                ),
                404
            );
        }

        // Delete the post.
        $result = wp_delete_post( $local_id, true );

        if ( ! $result ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Failed to delete content.', 'wp-site-connector' ),
                ),
                500
            );
        }

        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Content deleted successfully.', 'wp-site-connector' ),
            ),
            200
        );
    }

    /**
     * Get local ID by remote ID.
     *
     * @param int    $remote_id Remote post ID.
     * @param string $post_type Post type.
     * @return int|false Local post ID or false.
     */
    private function get_local_id_by_remote_id( $remote_id, $post_type ) {
        $posts = get_posts(
            array(
                'post_type'      => $post_type,
                'posts_per_page' => 1,
                'meta_key'       => '_wp_site_connector_remote_id',
                'meta_value'     => $remote_id,
                'fields'         => 'ids',
            )
        );

        return ! empty( $posts ) ? $posts[0] : false;
    }

    /**
     * Set featured image from URL.
     *
     * @param int    $post_id Post ID.
     * @param string $image_url Image URL.
     * @return int|false Attachment ID or false.
     */
    private function set_featured_image_from_url( $post_id, $image_url ) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_sideload_image( $image_url, $post_id, null, 'id' );

        if ( ! is_wp_error( $attachment_id ) ) {
            set_post_thumbnail( $post_id, $attachment_id );
            return $attachment_id;
        }

        return false;
    }
}
