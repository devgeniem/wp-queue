<?php
/**
 * The import controller queue entry class.
 */

namespace Geniem\Queue;

use Geniem\Queue\Interfaces\EntryInterface;

/**
 * The Entry class.
 *
 * @package Geniem\Queue
 */
class Entry implements EntryInterface {

    /**
     * Entry data.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Setter for the data.
     *
     * @param mixed $data The data.
     * @return self
     */
    public function set_data( $data ) : EntryInterface {
        $this->data = $data;

        return $this;
    }

    /**
     * Getter for the data.
     *
     * @return mixed
     */
    public function get_data() {
        return $this->data;
    }
}
