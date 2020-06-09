<?php
/**
 * Abstract implementation of a WP CLI command for
 * dequeueing a single entry from a queue.
 */

namespace Geniem\ImportController\CLI;

use WP_CLI;
use Geniem\ImportController\Dequeuer;
use Geniem\ImportController\Interfaces\QueueInterface;
use Psr\Log\LoggerInterface;

/**
 * RediPress CLI index command class.
 */
class DequeueCommand implements Command {

    /**
     * The command itself.
     *
     * @param array $args       The command parameters.
     * @param array $assoc_args The optional command parameters.
     * @return boolean
     */
    public function run( array $args = [], array $assoc_args = [] ) : bool {
        $queue_type = $args[0] ?? null;
        $queue_name = $args[1] ?? null;

        if ( empty( $queue_type ) ) {
            WP_CLI::error( 'Please define the queue type as the first command argument.' );
            return false;
        }

        if ( empty( $queue_name ) ) {
            WP_CLI::error( 'Please define the queue name as the second command argument.' );
            return false;
        }

        // Default the queue and logger values to null.
        add_filter( 'gic_get_queue_' . $queue_type, '__return_null', 1, 0 );
        add_filter( 'gic_get_logger_' . $queue_type, '__return_null', 1, 0 );

        /**
         * Fetch the queue by its type.
         *
         * @var QueueInterface
         */
        $queue = apply_filters( 'gic_get_queue_' . $queue_type, $queue_name );

        /**
         * Fetch a logger for the dequeuer.
         *
         * @var LoggerInterface
         */
        $logger = apply_filters( 'gic_get_logger_' . $queue_type, $queue_name );

        if ( ! $queue instanceof QueueInterface ) {
            WP_CLI::error( 'No queue found for name: ' . $queue_name . '.' );
            return false;
        }

        $dequeuer = new Dequeuer( $logger );

        $success = $dequeuer->dequeue( $queue );

        if ( $success ) {
            WP_CLI::success( 'Dequeue executed successfully!' );
        }
        else {
            WP_CLI::error(
                'An error occurred while executing the dequeue!
                See the dequeuer and/or queue log for detailed information.'
            );
        }

        return $success;
    }
}
