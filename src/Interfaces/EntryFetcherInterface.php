<?php
/**
 * Defines the entry fetcher logic.
 */

namespace Geniem\ImportController\Interfaces;

use Geniem\ImportController\Entry;

/**
 * Use this interface to define an entry fetcher.
 */
interface EntryFetcherInterface {

    /**
     * The method must return an array of entries or null.
     *
     * @return Entry[]|null
     */
    public function fetch() : ?array;

}
