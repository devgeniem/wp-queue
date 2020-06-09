<?php
/**
 * The queue creator.
 */

namespace Geniem\ImportController;

use Geniem\ImportController\Interfaces\QueueInterface;
use Geniem\ImportController\Interfaces\EntryFetcherInterface;
use Geniem\ImportController\Interfaces\EntryHandlerInterface;

/**
 * Queue creation logic.
 */
class QueueCreator {

    /**
     * The queue instance.
     *
     * @var QueueInterface
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
     * @param QueueInterface        $queue   The queue instance.
     * @param EntryFetcherInterface $fetcher The entry fetcher instance.
     * @param EntryHandlerInterface $handler The entry handler instance.
     */
    public function __construct(
        QueueInterface $queue, EntryFetcherInterface $fetcher, EntryHandlerInterface $handler
    ) {
        $this->queue   = $queue;
        $this->fetcher = $fetcher;
        $this->handler = $handler;
    }

    /**
     * Fetches the queue entries and saves it.
     */
    public function create() {
        $this->entries = $this->fetcher->fetch();
        $this->queue->set_entries( $this->entries );
        $this->queue->set_entry_handler( $this->handler );
        $this->queue->save();
    }
}
