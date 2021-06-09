<?php
/**
 * A mock queue for testing purposes.
 */

namespace Geniem\Queue\Mock;

use Geniem\Queue\Instance\Base;
use Geniem\Queue\Interfaces\EntryInterface;
use Geniem\Queue\Interfaces\EntryFetcherInterface;
use Geniem\Queue\Interfaces\EntryHandlerInterface;
use Geniem\Queue\Logger;

/**
 * Class MockQueue
 *
 * @package Geniem\Queue\Instance
 */
class MockQueue extends Base {

    /**
     * The simple entry queue.
     *
     * @var EntryInterface[]
     */
    protected $queue = [];

    /**
     * A checker for save state.
     *
     * @var bool
     */
    protected $is_saved = false;

    public function is_saved() : bool {
        return $this->is_saved;
    }

    /**
     * Queue constructor.
     *
     * @param string                $name    A unique name for the queue.
     * @param EntryFetcherInterface $fetcher The entry fetcher instance.
     * @param EntryHandlerInterface $handler The entry handler instance.
     */
    public function __construct( string $name, EntryFetcherInterface $fetcher, EntryHandlerInterface $handler ) {
        $this->name          = $name;
        $this->entry_fetcher = $fetcher;
        $this->entry_handler = $handler;

        // Set the default logger.
        $this->logger = new Logger();
    }

    /**
     * The simple queue always exists once instantiated.
     *
     * @return bool
     */
    public function exists(): bool {
        return true;
    }

    /**
     * Is the queue empty?
     *
     * @return bool
     */
    public function is_empty(): bool {
        return empty( $this->queue );
    }

    /**
     * Get the queue size.
     *
     * @return int
     */
    public function size(): int {
        return count( $this->queue );
    }

    /**
     * Pop the first element out of the queue.
     *
     * @return EntryInterface|null The dequeued entry or null.
     */
    public function dequeue(): ?EntryInterface {
        if ( empty( $this->queue ) ) {
            return null;
        }

        $entry = $this->queue[0];

        if ( $entry instanceof EntryInterface ) {
            $this->entry_handler->handle( $entry );

            unset( $this->queue[0] );

            // Reset indexes.
            $this->queue = array_values( $this->queue );

            return $entry;
        }

        return null;
    }

    /**
     * Enqueue a single entry.
     *
     * @param EntryInterface $entry The entry instance.
     */
    public function enqueue( EntryInterface $entry ) {
        array_push( $this->queue, $entry );
    }

    /**
     * Set the queue from the passed entries.
     *
     * @param array|EntryInterface[]|null $entries The entries/items.
     */
    public function set_entries( $entries ) {
        parent::set_entries( $entries );

        $this->queue = $this->entries;
    }

    /**
     * No saving required for this simple queue.
     */
    public function save() {
        $this->is_saved = true;
    }

    /**
     * Delete this queue instance.
     */
    public function delete() {
        // There is no way to internally remove the instance. Just clear the queue.
        $this->clear();
    }

    /**
     * @inheritDoc
     */
    public function clear() {
        $this->queue = [];
    }
}