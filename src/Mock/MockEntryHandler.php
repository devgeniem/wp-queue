<?php
/**
 * A mock entry handler.
 */

namespace Geniem\Queue\Mock;

use Geniem\Queue\Interfaces\EntryInterface;
use Geniem\Queue\Interfaces\HandleableInterface;
use Geniem\Queue\Logger;

/**
 * Class MockEntryHandler
 *
 * @package Geniem\Queue\Mock
 */
class MockEntryHandler implements HandleableInterface {

    /**
     * Handle the entry by just logging the data.
     *
     * @param EntryInterface $entry The entry.
     */
    public function handle( EntryInterface $entry ) {
        ( new Logger() )->info( 'Handling an entry.', [ 'entry' => $entry ] );
    }
}
