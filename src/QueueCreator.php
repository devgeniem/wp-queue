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
        // Run hooks before the entries are fetched.
        do_action( 'wpq_before_fetch', $this->queue );
        do_action( 'wpq_before_fetch_' . $this->queue->get_name(), $this->queue );

        $entries = $this->queue->get_entry_fetcher()->fetch();

        // Run hooks after the entries are fetched.
        do_action( 'wpq_after_fetch', $this->queue, $entries );
        do_action( 'wpq_after_fetch_' . $this->queue->get_name(), $this->queue, $entries );

        $this->queue->set_entries( $entries );
        $this->queue->save();

        // Run hooks after the queue is saved.
        do_action( 'wpq_after_save', $this->queue );
        do_action( 'wpq_after_save_' . $this->queue->get_name(), $this->queue );
    }
}
