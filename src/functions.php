<?php
/**
 * WordPress Queue global functions.
 */

use Geniem\Queue\QueuePlugin;

if ( ! function_exists( 'wpq' ) ) {
    /**
     * Get the WordPress Queue plugin instance.
     *
     * @return QueuePlugin
     */
    function wpq() : QueuePlugin {
        return QueuePlugin::plugin();
    }
}
