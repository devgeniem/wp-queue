<?php
/**
 * A simple queue for testing purposes.
 */

namespace Geniem\Queue\Instance;

use Geniem\Queue\Interfaces\EntryInterface;
use Geniem\Queue\Interfaces\FetchableInterface;
use Geniem\Queue\Interfaces\HandleableInterface;
use Geniem\Queue\Logger;

/**
 * Class SimpleQueue
 *
 * @package Geniem\Queue\Instance
 */
class SimpleQueue extends Base implements \Geniem\Queue\Interfaces\QueueInterface {

    /**
     * The simple entry queue.
     *
     * @var EntryInterface[]
     */
    protected $queue = [];

    /**
     * Queue constructor.
     *
     * @param string              $name    A unique name for the queue.
     * @param FetchableInterface  $fetcher The entry fetcher instance.
     * @param HandleableInterface $handler The entry handler instance.
     */
    public function __construct( string $name, FetchableInterface $fetcher, HandleableInterface $handler ) {
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
     */
    public function dequeue() {
        if ( empty( $this->queue ) ) {
            return;
        }

        $entry = $this->queue[0];

        if ( $entry instanceof EntryInterface ) {
            $this->entry_handler->handle( $entry );

            unset( $this->queue[0] );

            // Reset indexes.
            $this->queue = array_values( $this->queue );
        }
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
     * No saving required for this simple queue.
     */
    public function save() {
        // Nothing to do for a simple queue.
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