<?php
/**
 * The abstract base class for all queue classes.
 * This class groups basic method implementations.
 */

namespace Geniem\Queue\Instance;

use Psr\Log\LoggerInterface;
use Geniem\Queue\Interfaces\EntryInterface;
use Geniem\Queue\Interfaces\EntryFetcherInterface;
use Geniem\Queue\Interfaces\EntryHandlerInterface;
use Geniem\Queue\Interfaces\QueueInterface;

/**
 * Base class for queues.
 */
abstract class Base implements QueueInterface {

    /**
     * The unique name.
     *
     * @var string
     */
    protected $name;

    /**
     * The entry fetcher instance.
     *
     * @var EntryFetcherInterface|null
     */
    protected $entry_fetcher;

    /**
     * The entry handler instance.
     *
     * @var EntryHandlerInterface|null
     */
    protected $entry_handler;

    /**
     * The queue entry data array.
     *
     * @var EntryInterface[]|null
     */
    protected $entries;

    /**
     * The logger.
     *
     * @var Logger|null
     */
    protected $logger;

    /**
     * Get the name.
     *
     * @return string|null
     */
    public function get_name() : ?string {
        return $this->name;
    }

    /**
     * Get the entry handler.
     *
     * @return EntryHandlerInterface|null
     */
    public function get_entry_handler() : ?EntryHandlerInterface {
        return $this->entry_handler;
    }

    /**
     * Get the entry fetcher.
     *
     * @return EntryFetcherInterface|null
     */
    public function get_entry_fetcher() : ?EntryFetcherInterface {
        return $this->entry_fetcher;
    }

    /**
     * Get the entries.
     *
     * @return array|null
     */
    public function get_entries() : ?array {
        return $this->entries;
    }

    /**
     * Get the logger.
     *
     * @return LoggerInterface|null
     */
    public function get_logger() : ?LoggerInterface {
        return $this->logger;
    }

    /**
     * Set the entry handler.
     *
     * @param EntryHandlerInterface|null $entry_handler The callable to handle the single entry.
     *
     * @return Base
     */
    public function set_entry_handler( ?EntryHandlerInterface $entry_handler ) {
        $this->entry_handler = $entry_handler;

        return $this;
    }

    /**
     * Set the entry fetcher.
     *
     * @param EntryFetcherInterface|null $entry_fetcher The callable to handle the single entry.
     *
     * @return Base
     */
    public function set_entry_fetcher( ?EntryFetcherInterface $entry_fetcher ) {
        $this->entry_fetcher = $entry_fetcher;

        return $this;
    }

    /**
     * Set the entries.
     * Passed items will be wrapped into entries.
     *
     * @param EntryInterface[]|array $entries Array of entries or other items.
     */
    public function set_entries( ?array $entries ) {
        $this->entries = wpq_wrap_items_to_entries( $entries );
    }

    /**
     * Setter for the logger.
     *
     * @param LoggerInterface $logger A PSR-3 compatible logger instance.
     */
    public function set_logger( LoggerInterface $logger ) {
        $this->logger = $logger;
    }
}
