<?php
/**
 * This file initializes all plugin functionalities.
 */

namespace Geniem\ImportController;

/**
 * Class ImportControllerPlugin
 *
 * @package Geniem\ImportControllerNameSpace
 */
final class ImportControllerPlugin {

    /**
     * Holds the singleton.
     *
     * @var ImportControllerPlugin
     */
    protected static $instance;

    /**
     * Current plugin version.
     *
     * @var string
     */
    protected $version = '';

    /**
     * Get the instance.
     *
     * @return ImportControllerPlugin
     */
    public static function get_instance() : ImportControllerPlugin {
        return self::$instance;
    }

    /**
     * The plugin directory path.
     *
     * @var string
     */
    protected $plugin_path = '';

    /**
     * The plugin root uri without trailing slash.
     *
     * @var string
     */
    protected $plugin_uri = '';

    /**
     * Get the version.
     *
     * @return string
     */
    public function get_version(): string {
        return $this->version;
    }

    /**
     * Get the plugin directory path.
     *
     * @return string
     */
    public function get_plugin_path() : string {
        return $this->plugin_path;
    }

    /**
     * Get the plugin directory uri.
     *
     * @return string
     */
    public function get_plugin_uri() : string {
        return $this->plugin_uri;
    }

    /**
     * Initialize the plugin by creating the singleton.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    public static function init( $version, $plugin_path ) {
        if ( empty( static::$instance ) ) {
            static::$instance = new self( $version, $plugin_path );
            static::$instance->hooks();
        }
    }

    /**
     * Get the plugin instance.
     *
     * @return ImportControllerPlugin
     */
    public static function plugin() {
        return static::$instance;
    }

    /**
     * Initialize the plugin functionalities.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    protected function __construct( $version, $plugin_path ) {
        $this->version     = $version;
        $this->plugin_path = $plugin_path;
        $this->plugin_uri  = plugin_dir_url( $plugin_path ) . basename( $this->plugin_path );
    }

    /**
     * Add plugin hooks and filters.
     */
    protected function hooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
    }

    /**
     * Enqueue admin side scripts if they exist.
     */
    public function enqueue_admin_scripts() {
        // Get file modification times to enable more dynamic versioning.
        $css_mod_time = file_exists( $this->plugin_path . '/assets/dist/admin.css' ) ?
            filemtime( $this->plugin_path . '/assets/dist/admin.css' ) : $this->version;
        $js_mod_time  = file_exists( $this->plugin_path . '/assets/dist/admin.js' ) ?
            filemtime( $this->plugin_path . '/assets/dist/admin.js' ) : $this->version;

        if ( file_exists( $this->plugin_path . '/assets/dist/admin.css' ) ) {
            wp_enqueue_style(
                'import-controller-admin-css',
                $this->plugin_uri . '/assets/dist/admin.css',
                [],
                $css_mod_time,
                'all'
            );
        }

        if ( file_exists( $this->plugin_path . '/assets/dist/admin.js' ) ) {
            wp_enqueue_script(
                'import-controller-admin-js',
                $this->plugin_uri . '/assets/dist/admin.js',
                [ 'jquery' ],
                $js_mod_time
            );
        }
    }

}
