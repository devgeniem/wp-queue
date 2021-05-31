<?php
/**
 * WP-CLI command implementations.
 */

namespace Geniem\Queue\CLI;

use Exception;
use Psr\Container\ContainerInterface;
use WP_CLI;
use Geniem\Queue\Dequeuer;
use Geniem\Queue\Enqueuer;
use Geniem\Queue\Interfaces\QueueInterface;
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
     * Holds the queue container.
     *
     * @var ContainerInterface
     */
    protected $queue_container;

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Commands constructor.
     */
    public function __construct() {
        $plugin = wpq();

        // Get and set the queue container.
        $this->queue_container = $plugin->get_queue_container();
        // Get the plugin logger.
        $this->logger = $plugin->get_logger();
    }

    /**
     * Create a queue.
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
     *     # Create a queue with the name 'my_queue'.
     *     $ wp queue create my_queue
     *     Success: Dequeue executed successfully!
     *
     * phpcs:enable
     *
     * @param array $args The command parameters.
     * @return boolean
     */
    public function create( array $args = [] ) : bool {
        $queue_name = $args[0] ?? null;

        if ( empty( $queue_name ) ) {
            WP_CLI::error( 'Please define the queue name as the first command argument.' );
            return false;
        }

        /**
         * Fetch a queue by name.
         *
         * @var QueueInterface $queue
         */
        $queue = $this->queue_container->get( $queue_name );

        if ( ! $queue instanceof QueueInterface ) {
            WP_CLI::error( "No queue found with the name \"$queue_name\"." );
            return false;
        }

        $entry_handler = $queue->get_entry_handler();

        if ( empty( $entry_handler ) ) {
            WP_CLI::error(
                'The queue must contain an entry handler before creating the queue.'
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
            WP_CLI::success( "The queue \"$queue_name\" was created successfully!" );
            return true;
        }
        catch ( Exception $err ) {
            WP_CLI::error( $err->getMessage() );
            return false;
        }
    }

    /**
     * Delete a queue. All entries are cleared and then the queue data is deleted.
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
     *     # Clear entries and delete all queue data from a queue with the name 'my_queue'.
     *     $ wp queue delete my_queue
     *     Success: The queue was deleted succesfully!
     *
     * phpcs:enable
     *
     * @param array $args The command parameters.
     * @return boolean
     */
    public function delete( array $args = [] ) : bool {
        $queue_name = $args[0] ?? null;

        if ( empty( $queue_name ) ) {
            WP_CLI::error( 'Please define the queue name as the first command argument.' );
            return false;
        }

        /**
         * Fetch a queue by name.
         *
         * @var QueueInterface $queue
         */
        $queue = apply_filters( "wpq_get_queue_$queue_name", null );

        if ( ! $queue instanceof QueueInterface ) {
            WP_CLI::error( "No queue found with the name \"$queue_name\"." );
            return false;
        }

        try {
            $queue->delete();
            WP_CLI::success( "The queue \"$queue_name\" was deleted successfully!" );
            return true;
        }
        catch ( Exception $err ) {
            WP_CLI::error( 'An error occurred while deleting the queue: ' . $err->getMessage() );
            return false;
        }
    }

    /**
     * Check the number of entries in a queue.
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
     *     # Check the size of a queue with the name 'my_queue'.
     *     $ wp queue size my_queue
     *     Success: There are 5 entries in the queue.
     *
     * phpcs:enable
     *
     * @param array $args The command parameters.
     * @return boolean
     */
    public function size( array $args = [] ) : bool {
        $queue_name = $args[0] ?? null;

        if ( empty( $queue_name ) ) {
            WP_CLI::error( 'Please define the queue name as the first command argument.' );
            return false;
        }

        /**
         * Fetch a queue by name.
         *
         * @var QueueInterface $queue
         */
        $queue = apply_filters( "wpq_get_queue_$queue_name", null );

        if ( ! $queue instanceof QueueInterface ) {
            WP_CLI::error( "No queue found with the name \"$queue_name\"." );
            return false;
        }

        try {
            $size_text = sprintf(
                ngettext( 'is %d entry', 'are %d entries', $queue->size(), ),
                $queue->size()
            );

            WP_CLI::success( "There $size_text in the queue." );
            return true;
        }
        catch ( Exception $err ) {
            WP_CLI::error( 'An error occurred while getting the queue size: ' . $err->getMessage() );
            return false;
        }
    }

    /**
     * Fetch new entries and add them to the queue.
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
     *     # Fetch and enqueue new entries to the queue by the name 'my_queue'.
     *     $ wp queue fetch my_queue
     *     Success: "Entries for queue "my_queue" were enqueued successfully!"
     *
     * phpcs:enable
     *
     * @param array $args The command parameters.
     * @return boolean
     */
    public function fetch( array $args = [] ) : bool {
        $queue_name = $args[0] ?? null;

        if ( empty( $queue_name ) ) {
            WP_CLI::error( 'Please define the queue name as the second command argument.' );
            return false;
        }

        // Default the queue value to null.
        $queue = null;

        /**
         * Fetch a queue by name.
         *
         * @var QueueInterface $queue
         */
        $queue = $this->queue_container->get( $queue_name );

        if ( ! $queue instanceof QueueInterface ) {
            WP_CLI::error( "No queue found with the name \"$queue_name\"." );
            return false;
        }

        try {
            $enqueuer = new Enqueuer( $this->logger );
        }
        catch ( Exception $err ) {
            WP_CLI::error( $err->getMessage() );
            return false;
        }

        try {
            $enqueuer->enqueue( $queue );
            WP_CLI::success( "Entries for queue \"$queue_name\" were enqueued successfully!" );
            return true;
        }
        catch ( Exception $err ) {
            WP_CLI::error( $err->getMessage() );
            return false;
        }
    }

    /**
     * Dequeue a single entry from a queue.
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
     *     # Dequeue a single entry from a queue with the name 'my_queue'.
     *     $ wp queue dequeue my_queue
     *     Success: Dequeue for "my_queue" was executed successfully!
     *
     * phpcs:enable
     *
     * @param array $args The command parameters.
     * @return boolean
     */
    public function dequeue( array $args = [] ) : bool {
        $queue_name = $args[0] ?? null;

        if ( empty( $queue_name ) ) {
            WP_CLI::error( 'Please define the queue name as the second command argument.' );
            return false;
        }

        // Default the queue value to null.
        $queue = null;

        /**
         * Fetch a queue by name.
         *
         * @var QueueInterface $queue
         */
        $queue = $this->queue_container->get( $queue_name );

        if ( ! $queue instanceof QueueInterface ) {
            WP_CLI::error( "No queue found with the name \"$queue_name\"." );
            return false;
        }

        try {
            $dequeuer = new Dequeuer( $this->logger );
        }
        catch ( Exception $err ) {
            WP_CLI::error( $err->getMessage() );
            return false;
        }

        $success = $dequeuer->dequeue( $queue );

        if ( $success ) {
            WP_CLI::success( "Dequeue for the queue \"$queue_name\" was executed successfully!" );
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
