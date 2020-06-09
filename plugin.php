<?php
/**
 * Plugin Name:       WordPress Import Controller
 * Plugin URI:        https://github.com/devgeniem/wp-import-controller
 * Description:       This plugin adds a modular multipurpose importer logic to WordPress.
 * Version:           1.0.0
 * Requires at least: 5.4
 * Requires PHP:      7.0
 * Author:            Geniem
 * Author URI:        https://geniem.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

use Geniem\ImportController\ImportControllerPlugin;

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
ImportControllerPlugin::init( $plugin_version, $plugin_path );

if ( ! function_exists( 'geniem_import_controller' ) ) {
    /**
     * Get the Import Controller plugin instance.
     *
     * @return ImportControllerPlugin
     */
    function geniem_import_controller() : ImportControllerPlugin {
        return ImportControllerPlugin::plugin();
    }
}
