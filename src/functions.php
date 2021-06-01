<?php
/**
 * WordPress Queue global functions.
 */

use Geniem\Queue\Entry;
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
/**
 * Wraps item data into entries.
 *
 * @param array $items The items.
 *
 * @return array
 */
function wpq_wrap_items_to_entries( array $items ) : array {
    // Wrap data into entries if not already wrapped.
    return array_map(
        function( $item ) {
            if ( $item instanceof Entry ) {
                return $item;
            }
            $entry = new Entry();
            $entry->set_data( $item );
            return $entry;
        },
        $items
    );
}
