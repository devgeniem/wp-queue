<?php
/**
 * A mock entry fetcher.
 */

namespace Geniem\Queue\Mock;

use Geniem\Queue\Entry;

/**
 * Class MockEntryFetcher
 *
 * @package Geniem\Queue\Mock
 */
class MockEntryFetcher implements \Geniem\Queue\Interfaces\FetchableInterface {

    /**
     * The mock data.
     *
     * @var array
     */
    protected $data = [];

    public function __construct( $data ) {
        $this->data = $data;
    }

    /**
     * A setter for the mock data.
     *
     * @param array $data The mock data.
     */
    public function set_data( $data ) {
        $this->data = $data;
    }

    /**
     * Fetch the mock data.
     *
     * @return array|null
     */
    public function fetch() : ?array {
        return $this->data;
    }
}
