<?php
/**
 * Plugin Name: WP Site Connector
 * Plugin URI: https://github.com/inopik-lt/wp-connection
 * Description: Connects local WordPress site with remote WordPress site to sync content changes in real-time.
 * Version: 1.0.0
 * Author: Inopik
 * Author URI: https://github.com/inopik-lt
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-site-connector
 * Requires at least: 5.0
 * Requires PHP: 7.2
 *
 * @package WP_Site_Connector
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'WP_SITE_CONNECTOR_VERSION', '1.0.0' );
define( 'WP_SITE_CONNECTOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_SITE_CONNECTOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_SITE_CONNECTOR_PLUGIN_FILE', __FILE__ );

/**
 * Main plugin class.
 */
class WP_Site_Connector {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin.
     */
    private function __construct() {
        // Load required files.
        $this->includes();

        // Initialize components.
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        
        // Register activation/deactivation hooks.
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
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
     * Include required files.
     */
    private function includes() {
        require_once WP_SITE_CONNECTOR_PLUGIN_DIR . 'includes/class-admin.php';
        require_once WP_SITE_CONNECTOR_PLUGIN_DIR . 'includes/class-api.php';
        require_once WP_SITE_CONNECTOR_PLUGIN_DIR . 'includes/class-sync.php';
        require_once WP_SITE_CONNECTOR_PLUGIN_DIR . 'includes/class-logger.php';
    }

    /**
     * Initialize plugin components.
     */
    public function init() {
        // Initialize admin interface.
        if ( is_admin() ) {
            WP_Site_Connector_Admin::get_instance();
        }

        // Initialize REST API endpoints.
        WP_Site_Connector_API::get_instance();

        // Initialize sync functionality.
        WP_Site_Connector_Sync::get_instance();

        // Load text domain.
        load_plugin_textdomain( 'wp-site-connector', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Plugin activation.
     */
    public function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'site_connector_logs';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            object_type varchar(50) NOT NULL,
            object_id bigint(20) NOT NULL,
            remote_url varchar(255) NOT NULL,
            status varchar(20) NOT NULL,
            message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY event_type (event_type),
            KEY object_id (object_id),
            KEY status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Add default options.
        add_option( 'wp_site_connector_api_key', wp_generate_password( 32, false ) );
        add_option( 'wp_site_connector_remote_url', '' );
        add_option( 'wp_site_connector_remote_api_key', '' );
        add_option( 'wp_site_connector_sync_posts', '1' );
        add_option( 'wp_site_connector_sync_pages', '1' );
        add_option( 'wp_site_connector_enabled', '0' );
    }

    /**
     * Plugin deactivation.
     */
    public function deactivate() {
        // Clean up if needed.
        wp_clear_scheduled_hook( 'wp_site_connector_sync_check' );
    }
}

// Initialize the plugin.
WP_Site_Connector::get_instance();
