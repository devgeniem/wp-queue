<?php
/**
 * The enqueue controller.
 */

namespace Geniem\Queue;

use Psr\Log\LoggerInterface;
use Geniem\Queue\Interfaces\EntryInterface;
use Geniem\Queue\Interfaces\QueueInterface;

/**
 * Class Enqueuer
 *
 * @package Geniem\Queue
 */
class Enqueuer {

    /**
     * The enqueue logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger An optional PSR-3 compatible logger instance.
     *                                     If no logger is passed, dequeuer uses the plugin default.
     */
    public function __construct( ?LoggerInterface $logger = null ) {
        $this->logger = $logger ?? wpq()->get_logger();
    }

    /**
     * Fetch new entries and add them to the queue.
     *
     * @param QueueInterface $queue The queue name.
     *
     * @return int Number of entries enqueued. Negative integer on error.
     */
    public function fetch( QueueInterface $queue ) : int {
        $name = $queue->get_name();

        if ( ! $queue->exists() ) {
            $this->logger->error( "Unable to find the queue: $name.", [ __CLASS__ ] );
            return false;
        }

        // Fetch new entries.
        try {
            // Run hooks before the entries are fetched.
            do_action( 'wpq_before_fetch', $queue );
            do_action( 'wpq_before_fetch_' . $name, $queue );

            $fetcher = $queue->get_entry_fetcher();
            $entries = $fetcher->fetch();

            // Run hooks after the entries are fetched.
            do_action( 'wpq_after_fetch', $queue, $entries );
            do_action( 'wpq_after_fetch_' . $name, $queue, $entries );

            // Add entries to the queue.
            if ( ! empty( $entries ) ) {
                // Wrap data into entries if not already wrapped.
                $wrapped_entries = wpq_wrap_items_to_entries( $entries );

                // Enqueue entries.
                array_walk(
                    $wrapped_entries,
                    function( EntryInterface $entry ) use ( $queue ) {
                        $this->enqueue( $queue, $entry );
                    }
                );
            }

            // Run hooks after the entries are the process is completed.
            do_action( 'wpq_after_fetch_complete', $queue, $entries );
            do_action( 'wpq_after_fetch_complete_' . $name, $queue, $entries );

            return count( $entries );
        }
        catch ( \Exception $error ) {
            if ( $this->logger ) {
                $this->logger->error(
                    "An error occurred while enqueueing new entries to queue: $name.",
                    [
                        'message' => $error->getMessage(),
                        'file'    => $error->getFile(),
                        'line'    => $error->getLine(),
                    ]
                );
            }

            return -1;
        }
    }

    /**
     * Enqueue a single entry into a queue.
     *
     * @param QueueInterface $queue The queue name.
     * @param EntryInterface $entry The entry.
     *
     * @return bool True on success, false on failure.
     */
    public function enqueue( QueueInterface $queue, EntryInterface $entry ) : bool {
        $name = $queue->get_name();

        if ( ! $queue->exists() ) {
            $this->logger->error( "Unable to find the queue: $name.", [ __CLASS__ ] );
            return false;
        }

        // Fetch new entries.
        try {
            // Run hooks before the entries are fetched.
            do_action( 'wpq_before_enqueue', $queue, $entry );
            do_action( 'wpq_before_enqueue_' . $name, $queue, $entry );

            $queue->enqueue( $entry );

            // Run hooks after the entries are added to the queue.
            do_action( 'wpq_after_enqueue', $queue, $entry );
            do_action( 'wpq_after_enqueue_' . $name, $queue, $entry );

            return true;
        }
        catch ( \Exception $error ) {
            if ( $this->logger ) {
                $this->logger->error(
                    "An error occurred while enqueueing an entry to queue: $name.",
                    [
                        // Use this filter to sanitize sensitive data.
                        'data'    => apply_filters( 'wpq_entry_data_log', $entry->get_data() ),
                        'message' => $error->getMessage(),
                        'file'    => $error->getFile(),
                        'line'    => $error->getLine(),
                    ]
                );
            }

            return false;
        }
    }
}
