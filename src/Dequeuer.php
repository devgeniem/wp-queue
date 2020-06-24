<?php
/**
 * The dequeue controller.
 */

namespace Geniem\Queue;

use Psr\Log\LoggerInterface;
use Geniem\Queue\Interfaces\StorageInterface;

/**
 * Class Dequeuer
 *
 * @package Geniem\Queue
 */
class Dequeuer {

    /**
     * The dequeue logger.
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
     * Dequeues an entry from a queue.
     *
     * @param StorageInterface $queue  The queue name.
     *
     * @return bool True for success, false on failure.
     */
    public function dequeue( StorageInterface $queue ) {
        if ( ! $queue instanceof StorageInterface ) {
            $this->logger->error(
                'Unable to dequeue. The queue is not of type: ' . StorageInterface::class . '.',
                [ __CLASS__ ]
            );
            return false;
        }

        $name = $queue->get_name();

        if ( ! $queue->exists() ) {
            $this->logger->error( "Unable to find the queue: $name.", [ __CLASS__ ] );
            return false;
        }

        // Run the first entry.
        try {
            // Run hooks before the dequeue is executed.
            do_action( 'wpq_before_dequeue', $queue );
            do_action( 'wpq_before_dequeue_' . $name, $queue );

            $queue->dequeue();

            // Run hooks after the dequeue is done.
            do_action( 'wpq_after_dequeue', $queue );
            do_action( 'wpq_after_dequeue_' . $name, $queue );

            return true;
        }
        catch ( \Exception $error ) {
            if ( $this->logger ) {
                $this->logger->error(
                    "An error occurred while dequeueing from queue: $name.",
                    [
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
