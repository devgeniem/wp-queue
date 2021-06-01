<?php
/**
 * Test global functions.
 */

use Geniem\Queue\Interfaces\EntryInterface;
use Geniem\Queue\QueuePlugin;

/**
 * Class TestFunctions
 */
class TestFunctions extends \PHPUnit\Framework\TestCase {

    public function test_wpq() {
        $this->assertTrue( wpq() instanceof QueuePlugin );
    }

    public function test_wpq_wrap_items_to_entries() {
        $items = [ 1, 2, '3', (object) [ 'a' => 1 ] ];

        $entries = wpq_wrap_items_to_entries( $items );

        array_walk( $entries, function( $entry ) {
            $this->assertTrue( $entry instanceof EntryInterface );
        } );
    }
}