<?php
/**
 * WordPress Queue global functions.
 */

use Geniem\Queue\QueuePlugin;

if ( ! function_exists( 'wordpress_queue' ) ) {
    /**
     * Get the WordPress Queue plugin instance.
     *
     * @return QueuePlugin
     */
    function wordpress_queue() : QueuePlugin {
        return QueuePlugin::plugin();
    }
}
