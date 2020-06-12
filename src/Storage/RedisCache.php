<?php
/**
 * The Redis Cache implementation of an import queue.
 */

namespace Geniem\Queue\Storage;

use Geniem\Queue\Interfaces\EntryFetcherInterface;
use Geniem\Queue\Logger;
use Psr\Log\LoggerInterface;
use Geniem\Queue\Interfaces\EntryHandlerInterface;
use Geniem\Queue\Interfaces\EntryInterface;

/**
 * Class RedisCache
 *
 * @package Geniem\Queue
 */
class RedisCache extends Base {

    /**
     * This hook name is used to add a WP Cron event for a queue.
     */
    const QUEUE_CRON_HOOK = 'geniem_import_queue';

    /**
     * The option name prefix.
     */
    const QUEUE_PREFIX = 'geniem_import_queue_';

    /**
     * Defines if the queue exists in Redis or not.
     *
     * @var boolean
     */
    protected $exists = false;

    /**
     * Unique queue name.
     *
     * @var string
     */
    protected $name;

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Queues will be stored through the Redis instance.
     *
     * @var \Redis
     */
    protected $redis;

    /**
     * Add the Redis prefix to a queue name.
     *
     * @param string $name The queue name.
     *
     * @return string
     */
    protected function prefix_name( string $name ) : string {
        return static::QUEUE_PREFIX . $name;
    }

    /**
     * Queue constructor.
     *
     * @param string                $name    A unique name for the queue.
     * @param EntryFetcherInterface $fetcher The entry fetcher instance.
     * @param EntryHandlerInterface $handler The entry handler instance.
     */
    public function __construct( string $name, EntryFetcherInterface $fetcher, EntryHandlerInterface $handler ) {
        global $wp_object_cache;

        $this->name          = $name;
        $this->redis         = $wp_object_cache->redis_instance();
        $this->entry_fetcher = $fetcher;
        $this->entry_handler = $handler;

        // Set the default logger.
        $this->logger = new Logger();
    }

    /**
     * Loads the entry handler from Redis.
     *
     * @return void
     */
    protected function load_entry_handler() {
        // Try to load entry handler from storage.
        try {
            if ( empty( $this->entry_handler ) && $this->exists() ) {
                $raw                 = $this->redis->get( $this->get_storage_key() );
                $object              = maybe_unserialize( $raw );
                $this->entry_handler = $object->entry_handler ?? null;
            }
        }
        catch ( \Exception $e ) {
            $this->logger->error(
                'RedisCacheQueue - An error occurred while loading the entry handler.',
                [
                    'name'    => $this->name,
                    'message' => $e->getMessage(),
                    'file'    => $e->getLine(),
                    'line'    => $e->getLine(),
                ]
            );
        }
    }

    /**
     * Get the storage key for the queue.
     *
     * @return string
     */
    public function get_storage_key() : string {
        return $this->prefix_name( $this->name );
    }

    /**
     * Get the storage key for the queue entries list.
     *
     * @return string
     */
    protected function get_entries_key() : string {
        return $this->prefix_name( $this->name ) . '_entries';
    }

    /**
     * Get the queue lock key.
     *
     * @return string
     */
    protected function get_lock_key() : string {
        return 'lock:' . $this->get_storage_key();
    }

    /**
     * Save the queue. Rewrites all entries.
     */
    public function save() {
        // Save entries first.
        $entries = $this->entries;
        $key     = $this->get_entries_key();
        try {
            // Delete old list first by trimming off elements in batches of 100.
            while ( $this->redis->llen( $key ) > 0 ) {
                $this->redis->ltrim( $key, 0, -99 );
            }

            // Push all serialized entries into the empty list.
            $this->redis->lPush( $key, ...array_map( 'maybe_serialize', array_values( $entries ) ) );
        }
        catch ( \Exception $e ) {
            $this->logger->info(
                'RedisCacheQueue - Unable to save the entries. Deleting it. Error: ' . $e->getMessage()
            );
            $this->delete();
            return;
        }

        // Entries should not be stored with the object.
        $this->entries = null;

        // Store this queue for a month.
        try {
            $success = $this->redis->setex( $this->get_storage_key(), MONTH_IN_SECONDS, serialize( $this ) ); // phpcs:ignore

            // If unable to save the queue, delete it to prevent jamming any processes.
            if ( ! $success ) {
                $this->logger->error( 'RedisCacheQueue - Unable to save the queue. "setex" failed. Deleting queue..' );
                $this->delete();
                return;
            }
            else {
                $this->logger->info( 'RedisCacheQueue - "' . $this->name . '" saved!' );
            }
        }
        catch ( \Exception $e ) {
            $this->logger->error(
                'RedisCacheQueue - Unable to save the queue. Deleting it. Error: ' . $e->getMessage()
            );
            $this->delete();
            return;
        }

        // Put back entries.
        $this->entries = $entries;
    }

    /**
     * Delete the queue.
     *
     * @return bool
     */
    public function delete() : bool {
        try {
            // Expire lock.
            $this->redis->pExpire( $this->get_lock_key(), 1 );

            // Expire basic data.
            $this->redis->pExpire( $this->get_storage_key(), 1 );

            $key = $this->get_entries_key();

            // Delete entry list first by trimming off elements in batches of 100.
            while ( $this->redis->llen( $key ) > 0 ) {
                $this->redis->ltrim( $key, 0, -99 );
            }

            $this->exists = false;

            return true;
        }
        catch ( \Exception $e ) {
            $this->logger->error( 'RedisCacheQueue - Unable to delete queue. Error: ' . $e->getMessage() );

            return false;
        }
    }

    /**
     * Check if this queue exists.
     */
    public function exists() : bool {
        if ( $this->exists ) {
            return true;
        }
        try {
            $this->exists = $this->redis->exists( $this->get_storage_key() ) === 1;

            return $this->exists;
        }
        catch ( \Exception $e ) {
            $this->logger->error( 'RedisCacheQueue - Unable to check existence. Error: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Check whether the queue is empty.
     *
     * @return bool
     */
    public function is_empty() : bool {
        return $this->get_count() === 0;
    }

    /**
     * Checks the number of entries in the queue.
     *
     * @return bool
     */
    public function get_count() : int {
        try {
            return intval( $this->redis->llen( $this->get_entries_key() ) );
        }
        catch ( \Exception $e ) {
            $this->logger->error( 'RedisCacheQueue - Unable to read queue length. Error: ' . $e->getMessage() );

            return 0;
        }
    }

    /**
     * Run an event from the queue and store the rest.
     *
     * @return mixed Returns the popped entry or null on failure.
     * @throws \Exception An exception is thrown if the entry handler is not a callable.
     */
    public function dequeue() {
        $this->logger->info( 'RedisCacheQueue - Dequeueing event from queue: ' . $this->name );

        $lock_key = $this->get_lock_key();
        $lock_set = false;

        try {
            $this->load_entry_handler();

            // Do nothing if the handler is not the correct type.
            if ( ! $this->entry_handler instanceof EntryHandlerInterface ) {
                throw new \Exception( 'RedisCacheQueue - The entry handler is the wrong type.' );
            }

            // Try to set a lock. If this returns true, the queue was successfully locked.
            $lock_set = $this->redis->setnx( $lock_key, 1 ) === true;

            if ( ! $lock_set ) {
                $this->logger->info(
                    'RedisCacheQueue - Stopping a dequeue process. The queue is locked.',
                    [ $this->name ]
                );

                return null;
            }
            else {
                // Do not lock for eternity.
                $lock_ttl = apply_filters( 'wordpress_queue_redis_cache_lock_ttl', 5 * MINUTE_IN_SECONDS );
                $this->redis->expire( $lock_key, $lock_ttl );
            }

            $raw_entry = $this->redis->lIndex( $this->get_entries_key(), 0 );
            $entry     = maybe_unserialize( $raw_entry );

            $this->entry_handler->handle( $entry );

            // Handling was successful. Pop the entry out.
            $this->redis->lPop( $this->get_entries_key() );

            if ( $this->is_empty() ) {
                $this->logger->info( 'RedisCacheQueue - The queue is finished.', [ $this->name ] );
            }
            else {
                $this->logger->info( 'RedisCacheQueue - Dequeued.', [ $this->name ] );
            }
        }
        catch ( \Exception $e ) {
            $this->logger->error(
                'RedisCacheQueue - An error occurred while dequeueing.',
                [
                    'name'    => $this->name,
                    'message' => $e->getMessage(),
                    'file'    => $e->getLine(),
                    'line'    => $e->getLine(),
                ]
            );

            $entry = null;
        }

        if ( $lock_set ) {
            try {
                // Expire the lock after 1ms.
                // This is more optimized than deleting a key.
                $this->redis->pExpire( $lock_key, 1 );
            }
            catch ( \Exception $e ) {
                $this->logger->error(
                    'RedisCacheQueue - An error occurred while deleting the lock.',
                    [
                        'name'    => $this->name,
                        'message' => $e->getMessage(),
                        'file'    => $e->getLine(),
                        'line'    => $e->getLine(),
                    ]
                );

                $entry = null;
            }
        }

        return $entry;
    }

    /**
     * Adds an entry at the and of the queue.
     *
     * @param EntryInterface $entry The entry.
     */
    public function enqueue( EntryInterface $entry ) {
        try {
            $name   = $this->get_name();
            $length = $this->redis->rPush( $this->get_entries_key(), maybe_serialize( $entry ) );
            $this->logger->info( "RedisCacheQueue - Enqueued a new entry into queue: \"$name\". Length: $length." );
        }
        catch ( \Exception $err ) {
            $message = $err->getMessage();
            $this->logger->error(
                "RedisCacheQueue - Unable the enqueue a new entry into queue: \"$name\". Error: $message",
                $err->getTrace()
            );
            return null;
        }
    }
}
