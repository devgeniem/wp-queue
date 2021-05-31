<?php
/**
 * The enqueue controller.
 */

namespace Geniem\Queue;

use Geniem\Queue\Interfaces\EntryInterface;
use Psr\Log\LoggerInterface;
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
        $this->logger = $logger ?? new Logger();
    }

    /**
     * Fetch new entries and add them to the queue.
     *
     * @param QueueInterface $queue The queue name.
     *
     * @return int Number of entries enqueued. Negative integer on error.
     */
    public function enqueue( QueueInterface $queue ) : int {
        if ( ! $queue instanceof QueueInterface ) {
            $this->logger->error(
                'Unable to enqueue. The queue is not of type: ' . QueueInterface::class . '.',
                [ __CLASS__ ]
            );
            return false;
        }

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
                array_walk(
                    $entries,
                    function( EntryInterface $entry ) use ( $queue ) {
                        $queue->enqueue( $entry );
                    }
                );
            }

            // Run hooks after the entries are added to the queue.
            do_action( 'wpq_after_enqueue', $queue, $entries );
            do_action( 'wpq_after_enqueue_' . $name, $queue, $entries );

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
}
