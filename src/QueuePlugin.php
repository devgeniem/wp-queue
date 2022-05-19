<?php
/**
 * This class initializes all plugin functionalities.
 */

namespace Geniem\Queue;

use Geniem\Queue\CLI\Commands;
use Geniem\Queue\Exception\QueueContainerException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class QueuePlugin
 *
 * @package Geniem\QueueNameSpace
 */
final class QueuePlugin {

    /**
     * Holds the singleton.
     *
     * @var QueuePlugin|null
     */
    protected static $instance;

    /**
     * Current plugin version.
     *
     * @var string
     */
    protected $version = '';

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
     * Holds the queue container.
     *
     * @var ContainerInterface
     */
    protected $queue_container;

    /**
     * The plugin logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Get the version.
     *
     * @return string
     */
    public function get_version() : string {
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
     * Get the queue_container.
     *
     * @return ContainerInterface
     */
    public function get_queue_container() : ContainerInterface {
        return $this->queue_container;
    }

    /**
     * Use this method to override the default queue container instance.
     *
     * @param ContainerInterface $queue_container The queue container instance.
     *
     * @return QueuePlugin Return self to enable chaining.
     */
    public function set_queue_container( ContainerInterface $queue_container ) : QueuePlugin {
        $this->queue_container = $queue_container;

        return $this;
    }

    /**
     * Get the logger. If none is set use the default logger.
     *
     * @return LoggerInterface
     */
    public function get_logger() : LoggerInterface {
        return $this->logger ?? new Logger();
    }

    /**
     * Set the logger.
     *
     * @param ?LoggerInterface $logger The logger.
     *
     * @return QueuePlugin Return self to enable chaining.
     */
    public function set_logger( ?LoggerInterface $logger ) : QueuePlugin {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Initialize the plugin by creating the singleton.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     *
     * @throws \Geniem\Queue\Exception\QueueContainerException If QueueContainer doesn't implement ContainerInterface.
     */
    public static function init( string $version, string $plugin_path ) : void {
        if ( empty( self::$instance ) ) {
            self::$instance = new self( $version, $plugin_path );
            self::$instance->init_container();
            self::$instance->hooks();
            self::$instance->init_cli();
        }
    }

    /**
     * Get the plugin instance.
     *
     * @return QueuePlugin
     */
    public static function plugin() : QueuePlugin {
        if ( static::$instance === null ) {
            static::init( '', dirname( __DIR__, 2 ) );
        }

        return static::$instance;
    }

    /**
     * Initialize the plugin functionalities.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    protected function __construct( string $version, string $plugin_path ) {
        $this->version     = $version;
        $this->plugin_path = $plugin_path;
        $this->plugin_uri  = plugin_dir_url( $plugin_path ) . basename( $this->plugin_path );
    }

    /**
     * Add plugin hooks and filters.
     */
    protected function hooks() : void {
        // Queue plugin ready.
        do_action( 'wpq_init', $this );
        // The hook for adding queue instances.
        do_action( 'wpq_add_queue', $this->queue_container );
    }

    /**
     * Initializes the WP-CLI functionalities.
     *
     * @return void
     */
    protected function init_cli() : void {
        // Register the CLI commands if WP CLI is available.
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            \WP_CLI::add_command( 'queue', Commands::class, [ $this->queue_container ] );
        }
    }

    /**
     * Sets the default queue container.
     *
     * @throws QueueContainerException
     */
    protected function init_container() : void {
        $container = apply_filters( 'wpq_queue_container', new QueueContainer() );

        if ( ! $container instanceof ContainerInterface ) {
            $interface = ContainerInterface::class;
            throw new QueueContainerException( "The queue container must implement the $interface interface." );
        }

        $this->queue_container = $container;
    }

    /**
     * Initializes the logger through a filter.
     */
    protected function init_logger() : void {
        $this->logger = apply_filters( 'wpq_logger', new Logger() );
    }

    /**
     * Enqueue admin side scripts if they exist.
     */
    public function enqueue_admin_scripts() : void {
        // Get file modification times to enable more dynamic versioning.
        $css_mod_time = file_exists( $this->plugin_path . '/assets/dist/admin.css' )
            ? filemtime( $this->plugin_path . '/assets/dist/admin.css' )
            : $this->version;
        $js_mod_time  = file_exists( $this->plugin_path . '/assets/dist/admin.js' )
            ? filemtime( $this->plugin_path . '/assets/dist/admin.js' )
            : $this->version;

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
                $js_mod_time,
                true
            );
        }
    }
}
