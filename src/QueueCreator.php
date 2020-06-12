<?php
/**
 * The queue creator.
 */

namespace Geniem\Queue;

use Geniem\Queue\Interfaces\StorageInterface;
use Geniem\Queue\Interfaces\EntryFetcherInterface;
use Geniem\Queue\Interfaces\EntryHandlerInterface;

/**
 * Queue creation logic.
 */
class QueueCreator {

    /**
     * The queue instance.
     *
     * @var StorageInterface
     */
    protected $queue;

    /**
     * The fetcher instance.
     *
     * @var EntryFetcherInterface
     */
    protected $fetcher;

    /**
     * The handler instance.
     *
     * @var EntryHandlerInterface
     */
    protected $handler;

    /**
     * QueueCreator constructor.
     *
     * @param StorageInterface $queue The queue instance.
     */
    public function __construct( StorageInterface $queue ) {
        $this->queue = $queue;
    }

    /**
     * Fetches the queue entries and saves it.
     */
    public function create() {
        $entries = $this->queue->get_entry_fetcher()->fetch();
        $this->queue->set_entries( $entries );
        $this->queue->save();
    }
}
