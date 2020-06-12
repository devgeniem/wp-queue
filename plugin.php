<?php
/**
 * Plugin Name:       WordPress Queue
 * Plugin URI:        https://github.com/devgeniem/wp-import-controller
 * Description:       WordPress Queue is a modular library for managing queued tasks in WordPress.
 * Version:           1.0.0
 * Requires at least: 5.4
 * Requires PHP:      7.0
 * Author:            Geniem
 * Author URI:        https://geniem.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

use Geniem\Queue\QueuePlugin;

// Check if Composer has been initialized in this directory.
// Otherwise we just use global composer autoloading.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Get the plugin version.
$plugin_data    = get_file_data( __FILE__, [ 'Version' => 'Version' ], 'plugin' );
$plugin_version = $plugin_data['Version'];

$plugin_path = __DIR__;

// Initialize the plugin.
QueuePlugin::init( $plugin_version, $plugin_path );

if ( ! function_exists( 'geniem_import_controller' ) ) {
    /**
     * Get the Import Controller plugin instance.
     *
     * @return QueuePlugin
     */
    function geniem_import_controller() : QueuePlugin {
        return QueuePlugin::plugin();
    }
}
