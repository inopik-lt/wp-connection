<?php
/**
 * Admin interface for WP Site Connector.
 *
 * @package WP_Site_Connector
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin class.
 */
class WP_Site_Connector_Admin {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Initialize admin functionality.
     */
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_wp_site_connector_regenerate_key', array( $this, 'ajax_regenerate_key' ) );
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
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'WP Site Connector', 'wp-site-connector' ),
            __( 'Site Connector', 'wp-site-connector' ),
            'manage_options',
            'wp-site-connector',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        register_setting( 'wp_site_connector_settings', 'wp_site_connector_enabled' );
        register_setting( 'wp_site_connector_settings', 'wp_site_connector_remote_url' );
        register_setting( 'wp_site_connector_settings', 'wp_site_connector_remote_api_key' );
        register_setting( 'wp_site_connector_settings', 'wp_site_connector_api_key' );
        register_setting( 'wp_site_connector_settings', 'wp_site_connector_sync_posts' );
        register_setting( 'wp_site_connector_settings', 'wp_site_connector_sync_pages' );

        add_settings_section(
            'wp_site_connector_main',
            __( 'Connection Settings', 'wp-site-connector' ),
            array( $this, 'render_section_info' ),
            'wp-site-connector'
        );

        add_settings_field(
            'enabled',
            __( 'Enable Sync', 'wp-site-connector' ),
            array( $this, 'render_enabled_field' ),
            'wp-site-connector',
            'wp_site_connector_main'
        );

        add_settings_field(
            'remote_url',
            __( 'Remote Site URL', 'wp-site-connector' ),
            array( $this, 'render_remote_url_field' ),
            'wp-site-connector',
            'wp_site_connector_main'
        );

        add_settings_field(
            'remote_api_key',
            __( 'Remote Site API Key', 'wp-site-connector' ),
            array( $this, 'render_remote_api_key_field' ),
            'wp-site-connector',
            'wp_site_connector_main'
        );

        add_settings_field(
            'local_api_key',
            __( 'Local API Key', 'wp-site-connector' ),
            array( $this, 'render_local_api_key_field' ),
            'wp-site-connector',
            'wp_site_connector_main'
        );

        add_settings_field(
            'sync_posts',
            __( 'Sync Posts', 'wp-site-connector' ),
            array( $this, 'render_sync_posts_field' ),
            'wp-site-connector',
            'wp_site_connector_main'
        );

        add_settings_field(
            'sync_pages',
            __( 'Sync Pages', 'wp-site-connector' ),
            array( $this, 'render_sync_pages_field' ),
            'wp-site-connector',
            'wp_site_connector_main'
        );
    }

    /**
     * Render section info.
     */
    public function render_section_info() {
        echo '<p>' . esc_html__( 'Configure the connection to your remote WordPress site.', 'wp-site-connector' ) . '</p>';
    }

    /**
     * Render enabled field.
     */
    public function render_enabled_field() {
        $value = get_option( 'wp_site_connector_enabled', '0' );
        ?>
        <label>
            <input type="checkbox" name="wp_site_connector_enabled" value="1" <?php checked( $value, '1' ); ?> />
            <?php esc_html_e( 'Enable automatic synchronization', 'wp-site-connector' ); ?>
        </label>
        <?php
    }

    /**
     * Render remote URL field.
     */
    public function render_remote_url_field() {
        $value = get_option( 'wp_site_connector_remote_url', '' );
        ?>
        <input type="url" name="wp_site_connector_remote_url" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="https://example.com" />
        <p class="description"><?php esc_html_e( 'The URL of the remote WordPress site (without trailing slash).', 'wp-site-connector' ); ?></p>
        <?php
    }

    /**
     * Render remote API key field.
     */
    public function render_remote_api_key_field() {
        $value = get_option( 'wp_site_connector_remote_api_key', '' );
        ?>
        <input type="text" name="wp_site_connector_remote_api_key" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e( 'The API key from the remote WordPress site.', 'wp-site-connector' ); ?></p>
        <?php
    }

    /**
     * Render local API key field.
     */
    public function render_local_api_key_field() {
        $value = get_option( 'wp_site_connector_api_key', '' );
        ?>
        <input type="text" name="wp_site_connector_api_key" value="<?php echo esc_attr( $value ); ?>" class="regular-text" readonly />
        <button type="button" class="button" id="regenerate-api-key"><?php esc_html_e( 'Regenerate', 'wp-site-connector' ); ?></button>
        <p class="description"><?php esc_html_e( 'Share this API key with the remote site to receive updates.', 'wp-site-connector' ); ?></p>
        <?php
    }

    /**
     * Render sync posts field.
     */
    public function render_sync_posts_field() {
        $value = get_option( 'wp_site_connector_sync_posts', '1' );
        ?>
        <label>
            <input type="checkbox" name="wp_site_connector_sync_posts" value="1" <?php checked( $value, '1' ); ?> />
            <?php esc_html_e( 'Synchronize posts', 'wp-site-connector' ); ?>
        </label>
        <?php
    }

    /**
     * Render sync pages field.
     */
    public function render_sync_pages_field() {
        $value = get_option( 'wp_site_connector_sync_pages', '1' );
        ?>
        <label>
            <input type="checkbox" name="wp_site_connector_sync_pages" value="1" <?php checked( $value, '1' ); ?> />
            <?php esc_html_e( 'Synchronize pages', 'wp-site-connector' ); ?>
        </label>
        <?php
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Handle test connection.
        if ( isset( $_POST['test_connection'] ) && check_admin_referer( 'wp_site_connector_test' ) ) {
            $this->test_connection();
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'wp_site_connector_settings' );
                do_settings_sections( 'wp-site-connector' );
                submit_button();
                ?>
            </form>

            <hr />

            <h2><?php esc_html_e( 'Test Connection', 'wp-site-connector' ); ?></h2>
            <form method="post">
                <?php wp_nonce_field( 'wp_site_connector_test' ); ?>
                <p>
                    <button type="submit" name="test_connection" class="button button-secondary">
                        <?php esc_html_e( 'Test Connection', 'wp-site-connector' ); ?>
                    </button>
                </p>
            </form>

            <hr />

            <h2><?php esc_html_e( 'Sync Logs', 'wp-site-connector' ); ?></h2>
            <?php $this->render_logs(); ?>
        </div>
        <?php
    }

    /**
     * Test connection to remote site.
     */
    private function test_connection() {
        $remote_url = get_option( 'wp_site_connector_remote_url', '' );
        $remote_api_key = get_option( 'wp_site_connector_remote_api_key', '' );

        if ( empty( $remote_url ) || empty( $remote_api_key ) ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Please configure the remote URL and API key first.', 'wp-site-connector' ) . '</p></div>';
            return;
        }

        $response = wp_remote_post(
            trailingslashit( $remote_url ) . 'wp-json/wp-site-connector/v1/test',
            array(
                'headers' => array(
                    'X-API-Key' => $remote_api_key,
                ),
                'timeout' => 15,
            )
        );

        if ( is_wp_error( $response ) ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Connection failed: ', 'wp-site-connector' ) . esc_html( $response->get_error_message() ) . '</p></div>';
        } else {
            $code = wp_remote_retrieve_response_code( $response );
            if ( 200 === $code ) {
                echo '<div class="notice notice-success"><p>' . esc_html__( 'Connection successful!', 'wp-site-connector' ) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html__( 'Connection failed with code: ', 'wp-site-connector' ) . esc_html( $code ) . '</p></div>';
            }
        }
    }

    /**
     * Render sync logs.
     */
    private function render_logs() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'site_connector_logs';
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d",
                50
            )
        );

        if ( empty( $logs ) ) {
            echo '<p>' . esc_html__( 'No sync logs yet.', 'wp-site-connector' ) . '</p>';
            return;
        }

        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'wp-site-connector' ); ?></th>
                    <th><?php esc_html_e( 'Event', 'wp-site-connector' ); ?></th>
                    <th><?php esc_html_e( 'Object', 'wp-site-connector' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'wp-site-connector' ); ?></th>
                    <th><?php esc_html_e( 'Message', 'wp-site-connector' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $logs as $log ) : ?>
                    <tr>
                        <td><?php echo esc_html( $log->created_at ); ?></td>
                        <td><?php echo esc_html( $log->event_type ); ?></td>
                        <td><?php echo esc_html( $log->object_type . ' #' . $log->object_id ); ?></td>
                        <td>
                            <span class="status-<?php echo esc_attr( $log->status ); ?>">
                                <?php echo esc_html( $log->status ); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html( $log->message ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Enqueue admin scripts.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_scripts( $hook ) {
        if ( 'settings_page_wp-site-connector' !== $hook ) {
            return;
        }

        wp_enqueue_script(
            'wp-site-connector-admin',
            WP_SITE_CONNECTOR_PLUGIN_URL . 'assets/admin.js',
            array( 'jquery' ),
            WP_SITE_CONNECTOR_VERSION,
            true
        );

        wp_localize_script(
            'wp-site-connector-admin',
            'wpSiteConnector',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wp_site_connector_admin' ),
            )
        );

        wp_enqueue_style(
            'wp-site-connector-admin',
            WP_SITE_CONNECTOR_PLUGIN_URL . 'assets/admin.css',
            array(),
            WP_SITE_CONNECTOR_VERSION
        );
    }

    /**
     * AJAX handler to regenerate API key.
     */
    public function ajax_regenerate_key() {
        check_ajax_referer( 'wp_site_connector_admin', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized', 'wp-site-connector' ) ) );
        }

        $new_key = wp_generate_password( 32, false );
        update_option( 'wp_site_connector_api_key', $new_key );

        wp_send_json_success( array( 'api_key' => $new_key ) );
    }
}
