<?php
/**
 * Defines the entry fetcher logic.
 */

namespace Geniem\Queue\Interfaces;

use Geniem\Queue\Entry;

/**
 * Interface EntryFetcherInterface
 *
 * @package Geniem\Queue\Interfaces
 */
interface EntryFetcherInterface {

    /**
     * The method must return an array of entries or null.
     *
     * @return Entry[]|null
     */
    public function fetch() : ?array;

}
