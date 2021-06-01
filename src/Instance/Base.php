<?php
/**
 * The abstract base class for all queue classes.
 * This class groups basic method implementations.
 */

namespace Geniem\Queue\Instance;

use Psr\Log\LoggerInterface;
use Geniem\Queue\Interfaces\EntryInterface;
use Geniem\Queue\Interfaces\FetchableInterface;
use Geniem\Queue\Interfaces\HandleableInterface;
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
     * @var FetchableInterface|null
     */
    protected $entry_fetcher;

    /**
     * The entry handler instance.
     *
     * @var HandleableInterface|null
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
     * @return HandleableInterface|null
     */
    public function get_entry_handler() : ?HandleableInterface {
        return $this->entry_handler;
    }

    /**
     * Get the entry fetcher.
     *
     * @return FetchableInterface|null
     */
    public function get_entry_fetcher() : ?FetchableInterface {
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
     * @param HandleableInterface $entry_handler The callable to handle the single entry.
     */
    public function set_entry_handler( ?HandleableInterface $entry_handler ) {
        $this->entry_handler = $entry_handler;

        return $this;
    }

    /**
     * Set the entry fetcher.
     *
     * @param FetchableInterface $entry_fetcher The callable to handle the single entry.
     */
    public function set_entry_fetcher( ?FetchableInterface $entry_fetcher ) {
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
