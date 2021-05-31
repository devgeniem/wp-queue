<?php
/**
 * Defines the entry fetcher logic.
 */

namespace Geniem\Queue\Interfaces;

use Geniem\Queue\Entry;

/**
 * Use this interface to define an entry fetcher.
 */
interface FetchableInterface {

    /**
     * The method must return an array of entries or null.
     *
     * @return Entry[]|null
     */
    public function fetch() : ?array;

}
