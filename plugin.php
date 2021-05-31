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
 * License:           MIT
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Geniem\Queue;

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

// Require global functions.
require_once __DIR__ . '/src/functions.php';
