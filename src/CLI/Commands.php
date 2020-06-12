<?php
/**
 * WP-CLI command implementations.
 */

namespace Geniem\Queue\CLI;

use Exception;
use WP_CLI;
use Geniem\Queue\Dequeuer;
use Geniem\Queue\Interfaces\StorageInterface;
use Geniem\Queue\Logger;
use Geniem\Queue\QueueCreator;
use Psr\Log\LoggerInterface;

/**
 * Run WordPress Queue commands.
 *
 * ## EXAMPLES
 *
 *     # Create a queue with the name 'my_queue'.
 *     $ wp queue create my_queue
 *     Success: The queue "my_queue" was created successfully!
 *
 *     # Dequeue a single entry from a queue with the name 'my_queue'.
 *     $ wp queue dequeue my_queue
 *     Success: Dequeue for "my_queue" was executed successfully!
 */
class Commands {

    /**
     * Create an import queue.
     *
     * phpcs:disable
     *
     * ## OPTIONS
     *
     * <name>
     * : The queue name. The name is passed as the first argument for the 'wpq_get_queue_{name}' filter to be passed for the queue constructor.
     *
     * ## EXAMPLES
     *
     *     # Create a queue with the RedisCache queue with the name 'my_queue'.
     *     $ wp gci create redis_cache my_queue
     *     Success: Dequeue executed successfully!
     *
     * phpcs:enable
     *
     * @param array $args       The command parameters.
     * @return boolean
     */
    public function create( array $args = [] ) : bool {
        $queue_name = $args[1] ?? null;

        if ( empty( $queue_name ) ) {
            WP_CLI::error( 'Please define the queue name as the second command argument.' );
            return false;
        }

        /**
         * Fetch a queue by name.
         *
         * @var StorageInterface
         */
        $queue = apply_filters( "wpq_get_queue_$queue_name", null );

        if ( ! $queue instanceof StorageInterface ) {
            WP_CLI::error( 'No queue found with name "' . $queue_name . '".' );
            return false;
        }

        $entry_handler = $queue->get_entry_handler();
        $entry_fetcher = $queue->get_entry_fetcher();

        if ( $entry_handler || $entry_fetcher ) {
            WP_CLI::error(
                'The queue must have both the entry handler and the entry fetcher set before creating the queue.'
            );
            return false;
        }

        try {
            $queue_creator = new QueueCreator( $queue );
        }
        catch ( Exception $err ) {
            WP_CLI::error( $err->getMessage() );
            return false;
        }

        try {
            $queue_creator->create();
            WP_CLI::success( 'The queue "' . $queue_name . '" was created successfully!' );
            return true;
        }
        catch ( Exception $err ) {
            WP_CLI::error( $err->getMessage() );
            return false;
        }
    }

    /**
     * Dequeues a single entry from a queue.
     *
     * phpcs:disable
     *
     * ## OPTIONS
     *
     * <name>
     * : The queue name. The name is passed as the first argument for the 'wpq_get_queue_{name}' filter to be passed for the queue constructor.
     *
     * [<logger>]
     * : The optional dequeue logger type. The type is appended to 'wpq_get_dequeue_logger_{logger}' filter to fetch the correct logger instance. Note, set the logger for the queue before returning the instance on the 'wpq_get_queue_{type}' filter. // phpcs:ignore
     *
     * ## EXAMPLES
     *
     *     # Dequeue a single entry from a RedisCache queue with the name 'my_queue'.
     *     $ wp gci dequeue redis_cache my_queue
     *     Success: Dequeue for "my_queue" was executed successfully!
     *
     *     # Use a custom logger for the dequeuer. Return your PSR-3 logger instance with the 'wpq_get_logger_my_logger' filter.
     *     $ wp gci dequeue redis_cache my_queue my_logger
     *     Success: Dequeue for "my_queue" was executed successfully!
     *
     * phpcs:enable
     *
     * @param array $args The command parameters.
     * @return boolean
     */
    public function dequeue( array $args = [] ) : bool {
        $queue_name  = $args[0] ?? null;
        $logger_name = $args[1] ?? new Logger();

        if ( empty( $queue_type ) ) {
            WP_CLI::error( 'Please define the queue type as the first command argument.' );
            return false;
        }

        if ( empty( $queue_name ) ) {
            WP_CLI::error( 'Please define the queue name as the second command argument.' );
            return false;
        }

        // Default the queue and logger values to null.
        add_filter( "wpq_get_dequeue_logger_$logger_name", '__return_null', 0, 0 );

        /**
         * Fetch a queue by name.
         *
         * @var StorageInterface
         */
        $queue = apply_filters( 'wpq_get_queue_' . $queue_name, null );

        /**
         * Replace the logger with the global filter.
         *
         * The logger defaults to an instance of the \Geniem\Queue\Logger.
         *
         * @var LoggerInterface
         */
        $queue_logger = apply_filters( 'wpq_get_dequeue_logger', $queue_logger );

        /**
         * Fetch a logger for the dequeuer.
         *
         * The logger defaults to an instance of the \Geniem\Queue\Logger.
         *
         * @var LoggerInterface
         */
        $logger = apply_filters( 'wpq_get_dequeue_logger_' . $logger_name, $queue_logger, $queue_name );

        if ( ! $queue instanceof StorageInterface ) {
            WP_CLI::error( 'No queue found for type "' . $queue_type . '".' );
            return false;
        }

        try {
            $dequeuer = new Dequeuer( $logger );
        }
        catch ( Exception $err ) {
            WP_CLI::error( $err->getMessage() );
            return false;
        }

        $success = $dequeuer->dequeue( $queue );

        if ( $success ) {
            WP_CLI::success( 'Dequeue for "' . $queue_name . '" was executed successfully!' );
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
