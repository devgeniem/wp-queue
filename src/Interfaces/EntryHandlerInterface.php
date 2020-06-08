<?php
/**
 * Defines the entry handler logic.
 */

namespace Geniem\ImportController\Interfaces;

use Geniem\ImportController\Entry;

/**
 * Use this interface to define an entry handler.
 */
interface EntryHandlerInterface {

    /**
     * The method must return an array of entries or null.
     *
     * @param Entry $entry An entry instance.
     * @return void
     */
    public function handle( Entry $entry );

}
