<?php
/**
 * Defines functionalities for queue instances.
 */

namespace Geniem\Queue\Interfaces;

use Psr\Log\LoggerInterface;

/**
 * Interface QueueInterface
 *
 * @package Geniem\Queue\Interfaces
 */
interface QueueInterface {

    /**
     * Queue constructor.
     *
     * @param string              $name    A unique name for the queue.
     * @param FetchableInterface  $fetcher The entry fetcher instance.
     * @param HandleableInterface $handler The entry handler instance.
     */
    public function __construct( string $name, FetchableInterface $fetcher, HandleableInterface $handler );

    /**
     * Getter for the queue name.
     *
     * @return string|null
     */
    public function get_name() : ?string;

    /**
     * Getter for the entry handler.
     *
     * @return HandleableInterface|null
     */
    public function get_entry_handler() : ?HandleableInterface;

    /**
     * Getter for the entry fetcher.
     *
     * @return FetchableInterface|null
     */
    public function get_entry_fetcher() : ?FetchableInterface;

    /**
     * Getter for the entries.
     *
     * @return EntryInterface[]|null
     */
    public function get_entries() : ?array;

    /**
     * Getter for the logger.
     *
     * @return LoggerInterface|null
     */
    public function get_logger() : ?LoggerInterface;

    /**
     * Setter for the entries.
     *
     * @param EntryInterface[] $entries The queue entries.
     */
    public function set_entries( array $entries );

    /**
     * Setter for the entry handler.
     *
     * @param HandleableInterface $handler The entry handler.
     */
    public function set_entry_handler( HandleableInterface $handler );

    /**
     * Setter for the entry fetcher.
     *
     * @param FetchableInterface $fetcher The entry handler.
     */
    public function set_entry_fetcher( FetchableInterface $fetcher );

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
    public function size() : int;

    /**
     * Runs an entry from the queue and removes it from the queue.
     *
     * @return void
     */
    public function dequeue();

    /**
     * Adds an entry at the end of the queue.
     *
     * @param EntryInterface $entry An entry instance.
     * @return void
     */
    public function enqueue( EntryInterface $entry );

    /**
     * Save the queue. Rewrites all entries.
     *
     * @return void
     */
    public function save();

    /**
     * Delete the entire queue. This method should remove all entries by calling clear().
     *
     * @return void
     */
    public function delete();

    /**
     * Clear all entries.
     *
     * @return void
     */
    public function clear();
}
