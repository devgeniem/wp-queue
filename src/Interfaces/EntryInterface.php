<?php
/**
 * Defines the entry object logic.
 */

namespace Geniem\Queue\Interfaces;

/**
 * Use this interface to define an entry.
 */
interface EntryInterface {

    /**
     * Setter for the data.
     *
     * @param mixed $data The data.
     * @return self
     */
    public function set_data( $data ) : EntryInterface;

    /**
     * Getter for the data.
     *
     * @return mixed
     */
    public function get_data();

}
