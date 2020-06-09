<?php
/**
 * WP-CLI command implementations.
 */

namespace Geniem\ImportController\CLI;

use Exception;
use WP_CLI;
use Geniem\ImportController\Dequeuer;
use Geniem\ImportController\Interfaces\QueueInterface;
use Geniem\ImportController\QueueCreator;
use Psr\Log\LoggerInterface;

/**
 * Run Geniem Import Controller commands.
 *
 * ## EXAMPLES
 *
 *     # Create a queue with the RedisCache queue with the name 'my_queue'.
 *     $ wp gci create redis_cache my_queue
 *     Success: The queue "my_queue" was created successfully!
 *
 *     # Dequeue a single entry from a RedisCache queue with the name 'my_queue'.
 *     $ wp gci dequeue redis_cache my_queue
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
     * <type>
     * : The queue type. The type is appended to 'gic_get_queue_{type}' filter to fetch the correct queue instance.
     *
     * <name>
     * : The queue name. The name is passed as the first argument for the 'gic_get_queue_{type}' filter to be passed for the queue constructor.
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
        add_filter( 'gic_get_queue_' . $queue_type, '__return_null', 0, 0 );

        /**
         * Fetch the queue by its type and by name.
         *
         * @var QueueInterface
         */
        $queue = apply_filters( 'gic_get_queue_' . $queue_type, $queue_name );

        if ( ! $queue instanceof QueueInterface ) {
            WP_CLI::error( 'No queue found for type "' . $queue_type . '".' );
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
            $queue_creator = new QueueCreator( $queue, $entry_fetcher, $entry_handler );
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
     * <type>
     * : The queue type. The type is appended to 'gic_get_queue_{type}' filter to fetch the correct queue instance.
     *
     * <name>
     * : The queue name. The name is passed as the first argument for the 'gic_get_queue_{type}' filter to be passed for the queue constructor.
     *
     * [<logger>]
     * : The optional dequeue logger type. The type is appended to 'gic_get_dequeue_logger_' filter to fetch the correct logger instance. Note, set the logger for the queue before returning the instance on the 'gic_get_queue_{type}' filter. // phpcs:ignore
     *
     * ## EXAMPLES
     *
     *     # Dequeue a single entry from a RedisCache queue with the name 'my_queue'.
     *     $ wp gci dequeue redis_cache my_queue
     *     Success: Dequeue for "my_queue" was executed successfully!
     *
     *     # Use a custom logger for the dequeuer. Return your PSR-3 logger instance with the 'gic_get_logger_my_logger' filter.
     *     $ wp gci dequeue redis_cache my_queue my_logger
     *     Success: Dequeue for "my_queue" was executed successfully!
     *
     * phpcs:enable
     *
     * @param array $args The command parameters.
     * @return boolean
     */
    public function dequeue( array $args = [] ) : bool {
        $queue_type   = $args[0] ?? null;
        $queue_name   = $args[1] ?? null;
        $queue_logger = $args[2] ?? null;

        if ( empty( $queue_type ) ) {
            WP_CLI::error( 'Please define the queue type as the first command argument.' );
            return false;
        }

        if ( empty( $queue_name ) ) {
            WP_CLI::error( 'Please define the queue name as the second command argument.' );
            return false;
        }

        // Default the queue and logger values to null.
        add_filter( 'gic_get_queue_' . $queue_type, '__return_null', 0, 0 );
        add_filter( 'gic_get_dequeue_logger_' . $queue_logger, '__return_null', 0, 0 );

        /**
         * Fetch the queue by its type and by name.
         *
         * @var QueueInterface
         */
        $queue = apply_filters( 'gic_get_queue_' . $queue_type, $queue_name );

        /**
         * Fetch a logger for the dequeuer.
         *
         * @var LoggerInterface
         */
        $logger = apply_filters( 'gic_get_dequeue_logger_' . $queue_logger, $queue_name );

        if ( ! $queue instanceof QueueInterface ) {
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
