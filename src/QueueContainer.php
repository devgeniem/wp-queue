<?php

namespace Geniem\Queue;

use Geniem\Queue\Interfaces\QueueInterface;
use Geniem\Queue\Exception\QueueNotFoundException;
use Psr\Container\ContainerInterface;

class QueueContainer implements ContainerInterface {

    /**
     * An assoc array holding the queues.
     *
     * @var QueueInterface[]
     */
    protected $queues = [];

    /**
     * Adds a queue if one with the id does not exist already.
     * The name of the queue will be used as the id.
     *
     * @param QueueInterface $queue The queue instance.
     */
    public function add( QueueInterface $queue ) {
        if ( ! isset( $this->queues[ $queue->get_name() ] ) ) {
            $this->queues[ $queue->get_name() ] = $queue;
        }
    }

    /**
     * Adds a queue even if one with the id already exists.
     *
     * @param QueueInterface $queue The queue instance.
     */
    public function replace( QueueInterface $queue ) {
        $this->queues[ $queue->get_name() ] = $queue;
    }

    /**
     * Get the queue instance by its name.
     *
     * @param string $id The queue name is the id.
     *
     * @return QueueInterface         The found queue instance.
     * @throws QueueNotFoundException If no queue is found, an exception is thrown.
     */
    public function get( $id ) {
        if ( ! isset( $this->queues[ $id ] ) ) {
            throw new QueueNotFoundException( "No queue found for key: $id" );
        }

        return $this->queues[ $id ];
    }

    /**
     * Check whether a queue is set or not.
     *
     * @param string $id The queue name is the id.
     *
     * @return bool
     */
    public function has( $id ) {
        return isset( $this->queues[ $id ] );
    }
}
