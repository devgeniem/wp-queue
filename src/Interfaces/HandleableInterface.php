<?php
/**
 * Defines the entry handler logic.
 */

namespace Geniem\Queue\Interfaces;

/**
 * Use this interface to define an entry handler.
 */
interface HandleableInterface {

    /**
     * The method must return an array of entries or null.
     *
     * @param EntryInterface $entry An entry instance.
     * @return void
     */
    public function handle( EntryInterface $entry );

}
