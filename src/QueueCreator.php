<?php
/**
 * The queue creator.
 */

namespace Geniem\Queue;

use Geniem\Queue\Interfaces\QueueInterface;
use Geniem\Queue\Interfaces\EntryFetcherInterface;
use Geniem\Queue\Interfaces\EntryHandlerInterface;

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
     * QueueCreator constructor.
     *
     * @param QueueInterface $queue The queue instance.
     */
    public function __construct( QueueInterface $queue ) {
        $this->queue = $queue;
    }

    /**
     * Fetches the queue entries and saves it.
     */
    public function create() {
        $fetcher = $this->queue->get_entry_fetcher();

        if ( $fetcher instanceof EntryFetcherInterface ) {
            // Run hooks before the entries are fetched.
            do_action( 'wpq_before_fetch', $this->queue );
            do_action( 'wpq_before_fetch_' . $this->queue->get_name(), $this->queue );

            $entries = $this->queue->get_entry_fetcher()->fetch();

            // Run hooks after the entries are fetched.
            do_action( 'wpq_after_fetch', $this->queue, $entries );
            do_action( 'wpq_after_fetch_' . $this->queue->get_name(), $this->queue, $entries );

            $this->queue->set_entries( $entries );
        }

        // Run hooks before the queue is saved.
        do_action( 'wpq_before_save', $this->queue );
        do_action( 'wpq_before_save_' . $this->queue->get_name(), $this->queue );

        $this->queue->save();

        // Run hooks after the queue is saved.
        do_action( 'wpq_after_save', $this->queue );
        do_action( 'wpq_after_save_' . $this->queue->get_name(), $this->queue );
    }
}
