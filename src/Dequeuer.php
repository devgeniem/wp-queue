<?php
/**
 * The dequeue controller.
 */

namespace Geniem\ImportController;

use Psr\Log\LoggerInterface;
use Geniem\ImportController\Interfaces\QueueInterface;

/**
 * Class DequeuController
 *
 * @package Geniem\ImportController
 */
class Dequeuer {

    /**
     * The callback for dequeueing queues.
     *
     * @param string      $name   The queue name.
     * @param Logger|null $logger An optional PSR-3 compatible logger instance.
     *
     * @return QueueInterface|null The queue instance or null on failure.
     */
    public function dequeue( QueueInterface $queue, ?LoggerInterface $logger = null ) : ?QueueInterface {
        if ( ! $queue instanceof QueueInterface ) {
            $logger->error( 'Unable to dequeue. The queue is not of type: ' . QueueInterface::class . '.', [ __CLASS__ ] );
            return null;
        }

        $name = $queue->get_name();

        if ( $logger ) {
            $queue->set_logger( $logger );
        }

        if ( ! $queue->exists() ) {
            $logger->error( "Unable to find the queue: $name.", [ __CLASS__ ] );
            return null;
        }

        // Run the first entry.
        try {
            $queue->dequeue();

            // A hook to run after the dequeue is done.
            do_action( 'geniem_import_controller_after_dequeue', $queue );

            return $queue;
        }
        catch ( \Exception $error ) {
            if ( $logger ) {
                $logger->error( "An error occurred while dequeueing from queue: $name.", [
                    'message' => $error->getMessage(),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                ] );
            }

            return null;
        }
    }
}
