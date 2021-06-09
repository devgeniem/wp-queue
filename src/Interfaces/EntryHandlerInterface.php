<?php
/**
 * Defines the entry handler logic.
 */

namespace Geniem\Queue\Interfaces;

/**
 * Interface EntryHandlerInterface
 *
 * @package Geniem\Queue\Interfaces
 */
interface EntryHandlerInterface {

    /**
     * The handle method gets the dequeued entry to process.
     * If the handling is successful, return void. If an error
     * occurs, throw an error for the dequeuer to catch.
     *
     * @param EntryInterface $entry An entry instance.
     *
     * @return void
     */
    public function handle( EntryInterface $entry );

}
