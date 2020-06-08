<?php
/**
 * Defines the import controller queue interface.
 */

namespace Geniem\ImportController\Interfaces;

use Psr\Log\LoggerInterface;

/**
 * Use this interface to customize the queue logic.
 */
interface QueueInterface {

    /**
     * Queue constructor.
     *
     * @param string $name A unique name for the queue.
     */
    public function __construct( string $name );

    /**
     * Getter for the queue name.
     *
     * @return string
     */
    public function get_name() : string;

    /**
     * Getter for the entry handler.
     *
     * @return callable
     */
    public function get_entry_handler() : callable;

    /**
     * Getter for the entries.
     *
     * @return Entry[]
     */
    public function get_entries() : array;

    /**
     * Setter for the entries.
     *
     * @param Entry[] $entries The queue entries.
     */
    public function set_entries( array $entries );

    /**
     * Setter for the entry handler.
     *
     * @param EntryHandlerInterface $handler The entry handler.
     */
    public function set_entry_handler( EntryHandlerInterface $handler );

    /**
     * Setter for the logger.
     *
     * @param LoggerInterface $logger A PSR-3 compatible logger instance.
     */
    public function set_logger( LoggerInterface $logger );

    /**
     * Checks if the queue exists.
     *
     * @return bool
     */
    public function exists() : bool;

    /**
     * Checks whether the queue is empty.
     *
     * @return bool
     */
    public function is_empty() : bool;

    /**
     * Checks the number of entries in the queue.
     *
     * @return integer
     */
    public function get_count() : int;

    /**
     * Runs an entry from the queue and removes it from the queue.
     *
     * @return void
     */
    public function dequeue();

    /**
     * Save the queue. Rewrites all entries.
     *
     * @return void
     */
    public function save();
}